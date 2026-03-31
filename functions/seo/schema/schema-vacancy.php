<?php
/**
 * Schema.org — JobPosting for the vacancies CPT.
 *
 * Appends JobPosting node on singular pages, ItemList on archive.
 *
 * @package codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Archive: ItemList of JobPosting on current page.
 */
add_filter( 'codeweber_schema_graph', function ( array $graph ): array {
	if ( ! is_post_type_archive( 'vacancies' ) ) {
		return $graph;
	}

	global $wp_query;

	if ( empty( $wp_query->posts ) ) {
		return $graph;
	}

	$items = [];
	$pos   = 1;

	foreach ( $wp_query->posts as $post ) {
		$location = get_post_meta( $post->ID, '_vacancy_location', true );
		$salary   = get_post_meta( $post->ID, '_vacancy_salary', true );

		$item = [
			'@type'    => 'ListItem',
			'position' => $pos++,
			'item'     => [
				'@type'     => 'JobPosting',
				'title'     => get_the_title( $post ),
				'url'       => get_permalink( $post ),
				'datePosted' => get_the_date( 'c', $post ),
			],
		];

		if ( ! empty( $location ) ) {
			$item['item']['jobLocation'] = [
				'@type'   => 'Place',
				'address' => $location,
			];
		}

		if ( ! empty( $salary ) ) {
			$item['item']['baseSalary'] = [
				'@type'    => 'MonetaryAmount',
				'currency' => 'RUB',
				'value'    => $salary,
			];
		}

		$items[] = $item;
	}

	$graph[] = [
		'@type'           => 'ItemList',
		'@id'             => get_post_type_archive_link( 'vacancies' ) . '#itemlist',
		'itemListElement' => $items,
	];

	return $graph;
} );

/**
 * Single: full JobPosting schema.
 */
add_filter( 'codeweber_schema_graph', function ( array $graph ): array {
	if ( ! is_singular( 'vacancies' ) ) {
		return $graph;
	}

	$post_id  = get_the_ID();
	$url      = get_permalink( $post_id );
	$site_url = trailingslashit( home_url() );

	$job = [
		'@type'              => 'JobPosting',
		'@id'                => $url . '#jobposting',
		'title'              => get_the_title( $post_id ),
		'url'                => $url,
		'datePosted'         => get_the_date( 'c' ),
		'mainEntityOfPage'   => [ '@id' => $url . '#webpage' ],
		'hiringOrganization' => [ '@id' => $site_url . '#organization' ],
	];

	// Description.
	$desc = codeweber_get_seo_description( $post_id );
	if ( ! empty( $desc ) ) {
		$job['description'] = $desc;
	}

	// Company override.
	$company = get_post_meta( $post_id, '_vacancy_company', true );
	if ( ! empty( $company ) ) {
		$job['hiringOrganization'] = [
			'@type' => 'Organization',
			'name'  => $company,
			'sameAs' => $site_url,
		];
	}

	// Salary.
	$salary = get_post_meta( $post_id, '_vacancy_salary', true );
	if ( ! empty( $salary ) ) {
		$job['baseSalary'] = [
			'@type'    => 'MonetaryAmount',
			'currency' => 'RUB',
			'value'    => $salary,
		];
	}

	// Location.
	$location = get_post_meta( $post_id, '_vacancy_location', true );
	$address  = get_post_meta( $post_id, '_vacancy_yandex_address', true );
	$lat      = get_post_meta( $post_id, '_vacancy_latitude', true );
	$lng      = get_post_meta( $post_id, '_vacancy_longitude', true );

	if ( ! empty( $location ) || ! empty( $address ) ) {
		$place = [ '@type' => 'Place' ];

		if ( ! empty( $location ) ) {
			$place['address'] = $location;
		} elseif ( ! empty( $address ) ) {
			$place['address'] = $address;
		}

		if ( ! empty( $lat ) && ! empty( $lng ) ) {
			$place['geo'] = [
				'@type'     => 'GeoCoordinates',
				'latitude'  => (float) $lat,
				'longitude' => (float) $lng,
			];
		}

		$job['jobLocation'] = $place;
	}

	// Job location type from vacancy_schedule taxonomy (Remote, Office, Hybrid).
	$schedules = get_the_terms( $post_id, 'vacancy_schedule' );
	if ( $schedules && ! is_wp_error( $schedules ) ) {
		$slugs = wp_list_pluck( $schedules, 'slug' );
		if ( in_array( 'remote', $slugs, true ) ) {
			$job['jobLocationType'] = 'TELECOMMUTE';
		}
		// applicantLocationRequirements for remote.
		if ( isset( $job['jobLocationType'] ) && $job['jobLocationType'] === 'TELECOMMUTE' ) {
			$job['applicantLocationRequirements'] = [
				'@type' => 'Country',
				'name'  => 'RU',
			];
		}
	}

	// Employment type from vacancy_type taxonomy.
	$types = get_the_terms( $post_id, 'vacancy_type' );
	if ( $types && ! is_wp_error( $types ) ) {
		$type_map = [
			'full-time'  => 'FULL_TIME',
			'part-time'  => 'PART_TIME',
			'contract'   => 'CONTRACTOR',
			'internship' => 'INTERN',
			'temporary'  => 'TEMPORARY',
		];

		$employment_types = [];
		foreach ( $types as $type ) {
			$mapped = $type_map[ $type->slug ] ?? strtoupper( $type->slug );
			$employment_types[] = $mapped;
		}

		if ( count( $employment_types ) === 1 ) {
			$job['employmentType'] = $employment_types[0];
		} elseif ( count( $employment_types ) > 1 ) {
			$job['employmentType'] = $employment_types;
		}
	}

	// Status.
	$status = get_post_meta( $post_id, '_vacancy_status', true );
	if ( $status === 'closed' || $status === 'on_hold' ) {
		$job['validThrough'] = get_the_modified_date( 'c' );
	}

	// Experience requirements.
	$experience = get_post_meta( $post_id, '_vacancy_experience', true );
	if ( ! empty( $experience ) ) {
		$job['experienceRequirements'] = $experience;
	}

	// Education requirements.
	$education = get_post_meta( $post_id, '_vacancy_education', true );
	if ( ! empty( $education ) ) {
		$job['educationRequirements'] = [
			'@type'                => 'EducationalOccupationalCredential',
			'credentialCategory'   => $education,
		];
	}

	// Skills.
	$skills = get_post_meta( $post_id, '_vacancy_skills', true );
	if ( ! empty( $skills ) ) {
		$job['skills'] = $skills;
	}

	// Image.
	$thumb_id = get_post_thumbnail_id( $post_id );
	if ( $thumb_id ) {
		$image_url = wp_get_attachment_url( $thumb_id );
		if ( $image_url ) {
			$job['image'] = $image_url;
		}
	}

	$graph[] = $job;

	return $graph;
} );

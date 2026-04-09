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
	return codeweber_schema_archive_itemlist( 'vacancies', $graph, function ( WP_Post $post ): ?array {
		$site_url = trailingslashit( home_url() );

		$item = [
			'@type'      => 'JobPosting',
			'title'      => get_the_title( $post ),
			'url'        => get_permalink( $post ),
			'datePosted' => get_the_date( 'c', $post ),
		];

		// description — excerpt or stripped content.
		$excerpt = get_the_excerpt( $post );
		if ( ! empty( $excerpt ) ) {
			$item['description'] = wp_strip_all_tags( $excerpt );
		} else {
			$content = get_post_field( 'post_content', $post->ID );
			$content = wp_strip_all_tags( $content );
			if ( ! empty( $content ) ) {
				$item['description'] = wp_trim_words( $content, 55 );
			}
		}

		// hiringOrganization — company meta or site Organization node.
		$company = get_post_meta( $post->ID, '_vacancy_company', true );
		if ( ! empty( $company ) ) {
			$item['hiringOrganization'] = [
				'@type'  => 'Organization',
				'name'   => $company,
				'sameAs' => $site_url,
			];
		} else {
			$item['hiringOrganization'] = [ '@id' => $site_url . '#organization' ];
		}

		// jobLocation.
		$location = get_post_meta( $post->ID, '_vacancy_location', true );
		if ( ! empty( $location ) ) {
			$item['jobLocation'] = [
				'@type'   => 'Place',
				'address' => $location,
			];
		}

		// employmentType from _vacancy_employment_type meta.
		$emp_type = get_post_meta( $post->ID, '_vacancy_employment_type', true );
		if ( ! empty( $emp_type ) ) {
			$type_map = [
				'full-time'  => 'FULL_TIME',
				'part-time'  => 'PART_TIME',
				'contract'   => 'CONTRACTOR',
				'internship' => 'INTERN',
				'temporary'  => 'TEMPORARY',
				'seasonal'   => 'SEASONAL',
				'volunteer'  => 'VOLUNTEER',
			];
			$mapped = $type_map[ $emp_type ] ?? null;
			if ( $mapped ) {
				$item['employmentType'] = $mapped;
			}
		}

		// baseSalary — extract first number from free-text salary field.
		$salary = get_post_meta( $post->ID, '_vacancy_salary', true );
		if ( ! empty( $salary ) ) {
			preg_match( '/[\d\s]+/', $salary, $matches );
			$num = isset( $matches[0] ) ? (int) preg_replace( '/\s/', '', $matches[0] ) : 0;
			if ( $num > 0 ) {
				$item['baseSalary'] = [
					'@type'    => 'MonetaryAmount',
					'currency' => 'RUB',
					'value'    => [
						'@type'    => 'QuantitativeValue',
						'minValue' => $num,
						'unitText' => 'MONTH',
					],
				];
			}
		}

		return $item;
	} );
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

	// Salary — extract first number from free-text field.
	$salary = get_post_meta( $post_id, '_vacancy_salary', true );
	if ( ! empty( $salary ) ) {
		preg_match( '/[\d\s]+/', $salary, $matches );
		$salary_num = isset( $matches[0] ) ? (int) preg_replace( '/\s/', '', $matches[0] ) : 0;
		if ( $salary_num > 0 ) {
			$job['baseSalary'] = [
				'@type'    => 'MonetaryAmount',
				'currency' => 'RUB',
				'value'    => [
					'@type'    => 'QuantitativeValue',
					'minValue' => $salary_num,
					'unitText' => 'MONTH',
				],
			];
		}
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

	// Employment type from _vacancy_employment_type meta.
	$emp_type = get_post_meta( $post_id, '_vacancy_employment_type', true );
	if ( ! empty( $emp_type ) ) {
		$type_map = [
			'full-time'  => 'FULL_TIME',
			'part-time'  => 'PART_TIME',
			'contract'   => 'CONTRACTOR',
			'internship' => 'INTERN',
			'temporary'  => 'TEMPORARY',
			'seasonal'   => 'SEASONAL',
			'volunteer'  => 'VOLUNTEER',
		];
		$mapped = $type_map[ $emp_type ] ?? null;
		if ( $mapped ) {
			$job['employmentType'] = $mapped;
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
	$image = codeweber_schema_image( $post_id );
	if ( $image ) {
		$job['image'] = $image;
	}

	$graph[] = $job;

	return $graph;
} );

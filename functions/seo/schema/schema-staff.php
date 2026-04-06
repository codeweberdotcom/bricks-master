<?php
/**
 * Schema.org — Person for the staff CPT.
 *
 * Appends Person node on singular pages, ItemList on archive.
 *
 * @package codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Archive: ItemList of Person on current page.
 */
add_filter( 'codeweber_schema_graph', function ( array $graph ): array {
	return codeweber_schema_archive_itemlist( 'staff', $graph, function ( WP_Post $post ): ?array {
		$item = [
			'@type' => 'Person',
			'name'  => get_the_title( $post ),
			'url'   => get_permalink( $post ),
		];

		$position = get_post_meta( $post->ID, '_staff_position', true );
		if ( ! empty( $position ) ) {
			$item['jobTitle'] = $position;
		}

		$image = codeweber_schema_image( $post->ID );
		if ( $image ) {
			$item['image'] = $image;
		}

		return $item;
	} );
} );

/**
 * Single: full Person schema.
 */
add_filter( 'codeweber_schema_graph', function ( array $graph ): array {
	if ( ! is_singular( 'staff' ) ) {
		return $graph;
	}

	$post_id  = get_the_ID();
	$url      = get_permalink( $post_id );
	$site_url = trailingslashit( home_url() );

	$person = [
		'@type'            => 'Person',
		'@id'              => $url . '#person',
		'name'             => get_the_title( $post_id ),
		'url'              => $url,
		'mainEntityOfPage' => [ '@id' => $url . '#webpage' ],
		'worksFor'         => [ '@id' => $site_url . '#organization' ],
	];

	$desc = codeweber_get_seo_description( $post_id );
	if ( ! empty( $desc ) ) {
		$person['description'] = $desc;
	}

	$position = get_post_meta( $post_id, '_staff_position', true );
	if ( ! empty( $position ) ) {
		$person['jobTitle'] = $position;
	}

	$phone = get_post_meta( $post_id, '_staff_phone', true );
	if ( ! empty( $phone ) ) {
		$person['telephone'] = $phone;
	}

	$email = get_post_meta( $post_id, '_staff_email', true );
	if ( ! empty( $email ) ) {
		$person['email'] = $email;
	}

	$image = codeweber_schema_image( $post_id );
	if ( $image ) {
		$person['image'] = $image;
	}

	$city   = get_post_meta( $post_id, '_staff_city', true );
	$region = get_post_meta( $post_id, '_staff_region', true );
	if ( ! empty( $city ) || ! empty( $region ) ) {
		$address = [ '@type' => 'PostalAddress' ];
		if ( ! empty( $city ) ) {
			$address['addressLocality'] = $city;
		}
		if ( ! empty( $region ) ) {
			$address['addressRegion'] = $region;
		}
		$country = get_post_meta( $post_id, '_staff_country', true );
		if ( ! empty( $country ) ) {
			$address['addressCountry'] = $country;
		}
		$person['address'] = $address;
	}

	$social_keys = [
		'_staff_facebook', '_staff_twitter', '_staff_linkedin',
		'_staff_instagram', '_staff_telegram', '_staff_vk',
		'_staff_whatsapp', '_staff_website',
	];

	$same_as = [];
	foreach ( $social_keys as $key ) {
		$val = get_post_meta( $post_id, $key, true );
		if ( ! empty( $val ) ) {
			$same_as[] = $val;
		}
	}

	if ( ! empty( $same_as ) ) {
		$person['sameAs'] = $same_as;
	}

	$departments = get_the_terms( $post_id, 'departments' );
	if ( $departments && ! is_wp_error( $departments ) ) {
		$person['memberOf'] = [
			'@type' => 'Organization',
			'name'  => $departments[0]->name,
		];
	}

	$graph[] = $person;

	return $graph;
} );

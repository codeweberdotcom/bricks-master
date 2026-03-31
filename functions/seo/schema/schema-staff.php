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
	if ( ! is_post_type_archive( 'staff' ) ) {
		return $graph;
	}

	global $wp_query;

	if ( empty( $wp_query->posts ) ) {
		return $graph;
	}

	$items = [];
	$pos   = 1;

	foreach ( $wp_query->posts as $post ) {
		$position = get_post_meta( $post->ID, '_staff_position', true );

		$item = [
			'@type'    => 'ListItem',
			'position' => $pos++,
			'item'     => [
				'@type' => 'Person',
				'name'  => get_the_title( $post ),
				'url'   => get_permalink( $post ),
			],
		];

		if ( ! empty( $position ) ) {
			$item['item']['jobTitle'] = $position;
		}

		$thumb_id = get_post_thumbnail_id( $post->ID );
		if ( $thumb_id ) {
			$image_url = wp_get_attachment_url( $thumb_id );
			if ( $image_url ) {
				$item['item']['image'] = $image_url;
			}
		}

		$items[] = $item;
	}

	$graph[] = [
		'@type'           => 'ItemList',
		'@id'             => get_post_type_archive_link( 'staff' ) . '#itemlist',
		'itemListElement' => $items,
	];

	return $graph;
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

	// Description.
	$desc = codeweber_get_seo_description( $post_id );
	if ( ! empty( $desc ) ) {
		$person['description'] = $desc;
	}

	// Job title.
	$position = get_post_meta( $post_id, '_staff_position', true );
	if ( ! empty( $position ) ) {
		$person['jobTitle'] = $position;
	}

	// Contact.
	$phone = get_post_meta( $post_id, '_staff_phone', true );
	if ( ! empty( $phone ) ) {
		$person['telephone'] = $phone;
	}

	$email = get_post_meta( $post_id, '_staff_email', true );
	if ( ! empty( $email ) ) {
		$person['email'] = $email;
	}

	// Image.
	$thumb_id = get_post_thumbnail_id( $post_id );
	if ( $thumb_id ) {
		$image_url = wp_get_attachment_url( $thumb_id );
		if ( $image_url ) {
			$person['image'] = $image_url;
		}
	}

	// Address.
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

	// Social links (sameAs).
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

	// Department.
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

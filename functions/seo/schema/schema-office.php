<?php
/**
 * Schema.org — LocalBusiness for the offices CPT.
 *
 * Appends LocalBusiness node on singular pages, ItemList on archive.
 *
 * @package codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Archive: ItemList of LocalBusiness on current page.
 */
add_filter( 'codeweber_schema_graph', function ( array $graph ): array {
	if ( ! is_post_type_archive( 'offices' ) ) {
		return $graph;
	}

	global $wp_query;

	if ( empty( $wp_query->posts ) ) {
		return $graph;
	}

	$items = [];
	$pos   = 1;

	foreach ( $wp_query->posts as $post ) {
		$phone   = get_post_meta( $post->ID, '_office_phone', true );
		$address = get_post_meta( $post->ID, '_office_full_address', true );
		if ( empty( $address ) ) {
			$address = get_post_meta( $post->ID, '_office_street', true );
		}

		$item = [
			'@type'    => 'ListItem',
			'position' => $pos++,
			'item'     => [
				'@type' => 'LocalBusiness',
				'name'  => get_the_title( $post ),
				'url'   => get_permalink( $post ),
			],
		];

		if ( ! empty( $address ) ) {
			$item['item']['address'] = $address;
		}

		if ( ! empty( $phone ) ) {
			$item['item']['telephone'] = $phone;
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
		'@id'             => get_post_type_archive_link( 'offices' ) . '#itemlist',
		'itemListElement' => $items,
	];

	return $graph;
} );

/**
 * Single: full LocalBusiness schema.
 */
add_filter( 'codeweber_schema_graph', function ( array $graph ): array {
	if ( ! is_singular( 'offices' ) ) {
		return $graph;
	}

	$post_id  = get_the_ID();
	$url      = get_permalink( $post_id );
	$site_url = trailingslashit( home_url() );

	$business = [
		'@type'              => 'LocalBusiness',
		'@id'                => $url . '#localbusiness',
		'name'               => get_the_title( $post_id ),
		'url'                => $url,
		'mainEntityOfPage'   => [ '@id' => $url . '#webpage' ],
		'parentOrganization' => [ '@id' => $site_url . '#organization' ],
	];

	// Description.
	$desc = codeweber_get_seo_description( $post_id );
	if ( ! empty( $desc ) ) {
		$business['description'] = $desc;
	}

	// Address.
	$street  = get_post_meta( $post_id, '_office_street', true );
	$city    = get_post_meta( $post_id, '_office_city', true );
	$region  = get_post_meta( $post_id, '_office_region', true );
	$postal  = get_post_meta( $post_id, '_office_postal_code', true );
	$country = get_post_meta( $post_id, '_office_country', true );

	if ( ! empty( $street ) || ! empty( $city ) ) {
		$address = [ '@type' => 'PostalAddress' ];

		if ( ! empty( $street ) ) {
			$address['streetAddress'] = $street;
		}
		if ( ! empty( $city ) ) {
			$address['addressLocality'] = $city;
		}
		if ( ! empty( $region ) ) {
			$address['addressRegion'] = $region;
		}
		if ( ! empty( $postal ) ) {
			$address['postalCode'] = $postal;
		}
		if ( ! empty( $country ) ) {
			$address['addressCountry'] = $country;
		}

		$business['address'] = $address;
	}

	// Full address as fallback.
	$full_address = get_post_meta( $post_id, '_office_full_address', true );
	if ( ! isset( $business['address'] ) && ! empty( $full_address ) ) {
		$business['address'] = $full_address;
	}

	// Geo coordinates.
	$lat = get_post_meta( $post_id, '_office_latitude', true );
	$lng = get_post_meta( $post_id, '_office_longitude', true );

	if ( ! empty( $lat ) && ! empty( $lng ) ) {
		$business['geo'] = [
			'@type'     => 'GeoCoordinates',
			'latitude'  => (float) $lat,
			'longitude' => (float) $lng,
		];
	}

	// Contact.
	$phone = get_post_meta( $post_id, '_office_phone', true );
	if ( ! empty( $phone ) ) {
		$business['telephone'] = $phone;
	}

	$phone2 = get_post_meta( $post_id, '_office_phone_2', true );
	if ( ! empty( $phone2 ) ) {
		$business['telephone'] = [ $phone, $phone2 ];
	}

	$email = get_post_meta( $post_id, '_office_email', true );
	if ( ! empty( $email ) ) {
		$business['email'] = $email;
	}

	$fax = get_post_meta( $post_id, '_office_fax', true );
	if ( ! empty( $fax ) ) {
		$business['faxNumber'] = $fax;
	}

	$website = get_post_meta( $post_id, '_office_website', true );
	if ( ! empty( $website ) ) {
		$business['sameAs'] = $website;
	}

	// Opening hours.
	$hours = get_post_meta( $post_id, '_office_working_hours', true );
	if ( ! empty( $hours ) ) {
		$business['openingHours'] = wp_strip_all_tags( $hours );
	}

	// Image.
	$thumb_id = get_post_thumbnail_id( $post_id );
	if ( $thumb_id ) {
		$image_url = wp_get_attachment_url( $thumb_id );
		if ( $image_url ) {
			$business['image'] = $image_url;
		}
	}

	// Office-specific image.
	$office_image = get_post_meta( $post_id, '_office_image', true );
	if ( ! empty( $office_image ) && ! isset( $business['image'] ) ) {
		$image_url = wp_get_attachment_url( (int) $office_image );
		if ( $image_url ) {
			$business['image'] = $image_url;
		}
	}

	$graph[] = $business;

	return $graph;
} );

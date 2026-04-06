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
	return codeweber_schema_archive_itemlist( 'offices', $graph, function ( WP_Post $post ): ?array {
		$item = [
			'@type' => 'LocalBusiness',
			'name'  => get_the_title( $post ),
			'url'   => get_permalink( $post ),
		];

		$address = get_post_meta( $post->ID, '_office_full_address', true );
		if ( empty( $address ) ) {
			$address = get_post_meta( $post->ID, '_office_street', true );
		}
		if ( ! empty( $address ) ) {
			$item['address'] = $address;
		}

		$phone = get_post_meta( $post->ID, '_office_phone', true );
		if ( ! empty( $phone ) ) {
			$item['telephone'] = $phone;
		}

		$image = codeweber_schema_image( $post->ID );
		if ( $image ) {
			$item['image'] = $image;
		}

		return $item;
	} );
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

	// Opening hours (structured).
	$office_hours = codeweber_get_office_hours( $post_id );
	if ( ! empty( $office_hours ) ) {
		$specs = codeweber_schema_opening_hours( $office_hours );
		if ( ! empty( $specs ) ) {
			$business['openingHoursSpecification'] = $specs;
		}
	}

	// Image.
	$image = codeweber_schema_image( $post_id );
	if ( $image ) {
		$business['image'] = $image;
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

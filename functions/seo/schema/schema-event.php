<?php
/**
 * Schema.org — Event for the events CPT.
 *
 * Appends Event node on singular pages, ItemList on archive.
 *
 * @package codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Archive: ItemList of Events on current page.
 */
add_filter( 'codeweber_schema_graph', function ( array $graph ): array {
	if ( ! is_post_type_archive( 'events' ) ) {
		return $graph;
	}

	global $wp_query;

	if ( empty( $wp_query->posts ) ) {
		return $graph;
	}

	$items = [];
	$pos   = 1;

	foreach ( $wp_query->posts as $post ) {
		$date_start = get_post_meta( $post->ID, '_event_date_start', true );
		$location   = get_post_meta( $post->ID, '_event_location', true );

		$item = [
			'@type'    => 'ListItem',
			'position' => $pos++,
			'item'     => [
				'@type' => 'Event',
				'name'  => get_the_title( $post ),
				'url'   => get_permalink( $post ),
			],
		];

		if ( ! empty( $date_start ) ) {
			$item['item']['startDate'] = codeweber_schema_datetime( $date_start );
		}

		if ( ! empty( $location ) ) {
			$item['item']['location'] = [
				'@type' => 'Place',
				'name'  => $location,
			];
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
		'@id'             => get_post_type_archive_link( 'events' ) . '#itemlist',
		'itemListElement' => $items,
	];

	return $graph;
} );

/**
 * Single: full Event schema.
 */
add_filter( 'codeweber_schema_graph', function ( array $graph ): array {
	if ( ! is_singular( 'events' ) ) {
		return $graph;
	}

	$post_id  = get_the_ID();
	$url      = get_permalink( $post_id );
	$site_url = trailingslashit( home_url() );

	$event = [
		'@type'            => 'Event',
		'@id'              => $url . '#event',
		'name'             => codeweber_get_seo_title( $post_id ),
		'url'              => $url,
		'mainEntityOfPage' => [ '@id' => $url . '#webpage' ],
		'organizer'        => [ '@id' => $site_url . '#organization' ],
	];

	// Description.
	$desc = codeweber_get_seo_description( $post_id );
	if ( ! empty( $desc ) ) {
		$event['description'] = $desc;
	}

	// Dates.
	$date_start = get_post_meta( $post_id, '_event_date_start', true );
	if ( ! empty( $date_start ) ) {
		$event['startDate'] = codeweber_schema_datetime( $date_start );
	}

	$date_end = get_post_meta( $post_id, '_event_date_end', true );
	if ( ! empty( $date_end ) ) {
		$event['endDate'] = codeweber_schema_datetime( $date_end );
	}

	// Location.
	$location_name = get_post_meta( $post_id, '_event_location', true );
	$address       = get_post_meta( $post_id, '_event_address', true );
	$latitude      = get_post_meta( $post_id, '_event_latitude', true );
	$longitude     = get_post_meta( $post_id, '_event_longitude', true );

	// Determine event attendance mode from taxonomy.
	$formats        = get_the_terms( $post_id, 'event_format' );
	$is_online      = false;
	$attendance_mode = 'https://schema.org/OfflineEventAttendanceMode';

	if ( $formats && ! is_wp_error( $formats ) ) {
		$format_slugs = wp_list_pluck( $formats, 'slug' );
		if ( in_array( 'online', $format_slugs, true ) ) {
			$is_online      = true;
			$attendance_mode = 'https://schema.org/OnlineEventAttendanceMode';
		}
		if ( in_array( 'hybrid', $format_slugs, true ) ) {
			$attendance_mode = 'https://schema.org/MixedEventAttendanceMode';
		}
	}

	$event['eventAttendanceMode'] = $attendance_mode;

	if ( ! empty( $location_name ) || ! empty( $address ) ) {
		$place = [
			'@type' => 'Place',
		];

		if ( ! empty( $location_name ) ) {
			$place['name'] = $location_name;
		}

		if ( ! empty( $address ) ) {
			$place['address'] = [
				'@type'          => 'PostalAddress',
				'streetAddress'  => $address,
			];
		}

		if ( ! empty( $latitude ) && ! empty( $longitude ) ) {
			$place['geo'] = [
				'@type'     => 'GeoCoordinates',
				'latitude'  => (float) $latitude,
				'longitude' => (float) $longitude,
			];
		}

		$event['location'] = $place;
	}

	// Online location.
	if ( $is_online || $attendance_mode === 'https://schema.org/MixedEventAttendanceMode' ) {
		$reg_url = get_post_meta( $post_id, '_event_registration_url', true );
		$virtual = [
			'@type' => 'VirtualLocation',
			'url'   => ! empty( $reg_url ) ? $reg_url : $url,
		];

		if ( $is_online && empty( $event['location'] ) ) {
			$event['location'] = $virtual;
		} elseif ( $attendance_mode === 'https://schema.org/MixedEventAttendanceMode' ) {
			// Mixed: physical location already set, add virtual as array.
			$event['location'] = [ $event['location'], $virtual ];
		}
	}

	// Organizer override.
	$organizer = get_post_meta( $post_id, '_event_organizer', true );
	if ( ! empty( $organizer ) ) {
		$event['organizer'] = [
			'@type' => 'Organization',
			'name'  => $organizer,
		];
	}

	// Offers (price / registration).
	$price   = get_post_meta( $post_id, '_event_price', true );
	$reg_enabled = get_post_meta( $post_id, '_event_registration_enabled', true );

	$offer = [
		'@type' => 'Offer',
		'url'   => $url,
	];

	if ( ! empty( $price ) ) {
		// Try to extract numeric value.
		$numeric = preg_replace( '/[^\d.,]/', '', $price );
		if ( is_numeric( str_replace( ',', '.', $numeric ) ) && (float) str_replace( ',', '.', $numeric ) > 0 ) {
			$offer['price']         = str_replace( ',', '.', $numeric );
			$offer['priceCurrency'] = 'RUB';
		} else {
			$offer['price']         = 0;
			$offer['priceCurrency'] = 'RUB';
			$offer['description']   = $price;
		}
	} else {
		$offer['price']         = 0;
		$offer['priceCurrency'] = 'RUB';
	}

	// Availability.
	if ( ! empty( $reg_enabled ) && $reg_enabled !== '0' ) {
		$max = (int) get_post_meta( $post_id, '_event_max_participants', true );
		$offer['availability'] = 'https://schema.org/InStock';

		if ( $max > 0 ) {
			// Check real + fake registrations.
			$real_count = codeweber_schema_event_registration_count( $post_id );
			$fake       = (int) get_post_meta( $post_id, '_event_fake_registered', true );
			$total      = $real_count + $fake;

			if ( $total >= $max ) {
				$offer['availability'] = 'https://schema.org/SoldOut';
			}
		}
	}

	// Valid from (registration opens).
	$reg_open = get_post_meta( $post_id, '_event_registration_open', true );
	if ( ! empty( $reg_open ) ) {
		$offer['validFrom'] = codeweber_schema_datetime( $reg_open );
	}

	$event['offers'] = $offer;

	// Event status.
	$now        = current_time( 'timestamp' );
	$end_ts     = ! empty( $date_end ) ? strtotime( $date_end ) : 0;
	$start_ts   = ! empty( $date_start ) ? strtotime( $date_start ) : 0;

	if ( $end_ts && $now > $end_ts ) {
		$event['eventStatus'] = 'https://schema.org/EventPostponed';
	} elseif ( $start_ts && $now < $start_ts ) {
		$event['eventStatus'] = 'https://schema.org/EventScheduled';
	}

	// Image.
	$thumb_id = get_post_thumbnail_id( $post_id );
	if ( $thumb_id ) {
		$image_url = wp_get_attachment_url( $thumb_id );
		if ( $image_url ) {
			$event['image'] = $image_url;
		}
	}

	$graph[] = $event;

	return $graph;
} );

/**
 * Convert datetime-local value to ISO 8601.
 *
 * @param string $datetime Value from datetime-local input (Y-m-d\TH:i or Y-m-d H:i:s).
 * @return string ISO 8601 datetime string.
 */
function codeweber_schema_datetime( string $datetime ): string {
	$ts = strtotime( $datetime );

	if ( ! $ts ) {
		return $datetime;
	}

	return wp_date( 'c', $ts );
}

/**
 * Count real event registrations.
 *
 * @param int $event_id Event post ID.
 * @return int Registration count.
 */
function codeweber_schema_event_registration_count( int $event_id ): int {
	$query = new WP_Query( [
		'post_type'      => 'event_registration',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_query'     => [
			[
				'key'   => '_reg_event_id',
				'value' => $event_id,
			],
		],
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	] );

	return $query->post_count;
}

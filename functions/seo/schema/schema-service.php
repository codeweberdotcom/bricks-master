<?php
/**
 * Schema.org — Service for the services CPT.
 *
 * Appends Service node on singular pages, ItemList on archive.
 *
 * @package codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Archive: ItemList of Service on current page.
 */
add_filter( 'codeweber_schema_graph', function ( array $graph ): array {
	if ( ! is_post_type_archive( 'services' ) ) {
		return $graph;
	}

	global $wp_query;

	if ( empty( $wp_query->posts ) ) {
		return $graph;
	}

	$items = [];
	$pos   = 1;

	foreach ( $wp_query->posts as $post ) {
		$item = [
			'@type'    => 'ListItem',
			'position' => $pos++,
			'item'     => [
				'@type' => 'Service',
				'name'  => get_the_title( $post ),
				'url'   => get_permalink( $post ),
			],
		];

		$short_desc = get_post_meta( $post->ID, '_service_description_short', true );
		if ( ! empty( $short_desc ) ) {
			$item['item']['description'] = $short_desc;
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
		'@id'             => get_post_type_archive_link( 'services' ) . '#itemlist',
		'itemListElement' => $items,
	];

	return $graph;
} );

/**
 * Single: full Service schema.
 */
add_filter( 'codeweber_schema_graph', function ( array $graph ): array {
	if ( ! is_singular( 'services' ) ) {
		return $graph;
	}

	$post_id  = get_the_ID();
	$url      = get_permalink( $post_id );
	$site_url = trailingslashit( home_url() );

	$service = [
		'@type'            => 'Service',
		'@id'              => $url . '#service',
		'name'             => codeweber_get_seo_title( $post_id ),
		'url'              => $url,
		'mainEntityOfPage' => [ '@id' => $url . '#webpage' ],
		'provider'         => [ '@id' => $site_url . '#organization' ],
	];

	// Description.
	$desc = codeweber_get_seo_description( $post_id );
	if ( ! empty( $desc ) ) {
		$service['description'] = $desc;
	}

	// Short description.
	$short_desc = get_post_meta( $post_id, '_service_description_short', true );
	if ( ! empty( $short_desc ) && empty( $desc ) ) {
		$service['description'] = $short_desc;
	}

	// Category from taxonomy.
	$categories = get_the_terms( $post_id, 'service_category' );
	if ( $categories && ! is_wp_error( $categories ) ) {
		$service['category'] = $categories[0]->name;
	}

	// Service type from taxonomy.
	$types = get_the_terms( $post_id, 'types_of_services' );
	if ( $types && ! is_wp_error( $types ) ) {
		$service['serviceType'] = wp_list_pluck( $types, 'name' );
		if ( count( $service['serviceType'] ) === 1 ) {
			$service['serviceType'] = $service['serviceType'][0];
		}
	}

	// Price info.
	$price = get_post_meta( $post_id, '_service_price_info', true );
	if ( ! empty( $price ) ) {
		$service['offers'] = [
			'@type'         => 'Offer',
			'description'   => $price,
			'priceCurrency' => 'RUB',
			'url'           => $url,
		];
	}

	// Image.
	$thumb_id = get_post_thumbnail_id( $post_id );
	if ( $thumb_id ) {
		$image_url = wp_get_attachment_url( $thumb_id );
		if ( $image_url ) {
			$service['image'] = $image_url;
		}
	}

	$graph[] = $service;

	return $graph;
} );

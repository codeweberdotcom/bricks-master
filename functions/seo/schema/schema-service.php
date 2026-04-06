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
	return codeweber_schema_archive_itemlist( 'services', $graph, function ( WP_Post $post ): ?array {
		$item = [
			'@type' => 'Service',
			'name'  => get_the_title( $post ),
			'url'   => get_permalink( $post ),
		];

		$short_desc = get_post_meta( $post->ID, '_service_description_short', true );
		if ( ! empty( $short_desc ) ) {
			$item['description'] = $short_desc;
		}

		$image = codeweber_schema_image( $post->ID );
		if ( $image ) {
			$item['image'] = $image;
		}

		return $item;
	} );
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
	$image = codeweber_schema_image( $post_id );
	if ( $image ) {
		$service['image'] = $image;
	}

	$graph[] = $service;

	return $graph;
} );

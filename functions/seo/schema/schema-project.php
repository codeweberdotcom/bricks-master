<?php
/**
 * Schema.org — CreativeWork for the projects CPT.
 *
 * Appends CreativeWork node on singular pages, ItemList on archive.
 *
 * @package codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Archive: ItemList of CreativeWork on current page.
 */
add_filter( 'codeweber_schema_graph', function ( array $graph ): array {
	if ( ! is_post_type_archive( 'projects' ) ) {
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
				'@type' => 'CreativeWork',
				'name'  => get_the_title( $post ),
				'url'   => get_permalink( $post ),
			],
		];

		$thumb_id = get_post_thumbnail_id( $post->ID );
		if ( $thumb_id ) {
			$image_url = wp_get_attachment_url( $thumb_id );
			if ( $image_url ) {
				$item['item']['image'] = $image_url;
			}
		}

		$categories = get_the_terms( $post->ID, 'projects_category' );
		if ( $categories && ! is_wp_error( $categories ) ) {
			$item['item']['genre'] = $categories[0]->name;
		}

		$items[] = $item;
	}

	$graph[] = [
		'@type'           => 'ItemList',
		'@id'             => get_post_type_archive_link( 'projects' ) . '#itemlist',
		'itemListElement' => $items,
	];

	return $graph;
} );

/**
 * Single: full CreativeWork schema.
 */
add_filter( 'codeweber_schema_graph', function ( array $graph ): array {
	if ( ! is_singular( 'projects' ) ) {
		return $graph;
	}

	$post_id  = get_the_ID();
	$post     = get_post( $post_id );
	$url      = get_permalink( $post_id );
	$site_url = trailingslashit( home_url() );

	$work = [
		'@type'            => 'CreativeWork',
		'@id'              => $url . '#creativework',
		'name'             => codeweber_get_seo_title( $post_id ),
		'url'              => $url,
		'datePublished'    => get_the_date( 'c', $post ),
		'dateModified'     => get_the_modified_date( 'c', $post ),
		'mainEntityOfPage' => [ '@id' => $url . '#webpage' ],
		'creator'          => [ '@id' => $site_url . '#organization' ],
	];

	// Description.
	$desc = codeweber_get_seo_description( $post_id );
	if ( ! empty( $desc ) ) {
		$work['description'] = $desc;
	}

	// Image.
	$thumb_id = get_post_thumbnail_id( $post_id );
	if ( $thumb_id ) {
		$image_url = wp_get_attachment_url( $thumb_id );
		if ( $image_url ) {
			$work['image'] = $image_url;
		}
	}

	// Categories as keywords.
	$categories = get_the_terms( $post_id, 'projects_category' );
	if ( $categories && ! is_wp_error( $categories ) ) {
		$work['keywords'] = implode( ', ', wp_list_pluck( $categories, 'name' ) );
		$work['genre']    = $categories[0]->name;
	}

	// Author.
	$author = get_userdata( $post->post_author );
	if ( $author ) {
		$work['author'] = [
			'@type' => 'Person',
			'name'  => $author->display_name,
		];
	}

	$graph[] = $work;

	return $graph;
} );

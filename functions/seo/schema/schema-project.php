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
	return codeweber_schema_archive_itemlist( 'projects', $graph, function ( WP_Post $post ): ?array {
		$item = [
			'@type' => 'CreativeWork',
			'name'  => get_the_title( $post ),
			'url'   => get_permalink( $post ),
		];

		$image = codeweber_schema_image( $post->ID );
		if ( $image ) {
			$item['image'] = $image;
		}

		$categories = get_the_terms( $post->ID, 'projects_category' );
		if ( $categories && ! is_wp_error( $categories ) ) {
			$item['genre'] = $categories[0]->name;
		}

		return $item;
	} );
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
	$image = codeweber_schema_image( $post_id );
	if ( $image ) {
		$work['image'] = $image;
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

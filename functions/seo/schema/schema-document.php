<?php
/**
 * Schema.org — DigitalDocument for the documents CPT.
 *
 * Appends DigitalDocument node on singular pages, ItemList on archive.
 *
 * @package codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Archive: ItemList of DigitalDocument on current page.
 */
add_filter( 'codeweber_schema_graph', function ( array $graph ): array {
	if ( ! is_post_type_archive( 'documents' ) ) {
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
				'@type' => 'DigitalDocument',
				'name'  => get_the_title( $post ),
				'url'   => get_permalink( $post ),
			],
		];

		$file_url = codeweber_schema_document_url( $post->ID );
		if ( $file_url ) {
			$ext = strtolower( pathinfo( $file_url, PATHINFO_EXTENSION ) );
			if ( ! empty( $ext ) ) {
				$item['item']['encodingFormat'] = $ext;
			}
		}

		$items[] = $item;
	}

	$graph[] = [
		'@type'           => 'ItemList',
		'@id'             => get_post_type_archive_link( 'documents' ) . '#itemlist',
		'itemListElement' => $items,
	];

	return $graph;
} );

/**
 * Single: full DigitalDocument schema.
 */
add_filter( 'codeweber_schema_graph', function ( array $graph ): array {
	if ( ! is_singular( 'documents' ) ) {
		return $graph;
	}

	$post_id  = get_the_ID();
	$post     = get_post( $post_id );
	$url      = get_permalink( $post_id );
	$site_url = trailingslashit( home_url() );

	$doc = [
		'@type'            => 'DigitalDocument',
		'@id'              => $url . '#document',
		'name'             => codeweber_get_seo_title( $post_id ),
		'url'              => $url,
		'datePublished'    => get_the_date( 'c', $post ),
		'dateModified'     => get_the_modified_date( 'c', $post ),
		'mainEntityOfPage' => [ '@id' => $url . '#webpage' ],
		'publisher'        => [ '@id' => $site_url . '#organization' ],
	];

	// Description.
	$desc = codeweber_get_seo_description( $post_id );
	if ( ! empty( $desc ) ) {
		$doc['description'] = $desc;
	}

	// File URL and format.
	$file_url = codeweber_schema_document_url( $post_id );
	if ( $file_url ) {
		$doc['contentUrl'] = $file_url;

		$ext = strtolower( pathinfo( $file_url, PATHINFO_EXTENSION ) );
		if ( ! empty( $ext ) ) {
			$mime_map = [
				'pdf'  => 'application/pdf',
				'doc'  => 'application/msword',
				'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
				'xls'  => 'application/vnd.ms-excel',
				'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
				'csv'  => 'text/csv',
				'ppt'  => 'application/vnd.ms-powerpoint',
				'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
				'txt'  => 'text/plain',
				'rtf'  => 'application/rtf',
			];

			$doc['encodingFormat'] = $mime_map[ $ext ] ?? $ext;
		}
	}

	// Categories.
	$categories = get_the_terms( $post_id, 'document_category' );
	if ( $categories && ! is_wp_error( $categories ) ) {
		$doc['keywords'] = implode( ', ', wp_list_pluck( $categories, 'name' ) );
	}

	// Document type taxonomy.
	$types = get_the_terms( $post_id, 'document_type' );
	if ( $types && ! is_wp_error( $types ) ) {
		$doc['additionalType'] = $types[0]->name;
	}

	// Image.
	$thumb_id = get_post_thumbnail_id( $post_id );
	if ( $thumb_id ) {
		$image_url = wp_get_attachment_url( $thumb_id );
		if ( $image_url ) {
			$doc['image'] = $image_url;
		}
	}

	$graph[] = $doc;

	return $graph;
} );

/**
 * Get the document file URL from meta.
 *
 * The _document_file meta stores either an attachment ID or a direct URL.
 *
 * @param int $post_id Post ID.
 * @return string|null File URL or null.
 */
function codeweber_schema_document_url( int $post_id ): ?string {
	$file_meta = get_post_meta( $post_id, '_document_file', true );

	if ( empty( $file_meta ) ) {
		return null;
	}

	if ( is_numeric( $file_meta ) ) {
		$url = wp_get_attachment_url( (int) $file_meta );
		return $url ?: null;
	}

	return $file_meta;
}

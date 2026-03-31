<?php
/**
 * Schema.org — Article for blog posts.
 *
 * Appends Article node to the @graph on singular post pages.
 *
 * @package codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'codeweber_schema_graph', function ( array $graph ): array {
	if ( ! is_singular( 'post' ) ) {
		return $graph;
	}

	$post    = get_post();
	$post_id = $post->ID;
	$url     = get_permalink( $post_id );
	$site_url = trailingslashit( home_url() );

	$article = [
		'@type'            => 'Article',
		'@id'              => $url . '#article',
		'headline'         => codeweber_get_seo_title( $post_id ),
		'url'              => $url,
		'datePublished'    => get_the_date( 'c', $post ),
		'dateModified'     => get_the_modified_date( 'c', $post ),
		'mainEntityOfPage' => [ '@id' => $url . '#webpage' ],
		'publisher'        => [ '@id' => $site_url . '#organization' ],
	];

	// Description.
	$desc = codeweber_get_seo_description( $post_id );
	if ( ! empty( $desc ) ) {
		$article['description'] = $desc;
	}

	// Author.
	$author = get_userdata( $post->post_author );
	if ( $author ) {
		$article['author'] = [
			'@type' => 'Person',
			'name'  => $author->display_name,
			'url'   => get_author_posts_url( $author->ID ),
		];
	}

	// Image.
	$thumb_id = get_post_thumbnail_id( $post_id );
	if ( $thumb_id ) {
		$image_url = wp_get_attachment_url( $thumb_id );
		if ( $image_url ) {
			$article['image'] = [
				'@type' => 'ImageObject',
				'url'   => $image_url,
			];
			$meta = wp_get_attachment_metadata( $thumb_id );
			if ( $meta ) {
				$article['image']['width']  = $meta['width'] ?? 0;
				$article['image']['height'] = $meta['height'] ?? 0;
			}
		}
	}

	// Word count.
	$word_count = str_word_count( wp_strip_all_tags( $post->post_content ) );
	if ( $word_count > 0 ) {
		$article['wordCount'] = $word_count;
	}

	// Categories as keywords.
	$categories = get_the_category( $post_id );
	if ( ! empty( $categories ) ) {
		$article['keywords'] = implode( ', ', wp_list_pluck( $categories, 'name' ) );

		// Article section (first category).
		$article['articleSection'] = $categories[0]->name;
	}

	$graph[] = $article;

	return $graph;
} );

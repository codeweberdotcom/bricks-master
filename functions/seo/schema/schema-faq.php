<?php
/**
 * Schema.org — FAQPage for the faq CPT.
 *
 * Appends FAQPage node on singular and archive pages.
 *
 * @package codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Archive: FAQPage with all Q/A pairs from current page.
 */
add_filter( 'codeweber_schema_graph', function ( array $graph ): array {
	if ( ! is_post_type_archive( 'faq' ) ) {
		return $graph;
	}

	global $wp_query;

	if ( empty( $wp_query->posts ) ) {
		return $graph;
	}

	$questions = [];

	foreach ( $wp_query->posts as $post ) {
		$answer = preg_replace( '/<!--.*?-->/s', '', $post->post_content );
		$answer = strip_shortcodes( $answer );
		$answer = html_entity_decode( $answer, ENT_QUOTES, 'UTF-8' );
		$answer = wp_strip_all_tags( $answer );
		$answer = preg_replace( '/\s+/', ' ', trim( $answer ) );

		if ( empty( $answer ) ) {
			continue;
		}

		$questions[] = [
			'@type' => 'Question',
			'name'  => get_the_title( $post ),
			'acceptedAnswer' => [
				'@type' => 'Answer',
				'text'  => $answer,
			],
		];
	}

	if ( empty( $questions ) ) {
		return $graph;
	}

	$graph[] = [
		'@type'      => 'FAQPage',
		'@id'        => get_post_type_archive_link( 'faq' ) . '#faqpage',
		'mainEntity' => $questions,
	];

	return $graph;
} );

/**
 * Single: FAQPage with one Q/A pair.
 */
add_filter( 'codeweber_schema_graph', function ( array $graph ): array {
	if ( ! is_singular( 'faq' ) ) {
		return $graph;
	}

	$post_id = get_the_ID();
	$post    = get_post( $post_id );
	$url     = get_permalink( $post_id );

	$answer = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
	$answer = preg_replace( '/\s+/', ' ', trim( $answer ) );

	if ( empty( $answer ) ) {
		return $graph;
	}

	$graph[] = [
		'@type'            => 'FAQPage',
		'@id'              => $url . '#faqpage',
		'mainEntityOfPage' => [ '@id' => $url . '#webpage' ],
		'mainEntity'       => [
			[
				'@type' => 'Question',
				'name'  => get_the_title( $post_id ),
				'acceptedAnswer' => [
					'@type' => 'Answer',
					'text'  => $answer,
				],
			],
		],
	];

	return $graph;
} );

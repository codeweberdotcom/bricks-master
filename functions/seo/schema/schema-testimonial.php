<?php
/**
 * Schema.org — Review for the testimonials CPT.
 *
 * Appends Review node on singular pages, AggregateRating on archive.
 *
 * @package codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Archive: Organization with AggregateRating.
 */
add_filter( 'codeweber_schema_graph', function ( array $graph ): array {
	if ( ! is_post_type_archive( 'testimonials' ) ) {
		return $graph;
	}

	// Single SQL query for aggregate rating (avoids N+1 meta reads).
	global $wpdb;

	$row = $wpdb->get_row(
		"SELECT COUNT(*) AS total, SUM(pm.meta_value) AS sum_rating
		FROM {$wpdb->posts} p
		INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
		WHERE p.post_type = 'testimonials'
		AND p.post_status = 'publish'
		AND pm.meta_key = '_testimonial_rating'
		AND CAST(pm.meta_value AS UNSIGNED) > 0"
	);

	if ( ! $row || (int) $row->total === 0 ) {
		return $graph;
	}

	$total = (int) $row->total;
	$sum   = (int) $row->sum_rating;

	$site_url = trailingslashit( home_url() );
	$org_id   = $site_url . '#organization';

	// Add aggregateRating to existing Organization node.
	foreach ( $graph as &$node ) {
		if ( isset( $node['@id'] ) && $node['@id'] === $org_id ) {
			$node['aggregateRating'] = [
				'@type'       => 'AggregateRating',
				'ratingValue' => round( $sum / $total, 1 ),
				'reviewCount' => $total,
				'bestRating'  => 5,
				'worstRating' => 1,
			];
			break;
		}
	}
	unset( $node );

	return $graph;
} );

/**
 * Single: full Review schema.
 */
add_filter( 'codeweber_schema_graph', function ( array $graph ): array {
	if ( ! is_singular( 'testimonials' ) ) {
		return $graph;
	}

	$post_id  = get_the_ID();
	$post     = get_post( $post_id );
	$url      = get_permalink( $post_id );
	$site_url = trailingslashit( home_url() );

	$review = [
		'@type'            => 'Review',
		'@id'              => $url . '#review',
		'url'              => $url,
		'mainEntityOfPage' => [ '@id' => $url . '#webpage' ],
		'itemReviewed'     => [ '@id' => $site_url . '#organization' ],
		'datePublished'    => get_the_date( 'c', $post ),
	];

	// Author.
	$author_name  = get_post_meta( $post_id, '_testimonial_author_name', true );
	$author_title = get_post_meta( $post_id, '_testimonial_author_title', true );

	if ( ! empty( $author_name ) ) {
		$author = [
			'@type' => 'Person',
			'name'  => $author_name,
		];
		if ( ! empty( $author_title ) ) {
			$author['jobTitle'] = $author_title;
		}

		// Author image (post thumbnail).
		$thumb_id = get_post_thumbnail_id( $post_id );
		if ( $thumb_id ) {
			$image_url = wp_get_attachment_url( $thumb_id );
			if ( $image_url ) {
				$author['image'] = $image_url;
			}
		}

		$review['author'] = $author;
	}

	// Rating.
	$rating = (int) get_post_meta( $post_id, '_testimonial_rating', true );
	if ( $rating > 0 ) {
		$review['reviewRating'] = [
			'@type'      => 'Rating',
			'ratingValue' => $rating,
			'bestRating'  => 5,
			'worstRating' => 1,
		];
	}

	// Review body.
	$body = wp_strip_all_tags( $post->post_content );
	if ( ! empty( $body ) ) {
		$review['reviewBody'] = $body;
	}

	// Short content as name/headline.
	$short = get_post_meta( $post_id, '_testimonial_content_short', true );
	if ( ! empty( $short ) ) {
		$review['name'] = $short;
	} else {
		$review['name'] = get_the_title( $post_id );
	}

	// Video testimonial.
	$video_url = get_post_meta( $post_id, '_testimonial_video_url', true );
	if ( ! empty( $video_url ) ) {
		$review['video'] = [
			'@type'    => 'VideoObject',
			'embedUrl' => $video_url,
			'name'     => get_the_title( $post_id ),
		];
	}

	$graph[] = $review;

	return $graph;
} );

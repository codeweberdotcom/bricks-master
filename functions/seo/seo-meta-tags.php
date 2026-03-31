<?php
/**
 * SEO Data Helpers.
 *
 * Provides codeweber_get_seo_title() and codeweber_get_seo_description()
 * that read from Rank Math / Yoast fields with automatic fallback.
 * Used by the OG and Schema modules.
 *
 * @package codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the SEO title for a given post.
 *
 * Priority: Rank Math → Yoast → post title.
 *
 * @param int|null $post_id Post ID (defaults to current post).
 * @return string SEO title.
 */
function codeweber_get_seo_title( ?int $post_id = null ): string {
	if ( null === $post_id ) {
		$post_id = get_the_ID();
	}

	if ( ! $post_id ) {
		return '';
	}

	// Rank Math
	$title = get_post_meta( $post_id, 'rank_math_title', true );

	// Yoast
	if ( empty( $title ) ) {
		$title = get_post_meta( $post_id, '_yoast_wpseo_title', true );
	}

	// Fallback
	if ( empty( $title ) ) {
		$title = get_the_title( $post_id );
	}

	return $title;
}

/**
 * Get the SEO description for a given post.
 *
 * Priority: Rank Math → Yoast → excerpt → trimmed content.
 *
 * @param int|null $post_id Post ID (defaults to current post).
 * @return string SEO description (plain text, max 160 chars).
 */
function codeweber_get_seo_description( ?int $post_id = null ): string {
	if ( null === $post_id ) {
		$post_id = get_the_ID();
	}

	if ( ! $post_id ) {
		return '';
	}

	// Rank Math
	$desc = get_post_meta( $post_id, 'rank_math_description', true );

	// Yoast
	if ( empty( $desc ) ) {
		$desc = get_post_meta( $post_id, '_yoast_wpseo_metadesc', true );
	}

	// Fallback: excerpt → content
	if ( empty( $desc ) ) {
		$post = get_post( $post_id );

		if ( $post ) {
			$desc = $post->post_excerpt;

			if ( empty( $desc ) ) {
				$desc = $post->post_content;
			}

			$desc = wp_strip_all_tags( strip_shortcodes( $desc ) );
			$desc = preg_replace( '/\s+/', ' ', $desc );
			$desc = mb_substr( trim( $desc ), 0, 160 );
		}
	}

	return $desc ?: '';
}

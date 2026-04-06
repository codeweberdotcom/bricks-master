<?php
/**
 * SEO Plugin Detection & Schema Suppression.
 *
 * Detects known SEO plugins and disables their Schema JSON-LD output
 * so the theme generates its own. OG, Twitter Cards, title, description,
 * canonical and robots remain under the SEO plugin's control.
 *
 * @package codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check whether any supported SEO plugin is currently active.
 *
 * Detected plugins: Rank Math, Yoast SEO, SEOPress, All in One SEO.
 *
 * @return bool True when an SEO plugin is active.
 */
function codeweber_has_seo_plugin(): bool {
	static $result = null;

	if ( null !== $result ) {
		return $result;
	}

	$result = (
		// Rank Math
		class_exists( 'RankMath' ) ||
		// Yoast SEO
		defined( 'WPSEO_VERSION' ) ||
		// SEOPress
		defined( 'SEOPRESS_VERSION' ) ||
		// All in One SEO
		defined( 'AIOSEO_VERSION' )
	);

	return $result;
}

/**
 * Disable Schema JSON-LD output from SEO plugins.
 * The theme generates its own structured data for all CPTs.
 */
add_action( 'init', function (): void {
	// ── Rank Math ─────────────────────────────────────────────────────────────
	if ( class_exists( 'RankMath' ) ) {
		add_filter( 'rank_math/json_ld', '__return_empty_array', 9999 );
	}

	// ── Yoast SEO ─────────────────────────────────────────────────────────────
	if ( defined( 'WPSEO_VERSION' ) ) {
		add_filter( 'wpseo_json_ld_output', '__return_empty_array' );
	}

	// ── SEOPress ──────────────────────────────────────────────────────────────
	if ( defined( 'SEOPRESS_VERSION' ) ) {
		add_filter( 'seopress_schemas_auto_disable', '__return_true' );
	}

	// ── All in One SEO ────────────────────────────────────────────────────────
	if ( defined( 'AIOSEO_VERSION' ) ) {
		add_filter( 'aioseo_disable_schema_output', '__return_true' );
	}
} );

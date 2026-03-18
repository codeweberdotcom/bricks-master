<?php
/**
 * WooCommerce Shop Filters
 *
 * URL helpers, data-fetch helpers, WP widget classes and main render function
 * for the shop filter panel.
 *
 * URL format (standard WooCommerce):
 *   min_price / max_price              → price range (handled by WC_Query)
 *   filter_<attribute_slug>=v1,v2      → attribute (OR within, AND between — WC native)
 *   query_type_<slug>=and              → switch attribute query to AND
 *   rating_filter=4,5                  → star rating (handled by WC_Query)
 *   filter_stock_status=instock        → stock status (custom pre_get_posts hook)
 *
 * @package CodeWeber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Require WooCommerce ────────────────────────────────────────────────────────
if ( ! function_exists( 'WC' ) ) {
	return;
}

// =============================================================================
// URL HELPERS
// =============================================================================

/**
 * Toggle a single value inside a comma-separated query param.
 *
 * OR logic: ?filter_color=red → click blue → ?filter_color=red,blue
 *           ?filter_color=red,blue → click red → ?filter_color=blue
 *
 * @param string $param  Query parameter name (e.g. 'filter_color').
 * @param string $value  Term slug to add or remove.
 * @return string Absolute URL.
 */
function cw_get_filter_url( $param, $value ) {
	$current_values = cw_get_current_filter_values( $param );

	if ( in_array( $value, $current_values, true ) ) {
		$current_values = array_values( array_diff( $current_values, [ $value ] ) );
	} else {
		$current_values[] = $value;
	}

	$base = cw_filter_base_url();

	if ( empty( $current_values ) ) {
		return esc_url( remove_query_arg( $param, $base ) );
	}

	return esc_url( add_query_arg( $param, implode( ',', array_unique( $current_values ) ), $base ) );
}

/**
 * Build URL that sets min/max price.
 *
 * @param int|float $min
 * @param int|float $max
 * @return string
 */
function cw_get_price_filter_url( $min, $max ) {
	$base = cw_filter_base_url();
	return esc_url( add_query_arg( [ 'min_price' => (int) $min, 'max_price' => (int) $max ], $base ) );
}

/**
 * Remove all filter-related params and return the clean base page URL.
 *
 * @return string
 */
function cw_get_clear_filters_url() {
	$url = strtok( get_pagenum_link( 1 ), '?' );
	return esc_url( $url );
}

/**
 * Check if a specific filter value is active.
 *
 * @param string $param  Query param name.
 * @param string $value  Term slug.
 * @return bool
 */
function cw_is_filter_active( $param, $value ) {
	return in_array( $value, cw_get_current_filter_values( $param ), true );
}

/**
 * Check if any shop filter is currently active.
 *
 * @return bool
 */
function cw_has_active_filters() {
	return ! empty( cw_get_active_filter_params() );
}

/**
 * Return array of all active filter params for display as "active chips".
 *
 * @return array[] { param, value, label, remove_url }
 */
function cw_get_active_filter_params() {
	$active = [];

	if ( ! function_exists( 'WC' ) ) {
		return $active;
	}

	// WC attribute filters
	$chosen = WC_Query::get_layered_nav_chosen_attributes();
	foreach ( $chosen as $taxonomy => $data ) {
		foreach ( $data['terms'] as $term_slug ) {
			$term = get_term_by( 'slug', $term_slug, $taxonomy );
			if ( ! $term ) {
				continue;
			}
			$param = 'filter_' . wc_attribute_taxonomy_slug( $taxonomy );
			$active[] = [
				'param'      => $param,
				'value'      => $term_slug,
				'label'      => $term->name,
				'remove_url' => cw_get_filter_url( $param, $term_slug ),
			];
		}
	}

	// Price filter
	// phpcs:disable WordPress.Security.NonceVerification
	$min_price = isset( $_GET['min_price'] ) ? (int) $_GET['min_price'] : null;
	$max_price = isset( $_GET['max_price'] ) ? (int) $_GET['max_price'] : null;
	// phpcs:enable

	if ( null !== $min_price || null !== $max_price ) {
		$range = cw_get_price_filter_range();
		$show  = false;
		if ( null !== $min_price && $min_price > $range['min'] ) {
			$show = true;
		}
		if ( null !== $max_price && $max_price < $range['max'] ) {
			$show = true;
		}
		if ( $show ) {
			$label = sprintf(
				/* translators: 1: min price, 2: max price */
				__( 'Цена: %1$s — %2$s', 'codeweber' ),
				wc_price( $min_price ?? $range['min'] ),
				wc_price( $max_price ?? $range['max'] )
			);
			$active[] = [
				'param'      => 'price',
				'value'      => '',
				'label'      => $label,
				'remove_url' => esc_url( remove_query_arg( [ 'min_price', 'max_price' ], cw_filter_base_url() ) ),
			];
		}
	}

	// Rating filter
	// phpcs:disable WordPress.Security.NonceVerification
	if ( ! empty( $_GET['rating_filter'] ) ) {
		$ratings = array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_GET['rating_filter'] ) ) ) ) );
		foreach ( $ratings as $rating ) {
			$active[] = [
				'param'      => 'rating_filter',
				'value'      => (string) $rating,
				'label'      => sprintf( _n( '%d звезда', '%d звёзд', $rating, 'codeweber' ), $rating ),
				'remove_url' => cw_get_filter_url( 'rating_filter', (string) $rating ),
			];
		}
	}

	// Product tag filter
	if ( ! empty( $_GET['filter_product_tag'] ) ) {
		$tag_slugs = array_filter( array_map( 'sanitize_key', explode( ',', sanitize_text_field( wp_unslash( $_GET['filter_product_tag'] ) ) ) ) );
		foreach ( $tag_slugs as $slug ) {
			$term = get_term_by( 'slug', $slug, 'product_tag' );
			if ( ! $term ) {
				continue;
			}
			$active[] = [
				'param'      => 'filter_product_tag',
				'value'      => $slug,
				'label'      => $term->name,
				'remove_url' => cw_get_filter_url( 'filter_product_tag', $slug ),
			];
		}
	}

	// Stock filter
	if ( ! empty( $_GET['filter_stock_status'] ) ) {
		$stock_labels = [
			'instock'    => __( 'В наличии', 'codeweber' ),
			'outofstock' => __( 'Нет в наличии', 'codeweber' ),
			'onbackorder' => __( 'Под заказ', 'codeweber' ),
		];
		$val = sanitize_text_field( wp_unslash( $_GET['filter_stock_status'] ) );
		if ( isset( $stock_labels[ $val ] ) ) {
			$active[] = [
				'param'      => 'filter_stock_status',
				'value'      => $val,
				'label'      => $stock_labels[ $val ],
				'remove_url' => esc_url( remove_query_arg( 'filter_stock_status', cw_filter_base_url() ) ),
			];
		}
	}
	// phpcs:enable

	return $active;
}

// ── Private helpers ────────────────────────────────────────────────────────────

/**
 * Get array of currently selected values for a comma-separated param.
 *
 * @param string $param
 * @return string[]
 */
function cw_get_current_filter_values( $param ) {
	// phpcs:ignore WordPress.Security.NonceVerification
	$raw = isset( $_GET[ $param ] ) ? sanitize_text_field( wp_unslash( $_GET[ $param ] ) ) : '';
	if ( '' === $raw ) {
		return [];
	}
	return array_filter( explode( ',', $raw ) );
}

/**
 * Base URL for filter links — current page without paged/page params.
 *
 * @return string
 */
function cw_filter_base_url() {
	// Use $escape=false to get a raw URL (esc_url_raw, plain & separators).
	// get_pagenum_link(1) with default $escape=true returns &#038; encoded &,
	// which causes add_query_arg() to split the URL at the literal # in &#038;,
	// corrupting all query params that follow into a URL fragment.
	return remove_query_arg( [ 'paged', 'page' ], get_pagenum_link( 1, false ) );
}

// =============================================================================
// DATA FETCH HELPERS
// =============================================================================

/**
 * Get global min/max prices from wc_product_meta_lookup table.
 *
 * @return array{ min: float, max: float }
 */
function cw_get_price_filter_range() {
	global $wpdb;

	static $range = null;
	if ( null !== $range ) {
		return $range;
	}

	// Build a cache key from the active filter state.
	// phpcs:disable WordPress.Security.NonceVerification
	$cache_key = 'cw_price_range_' . md5( wp_json_encode( [
		'cat'   => is_product_category() ? get_queried_object_id() : 0,
		'attrs' => class_exists( 'WC_Query' ) ? WC_Query::get_layered_nav_chosen_attributes() : [],
		'tag'   => sanitize_text_field( wp_unslash( $_GET['filter_product_tag'] ?? '' ) ),
		'stock' => sanitize_key( wp_unslash( $_GET['filter_stock_status'] ?? '' ) ),
		'rate'  => sanitize_text_field( wp_unslash( $_GET['rating_filter'] ?? '' ) ),
	] ) );
	// phpcs:enable

	$cached = wp_cache_get( $cache_key, 'cw_filters' );
	if ( false !== $cached ) {
		$range = $cached;
		return $range;
	}

	$meta_table = $wpdb->prefix . 'wc_product_meta_lookup';

	// Start with a JOIN to wp_posts so we only count published products.
	$joins = [
		"INNER JOIN {$wpdb->posts} AS p ON p.ID = lookup.product_id AND p.post_type = 'product' AND p.post_status = 'publish'",
	];
	$where = [];

	// ── Category archive (incl. child categories) ──────────────────────────
	if ( is_product_category() ) {
		$term = get_queried_object();
		if ( $term && ! is_wp_error( $term ) ) {
			$term_ids = array_merge(
				[ (int) $term->term_id ],
				array_map( 'intval', get_term_children( $term->term_id, 'product_cat' ) )
			);
			$ids_list = implode( ',', $term_ids );
			$where[]  = "lookup.product_id IN (
				SELECT tr.object_id FROM {$wpdb->term_relationships} tr
				INNER JOIN {$wpdb->term_taxonomy} tt
					ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = 'product_cat'
				WHERE tt.term_id IN ({$ids_list})
			)";
		}
	}

	// ── WC attribute filters (pa_color, pa_size …) ─────────────────────────
	// phpcs:disable WordPress.Security.NonceVerification
	if ( class_exists( 'WC_Query' ) ) {
		foreach ( WC_Query::get_layered_nav_chosen_attributes() as $taxonomy => $data ) {
			if ( empty( $data['terms'] ) ) {
				continue;
			}
			$tax_safe    = esc_sql( $taxonomy );
			$slug_list   = implode( ',', array_map( function( $s ) use ( $wpdb ) {
				return $wpdb->prepare( '%s', $s );
			}, $data['terms'] ) );

			if ( 'and' === $data['query_type'] ) {
				$term_count = count( $data['terms'] );
				$where[]    = "lookup.product_id IN (
					SELECT tr.object_id FROM {$wpdb->term_relationships} tr
					INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = '{$tax_safe}'
					INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id AND t.slug IN ({$slug_list})
					GROUP BY tr.object_id HAVING COUNT(DISTINCT t.term_id) = {$term_count}
				)";
			} else {
				$where[] = "lookup.product_id IN (
					SELECT tr.object_id FROM {$wpdb->term_relationships} tr
					INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = '{$tax_safe}'
					INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id AND t.slug IN ({$slug_list})
				)";
			}
		}
	}

	// ── Product tag filter ─────────────────────────────────────────────────
	if ( ! empty( $_GET['filter_product_tag'] ) ) {
		$tag_slugs = array_filter(
			array_map( 'sanitize_key', explode( ',', sanitize_text_field( wp_unslash( $_GET['filter_product_tag'] ) ) ) )
		);
		if ( $tag_slugs ) {
			$slug_list = implode( ',', array_map( function( $s ) use ( $wpdb ) {
				return $wpdb->prepare( '%s', $s );
			}, $tag_slugs ) );
			$where[]   = "lookup.product_id IN (
				SELECT tr.object_id FROM {$wpdb->term_relationships} tr
				INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = 'product_tag'
				INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id AND t.slug IN ({$slug_list})
			)";
		}
	}

	// ── Stock status — lookup table has stock_status column (WC ≥ 3.6) ─────
	if ( ! empty( $_GET['filter_stock_status'] ) ) {
		$status = sanitize_key( wp_unslash( $_GET['filter_stock_status'] ) );
		if ( in_array( $status, [ 'instock', 'outofstock', 'onbackorder' ], true ) ) {
			$where[] = $wpdb->prepare( 'lookup.stock_status = %s', $status );
		}
	}

	// ── Rating — lookup table has average_rating column (WC ≥ 3.6) ─────────
	if ( ! empty( $_GET['rating_filter'] ) ) {
		$ratings = array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_GET['rating_filter'] ) ) ) ) );
		if ( $ratings ) {
			$where[] = $wpdb->prepare( 'FLOOR(lookup.average_rating) >= %d', min( $ratings ) );
		}
	}
	// phpcs:enable

	$joins_sql = implode( ' ', $joins );
	$where_sql = $where ? 'WHERE ' . implode( ' AND ', $where ) : '';

	// Two aggregates, zero PHP arrays — all done in the DB.
	// phpcs:disable WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$min = (float) $wpdb->get_var( "SELECT MIN(lookup.min_price) FROM `{$meta_table}` AS lookup {$joins_sql} {$where_sql}" );
	$max = (float) $wpdb->get_var( "SELECT MAX(lookup.max_price) FROM `{$meta_table}` AS lookup {$joins_sql} {$where_sql}" );
	// phpcs:enable

	if ( 0 === (int) $min && 0 === (int) $max ) {
		$min = (float) get_option( 'woocommerce_price_filter_range_min', 0 );
		$max = (float) get_option( 'woocommerce_price_filter_range_max', 10000 );
	}

	$range = [
		'min' => floor( $min ),
		'max' => ceil( $max ),
	];

	wp_cache_set( $cache_key, $range, 'cw_filters', 5 * MINUTE_IN_SECONDS );

	return $range;
}

/**
 * Invalidate price range cache when a product is saved.
 */
add_action( 'save_post_product', function() {
	wp_cache_flush_group( 'cw_filters' );
} );

/**
 * Get filtered product count per term for a given taxonomy.
 *
 * Uses the same filter conditions as cw_get_price_filter_range() but returns
 * a map of term_id → product count instead of MIN/MAX price.
 *
 * @param string $taxonomy         Full taxonomy slug (e.g. 'pa_color', 'product_cat').
 * @param string $exclude_taxonomy Taxonomy to skip in WHERE (use the attribute itself for OR-mode).
 * @return array<int, int> Map of term_id => product count.
 */
function cw_get_filtered_term_counts( $taxonomy, $exclude_taxonomy = '' ) {
	global $wpdb;

	// phpcs:disable WordPress.Security.NonceVerification
	$cache_key = 'cw_term_counts_' . md5( wp_json_encode( [
		'tax'   => $taxonomy,
		'excl'  => $exclude_taxonomy,
		'cat'   => is_product_category() ? get_queried_object_id() : 0,
		'attrs' => class_exists( 'WC_Query' ) ? WC_Query::get_layered_nav_chosen_attributes() : [],
		'tag'   => sanitize_text_field( wp_unslash( $_GET['filter_product_tag'] ?? '' ) ),
		'stock' => sanitize_key( wp_unslash( $_GET['filter_stock_status'] ?? '' ) ),
		'rate'  => sanitize_text_field( wp_unslash( $_GET['rating_filter'] ?? '' ) ),
	] ) );
	// phpcs:enable

	$cached = wp_cache_get( $cache_key, 'cw_filters' );
	if ( false !== $cached ) {
		return $cached;
	}

	$meta_table = $wpdb->prefix . 'wc_product_meta_lookup';

	$joins = [
		"INNER JOIN {$wpdb->posts} AS p ON p.ID = lookup.product_id AND p.post_type = 'product' AND p.post_status = 'publish'",
	];
	$where = [];

	// ── Category archive (incl. child categories) ──────────────────────────
	if ( is_product_category() ) {
		$cat_term = get_queried_object();
		if ( $cat_term && ! is_wp_error( $cat_term ) ) {
			$term_ids = array_merge(
				[ (int) $cat_term->term_id ],
				array_map( 'intval', get_term_children( $cat_term->term_id, 'product_cat' ) )
			);
			$ids_list = implode( ',', $term_ids );
			$where[]  = "lookup.product_id IN (
				SELECT tr.object_id FROM {$wpdb->term_relationships} tr
				INNER JOIN {$wpdb->term_taxonomy} tt
					ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = 'product_cat'
				WHERE tt.term_id IN ({$ids_list})
			)";
		}
	}

	// ── WC attribute filters (skip $exclude_taxonomy) ──────────────────────
	// phpcs:disable WordPress.Security.NonceVerification
	if ( class_exists( 'WC_Query' ) ) {
		foreach ( WC_Query::get_layered_nav_chosen_attributes() as $attr_tax => $data ) {
			if ( $attr_tax === $exclude_taxonomy || empty( $data['terms'] ) ) {
				continue;
			}
			$tax_safe  = esc_sql( $attr_tax );
			$slug_list = implode( ',', array_map( function( $s ) use ( $wpdb ) {
				return $wpdb->prepare( '%s', $s );
			}, $data['terms'] ) );

			if ( 'and' === $data['query_type'] ) {
				$term_count = count( $data['terms'] );
				$where[]    = "lookup.product_id IN (
					SELECT tr.object_id FROM {$wpdb->term_relationships} tr
					INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = '{$tax_safe}'
					INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id AND t.slug IN ({$slug_list})
					GROUP BY tr.object_id HAVING COUNT(DISTINCT t.term_id) = {$term_count}
				)";
			} else {
				$where[] = "lookup.product_id IN (
					SELECT tr.object_id FROM {$wpdb->term_relationships} tr
					INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = '{$tax_safe}'
					INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id AND t.slug IN ({$slug_list})
				)";
			}
		}
	}

	// ── Product tag filter ─────────────────────────────────────────────────
	if ( 'product_tag' !== $exclude_taxonomy && ! empty( $_GET['filter_product_tag'] ) ) {
		$tag_slugs = array_filter(
			array_map( 'sanitize_key', explode( ',', sanitize_text_field( wp_unslash( $_GET['filter_product_tag'] ) ) ) )
		);
		if ( $tag_slugs ) {
			$slug_list = implode( ',', array_map( function( $s ) use ( $wpdb ) {
				return $wpdb->prepare( '%s', $s );
			}, $tag_slugs ) );
			$where[] = "lookup.product_id IN (
				SELECT tr.object_id FROM {$wpdb->term_relationships} tr
				INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = 'product_tag'
				INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id AND t.slug IN ({$slug_list})
			)";
		}
	}

	// ── Stock status ────────────────────────────────────────────────────────
	if ( ! empty( $_GET['filter_stock_status'] ) ) {
		$status = sanitize_key( wp_unslash( $_GET['filter_stock_status'] ) );
		if ( in_array( $status, [ 'instock', 'outofstock', 'onbackorder' ], true ) ) {
			$where[] = $wpdb->prepare( 'lookup.stock_status = %s', $status );
		}
	}

	// ── Rating ──────────────────────────────────────────────────────────────
	if ( ! empty( $_GET['rating_filter'] ) ) {
		$ratings = array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_GET['rating_filter'] ) ) ) ) );
		if ( $ratings ) {
			$where[] = $wpdb->prepare( 'FLOOR(lookup.average_rating) >= %d', min( $ratings ) );
		}
	}
	// phpcs:enable

	$joins_sql = implode( ' ', $joins );
	$where_sql = $where ? 'WHERE ' . implode( ' AND ', $where ) : '';
	$tax_safe  = esc_sql( $taxonomy );

	// phpcs:disable WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$rows = $wpdb->get_results(
		"SELECT tt_main.term_id, COUNT(DISTINCT lookup.product_id) AS cnt
		FROM `{$meta_table}` AS lookup
		{$joins_sql}
		INNER JOIN {$wpdb->term_relationships} tr_main ON tr_main.object_id = lookup.product_id
		INNER JOIN {$wpdb->term_taxonomy} tt_main
			ON tt_main.term_taxonomy_id = tr_main.term_taxonomy_id AND tt_main.taxonomy = '{$tax_safe}'
		{$where_sql}
		GROUP BY tt_main.term_id"
	);
	// phpcs:enable

	$counts = [];
	foreach ( $rows as $row ) {
		$counts[ (int) $row->term_id ] = (int) $row->cnt;
	}

	wp_cache_set( $cache_key, $counts, 'cw_filters', 5 * MINUTE_IN_SECONDS );

	return $counts;
}

/**
 * Get terms for a WooCommerce product attribute taxonomy with filtered counts.
 *
 * @param string $taxonomy   Full taxonomy slug (e.g. 'pa_color').
 * @param bool   $show_count Whether to include product count.
 * @return array{ term: WP_Term, count: int, is_active: bool, is_empty: bool, url: string }[]
 */
function cw_get_attribute_filter_terms( $taxonomy, $show_count = true ) {
	$param = 'filter_' . wc_attribute_taxonomy_slug( $taxonomy );

	$terms = get_terms( [
		'taxonomy'   => $taxonomy,
		'hide_empty' => true,
		'orderby'    => 'name',
	] );

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return [];
	}

	// OR-mode: exclude this taxonomy's own filter when computing counts (show all options).
	// AND-mode: keep this taxonomy's filter (show only compatible options).
	$chosen_attrs = class_exists( 'WC_Query' ) ? WC_Query::get_layered_nav_chosen_attributes() : [];
	$query_type   = $chosen_attrs[ $taxonomy ]['query_type'] ?? 'or';
	$exclude      = ( 'or' === $query_type ) ? $taxonomy : '';
	$counts       = cw_get_filtered_term_counts( $taxonomy, $exclude );

	$result = [];
	foreach ( $terms as $term ) {
		$filtered_count = $counts[ (int) $term->term_id ] ?? 0;
		$result[] = [
			'term'      => $term,
			'count'     => $filtered_count,
			'is_active' => cw_is_filter_active( $param, $term->slug ),
			'is_empty'  => ( 0 === $filtered_count ),
			'url'       => cw_get_filter_url( $param, $term->slug ),
		];
	}

	return $result;
}

/**
 * Get product categories for the filter with filtered counts.
 *
 * @param int  $parent     Parent category ID (0 = top-level).
 * @param bool $show_count Whether to include product count.
 * @return array{ term: WP_Term, count: int, is_active: bool, is_empty: bool, children: array }[]
 */
function cw_get_category_filter_terms( $parent = 0, $show_count = true ) {
	$queried = is_product_category() ? get_queried_object() : null;

	$terms = get_terms( [
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'parent'     => $parent,
		'orderby'    => 'name',
	] );

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return [];
	}

	$counts = cw_get_filtered_term_counts( 'product_cat', '' );

	$result = [];
	foreach ( $terms as $term ) {
		$filtered_count = $counts[ (int) $term->term_id ] ?? 0;
		$result[] = [
			'term'      => $term,
			'count'     => $filtered_count,
			'is_active' => ( $queried && (int) $queried->term_id === (int) $term->term_id ),
			'is_empty'  => ( 0 === $filtered_count ),
			'url'       => get_term_link( $term ),
			'children'  => [],
		];
	}

	return $result;
}

/**
 * Get filtered product count per stock status.
 *
 * Applies all active filters EXCEPT the stock filter itself (OR-mode).
 * Returns map: stock_status => product count.
 *
 * @return array<string, int>
 */
function cw_get_filtered_stock_counts() {
	global $wpdb;

	// phpcs:disable WordPress.Security.NonceVerification
	$cache_key = 'cw_stock_counts_' . md5( wp_json_encode( [
		'cat'   => is_product_category() ? get_queried_object_id() : 0,
		'attrs' => class_exists( 'WC_Query' ) ? WC_Query::get_layered_nav_chosen_attributes() : [],
		'tag'   => sanitize_text_field( wp_unslash( $_GET['filter_product_tag'] ?? '' ) ),
		'rate'  => sanitize_text_field( wp_unslash( $_GET['rating_filter'] ?? '' ) ),
	] ) );
	// phpcs:enable

	$cached = wp_cache_get( $cache_key, 'cw_filters' );
	if ( false !== $cached ) {
		return $cached;
	}

	$meta_table = $wpdb->prefix . 'wc_product_meta_lookup';
	$joins      = [
		"INNER JOIN {$wpdb->posts} AS p ON p.ID = lookup.product_id AND p.post_type = 'product' AND p.post_status = 'publish'",
	];
	$where = [];

	// ── Category archive ────────────────────────────────────────────────────
	if ( is_product_category() ) {
		$cat_term = get_queried_object();
		if ( $cat_term && ! is_wp_error( $cat_term ) ) {
			$term_ids = array_merge(
				[ (int) $cat_term->term_id ],
				array_map( 'intval', get_term_children( $cat_term->term_id, 'product_cat' ) )
			);
			$ids_list = implode( ',', $term_ids );
			$where[]  = "lookup.product_id IN (
				SELECT tr.object_id FROM {$wpdb->term_relationships} tr
				INNER JOIN {$wpdb->term_taxonomy} tt
					ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = 'product_cat'
				WHERE tt.term_id IN ({$ids_list})
			)";
		}
	}

	// ── Attribute filters ───────────────────────────────────────────────────
	// phpcs:disable WordPress.Security.NonceVerification
	if ( class_exists( 'WC_Query' ) ) {
		foreach ( WC_Query::get_layered_nav_chosen_attributes() as $attr_tax => $data ) {
			if ( empty( $data['terms'] ) ) {
				continue;
			}
			$tax_safe  = esc_sql( $attr_tax );
			$slug_list = implode( ',', array_map( function( $s ) use ( $wpdb ) {
				return $wpdb->prepare( '%s', $s );
			}, $data['terms'] ) );
			if ( 'and' === $data['query_type'] ) {
				$n       = count( $data['terms'] );
				$where[] = "lookup.product_id IN (
					SELECT tr.object_id FROM {$wpdb->term_relationships} tr
					INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = '{$tax_safe}'
					INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id AND t.slug IN ({$slug_list})
					GROUP BY tr.object_id HAVING COUNT(DISTINCT t.term_id) = {$n}
				)";
			} else {
				$where[] = "lookup.product_id IN (
					SELECT tr.object_id FROM {$wpdb->term_relationships} tr
					INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = '{$tax_safe}'
					INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id AND t.slug IN ({$slug_list})
				)";
			}
		}
	}

	// ── Product tag filter ─────────────────────────────────────────────────
	if ( ! empty( $_GET['filter_product_tag'] ) ) {
		$tag_slugs = array_filter(
			array_map( 'sanitize_key', explode( ',', sanitize_text_field( wp_unslash( $_GET['filter_product_tag'] ) ) ) )
		);
		if ( $tag_slugs ) {
			$slug_list = implode( ',', array_map( function( $s ) use ( $wpdb ) {
				return $wpdb->prepare( '%s', $s );
			}, $tag_slugs ) );
			$where[] = "lookup.product_id IN (
				SELECT tr.object_id FROM {$wpdb->term_relationships} tr
				INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = 'product_tag'
				INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id AND t.slug IN ({$slug_list})
			)";
		}
	}

	// ── Rating (applied — we are excluding STOCK, not rating) ──────────────
	if ( ! empty( $_GET['rating_filter'] ) ) {
		$ratings = array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_GET['rating_filter'] ) ) ) ) );
		if ( $ratings ) {
			$where[] = $wpdb->prepare( 'FLOOR(lookup.average_rating) >= %d', min( $ratings ) );
		}
	}
	// phpcs:enable

	$joins_sql = implode( ' ', $joins );
	$where_sql = $where ? 'WHERE ' . implode( ' AND ', $where ) : '';

	// phpcs:disable WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$rows = $wpdb->get_results(
		"SELECT lookup.stock_status, COUNT(DISTINCT lookup.product_id) AS cnt
		FROM `{$meta_table}` AS lookup {$joins_sql} {$where_sql}
		GROUP BY lookup.stock_status"
	);
	// phpcs:enable

	$counts = [];
	foreach ( $rows as $row ) {
		$counts[ $row->stock_status ] = (int) $row->cnt;
	}

	wp_cache_set( $cache_key, $counts, 'cw_filters', 5 * MINUTE_IN_SECONDS );

	return $counts;
}

/**
 * Get filtered product count for each "N stars and above" rating option.
 *
 * Applies all active filters EXCEPT the rating filter itself (OR-mode).
 * Returns map: N (1–5) => count of products with average_rating >= N.
 *
 * @return array<int, int>
 */
function cw_get_filtered_rating_counts() {
	global $wpdb;

	// phpcs:disable WordPress.Security.NonceVerification
	$cache_key = 'cw_rating_counts_' . md5( wp_json_encode( [
		'cat'   => is_product_category() ? get_queried_object_id() : 0,
		'attrs' => class_exists( 'WC_Query' ) ? WC_Query::get_layered_nav_chosen_attributes() : [],
		'tag'   => sanitize_text_field( wp_unslash( $_GET['filter_product_tag'] ?? '' ) ),
		'stock' => sanitize_key( wp_unslash( $_GET['filter_stock_status'] ?? '' ) ),
	] ) );
	// phpcs:enable

	$cached = wp_cache_get( $cache_key, 'cw_filters' );
	if ( false !== $cached ) {
		return $cached;
	}

	$meta_table = $wpdb->prefix . 'wc_product_meta_lookup';
	$joins      = [
		"INNER JOIN {$wpdb->posts} AS p ON p.ID = lookup.product_id AND p.post_type = 'product' AND p.post_status = 'publish'",
	];
	$where = [];

	// ── Category archive ────────────────────────────────────────────────────
	if ( is_product_category() ) {
		$cat_term = get_queried_object();
		if ( $cat_term && ! is_wp_error( $cat_term ) ) {
			$term_ids = array_merge(
				[ (int) $cat_term->term_id ],
				array_map( 'intval', get_term_children( $cat_term->term_id, 'product_cat' ) )
			);
			$ids_list = implode( ',', $term_ids );
			$where[]  = "lookup.product_id IN (
				SELECT tr.object_id FROM {$wpdb->term_relationships} tr
				INNER JOIN {$wpdb->term_taxonomy} tt
					ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = 'product_cat'
				WHERE tt.term_id IN ({$ids_list})
			)";
		}
	}

	// ── Attribute filters ───────────────────────────────────────────────────
	// phpcs:disable WordPress.Security.NonceVerification
	if ( class_exists( 'WC_Query' ) ) {
		foreach ( WC_Query::get_layered_nav_chosen_attributes() as $attr_tax => $data ) {
			if ( empty( $data['terms'] ) ) {
				continue;
			}
			$tax_safe  = esc_sql( $attr_tax );
			$slug_list = implode( ',', array_map( function( $s ) use ( $wpdb ) {
				return $wpdb->prepare( '%s', $s );
			}, $data['terms'] ) );
			if ( 'and' === $data['query_type'] ) {
				$n       = count( $data['terms'] );
				$where[] = "lookup.product_id IN (
					SELECT tr.object_id FROM {$wpdb->term_relationships} tr
					INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = '{$tax_safe}'
					INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id AND t.slug IN ({$slug_list})
					GROUP BY tr.object_id HAVING COUNT(DISTINCT t.term_id) = {$n}
				)";
			} else {
				$where[] = "lookup.product_id IN (
					SELECT tr.object_id FROM {$wpdb->term_relationships} tr
					INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = '{$tax_safe}'
					INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id AND t.slug IN ({$slug_list})
				)";
			}
		}
	}

	// ── Product tag filter ─────────────────────────────────────────────────
	if ( ! empty( $_GET['filter_product_tag'] ) ) {
		$tag_slugs = array_filter(
			array_map( 'sanitize_key', explode( ',', sanitize_text_field( wp_unslash( $_GET['filter_product_tag'] ) ) ) )
		);
		if ( $tag_slugs ) {
			$slug_list = implode( ',', array_map( function( $s ) use ( $wpdb ) {
				return $wpdb->prepare( '%s', $s );
			}, $tag_slugs ) );
			$where[] = "lookup.product_id IN (
				SELECT tr.object_id FROM {$wpdb->term_relationships} tr
				INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = 'product_tag'
				INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id AND t.slug IN ({$slug_list})
			)";
		}
	}

	// ── Stock status (applied — we are excluding RATING, not stock) ─────────
	if ( ! empty( $_GET['filter_stock_status'] ) ) {
		$status = sanitize_key( wp_unslash( $_GET['filter_stock_status'] ) );
		if ( in_array( $status, [ 'instock', 'outofstock', 'onbackorder' ], true ) ) {
			$where[] = $wpdb->prepare( 'lookup.stock_status = %s', $status );
		}
	}
	// phpcs:enable

	$joins_sql = implode( ' ', $joins );
	$where_sql = $where ? 'WHERE ' . implode( ' AND ', $where ) : '';

	// Count products per exact integer rating; compute cumulative below.
	// phpcs:disable WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$rows = $wpdb->get_results(
		"SELECT FLOOR(lookup.average_rating) AS rating, COUNT(DISTINCT lookup.product_id) AS cnt
		FROM `{$meta_table}` AS lookup {$joins_sql} {$where_sql}
		GROUP BY FLOOR(lookup.average_rating)"
	);
	// phpcs:enable

	// Build per-exact-rating map.
	$per_rating = [ 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0 ];
	foreach ( $rows as $row ) {
		$r = (int) $row->rating;
		if ( $r >= 1 && $r <= 5 ) {
			$per_rating[ $r ] = (int) $row->cnt;
		}
	}

	// Convert to "N stars and above" cumulative counts.
	$counts = [];
	for ( $n = 1; $n <= 5; $n++ ) {
		$counts[ $n ] = 0;
		for ( $r = $n; $r <= 5; $r++ ) {
			$counts[ $n ] += $per_rating[ $r ];
		}
	}

	wp_cache_set( $cache_key, $counts, 'cw_filters', 5 * MINUTE_IN_SECONDS );

	return $counts;
}

/**
 * Get rating options (1–5 stars) with is_empty flag for disabled state.
 *
 * @return array{ value: string, label: int, is_active: bool, is_empty: bool, url: string }[]
 */
function cw_get_rating_filter_options() {
	$counts  = cw_get_filtered_rating_counts();
	$options = [];
	for ( $i = 5; $i >= 1; $i-- ) {
		$is_active = cw_is_filter_active( 'rating_filter', (string) $i );
		$options[] = [
			'value'     => (string) $i,
			'label'     => $i,
			'is_active' => $is_active,
			'is_empty'  => ( ! $is_active && 0 === ( $counts[ $i ] ?? 0 ) ),
			'url'       => cw_get_filter_url( 'rating_filter', (string) $i ),
		];
	}
	return $options;
}

/**
 * Get stock status filter options with is_empty flag for disabled state.
 *
 * @return array{ value: string, label: string, is_active: bool, is_empty: bool, url: string }[]
 */
function cw_get_stock_filter_options() {
	$param  = 'filter_stock_status';
	$counts = cw_get_filtered_stock_counts();

	$statuses = [
		'instock'     => __( 'В наличии', 'codeweber' ),
		'outofstock'  => __( 'Нет в наличии', 'codeweber' ),
		'onbackorder' => __( 'Под заказ', 'codeweber' ),
	];

	$options = [];
	foreach ( $statuses as $value => $label ) {
		$is_active = cw_is_filter_active( $param, $value );
		$options[] = [
			'value'     => $value,
			'label'     => $label,
			'is_active' => $is_active,
			'is_empty'  => ( ! $is_active && 0 === ( $counts[ $value ] ?? 0 ) ),
			'url'       => cw_get_filter_url( $param, $value ),
		];
	}
	return $options;
}

/**
 * Get product tags for the filter with filtered counts.
 *
 * @param bool $show_count Whether to include product count.
 * @return array{ term: WP_Term, count: int, is_active: bool, is_empty: bool, url: string }[]
 */
function cw_get_tag_filter_terms( $show_count = true ) {
	$param = 'filter_product_tag';

	$terms = get_terms( [
		'taxonomy'   => 'product_tag',
		'hide_empty' => true,
		'orderby'    => 'name',
	] );

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return [];
	}

	// OR-mode tags: exclude the tag filter itself when computing per-tag counts.
	$counts = cw_get_filtered_term_counts( 'product_tag', 'product_tag' );

	$result = [];
	foreach ( $terms as $term ) {
		$filtered_count = $counts[ (int) $term->term_id ] ?? 0;
		$result[] = [
			'term'      => $term,
			'count'     => $filtered_count,
			'is_active' => cw_is_filter_active( $param, $term->slug ),
			'is_empty'  => ( 0 === $filtered_count ),
			'url'       => cw_get_filter_url( $param, $term->slug ),
		];
	}

	return $result;
}

// =============================================================================
// STOCK STATUS QUERY HOOK
// =============================================================================

/**
 * Apply stock status filter to the main WC product query.
 * Triggered by ?filter_stock_status=instock|outofstock|onbackorder.
 */
add_action( 'woocommerce_product_query', function ( WP_Query $q ) {
	// phpcs:ignore WordPress.Security.NonceVerification
	if ( empty( $_GET['filter_stock_status'] ) ) {
		return;
	}

	$allowed = [ 'instock', 'outofstock', 'onbackorder' ];
	// phpcs:ignore WordPress.Security.NonceVerification
	$value = sanitize_key( wp_unslash( $_GET['filter_stock_status'] ) );

	if ( ! in_array( $value, $allowed, true ) ) {
		return;
	}

	$meta_query   = (array) $q->get( 'meta_query' );
	$meta_query[] = [
		'key'   => '_stock_status',
		'value' => $value,
	];
	$q->set( 'meta_query', $meta_query );
}, 10 );

/**
 * Apply product tag filter to the main WC product query.
 * Triggered by ?filter_product_tag=slug1,slug2.
 */
add_action( 'woocommerce_product_query', function ( WP_Query $q ) {
	// phpcs:ignore WordPress.Security.NonceVerification
	if ( empty( $_GET['filter_product_tag'] ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification
	$tag_slugs = array_filter(
		array_map( 'sanitize_key', explode( ',', sanitize_text_field( wp_unslash( $_GET['filter_product_tag'] ) ) ) )
	);

	if ( empty( $tag_slugs ) ) {
		return;
	}

	$tax_query   = (array) $q->get( 'tax_query' );
	$tax_query[] = [
		'taxonomy' => 'product_tag',
		'field'    => 'slug',
		'terms'    => $tag_slugs,
		'operator' => 'IN',
	];
	$q->set( 'tax_query', $tax_query );
}, 10 );

// =============================================================================
// MAIN RENDER FUNCTION
// =============================================================================

/**
 * Render filter panel items from the Gutenberg block's repeater structure.
 *
 * Each item in $items can be:
 *   type=filter         → accordion section with a specific filter
 *   type=reset_button   → "Сбросить все фильтры" link (shown only when filters active)
 *   type=active_chips   → active filter chips list
 *
 * @param array $items Array of item objects from block.json 'items' attribute.
 */
function cw_render_filter_items( $items, $panel_atts = [] ) {
	if ( ! function_exists( 'WC' ) || empty( $items ) || ! is_array( $items ) ) {
		return;
	}

	$section_style       = in_array( $panel_atts['section_style'] ?? 'plain', [ 'plain', 'accordion' ], true )
		? $panel_atts['section_style'] : 'plain';
	$sections_open       = isset( $panel_atts['sections_open'] ) ? (bool) $panel_atts['sections_open'] : true;
	$wrapper_class       = isset( $panel_atts['wrapper_class'] ) ? esc_attr( $panel_atts['wrapper_class'] ) : 'widget';
	$heading_tag         = in_array( $panel_atts['heading_tag'] ?? 'h4', [ 'h2', 'h3', 'h4', 'h5', 'h6', 'p' ], true )
		? $panel_atts['heading_tag'] : 'h4';
	$heading_class       = isset( $panel_atts['heading_class'] ) ? esc_attr( $panel_atts['heading_class'] ) : 'widget-title mb-3';
	$checkbox_size       = in_array( $panel_atts['checkbox_size'] ?? '', [ '', 'sm' ], true )
		? $panel_atts['checkbox_size'] : '';
	$checkbox_item_class = isset( $panel_atts['checkbox_item_class'] ) ? esc_attr( $panel_atts['checkbox_item_class'] ) : '';
	$radio_size          = in_array( $panel_atts['radio_size'] ?? '', [ '', 'sm' ], true )
		? $panel_atts['radio_size'] : '';
	$radio_item_class    = isset( $panel_atts['radio_item_class'] ) ? esc_attr( $panel_atts['radio_item_class'] ) : '';
	// Button class generation from size + style + color (new API)
	if ( isset( $panel_atts['button_size'] ) || isset( $panel_atts['button_style'] ) || isset( $panel_atts['button_color'] ) ) {
		$btn_size  = $panel_atts['button_size'] ?? 'btn-sm';
		$btn_style = $panel_atts['button_style'] ?? 'outline';
		$btn_color = $panel_atts['button_color'] ?? 'secondary';
		$btn_size_prefix = $btn_size ? $btn_size . ' ' : '';
		if ( 'outline' === $btn_style ) {
			$button_class        = $btn_size_prefix . 'btn-outline-' . $btn_color;
			$button_active_class = $btn_size_prefix . 'btn-' . $btn_color;
		} elseif ( 'soft' === $btn_style ) {
			$button_class        = $btn_size_prefix . 'btn-soft-' . $btn_color;
			$button_active_class = $btn_size_prefix . 'btn-' . $btn_color;
		} else { // solid
			$button_class        = $btn_size_prefix . 'btn-' . $btn_color;
			$button_active_class = $btn_size_prefix . 'btn-soft-' . $btn_color;
		}
		// Append button shape class
		$btn_shape = isset( $panel_atts['button_shape'] ) ? ' ' . $panel_atts['button_shape'] : '';
		$button_class        .= $btn_shape;
		$button_active_class .= $btn_shape;
	} else {
		$button_class        = isset( $panel_atts['button_class'] ) ? esc_attr( $panel_atts['button_class'] ) : 'btn-outline-secondary';
		$button_active_class = isset( $panel_atts['button_active_class'] ) ? esc_attr( $panel_atts['button_active_class'] ) : 'btn-secondary';
	}
	// Append extra class (e.g. 'has-ripple my-custom-class')
	$button_extra_class = isset( $panel_atts['button_extra_class'] ) ? trim( esc_attr( $panel_atts['button_extra_class'] ) ) : '';
	if ( $button_extra_class ) {
		$button_class        .= ' ' . $button_extra_class;
		$button_active_class .= ' ' . $button_extra_class;
	}
	$reset_label         = isset( $panel_atts['reset_label'] ) ? sanitize_text_field( $panel_atts['reset_label'] ) : '';

	$checkbox_size_class = 'sm' === $checkbox_size ? ' form-check-sm' : '';
	$radio_size_class    = 'sm' === $radio_size ? ' form-check-sm' : '';

	// Price slider thumb size: lg=18px, md=14px, sm=10px
	$slider_size_map = [ 'lg' => 18, 'md' => 14, 'sm' => 10 ];
	$slider_size_raw = $panel_atts['slider_size'] ?? 'lg';
	$slider_size_px  = $slider_size_map[ array_key_exists( $slider_size_raw, $slider_size_map ) ? $slider_size_raw : 'lg' ];

	$filters_dir = get_template_directory() . '/templates/woocommerce/filters/';

	foreach ( $items as $item ) {
		if ( isset( $item['enabled'] ) && false === (bool) $item['enabled'] ) {
			continue;
		}

		$item_type = $item['type'] ?? 'filter';

		// ── Reset button ──────────────────────────────────────────────────────
		if ( 'reset_button' === $item_type ) {
			if ( function_exists( 'cw_has_active_filters' ) && cw_has_active_filters() ) {
				$reset_text = $reset_label ?: __( 'Сбросить все фильтры', 'codeweber' );
				echo '<div class="' . esc_attr( $wrapper_class . ( $item_class ? ' ' . $item_class : '' ) ) . '">';
				echo '<a href="' . esc_url( cw_get_clear_filters_url() ) . '" class="btn btn-sm btn-outline-secondary w-100 pjax-link">';
				echo esc_html( $reset_text );
				echo '</a>';
				echo '</div>';
			}
			continue;
		}

		// ── Active chips ──────────────────────────────────────────────────────
		if ( 'active_chips' === $item_type ) {
			$active = cw_get_active_filter_params();
			if ( ! empty( $active ) ) {
				include $filters_dir . 'filter-active.php';
			}
			continue;
		}

		// ── Filter section ────────────────────────────────────────────────────
		if ( 'filter' !== $item_type ) {
			continue;
		}

		$filter_type  = $item['filterType'] ?? 'price';
		$label        = isset( $item['label'] ) ? sanitize_text_field( $item['label'] ) : '';
		$display_mode = in_array( $item['displayMode'] ?? '', [ 'checkbox', 'radio', 'list', 'button', 'color', 'image' ], true )
			? $item['displayMode'] : 'checkbox';
		$show_count       = isset( $item['showCount'] ) ? (bool) $item['showCount'] : true;
		$taxonomy         = isset( $item['taxonomy'] ) ? sanitize_key( $item['taxonomy'] ) : '';
		$checkbox_columns = isset( $item['checkboxColumns'] ) ? (int) $item['checkboxColumns'] : 1;
		$swatch_columns   = isset( $item['swatchColumns'] ) ? max( 0, (int) $item['swatchColumns'] ) : 0;
		$swatch_item_class = isset( $item['swatchItemClass'] ) ? esc_attr( $item['swatchItemClass'] ) : '';
		$empty_behavior_raw = $item['emptyBehavior'] ?? 'disable';
		$empty_behavior     = in_array( $empty_behavior_raw, [ 'default', 'hide', 'disable', 'disable_clickable', 'hide_block' ], true ) ? $empty_behavior_raw : 'disable';
		$section_id       = 'cw-filter-' . sanitize_html_class( $item['id'] ?? uniqid() );
		$item_class        = isset( $item['itemClass'] ) ? sanitize_text_field( $item['itemClass'] ) : '';
		$limit_type        = in_array( $item['limitType'] ?? 'none', [ 'none', 'count', 'height' ], true ) ? ( $item['limitType'] ?? 'none' ) : 'none';
		$limit_value       = isset( $item['limitValue'] ) ? max( 1, (int) $item['limitValue'] ) : 5;
		$show_more_text    = isset( $item['showMoreText'] ) && '' !== trim( $item['showMoreText'] ) ? sanitize_text_field( $item['showMoreText'] ) : __( 'Показать ещё', 'codeweber' );
		$show_less_text    = isset( $item['showLessText'] ) && '' !== trim( $item['showLessText'] ) ? sanitize_text_field( $item['showLessText'] ) : __( 'Свернуть', 'codeweber' );

		$section_label   = $label;
		$section_content = '';
		$has_content     = true;

		ob_start();

		switch ( $filter_type ) {

			case 'price':
				if ( ! $section_label ) {
					$section_label = __( 'Цена', 'codeweber' );
				}
				include $filters_dir . 'filter-price.php';
				break;

			case 'categories':
				if ( ! $section_label ) {
					$section_label = __( 'Категории', 'codeweber' );
				}
				$terms_data = cw_get_category_filter_terms( 0, $show_count );
				$radio_name = 'cw_filter_radio_cat';
				if ( empty( $terms_data ) ) {
					$has_content = false;
				} else {
					if ( 'hide_block' === $empty_behavior ) {
						$all_empty = ! array_filter( $terms_data, fn( $t ) => ! ( $t['is_empty'] ?? false ) );
						if ( $all_empty ) { $has_content = false; break; }
						$empty_behavior = 'disable';
					}
					include $filters_dir . 'filter-category.php';
				}
				break;

			case 'tags':
				if ( ! $section_label ) {
					$section_label = __( 'Метки', 'codeweber' );
				}
				$terms_data = cw_get_tag_filter_terms( $show_count );
				$radio_name = 'cw_filter_radio_tags';
				if ( empty( $terms_data ) ) {
					$has_content = false;
				} else {
					if ( 'hide_block' === $empty_behavior ) {
						$all_empty = ! array_filter( $terms_data, fn( $t ) => ! ( $t['is_empty'] ?? false ) );
						if ( $all_empty ) { $has_content = false; break; }
						$empty_behavior = 'disable';
					}
					include $filters_dir . 'filter-attribute.php';
				}
				break;

			case 'rating':
				if ( ! $section_label ) {
					$section_label = __( 'Рейтинг', 'codeweber' );
				}
				$options = cw_get_rating_filter_options();
				if ( 'hide_block' === $empty_behavior ) {
					$all_empty = ! array_filter( $options, fn( $o ) => ! ( $o['is_empty'] ?? false ) );
					if ( $all_empty ) { $has_content = false; break; }
					$empty_behavior = 'disable';
				}
				include $filters_dir . 'filter-rating.php';
				break;

			case 'stock':
				if ( ! $section_label ) {
					$section_label = __( 'Наличие', 'codeweber' );
				}
				$options = cw_get_stock_filter_options();
				if ( 'hide_block' === $empty_behavior ) {
					$all_empty = ! array_filter( $options, fn( $o ) => ! ( $o['is_empty'] ?? false ) );
					if ( $all_empty ) { $has_content = false; break; }
					$empty_behavior = 'disable';
				}
				include $filters_dir . 'filter-stock.php';
				break;

			case 'attributes':
				if ( ! $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
					$has_content = false;
					break;
				}
				if ( ! $section_label ) {
					$attr_id       = wc_attribute_taxonomy_id_by_name( $taxonomy );
					$attr_obj      = $attr_id ? wc_get_attribute( $attr_id ) : null;
					$section_label = $attr_obj ? $attr_obj->name : $taxonomy;
				}
				$terms_data = cw_get_attribute_filter_terms( $taxonomy, $show_count );
				$radio_name = 'cw_filter_radio_' . $taxonomy;
				if ( empty( $terms_data ) ) {
					$has_content = false;
				} else {
					if ( 'hide_block' === $empty_behavior ) {
						$all_empty = ! array_filter( $terms_data, fn( $t ) => ! ( $t['is_empty'] ?? false ) );
						if ( $all_empty ) { $has_content = false; break; }
						$empty_behavior = 'disable';
					}
					include $filters_dir . 'filter-attribute.php';
				}
				break;

			default:
				$has_content = false;
				break;
		}

		$section_content = ob_get_clean();

		if ( ! $has_content || '' === trim( $section_content ) ) {
			continue;
		}

		if ( 'none' !== $limit_type ) {
			$limit_div_id = 'cw-fl-' . $section_id;
			$pre_style    = '';
			$style_attr   = '';

			if ( 'height' === $limit_type ) {
				// Inline max-height: скрывает сразу при загрузке, без FOUC
				$style_attr = ' style="max-height:' . (int) $limit_value . 'px;overflow:hidden"';
			} elseif ( 'count' === $limit_type ) {
				// CSS :nth-child скрывает лишние элементы до инициализации JS
				$nth       = (int) $limit_value + 1;
				$lid_esc   = esc_attr( $limit_div_id );
				$pre_style = '<style id="' . $lid_esc . '-css">'
					. '#' . $lid_esc . '>ul>li:nth-child(n+' . $nth . '),'
					. '#' . $lid_esc . '>div>*:nth-child(n+' . $nth . ')'
					. '{display:none}</style>';
			}

			$section_content = $pre_style
				. '<div id="' . esc_attr( $limit_div_id ) . '" class="cw-filter-limit"'
				. ' data-limit-type="' . esc_attr( $limit_type ) . '"'
				. ' data-limit="' . esc_attr( (string) $limit_value ) . '"'
				. ' data-show-more="' . esc_attr( $show_more_text ) . '"'
				. ' data-show-less="' . esc_attr( $show_less_text ) . '"'
				. $style_attr . '>'
				. $section_content
				. '</div>';
		}

		if ( 'accordion' === $section_style ) {
			?>
			<div class="border-bottom<?php echo $item_class ? ' ' . esc_attr( $item_class ) : ''; ?>">
				<button class="cw-collapse-toggle btn btn-link px-0 py-3 d-flex w-100 justify-content-between align-items-center text-body text-decoration-none fw-semibold small"
					type="button"
					data-bs-toggle="collapse"
					data-bs-target="#<?php echo esc_attr( $section_id ); ?>"
					aria-expanded="<?php echo $sections_open ? 'true' : 'false'; ?>"
					aria-controls="<?php echo esc_attr( $section_id ); ?>">
					<?php echo esc_html( $section_label ); ?>
				</button>
				<div id="<?php echo esc_attr( $section_id ); ?>" class="collapse<?php echo $sections_open ? ' show' : ''; ?>">
					<div class="pb-3">
						<?php echo $section_content; // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</div>
				</div>
			</div>
			<?php
		} else {
			echo '<div class="' . esc_attr( $wrapper_class . ( $item_class ? ' ' . $item_class : '' ) ) . '">';
			echo '<' . $heading_tag . ' class="' . esc_attr( $heading_class ) . '">' . esc_html( $section_label ) . '</' . $heading_tag . '>';
			echo $section_content; // phpcs:ignore WordPress.Security.EscapeOutput
			echo '</div>';
		}
	}
}

/**
 * Render the filter panel HTML.
 *
 * @param array $atts {
 *   @type bool   show_price       Show price filter. Default true.
 *   @type bool   show_categories  Show category filter. Default true.
 *   @type array  attributes       Array of taxonomy slugs to show (e.g. ['pa_color','pa_size']). Default [].
 *   @type bool   show_rating      Show rating filter. Default false.
 *   @type bool   show_stock       Show stock filter. Default false.
 *   @type string display_mode     'list' | 'checkbox' | 'button'. Default 'checkbox'.
 *   @type bool   show_count       Show product count next to term. Default true.
 *   @type string title            Optional filter panel heading.
 * }
 */
function cw_render_filter_block( $atts = [] ) {
	if ( ! function_exists( 'WC' ) ) {
		return;
	}

	$atts = wp_parse_args( $atts, [
		'show_price'      => true,
		'show_categories' => true,
		'attributes'      => [],
		'show_rating'     => false,
		'show_stock'      => false,
		'display_mode'    => 'checkbox',
		'show_count'      => true,
		'title'           => '',
	] );

	// Ensure we're on a shop/archive page for filters to make sense
	// (still render in editor context / widget preview)
	$filters_dir = get_template_directory() . '/templates/woocommerce/filters/';

	include $filters_dir . 'filter-panel.php';
}

// =============================================================================
// WP WIDGETS
// =============================================================================

/**
 * Register all filter widgets.
 */
function cw_register_filter_widgets() {
	register_widget( 'CW_Widget_Price_Filter' );
	register_widget( 'CW_Widget_Attribute_Filter' );
	register_widget( 'CW_Widget_Category_Filter' );
	register_widget( 'CW_Widget_Rating_Filter' );
	register_widget( 'CW_Widget_Stock_Filter' );
	register_widget( 'CW_Widget_Active_Filters' );
}
add_action( 'widgets_init', 'cw_register_filter_widgets' );

// ── Price Filter Widget ────────────────────────────────────────────────────────

class CW_Widget_Price_Filter extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'cw_price_filter',
			__( 'CW: Фильтр по цене', 'codeweber' ),
			[ 'description' => __( 'Ценовой слайдер для страниц каталога WooCommerce.', 'codeweber' ) ]
		);
	}

	public function widget( $args, $instance ) {
		if ( ! is_shop() && ! is_product_category() && ! is_product_tag() && ! is_product_taxonomy() ) {
			return;
		}

		$title = apply_filters( 'widget_title', $instance['title'] ?? __( 'Цена', 'codeweber' ) );

		$filters_dir = get_template_directory() . '/templates/woocommerce/filters/';

		echo wp_kses_post( $args['before_widget'] );
		if ( $title ) {
			echo wp_kses_post( $args['before_title'] ) . esc_html( $title ) . wp_kses_post( $args['after_title'] );
		}
		include $filters_dir . 'filter-price.php';
		echo wp_kses_post( $args['after_widget'] );
	}

	public function form( $instance ) {
		$title = $instance['title'] ?? __( 'Цена', 'codeweber' );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Заголовок:', 'codeweber' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
				type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		return [ 'title' => sanitize_text_field( $new_instance['title'] ) ];
	}
}

// ── Attribute Filter Widget ────────────────────────────────────────────────────

class CW_Widget_Attribute_Filter extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'cw_attribute_filter',
			__( 'CW: Фильтр по атрибуту', 'codeweber' ),
			[ 'description' => __( 'Фильтр по атрибуту товаров WooCommerce (цвет, размер и др.).', 'codeweber' ) ]
		);
	}

	public function widget( $args, $instance ) {
		if ( ! is_shop() && ! is_product_category() && ! is_product_tag() && ! is_product_taxonomy() ) {
			return;
		}

		$taxonomy = $instance['attribute'] ?? '';
		if ( ! $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
			return;
		}

		$title = apply_filters( 'widget_title', $instance['title'] ?? '' );
		if ( ! $title ) {
			$attr_obj = wc_get_attribute( str_replace( 'pa_', '', $taxonomy ) );
			$title = $attr_obj ? $attr_obj->name : $taxonomy;
		}

		$display_mode = $instance['display_mode'] ?? 'checkbox';
		$show_count   = ! empty( $instance['show_count'] );
		$terms_data   = cw_get_attribute_filter_terms( $taxonomy, $show_count );

		if ( empty( $terms_data ) ) {
			return;
		}

		$filters_dir = get_template_directory() . '/templates/woocommerce/filters/';

		echo wp_kses_post( $args['before_widget'] );
		if ( $title ) {
			echo wp_kses_post( $args['before_title'] ) . esc_html( $title ) . wp_kses_post( $args['after_title'] );
		}
		include $filters_dir . 'filter-attribute.php';
		echo wp_kses_post( $args['after_widget'] );
	}

	public function form( $instance ) {
		$title        = $instance['title'] ?? '';
		$attribute    = $instance['attribute'] ?? '';
		$display_mode = $instance['display_mode'] ?? 'checkbox';
		$show_count   = ! empty( $instance['show_count'] ) ? 'checked' : '';

		$attribute_taxonomies = wc_get_attribute_taxonomies();
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Заголовок:', 'codeweber' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
				type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'attribute' ) ); ?>"><?php esc_html_e( 'Атрибут:', 'codeweber' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'attribute' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'attribute' ) ); ?>">
				<option value=""><?php esc_html_e( '— выберите —', 'codeweber' ); ?></option>
				<?php foreach ( $attribute_taxonomies as $tax ) : ?>
					<option value="pa_<?php echo esc_attr( $tax->attribute_name ); ?>"
						<?php selected( $attribute, 'pa_' . $tax->attribute_name ); ?>>
						<?php echo esc_html( $tax->attribute_label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'display_mode' ) ); ?>"><?php esc_html_e( 'Отображение:', 'codeweber' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'display_mode' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'display_mode' ) ); ?>">
				<option value="checkbox" <?php selected( $display_mode, 'checkbox' ); ?>><?php esc_html_e( 'Чекбоксы', 'codeweber' ); ?></option>
				<option value="list"     <?php selected( $display_mode, 'list' ); ?>><?php esc_html_e( 'Список ссылок', 'codeweber' ); ?></option>
				<option value="button"   <?php selected( $display_mode, 'button' ); ?>><?php esc_html_e( 'Кнопки', 'codeweber' ); ?></option>
			</select>
		</p>
		<p>
			<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_count' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'show_count' ) ); ?>"
				value="1" <?php echo esc_attr( $show_count ); ?>>
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_count' ) ); ?>"><?php esc_html_e( 'Показывать кол-во товаров', 'codeweber' ); ?></label>
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		return [
			'title'        => sanitize_text_field( $new_instance['title'] ),
			'attribute'    => sanitize_key( $new_instance['attribute'] ),
			'display_mode' => in_array( $new_instance['display_mode'], [ 'checkbox', 'list', 'button' ], true )
				? $new_instance['display_mode'] : 'checkbox',
			'show_count'   => ! empty( $new_instance['show_count'] ) ? 1 : 0,
		];
	}
}

// ── Category Filter Widget ─────────────────────────────────────────────────────

class CW_Widget_Category_Filter extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'cw_category_filter',
			__( 'CW: Фильтр по категории', 'codeweber' ),
			[ 'description' => __( 'Список категорий товаров для фильтрации.', 'codeweber' ) ]
		);
	}

	public function widget( $args, $instance ) {
		if ( ! is_shop() && ! is_product_category() && ! is_product_tag() ) {
			return;
		}

		$title      = apply_filters( 'widget_title', $instance['title'] ?? __( 'Категории', 'codeweber' ) );
		$show_count = ! empty( $instance['show_count'] );
		$terms_data = cw_get_category_filter_terms( 0, $show_count );

		if ( empty( $terms_data ) ) {
			return;
		}

		$filters_dir = get_template_directory() . '/templates/woocommerce/filters/';

		echo wp_kses_post( $args['before_widget'] );
		if ( $title ) {
			echo wp_kses_post( $args['before_title'] ) . esc_html( $title ) . wp_kses_post( $args['after_title'] );
		}
		include $filters_dir . 'filter-category.php';
		echo wp_kses_post( $args['after_widget'] );
	}

	public function form( $instance ) {
		$title      = $instance['title'] ?? __( 'Категории', 'codeweber' );
		$show_count = ! empty( $instance['show_count'] ) ? 'checked' : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Заголовок:', 'codeweber' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
				type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_count' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'show_count' ) ); ?>"
				value="1" <?php echo esc_attr( $show_count ); ?>>
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_count' ) ); ?>"><?php esc_html_e( 'Показывать кол-во товаров', 'codeweber' ); ?></label>
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		return [
			'title'      => sanitize_text_field( $new_instance['title'] ),
			'show_count' => ! empty( $new_instance['show_count'] ) ? 1 : 0,
		];
	}
}

// ── Rating Filter Widget ───────────────────────────────────────────────────────

class CW_Widget_Rating_Filter extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'cw_rating_filter',
			__( 'CW: Фильтр по рейтингу', 'codeweber' ),
			[ 'description' => __( 'Фильтр товаров по звёздному рейтингу.', 'codeweber' ) ]
		);
	}

	public function widget( $args, $instance ) {
		if ( ! is_shop() && ! is_product_category() && ! is_product_tag() ) {
			return;
		}

		$title   = apply_filters( 'widget_title', $instance['title'] ?? __( 'Рейтинг', 'codeweber' ) );
		$options = cw_get_rating_filter_options();

		$filters_dir = get_template_directory() . '/templates/woocommerce/filters/';

		echo wp_kses_post( $args['before_widget'] );
		if ( $title ) {
			echo wp_kses_post( $args['before_title'] ) . esc_html( $title ) . wp_kses_post( $args['after_title'] );
		}
		include $filters_dir . 'filter-rating.php';
		echo wp_kses_post( $args['after_widget'] );
	}

	public function form( $instance ) {
		$title = $instance['title'] ?? __( 'Рейтинг', 'codeweber' );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Заголовок:', 'codeweber' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
				type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		return [ 'title' => sanitize_text_field( $new_instance['title'] ) ];
	}
}

// ── Stock Filter Widget ────────────────────────────────────────────────────────

class CW_Widget_Stock_Filter extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'cw_stock_filter',
			__( 'CW: Фильтр по наличию', 'codeweber' ),
			[ 'description' => __( 'Фильтр товаров по статусу наличия.', 'codeweber' ) ]
		);
	}

	public function widget( $args, $instance ) {
		if ( ! is_shop() && ! is_product_category() && ! is_product_tag() ) {
			return;
		}

		$title   = apply_filters( 'widget_title', $instance['title'] ?? __( 'Наличие', 'codeweber' ) );
		$options = cw_get_stock_filter_options();

		$filters_dir = get_template_directory() . '/templates/woocommerce/filters/';

		echo wp_kses_post( $args['before_widget'] );
		if ( $title ) {
			echo wp_kses_post( $args['before_title'] ) . esc_html( $title ) . wp_kses_post( $args['after_title'] );
		}
		include $filters_dir . 'filter-stock.php';
		echo wp_kses_post( $args['after_widget'] );
	}

	public function form( $instance ) {
		$title = $instance['title'] ?? __( 'Наличие', 'codeweber' );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Заголовок:', 'codeweber' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
				type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		return [ 'title' => sanitize_text_field( $new_instance['title'] ) ];
	}
}

// ── Active Filters Widget ──────────────────────────────────────────────────────

class CW_Widget_Active_Filters extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'cw_active_filters',
			__( 'CW: Активные фильтры', 'codeweber' ),
			[ 'description' => __( 'Показывает применённые фильтры с кнопкой сброса.', 'codeweber' ) ]
		);
	}

	public function widget( $args, $instance ) {
		if ( ! function_exists( 'cw_has_active_filters' ) || ! cw_has_active_filters() ) {
			return;
		}

		$title  = apply_filters( 'widget_title', $instance['title'] ?? __( 'Выбрано', 'codeweber' ) );
		$active = cw_get_active_filter_params();

		$filters_dir = get_template_directory() . '/templates/woocommerce/filters/';

		echo wp_kses_post( $args['before_widget'] );
		if ( $title ) {
			echo wp_kses_post( $args['before_title'] ) . esc_html( $title ) . wp_kses_post( $args['after_title'] );
		}
		include $filters_dir . 'filter-active.php';
		echo wp_kses_post( $args['after_widget'] );
	}

	public function form( $instance ) {
		$title = $instance['title'] ?? __( 'Выбрано', 'codeweber' );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Заголовок:', 'codeweber' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
				type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		return [ 'title' => sanitize_text_field( $new_instance['title'] ) ];
	}
}

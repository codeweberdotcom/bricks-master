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
	return remove_query_arg( [ 'paged', 'page' ], get_pagenum_link( 1 ) );
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

	$meta_table = $wpdb->prefix . 'wc_product_meta_lookup';

	// phpcs:disable WordPress.DB.DirectDatabaseQuery
	$min = (float) $wpdb->get_var( "SELECT MIN(min_price) FROM `{$meta_table}` WHERE min_price IS NOT NULL" );
	$max = (float) $wpdb->get_var( "SELECT MAX(max_price) FROM `{$meta_table}` WHERE max_price IS NOT NULL" );
	// phpcs:enable

	if ( 0 === (int) $min && 0 === (int) $max ) {
		// Fallback: scan postmeta
		$min = (float) get_option( 'woocommerce_price_filter_range_min', 0 );
		$max = (float) get_option( 'woocommerce_price_filter_range_max', 10000 );
	}

	$range = [
		'min' => floor( $min ),
		'max' => ceil( $max ),
	];

	return $range;
}

/**
 * Get terms for a WooCommerce product attribute taxonomy.
 * Returns products counts if WC product meta lookup table is available.
 *
 * @param string $taxonomy   Full taxonomy slug (e.g. 'pa_color').
 * @param bool   $show_count Whether to include product count.
 * @return array{ term: WP_Term, count: int, is_active: bool, url: string }[]
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

	$result = [];
	foreach ( $terms as $term ) {
		$result[] = [
			'term'      => $term,
			'count'     => (int) $term->count,
			'is_active' => cw_is_filter_active( $param, $term->slug ),
			'url'       => cw_get_filter_url( $param, $term->slug ),
		];
	}

	return $result;
}

/**
 * Get product categories for the filter.
 *
 * @param int  $parent     Parent category ID (0 = top-level).
 * @param bool $show_count Whether to include product count.
 * @return array{ term: WP_Term, is_active: bool, children: array }[]
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

	$result = [];
	foreach ( $terms as $term ) {
		$result[] = [
			'term'      => $term,
			'is_active' => ( $queried && (int) $queried->term_id === (int) $term->term_id ),
			'url'       => get_term_link( $term ),
			'children'  => [],
		];
	}

	return $result;
}

/**
 * Get rating options (1–5 stars).
 *
 * @return array{ value: string, label: string, is_active: bool, url: string }[]
 */
function cw_get_rating_filter_options() {
	$options = [];
	for ( $i = 5; $i >= 1; $i-- ) {
		$options[] = [
			'value'     => (string) $i,
			'label'     => $i,
			'is_active' => cw_is_filter_active( 'rating_filter', (string) $i ),
			'url'       => cw_get_filter_url( 'rating_filter', (string) $i ),
		];
	}
	return $options;
}

/**
 * Get stock status filter options.
 *
 * @return array{ value: string, label: string, is_active: bool, url: string }[]
 */
function cw_get_stock_filter_options() {
	$param = 'filter_stock_status';
	return [
		[
			'value'     => 'instock',
			'label'     => __( 'В наличии', 'codeweber' ),
			'is_active' => cw_is_filter_active( $param, 'instock' ),
			'url'       => cw_get_filter_url( $param, 'instock' ),
		],
		[
			'value'     => 'outofstock',
			'label'     => __( 'Нет в наличии', 'codeweber' ),
			'is_active' => cw_is_filter_active( $param, 'outofstock' ),
			'url'       => cw_get_filter_url( $param, 'outofstock' ),
		],
		[
			'value'     => 'onbackorder',
			'label'     => __( 'Под заказ', 'codeweber' ),
			'is_active' => cw_is_filter_active( $param, 'onbackorder' ),
			'url'       => cw_get_filter_url( $param, 'onbackorder' ),
		],
	];
}

/**
 * Get product tags for the filter.
 *
 * @param bool $show_count Whether to include product count.
 * @return array{ term: WP_Term, count: int, is_active: bool, url: string }[]
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

	$result = [];
	foreach ( $terms as $term ) {
		$result[] = [
			'term'      => $term,
			'count'     => (int) $term->count,
			'is_active' => cw_is_filter_active( $param, $term->slug ),
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
	$button_class        = isset( $panel_atts['button_class'] ) ? esc_attr( $panel_atts['button_class'] ) : 'btn-outline-secondary';
	$button_active_class = isset( $panel_atts['button_active_class'] ) ? esc_attr( $panel_atts['button_active_class'] ) : 'btn-secondary';
	$reset_label         = isset( $panel_atts['reset_label'] ) ? sanitize_text_field( $panel_atts['reset_label'] ) : '';

	$checkbox_size_class = 'sm' === $checkbox_size ? ' form-check-sm' : '';

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
				echo '<div class="mb-2">';
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
		$display_mode = in_array( $item['displayMode'] ?? '', [ 'checkbox', 'list', 'button' ], true )
			? $item['displayMode'] : 'checkbox';
		$show_count   = isset( $item['showCount'] ) ? (bool) $item['showCount'] : true;
		$taxonomy         = isset( $item['taxonomy'] ) ? sanitize_key( $item['taxonomy'] ) : '';
		$checkbox_columns = isset( $item['checkboxColumns'] ) ? (int) $item['checkboxColumns'] : 1;
		$section_id       = 'cw-filter-' . sanitize_html_class( $item['id'] ?? uniqid() );

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
				if ( empty( $terms_data ) ) {
					$has_content = false;
				} else {
					include $filters_dir . 'filter-category.php';
				}
				break;

			case 'tags':
				if ( ! $section_label ) {
					$section_label = __( 'Метки', 'codeweber' );
				}
				$terms_data = cw_get_tag_filter_terms( $show_count );
				if ( empty( $terms_data ) ) {
					$has_content = false;
				} else {
					include $filters_dir . 'filter-attribute.php';
				}
				break;

			case 'rating':
				if ( ! $section_label ) {
					$section_label = __( 'Рейтинг', 'codeweber' );
				}
				$options = cw_get_rating_filter_options();
				include $filters_dir . 'filter-rating.php';
				break;

			case 'stock':
				if ( ! $section_label ) {
					$section_label = __( 'Наличие', 'codeweber' );
				}
				$options = cw_get_stock_filter_options();
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
				if ( empty( $terms_data ) ) {
					$has_content = false;
				} else {
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

		if ( 'accordion' === $section_style ) {
			?>
			<div class="border-bottom">
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
			echo '<div class="' . esc_attr( $wrapper_class ) . '">';
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

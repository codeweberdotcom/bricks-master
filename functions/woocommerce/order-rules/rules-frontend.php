<?php
/**
 * Order rules: front end (quantity field arguments, start value, product page note, script).
 *
 * @package codeweber
 */

defined( 'ABSPATH' ) || exit;

/**
 * Applies the rule to the quantity field: minimum, step, maximum and the start value of the add-to-cart form.
 * Runs after other plugins (priority 20).
 *
 * @param array           $args    Field arguments.
 * @param WC_Product|null $product Product.
 * @return array
 */
add_filter( 'woocommerce_quantity_input_args', 'codeweber_order_rules_quantity_args', 20, 2 );
function codeweber_order_rules_quantity_args( $args, $product ) {
	if ( ! is_array( $args ) || ! $product instanceof WC_Product || $product->is_sold_individually() ) {
		return $args;
	}

	$rules = codeweber_order_rules_get( $product );
	if ( empty( $rules['active'] ) ) {
		return $args;
	}

	$args['min_value'] = $rules['min'];
	$args['step']      = $rules['step'];

	if ( $rules['max'] > 0 ) {
		$current_max       = isset( $args['max_value'] ) ? (float) $args['max_value'] : -1;
		$args['max_value'] = ( $current_max > 0 ) ? min( $current_max, $rules['max'] ) : $rules['max'];
	}

	// The add-to-cart form starts with the nearest allowed quantity (the cart keeps its own values).
	// Other callers (for example the product options plugin) pass partial arguments without input_value.
	if ( isset( $args['input_name'], $args['input_value'] ) && 'quantity' === $args['input_name'] ) {
		$args['input_value'] = codeweber_order_rules_snap( (int) $args['input_value'], $rules );
	}

	return $args;
}

/**
 * Text under the add-to-cart form: "Minimum order: 5 pcs. Sold in multiples of 5."
 */
add_action( 'woocommerce_after_add_to_cart_form', 'codeweber_order_rules_render_note' );
function codeweber_order_rules_render_note(): void {
	global $product;

	if ( ! $product instanceof WC_Product || ! class_exists( 'Codeweber_Options' ) ) {
		return;
	}
	$show = Codeweber_Options::get( 'cw_qty_show_notice', 1 );
	if ( in_array( $show, array( 0, '0', false, 'false', 'off' ), true ) ) {
		return;
	}

	$rules = codeweber_order_rules_get( $product );
	if ( empty( $rules['active'] ) ) {
		return;
	}

	$custom = trim( (string) Codeweber_Options::get( 'cw_qty_notice_text', '' ) );
	if ( '' !== $custom ) {
		$text = str_replace(
			[ '{min}', '{step}', '{max}' ],
			[ (string) $rules['min'], (string) $rules['step'], (string) $rules['max'] ],
			$custom
		);
	} else {
		$parts = [];
		if ( $rules['min'] > 1 ) {
			/* translators: %d: minimum quantity */
			$parts[] = sprintf( __( 'Minimum order: %d pcs.', 'codeweber' ), $rules['min'] );
		}
		if ( $rules['step'] > 1 ) {
			/* translators: %d: quantity step */
			$parts[] = sprintf( __( 'Sold in multiples of %d.', 'codeweber' ), $rules['step'] );
		}
		if ( $rules['max'] > 0 ) {
			/* translators: %d: maximum quantity */
			$parts[] = sprintf( __( 'Maximum order: %d pcs.', 'codeweber' ), $rules['max'] );
		}
		$text = implode( ' ', $parts );
	}

	if ( '' === trim( $text ) ) {
		return;
	}

	printf( '<p class="codeweber-order-rules-note text-muted mt-3 mb-0">%s</p>', esc_html( $text ) );
}

/**
 * Script for the +/- buttons with a step and for rounding typed values (product and cart pages only).
 */
add_action( 'wp_enqueue_scripts', 'codeweber_order_rules_enqueue_script' );
function codeweber_order_rules_enqueue_script(): void {
	if ( ! function_exists( 'is_product' ) || ! ( is_product() || is_cart() ) || ! codeweber_order_rules_enabled() ) {
		return;
	}
	wp_enqueue_script(
		'codeweber-order-rules',
		get_template_directory_uri() . '/functions/woocommerce/order-rules/order-rules.js',
		[],
		wp_get_theme()->get( 'Version' ),
		[
			'in_footer' => true,
			'strategy'  => 'defer',
		]
	);
}

<?php
/**
 * Order rules: server-side checks (add to cart, cart update, cart/checkout) and messages.
 *
 * @package codeweber
 */

defined( 'ABSPATH' ) || exit;

/**
 * Error text for a broken rule. Texts can be overridden in the theme options: placeholders {product} and {value}.
 *
 * @param string     $type    'min', 'step' or 'max'.
 * @param WC_Product $product Product.
 * @param array      $rules   Effective rule.
 * @return string
 */
function codeweber_order_rules_message( string $type, WC_Product $product, array $rules ): string {
	$name  = $product->get_name();
	$value = ( 'step' === $type ) ? $rules['step'] : $rules[ $type ];

	$custom = class_exists( 'Codeweber_Options' ) ? trim( (string) Codeweber_Options::get( 'cw_qty_msg_' . $type, '' ) ) : '';
	if ( '' !== $custom ) {
		return esc_html( str_replace( [ '{product}', '{value}' ], [ $name, (string) $value ], $custom ) );
	}

	switch ( $type ) {
		case 'min':
			/* translators: 1: product name, 2: minimum quantity */
			$text = __( 'The minimum quantity for “%1$s” is %2$d.', 'codeweber' );
			break;
		case 'max':
			/* translators: 1: product name, 2: maximum quantity */
			$text = __( 'The maximum quantity for “%1$s” is %2$d.', 'codeweber' );
			break;
		default:
			/* translators: 1: product name, 2: quantity step */
			$text = __( 'The quantity of “%1$s” must be a multiple of %2$d.', 'codeweber' );
	}

	return sprintf( esc_html( $text ), esc_html( $name ), (int) $value );
}

/**
 * Add to cart: the added quantity must satisfy the rule (each cart line is checked on its own).
 *
 * @param bool $passed     Validation result so far.
 * @param int  $product_id Product ID.
 * @param int  $quantity   Quantity.
 * @return bool
 */
add_filter( 'woocommerce_add_to_cart_validation', 'codeweber_order_rules_validate_add', 15, 3 );
function codeweber_order_rules_validate_add( $passed, $product_id, $quantity ) {
	if ( ! $passed ) {
		return $passed;
	}

	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		return $passed;
	}

	$rules = codeweber_order_rules_get( $product );
	$error = codeweber_order_rules_check( (int) $quantity, $rules );
	if ( null === $error ) {
		return $passed;
	}

	wc_add_notice( codeweber_order_rules_message( $error, $product, $rules ), 'error' );
	return false;
}

/**
 * Cart: a changed quantity of a line must satisfy the rule.
 *
 * @param bool   $passed        Validation result so far.
 * @param string $cart_item_key Cart item key.
 * @param array  $values        Cart item.
 * @param int    $quantity      New quantity.
 * @return bool
 */
add_filter( 'woocommerce_update_cart_validation', 'codeweber_order_rules_validate_update', 15, 4 );
function codeweber_order_rules_validate_update( $passed, $cart_item_key, $values, $quantity ) {
	if ( ! $passed || empty( $values['data'] ) || ! $values['data'] instanceof WC_Product ) {
		return $passed;
	}

	// Quantity 0 removes the line from the cart: always allowed.
	if ( (int) $quantity <= 0 ) {
		return $passed;
	}

	$product = $values['data'];
	$rules   = codeweber_order_rules_get( $product );
	$error   = codeweber_order_rules_check( (int) $quantity, $rules );
	if ( null === $error ) {
		return $passed;
	}

	wc_add_notice( codeweber_order_rules_message( $error, $product, $rules ), 'error' );
	return false;
}

/**
 * Cart and checkout: every line must satisfy the rule (covers old carts and changed rules).
 */
add_action( 'woocommerce_check_cart_items', 'codeweber_order_rules_validate_cart', 15 );
function codeweber_order_rules_validate_cart(): void {
	if ( ! WC()->cart ) {
		return;
	}

	foreach ( WC()->cart->get_cart() as $item ) {
		if ( empty( $item['data'] ) || ! $item['data'] instanceof WC_Product ) {
			continue;
		}
		$rules = codeweber_order_rules_get( $item['data'] );
		$error = codeweber_order_rules_check( (int) $item['quantity'], $rules );
		if ( null === $error ) {
			continue;
		}
		$message = codeweber_order_rules_message( $error, $item['data'], $rules );
		if ( ! wc_has_notice( $message, 'error' ) ) {
			wc_add_notice( $message, 'error' );
		}
	}
}

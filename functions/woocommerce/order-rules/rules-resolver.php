<?php
/**
 * Order rules: resolving the quantity rule of a product.
 *
 * Cascade: product -> product category -> global defaults (Redux).
 * A rule is [ 'min' => int, 'step' => int, 'max' => int ], 0 means "not set".
 *
 * @package codeweber
 */

defined( 'ABSPATH' ) || exit;

/**
 * Meta keys. Product meta and product_cat term meta share the same value keys;
 * the mode key exists on products only.
 */
const CODEWEBER_ORDER_RULES_KEY_MODE = '_cw_qty_mode';
const CODEWEBER_ORDER_RULES_KEY_MIN  = '_cw_min_qty';
const CODEWEBER_ORDER_RULES_KEY_STEP = '_cw_qty_step';
const CODEWEBER_ORDER_RULES_KEY_MAX  = '_cw_max_qty';

/**
 * Product rule modes.
 *
 * @return string[] Allowed values of the product mode.
 */
function codeweber_order_rules_modes(): array {
	return [ 'inherit', 'custom', 'none' ];
}

/**
 * A rule with nothing set.
 *
 * @return array{min:int,step:int,max:int}
 */
function codeweber_order_rules_empty(): array {
	return [
		'min'  => 0,
		'step' => 0,
		'max'  => 0,
	];
}

/**
 * Whether the rule has at least one value set.
 *
 * @param array $rule Rule.
 * @return bool
 */
function codeweber_order_rules_is_set( array $rule ): bool {
	return ( $rule['min'] > 0 || $rule['step'] > 0 || $rule['max'] > 0 );
}

/**
 * Master switch of the module (Redux, default on). When off, no rule is applied anywhere.
 *
 * @return bool
 */
function codeweber_order_rules_enabled(): bool {
	if ( ! class_exists( 'Codeweber_Options' ) ) {
		return true;
	}
	$value = Codeweber_Options::get( 'cw_qty_enabled', 1 );
	return ! in_array( $value, array( 0, '0', false, 'false', 'off' ), true );
}

/**
 * Reads a rule stored in post or term meta.
 *
 * @param int    $object_id Product or term ID.
 * @param string $type      'post' or 'term'.
 * @return array{min:int,step:int,max:int}
 */
function codeweber_order_rules_read( int $object_id, string $type ): array {
	$read = ( 'term' === $type ) ? 'get_term_meta' : 'get_post_meta';
	return [
		'min'  => absint( $read( $object_id, CODEWEBER_ORDER_RULES_KEY_MIN, true ) ),
		'step' => absint( $read( $object_id, CODEWEBER_ORDER_RULES_KEY_STEP, true ) ),
		'max'  => absint( $read( $object_id, CODEWEBER_ORDER_RULES_KEY_MAX, true ) ),
	];
}

/**
 * Global defaults from the theme settings (Redux).
 *
 * @return array{min:int,step:int,max:int}
 */
function codeweber_order_rules_global(): array {
	if ( ! class_exists( 'Codeweber_Options' ) ) {
		return codeweber_order_rules_empty();
	}
	return [
		'min'  => absint( Codeweber_Options::get( 'cw_qty_min', 0 ) ),
		'step' => absint( Codeweber_Options::get( 'cw_qty_step', 0 ) ),
		'max'  => absint( Codeweber_Options::get( 'cw_qty_max', 0 ) ),
	];
}

/**
 * Rule coming from the product categories.
 *
 * For every category of the product the nearest category (itself or an ancestor) that carries a rule is found.
 * Among those the deepest one wins; when several are equally deep the larger value of each field is taken.
 *
 * @param int $product_id Product ID (parent ID for variations).
 * @return array|null [ 'rule' => array, 'term_id' => int ] or null when no category has a rule.
 */
function codeweber_order_rules_from_categories( int $product_id ): ?array {
	$term_ids = wc_get_product_term_ids( $product_id, 'product_cat' );
	$best     = null;

	foreach ( $term_ids as $term_id ) {
		$cursor = (int) $term_id;
		$guard  = 0;
		while ( $cursor > 0 && $guard < 30 ) {
			++$guard;
			$rule = codeweber_order_rules_read( $cursor, 'term' );
			if ( codeweber_order_rules_is_set( $rule ) ) {
				$depth = count( get_ancestors( $cursor, 'product_cat', 'taxonomy' ) );
				if ( null === $best || $depth > $best['depth'] ) {
					$best = [
						'depth'   => $depth,
						'rule'    => $rule,
						'term_id' => $cursor,
					];
				} elseif ( $depth === $best['depth'] && $cursor !== $best['term_id'] ) {
					$best['rule'] = [
						'min'  => max( $best['rule']['min'], $rule['min'] ),
						'step' => max( $best['rule']['step'], $rule['step'] ),
						'max'  => max( $best['rule']['max'], $rule['max'] ),
					];
				}
				break;
			}
			$term   = get_term( $cursor, 'product_cat' );
			$cursor = ( $term && ! is_wp_error( $term ) ) ? (int) $term->parent : 0;
		}
	}

	return ( null === $best ) ? null : [
		'rule'    => $best['rule'],
		'term_id' => $best['term_id'],
	];
}

/**
 * Turns a raw rule into the effective one: minimum is at least 1 and a multiple of the step,
 * maximum is a multiple of the step and not lower than the minimum.
 *
 * @param array $rule Raw rule.
 * @return array{min:int,step:int,max:int}
 */
function codeweber_order_rules_normalize( array $rule ): array {
	$step = max( 1, (int) $rule['step'] );
	$min  = max( 1, (int) $rule['min'] );
	$max  = (int) $rule['max'];

	if ( $step > 1 ) {
		$min = (int) ( ceil( $min / $step ) * $step );
		if ( $max > 0 ) {
			$max = (int) ( floor( $max / $step ) * $step );
		}
	}
	if ( $max > 0 && $max < $min ) {
		$max = $min;
	}

	return [
		'min'  => $min,
		'step' => $step,
		'max'  => $max,
	];
}

/**
 * Effective order rule of a product.
 *
 * Returns the effective [ min, step, max ] plus:
 *  - active    (bool)   whether any restriction applies;
 *  - source    (string) 'disabled' | 'product' | 'product-none' | 'category' | 'global' | 'none';
 *  - source_id (int)    product ID, category ID or 0.
 *
 * The result goes through the 'codeweber_order_rules_resolved' filter so other modules (e.g. a rule calculated from
 * the product configurator, user roles) can adjust it.
 *
 * @param WC_Product|int $product Product object or ID (variations resolve to the parent product).
 * @return array
 */
function codeweber_order_rules_get( $product ): array {
	$product = wc_get_product( $product );
	$empty   = codeweber_order_rules_normalize( codeweber_order_rules_empty() ) + [
		'active'    => false,
		'source'    => 'none',
		'source_id' => 0,
	];
	if ( ! $product ) {
		return $empty;
	}

	$id = $product->is_type( 'variation' ) ? (int) $product->get_parent_id() : (int) $product->get_id();

	if ( ! codeweber_order_rules_enabled() ) {
		$rules           = $empty;
		$rules['source'] = 'disabled';
		return apply_filters( 'codeweber_order_rules_resolved', $rules, $product );
	}

	$raw       = codeweber_order_rules_empty();
	$source    = 'none';
	$source_id = 0;
	$mode      = get_post_meta( $id, CODEWEBER_ORDER_RULES_KEY_MODE, true );

	if ( 'none' === $mode ) {
		$source = 'product-none';
	} elseif ( 'custom' === $mode ) {
		$raw       = codeweber_order_rules_read( $id, 'post' );
		$source    = 'product';
		$source_id = $id;
	} else {
		$from_categories = codeweber_order_rules_from_categories( $id );
		if ( null !== $from_categories ) {
			$raw       = $from_categories['rule'];
			$source    = 'category';
			$source_id = $from_categories['term_id'];
		} else {
			$global = codeweber_order_rules_global();
			if ( codeweber_order_rules_is_set( $global ) ) {
				$raw    = $global;
				$source = 'global';
			}
		}
	}

	$rules           = codeweber_order_rules_normalize( $raw );
	$rules['active'] = ( $rules['min'] > 1 || $rules['step'] > 1 || $rules['max'] > 0 );
	$rules['source'] = $source;
	$rules['source_id'] = $source_id;

	return apply_filters( 'codeweber_order_rules_resolved', $rules, $product );
}

/**
 * Checks a quantity against a rule.
 *
 * @param int   $quantity Quantity.
 * @param array $rules    Result of codeweber_order_rules_get().
 * @return string|null 'min', 'step', 'max' or null when the quantity is allowed.
 */
function codeweber_order_rules_check( int $quantity, array $rules ): ?string {
	if ( empty( $rules['active'] ) ) {
		return null;
	}
	if ( $quantity < $rules['min'] ) {
		return 'min';
	}
	if ( $rules['max'] > 0 && $quantity > $rules['max'] ) {
		return 'max';
	}
	if ( $rules['step'] > 1 && 0 !== $quantity % $rules['step'] ) {
		return 'step';
	}
	return null;
}

/**
 * Nearest allowed quantity that is not lower than the given one (clamped to the maximum).
 *
 * @param int   $quantity Quantity.
 * @param array $rules    Result of codeweber_order_rules_get().
 * @return int
 */
function codeweber_order_rules_snap( int $quantity, array $rules ): int {
	if ( empty( $rules['active'] ) ) {
		return $quantity;
	}
	$value = max( $quantity, $rules['min'] );
	if ( $rules['step'] > 1 ) {
		$value = (int) ( ceil( $value / $rules['step'] ) * $rules['step'] );
	}
	if ( $rules['max'] > 0 && $value > $rules['max'] ) {
		$value = $rules['max'];
	}
	return $value;
}

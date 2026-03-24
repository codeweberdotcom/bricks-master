<?php
/**
 * WooCommerce Product Card — Shared Setup
 *
 * Подключается через require в начале каждого шаблона карточки.
 * Устанавливает общие переменные: изображение, категория, рейтинг,
 * значок Sale/New, wishlist-кнопка, compare-флаг, класс колонки.
 *
 * @package Codeweber
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product || ! $product->is_visible() ) {
	return;
}

$product_id  = $product->get_id();
$product_url = get_permalink( $product_id );

// ── Стили из Redux ────────────────────────────────────────────────────────────
$card_radius = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : 'rounded';
$btn_style   = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button' ) : '';

// ── Изображения ───────────────────────────────────────────────────────────────
$image_html     = $product->get_image( 'woocommerce_thumbnail', [ 'class' => '' ] );
$hover_img_html = '';
$gallery_ids    = $product->get_gallery_image_ids();
if ( ! empty( $gallery_ids ) ) {
	$hover_img_html = wp_get_attachment_image(
		$gallery_ids[0],
		'woocommerce_thumbnail',
		false,
		[ 'class' => 'product-hover-img', 'alt' => '' ]
	);
}

// ── Категория (верхнеуровневая) ───────────────────────────────────────────────
$categories    = get_the_terms( $product_id, 'product_cat' );
$category_name = '';
if ( $categories && ! is_wp_error( $categories ) ) {
	$top   = array_filter( $categories, fn( $t ) => 0 === $t->parent );
	$first = $top ? array_values( $top )[0] : $categories[0];
	$category_name = $first->name;
}

// ── Рейтинг ───────────────────────────────────────────────────────────────────
$rating_words = [ '', 'one', 'two', 'three', 'four', 'five' ];
$rating_index = min( 5, max( 0, round( (float) $product->get_average_rating() ) ) );
$rating_word  = $rating_words[ $rating_index ];

// ── Корзина ───────────────────────────────────────────────────────────────────
$is_simple        = $product->is_type( 'simple' );
$add_to_cart_url  = $product->add_to_cart_url();
$add_to_cart_text = $product->add_to_cart_text();

// ── Redux-настройки ───────────────────────────────────────────────────────────
$cw_opts = get_option( 'redux_demo', [] );

// ── Значок Sale / New ─────────────────────────────────────────────────────────
$badge_shape_map = [
	'1' => 'rounded-pill',
	'2' => 'rounded',
	'3' => 'rounded-3',
	'4' => 'rounded-0',
];
$use_theme_shape = ! isset( $cw_opts['woo_badge_shape_use_theme'] ) || (bool) $cw_opts['woo_badge_shape_use_theme'];
$shape_key       = $use_theme_shape
	? ( $cw_opts['opt_button_select_style'] ?? '1' )
	: ( $cw_opts['woo_badge_shape'] ?? '1' );
$badge_shape = $badge_shape_map[ $shape_key ] ?? 'rounded-pill';

// Позиция значка — через CSS-класс (не inline)
$badge_pos_class = ( isset( $cw_opts['woo_badge_position'] ) && $cw_opts['woo_badge_position'] === 'top-right' )
	? 'cw-badge--right'
	: 'cw-badge--left';

$badge         = '';
$sale_badge_on = ! isset( $cw_opts['woo_badge_sale_enable'] ) || (bool) $cw_opts['woo_badge_sale_enable'];
$new_badge_on  = ! isset( $cw_opts['woo_badge_new_enable'] ) || (bool) $cw_opts['woo_badge_new_enable'];

if ( $sale_badge_on && $product->is_on_sale() ) {
	$bg        = ! empty( $cw_opts['woo_badge_sale_bg'] ) ? $cw_opts['woo_badge_sale_bg'] : '#d16b86';
	$color     = ! empty( $cw_opts['woo_badge_sale_color'] ) ? $cw_opts['woo_badge_sale_color'] : '#ffffff';
	$sale_type = $cw_opts['woo_badge_sale_type'] ?? 'text';
	if ( 'percent' === $sale_type ) {
		$percent = 0;
		if ( $product->is_type( 'variable' ) ) {
			$regular = (float) $product->get_variation_regular_price( 'max' );
			$sale    = (float) $product->get_variation_sale_price( 'min' );
			if ( $regular > 0 ) {
				$percent = round( ( $regular - $sale ) / $regular * 100 );
			}
		} else {
			$regular = (float) $product->get_regular_price();
			$sale    = (float) $product->get_sale_price();
			if ( $regular > 0 ) {
				$percent = round( ( $regular - $sale ) / $regular * 100 );
			}
		}
		$text = $percent > 0 ? '−' . $percent . '%' : ( ! empty( $cw_opts['woo_badge_sale_text'] ) ? $cw_opts['woo_badge_sale_text'] : __( 'Распродажа!', 'codeweber' ) );
	} else {
		$text = ! empty( $cw_opts['woo_badge_sale_text'] ) ? $cw_opts['woo_badge_sale_text'] : __( 'Распродажа!', 'codeweber' );
	}
	$badge = '<span class="' . esc_attr( $badge_shape . ' ' . $badge_pos_class ) . ' w-10 h-10 position-absolute text-uppercase fs-13 d-flex align-items-center justify-content-center text-center lh-sm" style="background-color:' . esc_attr( $bg ) . ';color:' . esc_attr( $color ) . ';"><span>' . esc_html( $text ) . '</span></span>';
} elseif ( $new_badge_on && $product->is_featured() ) {
	$bg    = ! empty( $cw_opts['woo_badge_new_bg'] ) ? $cw_opts['woo_badge_new_bg'] : '#54a8c7';
	$color = ! empty( $cw_opts['woo_badge_new_color'] ) ? $cw_opts['woo_badge_new_color'] : '#ffffff';
	$text  = ! empty( $cw_opts['woo_badge_new_text'] ) ? $cw_opts['woo_badge_new_text'] : __( 'Новинка!', 'codeweber' );
	$badge = '<span class="' . esc_attr( $badge_shape . ' ' . $badge_pos_class ) . ' w-10 h-10 position-absolute text-uppercase fs-13 d-flex align-items-center justify-content-center text-center lh-sm" style="background-color:' . esc_attr( $bg ) . ';color:' . esc_attr( $color ) . ';"><span>' . esc_html( $text ) . '</span></span>';
}

// ── Wishlist ──────────────────────────────────────────────────────────────────
$cw_wl_active = false;
if ( function_exists( 'cw_get_wishlist_url' ) && class_exists( 'CW_Wishlist_Item' ) ) {
	global $cw_wishlist_instance;
	if ( $cw_wishlist_instance instanceof CW_Wishlist_Item ) {
		$cw_wl_active = $cw_wishlist_instance->is_in_wishlist( $product_id );
	}
}
$cw_wl_href  = $cw_wl_active && function_exists( 'cw_get_wishlist_url' ) ? esc_url( cw_get_wishlist_url() ) : '#';
$cw_wl_class = 'item-like cw-wishlist-btn' . ( $cw_wl_active ? ' cw-wishlist-btn--active' : '' );
$cw_wl_title = $cw_wl_active ? __( 'In Wishlist', 'codeweber' ) : __( 'Add to Wishlist', 'codeweber' );

// ── Compare ───────────────────────────────────────────────────────────────────
$cw_compare_on = class_exists( 'CW_Compare_Storage' )
	&& class_exists( 'Redux' )
	&& ( (bool) Redux::get_option( 'redux_demo', 'compare_enable', true ) )
	&& ( (bool) Redux::get_option( 'redux_demo', 'compare_btn_loop', true ) );

// ── Класс колонки ─────────────────────────────────────────────────────────────
$cw_wl_mode = ! empty( $GLOBALS['cw_wishlist_render'] );
$cw_col     = $cw_wl_mode
	? 'col-6 col-md-4 col-xl-3 cw-wishlist-card'
	: ( ! empty( $GLOBALS['cw_swiper_loop'] ) ? 'w-100'
	: ( ! empty( $GLOBALS['cw_shop_col_class'] ) ? $GLOBALS['cw_shop_col_class'] : 'col-12 col-sm-6 col-lg-4' ) );
$cw_wl_attr = $cw_wl_mode ? ' data-product-id="' . esc_attr( $product_id ) . '"' : '';

<?php
/**
 * WooCommerce Product Loop Item — Dispatcher
 *
 * Читает Redux-настройку archive_template_select_product и передаёт управление
 * нужному шаблону карточки из templates/post-cards/product/.
 *
 * Переопределяет woocommerce/content-product.php из плагина WooCommerce.
 */

defined( 'ABSPATH' ) || exit;

global $product, $opt_name;

if ( ! $product || ! $product->is_visible() ) {
	return;
}

$template = 'shop2';

if ( class_exists( 'Redux' ) && ! empty( $opt_name ) ) {
	$redux_template = Redux::get_option( $opt_name, 'archive_template_select_product' );
	if ( ! empty( $redux_template ) ) {
		$template = $redux_template;
	}
}

get_template_part( 'templates/woocommerce/cards/' . $template );

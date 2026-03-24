<?php
/**
 * WooCommerce — точка входа.
 *
 * Подключается из functions.php только при активном WooCommerce:
 *   if ( class_exists( 'WooCommerce' ) ) {
 *       require_once get_template_directory() . '/functions/woocommerce/init.php';
 *   }
 */

defined( 'ABSPATH' ) || exit;

$_woo_dir = get_template_directory() . '/functions/woocommerce/';

// Core
require_once $_woo_dir . 'core.php';
require_once $_woo_dir . 'cart-offcanvas.php';
require_once $_woo_dir . 'checkout.php';

// Wishlist
require_once $_woo_dir . 'wishlist/functions.php';
require_once $_woo_dir . 'wishlist/class-cw-storage-interface.php';
require_once $_woo_dir . 'wishlist/class-cw-cookie-storage.php';
require_once $_woo_dir . 'wishlist/class-cw-session-storage.php';
require_once $_woo_dir . 'wishlist/class-cw-db-storage.php';
require_once $_woo_dir . 'wishlist/class-cw-wishlist-item.php';
require_once $_woo_dir . 'wishlist/class-cw-wishlist-ui.php';
require_once $_woo_dir . 'wishlist/class-cw-wishlist.php';

// Compare
require_once $_woo_dir . 'compare/functions.php';
require_once $_woo_dir . 'compare/class-cw-compare-storage.php';
require_once $_woo_dir . 'compare/class-cw-compare-table.php';
require_once $_woo_dir . 'compare/class-cw-compare-ui.php';
require_once $_woo_dir . 'compare/class-cw-compare.php';

// Инициализируем после Redux (priority 40), чтобы is_enabled() мог прочитать настройки.
add_action( 'after_setup_theme', function () {
	new CW_Wishlist();
	new CW_Compare();
}, 40 );

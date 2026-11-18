<?php
/**
 * Order rules module: minimum, step and maximum quantity for products and product categories.
 *
 * Cascade: product -> product category -> global settings (Redux, "Woocommerce / Order rules";
 * the section lives in redux-framework/sample/sections/codeweber/woocommerce.php).
 * Public API: codeweber_order_rules_get( $product ); filter 'codeweber_order_rules_resolved'.
 *
 * @package codeweber
 */

defined( 'ABSPATH' ) || exit;

$codeweber_order_rules_dir = __DIR__;

require_once $codeweber_order_rules_dir . '/rules-resolver.php';
require_once $codeweber_order_rules_dir . '/rules-admin.php';
require_once $codeweber_order_rules_dir . '/rules-frontend.php';
require_once $codeweber_order_rules_dir . '/rules-validate.php';

unset( $codeweber_order_rules_dir );

<?php
/**
 * Single Product Price
 *
 * Переопределяет single-product/price.php.
 * Добавляет fs-20 mb-2 — как в образце shop-product.html.
 *
 * @version 3.0.0
 */

defined( 'ABSPATH' ) || exit;

global $product;
?>
<p class="<?php echo esc_attr( apply_filters( 'woocommerce_product_price_class', 'price' ) ); ?> fs-20 mb-2"><?php echo $product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>

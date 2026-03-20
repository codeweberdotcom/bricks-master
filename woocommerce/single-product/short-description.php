<?php
/**
 * Single Product Short Description
 *
 * Переопределяет single-product/short-description.php.
 * Добавляет mb-6 — как в образце shop-product.html.
 *
 * @version 3.3.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

$short_description = apply_filters( 'woocommerce_short_description', $product->get_short_description() );

if ( ! $short_description ) {
	return;
}
?>
<div class="woocommerce-product-details__short-description mb-6">
	<?php echo $short_description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>

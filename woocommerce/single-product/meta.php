<?php
/**
 * Single Product Meta (SKU, Categories, Tags)
 *
 * Переопределяет single-product/meta.php.
 * Bootstrap-утилиты вместо custom CSS.
 *
 * @version 9.7.0
 */

use Automattic\WooCommerce\Enums\ProductType;

defined( 'ABSPATH' ) || exit;

global $product;
?>

<div class="product_meta mt-3 pt-3 border-top">
	<?php do_action( 'woocommerce_product_meta_start' ); ?>

	<?php if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( ProductType::VARIABLE ) ) ) : ?>
		<span class="d-block small text-muted mb-1">
			<?php esc_html_e( 'SKU:', 'woocommerce' ); ?>
			<span class="sku fw-semibold text-dark">
				<?php echo ( $sku = $product->get_sku() ) ? esc_html( $sku ) : esc_html__( 'N/A', 'woocommerce' ); ?>
			</span>
		</span>
	<?php endif; ?>

	<?php
	echo wc_get_product_category_list(
		$product->get_id(),
		', ',
		'<span class="d-block small text-muted mb-1 posted_in">' . _n( 'Category:', 'Categories:', count( $product->get_category_ids() ), 'woocommerce' ) . ' <span class="fw-semibold">',
		'</span></span>'
	);
	?>

	<?php
	echo wc_get_product_tag_list(
		$product->get_id(),
		', ',
		'<span class="d-block small text-muted mb-1 tagged_as">' . _n( 'Tag:', 'Tags:', count( $product->get_tag_ids() ), 'woocommerce' ) . ' <span class="fw-semibold">',
		'</span></span>'
	);
	?>

	<?php do_action( 'woocommerce_product_meta_end' ); ?>
</div>

<?php
/**
 * Product Card: shop-compact
 *
 * Минималистичная карточка для плотной сетки (4-6 колонок).
 * Фото без hover-overlay — только wishlist-иконка при hover.
 * Название, цена, ссылка на товар. Без категории и рейтинга.
 *
 * @package Codeweber
 */

defined( 'ABSPATH' ) || exit;

require __DIR__ . '/_common.php';

if ( ! isset( $product_id ) ) {
	return;
}
?>
<div id="product-<?php echo esc_attr( $product_id ); ?>" class="project item <?php echo esc_attr( $cw_col ); ?>"<?php echo $cw_wl_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<figure class="<?php echo esc_attr( $card_radius ); ?> mb-4 position-relative overflow-hidden">

		<a href="<?php echo esc_url( $product_url ); ?>">
			<?php echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</a>

		<?php if ( $hover_img_html ) : ?>
			<?php echo $hover_img_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php endif; ?>

		<?php echo $badge; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		<a class="<?php echo esc_attr( $cw_wl_class ); ?>"
		   href="<?php echo $cw_wl_href; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"
		   data-product-id="<?php echo esc_attr( $product_id ); ?>"
		   data-bs-toggle="white-tooltip"
		   title="<?php echo esc_attr( $cw_wl_title ); ?>"
		   aria-label="<?php echo esc_attr( $cw_wl_title ); ?>">
			<span class="cw-wishlist-icon"><i class="uil uil-heart"></i></span>
		</a>

		<a class="item-view" href="<?php echo esc_url( $product_url ); ?>"
		   data-product-id="<?php echo esc_attr( $product_id ); ?>"
		   data-bs-toggle="white-tooltip"
		   title="<?php esc_attr_e( 'Quick view', 'codeweber' ); ?>">
			<i class="uil uil-eye"></i>
		</a>

		<?php if ( $cw_compare_on ) : ?>
			<?php CW_Compare_UI::render_loop_button( $product_id ); ?>
		<?php endif; ?>

		<?php if ( $is_simple ) : ?>
			<a href="<?php echo esc_url( $add_to_cart_url ); ?>"
			   class="item-cart ajax_add_to_cart"
			   data-product_id="<?php echo esc_attr( $product_id ); ?>"
			   data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
			   data-quantity="1"
			   rel="nofollow">
				<i class="uil uil-shopping-bag"></i>
				<?php echo esc_html( $add_to_cart_text ); ?>
			</a>
		<?php else : ?>
			<a href="<?php echo esc_url( $product_url ); ?>" class="item-cart">
				<i class="uil uil-shopping-bag"></i>
				<?php echo esc_html( $add_to_cart_text ); ?>
			</a>
		<?php endif; ?>

	</figure>

	<h2 class="post-title fs-17 mb-1">
		<a href="<?php echo esc_url( $product_url ); ?>" class="link-dark">
			<?php echo esc_html( $product->get_name() ); ?>
		</a>
	</h2>

	<p class="price mb-0"><?php echo $product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>

</div>
<!-- /.item -->

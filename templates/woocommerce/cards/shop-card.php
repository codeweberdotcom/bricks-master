<?php
/**
 * Product Card: shop-card
 *
 * Bootstrap .card с явной кнопкой «В корзину» внизу.
 * Фото сверху — hover-иконки wishlist / quick view / compare.
 * Hover-swap второго изображения. Значок Sale/New.
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
	<div class="card<?php echo $card_radius ? ' ' . esc_attr( $card_radius ) : ''; ?> h-100 d-flex flex-column">

		<figure class="mb-0 position-relative overflow-hidden<?php echo $card_radius ? ' ' . esc_attr( $card_radius ) . '-top' : ''; ?>">
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
		</figure>

		<div class="card-body d-flex flex-column p-5">

			<div class="d-flex align-items-center justify-content-between mb-2">
				<?php if ( $category_name ) : ?>
					<span class="post-category text-ash mb-0"><?php echo esc_html( $category_name ); ?></span>
				<?php endif; ?>
				<?php if ( $rating_word ) : ?>
					<span class="ratings <?php echo esc_attr( $rating_word ); ?>"></span>
				<?php endif; ?>
			</div>

			<h2 class="post-title h5 mb-2">
				<a href="<?php echo esc_url( $product_url ); ?>" class="link-dark">
					<?php echo esc_html( $product->get_name() ); ?>
				</a>
			</h2>

			<p class="price mb-4"><?php echo $product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>

			<div class="mt-auto">
				<?php if ( $is_simple ) : ?>
					<a href="<?php echo esc_url( $add_to_cart_url ); ?>"
					   class="btn btn-primary w-100 has-ripple ajax_add_to_cart<?php echo esc_attr( $btn_style ); ?>"
					   data-product_id="<?php echo esc_attr( $product_id ); ?>"
					   data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
					   data-quantity="1"
					   rel="nofollow">
						<?php echo esc_html( $add_to_cart_text ); ?>
					</a>
				<?php else : ?>
					<a href="<?php echo esc_url( $product_url ); ?>"
					   class="btn btn-outline-primary w-100 has-ripple<?php echo esc_attr( $btn_style ); ?>">
						<?php echo esc_html( $add_to_cart_text ); ?>
					</a>
				<?php endif; ?>
			</div>

		</div>
		<!-- /.card-body -->

	</div>
	<!-- /.card -->
</div>
<!-- /.item -->

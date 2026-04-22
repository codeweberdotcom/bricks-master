<?php
/**
 * Product Card: shop-list
 *
 * Горизонтальная карточка: фото слева (1/3), контент справа (2/3).
 * На мобильном стакается вертикально через .card-horizontal CSS.
 * Иконки wishlist / quick view / compare появляются при hover на фото.
 *
 * @package Codeweber
 */

defined( 'ABSPATH' ) || exit;

require __DIR__ . '/_common.php';

if ( ! isset( $product_id ) ) {
	return;
}

$figure_radius = $card_radius && $card_radius !== 'rounded-0' ? ' rounded-start' : ( $card_radius ? ' ' . trim( $card_radius ) : '' );
?>
<div id="product-<?php echo esc_attr( $product_id ); ?>" class="project item <?php echo esc_attr( $cw_col ); ?>"<?php echo $cw_wl_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="card card-horizontal<?php echo $card_radius ? ' ' . esc_attr( $card_radius ) : ''; ?>">

		<figure class="card-img position-relative text-reset<?php echo $figure_radius ? ' ' . esc_attr( trim( $figure_radius ) ) : ''; ?>">
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

		<div class="card-body p-5 d-flex flex-column">

			<?php if ( $show_category || $show_rating ) : ?>
			<div class="d-flex align-items-center justify-content-between mb-2">
				<?php if ( $show_category && $category_name ) : ?>
					<span class="post-category text-ash mb-0"><?php echo esc_html( $category_name ); ?></span>
				<?php endif; ?>
				<?php if ( $show_rating && $rating_word ) : ?>
					<span class="ratings <?php echo esc_attr( $rating_word ); ?>"></span>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<h2 class="post-title h4 mb-2">
				<a href="<?php echo esc_url( $product_url ); ?>" class="link-dark text-dark">
					<?php echo esc_html( $product->get_name() ); ?>
				</a>
			</h2>

			<?php
			$excerpt = $product->get_short_description();
			if ( $excerpt ) :
			?>
				<p class="mb-3 text-muted fs-md line-clamp-2"><?php echo wp_kses_post( wp_trim_words( wp_strip_all_tags( $excerpt ), 20 ) ); ?></p>
			<?php endif; ?>

			<?php if ( $show_price ) : ?>
			<p class="price mb-4"><?php echo $product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
			<?php endif; ?>

			<?php if ( $show_cart ) : ?>
			<div class="mt-auto">
				<?php if ( $is_simple ) : ?>
					<a href="<?php echo esc_url( $add_to_cart_url ); ?>"
					   class="btn btn-primary btn-sm btn-icon btn-icon-start has-ripple ajax_add_to_cart<?php echo esc_attr( $btn_style ); ?>"
					   data-product_id="<?php echo esc_attr( $product_id ); ?>"
					   data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
					   data-quantity="1"
					   rel="nofollow">
						<i class="uil uil-shopping-bag"></i>
						<?php echo esc_html( $add_to_cart_text ); ?>
					</a>
				<?php else : ?>
					<a href="<?php echo esc_url( $product_url ); ?>"
					   class="btn btn-primary btn-sm btn-icon btn-icon-start has-ripple<?php echo esc_attr( $btn_style ); ?>">
						<i class="uil uil-arrow-right"></i>
						<?php echo esc_html( $add_to_cart_text ); ?>
					</a>
				<?php endif; ?>
			</div>
			<?php endif; ?>

		</div>
		<!-- /.card-body -->

	</div>
	<!-- /.card -->
</div>
<!-- /.item -->

<?php
/**
 * Quick View Modal Content
 *
 * Шаблон содержимого модала Quick View.
 * Вызывается из cw_quick_view_handler() через get_template_part().
 *
 * Доступно: global $post, $product (установлено в обработчике).
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product || ! $product->is_visible() ) {
	return;
}

$product_id  = $product->get_id();
$product_url = get_permalink( $product_id );

// Галерея: главное фото + дополнительные
$main_image_id = $product->get_image_id();
$gallery_ids   = $product->get_gallery_image_ids();
$all_image_ids = array_merge(
	$main_image_id ? array( $main_image_id ) : array(),
	$gallery_ids
);
$has_gallery   = count( $all_image_ids ) > 1;
?>

<div class="row g-0">

	<?php /* ── Галерея ─────────────────────────────── */ ?>
	<div class="col-md-6 bg-light">

		<?php if ( ! empty( $all_image_ids ) ) : ?>

			<div class="swiper-container"
			     data-margin="0"
			     data-dots="<?php echo $has_gallery ? 'true' : 'false'; ?>"
			     data-nav="<?php echo $has_gallery ? 'true' : 'false'; ?>">

				<div class="swiper">
					<div class="swiper-wrapper">
						<?php foreach ( $all_image_ids as $img_id ) : ?>
							<div class="swiper-slide">
								<figure class="rounded m-0">
									<a href="<?php echo esc_url( $product_url ); ?>">
										<?php echo wp_get_attachment_image( $img_id, 'woocommerce_single', false, array( 'class' => 'w-100' ) ); ?>
									</a>
								</figure>
							</div>
						<?php endforeach; ?>
					</div>
				</div>

			</div>

		<?php else : ?>
			<div class="d-flex align-items-center justify-content-center" style="min-height:300px;">
				<?php echo wc_placeholder_img( 'woocommerce_single' ); ?>
			</div>
		<?php endif; ?>

	</div>
	<?php /* ── /Галерея ────────────────────────────── */ ?>

	<?php /* ── Summary ─────────────────────────────── */ ?>
	<div class="col-md-6 p-4 p-lg-5">

		<?php
		/**
		 * woocommerce_single_product_summary hook.
		 *
		 * @hooked woocommerce_template_single_title       – 5
		 * @hooked woocommerce_template_single_rating      – 10
		 * @hooked woocommerce_template_single_price       – 10
		 * @hooked woocommerce_template_single_excerpt     – 20
		 * @hooked woocommerce_template_single_add_to_cart – 30
		 * @hooked woocommerce_template_single_meta        – 40
		 */
		do_action( 'woocommerce_single_product_summary' );
		?>

		<a href="<?php echo esc_url( $product_url ); ?>" class="btn btn-soft-primary btn-sm mt-3">
			<?php esc_html_e( 'View full details', 'codeweber' ); ?>
			<i class="uil uil-arrow-right ms-1"></i>
		</a>

	</div>
	<?php /* ── /Summary ────────────────────────────── */ ?>

</div>

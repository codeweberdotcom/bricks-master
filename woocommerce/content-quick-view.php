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

$carousel_id = 'cw-qv-carousel-' . $product_id;
?>

<div class="row g-0">

	<?php /* ── Галерея ─────────────────────────────── */ ?>
	<div class="col-md-6 bg-light">

		<?php if ( ! empty( $all_image_ids ) ) : ?>

			<div id="<?php echo esc_attr( $carousel_id ); ?>" class="carousel slide" data-bs-ride="false">
				<div class="carousel-inner">
					<?php foreach ( $all_image_ids as $i => $img_id ) : ?>
						<div class="carousel-item<?php echo 0 === $i ? ' active' : ''; ?>">
							<a href="<?php echo esc_url( $product_url ); ?>">
								<?php echo wp_get_attachment_image( $img_id, 'woocommerce_single', false, array( 'class' => 'd-block w-100' ) ); ?>
							</a>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<?php if ( $has_gallery ) : ?>
				<div class="d-flex gap-2 flex-wrap p-3 pt-0">
					<?php foreach ( $all_image_ids as $i => $img_id ) : ?>
						<button type="button"
						        class="cw-qv-thumb p-0 border-0 bg-transparent rounded overflow-hidden<?php echo 0 === $i ? ' active' : ''; ?>"
						        data-bs-target="#<?php echo esc_attr( $carousel_id ); ?>"
						        data-bs-slide-to="<?php echo esc_attr( $i ); ?>"
						        aria-label="<?php echo esc_attr( sprintf( __( 'Slide %d', 'codeweber' ), $i + 1 ) ); ?>">
							<?php echo wp_get_attachment_image( $img_id, 'thumbnail', false, array( 'class' => 'd-block' ) ); ?>
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

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

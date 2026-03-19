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
$main_image_id  = $product->get_image_id();
$gallery_ids    = $product->get_gallery_image_ids();
$all_image_ids  = array_merge(
	$main_image_id ? array( $main_image_id ) : array(),
	$gallery_ids
);
$has_gallery    = count( $all_image_ids ) > 1;

$carousel_id = 'cw-qv-carousel-' . $product_id;
?>

<div class="cw-qv-wrap row g-0">

	<?php /* ── Галерея ────────────────────────────────────────── */ ?>
	<div class="col-md-6 cw-qv-gallery-col">

		<?php if ( ! empty( $all_image_ids ) ) : ?>

			<div id="<?php echo esc_attr( $carousel_id ); ?>"
			     class="carousel slide cw-qv-carousel"
			     data-bs-ride="false">

				<div class="carousel-inner">
					<?php foreach ( $all_image_ids as $i => $img_id ) : ?>
						<div class="carousel-item<?php echo 0 === $i ? ' active' : ''; ?>">
							<a href="<?php echo esc_url( $product_url ); ?>">
								<?php echo wp_get_attachment_image( $img_id, 'woocommerce_single', false, array( 'class' => 'cw-qv-main-img d-block w-100' ) ); ?>
							</a>
						</div>
					<?php endforeach; ?>
				</div>

				<?php if ( $has_gallery ) : ?>
					<button class="carousel-control-prev" type="button"
					        data-bs-target="#<?php echo esc_attr( $carousel_id ); ?>"
					        data-bs-slide="prev">
						<span class="carousel-control-prev-icon" aria-hidden="true"></span>
						<span class="visually-hidden"><?php esc_html_e( 'Previous', 'codeweber' ); ?></span>
					</button>
					<button class="carousel-control-next" type="button"
					        data-bs-target="#<?php echo esc_attr( $carousel_id ); ?>"
					        data-bs-slide="next">
						<span class="carousel-control-next-icon" aria-hidden="true"></span>
						<span class="visually-hidden"><?php esc_html_e( 'Next', 'codeweber' ); ?></span>
					</button>
				<?php endif; ?>

			</div>

			<?php if ( $has_gallery ) : ?>
				<div class="cw-qv-thumbs d-flex gap-2 flex-wrap px-4 pb-4">
					<?php foreach ( $all_image_ids as $i => $img_id ) : ?>
						<button type="button"
						        class="cw-qv-thumb p-0 border-0 bg-transparent<?php echo 0 === $i ? ' active' : ''; ?>"
						        data-bs-target="#<?php echo esc_attr( $carousel_id ); ?>"
						        data-bs-slide-to="<?php echo esc_attr( $i ); ?>"
						        aria-label="<?php echo esc_attr( sprintf( __( 'Slide %d', 'codeweber' ), $i + 1 ) ); ?>">
							<?php echo wp_get_attachment_image( $img_id, 'thumbnail', false, array( 'class' => 'cw-qv-thumb-img' ) ); ?>
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

		<?php else : ?>
			<div class="cw-qv-no-image d-flex align-items-center justify-content-center bg-light" style="min-height:300px;">
				<?php echo wc_placeholder_img( 'woocommerce_single' ); ?>
			</div>
		<?php endif; ?>

	</div>
	<?php /* ── /Галерея ─────────────────────────────────────── */ ?>

	<?php /* ── Summary ─────────────────────────────────────── */ ?>
	<div class="col-md-6 cw-qv-summary-col">
		<div class="cw-qv-summary">

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
	</div>
	<?php /* ── /Summary ─────────────────────────────────────── */ ?>

</div>
<?php /* ── /.cw-qv-wrap ─────────────────────────────────────── */ ?>

<?php
/**
 * Compare Bar — inner content (заменяется через AJAX).
 *
 * Переменные: $compare_ids (int[]), $limit (int)
 * Передаются через get_template_part() args или get_template_part + extract.
 *
 * @package CodeWeber
 */

defined( 'ABSPATH' ) || exit;

// Поддержка передачи через args (WP 5.5+)
if ( ! isset( $compare_ids ) ) {
	$args        = $args ?? array();
	$compare_ids = $args['compare_ids'] ?? array();
	$limit       = $args['limit'] ?? 4;
}
$compare_ids = array_values( array_filter( array_map( 'absint', $compare_ids ) ) );
$limit       = max( 2, (int) $limit );
$count       = count( $compare_ids );
?>
<div class="cw-compare-bar-inner d-flex align-items-center gap-3 flex-wrap">

	<!-- Слоты -->
	<div class="cw-compare-slots d-flex align-items-center gap-2 flex-grow-1">

		<?php foreach ( $compare_ids as $pid ) :
			$product = wc_get_product( $pid );
			if ( ! $product ) {
				continue;
			}
			$img_id  = $product->get_image_id();
			$img_src = $img_id
				? wp_get_attachment_image_url( $img_id, array( 64, 64 ) )
				: wc_placeholder_img_src( array( 64, 64 ) );
			$name    = $product->get_name();
			if ( $product->is_type( 'variation' ) ) {
				$parent = wc_get_product( $product->get_parent_id() );
				$name   = $parent ? $parent->get_name() : $name;
			}
			?>
			<div class="cw-compare-slot position-relative rounded border bg-light"
				data-product-id="<?php echo esc_attr( $pid ); ?>">
				<img src="<?php echo esc_url( $img_src ); ?>"
					alt="<?php echo esc_attr( $name ); ?>"
					width="56" height="56"
					loading="lazy"
					class="rounded">
				<button
					type="button"
					class="cw-compare-slot-remove position-absolute top-0 end-0 btn btn-link p-0 lh-1"
					data-product-id="<?php echo esc_attr( $pid ); ?>"
					aria-label="<?php esc_attr_e( 'Убрать из сравнения', 'codeweber' ); ?>"
					title="<?php esc_attr_e( 'Убрать', 'codeweber' ); ?>"
					style="width:18px;height:18px;top:-6px;right:-6px;background:rgba(0,0,0,.6);border-radius:50%;color:#fff;font-size:10px;display:flex;align-items:center;justify-content:center;">
					<i class="uil uil-times" aria-hidden="true"></i>
				</button>
			</div>
		<?php endforeach; ?>

		<?php for ( $i = $count; $i < $limit; $i++ ) : ?>
			<div class="cw-compare-slot cw-compare-slot--empty rounded border d-flex align-items-center justify-content-center text-muted"
				style="width:56px;height:56px;border-style:dashed !important;opacity:.45;">
				<i class="uil uil-plus" aria-hidden="true"></i>
			</div>
		<?php endfor; ?>

	</div>
	<!-- /.cw-compare-slots -->

	<!-- Действия -->
	<div class="cw-compare-actions d-flex gap-2 align-items-center flex-shrink-0">
		<a href="<?php echo esc_url( cw_get_compare_url() ); ?>"
			class="btn btn-primary btn-sm has-ripple">
			<?php esc_html_e( 'Сравнить', 'codeweber' ); ?>
			<span class="badge bg-white text-primary ms-1"><?php echo esc_html( $count ); ?></span>
		</a>
		<button type="button" class="cw-compare-clear btn btn-outline-secondary btn-sm">
			<?php esc_html_e( 'Очистить', 'codeweber' ); ?>
		</button>
	</div>

</div>
<!-- /.cw-compare-bar-inner -->

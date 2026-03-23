<?php
/**
 * Compare Bar — inner content (replaced via AJAX).
 *
 * Variables: $compare_ids (int[]), $limit (int)
 * Passed via get_template_part() args (WP 5.5+).
 *
 * @package CodeWeber
 */

defined( 'ABSPATH' ) || exit;

// Support args passed via get_template_part() (WP 5.5+).
if ( ! isset( $compare_ids ) ) {
	$args        = $args ?? array();
	$compare_ids = $args['compare_ids'] ?? array();
	$limit       = $args['limit'] ?? 4;
}
$compare_ids = array_values( array_filter( array_map( 'absint', $compare_ids ) ) );
$limit       = max( 2, (int) $limit );
$count       = count( $compare_ids );

$btn_style    = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button' ) : '';
$card_radius  = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : 'rounded';
?>
<div class="cw-compare-bar-inner w-100 d-flex align-items-center gap-2 gap-sm-3 flex-wrap">

	<!-- Slots -->
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
			<div class="cw-compare-slot position-relative flex-shrink-0 border bg-light <?php echo esc_attr( $card_radius ); ?>"
				data-product-id="<?php echo esc_attr( $pid ); ?>">
				<img src="<?php echo esc_url( $img_src ); ?>"
					alt="<?php echo esc_attr( $name ); ?>"
					width="56" height="56"
					loading="lazy"
					class="d-block w-100 h-100 <?php echo esc_attr( $card_radius ); ?>">
				<button
					type="button"
					class="cw-compare-slot-remove position-absolute d-flex align-items-center justify-content-center rounded-circle text-white btn btn-link p-0 lh-1"
					data-product-id="<?php echo esc_attr( $pid ); ?>"
					aria-label="<?php esc_attr_e( 'Remove from compare', 'codeweber' ); ?>"
					title="<?php esc_attr_e( 'Remove', 'codeweber' ); ?>">
					<i class="uil uil-times" aria-hidden="true"></i>
				</button>
			</div>
		<?php endforeach; ?>

		<?php for ( $i = $count; $i < $limit; $i++ ) : ?>
			<div class="cw-compare-slot cw-compare-slot--empty border d-flex align-items-center justify-content-center text-muted <?php echo esc_attr( $card_radius ); ?>">
				<i class="uil uil-plus" aria-hidden="true"></i>
			</div>
		<?php endfor; ?>

	</div>
	<!-- /.cw-compare-slots -->

	<!-- Actions -->
	<div class="cw-compare-actions d-flex gap-2 align-items-center flex-shrink-0">
		<a href="<?php echo esc_url( cw_get_compare_url() ); ?>"
			class="btn btn-primary btn-sm has-ripple <?php echo esc_attr( $btn_style ); ?>">
			<i class="uil uil-exchange" aria-hidden="true"></i>
			<span class="d-none d-sm-inline ms-1"><?php esc_html_e( 'Compare', 'codeweber' ); ?></span>
			<span class="badge bg-white text-primary ms-1"><?php echo esc_html( $count ); ?></span>
		</a>
		<button type="button" class="cw-compare-clear btn btn-outline-danger btn-sm <?php echo esc_attr( $btn_style ); ?>">
			<i class="uil uil-trash-alt me-1" aria-hidden="true"></i>
			<?php esc_html_e( 'Clear', 'codeweber' ); ?>
		</button>
	</div>

</div>
<!-- /.cw-compare-bar-inner -->

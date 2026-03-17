<?php
/**
 * Price range slider filter.
 *
 * Uses two native <input type="range"> elements with CSS track overlay.
 * JS (shop-pjax.js) initialises the slider and updates the Apply button href.
 *
 * @package CodeWeber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$range = cw_get_price_filter_range();
if ( (int) $range['min'] === (int) $range['max'] ) {
	return; // nothing to filter
}

// phpcs:disable WordPress.Security.NonceVerification
$current_min = isset( $_GET['min_price'] ) ? (int) $_GET['min_price'] : $range['min'];
$current_max = isset( $_GET['max_price'] ) ? (int) $_GET['max_price'] : $range['max'];
// phpcs:enable

// Clamp to valid range
$current_min = max( $range['min'], min( $current_max, $current_min ) );
$current_max = min( $range['max'], max( $current_min, $current_max ) );

$apply_url = cw_get_price_filter_url( $current_min, $current_max );
?>

<div class="cw-filter-price"
	data-min="<?php echo esc_attr( $range['min'] ); ?>"
	data-max="<?php echo esc_attr( $range['max'] ); ?>">

	<div class="cw-price-slider-wrap">
		<div class="cw-price-track">
			<div class="cw-price-range" id="cw-price-range"></div>
		</div>

		<input
			type="range"
			class="cw-range cw-range-min"
			min="<?php echo esc_attr( $range['min'] ); ?>"
			max="<?php echo esc_attr( $range['max'] ); ?>"
			value="<?php echo esc_attr( $current_min ); ?>"
			step="1"
			aria-label="<?php esc_attr_e( 'Минимальная цена', 'codeweber' ); ?>">

		<input
			type="range"
			class="cw-range cw-range-max"
			min="<?php echo esc_attr( $range['min'] ); ?>"
			max="<?php echo esc_attr( $range['max'] ); ?>"
			value="<?php echo esc_attr( $current_max ); ?>"
			step="1"
			aria-label="<?php esc_attr_e( 'Максимальная цена', 'codeweber' ); ?>">
	</div>

	<div class="cw-price-inputs d-flex align-items-center gap-2 mt-2">
		<span class="cw-price-display cw-price-display--min">
			<?php echo wp_kses_post( wc_price( $current_min ) ); ?>
		</span>
		<span class="text-muted">—</span>
		<span class="cw-price-display cw-price-display--max">
			<?php echo wp_kses_post( wc_price( $current_max ) ); ?>
		</span>
	</div>

	<a href="<?php echo esc_url( $apply_url ); ?>"
		class="btn btn-sm btn-primary pjax-link cw-price-apply mt-3 w-100"
		data-base-url="<?php echo esc_url( cw_filter_base_url() ); ?>">
		<?php esc_html_e( 'Применить', 'codeweber' ); ?>
	</a>

</div><!-- .cw-filter-price -->

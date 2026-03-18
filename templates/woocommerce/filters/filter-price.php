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

?>

<?php
$slider_thumb_px   = isset( $slider_size_px ) ? (int) $slider_size_px : 18;
$slider_size_style = ( 18 !== $slider_thumb_px ) ? ' style="--cw-thumb-size:' . $slider_thumb_px . 'px"' : '';
?>
<div class="cw-filter-price"
	data-min="<?php echo esc_attr( $range['min'] ); ?>"
	data-max="<?php echo esc_attr( $range['max'] ); ?>"<?php echo $slider_size_style; // phpcs:ignore WordPress.Security.EscapeOutput ?>>

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
		<input type="number"
			class="form-control form-control-sm cw-price-input cw-price-input--min"
			min="<?php echo esc_attr( $range['min'] ); ?>"
			max="<?php echo esc_attr( $range['max'] ); ?>"
			value="<?php echo esc_attr( $current_min ); ?>"
			step="1"
			aria-label="<?php esc_attr_e( 'Цена от', 'codeweber' ); ?>">
		<span class="text-muted flex-shrink-0">—</span>
		<input type="number"
			class="form-control form-control-sm cw-price-input cw-price-input--max"
			min="<?php echo esc_attr( $range['min'] ); ?>"
			max="<?php echo esc_attr( $range['max'] ); ?>"
			value="<?php echo esc_attr( $current_max ); ?>"
			step="1"
			aria-label="<?php esc_attr_e( 'Цена до', 'codeweber' ); ?>">
	</div>

</div><!-- .cw-filter-price -->

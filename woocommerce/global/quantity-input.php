<?php
/**
 * Product quantity input
 *
 * Переопределяет global/quantity-input.php.
 * cw-qty-v — вертикальные кнопки +/−.
 *
 * @version 10.1.0
 */

defined( 'ABSPATH' ) || exit;

/* translators: %s: Quantity. */
$label = ! empty( $args['product_name'] )
	? sprintf( esc_html__( '%s quantity', 'woocommerce' ), wp_strip_all_tags( $args['product_name'] ) )
	: esc_html__( 'Quantity', 'woocommerce' );

$classes[] = 'form-control';
$classes[] = 'form-control-sm';
$classes[] = 'text-center';

$btn_style      = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button' ) : '';
$qty_extra_class = ( trim( $btn_style ) === 'rounded-0' ) ? ' rounded-0' : '';
?>

<div class="quantity cw-qty-v<?php echo esc_attr( $qty_extra_class ); ?>">
	<?php do_action( 'woocommerce_before_quantity_input_field' ); ?>

	<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>">
		<?php echo esc_attr( $label ); ?>
	</label>

	<input
		type="text"
		<?php echo $readonly ? 'readonly="readonly"' : ''; ?>
		id="<?php echo esc_attr( $input_id ); ?>"
		class="<?php echo esc_attr( implode( ' ', (array) $classes ) ); ?>"
		name="<?php echo esc_attr( $input_name ); ?>"
		value="<?php echo esc_attr( $input_value ); ?>"
		aria-label="<?php esc_attr_e( 'Product quantity', 'woocommerce' ); ?>"
		data-min="<?php echo esc_attr( $min_value ); ?>"
		<?php if ( 0 < $max_value ) : ?>
			data-max="<?php echo esc_attr( $max_value ); ?>"
		<?php endif; ?>
		<?php if ( ! $readonly ) : ?>
			inputmode="numeric"
			pattern="[0-9]*"
			placeholder="<?php echo esc_attr( $placeholder ); ?>"
			autocomplete="<?php echo esc_attr( isset( $autocomplete ) ? $autocomplete : 'on' ); ?>"
		<?php endif; ?>
		style="width:3rem"
	/>

	<?php if ( ! $readonly ) : ?>
	<div class="cw-qty-v__btns">
		<button class="btn btn-outline-secondary" type="button" data-qty-inc
			aria-label="<?php esc_attr_e( 'Increase quantity', 'woocommerce' ); ?>">
			<i class="uil uil-plus"></i>
		</button>
		<button class="btn btn-outline-secondary" type="button" data-qty-dec
			aria-label="<?php esc_attr_e( 'Decrease quantity', 'woocommerce' ); ?>">
			<i class="uil uil-minus"></i>
		</button>
	</div>
	<?php endif; ?>

	<?php do_action( 'woocommerce_after_quantity_input_field' ); ?>
</div>

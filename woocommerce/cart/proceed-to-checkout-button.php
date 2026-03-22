<?php
/**
 * Proceed to checkout button
 *
 * Переопределяет woocommerce/cart/proceed-to-checkout-button.php.
 *
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

$btn_shape = class_exists( 'Codeweber_Options' ) ? ' ' . esc_attr( trim( Codeweber_Options::style( 'button' ) ) ) : ' rounded';
?>

<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="checkout-button btn btn-primary<?php echo $btn_shape; ?> w-100 wc-forward has-ripple">
	<?php esc_html_e( 'Перейти к оформлению', 'codeweber' ); ?>
</a>

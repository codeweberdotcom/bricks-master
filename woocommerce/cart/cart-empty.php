<?php
/**
 * Empty cart page
 *
 * Переопределяет woocommerce/cart/cart-empty.php.
 *
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

/*
 * @hooked wc_empty_cart_message - 10
 */
do_action( 'woocommerce_cart_is_empty' );

if ( wc_get_page_id( 'shop' ) > 0 ) : ?>
	<p class="return-to-shop text-center py-8">
		<a class="btn btn-primary<?php echo class_exists( 'Codeweber_Options' ) ? ' ' . esc_attr( trim( Codeweber_Options::style( 'button' ) ) ) : ' rounded'; ?> wc-backward has-ripple" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
			<?php echo esc_html( apply_filters( 'woocommerce_return_to_shop_text', __( 'Return to shop', 'woocommerce' ) ) ); ?>
		</a>
	</p>
<?php endif; ?>

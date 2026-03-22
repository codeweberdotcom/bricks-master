<?php
/**
 * Checkout Form
 *
 * Переопределяет woocommerce/checkout/form-checkout.php.
 * Двухколоночный Bootstrap-макет: поля покупателя (col-lg-8) + сводка заказа (col-lg-4).
 *
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}
?>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">

	<div class="row gx-md-8 gx-xl-12 gy-12">

		<div class="col-lg-8">
			<?php if ( $checkout->get_checkout_fields() ) : ?>

				<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

				<?php do_action( 'woocommerce_checkout_billing' ); ?>
				<?php do_action( 'woocommerce_checkout_shipping' ); ?>

				<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

			<?php endif; ?>
		</div>
		<!-- /col-lg-8 -->

		<div class="col-lg-4">
			<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>

			<h3 class="mb-4"><?php esc_html_e( 'Ваш заказ', 'codeweber' ); ?></h3>

			<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

			<div id="order_review" class="woocommerce-checkout-review-order">
				<?php do_action( 'woocommerce_checkout_order_review' ); ?>
			</div>

			<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
		</div>
		<!-- /col-lg-4 -->

	</div>
	<!-- /.row -->

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>

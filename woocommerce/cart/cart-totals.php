<?php
/**
 * Cart totals — Order Summary
 *
 * Переопределяет woocommerce/cart/cart-totals.php.
 *
 * @version 2.3.6
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="cart_totals <?php echo WC()->customer->has_calculated_shipping() ? 'calculated_shipping' : ''; ?>">

	<?php do_action( 'woocommerce_before_cart_totals' ); ?>

	<h3 class="mb-4"><?php esc_html_e( 'Ваш заказ', 'codeweber' ); ?></h3>

	<div class="table-responsive">
		<table class="table table-order">
			<tbody>
				<tr class="cart-subtotal">
					<td class="ps-0"><strong class="text-dark"><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></strong></td>
					<td class="pe-0 text-end"><p class="price mb-0"><?php wc_cart_totals_subtotal_html(); ?></p></td>
				</tr>

				<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
				<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
					<td class="ps-0"><strong class="text-dark"><?php wc_cart_totals_coupon_label( $coupon ); ?></strong></td>
					<td class="pe-0 text-end"><p class="price text-red mb-0"><?php wc_cart_totals_coupon_html( $coupon ); ?></p></td>
				</tr>
				<?php endforeach; ?>

				<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
					<?php do_action( 'woocommerce_cart_totals_before_shipping' ); ?>
					<?php wc_cart_totals_shipping_html(); ?>
					<?php do_action( 'woocommerce_cart_totals_after_shipping' ); ?>

				<?php elseif ( WC()->cart->needs_shipping() && 'yes' === get_option( 'woocommerce_enable_shipping_calc' ) ) : ?>
				<tr class="shipping">
					<td class="ps-0"><strong class="text-dark"><?php esc_html_e( 'Shipping', 'woocommerce' ); ?></strong></td>
					<td class="pe-0 text-end"><?php woocommerce_shipping_calculator(); ?></td>
				</tr>
				<?php endif; ?>

				<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
				<tr class="fee">
					<td class="ps-0"><strong class="text-dark"><?php echo esc_html( $fee->name ); ?></strong></td>
					<td class="pe-0 text-end"><p class="price mb-0"><?php wc_cart_totals_fee_html( $fee ); ?></p></td>
				</tr>
				<?php endforeach; ?>

				<?php
				if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) {
					$taxable_address = WC()->customer->get_taxable_address();
					$estimated_text  = '';

					if ( WC()->customer->is_customer_outside_base() && ! WC()->customer->has_calculated_shipping() ) {
						/* translators: %s location. */
						$estimated_text = sprintf( ' <small>' . esc_html__( '(estimated for %s)', 'woocommerce' ) . '</small>', WC()->countries->estimated_for_prefix( $taxable_address[0] ) . WC()->countries->countries[ $taxable_address[0] ] );
					}

					if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
						foreach ( WC()->cart->get_tax_totals() as $code => $tax ) {
							?>
							<tr class="tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
								<td class="ps-0"><strong class="text-dark"><?php echo esc_html( $tax->label ) . $estimated_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong></td>
								<td class="pe-0 text-end"><p class="price mb-0"><?php echo wp_kses_post( $tax->formatted_amount ); ?></p></td>
							</tr>
							<?php
						}
					} else {
						?>
						<tr class="tax-total">
							<td class="ps-0"><strong class="text-dark"><?php echo esc_html( WC()->countries->tax_or_vat() ) . $estimated_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong></td>
							<td class="pe-0 text-end"><p class="price mb-0"><?php wc_cart_totals_taxes_total_html(); ?></p></td>
						</tr>
						<?php
					}
				}
				?>

				<?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>

				<tr class="order-total">
					<td class="ps-0"><strong class="text-dark"><?php esc_html_e( 'Итого', 'codeweber' ); ?></strong></td>
					<td class="pe-0 text-end"><p class="price text-dark fw-bold mb-0"><?php wc_cart_totals_order_total_html(); ?></p></td>
				</tr>

				<?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>
			</tbody>
		</table>
	</div>

	<div class="mt-4">
		<?php do_action( 'woocommerce_proceed_to_checkout' ); ?>
	</div>

	<?php do_action( 'woocommerce_after_cart_totals' ); ?>

</div>

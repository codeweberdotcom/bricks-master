<?php
/**
 * Review order table — Checkout Order Summary
 *
 * Переопределяет woocommerce/checkout/review-order.php.
 * Список товаров в виде карточек + итоговая таблица .table-order.
 *
 * @version 5.2.0
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="shopping-cart mb-7">
	<?php do_action( 'woocommerce_review_order_before_cart_contents' ); ?>

	<?php foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
		$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

		if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) :
			$product_permalink = $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '';
	?>
	<div class="shopping-cart-item d-flex justify-content-between mb-4 <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
		<div class="d-flex flex-row align-items-center">
			<figure class="rounded w-17">
				<?php
				$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
				if ( $product_permalink ) {
					echo '<a href="' . esc_url( $product_permalink ) . '">' . $thumbnail . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				} else {
					echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				?>
			</figure>
			<div class="w-100 ms-4">
				<h3 class="post-title h6 lh-xs mb-1">
					<?php if ( $product_permalink ) : ?>
						<a href="<?php echo esc_url( $product_permalink ); ?>" class="link-dark">
							<?php echo wp_kses_post( $_product->get_name() ); ?>
						</a>
					<?php else : ?>
						<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ); ?>
					<?php endif; ?>
				</h3>
				<?php echo apply_filters( 'woocommerce_checkout_cart_item_quantity', '<span class="text-muted small">&times;&nbsp;' . $cart_item['quantity'] . '</span>', $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
		<div class="ms-2 d-flex align-items-center flex-shrink-0">
			<p class="price fs-sm mb-0">
				<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</p>
		</div>
	</div>
	<?php endif; endforeach; ?>

	<?php do_action( 'woocommerce_review_order_after_cart_contents' ); ?>
</div>

<hr class="my-4">

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
				<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>
				<?php wc_cart_totals_shipping_html(); ?>
				<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>
			<?php endif; ?>

			<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
			<tr class="fee">
				<td class="ps-0"><strong class="text-dark"><?php echo esc_html( $fee->name ); ?></strong></td>
				<td class="pe-0 text-end"><p class="price mb-0"><?php wc_cart_totals_fee_html( $fee ); ?></p></td>
			</tr>
			<?php endforeach; ?>

			<?php
			if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) {
				if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
					foreach ( WC()->cart->get_tax_totals() as $code => $tax ) {
						?>
						<tr class="tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
							<td class="ps-0"><strong class="text-dark"><?php echo esc_html( $tax->label ); ?></strong></td>
							<td class="pe-0 text-end"><p class="price mb-0"><?php echo wp_kses_post( $tax->formatted_amount ); ?></p></td>
						</tr>
						<?php
					}
				} else {
					?>
					<tr class="tax-total">
						<td class="ps-0"><strong class="text-dark"><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></strong></td>
						<td class="pe-0 text-end"><p class="price mb-0"><?php wc_cart_totals_taxes_total_html(); ?></p></td>
					</tr>
					<?php
				}
			}
			?>

			<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

			<tr class="order-total">
				<td class="ps-0"><strong class="text-dark"><?php esc_html_e( 'Итого', 'codeweber' ); ?></strong></td>
				<td class="pe-0 text-end"><p class="price text-dark fw-bold mb-0"><?php wc_cart_totals_order_total_html(); ?></p></td>
			</tr>

			<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>
		</tbody>
	</table>
</div>

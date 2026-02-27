<?php
/**
 * Order Customer Details (theme override: h3 mb-3).
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.7.0
 */

defined( 'ABSPATH' ) || exit;

$show_shipping = ! wc_ship_to_billing_address_only() && $order->needs_shipping_address();
?>
<section class="woocommerce-customer-details">

	<?php if ( $show_shipping ) : ?>

	<section class="woocommerce-columns woocommerce-columns--2 woocommerce-columns--addresses col2-set addresses">
		<div class="woocommerce-column woocommerce-column--1 woocommerce-column--billing-address col-1">

	<?php endif; ?>

	<h3 class="woocommerce-column__title mb-3"><?php esc_html_e( 'Billing address', 'woocommerce' ); ?></h3>

	<div class="woocommerce-customer-details__address">
		<?php
		$countries = WC()->countries->get_countries();
		$bill_cc   = $order->get_billing_country();
		$bill_country_name = $bill_cc && isset( $countries[ $bill_cc ] ) ? $countries[ $bill_cc ] : $bill_cc;
		$billing_fields = array(
			'name'     => array( 'label' => __( 'Name', 'woocommerce' ), 'value' => trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ) ),
			'company'  => array( 'label' => __( 'Company', 'woocommerce' ), 'value' => $order->get_billing_company() ),
			'address'  => array( 'label' => __( 'Address', 'woocommerce' ), 'value' => $order->get_billing_address_1() ),
			'address_2' => array( 'label' => __( 'Apartment, suite, etc.', 'woocommerce' ), 'value' => $order->get_billing_address_2() ),
			'city'     => array( 'label' => __( 'City', 'woocommerce' ), 'value' => $order->get_billing_city() ),
			'state'    => array( 'label' => __( 'State / County', 'woocommerce' ), 'value' => $order->get_billing_state() ),
			'postcode' => array( 'label' => __( 'Postcode / ZIP', 'woocommerce' ), 'value' => $order->get_billing_postcode() ),
			'country'  => array( 'label' => __( 'Country', 'woocommerce' ), 'value' => $bill_country_name ),
			'phone'    => array( 'label' => __( 'Phone', 'woocommerce' ), 'value' => $order->get_billing_phone() ),
			'email'    => array( 'label' => __( 'Email', 'woocommerce' ), 'value' => $order->get_billing_email() ),
		);
		?>
		<table class="woocommerce-table woocommerce-table--customer-details shop_table customer_details">
			<tbody>
				<?php foreach ( $billing_fields as $key => $field ) : ?>
					<?php if ( ! empty( $field['value'] ) ) : ?>
						<?php $cell_class = ( $key === 'phone' ) ? 'tel' : ( ( $key === 'email' ) ? 'email-address' : $key ); ?>
						<tr>
							<th scope="row" class="woocommerce-customer-details--<?php echo esc_attr( $cell_class ); ?>"><?php echo esc_html( $field['label'] ); ?></th>
							<td class="woocommerce-customer-details--<?php echo esc_attr( $cell_class ); ?>"><?php echo esc_html( $field['value'] ); ?></td>
						</tr>
					<?php endif; ?>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php do_action( 'woocommerce_order_details_after_customer_address', 'billing', $order ); ?>
	</div>

	<?php if ( $show_shipping ) : ?>

		</div><!-- /.col-1 -->

		<div class="woocommerce-column woocommerce-column--2 woocommerce-column--shipping-address col-2">
			<h3 class="woocommerce-column__title mb-3"><?php esc_html_e( 'Shipping address', 'woocommerce' ); ?></h3>
			<div class="woocommerce-customer-details__address">
				<?php
				$shipping_country_code = $order->get_shipping_country();
				$ship_country_name     = $shipping_country_code && isset( $countries[ $shipping_country_code ] ) ? $countries[ $shipping_country_code ] : $shipping_country_code;
				$shipping_fields       = array(
					'name'     => array( 'label' => __( 'Name', 'woocommerce' ), 'value' => trim( $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name() ) ),
					'company'  => array( 'label' => __( 'Company', 'woocommerce' ), 'value' => $order->get_shipping_company() ),
					'address'  => array( 'label' => __( 'Address', 'woocommerce' ), 'value' => $order->get_shipping_address_1() ),
					'address_2' => array( 'label' => __( 'Apartment, suite, etc.', 'woocommerce' ), 'value' => $order->get_shipping_address_2() ),
					'city'     => array( 'label' => __( 'City', 'woocommerce' ), 'value' => $order->get_shipping_city() ),
					'state'    => array( 'label' => __( 'State / County', 'woocommerce' ), 'value' => $order->get_shipping_state() ),
					'postcode' => array( 'label' => __( 'Postcode / ZIP', 'woocommerce' ), 'value' => $order->get_shipping_postcode() ),
					'country'  => array( 'label' => __( 'Country', 'woocommerce' ), 'value' => $ship_country_name ),
					'phone'    => array( 'label' => __( 'Phone', 'woocommerce' ), 'value' => $order->get_shipping_phone() ),
				);
				?>
				<table class="woocommerce-table woocommerce-table--customer-details shop_table customer_details">
					<tbody>
						<?php foreach ( $shipping_fields as $key => $field ) : ?>
							<?php if ( ! empty( $field['value'] ) ) : ?>
								<?php $cell_class = ( $key === 'phone' ) ? 'tel' : ( ( $key === 'email' ) ? 'email-address' : $key ); ?>
								<tr>
									<th scope="row" class="woocommerce-customer-details--<?php echo esc_attr( $cell_class ); ?>"><?php echo esc_html( $field['label'] ); ?></th>
									<td class="woocommerce-customer-details--<?php echo esc_attr( $cell_class ); ?>"><?php echo esc_html( $field['value'] ); ?></td>
								</tr>
							<?php endif; ?>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php do_action( 'woocommerce_order_details_after_customer_address', 'shipping', $order ); ?>
			</div>
		</div><!-- /.col-2 -->

	</section><!-- /.col2-set -->

	<?php endif; ?>

	<?php do_action( 'woocommerce_order_details_after_customer_details', $order ); ?>

</section>

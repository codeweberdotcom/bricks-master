<?php
/**
 * Payment methods
 *
 * Shows customer payment methods on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/payment-methods.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.9.0
 */

defined( 'ABSPATH' ) || exit;

$saved_methods = wc_get_customer_saved_methods_list( get_current_user_id() );
$has_methods   = (bool) $saved_methods;
$types         = wc_get_account_payment_methods_types();

do_action( 'woocommerce_before_account_payment_methods', $has_methods ); ?>

<?php if ( $has_methods ) : ?>

	<table class="woocommerce-MyAccount-paymentMethods shop_table shop_table_responsive account-payment-methods-table">
		<thead>
			<tr>
				<th class="woocommerce-PaymentMethod woocommerce-PaymentMethod--method payment-method-method"><span class="nobr"><?php esc_html_e( 'Method', 'woocommerce' ); ?></span></th>
				<th class="woocommerce-PaymentMethod woocommerce-PaymentMethod--expires payment-method-expires"><span class="nobr"><?php esc_html_e( 'Expires', 'woocommerce' ); ?></span></th>
				<th class="woocommerce-PaymentMethod woocommerce-PaymentMethod--actions payment-method-actions"><span class="nobr">&nbsp;</span></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ( $saved_methods as $type => $methods ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
			<?php foreach ( $methods as $method ) : ?>
				<tr class="payment-method<?php echo ! empty( $method['is_default'] ) ? ' default-payment-method' : ''; ?>">
					<td class="woocommerce-PaymentMethod woocommerce-PaymentMethod--method payment-method-method" data-title="<?php esc_attr_e( 'Method', 'woocommerce' ); ?>">
						<?php
						if ( has_action( 'woocommerce_account_payment_methods_column_method' ) ) {
							do_action( 'woocommerce_account_payment_methods_column_method', $method );
						} elseif ( ! empty( $method['method']['last4'] ) ) {
							/* translators: 1: credit card type 2: last 4 digits */
							echo esc_html( sprintf( __( '%1$s ending in %2$s', 'woocommerce' ), wc_get_credit_card_type_label( $method['method']['brand'] ), $method['method']['last4'] ) );
						} else {
							echo esc_html( wc_get_credit_card_type_label( $method['method']['brand'] ) );
						}
						?>
					</td>
					<td class="woocommerce-PaymentMethod woocommerce-PaymentMethod--expires payment-method-expires" data-title="<?php esc_attr_e( 'Expires', 'woocommerce' ); ?>">
						<?php echo esc_html( $method['expires'] ); ?>
					</td>
					<td class="woocommerce-PaymentMethod woocommerce-PaymentMethod--actions payment-method-actions" data-title="&nbsp;">
						<?php
						$theme_btn = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style('button') : '';
						foreach ( $method['actions'] as $key => $action ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
							$btn_class = 'delete' === $key ? 'btn btn-outline-red btn-sm delete' : 'btn btn-outline-secondary btn-sm ' . sanitize_html_class( $key );
							echo '<a href="' . esc_url( $action['url'] ) . '" class="' . esc_attr( $btn_class . ( $theme_btn ? ' ' . trim( $theme_btn ) : '' ) ) . '">' . esc_html( $action['name'] ) . '</a>&nbsp;';
						}
						?>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php endforeach; ?>
		</tbody>
	</table>

<?php else : ?>

	<?php wc_print_notice( esc_html__( 'No saved methods found.', 'woocommerce' ), 'notice' ); ?>

<?php endif; ?>

<?php do_action( 'woocommerce_after_account_payment_methods', $has_methods ); ?>

<?php if ( WC()->payment_gateways->get_available_payment_gateways() ) : ?>
	<a href="<?php echo esc_url( wc_get_endpoint_url( 'add-payment-method' ) ); ?>" class="btn btn-primary btn-sm<?php echo class_exists( 'Codeweber_Options' ) ? ' ' . esc_attr( trim( Codeweber_Options::style('button') ) ) : ''; ?>"><?php esc_html_e( 'Add payment method', 'woocommerce' ); ?></a>
<?php endif; ?>

<?php
/**
 * Single variation cart button
 *
 * Переопределяет single-product/add-to-cart/variation-add-to-cart-button.php.
 * Bootstrap btn btn-primary + Unicons иконка.
 *
 * @version 10.5.2
 */

defined( 'ABSPATH' ) || exit;

global $product;

$btn_style = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button' ) : ' rounded';
?>

<div class="woocommerce-variation-add-to-cart variations_button">

	<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

	<div class="row">
		<div class="col-lg-9 d-flex flex-row pt-2 gap-2">

			<?php do_action( 'woocommerce_before_add_to_cart_quantity' ); ?>

			<?php
			woocommerce_quantity_input( [
				'min_value'   => $product->get_min_purchase_quantity(),
				'max_value'   => $product->get_max_purchase_quantity(),
				'input_value' => isset( $_POST['quantity'] ) // phpcs:ignore WordPress.Security.NonceVerification
					? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) // phpcs:ignore WordPress.Security.NonceVerification
					: $product->get_min_purchase_quantity(),
			] );
			?>

			<?php do_action( 'woocommerce_after_add_to_cart_quantity' ); ?>

			<div class="flex-grow-1">
				<button type="submit"
				        class="btn btn-primary btn-icon btn-icon-start has-ripple<?php echo esc_attr( $btn_style ); ?> w-100 single_add_to_cart_button">
					<i class="uil uil-shopping-bag"></i>
					<?php echo esc_html( $product->single_add_to_cart_text() ); ?>
				</button>
			</div>

			<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>

		</div>
	</div>

	<input type="hidden" name="add-to-cart"   value="<?php echo absint( $product->get_id() ); ?>" />
	<input type="hidden" name="product_id"    value="<?php echo absint( $product->get_id() ); ?>" />
	<input type="hidden" name="variation_id"  class="variation_id" value="0" />

</div>

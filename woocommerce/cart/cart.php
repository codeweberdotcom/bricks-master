<?php
/**
 * Cart Page
 *
 * Переопределяет woocommerce/cart/cart.php.
 * Двухколоночный Bootstrap-макет: товары (col-lg-8) + сводка заказа (col-lg-4).
 *
 * @version 10.1.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' );
?>

<div class="row gx-md-8 gx-xl-12 gy-12">

	<div class="col-lg-8">
		<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
			<?php do_action( 'woocommerce_before_cart_table' ); ?>

			<div class="table-responsive">
				<table class="table text-center shopping-cart woocommerce-cart-form__contents" cellspacing="0">
					<thead>
						<tr>
							<th class="ps-0 w-25">
								<div class="h4 mb-0 text-start"><?php esc_html_e( 'Product', 'woocommerce' ); ?></div>
							</th>
							<th>
								<div class="h4 mb-0"><?php esc_html_e( 'Price', 'woocommerce' ); ?></div>
							</th>
							<th>
								<div class="h4 mb-0"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></div>
							</th>
							<th>
								<div class="h4 mb-0"><?php esc_html_e( 'Total', 'woocommerce' ); ?></div>
							</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php do_action( 'woocommerce_before_cart_contents' ); ?>

						<?php
						foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
							$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
							$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
							$product_name = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );

							if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
								$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
								?>
								<tr class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

									<td class="product-name option text-start d-flex flex-row align-items-center ps-0" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
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
												<?php
												if ( $product_permalink ) {
													echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s" class="link-dark">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
												} else {
													echo wp_kses_post( $product_name );
												}
												?>
											</h3>
											<?php echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
											<?php do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key ); ?>
											<?php if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) : ?>
												<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification small">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>', $product_id ) ); ?>
											<?php endif; ?>
										</div>
									</td>

									<td class="product-price" data-title="<?php esc_attr_e( 'Price', 'woocommerce' ); ?>">
										<p class="price mb-0">
											<?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										</p>
									</td>

									<td class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'woocommerce' ); ?>">
									<?php
									if ( $_product->is_sold_individually() ) {
										$min_qty = 1;
										$max_qty = 1;
									} else {
										$min_qty = 0;
										$max_qty = $_product->get_max_purchase_quantity();
									}

									$product_quantity = woocommerce_quantity_input(
										array(
											'input_name'   => "cart[{$cart_item_key}][qty]",
											'input_value'  => $cart_item['quantity'],
											'max_value'    => $max_qty,
											'min_value'    => $min_qty,
											'product_name' => $product_name,
										),
										$_product,
										false
									);

									echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									?>
								</td>

								<td class="product-subtotal" data-title="<?php esc_attr_e( 'Total', 'woocommerce' ); ?>">
										<p class="price mb-0">
											<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										</p>
									</td>

									<td class="pe-0">
										<?php
										echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											'woocommerce_cart_item_remove_link',
											sprintf(
												'<a href="%s" class="link-dark" aria-label="%s" data-product_id="%s" data-product_sku="%s"><i class="uil uil-trash-alt"></i></a>',
												esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
												/* translators: %s is the product name */
												esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) ),
												esc_attr( $product_id ),
												esc_attr( $_product->get_sku() )
											),
											$cart_item_key
										);
										?>
									</td>

								</tr>
								<?php
							}
						}
						?>

						<?php do_action( 'woocommerce_cart_contents' ); ?>
						<?php do_action( 'woocommerce_after_cart_contents' ); ?>
					</tbody>
				</table>
			</div>
			<!-- /.table-responsive -->

			<?php do_action( 'woocommerce_after_cart_table' ); ?>

			<div class="row mt-0 gy-4">
				<?php if ( wc_coupons_enabled() ) : ?>
				<div class="col-md-8 col-lg-7">
					<div class="form-floating input-group">
						<input type="text" name="coupon_code" class="form-control" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" />
						<label for="coupon_code"><?php esc_html_e( 'Enter promo code', 'codeweber' ); ?></label>
						<button type="submit" class="btn btn-primary" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>">
							<?php esc_html_e( 'Apply', 'codeweber' ); ?>
						</button>
						<?php do_action( 'woocommerce_cart_coupon' ); ?>
					</div>
					<!-- /.input-group -->
				</div>
				<!-- /column -->
				<?php endif; ?>

				<div class="d-none">
					<button type="submit" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>"></button>
					<?php do_action( 'woocommerce_cart_actions' ); ?>
					<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
				</div>
				<!-- /column -->
			</div>
			<!-- /.row -->
		</form>
	</div>
	<!-- /col-lg-8 -->

	<div class="col-lg-4">
		<?php
		do_action( 'woocommerce_before_cart_collaterals' );
		woocommerce_cart_totals();
		?>
	</div>
	<!-- /col-lg-4 -->

</div>
<!-- /.row -->

<?php do_action( 'woocommerce_after_cart' ); ?>

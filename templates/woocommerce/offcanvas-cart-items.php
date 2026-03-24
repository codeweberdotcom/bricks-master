<?php
/**
 * Offcanvas Cart Items
 *
 * Содержимое корзины для #offcanvas-cart.
 * Этот файл используется дважды:
 *   1. При первичном рендере offcanvas в wp_footer.
 *   2. Как WC cart fragment — ключ '.cw-offcanvas-cart-inner'.
 *      WooCommerce автоматически заменяет элемент в DOM при изменении корзины.
 */

defined( 'ABSPATH' ) || exit;

$cart_items = WC()->cart ? WC()->cart->get_cart() : [];
?>
<div class="cw-offcanvas-cart-inner">

	<?php if ( empty( $cart_items ) ) : ?>

		<div class="text-center py-10">
			<i class="uil uil-shopping-cart fs-48 text-muted mb-3 d-block"></i>
			<p class="text-muted mb-4"><?php esc_html_e( 'Ваша корзина пуста', 'codeweber' ); ?></p>
			<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"
			   class="btn btn-primary btn-sm rounded has-ripple">
				<?php esc_html_e( 'В каталог', 'codeweber' ); ?>
			</a>
		</div>

	<?php else : ?>

		<div class="shopping-cart">
			<?php foreach ( $cart_items as $cart_item_key => $cart_item ) :

				/** @var WC_Product $product */
				$product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

				if ( ! $product || ! $product->exists() || 0 === $cart_item['quantity'] ) {
					continue;
				}

				$product_id   = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
				$product_url  = apply_filters( 'woocommerce_cart_item_permalink', $product->is_visible() ? get_permalink( $product_id ) : '', $cart_item, $cart_item_key );
				$product_name = apply_filters( 'woocommerce_cart_item_name', $product->get_name(), $cart_item, $cart_item_key );
				$product_img  = $product->get_image( 'thumbnail', array( 'class' => '' ) );
				$remove_url   = esc_url( wc_get_cart_remove_url( $cart_item_key ) );
				?>
				<div class="shopping-cart-item d-flex justify-content-between mb-4"
				     data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>">

					<div class="d-flex flex-row">
						<figure class="cw-cart-item-thumb rounded flex-shrink-0">
							<?php if ( $product_url ) : ?>
								<a href="<?php echo esc_url( $product_url ); ?>">
									<?php echo $product_img; // phpcs:ignore WordPress.Security.EscapeOutput ?>
								</a>
							<?php else : ?>
								<?php echo $product_img; // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<?php endif; ?>
						</figure>

						<div class="w-100 ms-4">
							<h3 class="post-title fs-16 lh-xs mb-1">
								<?php if ( $product_url ) : ?>
									<a href="<?php echo esc_url( $product_url ); ?>" class="link-dark">
										<?php echo esc_html( wp_strip_all_tags( $product_name ) ); ?>
									</a>
								<?php else : ?>
									<?php echo esc_html( wp_strip_all_tags( $product_name ) ); ?>
								<?php endif; ?>
							</h3>

							<p class="price fs-sm mb-1">
								<?php
								echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput
									'woocommerce_cart_item_price',
									WC()->cart->get_product_price( $product ),
									$cart_item,
									$cart_item_key
								);
								?>
							</p>

							<p class="text-muted fs-sm mb-0">
								<?php echo esc_html__( 'Кол-во:', 'codeweber' ) . ' ' . esc_html( $cart_item['quantity'] ); ?>
							</p>
						</div>
					</div>

					<div class="ms-2 flex-shrink-0">
						<a href="<?php echo $remove_url; // phpcs:ignore WordPress.Security.EscapeOutput ?>"
						   class="cw-cart-remove link-dark"
						   data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>"
						   aria-label="<?php esc_attr_e( 'Удалить из корзины', 'codeweber' ); ?>">
							<i class="uil uil-trash-alt"></i>
						</a>
					</div>

				</div>
				<!-- /.shopping-cart-item -->

			<?php endforeach; ?>
		</div>
		<!-- /.shopping-cart -->

		<div class="offcanvas-footer flex-column text-center">
			<div class="d-flex w-100 justify-content-between mb-4">
				<span><?php esc_html_e( 'Итого:', 'codeweber' ); ?></span>
				<span class="h6 mb-0">
					<?php echo WC()->cart->get_cart_subtotal(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</span>
			</div>

			<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>"
			   class="btn btn-primary btn-icon btn-icon-start rounded w-100 mb-3 has-ripple">
				<i class="uil uil-credit-card fs-18"></i>
				<?php esc_html_e( 'Оформить заказ', 'codeweber' ); ?>
			</a>

			<a href="<?php echo esc_url( wc_get_cart_url() ); ?>"
			   class="btn btn-outline-primary rounded w-100 has-ripple">
				<?php esc_html_e( 'Перейти в корзину', 'codeweber' ); ?>
			</a>
		</div>
		<!-- /.offcanvas-footer -->

	<?php endif; ?>

</div>

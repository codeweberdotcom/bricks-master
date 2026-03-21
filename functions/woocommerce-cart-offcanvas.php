<?php
/**
 * WooCommerce Cart Offcanvas
 *
 * AJAX add-to-cart для single product + offcanvas cart sidebar.
 * Архив: WC встроенный ajax_add_to_cart → событие added_to_cart → открыть offcanvas.
 * Single: перехват form.cart → наш AJAX → WC fragments → открыть offcanvas.
 */

defined( 'ABSPATH' ) || exit;

// ── Cart Fragment ─────────────────────────────────────────────────────────────
// Ключ '.cw-offcanvas-cart-inner' — CSS-селектор элемента в DOM.
// WC заменяет элемент в DOM при каждом обновлении корзины (added_to_cart, wc_fragment_refresh).

add_filter( 'woocommerce_add_to_cart_fragments', 'cw_cart_offcanvas_fragment' );

function cw_cart_offcanvas_fragment( $fragments ) {
	ob_start();
	get_template_part( 'templates/woocommerce/offcanvas-cart-items' );
	$html = ob_get_clean();

	$fragments['.cw-offcanvas-cart-inner'] = $html;

	return $fragments;
}

// ── AJAX: добавление в корзину (single product) ───────────────────────────────

add_action( 'wp_ajax_cw_add_to_cart',        'cw_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_cw_add_to_cart', 'cw_ajax_add_to_cart' );

function cw_ajax_add_to_cart() {
	check_ajax_referer( 'cw_add_to_cart', 'nonce' );

	// product_id: из кнопки submit name="add-to-cart" или скрытого поля
	$product_id   = absint( wp_unslash( $_POST['add-to-cart'] ?? $_POST['product_id'] ?? 0 ) );
	$quantity     = max( 1, absint( wp_unslash( $_POST['quantity'] ?? 1 ) ) );
	$variation_id = absint( wp_unslash( $_POST['variation_id'] ?? 0 ) );

	// Атрибуты вариации: поля с префиксом attribute_
	$variation = array();
	foreach ( $_POST as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification
		if ( strpos( $key, 'attribute_' ) === 0 ) {
			$variation[ sanitize_key( $key ) ] = sanitize_text_field( wp_unslash( $value ) );
		}
	}

	if ( ! $product_id ) {
		wp_send_json_error( array( 'message' => esc_html__( 'Неверный товар.', 'codeweber' ) ) );
	}

	$cart_item_key = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation );

	if ( false === $cart_item_key ) {
		$notices = wc_get_notices( 'error' );
		wc_clear_notices();
		$message = ! empty( $notices )
			? wp_strip_all_tags( $notices[0]['notice'] )
			: esc_html__( 'Не удалось добавить товар в корзину.', 'codeweber' );
		wp_send_json_error( array( 'message' => $message ) );
	}

	WC()->cart->calculate_totals();
	wc_clear_notices();

	wp_send_json_success( array(
		'cart_count' => WC()->cart->get_cart_contents_count(),
		'cart_hash'  => WC()->cart->get_cart_hash(),
	) );
}

// ── Footer: offcanvas container ───────────────────────────────────────────────
// Только на WooCommerce-страницах — по образцу cw_quick_view_modal_container.

add_action( 'wp_footer', 'cw_cart_offcanvas_container' );

function cw_cart_offcanvas_container() {
	if ( ! function_exists( 'is_woocommerce' ) ) {
		return;
	}
	if (
		! is_woocommerce() &&
		! is_shop() &&
		! is_product_category() &&
		! is_product_tag() &&
		! is_cart() &&
		! is_checkout() &&
		! ( function_exists( 'cw_is_wishlist_page' ) && cw_is_wishlist_page() )
	) {
		return;
	}
	?>
	<div class="offcanvas offcanvas-end bg-light"
	     id="offcanvas-cart"
	     data-bs-scroll="true"
	     tabindex="-1"
	     aria-labelledby="offcanvas-cart-label">

		<div class="offcanvas-header">
			<h3 class="mb-0" id="offcanvas-cart-label">
				<?php esc_html_e( 'Корзина', 'codeweber' ); ?>
			</h3>
			<button type="button"
			        class="btn-close"
			        data-bs-dismiss="offcanvas"
			        aria-label="<?php esc_attr_e( 'Закрыть', 'codeweber' ); ?>">
			</button>
		</div>

		<div class="offcanvas-body d-flex flex-column">
			<?php get_template_part( 'templates/woocommerce/offcanvas-cart-items' ); ?>
		</div>

	</div>
	<!-- /#offcanvas-cart -->
	<?php
}

// ── Enqueue JS ────────────────────────────────────────────────────────────────

add_action( 'wp_enqueue_scripts', 'cw_cart_offcanvas_enqueue', 36 );

function cw_cart_offcanvas_enqueue() {
	if ( ! function_exists( 'is_woocommerce' ) ) {
		return;
	}
	if (
		! is_woocommerce() &&
		! is_shop() &&
		! is_product_category() &&
		! is_product_tag() &&
		! is_cart() &&
		! is_checkout() &&
		! ( function_exists( 'cw_is_wishlist_page' ) && cw_is_wishlist_page() )
	) {
		return;
	}

	$dist_path = codeweber_get_dist_file_path( 'dist/assets/js/woo-cart-offcanvas.js' );
	$dist_url  = codeweber_get_dist_file_url( 'dist/assets/js/woo-cart-offcanvas.js' );

	if ( ! $dist_path || ! $dist_url ) {
		return;
	}

	wp_enqueue_script(
		'cw-cart-offcanvas',
		$dist_url,
		array( 'jquery', 'wc-cart-fragments' ),
		codeweber_asset_version( $dist_path ),
		true
	);

	wp_localize_script(
		'cw-cart-offcanvas',
		'cwCartOffcanvas',
		array(
			'ajaxUrl' => esc_url( admin_url( 'admin-ajax.php' ) ),
			'nonce'   => wp_create_nonce( 'cw_add_to_cart' ),
			'action'  => 'cw_add_to_cart',
			'i18n'    => array(
				'error' => esc_html__( 'Не удалось добавить товар в корзину.', 'codeweber' ),
			),
		)
	);
}

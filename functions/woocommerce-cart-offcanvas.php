<?php
/**
 * WooCommerce Cart Offcanvas
 *
 * AJAX add-to-cart для single product + offcanvas cart sidebar.
 * Архив: WC встроенный ajax_add_to_cart → событие added_to_cart → открыть offcanvas.
 * Single: перехват form.cart → наш AJAX → WC fragments → открыть offcanvas.
 */

defined( 'ABSPATH' ) || exit;

// ── Cart Fragments ────────────────────────────────────────────────────────────
// Регистрируем два фрагмента:
//   1. '.cw-offcanvas-cart-inner'  — список товаров + итого
//   2. '.badge-cart'               — счётчик товаров в шапке
// WC заменяет эти элементы в DOM при каждом изменении корзины.

add_filter( 'woocommerce_add_to_cart_fragments', 'cw_cart_offcanvas_fragment' );

function cw_cart_offcanvas_fragment( $fragments ) {
	// Фрагмент 1: содержимое корзины
	ob_start();
	get_template_part( 'templates/woocommerce/offcanvas-cart-items' );
	$fragments['.cw-offcanvas-cart-inner'] = ob_get_clean();

	// Фрагмент 2: счётчик товаров (.badge-cart в шапке)
	$count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	$fragments['.badge-cart'] = '<span class="badge badge-cart bg-primary"'
		. ( 0 === $count ? ' style="display:none"' : '' )
		. '>' . esc_html( $count ) . '</span>';

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

	wc_clear_notices();

	// Возвращаем фрагменты сразу — JS не делает второй запрос
	ob_start();
	get_template_part( 'templates/woocommerce/offcanvas-cart-items' );
	$cart_html = ob_get_clean();

	$count = WC()->cart->get_cart_contents_count();

	wp_send_json_success( array(
		'cart_html'  => $cart_html,
		'cart_count' => $count,
		'cart_hash'  => WC()->cart->get_cart_hash(),
	) );
}

// ── Footer: offcanvas container ───────────────────────────────────────────────
// Рендерим на ВСЕХ страницах — кнопка корзины есть в шапке везде.

add_action( 'wp_footer', 'cw_cart_offcanvas_container' );

function cw_cart_offcanvas_container() {
	if ( ! function_exists( 'WC' ) ) {
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
// Грузим на ВСЕХ страницах — offcanvas и слушатели событий нужны везде.

add_action( 'wp_enqueue_scripts', 'cw_cart_offcanvas_enqueue', 36 );

function cw_cart_offcanvas_enqueue() {
	if ( ! function_exists( 'WC' ) ) {
		return;
	}

	$dist_path = codeweber_get_dist_file_path( 'dist/assets/js/woo-cart-offcanvas.js' );
	$dist_url  = codeweber_get_dist_file_url( 'dist/assets/js/woo-cart-offcanvas.js' );

	if ( ! $dist_path || ! $dist_url ) {
		return;
	}

	// Зависимость wc-add-to-cart нужна для события added_to_cart на архивах
	wp_enqueue_script(
		'cw-cart-offcanvas',
		$dist_url,
		array( 'jquery', 'wc-add-to-cart' ),
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

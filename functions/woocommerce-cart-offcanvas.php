<?php
/**
 * WooCommerce Cart Offcanvas
 *
 * AJAX add-to-cart для single product + offcanvas cart sidebar.
 * Архив: WC встроенный ajax_add_to_cart → событие added_to_cart → открыть offcanvas.
 * Single: перехват form.cart → наш AJAX → WC fragments → открыть offcanvas.
 */

defined( 'ABSPATH' ) || exit;

// Отключаем встроенный WC AJAX для архивов — используем свой механизм
add_filter( 'pre_option_woocommerce_enable_ajax_add_to_cart', '__return_false' );

// ── Атрибуты вариации в корзине ───────────────────────────────────────────────
// wc_get_formatted_cart_item_data() пропускает атрибуты если они уже в названии.
// Принудительно добавляем их для показа под названием товара.

add_filter( 'woocommerce_get_item_data', 'cw_force_variation_data_in_cart', 10, 2 );

function cw_force_variation_data_in_cart( $item_data, $cart_item ) {
	if ( ! empty( $item_data ) ) {
		return $item_data;
	}
	if ( ! $cart_item['data']->is_type( 'variation' ) || empty( $cart_item['variation'] ) ) {
		return $item_data;
	}
	foreach ( $cart_item['variation'] as $name => $value ) {
		if ( '' === $value ) {
			continue;
		}
		$taxonomy = str_replace( 'attribute_', '', sanitize_title( $name ) ); // pa_color
		if ( taxonomy_exists( $taxonomy ) ) {
			$term = get_term_by( 'slug', $value, $taxonomy );
			if ( ! is_wp_error( $term ) && $term && $term->name ) {
				$value = $term->name;
			}
			$label = wc_attribute_label( $taxonomy );
		} else {
			$label = wc_attribute_label( str_replace( 'attribute_', '', $name ), $cart_item['data'] );
		}
		$item_data[] = array(
			'key'   => $label,
			'value' => $value,
		);
	}
	return $item_data;
}

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

// ── AJAX: удаление из корзины ─────────────────────────────────────────────────

add_action( 'wp_ajax_cw_remove_from_cart',        'cw_ajax_remove_from_cart' );
add_action( 'wp_ajax_nopriv_cw_remove_from_cart', 'cw_ajax_remove_from_cart' );

function cw_ajax_remove_from_cart() {
	check_ajax_referer( 'cw_add_to_cart', 'nonce' );

	$cart_item_key = sanitize_key( wp_unslash( $_POST['cart_item_key'] ?? '' ) );

	if ( ! $cart_item_key ) {
		wp_send_json_error( array( 'message' => 'Invalid key.' ) );
	}

	WC()->cart->remove_cart_item( $cart_item_key );

	ob_start();
	get_template_part( 'templates/woocommerce/offcanvas-cart-items' );
	$cart_html = ob_get_clean();

	$count = WC()->cart->get_cart_contents_count();

	wp_send_json_success( array(
		'cart_html'  => $cart_html,
		'cart_count' => $count,
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

	// Авто-обновление корзины при изменении количества товара
	if ( is_cart() ) {
		wp_add_inline_script(
			'cw-cart-offcanvas',
			'jQuery(function($){
				$(document).on("change", "form.woocommerce-cart-form .qty", function(){
					$("[name=\'update_cart\']").prop("disabled", false).trigger("click");
				});
			});'
		);
	}
}

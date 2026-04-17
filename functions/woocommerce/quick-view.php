<?php
/**
 * WooCommerce Quick View
 *
 * AJAX-обработчик быстрого просмотра товара.
 * Возвращает HTML-фрагмент для Bootstrap Modal (#cw-quick-view-modal).
 */

defined( 'ABSPATH' ) || exit;

// ── Проверка: quick view включён в Redux ─────────────────────────────────────

function cw_is_quick_view_enabled() {
	$opts = get_option( 'redux_demo', [] );
	return (bool) ( $opts['quick_view_enable'] ?? true );
}

if ( ! cw_is_quick_view_enabled() ) {
	return;
}

// ── AJAX-обработчик ─────────────────────────────────────────────────────────

add_action( 'wp_ajax_cw_quick_view',        'cw_quick_view_handler' );
add_action( 'wp_ajax_nopriv_cw_quick_view', 'cw_quick_view_handler' );

function cw_quick_view_handler() {
	$product_id = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0;

	if ( ! $product_id || ! function_exists( 'wc_get_product' ) ) {
		wp_send_json_error( array( 'message' => 'Invalid product ID' ) );
	}

	global $post, $product;

	$post    = get_post( $product_id );
	$product = wc_get_product( $product_id );

	if ( ! $product || ! $product->is_visible() ) {
		wp_send_json_error( array( 'message' => 'Product not found' ) );
	}

	setup_postdata( $post );

	// Убираем лишние элементы из single_product_summary для quick view
	remove_action( 'woocommerce_before_single_product', 'wc_print_notices', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );

	ob_start();
	get_template_part( 'woocommerce/content-quick-view' );
	$html = ob_get_clean();

	wp_reset_postdata();

	// Восстанавливаем хуки (если вдруг страница рендерится дальше)
	add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );

	wp_send_json_success( $html );
}

// ── Helper: текущая страница — страница вишлиста ─────────────────────────────

function cw_is_wishlist_page() {
	$opts    = get_option( 'redux_demo', [] );
	$page_id = (int) ( $opts['wishlist_page'] ?? 0 );
	return $page_id && is_page( $page_id );
}

// ── Modal container в footer ─────────────────────────────────────────────────

add_action( 'wp_footer', 'cw_quick_view_modal_container' );

function cw_quick_view_modal_container() {
	if ( ! function_exists( 'is_woocommerce' ) ) {
		return;
	}
	// Пропускаем только админку и feed-ы. На любой фронт-странице с WC
	// рендерим контейнер — он скрыт, стоимость минимальна, зато QV работает
	// везде, где может появиться кнопка .item-view (Post Grid, кастомные шорткоды, ...).
	if ( is_admin() || is_feed() ) {
		return;
	}
	$card_radius = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : 'rounded';
	?>
	<div class="modal fade" id="cw-quick-view-modal" tabindex="-1" aria-label="<?php esc_attr_e( 'Quick view', 'codeweber' ); ?>" aria-hidden="true">
		<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
			<div class="modal-content position-relative overflow-hidden <?php echo esc_attr( $card_radius ); ?>">
				<button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="<?php esc_attr_e( 'Close', 'codeweber' ); ?>"></button>
				<div class="modal-body p-0" id="cw-quick-view-body">
					<div class="cw-qv-loading-wrap d-flex align-items-center justify-content-center">
						<div class="spinner-border text-primary" role="status">
							<span class="visually-hidden"><?php esc_html_e( 'Loading...', 'codeweber' ); ?></span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
}

// ── Enqueue JS ───────────────────────────────────────────────────────────────

add_action( 'wp_enqueue_scripts', 'cw_quick_view_enqueue', 35 );

function cw_quick_view_enqueue() {
	if ( ! function_exists( 'is_woocommerce' ) ) {
		return;
	}
	// Грузим JS везде на фронте — кнопка .item-view может быть в Post Grid,
	// кастомных блоках, шорткодах и т.д. Обнаружение через has_block() ненадёжно
	// для template parts / FSE-шаблонов.
	if ( is_admin() || is_feed() ) {
		return;
	}

	$dist_path = codeweber_get_dist_file_path( 'dist/assets/js/woo-quick-view.js' );
	$dist_url  = codeweber_get_dist_file_url( 'dist/assets/js/woo-quick-view.js' );

	if ( ! $dist_path || ! $dist_url ) {
		return;
	}

	wp_enqueue_script(
		'cw-quick-view',
		$dist_url,
		array( 'jquery', 'wc-add-to-cart-variation' ),
		codeweber_asset_version( $dist_path ),
		true
	);

	wp_localize_script(
		'cw-quick-view',
		'cwQuickView',
		array(
			'ajaxUrl' => esc_url( admin_url( 'admin-ajax.php' ) ),
			'action'  => 'cw_quick_view',
			'i18n'    => array(
				'loading' => esc_html__( 'Loading...', 'codeweber' ),
				'error'   => esc_html__( 'Failed to load product. Please try again.', 'codeweber' ),
			),
		)
	);
}

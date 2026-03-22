<?php
/**
 * Compare — главный класс: AJAX, инициализация.
 *
 * @package CodeWeber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CW_Compare
 */
class CW_Compare {

	/**
	 * Is compare enabled via Redux.
	 *
	 * @var bool
	 */
	private $enabled = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->enabled = $this->is_enabled();

		if ( ! $this->enabled ) {
			return;
		}

		add_action( 'init', array( $this, 'init' ), 1 );

		add_action( 'wp_ajax_cw_compare_toggle',        array( $this, 'ajax_toggle' ) );
		add_action( 'wp_ajax_nopriv_cw_compare_toggle', array( $this, 'ajax_toggle' ) );

		add_action( 'wp_ajax_cw_compare_clear',        array( $this, 'ajax_clear' ) );
		add_action( 'wp_ajax_nopriv_cw_compare_clear', array( $this, 'ajax_clear' ) );
	}

	/**
	 * Initialize UI.
	 */
	public function init() {
		new CW_Compare_UI();
	}

	/**
	 * AJAX: toggle product in compare list (add or remove).
	 */
	public function ajax_toggle() {
		check_ajax_referer( 'cw_compare_nonce', 'nonce' );

		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;

		if ( $product_id < 1 ) {
			wp_send_json_error( array( 'message' => __( 'Invalid product_id', 'codeweber' ) ) );
		}

		// Проверяем существование товара
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			wp_send_json_error( array( 'message' => __( 'Product not found', 'codeweber' ) ) );
		}

		$limit  = cw_get_compare_limit();
		$added  = false;

		if ( CW_Compare_Storage::has( $product_id ) ) {
			// Уже есть — удаляем
			CW_Compare_Storage::remove( $product_id );
			$added = false;
		} else {
			// Добавляем
			$result = CW_Compare_Storage::add( $product_id, $limit );

			if ( ! $result ) {
				wp_send_json_error( array(
					'message'       => __( 'Достигнут лимит товаров для сравнения', 'codeweber' ),
					'limit_reached' => true,
				) );
			}

			$added = true;
		}

		$ids   = CW_Compare_Storage::get_ids();
		$count = count( $ids );

		// Рендерим inner-контент бара
		$bar_html = $this->render_bar_inner( $ids, $limit );

		// Matomo tracking
		if ( $added ) {
			$this->track_matomo_compare_add( $product_id, $product->get_name() );
		}

		wp_send_json_success( array(
			'added'         => $added,
			'ids'           => $ids,
			'count'         => $count,
			'limit_reached' => $count >= $limit,
			'bar_html'      => $bar_html,
		) );
	}

	/**
	 * AJAX: clear all products from compare.
	 */
	public function ajax_clear() {
		check_ajax_referer( 'cw_compare_nonce', 'nonce' );

		CW_Compare_Storage::clear();

		wp_send_json_success( array(
			'ids'   => array(),
			'count' => 0,
		) );
	}

	/**
	 * Render bar inner HTML for AJAX response.
	 *
	 * @param int[] $ids   Current IDs.
	 * @param int   $limit Limit.
	 * @return string
	 */
	private function render_bar_inner( $ids, $limit ) {
		ob_start();

		get_template_part( 'woocommerce/content-compare', 'bar', array(
			'compare_ids' => $ids,
			'limit'       => $limit,
		) );

		return ob_get_clean();
	}

	/**
	 * Check if compare is enabled in Redux.
	 *
	 * @return bool
	 */
	private function is_enabled() {
		if ( ! class_exists( 'Redux' ) || ! class_exists( 'WooCommerce' ) ) {
			return false;
		}

		global $opt_name;
		return (bool) Redux::get_option( $opt_name, 'compare_enable', 0 );
	}

	/**
	 * Send "Add to Compare" event to Matomo.
	 *
	 * @param int    $product_id   Product ID.
	 * @param string $product_name Product name.
	 */
	private function track_matomo_compare_add( $product_id, $product_name ) {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( ! is_plugin_active( 'matomo/matomo.php' ) ) {
			return;
		}

		$visitor_id = '';
		foreach ( $_COOKIE as $name => $value ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			if ( strpos( $name, '_pk_id_' ) === 0 ) {
				$parts = explode( '.', $value );
				if ( ! empty( $parts[0] ) && strlen( $parts[0] ) === 16 ) {
					$visitor_id = $parts[0];
					break;
				}
			}
		}
		if ( empty( $visitor_id ) ) {
			$visitor_id = substr( md5( ( $_SERVER['REMOTE_ADDR'] ?? '' ) . ( $_SERVER['HTTP_USER_AGENT'] ?? '' ) ), 0, 16 );
		}

		$params = array(
			'idsite'     => defined( 'MATOMO_SITE_ID' ) ? MATOMO_SITE_ID : 1,
			'rec'        => 1,
			'ua'         => sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ?? '' ),
			'_id'        => $visitor_id,
			'e_c'        => 'Compare',
			'e_a'        => 'Add to Compare',
			'e_n'        => $product_name,
			'e_v'        => $product_id,
			'url'        => home_url( $_SERVER['REQUEST_URI'] ?? '/' ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			'urlref'     => $_SERVER['HTTP_REFERER'] ?? home_url(),
			'send_image' => 0,
		);

		wp_remote_post(
			home_url( '/wp-json/matomo/v1/hit/' ),
			array(
				'timeout'   => 2,
				'blocking'  => false,
				'sslverify' => false,
				'body'      => $params,
			)
		);
	}
}

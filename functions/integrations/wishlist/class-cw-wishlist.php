<?php
/**
 * Wishlist main class — установка таблиц, AJAX, инициализация.
 *
 * @package CodeWeber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CW_Wishlist
 */
class CW_Wishlist {

	/**
	 * Wishlist item instance.
	 *
	 * @var CW_Wishlist_Item|null
	 */
	private $wishlist = null;

	/**
	 * Is wishlist enabled via Redux.
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

		add_action( 'after_switch_theme', array( $this, 'install' ) );
		add_action( 'admin_init', array( $this, 'maybe_install' ) );

		add_action( 'init', array( $this, 'init' ), 1 );

		add_action( 'wp_ajax_cw_add_to_wishlist', array( $this, 'ajax_add' ) );
		add_action( 'wp_ajax_nopriv_cw_add_to_wishlist', array( $this, 'ajax_add' ) );

		add_action( 'wp_ajax_cw_remove_from_wishlist', array( $this, 'ajax_remove' ) );
		add_action( 'wp_ajax_nopriv_cw_remove_from_wishlist', array( $this, 'ajax_remove' ) );

		add_action( 'delete_user', array( $this, 'delete_user_wishlist' ) );
	}

	/**
	 * Initialize wishlist object and UI.
	 */
	public function init() {
		// Создаём таблицы при первом обращении — upgrade.php доступен всегда.
		if ( ! get_option( 'cw_wishlist_installed' ) ) {
			if ( ! function_exists( 'dbDelta' ) ) {
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			}
			$this->install();
		}

		if ( get_option( 'cw_wishlist_installed' ) ) {
			$this->wishlist            = new CW_Wishlist_Item();
			$GLOBALS['cw_wishlist_instance'] = $this->wishlist;
		}

		// UI (и шорткод) регистрируем всегда — иначе [cw_wishlist] не работает.
		new CW_Wishlist_UI( $this->wishlist );
	}

	/**
	 * Get wishlist object.
	 *
	 * @return CW_Wishlist_Item|null
	 */
	public function get_wishlist() {
		return $this->wishlist;
	}

	/**
	 * AJAX: add product to wishlist.
	 */
	public function ajax_add() {
		check_ajax_referer( 'cw_wishlist_nonce', 'nonce' );

		if ( ! isset( $_POST['product_id'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Missing product_id', 'codeweber' ) ) );
		}

		$product_id = (int) $_POST['product_id']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		if ( $product_id < 1 ) {
			wp_send_json_error( array( 'message' => __( 'Invalid product_id', 'codeweber' ) ) );
		}

		// Проверяем гостевой доступ.
		if ( ! is_user_logged_in() && ! $this->guests_allowed() ) {
			wp_send_json_error( array(
				'message'  => __( 'Войдите, чтобы добавить товар в избранное.', 'codeweber' ),
				'redirect' => wc_get_page_permalink( 'myaccount' ),
			) );
		}

		$wishlist = new CW_Wishlist_Item();
		$added    = $wishlist->add( $product_id );

		$wishlist->update_count_cookie();

		wp_send_json_success( array(
			'added' => $added,
			'count' => $wishlist->get_count(),
		) );
	}

	/**
	 * AJAX: remove product from wishlist.
	 */
	public function ajax_remove() {
		check_ajax_referer( 'cw_wishlist_nonce', 'nonce' );

		if ( ! isset( $_POST['product_id'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Missing product_id', 'codeweber' ) ) );
		}

		$product_id = (int) $_POST['product_id']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		if ( $product_id < 1 ) {
			wp_send_json_error( array( 'message' => __( 'Invalid product_id', 'codeweber' ) ) );
		}

		if ( ! is_user_logged_in() && ! $this->guests_allowed() ) {
			wp_send_json_error( array( 'message' => __( 'Не авторизован', 'codeweber' ) ) );
		}

		$wishlist = new CW_Wishlist_Item();
		$wishlist->remove( $product_id );
		$wishlist->update_count_cookie();

		wp_send_json_success( array(
			'count' => $wishlist->get_count(),
		) );
	}

	/**
	 * Install DB tables.
	 */
	public function install() {
		global $wpdb;

		if ( get_option( 'cw_wishlist_installed' ) ) {
			return;
		}

		$charset = $wpdb->get_charset_collate();

		$wishlists_table = $wpdb->prefix . 'cw_wishlists';
		$products_table  = $wpdb->prefix . 'cw_wishlist_products';

		$sql = "CREATE TABLE {$wishlists_table} (
			ID         INT(11)   NOT NULL AUTO_INCREMENT,
			user_id    INT(11)   NOT NULL,
			date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (ID),
			KEY user_id (user_id)
		) {$charset};";

		$sql .= "CREATE TABLE {$products_table} (
			ID           INT(11)   NOT NULL AUTO_INCREMENT,
			wishlist_id  INT(11)   NOT NULL,
			product_id   INT(11)   NOT NULL,
			date_added   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (ID),
			KEY wishlist_id (wishlist_id),
			KEY product_id  (product_id)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'cw_wishlist_installed', true );
	}

	/**
	 * Install on admin_init if settings were saved.
	 */
	public function maybe_install() {
		if ( isset( $_GET['settings-updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$this->install();
		}
	}

	/**
	 * Remove wishlist data when user is deleted.
	 *
	 * @param int $user_id User ID.
	 */
	public function delete_user_wishlist( $user_id ) {
		global $wpdb;

		$wishlists_table = $wpdb->prefix . 'cw_wishlists';
		$products_table  = $wpdb->prefix . 'cw_wishlist_products';

		$wishlist_ids = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT ID FROM `{$wishlists_table}` WHERE user_id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				(int) $user_id
			)
		);

		if ( ! empty( $wishlist_ids ) ) {
			$ids_placeholder = implode( ',', array_fill( 0, count( $wishlist_ids ), '%d' ) );
			$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->prepare(
					"DELETE FROM `{$products_table}` WHERE wishlist_id IN ({$ids_placeholder})", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$wishlist_ids // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
				)
			);
		}

		$wpdb->delete( $wishlists_table, array( 'user_id' => (int) $user_id ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	}

	/**
	 * Check if wishlist is enabled in Redux.
	 *
	 * @return bool
	 */
	private function is_enabled() {
		if ( ! class_exists( 'Redux' ) || ! class_exists( 'WooCommerce' ) ) {
			return false;
		}

		global $opt_name;
		return (bool) Redux::get_option( $opt_name, 'wishlist_enable', 0 );
	}

	/**
	 * Check if guest wishlist is allowed.
	 *
	 * @return bool
	 */
	private function guests_allowed() {
		global $opt_name;
		return (bool) Redux::get_option( $opt_name, 'wishlist_guests', 1 );
	}
}

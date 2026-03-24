<?php
/**
 * Wishlist item — represents the wishlist for a specific user.
 *
 * @package CodeWeber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CW_Wishlist_Item
 */
class CW_Wishlist_Item {

	/**
	 * Wishlist DB ID.
	 *
	 * @var int
	 */
	private $wishlist_id = 0;

	/**
	 * User ID.
	 *
	 * @var int
	 */
	private $user_id = 0;

	/**
	 * Storage instance.
	 *
	 * @var CW_Wishlist_Storage
	 */
	private $storage;

	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( is_user_logged_in() ) {
			$this->user_id     = get_current_user_id();
			$this->wishlist_id = $this->get_or_create_wishlist_id();
			$this->storage     = new CW_DB_Storage( $this->wishlist_id, $this->user_id );
		} else {
			$this->storage = new CW_Session_Storage();
		}
	}

	/**
	 * Add product to wishlist.
	 *
	 * @param int $product_id Product ID.
	 * @return bool
	 */
	public function add( $product_id ) {
		return $this->storage->add( (int) $product_id );
	}

	/**
	 * Remove product from wishlist.
	 *
	 * @param int $product_id Product ID.
	 * @return bool
	 */
	public function remove( $product_id ) {
		return $this->storage->remove( (int) $product_id );
	}

	/**
	 * Get all products.
	 *
	 * @return array
	 */
	public function get_all() {
		return $this->storage->get_all();
	}

	/**
	 * Check if product is in wishlist.
	 *
	 * @param int $product_id Product ID.
	 * @return bool
	 */
	public function is_in_wishlist( $product_id ) {
		return $this->storage->is_product_exists( (int) $product_id );
	}

	/**
	 * Get count of products.
	 *
	 * @return int
	 */
	public function get_count() {
		return count( $this->get_all() );
	}

	/**
	 * Update the count in cookie (for the JS widget in the header).
	 */
	public function update_count_cookie() {
		$expire = time() + ( 7 * DAY_IN_SECONDS );
		setcookie( 'cw_wishlist_count', $this->get_count(), $expire, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), false );
		$_COOKIE['cw_wishlist_count'] = $this->get_count();
	}

	/**
	 * Get wishlist DB ID.
	 *
	 * @return int
	 */
	public function get_wishlist_id() {
		return $this->wishlist_id;
	}

	/**
	 * Get wishlist ID from DB for current user, create if not exists.
	 *
	 * @return int
	 */
	private function get_or_create_wishlist_id() {
		global $wpdb;

		if ( ! get_option( 'cw_wishlist_installed' ) ) {
			return 0;
		}

		$table = $wpdb->prefix . 'cw_wishlists';

		$cache_key = 'cw_wishlist_id_' . $this->user_id;
		$cached    = get_transient( $cache_key );

		if ( $cached ) {
			return (int) $cached;
		}

		$id = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT ID FROM `{$table}` WHERE user_id = %d LIMIT 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$this->user_id
			)
		);

		if ( ! $id ) {
			$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$table,
				array(
					'user_id'      => $this->user_id,
					'date_created' => current_time( 'mysql', 1 ),
				),
				array( '%d', '%s' )
			);
			$id = $wpdb->insert_id;
		}

		set_transient( $cache_key, $id, 2 * HOUR_IN_SECONDS );

		return (int) $id;
	}

}

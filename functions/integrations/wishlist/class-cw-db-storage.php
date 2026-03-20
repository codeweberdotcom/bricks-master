<?php
/**
 * Wishlist database storage — для залогиненных пользователей.
 *
 * @package CodeWeber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CW_DB_Storage
 */
class CW_DB_Storage implements CW_Wishlist_Storage {

	/**
	 * Wishlist ID in DB.
	 *
	 * @var int
	 */
	private $wishlist_id;

	/**
	 * User ID.
	 *
	 * @var int
	 */
	private $user_id;

	/**
	 * Products table name.
	 *
	 * @var string
	 */
	private $products_table;

	/**
	 * Wishlists table name.
	 *
	 * @var string
	 */
	private $wishlists_table;

	/**
	 * User meta cache key.
	 *
	 * @var string
	 */
	private $cache_key;

	/**
	 * Constructor.
	 *
	 * @param int $wishlist_id Wishlist ID.
	 * @param int $user_id     User ID.
	 */
	public function __construct( $wishlist_id, $user_id ) {
		global $wpdb;

		$this->wishlist_id     = (int) $wishlist_id;
		$this->user_id         = (int) $user_id;
		$this->products_table  = $wpdb->prefix . 'cw_wishlist_products';
		$this->wishlists_table = $wpdb->prefix . 'cw_wishlists';
		$this->cache_key       = 'cw_wishlist_' . $this->wishlist_id;
	}

	/**
	 * Add product to DB wishlist.
	 *
	 * @param int $product_id Product ID.
	 * @return bool
	 */
	public function add( $product_id ) {
		global $wpdb;

		$product_id = (int) $product_id;

		if ( ! $this->wishlist_id || $this->is_product_exists( $product_id ) ) {
			return false;
		}

		$result = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$this->products_table,
			array(
				'wishlist_id' => $this->wishlist_id,
				'product_id'  => $product_id,
				'date_added'  => current_time( 'mysql', 1 ),
			),
			array( '%d', '%d', '%s' )
		);

		if ( $result ) {
			delete_user_meta( $this->user_id, $this->cache_key );
		}

		return (bool) $result;
	}

	/**
	 * Remove product from DB wishlist.
	 *
	 * @param int $product_id Product ID.
	 * @return bool
	 */
	public function remove( $product_id ) {
		global $wpdb;

		$product_id = (int) $product_id;

		if ( ! $this->is_product_exists( $product_id ) ) {
			return false;
		}

		$result = $wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$this->products_table,
			array(
				'wishlist_id' => $this->wishlist_id,
				'product_id'  => $product_id,
			),
			array( '%d', '%d' )
		);

		if ( $result ) {
			delete_user_meta( $this->user_id, $this->cache_key );
		}

		return (bool) $result;
	}

	/**
	 * Get all products from DB.
	 *
	 * @return array
	 */
	public function get_all() {
		global $wpdb;

		if ( ! $this->wishlist_id ) {
			return array();
		}

		$cache = get_user_meta( $this->user_id, $this->cache_key, true );

		if ( ! empty( $cache ) && is_array( $cache ) && isset( $cache['expires'] ) && $cache['expires'] > time() ) {
			return $cache['products'];
		}

		$products = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT product_id, date_added FROM `{$this->products_table}` WHERE wishlist_id = %d ORDER BY date_added DESC", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$this->wishlist_id
			),
			ARRAY_A
		);

		if ( $products === null ) {
			return array();
		}

		update_user_meta(
			$this->user_id,
			$this->cache_key,
			array(
				'expires'  => time() + WEEK_IN_SECONDS,
				'products' => $products,
			)
		);

		return $products;
	}

	/**
	 * Check if product exists in DB.
	 *
	 * @param int $product_id Product ID.
	 * @return bool
	 */
	public function is_product_exists( $product_id ) {
		global $wpdb;

		if ( ! $this->wishlist_id ) {
			return false;
		}

		$result = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT ID FROM `{$this->products_table}` WHERE wishlist_id = %d AND product_id = %d LIMIT 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$this->wishlist_id,
				(int) $product_id
			)
		);

		return ! is_null( $result );
	}

	/**
	 * Get wishlist ID.
	 *
	 * @return int
	 */
	public function get_wishlist_id() {
		return $this->wishlist_id;
	}

	/**
	 * Get all product IDs.
	 *
	 * @return array
	 */
	public function get_product_ids() {
		return array_column( $this->get_all(), 'product_id' );
	}

	/**
	 * Delete all cache for this user.
	 */
	public function clear_cache() {
		delete_user_meta( $this->user_id, $this->cache_key );
	}
}

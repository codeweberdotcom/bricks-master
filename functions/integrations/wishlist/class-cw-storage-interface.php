<?php
/**
 * Wishlist storage interface.
 *
 * @package CodeWeber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface CW_Wishlist_Storage
 */
interface CW_Wishlist_Storage {

	/**
	 * Add product to wishlist.
	 *
	 * @param int $product_id Product ID.
	 * @return bool
	 */
	public function add( $product_id );

	/**
	 * Remove product from wishlist.
	 *
	 * @param int $product_id Product ID.
	 * @return bool
	 */
	public function remove( $product_id );

	/**
	 * Get all products.
	 *
	 * @return array
	 */
	public function get_all();

	/**
	 * Check if product exists in wishlist.
	 *
	 * @param int $product_id Product ID.
	 * @return bool
	 */
	public function is_product_exists( $product_id );
}

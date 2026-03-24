<?php
/**
 * Wishlist session storage — for non-logged-in users via WC_Session.
 *
 * @package CodeWeber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CW_Session_Storage
 */
class CW_Session_Storage implements CW_Wishlist_Storage {

	/**
	 * WooCommerce session key.
	 *
	 * @var string
	 */
	const SESSION_KEY = 'cw_wishlist_products';

	/**
	 * Add product to session wishlist.
	 *
	 * @param int $product_id Product ID.
	 * @return bool
	 */
	public function add( $product_id ) {
		$product_id = (int) $product_id;
		$products   = $this->get_map();

		if ( isset( $products[ $product_id ] ) ) {
			return false;
		}

		$products[ $product_id ] = array( 'product_id' => $product_id );
		$this->save( $products );

		return true;
	}

	/**
	 * Remove product from session wishlist.
	 *
	 * @param int $product_id Product ID.
	 * @return bool
	 */
	public function remove( $product_id ) {
		$product_id = (int) $product_id;
		$products   = $this->get_map();

		if ( ! isset( $products[ $product_id ] ) ) {
			return false;
		}

		unset( $products[ $product_id ] );
		$this->save( $products );

		return true;
	}

	/**
	 * Get all products from session.
	 *
	 * @return array
	 */
	public function get_all() {
		return array_values( $this->get_map() );
	}

	/**
	 * Check if product exists in session.
	 *
	 * @param int $product_id Product ID.
	 * @return bool
	 */
	public function is_product_exists( $product_id ) {
		return isset( $this->get_map()[ (int) $product_id ] );
	}

	/**
	 * Get products map (product_id => data) from WC session.
	 *
	 * @return array
	 */
	private function get_map() {
		if ( ! $this->session_available() ) {
			return [];
		}

		$data = WC()->session->get( self::SESSION_KEY );

		return is_array( $data ) ? $data : [];
	}

	/**
	 * Save products map to WC session.
	 *
	 * @param array $products
	 */
	private function save( $products ) {
		if ( ! $this->session_available() ) {
			return;
		}

		WC()->session->set( self::SESSION_KEY, $products );
	}

	/**
	 * Check if WC session is available.
	 *
	 * @return bool
	 */
	private function session_available() {
		return function_exists( 'WC' ) && WC()->session instanceof WC_Session;
	}
}

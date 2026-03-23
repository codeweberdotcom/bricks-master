<?php
/**
 * Wishlist cookie storage — for non-logged-in users.
 *
 * @package CodeWeber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CW_Cookie_Storage
 */
class CW_Cookie_Storage implements CW_Wishlist_Storage {

	/**
	 * Cookie name for products list.
	 *
	 * @var string
	 */
	private $cookie_products = 'cw_wishlist_products';

	/**
	 * Cookie name for count.
	 *
	 * @var string
	 */
	private $cookie_count = 'cw_wishlist_count';

	/**
	 * Add product to wishlist cookie.
	 *
	 * @param int $product_id Product ID.
	 * @return bool
	 */
	public function add( $product_id ) {
		if ( $this->is_product_exists( $product_id ) ) {
			return false;
		}

		$all = $this->get_all();
		$all[ $product_id ] = array( 'product_id' => (int) $product_id );

		$this->set_cookie( $this->cookie_products, wp_json_encode( $all ) );
		$this->set_cookie( $this->cookie_count, count( $all ) );

		return true;
	}

	/**
	 * Remove product from wishlist cookie.
	 *
	 * @param int $product_id Product ID.
	 * @return bool
	 */
	public function remove( $product_id ) {
		if ( ! $this->is_product_exists( $product_id ) ) {
			return false;
		}

		$all = $this->get_all();
		unset( $all[ $product_id ] );

		$this->set_cookie( $this->cookie_products, wp_json_encode( $all ) );
		$this->set_cookie( $this->cookie_count, count( $all ) );

		return true;
	}

	/**
	 * Get all products from cookie.
	 *
	 * @return array
	 */
	public function get_all() {
		if ( ! isset( $_COOKIE[ $this->cookie_products ] ) ) {
			return array();
		}

		$raw = sanitize_text_field( wp_unslash( $_COOKIE[ $this->cookie_products ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$decoded = json_decode( $raw, true );

		return is_array( $decoded ) ? $decoded : array();
	}

	/**
	 * Check if product exists in cookie.
	 *
	 * @param int $product_id Product ID.
	 * @return bool
	 */
	public function is_product_exists( $product_id ) {
		$all = $this->get_all();
		return isset( $all[ $product_id ] );
	}

	/**
	 * Get count cookie name.
	 *
	 * @return string
	 */
	public function get_count_cookie_name() {
		return $this->cookie_count;
	}

	/**
	 * Get products cookie name.
	 *
	 * @return string
	 */
	public function get_products_cookie_name() {
		return $this->cookie_products;
	}

	/**
	 * Set cookie helper.
	 *
	 * @param string $name  Cookie name.
	 * @param string $value Cookie value.
	 */
	private function set_cookie( $name, $value ) {
		$expire = time() + ( 7 * DAY_IN_SECONDS );
		setcookie( $name, $value, $expire, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), false );
		$_COOKIE[ $name ] = $value;
	}
}

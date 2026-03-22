<?php
/**
 * Compare Storage — cookie CRUD.
 * Хранит массив ID товаров (parent или variation) в cookie cw_compare.
 *
 * @package CodeWeber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CW_Compare_Storage
 */
class CW_Compare_Storage {

	const COOKIE_NAME = 'cw_compare';
	const COOKIE_DAYS = 30;

	/**
	 * Get all stored product/variation IDs.
	 *
	 * @return int[]
	 */
	public static function get_ids() {
		if ( ! isset( $_COOKIE[ self::COOKIE_NAME ] ) ) {
			return array();
		}

		$raw  = stripslashes( sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE_NAME ] ) ) );
		$data = json_decode( $raw, true );

		if ( ! is_array( $data ) ) {
			return array();
		}

		return array_values( array_filter( array_map( 'absint', $data ) ) );
	}

	/**
	 * Add a product/variation ID. Returns false if limit reached.
	 *
	 * @param int $id     Product or variation ID.
	 * @param int $limit  Max items.
	 * @return bool
	 */
	public static function add( $id, $limit = 4 ) {
		$id  = absint( $id );
		$ids = self::get_ids();

		if ( in_array( $id, $ids, true ) ) {
			return true; // уже есть
		}

		if ( count( $ids ) >= $limit ) {
			return false; // лимит
		}

		$ids[] = $id;
		self::save( $ids );

		return true;
	}

	/**
	 * Remove a product/variation ID from cookie.
	 *
	 * @param int $id Product or variation ID.
	 * @return bool
	 */
	public static function remove( $id ) {
		$id  = absint( $id );
		$ids = self::get_ids();

		$new = array_values( array_filter( $ids, fn( $i ) => $i !== $id ) );

		if ( count( $new ) === count( $ids ) ) {
			return false; // не было в списке
		}

		self::save( $new );

		return true;
	}

	/**
	 * Clear all IDs from cookie.
	 */
	public static function clear() {
		self::save( array() );
	}

	/**
	 * Check if ID is in compare list.
	 *
	 * @param int $id Product or variation ID.
	 * @return bool
	 */
	public static function has( $id ) {
		return in_array( absint( $id ), self::get_ids(), true );
	}

	/**
	 * Get count of items in compare.
	 *
	 * @return int
	 */
	public static function count() {
		return count( self::get_ids() );
	}

	/**
	 * Save IDs array to cookie.
	 *
	 * @param int[] $ids Array of IDs.
	 */
	private static function save( array $ids ) {
		$value   = wp_json_encode( array_values( $ids ) );
		$expires = time() + self::COOKIE_DAYS * DAY_IN_SECONDS;

		setcookie( self::COOKIE_NAME, $value, $expires, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), false );

		// Обновляем суперглобальный массив для текущего запроса
		$_COOKIE[ self::COOKIE_NAME ] = $value;
	}
}

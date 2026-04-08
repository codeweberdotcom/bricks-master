<?php
/**
 * CodeWeber Forms — One-time submission token
 *
 * Generates a single-use token per form render.
 * Token is stored in a transient (TTL 30 min) and deleted on first use,
 * preventing nonce reuse across multiple submissions.
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CodeweberFormsToken {

	const TTL         = 1800; // 30 minutes
	const TRANSIENT_PREFIX = 'cwf_token_';

	/**
	 * Generate a one-time token for a form.
	 *
	 * @param  string|int $form_id
	 * @return string  UUID token
	 */
	public static function generate( $form_id ) {
		$token = wp_generate_uuid4();
		set_transient( self::TRANSIENT_PREFIX . $token, (string) $form_id, self::TTL );
		return $token;
	}

	/**
	 * Verify and consume a token.
	 * Returns true once — then the transient is deleted.
	 *
	 * @param  string $token
	 * @return bool
	 */
	public static function verify( $token ) {
		if ( empty( $token ) || ! is_string( $token ) ) {
			return false;
		}

		// Basic UUID format check to avoid unnecessary DB lookups
		if ( ! preg_match( '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $token ) ) {
			return false;
		}

		$key   = self::TRANSIENT_PREFIX . $token;
		$value = get_transient( $key );

		if ( false === $value ) {
			return false;
		}

		// One-use: delete immediately after reading
		delete_transient( $key );

		return true;
	}
}

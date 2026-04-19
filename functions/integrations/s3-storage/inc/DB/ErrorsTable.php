<?php

namespace Codeweber\S3Storage\DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ErrorsTable {

	public static function name() {
		global $wpdb;
		return $wpdb->prefix . 'cws3_errors';
	}

	public static function schema() {
		global $wpdb;
		$table   = self::name();
		$charset = $wpdb->get_charset_collate();

		return "CREATE TABLE {$table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			operation VARCHAR(64) NOT NULL,
			attachment_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			error_code VARCHAR(64) NOT NULL DEFAULT '',
			error_message TEXT NOT NULL,
			context_json LONGTEXT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY operation (operation),
			KEY attachment_id (attachment_id),
			KEY created_at (created_at)
		) {$charset};";
	}

	public static function log( string $operation, string $message, array $context = [], int $attachment_id = 0, string $code = '' ) {
		global $wpdb;
		$wpdb->insert( self::name(), [
			'operation'     => substr( $operation, 0, 64 ),
			'attachment_id' => $attachment_id,
			'error_code'    => substr( $code, 0, 64 ),
			'error_message' => $message,
			'context_json'  => $context ? wp_json_encode( $context ) : null,
		] );
	}

	public static function recent( int $limit = 100 ) {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare(
			'SELECT * FROM ' . self::name() . ' ORDER BY id DESC LIMIT %d',
			$limit
		) );
	}

	public static function clear() {
		global $wpdb;
		return $wpdb->query( 'TRUNCATE TABLE ' . self::name() );
	}

	public static function count() {
		global $wpdb;
		return (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . self::name() );
	}
}

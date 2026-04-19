<?php

namespace Codeweber\S3Storage\DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class JobsTable {

	const STATUS_PENDING   = 'pending';
	const STATUS_RUNNING   = 'running';
	const STATUS_PAUSED    = 'paused';
	const STATUS_COMPLETED = 'completed';
	const STATUS_CANCELLED = 'cancelled';
	const STATUS_FAILED    = 'failed';

	public static function name() {
		global $wpdb;
		return $wpdb->prefix . 'cws3_jobs';
	}

	public static function schema() {
		global $wpdb;
		$table   = self::name();
		$charset = $wpdb->get_charset_collate();

		return "CREATE TABLE {$table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			job_type VARCHAR(32) NOT NULL,
			status VARCHAR(16) NOT NULL DEFAULT 'pending',
			total INT UNSIGNED NOT NULL DEFAULT 0,
			processed INT UNSIGNED NOT NULL DEFAULT 0,
			failed INT UNSIGNED NOT NULL DEFAULT 0,
			batch_size INT UNSIGNED NOT NULL DEFAULT 20,
			dry_run TINYINT(1) NOT NULL DEFAULT 0,
			cursor_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			payload LONGTEXT NULL,
			last_error TEXT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY job_type (job_type),
			KEY status (status)
		) {$charset};";
	}

	public static function create( string $type, int $total, int $batch_size = 20, bool $dry_run = false ) {
		global $wpdb;
		$wpdb->insert( self::name(), [
			'job_type'   => $type,
			'status'     => self::STATUS_PENDING,
			'total'      => $total,
			'batch_size' => $batch_size,
			'dry_run'    => $dry_run ? 1 : 0,
		] );
		return (int) $wpdb->insert_id;
	}

	public static function get( int $id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::name() . ' WHERE id = %d', $id ) );
	}

	public static function current_of_type( string $type ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare(
			'SELECT * FROM ' . self::name() . " WHERE job_type = %s AND status IN ('pending','running','paused') ORDER BY id DESC LIMIT 1",
			$type
		) );
	}

	public static function update( int $id, array $data ) {
		global $wpdb;
		return $wpdb->update( self::name(), $data, [ 'id' => $id ] );
	}

	public static function set_status( int $id, string $status, ?string $error = null ) {
		$data = [ 'status' => $status ];
		if ( $error !== null ) {
			$data['last_error'] = $error;
		}
		self::update( $id, $data );
	}

	public static function increment( int $id, int $processed = 0, int $failed = 0, int $cursor = 0 ) {
		global $wpdb;
		$parts  = [];
		$values = [];
		if ( $processed ) {
			$parts[]  = 'processed = processed + %d';
			$values[] = $processed;
		}
		if ( $failed ) {
			$parts[]  = 'failed = failed + %d';
			$values[] = $failed;
		}
		if ( $cursor ) {
			$parts[]  = 'cursor_id = %d';
			$values[] = $cursor;
		}
		if ( ! $parts ) {
			return;
		}
		$values[] = $id;
		$wpdb->query( $wpdb->prepare(
			'UPDATE ' . self::name() . ' SET ' . implode( ', ', $parts ) . ' WHERE id = %d',
			$values
		) );
	}
}

<?php

namespace Codeweber\S3Storage\DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ItemsTable {

	public static function name() {
		global $wpdb;
		return $wpdb->prefix . 'cws3_items';
	}

	public static function schema() {
		global $wpdb;
		$table   = self::name();
		$charset = $wpdb->get_charset_collate();

		return "CREATE TABLE {$table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			attachment_id BIGINT UNSIGNED NOT NULL,
			source_type VARCHAR(32) NOT NULL DEFAULT 'original',
			source_id VARCHAR(64) NOT NULL DEFAULT '',
			bucket VARCHAR(191) NOT NULL,
			object_key VARCHAR(500) NOT NULL,
			region VARCHAR(64) NOT NULL DEFAULT '',
			provider VARCHAR(32) NOT NULL DEFAULT 's3',
			file_size BIGINT UNSIGNED NOT NULL DEFAULT 0,
			is_offloaded TINYINT(1) NOT NULL DEFAULT 0,
			is_local TINYINT(1) NOT NULL DEFAULT 1,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY attachment_id (attachment_id),
			KEY source_lookup (attachment_id, source_type, source_id),
			KEY is_offloaded (is_offloaded),
			KEY is_local (is_local)
		) {$charset};";
	}

	public static function insert( array $data ) {
		global $wpdb;
		$wpdb->insert( self::name(), $data );
		return (int) $wpdb->insert_id;
	}

	public static function upsert( int $attachment_id, string $source_type, string $source_id, array $data ) {
		global $wpdb;
		$existing = $wpdb->get_var( $wpdb->prepare(
			'SELECT id FROM ' . self::name() . ' WHERE attachment_id = %d AND source_type = %s AND source_id = %s',
			$attachment_id,
			$source_type,
			$source_id
		) );
		if ( $existing ) {
			$wpdb->update( self::name(), $data, [ 'id' => (int) $existing ] );
			return (int) $existing;
		}
		$data['attachment_id'] = $attachment_id;
		$data['source_type']   = $source_type;
		$data['source_id']     = $source_id;
		return self::insert( $data );
	}

	public static function get_by_attachment( int $attachment_id ) {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare(
			'SELECT * FROM ' . self::name() . ' WHERE attachment_id = %d',
			$attachment_id
		) );
	}

	public static function get_by_attachments( array $ids ) {
		if ( empty( $ids ) ) {
			return [];
		}
		global $wpdb;
		$ids_in = implode( ',', array_map( 'intval', $ids ) );
		$rows   = $wpdb->get_results(
			'SELECT * FROM ' . self::name() . " WHERE attachment_id IN ({$ids_in})"
		);
		$by_id = [];
		foreach ( $rows as $row ) {
			$by_id[ (int) $row->attachment_id ][] = $row;
		}
		return $by_id;
	}

	public static function delete_by_attachment( int $attachment_id ) {
		global $wpdb;
		return $wpdb->delete( self::name(), [ 'attachment_id' => $attachment_id ] );
	}

	public static function count_offloaded() {
		global $wpdb;
		return (int) $wpdb->get_var(
			'SELECT COUNT(DISTINCT attachment_id) FROM ' . self::name() . ' WHERE is_offloaded = 1'
		);
	}

	public static function ids_not_offloaded( int $limit = 20, int $offset = 0 ) {
		global $wpdb;
		return $wpdb->get_col( $wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts}
			WHERE post_type = 'attachment'
			AND ID NOT IN (SELECT attachment_id FROM " . self::name() . " WHERE is_offloaded = 1)
			ORDER BY ID ASC
			LIMIT %d OFFSET %d",
			$limit,
			$offset
		) );
	}

	public static function summarize_status( array $rows ) {
		$rows = array_filter( $rows, function ( $r ) { return (int) $r->is_offloaded === 1; } );
		if ( empty( $rows ) ) {
			return 'local';
		}
		$any_local    = false;
		$all_local    = true;
		foreach ( $rows as $r ) {
			if ( (int) $r->is_local === 1 ) {
				$any_local = true;
			} else {
				$all_local = false;
			}
		}
		if ( $any_local && $all_local ) {
			return 'mirror';
		}
		if ( $any_local ) {
			return 'mirror_partial';
		}
		return 's3';
	}
}

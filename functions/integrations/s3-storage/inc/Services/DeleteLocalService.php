<?php

namespace Codeweber\S3Storage\Services;

use Codeweber\S3Storage\DB\ItemsTable;
use Codeweber\S3Storage\Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DeleteLocalService {

	public static function delete_local_for_attachment( int $attachment_id ) {
		global $wpdb;

		$rows = $wpdb->get_results( $wpdb->prepare(
			'SELECT * FROM ' . ItemsTable::name() . ' WHERE attachment_id = %d AND is_offloaded = 1 AND is_local = 1',
			$attachment_id
		) );

		if ( empty( $rows ) ) {
			return [ 'processed' => 0, 'failed' => 0 ];
		}

		$upload    = wp_get_upload_dir();
		$basedir   = trailingslashit( $upload['basedir'] );
		$processed = 0;
		$failed    = 0;

		foreach ( $rows as $row ) {
			$path = $basedir . ltrim( $row->object_key, '/' );
			if ( ! file_exists( $path ) ) {
				$wpdb->update( ItemsTable::name(), [ 'is_local' => 0 ], [ 'id' => (int) $row->id ] );
				$processed++;
				continue;
			}
			if ( @unlink( $path ) ) {
				$wpdb->update( ItemsTable::name(), [ 'is_local' => 0 ], [ 'id' => (int) $row->id ] );
				Logger::info( 'delete_local', 'Local file removed.', [ 'path' => basename( $path ) ], $attachment_id );
				$processed++;
			} else {
				$failed++;
				Logger::error( 'delete_local', 'Failed to delete local file.', [ 'path' => $path ], $attachment_id );
			}
		}

		return [ 'processed' => $processed, 'failed' => $failed ];
	}
}

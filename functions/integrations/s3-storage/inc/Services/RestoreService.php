<?php

namespace Codeweber\S3Storage\Services;

use Codeweber\S3Storage\Client;
use Codeweber\S3Storage\DB\ItemsTable;
use Codeweber\S3Storage\Logger;
use Codeweber\S3Storage\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RestoreService {

	public static function restore_attachment( int $attachment_id ) {
		global $wpdb;

		$rows = $wpdb->get_results( $wpdb->prepare(
			'SELECT * FROM ' . ItemsTable::name() . ' WHERE attachment_id = %d AND is_offloaded = 1 AND is_local = 0',
			$attachment_id
		) );

		if ( empty( $rows ) ) {
			return [ 'processed' => 0, 'failed' => 0 ];
		}

		$settings = Settings::get();
		try {
			$client = Client::factory( $settings );
		} catch ( \Throwable $e ) {
			Logger::error( 'restore', $e->getMessage(), [], $attachment_id, 'client_init' );
			return [ 'processed' => 0, 'failed' => count( $rows ) ];
		}

		$upload    = wp_get_upload_dir();
		$basedir   = trailingslashit( $upload['basedir'] );
		$processed = 0;
		$failed    = 0;

		foreach ( $rows as $row ) {
			$target = $basedir . ltrim( $row->object_key, '/' );
			wp_mkdir_p( dirname( $target ) );

			try {
				$client->getObject( [
					'Bucket' => $row->bucket,
					'Key'    => $row->object_key,
					'SaveAs' => $target,
				] );
				if ( file_exists( $target ) ) {
					$wpdb->update( ItemsTable::name(), [ 'is_local' => 1 ], [ 'id' => (int) $row->id ] );
					Logger::info( 'restore', 'Object restored.', [ 'object' => $row->object_key ], $attachment_id );
					$processed++;
				} else {
					$failed++;
					Logger::error( 'restore', 'File not saved after getObject.', [ 'object' => $row->object_key ], $attachment_id );
				}
			} catch ( \Throwable $e ) {
				$failed++;
				Logger::error( 'restore', $e->getMessage(), [ 'object' => $row->object_key ], $attachment_id );
			}
		}

		return [ 'processed' => $processed, 'failed' => $failed ];
	}
}

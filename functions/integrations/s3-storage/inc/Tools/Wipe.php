<?php

namespace Codeweber\S3Storage\Tools;

use Codeweber\S3Storage\Client;
use Codeweber\S3Storage\DB\ItemsTable;
use Codeweber\S3Storage\Logger;
use Codeweber\S3Storage\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Wipe {

	public function estimate_total() {
		global $wpdb;
		return (int) $wpdb->get_var(
			'SELECT COUNT(DISTINCT attachment_id) FROM ' . ItemsTable::name() . ' WHERE is_offloaded = 1'
		);
	}

	public function run_batch( $job ) {
		global $wpdb;
		$batch = max( 1, (int) $job->batch_size );
		$ids   = $wpdb->get_col( $wpdb->prepare(
			'SELECT DISTINCT attachment_id FROM ' . ItemsTable::name() . ' WHERE is_offloaded = 1 ORDER BY attachment_id ASC LIMIT %d',
			$batch
		) );

		if ( empty( $ids ) ) {
			return [ 'done' => true, 'processed' => 0, 'failed' => 0 ];
		}

		$settings = Settings::get();
		if ( empty( $settings['bucket'] ) ) {
			throw new \RuntimeException( 'Bucket is not configured.' );
		}

		try {
			$client = Client::factory( $settings );
		} catch ( \Throwable $e ) {
			throw new \RuntimeException( 'S3 client init failed: ' . $e->getMessage() );
		}

		$processed = 0;
		$failed    = 0;

		foreach ( $ids as $id ) {
			$id    = (int) $id;
			$items = ItemsTable::get_by_attachment( $id );
			$keys  = [];
			foreach ( $items as $row ) {
				if ( (int) $row->is_offloaded === 1 && ! empty( $row->object_key ) ) {
					$keys[] = [ 'Key' => $row->object_key ];
				}
			}

			if ( empty( $keys ) ) {
				$processed++;
				continue;
			}

			if ( (int) $job->dry_run === 1 ) {
				$processed++;
				continue;
			}

			try {
				$client->deleteObjects( [
					'Bucket' => $settings['bucket'],
					'Delete' => [ 'Objects' => $keys, 'Quiet' => true ],
				] );

				$wpdb->update(
					ItemsTable::name(),
					[
						'is_offloaded' => 0,
						'is_local'     => 1,
						'object_key'   => '',
					],
					[ 'attachment_id' => $id ]
				);

				Logger::info( 'wipe', 'Removed from S3.', [
					'bucket' => $settings['bucket'],
					'count'  => count( $keys ),
				], $id );

				$processed++;
			} catch ( \Throwable $e ) {
				$failed++;
				Logger::error( 'wipe', $e->getMessage(), [ 'count' => count( $keys ) ], $id, 'delete_objects' );
			}
		}

		return [ 'done' => false, 'processed' => $processed, 'failed' => $failed ];
	}
}

<?php

namespace Codeweber\S3Storage\Tools;

use Codeweber\S3Storage\Client;
use Codeweber\S3Storage\DB\ItemsTable;
use Codeweber\S3Storage\Logger;
use Codeweber\S3Storage\Services\MetadataService;
use Codeweber\S3Storage\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ReapplyCacheHeaders {

	public function estimate_total() {
		global $wpdb;
		return (int) $wpdb->get_var(
			'SELECT COUNT(*) FROM ' . ItemsTable::name() . ' WHERE is_offloaded = 1'
		);
	}

	public function run_batch( $job ) {
		global $wpdb;
		$batch = max( 1, (int) $job->batch_size );
		$rows  = $wpdb->get_results( $wpdb->prepare(
			'SELECT * FROM ' . ItemsTable::name() . ' WHERE is_offloaded = 1 ORDER BY id ASC LIMIT %d OFFSET %d',
			$batch,
			(int) $job->cursor_id
		) );

		if ( empty( $rows ) ) {
			return [ 'done' => true, 'processed' => 0, 'failed' => 0 ];
		}

		$settings = Settings::get();
		try {
			$client = Client::factory( $settings );
		} catch ( \Throwable $e ) {
			throw new \RuntimeException( 'S3 client init failed: ' . $e->getMessage() );
		}

		$cache_control = MetadataService::build_cache_control( $settings );
		$processed     = 0;
		$failed        = 0;
		$last_cursor   = (int) $job->cursor_id;

		foreach ( $rows as $row ) {
			$last_cursor = (int) $row->id;

			if ( (int) $job->dry_run === 1 ) {
				$processed++;
				continue;
			}

			$mime = MetadataService::resolve_mime_for_attachment( (int) $row->attachment_id, $row->object_key );
			$args = [
				'Bucket'            => $row->bucket,
				'Key'               => $row->object_key,
				'CopySource'        => $row->bucket . '/' . ltrim( $row->object_key, '/' ),
				'MetadataDirective' => 'REPLACE',
				'ContentType'       => $mime,
				'ACL'               => 'public-read',
			];
			if ( $cache_control ) {
				$args['CacheControl'] = $cache_control;
			}

			try {
				$client->copyObject( $args );
				Logger::info( 'reapply_cache', 'Metadata refreshed.', [
					'object'       => $row->object_key,
					'content_type' => $mime,
				], (int) $row->attachment_id );
				$processed++;
			} catch ( \Throwable $e ) {
				$failed++;
				Logger::error( 'reapply_cache', $e->getMessage(), [ 'object' => $row->object_key ], (int) $row->attachment_id );
			}
		}

		return [
			'done'      => false,
			'processed' => $processed,
			'failed'    => $failed,
			'cursor'    => $last_cursor,
		];
	}
}

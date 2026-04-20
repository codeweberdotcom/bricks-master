<?php

namespace Codeweber\S3Storage\Services;

use Codeweber\S3Storage\Client;
use Codeweber\S3Storage\DB\ItemsTable;
use Codeweber\S3Storage\Logger;
use Codeweber\S3Storage\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MetadataService {

	public static function resolve_mime_for_attachment( int $attachment_id, string $object_key ) {
		$mime = get_post_mime_type( $attachment_id );
		if ( $mime ) {
			return $mime;
		}
		$ext = strtolower( pathinfo( $object_key, PATHINFO_EXTENSION ) );
		if ( $ext ) {
			$check = wp_check_filetype( 'file.' . $ext );
			if ( ! empty( $check['type'] ) ) {
				return $check['type'];
			}
		}
		return 'application/octet-stream';
	}

	public static function build_cache_control( array $settings ) {
		$max_age = (int) ( $settings['cache_max_age'] ?? 0 );
		if ( $max_age <= 0 ) {
			return null;
		}
		return 'public, max-age=' . $max_age . ', immutable';
	}

	public static function reapply_for_attachment( int $attachment_id ) {
		$rows = ItemsTable::get_by_attachment( $attachment_id );
		$rows = array_filter( $rows, function ( $r ) { return (int) $r->is_offloaded === 1 && ! empty( $r->object_key ); } );

		if ( empty( $rows ) ) {
			return [ 'processed' => 0, 'failed' => 0 ];
		}

		$settings = Settings::get();
		try {
			$client = Client::factory( $settings );
		} catch ( \Throwable $e ) {
			Logger::error( 'reapply_metadata', $e->getMessage(), [], $attachment_id, 'client_init' );
			return [ 'processed' => 0, 'failed' => count( $rows ) ];
		}

		$cache_control = self::build_cache_control( $settings );
		$processed     = 0;
		$failed        = 0;

		foreach ( $rows as $row ) {
			$mime = self::resolve_mime_for_attachment( $attachment_id, $row->object_key );
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
				Logger::info( 'reapply_metadata', 'Metadata refreshed.', [
					'object'       => $row->object_key,
					'content_type' => $mime,
				], $attachment_id );
				$processed++;
			} catch ( \Throwable $e ) {
				$failed++;
				Logger::error( 'reapply_metadata', $e->getMessage(), [ 'object' => $row->object_key ], $attachment_id );
			}
		}

		return [ 'processed' => $processed, 'failed' => $failed ];
	}
}

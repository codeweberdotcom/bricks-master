<?php

namespace Codeweber\S3Storage\Services;

use Codeweber\S3Storage\Client;
use Codeweber\S3Storage\DB\ItemsTable;
use Codeweber\S3Storage\Logger;
use Codeweber\S3Storage\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VerifyService {

	public static function verify_attachment( int $attachment_id ) {
		$items = ItemsTable::get_by_attachment( $attachment_id );
		if ( empty( $items ) ) {
			return [ 'ok' => 0, 'missing' => 0, 'error' => 0 ];
		}

		$settings = Settings::get();
		try {
			$client = Client::factory( $settings );
		} catch ( \Throwable $e ) {
			Logger::error( 'verify', $e->getMessage(), [], $attachment_id, 'client_init' );
			return [ 'ok' => 0, 'missing' => 0, 'error' => count( $items ) ];
		}

		$ok      = 0;
		$missing = 0;
		$error   = 0;

		foreach ( $items as $item ) {
			if ( (int) $item->is_offloaded !== 1 ) {
				continue;
			}
			try {
				$client->headObject( [ 'Bucket' => $item->bucket, 'Key' => $item->object_key ] );
				$ok++;
			} catch ( \Throwable $e ) {
				$code = $e instanceof \Aws\S3\Exception\S3Exception ? $e->getStatusCode() : 0;
				if ( $code === 404 ) {
					$missing++;
					Logger::warning( 'verify', 'Object missing in bucket.', [ 'object' => $item->object_key ], $attachment_id );
				} else {
					$error++;
					Logger::error( 'verify', $e->getMessage(), [ 'object' => $item->object_key ], $attachment_id );
				}
			}
		}

		return [ 'ok' => $ok, 'missing' => $missing, 'error' => $error ];
	}
}

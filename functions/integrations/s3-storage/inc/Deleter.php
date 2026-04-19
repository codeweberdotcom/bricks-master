<?php

namespace Codeweber\S3Storage;

use Codeweber\S3Storage\DB\ItemsTable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Deleter {

	public function register() {
		add_action( 'delete_attachment', [ $this, 'on_delete_attachment' ] );
	}

	public function on_delete_attachment( $attachment_id ) {
		$attachment_id = (int) $attachment_id;
		$items         = ItemsTable::get_by_attachment( $attachment_id );
		if ( empty( $items ) ) {
			return;
		}

		$settings = Settings::get();
		if ( empty( $settings['bucket'] ) ) {
			ItemsTable::delete_by_attachment( $attachment_id );
			return;
		}

		try {
			$client = Client::factory( $settings );
		} catch ( \Throwable $e ) {
			Logger::error( 'delete', $e->getMessage(), [], $attachment_id, 'client_init' );
			return;
		}

		$objects = [];
		foreach ( $items as $item ) {
			if ( (int) $item->is_offloaded === 1 ) {
				$objects[] = [ 'Key' => $item->object_key ];
			}
		}

		if ( ! empty( $objects ) ) {
			try {
				$client->deleteObjects( [
					'Bucket' => $settings['bucket'],
					'Delete' => [ 'Objects' => $objects, 'Quiet' => true ],
				] );
				Logger::info( 'delete', 'Objects deleted from bucket.', [
					'bucket' => $settings['bucket'],
					'count'  => count( $objects ),
				], $attachment_id );
			} catch ( \Throwable $e ) {
				Logger::error( 'delete', $e->getMessage(), [ 'count' => count( $objects ) ], $attachment_id, 'delete_objects' );
			}
		}

		ItemsTable::delete_by_attachment( $attachment_id );
	}
}

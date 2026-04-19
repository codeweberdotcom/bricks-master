<?php

namespace Codeweber\S3Storage\Tools;

use Codeweber\S3Storage\DB\ItemsTable;
use Codeweber\S3Storage\Logger;
use Codeweber\S3Storage\Uploader;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Offload {

	public function estimate_total() {
		global $wpdb;
		return (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			 WHERE post_type = 'attachment'
			 AND ID NOT IN (SELECT attachment_id FROM " . ItemsTable::name() . ' WHERE is_offloaded = 1)'
		);
	}

	public function run_batch( $job ) {
		$batch = max( 1, (int) $job->batch_size );
		$ids   = ItemsTable::ids_not_offloaded( $batch, 0 );

		if ( empty( $ids ) ) {
			return [ 'done' => true, 'processed' => 0, 'failed' => 0 ];
		}

		$processed = 0;
		$failed    = 0;
		$uploader  = new Uploader();

		foreach ( $ids as $id ) {
			$id       = (int) $id;
			$metadata = wp_get_attachment_metadata( $id );

			if ( (int) $job->dry_run === 1 ) {
				$processed++;
				continue;
			}

			if ( $uploader->offload_attachment( $id, $metadata ) ) {
				$processed++;
			} else {
				$failed++;
				Logger::error( 'bulk_offload', 'Offload failed for attachment.', [], $id );
			}
		}

		return [ 'done' => false, 'processed' => $processed, 'failed' => $failed ];
	}
}

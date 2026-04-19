<?php

namespace Codeweber\S3Storage\Tools;

use Codeweber\S3Storage\DB\ItemsTable;
use Codeweber\S3Storage\Services\RestoreService;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Restore {

	public function estimate_total() {
		global $wpdb;
		return (int) $wpdb->get_var(
			'SELECT COUNT(DISTINCT attachment_id) FROM ' . ItemsTable::name() . ' WHERE is_offloaded = 1 AND is_local = 0'
		);
	}

	public function run_batch( $job ) {
		global $wpdb;
		$batch = max( 1, (int) $job->batch_size );
		$ids   = $wpdb->get_col( $wpdb->prepare(
			'SELECT DISTINCT attachment_id FROM ' . ItemsTable::name() . ' WHERE is_offloaded = 1 AND is_local = 0 ORDER BY attachment_id ASC LIMIT %d',
			$batch
		) );

		if ( empty( $ids ) ) {
			return [ 'done' => true, 'processed' => 0, 'failed' => 0 ];
		}

		$processed = 0;
		$failed    = 0;

		foreach ( $ids as $id ) {
			if ( (int) $job->dry_run === 1 ) {
				$processed++;
				continue;
			}
			$r = RestoreService::restore_attachment( (int) $id );
			if ( $r['processed'] > 0 && $r['failed'] === 0 ) {
				$processed++;
			} else {
				$failed++;
			}
		}

		return [ 'done' => false, 'processed' => $processed, 'failed' => $failed ];
	}
}

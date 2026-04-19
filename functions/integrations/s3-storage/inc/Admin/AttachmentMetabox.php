<?php

namespace Codeweber\S3Storage\Admin;

use Codeweber\S3Storage\Client;
use Codeweber\S3Storage\DB\ItemsTable;
use Codeweber\S3Storage\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AttachmentMetabox {

	public function register() {
		add_action( 'add_meta_boxes_attachment', [ $this, 'add_metabox' ] );
	}

	public function add_metabox( $post ) {
		add_meta_box(
			'cws3-s3-storage',
			__( 'S3 Storage', 'codeweber' ),
			[ $this, 'render' ],
			'attachment',
			'side',
			'default'
		);
	}

	public function render( $post ) {
		$rows     = ItemsTable::get_by_attachment( (int) $post->ID );
		$status   = ItemsTable::summarize_status( $rows );
		$badge    = MediaLibrary::badge_for( $rows );
		$settings = Settings::get();

		$total_sizes       = count( array_filter( $rows, function ( $r ) { return $r->source_type === 'size'; } ) );
		$offloaded_sizes   = count( array_filter( $rows, function ( $r ) { return $r->source_type === 'size' && (int) $r->is_offloaded === 1; } ) );

		$original = null;
		foreach ( $rows as $row ) {
			if ( $row->source_type === 'original' && (int) $row->is_offloaded === 1 ) {
				$original = $row;
				break;
			}
		}

		include CWS3_MODULE_DIR . '/inc/views/attachment-metabox.php';
	}
}

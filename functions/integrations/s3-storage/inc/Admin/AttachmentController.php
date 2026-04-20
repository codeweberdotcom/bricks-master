<?php

namespace Codeweber\S3Storage\Admin;

use Codeweber\S3Storage\DB\ItemsTable;
use Codeweber\S3Storage\Services\DeleteLocalService;
use Codeweber\S3Storage\Services\MetadataService;
use Codeweber\S3Storage\Services\RestoreService;
use Codeweber\S3Storage\Services\VerifyService;
use Codeweber\S3Storage\Uploader;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AttachmentController {

	public function register() {
		add_action( 'wp_ajax_cws3_attachment_action', [ $this, 'ajax' ] );
	}

	public function ajax() {
		check_ajax_referer( 'cws3_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'codeweber' ) ], 403 );
		}

		$attachment_id = isset( $_POST['attachment_id'] ) ? (int) $_POST['attachment_id'] : 0;
		$action        = isset( $_POST['op'] ) ? sanitize_key( wp_unslash( $_POST['op'] ) ) : '';

		if ( ! $attachment_id || get_post_type( $attachment_id ) !== 'attachment' ) {
			wp_send_json_error( [ 'message' => __( 'Invalid attachment.', 'codeweber' ) ] );
		}

		$result = null;

		try {
			switch ( $action ) {
				case 'offload':
					$uploader = new Uploader();
					$ok       = $uploader->offload_attachment( $attachment_id );
					$result   = [ 'ok' => $ok ];
					break;
				case 'restore':
					$result = RestoreService::restore_attachment( $attachment_id );
					break;
				case 'delete_local':
					$result = DeleteLocalService::delete_local_for_attachment( $attachment_id );
					break;
				case 'verify':
					$result = VerifyService::verify_attachment( $attachment_id );
					break;
				case 'resync':
					DeleteLocalService::delete_local_for_attachment( $attachment_id );
					$uploader = new Uploader();
					$ok       = $uploader->offload_attachment( $attachment_id );
					$result   = [ 'ok' => $ok ];
					break;
				case 'reapply_metadata':
					$result = MetadataService::reapply_for_attachment( $attachment_id );
					break;
				default:
					wp_send_json_error( [ 'message' => __( 'Unknown action.', 'codeweber' ) ] );
			}
		} catch ( \Throwable $e ) {
			wp_send_json_error( [ 'message' => $e->getMessage() ] );
		}

		$rows  = ItemsTable::get_by_attachment( $attachment_id );
		$badge = MediaLibrary::badge_for( $rows );

		wp_send_json_success( [
			'result' => $result,
			'badge'  => $badge,
			'status' => ItemsTable::summarize_status( $rows ),
		] );
	}
}

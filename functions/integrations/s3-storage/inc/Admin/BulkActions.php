<?php

namespace Codeweber\S3Storage\Admin;

use Codeweber\S3Storage\Services\DeleteLocalService;
use Codeweber\S3Storage\Services\RestoreService;
use Codeweber\S3Storage\Services\VerifyService;
use Codeweber\S3Storage\Uploader;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BulkActions {

	public function register() {
		add_filter( 'bulk_actions-upload', [ $this, 'add_bulk_actions' ] );
		add_filter( 'handle_bulk_actions-upload', [ $this, 'handle_bulk' ], 10, 3 );
		add_action( 'admin_notices', [ $this, 'notices' ] );
		add_filter( 'media_row_actions', [ $this, 'add_row_actions' ], 10, 2 );
	}

	public function add_bulk_actions( $actions ) {
		$actions['cws3_offload']      = __( 'Offload to S3', 'codeweber-s3-storage' );
		$actions['cws3_restore']      = __( 'Restore to local', 'codeweber-s3-storage' );
		$actions['cws3_delete_local'] = __( 'Delete local copies', 'codeweber-s3-storage' );
		$actions['cws3_verify']       = __( 'Verify in bucket', 'codeweber-s3-storage' );
		return $actions;
	}

	public function handle_bulk( $redirect_to, $doaction, $post_ids ) {
		if ( strpos( $doaction, 'cws3_' ) !== 0 ) {
			return $redirect_to;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return $redirect_to;
		}

		$processed = 0;
		$failed    = 0;

		foreach ( $post_ids as $id ) {
			$id = (int) $id;
			$ok = false;
			switch ( $doaction ) {
				case 'cws3_offload':
					$uploader = new Uploader();
					$ok       = $uploader->offload_attachment( $id );
					break;
				case 'cws3_restore':
					$r  = RestoreService::restore_attachment( $id );
					$ok = $r['failed'] === 0 && $r['processed'] > 0;
					break;
				case 'cws3_delete_local':
					$r  = DeleteLocalService::delete_local_for_attachment( $id );
					$ok = $r['failed'] === 0 && $r['processed'] > 0;
					break;
				case 'cws3_verify':
					$r  = VerifyService::verify_attachment( $id );
					$ok = $r['missing'] === 0 && $r['error'] === 0;
					break;
			}
			if ( $ok ) { $processed++; } else { $failed++; }
		}

		return add_query_arg( [
			'cws3_bulk'     => $doaction,
			'cws3_done'     => $processed,
			'cws3_failed'   => $failed,
		], $redirect_to );
	}

	public function notices() {
		if ( empty( $_GET['cws3_bulk'] ) ) {
			return;
		}
		$action    = sanitize_key( wp_unslash( $_GET['cws3_bulk'] ) );
		$done      = (int) ( $_GET['cws3_done'] ?? 0 );
		$failed    = (int) ( $_GET['cws3_failed'] ?? 0 );
		$label_map = [
			'cws3_offload'      => __( 'Offload', 'codeweber-s3-storage' ),
			'cws3_restore'      => __( 'Restore', 'codeweber-s3-storage' ),
			'cws3_delete_local' => __( 'Delete local', 'codeweber-s3-storage' ),
			'cws3_verify'       => __( 'Verify', 'codeweber-s3-storage' ),
		];
		$label = $label_map[ $action ] ?? $action;

		$class = $failed === 0 ? 'notice-success' : 'notice-warning';
		echo '<div class="notice ' . esc_attr( $class ) . ' is-dismissible"><p>' .
			sprintf(
				/* translators: 1: action label, 2: success count, 3: failed count */
				esc_html__( '%1$s: %2$d succeeded, %3$d failed.', 'codeweber-s3-storage' ),
				esc_html( $label ),
				$done,
				$failed
			) . '</p></div>';
	}

	public function add_row_actions( $actions, $post ) {
		if ( $post->post_type !== 'attachment' ) {
			return $actions;
		}
		$nonce = wp_create_nonce( 'cws3_admin' );
		$base  = admin_url( 'admin-ajax.php' );
		$id    = (int) $post->ID;
		$actions['cws3_offload'] = sprintf(
			'<a href="#" class="cws3-row-action" data-action="offload" data-id="%d">%s</a>',
			$id,
			esc_html__( 'Offload', 'codeweber-s3-storage' )
		);
		return $actions;
	}
}

<?php

namespace Codeweber\S3Storage\Admin;

use Codeweber\S3Storage\Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LogViewer {

	public function register() {
		add_action( 'admin_menu', [ $this, 'add_menu' ] );
		add_action( 'wp_ajax_cws3_clear_logs', [ $this, 'ajax_clear' ] );
	}

	public function add_menu() {
		add_management_page(
			__( 'S3 Storage Logs', 'codeweber-s3-storage' ),
			__( 'S3 Storage Logs', 'codeweber-s3-storage' ),
			'manage_options',
			'cws3-logs',
			[ $this, 'render' ]
		);
	}

	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$files    = Logger::list_log_files();
		$current  = isset( $_GET['file'] ) ? basename( sanitize_text_field( wp_unslash( $_GET['file'] ) ) ) : ( $files ? basename( $files[0] ) : '' );
		$content  = $current ? Logger::read_log_file( $current, 1000 ) : '';
		$filter   = isset( $_GET['level'] ) ? sanitize_key( wp_unslash( $_GET['level'] ) ) : 'all';

		if ( $content && $filter !== 'all' ) {
			$needle  = strtoupper( $filter );
			$lines   = explode( "\n", $content );
			$content = implode( "\n", array_filter( $lines, function ( $l ) use ( $needle ) {
				return stripos( $l, '[' . $needle . ']' ) !== false;
			} ) );
		}

		include CWS3_MODULE_DIR . '/inc/views/log-viewer.php';
	}

	public function ajax_clear() {
		check_ajax_referer( 'cws3_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [], 403 );
		}
		Logger::clear_logs();
		wp_send_json_success();
	}
}

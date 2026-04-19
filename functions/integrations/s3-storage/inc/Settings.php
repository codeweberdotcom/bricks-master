<?php

namespace Codeweber\S3Storage;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {

	const OPTION = 'cws3_settings';

	public static function defaults() {
		return [
			'endpoint'        => '',
			'region'          => 'us-east-1',
			'access_key'      => '',
			'secret_key'      => '',
			'bucket'          => '',
			'path_style'      => 1,
			'verify_ssl'      => 0,
			'public_url'      => '',
			'key_prefix'      => '{year}/{month}/',
			'storage_mode'    => 'local',
			'force_https'     => 1,
			'rewrite_content' => 0,
			'log_level'       => 'error',
			'log_retention'   => 14,
		];
	}

	public static function get() {
		$saved  = get_option( self::OPTION, [] );
		$merged = array_merge( self::defaults(), is_array( $saved ) ? $saved : [] );

		foreach ( [
			'CWS3_ENDPOINT'   => 'endpoint',
			'CWS3_REGION'     => 'region',
			'CWS3_KEY'        => 'access_key',
			'CWS3_SECRET'     => 'secret_key',
			'CWS3_BUCKET'     => 'bucket',
			'CWS3_PATH_STYLE' => 'path_style',
			'CWS3_VERIFY_SSL' => 'verify_ssl',
			'CWS3_PUBLIC_URL' => 'public_url',
		] as $const => $key ) {
			if ( defined( $const ) ) {
				$merged[ $key ] = constant( $const );
			}
		}

		return $merged;
	}

	public static function is_defined_by_constant( string $key ) {
		$map = [
			'endpoint'   => 'CWS3_ENDPOINT',
			'region'     => 'CWS3_REGION',
			'access_key' => 'CWS3_KEY',
			'secret_key' => 'CWS3_SECRET',
			'bucket'     => 'CWS3_BUCKET',
			'path_style' => 'CWS3_PATH_STYLE',
			'verify_ssl' => 'CWS3_VERIFY_SSL',
			'public_url' => 'CWS3_PUBLIC_URL',
		];
		return isset( $map[ $key ] ) && defined( $map[ $key ] );
	}

	public function register() {
		add_action( 'admin_menu', [ $this, 'add_menu' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'wp_ajax_cws3_test_connection', [ $this, 'ajax_test_connection' ] );
		add_action( 'wp_ajax_cws3_clear_errors', [ $this, 'ajax_clear_errors' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
	}

	public function add_menu() {
		add_options_page(
			__( 'S3 Storage', 'codeweber-s3-storage' ),
			__( 'S3 Storage', 'codeweber-s3-storage' ),
			'manage_options',
			'cws3-settings',
			[ $this, 'render_page' ]
		);
	}

	public function register_settings() {
		register_setting( 'cws3_settings_group', self::OPTION, [ $this, 'sanitize' ] );
	}

	public function enqueue( $hook ) {
		$pages = [
			'settings_page_cws3-settings',
			'tools_page_cws3-tools',
			'tools_page_cws3-logs',
			'post.php',
			'upload.php',
		];
		if ( ! in_array( $hook, $pages, true ) ) {
			return;
		}
		wp_enqueue_style( 'cws3-admin', CWS3_MODULE_URL . '/assets/admin.css', [], CWS3_VERSION );
		wp_enqueue_script( 'cws3-admin', CWS3_MODULE_URL . '/assets/admin.js', [ 'jquery' ], CWS3_VERSION, true );
		wp_localize_script( 'cws3-admin', 'cws3', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'cws3_admin' ),
			'i18n'    => [
				'testing'       => __( 'Testing...', 'codeweber-s3-storage' ),
				'test_ok'       => __( 'Connection successful.', 'codeweber-s3-storage' ),
				'test_fail'     => __( 'Connection failed: ', 'codeweber-s3-storage' ),
				'confirm_clear' => __( 'Clear the error log?', 'codeweber-s3-storage' ),
				'running'       => __( 'Running...', 'codeweber-s3-storage' ),
				'idle'          => __( 'Idle', 'codeweber-s3-storage' ),
				'completed'     => __( 'Completed.', 'codeweber-s3-storage' ),
				'cancelled'     => __( 'Cancelled.', 'codeweber-s3-storage' ),
				'failed'        => __( 'Failed.', 'codeweber-s3-storage' ),
				'working'       => __( 'Working...', 'codeweber-s3-storage' ),
				'done'          => __( 'Done', 'codeweber-s3-storage' ),
				'error'         => __( 'Error', 'codeweber-s3-storage' ),
			],
		] );
	}

	public function sanitize( $input ) {
		$out = self::defaults();
		$out['endpoint']        = esc_url_raw( trim( $input['endpoint'] ?? '' ) );
		$out['region']          = sanitize_text_field( $input['region'] ?? 'us-east-1' );
		$out['access_key']      = sanitize_text_field( $input['access_key'] ?? '' );
		$out['secret_key']      = $this->sanitize_secret( $input['secret_key'] ?? '' );
		$out['bucket']          = $this->sanitize_bucket( $input['bucket'] ?? '' );
		$out['path_style']      = empty( $input['path_style'] ) ? 0 : 1;
		$out['verify_ssl']      = empty( $input['verify_ssl'] ) ? 0 : 1;
		$out['public_url']      = esc_url_raw( trim( $input['public_url'] ?? '' ) );
		$out['key_prefix']      = sanitize_text_field( $input['key_prefix'] ?? '{year}/{month}/' );
		$out['storage_mode']    = in_array( $input['storage_mode'] ?? '', [ 'local', 's3', 'mirror' ], true ) ? $input['storage_mode'] : 'local';
		$out['force_https']     = empty( $input['force_https'] ) ? 0 : 1;
		$out['rewrite_content'] = empty( $input['rewrite_content'] ) ? 0 : 1;
		$out['log_level']       = in_array( $input['log_level'] ?? '', [ 'off', 'error', 'info', 'debug' ], true ) ? $input['log_level'] : 'error';
		$out['log_retention']   = max( 1, min( 90, (int) ( $input['log_retention'] ?? 14 ) ) );
		return $out;
	}

	private function sanitize_secret( $value ) {
		$value = (string) $value;
		return preg_replace( '/[^\x20-\x7E]/', '', $value );
	}

	private function sanitize_bucket( $value ) {
		$value = strtolower( (string) $value );
		return preg_replace( '/[^a-z0-9\-\.]/', '', $value );
	}

	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$settings = self::get();
		include CWS3_MODULE_DIR . '/inc/views/settings.php';
	}

	public function ajax_test_connection() {
		check_ajax_referer( 'cws3_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'codeweber-s3-storage' ) ], 403 );
		}

		try {
			$client = Client::factory( self::get() );
			$bucket = self::get()['bucket'];
			if ( ! $bucket ) {
				wp_send_json_error( [ 'message' => __( 'Bucket is not configured.', 'codeweber-s3-storage' ) ] );
			}

			$client->headBucket( [ 'Bucket' => $bucket ] );

			$test_key = '_cws3_test_' . wp_generate_password( 8, false ) . '.txt';
			$client->putObject( [
				'Bucket' => $bucket,
				'Key'    => $test_key,
				'Body'   => 'cws3 connection test',
			] );
			$client->deleteObject( [
				'Bucket' => $bucket,
				'Key'    => $test_key,
			] );

			Logger::info( 'test_connection', 'Connection test OK', [ 'bucket' => $bucket ] );
			wp_send_json_success( [ 'message' => __( 'Bucket reachable, put/delete OK.', 'codeweber-s3-storage' ) ] );
		} catch ( \Throwable $e ) {
			Logger::error( 'test_connection', $e->getMessage() );
			wp_send_json_error( [ 'message' => $e->getMessage() ] );
		}
	}

	public function ajax_clear_errors() {
		check_ajax_referer( 'cws3_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [], 403 );
		}
		\Codeweber\S3Storage\DB\ErrorsTable::clear();
		wp_send_json_success();
	}
}

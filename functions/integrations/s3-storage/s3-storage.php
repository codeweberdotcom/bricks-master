<?php
/**
 * S3 Storage module for the Codeweber theme.
 *
 * Offloads WordPress media to a custom S3-compatible server (MinIO, Ceph, Garage).
 * Gated by Redux option `s3_storage_enabled` with `CWS3_FORCE_ENABLE` override.
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'CWS3_MODULE_LOADED' ) ) {
	return;
}
define( 'CWS3_MODULE_LOADED', true );

define( 'CWS3_VERSION', '0.2.0' );
define( 'CWS3_MODULE_DIR', __DIR__ );
define( 'CWS3_MODULE_URL', get_template_directory_uri() . '/functions/integrations/s3-storage' );
define( 'CWS3_LOG_DIR', WP_CONTENT_DIR . '/uploads/cws3-logs' );

( function () {
	$forced = defined( 'CWS3_FORCE_ENABLE' ) && CWS3_FORCE_ENABLE;

	add_action( 'after_setup_theme', function () use ( $forced ) {
		$enabled = $forced;

		if ( ! $enabled ) {
			$options = get_option( 'redux_demo', [] );
			$enabled = ! empty( $options['s3_storage_enabled'] );
		}

		if ( ! apply_filters( 'cws3_enabled', $enabled ) ) {
			return;
		}

		$autoload = CWS3_MODULE_DIR . '/vendor/autoload.php';
		if ( file_exists( $autoload ) ) {
			require_once $autoload;
		}

		spl_autoload_register( function ( $class ) {
			$prefix = 'Codeweber\\S3Storage\\';
			if ( strpos( $class, $prefix ) !== 0 ) {
				return;
			}
			$relative = substr( $class, strlen( $prefix ) );
			$path     = CWS3_MODULE_DIR . '/inc/' . str_replace( '\\', '/', $relative ) . '.php';
			if ( file_exists( $path ) ) {
				require_once $path;
			}
		} );

		\Codeweber\S3Storage\Module::instance()->boot();
	}, 20 );
} )();

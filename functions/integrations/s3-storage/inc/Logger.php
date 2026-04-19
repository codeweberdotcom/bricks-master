<?php

namespace Codeweber\S3Storage;

use Codeweber\S3Storage\DB\ErrorsTable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Logger {

	const LEVEL_OFF   = 0;
	const LEVEL_ERROR = 1;
	const LEVEL_INFO  = 2;
	const LEVEL_DEBUG = 3;

	private static $level_cache = null;

	public static function debug( string $operation, string $message, array $context = [], int $attachment_id = 0 ) {
		self::log_to_file( self::LEVEL_DEBUG, 'DEBUG', $operation, $message, $context, $attachment_id );
	}

	public static function info( string $operation, string $message, array $context = [], int $attachment_id = 0 ) {
		self::log_to_file( self::LEVEL_INFO, 'INFO', $operation, $message, $context, $attachment_id );
	}

	public static function warning( string $operation, string $message, array $context = [], int $attachment_id = 0 ) {
		self::log_to_file( self::LEVEL_ERROR, 'WARN', $operation, $message, $context, $attachment_id );
	}

	public static function error( string $operation, string $message, array $context = [], int $attachment_id = 0, string $code = '' ) {
		ErrorsTable::log( $operation, $message, self::sanitize_context( $context ), $attachment_id, $code );
		self::log_to_file( self::LEVEL_ERROR, 'ERROR', $operation, $message, $context, $attachment_id );

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$suffix = $attachment_id ? ' (attachment ' . $attachment_id . ')' : '';
			error_log( '[CWS3][' . $operation . '] ' . $message . $suffix );
		}
	}

	public static function list_log_files() {
		$files = glob( CWS3_LOG_DIR . '/cws3-*.log' );
		if ( ! $files ) {
			return [];
		}
		rsort( $files );
		return $files;
	}

	public static function read_log_file( string $filename, int $lines = 500 ) {
		$filename = basename( $filename );
		$path     = CWS3_LOG_DIR . '/' . $filename;
		if ( ! preg_match( '/^cws3-[\d\-]+\.log(\.\d+)?$/', $filename ) || ! file_exists( $path ) ) {
			return '';
		}
		$content = @file_get_contents( $path );
		if ( ! $content ) {
			return '';
		}
		$rows = explode( "\n", trim( $content ) );
		if ( count( $rows ) > $lines ) {
			$rows = array_slice( $rows, -$lines );
		}
		return implode( "\n", $rows );
	}

	public static function clear_logs() {
		foreach ( self::list_log_files() as $path ) {
			@unlink( $path );
		}
	}

	public static function rotate() {
		$settings  = Settings::get();
		$retention = max( 1, (int) $settings['log_retention'] );
		$cutoff    = time() - ( $retention * DAY_IN_SECONDS );
		foreach ( self::list_log_files() as $path ) {
			if ( @filemtime( $path ) < $cutoff ) {
				@unlink( $path );
			}
		}
	}

	private static function log_to_file( int $required_level, string $tag, string $operation, string $message, array $context, int $attachment_id ) {
		$current = self::current_level();
		if ( $current < $required_level ) {
			return;
		}

		if ( ! file_exists( CWS3_LOG_DIR ) ) {
			@wp_mkdir_p( CWS3_LOG_DIR );
		}
		if ( ! is_writable( CWS3_LOG_DIR ) ) {
			return;
		}

		$context = self::sanitize_context( $context );
		$line    = sprintf(
			'[%s] [%s] [%s]%s %s%s',
			gmdate( 'Y-m-d H:i:s' ),
			$tag,
			$operation,
			$attachment_id ? ' attachment=' . $attachment_id : '',
			$message,
			$context ? ' ' . wp_json_encode( $context ) : ''
		);

		$file = CWS3_LOG_DIR . '/cws3-' . gmdate( 'Y-m-d' ) . '.log';
		@file_put_contents( $file, $line . PHP_EOL, FILE_APPEND | LOCK_EX );

		if ( mt_rand( 1, 200 ) === 1 ) {
			self::rotate();
		}
	}

	public static function current_level() {
		if ( self::$level_cache !== null ) {
			return self::$level_cache;
		}
		$settings = Settings::get();
		$map      = [
			'off'   => self::LEVEL_OFF,
			'error' => self::LEVEL_ERROR,
			'info'  => self::LEVEL_INFO,
			'debug' => self::LEVEL_DEBUG,
		];
		self::$level_cache = $map[ $settings['log_level'] ] ?? self::LEVEL_ERROR;
		return self::$level_cache;
	}

	public static function reset_level_cache() {
		self::$level_cache = null;
	}

	private static function sanitize_context( array $context ) {
		$redact = [ 'secret', 'secret_key', 'access_key', 'password', 'Authorization', 'key' ];
		foreach ( $context as $key => $value ) {
			if ( in_array( strtolower( (string) $key ), array_map( 'strtolower', $redact ), true ) ) {
				$context[ $key ] = '***';
			}
		}
		return $context;
	}
}

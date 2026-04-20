<?php

namespace Codeweber\S3Storage;

use Codeweber\S3Storage\DB\ItemsTable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Uploader {

	const DEFERRED_HOOK = 'cws3_deferred_offload';

	public function register() {
		add_filter( 'wp_generate_attachment_metadata', [ $this, 'on_generate_metadata' ], 20, 2 );
		add_filter( 'wp_update_attachment_metadata', [ $this, 'on_update_metadata' ], 20, 2 );
		add_action( self::DEFERRED_HOOK, [ $this, 'cron_offload' ], 10, 1 );
	}

	public function on_generate_metadata( $metadata, $attachment_id ) {
		if ( ! StorageMode::uses_s3() ) {
			return $metadata;
		}
		$this->offload_attachment( (int) $attachment_id, $metadata );
		return $metadata;
	}

	public function on_update_metadata( $metadata, $attachment_id ) {
		if ( ! StorageMode::uses_s3() ) {
			return $metadata;
		}
		// Already on S3 → thumbnails were regenerated; queue deferred re-offload.
		// Not yet offloaded → on_generate_metadata handles the initial upload.
		if ( ItemsTable::is_offloaded( (int) $attachment_id ) ) {
			$this->schedule_deferred_offload( (int) $attachment_id );
		}
		return $metadata;
	}

	private function schedule_deferred_offload( int $attachment_id ) {
		if ( ! wp_next_scheduled( self::DEFERRED_HOOK, [ $attachment_id ] ) ) {
			wp_schedule_single_event( time() + 5, self::DEFERRED_HOOK, [ $attachment_id ] );
			if ( function_exists( 'spawn_cron' ) ) {
				spawn_cron();
			}
		}
	}

	public function cron_offload( int $attachment_id ) {
		$this->offload_attachment( $attachment_id );
	}

	public function offload_attachment( int $attachment_id, $metadata = null, bool $force_remove_local = false ) {
		$settings = Settings::get();
		if ( empty( $settings['bucket'] ) ) {
			Logger::error( 'offload', 'Bucket is not configured.', [], $attachment_id, 'no_bucket' );
			return false;
		}

		try {
			$client = Client::factory( $settings );
		} catch ( \Throwable $e ) {
			Logger::error( 'offload', $e->getMessage(), [], $attachment_id, 'client_init' );
			return false;
		}

		if ( $metadata === null ) {
			$metadata = wp_get_attachment_metadata( $attachment_id );
		}

		$paths = $this->collect_file_paths( $attachment_id, $metadata );
		if ( empty( $paths ) ) {
			Logger::warning( 'offload', 'No local files to offload.', [], $attachment_id );
			return false;
		}

		$remove_local = $force_remove_local || StorageMode::removes_local_after_offload();
		$success_any  = false;
		$started      = microtime( true );

		foreach ( $paths as $source => $path ) {
			[ $source_type, $source_id ] = $this->split_source( $source );
			$key = $this->build_key( $settings, $path );

			try {
				$req_started = microtime( true );
				$put_args = [
					'Bucket'      => $settings['bucket'],
					'Key'         => $key,
					'SourceFile'  => $path,
					'ACL'         => 'public-read',
					'ContentType' => \Codeweber\S3Storage\Services\MetadataService::resolve_mime_for_attachment( $attachment_id, $key ),
				];
				$cache_control = \Codeweber\S3Storage\Services\MetadataService::build_cache_control( $settings );
				if ( $cache_control ) {
					$put_args['CacheControl'] = $cache_control;
				}
				$client->putObject( $put_args );
				$duration = round( ( microtime( true ) - $req_started ) * 1000 );
				$size     = @filesize( $path );

				Logger::info( 'offload', 'PUT ok', [
					'bucket'   => $settings['bucket'],
					'object'   => $key,
					'size'     => $size,
					'duration' => $duration,
				], $attachment_id );

				ItemsTable::upsert( $attachment_id, $source_type, $source_id, [
					'bucket'       => $settings['bucket'],
					'object_key'   => $key,
					'region'       => $settings['region'],
					'provider'     => 's3',
					'file_size'    => $size ? (int) $size : 0,
					'is_offloaded' => 1,
					'is_local'     => 1,
				] );

				$success_any = true;

				if ( $remove_local ) {
					if ( @unlink( $path ) ) {
						ItemsTable::upsert( $attachment_id, $source_type, $source_id, [ 'is_local' => 0 ] );
						Logger::info( 'offload', 'Local file removed after offload.', [ 'path' => basename( $path ) ], $attachment_id );
					}
				}
			} catch ( \Throwable $e ) {
				Logger::error( 'offload', $e->getMessage(), [ 'object' => $key, 'source' => $source ], $attachment_id, 'put_object' );
			}
		}

		if ( $success_any ) {
			Logger::info( 'offload', 'Attachment offload complete.', [
				'files'    => count( $paths ),
				'duration' => round( ( microtime( true ) - $started ) * 1000 ),
			], $attachment_id );
		}

		return $success_any;
	}

	private function collect_file_paths( int $attachment_id, $metadata ) {
		$paths = [];

		$original = get_attached_file( $attachment_id, true );
		if ( $original && file_exists( $original ) ) {
			$paths['original'] = $original;
		}

		if ( ! is_array( $metadata ) ) {
			return $paths;
		}

		$upload_dir = wp_get_upload_dir();
		$base_dir   = $upload_dir['basedir'];

		if ( ! empty( $metadata['file'] ) ) {
			$full = trailingslashit( $base_dir ) . ltrim( dirname( $metadata['file'] ), './' );
			$full = rtrim( $full, '/' );
		} else {
			$full = $original ? dirname( $original ) : $base_dir;
		}

		if ( ! empty( $metadata['original_image'] ) ) {
			$p = trailingslashit( $full ) . $metadata['original_image'];
			if ( file_exists( $p ) ) {
				$paths['original_image:' . $metadata['original_image']] = $p;
			}
		}

		if ( ! empty( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ) {
			foreach ( $metadata['sizes'] as $size_name => $size ) {
				if ( empty( $size['file'] ) ) {
					continue;
				}
				$p = trailingslashit( $full ) . $size['file'];
				if ( file_exists( $p ) ) {
					$paths[ 'size:' . $size_name ] = $p;
				}
			}
		}

		return $paths;
	}

	private function split_source( string $source ) {
		if ( strpos( $source, ':' ) === false ) {
			return [ $source, '' ];
		}
		[ $type, $id ] = explode( ':', $source, 2 );
		return [ $type, $id ];
	}

	private function build_key( array $settings, string $path ) {
		$upload_dir = wp_get_upload_dir();
		$base       = trailingslashit( $upload_dir['basedir'] );
		$relative   = ltrim( str_replace( '\\', '/', str_replace( $base, '', $path ) ), '/' );

		$prefix = (string) $settings['key_prefix'];
		if ( $prefix === '' || $prefix === '{year}/{month}/' ) {
			return $relative;
		}

		$prefix = strtr( $prefix, [
			'{year}'  => gmdate( 'Y' ),
			'{month}' => gmdate( 'm' ),
			'{day}'   => gmdate( 'd' ),
		] );

		$prefix   = trim( $prefix, '/' );
		$filename = basename( $relative );
		return $prefix . '/' . $filename;
	}
}

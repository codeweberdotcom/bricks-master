<?php

namespace Codeweber\S3Storage;

use Codeweber\S3Storage\DB\ItemsTable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UrlRewriter {

	private $cache = [];

	public function register() {
		add_filter( 'wp_get_attachment_url', [ $this, 'filter_attachment_url' ], 10, 2 );
		add_filter( 'wp_get_attachment_image_src', [ $this, 'filter_image_src' ], 10, 4 );
		add_filter( 'wp_calculate_image_srcset', [ $this, 'filter_srcset' ], 10, 5 );

		$settings = Settings::get();
		if ( ! empty( $settings['rewrite_content'] ) ) {
			add_filter( 'the_content', [ $this, 'filter_content' ], 20 );
		}
	}

	public function filter_attachment_url( $url, $attachment_id ) {
		$remote = $this->remote_url_for( (int) $attachment_id, 'original', '' );
		return $remote ?: $url;
	}

	public function filter_image_src( $image, $attachment_id, $size, $icon ) {
		if ( ! is_array( $image ) || empty( $image[0] ) ) {
			return $image;
		}
		$size_id = is_string( $size ) ? $size : '';
		$remote  = $this->remote_url_for( (int) $attachment_id, 'size', $size_id );
		if ( $remote ) {
			$image[0] = $remote;
			return $image;
		}
		$remote_orig = $this->remote_url_for( (int) $attachment_id, 'original', '' );
		if ( $remote_orig ) {
			$image[0] = $this->swap_filename_in_url( $remote_orig, basename( wp_parse_url( $image[0], PHP_URL_PATH ) ) );
		}
		return $image;
	}

	public function filter_srcset( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {
		if ( ! is_array( $sources ) ) {
			return $sources;
		}
		$items = $this->items_for( (int) $attachment_id );
		if ( empty( $items ) ) {
			return $sources;
		}
		$by_filename = [];
		foreach ( $items as $item ) {
			$by_filename[ basename( $item->object_key ) ] = $item;
		}
		$settings = Settings::get();
		foreach ( $sources as $width => $data ) {
			if ( empty( $data['url'] ) ) {
				continue;
			}
			$filename = basename( wp_parse_url( $data['url'], PHP_URL_PATH ) );
			if ( isset( $by_filename[ $filename ] ) ) {
				$sources[ $width ]['url'] = Client::public_url_for_key( $settings, $by_filename[ $filename ]->object_key );
			}
		}
		return $sources;
	}

	public function filter_content( $content ) {
		$upload_dir = wp_get_upload_dir();
		$base_url   = $upload_dir['baseurl'];
		if ( ! $base_url || strpos( $content, $base_url ) === false ) {
			return $content;
		}
		$settings = Settings::get();
		$target   = rtrim(
			! empty( $settings['public_url'] )
				? $settings['public_url']
				: $settings['endpoint'] . ( ! empty( $settings['path_style'] ) ? '/' . $settings['bucket'] : '' ),
			'/'
		);
		if ( ! $target ) {
			return $content;
		}
		return str_replace( $base_url, $target, $content );
	}

	private function items_for( int $attachment_id ) {
		if ( ! isset( $this->cache[ $attachment_id ] ) ) {
			$this->cache[ $attachment_id ] = ItemsTable::get_by_attachment( $attachment_id );
		}
		return $this->cache[ $attachment_id ];
	}

	private function remote_url_for( int $attachment_id, string $source_type, string $source_id ) {
		$items = $this->items_for( $attachment_id );
		foreach ( $items as $item ) {
			if ( (int) $item->is_offloaded !== 1 ) {
				continue;
			}
			if ( $item->source_type === $source_type && $item->source_id === $source_id ) {
				return Client::public_url_for_key( Settings::get(), $item->object_key );
			}
		}
		return null;
	}

	private function swap_filename_in_url( string $url, string $new_filename ) {
		$parts = wp_parse_url( $url );
		if ( empty( $parts['path'] ) ) {
			return $url;
		}
		$parts['path'] = preg_replace( '#/[^/]+$#', '/' . $new_filename, $parts['path'] );
		$scheme        = $parts['scheme'] ?? 'https';
		$host          = $parts['host'] ?? '';
		$port          = isset( $parts['port'] ) ? ':' . $parts['port'] : '';
		return $scheme . '://' . $host . $port . $parts['path'];
	}
}

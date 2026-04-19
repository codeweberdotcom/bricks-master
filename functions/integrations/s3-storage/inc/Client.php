<?php

namespace Codeweber\S3Storage;

use Aws\S3\S3Client;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Client {

	public static function factory( array $settings ) {
		if ( ! class_exists( S3Client::class ) ) {
			throw new \RuntimeException( 'AWS SDK not loaded. Run `composer install` in the module directory.' );
		}

		if ( empty( $settings['endpoint'] ) ) {
			throw new \RuntimeException( 'Endpoint URL is not configured.' );
		}
		if ( empty( $settings['access_key'] ) || empty( $settings['secret_key'] ) ) {
			throw new \RuntimeException( 'Access key or secret key is not configured.' );
		}

		$args = [
			'version'                 => 'latest',
			'region'                  => $settings['region'] ?: 'us-east-1',
			'endpoint'                => rtrim( $settings['endpoint'], '/' ),
			'use_path_style_endpoint' => ! empty( $settings['path_style'] ),
			'credentials'             => [
				'key'    => $settings['access_key'],
				'secret' => $settings['secret_key'],
			],
		];

		if ( empty( $settings['verify_ssl'] ) ) {
			$args['http'] = [ 'verify' => false ];
		}

		return new S3Client( $args );
	}

	public static function public_url_for_key( array $settings, string $key ) {
		$base = ! empty( $settings['public_url'] )
			? rtrim( $settings['public_url'], '/' )
			: self::build_default_base( $settings );

		$url = $base . '/' . ltrim( $key, '/' );

		if ( ! empty( $settings['force_https'] ) ) {
			$url = preg_replace( '#^http://#i', 'https://', $url );
		}

		return $url;
	}

	private static function build_default_base( array $settings ) {
		$endpoint = rtrim( $settings['endpoint'], '/' );
		$bucket   = $settings['bucket'];
		if ( empty( $bucket ) ) {
			return $endpoint;
		}
		if ( ! empty( $settings['path_style'] ) ) {
			return $endpoint . '/' . $bucket;
		}
		$parts = wp_parse_url( $endpoint );
		if ( empty( $parts['host'] ) ) {
			return $endpoint . '/' . $bucket;
		}
		$scheme = $parts['scheme'] ?? 'https';
		$port   = isset( $parts['port'] ) ? ':' . $parts['port'] : '';
		return $scheme . '://' . $bucket . '.' . $parts['host'] . $port;
	}
}

<?php

namespace Codeweber\S3Storage;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class StorageMode {

	const LOCAL  = 'local';
	const S3     = 's3';
	const MIRROR = 'mirror';

	public static function current() {
		$settings = Settings::get();
		return in_array( $settings['storage_mode'], [ self::LOCAL, self::S3, self::MIRROR ], true )
			? $settings['storage_mode']
			: self::LOCAL;
	}

	public static function uses_s3() {
		return in_array( self::current(), [ self::S3, self::MIRROR ], true );
	}

	public static function removes_local_after_offload() {
		return self::current() === self::S3;
	}

	public static function keeps_local() {
		return in_array( self::current(), [ self::LOCAL, self::MIRROR ], true );
	}
}

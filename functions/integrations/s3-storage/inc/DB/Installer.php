<?php

namespace Codeweber\S3Storage\DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Installer {

	const DB_VERSION = '2';

	public static function maybe_install() {
		if ( get_option( 'cws3_db_version' ) === self::DB_VERSION ) {
			return;
		}
		self::install();
	}

	public static function install() {
		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}
		dbDelta( ItemsTable::schema() );
		dbDelta( ErrorsTable::schema() );
		dbDelta( JobsTable::schema() );
		update_option( 'cws3_db_version', self::DB_VERSION, false );

		if ( get_option( 'cws3_settings' ) === false ) {
			update_option( 'cws3_settings', \Codeweber\S3Storage\Settings::defaults(), false );
		}
	}
}

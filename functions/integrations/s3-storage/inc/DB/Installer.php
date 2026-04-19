<?php

namespace Codeweber\S3Storage\DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Installer {

	const DB_VERSION = '3';

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
		$previous = get_option( 'cws3_db_version' );

		dbDelta( ItemsTable::schema() );
		dbDelta( ErrorsTable::schema() );
		dbDelta( JobsTable::schema() );

		if ( get_option( 'cws3_settings' ) === false ) {
			update_option( 'cws3_settings', \Codeweber\S3Storage\Settings::defaults(), false );
		}

		if ( $previous && version_compare( (string) $previous, '3', '<' ) ) {
			$settings = get_option( 'cws3_settings', [] );
			if ( is_array( $settings ) ) {
				$settings['rewrite_content'] = 1;
				update_option( 'cws3_settings', $settings, false );
			}
		}

		update_option( 'cws3_db_version', self::DB_VERSION, false );
	}
}

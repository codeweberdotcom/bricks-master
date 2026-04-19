<?php

namespace Codeweber\S3Storage\Tools;

use Codeweber\S3Storage\DB\ItemsTable;
use Codeweber\S3Storage\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ToolsPage {

	public function register() {
		add_action( 'admin_menu', [ $this, 'add_menu' ] );
	}

	public function add_menu() {
		add_management_page(
			__( 'S3 Storage', 'codeweber' ),
			__( 'S3 Storage', 'codeweber' ),
			'manage_options',
			'cws3-tools',
			[ $this, 'render' ]
		);
	}

	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$settings = Settings::get();
		$stats    = $this->stats();
		include CWS3_MODULE_DIR . '/inc/views/tools.php';
	}

	private function stats() {
		global $wpdb;
		return [
			'total'        => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment'" ),
			'offloaded'    => ItemsTable::count_offloaded(),
			'orphan_local' => (int) $wpdb->get_var( 'SELECT COUNT(DISTINCT attachment_id) FROM ' . ItemsTable::name() . ' WHERE is_offloaded = 1 AND is_local = 1' ),
			'remote_only'  => (int) $wpdb->get_var( 'SELECT COUNT(DISTINCT attachment_id) FROM ' . ItemsTable::name() . ' WHERE is_offloaded = 1 AND is_local = 0' ),
		];
	}
}

<?php

namespace Codeweber\S3Storage\Tools;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Uninstaller {

	public function estimate_total() {
		return ( new Restore() )->estimate_total();
	}

	public function run_batch( $job ) {
		$result = ( new Restore() )->run_batch( $job );
		if ( ! empty( $result['done'] ) ) {
			update_option( 'cws3_safe_to_uninstall', 1, false );
		}
		return $result;
	}
}

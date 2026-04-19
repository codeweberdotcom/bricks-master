<?php

namespace Codeweber\S3Storage;

use Codeweber\S3Storage\Admin\AttachmentController;
use Codeweber\S3Storage\Admin\AttachmentMetabox;
use Codeweber\S3Storage\Admin\BulkActions;
use Codeweber\S3Storage\Admin\LogViewer;
use Codeweber\S3Storage\Admin\MediaLibrary;
use Codeweber\S3Storage\DB\Installer;
use Codeweber\S3Storage\Tools\ToolsPage;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Module {

	private static $instance = null;

	public static function instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function boot() {
		add_action( 'admin_init', [ Installer::class, 'maybe_install' ] );

		( new Settings() )->register();
		( new ToolsPage() )->register();
		( new LogViewer() )->register();
		( new MediaLibrary() )->register();
		( new AttachmentMetabox() )->register();
		( new BulkActions() )->register();
		( new AttachmentController() )->register();
		( new Queue\BatchRunner() )->register_ajax();

		( new Uploader() )->register();
		( new UrlRewriter() )->register();
		( new Deleter() )->register();
		( new Queue\BatchRunner() )->register_cron();
	}
}

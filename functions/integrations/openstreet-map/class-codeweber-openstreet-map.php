<?php
/**
 * CodeWeber OpenStreetMap (Leaflet) integration
 *
 * Enqueues Leaflet.js + MarkerCluster from CDN and the theme's
 * initialisation script on any frontend page that contains the block.
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Codeweber_OpenStreet_Map {

	private static ?self $instance = null;

	private string $version = '1.0.0';
	private string $leaflet_version = '1.9.4';
	private string $cluster_version = '1.5.3';
	private string $path;
	private string $url;

	private function __construct() {
		$this->path = get_template_directory() . '/functions/integrations/openstreet-map';
		$this->url  = get_template_directory_uri() . '/functions/integrations/openstreet-map';

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
	}

	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function enqueue_scripts(): void {
		if ( ! $this->page_has_block() ) {
			return;
		}

		$this->register_leaflet();

		wp_enqueue_style( 'leaflet' );
		wp_enqueue_style( 'leaflet-markercluster' );
		wp_enqueue_style( 'leaflet-markercluster-default' );

		wp_enqueue_script(
			'codeweber-openstreet-map',
			$this->url . '/assets/js/openstreet-map.js',
			[ 'leaflet', 'leaflet-markercluster' ],
			$this->version,
			true
		);
	}

	private function page_has_block(): bool {
		global $post;

		if ( is_a( $post, 'WP_Post' ) && has_block( 'codeweber-blocks/openstreet-map', $post ) ) {
			return true;
		}

		// Also check queried object for archives/templates.
		$queried = get_queried_object();
		if ( is_a( $queried, 'WP_Post' ) && has_block( 'codeweber-blocks/openstreet-map', $queried ) ) {
			return true;
		}

		return false;
	}

	public function enqueue_admin_scripts( string $hook_suffix ): void {
		$screen          = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		$is_block_editor = $screen && method_exists( $screen, 'is_block_editor' ) && $screen->is_block_editor();

		if ( ! $is_block_editor ) {
			return;
		}

		$this->register_leaflet();

		// Load Leaflet in the editor so the marker picker map works.
		wp_enqueue_style( 'leaflet' );
		wp_enqueue_script( 'leaflet' );
	}

	private function register_leaflet(): void {
		$leaflet_base    = "https://unpkg.com/leaflet@{$this->leaflet_version}/dist";
		$cluster_base    = "https://unpkg.com/leaflet.markercluster@{$this->cluster_version}/dist";

		if ( ! wp_script_is( 'leaflet', 'registered' ) ) {
			wp_register_style(
				'leaflet',
				"{$leaflet_base}/leaflet.css",
				[],
				$this->leaflet_version
			);

			wp_register_script(
				'leaflet',
				"{$leaflet_base}/leaflet.js",
				[],
				$this->leaflet_version,
				true
			);

			wp_register_style(
				'leaflet-markercluster',
				"{$cluster_base}/MarkerCluster.css",
				[ 'leaflet' ],
				$this->cluster_version
			);

			wp_register_style(
				'leaflet-markercluster-default',
				"{$cluster_base}/MarkerCluster.Default.css",
				[ 'leaflet-markercluster' ],
				$this->cluster_version
			);

			wp_register_script(
				'leaflet-markercluster',
				"{$cluster_base}/leaflet.markercluster.js",
				[ 'leaflet' ],
				$this->cluster_version,
				true
			);
		}
	}
}

<?php
/**
 * Stock Photos — search & import free images from Unsplash / Pexels / Pixabay.
 *
 * Surfaces:
 *   1. A "Free Photos" tab inside the wp.media modal (insert / featured image / blocks).
 *   2. A button on the Media Library screen (upload.php).
 *   3. A dedicated admin page (Media → Free Photos).
 *
 * API keys live in Redux (option `redux_demo`) and never reach the browser —
 * all provider requests go through a server-side AJAX proxy.
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/inc/proxy.php';
require_once __DIR__ . '/inc/import.php';

/**
 * Read a value from the Redux options array.
 *
 * @param string $key     Option id.
 * @param mixed  $default Fallback.
 * @return mixed
 */
function cw_stock_photos_option( $key, $default = '' ) {
	$opts = get_option( 'redux_demo' );
	return ( is_array( $opts ) && isset( $opts[ $key ] ) ) ? $opts[ $key ] : $default;
}

/**
 * Whether the module is enabled in Redux.
 *
 * @return bool
 */
function cw_stock_photos_enabled() {
	return (bool) cw_stock_photos_option( 'stock_photos_enabled', false );
}

/**
 * Active providers that also have a key configured.
 *
 * @return array<string,array> Keyed by provider slug.
 */
function cw_stock_photos_providers() {
	$selected = cw_stock_photos_option( 'stock_photos_providers', array() );
	$selected = is_array( $selected ) ? $selected : array();

	$registry = array(
		'unsplash'  => array(
			'label'   => 'Unsplash',
			'key'     => trim( (string) cw_stock_photos_option( 'unsplash_access_key', '' ) ),
			'keyless' => false,
			'license' => __( 'Free to use. Attribution to the photographer is appreciated.', 'codeweber' ),
		),
		'pexels'    => array(
			'label'   => 'Pexels',
			'key'     => trim( (string) cw_stock_photos_option( 'pexels_api_key', '' ) ),
			'keyless' => false,
			'license' => __( 'Free to use. Attribution is appreciated but not required.', 'codeweber' ),
		),
		'pixabay'   => array(
			'label'   => 'Pixabay',
			'key'     => trim( (string) cw_stock_photos_option( 'pixabay_api_key', '' ) ),
			'keyless' => false,
			'license' => __( 'Free to use. No attribution required.', 'codeweber' ),
		),
		'openverse' => array(
			'label'   => 'Openverse',
			'key'     => '',
			'keyless' => true,
			'license' => __( 'Creative Commons / Public Domain. Check each item\'s license; attribution often required.', 'codeweber' ),
		),
	);

	$out = array();
	foreach ( $registry as $slug => $data ) {
		if ( empty( $selected[ $slug ] ) ) {
			continue;
		}
		// Keyless providers (Openverse) are available without a key; others need one.
		if ( ! empty( $data['keyless'] ) || '' !== $data['key'] ) {
			$out[ $slug ] = $data;
		}
	}
	return $out;
}

/**
 * Default request args for the module's outbound HTTP calls.
 *
 * Delegates to the shared proxy helper so requests are routed through the
 * configured proxy when the "Stock Photos" scope is enabled.
 *
 * @param array $extra Extra args to merge.
 * @return array
 */
function cw_stock_photos_request_args( $extra = array() ) {
	if ( function_exists( 'cw_proxy_request_args' ) ) {
		return cw_proxy_request_args( 'stock_photos', $extra );
	}
	return array_merge( array( 'timeout' => 15 ), $extra );
}

/**
 * Register the dedicated admin page under the Media menu.
 */
function cw_stock_photos_admin_menu() {
	if ( ! cw_stock_photos_enabled() ) {
		return;
	}

	add_submenu_page(
		'upload.php',
		esc_html__( 'Free Photos', 'codeweber' ),
		esc_html__( 'Free Photos', 'codeweber' ),
		'upload_files',
		'cw-stock-photos',
		'cw_stock_photos_render_page'
	);
}
add_action( 'admin_menu', 'cw_stock_photos_admin_menu' );

/**
 * Render the dedicated admin page.
 */
function cw_stock_photos_render_page() {
	require __DIR__ . '/views/page.php';
}

/**
 * Enqueue admin assets where the search UI is needed.
 *
 * @param string $hook Current admin page hook.
 */
function cw_stock_photos_enqueue( $hook ) {
	if ( ! cw_stock_photos_enabled() || ! current_user_can( 'upload_files' ) ) {
		return;
	}

	$providers = cw_stock_photos_providers();
	if ( empty( $providers ) ) {
		return;
	}

	// Surfaces: post editor (media modal), media library, our own page.
	$is_editor  = in_array( $hook, array( 'post.php', 'post-new.php', 'widgets.php', 'site-editor.php' ), true );
	$is_library = ( 'upload.php' === $hook );
	$is_page    = ( 'media_page_cw-stock-photos' === $hook );

	if ( ! $is_editor && ! $is_library && ! $is_page ) {
		return;
	}

	// Media modal integration relies on wp.media.
	if ( $is_editor || $is_library ) {
		wp_enqueue_media();
	}

	$base = get_template_directory_uri() . '/functions/integrations/stock-photos/assets';
	$dir  = get_template_directory() . '/functions/integrations/stock-photos/assets';
	$cssv = file_exists( $dir . '/stock-photos.css' ) ? filemtime( $dir . '/stock-photos.css' ) : '1.0.0';
	$jsv  = file_exists( $dir . '/stock-photos.js' ) ? filemtime( $dir . '/stock-photos.js' ) : '1.0.0';

	wp_enqueue_style(
		'cw-stock-photos',
		$base . '/stock-photos.css',
		array(),
		$cssv
	);

	wp_enqueue_script(
		'cw-stock-photos',
		$base . '/stock-photos.js',
		array( 'jquery' ),
		$jsv,
		true
	);

	// Provider config for the browser — labels & licenses only, never keys.
	$provider_cfg = array();
	foreach ( $providers as $slug => $data ) {
		$provider_cfg[] = array(
			'slug'    => $slug,
			'label'   => $data['label'],
			'license' => $data['license'],
		);
	}

	wp_localize_script(
		'cw-stock-photos',
		'cwStockPhotos',
		array(
			'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
			'nonce'     => wp_create_nonce( 'cw_stock_photos' ),
			'providers' => $provider_cfg,
			'context'   => array(
				'editor'  => $is_editor,
				'library' => $is_library,
				'page'    => $is_page,
			),
			'perPage'   => 24,
			'i18n'      => array(
				'tabTitle'    => __( 'Free Photos', 'codeweber' ),
				'searchPh'    => __( 'Search free photos…', 'codeweber' ),
				'search'      => __( 'Search', 'codeweber' ),
				'loadMore'    => __( 'Load more', 'codeweber' ),
				'importing'   => __( 'Importing…', 'codeweber' ),
				'import'      => __( 'Import', 'codeweber' ),
				'imported'    => __( 'Imported', 'codeweber' ),
				'noResults'   => __( 'Nothing found. Try another query.', 'codeweber' ),
				'error'       => __( 'Request error. Try again.', 'codeweber' ),
				'photoBy'     => __( 'Photo by', 'codeweber' ),
				'on'          => __( 'on', 'codeweber' ),
				'openLibrary' => __( 'Open in Media Library', 'codeweber' ),
				'startHint'   => __( 'Enter a query and press Search to find free photos.', 'codeweber' ),
			),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'cw_stock_photos_enqueue' );

/**
 * Add a "Free Photos" button to the Media Library toolbar (upload.php, list & grid).
 * The button is a hook the JS picks up to open the search modal.
 */
function cw_stock_photos_library_button() {
	if ( ! cw_stock_photos_enabled() || empty( cw_stock_photos_providers() ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || 'upload' !== $screen->id ) {
		return;
	}
	?>
	<script type="text/template" id="cw-stock-photos-library-trigger">
		<button type="button" class="button button-primary cw-stock-open"><?php esc_html_e( 'Free Photos', 'codeweber' ); ?></button>
	</script>
	<?php
}
add_action( 'admin_head-upload.php', 'cw_stock_photos_library_button' );

<?php
/**
 * Projects Settings Page
 *
 * Страница настроек «Проекты → Настройки».
 * Опция хранится в: codeweber_projects_settings
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Menu
// ---------------------------------------------------------------------------

function codeweber_projects_settings_register_page(): void {
	add_submenu_page(
		'edit.php?post_type=projects',
		__( 'Projects Settings', 'codeweber' ),
		__( 'Settings', 'codeweber' ),
		'manage_options',
		'codeweber-projects-settings',
		'codeweber_projects_settings_render_page'
	);
}
add_action( 'admin_menu', 'codeweber_projects_settings_register_page' );

// ---------------------------------------------------------------------------
// Settings registration
// ---------------------------------------------------------------------------

function codeweber_projects_settings_register(): void {
	register_setting(
		'codeweber_projects_settings_group',
		'codeweber_projects_settings',
		[
			'sanitize_callback' => 'codeweber_projects_settings_sanitize',
			'default'           => [],
		]
	);

	add_settings_section(
		'codeweber_projects_map',
		__( 'Map', 'codeweber' ),
		null,
		'codeweber-projects-settings'
	);

	add_settings_field(
		'show_map',
		__( 'Show map of projects', 'codeweber' ),
		'codeweber_projects_field_show_map',
		'codeweber-projects-settings',
		'codeweber_projects_map'
	);
}
add_action( 'admin_init', 'codeweber_projects_settings_register' );

// ---------------------------------------------------------------------------
// Helper
// ---------------------------------------------------------------------------

function codeweber_projects_settings_get( string $key, $default = '' ) {
	$options = get_option( 'codeweber_projects_settings', [] );
	return $options[ $key ] ?? $default;
}

// ---------------------------------------------------------------------------
// Field renderers
// ---------------------------------------------------------------------------

function codeweber_projects_field_show_map(): void {
	$val = codeweber_projects_settings_get( 'show_map', '1' );
	echo '<label><input type="checkbox" name="codeweber_projects_settings[show_map]" value="1" ' . checked( $val, '1', false ) . '> ';
	esc_html_e( 'Show «Show on map» button and map offcanvas on single project pages', 'codeweber' );
	echo '</label>';
	echo '<p class="description">' . esc_html__( 'Requires latitude and longitude to be filled in project settings.', 'codeweber' ) . '</p>';
}

// ---------------------------------------------------------------------------
// Sanitize
// ---------------------------------------------------------------------------

function codeweber_projects_settings_sanitize( array $input ): array {
	return [
		'show_map' => isset( $input['show_map'] ) ? '1' : '0',
	];
}

// ---------------------------------------------------------------------------
// Page render
// ---------------------------------------------------------------------------

function codeweber_projects_settings_render_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Projects Settings', 'codeweber' ); ?></h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'codeweber_projects_settings_group' );
			do_settings_sections( 'codeweber-projects-settings' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

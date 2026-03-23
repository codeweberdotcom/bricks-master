<?php
/**
 * Events Settings Page
 *
 * Страница настроек «Мероприятия → Настройки».
 * Опция хранится в: codeweber_events_settings
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function codeweber_events_settings_register_page(): void {
	add_submenu_page(
		'edit.php?post_type=events',
		__( 'Events Settings', 'codeweber' ),
		__( 'Settings', 'codeweber' ),
		'manage_options',
		'codeweber-events-settings',
		'codeweber_events_settings_render_page'
	);
}
add_action( 'admin_menu', 'codeweber_events_settings_register_page' );

function codeweber_events_settings_register(): void {
	register_setting(
		'codeweber_events_settings_group',
		'codeweber_events_settings',
		[
			'sanitize_callback' => 'codeweber_events_settings_sanitize',
			'default'           => [],
		]
	);

	// Section: Frontend display
	add_settings_section(
		'codeweber_events_frontend',
		__( 'Frontend Display', 'codeweber' ),
		null,
		'codeweber-events-settings'
	);

	add_settings_field(
		'show_seats_taken',
		__( 'Show booked seats count', 'codeweber' ),
		'codeweber_events_field_show_seats_taken',
		'codeweber-events-settings',
		'codeweber_events_frontend'
	);

	add_settings_field(
		'show_seats_left',
		__( 'Show available seats count', 'codeweber' ),
		'codeweber_events_field_show_seats_left',
		'codeweber-events-settings',
		'codeweber_events_frontend'
	);

	add_settings_field(
		'show_seats_progress',
		__( 'Show seats progress bar', 'codeweber' ),
		'codeweber_events_field_show_seats_progress',
		'codeweber-events-settings',
		'codeweber_events_frontend'
	);

	// Section: Registration form
	add_settings_section(
		'codeweber_events_registration',
		__( 'Registration Form', 'codeweber' ),
		null,
		'codeweber-events-settings'
	);

	add_settings_field(
		'btn_register_text',
		__( 'Register button text', 'codeweber' ),
		'codeweber_events_field_btn_register_text',
		'codeweber-events-settings',
		'codeweber_events_registration'
	);

	add_settings_field(
		'no_seats_text',
		__( 'Text when no seats available', 'codeweber' ),
		'codeweber_events_field_no_seats_text',
		'codeweber-events-settings',
		'codeweber_events_registration'
	);

	add_settings_field(
		'success_message',
		__( 'Success message after registration', 'codeweber' ),
		'codeweber_events_field_success_message',
		'codeweber-events-settings',
		'codeweber_events_registration'
	);

	// Section: Notifications
	add_settings_section(
		'codeweber_events_notifications',
		__( 'Notifications', 'codeweber' ),
		null,
		'codeweber-events-settings'
	);

	add_settings_field(
		'notify_email',
		__( 'Notification email for new registrations', 'codeweber' ),
		'codeweber_events_field_notify_email',
		'codeweber-events-settings',
		'codeweber_events_notifications'
	);
}
add_action( 'admin_init', 'codeweber_events_settings_register' );

// ---------------------------------------------------------------------------
// Field renderers
// ---------------------------------------------------------------------------

function codeweber_events_settings_get( string $key, $default = '' ) {
	$options = get_option( 'codeweber_events_settings', [] );
	return $options[ $key ] ?? $default;
}

function codeweber_events_field_show_seats_taken(): void {
	$val = codeweber_events_settings_get( 'show_seats_taken', '1' );
	echo '<label><input type="checkbox" name="codeweber_events_settings[show_seats_taken]" value="1" ' . checked( $val, '1', false ) . '> ';
	esc_html_e( 'Show how many seats are already taken', 'codeweber' );
	echo '</label>';
}

function codeweber_events_field_show_seats_left(): void {
	$val = codeweber_events_settings_get( 'show_seats_left', '1' );
	echo '<label><input type="checkbox" name="codeweber_events_settings[show_seats_left]" value="1" ' . checked( $val, '1', false ) . '> ';
	esc_html_e( 'Show how many seats are still available', 'codeweber' );
	echo '</label>';
}

function codeweber_events_field_show_seats_progress(): void {
	$val = codeweber_events_settings_get( 'show_seats_progress', '1' );
	echo '<label><input type="checkbox" name="codeweber_events_settings[show_seats_progress]" value="1" ' . checked( $val, '1', false ) . '> ';
	esc_html_e( 'Show a Bootstrap progress bar for seat capacity', 'codeweber' );
	echo '</label>';
}

function codeweber_events_field_btn_register_text(): void {
	$val = codeweber_events_settings_get( 'btn_register_text', __( 'Register', 'codeweber' ) );
	echo '<input type="text" name="codeweber_events_settings[btn_register_text]" value="' . esc_attr( $val ) . '" class="regular-text">';
}

function codeweber_events_field_no_seats_text(): void {
	$val = codeweber_events_settings_get( 'no_seats_text', __( 'No seats available', 'codeweber' ) );
	echo '<input type="text" name="codeweber_events_settings[no_seats_text]" value="' . esc_attr( $val ) . '" class="regular-text">';
}

function codeweber_events_field_success_message(): void {
	$val = codeweber_events_settings_get( 'success_message', __( 'You have successfully registered for the event. We will contact you shortly.', 'codeweber' ) );
	echo '<input type="text" name="codeweber_events_settings[success_message]" value="' . esc_attr( $val ) . '" class="large-text">';
}

function codeweber_events_field_notify_email(): void {
	$val = codeweber_events_settings_get( 'notify_email', '' );
	echo '<input type="email" name="codeweber_events_settings[notify_email]" value="' . esc_attr( $val ) . '" class="regular-text" placeholder="' . esc_attr( get_option( 'admin_email' ) ) . '">';
	echo '<p class="description">' . esc_html__( 'Leave empty to use admin email.', 'codeweber' ) . '</p>';
}

// ---------------------------------------------------------------------------
// Sanitize
// ---------------------------------------------------------------------------

function codeweber_events_settings_sanitize( array $input ): array {
	$clean = [];
	$clean['show_seats_taken']    = isset( $input['show_seats_taken'] ) ? '1' : '0';
	$clean['show_seats_left']     = isset( $input['show_seats_left'] ) ? '1' : '0';
	$clean['show_seats_progress'] = isset( $input['show_seats_progress'] ) ? '1' : '0';
	$clean['btn_register_text']   = sanitize_text_field( $input['btn_register_text'] ?? '' );
	$clean['no_seats_text']       = sanitize_text_field( $input['no_seats_text'] ?? '' );
	$clean['success_message']     = sanitize_text_field( $input['success_message'] ?? '' );
	$clean['notify_email']        = sanitize_email( $input['notify_email'] ?? '' );
	return $clean;
}

// ---------------------------------------------------------------------------
// Page render
// ---------------------------------------------------------------------------

function codeweber_events_settings_render_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Events Settings', 'codeweber' ); ?></h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'codeweber_events_settings_group' );
			do_settings_sections( 'codeweber-events-settings' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

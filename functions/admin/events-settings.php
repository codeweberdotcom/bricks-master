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
// Test email
// ---------------------------------------------------------------------------

add_action( 'admin_post_codeweber_events_test_email', 'codeweber_events_send_test_email' );

function codeweber_events_send_test_email(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Insufficient permissions.', 'codeweber' ) );
	}
	check_admin_referer( 'codeweber_events_test_email' );

	$settings   = get_option( 'codeweber_events_settings', [] );
	$to         = ! empty( $settings['notify_email'] ) ? $settings['notify_email'] : get_option( 'admin_email' );
	$from_name  = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$from_email = get_option( 'admin_email' );
	$headers    = [
		'Content-Type: text/html; charset=UTF-8',
		'From: ' . $from_name . ' <' . $from_email . '>',
	];

	$mail_error = null;
	$fail_cb    = function( \WP_Error $err ) use ( &$mail_error ) {
		$mail_error = $err;
	};
	add_action( 'wp_mail_failed', $fail_cb );

	$sent = wp_mail(
		$to,
		/* translators: %s: site name */
		sprintf( __( 'Test email from %s Events', 'codeweber' ), get_bloginfo( 'name' ) ),
		'<p>' . esc_html__( 'This is a test email from the Events module. If you received this, email delivery is working correctly.', 'codeweber' ) . '</p>',
		$headers
	);

	remove_action( 'wp_mail_failed', $fail_cb );

	$status  = ( $sent && ! $mail_error ) ? 'sent' : 'failed';
	$err_msg = $mail_error ? $mail_error->get_error_message() : '';

	wp_safe_redirect( add_query_arg(
		array_filter( [
			'page'       => 'codeweber-events-settings',
			'test_email' => $status,
			'email_to'   => rawurlencode( $to ),
			'email_err'  => $err_msg ? rawurlencode( $err_msg ) : '',
		] ),
		admin_url( 'edit.php?post_type=events' )
	) );
	exit;
}

// ---------------------------------------------------------------------------
// Page render
// ---------------------------------------------------------------------------

function codeweber_events_settings_render_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Test email result notice
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	if ( ! empty( $_GET['test_email'] ) ) {
		$test_status = sanitize_key( $_GET['test_email'] );
		$email_to    = sanitize_email( rawurldecode( $_GET['email_to'] ?? '' ) );
		$email_err   = sanitize_text_field( rawurldecode( $_GET['email_err'] ?? '' ) );
		if ( $test_status === 'sent' ) {
			echo '<div class="notice notice-success is-dismissible"><p>'
				. sprintf(
					/* translators: %s: email address */
					esc_html__( 'Test email successfully sent to %s.', 'codeweber' ),
					'<strong>' . esc_html( $email_to ) . '</strong>'
				) . '</p></div>';
		} else {
			echo '<div class="notice notice-error is-dismissible"><p>'
				. esc_html__( 'Failed to send test email.', 'codeweber' );
			if ( $email_err ) {
				echo ' <strong>' . esc_html( $email_err ) . '</strong>';
			}
			echo '</p></div>';
		}
	}
	// phpcs:enable
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

		<hr>
		<h2><?php esc_html_e( 'Email Delivery Test', 'codeweber' ); ?></h2>
		<?php
		$settings   = get_option( 'codeweber_events_settings', [] );
		$test_to    = ! empty( $settings['notify_email'] ) ? $settings['notify_email'] : get_option( 'admin_email' );
		?>
		<p><?php printf(
			/* translators: %s: email address */
			esc_html__( 'Send a test email to %s to verify your mail server is configured correctly.', 'codeweber' ),
			'<strong>' . esc_html( $test_to ) . '</strong>'
		); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="codeweber_events_test_email">
			<?php wp_nonce_field( 'codeweber_events_test_email' ); ?>
			<?php submit_button( __( 'Send Test Email', 'codeweber' ), 'secondary', 'submit', false ); ?>
		</form>
	</div>
	<?php
}

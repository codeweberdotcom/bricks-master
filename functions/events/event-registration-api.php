<?php
/**
 * Events REST API
 *
 * Endpoints:
 *  POST codeweber/v1/events/register   — submit registration
 *  GET  codeweber/v1/events/calendar   — FullCalendar feed
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Codeweber_Event_Registration_API {

	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public function register_routes(): void {
		register_rest_route( 'wp/v2', '/modal/event-reg-(?P<id>\d+)', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'get_form_html' ],
			'permission_callback' => '__return_true',
			'args'                => [
				'id' => [
					'required'          => true,
					'sanitize_callback' => 'absint',
					'validate_callback' => function( $val ) {
						return $val > 0 && get_post_type( $val ) === 'events';
					},
				],
			],
		] );

		register_rest_route( 'codeweber/v1', '/events/register', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'handle_register' ],
			'permission_callback' => '__return_true',
			'args'                => [
				'event_id' => [
					'required'          => true,
					'sanitize_callback' => 'absint',
					'validate_callback' => function( $val ) {
						return $val > 0 && get_post_type( $val ) === 'events';
					},
				],
				'name' => [
					'required'          => true,
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => function( $val ) {
						return ! empty( trim( $val ) );
					},
				],
				'email' => [
					'required'          => true,
					'sanitize_callback' => 'sanitize_email',
					'validate_callback' => 'is_email',
				],
				'phone' => [
					'required'          => false,
					'sanitize_callback' => 'sanitize_text_field',
				],
				'message' => [
					'required'          => false,
					'sanitize_callback' => 'sanitize_textarea_field',
				],
				'nonce' => [
					'required'          => true,
					'sanitize_callback' => 'sanitize_text_field',
				],
				'seats' => [
					'required'          => false,
					'sanitize_callback' => 'absint',
					'validate_callback' => function( $val ) {
						return $val === null || (int) $val >= 1;
					},
				],
				'consents' => [
					'required'          => false,
					'sanitize_callback' => function( $val ) {
						if ( ! is_array( $val ) ) { return []; }
						$clean = [];
						foreach ( $val as $k => $v ) {
							$clean[ (string) absint( $k ) ] = '1';
						}
						return $clean;
					},
				],
				'honeypot' => [
					'required'          => false,
					'sanitize_callback' => 'sanitize_text_field',
				],
			],
		] );

		register_rest_route( 'codeweber/v1', '/events/calendar', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'handle_calendar_feed' ],
			'permission_callback' => '__return_true',
			'args'                => [
				'start' => [
					'required'          => false,
					'sanitize_callback' => 'sanitize_text_field',
				],
				'end' => [
					'required'          => false,
					'sanitize_callback' => 'sanitize_text_field',
				],
				'category' => [
					'required'          => false,
					'sanitize_callback' => 'absint',
				],
			],
		] );
	}

	// -------------------------------------------------------------------------
	// Register handler
	// -------------------------------------------------------------------------

	public function handle_register( \WP_REST_Request $request ): \WP_REST_Response {
		// Honeypot
		if ( ! empty( $request->get_param( 'honeypot' ) ) ) {
			return new \WP_REST_Response( [ 'success' => false, 'message' => __( 'Spam detected.', 'codeweber' ) ], 400 );
		}

		// Nonce — form nonce first, fall back to REST API nonce (like testimonials)
		$form_nonce = $request->get_param( 'nonce' );
		$rest_nonce = $request->get_header( 'X-WP-Nonce' );
		$nonce_valid = ( ! empty( $form_nonce ) && wp_verify_nonce( $form_nonce, 'codeweber_event_register' ) )
			|| ( ! empty( $rest_nonce ) && wp_verify_nonce( $rest_nonce, 'wp_rest' ) );
		if ( ! $nonce_valid ) {
			return new \WP_REST_Response( [ 'success' => false, 'message' => __( 'Security check failed. Please reload the page.', 'codeweber' ) ], 403 );
		}

		$event_id = $request->get_param( 'event_id' );

		// Validate required fields based on event settings
		$email_required = get_post_meta( $event_id, '_event_reg_email_required', true );
		$phone_required = get_post_meta( $event_id, '_event_reg_phone_required', true );
		if ( $email_required === '' ) { $email_required = '1'; }

		if ( $email_required === '1' ) {
			$email_val = $request->get_param( 'email' );
			if ( empty( $email_val ) || ! is_email( $email_val ) ) {
				return new \WP_REST_Response( [ 'success' => false, 'message' => __( 'Please enter a valid email.', 'codeweber' ) ], 422 );
			}
		}
		if ( $phone_required === '1' ) {
			$phone_val = $request->get_param( 'phone' );
			if ( empty( trim( $phone_val ?? '' ) ) ) {
				return new \WP_REST_Response( [ 'success' => false, 'message' => __( 'Please enter your phone number.', 'codeweber' ) ], 422 );
			}
		}

		// Validate required consents
		$event_consents = get_post_meta( $event_id, '_event_reg_consents', true );
		if ( is_array( $event_consents ) && ! empty( $event_consents ) ) {
			$submitted_consents = $request->get_param( 'consents' );
			if ( ! is_array( $submitted_consents ) ) { $submitted_consents = []; }
			foreach ( $event_consents as $ec ) {
				if ( ! empty( $ec['required'] ) && empty( $submitted_consents[ (string) $ec['document_id'] ] ) ) {
					return new \WP_REST_Response( [ 'success' => false, 'message' => __( 'Please accept all required consents.', 'codeweber' ) ], 422 );
				}
			}
		}

		// Check registration status
		$status = codeweber_events_get_registration_status( $event_id );
		if ( ! $status['show_form'] && $status['status'] !== 'modal' ) {
			return new \WP_REST_Response( [ 'success' => false, 'message' => $status['label'] ], 422 );
		}

		// Duplicate check (same email for same event)
		$existing = get_posts( [
			'post_type'      => 'event_registrations',
			'post_status'    => [ 'reg_pending', 'reg_confirmed', 'reg_awaiting' ],
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => [
				'relation' => 'AND',
				[ 'key' => '_reg_event_id', 'value' => $event_id, 'type' => 'NUMERIC' ],
				[ 'key' => '_reg_email', 'value' => $request->get_param( 'email' ) ],
			],
		] );

		if ( ! empty( $existing ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => __( 'You are already registered for this event.', 'codeweber' ),
			], 422 );
		}

		// Create registration post
		$event_title = get_the_title( $event_id );
		$post_id = wp_insert_post( [
			'post_type'   => 'event_registrations',
			'post_title'  => sanitize_text_field( $request->get_param( 'name' ) ) . ' — ' . $event_title,
			'post_status' => 'reg_pending',
			'post_author' => get_current_user_id() ?: 1,
		] );

		if ( is_wp_error( $post_id ) ) {
			return new \WP_REST_Response( [ 'success' => false, 'message' => __( 'Failed to save registration. Please try again.', 'codeweber' ) ], 500 );
		}

		$show_seats = get_post_meta( $event_id, '_event_reg_show_seats', true );
		$seats      = ( $show_seats === '1' && $request->get_param( 'seats' ) )
			? max( 1, (int) $request->get_param( 'seats' ) )
			: 1;

		update_post_meta( $post_id, '_reg_event_id', $event_id );
		update_post_meta( $post_id, '_reg_name',     sanitize_text_field( $request->get_param( 'name' ) ) );
		update_post_meta( $post_id, '_reg_email',    sanitize_email( $request->get_param( 'email' ) ) );
		update_post_meta( $post_id, '_reg_phone',    sanitize_text_field( $request->get_param( 'phone' ) ?? '' ) );
		update_post_meta( $post_id, '_reg_message',  sanitize_textarea_field( $request->get_param( 'message' ) ?? '' ) );
		update_post_meta( $post_id, '_reg_seats',    $seats );
		update_post_meta( $post_id, '_reg_status',   'reg_pending' );

		// ----------------------------------------------------------------
		// User creation + consents + newsletter — same pattern as codeweber forms
		// ----------------------------------------------------------------

		$_reg_email  = sanitize_email( $request->get_param( 'email' ) ?? '' );
		$_reg_phone  = sanitize_text_field( $request->get_param( 'phone' ) ?? '' );
		$_reg_name   = sanitize_text_field( $request->get_param( 'name' ) );
		$_raw_consents = $request->get_param( 'consents' );
		if ( ! is_array( $_raw_consents ) ) { $_raw_consents = []; }

		// 1. Save consents to registration post meta (audit trail for admin)
		if ( ! empty( $_raw_consents ) ) {
			$_consents_meta = [];
			foreach ( $_raw_consents as $_doc_id => $_val ) {
				$_doc = get_post( (int) $_doc_id );
				if ( ! $_doc ) { continue; }
				$_consents_meta[] = [
					'document_id'      => (int) $_doc_id,
					'document_title'   => $_doc->post_title,
					'document_version' => $_doc->post_modified,
					'accepted'         => true,
					'timestamp'        => current_time( 'mysql' ),
				];
			}
			if ( ! empty( $_consents_meta ) ) {
				update_post_meta( $post_id, '_reg_consents', $_consents_meta );
			}
		}

		// 2. Get or create WP user
		if ( function_exists( 'codeweber_forms_get_or_create_user' ) ) {
			$_wp_user = codeweber_forms_get_or_create_user(
				is_email( $_reg_email ) ? $_reg_email : null,
				[ 'first_name' => $_reg_name, 'phone' => $_reg_phone ]
			);

			if ( ! is_wp_error( $_wp_user ) ) {
				update_post_meta( $post_id, '_reg_user_id', $_wp_user->ID );

				// 3. Save consents to user meta (for PII export)
				if ( ! empty( $_raw_consents ) && function_exists( 'codeweber_forms_save_user_consents' ) ) {
					$_consents_for_save = [];
					foreach ( $_raw_consents as $_doc_id => $_val ) {
						$_doc = get_post( (int) $_doc_id );
						if ( ! $_doc ) { continue; }
						$_consents_for_save[ (int) $_doc_id ] = [
							'value'                      => '1',
							'document_id'                => (int) $_doc_id,
							'document_version'           => $_doc->post_modified,
							'document_version_timestamp' => strtotime( $_doc->post_modified ),
						];
					}
					if ( ! empty( $_consents_for_save ) ) {
						codeweber_forms_save_user_consents( $_wp_user->ID, $_consents_for_save, [
							'form_id'       => 'event-registration',
							'form_name'     => get_the_title( $event_id ) . ' — ' . __( 'Event Registration', 'codeweber' ),
							'submission_id' => $post_id,
							'ip_address'    => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' ),
							'user_agent'    => sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ?? '' ),
						] );
					}

					// 4. Newsletter subscription (if mailing consent document is among submitted)
					if ( function_exists( 'codeweber_forms_newsletter_integration' ) ) {
						codeweber_forms_newsletter_integration( $post_id, 'event-registration', [
							'email'         => $_reg_email,
							'phone'         => $_reg_phone,
							'name'          => $_reg_name,
							'form_consents' => $_consents_for_save,
							'_form_name'    => get_the_title( $event_id ) . ' — ' . __( 'Event Registration', 'codeweber' ),
						] );
					}
				}
			}
		}

		// Email notification
		$this->send_admin_notification( $post_id, $event_id );

		$settings = get_option( 'codeweber_events_settings', [] );
		$message  = ! empty( $settings['success_message'] )
			? $settings['success_message']
			: __( 'You have successfully registered for the event. We will contact you shortly.', 'codeweber' );

		return new \WP_REST_Response( [
			'success'         => true,
			'message'         => $message,
			'registration_id' => $post_id,
		], 201 );
	}

	// -------------------------------------------------------------------------
	// FullCalendar feed
	// -------------------------------------------------------------------------

	public function handle_calendar_feed( \WP_REST_Request $request ): \WP_REST_Response {
		$args = [
			'post_type'      => 'events',
			'post_status'    => 'publish',
			'posts_per_page' => 200,
			'meta_key'       => '_event_date_start',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
		];

		// Date range filter
		$start = $request->get_param( 'start' );
		$end   = $request->get_param( 'end' );
		if ( $start && $end ) {
			$args['meta_query'] = [
				'relation' => 'AND',
				[
					'key'     => '_event_date_start',
					'value'   => sanitize_text_field( $end ),
					'compare' => '<=',
					'type'    => 'DATETIME',
				],
				[
					'key'     => '_event_date_end',
					'value'   => sanitize_text_field( $start ),
					'compare' => '>=',
					'type'    => 'DATETIME',
				],
			];
		}

		// Category filter
		$category = $request->get_param( 'category' );
		if ( $category ) {
			$args['tax_query'] = [
				[
					'taxonomy' => 'event_category',
					'field'    => 'term_id',
					'terms'    => $category,
				],
			];
		}

		$query  = new WP_Query( $args );
		$events = [];

		foreach ( $query->posts as $post ) {
			$date_start = get_post_meta( $post->ID, '_event_date_start', true );
			$date_end   = get_post_meta( $post->ID, '_event_date_end', true );
			$location   = get_post_meta( $post->ID, '_event_location', true );

			// Category color
			$terms = get_the_terms( $post->ID, 'event_category' );
			$color = '#0d6efd'; // Bootstrap primary
			if ( $terms && ! is_wp_error( $terms ) ) {
				$term_color = get_term_meta( $terms[0]->term_id, 'event_category_color', true );
				if ( $term_color ) {
					$color = $term_color;
				}
			}

			// Registration status for extendedProps
			$reg_status = codeweber_events_get_registration_status( $post->ID );

			$events[] = [
				'id'    => $post->ID,
				'title' => $post->post_title,
				'start' => $date_start ?: '',
				'end'   => $date_end ?: '',
				'url'   => get_permalink( $post->ID ),
				'color' => $color,
				'extendedProps' => [
					'location'          => $location,
					'registration_status' => $reg_status['status'],
					'registration_label' => $reg_status['label'],
				],
			];
		}

		return new \WP_REST_Response( $events, 200 );
	}

	// -------------------------------------------------------------------------
	// Email notification to admin
	// -------------------------------------------------------------------------

	private function send_admin_notification( int $reg_id, int $event_id ): void {
		$settings = get_option( 'codeweber_events_settings', [] );
		$to       = ! empty( $settings['notify_email'] ) ? $settings['notify_email'] : get_option( 'admin_email' );

		if ( ! $to ) {
			return;
		}

		$name        = get_post_meta( $reg_id, '_reg_name', true );
		$email       = get_post_meta( $reg_id, '_reg_email', true );
		$phone       = get_post_meta( $reg_id, '_reg_phone', true );
		$seats       = (int) get_post_meta( $reg_id, '_reg_seats', true );
		$msg         = get_post_meta( $reg_id, '_reg_message', true );
		$event_title = get_the_title( $event_id );
		$event_url   = get_edit_post_link( $event_id, 'raw' );
		$reg_url     = get_edit_post_link( $reg_id, 'raw' );

		/* translators: %s: event title */
		$subject = sprintf( __( 'New registration: %s', 'codeweber' ), $event_title );

		$from_name  = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
		$from_email = get_option( 'admin_email' );
		$headers    = [
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . $from_name . ' <' . $from_email . '>',
		];

		$rows = '';
		$rows .= '<tr><th align="left" style="padding:4px 12px 4px 0">' . esc_html__( 'Name', 'codeweber' ) . ':</th><td>' . esc_html( $name ) . '</td></tr>';
		$rows .= '<tr><th align="left" style="padding:4px 12px 4px 0">' . esc_html__( 'Email', 'codeweber' ) . ':</th><td><a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a></td></tr>';
		if ( $phone ) {
			$rows .= '<tr><th align="left" style="padding:4px 12px 4px 0">' . esc_html__( 'Phone', 'codeweber' ) . ':</th><td>' . esc_html( $phone ) . '</td></tr>';
		}
		if ( $seats > 1 ) {
			$rows .= '<tr><th align="left" style="padding:4px 12px 4px 0">' . esc_html__( 'Seats', 'codeweber' ) . ':</th><td>' . esc_html( $seats ) . '</td></tr>';
		}
		if ( $msg ) {
			$rows .= '<tr><th align="left" style="padding:4px 12px 4px 0;vertical-align:top">' . esc_html__( 'Comment', 'codeweber' ) . ':</th><td>' . nl2br( esc_html( $msg ) ) . '</td></tr>';
		}

		$body  = '<p>' . sprintf(
			/* translators: %s: event title */
			esc_html__( 'New registration for event: %s', 'codeweber' ),
			'<strong>' . esc_html( $event_title ) . '</strong>'
		) . '</p>';
		$body .= '<table style="border-collapse:collapse">' . $rows . '</table>';
		$body .= '<p style="margin-top:16px">';
		$body .= '<a href="' . esc_url( $reg_url ) . '">' . esc_html__( 'View registration in admin', 'codeweber' ) . '</a>';
		$body .= ' &nbsp;|&nbsp; ';
		$body .= '<a href="' . esc_url( $event_url ) . '">' . esc_html__( 'View event', 'codeweber' ) . '</a>';
		$body .= '</p>';

		// Log failures (both via return value and wp_mail_failed hook).
		$mail_error = null;
		$fail_cb    = function( \WP_Error $err ) use ( &$mail_error ) {
			$mail_error = $err;
		};
		add_action( 'wp_mail_failed', $fail_cb );

		$sent = wp_mail( $to, $subject, $body, $headers );

		remove_action( 'wp_mail_failed', $fail_cb );

		if ( ! $sent || $mail_error ) {
			$error_msg = $mail_error ? $mail_error->get_error_message() : 'wp_mail() returned false';
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( '[Events] Notification email failed. To: ' . $to . ' | Error: ' . $error_msg );
		}
	}

	/**
	 * GET wp/v2/modal/event-reg-{id}
	 * Returns registration form HTML for the theme modal system.
	 */
	public function get_form_html( \WP_REST_Request $request ): \WP_REST_Response {
		$event_id = absint( $request->get_param( 'id' ) );

		$reg_status = codeweber_events_get_registration_status( $event_id );
		if ( ! $reg_status['show_form'] && $reg_status['status'] !== 'modal' ) {
			return new \WP_REST_Response( [ 'html' => '<p>' . esc_html__( 'Registration is not available.', 'codeweber' ) . '</p>' ], 200 );
		}

		$_evt_global_form_title = codeweber_events_settings_get( 'reg_form_title', __( 'Register', 'codeweber' ) );
		$_evt_global_btn_label  = codeweber_events_settings_get( 'btn_register_text', __( 'Register', 'codeweber' ) );
		$reg_form_title   = get_post_meta( $event_id, '_event_reg_form_title', true ) ?: $_evt_global_form_title;
		$reg_button_label = get_post_meta( $event_id, '_event_reg_button_label', true ) ?: $_evt_global_btn_label;

		$default_forms = new \CodeweberFormsDefaultForms();
		$form_html     = $default_forms->get_default_event_registration_form_html( $event_id, $reg_button_label );
		$html          = '<div class="event-registration-wrap">'
			. '<h3 class="mb-4">' . esc_html( $reg_form_title ) . '</h3>'
			. $form_html
			. '</div>';

		return new \WP_REST_Response( [ 'content' => [ 'rendered' => $html ] ], 200 );
	}
}

new Codeweber_Event_Registration_API();

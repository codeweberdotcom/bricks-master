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

		// Nonce
		if ( ! wp_verify_nonce( $request->get_param( 'nonce' ), 'codeweber_event_register' ) ) {
			return new \WP_REST_Response( [ 'success' => false, 'message' => __( 'Security check failed. Please reload the page.', 'codeweber' ) ], 403 );
		}

		$event_id = $request->get_param( 'event_id' );

		// Check registration status
		$status = codeweber_events_get_registration_status( $event_id );
		if ( ! $status['show_form'] ) {
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

		update_post_meta( $post_id, '_reg_event_id', $event_id );
		update_post_meta( $post_id, '_reg_name',     sanitize_text_field( $request->get_param( 'name' ) ) );
		update_post_meta( $post_id, '_reg_email',    sanitize_email( $request->get_param( 'email' ) ) );
		update_post_meta( $post_id, '_reg_phone',    sanitize_text_field( $request->get_param( 'phone' ) ?? '' ) );
		update_post_meta( $post_id, '_reg_message',  sanitize_textarea_field( $request->get_param( 'message' ) ?? '' ) );
		update_post_meta( $post_id, '_reg_status',   'reg_pending' );

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

		$name      = get_post_meta( $reg_id, '_reg_name', true );
		$email     = get_post_meta( $reg_id, '_reg_email', true );
		$phone     = get_post_meta( $reg_id, '_reg_phone', true );
		$event_url = get_edit_post_link( $event_id, 'raw' );
		$reg_url   = get_edit_post_link( $reg_id, 'raw' );

		/* translators: %s: event title */
		$subject = sprintf( __( 'New registration: %s', 'codeweber' ), get_the_title( $event_id ) );

		$body  = sprintf( __( 'New registration for event: %s', 'codeweber' ), get_the_title( $event_id ) ) . "\n";
		$body .= $event_url . "\n\n";
		$body .= __( 'Name:', 'codeweber' ) . ' ' . $name . "\n";
		$body .= __( 'Email:', 'codeweber' ) . ' ' . $email . "\n";
		if ( $phone ) {
			$body .= __( 'Phone:', 'codeweber' ) . ' ' . $phone . "\n";
		}
		$body .= "\n" . __( 'View registration:', 'codeweber' ) . ' ' . $reg_url;

		wp_mail( $to, $subject, $body );
	}
}

new Codeweber_Event_Registration_API();

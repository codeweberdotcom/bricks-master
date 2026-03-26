<?php
/**
 * FAQ Form REST API
 *
 * Provides modal endpoint for the FAQ question form.
 * Route: GET wp/v2/modal/faq-form
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Codeweber_FAQ_Form_API {

	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public function register_routes(): void {
		register_rest_route( 'wp/v2', '/modal/faq-form', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'get_form_html' ],
			'permission_callback' => '__return_true',
		] );
	}

	/**
	 * GET wp/v2/modal/faq-form
	 * Returns FAQ question form HTML for the theme modal system.
	 */
	public function get_form_html( \WP_REST_Request $request ): \WP_REST_Response {
		if ( ! class_exists( 'CodeweberFormsDefaultForms' ) ) {
			return new \WP_REST_Response( [
				'content' => [ 'rendered' => '<p>' . esc_html__( 'Form is not available.', 'codeweber' ) . '</p>' ],
			], 200 );
		}

		$forms     = new \CodeweberFormsDefaultForms();
		$form_html = $forms->get_default_faq_form_html();

		$html = '<div class="faq-form-wrap">'
			. '<h3 class="mb-4">' . esc_html__( 'Ask a Question', 'codeweber' ) . '</h3>'
			. $form_html
			. '</div>';

		return new \WP_REST_Response( [ 'content' => [ 'rendered' => $html ] ], 200 );
	}
}

new Codeweber_FAQ_Form_API();

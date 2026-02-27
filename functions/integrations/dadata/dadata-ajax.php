<?php
/**
 * DaData: AJAX endpoint for address standardization.
 * Registered for both logged-in and guest (checkout). Secured by nonce.
 *
 * @package Codeweber
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handle AJAX: clean address and return WooCommerce-style fields.
 */
function codeweber_dadata_ajax_clean_address() {
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'codeweber_dadata_clean' ) ) {
		wp_send_json( array(
			'success' => false,
			'error'   => __( 'Ошибка безопасности. Обновите страницу и попробуйте снова.', 'codeweber' ),
		) );
	}

	global $opt_name;
	if ( ! class_exists( 'Redux' ) ) {
		wp_send_json( array( 'success' => false, 'error' => __( 'Сервис проверки адреса временно недоступен.', 'codeweber' ) ) );
	}
	if ( ! Redux::get_option( $opt_name, 'dadata_enabled' ) ) {
		wp_send_json( array( 'success' => false, 'error' => __( 'Сервис проверки адреса отключён.', 'codeweber' ) ) );
	}

	$address = isset( $_POST['address'] ) ? sanitize_text_field( wp_unslash( $_POST['address'] ) ) : '';
	if ( $address === '' ) {
		wp_send_json( array( 'success' => false, 'error' => __( 'Введите адрес для проверки.', 'codeweber' ) ) );
	}

	require_once dirname( __FILE__ ) . '/class-codeweber-dadata.php';
	$dadata = new Codeweber_Dadata();
	$result = $dadata->clean_address( $address );

	if ( ! $result['success'] ) {
		wp_send_json( array(
			'success' => false,
			'error'   => $result['error'],
		) );
	}

	wp_send_json( array(
		'success' => true,
		'data'    => $result['data'],
	) );
}

add_action( 'wp_ajax_dadata_clean_address', 'codeweber_dadata_ajax_clean_address' );
add_action( 'wp_ajax_nopriv_dadata_clean_address', 'codeweber_dadata_ajax_clean_address' );

/**
 * Handle AJAX: suggest addresses (autocomplete).
 */
function codeweber_dadata_ajax_suggest_address() {
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'codeweber_dadata_clean' ) ) {
		wp_send_json( array( 'success' => false, 'suggestions' => array() ) );
	}

	global $opt_name;
	if ( ! class_exists( 'Redux' ) ) {
		wp_send_json( array( 'success' => false, 'suggestions' => array() ) );
	}
	if ( ! Redux::get_option( $opt_name, 'dadata_enabled' ) ) {
		wp_send_json( array( 'success' => false, 'suggestions' => array() ) );
	}

	$query = isset( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : '';
	$count = isset( $_POST['count'] ) ? max( 1, min( 20, (int) $_POST['count'] ) ) : 10;

	$dadata = new Codeweber_Dadata();
	$result = $dadata->suggest_address( $query, $count );

	if ( ! $result['success'] ) {
		wp_send_json( array(
			'success'     => false,
			'suggestions' => array(),
			'error'       => isset( $result['error'] ) ? $result['error'] : '',
		) );
	}

	wp_send_json( array(
		'success'     => true,
		'suggestions' => isset( $result['suggestions'] ) ? $result['suggestions'] : array(),
	) );
}

add_action( 'wp_ajax_dadata_suggest_address', 'codeweber_dadata_ajax_suggest_address' );
add_action( 'wp_ajax_nopriv_dadata_suggest_address', 'codeweber_dadata_ajax_suggest_address' );

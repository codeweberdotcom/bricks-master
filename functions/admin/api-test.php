<?php
/**
 * API Test — AJAX handlers + enqueue для кнопок тестирования API на странице Redux.
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ─── AJAX handlers ───────────────────────────────────────────────────────────

add_action( 'wp_ajax_codeweber_api_test_dadata',  'codeweber_api_test_dadata' );
add_action( 'wp_ajax_codeweber_api_test_yandex',  'codeweber_api_test_yandex' );
add_action( 'wp_ajax_codeweber_api_test_smsru',   'codeweber_api_test_smsru' );

function codeweber_api_test_dadata() {
	check_ajax_referer( 'codeweber_api_test', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Нет доступа' ) );
	}

	$token = sanitize_text_field( wp_unslash( $_POST['token'] ?? '' ) );

	if ( empty( $token ) ) {
		wp_send_json_error( array( 'message' => 'Введите API Token и сохраните настройки, либо введите ключ в поле' ) );
	}

	$response = wp_remote_post(
		'https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address',
		array(
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Token ' . $token,
				'Accept'        => 'application/json',
			),
			'body'    => wp_json_encode( array( 'query' => 'Москва', 'count' => 1 ) ),
			'timeout' => 10,
		)
	);

	if ( is_wp_error( $response ) ) {
		wp_send_json_error( array( 'message' => 'Ошибка соединения: ' . $response->get_error_message() ) );
	}

	$code = wp_remote_retrieve_response_code( $response );
	$body = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( 200 === $code && isset( $body['suggestions'] ) ) {
		$count = count( $body['suggestions'] );
		wp_send_json_success( array( 'message' => 'Токен действителен — получено ' . $count . ' подсказок' ) );
	} elseif ( 401 === $code ) {
		wp_send_json_error( array( 'message' => 'Неверный токен (401 Unauthorized)' ) );
	} else {
		$msg = isset( $body['message'] ) ? $body['message'] : wp_remote_retrieve_body( $response );
		wp_send_json_error( array( 'message' => 'Ошибка ' . $code . ': ' . $msg ) );
	}
}

function codeweber_api_test_yandex() {
	check_ajax_referer( 'codeweber_api_test', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Нет доступа' ) );
	}

	$key = sanitize_text_field( wp_unslash( $_POST['key'] ?? '' ) );

	if ( empty( $key ) ) {
		wp_send_json_error( array( 'message' => 'Введите API ключ' ) );
	}

	$url = add_query_arg(
		array(
			'apikey'  => $key,
			'geocode' => 'Москва',
			'format'  => 'json',
			'results' => 1,
		),
		'https://geocode-maps.yandex.ru/1.x/'
	);

	$response = wp_remote_get( $url, array( 'timeout' => 10 ) );

	if ( is_wp_error( $response ) ) {
		wp_send_json_error( array( 'message' => 'Ошибка соединения: ' . $response->get_error_message() ) );
	}

	$code = wp_remote_retrieve_response_code( $response );
	$body = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( 200 === $code && isset( $body['response'] ) ) {
		wp_send_json_success( array( 'message' => 'Ключ действителен' ) );
	} elseif ( 403 === $code ) {
		wp_send_json_error( array( 'message' => 'Ключ недействителен (403 Forbidden)' ) );
	} else {
		$msg = isset( $body['message'] ) ? $body['message'] : wp_remote_retrieve_body( $response );
		wp_send_json_error( array( 'message' => 'Ошибка ' . $code . ': ' . $msg ) );
	}
}

function codeweber_api_test_smsru() {
	check_ajax_referer( 'codeweber_api_test', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Нет доступа' ) );
	}

	$api_id = sanitize_text_field( wp_unslash( $_POST['api_id'] ?? '' ) );

	if ( empty( $api_id ) ) {
		wp_send_json_error( array( 'message' => 'Введите API ключ' ) );
	}

	$url = add_query_arg(
		array(
			'api_id' => $api_id,
			'json'   => 1,
		),
		'https://sms.ru/auth/check'
	);

	$response = wp_remote_get( $url, array( 'timeout' => 10 ) );

	if ( is_wp_error( $response ) ) {
		wp_send_json_error( array( 'message' => 'Ошибка соединения: ' . $response->get_error_message() ) );
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( isset( $body['status'] ) && 'OK' === $body['status'] ) {
		wp_send_json_success( array( 'message' => 'Авторизация прошла успешно' ) );
	} else {
		$status_text = isset( $body['status_text'] ) ? $body['status_text'] : 'Неизвестная ошибка';
		wp_send_json_error( array( 'message' => 'Ошибка: ' . $status_text ) );
	}
}

// ─── Enqueue JS ──────────────────────────────────────────────────────────────

add_action( 'admin_enqueue_scripts', 'codeweber_api_test_enqueue' );

function codeweber_api_test_enqueue( $hook ) {
	// Только на странице настроек Redux
	if ( ! isset( $_GET['page'] ) || 'redux_demo' !== $_GET['page'] ) {
		return;
	}

	// Inline styles для результатов теста
	wp_add_inline_style(
		'redux-admin-css',
		'.cw-api-test-result { margin-left: 8px; vertical-align: middle; font-size: 13px; }
		.cw-api-test-success { color: #46b450; font-weight: 600; }
		.cw-api-test-error   { color: #dc3232; font-weight: 600; }'
	);

	wp_enqueue_script(
		'codeweber-api-test',
		get_template_directory_uri() . '/functions/admin/api-test.js',
		array( 'jquery' ),
		'1.0.0',
		true
	);

	wp_localize_script(
		'codeweber-api-test',
		'codeweberApiTest',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'codeweber_api_test' ),
			'labels'  => array(
				'testing' => 'Проверка...',
				'test'    => 'Тест',
			),
		)
	);
}

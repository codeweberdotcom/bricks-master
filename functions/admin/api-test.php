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

add_action( 'wp_ajax_codeweber_api_test_dadata',    'codeweber_api_test_dadata' );
add_action( 'wp_ajax_codeweber_api_test_yandex',    'codeweber_api_test_yandex' );
add_action( 'wp_ajax_codeweber_api_test_smsru',     'codeweber_api_test_smsru' );
add_action( 'wp_ajax_codeweber_api_test_telegram',  'codeweber_api_test_telegram' );
add_action( 'wp_ajax_codeweber_api_test_unsplash',  'codeweber_api_test_unsplash' );
add_action( 'wp_ajax_codeweber_api_test_pexels',    'codeweber_api_test_pexels' );
add_action( 'wp_ajax_codeweber_api_test_pixabay',   'codeweber_api_test_pixabay' );
add_action( 'wp_ajax_codeweber_api_test_freepik',   'codeweber_api_test_freepik' );
add_action( 'wp_ajax_codeweber_api_test_proxy',     'codeweber_api_test_proxy' );

function codeweber_api_test_proxy() {
	check_ajax_referer( 'codeweber_api_test', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Нет доступа' ) );
	}

	if ( ! function_exists( 'cw_proxy_config' ) || ! cw_proxy_config() ) {
		wp_send_json_error( array( 'message' => 'Включите прокси и сохраните host/port' ) );
	}

	// Force the request through the proxy regardless of module scope.
	$response = wp_remote_get(
		'https://api.ipify.org/?format=json',
		array(
			'timeout'      => 12,
			'cw_use_proxy' => true,
		)
	);

	if ( is_wp_error( $response ) ) {
		wp_send_json_error( array( 'message' => 'Ошибка через прокси: ' . $response->get_error_message() ) );
	}

	$code = (int) wp_remote_retrieve_response_code( $response );
	$body = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( 200 === $code && ! empty( $body['ip'] ) ) {
		wp_send_json_success( array( 'message' => 'Прокси работает. Внешний IP: ' . $body['ip'] ) );
	}

	wp_send_json_error( array( 'message' => 'Прокси ответил, но IP не получен (код ' . $code . ')' ) );
}

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

function codeweber_api_test_telegram() {
	check_ajax_referer( 'codeweber_api_test', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Нет доступа' ) );
	}

	$token   = sanitize_text_field( wp_unslash( $_POST['token'] ?? '' ) );
	$chat_id = sanitize_text_field( wp_unslash( $_POST['chat_id'] ?? '' ) );

	if ( ! $token ) {
		wp_send_json_error( array( 'message' => esc_html__( 'Enter Bot Token and save settings, or enter the key in the field', 'codeweber' ) ) );
	}

	if ( ! $chat_id ) {
		wp_send_json_error( array( 'message' => esc_html__( 'Enter Chat / Channel ID', 'codeweber' ) ) );
	}

	$url = 'https://api.telegram.org/bot' . $token . '/sendMessage';

	$tg_args = array(
		'headers' => array( 'Content-Type' => 'application/json' ),
		'body'    => wp_json_encode(
			array(
				'chat_id'                  => $chat_id,
				'text'                     => '✅ ' . esc_html__( 'Test CodeWeber — bot connected!', 'codeweber' ),
				'parse_mode'               => 'HTML',
				'disable_web_page_preview' => true,
			)
		),
		'timeout' => 10,
	);

	if ( function_exists( 'cw_proxy_request_args' ) ) {
		$tg_args = cw_proxy_request_args( 'telegram', $tg_args );
	}

	$response = wp_remote_post( $url, $tg_args );

	if ( is_wp_error( $response ) ) {
		wp_send_json_error( array( 'message' => 'Ошибка соединения: ' . $response->get_error_message() ) );
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( ! empty( $body['ok'] ) ) {
		wp_send_json_success( array( 'message' => esc_html__( 'Message sent successfully', 'codeweber' ) ) );
	} else {
		$description = isset( $body['description'] ) ? $body['description'] : esc_html__( 'Unknown error', 'codeweber' );
		wp_send_json_error( array( 'message' => 'Telegram: ' . $description ) );
	}
}

function codeweber_api_test_unsplash() {
	check_ajax_referer( 'codeweber_api_test', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Нет доступа' ) );
	}

	$key = sanitize_text_field( wp_unslash( $_POST['key'] ?? '' ) );

	if ( empty( $key ) ) {
		wp_send_json_error( array( 'message' => 'Введите Access Key' ) );
	}

	$url = add_query_arg(
		array( 'query' => 'nature', 'per_page' => 1 ),
		'https://api.unsplash.com/search/photos'
	);

	$response = wp_remote_get(
		$url,
		cw_stock_photos_request_args(
			array(
				'headers' => array( 'Authorization' => 'Client-ID ' . $key ),
				'timeout' => 10,
			)
		)
	);

	if ( is_wp_error( $response ) ) {
		wp_send_json_error( array( 'message' => 'Ошибка соединения: ' . $response->get_error_message() ) );
	}

	$code = wp_remote_retrieve_response_code( $response );
	$body = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( 200 === $code && isset( $body['total'] ) ) {
		wp_send_json_success( array( 'message' => 'Ключ действителен — найдено ' . (int) $body['total'] . ' фото' ) );
	} elseif ( 401 === $code ) {
		wp_send_json_error( array( 'message' => 'Неверный Access Key (401 Unauthorized)' ) );
	} else {
		$msg = isset( $body['errors'][0] ) ? $body['errors'][0] : wp_remote_retrieve_body( $response );
		wp_send_json_error( array( 'message' => 'Ошибка ' . $code . ': ' . $msg ) );
	}
}

function codeweber_api_test_pexels() {
	check_ajax_referer( 'codeweber_api_test', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Нет доступа' ) );
	}

	$key = sanitize_text_field( wp_unslash( $_POST['key'] ?? '' ) );

	if ( empty( $key ) ) {
		wp_send_json_error( array( 'message' => 'Введите API ключ' ) );
	}

	$url = add_query_arg(
		array( 'query' => 'nature', 'per_page' => 1 ),
		'https://api.pexels.com/v1/search'
	);

	$response = wp_remote_get(
		$url,
		cw_stock_photos_request_args(
			array(
				'headers' => array( 'Authorization' => $key ),
				'timeout' => 10,
			)
		)
	);

	if ( is_wp_error( $response ) ) {
		wp_send_json_error( array( 'message' => 'Ошибка соединения: ' . $response->get_error_message() ) );
	}

	$code = wp_remote_retrieve_response_code( $response );
	$body = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( 200 === $code && isset( $body['total_results'] ) ) {
		wp_send_json_success( array( 'message' => 'Ключ действителен — найдено ' . (int) $body['total_results'] . ' фото' ) );
	} elseif ( 401 === $code ) {
		wp_send_json_error( array( 'message' => 'Неверный ключ (401 Unauthorized)' ) );
	} else {
		$msg = isset( $body['error'] ) ? $body['error'] : wp_remote_retrieve_body( $response );
		wp_send_json_error( array( 'message' => 'Ошибка ' . $code . ': ' . $msg ) );
	}
}

function codeweber_api_test_pixabay() {
	check_ajax_referer( 'codeweber_api_test', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Нет доступа' ) );
	}

	$key = sanitize_text_field( wp_unslash( $_POST['key'] ?? '' ) );

	if ( empty( $key ) ) {
		wp_send_json_error( array( 'message' => 'Введите API ключ' ) );
	}

	$url = add_query_arg(
		array( 'key' => $key, 'q' => 'nature', 'per_page' => 3 ),
		'https://pixabay.com/api/'
	);

	$response = wp_remote_get( $url, cw_stock_photos_request_args( array( 'timeout' => 10 ) ) );

	if ( is_wp_error( $response ) ) {
		wp_send_json_error( array( 'message' => 'Ошибка соединения: ' . $response->get_error_message() ) );
	}

	$code = wp_remote_retrieve_response_code( $response );
	$body = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( 200 === $code && isset( $body['totalHits'] ) ) {
		wp_send_json_success( array( 'message' => 'Ключ действителен — найдено ' . (int) $body['totalHits'] . ' фото' ) );
	} else {
		// Pixabay returns plain text like "[ERROR 400] ..." on bad key.
		$msg = wp_remote_retrieve_body( $response );
		wp_send_json_error( array( 'message' => 'Ошибка ' . $code . ': ' . $msg ) );
	}
}

function codeweber_api_test_freepik() {
	check_ajax_referer( 'codeweber_api_test', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Нет доступа' ) );
	}

	$key = sanitize_text_field( wp_unslash( $_POST['key'] ?? '' ) );

	if ( empty( $key ) ) {
		wp_send_json_error( array( 'message' => 'Введите API ключ' ) );
	}

	$url = 'https://api.freepik.com/v1/resources?' . http_build_query( array(
		'term'  => 'nature',
		'limit' => 1,
		'filters' => array( 'content_type' => array( 'photo' => 1 ) ),
	) );

	$response = wp_remote_get(
		$url,
		cw_stock_photos_request_args( array(
			'headers' => array(
				'x-freepik-api-key' => $key,
				'Accept-Language'   => 'en-US',
				'Accept'            => 'application/json',
			),
			'timeout' => 10,
		) )
	);

	if ( is_wp_error( $response ) ) {
		wp_send_json_error( array( 'message' => 'Ошибка соединения: ' . $response->get_error_message() ) );
	}

	$code = wp_remote_retrieve_response_code( $response );
	$body = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( 200 === $code && isset( $body['data'] ) ) {
		$total = (int) ( $body['meta']['total'] ?? 0 );
		wp_send_json_success( array( 'message' => 'Ключ действителен — найдено ' . $total . ' фото' ) );
	} elseif ( 401 === $code || 403 === $code ) {
		wp_send_json_error( array( 'message' => 'Неверный API ключ (' . $code . ')' ) );
	} else {
		$msg = isset( $body['message'] ) ? $body['message'] : wp_remote_retrieve_body( $response );
		wp_send_json_error( array( 'message' => 'Ошибка ' . $code . ': ' . $msg ) );
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
		'1.1.0',
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

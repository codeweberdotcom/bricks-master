<?php
/**
 * Desktop Mode — Recent Form Submissions widget.
 *
 * Регистрирует виджет для плагина WordPress/desktop-mode (v0.8.9+),
 * который выводит последние отправки CodeWeber Forms в правой
 * колонке desktop-shell.
 *
 * Модуль — no-op, если плагин desktop-mode не активен
 * (функция desktop_mode_register_widget() отсутствует).
 *
 * Контракт desktop-mode v0.8.9:
 *  - PHP: desktop_mode_register_widget( $id, $args ) — args в snake_case.
 *  - JS:  mount-колбэк регистрируется как
 *         window.desktopModeWidgets[ $id ] = (container, ctx) => teardown.
 *  - Плагин сам делает wp_enqueue_script( handle ) на admin_enqueue_scripts @20,
 *    теме достаточно зарегистрировать скрипт (wp_register_script) до 20.
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Плагин desktop-mode не установлен/не активен — выходим, ничего не регистрируя.
if ( ! function_exists( 'desktop_mode_register_widget' ) ) {
	return;
}

if ( ! defined( 'CODEWEBER_DM_WIDGET_ID' ) ) {
	define( 'CODEWEBER_DM_WIDGET_ID', 'codeweber/recent-forms' );
}
if ( ! defined( 'CODEWEBER_DM_WIDGET_HANDLE' ) ) {
	define( 'CODEWEBER_DM_WIDGET_HANDLE', 'codeweber-dm-widgets' );
}

/**
 * Серверная регистрация виджета. Выполняется только в админке —
 * payload пикера собирается при рендере admin-страницы.
 */
function codeweber_dm_register_recent_forms_widget() {
	if ( ! is_admin() || ! function_exists( 'desktop_mode_register_widget' ) ) {
		return;
	}

	desktop_mode_register_widget(
		CODEWEBER_DM_WIDGET_ID,
		array(
			'label'          => __( 'Recent Forms', 'codeweber' ),
			'description'    => __( 'Latest form submissions', 'codeweber' ),
			'icon'           => 'dashicons-feedback',
			'script'         => CODEWEBER_DM_WIDGET_HANDLE,
			'movable'        => true,
			'resizable'      => true,
			'min_width'      => 260,
			'min_height'     => 180,
			'default_width'  => 340,
			'default_height' => 360,
			'capabilities'   => array( 'manage_options' ),
		)
	);
}
add_action( 'init', 'codeweber_dm_register_recent_forms_widget' );

/**
 * Регистрация JS-скрипта виджета и проброс REST-данных.
 * Приоритет 10 — раньше плагинного enqueue (@20), чтобы хэндл
 * уже существовал к моменту wp_enqueue_script() в desktop-mode.
 */
function codeweber_dm_register_widget_assets() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	wp_register_script(
		CODEWEBER_DM_WIDGET_HANDLE,
		get_template_directory_uri() . '/functions/integrations/desktop-mode-widgets/recent-forms-widget.js',
		array(),
		'1.0.0',
		true
	);

	wp_localize_script(
		CODEWEBER_DM_WIDGET_HANDLE,
		'codeweberDmRecentForms',
		array(
			'root'     => esc_url_raw( rest_url( 'codeweber/v1/recent-form-submissions' ) ),
			'nonce'    => wp_create_nonce( 'wp_rest' ),
			'adminUrl' => admin_url( 'edit.php?post_type=codeweber_form&page=codeweber' ),
			'i18n'     => array(
				'empty'   => __( 'No submissions yet', 'codeweber' ),
				'error'   => __( 'Failed to load submissions', 'codeweber' ),
				'loading' => __( 'Loading…', 'codeweber' ),
				'viewAll' => __( 'All submissions', 'codeweber' ),
			),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'codeweber_dm_register_widget_assets', 10 );

/**
 * REST-эндпоинт со списком последних отправок форм.
 * Доступ — только manage_options (как у страницы submissions).
 */
function codeweber_dm_register_recent_forms_rest() {
	register_rest_route(
		'codeweber/v1',
		'/recent-form-submissions',
		array(
			'methods'             => 'GET',
			'callback'            => 'codeweber_dm_recent_forms_rest_callback',
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			},
			'args'                => array(
				'limit' => array(
					'default'           => 7,
					'sanitize_callback' => 'absint',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'codeweber_dm_register_recent_forms_rest' );

/**
 * Колбэк REST: возвращает последние отправки (без корзины).
 *
 * @param WP_REST_Request $request Запрос.
 * @return WP_REST_Response
 */
function codeweber_dm_recent_forms_rest_callback( $request ) {
	if ( ! class_exists( 'CodeweberFormsDatabase' ) ) {
		return new WP_REST_Response( array(), 200 );
	}

	$limit = (int) $request->get_param( 'limit' );
	$limit = max( 1, min( 20, $limit ) );

	$db   = new CodeweberFormsDatabase();
	$rows = $db->get_submissions(
		array(
			'limit'          => $limit,
			'orderby'        => 'created_at',
			'order'          => 'DESC',
			'exclude_status' => 'trash',
		)
	);

	$items = array();
	foreach ( (array) $rows as $row ) {
		$form_name = ( isset( $row->form_name ) && '' !== $row->form_name )
			? $row->form_name
			: __( 'Form', 'codeweber' );

		$items[] = array(
			'id'       => (int) $row->id,
			'formName' => $form_name,
			'formType' => ! empty( $row->form_type ) ? $row->form_type : 'form',
			'status'   => $row->status,
			'date'     => mysql2date( 'd.m.Y H:i', $row->created_at ),
			'preview'  => codeweber_dm_submission_preview( $row->submission_data ),
			'viewUrl'  => admin_url(
				'edit.php?post_type=codeweber_form&page=codeweber&action=view&id=' . (int) $row->id
			),
		);
	}

	return new WP_REST_Response( $items, 200 );
}

/**
 * Короткое превью отправки: первые непустые значения полей.
 *
 * @param string $json JSON из колонки submission_data.
 * @return string
 */
function codeweber_dm_submission_preview( $json ) {
	$data = json_decode( (string) $json, true );
	if ( ! is_array( $data ) ) {
		return '';
	}

	$parts = array();
	foreach ( $data as $value ) {
		if ( is_array( $value ) ) {
			$value = implode( ', ', array_filter( $value, 'is_scalar' ) );
		}
		$value = trim( wp_strip_all_tags( (string) $value ) );
		if ( '' === $value ) {
			continue;
		}
		$parts[] = $value;
		if ( count( $parts ) >= 2 ) {
			break;
		}
	}

	$preview = implode( ' · ', $parts );

	return ( function_exists( 'mb_substr' ) )
		? mb_substr( $preview, 0, 80 )
		: substr( $preview, 0, 80 );
}

<?php
/**
 * AJAX обработчики для Demo данных
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX обработчик для создания demo клиентов
 */
function cw_demo_ajax_create_clients() {
    // Проверка прав
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'message' => 'Недостаточно прав для выполнения операции'
        ));
    }
    
    // Проверка nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cw_demo_create_clients')) {
        wp_send_json_error(array(
            'message' => 'Ошибка безопасности. Обновите страницу и попробуйте снова.'
        ));
    }
    
    // Выполняем создание
    $result = cw_demo_create_clients();
    
    if ($result['success']) {
        wp_send_json_success(array(
            'message' => $result['message'],
            'created' => $result['created'],
            'total' => $result['total'],
            'errors' => $result['errors']
        ));
    } else {
        wp_send_json_error(array(
            'message' => $result['message']
        ));
    }
}
add_action('wp_ajax_cw_demo_create_clients', 'cw_demo_ajax_create_clients');

/**
 * AJAX обработчик для удаления demo клиентов
 */
function cw_demo_ajax_delete_clients() {
    // Проверка прав
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'message' => 'Недостаточно прав для выполнения операции'
        ));
    }
    
    // Проверка nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cw_demo_delete_clients')) {
        wp_send_json_error(array(
            'message' => 'Ошибка безопасности. Обновите страницу и попробуйте снова.'
        ));
    }
    
    // Выполняем удаление
    $result = cw_demo_delete_clients();
    
    if ($result['success']) {
        wp_send_json_success(array(
            'message' => $result['message'],
            'deleted' => $result['deleted'],
            'errors' => $result['errors']
        ));
    } else {
        wp_send_json_error(array(
            'message' => $result['message']
        ));
    }
}
add_action('wp_ajax_cw_demo_delete_clients', 'cw_demo_ajax_delete_clients');

/**
 * AJAX обработчик для создания demo FAQ
 */
function cw_demo_ajax_create_faq() {
    // Проверка прав
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'message' => 'Недостаточно прав для выполнения операции'
        ));
    }
    
    // Проверка nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cw_demo_create_faq')) {
        wp_send_json_error(array(
            'message' => 'Ошибка безопасности. Обновите страницу и попробуйте снова.'
        ));
    }
    
    // Выполняем создание
    $result = cw_demo_create_faq();
    
    if ($result['success']) {
        wp_send_json_success(array(
            'message' => $result['message'],
            'created' => $result['created'],
            'total' => $result['total'],
            'errors' => $result['errors']
        ));
    } else {
        wp_send_json_error(array(
            'message' => $result['message']
        ));
    }
}
add_action('wp_ajax_cw_demo_create_faq', 'cw_demo_ajax_create_faq');

/**
 * AJAX обработчик для удаления demo FAQ
 */
function cw_demo_ajax_delete_faq() {
    // Проверка прав
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'message' => 'Недостаточно прав для выполнения операции'
        ));
    }
    
    // Проверка nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cw_demo_delete_faq')) {
        wp_send_json_error(array(
            'message' => 'Ошибка безопасности. Обновите страницу и попробуйте снова.'
        ));
    }
    
    // Выполняем удаление
    $result = cw_demo_delete_faq();
    
    if ($result['success']) {
        wp_send_json_success(array(
            'message' => $result['message'],
            'deleted' => $result['deleted'],
            'errors' => $result['errors']
        ));
    } else {
        wp_send_json_error(array(
            'message' => $result['message']
        ));
    }
}
add_action('wp_ajax_cw_demo_delete_faq', 'cw_demo_ajax_delete_faq');

/**
 * AJAX обработчик для создания demo testimonials
 */
function cw_demo_ajax_create_testimonials() {
    // Проверка прав
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'message' => __('Недостаточно прав для выполнения операции', 'codeweber')
        ));
    }
    
    // Проверка nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cw_demo_create_testimonials')) {
        wp_send_json_error(array(
            'message' => __('Ошибка безопасности. Обновите страницу и попробуйте снова.', 'codeweber')
        ));
    }
    
    // Выполняем создание
    $result = cw_demo_create_testimonials();
    
    if ($result['success']) {
        wp_send_json_success(array(
            'message' => $result['message'],
            'created' => $result['created'],
            'total' => $result['total'],
            'errors' => $result['errors']
        ));
    } else {
        wp_send_json_error(array(
            'message' => $result['message']
        ));
    }
}
add_action('wp_ajax_cw_demo_create_testimonials', 'cw_demo_ajax_create_testimonials');

/**
 * AJAX обработчик для удаления demo testimonials
 */
function cw_demo_ajax_delete_testimonials() {
    // Проверка прав
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'message' => __('Недостаточно прав для выполнения операции', 'codeweber')
        ));
    }
    
    // Проверка nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cw_demo_delete_testimonials')) {
        wp_send_json_error(array(
            'message' => __('Ошибка безопасности. Обновите страницу и попробуйте снова.', 'codeweber')
        ));
    }
    
    // Выполняем удаление
    $result = cw_demo_delete_testimonials();
    
    if ($result['success']) {
        wp_send_json_success(array(
            'message' => $result['message'],
            'deleted' => $result['deleted'],
            'errors' => $result['errors']
        ));
    } else {
        wp_send_json_error(array(
            'message' => $result['message']
        ));
    }
}
add_action('wp_ajax_cw_demo_delete_testimonials', 'cw_demo_ajax_delete_testimonials');

/**
 * AJAX обработчик для создания demo staff
 */
function cw_demo_ajax_create_staff() {
    // Проверка прав
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'message' => __('Недостаточно прав для выполнения операции', 'codeweber')
        ));
    }
    
    // Проверка nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cw_demo_create_staff')) {
        wp_send_json_error(array(
            'message' => __('Ошибка безопасности. Обновите страницу и попробуйте снова.', 'codeweber')
        ));
    }
    
    // Выполняем создание
    $result = cw_demo_create_staff();
    
    if ($result['success']) {
        wp_send_json_success(array(
            'message' => $result['message'],
            'created' => $result['created'],
            'total' => $result['total'],
            'errors' => $result['errors']
        ));
    } else {
        wp_send_json_error(array(
            'message' => $result['message']
        ));
    }
}
add_action('wp_ajax_cw_demo_create_staff', 'cw_demo_ajax_create_staff');

/**
 * AJAX обработчик для удаления demo staff
 */
function cw_demo_ajax_delete_staff() {
    // Проверка прав
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'message' => __('Недостаточно прав для выполнения операции', 'codeweber')
        ));
    }
    
    // Проверка nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cw_demo_delete_staff')) {
        wp_send_json_error(array(
            'message' => __('Ошибка безопасности. Обновите страницу и попробуйте снова.', 'codeweber')
        ));
    }
    
    // Выполняем удаление
    $result = cw_demo_delete_staff();
    
    if ($result['success']) {
        wp_send_json_success(array(
            'message' => $result['message'],
            'deleted' => $result['deleted'],
            'errors' => $result['errors']
        ));
    } else {
        wp_send_json_error(array(
            'message' => $result['message']
        ));
    }
}
add_action('wp_ajax_cw_demo_delete_staff', 'cw_demo_ajax_delete_staff');

/**
 * AJAX обработчик для создания demo vacancies
 */
function cw_demo_ajax_create_vacancies() {
    // Проверка прав
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'message' => __('Недостаточно прав для выполнения операции', 'codeweber')
        ));
    }
    
    // Проверка nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cw_demo_create_vacancies')) {
        wp_send_json_error(array(
            'message' => __('Ошибка безопасности. Обновите страницу и попробуйте снова.', 'codeweber')
        ));
    }
    
    // Выполняем создание
    $result = cw_demo_create_vacancies();
    
    if ($result['success']) {
        wp_send_json_success(array(
            'message' => $result['message'],
            'created' => $result['created'],
            'total' => $result['total'],
            'errors' => $result['errors']
        ));
    } else {
        wp_send_json_error(array(
            'message' => $result['message']
        ));
    }
}
add_action('wp_ajax_cw_demo_create_vacancies', 'cw_demo_ajax_create_vacancies');

/**
 * AJAX обработчик для удаления demo vacancies
 */
function cw_demo_ajax_delete_vacancies() {
    // Проверка прав
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'message' => __('Недостаточно прав для выполнения операции', 'codeweber')
        ));
    }
    
    // Проверка nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cw_demo_delete_vacancies')) {
        wp_send_json_error(array(
            'message' => __('Ошибка безопасности. Обновите страницу и попробуйте снова.', 'codeweber')
        ));
    }
    
    // Выполняем удаление
    $result = cw_demo_delete_vacancies();
    
    if ($result['success']) {
        wp_send_json_success(array(
            'message' => $result['message'],
            'deleted' => $result['deleted'],
            'errors' => $result['errors']
        ));
    } else {
        wp_send_json_error(array(
            'message' => $result['message']
        ));
    }
}
add_action('wp_ajax_cw_demo_delete_vacancies', 'cw_demo_ajax_delete_vacancies');

/**
 * AJAX обработчик для создания demo форм
 */
function cw_demo_ajax_create_forms() {
    // Проверка прав
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'message' => __('Недостаточно прав для выполнения операции', 'codeweber')
        ));
    }
    
    // Проверка nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cw_demo_create_forms')) {
        wp_send_json_error(array(
            'message' => __('Ошибка безопасности. Обновите страницу и попробуйте снова.', 'codeweber')
        ));
    }
    
    // Выполняем создание
    $result = cw_demo_create_forms();
    
    if ($result['success']) {
        wp_send_json_success(array(
            'message' => $result['message'],
            'created' => $result['created'],
            'total' => $result['total'],
            'errors' => $result['errors']
        ));
    } else {
        wp_send_json_error(array(
            'message' => $result['message']
        ));
    }
}
add_action('wp_ajax_cw_demo_create_forms', 'cw_demo_ajax_create_forms');

/**
 * AJAX обработчик для удаления demo форм
 */
function cw_demo_ajax_delete_forms() {
    // Проверка прав
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'message' => __('Недостаточно прав для выполнения операции', 'codeweber')
        ));
    }
    
    // Проверка nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cw_demo_delete_forms')) {
        wp_send_json_error(array(
            'message' => __('Ошибка безопасности. Обновите страницу и попробуйте снова.', 'codeweber')
        ));
    }
    
    // Выполняем удаление
    $result = cw_demo_delete_forms();
    
    if ($result['success']) {
        wp_send_json_success(array(
            'message' => $result['message'],
            'deleted' => $result['deleted'],
            'errors' => $result['errors']
        ));
    } else {
        wp_send_json_error(array(
            'message' => $result['message']
        ));
    }
}
add_action('wp_ajax_cw_demo_delete_forms', 'cw_demo_ajax_delete_forms');

/**
 * AJAX обработчик для создания demo форм CF7
 */
function cw_demo_ajax_create_cf7_forms() {
    // Проверка прав
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'message' => 'Недостаточно прав для выполнения операции'
        ));
    }
    
    // Проверка nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cw_demo_create_cf7_forms')) {
        wp_send_json_error(array(
            'message' => 'Ошибка безопасности. Обновите страницу и попробуйте снова.'
        ));
    }
    
    // Выполняем создание
    $result = cw_demo_create_cf7_forms();
    
    if ($result['success']) {
        wp_send_json_success(array(
            'message' => $result['message'],
            'created' => $result['created'],
            'total' => $result['total'],
            'forms' => $result['forms'],
            'errors' => $result['errors']
        ));
    } else {
        wp_send_json_error(array(
            'message' => $result['message']
        ));
    }
}
add_action('wp_ajax_cw_demo_create_cf7_forms', 'cw_demo_ajax_create_cf7_forms');

/**
 * AJAX обработчик для удаления demo форм CF7
 */
function cw_demo_ajax_delete_cf7_forms() {
    // Проверка прав
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'message' => 'Недостаточно прав для выполнения операции'
        ));
    }
    
    // Проверка nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cw_demo_delete_cf7_forms')) {
        wp_send_json_error(array(
            'message' => 'Ошибка безопасности. Обновите страницу и попробуйте снова.'
        ));
    }
    
    // Выполняем удаление
    $result = cw_demo_delete_cf7_forms();
    
    if ($result['success']) {
        wp_send_json_success(array(
            'message' => $result['message'],
            'deleted' => $result['deleted'],
            'errors' => $result['errors']
        ));
    } else {
        wp_send_json_error(array(
            'message' => $result['message']
        ));
    }
}
add_action('wp_ajax_cw_demo_delete_cf7_forms', 'cw_demo_ajax_delete_cf7_forms');

/**
 * AJAX обработчик для создания demo offices
 */
function cw_demo_ajax_create_offices() {
    // Проверка прав
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'message' => __('Недостаточно прав для выполнения операции', 'codeweber')
        ));
    }
    
    // Проверка nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cw_demo_create_offices')) {
        wp_send_json_error(array(
            'message' => __('Ошибка безопасности. Обновите страницу и попробуйте снова.', 'codeweber')
        ));
    }
    
    // Выполняем создание
    $result = cw_demo_create_offices();
    
    if ($result['success']) {
        wp_send_json_success(array(
            'message' => $result['message'],
            'created' => $result['created'],
            'total' => $result['total'],
            'towns_created' => $result['towns_created'] ?? 0,
            'errors' => $result['errors']
        ));
    } else {
        wp_send_json_error(array(
            'message' => $result['message']
        ));
    }
}
add_action('wp_ajax_cw_demo_create_offices', 'cw_demo_ajax_create_offices');

/**
 * AJAX обработчик для удаления demo offices
 */
function cw_demo_ajax_delete_offices() {
    // Проверка прав
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'message' => __('Недостаточно прав для выполнения операции', 'codeweber')
        ));
    }
    
    // Проверка nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cw_demo_delete_offices')) {
        wp_send_json_error(array(
            'message' => __('Ошибка безопасности. Обновите страницу и попробуйте снова.', 'codeweber')
        ));
    }
    
    // Выполняем удаление
    $result = cw_demo_delete_offices();
    
    if ($result['success']) {
        wp_send_json_success(array(
            'message' => $result['message'],
            'deleted' => $result['deleted'],
            'errors' => $result['errors']
        ));
    } else {
        wp_send_json_error(array(
            'message' => $result['message']
        ));
    }
}
add_action('wp_ajax_cw_demo_delete_offices', 'cw_demo_ajax_delete_offices');

/**
 * AJAX обработчик для создания demo футеров
 */
function cw_demo_ajax_create_footers() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Недостаточно прав для выполнения операции', 'codeweber' ) ) );
	}
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'cw_demo_create_footers' ) ) {
		wp_send_json_error( array( 'message' => __( 'Ошибка безопасности. Обновите страницу и попробуйте снова.', 'codeweber' ) ) );
	}
	$result = cw_demo_create_footers();
	if ( $result['success'] ) {
		wp_send_json_success( array(
			'message' => $result['message'],
			'created' => $result['created'],
			'total'   => $result['total'],
			'errors'  => $result['errors'],
		) );
	}
	wp_send_json_error( array( 'message' => $result['message'] ) );
}
add_action( 'wp_ajax_cw_demo_create_footers', 'cw_demo_ajax_create_footers' );

/**
 * AJAX обработчик для удаления demo футеров
 */
function cw_demo_ajax_delete_footers() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Недостаточно прав для выполнения операции', 'codeweber' ) ) );
	}
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'cw_demo_delete_footers' ) ) {
		wp_send_json_error( array( 'message' => __( 'Ошибка безопасности. Обновите страницу и попробуйте снова.', 'codeweber' ) ) );
	}
	$result = cw_demo_delete_footers();
	if ( $result['success'] ) {
		wp_send_json_success( array(
			'message' => $result['message'],
			'deleted' => $result['deleted'],
			'errors'  => $result['errors'],
		) );
	}
	wp_send_json_error( array( 'message' => $result['message'] ) );
}
add_action( 'wp_ajax_cw_demo_delete_footers', 'cw_demo_ajax_delete_footers' );

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


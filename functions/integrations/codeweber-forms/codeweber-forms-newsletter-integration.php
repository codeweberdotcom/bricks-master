<?php
/**
 * CodeWeber Forms Newsletter Subscription Integration
 * 
 * Интеграция форм codeweber-forms с системой newsletter subscription
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Интеграция: при отправке newsletter формы сохраняем подписку
 */
add_action('codeweber_form_saved', 'codeweber_forms_newsletter_integration', 10, 3);

function codeweber_forms_newsletter_integration($submission_id, $form_id, $form_data) {
    // Проверяем, является ли форма newsletter формой
    if (!codeweber_forms_is_newsletter_form($form_id)) {
        return;
    }
    
    // Проверяем наличие класса NewsletterSubscription
    if (!class_exists('NewsletterSubscription')) {
        error_log('NewsletterSubscription class not found');
        return;
    }
    
    // Получаем email из данных формы
    $email = '';
    if (is_array($form_data)) {
        // Ищем поле email в разных вариантах названий
        $email = $form_data['email'] ?? 
                 $form_data['email-address'] ?? 
                 $form_data['EMAIL'] ?? 
                 '';
    }
    
    if (empty($email) || !is_email($email)) {
        error_log('Newsletter integration: Invalid or missing email in form data');
        return;
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'newsletter_subscriptions';
    
    // Проверяем, не существует ли уже подписка
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$table_name} WHERE email = %s",
        $email
    ));
    
    if ($exists) {
        error_log('Newsletter integration: Email already subscribed: ' . $email);
        return;
    }
    
    // Получаем дополнительные данные из формы
    $first_name = $form_data['first_name'] ?? $form_data['text-name'] ?? $form_data['name'] ?? '';
    $last_name = $form_data['last_name'] ?? $form_data['text-surname'] ?? $form_data['surname'] ?? '';
    $phone = $form_data['phone'] ?? $form_data['tel'] ?? '';
    
    // Генерируем токен отписки
    $unsubscribe_token = wp_generate_password(32, false);
    
    // Получаем IP и User Agent
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Вставляем подписку в таблицу
    $insert_data = [
        'email' => sanitize_email($email),
        'first_name' => sanitize_text_field($first_name),
        'last_name' => sanitize_text_field($last_name),
        'phone' => sanitize_text_field($phone),
        'ip_address' => sanitize_text_field($ip_address),
        'user_agent' => sanitize_textarea_field($user_agent),
        'form_id' => 'codeweber_form_' . $form_id,
        'status' => 'confirmed',
        'created_at' => current_time('mysql'),
        'confirmed_at' => current_time('mysql'),
        'updated_at' => current_time('mysql'),
        'unsubscribe_token' => $unsubscribe_token
    ];
    
    $result = $wpdb->insert($table_name, $insert_data);
    
    if ($result === false) {
        error_log('Newsletter integration: Database insert failed: ' . $wpdb->last_error);
        return;
    }
    
    if ($result) {
        error_log('Newsletter integration: Subscription saved for: ' . $email);
        
        // Отправляем email подтверждения, если настроено
        $newsletter_options = get_option('newsletter_subscription_settings', []);
        $send_email = isset($newsletter_options['send_confirmation_email']) ? $newsletter_options['send_confirmation_email'] : true;
        
        if ($send_email && class_exists('NewsletterSubscription')) {
            $newsletter = new NewsletterSubscription();
            if (method_exists($newsletter, 'send_confirmation_email')) {
                $newsletter->send_confirmation_email($email, $first_name, $last_name, $unsubscribe_token);
            }
        }
    }
}

/**
 * Проверяет, является ли форма newsletter формой
 */
function codeweber_forms_is_newsletter_form($form_id) {
    if (!$form_id) {
        return false;
    }
    
    // Convert to integer if string
    $form_id = intval($form_id);
    if ($form_id <= 0) {
        return false;
    }
    
    // Known newsletter form IDs
    $known_newsletter_form_ids = [6119]; // Add more IDs here if needed
    
    if (in_array($form_id, $known_newsletter_form_ids)) {
        return true;
    }
    
    // Проверяем по метаполю
    $form_type = get_post_meta($form_id, '_form_type', true);
    if ($form_type === 'newsletter') {
        return true;
    }
    
    // Проверяем по названию
    $form_post = get_post($form_id);
    if ($form_post && $form_post->post_type === 'codeweber_form') {
        $name_lower = strtolower($form_post->post_title);
        if (strpos($name_lower, 'newsletter') !== false || strpos($name_lower, 'subscription') !== false) {
            return true;
        }
    }
    
    return false;
}


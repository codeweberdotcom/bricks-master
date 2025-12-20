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
    // Проверяем, является ли форма newsletter формой ИЛИ есть ли согласия на рассылку
    $is_newsletter_form = codeweber_forms_is_newsletter_form($form_id);
    $has_mailing_consent = false;
    
    // Проверяем, не была ли подписка уже создана (чтобы избежать дубликатов при прямом вызове)
    static $processed_submissions = [];
    $submission_key = $submission_id . '_' . $form_id;
    if (isset($processed_submissions[$submission_key])) {
        error_log('Newsletter integration: Subscription already processed for submission_id: ' . $submission_id . ', form_id: ' . $form_id);
        return;
    }
    $processed_submissions[$submission_key] = true;
    
    // Проверяем наличие согласий на рассылку в данных формы
    if (!empty($form_data['newsletter_consents']) && is_array($form_data['newsletter_consents'])) {
        foreach ($form_data['newsletter_consents'] as $doc_id => $consent) {
            // Проверяем, что согласие дано
            $consent_value = null;
            if (is_array($consent)) {
                $consent_value = isset($consent['value']) ? $consent['value'] : null;
            } else {
                $consent_value = $consent;
            }
            
            if ($consent_value === '1' || $consent_value === 1) {
                // Проверяем, является ли документ согласием на рассылку
                $doc = get_post(intval($doc_id));
                if ($doc) {
                    $doc_title_lower = mb_strtolower($doc->post_title, 'UTF-8');
                    // Проверяем по названию документа
                    if (strpos($doc_title_lower, 'рассылк') !== false || 
                        strpos($doc_title_lower, 'mailing') !== false || 
                        strpos($doc_title_lower, 'newsletter') !== false ||
                        (strpos($doc_title_lower, 'информационн') !== false && strpos($doc_title_lower, 'рекламн') !== false)) {
                        $has_mailing_consent = true;
                        error_log('Newsletter integration: Found mailing consent in document ID: ' . $doc_id);
                        break;
                    }
                }
            }
        }
    }
    
    // Новое правило: подписка создаётся только при наличии согласия на рассылку,
    // вне зависимости от того, является ли форма newsletter-формой.
    if (!$has_mailing_consent) {
        error_log('Newsletter integration: Mailing consent not found, skipping subscription');
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
    
    // Проверяем, существует ли уже подписка
    $subscription = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$table_name} WHERE email = %s",
        $email
    ));
    
    if ($subscription) {
        // Уже подтвержден — не создаём дубликат
        if ($subscription->status === 'confirmed') {
            error_log('Newsletter integration: Email already subscribed: ' . $email);
            return;
        }
        
        // Ранее отписался — реактивируем подписку
        if ($subscription->status === 'unsubscribed') {
            error_log('Newsletter integration: Reactivating unsubscribed email: ' . $email);
            
            $unsubscribe_token = wp_generate_password(32, false);
            $now = current_time('mysql');
            
            // Получаем IP и User Agent для обновления при реактивации
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            // Обновляем историю событий
            $events = [];
            if (!empty($subscription->events_history)) {
                $decoded = json_decode($subscription->events_history, true);
                if (is_array($decoded)) {
                    $events = $decoded;
                }
            }
            
            // ИМЕНА ФОРМЫ ВСЕГДА БЕРЕТСЯ ИЗ TITLE CPT ФОРМЫ
            // Источник всегда один - post_title из CPT по form_id
            $form_name_for_event = '';
            
            error_log('=== NEWSLETTER INTEGRATION (REACTIVATION): Getting form_name from CPT title START ===');
            error_log('form_id type: ' . gettype($form_id));
            error_log('form_id value: ' . var_export($form_id, true));
            error_log('form_id is_numeric: ' . (is_numeric($form_id) ? 'YES' : 'NO'));
            
            // Получаем title из CPT формы
            if (is_numeric($form_id) && (int) $form_id > 0) {
                $int_form_id = (int) $form_id;
                error_log('Getting post from CPT. int_form_id: ' . $int_form_id);
                $form_post = get_post($int_form_id);
                error_log('get_post result: ' . ($form_post ? 'FOUND' : 'NOT FOUND'));
                if ($form_post) {
                    error_log('Post ID: ' . $form_post->ID);
                    error_log('Post type: ' . $form_post->post_type);
                    error_log('Post title: ' . $form_post->post_title);
                    error_log('Post type match: ' . ($form_post->post_type === 'codeweber_form' ? 'YES' : 'NO'));
                    error_log('Post title empty: ' . (empty($form_post->post_title) ? 'YES' : 'NO'));
                }
                if ($form_post && $form_post->post_type === 'codeweber_form' && !empty($form_post->post_title)) {
                    $form_name_for_event = $form_post->post_title;
                    error_log('Got form_name from CPT title: ' . $form_name_for_event);
                } else {
                    error_log('Failed to get form_name from CPT title. Post exists: ' . ($form_post ? 'YES' : 'NO'));
                    if ($form_post) {
                        error_log('Reason: post_type=' . $form_post->post_type . ' (expected codeweber_form), title_empty=' . (empty($form_post->post_title) ? 'YES' : 'NO'));
                    }
                }
            } else {
                error_log('WARNING: form_id is not numeric, cannot get CPT title. form_id: ' . var_export($form_id, true));
            }
            
            error_log('Final form_name_for_event from CPT title (reactivation): ' . var_export($form_name_for_event, true));
            error_log('=== NEWSLETTER INTEGRATION (REACTIVATION): Getting form_name from CPT title END ===');
            
            $normalized_form_id = is_numeric($form_id) ? (string) (int) $form_id : (string) $form_id;
            $event = [
                'type'      => 'confirmed',
                'date'      => $now,
                'source'    => 'codeweber_form_resubscribe',
                'form_id'   => $normalized_form_id,
                'form_name' => $form_name_for_event,
                'page_url'  => wp_get_referer() ?: home_url($_SERVER['REQUEST_URI'] ?? '/'),
                'ip_address' => sanitize_text_field($ip_address), // ИСПРАВЛЕНО: сохраняем IP в событии истории
            ];
            
            // Добавляем согласия в событие, если есть
            if (!empty($form_data['newsletter_consents']) && is_array($form_data['newsletter_consents'])) {
                $consents_for_event = [];
                
                if (!function_exists('codeweber_forms_get_document_url')) {
                    require_once get_template_directory() . '/functions/integrations/codeweber-forms/codeweber-forms-consent-helper.php';
                }
                
                foreach ($form_data['newsletter_consents'] as $doc_id => $consent) {
                    $doc = get_post(intval($doc_id));
                    if (!$doc) {
                        continue;
                    }
                    
                    $doc_title = $doc->post_title;
                    $version = is_array($consent) ? ($consent['document_version'] ?? null) : null;
                    $doc_url = codeweber_forms_get_document_url(intval($doc_id), $version);
                    
                    $consents_for_event[] = [
                        'id'                  => (int) $doc_id,
                        'title'               => $doc_title,
                        'document_revision_id'=> is_array($consent) ? ($consent['document_revision_id'] ?? null) : null,
                        'document_version'    => $version,
                        'url'                 => $doc_url,
                    ];
                }
                
                if (!empty($consents_for_event)) {
                    $event['consents'] = $consents_for_event;
                }
            }
            
            $events[] = $event;
            
            // Получаем user_id авторизованного пользователя при реактивации
            $user_id = 0;
            if (is_user_logged_in()) {
                $user_id = get_current_user_id();
            }
            
            // Реактивируем подписку
            // ИСПРАВЛЕНО: обновляем form_id и ip_address на новые из запроса при реактивации
            $updated = $wpdb->update(
                $table_name,
                [
                    'form_id'          => $normalized_form_id, // ИСПРАВЛЕНО: обновляем на новый form_id
                    'ip_address'       => sanitize_text_field($ip_address), // ИСПРАВЛЕНО: обновляем IP на новый
                    'user_agent'       => sanitize_textarea_field($user_agent), // ИСПРАВЛЕНО: обновляем user_agent на новый
                    'user_id'          => $user_id, // Обновляем user_id авторизованного пользователя
                    'status'            => 'confirmed',
                    'confirmed_at'      => $now,
                    'unsubscribed_at'   => null,
                    'updated_at'        => $now,
                    'unsubscribe_token' => $unsubscribe_token,
                    'events_history'    => wp_json_encode($events, JSON_UNESCAPED_UNICODE),
                ],
                ['id' => $subscription->id],
                ['%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s'],
                ['%d']
            );
            
            if ($updated !== false) {
                error_log('Newsletter integration: Subscription reactivated for: ' . $email);
                return; // Подписка реактивирована, выходим
            } else {
                error_log('Newsletter integration: Failed to reactivate subscription: ' . $wpdb->last_error);
                // Продолжаем создавать новую подписку
            }
        }
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
    
    // ИМЕНА ФОРМЫ ВСЕГДА БЕРЕТСЯ ИЗ TITLE CPT ФОРМЫ
    // Источник всегда один - post_title из CPT по form_id
    $form_name_for_event = '';
    
    error_log('=== NEWSLETTER INTEGRATION: Getting form_name from CPT title START ===');
    error_log('form_id type: ' . gettype($form_id));
    error_log('form_id value: ' . var_export($form_id, true));
    error_log('form_id is_numeric: ' . (is_numeric($form_id) ? 'YES' : 'NO'));
    
    // Получаем title из CPT формы
    if (is_numeric($form_id) && (int) $form_id > 0) {
        $int_form_id = (int) $form_id;
        error_log('Getting post from CPT. int_form_id: ' . $int_form_id);
        $form_post = get_post($int_form_id);
        error_log('get_post result: ' . ($form_post ? 'FOUND' : 'NOT FOUND'));
        if ($form_post) {
            error_log('Post ID: ' . $form_post->ID);
            error_log('Post type: ' . $form_post->post_type);
            error_log('Post title: ' . $form_post->post_title);
            error_log('Post type match: ' . ($form_post->post_type === 'codeweber_form' ? 'YES' : 'NO'));
            error_log('Post title empty: ' . (empty($form_post->post_title) ? 'YES' : 'NO'));
        }
        if ($form_post && $form_post->post_type === 'codeweber_form' && !empty($form_post->post_title)) {
            $form_name_for_event = $form_post->post_title;
            error_log('Got form_name from CPT title: ' . $form_name_for_event);
        } else {
            error_log('Failed to get form_name from CPT title. Post exists: ' . ($form_post ? 'YES' : 'NO'));
            if ($form_post) {
                error_log('Reason: post_type=' . $form_post->post_type . ' (expected codeweber_form), title_empty=' . (empty($form_post->post_title) ? 'YES' : 'NO'));
            }
        }
    } else {
        error_log('WARNING: form_id is not numeric, cannot get CPT title. form_id: ' . var_export($form_id, true));
    }
    
    error_log('Final form_name_for_event from CPT title: ' . var_export($form_name_for_event, true));
    error_log('=== NEWSLETTER INTEGRATION: Getting form_name from CPT title END ===');

    // Формируем историю событий (events_history)
    $now = current_time('mysql');
    $normalized_form_id = is_numeric($form_id) ? (string) (int) $form_id : (string) $form_id;
    $events = [
        [
            'type'     => 'confirmed',
            'date'     => $now,
            'source'   => 'codeweber_form',
            // В events_history храним ID формы и при наличии – человекочитаемое имя
            'form_id'  => $normalized_form_id,
            'form_name'=> $form_name_for_event,
            'page_url' => wp_get_referer() ?: home_url($_SERVER['REQUEST_URI'] ?? '/'),
            'ip_address' => sanitize_text_field($ip_address), // ИСПРАВЛЕНО: сохраняем IP в событии истории
            'consents' => [],
        ],
    ];
    
    error_log('=== NEWSLETTER INTEGRATION: Saving event to DB ===');
    error_log('normalized_form_id: ' . var_export($normalized_form_id, true));
    error_log('form_name_for_event in event: ' . var_export($form_name_for_event, true));
    error_log('Event array: ' . print_r($events[0], true));
    error_log('Events JSON: ' . wp_json_encode($events, JSON_UNESCAPED_UNICODE));

    // Добавляем в событие согласия, которые были даны при подписке (если есть)
    if (!empty($form_data['newsletter_consents']) && is_array($form_data['newsletter_consents'])) {
        $consents_for_event = [];
        foreach ($form_data['newsletter_consents'] as $doc_id => $consent) {
            $doc = get_post($doc_id);
            if (!$doc) {
                continue;
            }

            $doc_title = $doc->post_title;
            $version   = $consent['document_version'] ?? null;

            // Используем общий helper, чтобы ссылка на документ/ревизию
            // была полностью консистентна с другими местами (экспорт, письма и т.д.)
            if (!function_exists('codeweber_forms_get_document_url')) {
                require_once get_template_directory() . '/functions/integrations/codeweber-forms/codeweber-forms-consent-helper.php';
            }

            $doc_url = codeweber_forms_get_document_url($doc_id, $version);

            $consents_for_event[] = [
                'id'                 => (int) $doc_id,
                'title'              => $doc_title,
                'document_revision_id'=> null, // вычисляется helper'ом по версии, если нужна ревизия
                'document_version'   => $version,
                'url'                => $doc_url,
            ];
        }

        if (!empty($consents_for_event)) {
            $events[0]['consents'] = $consents_for_event;
        }
    }

    // Получаем user_id авторизованного пользователя, который отправил форму
    $user_id = 0;
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
    }
    
    // Вставляем подписку в таблицу
    // form_id храним без префиксов, только реальный ID/ключ формы:
    // - для встроенных форм: newsletter, testimonial и т.д.
    // - для CPT-форм: числовой ID (6119 и т.п.)
    $insert_data = [
        'email' => sanitize_email($email),
        'first_name' => sanitize_text_field($first_name),
        'last_name' => sanitize_text_field($last_name),
        'phone' => sanitize_text_field($phone),
        'ip_address' => sanitize_text_field($ip_address),
        'user_agent' => sanitize_textarea_field($user_agent),
        'form_id' => $normalized_form_id,
        'user_id' => $user_id,
        'status' => 'confirmed',
        'created_at' => $now,
        'confirmed_at' => $now,
        'updated_at' => $now,
        'unsubscribe_token' => $unsubscribe_token,
        'events_history' => wp_json_encode($events, JSON_UNESCAPED_UNICODE),
    ];
    
    error_log('=== NEWSLETTER INTEGRATION: Inserting subscription ===');
    error_log('events_history JSON length: ' . strlen($insert_data['events_history']));
    error_log('events_history JSON preview: ' . substr($insert_data['events_history'], 0, 500));
    
    $result = $wpdb->insert($table_name, $insert_data);
    
    if ($result === false) {
        error_log('Newsletter integration: Database insert failed: ' . $wpdb->last_error);
        return;
    }
    
    if ($result) {
        error_log('Newsletter integration: Subscription saved for: ' . $email);
        // Письмо подписки отправляется через автоответ Codeweber Forms (newsletter_reply)
    }
}

/**
 * Проверяет, является ли форма newsletter формой
 * 
 * @param int|string $form_id Form ID
 * @return bool
 */
function codeweber_forms_is_newsletter_form($form_id) {
    if (!$form_id) {
        return false;
    }

    // НОВОЕ: Используем единую функцию для получения типа формы
    if (class_exists('CodeweberFormsCore')) {
        $form_type = CodeweberFormsCore::get_form_type($form_id);
        return ($form_type === 'newsletter');
    }

    // LEGACY: Fallback для обратной совместимости (если класс не загружен)
    // Поддержка строкового ключа встроенной формы
    if (is_string($form_id)) {
        $key = strtolower($form_id);
        if ($key === 'newsletter') {
            return true;
        }
    }

    // Для совместимости также поддерживаем числовые ID CPT
    $form_id = (int) $form_id;
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


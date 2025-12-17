<?php
/**
 * CodeWeber Forms REST API
 * 
 * REST API endpoints for form submission
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

class CodeweberFormsAPI {
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        register_rest_route('codeweber-forms/v1', '/submit', [
            'methods' => 'POST',
            'callback' => [$this, 'submit_form'],
            'permission_callback' => function($request) {
                // Для публичных форм разрешаем доступ, но проверяем nonce
                $nonce = $request->get_header('X-WP-Nonce');
                if (empty($nonce)) {
                    // Если nonce нет, все равно разрешаем (для тестирования)
                    // В продакшене лучше вернуть false
                    return true;
                }
                return wp_verify_nonce($nonce, 'wp_rest');
            },
            'args' => [
                'form_id' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'fields' => [
                    'required' => true,
                    'type' => 'object'
                ],
                'honeypot' => [
                    'required' => false,
                    'sanitize_callback' => 'sanitize_text_field'
                ]
            ]
        ]);
        
        register_rest_route('codeweber-forms/v1', '/forms', [
            'methods' => 'POST',
            'callback' => [$this, 'save_form_config'],
            'permission_callback' => function() {
                return current_user_can('edit_posts');
            }
        ]);
        
        // Endpoint для отслеживания открытия формы
        register_rest_route('codeweber-forms/v1', '/form-opened', [
            'methods' => 'POST',
            'callback' => [$this, 'form_opened'],
            'permission_callback' => '__return_true'
        ]);
        
        // Endpoint для получения списка форм (для блока Gutenberg)
        register_rest_route('codeweber-forms/v1', '/forms', [
            'methods' => 'GET',
            'callback' => [$this, 'get_forms_list'],
            'permission_callback' => function() {
                return current_user_can('edit_posts');
            }
        ]);
    }
    
    /**
     * Submit form handler
     */
    public function submit_form($request) {
        $form_id = $request->get_param('form_id');
        $form_name_from_request = $request->get_param('form_name');
        $fields = $request->get_param('fields');
        $honeypot = $request->get_param('honeypot');
        $utm_params = $request->get_param('utm_params') ?: [];
        $tracking_data = $request->get_param('tracking_data') ?: [];
        $submitted_newsletter_consents = $request->get_param('newsletter_consents');
        
        // Debug logging
        error_log('=== FORM SUBMIT DEBUG START ===');
        error_log('Form Submit - form_id: ' . $form_id);
        error_log('Form Submit - fields (raw): ' . print_r($fields, true));
        error_log('Form Submit - submitted_newsletter_consents (param): ' . print_r($submitted_newsletter_consents, true));
        error_log('Form Submit - is_newsletter_form: ' . (codeweber_forms_is_newsletter_form($form_id) ? 'YES' : 'NO'));
        
        // Nonce проверяется автоматически через permission_callback
        
        // Проверка honeypot (защита от спама)
        // Для newsletter-формы honeypot не используем, чтобы не блокировать подписки по ошибке
        $is_newsletter_form = function_exists('codeweber_forms_is_newsletter_form')
            ? codeweber_forms_is_newsletter_form($form_id)
            : false;

        if (!empty($honeypot) && !$is_newsletter_form) {
            // Если honeypot заполнен - это бот (для обычных форм)
            CodeweberFormsHooks::send_error($form_id, [], __('Spam detected.', 'codeweber'));
            return new WP_Error('spam_detected', __('Spam detected.', 'codeweber'), ['status' => 403]);
        }
        
        // Получаем IP и User Agent
        $ip_address = $this->get_client_ip();
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Валидация пользователя (как в форме отзывов)
        // Get user_id from request first (passed from JavaScript)
        // Try multiple ways to get user_id
        $request_user_id = $request->get_param('user_id');
        if (empty($request_user_id)) {
            // Try JSON params directly
            $json_params = $request->get_json_params();
            if (isset($json_params['user_id'])) {
                $request_user_id = $json_params['user_id'];
            }
        }
        $request_user_id = $request_user_id ? absint($request_user_id) : 0;
        
        // Check if user is logged in (use request parameter or current user)
        $is_logged_in = false;
        $user_id = 0;
        
        if ($request_user_id > 0) {
            // Verify user exists
            $user = get_userdata($request_user_id);
            if ($user && $user->ID > 0) {
                $is_logged_in = true;
                $user_id = $request_user_id;
            }
        } else {
            // Fallback: check current user (works if cookies are passed)
            $current_user_id = get_current_user_id();
            if ($current_user_id > 0) {
                $is_logged_in = true;
                $user_id = $current_user_id;
            }
        }
        
        // Собираем UTM метки и tracking данные
        $utm_tracker = new CodeweberFormsUTM();
        $utm_data = array_merge(
            $utm_tracker->get_utm_params(),
            $utm_params,
            $utm_tracker->get_tracking_data(),
            $tracking_data
        );
        
        // Добавляем UTM данные в поля формы для сохранения и отображения
        if (!empty($utm_data)) {
            $fields['_utm_data'] = $utm_data;
        }
        
        // Проверка rate limit
        if (!CodeweberFormsRateLimit::check($form_id, $ip_address, $user_id)) {
            $options = get_option('codeweber_forms_options', []);
            $period = isset($options['rate_limit_period']) ? intval($options['rate_limit_period']) : 60;
            CodeweberFormsHooks::send_error($form_id, [], __('Rate limit exceeded.', 'codeweber'));
            return new WP_Error(
                'rate_limit_exceeded', 
                sprintf(__('Too many submissions. Please try again in %d minutes.', 'codeweber'), $period), 
                ['status' => 429]
            );
        }
        
        // Получаем настройки формы
        $form_settings = $this->get_form_settings($form_id);
        if (!$form_settings) {
            CodeweberFormsHooks::send_error($form_id, [], __('Form not found.', 'codeweber'));
            return new WP_Error('form_not_found', __('Form not found.', 'codeweber'), ['status' => 404]);
        }
        
        // Валидация полей (передаем form_id для проверки типа формы)
        error_log('=== FORM SUBMIT API START ===');
        error_log('Form ID: ' . print_r($form_id, true));
        error_log('Form ID type: ' . (is_numeric($form_id) ? 'numeric (CPT)' : 'string (legacy)'));
        error_log('Form settings: ' . print_r($form_settings, true));
        error_log('Fields: ' . print_r($fields, true));
        error_log('Submitted newsletter_consents: ' . print_r($submitted_newsletter_consents, true));
        
        $validation_result = $this->validate_fields($fields, $form_settings, $form_id);
        if (!$validation_result['valid']) {
            error_log('Field validation failed: ' . $validation_result['message']);
            return new WP_Error('validation_failed', $validation_result['message'], ['status' => 400]);
        }
        error_log('Field validation passed');
        
        // Валидация согласий для newsletter формы (как в форме отзывов)
        if (codeweber_forms_is_newsletter_form($form_id) && function_exists('codeweber_forms_validate_consents')) {
            error_log('=== NEWSLETTER CONSENTS VALIDATION START ===');
            error_log('Form ID: ' . print_r($form_id, true));
            error_log('Form ID type: ' . (is_numeric($form_id) ? 'numeric (CPT)' : 'string (legacy)'));
            
            // НОВОЕ: Для CPT форм согласия извлекаются из блоков формы, а не из глобальных настроек
            $newsletter_consents_config = [];
            
            if (is_numeric($form_id)) {
                // CPT форма - извлекаем согласия из блоков form-field с типом consents_block
                error_log('CPT form - extracting consents from blocks');
                if (class_exists('CodeweberFormsCore')) {
                    $newsletter_consents_config = CodeweberFormsCore::extract_consents_from_blocks($form_id);
                    error_log('Extracted consents from blocks: ' . print_r($newsletter_consents_config, true));
                } else {
                    error_log('CodeweberFormsCore class not found - cannot extract consents from blocks');
                }
            } else {
                // LEGACY: Для встроенных форм (строковый ID) используем глобальные настройки
                error_log('Legacy form - using global builtin_form_consents');
                $all_consents = get_option('builtin_form_consents', []);
                $newsletter_consents_config = isset($all_consents['newsletter']) ? $all_consents['newsletter'] : [];
                error_log('Legacy consents config: ' . print_r($newsletter_consents_config, true));
            }
            
            error_log('Final consents config: ' . print_r($newsletter_consents_config, true));
            error_log('Consents config is array: ' . (is_array($newsletter_consents_config) ? 'YES' : 'NO'));
            error_log('Consents config is empty: ' . (empty($newsletter_consents_config) ? 'YES' : 'NO'));
            
            if (!empty($newsletter_consents_config) && is_array($newsletter_consents_config)) {
                // Получаем обязательные согласия
                $required_consents = [];
                foreach ($newsletter_consents_config as $consent) {
                    if (!empty($consent['required']) && !empty($consent['document_id'])) {
                        $required_consents[] = intval($consent['document_id']);
                    }
                }
                
                error_log('Required consents (document IDs): ' . print_r($required_consents, true));
                
                // Получаем отправленные согласия (из параметра или из fields)
                $submitted_consents = [];
                error_log('Submitted newsletter_consents parameter: ' . print_r($submitted_newsletter_consents, true));
                error_log('Fields newsletter_consents: ' . print_r(isset($fields['newsletter_consents']) ? $fields['newsletter_consents'] : 'NOT SET', true));
                
                // Сначала проверяем параметр запроса
                if (!empty($submitted_newsletter_consents) && is_array($submitted_newsletter_consents)) {
                    error_log('Processing newsletter_consents from request parameter');
                    // Преобразуем в формат для валидации (простой массив doc_id => '1')
                    foreach ($submitted_newsletter_consents as $doc_id => $value) {
                        if (is_array($value) && isset($value['value'])) {
                            $submitted_consents[$doc_id] = $value['value'];
                        } else {
                            $submitted_consents[$doc_id] = $value;
                        }
                    }
                } 
                // Затем проверяем в fields
                if (empty($submitted_consents) && isset($fields['newsletter_consents']) && is_array($fields['newsletter_consents'])) {
                    error_log('Processing newsletter_consents from fields');
                    // Преобразуем в формат для валидации
                    foreach ($fields['newsletter_consents'] as $doc_id => $value) {
                        if (is_array($value) && isset($value['value'])) {
                            $submitted_consents[$doc_id] = $value['value'];
                        } else {
                            $submitted_consents[$doc_id] = $value;
                        }
                    }
                }
                
                error_log('Final submitted consents (after processing): ' . print_r($submitted_consents, true));
                error_log('Submitted consents keys (document IDs): ' . print_r(array_keys($submitted_consents), true));
                
                // Валидация
                if (!empty($required_consents)) {
                    error_log('Running validation function...');
                    $validation = codeweber_forms_validate_consents($submitted_consents, $required_consents);
                    error_log('Validation result: ' . print_r($validation, true));
                    
                    if (!$validation['valid']) {
                        error_log('VALIDATION FAILED - Missing required consents');
                        error_log('=== NEWSLETTER CONSENTS VALIDATION END (FAILED) ===');
                        return new WP_Error(
                            'consent_required',
                            __('Please accept all required consents.', 'codeweber'),
                            ['status' => 400]
                        );
                    } else {
                        error_log('VALIDATION PASSED');
                    }
                } else {
                    error_log('No required consents configured - skipping validation');
                }
            } else {
                error_log('No consents config found or config is not an array - skipping validation');
            }
            
            error_log('=== NEWSLETTER CONSENTS VALIDATION END (SUCCESS) ===');
        } else {
            error_log('Form is NOT newsletter form or validate_consents function not available');
            error_log('codeweber_forms_is_newsletter_form result: ' . (function_exists('codeweber_forms_is_newsletter_form') ? (codeweber_forms_is_newsletter_form($form_id) ? 'TRUE' : 'FALSE') : 'FUNCTION NOT EXISTS'));
            error_log('codeweber_forms_validate_consents function exists: ' . (function_exists('codeweber_forms_validate_consents') ? 'YES' : 'NO'));
        }
        
        // Исключаем newsletter_consents из fields перед санитизацией
        // Делаем это ДО логики newsletter, чтобы в ней тоже можно было использовать $newsletter_consents_for_save
        $newsletter_consents_for_save = null;
        
        // Сначала проверяем отдельный параметр newsletter_consents
        if (!empty($submitted_newsletter_consents) && is_array($submitted_newsletter_consents)) {
            error_log('Form Submit - Found newsletter_consents in request parameter');
            $newsletter_consents_for_save = $submitted_newsletter_consents;
        }
        // Затем проверяем в fields
        elseif (isset($fields['newsletter_consents']) && is_array($fields['newsletter_consents'])) {
            error_log('Form Submit - Found newsletter_consents in fields');
            $newsletter_consents_for_save = $fields['newsletter_consents'];
        }

        // Если есть согласия — сразу обогащаем их версией документа,
        // чтобы далее (в том числе в логике ресабскрайба) всегда использовать единый формат
        if ($newsletter_consents_for_save !== null) {
            error_log('Form Submit - Processing newsletter_consents_for_save: ' . print_r($newsletter_consents_for_save, true));
            
            // Сохраняем версии документов на момент подписки
            $consents_with_versions = [];
            foreach ($newsletter_consents_for_save as $doc_id => $value) {
                // Обрабатываем разные форматы: '1', 1, ['value' => '1'], etc.
                $consent_value = null;
                if (is_array($value)) {
                    $consent_value = isset($value['value']) ? $value['value'] : (isset($value[0]) ? $value[0] : null);
                } else {
                    $consent_value = $value;
                }
                
                if ($consent_value === '1' || $consent_value === 1) {
                    $doc_id = intval($doc_id);
                    $doc = get_post($doc_id);
                    if ($doc) {
                        // Сохраняем ID документа и дату его последнего изменения (версию)
                        $consents_with_versions[$doc_id] = [
                            'value' => '1',
                            'document_id' => $doc_id,
                            'document_version' => $doc->post_modified, // Дата последнего изменения документа
                            'document_version_timestamp' => strtotime($doc->post_modified), // Timestamp для удобства
                        ];
                        error_log('Form Submit - Added consent for doc_id: ' . $doc_id . ' (version: ' . $doc->post_modified . ')');
                    } else {
                        // Если документ не найден, сохраняем как есть
                        $consents_with_versions[$doc_id] = $value;
                        error_log('Form Submit - WARNING: Document not found for doc_id: ' . $doc_id);
                    }
                } else {
                    error_log('Form Submit - Skipping consent for doc_id: ' . $doc_id . ' (value: ' . print_r($value, true) . ')');
                }
            }
            $newsletter_consents_for_save = $consents_with_versions;
            error_log('Form Submit - Final newsletter_consents_for_save: ' . print_r($newsletter_consents_for_save, true));
            
            // Убираем из fields, чтобы не санитизировалось как обычное поле
            unset($fields['newsletter_consents']);
        } else {
            error_log('Form Submit - WARNING: No newsletter_consents found in request or fields!');
        }
        
        /**
         * Дополнительная логика для newsletter-форм:
         * 1) Если email уже подписан (status = confirmed) — возвращаем явную ошибку "уже подписан".
         * 2) Если статус = unsubscribed — реактивируем подписку и отправляем письмо заново.
         * 
         * Это выполняется до сохранения отправки формы, чтобы пользователь сразу получил корректное сообщение.
         */
        if (codeweber_forms_is_newsletter_form($form_id)) {
            // Определяем email из полей формы
            // Сначала проверяем поле с именем 'email'
            $email_raw = $fields['email'] ?? '';
            $email = sanitize_email($email_raw);
            
            // Если email не найден по имени 'email', ищем в других полях по значению
            if (empty($email) || !is_email($email)) {
                foreach ($fields as $field_name => $field_value) {
                    // Пропускаем служебные поля
                    if (in_array($field_name, ['form_id', 'form_nonce', 'form_honeypot', '_wp_http_referer', 'newsletter_consents', 'utm_params', 'tracking_data', '_utm_data'])) {
                        continue;
                    }
                    // Проверяем, является ли значение валидным email
                    if (!empty($field_value) && is_email($field_value)) {
                        $email = sanitize_email($field_value);
                        break;
                    }
                }
            }

            if (!empty($email) && is_email($email)) {
                global $wpdb;
                $table_name = $wpdb->prefix . 'newsletter_subscriptions';

                $subscription = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$table_name} WHERE email = %s",
                    $email
                ));

                if ($subscription) {
                    // Уже подтвержден — показываем явное сообщение "уже подписан"
                    if ($subscription->status === 'confirmed') {
                        $message = __('This email is already subscribed to the newsletter.', 'codeweber');
                        CodeweberFormsHooks::send_error($form_id, $form_settings, $message);
                        return new WP_Error(
                            'already_subscribed',
                            $message,
                            ['status' => 400]
                        );
                    }

                    // Ранее отписался — реактивируем
                    if ($subscription->status === 'unsubscribed') {
                        $unsubscribe_token = wp_generate_password(32, false);

                        // Получаем IP и User Agent для обновления при реактивации
                        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
                        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

                        // Обновляем историю событий (events_history)
                        $events = [];
                        if (!empty($subscription->events_history)) {
                            $decoded = json_decode($subscription->events_history, true);
                            if (is_array($decoded)) {
                                $events = $decoded;
                            }
                        }
                        $now = current_time('mysql');
                        
                        // Получаем название формы для события
                        $form_name_for_event = '';
                        if (!empty($form_name_from_request)) {
                            $form_name_for_event = sanitize_text_field($form_name_from_request);
                        } elseif (is_numeric($form_id) && $form_id > 0) {
                            // Если название не пришло в запросе, получаем из CPT
                            $form_post = get_post((int) $form_id);
                            if ($form_post && $form_post->post_type === 'codeweber_form' && !empty($form_post->post_title)) {
                                $form_name_for_event = $form_post->post_title;
                            }
                        }
                        
                        // Нормализуем form_id для события (используем НОВЫЙ из запроса, а не старый из базы)
                        $normalized_form_id = is_numeric($form_id) ? (string) (int) $form_id : (string) $form_id;
                        
                        $event = [
                            'type'      => 'confirmed',
                            'date'      => $now,
                            'source'    => 'codeweber_form_resubscribe',
                            'form_id'   => $normalized_form_id, // ИСПРАВЛЕНО: используем новый form_id из запроса
                            'form_name' => $form_name_for_event, // ИСПРАВЛЕНО: получаем название из CPT если нужно
                            'page_url'  => wp_get_referer() ?: home_url($_SERVER['REQUEST_URI'] ?? '/'),
                            'ip_address' => sanitize_text_field($ip_address), // ИСПРАВЛЕНО: сохраняем IP в событии истории
                        ];

                        // Если в текущем запросе есть согласия, добавляем их в событие
                        if (!empty($newsletter_consents_for_save) && is_array($newsletter_consents_for_save)) {
                            $consents_for_event = [];

                            if (!function_exists('codeweber_forms_get_document_url')) {
                                require_once get_template_directory() . '/functions/integrations/codeweber-forms/codeweber-forms-consent-helper.php';
                            }

                            foreach ($newsletter_consents_for_save as $doc_id => $consent) {
                                $doc = get_post($doc_id);
                                if (!$doc) {
                                    continue;
                                }

                                $doc_title = $doc->post_title;
                                // Берем версию из согласия, а если её нет — из поста
                                $version = $consent['document_version'] ?? ($consent['document_version_timestamp'] ?? $doc->post_modified);

                                // Получаем корректный URL (с ревизией, если есть)
                                $doc_url = codeweber_forms_get_document_url($doc_id, $version);

                                $consents_for_event[] = [
                                    'id'                  => (int) $doc_id,
                                    'title'               => $doc_title,
                                    'document_revision_id'=> $consent['document_revision_id'] ?? null,
                                    'document_version'    => $version,
                                    'url'                 => $doc_url,
                                ];
                            }

                            if (!empty($consents_for_event)) {
                                $event['consents'] = $consents_for_event;
                            }
                        } else {
                            // Если новых согласий нет, пробуем взять их из последнего события с consents
                            if (!empty($events) && is_array($events)) {
                                for ($i = count($events) - 1; $i >= 0; $i--) {
                                    if (!empty($events[$i]['consents']) && is_array($events[$i]['consents'])) {
                                        $event['consents'] = $events[$i]['consents'];
                                        break;
                                    }
                                }
                            }
                        }

                        $events[] = $event;

                        // ВАЖНО: не затираем confirmed_at и unsubscribed_at, чтобы сохранялась история.
                        // Обновляем статус, form_id (на новый из запроса), ip_address, updated_at, новый unsubscribe_token и историю событий.
                        // При повторной подписке через форму codeweber:
                        // - статус: confirmed
                        // - form_id: обновляем на новый из запроса (ИСПРАВЛЕНО)
                        // - ip_address: обновляем на новый (ИСПРАВЛЕНО)
                        // - confirmed_at: дата последней подписки
                        // - unsubscribed_at: очищаем (пользователь снова подписан)
                        $normalized_form_id = is_numeric($form_id) ? (string) (int) $form_id : (string) $form_id;
                        $updated = $wpdb->update(
                            $table_name,
                            [
                                'form_id'          => $normalized_form_id, // ИСПРАВЛЕНО: обновляем на новый form_id
                                'ip_address'       => sanitize_text_field($ip_address), // ИСПРАВЛЕНО: обновляем IP на новый
                                'user_agent'       => sanitize_textarea_field($user_agent), // ИСПРАВЛЕНО: обновляем user_agent на новый
                                'status'            => 'confirmed',
                                'confirmed_at'      => $now,
                                'unsubscribed_at'   => null,
                                'updated_at'        => $now,
                                'unsubscribe_token' => $unsubscribe_token,
                                'events_history'    => wp_json_encode($events, JSON_UNESCAPED_UNICODE),
                            ],
                            ['id' => $subscription->id],
                            ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'],
                            ['%d']
                        );

                        // Продолжаем обработку как успешную подписку (форма отработает "успешно")
                    }
                }
            }
        }
        
        if ($newsletter_consents_for_save !== null) {
            error_log('Form Submit - Processing newsletter_consents_for_save: ' . print_r($newsletter_consents_for_save, true));
            
            // Сохраняем версии документов на момент подписки
            $consents_with_versions = [];
            foreach ($newsletter_consents_for_save as $doc_id => $value) {
                // Обрабатываем разные форматы: '1', 1, ['value' => '1'], etc.
                $consent_value = null;
                if (is_array($value)) {
                    $consent_value = isset($value['value']) ? $value['value'] : (isset($value[0]) ? $value[0] : null);
                } else {
                    $consent_value = $value;
                }
                
                if ($consent_value === '1' || $consent_value === 1) {
                    $doc_id = intval($doc_id);
                    $doc = get_post($doc_id);
                    if ($doc) {
                        // Сохраняем ID документа и дату его последнего изменения (версию)
                        $consents_with_versions[$doc_id] = [
                            'value' => '1',
                            'document_id' => $doc_id,
                            'document_version' => $doc->post_modified, // Дата последнего изменения документа
                            'document_version_timestamp' => strtotime($doc->post_modified), // Timestamp для удобства
                        ];
                        error_log('Form Submit - Added consent for doc_id: ' . $doc_id . ' (version: ' . $doc->post_modified . ')');
                    } else {
                        // Если документ не найден, сохраняем как есть
                        $consents_with_versions[$doc_id] = $value;
                        error_log('Form Submit - WARNING: Document not found for doc_id: ' . $doc_id);
                    }
                } else {
                    error_log('Form Submit - Skipping consent for doc_id: ' . $doc_id . ' (value: ' . print_r($value, true) . ')');
                }
            }
            $newsletter_consents_for_save = $consents_with_versions;
            error_log('Form Submit - Final newsletter_consents_for_save: ' . print_r($newsletter_consents_for_save, true));
            
            // Убираем из fields, чтобы не санитизировалось как обычное поле
            unset($fields['newsletter_consents']);
        } else {
            error_log('Form Submit - WARNING: No newsletter_consents found in request or fields!');
        }
        
        // Санитизация данных
        $sanitized_fields = $this->sanitize_fields($fields);
        error_log('Form Submit - sanitized_fields (before adding consents): ' . print_r($sanitized_fields, true));
        
        // Для newsletter форм: если email не найден по имени 'email', ищем его в других полях
        if ($form_id && function_exists('codeweber_forms_is_newsletter_form') && codeweber_forms_is_newsletter_form($form_id)) {
            if (empty($sanitized_fields['email']) || !is_email($sanitized_fields['email'])) {
                // Ищем email значение в других полях
                foreach ($sanitized_fields as $field_name => $field_value) {
                    // Пропускаем служебные поля
                    if (in_array($field_name, ['form_id', 'form_nonce', 'form_honeypot', '_wp_http_referer', 'newsletter_consents', 'utm_params', 'tracking_data', '_utm_data'])) {
                        continue;
                    }
                    // Проверяем, является ли значение валидным email
                    if (!empty($field_value) && is_email($field_value)) {
                        $sanitized_fields['email'] = sanitize_email($field_value);
                        error_log('Form Submit - Mapped email field: ' . $field_name . ' -> email');
                        break;
                    }
                }
            }
        }
        
        // Добавляем newsletter_consents обратно после санитизации
        if ($newsletter_consents_for_save !== null) {
            $sanitized_fields['newsletter_consents'] = $newsletter_consents_for_save;
            error_log('Form Submit - Added newsletter_consents to sanitized_fields');
        } else {
            error_log('Form Submit - WARNING: newsletter_consents_for_save is null, not adding to sanitized_fields');
        }
        error_log('Form Submit - sanitized_fields (final): ' . print_r($sanitized_fields, true));
        
        // ИМЕНА ФОРМЫ ВСЕГДА БЕРЕТСЯ ИЗ TITLE CPT ФОРМЫ
        // Источник всегда один - post_title из CPT по form_id
        $form_name_for_integrations = '';
        
        // Получаем title из CPT формы
        if (is_numeric($form_id) && (int) $form_id > 0) {
            $form_post = get_post((int) $form_id);
            if ($form_post && $form_post->post_type === 'codeweber_form' && !empty($form_post->post_title)) {
                $form_name_for_integrations = $form_post->post_title;
            }
        }
        
        // Всегда добавляем _form_name в sanitized_fields для использования в интеграциях
        // (newsletter, логи и т.д.)
        if (!empty($form_name_for_integrations)) {
            $sanitized_fields['_form_name'] = $form_name_for_integrations;
            error_log('Form Submit - Added _form_name to sanitized_fields from CPT title: ' . $form_name_for_integrations);
        } else {
            error_log('Form Submit - WARNING: Could not get form_name from CPT title. form_id: ' . var_export($form_id, true));
        }

        // Обработка файлов
        $files_data = $this->handle_file_uploads($request);
        
        // Сохранение в БД
        $db = new CodeweberFormsDatabase();
        
        // Определяем название формы для сохранения в БД
        $form_name_for_db = '';
        
        // Приоритет 1: Если пришло название из запроса (атрибут name в шорткоде)
        if (!empty($form_name_from_request) && trim($form_name_from_request) !== '') {
            $form_name_for_db = sanitize_text_field($form_name_from_request);
        }
        // Приоритет 2: Если form_id - число (CPT форма), получаем название из заголовка CPT записи
        elseif (is_numeric($form_id) && (int) $form_id > 0) {
            $form_post = get_post((int) $form_id);
            if ($form_post && $form_post->post_type === 'codeweber_form' && !empty($form_post->post_title)) {
                $form_name_for_db = $form_post->post_title;
            }
        }
        // Приоритет 3: Для встроенных форм (newsletter, testimonial и т.п.) используем лейблы
        elseif (is_string($form_id)) {
            $builtin_labels = [
                'testimonial' => __('Testimonial Form', 'codeweber'),
                'newsletter'  => __('Newsletter Subscription', 'codeweber'),
                'resume'      => __('Resume Form', 'codeweber'),
                'callback'    => __('Callback Request', 'codeweber'),
            ];
            if (isset($builtin_labels[$form_id])) {
                $form_name_for_db = $builtin_labels[$form_id];
            } else {
                // Фоллбек: если это какой‑то другой строковый ID, используем его как есть
                $form_name_for_db = $form_id;
            }
        }
        // Приоритет 4: Используем formTitle из настроек формы
        if (empty($form_name_for_db)) {
            $form_name_for_db = $form_settings['formTitle'] ?? 'Contact Form';
        }

        $submission_id = $db->save_submission([
            'form_id'   => $form_id,
            // form_name: приоритет - название из запроса, затем из заголовка CPT, затем из настроек
            'form_name' => $form_name_for_db,
            'submission_data' => $sanitized_fields,
            'files_data' => $files_data,
            'ip_address' => $ip_address,
            'user_agent' => $user_agent,
            'user_id' => $user_id,
        ]);
        
        if (!$submission_id) {
            CodeweberFormsHooks::send_error($form_id, $form_settings, __('Failed to save submission.', 'codeweber'));
            return new WP_Error('save_failed', __('Failed to save submission.', 'codeweber'), ['status' => 500]);
        }
        
        // Хук перед отправкой
        CodeweberFormsHooks::before_send($form_id, $form_settings, $sanitized_fields);
        
        // Отправка email администратору
        $email_sent = false;
        $email_error = null;
        $email_templates = get_option('codeweber_forms_email_templates', []);
        $admin_notification_enabled = isset($email_templates['admin_notification_enabled']) 
            ? $email_templates['admin_notification_enabled'] 
            : true;
        
        if ($admin_notification_enabled && !empty($form_settings['recipientEmail'])) {
            $email_result = $this->send_email($form_id, $form_settings, $sanitized_fields, $files_data, $submission_id, 'admin');
            $email_sent = $email_result['success'];
            $email_error = $email_result['error'] ?? null;
            
            // Хук при ошибке отправки email
            if (!$email_sent && $email_error) {
                CodeweberFormsHooks::send_error($form_id, $form_settings, $email_error);
            }
            
            // Обновляем статус отправки email
            $db->update_submission($submission_id, [
                'email_sent' => $email_sent ? 1 : 0,
                'email_error' => $email_error,
            ]);
        }
        
        // Для newsletter форм: создаем подписку ДО отправки auto-reply, чтобы unsubscribe_url был доступен
        if (codeweber_forms_is_newsletter_form($form_id) && !empty($sanitized_fields['email'])) {
            $user_email = sanitize_email($sanitized_fields['email']);
            if (is_email($user_email)) {
                // Вызываем интеграцию newsletter ДО отправки auto-reply
                if (function_exists('codeweber_forms_newsletter_integration')) {
                    codeweber_forms_newsletter_integration($submission_id, $form_id, $sanitized_fields);
                }
            }
        }
        
        // Отправка auto-reply пользователю
        // Определяем тип формы для выбора правильного шаблона
        $form_type = $this->detect_form_type($form_id, $form_settings);
        
        $auto_reply_sent = false;
        $auto_reply_error = null;
        
        if (!empty($sanitized_fields['email'])) {
            $user_email = sanitize_email($sanitized_fields['email']);
            if (is_email($user_email)) {
                // Отправляем соответствующий шаблон в зависимости от типа формы
                $auto_reply_result = $this->send_auto_reply($form_id, $form_settings, $sanitized_fields, $user_email, $form_type, $submission_id);
                $auto_reply_sent = $auto_reply_result['success'] ?? false;
                $auto_reply_error = $auto_reply_result['error'] ?? null;
                
                // Обновляем статус отправки автоответа
                if ($submission_id) {
                    $db->update_submission($submission_id, [
                        'auto_reply_sent' => $auto_reply_sent ? 1 : 0,
                        'auto_reply_error' => $auto_reply_error,
                    ]);
                }
            }
        }
        
        // Хук после сохранения
        error_log('Form Submit - Calling codeweber_form_saved hook with submission_id: ' . $submission_id . ', form_id: ' . $form_id);
        error_log('Form Submit - Data passed to hook: ' . print_r($sanitized_fields, true));
        CodeweberFormsHooks::after_saved($submission_id, $form_id, $sanitized_fields);
        error_log('=== FORM SUBMIT DEBUG END ===');
        
        // Хук после отправки
        CodeweberFormsHooks::after_send($form_id, $form_settings, $submission_id);
        
        // Special message for newsletter subscription form
        // If form has custom successMessage in meta, use it; otherwise use default for newsletter
        if (codeweber_forms_is_newsletter_form($form_id)) {
            // Check if custom message is set in form meta
            $custom_message = '';
            if (is_numeric($form_id)) {
                $custom_message = get_post_meta($form_id, '_codeweber_form_success_message', true);
            }
            // Use custom message if exists, otherwise use newsletter default
            $success_message = !empty($custom_message) ? $custom_message : __('Thank you for subscribing!', 'codeweber');
        } else {
            $success_message = $form_settings['successMessage'] ?? __('Thank you! Your message has been sent.', 'codeweber');
        }
        
        error_log('=== FORM SUBMIT API END (SUCCESS) ===');
        error_log('Submission ID: ' . $submission_id);
        
        return new WP_REST_Response([
            'success' => true,
            'message' => $success_message,
            'submission_id' => $submission_id,
        ], 200);
    }
    
    /**
     * Get form settings
     * 
     * Priority order:
     * 1. Block attributes (from post_content)
     * 2. Post meta fields (_codeweber_form_*)
     * 3. Global settings (codeweber_forms_options)
     * 4. Default values
     */
    private function get_form_settings($form_id) {
        // Получаем настройки по умолчанию из админки
        $default_options = get_option('codeweber_forms_options', []);
        
        // Если form_id - это ID поста CPT
        if (is_numeric($form_id)) {
            $post = get_post($form_id);
            if ($post && $post->post_type === 'codeweber_form') {
                // Извлекаем настройки из атрибутов блока (приоритет 1)
                $block_attrs = [];
                if (has_blocks($post->post_content)) {
                    $blocks = parse_blocks($post->post_content);
                    foreach ($blocks as $block) {
                        if ($block['blockName'] === 'codeweber-blocks/form' && !empty($block['attrs'])) {
                            $block_attrs = $block['attrs'];
                            break;
                        }
                    }
                }
                
                // Получаем метаполя поста (приоритет 2)
                $post_meta = [
                    'recipientEmail' => get_post_meta($post->ID, '_codeweber_form_recipient_email', true),
                    'senderEmail' => get_post_meta($post->ID, '_codeweber_form_sender_email', true),
                    'senderName' => get_post_meta($post->ID, '_codeweber_form_sender_name', true),
                    'subject' => get_post_meta($post->ID, '_codeweber_form_subject', true),
                    'successMessage' => get_post_meta($post->ID, '_codeweber_form_success_message', true),
                    'errorMessage' => get_post_meta($post->ID, '_codeweber_form_error_message', true),
                ];
                
                // Применяем приоритет: блок -> метаполя -> глобальные -> дефолты
                return [
                    'formTitle' => $post->post_title,
                    'recipientEmail' => !empty($block_attrs['recipientEmail']) 
                        ? $block_attrs['recipientEmail'] 
                        : (!empty($post_meta['recipientEmail']) 
                            ? $post_meta['recipientEmail'] 
                            : ($default_options['default_recipient_email'] ?? get_option('admin_email'))),
                    'senderEmail' => !empty($block_attrs['senderEmail']) 
                        ? $block_attrs['senderEmail'] 
                        : (!empty($post_meta['senderEmail']) 
                            ? $post_meta['senderEmail'] 
                            : ($default_options['default_sender_email'] ?? get_option('admin_email'))),
                    'senderName' => !empty($block_attrs['senderName']) 
                        ? $block_attrs['senderName'] 
                        : (!empty($post_meta['senderName']) 
                            ? $post_meta['senderName'] 
                            : ($default_options['default_sender_name'] ?? get_bloginfo('name'))),
                    'subject' => !empty($block_attrs['subject']) 
                        ? $block_attrs['subject'] 
                        : (!empty($post_meta['subject']) 
                            ? $post_meta['subject'] 
                            : ($default_options['default_subject'] ?? __('New Form Submission', 'codeweber'))),
                    'successMessage' => !empty($block_attrs['successMessage']) 
                        ? $block_attrs['successMessage'] 
                        : (!empty($post_meta['successMessage']) 
                            ? $post_meta['successMessage'] 
                            : ($default_options['success_message'] ?? __('Thank you! Your message has been sent.', 'codeweber'))),
                    'errorMessage' => !empty($block_attrs['errorMessage']) 
                        ? $block_attrs['errorMessage'] 
                        : (!empty($post_meta['errorMessage']) 
                            ? $post_meta['errorMessage'] 
                            : ($default_options['error_message'] ?? __('An error occurred. Please try again.', 'codeweber'))),
                ];
            }
        }
        
        // Иначе возвращаем настройки по умолчанию из админки
        // Здесь form_id может быть строковым ключом встроенной формы (newsletter, testimonial и т.п.)
        $form_title = 'Contact Form';

        // Пытаемся получить человекочитаемый заголовок для встроенных форм
        if (is_string($form_id)) {
            $key = strtolower($form_id);
            $builtin_labels = [
                'testimonial' => __('Testimonial Form', 'codeweber'),
                'resume'      => __('Resume Form', 'codeweber'),
                'newsletter'  => __('Newsletter Subscription', 'codeweber'),
                'callback'    => __('Callback Request', 'codeweber'),
            ];
            if (isset($builtin_labels[$key])) {
                $form_title = $builtin_labels[$key];
            }
        }

        return [
            'formTitle'      => $form_title,
            'recipientEmail' => $default_options['default_recipient_email'] ?? get_option('admin_email'),
            'senderEmail'    => $default_options['default_sender_email'] ?? get_option('admin_email'),
            'senderName'     => $default_options['default_sender_name'] ?? get_bloginfo('name'),
            'subject'        => $default_options['default_subject'] ?? __('New Form Submission', 'codeweber'),
            'successMessage' => $default_options['success_message'] ?? __('Thank you! Your message has been sent.', 'codeweber'),
            'errorMessage'   => $default_options['error_message'] ?? __('An error occurred. Please try again.', 'codeweber'),
        ];
    }
    
    /**
     * Validate fields
     */
    private function validate_fields($fields, $form_settings, $form_id = 0) {
        if (empty($fields) || !is_array($fields)) {
            error_log('Form validation failed: No fields provided');
            return ['valid' => false, 'message' => __('No fields provided.', 'codeweber')];
        }
        
        // Получаем структуру полей формы для валидации
        $form_fields_config = $this->get_form_fields_config($form_settings);
        
        // Если конфигурация полей пустая, пропускаем детальную валидацию
        // (базовая проверка на наличие полей уже пройдена выше)
        if (empty($form_fields_config)) {
            // Для newsletter формы проверяем наличие email
            if ($form_id && function_exists('codeweber_forms_is_newsletter_form') && codeweber_forms_is_newsletter_form($form_id)) {
                // Ищем email поле - сначала по имени 'email', затем по типу поля или по значению
                $email_value = null;
                
                // 1. Проверяем поле с именем 'email'
                if (!empty($fields['email']) && is_email($fields['email'])) {
                    $email_value = $fields['email'];
                } else {
                    // 2. Ищем поле с типом email или newsletter в значениях
                    // Проходим по всем полям и ищем email значение
                    foreach ($fields as $field_name => $field_value) {
                        // Пропускаем служебные поля
                        if (in_array($field_name, ['form_id', 'form_nonce', 'form_honeypot', '_wp_http_referer', 'newsletter_consents', 'utm_params', 'tracking_data', '_utm_data'])) {
                            continue;
                        }
                        
                        // Проверяем, является ли значение валидным email
                        if (!empty($field_value) && is_email($field_value)) {
                            $email_value = $field_value;
                            break;
                        }
                    }
                }
                
                // Если email не найден или невалиден
                if (empty($email_value) || !is_email($email_value)) {
                    return ['valid' => false, 'message' => __('E-Mail обязателен для заполнения', 'codeweber')];
                }
            }
            return ['valid' => true];
        }
        
        $errors = [];
        
        // Валидация каждого поля
        foreach ($form_fields_config as $field_config) {
            $field_name = $field_config['fieldName'] ?? '';
            if (empty($field_name)) continue;
            
            $field_value = $fields[$field_name] ?? '';
            $is_required = !empty($field_config['isRequired']);
            $field_type = $field_config['fieldType'] ?? 'text';
            
            // Проверка обязательности
            if ($is_required && empty($field_value)) {
                $field_label = $field_config['fieldLabel'] ?? $field_name;
                $errors[] = sprintf(__('%s is required.', 'codeweber'), $field_label);
                continue;
            }
            
            // Валидация по типу поля
            if (!empty($field_value)) {
                $validation_result = CodeweberFormsValidator::validate($field_type, $field_value, $field_config);
                if (!$validation_result['valid']) {
                    $errors[] = $validation_result['message'];
                }
            }
        }
        
        if (!empty($errors)) {
            return ['valid' => false, 'message' => implode(' ', $errors)];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Get form fields configuration
     */
    private function get_form_fields_config($form_settings) {
        // TODO: Получить структуру полей из настроек формы
        // Пока возвращаем пустой массив - валидация будет базовая
        return [];
    }
    
    /**
     * Sanitize fields
     */
    private function sanitize_fields($fields) {
        $sanitized = [];
        foreach ($fields as $field_name => $field_value) {
            // Пропускаем служебные поля
            if (in_array($field_name, ['form_id', 'form_nonce', 'form_honeypot', '_wp_http_referer'])) {
                continue;
            }
            
            // Определяем тип поля для правильной санитизации
            $field_type = $this->detect_field_type($field_name, $field_value);
            
            if (is_array($field_value)) {
                $sanitized[$field_name] = array_map(function($value) use ($field_type) {
                    return CodeweberFormsSanitizer::sanitize($value, $field_type);
                }, $field_value);
            } else {
                $sanitized[$field_name] = CodeweberFormsSanitizer::sanitize($field_value, $field_type);
            }
        }
        return $sanitized;
    }
    
    /**
     * Detect field type by name or value
     */
    private function detect_field_type($field_name, $field_value) {
        $name_lower = strtolower($field_name);
        
        if (strpos($name_lower, 'email') !== false) {
            return 'email';
        }
        if (strpos($name_lower, 'url') !== false) {
            return 'url';
        }
        if (strpos($name_lower, 'tel') !== false || strpos($name_lower, 'phone') !== false) {
            return 'tel';
        }
        if (strpos($name_lower, 'message') !== false || strpos($name_lower, 'comment') !== false) {
            return 'textarea';
        }
        
        return 'text';
    }
    
    /**
     * Handle file uploads
     */
    private function handle_file_uploads($request) {
        // TODO: Реализовать загрузку файлов
        return null;
    }
    
    /**
     * Send email
     */
    private function send_email($form_id, $form_settings, $fields, $files_data, $submission_id, $type = 'admin') {
        try {
            $email_templates = get_option('codeweber_forms_email_templates', []);
            
            if ($type === 'admin') {
                $recipient = $form_settings['recipientEmail'] ?? get_option('admin_email');
                $subject = isset($email_templates['admin_notification_subject']) && !empty($email_templates['admin_notification_subject'])
                    ? $email_templates['admin_notification_subject']
                    : ($form_settings['subject'] ?? __('New Form Submission', 'codeweber'));
            } elseif ($type === 'testimonial_reply') {
                $recipient = $fields['email'] ?? '';
                $subject = isset($email_templates['testimonial_reply_subject']) && !empty($email_templates['testimonial_reply_subject'])
                    ? $email_templates['testimonial_reply_subject']
                    : __('Thank you for your testimonial', 'codeweber');
            } elseif ($type === 'resume_reply') {
                $recipient = $fields['email'] ?? '';
                $subject = isset($email_templates['resume_reply_subject']) && !empty($email_templates['resume_reply_subject'])
                    ? $email_templates['resume_reply_subject']
                    : __('Your resume has been received', 'codeweber');
            } elseif ($type === 'newsletter_reply') {
                $recipient = $fields['email'] ?? '';
                $subject = isset($email_templates['newsletter_reply_subject']) && !empty($email_templates['newsletter_reply_subject'])
                    ? $email_templates['newsletter_reply_subject']
                    : __('Thank you for subscribing', 'codeweber');
            } else {
                // auto_reply по умолчанию
                $recipient = $fields['email'] ?? '';
                $subject = isset($email_templates['auto_reply_subject']) && !empty($email_templates['auto_reply_subject'])
                    ? $email_templates['auto_reply_subject']
                    : __('Thank you for your message', 'codeweber');
            }
            
            // Обработка переменных в теме письма
            $subject = str_replace(
                ['{form_name}', '{user_name}', '{site_name}'],
                [
                    $form_settings['formTitle'] ?? 'Contact Form',
                    $fields['name'] ?? '',
                    get_bloginfo('name')
                ],
                $subject
            );
            
            // Получаем шаблон письма
            $template = $this->get_email_template($form_id, $type);
            
            // Подготовка данных для шаблона
            $template_data = [
                'form_name'       => $form_settings['formTitle'] ?? 'Contact Form',
                'fields'          => $fields,
                'user_name'       => $fields['name'] ?? '',
                'user_email'      => $fields['email'] ?? '',
                'submission_date' => current_time('mysql'),
                'ip_address'      => $this->get_client_ip(),
                'user_agent'      => $_SERVER['HTTP_USER_AGENT'] ?? '',
            ];

            // Специально для newsletter шаблонов: пытаемся добавить ссылку для отписки
            if ($type === 'newsletter_reply' && !empty($template_data['user_email']) && function_exists('add_query_arg')) {
                global $wpdb;
                $table_name = $wpdb->prefix . 'newsletter_subscriptions';

                $subscription = $wpdb->get_row($wpdb->prepare(
                    "SELECT unsubscribe_token FROM {$table_name} WHERE email = %s LIMIT 1",
                    $template_data['user_email']
                ));

                if ($subscription && !empty($subscription->unsubscribe_token)) {
                    $unsubscribe_url = add_query_arg(
                        [
                            'action' => 'newsletter_unsubscribe',
                            'email'  => rawurlencode($template_data['user_email']),
                            'token'  => rawurlencode($subscription->unsubscribe_token),
                        ],
                        home_url('/')
                    );
                    $template_data['unsubscribe_url'] = $unsubscribe_url;
                } else {
                    // Если подписка ещё не создана, оставляем пустым (будет обрабатываться модулем newsletter)
                    $template_data['unsubscribe_url'] = '';
                }
            }
            
            // Обработка шаблона
            $message = CodeweberFormsMailer::process_template($template, $template_data);
            
            // Отправка
            $sent = CodeweberFormsMailer::send($form_id, $form_settings, $recipient, $subject, $message);
            
            if ($sent) {
                return ['success' => true];
            } else {
                return ['success' => false, 'error' => __('Email sending failed.', 'codeweber')];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Detect form type based on form_id and form_settings
     * 
     * НОВОЕ: Использует единую функцию get_form_type() для определения типа
     */
    private function detect_form_type($form_id, $form_settings) {
        // НОВОЕ: Используем единую функцию для получения типа формы
        if (class_exists('CodeweberFormsCore')) {
            $form_type = CodeweberFormsCore::get_form_type($form_id, ['settings' => $form_settings]);
            
            // Маппинг типов форм на типы автоответов
            switch ($form_type) {
                case 'testimonial':
                    return 'testimonial';
                case 'resume':
                    return 'resume';
                case 'newsletter':
                    return 'newsletter';
                case 'callback':
                    return 'callback';
                case 'form':
                default:
                    return 'auto_reply';
            }
        }
        
        // LEGACY: Fallback для обратной совместимости
        $form_name = strtolower($form_settings['formTitle'] ?? '');
        $form_id_str = strtolower($form_id);
        
        // Проверяем по названию формы
        if (strpos($form_name, 'testimonial') !== false || 
            strpos($form_id_str, 'testimonial') !== false) {
            return 'testimonial';
        }
        
        if (strpos($form_name, 'resume') !== false || 
            strpos($form_name, 'cv') !== false ||
            strpos($form_id_str, 'resume') !== false ||
            strpos($form_id_str, 'cv') !== false) {
            return 'resume';
        }
        
        if (strpos($form_name, 'newsletter') !== false || 
            strpos($form_id_str, 'newsletter') !== false) {
            return 'newsletter';
        }
        
        // По умолчанию обычный auto-reply
        return 'auto_reply';
    }
    
    /**
     * Send auto-reply email to user
     * 
     * @return array ['success' => bool, 'error' => string|null]
     */
    private function send_auto_reply($form_id, $form_settings, $fields, $user_email, $form_type = 'auto_reply', $submission_id = 0) {
        try {
            $email_templates = get_option('codeweber_forms_email_templates', []);
            
            // Проверяем, включен ли соответствующий тип ответа
            $enabled = false;
            $template_type = 'auto_reply';
            
            switch ($form_type) {
                case 'testimonial':
                    $enabled = isset($email_templates['testimonial_reply_enabled']) 
                        ? $email_templates['testimonial_reply_enabled'] 
                        : false;
                    $template_type = 'testimonial_reply';
                    break;
                    
                case 'resume':
                    $enabled = isset($email_templates['resume_reply_enabled']) 
                        ? $email_templates['resume_reply_enabled'] 
                        : false;
                    $template_type = 'resume_reply';
                    break;
                    
                case 'newsletter':
                    $enabled = isset($email_templates['newsletter_reply_enabled']) 
                        ? $email_templates['newsletter_reply_enabled'] 
                        : false;
                    $template_type = 'newsletter_reply';
                    break;
                    
                default:
                    $enabled = isset($email_templates['auto_reply_enabled']) 
                        ? $email_templates['auto_reply_enabled'] 
                        : false;
                    $template_type = 'auto_reply';
                    break;
            }
            
            if ($enabled) {
                $email_result = $this->send_email($form_id, $form_settings, $fields, null, $submission_id, $template_type);
                return [
                    'success' => $email_result['success'] ?? false,
                    'error' => $email_result['error'] ?? null,
                ];
            }
            
            return ['success' => false, 'error' => __('Auto-reply is disabled.', 'codeweber')];
        } catch (Exception $e) {
            error_log('Auto-reply email error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get email template
     */
    private function get_email_template($form_id, $type = 'admin') {
        // Проверяем, есть ли переопределение для формы
        if (is_numeric($form_id)) {
            $post = get_post($form_id);
            if ($post && $post->post_type === 'codeweber_form') {
                $template = get_post_meta($post->ID, '_codeweber_form_' . $type . '_email_template', true);
                if (!empty($template)) {
                    return $template;
                }
            }
        }
        
        // Используем шаблон из настроек Email Templates
        $email_templates = get_option('codeweber_forms_email_templates', []);
        
        if ($type === 'admin') {
            $template = isset($email_templates['admin_notification_template']) 
                ? $email_templates['admin_notification_template'] 
                : null;
        } elseif ($type === 'testimonial_reply') {
            $template = isset($email_templates['testimonial_reply_template']) 
                ? $email_templates['testimonial_reply_template'] 
                : null;
        } elseif ($type === 'resume_reply') {
            $template = isset($email_templates['resume_reply_template']) 
                ? $email_templates['resume_reply_template'] 
                : null;
        } elseif ($type === 'newsletter_reply') {
            $template = isset($email_templates['newsletter_reply_template']) 
                ? $email_templates['newsletter_reply_template'] 
                : null;
        } else {
            // auto_reply по умолчанию
            $template = isset($email_templates['auto_reply_template']) 
                ? $email_templates['auto_reply_template'] 
                : null;
        }
        
        // Если шаблон не найден, используем дефолтный
        if (empty($template)) {
            return $this->get_default_template($type);
        }
        
        return $template;
    }
    
    /**
     * Get default email template
     */
    private function get_default_template($type) {
        $templates = new CodeweberFormsEmailTemplates();
        
        if ($type === 'admin') {
            return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #0073aa; color: white; padding: 20px; text-align: center; }
        .content { background-color: #f9f9f9; padding: 20px; }
        .footer { background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background-color: #f5f5f5; padding: 10px; text-align: left; border: 1px solid #ddd; }
        td { padding: 10px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0;">' . __('New Form Submission', 'codeweber') . '</h2>
        </div>
        <div class="content">
            <p><strong>' . __('Form:', 'codeweber') . '</strong> {form_name}</p>
            <p><strong>' . __('Date:', 'codeweber') . '</strong> {submission_date} {submission_time}</p>
            <p><strong>' . __('From:', 'codeweber') . '</strong> {user_name} ({user_email})</p>
            <hr>
            <h3>' . __('Form Fields:', 'codeweber') . '</h3>
            {form_fields}
        </div>
        <div class="footer">
            <p><small>IP: {user_ip}<br>User Agent: {user_agent}</small></p>
        </div>
    </div>
</body>
</html>';
        } elseif ($type === 'testimonial_reply') {
            return $templates->get_default_testimonial_reply_template();
        } elseif ($type === 'resume_reply') {
            return $templates->get_default_resume_reply_template();
        }
        
        return $templates->get_default_auto_reply_template();
    }
    
    /**
     * Get client IP address
     */
    private function get_client_ip() {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }
    
    /**
     * Handle form opened event
     */
    public function form_opened($request) {
        $form_id = $request->get_param('form_id');
        if ($form_id) {
            CodeweberFormsHooks::form_opened($form_id);
        }
        return new WP_REST_Response(['success' => true], 200);
    }
    
    /**
     * Save form configuration
     */
    public function save_form_config($request) {
        // TODO: Implement form config saving
        return new WP_REST_Response(['success' => false, 'message' => 'Not implemented yet'], 200);
    }
    
    /**
     * Get list of forms for Gutenberg block
     */
    public function get_forms_list($request) {
        $forms = get_posts([
            'post_type' => 'codeweber_form',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);
        
        $forms_list = [];
        foreach ($forms as $form) {
            $forms_list[] = [
                'id' => $form->ID,
                'title' => $form->post_title,
                'shortcode' => '[codeweber_form id="' . $form->ID . '"]',
            ];
        }
        
        return new WP_REST_Response([
            'success' => true,
            'forms' => $forms_list,
        ], 200);
    }
    
}


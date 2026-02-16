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
                ],
                'file_ids' => [
                    'required' => false,
                    'type' => 'array',
                    'items' => [
                        'type' => 'string'
                    ]
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
        
        // Endpoint для загрузки файлов (instant upload)
        register_rest_route('codeweber-forms/v1', '/upload', [
            'methods' => 'POST',
            'callback' => [$this, 'upload_file'],
            'permission_callback' => function($request) {
                $nonce = $request->get_header('X-WP-Nonce');
                if (empty($nonce)) {
                    return false;
                }
                return wp_verify_nonce($nonce, 'wp_rest');
            },
            'args' => []
        ]);
        
        // Endpoint для удаления временного файла
        register_rest_route('codeweber-forms/v1', '/upload/(?P<id>[a-f0-9\-]+)', [
            'methods' => 'DELETE',
            'callback' => [$this, 'delete_temp_file'],
            'permission_callback' => function($request) {
                $nonce = $request->get_header('X-WP-Nonce');
                if (empty($nonce)) {
                    return false;
                }
                return wp_verify_nonce($nonce, 'wp_rest');
            },
            'args' => [
                'id' => [
                    'required' => true,
                    'type' => 'string',
                    'validate_callback' => function($param) {
                        return !empty($param) && preg_match('/^[a-f0-9\-]+$/', $param);
                    }
                ]
            ]
        ]);
    }
    
    /**
     * Submit form handler
     */
    public function submit_form($request) {
        $form_id = $request->get_param('form_id');
        $form_name_from_request = $request->get_param('form_name');
        $form_type_from_request = $request->get_param('form_type'); // Тип формы из JavaScript (для default форм)
        
        // Дополнительная проверка через JSON params (на случай, если get_param не работает с JSON)
        if (empty($form_type_from_request)) {
            $json_params = $request->get_json_params();
            if (!empty($json_params['form_type'])) {
                $form_type_from_request = $json_params['form_type'];
            }
        }
        
        $fields = $request->get_param('fields');
        $honeypot = $request->get_param('honeypot');
        $file_ids = $request->get_param('file_ids'); // File IDs from FilePond instant upload
        $utm_params = $request->get_param('utm_params') ?: [];
        $tracking_data = $request->get_param('tracking_data') ?: [];
        $submitted_form_consents = $request->get_param('form_consents'); // Универсальный префикс
        $submitted_newsletter_consents = $request->get_param('newsletter_consents'); // Обратная совместимость
        $submitted_testimonial_consents = $request->get_param('testimonial_consents'); // Обратная совместимость
        
        // If file_ids not in params, try to get from JSON body
        if (empty($file_ids)) {
            $json_params = $request->get_json_params();
            if (!empty($json_params['file_ids'])) {
                $file_ids = $json_params['file_ids'];
            }
        }

        // Fallback: extract file_ids from raw fields if present (e.g. "file[]" sent as part of form data)
        if (empty($file_ids) && !empty($fields)) {
            $file_field_keys = ['file', 'File', 'file[]', 'File[]', 'files', 'Files'];
            $collected_ids = [];
            foreach ($file_field_keys as $key) {
                if (!isset($fields[$key])) {
                    continue;
                }
                $value = $fields[$key];
                if (is_array($value)) {
                    foreach ($value as $v) {
                        if (is_string($v) && preg_match('/^[a-f0-9\\-]{36}$/i', trim($v))) {
                            $collected_ids[] = trim($v);
                        }
                    }
                } elseif (is_string($value)) {
                    // Comma-separated UUIDs
                    $parts = array_map('trim', explode(',', $value));
                    foreach ($parts as $part) {
                        if (preg_match('/^[a-f0-9\\-]{36}$/i', $part)) {
                            $collected_ids[] = $part;
                        }
                    }
                }
            }
            if (!empty($collected_ids)) {
                $file_ids = array_values(array_unique($collected_ids));
            }
        }
        
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
        $validation_result = $this->validate_fields($fields, $form_settings, $form_id);
        if (!$validation_result['valid']) {
            return new WP_Error('validation_failed', $validation_result['message'], ['status' => 400]);
        }
        
        // Валидация согласий для newsletter формы (как в форме отзывов)
        if (codeweber_forms_is_newsletter_form($form_id) && function_exists('codeweber_forms_validate_consents')) {
            // Проверяем, является ли форма default формой (form_id = 0)
            $form_id_int = is_numeric($form_id) ? intval($form_id) : 0;
            $is_default_form = ($form_id_int === 0);
            
            // Default формы (form_id = 0) не имеют согласий, пропускаем проверку
            if (!$is_default_form) {
                // Для CPT форм согласия извлекаются из блоков формы
                $newsletter_consents_config = [];
                
                if (is_numeric($form_id) && $form_id_int > 0) {
                    // CPT форма - извлекаем согласия из блоков form-field с типом consents_block
                    if (class_exists('CodeweberFormsCore')) {
                        $newsletter_consents_config = CodeweberFormsCore::extract_consents_from_blocks($form_id);
                    }
                }
            
            if (!empty($newsletter_consents_config) && is_array($newsletter_consents_config)) {
                // Получаем обязательные согласия
                $required_consents = [];
                foreach ($newsletter_consents_config as $consent) {
                    if (!empty($consent['required']) && !empty($consent['document_id'])) {
                        $required_consents[] = intval($consent['document_id']);
                    }
                }
                
                // Получаем отправленные согласия (из параметра или из fields)
                $submitted_consents = [];
                
                // Сначала проверяем параметр запроса
                if (!empty($submitted_newsletter_consents) && is_array($submitted_newsletter_consents)) {
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
                    // Преобразуем в формат для валидации
                    foreach ($fields['newsletter_consents'] as $doc_id => $value) {
                        if (is_array($value) && isset($value['value'])) {
                            $submitted_consents[$doc_id] = $value['value'];
                        } else {
                            $submitted_consents[$doc_id] = $value;
                        }
                    }
                }
                
                // Валидация
                if (!empty($required_consents)) {
                    $validation = codeweber_forms_validate_consents($submitted_consents, $required_consents);
                    
                    if (!$validation['valid']) {
                        return new WP_Error(
                            'consent_required',
                            __('Please accept all required consents.', 'codeweber'),
                            ['status' => 400]
                        );
                    }
                }
            }
            }
        }
        
        // Исключаем согласия из fields перед санитизацией
        // Делаем это ДО логики newsletter, чтобы в ней тоже можно было использовать $newsletter_consents_for_save
        $newsletter_consents_for_save = null;
        
        // ПРИОРИТЕТ 1: form_consents (универсальный префикс)
        if (!empty($submitted_form_consents) && is_array($submitted_form_consents)) {
            $newsletter_consents_for_save = $submitted_form_consents;
        }
        // ПРИОРИТЕТ 2: newsletter_consents (обратная совместимость)
        elseif (!empty($submitted_newsletter_consents) && is_array($submitted_newsletter_consents)) {
            $newsletter_consents_for_save = $submitted_newsletter_consents;
        }
        // ПРИОРИТЕТ 3: testimonial_consents (обратная совместимость)
        elseif (!empty($submitted_testimonial_consents) && is_array($submitted_testimonial_consents)) {
            $newsletter_consents_for_save = $submitted_testimonial_consents;
        }
        // ПРИОРИТЕТ 4: из fields (form_consents)
        elseif (isset($fields['form_consents']) && is_array($fields['form_consents'])) {
            $newsletter_consents_for_save = $fields['form_consents'];
        }
        // ПРИОРИТЕТ 5: из fields (newsletter_consents - обратная совместимость)
        elseif (isset($fields['newsletter_consents']) && is_array($fields['newsletter_consents'])) {
            $newsletter_consents_for_save = $fields['newsletter_consents'];
        }

        // Если есть согласия — сразу обогащаем их версией документа,
        // чтобы далее (в том числе в логике ресабскрайба) всегда использовать единый формат
        if ($newsletter_consents_for_save !== null) {
            // ОПТИМИЗАЦИЯ: Получаем все документы одним запросом вместо множественных get_post()
            $doc_ids = array_map('intval', array_keys($newsletter_consents_for_save));
            $docs_map = [];
            if (!empty($doc_ids)) {
                $documents = get_posts([
                    'post_type' => 'legal',
                    'post__in' => $doc_ids,
                    'posts_per_page' => -1,
                    'post_status' => 'publish'
                ]);
                
                // Создаем маппинг ID => документ для быстрого доступа
                foreach ($documents as $doc) {
                    $docs_map[$doc->ID] = $doc;
                }
            }
            
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
                    // Используем кешированный документ вместо get_post()
                    $doc = $docs_map[$doc_id] ?? null;
                    if ($doc) {
                        // Сохраняем ID документа и дату его последнего изменения (версию)
                        $consents_with_versions[$doc_id] = [
                            'value' => '1',
                            'document_id' => $doc_id,
                            'document_version' => $doc->post_modified, // Дата последнего изменения документа
                            'document_version_timestamp' => strtotime($doc->post_modified), // Timestamp для удобства
                        ];
                    } else {
                        // Если документ не найден, сохраняем как есть
                        $consents_with_versions[$doc_id] = $value;
                    }
                }
            }
            $newsletter_consents_for_save = $consents_with_versions;
            
            // Убираем из fields, чтобы не санитизировалось как обычное поле
            unset($fields['form_consents']);
            unset($fields['newsletter_consents']);
            unset($fields['testimonial_consents']);
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
                    if (in_array($field_name, ['form_id', 'form_nonce', 'form_honeypot', '_wp_http_referer', 'form_consents', 'newsletter_consents', 'testimonial_consents', 'utm_params', 'tracking_data', '_utm_data'])) {
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
        
        // Санитизация данных
        $sanitized_fields = $this->sanitize_fields($fields);
        
        // Для newsletter форм: если email не найден по имени 'email', ищем его в других полях
        if ($form_id && function_exists('codeweber_forms_is_newsletter_form') && codeweber_forms_is_newsletter_form($form_id)) {
            if (empty($sanitized_fields['email']) || !is_email($sanitized_fields['email'])) {
                // Ищем email значение в других полях
                foreach ($sanitized_fields as $field_name => $field_value) {
                    // Пропускаем служебные поля
                    if (in_array($field_name, ['form_id', 'form_nonce', 'form_honeypot', '_wp_http_referer', 'form_consents', 'newsletter_consents', 'testimonial_consents', 'utm_params', 'tracking_data', '_utm_data'])) {
                        continue;
                    }
                    // Проверяем, является ли значение валидным email
                    if (!empty($field_value) && is_email($field_value)) {
                        $sanitized_fields['email'] = sanitize_email($field_value);
                        break;
                    }
                }
            }
        }
        
        // Добавляем newsletter_consents обратно после санитизации
        if ($newsletter_consents_for_save !== null) {
            $sanitized_fields['newsletter_consents'] = $newsletter_consents_for_save;
        }
        
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
        }

        // Обработка файлов (используем file_ids из запроса)
        $files_data = null;
        if (!empty($file_ids) && is_array($file_ids)) {
            $request->set_param('file_ids', $file_ids);
            $files_data = $this->handle_file_uploads($request);
        }
        
        // Remove file UUIDs from sanitized_fields if they were included as regular fields
        // FilePond might send file IDs through form fields (e.g., "File[]" field)
        if (!empty($sanitized_fields)) {
            // Remove common file field names that might contain UUIDs
            $file_field_patterns = ['File', 'file', 'File[]', 'file[]', 'files', 'Files'];
            foreach ($file_field_patterns as $pattern) {
                if (isset($sanitized_fields[$pattern])) {
                    $field_value = $sanitized_fields[$pattern];
                    // Check if value looks like UUIDs (comma-separated UUIDs)
                    if (is_string($field_value) && preg_match('/^[a-f0-9\-]{36}(,\s*[a-f0-9\-]{36})*$/i', $field_value)) {
                        unset($sanitized_fields[$pattern]);
                    }
                }
            }
        }
        
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

        // Save submission first (files_data will be updated after moving files)
        $submission_id = $db->save_submission([
            'form_id'   => $form_id,
            // form_name: приоритет - название из запроса, затем из заголовка CPT, затем из настроек
            'form_name' => $form_name_for_db,
            'submission_data' => $sanitized_fields,
            'files_data' => null, // Will be updated after files are moved
            'ip_address' => $ip_address,
            'user_agent' => $user_agent,
            'user_id' => $user_id,
        ]);
        
        if (!$submission_id) {
            CodeweberFormsHooks::send_error($form_id, $form_settings, __('Failed to save submission.', 'codeweber'));
            return new WP_Error('save_failed', __('Failed to save submission.', 'codeweber'), ['status' => 500]);
        }
        
        // Process files and move to permanent location
        if (!empty($files_data) && is_array($files_data)) {
            $temp_files = new CodeweberFormsTempFiles();
            $permanent_files = [];
            
            foreach ($files_data as $file_info) {
                if (isset($file_info['file_id'])) {
                    $moved_file = $temp_files->move_to_permanent($file_info['file_id'], $submission_id);
                    if ($moved_file) {
                        $permanent_files[] = [
                            'file_id' => $moved_file['file_id'],
                            'file_url' => $moved_file['file_url'],
                            'file_name' => $moved_file['file_name'],
                            'file_size' => $moved_file['file_size'],
                            'file_type' => $moved_file['file_type']
                        ];
                    }
                }
            }
            
            // Update submission with final files data
            if (!empty($permanent_files)) {
                $db->update_submission($submission_id, [
                    'files_data' => json_encode($permanent_files, JSON_UNESCAPED_UNICODE)
                ]);
                $files_data = $permanent_files;
            } else {
                $files_data = null;
            }
        }
        
        // Хук перед отправкой
        CodeweberFormsHooks::before_send($form_id, $form_settings, $sanitized_fields);
        
        // Отправка email администратору (асинхронно)
        $email_templates = get_option('codeweber_forms_email_templates', []);
        $admin_notification_enabled = isset($email_templates['admin_notification_enabled']) 
            ? $email_templates['admin_notification_enabled'] 
            : true;
        
        if ($admin_notification_enabled && !empty($form_settings['recipientEmail'])) {
            // Планируем отправку через WP Cron (не блокирует ответ)
            wp_schedule_single_event(time(), 'codeweber_forms_send_admin_email', [
                $form_id,
                $form_settings,
                $sanitized_fields,
                $files_data,
                $submission_id
            ]);
            
            // Помечаем как "в процессе отправки" (статус обновится после отправки)
            $db->update_submission($submission_id, [
                'email_sent' => 0,
                'email_error' => null,
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
        
        // Отправка auto-reply пользователю (асинхронно)
        // Определяем тип формы для выбора правильного шаблона
        $form_type = $this->detect_form_type($form_id, $form_settings);
        
        if (!empty($sanitized_fields['email'])) {
            $user_email = sanitize_email($sanitized_fields['email']);
            if (is_email($user_email)) {
                // Планируем отправку через WP Cron (не блокирует ответ)
                wp_schedule_single_event(time(), 'codeweber_forms_send_auto_reply', [
                    $form_id,
                    $form_settings,
                    $sanitized_fields,
                    $user_email,
                    $form_type,
                    $submission_id
                ]);
                
                // Помечаем как "в процессе отправки" (статус обновится после отправки)
                if ($submission_id) {
                    $db->update_submission($submission_id, [
                        'auto_reply_sent' => 0,
                        'auto_reply_error' => null,
                    ]);
                }
            }
        }
        
        // Хук после сохранения
        CodeweberFormsHooks::after_saved($submission_id, $form_id, $sanitized_fields);
        
        // Хук после отправки
        CodeweberFormsHooks::after_send($form_id, $form_settings, $submission_id);
        
        // Special message for newsletter / testimonial / callback forms
        // Приоритет: form_type из запроса -> detect_form_type -> formType из настроек
        $detected_form_type = null;

        // 1) Сначала проверяем мета-поле формы для всех типов форм
        $custom_message = '';
        if (is_numeric($form_id) && (int) $form_id > 0) {
            $custom_message = get_post_meta($form_id, '_codeweber_form_success_message', true);
        }
        
        // Если мета-поле заполнено, используем его для всех типов форм
        if (!empty($custom_message)) {
            $success_message = $custom_message;
        } else {
            // Если мета-поле пустое, определяем тип формы и используем дефолтное сообщение
            
            // 2) Form type из запроса (JavaScript отправляет form_type из data-form-type)
            // Нормализуем значение: приводим к нижнему регистру и обрезаем пробелы
            $form_type_normalized = $form_type_from_request ? strtolower(trim((string) $form_type_from_request)) : '';
            
            if ($form_type_normalized === 'newsletter') {
                $detected_form_type = 'newsletter';
            } elseif ($form_type_normalized === 'testimonial') {
                $detected_form_type = 'testimonial';
            } elseif ($form_type_normalized === 'callback') {
                $detected_form_type = 'callback';
            } elseif ($form_type_normalized === 'resume') {
                $detected_form_type = 'resume';
            }

            // 3) Если не пришло из запроса, пытаемся определить из содержимого/мета
            if (!$detected_form_type) {
                $detected_type = $this->detect_form_type($form_id, $form_settings);
                if ($detected_type === 'newsletter') {
                    $detected_form_type = 'newsletter';
                } elseif ($detected_type === 'testimonial') {
                    $detected_form_type = 'testimonial';
                } elseif ($detected_type === 'callback') {
                    $detected_form_type = 'callback';
                } elseif ($detected_type === 'resume') {
                    $detected_form_type = 'resume';
                }
            }

            // 4) Fallback: formType в настройках формы
            if (!$detected_form_type && !empty($form_settings['formType'])) {
                $form_type_setting = strtolower(trim((string) $form_settings['formType']));
                if (in_array($form_type_setting, ['newsletter', 'testimonial', 'callback', 'resume'], true)) {
                    $detected_form_type = $form_type_setting;
                }
            }
            
            // Определяем дефолтное сообщение в зависимости от типа формы
            if ($detected_form_type === 'newsletter') {
                $success_message = __('Thank you for subscribing!', 'codeweber');
            } elseif ($detected_form_type === 'testimonial') {
                $success_message = __('Thank you for your testimonial', 'codeweber');
            } elseif ($detected_form_type === 'callback') {
                $success_message = __('Thank you for your request', 'codeweber');
            } elseif ($detected_form_type === 'resume') {
                $success_message = __('Your resume has been sent', 'codeweber');
            } else {
                // Для остальных типов форм - стандартное сообщение
                $success_message = $form_settings['successMessage'] ?? __('Thank you! Your message has been sent.', 'codeweber');
            }
        }
        
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
                        if (in_array($field_name, ['form_id', 'form_nonce', 'form_honeypot', '_wp_http_referer', 'form_consents', 'newsletter_consents', 'testimonial_consents', 'utm_params', 'tracking_data', '_utm_data'])) {
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
                    return ['valid' => false, 'message' => __('Email is required', 'codeweber')];
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
     * Upload file (instant upload for FilePond)
     * Uses WordPress standard wp_handle_upload() for security and compatibility
     */
    public function upload_file($request) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        // FilePond sends file as 'filepond' by default
        // But REST API might need special handling
        
        // Try to get file from $_FILES first
        $file_key = null;
        $file = null;
        
        // Check common FilePond field names
        // FilePond sends as 'filepond' but may arrive as 'file' or 'file[]'
        $possible_keys = ['filepond', 'file'];
        
        foreach ($possible_keys as $key) {
            if (!empty($_FILES[$key]) && is_array($_FILES[$key])) {
                // Check if it's array format (multiple files or file[])
                if (isset($_FILES[$key]['tmp_name']) && is_array($_FILES[$key]['tmp_name']) && isset($_FILES[$key]['tmp_name'][0])) {
                    // Array format - take first file
                    if (!empty($_FILES[$key]['tmp_name'][0])) {
                        $file_key = $key;
                        $file = [
                            'name' => $_FILES[$key]['name'][0],
                            'type' => $_FILES[$key]['type'][0],
                            'tmp_name' => $_FILES[$key]['tmp_name'][0],
                            'error' => $_FILES[$key]['error'][0],
                            'size' => $_FILES[$key]['size'][0]
                        ];
                        break;
                    }
                } elseif (isset($_FILES[$key]['tmp_name']) && !is_array($_FILES[$key]['tmp_name'])) {
                    // Single file (not array)
                    if (!empty($_FILES[$key]['tmp_name'])) {
                        $file_key = $key;
                        $file = $_FILES[$key];
                        break;
                    }
                }
            }
        }
        
        // If not found, check all $_FILES keys
        if (!$file && !empty($_FILES)) {
            foreach ($_FILES as $key => $file_data) {
                if (is_array($file_data)) {
                    if (isset($file_data['tmp_name']) && !empty($file_data['tmp_name'])) {
                        $file_key = $key;
                        $file = $file_data;
                        break;
                    } elseif (isset($file_data['tmp_name'][0]) && !empty($file_data['tmp_name'][0])) {
                        $file_key = $key;
                        $file = [
                            'name' => $file_data['name'][0],
                            'type' => $file_data['type'][0],
                            'tmp_name' => $file_data['tmp_name'][0],
                            'error' => $file_data['error'][0],
                            'size' => $file_data['size'][0]
                        ];
                        break;
                    }
                }
            }
        }
        
        
        if (!$file) {
            return new WP_Error('no_file', __('No file uploaded.', 'codeweber'), ['status' => 400]);
        }
        
        // Validate file
        // Ensure error code is scalar (not array)
        $file_error = isset($file['error']) ? $file['error'] : null;
        if (is_array($file_error)) {
            // If error is array, take first element
            $file_error = !empty($file_error) ? reset($file_error) : UPLOAD_ERR_NO_FILE;
        }
        
        if ($file_error === null || $file_error !== UPLOAD_ERR_OK) {
            $error_code = $file_error !== null ? intval($file_error) : 'unknown';
            $error_messages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
            ];
            // Ensure error_code is valid key (int or string)
            $error_message = (is_int($error_code) || is_string($error_code)) && isset($error_messages[$error_code]) 
                ? $error_messages[$error_code] 
                : 'Unknown error';
            return new WP_Error('upload_error', __('File upload error.', 'codeweber') . ': ' . $error_message, ['status' => 400]);
        }
        
        // Get file info
        $file_name = sanitize_file_name($file['name']);
        $file_size = $file['size'];
        $file_type = $file['type'];
        $tmp_name = $file['tmp_name'];
        
        // Validate file exists
        if (!file_exists($tmp_name)) {
            return new WP_Error('file_not_found', __('Temporary file not found.', 'codeweber'), ['status' => 400]);
        }
        
        // WordPress standard file type validation
        $wp_filetype = wp_check_filetype_and_ext($tmp_name, $file_name);
        if (!$wp_filetype['ext'] || !$wp_filetype['type']) {
            return new WP_Error('invalid_file_type', __('Sorry, this file type is not permitted for security reasons.', 'codeweber'), ['status' => 400]);
        }
        
        // Additional allowed types check (from settings)
        $allowed_types = get_option('codeweber_forms_allowed_file_types', []);
        if (!empty($allowed_types) && !in_array($wp_filetype['type'], $allowed_types)) {
            return new WP_Error('invalid_file_type', __('File type not allowed.', 'codeweber'), ['status' => 400]);
        }
        
        // Validate file size
        $max_size = get_option('codeweber_forms_max_file_size', 10 * 1024 * 1024); // Default 10MB
        if ($file_size > $max_size) {
            return new WP_Error('file_too_large', __('File is too large.', 'codeweber'), ['status' => 400]);
        }
        
        // Initialize temp files handler
        $temp_files = new CodeweberFormsTempFiles();
        $temp_dir = $temp_files->get_temp_dir();
        
        // Ensure temp directory exists
        if (!is_dir($temp_dir)) {
            wp_mkdir_p($temp_dir);
        }
        if (!is_writable($temp_dir)) {
            return new WP_Error('dir_not_writable', __('Temporary directory is not writable.', 'codeweber'), ['status' => 500]);
        }
        
        // Use WordPress approach similar to Contact Form 7:
        // - wp_check_filetype_and_ext() for validation (already done above)
        // - wp_unique_filename() for unique filename generation
        // - move_uploaded_file() for moving file
        // - Proper file permissions
        
        // Sanitize and prepare filename
        $filename = sanitize_file_name($file_name);
        $filename = wp_unique_filename($temp_dir, $filename);
        $temp_path = $temp_dir . '/' . $filename;
        
        // Move uploaded file (WordPress standard approach for temp files)
        if (!@move_uploaded_file($tmp_name, $temp_path)) {
            return new WP_Error('move_failed', __('Failed to save file.', 'codeweber'), ['status' => 500]);
        }
        
        // Set correct file permissions (WordPress standard: 0666 masked by umask)
        $stat = stat(dirname($temp_path));
        if ($stat) {
            $perms = $stat['mode'] & 0000666;
            @chmod($temp_path, $perms);
        }
        
        // Use validated file type from wp_check_filetype_and_ext()
        $uploaded_file_type = $wp_filetype['type'];
        
        // Save to database
        $file_data = $temp_files->save_temp_file($temp_path, $file_name, $file_size, $uploaded_file_type);
        
        if (!$file_data) {
            @unlink($temp_path);
            return new WP_Error('save_failed', __('Failed to save file record.', 'codeweber'), ['status' => 500]);
        }
        
        // Return file ID (FilePond expects this as response)
        // FilePond expects just the file ID string, not JSON object
        // But we'll return JSON and parse it in onload callback
        return rest_ensure_response([
            'success' => true,
            'file' => [
                'id' => $file_data['file_id'],
                'name' => $file_data['file_name'],
                'size' => $file_data['file_size'],
                'type' => $file_data['file_type']
            ]
        ]);
    }
    
    /**
     * Delete temp file
     */
    public function delete_temp_file($request) {
        $file_id = $request->get_param('id');
        
        if (empty($file_id)) {
            return new WP_Error('invalid_file_id', __('Invalid file ID.', 'codeweber'), ['status' => 400]);
        }
        
        $temp_files = new CodeweberFormsTempFiles();
        $result = $temp_files->delete_temp_file($file_id);
        
        if ($result) {
            return new WP_REST_Response(['success' => true], 200);
        } else {
            return new WP_Error('delete_failed', __('Failed to delete file.', 'codeweber'), ['status' => 500]);
        }
    }
    
    /**
     * Handle file uploads when form is submitted
     * Returns array of file info for processing
     */
    private function handle_file_uploads($request) {
        $file_ids = $request->get_param('file_ids');
        
        if (empty($file_ids) || !is_array($file_ids)) {
            return null;
        }
        
        $temp_files = new CodeweberFormsTempFiles();
        $files_data = [];
        
        // Collect file info from temp files
        foreach ($file_ids as $file_id) {
            $file = $temp_files->get_temp_file($file_id);
            if (!$file) {
                continue;
            }
            
            // Check if file hasn't expired
            if (strtotime($file->expires_at) < time()) {
                // File expired, skip it
                continue;
            }
            
            $files_data[] = [
                'file_id' => $file->file_id,
                'file_name' => $file->file_name,
                'file_size' => $file->file_size,
                'file_type' => $file->file_type,
                'temp_path' => $file->file_path
            ];
        }
        
        return !empty($files_data) ? $files_data : null;
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
            $sent = CodeweberFormsMailer::send($form_id, $form_settings, $recipient, $subject, $message, $files_data);
            
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
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Public method for async email sending (used by WP Cron)
     * 
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function send_email_async($form_id, $form_settings, $fields, $files_data, $submission_id, $type = 'admin') {
        return $this->send_email($form_id, $form_settings, $fields, $files_data, $submission_id, $type);
    }
    
    /**
     * Public method for async auto-reply sending (used by WP Cron)
     * 
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function send_auto_reply_async($form_id, $form_settings, $fields, $user_email, $form_type = 'auto_reply', $submission_id = 0) {
        return $this->send_auto_reply($form_id, $form_settings, $fields, $user_email, $form_type, $submission_id);
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


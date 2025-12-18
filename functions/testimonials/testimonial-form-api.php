<?php
/**
 * Testimonial Form API
 * 
 * REST API endpoint for submitting testimonials via AJAX
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

class TestimonialFormAPI {
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register REST API routes
     */
    public function register_routes() {
        // Endpoint for getting testimonial form HTML (for modal)
        register_rest_route('wp/v2', '/modal/add-testimonial', [
            'methods' => 'GET',
            'callback' => [$this, 'get_testimonial_form_html'],
            'permission_callback' => '__return_true'
        ]);
        
        // Endpoint for submitting testimonial
        register_rest_route('codeweber/v1', '/submit-testimonial', [
            'methods' => 'POST',
            'callback' => [$this, 'submit_testimonial'],
            'permission_callback' => '__return_true',
            'args' => [
                'message' => [
                    'required' => true,
                    'sanitize_callback' => 'wp_kses_post',
                    'validate_callback' => function($param) {
                        return !empty(trim(strip_tags($param)));
                    }
                ],
                'name' => [
                    'required' => false,
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'email' => [
                    'required' => false,
                    'sanitize_callback' => 'sanitize_email'
                ],
                'user_id' => [
                    'required' => false,
                    'sanitize_callback' => 'absint'
                ],
                'role' => [
                    'required' => false,
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'company' => [
                    'required' => false,
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'rating' => [
                    'required' => true,
                    'sanitize_callback' => 'absint',
                    'validate_callback' => function($param) {
                        return $param >= 1 && $param <= 5;
                    }
                ],
                'nonce' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'honeypot' => [
                    'required' => false,
                    'sanitize_callback' => 'sanitize_text_field'
                ]
            ]
        ]);
    }

    /**
     * Get testimonial form HTML for modal
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_testimonial_form_html($request) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[Testimonial Form API] get_testimonial_form_html called');
        }
        
        // Check if user is logged in
        // Get user_id from request parameter (passed from JS)
        $request_user_id = $request->get_param('user_id');
        $is_logged_in = false;
        $current_user = null;
        
        if ($request_user_id) {
            $user_id = absint($request_user_id);
            // Verify user exists
            $user = get_userdata($user_id);
            if ($user && $user->ID > 0) {
                // User exists, consider them logged in
                // Note: In REST API context, we trust the user_id passed from client
                // because it's only used to show/hide form fields, not for security
                $is_logged_in = true;
                $current_user = $user;
            }
        }
        
        // If no user_id in request, try to get current user (works if cookies are passed)
        if (!$is_logged_in) {
            $current_user_id = get_current_user_id();
            if ($current_user_id > 0) {
                $is_logged_in = true;
                $current_user = wp_get_current_user();
            }
        }
        
        // Приоритет 1: Ищем CPT форму с типом testimonial
        $cpt_form = null;
        if (class_exists('CodeweberFormsCore')) {
            $cpt_form = CodeweberFormsCore::get_form_by_type('testimonial');
            if (defined('WP_DEBUG') && WP_DEBUG) {
                if ($cpt_form) {
                    error_log('[Testimonial Form API] Found CPT form, ID: ' . $cpt_form->ID . ', status: ' . $cpt_form->post_status);
                } else {
                    error_log('[Testimonial Form API] No CPT form found');
                }
            }
        }
        
        // Если найдена CPT форма - используем её
        if ($cpt_form) {
            if (class_exists('CodeweberFormsRenderer')) {
                $renderer = new CodeweberFormsRenderer();
                $form_html = $renderer->render($cpt_form->ID, $cpt_form);
                
                // Обертываем в структуру модального окна
                $wrapped_html = '<div class="testimonial-form-modal text-start">' . 
                    '<h5 class="modal-title mb-4">' . esc_html__('Leave Your Testimonial', 'codeweber') . '</h5>' . 
                    $form_html . 
                    '</div>';
                
                return new WP_REST_Response([
                    'content' => [
                        'rendered' => $wrapped_html
                    ],
                    'modal_size' => 'modal-lg'
                ], 200);
            }
        }
        
        // Приоритет 2: Используем default форму
        if (class_exists('CodeweberFormsDefaultForms')) {
            $default_forms = new CodeweberFormsDefaultForms();
            $current_user_id_for_form = $is_logged_in && $current_user ? $current_user->ID : 0;
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[Testimonial Form API] Getting default form, is_logged_in: ' . ($is_logged_in ? 'true' : 'false') . ', user_id: ' . $current_user_id_for_form);
            }
            
            try {
                $form_html = $default_forms->get_default_form_html('testimonial', $is_logged_in, $current_user_id_for_form);
                
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('[Testimonial Form API] Default form HTML length: ' . strlen($form_html));
                    if (empty($form_html)) {
                        error_log('[Testimonial Form API] Default form HTML is empty!');
                    }
                }
            } catch (Exception $e) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('[Testimonial Form API] Exception when getting default form: ' . $e->getMessage());
                }
                $form_html = '';
            }
            
            if (!empty($form_html)) {
                // Обертываем в структуру модального окна
                $wrapped_html = '<div class="testimonial-form-modal text-start">' . 
                    '<h5 class="modal-title mb-4">' . esc_html__('Leave Your Testimonial', 'codeweber') . '</h5>' . 
                    $form_html . 
                    '</div>';
                
                return new WP_REST_Response([
                    'content' => [
                        'rendered' => $wrapped_html
                    ],
                    'modal_size' => 'modal-lg'
                ], 200);
            } else {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('[Testimonial Form API] Default form HTML is empty');
                }
            }
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[Testimonial Form API] CodeweberFormsDefaultForms class not found');
            }
        }
        
        // Если ни CPT, ни default форма не найдены - возвращаем ошибку
        return new WP_Error(
            'form_not_found',
            __('Testimonial form not found.', 'codeweber'),
            ['status' => 404]
        );
    }

    /**
     * Submit testimonial
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function submit_testimonial($request) {
        // Check nonce - try both form nonce and REST API nonce
        $nonce = $request->get_param('nonce');
        $rest_nonce = $request->get_header('X-WP-Nonce');
        
        // Try form nonce first (submit_testimonial action)
        $nonce_valid = false;
        if (!empty($nonce)) {
            $nonce_valid = wp_verify_nonce($nonce, 'submit_testimonial');
        }
        
        // If form nonce failed, try REST API nonce (wp_rest action)
        if (!$nonce_valid && !empty($rest_nonce)) {
            $nonce_valid = wp_verify_nonce($rest_nonce, 'wp_rest');
        }
        
        if (!$nonce_valid) {
            return new \WP_Error(
                'invalid_nonce',
                __('Security check failed. Please refresh the page and try again.', 'codeweber'),
                ['status' => 403]
            );
        }

        // Honeypot check (spam protection)
        $honeypot = $request->get_param('honeypot');
        if (!empty($honeypot)) {
            // Bot detected, silently fail
            return new WP_REST_Response([
                'success' => true,
                'message' => __('Thank you for your submission.', 'codeweber')
            ], 200);
        }

        // Rate limiting check (prevent spam)
        // Get IP address for logging (needed for all submissions)
        $ip = $this->get_client_ip();
        
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
        
        // Debug: log request data
        error_log('Testimonial Submit - Request user_id (get_param): ' . $request->get_param('user_id'));
        error_log('Testimonial Submit - Request user_id (after processing): ' . $request_user_id);
        error_log('Testimonial Submit - JSON params: ' . print_r($request->get_json_params(), true));
        error_log('Testimonial Submit - All params: ' . print_r($request->get_params(), true));
        
        // Check if user is logged in (use request parameter or current user)
        $is_logged_in = false;
        $user_id = 0;
        
        if ($request_user_id > 0) {
            // Verify user exists
            $user = get_userdata($request_user_id);
            if ($user && $user->ID > 0) {
                $is_logged_in = true;
                $user_id = $request_user_id;
                error_log('Testimonial Submit - User authenticated via request parameter: ' . $user_id);
            } else {
                error_log('Testimonial Submit - User not found for user_id: ' . $request_user_id);
            }
        } else {
            // Fallback: check current user (works if cookies are passed)
            $current_user_id = get_current_user_id();
            if ($current_user_id > 0) {
                $is_logged_in = true;
                $user_id = $current_user_id;
                error_log('Testimonial Submit - User authenticated via get_current_user_id(): ' . $user_id);
            } else {
                error_log('Testimonial Submit - No user authenticated');
            }
        }
        
        // Rate limiting
        // Проверяем настройки - можно отключить через админку
        $forms_options = get_option('codeweber_forms_options', []);
        $rate_limit_enabled = isset($forms_options['rate_limit_enabled']) ? (bool) $forms_options['rate_limit_enabled'] : true;
        
        if (!$rate_limit_enabled) {
            // Rate limiting отключен в настройках
            $transient_key = null;
            $submissions = 0;
        } elseif ($is_logged_in && $user_id > 0) {
            // For logged-in users, skip rate limiting (trusted users)
            $transient_key = null;
            $submissions = 0;
        } else {
            // For guests, use IP address (stricter limit)
            $transient_key = 'testimonial_submit_' . md5($ip);
            $submissions = get_transient($transient_key);
            
            if ($submissions === false) {
                $submissions = 0;
            }
            
            // Limit: 3 submissions per hour per IP for guests
            if ($submissions >= 3) {
                return new \WP_Error(
                    'rate_limit_exceeded',
                    __('Too many submissions. Please try again later.', 'codeweber'),
                    ['status' => 429]
                );
            }
        }
        
        // Get and validate data
        // Используем стандартные имена полей: message, name, email
        $testimonial_text = $request->get_param('message');
        $rating = $request->get_param('rating');
        $utm_params = $request->get_param('utm_params') ?: [];
        $tracking_data = $request->get_param('tracking_data') ?: [];
        
        // Initialize variables
        $author_name = '';
        $author_email = '';
        $role = '';
        $company = '';
        $author_type = 'custom';
        
        // If user is logged in, use their data
        if ($is_logged_in && $user_id > 0) {
            $current_user = get_userdata($user_id);
            if ($current_user && $current_user->ID > 0) {
                // Use logged-in user's data
                $author_name = $current_user->display_name;
                $author_email = $current_user->user_email;
                $role = get_user_meta($user_id, 'position', true) ?: '';
                $company = get_user_meta($user_id, 'company', true) ?: '';
                // Use 'user' to match admin interface (not 'registered')
                $author_type = 'user';
                error_log('Testimonial Submit - Using registered user data: ' . $author_name . ' (' . $author_email . ')');
                error_log('Testimonial Submit - Author type: ' . $author_type . ', User ID: ' . $user_id);
            } else {
                // User not found, treat as guest
                $is_logged_in = false;
                error_log('Testimonial Submit - User data not found, treating as guest');
            }
        } else {
            error_log('Testimonial Submit - User not logged in, using guest data');
        }
        
        // If not logged in, get data from form (используем стандартные имена: name, email, role)
        if (!$is_logged_in) {
            $author_name = $request->get_param('name');
            $author_email = $request->get_param('email');
            $role = $request->get_param('role');
            $company = $request->get_param('company');
            $author_type = 'custom';
            
            // Validate required fields for guests
            if (empty($author_name) || empty($author_email)) {
                return new \WP_Error(
                    'missing_fields',
                    __('Name and email are required.', 'codeweber'),
                    ['status' => 400]
                );
            }
            
            if (!is_email($author_email)) {
                return new \WP_Error(
                    'invalid_email',
                    __('Please provide a valid email address.', 'codeweber'),
                    ['status' => 400]
                );
            }
            
            // Проверяем, существует ли пользователь с таким email
            $user_by_email = get_user_by('email', $author_email);
            if ($user_by_email && $user_by_email->ID > 0) {
                // Пользователь существует - используем данные из профиля и устанавливаем тип автора как 'user'
                $author_type = 'user';
                $user_id = $user_by_email->ID;
                $is_logged_in = true; // Помечаем как найденного пользователя
                
                // Используем данные из профиля пользователя
                $author_name = $user_by_email->display_name;
                $author_email = $user_by_email->user_email;
                $role = get_user_meta($user_id, 'position', true) ?: ($request->get_param('role') ?: '');
                $company = get_user_meta($user_id, 'company', true) ?: ($request->get_param('company') ?: '');
                
                error_log('Testimonial Submit - Found existing user by email: ' . $author_email . ' (ID: ' . $user_id . ')');
                error_log('Testimonial Submit - Using user profile data: name=' . $author_name . ', role=' . $role . ', company=' . $company);
                error_log('Testimonial Submit - Setting author_type to: user');
            }
        }
        
        // Validate consents
        // Получаем form_id из запроса (для CPT форм это числовой ID)
        $form_id_from_request = $request->get_param('form_id');
        $form_id_int = intval($form_id_from_request);
        $is_cpt_form = !empty($form_id_from_request) && is_numeric($form_id_from_request) && $form_id_int > 0; // form_id = 0 это default форма без согласий
        $is_default_form = ($form_id_int === 0);
        
        // Default формы (form_id = 0) не имеют согласий, пропускаем проверку
        if (!$is_default_form && function_exists('codeweber_forms_validate_consents')) {
            $consents_config = [];
            
            // НОВОЕ: Для CPT форм согласия извлекаются из блоков формы, а не из глобальных настроек
            if ($is_cpt_form && class_exists('CodeweberFormsCore')) {
                $consents_config = CodeweberFormsCore::extract_consents_from_blocks($form_id_int);
                error_log('Testimonial Submit - CPT form, extracted consents from blocks: ' . print_r($consents_config, true));
            } else {
                // LEGACY: Для встроенных форм (строковый ID) используем глобальные настройки
                $all_consents = get_option('builtin_form_consents', []);
                $consents_config = isset($all_consents['testimonial']) ? $all_consents['testimonial'] : [];
                error_log('Testimonial Submit - Legacy form, using builtin_form_consents: ' . print_r($consents_config, true));
            }
            
            // Извлекаем только обязательные согласия из конфигурации
            $required_consents = [];
            if (!empty($consents_config) && is_array($consents_config)) {
                foreach ($consents_config as $consent) {
                    if (!empty($consent['required']) && !empty($consent['document_id'])) {
                        $required_consents[] = intval($consent['document_id']);
                    }
                }
            }
            
            error_log('Testimonial Submit - Required consents (document IDs): ' . print_r($required_consents, true));
            
            // Проверяем согласия ТОЛЬКО если в форме есть обязательные согласия
            // Если обязательных согласий нет (массив пустой), пропускаем проверку
            if (!empty($required_consents)) {
                // Get submitted consents
                $submitted_consents = $request->get_param('testimonial_consents');
                if (!is_array($submitted_consents)) {
                    $submitted_consents = [];
                }
                
                error_log('Testimonial Submit - Submitted consents: ' . print_r($submitted_consents, true));
                
                // Validate
                $validation = codeweber_forms_validate_consents($submitted_consents, $required_consents);
                error_log('Testimonial Submit - Consent validation result: ' . print_r($validation, true));
                
                if (!$validation['valid']) {
                    return new \WP_Error(
                        'consent_required',
                        __('Please accept all required consents.', 'codeweber'),
                        ['status' => 400]
                    );
                }
            } else {
                error_log('Testimonial Submit - No required consents found in form, skipping consent validation');
            }
        } else if ($is_default_form) {
            error_log('Testimonial Submit - Default form (form_id=0), skipping consent validation');
        }

        // Обрабатываем testimonial_consents аналогично newsletter_consents для универсальной обработки
        $testimonial_consents_for_save = null;
        $submitted_testimonial_consents = $request->get_param('testimonial_consents');
        if (!empty($submitted_testimonial_consents) && is_array($submitted_testimonial_consents)) {
            error_log('Testimonial Submit - Processing testimonial_consents: ' . print_r($submitted_testimonial_consents, true));
            
            // Сохраняем версии документов на момент подписки
            $consents_with_versions = [];
            foreach ($submitted_testimonial_consents as $doc_id => $value) {
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
                            'document_version' => $doc->post_modified,
                            'document_version_timestamp' => strtotime($doc->post_modified),
                        ];
                        error_log('Testimonial Submit - Added consent for doc_id: ' . $doc_id . ' (version: ' . $doc->post_modified . ')');
                    } else {
                        $consents_with_versions[$doc_id] = $value;
                        error_log('Testimonial Submit - WARNING: Document not found for doc_id: ' . $doc_id);
                    }
                }
            }
            if (!empty($consents_with_versions)) {
                $testimonial_consents_for_save = $consents_with_versions;
                error_log('Testimonial Submit - Final testimonial_consents_for_save: ' . print_r($testimonial_consents_for_save, true));
            }
        }

        // Create testimonial post
        $post_data = [
            'post_title' => sprintf(__('Testimonial from %s', 'codeweber'), $author_name),
            'post_content' => '',
            'post_status' => 'pending', // Requires approval
            'post_type' => 'testimonials',
            'meta_input' => [
                '_testimonial_text' => wp_kses_post($testimonial_text),
                '_testimonial_author_type' => $author_type,
                '_testimonial_author_name' => sanitize_text_field($author_name),
                '_testimonial_author_role' => sanitize_text_field($role),
                '_testimonial_company' => sanitize_text_field($company),
                '_testimonial_rating' => absint($rating),
                '_testimonial_status' => 'pending',
                '_testimonial_submitted_email' => sanitize_email($author_email),
                '_testimonial_submitted_ip' => $ip,
                '_testimonial_submitted_date' => current_time('mysql')
            ]
        ];
        
        // If user is logged in, link to user
        if ($is_logged_in && $user_id > 0) {
            // Use the same meta key as in admin (for consistency)
            $post_data['meta_input']['_testimonial_author_user_id'] = absint($user_id);
            error_log('Testimonial Submit - Setting _testimonial_author_user_id: ' . $user_id);
        } else {
            error_log('Testimonial Submit - NOT setting _testimonial_author_user_id (is_logged_in: ' . ($is_logged_in ? 'true' : 'false') . ', user_id: ' . $user_id . ')');
        }
        
        error_log('Testimonial Submit - Final post_data meta_input: ' . print_r($post_data['meta_input'], true));

        $post_id = wp_insert_post($post_data, true);

        if (is_wp_error($post_id)) {
            return new \WP_Error(
                'post_creation_failed',
                __('Failed to create testimonial. Please try again.', 'codeweber'),
                ['status' => 500]
            );
        }
        
        // Debug: verify meta fields were saved
        error_log('Testimonial Submit - Post created with ID: ' . $post_id);
        error_log('Testimonial Submit - _testimonial_author_type: ' . get_post_meta($post_id, '_testimonial_author_type', true));
        error_log('Testimonial Submit - _testimonial_author_user_id: ' . get_post_meta($post_id, '_testimonial_author_user_id', true));
        error_log('Testimonial Submit - _testimonial_author_name: ' . get_post_meta($post_id, '_testimonial_author_name', true));
        
        // Double-check: if user is logged in or found by email, ensure meta fields are set correctly
        if ($is_logged_in && $user_id > 0) {
            // Force update meta fields to ensure they're saved
            // Use 'user' instead of 'registered' to match admin interface
            update_post_meta($post_id, '_testimonial_author_type', 'user');
            update_post_meta($post_id, '_testimonial_author_user_id', absint($user_id));
            error_log('Testimonial Submit - Force updated meta fields for user (ID: ' . $user_id . ')');
        }

        // Собираем UTM метки и tracking данные
        $utm_tracker = new CodeweberFormsUTM();
        $utm_data = array_merge(
            $utm_tracker->get_utm_params(),
            $utm_params,
            $utm_tracker->get_tracking_data(),
            $tracking_data
        );
        
        // Prepare submission data (используем стандартные имена)
        $submission_fields = [
            'name' => $author_name,
            'email' => $author_email,
            'role' => $role,
            'company' => $company,
            'message' => $testimonial_text,
            'rating' => $rating,
        ];
        
        // Добавляем согласия в формате newsletter_consents для универсальной обработки
        if ($testimonial_consents_for_save !== null && !empty($testimonial_consents_for_save)) {
            $submission_fields['newsletter_consents'] = $testimonial_consents_for_save;
            error_log('Testimonial Submit - Added newsletter_consents to submission_fields');
        }
        
        // Добавляем UTM данные в поля формы для сохранения и отображения
        if (!empty($utm_data)) {
            $submission_fields['_utm_data'] = $utm_data;
        }
        
        // Get user agent
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        
        // Хук перед отправкой (если класс доступен)
        if (class_exists('CodeweberFormsHooks')) {
            $form_settings = [
                'formTitle' => __('Testimonial Form', 'codeweber'),
                'recipientEmail' => get_option('admin_email'),
            ];
            CodeweberFormsHooks::before_send('testimonial', $form_settings, $submission_fields);
        }
        
        // Save to forms submissions table
        $submission_id = null;
        if (class_exists('CodeweberFormsDatabase')) {
            $db = new CodeweberFormsDatabase();
            
            $submission_id = $db->save_submission([
                'form_id' => 'testimonial', // Используем строковый ключ для встроенной формы
                'form_name' => __('Testimonial Form', 'codeweber'),
                'submission_data' => $submission_fields,
                'files_data' => null,
                'ip_address' => $ip,
                'user_agent' => $user_agent,
                'user_id' => $is_logged_in ? $user_id : 0,
                'status' => 'new',
                'email_sent' => 0,
                'email_error' => null,
            ]);
            
            if ($submission_id) {
                error_log('Testimonial Submit - Saved to forms submissions table with ID: ' . $submission_id);
                
                // Хук после сохранения (передаем 'testimonial' вместо 0)
                if (class_exists('CodeweberFormsHooks')) {
                    CodeweberFormsHooks::after_saved($submission_id, 'testimonial', $submission_fields);
                }
            } else {
                error_log('Testimonial Submit - Failed to save to forms submissions table');
                
                // Хук при ошибке сохранения
                if (class_exists('CodeweberFormsHooks')) {
                    CodeweberFormsHooks::send_error('testimonial', $form_settings ?? [], __('Failed to save submission.', 'codeweber-forms'));
                }
            }
        }
        
        // Хук после успешной отправки
        if (class_exists('CodeweberFormsHooks') && $submission_id) {
            $form_settings = [
                'formTitle' => __('Testimonial Form', 'codeweber'),
                'recipientEmail' => get_option('admin_email'),
            ];
            CodeweberFormsHooks::after_send('testimonial', $form_settings, $submission_id);
        }

        // Update rate limiting (только если включен и для гостей)
        if ($transient_key !== null) {
            set_transient($transient_key, $submissions + 1, HOUR_IN_SECONDS);
        }

        // Send notification email to admin using forms module templates
        $this->send_admin_notification_via_forms_module($submission_id, $submission_fields, $post_id, $author_name, $author_email, $ip, $user_agent);

        // Send auto-reply email to user using forms module templates
        $auto_reply_result = $this->send_testimonial_auto_reply($submission_id, $submission_fields, $author_name, $author_email, $ip, $user_agent);
        
        // Обновляем статус отправки автоответа
        if ($submission_id && class_exists('CodeweberFormsDatabase')) {
            $db = new CodeweberFormsDatabase();
            $db->update_submission($submission_id, [
                'auto_reply_sent' => $auto_reply_result['success'] ? 1 : 0,
                'auto_reply_error' => $auto_reply_result['error'] ?? null,
            ]);
        }

        return new WP_REST_Response([
            'success' => true,
            'message' => __('Thank you for your testimonial! It will be reviewed and published soon.', 'codeweber'),
            'data' => [
                'post_id' => $post_id
            ]
        ], 200);
    }

    /**
     * Get client IP address
     * 
     * @return string
     */
    private function get_client_ip() {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    }

    /**
     * Send notification email to admin using forms module templates
     * 
     * @param int $submission_id
     * @param array $submission_fields
     * @param int $post_id
     * @param string $author_name
     * @param string $author_email
     * @param string $ip
     * @param string $user_agent
     */
    private function send_admin_notification_via_forms_module($submission_id, $submission_fields, $post_id, $author_name, $author_email, $ip, $user_agent) {
        // Используем систему шаблонов модуля форм, если доступна
        if (class_exists('CodeweberFormsMailer') && class_exists('CodeweberFormsEmailTemplates')) {
            $email_templates = get_option('codeweber_forms_email_templates', []);
            $admin_notification_enabled = isset($email_templates['admin_notification_enabled']) 
                ? $email_templates['admin_notification_enabled'] 
                : true;
            
            if (!$admin_notification_enabled) {
                return; // Уведомления отключены
            }
            
            $admin_email = get_option('admin_email');
            if (!$admin_email) {
                return;
            }
            
            // Получаем тему письма
            $subject = isset($email_templates['admin_notification_subject']) && !empty($email_templates['admin_notification_subject'])
                ? $email_templates['admin_notification_subject']
                : __('New Testimonial Submission', 'codeweber-forms');
            
            // Обработка переменных в теме
            $subject = str_replace(
                ['{form_name}', '{user_name}', '{site_name}'],
                [
                    __('Testimonial Form', 'codeweber'),
                    $author_name,
                    get_bloginfo('name')
                ],
                $subject
            );
            
            // Получаем шаблон
            $template = '';
            if (isset($email_templates['admin_notification_template']) && !empty($email_templates['admin_notification_template'])) {
                $template = $email_templates['admin_notification_template'];
            } else {
                // Используем дефолтный шаблон из класса
                $templates_class = new CodeweberFormsEmailTemplates();
                $template = $templates_class->get_default_admin_template();
            }
            
            // Добавляем ссылку на редактирование отзыва в админке
            $submission_fields['edit_testimonial'] = admin_url('post.php?post=' . intval($post_id) . '&action=edit');
            
            // Вычисляем, на какой странице архива находится отзыв (9 отзывов на страницу)
            // Учитываем, что отзыв может быть pending, поэтому ищем среди всех отзывов
            $posts_per_page = 9;
            $args = [
                'post_type' => 'testimonials',
                'post_status' => ['publish', 'pending', 'draft'],
                'posts_per_page' => -1,
                'meta_query' => [
                    'relation' => 'OR',
                    [
                        'key' => '_testimonial_status',
                        'value' => 'approved',
                        'compare' => '='
                    ],
                    [
                        'key' => '_testimonial_status',
                        'compare' => 'NOT EXISTS'
                    ],
                    [
                        'key' => '_testimonial_status',
                        'value' => 'pending',
                        'compare' => '='
                    ]
                ],
                'orderby' => 'date',
                'order' => 'DESC',
                'fields' => 'ids'
            ];
            $all_testimonials = get_posts($args);
            $post_position = array_search($post_id, $all_testimonials);
            
            if ($post_position !== false) {
                $page_number = floor($post_position / $posts_per_page) + 1;
                $archive_url = get_post_type_archive_link('testimonials');
                // Убираем trailing slash из archive_url, если есть, чтобы избежать двойного слеша
                $archive_url = rtrim($archive_url, '/');
                $submission_fields['view_testimonial'] = $archive_url . '/page/' . $page_number . '/';
            } else {
                // Если отзыв не найден, просто ссылка на первую страницу архива
                $archive_url = get_post_type_archive_link('testimonials');
                $archive_url = rtrim($archive_url, '/');
                $submission_fields['view_testimonial'] = $archive_url . '/page/1/';
            }
            $submission_fields['testimonial_id'] = $post_id;
            
            // Подготовка данных для шаблона
            $template_data = [
                'form_name' => __('Testimonial Form', 'codeweber'),
                'fields' => $submission_fields,
                'user_name' => $author_name,
                'user_email' => $author_email,
                'submission_date' => date_i18n(get_option('date_format'), current_time('timestamp')),
                'submission_time' => date('H:i', current_time('timestamp')),
                'ip_address' => $ip,
                'user_agent' => $user_agent,
            ];
            
            // Обработка шаблона
            $message = CodeweberFormsMailer::process_template($template, $template_data);
            
            // Отправка через модуль форм
            $form_settings = [
                'formTitle' => __('Testimonial Form', 'codeweber'),
                'recipientEmail' => $admin_email,
            ];
            
            $email_sent = CodeweberFormsMailer::send(0, $form_settings, $admin_email, $subject, $message);
            $email_error = $email_sent ? null : __('Email sending failed.', 'codeweber-forms');
            
            // Обновляем статус отправки email в базе данных
            if ($submission_id && class_exists('CodeweberFormsDatabase')) {
                $db = new CodeweberFormsDatabase();
                $db->update_submission($submission_id, [
                    'email_sent' => $email_sent ? 1 : 0,
                    'email_error' => $email_error,
                ]);
            }
        } else {
            // Fallback: простая отправка, если модуль форм недоступен
            $admin_email = get_option('admin_email');
            if (!$admin_email) {
                return;
            }

            $subject = sprintf(__('New Testimonial Submission from %s', 'codeweber'), $author_name);
            $message = sprintf(
                __("A new testimonial has been submitted and is pending review.\n\nAuthor: %s\nEmail: %s\n\nView: %s", 'codeweber'),
                $author_name,
                $author_email,
                admin_url('post.php?post=' . $post_id . '&action=edit')
            );

            $email_sent = wp_mail($admin_email, $subject, $message);
            $email_error = $email_sent ? null : __('Email sending failed.', 'codeweber-forms');
            
            // Обновляем статус отправки email в базе данных
            if ($submission_id && class_exists('CodeweberFormsDatabase')) {
                $db = new CodeweberFormsDatabase();
                $db->update_submission($submission_id, [
                    'email_sent' => $email_sent ? 1 : 0,
                    'email_error' => $email_error,
                ]);
            }
        }
    }

    /**
     * Send auto-reply email to user using forms module templates
     * 
     * @param int $submission_id
     * @param array $submission_fields
     * @param string $author_name
     * @param string $author_email
     * @param string $ip
     * @param string $user_agent
     * @return array ['success' => bool, 'error' => string|null]
     */
    private function send_testimonial_auto_reply($submission_id, $submission_fields, $author_name, $author_email, $ip, $user_agent) {
        error_log('Testimonial Form - send_testimonial_auto_reply called');
        error_log('Testimonial Form - author_email: ' . $author_email);
        
        // Проверяем наличие классов
        $mailer_exists = class_exists('CodeweberFormsMailer');
        $templates_exists = class_exists('CodeweberFormsEmailTemplates');
        error_log('Testimonial Form - CodeweberFormsMailer exists: ' . ($mailer_exists ? 'yes' : 'no'));
        error_log('Testimonial Form - CodeweberFormsEmailTemplates exists: ' . ($templates_exists ? 'yes' : 'no'));
        
        // Используем систему шаблонов модуля форм, если доступна
        if ($mailer_exists && $templates_exists) {
            error_log('Testimonial Form - Classes exist, checking templates');
            
            $email_templates = get_option('codeweber_forms_email_templates', []);
            $testimonial_reply_enabled = isset($email_templates['testimonial_reply_enabled']) 
                ? $email_templates['testimonial_reply_enabled'] 
                : true;
            
            error_log('Testimonial Form - testimonial_reply_enabled: ' . ($testimonial_reply_enabled ? 'true' : 'false'));
            
            if (!$testimonial_reply_enabled) {
                error_log('Testimonial Form - Auto-reply is disabled in settings');
                return ['success' => false, 'error' => __('Auto-reply is disabled.', 'codeweber-forms')];
            }
            
            if (empty($author_email) || !is_email($author_email)) {
                error_log('Testimonial Form - Invalid email address: ' . $author_email);
                return ['success' => false, 'error' => __('Invalid email address.', 'codeweber-forms')];
            }
            
            error_log('Testimonial Form - Starting auto-reply email preparation');
            
            // Получаем тему письма
            $subject = isset($email_templates['testimonial_reply_subject']) && !empty($email_templates['testimonial_reply_subject'])
                ? $email_templates['testimonial_reply_subject']
                : __('Thank you for your testimonial', 'codeweber-forms');
            
            // Обработка переменных в теме
            $subject = str_replace(
                ['{form_name}', '{user_name}', '{site_name}'],
                [
                    __('Testimonial Form', 'codeweber'),
                    $author_name,
                    get_bloginfo('name')
                ],
                $subject
            );
            
            // Получаем шаблон
            $template = '';
            if (isset($email_templates['testimonial_reply_template']) && !empty($email_templates['testimonial_reply_template'])) {
                $template = $email_templates['testimonial_reply_template'];
            } else {
                // Используем дефолтный шаблон из класса
                $templates_class = new CodeweberFormsEmailTemplates();
                $template = $templates_class->get_default_testimonial_reply_template();
            }
            
            // Подготовка данных для шаблона
            $template_data = [
                'form_name' => __('Testimonial Form', 'codeweber'),
                'fields' => $submission_fields,
                'user_name' => $author_name,
                'user_email' => $author_email,
                'submission_date' => date_i18n(get_option('date_format'), current_time('timestamp')),
                'submission_time' => date('H:i', current_time('timestamp')),
                'ip_address' => $ip,
                'user_agent' => $user_agent,
            ];
            
            // Обработка шаблона
            $message = CodeweberFormsMailer::process_template($template, $template_data);
            
            // Отправка через модуль форм
            $form_settings = [
                'formTitle' => __('Testimonial Form', 'codeweber'),
                'recipientEmail' => $author_email,
            ];
            
            $sent = CodeweberFormsMailer::send(0, $form_settings, $author_email, $subject, $message);
            $error = $sent ? null : __('Email sending failed.', 'codeweber-forms');
            
            if (!$sent) {
                error_log('Testimonial Form - Failed to send auto-reply email to: ' . $author_email);
                error_log('Testimonial Form - Subject: ' . $subject);
                error_log('Testimonial Form - Template length: ' . strlen($template));
                error_log('Testimonial Form - Message length: ' . strlen($message));
            } else {
                error_log('Testimonial Form - Auto-reply email sent successfully to: ' . $author_email);
            }
            
            return [
                'success' => $sent,
                'error' => $error,
            ];
        } else {
            error_log('Testimonial Form - CodeweberFormsMailer or CodeweberFormsEmailTemplates class not found for auto-reply.');
            return [
                'success' => false,
                'error' => __('Email templates module not available.', 'codeweber-forms'),
            ];
        }
    }
}

// Initialize
new TestimonialFormAPI();


<?php
/**
 * Universal Matomo Forms Integration Module
 * Works with both Codeweber Forms and CF7 forms
 * Tracks: Form Opened, Form Submission, Form Errors
 * 
 * Domain: codeweber-forms
 */

if (!defined('ABSPATH')) {
    exit;
}

// Проверяем установлен ли плагин Matomo
function codeweber_forms_matomo_is_plugin_active() {
    return function_exists('is_plugin_active') && is_plugin_active('matomo/matomo.php');
}

// Добавляем меню в админку только если Matomo установлен
add_action('admin_menu', 'codeweber_forms_matomo_integration_admin_menu', 20);
function codeweber_forms_matomo_integration_admin_menu() {
    if (!codeweber_forms_matomo_is_plugin_active()) {
        return;
    }

    // Добавляем под меню "Codeweber" (страница со списком отправок)
    add_submenu_page(
        'codeweber',
        __('Matomo Integration', 'codeweber'),
        __('Matomo Integration', 'codeweber'),
        'manage_options',
        'codeweber-forms-matomo-integration',
        'codeweber_forms_matomo_integration_page'
    );
}

// Жестко прописанные настройки
define('CODEWEBER_FORMS_MATOMO_SITE_ID', 1);

// Функция: Получение visitor_id из cookies Matomo
function codeweber_forms_matomo_get_consistent_visitor_id() {
    foreach ($_COOKIE as $name => $value) {
        if (strpos($name, '_pk_id_') === 0) {
            $parts = explode('.', $value);
            if (!empty($parts[0]) && strlen($parts[0]) === 16) {
                return $parts[0];
            }
        }
    }

    $user_ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return substr(md5($user_ip . $user_agent), 0, 16);
}

// Функция: Определение типа формы (CF7 или Codeweber)
function codeweber_forms_matomo_get_form_type($form_id, $form_settings = []) {
    // Проверяем по form_settings (для CF7 там будет специальный ключ)
    if (isset($form_settings['form_type']) && $form_settings['form_type'] === 'cf7') {
        return 'cf7';
    }
    
    // Проверяем по form_id - если это число и есть пост типа wpcf7_contact_form
    if (is_numeric($form_id) && $form_id > 0) {
        $post = get_post((int) $form_id);
        if ($post) {
            if ($post->post_type === 'wpcf7_contact_form') {
                return 'cf7';
            } elseif ($post->post_type === 'codeweber_form') {
                return 'codeweber';
            }
        }
    }
    
    // Проверяем класс CodeweberFormsCore для определения типа
    if (class_exists('CodeweberFormsCore')) {
        $form_type = CodeweberFormsCore::get_form_type($form_id);
        if ($form_type === 'cf7') {
            return 'cf7';
        }
    }
    
    // По умолчанию считаем Codeweber формой
    return 'codeweber';
}

// Функция: Получение названия формы
function codeweber_forms_matomo_get_form_name($form_id, $form_settings = []) {
    // Приоритет 1: Из настроек формы
    if (!empty($form_settings['formTitle'])) {
        return $form_settings['formTitle'];
    }
    
    // Приоритет 2: Из поста
    if (is_numeric($form_id) && $form_id > 0) {
        $post = get_post((int) $form_id);
        if ($post && !empty($post->post_title)) {
            return $post->post_title;
        }
    }
    
    // Приоритет 3: Из form_id если это строка (например, 'newsletter', 'testimonial')
    if (!is_numeric($form_id) && !empty($form_id)) {
        return ucfirst($form_id);
    }
    
    return __('Form', 'codeweber');
}

// Функция: Перевод действия для Matomo
function codeweber_forms_matomo_translate_action($action) {
    $translations = [
        'Form Submission' => __('Form Submission', 'codeweber'),
        'Form Opened' => __('Form Opened', 'codeweber'),
        'Form Error' => __('Form Error', 'codeweber'),
        'Validation Error' => __('Validation Error', 'codeweber'),
        'Server Error' => __('Server Error', 'codeweber'),
        'Spam Detected' => __('Spam Detected', 'codeweber'),
    ];
    return $translations[$action] ?? $action;
}

// Функция: Отправка события отправки формы в Matomo
function codeweber_forms_matomo_track_form_event($form_id, $form_name, $form_type, $action = 'Form Submission', $value = 1, $current_url = '', $additional_data = []) {
    if (!get_option('codeweber_forms_matomo_track_forms', 1) || !codeweber_forms_matomo_is_plugin_active()) {
        return false;
    }

    $visitor_id = codeweber_forms_matomo_get_consistent_visitor_id();
    if (empty($visitor_id)) {
        return false;
    }

    // Если URL не передан, используем текущий URL
    if (empty($current_url)) {
        $current_url = home_url($_SERVER['REQUEST_URI'] ?? '');
    }

    // Translate action name for Matomo
    $translated_action = codeweber_forms_matomo_translate_action($action);
    
    // Определяем категорию события в зависимости от типа формы
    $category = $form_type === 'cf7' 
        ? __('Contact Form 7', 'codeweber') 
        : __('Codeweber Forms', 'codeweber');
    
    // Формируем название события с типом формы
    $event_name = $form_name . ' (ID: ' . $form_id . ')';
    if ($form_type === 'cf7') {
        $event_name .= ' [CF7]';
    }
    
    $params = [
        'idsite' => CODEWEBER_FORMS_MATOMO_SITE_ID,
        'rec' => 1,
        'ua' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        '_id' => $visitor_id,
        'e_c' => $category,
        'e_a' => $translated_action,
        'e_n' => $event_name,
        'e_v' => $value,
        'url' => $current_url,
        'urlref' => $_SERVER['HTTP_REFERER'] ?? home_url(),
        'send_image' => 0,
    ];

    $response = wp_remote_post(
        home_url('/wp-json/matomo/v1/hit/'),
        [
            'timeout' => 2, // Максимум 2 секунды
            'blocking' => false, // Не ждать ответа (асинхронно)
            'sslverify' => false,
            'body' => $params,
        ]
    );

    if (is_wp_error($response)) {
        if (get_option('codeweber_forms_matomo_debug_mode', 0)) {
        }
        return false;
    }

    $response_code = wp_remote_retrieve_response_code($response);
    
    if (get_option('codeweber_forms_matomo_debug_mode', 0)) {
    }
    
    return in_array($response_code, [200, 204]);
}

// Хуки для отслеживания отправок форм (работает для обеих систем)
if (codeweber_forms_matomo_is_plugin_active()) {
    // ========== 1. ОТСЛЕЖИВАНИЕ ОТКРЫТИЯ ФОРМЫ ==========
    
    // Для Codeweber Forms: через хук codeweber_form_opened
    add_action('codeweber_form_opened', 'codeweber_forms_matomo_track_form_opened', 10, 1);
    function codeweber_forms_matomo_track_form_opened($form_id) {
        $form_type = codeweber_forms_matomo_get_form_type($form_id);
        $form_name = codeweber_forms_matomo_get_form_name($form_id);
        $current_url = home_url($_SERVER['REQUEST_URI'] ?? '');
        codeweber_forms_matomo_track_form_event($form_id, $form_name, $form_type, 'Form Opened', 1, $current_url, []);
    }
    
    // Для CF7: через REST API endpoint (вызывается из JavaScript)
    add_action('rest_api_init', function() {
        register_rest_route('codeweber-forms/v1', '/cf7-form-opened', [
            'methods' => 'POST',
            'callback' => 'codeweber_forms_matomo_track_cf7_form_opened',
            'permission_callback' => '__return_true'
        ]);
    });
    
    function codeweber_forms_matomo_track_cf7_form_opened($request) {
        $form_id = $request->get_param('form_id');
        if (empty($form_id)) {
            return new WP_Error('missing_form_id', __('Form ID is required', 'codeweber'), ['status' => 400]);
        }
        
        $form_type = 'cf7';
        $form_name = codeweber_forms_matomo_get_form_name($form_id);
        $current_url = $request->get_param('url') ?? home_url($_SERVER['REQUEST_URI'] ?? '');
        
        codeweber_forms_matomo_track_form_event($form_id, $form_name, $form_type, 'Form Opened', 1, $current_url, []);
        
        return new WP_REST_Response(['success' => true], 200);
    }
    
    // ========== 2. ОТСЛЕЖИВАНИЕ УСПЕШНОЙ ОТПРАВКИ ==========
    
    // Работает для обеих систем через универсальный хук
    add_action('codeweber_form_after_send', 'codeweber_forms_matomo_track_submission', 10, 3);
    function codeweber_forms_matomo_track_submission($form_id, $form_settings, $submission_id) {
        $form_type = codeweber_forms_matomo_get_form_type($form_id, $form_settings);
        $form_name = codeweber_forms_matomo_get_form_name($form_id, $form_settings);
        $current_url = $_SERVER['HTTP_REFERER'] ?? home_url();
        
        codeweber_forms_matomo_track_form_event(
            $form_id, 
            $form_name, 
            $form_type, 
            'Form Submission', 
            1, 
            $current_url
        );
    }
    
    // ========== 3. ОТСЛЕЖИВАНИЕ ОШИБОК ==========
    
    // Для Codeweber Forms: через хук codeweber_form_send_error
    add_action('codeweber_form_send_error', 'codeweber_forms_matomo_track_error', 10, 3);
    function codeweber_forms_matomo_track_error($form_id, $form_settings, $error_message) {
        $form_type = codeweber_forms_matomo_get_form_type($form_id, $form_settings);
        $form_name = codeweber_forms_matomo_get_form_name($form_id, $form_settings);
        $current_url = $_SERVER['HTTP_REFERER'] ?? home_url();
        codeweber_forms_matomo_track_form_event($form_id, $form_name, $form_type, 'Form Error', 0, $current_url, []);
    }
    
    // Для CF7: через события CF7 (обрабатываем через JavaScript + REST API)
    // Подключаем JavaScript для отслеживания CF7 форм
    if (class_exists('WPCF7')) {
        add_action('wp_footer', 'codeweber_forms_matomo_add_cf7_tracking_script', 999);
    }
}

// JavaScript для отслеживания CF7 форм
function codeweber_forms_matomo_add_cf7_tracking_script() {
    if (is_admin() || !codeweber_forms_matomo_is_plugin_active() || !class_exists('WPCF7')) {
        return;
    }
    
    $rest_url = rest_url('codeweber-forms/v1/cf7-form-opened');
    $rest_error_url = rest_url('codeweber-forms/v1/cf7-form-error');
    $rest_nonce = wp_create_nonce('wp_rest');
    $cf7_title_url = rest_url('custom/v1/cf7-title/');
    ?>
    <script type="text/javascript">
    (function() {
        'use strict';
        
        // Функция для получения названия CF7 формы
        async function getCF7FormTitle(formId) {
            if (!formId) return '<?php echo esc_js(__('No Name Form', 'codeweber')); ?>';
            try {
                const response = await fetch('<?php echo esc_url($cf7_title_url); ?>' + formId);
                if (response.ok) {
                    const data = await response.json();
                    return data.title || '<?php echo esc_js(__('No Name Form', 'codeweber')); ?>';
                }
            } catch (error) {
                console.error('Error fetching CF7 form title:', error);
            }
            return '<?php echo esc_js(__('No Name Form', 'codeweber')); ?>';
        }
        
        // Отслеживание открытия CF7 формы
        function trackCF7FormOpened(form) {
            const formIdInput = form.querySelector('input[name="_wpcf7"]');
            if (!formIdInput || form.dataset.matomoOpened) {
                return; // Уже отслежено
            }
            
            const formId = formIdInput.value;
            if (!formId) return;
            
            form.dataset.matomoOpened = 'true';
            
            // Отправляем событие через REST API
            fetch('<?php echo esc_url($rest_url); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': '<?php echo esc_js($rest_nonce); ?>'
                },
                body: JSON.stringify({
                    form_id: formId,
                    url: window.location.href
                })
            }).catch(function(error) {
                console.error('Error tracking CF7 form opened:', error);
            });
        }
        
        // Инициализация при загрузке DOM
        function initCF7Tracking() {
            // Отслеживаем открытие форм при загрузке страницы
            document.querySelectorAll('form.wpcf7-form').forEach(function(form) {
                trackCF7FormOpened(form);
            });
            
            // Отслеживаем формы, загруженные динамически (например, в модальных окнах)
            if (typeof MutationObserver !== 'undefined') {
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === 1) {
                                const forms = node.querySelectorAll ? node.querySelectorAll('form.wpcf7-form') : [];
                                forms.forEach(function(form) {
                                    trackCF7FormOpened(form);
                                });
                                if (node.tagName === 'FORM' && node.classList.contains('wpcf7-form')) {
                                    trackCF7FormOpened(node);
                                }
                            }
                        });
                    });
                });
                
                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
            }
            
            // ========== ОТСЛЕЖИВАНИЕ ОШИБОК CF7 ==========
            
            // Ошибки валидации
            document.addEventListener('wpcf7invalid', async function(event) {
                const formId = event.detail.contactFormId;
                if (!formId) return;
                
                const formName = await getCF7FormTitle(formId);
                const errorsCount = event.detail.apiResponse?.invalid_fields?.length || 1;
                
                // Отправляем событие об ошибке через REST API
                fetch('<?php echo esc_url($rest_error_url); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': '<?php echo esc_js($rest_nonce); ?>'
                    },
                    body: JSON.stringify({
                        form_id: formId,
                        form_name: formName,
                        error_type: 'validation',
                        error_count: errorsCount,
                        url: window.location.href
                    })
                }).catch(function(error) {
                    console.error('Error tracking CF7 validation error:', error);
                });
            });
            
            // Ошибка сервера
            document.addEventListener('wpcf7mailfailed', async function(event) {
                const formId = event.detail.contactFormId;
                if (!formId) return;
                
                const formName = await getCF7FormTitle(formId);
                
                fetch('<?php echo esc_url($rest_error_url); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': '<?php echo esc_js($rest_nonce); ?>'
                    },
                    body: JSON.stringify({
                        form_id: formId,
                        form_name: formName,
                        error_type: 'server',
                        url: window.location.href
                    })
                }).catch(function(error) {
                    console.error('Error tracking CF7 server error:', error);
                });
            });
            
            // Спам
            document.addEventListener('wpcf7spam', async function(event) {
                const formId = event.detail.contactFormId;
                if (!formId) return;
                
                const formName = await getCF7FormTitle(formId);
                
                fetch('<?php echo esc_url($rest_error_url); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': '<?php echo esc_js($rest_nonce); ?>'
                    },
                    body: JSON.stringify({
                        form_id: formId,
                        form_name: formName,
                        error_type: 'spam',
                        url: window.location.href
                    })
                }).catch(function(error) {
                    console.error('Error tracking CF7 spam:', error);
                });
            });
        }
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCF7Tracking);
        } else {
            initCF7Tracking();
        }
    })();
    </script>
    <?php
}

// REST API endpoint для отслеживания ошибок CF7
if (codeweber_forms_matomo_is_plugin_active() && class_exists('WPCF7')) {
    add_action('rest_api_init', function() {
        register_rest_route('codeweber-forms/v1', '/cf7-form-error', [
            'methods' => 'POST',
            'callback' => 'codeweber_forms_matomo_track_cf7_form_error',
            'permission_callback' => '__return_true'
        ]);
    });
}

function codeweber_forms_matomo_track_cf7_form_error($request) {
    $form_id = $request->get_param('form_id');
    $form_name = $request->get_param('form_name') ?? codeweber_forms_matomo_get_form_name($form_id);
    $error_type = $request->get_param('error_type') ?? 'error';
    $current_url = $request->get_param('url') ?? home_url($_SERVER['REQUEST_URI'] ?? '');
    
    // Определяем действие в зависимости от типа ошибки
    $action_map = [
        'validation' => 'Validation Error',
        'server' => 'Server Error',
        'spam' => 'Spam Detected',
    ];
    $action = $action_map[$error_type] ?? 'Form Error';
    
    codeweber_forms_matomo_track_form_event(
        $form_id, 
        $form_name, 
        'cf7', 
        $action, 
        0, 
        $current_url,
        []
    );
    
    return new WP_REST_Response(['success' => true], 200);
}

// Страница настройки интеграции
function codeweber_forms_matomo_integration_page() {
    $matomo_active = codeweber_forms_matomo_is_plugin_active();

    if (isset($_POST['save_matomo_settings']) && wp_verify_nonce($_POST['matomo_nonce'], 'save_matomo_settings')) {
        update_option('codeweber_forms_matomo_track_forms', isset($_POST['matomo_track_forms']) ? 1 : 0);
        update_option('codeweber_forms_matomo_debug_mode', isset($_POST['matomo_debug_mode']) ? 1 : 0);
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved!', 'codeweber') . '</p></div>';
    }

    $matomo_track_forms = get_option('codeweber_forms_matomo_track_forms', 1);
    $matomo_debug_mode = get_option('codeweber_forms_matomo_debug_mode', 0);
    
    // Подсчитываем формы
    $cf7_count = class_exists('WPCF7') ? count(get_posts([
        'post_type' => 'wpcf7_contact_form',
        'numberposts' => -1,
        'post_status' => 'publish'
    ])) : 0;
    
    $codeweber_count = count(get_posts([
        'post_type' => 'codeweber_form',
        'numberposts' => -1,
        'post_status' => 'publish'
    ]));
?>

    <div class="wrap">
        <h1><?php _e('Matomo Forms Integration', 'codeweber'); ?></h1>
        <p class="description"><?php _e('Universal integration for Codeweber Forms and Contact Form 7', 'codeweber'); ?></p>

        <?php if (!$matomo_active): ?>
            <div class="notice notice-warning is-dismissible">
                <p><?php _e('Matomo plugin is not active. Form tracking is disabled.', 'codeweber'); ?></p>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2><?php _e('Settings', 'codeweber'); ?></h2>
            <form method="post">
                <?php wp_nonce_field('save_matomo_settings', 'matomo_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Track Form Submissions', 'codeweber'); ?></th>
                        <td>
                            <input type="checkbox" id="matomo_track_forms" name="matomo_track_forms" value="1"
                                <?php checked($matomo_track_forms, 1); ?>>
                            <label for="matomo_track_forms"><?php _e('Send form events to Matomo', 'codeweber'); ?></label>
                            <p class="description"><?php _e('Tracks: Form Opened, Form Submission, Form Errors', 'codeweber'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Debug Mode', 'codeweber'); ?></th>
                        <td>
                            <input type="checkbox" id="matomo_debug_mode" name="matomo_debug_mode" value="1" <?php checked($matomo_debug_mode, 1); ?>>
                            <label for="matomo_debug_mode"><?php _e('Enable debug logging', 'codeweber'); ?></label>
                            <p class="description"><?php _e('Logs all Matomo tracking events to WordPress debug log', 'codeweber'); ?></p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="save_matomo_settings" class="button button-primary" value="<?php _e('Save Settings', 'codeweber'); ?>">
                </p>
            </form>
        </div>
        
        <div class="card">
            <h2><?php _e('Integration Status', 'codeweber'); ?></h2>
            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php _e('Form System', 'codeweber'); ?></th>
                        <th><?php _e('Forms Count', 'codeweber'); ?></th>
                        <th><?php _e('Status', 'codeweber'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong><?php _e('Codeweber Forms', 'codeweber'); ?></strong></td>
                        <td><?php echo $codeweber_count; ?></td>
                        <td><span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> <?php _e('Active', 'codeweber'); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Contact Form 7', 'codeweber'); ?></strong></td>
                        <td><?php echo $cf7_count; ?></td>
                        <td>
                            <?php if (class_exists('WPCF7')): ?>
                                <span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> <?php _e('Active', 'codeweber'); ?>
                            <?php else: ?>
                                <span class="dashicons dashicons-dismiss" style="color: #dc3232;"></span> <?php _e('Not Active', 'codeweber'); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="card">
            <h2><?php _e('Tracked Events', 'codeweber'); ?></h2>
            <ul>
                <li><strong><?php _e('Form Opened', 'codeweber'); ?></strong> - <?php _e('When form is displayed/opened', 'codeweber'); ?></li>
                <li><strong><?php _e('Form Submission', 'codeweber'); ?></strong> - <?php _e('Successful form submission', 'codeweber'); ?></li>
                <li><strong><?php _e('Form Errors', 'codeweber'); ?></strong> - <?php _e('Validation errors, server errors, spam detection', 'codeweber'); ?></li>
            </ul>
        </div>
    </div>

    <style>
        .card {
            margin: 20px 0;
            padding: 20px;
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }

        .card h2 {
            margin-top: 0;
        }
    </style>
<?php
}

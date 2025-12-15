<?php
/**
 * Matomo Forms Integration Module
 * Отправляет события отправки форм в Matomo для анализа
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
// Используем приоритет 20, чтобы выполниться после регистрации основного меню
add_action('admin_menu', 'codeweber_forms_matomo_integration_admin_menu', 20);
function codeweber_forms_matomo_integration_admin_menu() {
    if (!codeweber_forms_matomo_is_plugin_active()) {
        return;
    }

    // Проверяем, что главное меню уже зарегистрировано
    global $submenu;
    if (!isset($submenu['codeweber'])) {
        return;
    }

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

// Функция: Перевод действия для Matomo
function codeweber_forms_matomo_translate_action($action) {
    $translations = [
        'Form Submission' => __('Form Submission', 'codeweber'),
        'Form Opened' => __('Form Opened', 'codeweber'),
        'Form Error' => __('Form Error', 'codeweber'),
    ];
    return $translations[$action] ?? $action;
}

// Функция: Отправка события отправки формы в Matomo
function codeweber_forms_matomo_track_form_event($form_id, $form_name, $action = 'Form Submission', $value = 1, $current_url = '') {
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
    
    $params = [
        'idsite' => CODEWEBER_FORMS_MATOMO_SITE_ID,
        'rec' => 1,
        'ua' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        '_id' => $visitor_id,
        'e_c' => __('Form', 'codeweber'),
        'e_a' => $translated_action,
        'e_n' => $form_name . ' (ID: ' . $form_id . ')',
        'e_v' => $value,
        'url' => $current_url,
        'urlref' => $_SERVER['HTTP_REFERER'] ?? home_url(),
        'send_image' => 0,
    ];

    $response = wp_remote_post(
        home_url('/wp-json/matomo/v1/hit/'),
        [
            'timeout' => 10,
            'sslverify' => false,
            'body' => $params,
        ]
    );

    if (is_wp_error($response)) {
        if (get_option('codeweber_forms_matomo_debug_mode', 0)) {
            error_log('Codeweber Forms Matomo Error: ' . $response->get_error_message());
        }
        return false;
    }

    $response_code = wp_remote_retrieve_response_code($response);
    
    if (get_option('codeweber_forms_matomo_debug_mode', 0)) {
        error_log('Codeweber Forms Matomo Event: ' . $form_name . ' - ' . $action . ' - Response: ' . $response_code);
    }
    
    return in_array($response_code, [200, 204]);
}

// Хуки для отслеживания отправок форм
if (codeweber_forms_matomo_is_plugin_active()) {
    // Отслеживание открытия формы
    add_action('codeweber_form_opened', 'codeweber_forms_matomo_track_form_opened', 10, 1);
    function codeweber_forms_matomo_track_form_opened($form_id) {
        // Получаем настройки формы для имени
        $form_settings = [];
        if (is_numeric($form_id) && $form_id > 0) {
            $post = get_post($form_id);
            if ($post && $post->post_type === 'codeweber_form') {
                $form_settings['formName'] = $post->post_title;
            }
        }
        $form_name = $form_settings['formName'] ?? __('Form', 'codeweber');
        $current_url = home_url($_SERVER['REQUEST_URI'] ?? '');
        codeweber_forms_matomo_track_form_event($form_id, $form_name, 'Form Opened', 1, $current_url);
    }
    
    // Отслеживание успешной отправки формы
    add_action('codeweber_form_after_send', 'codeweber_forms_matomo_track_submission', 10, 3);
    function codeweber_forms_matomo_track_submission($form_id, $form_settings, $submission_id) {
        $form_name = $form_settings['formName'] ?? __('Form', 'codeweber');
        $current_url = $_SERVER['HTTP_REFERER'] ?? home_url();
        codeweber_forms_matomo_track_form_event($form_id, $form_name, 'Form Submission', 1, $current_url);
    }
    
    // Отслеживание ошибок отправки
    add_action('codeweber_form_send_error', 'codeweber_forms_matomo_track_error', 10, 3);
    function codeweber_forms_matomo_track_error($form_id, $form_settings, $error_message) {
        $form_name = $form_settings['formName'] ?? __('Form', 'codeweber');
        $current_url = $_SERVER['HTTP_REFERER'] ?? home_url();
        codeweber_forms_matomo_track_form_event($form_id, $form_name, 'Form Error', 0, $current_url);
    }
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
?>

    <div class="wrap">
        <h1><?php _e('Matomo Forms Integration', 'codeweber'); ?></h1>

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
                            <label for="matomo_track_forms"><?php _e('Send form submissions to Matomo as Events', 'codeweber'); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Debug Mode', 'codeweber'); ?></th>
                        <td>
                            <input type="checkbox" id="matomo_debug_mode" name="matomo_debug_mode" value="1" <?php checked($matomo_debug_mode, 1); ?>>
                            <label for="matomo_debug_mode"><?php _e('Enable debug logging', 'codeweber'); ?></label>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="save_matomo_settings" class="button button-primary" value="<?php _e('Save Settings', 'codeweber'); ?>">
                </p>
            </form>
        </div>
    </div>

    <style>
        .card {
            margin: 20px 0;
            padding: 20px;
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px
        }

        .card h2 {
            margin-top: 0
        }
    </style>
<?php
}


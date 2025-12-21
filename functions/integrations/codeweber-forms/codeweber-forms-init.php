<?php
/**
 * CodeWeber Forms Module Initialization
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

// Определяем константы модуля
define('CODEWEBER_FORMS_PATH', __DIR__);
define('CODEWEBER_FORMS_URL', get_template_directory_uri() . '/functions/integrations/codeweber-forms');
define('CODEWEBER_FORMS_VERSION', '1.0.0');
define('CODEWEBER_FORMS_LANGUAGES', CODEWEBER_FORMS_PATH . '/languages');

// Подключаем файлы модуля
require_once CODEWEBER_FORMS_PATH . '/codeweber-forms-cpt.php';
require_once CODEWEBER_FORMS_PATH . '/codeweber-forms-gutenberg-restrictions.php';
require_once CODEWEBER_FORMS_PATH . '/codeweber-forms-block-selector.php';
require_once CODEWEBER_FORMS_PATH . '/codeweber-forms-database.php';
require_once CODEWEBER_FORMS_PATH . '/codeweber-forms-temp-files.php';
require_once CODEWEBER_FORMS_PATH . '/codeweber-forms-core.php';
require_once CODEWEBER_FORMS_PATH . '/codeweber-forms-default-forms.php'; // Default формы (хранятся в коде)
require_once CODEWEBER_FORMS_PATH . '/codeweber-forms-migrate-cpt-types.php'; // НОВОЕ: Скрипт миграции CPT форм
require_once CODEWEBER_FORMS_PATH . '/codeweber-forms-api.php';
require_once CODEWEBER_FORMS_PATH . '/codeweber-forms-validator.php';
require_once CODEWEBER_FORMS_PATH . '/codeweber-forms-sanitizer.php';
require_once CODEWEBER_FORMS_PATH . '/codeweber-forms-mailer.php';
require_once CODEWEBER_FORMS_PATH . '/codeweber-forms-rate-limit.php';
require_once CODEWEBER_FORMS_PATH . '/codeweber-forms-hooks.php';
require_once CODEWEBER_FORMS_PATH . '/codeweber-forms-renderer.php';
require_once CODEWEBER_FORMS_PATH . '/codeweber-forms-shortcode.php';
require_once CODEWEBER_FORMS_PATH . '/codeweber-forms-utm.php';
require_once CODEWEBER_FORMS_PATH . '/codeweber-forms-consent-helper.php';
require_once CODEWEBER_FORMS_PATH . '/codeweber-forms-document-version.php';
require_once CODEWEBER_FORMS_PATH . '/codeweber-forms-user-consents.php';
require_once CODEWEBER_FORMS_PATH . '/matomo-forms-integration.php';
require_once CODEWEBER_FORMS_PATH . '/codeweber-forms-newsletter-integration.php';

// Интеграция с Contact Form 7 (только если CF7 активен)
if (class_exists('WPCF7')) {
    require_once CODEWEBER_FORMS_PATH . '/codeweber-forms-cf7-integration.php';
}

// Админка
if (is_admin()) {
    require_once CODEWEBER_FORMS_PATH . '/admin/codeweber-forms-admin.php';
    require_once CODEWEBER_FORMS_PATH . '/admin/codeweber-forms-settings.php';
    require_once CODEWEBER_FORMS_PATH . '/admin/codeweber-forms-submissions.php';
    require_once CODEWEBER_FORMS_PATH . '/admin/codeweber-forms-consent-metabox.php';
    require_once CODEWEBER_FORMS_PATH . '/admin/codeweber-forms-builtin-settings.php';
}
// Email Templates класс нужен и на фронтенде для отправки писем
require_once CODEWEBER_FORMS_PATH . '/admin/codeweber-forms-email-templates.php';

// Загрузка переводов для темы
// Загружаем переводы раньше, до after_setup_theme, чтобы они были доступны сразу
add_action('plugins_loaded', function() {
    $locale = get_locale();
    $mofile = CODEWEBER_FORMS_LANGUAGES . '/codeweber-forms-' . $locale . '.mo';
    
    // Загружаем .mo файл напрямую
    if (file_exists($mofile)) {
        $loaded = load_textdomain('codeweber', $mofile);
        // Если не загрузилось, пробуем альтернативный путь
        if (!$loaded) {
            // Пробуем загрузить через load_theme_textdomain
            load_theme_textdomain('codeweber', CODEWEBER_FORMS_LANGUAGES);
        }
    }
}, 5); // Приоритет 5, чтобы загружалось очень рано

// Дублируем загрузку на after_setup_theme для совместимости
add_action('after_setup_theme', function() {
    $locale = get_locale();
    $mofile = CODEWEBER_FORMS_LANGUAGES . '/codeweber-forms-' . $locale . '.mo';
    
    // Загружаем .mo файл напрямую
    if (file_exists($mofile)) {
        $loaded = load_textdomain('codeweber', $mofile);
        // Если не загрузилось, пробуем альтернативный путь
        if (!$loaded) {
            // Пробуем загрузить через load_theme_textdomain
            load_theme_textdomain('codeweber', CODEWEBER_FORMS_LANGUAGES);
        }
    }
}, 5); // Приоритет 5, чтобы загружалось раньше

// REST API регистрируем сразу, до init
new CodeweberFormsAPI();

// Регистрируем CPT для форм ДО хука init, чтобы хук init в конструкторе успел зарегистрироваться
new CodeweberFormsCPT();

// Блок для выбора формы на обычных страницах - регистрируем ДО хука init
// чтобы хук init в конструкторе успел зарегистрироваться с приоритетом 5
new CodeweberFormsBlockSelector();

// Инициализация
add_action('init', function() {
    // Инициализируем БД для отправок
    new CodeweberFormsDatabase();
    
    // Инициализируем класс для временных файлов
    new CodeweberFormsTempFiles();
    
    // Основной класс
    new CodeweberFormsCore();
    
    // Шорткод
    new CodeweberFormsShortcode();
    
    // Админка
    if (is_admin()) {
        new CodeweberFormsAdmin();
        new CodeweberFormsSettings();
        new CodeweberFormsSubmissions();
        // new CodeweberFormsConsentMetabox(); // Отключено - согласия настраиваются через блок Form Field
        new CodeweberFormsBuiltinSettings();
        
        // Ограничения Gutenberg редактора для CPT форм
        new CodeweberFormsGutenbergRestrictions();
    }
    
    // Email Templates нужен и на фронтенде для отправки писем
    new CodeweberFormsEmailTemplates();
    
    // Schedule cleanup cron job for temp files
    if (!wp_next_scheduled('codeweber_forms_cleanup_temp_files')) {
        wp_schedule_event(time(), 'daily', 'codeweber_forms_cleanup_temp_files');
    }
}, 20);

// Cleanup expired temp files
add_action('codeweber_forms_cleanup_temp_files', function() {
    $temp_files = new CodeweberFormsTempFiles();
    $deleted_count = $temp_files->cleanup_expired_files(100);
    error_log('Codeweber Forms: Cleaned up ' . $deleted_count . ' expired temp files');
});

// Регистрация провайдера в Personal Data V2 (новый универсальный модуль)
// Старый функционал продолжает работать, новый модуль добавляется параллельно
add_action('personal_data_v2_ready', function($manager) {
    if (file_exists(__DIR__ . '/../personal-data-v2/providers/class-codeweber-forms-provider.php')) {
        require_once __DIR__ . '/../personal-data-v2/providers/class-codeweber-forms-provider.php';
        $provider = new Codeweber_Forms_Data_Provider();
        $manager->register_provider($provider);
    }
}, 10);



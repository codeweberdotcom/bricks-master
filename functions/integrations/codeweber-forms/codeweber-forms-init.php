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
require_once CODEWEBER_FORMS_PATH . '/codeweber-forms-core.php';
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
    
    // Основной класс
    new CodeweberFormsCore();
    
    // Шорткод
    new CodeweberFormsShortcode();
    
    // Админка
    if (is_admin()) {
        new CodeweberFormsAdmin();
        new CodeweberFormsSettings();
        new CodeweberFormsSubmissions();
        new CodeweberFormsConsentMetabox();
        new CodeweberFormsBuiltinSettings();
        
        // Ограничения Gutenberg редактора для CPT форм
        new CodeweberFormsGutenbergRestrictions();
    }
    
    // Email Templates нужен и на фронтенде для отправки писем
    new CodeweberFormsEmailTemplates();
}, 20);

// Регистрация провайдера в Personal Data V2 (новый универсальный модуль)
// Старый функционал продолжает работать, новый модуль добавляется параллельно
add_action('personal_data_v2_ready', function($manager) {
    if (file_exists(__DIR__ . '/../personal-data-v2/providers/class-codeweber-forms-provider.php')) {
        require_once __DIR__ . '/../personal-data-v2/providers/class-codeweber-forms-provider.php';
        $provider = new Codeweber_Forms_Data_Provider();
        $manager->register_provider($provider);
    }
}, 10);



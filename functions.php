<?php

/**
 *  https://developer.wordpress.org/themes/basics/theme-functions/
 */

// Подключение файлов CPT
require_once get_template_directory() . '/functions/cpt/cpt-header.php';
require_once get_template_directory() . '/functions/cpt/cpt-footer.php';
require_once get_template_directory() . '/functions/cpt/cpt-page-header.php';
require_once get_template_directory() . '/functions/cpt/cpt-modals.php';
require_once get_template_directory() . '/functions/cpt/cpt-html_blocks.php';
require_once get_template_directory() . '/functions/cpt/cpt-clients.php';

require_once get_template_directory() . '/functions/setup.php';
require_once get_template_directory() . '/functions/roles.php';
require_once get_template_directory() . '/functions/gulp.php';

require_once get_template_directory() . '/plugins/tgm/class-tgm-plugin-activation.php';
require_once get_template_directory() . '/plugins/tgm/plugins_autoinstall.php';

require_once get_template_directory() . '/functions/enqueues.php';
require_once get_template_directory() . '/functions/images.php';
require_once get_template_directory() . '/functions/pdf-thumbnail-install.php';
require_once get_template_directory() . '/functions/pdf-thumbnail.php';
require_once get_template_directory() . '/functions/pdf-thumbnail-js.php';
require_once get_template_directory() . '/functions/navmenus.php';
require_once get_template_directory() . '/functions/sidebars.php';
require_once get_template_directory() . '/functions/lib/class-wp-bootstrap-navwalker.php';
require_once get_template_directory() . '/functions/global.php';
require_once get_template_directory() . '/functions/breadcrumbs.php';
require_once get_template_directory() . '/functions/cleanup.php';
require_once get_template_directory() . '/functions/custom.php';

if (class_exists('WPCF7')) {
	require_once get_template_directory() . '/functions/integrations/cf7.php';
};

require_once get_template_directory() . '/functions/admin/admin_settings.php';
require_once get_template_directory() . '/functions/fetch/fetch-handler.php';

if (class_exists('WooCommerce')) {
	require_once get_template_directory() . '/functions/woocommerce.php';
}

// Подключаем модуль персональных данных Cyr-to-Lat
require_once get_template_directory() . '/functions/user.php';


// Подключаем модуль персональных данных Cyr-to-Lat
require_once get_template_directory() . '/functions/cyr-to-lat.php';

require_once get_template_directory() . '/functions/lib/comments-helper.php'; // --- Comments Helper ---
require_once get_template_directory() . '/functions/comments-reply.php'; // --- Comments Reply Functions ---
require_once get_template_directory() . '/functions/post-card-templates.php'; // --- Post Card Templates System ---


// Подключаем универсальный модуль персональных данных V2
require_once get_template_directory() . '/functions/integrations/personal-data-v2/init.php';

// Подключение модуля newsletter subscription
require_once get_template_directory() . '/functions/integrations/newsletter-subscription/newsletter-init.php';

// Регистрация провайдеров Personal Data V2
add_action('personal_data_v2_ready', function($manager) {
    // CF7 Provider
    if (file_exists(get_template_directory() . '/functions/integrations/personal-data-v2/providers/class-cf7-provider.php')) {
        require_once get_template_directory() . '/functions/integrations/personal-data-v2/providers/class-cf7-provider.php';
        $provider = new CF7_Data_Provider();
        $manager->register_provider($provider);
    }
    
    // Testimonials Provider
    if (file_exists(get_template_directory() . '/functions/integrations/personal-data-v2/providers/class-testimonials-provider.php')) {
        require_once get_template_directory() . '/functions/integrations/personal-data-v2/providers/class-testimonials-provider.php';
        $provider = new Testimonials_Data_Provider();
        $manager->register_provider($provider);
    }
    
    // Consent Provider
    if (file_exists(get_template_directory() . '/functions/integrations/personal-data-v2/providers/class-consent-provider.php')) {
        require_once get_template_directory() . '/functions/integrations/personal-data-v2/providers/class-consent-provider.php';
        $provider = new Consent_Data_Provider();
        $manager->register_provider($provider);
    }
}, 10);

// Тестовый скрипт для проверки провайдеров (удалите после тестирования)
if (defined('WP_DEBUG') && WP_DEBUG) {
    require_once get_template_directory() . '/functions/integrations/personal-data-v2/test-providers.php';
}


/**
 * Инициализация Redux Framework
 */
function codeweber_initialize_redux()
{
	if (!class_exists('Redux')) {
		require_once get_template_directory() . '/redux-framework/redux-core/framework.php';
	}

	global $opt_name;
	$opt_name = 'redux_demo';
	// #region agent log
	$log_data = json_encode(['location' => 'functions.php:75', 'message' => 'Redux initialization', 'data' => ['opt_name' => $opt_name, 'class_exists_Redux' => class_exists('Redux'), 'hook' => 'after_setup_theme', 'priority' => 30, 'role_exists' => get_role('simpleadmin') !== null], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A']);
	$log_file = ABSPATH . '.cursor/debug.log';
	@file_put_contents($log_file, $log_data . "\n", FILE_APPEND);
	// #endregion

	require_once get_template_directory() . '/redux-framework/sample/theme-config.php';
	require_once get_template_directory() . '/functions/cpt/redux_cpt.php';
	require_once get_template_directory() . '/functions/sidebars-redux.php';
	
	// Подключаем theme-settings.php с настройками Redux
	if (file_exists(get_template_directory() . '/redux-framework/theme-settings/theme-settings.php')) {
		require_once get_template_directory() . '/redux-framework/theme-settings/theme-settings.php';
	}
}
add_action('after_setup_theme', 'codeweber_initialize_redux', 30);


/**
 * Подключение модуля лицензий изображений
 */
require_once get_template_directory() . '/functions/integrations/image-licenses/image-licenses.php';

/**
 * Подключение модуля поиска и статистики
 */
require_once get_template_directory() . '/functions/integrations/ajax-search-module/ajax-search.php';

require_once get_template_directory() . '/functions/integrations/ajax-search-module/search-statistics.php';

require_once get_template_directory() . '/functions/integrations/ajax-search-module/matomo-search-integration.php';


/**
 * Подключение модуля форм CodeWeber
 */
require_once get_template_directory() . '/functions/integrations/codeweber-forms/codeweber-forms-init.php';
require_once get_template_directory() . '/functions/integrations/yandex-maps/yandex-maps-init.php';

// Подключение универсального AJAX фильтра
require_once get_template_directory() . '/functions/ajax-filter.php';

// Подключение demo функций
require_once get_template_directory() . '/functions/demo/demo-clients.php';
require_once get_template_directory() . '/functions/demo/demo-faq.php';
require_once get_template_directory() . '/functions/demo/demo-testimonials.php';
require_once get_template_directory() . '/functions/demo/demo-staff.php';
require_once get_template_directory() . '/functions/demo/demo-vacancies.php';
require_once get_template_directory() . '/functions/demo/demo-forms.php';
require_once get_template_directory() . '/functions/demo/demo-cf7-forms.php';
require_once get_template_directory() . '/functions/demo/demo-offices.php';
require_once get_template_directory() . '/functions/demo/demo-ajax.php';

/**
 * Подключение универсального модального контейнера
 */
require_once get_template_directory() . '/functions/integrations/modal-container.php';

/**
 * Подключение REST API расширений для модальных окон
 */
require_once get_template_directory() . '/functions/integrations/modal-rest-api.php';

/**
 * Подключение единого шаблона сообщения об успешной отправке
 */
require_once get_template_directory() . '/functions/integrations/success-message-template.php';

/**
 * Подключение API для формы отправки отзывов
 */
require_once get_template_directory() . '/functions/testimonials/testimonial-form-api.php';
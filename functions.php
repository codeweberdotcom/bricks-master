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
require_once get_template_directory() . '/functions/gulp.php';

require_once get_template_directory() . '/plugins/tgm/class-tgm-plugin-activation.php';
require_once get_template_directory() . '/plugins/tgm/plugins_autoinstall.php';

require_once get_template_directory() . '/functions/enqueues.php';
require_once get_template_directory() . '/functions/images.php';
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


// Подключаем модуль персональных данных
require_once get_template_directory() . '/functions/integrations/personal-data/init.php';

// Подключение модуля newsletter subscription
require_once get_template_directory() . '/functions/integrations/newsletter-subscription/newsletter-init.php';


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

	require_once get_template_directory() . '/redux-framework/sample/theme-config.php';
	require_once get_template_directory() . '/functions/cpt/redux_cpt.php';
	require_once get_template_directory() . '/functions/sidebars-redux.php';
}
add_action('after_setup_theme', 'codeweber_initialize_redux', 20);


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

require_once get_template_directory() . '/functions/integrations/ajax-search-module/contact-form7-matomo-integration.php';

// Подключение demo функций
require_once get_template_directory() . '/functions/demo/demo-clients.php';
require_once get_template_directory() . '/functions/demo/demo-faq.php';
require_once get_template_directory() . '/functions/demo/demo-testimonials.php';
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
 * Подключение API для формы отправки отзывов
 */
require_once get_template_directory() . '/functions/testimonials/testimonial-form-api.php';
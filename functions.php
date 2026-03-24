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
require_once get_template_directory() . '/functions/cpt/cpt-notifications.php';

require_once get_template_directory() . '/functions/setup.php';
require_once get_template_directory() . '/functions/roles.php';
require_once get_template_directory() . '/functions/gulp.php';

require_once get_template_directory() . '/plugins/tgm/class-tgm-plugin-activation.php';
require_once get_template_directory() . '/plugins/tgm/plugins_autoinstall.php';

require_once get_template_directory() . '/functions/class-codeweber-options.php';
require_once get_template_directory() . '/functions/enqueues.php';
require_once get_template_directory() . '/functions/images.php';
require_once get_template_directory() . '/functions/lib/pdf-thumbnail/init.php';
require_once get_template_directory() . '/functions/navmenus.php';
require_once get_template_directory() . '/functions/sidebars.php';
require_once get_template_directory() . '/functions/documentation.php';
require_once get_template_directory() . '/functions/lib/class-wp-bootstrap-navwalker.php';
require_once get_template_directory() . '/functions/lib/class-codeweber-vertical-dropdown-walker.php';
require_once get_template_directory() . '/functions/lib/cw-notify/class-cw-notify.php';
require_once get_template_directory() . '/functions/lib/class-codeweber-menu-collapse-walker.php';
require_once get_template_directory() . '/functions/codeweber-nav.php';
require_once get_template_directory() . '/functions/global.php';
require_once get_template_directory() . '/functions/breadcrumbs.php';
require_once get_template_directory() . '/functions/cleanup.php';
require_once get_template_directory() . '/functions/custom.php';

if ( class_exists( 'WPCF7' ) ) {
	require_once get_template_directory() . '/functions/integrations/cf7/cf7.php';
}

require_once get_template_directory() . '/functions/admin/admin_settings.php';
require_once get_template_directory() . '/functions/admin/media-regenerate.php';
require_once get_template_directory() . '/functions/fetch/fetch-handler.php';

if ( class_exists( 'WooCommerce' ) ) {
	require_once get_template_directory() . '/functions/woocommerce/init.php';
}

// CWNotify — универсальный менеджер уведомлений (инит после Redux, priority 40).
add_action( 'after_setup_theme', function () {
	new CW_Notify();
}, 40 );

// DaData: стандартизация адресов (clean/address)
require_once get_template_directory() . '/functions/integrations/dadata/class-codeweber-dadata.php';
require_once get_template_directory() . '/functions/integrations/dadata/dadata-ajax.php';

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





/**
 * Подключение модуля лицензий изображений
 */
require_once get_template_directory() . '/functions/integrations/image-licenses/image-licenses.php';

// Ajax Search: поиск, статистика, Matomo
require_once get_template_directory() . '/functions/integrations/ajax-search-module/init.php';


/**
 * Подключение модуля форм CodeWeber
 */
require_once get_template_directory() . '/functions/integrations/codeweber-forms/codeweber-forms-init.php';
require_once get_template_directory() . '/functions/integrations/yandex-maps/yandex-maps-init.php';

// Подключение demo функций (только в режиме разработки)
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
	require_once get_template_directory() . '/functions/demo/demo-clients.php';
	require_once get_template_directory() . '/functions/demo/demo-faq.php';
	require_once get_template_directory() . '/functions/demo/demo-testimonials.php';
	require_once get_template_directory() . '/functions/demo/demo-staff.php';
	require_once get_template_directory() . '/functions/demo/demo-vacancies.php';
	require_once get_template_directory() . '/functions/demo/demo-forms.php';
	require_once get_template_directory() . '/functions/demo/demo-cf7-forms.php';
	require_once get_template_directory() . '/functions/demo/demo-offices.php';
	require_once get_template_directory() . '/functions/demo/demo-footer.php';
	require_once get_template_directory() . '/functions/demo/demo-header.php';
	require_once get_template_directory() . '/functions/demo/demo-products.php';
	require_once get_template_directory() . '/functions/demo/demo-events.php';
	require_once get_template_directory() . '/functions/demo/demo-ajax.php';
}

// Modal: контейнер, REST API, шаблон успешной отправки
require_once get_template_directory() . '/functions/integrations/modal/init.php';

/**
 * Подключение API для формы отправки отзывов
 */
require_once get_template_directory() . '/functions/testimonials/testimonial-form-api.php';



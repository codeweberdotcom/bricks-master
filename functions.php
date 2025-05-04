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

require_once get_template_directory() . '/functions/setup.php';

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
require_once get_template_directory() . '/functions/integrations/cf7.php';
require_once get_template_directory() . '/functions/admin/admin_settings.php';
require_once get_template_directory() . '/functions/fetch/fetch-handler.php';



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


// Хук для создания демоформ при активации плагина CF7
add_action('activated_plugin', 'create_cf7_forms_on_activation', 10, 2);

function create_cf7_forms_on_activation($plugin, $network_wide)
{
	// Проверяем, что активирован плагин Contact Form 7
	if ($plugin === 'contact-form-7/wp-contact-form-7.php') {
		create_custom_cf7_form();
		create_custom_cf7_form_with_name_and_email();
		create_custom_cf7_form_with_name_comment_and_email();
	}
}
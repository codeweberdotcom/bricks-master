<?php

/**
 *  https://developer.wordpress.org/themes/basics/theme-functions/
 */

// Подключение файлов CPT
require_once get_template_directory() . '/functions/cpt/cpt-header.php';
require_once get_template_directory() . '/functions/cpt/cpt-footer.php';
require_once get_template_directory() . '/functions/cpt/cpt-page-header.php';

require_once get_template_directory() . '/functions/setup.php';

require_once get_template_directory() . '/components/plugins/tgm/class-tgm-plugin-activation.php';
require_once get_template_directory() . '/components/plugins_autoinstall.php';

require_once get_template_directory() . '/functions/enqueues.php';
require_once get_template_directory() . '/functions/images.php';
require_once get_template_directory() . '/functions/navmenus.php';
require_once get_template_directory() . '/functions/sidebars.php';
require_once get_template_directory() . '/functions/lib/class-wp-bootstrap-navwalker.php';
require_once get_template_directory() . '/functions/global.php';
require_once get_template_directory() . '/functions/breadcrumbs.php';
require_once get_template_directory() . '/functions/cleanup.php';
require_once get_template_directory() . '/functions/custom.php';
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

add_filter('pre_set_site_transient_update_themes', 'bricks_master_github_updater');

function bricks_master_github_updater($transient)
{
	if (empty($transient->checked)) {
		return $transient;
	}

	$github_api_url = 'https://api.github.com/repos/codeweberdotcom/bricks-master/releases/latest';

	// Получаем данные с GitHub
	$response = wp_remote_get($github_api_url, [
		'headers' => [
			'Accept'        => 'application/vnd.github.v3+json',
			'User-Agent'    => 'WordPress Theme Updater'
		]
	]);

	if (is_wp_error($response)) {
		return $transient;
	}

	$release = json_decode(wp_remote_retrieve_body($response));
	if (empty($release->tag_name)) {
		return $transient;
	}

	$theme_slug = 'bricks-master'; // Это папка темы!
	$current_version = wp_get_theme($theme_slug)->get('Version');
	$new_version     = ltrim($release->tag_name, 'v'); // v1.0.1 → 1.0.1

	if (version_compare($new_version, $current_version, '>')) {
		$transient->response[$theme_slug] = array(
			'theme'       => $theme_slug,
			'new_version' => $new_version,
			'url'         => $release->html_url,
			'package'     => $release->zipball_url
		);
	}

	return $transient;
}
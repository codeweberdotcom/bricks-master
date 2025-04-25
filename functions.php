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


add_action('admin_init', 'custom_update_theme_from_github');

function custom_update_theme_from_github()
{
	if (!current_user_can('update_themes')) {
		return;
	}

	// Проверка параметра update_bricks в URL
	if (isset($_GET['update_bricks']) && $_GET['update_bricks'] === 'true') {

		// Получение данных последнего релиза с GitHub
		$github_api_url = 'https://api.github.com/repos/codeweberdotcom/bricks-master/releases/latest';
		$response = wp_remote_get($github_api_url, [
			'headers' => [
				'Accept' => 'application/vnd.github.v3+json',
				'User-Agent' => 'WordPress Updater'
			]
		]);

		if (is_wp_error($response)) {
			wp_die('Ошибка при подключении к GitHub API.');
		}

		$release = json_decode(wp_remote_retrieve_body($response));
		if (empty($release->zipball_url)) {
			wp_die('Не удалось получить ссылку на архив.');
		}

		$zip_url = $release->zipball_url;

		// Пути для скачивания и распаковки
		$upload_dir = wp_upload_dir();
		$temp_dir = trailingslashit($upload_dir['basedir']) . 'bricks-updater/';
		$temp_zip = $temp_dir . 'bricks-master.zip';

		if (!file_exists($temp_dir)) {
			wp_mkdir_p($temp_dir);
		}

		// Скачиваем архив
		$zip_response = wp_remote_get($zip_url, ['timeout' => 60]);
		if (is_wp_error($zip_response)) {
			wp_die('Ошибка при загрузке архива: ' . $zip_response->get_error_message());
		}

		// Сохраняем файл
		$file_body = wp_remote_retrieve_body($zip_response);
		if (strlen($file_body) < 100) {
			wp_die('Ошибка: файл архива слишком маленький.');
		}

		file_put_contents($temp_zip, $file_body);

		// Распаковка
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		WP_Filesystem();
		$unzip = unzip_file($temp_zip, $temp_dir);
		if (is_wp_error($unzip)) {
			wp_die('Ошибка при распаковке архива: ' . $unzip->get_error_message());
		}

		// Поиск распакованной директории
		$extracted_dirs = glob($temp_dir . 'codeweberdotcom-bricks-master-*');
		if (empty($extracted_dirs)) {
			wp_die('Не удалось найти распакованную тему.');
		}

		$source = $extracted_dirs[0];

		// Проверка наличия style.css и functions.php
		if (!file_exists($source . '/style.css') || !file_exists($source . '/functions.php')) {
			wp_die('Папка с темой повреждена или неполная.');
		}

		$dest = get_theme_root() . '/bricks-master';

		$filesystem = new WP_Filesystem_Direct(false);

		// Удаляем старую тему после успешной загрузки и распаковки
		$filesystem->rmdir($dest, true);

		// Копируем новую
		copy_dir($source, $dest);

		// Удаляем временные файлы
		$filesystem->delete($temp_dir, true);

		echo '🎉 Тема обновлена до последней версии GitHub!';
		exit;
	}
}


add_action('admin_notices', 'bricks_master_update_notice');
function bricks_master_update_notice()
{
	if (!current_user_can('update_themes')) {
		return;
	}

	$theme_slug = 'bricks-master';
	$current_version = wp_get_theme($theme_slug)->get('Version');
	$github_api_url = 'https://api.github.com/repos/codeweberdotcom/bricks-master/releases/latest';

	$response = wp_remote_get($github_api_url, [
		'headers' => [
			'Accept' => 'application/vnd.github.v3+json',
			'User-Agent' => 'WordPress Theme Updater'
		]
	]);

	if (is_wp_error($response)) {
		return;
	}

	$release = json_decode(wp_remote_retrieve_body($response));
	if (empty($release->tag_name)) {
		return;
	}

	$new_version = ltrim($release->tag_name, 'v');
	if (version_compare($new_version, $current_version, '>')) {
		$update_url = esc_url(add_query_arg(['update_bricks' => 'true']));
		echo '<div class="notice notice-warning is-dismissible">';
		echo '<p>Доступна новая версия темы <strong>Bricks Master</strong>: ' . esc_html($new_version) . ' (текущая: ' . esc_html($current_version) . '). ';
		echo '<a href="' . $update_url . '" class="button button-primary">Обновить сейчас</a></p>';
		echo '</div>';
	}
}

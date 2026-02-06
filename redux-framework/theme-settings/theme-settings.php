<?php
// Основной идентификатор Redux
global $opt_name;
// Используем redux_demo вместо my_redux_options
if (empty($opt_name)) {
	$opt_name = 'redux_demo';
}

// Проверяем, инициализирован ли Redux
if (! class_exists('Redux')) {
	return;
}

// Стили для Redux панели
add_action('redux/options/' . $opt_name . '/enqueue', 'theme_settings_custom_styles');

function theme_settings_custom_styles()
{
	wp_enqueue_style('theme-settings-css', get_template_directory_uri() . '/redux-framework/theme-settings/theme-settings.css', false, wp_get_theme()->get('Version'), 'all');
}

// Функция для автоматического подключения всех файлов и добавления секций
function add_redux_sections_from_files($path, $opt_name)
{
	// Получаем список папок
	$folders = scandir($path);

	foreach ($folders as $folder) {
		if ($folder === '.' || $folder === '..') {
			continue;
		}

		$folder_path = $path . '/' . $folder;

		// Если это папка
		if (is_dir($folder_path)) {
			// Ищем файл с суффиксом _redux.php внутри папки
			$files = scandir($folder_path);

			foreach ($files as $file) {
				if (strpos($file, '_redux.php') !== false) {
					$file_path = $folder_path . '/' . $file;

					// Подключаем файл
					include_once $file_path;

					// Ожидаем, что файл добавляет секцию через Redux::setSection
					$section = include $file_path;

					if (is_array($section)) {
						Redux::setSection($opt_name, $section);
					}
				}
			}
		}
	}
}

// Указываем путь к настройкам темы
$theme_settings_path = get_template_directory() . '/redux-framework/theme-settings';

// Запускаем функцию для добавления секций в redux_demo
add_redux_sections_from_files($theme_settings_path, $opt_name);




// Функция для подключения стилей и скриптов для страницы Redux в админке
function codeweber_admin_styles_scripts()
{
	global $opt_name;
	if (empty($opt_name)) {
		$opt_name = 'redux_demo';
	}
	
	// Проверяем, что текущая страница - это страница Redux настроек
	if (isset($_GET['page']) && ($_GET['page'] === $opt_name || $_GET['page'] === 'redux_demo')) {
		// Подключаем файл стилей только для этой страницы
		wp_enqueue_style('theme-settings-css', get_template_directory_uri() . '/redux-framework/theme-settings/theme-settings.css', false, wp_get_theme()->get('Version'), 'all');
		// Скрытие аккордеонов Footer при типе футера "Кастомный"
		wp_enqueue_script('theme-settings-footer-admin', get_template_directory_uri() . '/redux-framework/theme-settings/footer-admin.js', array('jquery'), wp_get_theme()->get('Version'), true);
	}
}

// Хук для подключения стилей в админке
add_action('admin_enqueue_scripts', 'codeweber_admin_styles_scripts');

if (class_exists('Redux')) {
	// Redux Framework подключён
} else {
	// Redux Framework не подключён
	error_log('Redux Framework не найден.');
}

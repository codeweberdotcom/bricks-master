<?php
// Основной идентификатор Redux
$opt_name = "my_redux_options";

// Проверяем, инициализирован ли Redux
if (! class_exists('Redux')) {
	return;
}

// Устанавливаем основное меню Redux
Redux::setArgs($opt_name, array(
	'opt_name'    => $opt_name,
	'display_name' => 'Тема Настроек',
	'menu_title'  => 'Настройки темы',
	'menu_type'   => 'menu',
	'allow_sub_menu' => false,
	'menu_slug'   => 'theme-options',
));

// 👉 Сюда добавь этот блок 👇
add_action('redux/options/my_redux_options/enqueue', 'theme_settings_custom_styles');

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

// Запускаем функцию
add_redux_sections_from_files($theme_settings_path, $opt_name);

Redux::setSection($opt_name, array(
	'title'      => 'Color Selection',
	'id'         => 'color_selection_section',
	'desc'       => 'Выберите параметры цветов.',
	'icon'       => 'el el-brush',
	'fields'     => array(),  // Параметры основной секции
	'subsections' => array(  // Вложенные субсекции
		array(
			'title'      => 'Color',
			'id'         => 'color_subsection',
			'desc'       => 'Основной цвет.',
			'icon'       => 'el el-paint-brush',
			'fields'     => array(
				array(
					'title'   => 'Основной цвет',
					'id'      => 'main_color',
					'type'    => 'color',
					'default' => '#ff0000',
				),
			),
		),
		array(
			'title'      => 'Color Gradient',
			'id'         => 'color_gradient_subsection',
			'desc'       => 'Градиент цветов.',
			'icon'       => 'el el-gradients',
			'fields'     => array(
				array(
					'title'   => 'Градиент',
					'id'      => 'color_gradient',
					'type'    => 'text',
					'default' => 'linear-gradient(45deg, #ff0000, #00ff00)',
				),
			),
		),
		array(
			'title'      => 'Color RGBA',
			'id'         => 'color_rgba_subsection',
			'desc'       => 'RGBA цвет.',
			'icon'       => 'el el-paint-bucket',
			'fields'     => array(
				array(
					'title'   => 'RGBA значение',
					'id'      => 'color_rgba',
					'type'    => 'text',
					'default' => 'rgba(255, 0, 0, 0.5)',
				),
			),
		),
	),
));




// Функция для подключения стилей и скриптов только для страницы 'my_redux_options_options' в админке
function codeweber_admin_styles_scripts()
{
	// Проверяем, что текущая страница - это 'my_redux_options_options'
	if (isset($_GET['page']) && $_GET['page'] === 'my_redux_options_options') {
		// Подключаем файл стилей только для этой страницы
		wp_enqueue_style('theme-settings-css', get_template_directory_uri() . '/redux-framework/theme-settings/theme-settings.css', false, wp_get_theme()->get('Version'), 'all');
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

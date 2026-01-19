<?php

/**
 * Redux Framework Custom Post Types config.
 * For full documentation, please visit: https://devs.redux.io/
 *
 * @package Redux Framework
 */

defined('ABSPATH') || exit;

/**
 * Возвращает список файлов пользовательских типов записей (CPT),
 * находящихся в директории `/functions/cpt` текущей и дочерней тем.
 *
 * Функция ищет файлы, имена которых начинаются с `cpt-` и заканчиваются на `.php`.
 * Сначала проверяет дочернюю тему, затем родительскую.
 */
if (!function_exists('get_cpt_files_list')) {
    function get_cpt_files_list()
    {
        $files = array();

        // Сначала проверяем дочернюю тему
        $child_directory = get_stylesheet_directory() . '/functions/cpt';
        if (is_dir($child_directory)) {
            $child_files = scandir($child_directory);
            $child_cpt_files = array_filter($child_files, function ($file) use ($child_directory) {
                return is_file($child_directory . '/' . $file)
                    && strpos($file, 'cpt-') === 0
                    && pathinfo($file, PATHINFO_EXTENSION) === 'php';
            });

            foreach ($child_cpt_files as $file) {
                $files[] = $file;
            }
        }

        // Затем проверяем родительскую тему
        $parent_directory = get_template_directory() . '/functions/cpt';
        if (is_dir($parent_directory)) {
            $parent_files = scandir($parent_directory);
            $parent_cpt_files = array_filter($parent_files, function ($file) use ($parent_directory) {
                $is_valid = is_file($parent_directory . '/' . $file)
                    && strpos($file, 'cpt-') === 0
                    && pathinfo($file, PATHINFO_EXTENSION) === 'php';
                return $is_valid;
            });

            // Добавляем только те файлы, которых нет в дочерней теме
            foreach ($parent_cpt_files as $file) {
                if (!in_array($file, $files)) {
                    $files[] = $file;
                }
            }
        }

        return array_values($files);
    }
}

/**
 * Получает список файлов CPT ТОЛЬКО из дочерней темы
 *
 * @return array Массив файлов CPT из дочерней темы
 */
function get_child_cpt_files_list()
{
    $files = array();
    $child_directory = get_stylesheet_directory() . '/functions/cpt';

    if (is_dir($child_directory)) {
        $child_files = scandir($child_directory);
        $child_cpt_files = array_filter($child_files, function ($file) use ($child_directory) {
            return is_file($child_directory . '/' . $file)
                && strpos($file, 'cpt-') === 0
                && pathinfo($file, PATHINFO_EXTENSION) === 'php';
        });

        foreach ($child_cpt_files as $file) {
            $files[] = $file;
        }
    }

    return $files;
}



/**
 * Генерирует массив переключателей (switches) для всех пользовательских типов записей (CPT),
 * основанных на переданном списке файлов. Используется для создания интерфейса
 * управления включением/отключением CPT в настройках (например, через Redux Framework).
 *
 * Исключает из генерации файлы, содержащие определённые слова (например, "header", "footer").
 *
 * @param array $cpt_files Массив имён файлов (например, полученный из get_cpt_files_list()).
 *
 * @return array Массив конфигураций переключателей, подходящих для использования в Redux или других UI-фреймворках.
 */

$excluded_cpt = ['header', 'footer', 'html', 'modal', 'page-header', 'legal', 'notifications'];


function generate_cpt_switches($cpt_files)
{
	$cpt_switches = [];
	$excluded_cpt = ['header', 'footer', 'html', 'modal', 'page-header', 'legal', 'notifications'];

	foreach ($cpt_files as $file) {
		$file_lower = strtolower($file);
		$is_excluded = false;

		foreach ($excluded_cpt as $word) {
			if (str_contains($file_lower, $word)) {
				$is_excluded = true;
				break;
			}
		}

		if ($is_excluded) {
			continue;
		}

		$base_name = str_replace(['cpt-', '.php'], '', $file);
		if (empty($base_name)) {
			$base_name = 'unnamed';
		}

		$display_name = str_replace(['-', '_'], ' ', $base_name);
		$display_name = mb_convert_case($display_name, MB_CASE_TITLE, "UTF-8");
		$translated_label = __($display_name, 'codeweber');
		$option_id = 'cpt_switch_' . sanitize_key($base_name);

		$cpt_switches[] = [
			'id'       => $option_id,
			'type'     => 'switch',
			'title'    => $translated_label,
			'subtitle' => __('Enable/disable this post type', 'codeweber'),
			'default'  => false,
			'on'       => __('Enabled', 'codeweber'),
			'off'      => __('Disabled', 'codeweber'),
			'hint'     => [
				'content' => sprintf(__('File: %s', 'codeweber'), $file),
			]
		];
	}

	return $cpt_switches;
}



/**
 * Получает все опубликованные записи пользовательского типа 'header'
 * и возвращает их в виде ассоциативного массива, где ключом является ID записи,
 * а значением — её заголовок.
 *
 * Эта функция может быть полезна, например, для генерации выпадающего списка
 * в настройках темы или плагина (Redux, ACF и др.).
 *
 * @return array Ассоциативный массив вида [ ID => 'Название записи' ].
 *
 * Пример возвращаемого результата:
 * [
 *     42 => 'Главный хедер',
 *     56 => 'Второстепенный хедер',
 *     ...
 * ]
 */
function get_header_posts()
{
	$args = [
		'post_type'      => 'header',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
	];

	$posts = get_posts($args);
	$options = [];

	foreach ($posts as $post) {
		$options[$post->ID] = $post->post_title;
	}

	return $options;
}



/**
 * Регистрация настроек для пользовательских типов записей через Redux Framework
 *
 * Этот код создает секции настроек для каждого кастомного типа записи,
 * позволяя управлять их отображением и поведением через панель администратора.
 */

// Основной код
$custom_post_type_files = get_cpt_files_list(); // Все файлы из обеих тем
// Добавляем имитацию файлов для стандартных типов записей
$custom_post_type_files[] = 'cpt-post.php';

$child_cpt_files = get_child_cpt_files_list();  // Только файлы из дочерней темы
$excluded_cpt = ['header', 'footer', 'html', 'modal', 'page-header', 'legal', 'notifications'];

if (!empty($custom_post_type_files)) {
	// Фильтруем файлы для генерации переключателей
	$filtered_for_switches = array_filter($custom_post_type_files, function ($file) use ($excluded_cpt) {

		// Исключаем стандартные типы из переключателей
		if (str_contains(strtolower($file), 'blog')) {
			return false;
		}


		foreach ($excluded_cpt as $word) {
			if (str_contains(strtolower($file), $word)) {
				return false;
			}
		}
		return true;
	});
	// Затем используйте $filtered_cpt_files вместо $custom_post_type_files
	// для генерации переключателей:
	Redux::set_section(
		$opt_name,
		[
			'title'      => esc_html__('Custom Post Types', 'codeweber'),
			'id'         => 'custom_post_types_section',
			'subsection' => true,
			'desc'       => esc_html__('Settings for custom post types.', 'codeweber'),
			'fields'     => generate_cpt_switches($filtered_for_switches),
		]
	);

	// Проверяем наличие записей для стандартных типов
	$header_posts = get_posts(array(
		'post_type'      => 'header',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'post_status'    => 'publish'
	));

	$footer_posts = get_posts(array(
		'post_type'      => 'footer',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'post_status'    => 'publish'
	));

	$page_header_posts = get_posts(array(
		'post_type'      => 'page-header',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'post_status'    => 'publish'
	));

	// Формируем сообщения о наличии записей
	$header_message = empty($header_posts)
		? sprintf(
			esc_html__('No custom headers found. You can create one here: %s', 'codeweber'),
			'<a href="' . esc_url(admin_url('edit.php?post_type=header')) . '" target="_blank">' . esc_html__('Create Header', 'codeweber') . '</a>'
		)
		: esc_html__('Select header from available options', 'codeweber');

	$footer_message = empty($footer_posts)
		? sprintf(
			esc_html__('No custom footers found. You can create one here: %s', 'codeweber'),
			'<a href="' . esc_url(admin_url('edit.php?post_type=footer')) . '" target="_blank">' . esc_html__('Create Footer', 'codeweber') . '</a>'
		)
		: esc_html__('Select footer from available options', 'codeweber');

	$page_header_message = empty($page_header_posts)
		? sprintf(
			esc_html__('No custom page headers found. You can create one here: %s', 'codeweber'),
			'<a href="' . esc_url(admin_url('edit.php?post_type=page-header')) . '" target="_blank">' . esc_html__('Create Page Header', 'codeweber') . '</a>'
		)
		: esc_html__('Select page header from available options', 'codeweber');

	// Добавляем WooCommerce, если плагин активен
	if (class_exists('WooCommerce')) {
		$custom_post_type_files[] = 'cpt-woocommerce.php';
	}

	// Создаем настройки для каждого кастомного типа записи
	foreach ($custom_post_type_files as $file) {
		// Получаем базовое имя типа записи из имени файла
		$base_name = str_replace(array('cpt-', '.php'), '', $file);
		if (empty($base_name)) {
			continue;
		}

		// Формируем ID опции для Redux
		$option_id = 'cpt_switch_' . sanitize_key($base_name);

		// Для стандартных типов записей (blog и post) принудительно включаем без проверки переключателя
		if ($file === 'cpt-post.php') {
			$is_enabled = true;
		} else {
			// Проверяем, включен ли этот тип записи для обычных CPT
			$is_enabled = Redux::get_option($opt_name, $option_id);
		}

		// Для legal CPT принудительно включаем без проверки переключателя
		if ($file === 'cpt-legal.php') {
			$is_enabled = true;
		}

		// Пропускаем если тип записи отключен и это не WooCommerce и не стандартные типы
		if (!$is_enabled && $file !== 'cpt-woocommerce.php' && $file !== 'cpt-post.php') {
			continue;
		}



		// ОПРЕДЕЛЯЕМ ОТКУДА ФАЙЛ И ВЫБИРАЕМ ПРАВИЛЬНЫЙ ПУТЬ
		// Для имитированных файлов используем родительскую тему
		if ($file === 'cpt-post.php' ) {
			$is_from_child_theme = false;
		} else {
			$is_from_child_theme = in_array($file, $child_cpt_files);
		}


		$theme_directory = $is_from_child_theme
			? get_stylesheet_directory()  // Путь к дочерней теме
			: get_template_directory();   // Путь к родительской теме

		// Форматируем имя для отображения в интерфейсе
		$display_name = str_replace(array('-', '_'), ' ', $base_name);
		$display_name = mb_convert_case($display_name, MB_CASE_TITLE, 'UTF-8');
		$translated_label = __($display_name, 'codeweber');
		$sanitized_id = sanitize_key($base_name);


		// Для стандартных типов убираем выбор шаблонов, оставляем только остальные настройки
		$is_standard_type = ($file === 'cpt-post.php');

		// ИСПОЛЬЗУЕМ ПРАВИЛЬНЫЙ ПУТЬ ДЛЯ ПОИСКА ШАБЛОНОВ
		// Используем base_name в нижнем регистре для формирования пути к папкам шаблонов
		$template_folder_name = strtolower($base_name);
		$archive_template_directory = $theme_directory . "/templates/archives/{$template_folder_name}";
		$single_template_directory = $theme_directory . "/templates/singles/{$template_folder_name}";

		// Получаем список доступных шаблонов архива
		$archive_template_options = array('default' => esc_html__('Default Template', 'codeweber'));
		if (is_dir($archive_template_directory)) {
			$archive_template_files = scandir($archive_template_directory);
			foreach ($archive_template_files as $template_file) {
				if ($template_file === '.' || $template_file === '..') {
					continue;
				}

				if (is_file($archive_template_directory . '/' . $template_file) && pathinfo($template_file, PATHINFO_EXTENSION) === 'php') {
					$template_name = pathinfo($template_file, PATHINFO_FILENAME);
					$archive_template_options[$template_name] = $template_name;
				}
			}
		}

		// Получаем список доступных шаблонов для single
		$single_template_options = array('default' => esc_html__('Default Template', 'codeweber'));
		if (is_dir($single_template_directory)) {
			$single_template_files = scandir($single_template_directory);
			foreach ($single_template_files as $template_file) {
				// Пропускаем текущую и родительскую директории
				if ($template_file === '.' || $template_file === '..') {
					continue;
				}

				// Проверяем, что это PHP-файл
				$template_path = $single_template_directory . '/' . $template_file;
				if (is_file($template_path) && pathinfo($template_file, PATHINFO_EXTENSION) === 'php') {
					$template_name = pathinfo($template_file, PATHINFO_FILENAME);
					$single_template_options[$template_name] = $template_name;
				}
			}
		}

		// Основные поля секции
		$section_fields = array(
			// Выбор шаблона архива
			array(
				'id'       => 'archive_template_select_' . $sanitized_id,
				'type'     => 'select',
				'title'    => sprintf(esc_html__('Archive Template for %s', 'codeweber'), $translated_label),
				'subtitle' => esc_html__('Select template for archive page', 'codeweber'),
				'options'  => $archive_template_options,
				'default'  => 'default',
			),

			array(
				'id'       => 'single_template_select_' . $sanitized_id,
				'type'     => 'select',
				'title'    => sprintf(esc_html__('Single Template for %s', 'codeweber'), $translated_label),
				'subtitle' => esc_html__('Select template for single page', 'codeweber'),
				'options'  => $single_template_options,
				'default'  => 'default',
			),

			// Пользовательский заголовок
			array(
				'id'       => 'custom_title_' . $sanitized_id,
				'type'     => 'text',
				'title'    => sprintf(esc_html__('Custom Title for %s', 'codeweber'), $translated_label),
				'default'  => '',
			),

			// Пользовательский подзаголовок
			array(
				'id'       => 'custom_subtitle_' . $sanitized_id,
				'type'     => 'textarea',
				'title'    => sprintf(esc_html__('Custom Subtitle for %s', 'codeweber'), $translated_label),
				'default'  => '',
			),
		);

		// Настройки сайдбара
		$sidebar_settings = array(
			array(
				'id'       => 'sidebar_position_single_' . $sanitized_id,
				'type'     => 'button_set',
				'title'    => sprintf(esc_html__('Sidebar Position for Single %s', 'codeweber'), $translated_label),
				'options'  => array(
					'left'   => esc_html__('Left', 'codeweber'),
					'none'   => esc_html__('Disabled', 'codeweber'),
					'right'  => esc_html__('Right', 'codeweber'),
				),
				'default'  => 'right',
			),
			array(
				'id'       => 'sidebar_position_archive_' . $sanitized_id,
				'type'     => 'button_set',
				'title'    => sprintf(esc_html__('Sidebar Position for Archive %s', 'codeweber'), $translated_label),
				'options'  => array(
					'left'   => esc_html__('Left', 'codeweber'),
					'none'   => esc_html__('Disabled', 'codeweber'),
					'right'  => esc_html__('Right', 'codeweber'),
				),
				'default'  => 'right',
			),
		);

		// Получаем все записи header для опций
		$headers = get_posts(array(
			'post_type' => 'header',
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'orderby' => 'title',
			'order' => 'ASC'
		));

		// Создаем базовые опции для header
		$header_options = array(
			'default'  => esc_html__('Default Header', 'codeweber'),
			'disable'  => esc_html__('Disable - Hide Header', 'codeweber'),
		);

		// Добавляем созданные записи
		foreach ($headers as $header) {
			$header_options[$header->ID] = $header->post_title;
		}

		// Настройки хедера
		$header_settings = array(
			array(
				'id'       => 'single_header_select_' . $sanitized_id,
				'type'     => 'select',
				'title'    => sprintf(esc_html__('Header for Single %s', 'codeweber'), $translated_label),
				'desc'     => $header_message,
				'options'  => $header_options,
				'default'  => 'default',
			),
			array(
				'id'       => 'archive_header_select_' . $sanitized_id,
				'type'     => 'select',
				'title'    => sprintf(esc_html__('Header for Archive %s', 'codeweber'), $translated_label),
				'desc'     => $header_message,
				'options'  => $header_options,
				'default'  => 'default',
			),
		);

		// Получаем все записи footer для опций
		$footers = get_posts(array(
			'post_type' => 'footer',
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'orderby' => 'title',
			'order' => 'ASC'
		));

		// Создаем базовые опции для footer
		$footer_options = array(
			'default'  => esc_html__('Default Footer', 'codeweber'),
			'disable'  => esc_html__('Disable - Hide Footer', 'codeweber'),
		);

		// Добавляем созданные записи
		foreach ($footers as $footer) {
			$footer_options[$footer->ID] = $footer->post_title;
		}

		// Настройки футера
		$footer_settings = array(
			array(
				'id'       => 'single_footer_select_' . $sanitized_id,
				'type'     => 'select',
				'title'    => sprintf(esc_html__('Footer for Single %s', 'codeweber'), $translated_label),
				'desc'     => $footer_message,
				'options'  => $footer_options,
				'default'  => 'default',
			),
			array(
				'id'       => 'archive_footer_select_' . $sanitized_id,
				'type'     => 'select',
				'title'    => sprintf(esc_html__('Footer for Archive %s', 'codeweber'), $translated_label),
				'desc'     => $footer_message,
				'options'  => $footer_options,
				'default'  => 'default',
			),
		);

		// Сначала получаем все записи page-header
		$page_headers = get_posts(array(
			'post_type' => 'page-header',
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'orderby' => 'title',
			'order' => 'ASC'
		));

		// Создаем базовые опции
		$page_header_options = array(
			'default'  => esc_html__('Default Page Header', 'codeweber'),
			'disabled' => esc_html__('Disable - Hide Page Header', 'codeweber'),
		);

		// Добавляем созданные записи
		foreach ($page_headers as $header) {
			$page_header_options[$header->ID] = $header->post_title;
		}

		// Настройки заголовка страницы
		$page_header_settings = array(
			array(
				'id'       => 'single_page_header_select_' . $sanitized_id,
				'type'     => 'select',
				'title'    => sprintf(esc_html__('Page Header for Single %s', 'codeweber'), $translated_label),
				'desc'     => $page_header_message,
				'options'  => $page_header_options,
				'default'  => 'default',
			),
			array(
				'id'       => 'archive_page_header_select_' . $sanitized_id,
				'type'     => 'select',
				'title'    => sprintf(esc_html__('Page Header for Archive %s', 'codeweber'), $translated_label),
				'desc'     => $page_header_message,
				'options'  => $page_header_options,
				'default'  => 'default',
			),
		);

		// Добавляем все настройки в виде табов
		$section_fields[] = array(
			'id'     => 'sidebar_settings_' . $sanitized_id,
			'type'   => 'tabbed',
			'title'  => sprintf(esc_html__('%s Sidebar Settings', 'codeweber'), $translated_label),
			'tabs'   => array(
				array(
					'title'  => esc_html__('Single', 'codeweber'),
					'fields' => array($sidebar_settings[0]),
				),
				array(
					'title'  => esc_html__('Archive', 'codeweber'),
					'fields' => array($sidebar_settings[1]),
				),
			),
		);

		$section_fields[] = array(
			'id'     => 'header_settings_' . $sanitized_id,
			'type'   => 'tabbed',
			'title'  => sprintf(esc_html__('%s Header Settings', 'codeweber'), $translated_label),
			'tabs'   => array(
				array(
					'title'  => esc_html__('Single', 'codeweber'),
					'fields' => array($header_settings[0]),
				),
				array(
					'title'  => esc_html__('Archive', 'codeweber'),
					'fields' => array($header_settings[1]),
				),
			),
		);

		$section_fields[] = array(
			'id'     => 'footer_settings_' . $sanitized_id,
			'type'   => 'tabbed',
			'title'  => sprintf(esc_html__('%s Footer Settings', 'codeweber'), $translated_label),
			'tabs'   => array(
				array(
					'title'  => esc_html__('Single', 'codeweber'),
					'fields' => array($footer_settings[0]),
				),
				array(
					'title'  => esc_html__('Archive', 'codeweber'),
					'fields' => array($footer_settings[1]),
				),
			),
		);

		$section_fields[] = array(
			'id'     => 'page_header_settings_' . $sanitized_id,
			'type'   => 'tabbed',
			'title'  => sprintf(esc_html__('%s Page Header Settings', 'codeweber'), $translated_label),
			'tabs'   => array(
				array(
					'title'  => esc_html__('Single', 'codeweber'),
					'fields' => array($page_header_settings[0]),
				),
				array(
					'title'  => esc_html__('Archive', 'codeweber'),
					'fields' => array($page_header_settings[1]),
				),
			),
		);

		// Регистрируем секцию настроек для этого типа записи
		Redux::set_section(
			$opt_name,
			array(
				'title'      => $translated_label,
				'id'         => 'custom_post_type_section_' . $sanitized_id,
				'subsection' => true,
				'fields'     => $section_fields,
			)
		);
	}
} else {
	error_log('No custom post type files found in the cpt directory.');
}

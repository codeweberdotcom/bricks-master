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
 * находящихся в директории `/functions/cpt` текущей темы.
 *
 * Функция ищет файлы, имена которых начинаются с `cpt-` и заканчиваются на `.php`.
 * Это полезно для автоматического подключения CPT-файлов по шаблону.
 *
 * Пример имени файла: `cpt-faq.php`, `cpt-portfolio.php`.
 *
 * @return array Массив имён файлов (без путей), соответствующих критериям.
 *               Возвращает пустой массив, если директория не существует или нет подходящих файлов.
 */
if (! function_exists('get_cpt_files_list')) {
	function get_cpt_files_list()
	{
		$directory = get_template_directory() . '/functions/cpt';

		if (is_dir($directory)) {
			$files = scandir($directory);

			// Фильтруем только те, что начинаются с "cpt-" и имеют расширение ".php"
			$cpt_files = array_filter($files, function ($file) use ($directory) {
				$is_valid = is_file($directory . '/' . $file)
					&& strpos($file, 'cpt-') === 0
					&& pathinfo($file, PATHINFO_EXTENSION) === 'php';
				return $is_valid;
			});

			return array_values($cpt_files); // Возвращаем отфильтрованный список
		}

		return [];
	}
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
function generate_cpt_switches($cpt_files)
{
	$cpt_switches = [];
	$excluded_words = ['header', 'footer', 'html', 'modal', 'docs', 'price', 'page-header'];

	foreach ($cpt_files as $file) {
		// 1. Проверка на исключения
		$file_lower = strtolower($file);
		foreach ($excluded_words as $word) {
			if (str_contains($file_lower, $word)) {
				continue 2;
			}
		}

		// 2. Безопасное извлечение и форматирование имени
		$base_name = str_replace(['cpt-', '.php'], '', $file);
		if (empty($base_name)) {
			$base_name = 'unnamed';
		}

		// 3. Форматирование отображаемого названия
		$display_name = str_replace(['-', '_'], ' ', $base_name);
		$display_name = mb_convert_case($display_name, MB_CASE_TITLE, "UTF-8");

		// 4. Перевод с сохранением заглавной буквы
		$translated_label = __($display_name, 'codeweber');

		// 5. Формирование ID с санитизацией
		$option_id = 'cpt_switch_' . sanitize_key($base_name);

		$cpt_switches[] = [
			'id'       => $option_id,
			'type'     => 'switch',
			'title'    => $translated_label, // Уже с заглавной буквы
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
	$args = array(
		'post_type'      => 'header',       // Пользовательский тип записи
		'posts_per_page' => -1,             // Получить все записи без ограничения
		'post_status'    => 'publish',      // Только опубликованные записи
	);

	$posts = get_posts($args);

	$options = [];
	foreach ($posts as $post) {
		$options[$post->ID] = $post->post_title; // ID и заголовок каждой записи
	}

	return $options;
}



/**
 * Регистрация настроек для пользовательских типов записей через Redux Framework
 *
 * Этот код создает секции настроек для каждого кастомного типа записи,
 * позволяя управлять их отображением и поведением через панель администратора.
 */

// Получаем список всех файлов с кастомными типами записей
$custom_post_type_files = get_cpt_files_list();

// Проверяем, есть ли файлы с кастомными типами записей
if (!empty($custom_post_type_files)) {
    // Добавляем основную секцию для настройки кастомных типов записей
    Redux::set_section(
        $opt_name,
        array(
            'title'      => esc_html__('Custom Post Types', 'codeweber'),
            'id'         => 'custom_post_types_section',
            'subsection' => true,
            'desc'       => esc_html__('Settings for custom post types.', 'codeweber'),
            'fields'     => generate_cpt_switches($custom_post_type_files),
        )
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

        // Проверяем, включен ли этот тип записи
        $is_enabled = Redux::get_option($opt_name, $option_id);

        // Пропускаем если тип записи отключен и это не WooCommerce
        if (!$is_enabled && $file !== 'cpt-woocommerce.php') {
            continue;
        }

        // Форматируем имя для отображения в интерфейсе
        $display_name = str_replace(array('-', '_'), ' ', $base_name);
        $display_name = mb_convert_case($display_name, MB_CASE_TITLE, 'UTF-8');
        $translated_label = __($display_name, 'codeweber');
        $sanitized_id = sanitize_key($base_name);

        // Получаем список доступных шаблонов
        $archive_template_options = array('default' => esc_html__('Default Template', 'codeweber'));
        $archive_template_directory = get_template_directory() . "/templates/archives/{$display_name}";

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
		$single_template_directory = get_template_directory() . "/templates/singles/{$display_name}";

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

        // Настройки хедера
        $header_settings = array(
            array(
                'id'       => 'single_header_select_' . $sanitized_id,
                'type'     => 'select',
                'title'    => sprintf(esc_html__('Header for Single %s', 'codeweber'), $translated_label),
                'desc'     => $header_message,
                'data'     => 'posts',
                'args'     => array(
                    'post_type' => 'header',
                    'posts_per_page' => -1,
                ),
            ),
            array(
                'id'       => 'archive_header_select_' . $sanitized_id,
                'type'     => 'select',
                'title'    => sprintf(esc_html__('Header for Archive %s', 'codeweber'), $translated_label),
                'desc'     => $header_message,
                'data'     => 'posts',
                'args'     => array(
                    'post_type' => 'header',
                    'posts_per_page' => -1,
                ),
            ),
        );

        // Настройки футера
        $footer_settings = array(
            array(
                'id'       => 'single_footer_select_' . $sanitized_id,
                'type'     => 'select',
                'title'    => sprintf(esc_html__('Footer for Single %s', 'codeweber'), $translated_label),
                'desc'     => $footer_message,
                'data'     => 'posts',
                'args'     => array(
                    'post_type' => 'footer',
                    'posts_per_page' => -1,
                ),
            ),
            array(
                'id'       => 'archive_footer_select_' . $sanitized_id,
                'type'     => 'select',
                'title'    => sprintf(esc_html__('Footer for Archive %s', 'codeweber'), $translated_label),
                'desc'     => $footer_message,
                'data'     => 'posts',
                'args'     => array(
                    'post_type' => 'footer',
                    'posts_per_page' => -1,
                ),
            ),
        );

        // Настройки заголовка страницы
        $page_header_settings = array(
            array(
                'id'       => 'single_page_header_select_' . $sanitized_id,
                'type'     => 'select',
                'title'    => sprintf(esc_html__('Page Header for Single %s', 'codeweber'), $translated_label),
                'desc'     => $page_header_message,
                'data'     => 'posts',
                'args'     => array(
                    'post_type' => 'page-header',
                    'posts_per_page' => -1,
                ),
            ),
            array(
                'id'       => 'archive_page_header_select_' . $sanitized_id,
                'type'     => 'select',
                'title'    => sprintf(esc_html__('Page Header for Archive %s', 'codeweber'), $translated_label),
                'desc'     => $page_header_message,
                'data'     => 'posts',
                'args'     => array(
                    'post_type' => 'page-header',
                    'posts_per_page' => -1,
                ),
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

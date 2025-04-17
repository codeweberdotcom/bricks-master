<?php

/**
 * Redux Framework Custom Post Types config.
 * For full documentation, please visit: https://devs.redux.io/
 *
 * @package Redux Framework
 */

defined('ABSPATH') || exit;

// Проверяем, существует ли уже функция get_cpt_files_list
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


// Функция для получения записей типа 'product' и 'shop'
function get_woocommerce_post_types()
{
	// Массив для типов записей WooCommerce
	return [
		'product' => __('Product', 'codeweber'),
		'shop'    => __('Shop', 'codeweber'),
	];
}


// Функция для генерации переключателей для всех CPT
function generate_cpt_switches($cpt_files)
{
	$cpt_switches = [];

	// Список слов для исключения
	$excluded_words = ['header', 'footer', 'html', 'modal', 'docs', 'price', 'page-header'];



	foreach ($cpt_files as $file) {
		// Проверяем, содержит ли файл слова из списка исключений
		foreach ($excluded_words as $word) {
			if (stripos($file, $word) !== false) {
				continue 2; // Пропускаем текущий файл и переходим к следующему
			}
		}

		// Преобразуем имя файла в читаемый формат
		$label = ucwords(str_replace(array('cpt-', '.php'), '', $file));

		// Убедимся, что у нас есть заголовок
		$label = $label ?: __('Unnamed', 'codeweber'); // Если метка пуста, то используем "Unnamed"

		// Переводим $label перед использованием в интерфейсе
		$translated_label = __(ucwords(str_replace(array('cpt-', '.php'), '', $file)), 'codeweber');

		// Добавляем переключатель для каждого CPT
		$cpt_switches[] = array(
			'id'       => 'cpt_switch_' . $translated_label, // Уникальный ID для переключателя
			'type'     => 'switch', // Тип поля: переключатель
			'title'    => $translated_label, // Переводим метку
			'subtitle' => __('Enable/disable this post type', 'codeweber'), // Описание переключателя
			'default'  => false, // По умолчанию выключено
			'on'       => __('Enabled', 'codeweber'), // Переведённая строка для включения
			'off'      => __('Disabled', 'codeweber'), // Переведённая строка для выключения
		);
	}

	return $cpt_switches;
}


// Функция для получения записей типа 'header'
function get_header_posts()
{
	$args = array(
		'post_type'      => 'header', // Тип записи
		'posts_per_page' => -1, // Получить все записи
		'post_status'    => 'publish', // Только опубликованные записи
	);

	$posts = get_posts($args);

	$options = [];
	foreach ($posts as $post) {
		$options[$post->ID] = $post->post_title; // Добавляем ID и название записи в массив
	}

	return $options;
}

// Получаем список файлов с кастомными типами записей
$cpt_list = get_cpt_files_list();

// Проверяем, есть ли файлы CPT
if (!empty($cpt_list)) {
	// Добавляем основную секцию для настройки CPT
	Redux::set_section(
		$opt_name,
		array(
			'title'            => esc_html__('Custom Post Types', 'codeweber'),
			'id'               => 'cpt_section',
			'subsection'       => true, // Не создаем субсекцию для этой секции
			'desc'             => esc_html__('Settings for custom post types.', 'codeweber'),
			'fields'           => generate_cpt_switches($cpt_list), // Добавляем все переключатели для CPT
		)
	);


	// Проверка наличия записей типа "header"
	$header_posts = get_posts(array(
		'post_type'      => 'header',
		'posts_per_page' => -1, // Проверяем только наличие хотя бы одной записи
	));

	// Проверка наличия записей типа "header"
	$footer_posts = get_posts(array(
		'post_type'      => 'footer',
		'posts_per_page' => -1, // Проверяем только наличие хотя бы одной записи
	));

	// Проверка наличия записей типа "header"
	$page_header_posts = get_posts(array(
		'post_type'      => 'page-header',
		'posts_per_page' => -1, // Проверяем только наличие хотя бы одной записи
	));

	$no_headers_message = '';
	if (empty($header_posts)) {
		$no_headers_message = sprintf(
			esc_html__('No custom headers found. You can create one by visiting the following link: %s', 'codeweber'),
			'<a href="' . esc_url(admin_url('edit.php?post_type=header')) . '" target="_blank">' . esc_html__('Create Custom Header', 'codeweber') . '</a>'
		);
	}else{
		$no_headers_message = esc_html__('Select Header from Custom Headers', 'codeweber');
	}

	$no_footers_message = '';
	if (empty($footer_posts)) {
		$no_footers_message = sprintf(
			esc_html__('No custom footers found. You can create one by visiting the following link: %s', 'codeweber'),
			'<a href="' . esc_url(admin_url('edit.php?post_type=footer')) . '" target="_blank">' . esc_html__('Create Custom Footer', 'codeweber') . '</a>'
		);
	} else {
		$no_footers_message = esc_html__('Select Footer from Custom Headers', 'codeweber');
	}

	$no_page_header_message = '';
	if (empty($page_header_posts)) {
		$no_page_header_message = sprintf(
			esc_html__('No custom page-header found. You can create one by visiting the following link: %s', 'codeweber'),
			'<a href="' . esc_url(admin_url('edit.php?post_type=page-header')) . '" target="_blank">' . esc_html__('Create Custom Page_header', 'codeweber') . '</a>'
		);
	} else {
		$no_page_header_message = esc_html__('Select Page-header from Custom Headers', 'codeweber');
	}


	if (class_exists('WooCommerce')) {
		array_push($cpt_list, 'cpt-woocommerce.php');
	}

	// Пройдемся по всем CPT и создадим субсекции для включенных
	foreach ($cpt_list as $file) {
		$label = ucwords(str_replace(array('cpt-', '.php'), '', $file)); // Преобразуем имя файла в читаемый формат
		$label = $label ?: __('Unnamed', 'codeweber'); // Убедимся, что есть метка

		// Переводим $label
		$translated_label = __(ucwords(str_replace(array('cpt-', '.php'), '', $file)), 'codeweber');

		// Проверяем, включен ли CPT (переключатель с заданным ID)
		$option_id = 'cpt_switch_' . $translated_label;
		$is_enabled = Redux::get_option($opt_name, $option_id); // Получаем значение опции для этого CPT


		// Если включено, создаем субсекцию для этого CPT
		if ($is_enabled || $file === 'cpt-woocommerce.php') {


			$path = get_template_directory() . "/templates/archives/{$label}";
			$options = [];

			if (is_dir($path)) {
				$files = scandir($path);

				foreach ($files as $file) {
					if ($file === '.' || $file === '..') {
						continue;
					}

					if (is_file("$path/$file") && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
						$filename = pathinfo($file, PATHINFO_FILENAME); // без .php
						$options[$filename] = $filename;
					}
				}
			} else {
				$options['none'] = 'Папка не найдена';
			}



	Redux::set_section(
	$opt_name,
	array(
		'title'      => esc_html__($translated_label, 'codeweber' ),
		'id'         => 'additional-tabbed' . $translated_label,
		'desc'       => sprintf(esc_html__('Here you can make settings for the custom post type %s', 'codeweber'), $translated_label), // Переводим метку в заголовке
		'subsection' => true,
		'fields'     => array(
						array(
							'id'       => 'opt-select' . $translated_label,
							'type'     => 'select',
							'title'    => esc_html__('Select Archive Template ' . $translated_label, 'codeweber'),
							'subtitle' => esc_html__('Выберите шаблон из templates/archives/' . $label, 'codeweber'),
							'desc'     => esc_html__('Список файлов из директории archives/' . $label, 'codeweber'),
							'options'  => $options,
							'default'  => array_key_first($options),
						),
			array(
				'id'       => 'cpt-custom-title' . $translated_label,
				'type'     => 'text',
				'title'    => sprintf(esc_html__('Custom Title for %s', 'codeweber'), $translated_label),
				'default'  => '',
				),
			array(
				'id'    => 'cpt-sidebar-settings-' . $translated_label,
				'type'  => 'tabbed',
				'title' => sprintf(esc_html__('%s Settings Sidebar', 'codeweber'), $translated_label), // Переводим метку в заголовке
				'tabs'  => array(
								array(
					'title'            => sprintf(esc_html__('%s Single Sidebar Settings', 'codeweber'), $translated_label), // Переводим метку в заголовке
					'id'               => 'cpt-single-sidebar-settings-' . $translated_label,
					'subsection'       => true, // Устанавливаем как субсекцию
					'desc'             => sprintf(esc_html__('Settings for the Single Sidebar %s custom post type.', 'codeweber'), $translated_label),
					'fields'           => array(

						// Управление сайдбаром
						array(
							'id'       => 'sidebar-position-single-' . $translated_label,
							'type'     => 'button_set',
							'title'    => sprintf(esc_html__('Select Sidebar position for Single %s', 'codeweber'), $translated_label),
							'desc' => sprintf(esc_html__('This is the Sidebar position for Single %s', 'codeweber'), $translated_label),
							'options'  => array(
								'1' => esc_html__('Left Sidebar', 'codeweber'),
								'2' => esc_html__('Disable Sidebar', 'codeweber'),
								'3' => esc_html__('Right Sidebar', 'codeweber'),
							),
							'default'  => '1',
						),
					),
				),

								array(
									'title'            => sprintf(esc_html__('%s Archive Sidebar Settings', 'codeweber'), $translated_label), // Переводим метку в заголовке
									'id'               => 'cpt-archive-sidebar-settings-' . $translated_label,
									'subsection'       => true, // Устанавливаем как субсекцию
									'desc'             => sprintf(esc_html__('Settings for the Archive Sidebar %s custom post type.', 'codeweber'), $translated_label),
									'fields'           => array(

										array(
											'id'       => 'sidebar-position-archive-' . $translated_label,
											'type'     => 'button_set',
											'title'    => sprintf(esc_html__('Select Sidebar position for Archive %s', 'codeweber'), $translated_label),
											'desc' => sprintf(esc_html__('This is the Sidebar position for Archive %s', 'codeweber'), $translated_label),
											'options'  => array(
												'1' => esc_html__('Left Sidebar', 'codeweber'),
												'2' => esc_html__('Disable Sidebar', 'codeweber'),
												'3' => esc_html__('Right Sidebar', 'codeweber'),
											),
											'default'  => '1',
										),
									),
								),

				),
			),


						array(
							'id'    => 'cpt-header-settings-' . $translated_label,
							'type'  => 'tabbed',
							'title' => sprintf(esc_html__('%s Settings Header', 'codeweber'), $translated_label), // Переводим метку в заголовке
							'tabs'  => array(
								array(
									'title'            => sprintf(esc_html__('%s Single Header Settings', 'codeweber'), $translated_label), // Переводим метку в заголовке
									'id'               => 'cpt-single-header-settings-' . $translated_label,
									'subsection'       => true, // Устанавливаем как субсекцию
									'desc'             => sprintf(esc_html__('Settings for the Single Header %s custom post type.', 'codeweber'), $translated_label),
									'fields'           => array(

										// Добавляем выпадающий список для выбора записей типа header
										array(
											'id'       => 'cpt-single-post-header-' . $translated_label,
											'type'     => 'select',
											'title'    => sprintf(esc_html__('Select Header for Single %s', 'codeweber'), $translated_label),
											'desc'     => $no_headers_message, // Выводим сообщение, если записей нет
											'data'     => 'posts',
											'args'     => array(
												'post_type' => 'header',
												'posts_per_page' => -1,
											),
											'default'  => '',

										),


									),
								),

								array(
									'title'            => sprintf(esc_html__('%s Archive Header Settings', 'codeweber'), $translated_label), // Переводим метку в заголовке
									'id'               => 'cpt-archive-header-settings-' . $translated_label,
									'subsection'       => true, // Устанавливаем как субсекцию
									'desc'             => sprintf(esc_html__('Settings for the Archive Header %s custom post type.', 'codeweber'), $translated_label),
									'fields'           => array(

										array(
											'id'       => 'cpt-archive-post-header-' . $translated_label,
											'type'     => 'select',
											'title'    => sprintf(esc_html__('Select Header for Archive %s', 'codeweber'), $translated_label),
											'desc'     => $no_headers_message,
											'data'     => 'posts',
											'args'     => array(
												'post_type' => 'header',
												'posts_per_page' => -1,
											),
											'default'  => '',

										),


									),
								),

							),
						),




						array(
							'id'    => 'cpt-page-header-settings-' . $translated_label,
							'type'  => 'tabbed',
							'title' => sprintf(esc_html__('%s Settings Page Header', 'codeweber'), $translated_label), // Переводим метку в заголовке
							'tabs'  => array(
								array(
									'title'            => sprintf(esc_html__('%s Single Page Header Settings', 'codeweber'), $translated_label), // Переводим метку в заголовке
									'id'               => 'cpt-single-page-header-settings-' . $translated_label,
									'subsection'       => true, // Устанавливаем как субсекцию
									'desc'             => sprintf(esc_html__('Settings for the Single Page Header %s custom post type.', 'codeweber'), $translated_label),
									'fields'           => array(

										// Добавляем выпадающий список для выбора записей типа header
										array(
											'id'       => 'cpt-single-post-page-header-' . $translated_label,
											'type'     => 'select',
											'title'    => sprintf(esc_html__('Select Header for Single %s', 'codeweber'), $translated_label),
											'desc'     => $no_page_header_message, // Выводим сообщение, если записей нет
											'data'     => 'posts',
											'args'     => array(
												'post_type' => 'page-header',
												'posts_per_page' => -1,
											),
											'default'  => '',

										),


									),
								),

								array(
									'title'            => sprintf(esc_html__('%s Archive Page Header Settings', 'codeweber'), $translated_label), // Переводим метку в заголовке
									'id'               => 'cpt-archive-page-header-settings-' . $translated_label,
									'subsection'       => true, // Устанавливаем как субсекцию
									'desc'             => sprintf(esc_html__('Settings for the Archive Page Header %s custom post type.', 'codeweber'), $translated_label),
									'fields'           => array(

										array(
											'id'       => 'cpt-archive-post-page-header-' . $translated_label,
											'type'     => 'select',
											'title'    => sprintf(esc_html__('Select Page Header for Archive %s', 'codeweber'), $translated_label),
											'desc'     => $no_page_header_message,
											'data'     => 'posts',
											'args'     => array(
												'post_type' => 'page-header',
												'posts_per_page' => -1,
											),
											'default'  => '',

										),


									),
								),

							),
						),



						array(
							'id'    => 'cpt-footer-settings-' . $translated_label,
							'type'  => 'tabbed',
							'title' => sprintf(esc_html__('%s Settings Footer', 'codeweber'), $translated_label), // Переводим метку в заголовке
							'tabs'  => array(
								array(
									'title'            => sprintf(esc_html__('%s Single Footer Settings', 'codeweber'), $translated_label), // Переводим метку в заголовке
									'id'               => 'cpt-single-footer-settings-' . $translated_label,
									'subsection'       => true, // Устанавливаем как субсекцию
									'desc'             => sprintf(esc_html__('Settings for the Single Footer %s custom post type.', 'codeweber'), $translated_label),
									'fields'           => array(

										// Добавляем выпадающий список для выбора записей типа header
										array(
											'id'       => 'cpt-single-post-footer-' . $translated_label,
											'type'     => 'select',
											'title'    => sprintf(esc_html__('Select Footer for Single %s', 'codeweber'), $translated_label),
											'desc'     => $no_footers_message, // Выводим сообщение, если записей нет
											'data'     => 'posts',
											'args'     => array(
												'post_type' => 'footer',
												'posts_per_page' => -1,
											),
											'default'  => '',

										),
									),
								),

								array(
									'title'            => sprintf(esc_html__('%s Archive Footer Settings', 'codeweber'), $translated_label), // Переводим метку в заголовке
									'id'               => 'cpt-archive-footer-settings-' . $translated_label,
									'subsection'       => true, // Устанавливаем как субсекцию
									'desc'             => sprintf(esc_html__('Settings for the Archive Footer %s custom post type.', 'codeweber'), $translated_label),
									'fields'           => array(

										array(
											'id'       => 'cpt-archive-post-footer-' . $translated_label,
											'type'     => 'select',
											'title'    => sprintf(esc_html__('Select Footer for Archive %s', 'codeweber'), $translated_label),
											'desc'     => $no_footers_message,
											'data'     => 'posts',
											'args'     => array(
												'post_type' => 'footer',
												'posts_per_page' => -1,
											),
											'default'  => '',

										),
									),
								),

							),
						),
		),
	)
);
		}
	}
} else {
	error_log('No CPT files found.');
}

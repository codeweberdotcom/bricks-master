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
 *
 * Пример возвращаемого элемента:
 * [
 *   'id'       => 'cpt_switch_Faq',
 *   'type'     => 'switch',
 *   'title'    => 'Faq',
 *   'subtitle' => 'Enable/disable this post type',
 *   'default'  => false,
 *   'on'       => 'Enabled',
 *   'off'      => 'Disabled'
 * ]
 */
function generate_cpt_switches($cpt_files)
{
	$cpt_switches = [];

	// Список слов для исключения
	$excluded_words = ['header', 'footer', 'html', 'modal', 'docs', 'price', 'page-header'];

	foreach ($cpt_files as $file) {
		// Пропускаем файлы, содержащие исключённые слова
		foreach ($excluded_words as $word) {
			if (stripos($file, $word) !== false) {
				continue 2;
			}
		}

		// Преобразуем имя файла в человекочитаемый формат
		$label = ucwords(str_replace(['cpt-', '.php'], '', $file));
		$label = $label ?: __('Unnamed', 'codeweber');

		// Переводим метку
		$translated_label = __($label, 'codeweber');

		// Добавляем элемент переключателя
		$cpt_switches[] = [
			'id'       => 'cpt_switch_' . $translated_label,
			'type'     => 'switch',
			'title'    => $translated_label,
			'subtitle' => __('Enable/disable this post type', 'codeweber'),
			'default'  => false,
			'on'       => __('Enabled', 'codeweber'),
			'off'      => __('Disabled', 'codeweber'),
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
		'title'      => esc_html__($label, 'codeweber' ),
		'id'         => 'additional-tabbed' . $label,
		'desc'       => sprintf(esc_html__('Here you can make settings for the custom post type %s', 'codeweber'), $label), // Переводим метку в заголовке
		'subsection' => true,
		'fields'     => array(
						array(
							'id'       => 'opt-select' . $label,
							'type'     => 'select',
							'title'    => esc_html__('Select Archive Template ' . $label, 'codeweber'),
							'subtitle' => esc_html__('Выберите шаблон из templates/archives/' . $label, 'codeweber'),
							'desc'     => esc_html__('Список файлов из директории archives/' . $label, 'codeweber'),
							'options'  => $options,
							'default'  => array_key_first($options),
						),
			array(
				'id'       => 'cpt-custom-title' . $label,
				'type'     => 'text',
				'title'    => sprintf(esc_html__('Custom Title for %s', 'codeweber'), $label),
				'default'  => '',
				),

			array(
				'id'       => 'cpt-custom-sub-title' . $label,
				'type'     => 'textarea',
				'title'    =>  sprintf(esc_html__('Custom SubTitle for %s', 'codeweber'), $label),
				'default'  => '',
			),

			array(
				'id'    => 'cpt-sidebar-settings-' . $label,
				'type'  => 'tabbed',
				'title' => sprintf(esc_html__('%s Settings Sidebar', 'codeweber'), $label), // Переводим метку в заголовке
				'tabs'  => array(
								array(
					'title'            => sprintf(esc_html__('%s Single Sidebar Settings', 'codeweber'), $label), // Переводим метку в заголовке
					'id'               => 'cpt-single-sidebar-settings-' . $label,
					'subsection'       => true, // Устанавливаем как субсекцию
					'desc'             => sprintf(esc_html__('Settings for the Single Sidebar %s custom post type.', 'codeweber'), $label),
					'fields'           => array(

						// Управление сайдбаром
						array(
							'id'       => 'sidebar-position-single-' . $label,
							'type'     => 'button_set',
							'title'    => sprintf(esc_html__('Select Sidebar position for Single %s', 'codeweber'), $label),
							'desc' => sprintf(esc_html__('This is the Sidebar position for Single %s', 'codeweber'), $label),
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
									'title'            => sprintf(esc_html__('%s Archive Sidebar Settings', 'codeweber'), $label), // Переводим метку в заголовке
									'id'               => 'cpt-archive-sidebar-settings-' . $label,
									'subsection'       => true, // Устанавливаем как субсекцию
									'desc'             => sprintf(esc_html__('Settings for the Archive Sidebar %s custom post type.', 'codeweber'), $label),
									'fields'           => array(

										array(
											'id'       => 'sidebar-position-archive-' . $label,
											'type'     => 'button_set',
											'title'    => sprintf(esc_html__('Select Sidebar position for Archive %s', 'codeweber'), $label),
											'desc' => sprintf(esc_html__('This is the Sidebar position for Archive %s', 'codeweber'), $label),
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
							'id'    => 'cpt-header-settings-' . $label,
							'type'  => 'tabbed',
							'title' => sprintf(esc_html__('%s Settings Header', 'codeweber'), $label), // Переводим метку в заголовке
							'tabs'  => array(
								array(
									'title'            => sprintf(esc_html__('%s Single Header Settings', 'codeweber'), $label), // Переводим метку в заголовке
									'id'               => 'cpt-single-header-settings-' . $label,
									'subsection'       => true, // Устанавливаем как субсекцию
									'desc'             => sprintf(esc_html__('Settings for the Single Header %s custom post type.', 'codeweber'), $label),
									'fields'           => array(

										// Добавляем выпадающий список для выбора записей типа header
										array(
											'id'       => 'cpt-single-post-header-' . $label,
											'type'     => 'select',
											'title'    => sprintf(esc_html__('Select Header for Single %s', 'codeweber'), $label),
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
									'title'            => sprintf(esc_html__('%s Archive Header Settings', 'codeweber'), $label), // Переводим метку в заголовке
									'id'               => 'cpt-archive-header-settings-' . $label,
									'subsection'       => true, // Устанавливаем как субсекцию
									'desc'             => sprintf(esc_html__('Settings for the Archive Header %s custom post type.', 'codeweber'), $label),
									'fields'           => array(

										array(
											'id'       => 'cpt-archive-post-header-' . $label,
											'type'     => 'select',
											'title'    => sprintf(esc_html__('Select Header for Archive %s', 'codeweber'), $label),
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
							'id'    => 'cpt-page-header-settings-' . $label,
							'type'  => 'tabbed',
							'title' => sprintf(esc_html__('%s Settings Page Header', 'codeweber'), $label), // Переводим метку в заголовке
							'tabs'  => array(
								array(
									'title'            => sprintf(esc_html__('%s Single Page Header Settings', 'codeweber'), $label), // Переводим метку в заголовке
									'id'               => 'cpt-single-page-header-settings-' . $label,
									'subsection'       => true, // Устанавливаем как субсекцию
									'desc'             => sprintf(esc_html__('Settings for the Single Page Header %s custom post type.', 'codeweber'), $label),
									'fields'           => array(

										// Добавляем выпадающий список для выбора записей типа header
										array(
											'id'       => 'cpt-single-post-page-header-' . $label,
											'type'     => 'select',
											'title'    => sprintf(esc_html__('Select Header for Single %s', 'codeweber'), $label),
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
									'title'            => sprintf(esc_html__('%s Archive Page Header Settings', 'codeweber'), $label), // Переводим метку в заголовке
									'id'               => 'cpt-archive-page-header-settings-' . $label,
									'subsection'       => true, // Устанавливаем как субсекцию
									'desc'             => sprintf(esc_html__('Settings for the Archive Page Header %s custom post type.', 'codeweber'), $label),
									'fields'           => array(

										array(
											'id'       => 'cpt-archive-post-page-header-' . $label,
											'type'     => 'select',
											'title'    => sprintf(esc_html__('Select Page Header for Archive %s', 'codeweber'), $label),
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
							'id'    => 'cpt-footer-settings-' . $label,
							'type'  => 'tabbed',
							'title' => sprintf(esc_html__('%s Settings Footer', 'codeweber'), $label), // Переводим метку в заголовке
							'tabs'  => array(
								array(
									'title'            => sprintf(esc_html__('%s Single Footer Settings', 'codeweber'), $label), // Переводим метку в заголовке
									'id'               => 'cpt-single-footer-settings-' . $label,
									'subsection'       => true, // Устанавливаем как субсекцию
									'desc'             => sprintf(esc_html__('Settings for the Single Footer %s custom post type.', 'codeweber'), $label),
									'fields'           => array(

										// Добавляем выпадающий список для выбора записей типа header
										array(
											'id'       => 'cpt-single-post-footer-' . $label,
											'type'     => 'select',
											'title'    => sprintf(esc_html__('Select Footer for Single %s', 'codeweber'), $label),
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
									'title'            => sprintf(esc_html__('%s Archive Footer Settings', 'codeweber'), $label), // Переводим метку в заголовке
									'id'               => 'cpt-archive-footer-settings-' . $label,
									'subsection'       => true, // Устанавливаем как субсекцию
									'desc'             => sprintf(esc_html__('Settings for the Archive Footer %s custom post type.', 'codeweber'), $label),
									'fields'           => array(

										array(
											'id'       => 'cpt-archive-post-footer-' . $label,
											'type'     => 'select',
											'title'    => sprintf(esc_html__('Select Footer for Archive %s', 'codeweber'), $label),
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

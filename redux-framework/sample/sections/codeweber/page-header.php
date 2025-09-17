<?php

/**
 * Redux Framework header config.
 * For full documentation, please visit: https://devs.redux.io/
 *
 * @package Redux Framework
 */

defined('ABSPATH') || exit;

// Путь к файлу colors.json
$colors_file = get_template_directory() . '/components/colors.json';

// Проверяем, существует ли файл
if (file_exists($colors_file)) {
	// Загружаем содержимое файла и декодируем его в массив
	$colors_data = json_decode(file_get_contents($colors_file), true);

	// Проверяем, успешно ли декодирован JSON
	if ($colors_data && is_array($colors_data)) {
		// Преобразуем массив цветов в формат для Redux
		$color_options = array();
		$soft_color_options = array();
		foreach ($colors_data as $color) {
			$color_options[$color['value']] = esc_html__($color['label'], 'codeweber');
		}
		foreach ($colors_data as $color) {
			$soft_color_options['soft-' . $color['value']] = esc_html__('Soft-' . $color['label'], 'codeweber');
		}
	}
}

// Проверка наличия записей типа "page-header"
$page_header_posts = get_posts(array(
	'post_type'      => 'page-header',
	'posts_per_page' => 1, // Проверяем только наличие хотя бы одной записи
));

$no_page_headers_message = '';
if (empty($pageheaders_posts)) {
	// Если записей нет, выводим сообщение с предложением создать новый "Custom Header"
	$no_page_headers_message = sprintf(
		esc_html__('No custom pageheaders found. You can create one by visiting the following link: %s', 'codeweber'),
		'<a href="' . esc_url(admin_url('edit.php?post_type=page-header')) . '" target="_blank">' . esc_html__('Create Custom Page-header', 'codeweber') . '</a>'
	);
}


// Подключаем секцию и поле выбора цвета
Redux::set_section(
	$opt_name,
	array(
		'title'            => esc_html__("Page Header", "codeweber"),
		'id'               => 'global-page-header',
		'customizer_width' => '300px',
		'icon'             => 'el el-home',
		'sections'   => array(
			array(
				'icon_class' => 'icon-large',
				'icon'       => 'el-icon-home',
				'fields'     => array(
					array(
						'id'      => 'sidebar',
						'title'   => esc_html__('Sidebar', 'codeweber'),
						'type'    => 'select',
						'data'    => 'sidebars',
						'default' => 'None',
					),
				),
			),
		),
		'fields'           => array(

			array(
				'id'       => 'global_page_header_type_section',
				'type'     => 'accordion',
				'title'    => esc_html__('Page Header Type', 'codeweber'),
				'position' => 'start',
			),



			// Выбор типа Page Header
			array(
				'id'       => 'global_page_header_type',
				'type'     => 'button_set',
				'title'    => esc_html__('Select Page-Header Type', 'codeweber'),
				'options'  => array(
					'1' => esc_html__('Base', 'codeweber'),
					'2' => esc_html__('Custom', 'codeweber'),
				),
				'default'  => '1',
			),


			// Выбор типа фона
			array(
				'id'       => 'global-page-header-aligns',
				'type'     => 'button_set',
				'title'    => esc_html__('Page Header Background Type', 'codeweber'),
				'options'  => array(
					'1' => esc_html__('Left', 'codeweber'),
					'2' => esc_html__('Center', 'codeweber'),
					'3' => esc_html__('Right', 'codeweber'),
				),
				'default'  => '1',
				'required' => array('global_page_header_type', '=', '1'),
			),

			// Custom Header List
			array(
				'id'       => 'custom_page_header',
				'type'     => 'select',
				'title'    => esc_html__('Custom Page-Header', 'codeweber'),
				'data'     => 'posts',
				'args'     => array(
					'post_type' => 'page-header',
					'posts_per_page' => -1,
				),
				'required' => array('global_page_header_type', '=', '2'),
				'desc'     => $no_headers_message, // Выводим сообщение, если записей нет
			),

			// Base Header Models
			array(
				'id'       => 'global_page_header_model',
				'type'     => 'image_select',
				'title'    => esc_html__('Base Page Header Models', 'codeweber'),
				'options'  => array(
					'1' => array(
						'title' => esc_html__('Page Header Type 1', 'codeweber'),
						'img'   => get_template_directory_uri() . '/redux-framework/sample/patterns/pageheader_1.jpg',
						'class' => 'header_viewport',
					),
					'2' => array(
						'title' => esc_html__('Page Header Type 2', 'codeweber'),
						'img'   => get_template_directory_uri() . '/redux-framework/sample/patterns/pageheader_2.jpg',
						'class' => 'header_viewport',
					),
					'3' => array(
						'title' => esc_html__('Page Header Type 3', 'codeweber'),
						'img'   => get_template_directory_uri() . '/redux-framework/sample/patterns/pageheader_3.jpg',
						'class' => 'header_viewport',
					),
					'4' => array(
						'title' => esc_html__('Page Header Type 4', 'codeweber'),
						'img'   => get_template_directory_uri() . '/redux-framework/sample/patterns/pageheader_4.jpg',
						'class' => 'header_viewport',
					),
					'5' => array(
						'title' => esc_html__('Page Header Type 5', 'codeweber'),
						'img'   => get_template_directory_uri() . '/redux-framework/sample/patterns/pageheader_5.jpg',
						'class' => 'header_viewport',
					),
					'6' => array(
						'title' => esc_html__('Page Header Type 6', 'codeweber'),
						'img'   => get_template_directory_uri() . '/redux-framework/sample/patterns/pageheader_6.jpg',
						'class' => 'header_viewport',
					),
					'7' => array(
						'title' => esc_html__('Page Header Type 7', 'codeweber'),
						'img'   => get_template_directory_uri() . '/redux-framework/sample/patterns/pageheader_7.jpg',
						'class' => 'header_viewport',
					),
					'8' => array(
						'title' => esc_html__('Page Header Type 8', 'codeweber'),
						'img'   => get_template_directory_uri() . '/redux-framework/sample/patterns/pageheader_8.jpg',
						'class' => 'header_viewport',
					),
					'9' => array(
						'title' => esc_html__('Page Header Type 9', 'codeweber'),
						'img'   => get_template_directory_uri() . '/redux-framework/sample/patterns/pageheader_9.jpg',
						'class' => 'header_viewport',
					),
				),
				'default'  => '1',
				'required' => array('global_page_header_type', '=', '1'),
			),

			array(
				'id'       => 'global-page-header-title-section',
				'type'     => 'accordion',
				'title'    => esc_html__('Title', 'codeweber'),
				'position' => 'start',
			),

			// Цвет Breadcrumbs
			array(
				'id'       => 'global-page-header-title-color',
				'type'     => 'button_set',
				'title'    => esc_html__('Breadcrumbs Color', 'codeweber'),
				'options'  => array(
					'1' => esc_html__('Dark', 'codeweber'),
					'2' => esc_html__('Light', 'codeweber'),
				),
				'default'  => '1',
				'required' => array('global_page_header_type', '=', '1'),
			),

			array(
				'id'       => 'global-page-header-background-section',
				'type'     => 'accordion',
				'title'    => esc_html__('Page Header Background', 'codeweber'),
				'position' => 'start',
			),
			// Выбор типа фона
			array(
				'id'       => 'global-page-header-background',
				'type'     => 'button_set',
				'title'    => esc_html__('Page Header Background Type', 'codeweber'),
				'options'  => array(
					'1' => esc_html__('Solid-Color', 'codeweber'),
					'2' => esc_html__('Soft-Color', 'codeweber'),
					'3' => esc_html__('Image', 'codeweber'),
					'4' => esc_html__('Pattern', 'codeweber'),
					'5' => esc_html__('None', 'codeweber'),
				),
				'default'  => '1',
				'required' => array('global_page_header_type', '=', '1'),
			),

			// Выбор Solid-Color с динамическими цветами
			array(
				'id'       => 'global-page-header-bg-solid-color',
				'type'     => 'select',
				'title'    => esc_html__('Select Solid Color', 'codeweber'),
				'options'  => $color_options, // Используем динамически полученные цвета
				'default'  => 'primary',  // Можно выбрать дефолтный цвет
				'required' => array(
					array('global-page-header-background', '=', '1'),
					array('global_page_header_type', '=', '1')
				),
			),

			// Выбор Soft-Color
			array(
				'id'       => 'global-page-header-bg-soft-color',
				'type'     => 'select',
				'title'    => esc_html__('Select Soft Color', 'codeweber'),
				'options'  => $soft_color_options,
				'default'  => 'soft-primary',
				'required' => array(
					array('global-page-header-background', '=', '2'),
					array('global_page_header_type', '=', '1')
				),
			),

			// Загрузка изображения
			array(
				'id'       => 'global-page-header-image',
				'type'     => 'media',
				'title'    => esc_html__('Upload Header Background Image', 'codeweber'),
				'required' => array(
					array('global-page-header-background', '=', '3'),
					array('global_page_header_type', '=', '1')
				),
			),


			// Загрузка изображения
			array(
				'id'       => 'global-page-header-pattern',
				'type'     => 'media',
				'title'    => esc_html__('Upload Header Pattern Image', 'codeweber'),
				'required' => array(
					array('global-page-header-background', '=', '4'),
					array('global_page_header_type', '=', '1')
				),
			),

			array(
				'id'       => 'global-page-header-breadcrumbs',
				'type'     => 'accordion',
				'title'    => esc_html__('Breadcrumbs', 'codeweber'),
				'position' => 'start',
			),
			// Активация Breadcrumbs
			array(
				'id'       => 'global-page-header-breadcrumb-enable',
				'type'     => 'switch',
				'title'    => esc_html__('Breadcrumbs', 'codeweber'),
				'default'  => 1,
				'required' => array('global_page_header_type', '=', '1'),
				'on'       => esc_html__('On', 'codeweber'),
				'off'      => esc_html__('Off', 'codeweber'),
			),

			// Выбор типа фона
			array(
				'id'       => 'global-bredcrumbs-aligns',
				'type'     => 'button_set',
				'title'    => esc_html__('Bredcrumbs align', 'codeweber'),
				'options'  => array(
					'1' => esc_html__('Left', 'codeweber'),
					'2' => esc_html__('Center', 'codeweber'),
					'3' => esc_html__('Right', 'codeweber'),
				),
				'default'  => '1',
				'required' => array(
					array('global-page-header-breadcrumb-enable', '=', '1'),
					array('global_page_header_type', '=', '1')
				),
			),


			// Цвет Breadcrumbs
			array(
				'id'       => 'global-page-header-breadcrumb-color',
				'type'     => 'button_set',
				'title'    => esc_html__('Breadcrumbs Color', 'codeweber'),
				'options'  => array(
					'1' => esc_html__('Dark', 'codeweber'),
					'2' => esc_html__('Light', 'codeweber'),
					'3' => esc_html__('Muted', 'codeweber'),
				),
				'default'  => '1',
				'required' => array(
					array('global-page-header-breadcrumb-enable', '=', '1'),
					array('global_page_header_type', '=', '1')
				),
			),


			// Background Breadcrumbs
			array(
				'id'       => 'global-page-header-breadcrumb-bg-color',
				'type'     => 'select',
				'title'    => esc_html__('Breadcrumb Background Color', 'codeweber'),
				'options'  => $color_options, // Используем динамически полученные цвета
				'default'  => 'primary',  // Можно выбрать дефолтный цвет
				'required' => array(
					array('global-page-header-breadcrumb-enable', '=', '1'),
					array('global_page_header_type', '=', '1')
				),
			),


		),
	)
);

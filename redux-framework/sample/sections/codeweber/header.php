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
			$soft_color_options['soft-'. $color['value']] = esc_html__('Soft-'. $color['label'], 'codeweber');
		}
	}
}

// Проверка наличия записей типа "header"
$header_posts = get_posts(array(
	'post_type'      => 'header',
	'posts_per_page' => 1, // Проверяем только наличие хотя бы одной записи
));

$no_headers_message = '';
if (empty($header_posts)) {
	// Если записей нет, выводим сообщение с предложением создать новый "Custom Header"
	$no_headers_message = sprintf(
		esc_html__('No custom headers found. You can create one by visiting the following link: %s', 'codeweber'),
		'<a href="' . esc_url(admin_url('edit.php?post_type=header')) . '" target="_blank">' . esc_html__('Create Custom Header', 'codeweber') . '</a>'
	);
}

// Подключаем секцию и поле выбора цвета
Redux::set_section(
	$opt_name,
	array(
		'title'            => esc_html__("Settings Header", "codeweber"),
		'id'               => 'header',
		'customizer_width' => '300px',
		'icon'             => 'el el-home',
		'fields'           => array(

			array(
				'id'       => 'header-accordeon-change-header',
				'type'     => 'accordion',
				'title'    => esc_html__('Header', 'codeweber'),
				'position' => 'start',
			),

			// Выбор типа Header
			array(
				'id'       => 'global-header-type',
				'type'     => 'button_set',
				'title'    => esc_html__('Select Header Type', 'codeweber'),
				'options'  => array(
					'1' => esc_html__('Base', 'codeweber'),
					'2' => esc_html__('Custom', 'codeweber'),
				),
				'default'  => '1',
			),

			array(
				'id'       => 'header-rounded',
				'type'     => 'button_set',
				'title'    => esc_html__('Header rounded', 'codeweber'),

				// Must provide key => value pairs for radio options.
				'options'  => array(
					'1' => esc_html__('rounded', 'codeweber'),
					'2' => esc_html__('rounded-pill', 'codeweber'),
					'3' => esc_html__('none', 'codeweber'),
				),
				'default'  => '1',
				'required' => array('global-header-type', '=', '1'),

			),


			// Custom Header List
			array(
				'id'       => 'custom-header',
				'type'     => 'select',
				'title'    => esc_html__('Select Custom Header', 'codeweber'),
				'data'     => 'posts',
				'args'     => array(
					'post_type' => 'header',
					'posts_per_page' => -1,
				),
				'required' => array('global-header-type', '=', '2'),
				'desc'     => $no_headers_message, // Выводим сообщение, если записей нет
			),

			// Выбор типа фона
			array(
				'id'       => 'social-icon-type',
				'type'     => 'button_set',
				'title'    => esc_html__('Social Icon Type', 'codeweber'),
				'options'  => array(
					'1' => esc_html__('Type 1', 'codeweber'),
					'2' => esc_html__('Type 2', 'codeweber'),
					'3' => esc_html__('Type 3', 'codeweber'),
					'4' => esc_html__('Type 4', 'codeweber'),
					'5' => esc_html__('Type 5', 'codeweber'),
				),
				'default'  => '1',
				'required' => array('global-header-type', '=', '1'),
			),

			array(
				'id'       => 'header-color-text',
				'type'     => 'button_set',
				'title'    => esc_html__('Header text color', 'codeweber'),
				// Must provide key => value pairs for radio options.
				'options'  => array(
					'1' =>  esc_html__('Dark', 'codeweber'),
					'2' =>  esc_html__('Light', 'codeweber'),
				),
				'default'  => '1',
				'required' => array('global-header-type', '=', '1'),
			),

			// Выбор типа фона
			array(
				'id'       => 'header-background',
				'type'     => 'button_set',
				'title'    => esc_html__('Select type Header background', 'codeweber'),
				'subtitle' => esc_html__('No validation can be done on this field type', 'codeweber'),
				'desc'     => esc_html__('This is the description field, again good for additional info.', 'codeweber'),
				'options'  => array(
					'1' => esc_html__('Solid-Color', 'codeweber'),
					'2' => esc_html__('Soft-Color', 'codeweber'),
					'3' => esc_html__('Transparent', 'codeweber'),
					'4' => esc_html__('None', 'codeweber'),
				),
				'default'  => '1',
				'required' => array('global-header-type', '=', '1'),
			),

			// Выбор Solid-Color с динамическими цветами
			array(
				'id'       => 'solid-color-header',
				'type'     => 'select',
				'title'    => esc_html__('Select Header Background Solid Color', 'codeweber'),
				'options'  => $color_options, // Используем динамически полученные цвета
				'default'  => 'light',  // Можно выбрать дефолтный цвет
				'required' => array(
					array('header-background', '=', '1'),
					array('global-header-type', '=', '1')
				),
			),



			// Выбор Soft-Color
			array(
				'id'       => 'soft-color-header',
				'type'     => 'select',
				'title'    => esc_html__('Select Header Background Soft Color', 'codeweber'),
				'options'  => $soft_color_options,
				'default'  => 'soft-red',
				'required' => array(
					array('header-background', '=', '2'),
					array('global-header-type', '=', '1')
				),
			),


			// Base Header Models
			array(
				'id'       => 'global-header-model',
				'type'     => 'image_select',
				'title'    => esc_html__('Base Header Models', 'codeweber'),
				'subtitle' => esc_html__('Select a predefined header model.', 'codeweber'),
				'options'  => array(
					'1' => array(
						'title' => esc_html__('Header Type 1', 'codeweber'),
						'img'   => get_template_directory_uri() . '/redux-framework/sample/patterns/header_1.jpg',
						'class' => 'header_viewport',
					),
					'2' => array(
						'title' => esc_html__('Header Type 2', 'codeweber'),
						'img'   => get_template_directory_uri() . '/redux-framework/sample/patterns/header_3.jpg',
						'class' => 'header_viewport',
					),
					'3' => array(
						'title' => esc_html__('Header Type 3', 'codeweber'),
						'img'   => get_template_directory_uri() . '/redux-framework/sample/patterns/header_5.jpg',
						'class' => 'header_viewport',
					),
					'4' => array(
						'title' => esc_html__('Header Type 4', 'codeweber'),
						'img'   => get_template_directory_uri() . '/redux-framework/sample/patterns/header_6.jpg',
						'class' => 'header_viewport',
					),
					'5' => array(
						'title' => esc_html__('Header Type 5', 'codeweber'),
						'img'   => get_template_directory_uri() . '/redux-framework/sample/patterns/header_2.jpg',
						'class' => 'header_viewport',
					),
					'6' => array(
						'title' => esc_html__('Header Type 6', 'codeweber'),
						'img'   => get_template_directory_uri() . '/redux-framework/sample/patterns/header_8.jpg',
						'class' => 'header_viewport',
					),
					'7' => array(
						'title' => esc_html__('Header Type 7', 'codeweber'),
						'img'   => get_template_directory_uri() . '/redux-framework/sample/patterns/header_4.jpg',
						'class' => 'header_viewport',
					),
					'8' => array(
						'title' => esc_html__('Header Type 8', 'codeweber'),
						'img'   => get_template_directory_uri() . '/redux-framework/sample/patterns/header_7.jpg',
						'class' => 'header_viewport',
					),
				),
				'default'  => '1',
				'required' => array('global-header-type', '=', '1'),
			),
			array(
				'id'       => 'header-accordeon-offcanvas-left',
				'type'     => 'accordion',
				'title'    => esc_html__('Mobile Menu', 'codeweber'),
				'position' => 'start',
			),


			// Выбор Solid-Color с динамическими цветами
			array(
				'id'       => 'global-background-offcanvas-left',
				'type'     => 'select',
				'title'    => esc_html__('Background Color', 'codeweber'),
				'options'  => $color_options, // Используем динамически полученные цвета
				'default'  => 'dark',  // Можно выбрать дефолтный цвет
				'required' => array(
					array('global-header-type', '=', '1')
				),
			),

			// Выбор Solid-Color с динамическими цветами
			array(
				'id'       => 'global-color-offcanvas-left',
				'type'     => 'select',
				'title'    => esc_html__('Text Color', 'codeweber'),
				'options'  => $color_options, // Используем динамически полученные цвета
				'default'  => 'light',  // Можно выбрать дефолтный цвет
				'required' => array(
					array('global-header-type', '=', '1')
				),
			),

			array(
				'id'       => 'sort-offcanvas-left',
				'type'     => 'sorter',
				'title'    => esc_html__('Order items in menu', 'codeweber'),
				'desc'     => esc_html__('Organize how you want the layout to appear on the homepage', 'codeweber'),
				'compiler' => true,
				'required' => array(
					array('global-header-type', '=', '1'),
				),
				'options'  => array(
					'enabled'  => array(
						'logo'   => esc_html__('Logo', 'codeweber'),
						'menu'   => esc_html__('Menu', 'codeweber'),
						'phones' => esc_html__('Phones', 'codeweber'),
						'socials' => esc_html__('Socials', 'codeweber'),
					),
					'disabled' => array(
						'widget_offcanvas_left'   => esc_html__('Widget 1', 'codeweber'),
						'widget_offcanvas_center' => esc_html__('Widget 2', 'codeweber'),
						'widget_offcanvas_right'  => esc_html__('Widget 3', 'codeweber'),
					),
				),
				'on'       => esc_html__('Enabled', 'codeweber'),
				'off'      => esc_html__('Disabled', 'codeweber'),
			),


			array(
				'id'       => 'header-accordeon-offcanvas-right',
				'type'     => 'accordion',
				'title'    => esc_html__('Offcanvas Right Menu', 'codeweber'),
				'position' => 'start',
			),

			// Активация Breadcrumbs
			array(
				'id'       => 'global-header-offcanvas-right',
				'type'     => 'switch',
				'title'    => esc_html__('Offcanvas Right Menu', 'codeweber'),
				'subtitle' => esc_html__('Enable Right Menu', 'codeweber'),
				'default'  => 1,
				'required' => array('global-header-type', '=', '1'),
			),

			array(
				'id'       => 'sort-offcanvas-right',
				'type'     => 'sorter',
				'title'    => esc_html__('Order items in side menu', 'codeweber'),
				'desc'     => esc_html__('Organize how you want the layout to appear on the side menu', 'codeweber'),
				'Organize how you want the layout to appear on the side menu',
				'compiler' => 'true',
				'options'  => array(
					'enabled'  => array(
						'description'             => esc_html__('Description', 'codeweber'),
						'phones'                  => esc_html__('Phones', 'codeweber'),
						'map'                     => esc_html__('Map', 'codeweber'),
						'socials'                 => esc_html__('Socials', 'codeweber'),
					),
					'disabled' => array(
						'menu'                    => esc_html__('Menu', 'codeweber'),
						'address'                    => esc_html__('Address', 'codeweber'),
						'widget_offcanvas_left'   => esc_html__('Widget 1', 'codeweber'),
						'widget_offcanvas_center' => esc_html__('Widget 2', 'codeweber'),
						'widget_offcanvas_right'  => esc_html__('Widget 3', 'codeweber'),
					),
				),
			),
		),
	)
);

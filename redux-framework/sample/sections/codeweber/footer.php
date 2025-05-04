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

// Проверка наличия записей типа "footer"
$footer_posts = get_posts(array(
	'post_type'      => 'footer',
	'posts_per_page' => 1, // Проверяем только наличие хотя бы одной записи
));

$no_footers_message = '';
if (empty($footer_posts)) {
	// Если записей нет, выводим сообщение с предложением создать новый "Custom Footer"
	$no_footers_message = sprintf(
		esc_html__('No custom footers found. You can create one by visiting the following link: %s', 'codeweber'),
		'<a href="' . esc_url(admin_url('edit.php?post_type=footer')) . '" target="_blank">' . esc_html__('Create Custom Footer', 'codeweber') . '</a>'
	);
}

// Подключаем секцию и поле выбора цвета
Redux::set_section(
	$opt_name,
	array(
		'title'            => esc_html__("Settings Footer", "codeweber"),
		'id'               => 'footer',
		'customizer_width' => '300px',
		'icon'             => 'el el-home',
		'fields'           => array(

			array(
				'id'       => 'footer-accordeon-change-footer',
				'type'     => 'accordion',
				'title'    => esc_html__('Footer', 'codeweber'),
				'position' => 'start',
			),

			// Выбор типа Footer
			array(
				'id'       => 'global-footer-type',
				'type'     => 'button_set',
				'title'    => esc_html__('Select Footer Type', 'codeweber'),
				'options'  => array(
					'1' => esc_html__('Base', 'codeweber'),
					'2' => esc_html__('Custom', 'codeweber'),
				),
				'default'  => '1',
			),



			// Custom Footer List
			array(
				'id'       => 'custom-footer',
				'type'     => 'select',
				'title'    => esc_html__('Select Custom Footer', 'codeweber'),
				'data'     => 'posts',
				'args'     => array(
					'post_type' => 'footer',
					'posts_per_page' => -1,
				),
				'required' => array('global-footer-type', '=', '2'),
				'desc'     => $no_footers_message, // Выводим сообщение, если записей нет
			),



			array(
				'id'       => 'footer-color-text',
				'type'     => 'button_set',
				'title'    => esc_html__('Footer text color', 'codeweber'),
				// Must provide key => value pairs for radio options.
				'options'  => array(
					'1' =>  esc_html__('Dark', 'codeweber'),
					'2' =>  esc_html__('Light', 'codeweber'),
				),
				'default'  => '1',
				'required' => array('global-footer-type', '=', '1'),
			),

			// Выбор типа фона
			array(
				'id'       => 'footer-background',
				'type'     => 'button_set',
				'title'    => esc_html__('Select type Footer background', 'codeweber'),
				'options'  => array(
					'1' => esc_html__('Solid-Color', 'codeweber'),
					'2' => esc_html__('Soft-Color', 'codeweber'),
				),
				'default'  => '1',
				'required' => array('global-footer-type', '=', '1'),
			),

			// Выбор Solid-Color с динамическими цветами
			array(
				'id'       => 'solid-color-footer',
				'type'     => 'select',
				'title'    => esc_html__('Select Footer Background Solid Color', 'codeweber'),
				'options'  => $color_options, // Используем динамически полученные цвета
				'default'  => 'light',  // Можно выбрать дефолтный цвет
				'required' => array(
					array('footer-background', '=', '1'),
					array('global-footer-type', '=', '1')
				),
			),



			// Выбор Soft-Color
			array(
				'id'       => 'soft-color-footer',
				'type'     => 'select',
				'title'    => esc_html__('Select Footer Background Soft Color', 'codeweber'),
				'options'  => $soft_color_options,
				'default'  => 'soft-red',
				'required' => array(
					array('footer-background', '=', '2'),
					array('global-footer-type', '=', '1')
				),
			),


			// Base Footer Models
			array(
				'id'       => 'global-footer-model',
				'type'     => 'image_select',
				'title'    => esc_html__('Base Footer Models', 'codeweber'),
				'options'  => array(
					'1' => array(
						'title' => esc_html__('Footer Type 1', 'codeweber'),
						'img'   => get_template_directory_uri() . '/redux-framework/sample/patterns/footer_1.jpg',
						'class' => 'footer_viewport',
					),
					'2' => array(
						'title' => esc_html__('Footer Type 2', 'codeweber'),
						'img'   => get_template_directory_uri() . '/redux-framework/sample/patterns/footer_2.jpg',
						'class' => 'footer_viewport',
					),
					'3' => array(
						'title' => esc_html__('Footer Type 3', 'codeweber'),
						'img'   => get_template_directory_uri() . '/redux-framework/sample/patterns/footer_3.jpg',
						'class' => 'footer_viewport',
					),
					'4' => array(
						'title' => esc_html__('Footer Type 4', 'codeweber'),
						'img'   => get_template_directory_uri() . '/redux-framework/sample/patterns/footer_4.jpg',
						'class' => 'footer_viewport',
					),
					'5' => array(
						'title' => esc_html__('Footer Type 5', 'codeweber'),
						'img'   => get_template_directory_uri() . '/redux-framework/sample/patterns/footer_5.jpg',
						'class' => 'footer_viewport',
					),
					'6' => array(
						'title' => esc_html__('Footer Type 6', 'codeweber'),
						'img'   => get_template_directory_uri() . '/redux-framework/sample/patterns/footer_6.jpg',
						'class' => 'footer_viewport',
					),
					'7' => array(
						'title' => esc_html__('Footer Type 7', 'codeweber'),
						'img'   => get_template_directory_uri() . '/redux-framework/sample/patterns/footer_7.jpg',
						'class' => 'footer_viewport',
					),
					'8' => array(
						'title' => esc_html__('Footer Type 8', 'codeweber'),
						'img'   => get_template_directory_uri() . '/redux-framework/sample/patterns/footer_8.jpg',
						'class' => 'footer_viewport',
					),
				),
				'default'  => '1',
				'required' => array('global-footer-type', '=', '1'),
			),
			array(
				'id'       => 'footer-accordeon-offcanvas-left',
				'type'     => 'accordion',
				'title'    => esc_html__('Social Icon', 'codeweber'),
				'position' => 'start',
			),

			// Выбор типа фона
			array(
				'id'       => 'social-icon-type-footer-menu',
				'type'     => 'button_set',
				'title'    => esc_html__('Social Icon Type Footer Menu', 'codeweber'),
				'options'  => array(
					'1' => esc_html__('Type 1', 'codeweber'),
					'2' => esc_html__('Type 2', 'codeweber'),
					'3' => esc_html__('Type 3', 'codeweber'),
					'4' => esc_html__('Type 4', 'codeweber'),
					'5' => esc_html__('Type 5', 'codeweber'),
					'6' => esc_html__('Type 6', 'codeweber'),
					'7' => esc_html__('Type 7', 'codeweber'),
				),
				'default'  => '1',
				'required' => array('global-footer-type', '=', '1'),
			),



			array(
				'id'       => 'footer-accordeon-offcanvas-right',
				'type'     => 'accordion',
				'title'    => esc_html__('Offcanvas Right Menu footer', 'codeweber'),
				'position' => 'start',
			),

			// Активация Breadcrumbs
			array(
				'id'       => 'global-footer-offcanvas-right',
				'type'     => 'switch',
				'title'    => esc_html__('Offcanvas Right Menu footer', 'codeweber'),
				'default'  => 1,
				'required' => array('global-footer-type', '=', '1'),
			),

			// Выбор типа фона
			array(
				'id'       => 'social-icon-type-footer',
				'type'     => 'button_set',
				'title'    => esc_html__('Social Icon Type footer ', 'codeweber'),
				'options'  => array(
					'1' => esc_html__('Type 1', 'codeweber'),
					'2' => esc_html__('Type 2', 'codeweber'),
					'3' => esc_html__('Type 3', 'codeweber'),
					'4' => esc_html__('Type 4', 'codeweber'),
					'5' => esc_html__('Type 5', 'codeweber'),
					'6' => esc_html__('Type 6', 'codeweber'),
					'7' => esc_html__('Type 7', 'codeweber'),
				),
				'default'  => '1',
				'required' => array('global-footer-offcanvas-right', '=', '1'),
			),

			array(
				'id'       => 'sort-offcanvas-footer',
				'type'     => 'sorter',
				'title'    => esc_html__('Order items in side menu', 'codeweber'),
				'compiler' => 'true',
				'required' => array('global-footer-offcanvas-right', '=', '1'),
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
						'widget_offcanvas_1'   => esc_html__('Widget 1', 'codeweber'),
						'widget_offcanvas_2' => esc_html__('Widget 2', 'codeweber'),
						'widget_offcanvas_3'  => esc_html__('Widget 3', 'codeweber'),
					),
				),
			),

			array(
				'id'       => 'footer-accordeon-topbar',
				'type'     => 'accordion',
				'title'    => esc_html__('Bottompbar', 'codeweber'),
				'position' => 'start',
			),

			// Активация Breadcrumbs
			array(
				'id'       => 'footer-bottomopbar-enable',
				'type'     => 'switch',
				'title'    => esc_html__('Footer Bootompbar', 'codeweber'),
				'default'  => 1,
				'required' => array('global-footer-type', '=', '1'),
			),
		),
	)
);

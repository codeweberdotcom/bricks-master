<?php

/**
 * Redux Framework header config.
 * For full documentation, please visit: https://devs.redux.io/
 *
 * @package Redux Framework
 */

defined('ABSPATH') || exit;

// Функция для генерации превью (стили Unicons уже подключены в админке)
if (!function_exists('codeweber_social_preview_with_styles')) {
	function codeweber_social_preview_with_styles($field_id, $shortcode) {
		return do_shortcode($shortcode);
	}
}

// Путь к файлу colors.json
$colors_file = get_template_directory() . '/components/colors.json';

// Инициализируем переменные по умолчанию
$color_options = array();
$soft_color_options = array();

// Проверяем, существует ли файл
if (file_exists($colors_file)) {
	// Загружаем содержимое файла и декодируем его в массив
	$colors_data = json_decode(file_get_contents($colors_file), true);

	// Проверяем, успешно ли декодирован JSON
	if ($colors_data && is_array($colors_data)) {
		// Преобразуем массив цветов в формат для Redux
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
				'id'       => 'global_footer_type',
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
				'required' => array('global_footer_type', '=', '2'),
				'desc'     => $no_footers_message, // Выводим сообщение, если записей нет
			),



			array(
				'id'       => 'footer_color_text',
				'type'     => 'button_set',
				'title'    => esc_html__('Footer text color', 'codeweber'),
				// Must provide key => value pairs for radio options.
				'options'  => array(
					'light' =>  esc_html__('Dark', 'codeweber'),
					'dark' =>  esc_html__('Light', 'codeweber'),
				),
				'default'  => 'light',
				'required' => array('global_footer_type', '=', '1'),
			),

			// Выбор типа фона
			array(
				'id'       => 'footer_background',
				'type'     => 'button_set',
				'title'    => esc_html__('Select type Footer background', 'codeweber'),
				'options'  => array(
					'solid' => esc_html__('Solid-Color', 'codeweber'),
					'soft' => esc_html__('Soft-Color', 'codeweber'),
				),
				'default'  => 'solid',
				'required' => array('global_footer_type', '=', '1'),
			),

			// Выбор Solid-Color с динамическими цветами
			array(
				'id'       => 'footer_solid_color',
				'type'     => 'select',
				'title'    => esc_html__('Select Footer Background Solid Color', 'codeweber'),
				'options'  => $color_options, // Используем динамически полученные цвета
				'default'  => 'light',  // Можно выбрать дефолтный цвет
				'required' => array(
					array('footer_background', '=', 'solid'),
					array('global_footer_type', '=', '1')
				),
			),

			// Выбор типа иконок соцсетей для футера
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
					'8' => esc_html__('Type 8', 'codeweber'),
					'9' => esc_html__('Type 9', 'codeweber'),
				),
				'default'  => '1',
				'required' => array('global_footer_type', '=', '1'),
			),

			// Выбор стиля кнопок соцсетей для футера
			array(
				'id'       => 'social-button-style-footer',
				'type'     => 'button_set',
				'title'    => esc_html__('Social Button Style', 'codeweber'),
				'options'  => array(
					'circle' => esc_html__('Circle', 'codeweber'),
					'block'  => esc_html__('Block', 'codeweber'),
				),
				'default'  => 'circle',
				'required' => array('global_footer_type', '=', '1'),
			),

			// Выбор размера кнопок соцсетей для футера
			array(
				'id'       => 'social-button-size-footer',
				'type'     => 'button_set',
				'title'    => esc_html__('Social Button Size', 'codeweber'),
				'options'  => array(
					'sm' => esc_html__('Small', 'codeweber'),
					'md' => esc_html__('Medium', 'codeweber'),
					'lg' => esc_html__('Large', 'codeweber'),
				),
				'default'  => 'md',
				'required' => array('global_footer_type', '=', '1'),
			),

			// Выбор цвета логотипа для футера
			array(
				'id'       => 'footer-logo-color',
				'type'     => 'button_set',
				'title'    => esc_html__('Footer Logo Color', 'codeweber'),
				'options'  => array(
					'light' => esc_html__('Light', 'codeweber'),
					'dark'  => esc_html__('Dark', 'codeweber'),
				),
				'default'  => 'light',
				'required' => array('global_footer_type', '=', '1'),
			),



			// Выбор Soft-Color
			array(
				'id'       => 'footer_soft_color',
				'type'     => 'select',
				'title'    => esc_html__('Select Footer Background Soft Color', 'codeweber'),
				'options'  => $soft_color_options,
				'default'  => 'soft-red',
				'required' => array(
					array('footer_background', '=', 'soft'),
					array('global_footer_type', '=', '1')
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
				'required' => array('global_footer_type', '=', '1'),
			),
			array(
				'id'       => 'footer-accordeon-offcanvas-right',
				'type'     => 'accordion',
				'title'    => esc_html__('Footer Column Settings', 'codeweber'),
				'position' => 'start',
				'required' => array('global_footer_type', '=', '1'),
			),

			// Активация Breadcrumbs
			array(
				'id'       => 'global_footer_offcanvas_right',
				'type'     => 'switch',
				'title'    => esc_html__('Offcanvas Right Menu footer', 'codeweber'),
				'default'  => 1,
				'required' => array('global_footer_type', '=', '1'),
			),

			array(
				'id'       => 'sort_offcanvas_footer',
				'type'     => 'sorter',
				'title'    => esc_html__('Order items in side menu', 'codeweber'),
				'compiler' => 'true',
				'required' => array('global_footer_offcanvas_right', '=', '1'),
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
				'id'       => 'footer_accordeon_topbar',
				'type'     => 'accordion',
				'title'    => esc_html__('Bottombar', 'codeweber'),
				'position' => 'start',
				'required' => array('global_footer_type', '=', '1'),
			),

			// Активация Breadcrumbs
			array(
				'id'       => 'footer_bottomobar_enable',
				'type'     => 'switch',
				'title'    => esc_html__('Footer Bootombar', 'codeweber'),
				'default'  => 1,
				'required' => array('global_footer_type', '=', '1'),
			),
		),
	)
);

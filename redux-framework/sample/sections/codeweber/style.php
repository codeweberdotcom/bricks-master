<?php

$color_theme_files = array(
	'default' => 'default'
);

$directory = get_template_directory() . '/dist/assets/css/colors';

if (file_exists($directory)) {
	$files = scandir($directory);
	foreach ($files as $file) {
		if (pathinfo($file, PATHINFO_EXTENSION) === 'css') {
			$filename = pathinfo($file, PATHINFO_FILENAME);
			$color_theme_files[$filename] = $filename;
		}
	}
}

$font_fields = require __DIR__ . '/redux-fonts.php';
$theme_gulp = require __DIR__ . '/theme_gulp.php';



Redux::set_section(
	$opt_name,
	array(
		'title'            => esc_html__("Theme style", "codeweber"),
		'id'               => 'themestyle',
		'customizer_width' => '300px',
		'icon'             => 'el el-home',
		'fields'           => array(
			array(
				'id'       => 'opt-select-color-theme',
				'type'     => 'select',
				'title'    => esc_html__('Theme color', 'codeweber'),
				'options'  => $color_theme_files,
				'default'  => 'default',
			),
			array(
				'id'       => 'opt-dark-logo',
				'type'     => 'media',
				'title'    => esc_html__('Dark Logo', 'codeweber'),
				'desc'     => esc_html__('Upload your dark logo', 'codeweber'),
				'default'  => '',
			),
			array(
				'id'       => 'opt-light-logo',
				'type'     => 'media',
				'title'    => esc_html__('Light Logo', 'codeweber'),
				'desc'     => esc_html__('Upload your light logo', 'codeweber'),
				'default'  => '',
			),
			array(
				'id'       => 'opt_button_select_style',
				'type'     => 'image_select',
				'title'    => esc_html__('Button Style', 'codeweber'),
				'options'  => array(
					'1' => array(
						'alt' => 'Pill',
						'img' => get_template_directory_uri() . '/redux-framework/sample/patterns/pill.jpg',
					),
					'2' => array(
						'alt' => 'Rounded',
						'img' => get_template_directory_uri() . '/redux-framework/sample/patterns/rounded.jpg',
					),
					'3' => array(
						'alt' => 'Rounder',
						'img' => get_template_directory_uri() . '/redux-framework/sample/patterns/rounder.jpg',
					),
					'4' => array(
						'alt' => 'Square',
						'img' => get_template_directory_uri() . '/redux-framework/sample/patterns/square.jpg',
					),
				),
				'default'  => '1',
			),
			array(
				'id'       => 'opt_card_image_border_radius',
				'type'     => 'image_select',
				'title'    => esc_html__('Card and Image rounded corners', 'codeweber'),
				'options'  => array(
					'2' => array(
						'alt' => 'Rounded',
						'img' => get_template_directory_uri() . '/redux-framework/sample/patterns/rounded.jpg',
					),
					'3' => array(
						'alt' => 'Rounder',
						'img' => get_template_directory_uri() . '/redux-framework/sample/patterns/rounder.jpg',
					),
					'4' => array(
						'alt' => 'Square',
						'img' => get_template_directory_uri() . '/redux-framework/sample/patterns/square.jpg',
					),
				),
				'default'  => '2',
			),
			array(
				'id'       => 'opt_form_border_radius',
				'type'     => 'image_select',
				'title'    => esc_html__('Forms rounded corners', 'codeweber'),
				'options'  => array(
					'2' => array(
						'alt' => 'Rounded',
						'img' => get_template_directory_uri() . '/redux-framework/sample/patterns/rounded.jpg',
					),
					'3' => array(
						'alt' => 'Rounder',
						'img' => get_template_directory_uri() . '/redux-framework/sample/patterns/rounder.jpg',
					),
					'4' => array(
						'alt' => 'Square',
						'img' => get_template_directory_uri() . '/redux-framework/sample/patterns/square.jpg',
					),
				),
				'default'  => '2',
			),
			array(
				'id'       => 'page-frame',
				'type'     => 'switch',
				'title'    => esc_html__('Page-frame', 'codeweber'),
				'default'  => false,
			),
			array(
				'id'       => 'page-frame-bg',
				'type'     => 'select',
				'title'    => esc_html__('Page-frame Background Color', 'codeweber'),
				'options'  => call_user_func(function () {
					$opts = array();
					$file = get_template_directory() . '/components/colors.json';
					if (file_exists($file)) {
						$data = json_decode(file_get_contents($file), true);
						if (is_array($data)) {
							foreach ($data as $c) {
								$opts[$c['value']] = esc_html__($c['label'], 'codeweber');
							}
						}
					}
					return $opts;
				}),
				'default'  => 'light',
				'required' => array('page-frame', '=', true),
			),
			array(
				'id'       => 'page-frame-bg-type',
				'type'     => 'button_set',
				'title'    => esc_html__('Page-frame Color Type', 'codeweber'),
				'options'  => array(
					'solid' => esc_html__('Solid', 'codeweber'),
					'soft'  => esc_html__('Soft', 'codeweber'),
				),
				'default'  => 'solid',
				'required' => array('page-frame', '=', true),
			),
			array(
				'id'       => 'page-loader',
				'type'     => 'switch',
				'title'    => esc_html__('Page Loader', 'codeweber'),
				'default'  => false,
			),
			array(
				'id'       => 'page-loader-type',
				'type'     => 'button_set',
				'title'    => esc_html__('Loader Type', 'codeweber'),
				'options'  => array(
					'default'    => esc_html__('Default', 'codeweber'),
					'logo-light' => esc_html__('Theme Logo Light', 'codeweber'),
					'logo-dark'  => esc_html__('Theme Logo Dark', 'codeweber'),
					'custom'     => esc_html__('Custom Logo', 'codeweber'),
				),
				'default'  => 'default',
				'required' => array('page-loader', '=', true),
			),
			array(
				'id'       => 'page-loader-custom-logo',
				'type'     => 'media',
				'title'    => esc_html__('Custom Loader Logo (SVG)', 'codeweber'),
				'subtitle' => esc_html__('Upload SVG image', 'codeweber'),
				'required' => array('page-loader-type', '=', 'custom'),
			),
			array(
				'id'       => 'page-loader-custom-class',
				'type'     => 'text',
				'title'    => esc_html__('Custom Class', 'codeweber'),
				'subtitle' => esc_html__('If filled, the background color below will not be applied', 'codeweber'),
				'default'  => '',
				'required' => array('page-loader', '=', true),
			),
			array(
				'id'       => 'page-loader-bg',
				'type'     => 'select',
				'title'    => esc_html__('Loader Background Color', 'codeweber'),
				'options'  => call_user_func(function () {
					$opts = array();
					$file = get_template_directory() . '/components/colors.json';
					if (file_exists($file)) {
						$data = json_decode(file_get_contents($file), true);
						if (is_array($data)) {
							foreach ($data as $c) {
								$opts[$c['value']] = esc_html__($c['label'], 'codeweber');
							}
						}
					}
					return $opts;
				}),
				'default'  => 'white',
				'required' => array('page-loader', '=', true),
			),

		),
	)
);


// Подсекция: Global Social Style — глобальные настройки иконок (соцсети, кнопки)
Redux::set_section(
	$opt_name,
	array(
		'title'       => esc_html__('Global Social Style', 'codeweber'),
		'id'          => 'codeweber-icons',
		'subsection'  => true,
		'parent'      => 'themestyle',
		'desc'        => esc_html__('Global icon settings (social links, vacancy sidebar, etc.). Header and footer can have their own overrides.', 'codeweber'),
		'fields'      => array(
			array(
				'id'       => 'global-social-icon-type',
				'type'     => 'button_set',
				'title'    => esc_html__('Social Icon Type (global)', 'codeweber'),
				'subtitle' => esc_html__('Default style for social/contact icons across the theme.', 'codeweber'),
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
			),
			array(
				'id'       => 'global-social-button-style',
				'type'     => 'button_set',
				'title'    => esc_html__('Social Button Style (global)', 'codeweber'),
				'options'  => array(
					'circle' => esc_html__('Circle', 'codeweber'),
					'block'  => esc_html__('Block', 'codeweber'),
				),
				'default'  => 'circle',
			),
			array(
				'id'       => 'global-social-button-size',
				'type'     => 'button_set',
				'title'    => esc_html__('Social Button Size (global)', 'codeweber'),
				'options'  => array(
					'sm' => esc_html__('Small', 'codeweber'),
					'md' => esc_html__('Medium', 'codeweber'),
					'lg' => esc_html__('Large', 'codeweber'),
				),
				'default'  => 'md',
			),
		),
	)
);

// Подсекция: Page Header — настройки заголовка страницы как отдельное подменю
Redux::set_section(
	$opt_name,
	array(
		'title'      => esc_html__('Page Header', 'codeweber'),
		'id'         => 'theme-page-header',
		'subsection' => true,
		'parent'     => 'themestyle',
		'fields'     => array(
			array(
				'id'       => 'opt-select-title-size',
				'type'     => 'select',
				'title'    => esc_html__('Title size', 'codeweber'),
				'options'  => array(
					// Display classes
					'display-1' => 'Display 1',
					'display-2' => 'Display 2',
					'display-3' => 'Display 3',
					'display-4' => 'Display 4',
					'display-5' => 'Display 5',
					'display-6' => 'Display 6',

					// HTML headings
					'h1' => 'Heading 1',
					'h2' => 'Heading 2',
					'h3' => 'Heading 3',
					'h4' => 'Heading 4',
					'h5' => 'Heading 5',
					'h6' => 'Heading 6',
				),
				'default'  => 'display-1',
			),
		),
	)
);

Redux::set_section(
	$opt_name,
	array(
		'title'       => esc_html__('Fonts', 'codeweber'),
		'id'          => 'themefonts',
		'subsection'  => true,
		'parent'      => 'themestyle',
		'fields'      => $font_fields,
	)
);

Redux::set_section(
	$opt_name,
	array(
		'title'       => esc_html__('Gulp', 'codeweber'),
		'id'          => 'themegulp',
		'subsection'  => true,
		'parent'      => 'themestyle',
		'fields'      => $theme_gulp,
	)
);

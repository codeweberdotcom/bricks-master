<?php

$color_theme_files = array(
	'default' => 'default'
);

$directory = get_template_directory() . '/dist/css/colors';

if (file_exists($directory)) {
	$files = scandir($directory);
	foreach ($files as $file) {
		if (pathinfo($file, PATHINFO_EXTENSION) === 'css') {
			$filename = pathinfo($file, PATHINFO_FILENAME);
			$color_theme_files[$filename] = $filename;
		}
	}
}


$sample_html = '<button  class="button button-primary">Запустить сборщик GULP</button>';

Redux::set_section(
	$opt_name,
	array(
		'title'            => esc_html__("Theme style", "codeweber"),
		'id'               => 'themestyle',
		'customizer_width' => '300px',
		'icon'             => 'el el-home',
		'fields'           => array(
			array(
				'id'       => 'themestylecolor',
				'type'     => 'accordion',
				'title'    => esc_html__('Theme style', 'codeweber'),
				'position' => 'start',
			),
			array(
				'id'       => 'opt-select-color-theme',
				'type'     => 'select',
				'title'    => esc_html__('Theme color', 'codeweber'),


				// Must provide key => value pairs for select options.
				'options'  => $color_theme_files,
				'default'  => 'default',
			),

			array(
				'id'       => 'opt-button-select-style',
				'type'     => 'image_select',
				'title'    => esc_html__('Button Style', 'codeweber'),

				// Must provide key => value(array:title|img) pairs for radio options.
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
				'id'       => 'page-frame',
				'type'     => 'switch',
				'title'    => esc_html__('Page-frame', 'codeweber'),
				'default'  => false,
			),
			array(
				'id'       => 'page-loader',
				'type'     => 'switch',
				'title'    => esc_html__('Page-loader', 'codeweber'),
				'default'  => false,
			),
			array(
				'id'       => 'themegulp',
				'type'     => 'accordion',
				'title'    => esc_html__('Gulp', 'codeweber'),
				'position' => 'start',
			),
			array(
				'id'       => 'opt-gulp-variation',
				'type'     => 'textarea',
				'title'    => esc_html__('Gulp Sass Variables', 'codeweber'),
				'default'  => '',
			),
			array(
				'id'       => 'opt-gulp-variation',
				'type'     => 'textarea',
				'title'    => esc_html__('Gulp JS Variables', 'codeweber'),
				'default'  => '',
			),
			array(
				'id'       => 'opt-raw_info_4333',
				'type'     => 'raw',
				'title'    => esc_html__('Standard Raw Field', 'codeweber'),

				'content'  => $sample_html,
			),
			array(
				'id'       => 'themefonts',
				'type'     => 'accordion',
				'title'    => esc_html__('Fonts', 'codeweber'),
				'position' => 'start',
			),
		),
	)
);

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
				'id'       => 'themestylecolor',
				'type'     => 'accordion',
				'title'    => esc_html__('Theme style', 'codeweber'),
				'position' => 'start',
			),
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
				'title'    => esc_html__('Скругление Card и Image', 'codeweber'),
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
				'default'  => '2',
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
				'id'       => 'themepageheader',
				'type'     => 'accordion',
				'title'    => esc_html__('Page Header', 'codeweber'),
				'position' => 'start',
			),

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
		'title'    => esc_html__('Fonts', 'codeweber'),
		'id'       => 'themefonts',
		'subsection' => true,   // <-- вот это делает её субсекцией
		'fields'   => $font_fields,
	)
);

Redux::set_section(
	$opt_name,
	array(
		'title'    => esc_html__('Gulp', 'codeweber'),
		'id'       => 'themegulp',
		'subsection' => true,   // <-- вот это делает её субсекцией
		'fields'   => $theme_gulp,
	),

);

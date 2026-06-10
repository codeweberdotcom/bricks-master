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
				'id'       => 'opt-color-switcher-enabled',
				'type'     => 'switch',
				'title'    => esc_html__('Floating color switcher', 'codeweber'),
				'subtitle' => esc_html__('Show a floating widget for visitors to preview theme colors (for demo / sites for sale). Client-side only (sessionStorage), not saved to settings.', 'codeweber'),
				'default'  => false,
			),
			array(
				'id'       => 'opt-color-switcher-position',
				'type'     => 'button_set',
				'title'    => esc_html__('Color switcher position', 'codeweber'),
				'options'  => array(
					'left'  => esc_html__('Left', 'codeweber'),
					'right' => esc_html__('Right', 'codeweber'),
				),
				'default'  => 'left',
				'required' => array('opt-color-switcher-enabled', '=', true),
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
				'id'       => 'opt-logo-type',
				'type'     => 'button_set',
				'title'    => esc_html__('Logo type', 'codeweber'),
				'subtitle' => esc_html__('Use an uploaded image or an inline SVG icon with text.', 'codeweber'),
				'options'  => array(
					'image'    => esc_html__('Image', 'codeweber'),
					'text_svg' => esc_html__('Text + SVG', 'codeweber'),
				),
				'default'  => 'image',
			),
			array(
				'id'       => 'opt-logo-dark-svg',
				'type'     => 'ace_editor',
				'title'    => esc_html__('Logo SVG — Dark', 'codeweber'),
				'subtitle' => esc_html__('Inline SVG markup shown on light backgrounds. Use currentColor to inherit text color.', 'codeweber'),
				'mode'     => 'html',
				'theme'    => 'monokai',
				'default'  => '<svg width="42" height="42" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M21 0C32.598 0 42 9.40202 42 21C42 32.598 32.598 42 21 42C9.40202 42 0 32.598 0 21C0 9.40202 9.40202 0 21 0ZM21 2.5C10.7827 2.5 2.5 10.7827 2.5 21C2.5 31.2173 10.7827 39.5 21 39.5C31.2173 39.5 39.5 31.2173 39.5 21C39.5 10.7827 31.2173 2.5 21 2.5ZM15 11.4502C16.9605 11.4503 18.5498 13.0395 18.5498 15C18.5498 15.6723 18.3628 16.301 18.0381 16.8369L20.999 19.7969L28.3984 12.3994C28.7304 12.0675 29.2686 12.0675 29.6006 12.3994C29.9322 12.7313 29.9322 13.2687 29.6006 13.6006L21.6064 21.5928C21.6042 21.5951 21.6029 21.5983 21.6006 21.6006C21.5983 21.6029 21.5951 21.6042 21.5928 21.6064L18.0381 25.1621C18.3631 25.6982 18.5498 26.3273 18.5498 27C18.5497 28.9605 16.9605 30.5497 15 30.5498C13.0395 30.5498 11.4503 28.9605 11.4502 27C11.4502 25.0394 13.0394 23.4502 15 23.4502C15.6719 23.4502 16.3003 23.6367 16.8359 23.9609L19.7969 20.999L16.8369 18.0381C16.301 18.3628 15.6723 18.5498 15 18.5498C13.0395 18.5498 11.4503 16.9605 11.4502 15C11.4502 13.0394 13.0394 11.4502 15 11.4502ZM22.8994 22.8994C23.2314 22.5675 23.7686 22.5675 24.1006 22.8994L29.6006 28.3994C29.9325 28.7314 29.9325 29.2687 29.6006 29.6006C29.2687 29.9325 28.7314 29.9325 28.3994 29.6006L22.8994 24.1006C22.5675 23.7686 22.5675 23.2314 22.8994 22.8994ZM15 25.1494C13.9783 25.1494 13.1494 25.9783 13.1494 27C13.1495 28.0216 13.9783 28.8496 15 28.8496C16.0216 28.8495 16.8495 28.0216 16.8496 27C16.8496 25.9783 16.0216 25.1495 15 25.1494ZM15 13.1494C13.9783 13.1494 13.1494 13.9783 13.1494 15C13.1495 16.0216 13.9783 16.8496 15 16.8496C16.0216 16.8495 16.8495 16.0216 16.8496 15C16.8496 13.9783 16.0216 13.1495 15 13.1494Z" fill="currentColor"/></svg>',
				'args'     => array(
					'minLines' => 6,
					'maxLines' => 20,
				),
				'required' => array('opt-logo-type', '=', 'text_svg'),
			),
			array(
				'id'       => 'opt-logo-dark-text',
				'type'     => 'text',
				'title'    => esc_html__('Logo Text — Dark', 'codeweber'),
				'subtitle' => esc_html__('Text shown on light backgrounds.', 'codeweber'),
				'default'  => 'CODEWEBER',
				'required' => array('opt-logo-type', '=', 'text_svg'),
			),
			array(
				'id'       => 'opt-logo-dark-text-class',
				'type'     => 'text',
				'title'    => esc_html__('Logo Text CSS classes — Dark', 'codeweber'),
				'subtitle' => esc_html__('Extra classes for the dark-variant text (Bootstrap / theme).', 'codeweber'),
				'default'  => 'text-uppercase text-dark ms-2 fw-bold fs-25',
				'required' => array('opt-logo-type', '=', 'text_svg'),
			),
			array(
				'id'       => 'opt-logo-light-svg',
				'type'     => 'ace_editor',
				'title'    => esc_html__('Logo SVG — Light', 'codeweber'),
				'subtitle' => esc_html__('Inline SVG markup shown on dark backgrounds. Use currentColor to inherit text color.', 'codeweber'),
				'mode'     => 'html',
				'theme'    => 'monokai',
				'default'  => '<svg width="42" height="42" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M21 0C32.598 0 42 9.40202 42 21C42 32.598 32.598 42 21 42C9.40202 42 0 32.598 0 21C0 9.40202 9.40202 0 21 0ZM21 2.5C10.7827 2.5 2.5 10.7827 2.5 21C2.5 31.2173 10.7827 39.5 21 39.5C31.2173 39.5 39.5 31.2173 39.5 21C39.5 10.7827 31.2173 2.5 21 2.5ZM15 11.4502C16.9605 11.4503 18.5498 13.0395 18.5498 15C18.5498 15.6723 18.3628 16.301 18.0381 16.8369L20.999 19.7969L28.3984 12.3994C28.7304 12.0675 29.2686 12.0675 29.6006 12.3994C29.9322 12.7313 29.9322 13.2687 29.6006 13.6006L21.6064 21.5928C21.6042 21.5951 21.6029 21.5983 21.6006 21.6006C21.5983 21.6029 21.5951 21.6042 21.5928 21.6064L18.0381 25.1621C18.3631 25.6982 18.5498 26.3273 18.5498 27C18.5497 28.9605 16.9605 30.5497 15 30.5498C13.0395 30.5498 11.4503 28.9605 11.4502 27C11.4502 25.0394 13.0394 23.4502 15 23.4502C15.6719 23.4502 16.3003 23.6367 16.8359 23.9609L19.7969 20.999L16.8369 18.0381C16.301 18.3628 15.6723 18.5498 15 18.5498C13.0395 18.5498 11.4503 16.9605 11.4502 15C11.4502 13.0394 13.0394 11.4502 15 11.4502ZM22.8994 22.8994C23.2314 22.5675 23.7686 22.5675 24.1006 22.8994L29.6006 28.3994C29.9325 28.7314 29.9325 29.2687 29.6006 29.6006C29.2687 29.9325 28.7314 29.9325 28.3994 29.6006L22.8994 24.1006C22.5675 23.7686 22.5675 23.2314 22.8994 22.8994ZM15 25.1494C13.9783 25.1494 13.1494 25.9783 13.1494 27C13.1495 28.0216 13.9783 28.8496 15 28.8496C16.0216 28.8495 16.8495 28.0216 16.8496 27C16.8496 25.9783 16.0216 25.1495 15 25.1494ZM15 13.1494C13.9783 13.1494 13.1494 13.9783 13.1494 15C13.1495 16.0216 13.9783 16.8496 15 16.8496C16.0216 16.8495 16.8495 16.0216 16.8496 15C16.8496 13.9783 16.0216 13.1495 15 13.1494Z" fill="currentColor"/></svg>',
				'args'     => array(
					'minLines' => 6,
					'maxLines' => 20,
				),
				'required' => array('opt-logo-type', '=', 'text_svg'),
			),
			array(
				'id'       => 'opt-logo-light-text',
				'type'     => 'text',
				'title'    => esc_html__('Logo Text — Light', 'codeweber'),
				'subtitle' => esc_html__('Text shown on dark backgrounds.', 'codeweber'),
				'default'  => 'CODEWEBER',
				'required' => array('opt-logo-type', '=', 'text_svg'),
			),
			array(
				'id'       => 'opt-logo-light-text-class',
				'type'     => 'text',
				'title'    => esc_html__('Logo Text CSS classes — Light', 'codeweber'),
				'subtitle' => esc_html__('Extra classes for the light-variant text (Bootstrap / theme).', 'codeweber'),
				'default'  => 'text-uppercase text-white ms-2 fw-bold fs-25',
				'required' => array('opt-logo-type', '=', 'text_svg'),
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
		),
	)
);


// Подсекция: Phone Mask
Redux::set_section(
	$opt_name,
	array(
		'title'      => esc_html__( 'Phone Mask', 'codeweber' ),
		'id'         => 'theme-phone-mask',
		'subsection' => true,
		'parent'     => 'themestyle',
		'fields'     => array(
			array(
				'id'       => 'opt_phone_mask',
				'type'     => 'text',
				'title'    => esc_html__( 'Phone mask', 'codeweber' ),
				'subtitle' => esc_html__( 'Mask for phone input fields in built-in forms. Use _ for digit positions. Example: +7 (___) ___-__-__', 'codeweber' ),
				'default'  => '+7 (___) ___-__-__',
			),
		),
	)
);


// Подсекция: Page Loader
Redux::set_section(
	$opt_name,
	array(
		'title'      => esc_html__( 'Page Loader', 'codeweber' ),
		'id'         => 'theme-page-loader',
		'subsection' => true,
		'parent'     => 'themestyle',
		'fields'     => array(
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


// Подсекция: Sidebar Widgets — настройки виджетов сайдбара
Redux::set_section(
	$opt_name,
	array(
		'title'      => esc_html__( 'Sidebar Widgets', 'codeweber' ),
		'id'         => 'theme-sidebar-widgets',
		'subsection' => true,
		'parent'     => 'themestyle',
		'fields'     => array(
			array(
				'id'       => 'widget_heading_size',
				'type'     => 'select',
				'title'    => esc_html__( 'Widget Heading Size', 'codeweber' ),
				'subtitle' => esc_html__( 'CSS class applied to headings inside sidebar widgets (vacancies, events, FAQ, maps)', 'codeweber' ),
				'options'  => array(
					'h1'        => 'h1',
					'h2'        => 'h2',
					'h3'        => 'h3',
					'h4'        => 'h4',
					'h5'        => 'h5',
					'h6'        => 'h6',
					'display-1' => 'display-1',
					'display-2' => 'display-2',
					'display-3' => 'display-3',
					'display-4' => 'display-4',
					'display-5' => 'display-5',
					'display-6' => 'display-6',
				),
				'default'  => 'h3',
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
			array(
				'id'      => 'share_button_color',
				'type'    => 'select',
				'title'   => esc_html__( 'Share Button Color', 'codeweber' ),
				'subtitle' => esc_html__( 'Color of the "Share" dropdown button', 'codeweber' ),
				'options' => call_user_func( function () {
					$opts = array();
					$file = get_template_directory() . '/components/colors.json';
					if ( file_exists( $file ) ) {
						$data = json_decode( file_get_contents( $file ), true );
						if ( is_array( $data ) ) {
							foreach ( $data as $c ) {
								$opts[ $c['value'] ] = esc_html__( $c['label'], 'codeweber' );
							}
						}
					}
					return $opts;
				} ),
				'default' => 'red',
			),
			array(
				'id'      => 'share_button_type',
				'type'    => 'button_set',
				'title'   => esc_html__( 'Share Button Type', 'codeweber' ),
				'options' => array(
					'solid' => esc_html__( 'Solid', 'codeweber' ),
					'soft'  => esc_html__( 'Soft', 'codeweber' ),
				),
				'default' => 'solid',
			),
			array(
				'id'      => 'projects_nav_type',
				'type'    => 'button_set',
				'title'   => esc_html__( 'Projects Prev/Next Style', 'codeweber' ),
				'subtitle' => esc_html__( 'Style of prev/next links on single project page. Share button is always shown.', 'codeweber' ),
				'options' => array(
					'text'    => esc_html__( 'Text Links', 'codeweber' ),
					'buttons' => esc_html__( 'Buttons', 'codeweber' ),
				),
				'default' => 'text',
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
			array(
				'id'       => 'global-page-header-title-color',
				'type'     => 'button_set',
				'title'    => esc_html__( 'Title Text Color', 'codeweber' ),
				'options'  => array(
					'1' => esc_html__( 'Dark', 'codeweber' ),
					'2' => esc_html__( 'Light', 'codeweber' ),
				),
				'default'  => '1',
			),
			array(
				'id'       => 'custom_title_color_woocommerce',
				'type'     => 'button_set',
				'title'    => esc_html__( 'WooCommerce Archive Title Color', 'codeweber' ),
				'subtitle' => esc_html__( 'Override title color for WooCommerce shop/archive. Default = use global.', 'codeweber' ),
				'options'  => array(
					'global' => esc_html__( 'Global', 'codeweber' ),
					'1'      => esc_html__( 'Dark', 'codeweber' ),
					'2'      => esc_html__( 'Light', 'codeweber' ),
				),
				'default'  => 'global',
			),
		),
	)
);

// ── Подсекция: Grid Gutters ────────────────────────────────────────────
Redux::set_section(
	$opt_name,
	array(
		'title'      => esc_html__( 'Grid Gutters', 'codeweber' ),
		'id'         => 'theme-grid-gutters',
		'subsection' => true,
		'parent'     => 'themestyle',
		'desc'       => esc_html__( 'Global Bootstrap grid gap used in shop, archives and Gutenberg blocks with "Theme" gap type.', 'codeweber' ),
		'fields'     => array(

			array(
				'id'       => 'opt_grid_gap_preset',
				'type'     => 'select',
				'title'    => esc_html__( 'Grid Gap Preset', 'codeweber' ),
				'subtitle' => esc_html__( 'Choose a preset or set custom values below.', 'codeweber' ),
				'options'  => array(
					'compact'  => esc_html__( 'Compact — g-3 (15px)', 'codeweber' ),
					'normal'   => esc_html__( 'Normal — g-6 (30px)', 'codeweber' ),
					'wide'     => esc_html__( 'Wide — gy-10 gx-md-8', 'codeweber' ),
					'spacious' => esc_html__( 'Spacious — gy-10 gy-md-13 gx-md-8', 'codeweber' ),
					'custom'   => esc_html__( 'Custom', 'codeweber' ),
				),
				'default'  => 'spacious',
			),

			array(
				'id'       => 'opt_grid_gap_x',
				'type'     => 'select',
				'title'    => esc_html__( 'Horizontal Gap (xs)', 'codeweber' ),
				'subtitle' => esc_html__( 'gx-* — all breakpoints. Leave empty to skip.', 'codeweber' ),
				'options'  => array(
					''   => esc_html__( '— none —', 'codeweber' ),
					'0'  => '0',
					'2'  => '2 (10px)',
					'3'  => '3 (15px)',
					'4'  => '4 (20px)',
					'5'  => '5 (25px)',
					'6'  => '6 (30px)',
					'7'  => '7 (35px)',
					'8'  => '8 (40px)',
					'9'  => '9 (45px)',
					'10' => '10 (50px)',
					'11' => '11 (60px)',
					'12' => '12 (70px)',
					'13' => '13 (80px)',
				),
				'default'  => '',
				'required' => [ 'opt_grid_gap_preset', '=', 'custom' ],
			),

			array(
				'id'       => 'opt_grid_gap_x_md',
				'type'     => 'select',
				'title'    => esc_html__( 'Horizontal Gap (md+)', 'codeweber' ),
				'subtitle' => esc_html__( 'gx-md-* — overrides horizontal gap from ≥768px.', 'codeweber' ),
				'options'  => array(
					''   => esc_html__( '— none —', 'codeweber' ),
					'0'  => '0',
					'2'  => '2 (10px)',
					'3'  => '3 (15px)',
					'4'  => '4 (20px)',
					'5'  => '5 (25px)',
					'6'  => '6 (30px)',
					'7'  => '7 (35px)',
					'8'  => '8 (40px)',
					'9'  => '9 (45px)',
					'10' => '10 (50px)',
					'11' => '11 (60px)',
					'12' => '12 (70px)',
					'13' => '13 (80px)',
				),
				'default'  => '',
				'required' => [ 'opt_grid_gap_preset', '=', 'custom' ],
			),

			array(
				'id'       => 'opt_grid_gap_y',
				'type'     => 'select',
				'title'    => esc_html__( 'Vertical Gap (xs)', 'codeweber' ),
				'subtitle' => esc_html__( 'gy-* — vertical gap at all breakpoints.', 'codeweber' ),
				'options'  => array(
					''   => esc_html__( '— none —', 'codeweber' ),
					'0'  => '0',
					'2'  => '2 (10px)',
					'3'  => '3 (15px)',
					'4'  => '4 (20px)',
					'5'  => '5 (25px)',
					'6'  => '6 (30px)',
					'7'  => '7 (35px)',
					'8'  => '8 (40px)',
					'9'  => '9 (45px)',
					'10' => '10 (50px)',
					'11' => '11 (60px)',
					'12' => '12 (70px)',
					'13' => '13 (80px)',
				),
				'default'  => '',
				'required' => [ 'opt_grid_gap_preset', '=', 'custom' ],
			),

			array(
				'id'       => 'opt_grid_gap_y_md',
				'type'     => 'select',
				'title'    => esc_html__( 'Vertical Gap (md+)', 'codeweber' ),
				'subtitle' => esc_html__( 'gy-md-* — overrides vertical gap from ≥768px.', 'codeweber' ),
				'options'  => array(
					''   => esc_html__( '— none —', 'codeweber' ),
					'0'  => '0',
					'2'  => '2 (10px)',
					'3'  => '3 (15px)',
					'4'  => '4 (20px)',
					'5'  => '5 (25px)',
					'6'  => '6 (30px)',
					'7'  => '7 (35px)',
					'8'  => '8 (40px)',
					'9'  => '9 (45px)',
					'10' => '10 (50px)',
					'11' => '11 (60px)',
					'12' => '12 (70px)',
					'13' => '13 (80px)',
				),
				'default'  => '',
				'required' => [ 'opt_grid_gap_preset', '=', 'custom' ],
			),

			array(
				'id'       => 'content_padding_mobile',
				'type'     => 'select',
				'title'    => esc_html__( 'Content & Sidebar Padding (mobile)', 'codeweber' ),
				'subtitle' => esc_html__( 'Vertical padding on screens below md breakpoint.', 'codeweber' ),
				'options'  => array(
					'py-4'  => 'py-4',
					'py-6'  => 'py-6',
					'py-8'  => 'py-8',
					'py-10' => 'py-10',
					'py-12' => 'py-12',
					'py-14' => 'py-14',
				),
				'default'  => 'py-10',
			),

			array(
				'id'       => 'content_padding_desktop',
				'type'     => 'select',
				'title'    => esc_html__( 'Content & Sidebar Padding (desktop)', 'codeweber' ),
				'subtitle' => esc_html__( 'Vertical padding from md breakpoint and above.', 'codeweber' ),
				'options'  => array(
					'py-8'  => 'py-8',
					'py-10' => 'py-10',
					'py-12' => 'py-12',
					'py-14' => 'py-14',
					'py-16' => 'py-16',
					'py-20' => 'py-20',
				),
				'default'  => 'py-14',
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

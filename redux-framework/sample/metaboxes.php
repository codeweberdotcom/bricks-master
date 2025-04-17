<?php
/**
 * Redux Framework Sample Metabox Config File
 * For full documentation, please visit: https://devs.redux.io/
 *
 * @package Redux Framework
 *
 * @noinspection PhpUndefinedVariableInspection
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Redux_Metaboxes' ) ) {
	return;
}

// Проверка наличия записей типа "header"
$header_posts = get_posts(array(
	'post_type'      => 'header',
	'posts_per_page' => -1, // Проверяем только наличие хотя бы одной записи
));

// Проверка наличия записей типа "footer"
$footer_posts = get_posts(array(
	'post_type'      => 'footer',
	'posts_per_page' => -1, // Проверяем только наличие хотя бы одной записи
));


// Проверка наличия записей типа "page-header"
$pageheader_posts = get_posts(array(
	'post_type'      => 'page-header',
	'posts_per_page' => -1, // Проверяем только наличие хотя бы одной записи
));

$no_headers_message = '';
if (empty($header_posts)) {
	$no_headers_message = sprintf(
		esc_html__('No custom headers found. You can create one by visiting the following link: %s', 'codeweber'),
		'<a href="' . esc_url(admin_url('edit.php?post_type=header')) . '" target="_blank">' . esc_html__('Create Custom Header', 'codeweber') . '</a>'
	);
} else {
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


$no_pageheader_message = '';
if (empty($pageheader_posts)) {
	$no_pageheader_message = sprintf(
		esc_html__('No custom Page Header found. You can create one by visiting the following link: %s', 'codeweber'),
		'<a href="' . esc_url(admin_url('edit.php?post_type=page-header')) . '" target="_blank">' . esc_html__('Create Custom Page Header', 'codeweber') . '</a>'
	);
} else {
	$no_pageheader_message = esc_html__('Select Page Header from Custom Page Headers', 'codeweber');
}

Redux_Metaboxes::set_box(
	$opt_name,
	array(
		'id'         => 'opt-metaboxes',
		'title'      => esc_html__( 'This Post Settings', 'codeweber' ),
		'post_types' => array( 'page', 'post', 'faq', 'projects', 'services', 'staff', 'clients', 'offices' ),
		'position'   => 'normal', // normal, advanced, side.
		'priority'   => 'high', // high, core, default, low.
		'sections'   => array(
			array(
				'title'  => esc_html__( 'Main Options', 'codeweber' ),
				'id'     => 'main-post-fields',
				'desc'   => '',
				'icon'   => 'el-icon-cogs',
				'fields' => array(
					array(
						'id'       => 'container-on',
						'type'     => 'switch',
						'title'    => esc_html__('Container', 'codeweber'),
						'subtitle' => esc_html__('Enable container for content', 'codeweber'),
						'default'  => 2,
					),

				),
			),

			array(
				'title'      => esc_html__( 'Header Settings', 'codeweber' ),
				'desc'       => '',
				'icon'       => 'el-icon-cog',
				'id'         => 'this-header-settings',
				'subsection' => false,
				'fields'     => array(

					// Управление Header Type
					array(
						'id'       => 'this-header-type',
						'type'     => 'button_set',
						'title'    => esc_html__('Select Header type for This Page', 'codeweber'),
						'desc' => esc_html__('Select Header type for This Page', 'codeweber'),
						'options'  => array(
							'1' => esc_html__('Default', 'codeweber'),
							'2' => esc_html__('Custom', 'codeweber'),
						),
						'default'  => '1',
					),


					array(
						'id'       => 'this-custom-post-header',
						'type'     => 'select',
						'title'    => esc_html__('Select Header', 'codeweber'),
						'desc'     => $no_headers_message,
						'data'     => 'posts',
						'args'     => array(
							'post_type' => 'header',
							'posts_per_page' => -1,
						),
						'default'  => '',
						'required' => array('this-header-type', '=', '2'),

					),

					array(
						'id'       => 'this-logo-light-header',
						'type'     => 'media',
						'url'      => true,
						'title'    => esc_html__('Logo Light Header w/ URL', 'codeweber'),
						'compiler' => 'true',
						'desc'     => esc_html__('Basic media uploader with disabled URL input field.', 'codeweber'),
						'subtitle' => esc_html__('Upload any media using the WordPress native uploader', 'codeweber'),
						'required' => array('this-header-type', '=', '1'),
					),

					array(
						'id'       => 'this-logo-dark-header',
						'type'     => 'media',
						'url'      => true,
						'title'    => esc_html__('Logo Dark Header w/ URL', 'codeweber'),
						'compiler' => 'true',
						'desc'     => esc_html__('Basic media uploader with disabled URL input field.', 'codeweber'),
						'subtitle' => esc_html__('Upload any media using the WordPress native uploader', 'codeweber'),
						'required' => array('this-header-type', '=', '1'),
					),
				),
			),



			array(
				'title'      => esc_html__('Footer Settings', 'codeweber'),
				'desc'       => '',
				'icon'       => 'el-icon-cog',
				'id'         => 'this-footer-settings',
				'subsection' => false,
				'fields'     => array(


					// Управление Footer Type
					array(
						'id'       => 'this-post-footer-type',
						'type'     => 'button_set',
						'title'    => esc_html__('Select Footer type for This Page', 'codeweber'),
						'desc' => esc_html__('Select Footer type for This Page', 'codeweber'),
						'options'  => array(
							'1' => esc_html__('Default', 'codeweber'),
							'2' => esc_html__('Custom', 'codeweber'),
						),
						'default'  => '1',
					),


					array(
						'id'       => 'custom-post-footer',
						'type'     => 'select',
						'title'    => esc_html__('Select Footer', 'codeweber'),
						'desc'     => $no_footers_message,
						'data'     => 'posts',
						'args'     => array(
							'post_type' => 'footer',
							'posts_per_page' => -1,
						),
						'default'  => '',
						'required' => array('this-post-footer-type', '=', '2'),
					),

					array(
						'id'       => 'this-logo-light-footer',
						'type'     => 'media',
						'url'      => true,
						'title'    => esc_html__('Logo Light Footer w/ URL', 'codeweber'),
						'compiler' => 'true',
						'desc'     => esc_html__('Basic media uploader with disabled URL input field.', 'codeweber'),
						'subtitle' => esc_html__('Upload any media using the WordPress native uploader', 'codeweber'),
						'required' => array('this-post-footer-type', '=', '1'),
					),

					array(
						'id'       => 'this-logo-dark-footer',
						'type'     => 'media',
						'url'      => true,
						'title'    => esc_html__('Logo Dark Footer w/ URL', 'codeweber'),
						'compiler' => 'true',
						'desc'     => esc_html__('Basic media uploader with disabled URL input field.', 'codeweber'),
						'subtitle' => esc_html__('Upload any media using the WordPress native uploader', 'codeweber'),
						'required' => array('this-post-footer-type', '=', '1'),
					),
				),
			),

			array(
				'title'  => esc_html__( 'Page Header Settings', 'codeweber' ),
				'desc'   => '',
				'icon'   => 'el-icon-pencil',
				'id'     => 'this-page-header',
				'fields' => array(

					// Управление Page Header
					array(
						'id'       => 'this-page-header-type',
						'type'     => 'button_set',
						'title'    => esc_html__('Select Page Header type for This Page', 'codeweber'),
						'desc' => esc_html__('Select Page Header type for This Page', 'codeweber'),
						'options'  => array(
							'1' => esc_html__('Default', 'codeweber'),
							'2' => esc_html__('Custom', 'codeweber'),
						),
						'default'  => '1',
					),


					// Выбор заголовка (page-header)
					array(
						'id'       => 'this-custom-page-header',
						'type'     => 'select',
						'title'    => esc_html__('Select Page Header', 'codeweber'),
						'subtitle' => esc_html__('Choose a custom Page Header for this page.', 'codeweber'),
						'desc'     => $no_pageheader_message,
						'data'     => 'posts', // Используем тип записи
						'args'     => array(
							'post_type' => 'page-header', // Указываем тип записи
							'posts_per_page' => -1,
						),
						'default'  => '',
						'required' => array('this-page-header-type', '=', '2'),




					),


				),
			),



			array(
				'title'  => esc_html__('Sidebar Settings', 'codeweber'),
				'desc'   => '',
				'icon'   => 'el-icon-pencil',
				'id'     => 'page-sidebar-settings',
				'fields'           => array(


					// Управление сайдбаром
					array(
						'id'       => 'custom-page-sidebar-type',
						'type'     => 'button_set',
						'title'    => esc_html__('Select Sidebar type for This Page', 'codeweber'),
						'desc' => esc_html__('Select Sidebar type for This Page', 'codeweber'),
						'options'  => array(
							'1' => esc_html__('Default', 'codeweber'),
							'2' => esc_html__('Custom', 'codeweber'),
						),
						'default'  => '1',
					),


					// Управление сайдбаром
					array(
						'id'       => 'custom-page-sidebar-position',
						'type'     => 'button_set',
						'title'    => esc_html__('Select Sidebar position for This Page', 'codeweber'),
						'desc' => esc_html__('Select Sidebar position for This Page', 'codeweber'),
						'options'  => array(
							'1' => esc_html__('Left Sidebar', 'codeweber'),
							'2' => esc_html__('Disable Sidebar', 'codeweber'),
							'3' => esc_html__('Right Sidebar', 'codeweber'),
						),
						'default'  => '1',
						'required' => array('custom-page-sidebar-type', '=', '2'),
					),

					// Выбор области виджета
					array(
						'id'       => 'custom-page-sidebar-widget',
						'type'     => 'select',
						'data'     => 'sidebars', // Используем доступные сайдбары
						'title'    => esc_html__('Select Widget Area', 'codeweber'),
						'subtitle' => esc_html__('Choose a widget area to display on this page.', 'codeweber'),
						'desc'     => esc_html__('Select a sidebar widget area for customization.', 'codeweber'),
						'required' => array('custom-page-sidebar-type', '=', '2'),
					),

				),
			),
		),
	)
);


Redux_Metaboxes::set_box(
	$opt_name,
	array(
		'id'         => 'opt-metaboxes-2',
		'post_types' => array( 'page', 'post' ),
		'position'   => 'side', // normal, advanced, side.
		'priority'   => 'high', // high, core, default, low.
		'sections'   => array(
			array(
				'icon_class' => 'icon-large',
				'icon'       => 'el-icon-home',
				'fields'     => array(
					array(
						'title'   => esc_html__( 'Cross Box Required', 'codeweber' ),
						'desc'    => esc_html__( 'Required arguments work across metaboxes! Click on Color Field under Metabox Options then adjust this field to see the fields within show or hide.', 'codeweber' ),
						'id'      => 'opt-layout',
						'type'    => 'radio',
						'options' => array(
							'on'  => esc_html__( 'On', 'codeweber' ),
							'off' => esc_html__( 'Off', 'codeweber' ),
						),
						'default' => 'on',
					),
				),
			),
		),
	)
);

Redux_Metaboxes::set_box(
	$opt_name,
	array(
		'id'         => 'opt-metaboxes-3',
		'post_types' => array( 'page', 'post' ),
		'position'   => 'side', // normal, advanced, side.
		'priority'   => 'high', // high, core, default, low.
		'sections'   => array(
			array(
				'icon_class' => 'icon-large',
				'icon'       => 'el-icon-home',
				'fields'     => array(
					array(
						'id'      => 'sidebar',
						'title'   => esc_html__( 'Sidebar', 'codeweber' ),
						'desc'    => esc_html__( 'Please select the sidebar you would like to display on this page. Note: You must first create the sidebar under Appearance > Widgets.', 'codeweber' ),
						'type'    => 'select',
						'data'    => 'sidebars',
						'default' => 'None',
					),
				),
			),
		),
	)
);

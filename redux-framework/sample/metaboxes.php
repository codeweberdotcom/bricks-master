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
			$soft_color_options['soft-'. $color['value']] = esc_html__('Soft-'. $color['label'], 'codeweber');
		}
	}
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
		'post_types' => array( 'page', 'post', 'faq', 'projects', 'services', 'staff', 'clients', 'offices', 'legal', 'product' ),
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
							'4' => esc_html__('Base Settings', 'codeweber'),
							'3' => esc_html__('Disable', 'codeweber'),
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
						'id'       => 'custom-logo-light-header',
						'type'     => 'media',
						'url'      => true,
						'title'    => esc_html__('Logo Light Header w/ URL', 'codeweber'),
						'compiler' => 'true',
						'desc'     => esc_html__('Basic media uploader with disabled URL input field.', 'codeweber'),
						'subtitle' => esc_html__('Upload any media using the WordPress native uploader', 'codeweber'),
						'required' => array('this-header-type', '=', '1'),
					),

					array(
						'id'       => 'custom-logo-dark-header',
						'type'     => 'media',
						'url'      => true,
						'title'    => esc_html__('Logo Dark Header w/ URL', 'codeweber'),
						'compiler' => 'true',
						'desc'     => esc_html__('Basic media uploader with disabled URL input field.', 'codeweber'),
						'subtitle' => esc_html__('Upload any media using the WordPress native uploader', 'codeweber'),
						'required' => array('this-header-type', '=', '1'),
					),

					// ========== ПОЛЯ ДЛЯ ТИПА 'Base Settings' (4) ==========
					
					// Header rounded
					array(
						'id'       => 'this-header-rounded',
						'type'     => 'button_set',
						'title'    => esc_html__('Header rounded', 'codeweber'),
						'options'  => array(
							'1' => esc_html__('rounded', 'codeweber'),
							'2' => esc_html__('rounded-pill', 'codeweber'),
							'3' => esc_html__('none', 'codeweber'),
						),
						'default'  => '1',
						'required' => array('this-header-type', '=', '4'),
					),

					// Header text color
					array(
						'id'       => 'this-header-color-text',
						'type'     => 'button_set',
						'title'    => esc_html__('Header text color', 'codeweber'),
						'options'  => array(
							'1' =>  esc_html__('Dark', 'codeweber'),
							'2' =>  esc_html__('Light', 'codeweber'),
						),
						'default'  => '1',
						'required' => array('this-header-type', '=', '4'),
					),

					// Header background type
					array(
						'id'       => 'this-header-background',
						'type'     => 'button_set',
						'title'    => esc_html__('Select type Header background', 'codeweber'),
						'options'  => array(
							'1' => esc_html__('Solid-Color', 'codeweber'),
							'2' => esc_html__('Soft-Color', 'codeweber'),
							'3' => esc_html__('Transparent', 'codeweber'),
						),
						'default'  => '1',
						'required' => array('this-header-type', '=', '4'),
					),

					// Solid-Color select
					array(
						'id'       => 'this-solid-color-header',
						'type'     => 'select',
						'title'    => esc_html__('Select Header Background Solid Color', 'codeweber'),
						'options'  => $color_options,
						'default'  => 'light',
						'required' => array(
							array('this-header-background', '=', '1'),
							array('this-header-type', '=', '4')
						),
					),

					// Soft-Color select
					array(
						'id'       => 'this-soft-color-header',
						'type'     => 'select',
						'title'    => esc_html__('Select Header Background Soft Color', 'codeweber'),
						'options'  => $soft_color_options,
						'default'  => 'soft-red',
						'required' => array(
							array('this-header-background', '=', '2'),
							array('this-header-type', '=', '4')
						),
					),

					// Base Header Models
					array(
						'id'       => 'this-global-header-model',
						'type'     => 'image_select',
						'title'    => esc_html__('Base Header Models', 'codeweber'),
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
						'required' => array('this-header-type', '=', '4'),
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
							'3' => esc_html__('Disable', 'codeweber'),
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
							'3' => esc_html__('Disable', 'codeweber'),
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
							'left' => esc_html__('Left Sidebar', 'codeweber'),
							'none' => esc_html__('Disable Sidebar', 'codeweber'),
							'right' => esc_html__('Right Sidebar', 'codeweber'),
						),
						'default'  => 'left',
						'required' => array('custom-page-sidebar-type', '=', '2'),
					),



				),
			),
		),
	)
);


// Project Gallery: replaced by FilePond + SortableJS metabox (see project-gallery-metabox.php).
// Redux opt-metaboxes-2 (slides) removed for post type 'projects'.

Redux_Metaboxes::set_box(
	$opt_name,
	array(
		'id'         => 'opt-metaboxes-3',
		'post_types' => array('page', 'post', 'faq', 'projects', 'services', 'staff', 'clients', 'offices', 'legal', 'product'),
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

// Modal Settings Metabox
Redux_Metaboxes::set_box(
	$opt_name,
	array(
		'id'         => 'opt-metaboxes-modal',
		'title'      => esc_html__( 'Modal Settings', 'codeweber' ),
		'post_types' => array( 'modal' ),
		'position'   => 'normal', // normal, advanced, side.
		'priority'   => 'high', // high, core, default, low.
		'sections'   => array(
			array(
				'title'      => esc_html__( 'Bootstrap Modal Options', 'codeweber' ),
				'icon_class' => 'icon-large',
				'icon'       => 'el-icon-screen',
				'fields'     => array(
					array(
						'id'       => 'modal-size',
						'type'     => 'select',
						'title'    => esc_html__( 'Modal Size', 'codeweber' ),
						'subtitle' => esc_html__( 'Select the size of the modal window', 'codeweber' ),
						'desc'     => esc_html__( 'Choose from Bootstrap modal size options', 'codeweber' ),
						'options'  => array(
							''                               => esc_html__( 'Default', 'codeweber' ),
							'modal-sm'                       => esc_html__( 'Small (modal-sm)', 'codeweber' ),
							'modal-lg'                       => esc_html__( 'Large (modal-lg)', 'codeweber' ),
							'modal-xl'                       => esc_html__( 'Extra Large (modal-xl)', 'codeweber' ),
							'modal-fullscreen'               => esc_html__( 'Full Screen', 'codeweber' ),
							'modal-fullscreen-sm-down'       => esc_html__( 'Full Screen Below SM', 'codeweber' ),
							'modal-fullscreen-md-down'       => esc_html__( 'Full Screen Below MD', 'codeweber' ),
							'modal-fullscreen-lg-down'       => esc_html__( 'Full Screen Below LG', 'codeweber' ),
							'modal-fullscreen-xl-down'       => esc_html__( 'Full Screen Below XL', 'codeweber' ),
							'modal-fullscreen-xxl-down'      => esc_html__( 'Full Screen Below XXL', 'codeweber' ),
						),
						'default'  => '',
					),
				),
			),
		),
	)
);

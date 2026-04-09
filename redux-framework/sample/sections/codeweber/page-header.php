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


// ── Родительская секция ───────────────────────────────────────────────────────
Redux::set_section(
	$opt_name,
	array(
		'title'            => esc_html__( 'Page Header', 'codeweber' ),
		'id'               => 'global-page-header',
		'customizer_width' => '300px',
		'icon'             => 'el el-home',
		'fields'           => array(
			array(
				'id'      => 'sidebar',
				'title'   => esc_html__( 'Sidebar', 'codeweber' ),
				'type'    => 'select',
				'data'    => 'sidebars',
				'default' => 'None',
			),
		),
	)
);

// ── Подсекция: Page Header Type ───────────────────────────────────────────────
Redux::set_section(
	$opt_name,
	array(
		'title'      => esc_html__( 'Page Header Type', 'codeweber' ),
		'id'         => 'global-page-header-type-sub',
		'subsection' => true,
		'parent'     => 'global-page-header',
		'fields'     => array(

			array(
				'id'      => 'global_page_header_type',
				'type'    => 'button_set',
				'title'   => esc_html__( 'Select Page-Header Type', 'codeweber' ),
				'options' => array(
					'1' => esc_html__( 'Base', 'codeweber' ),
					'2' => esc_html__( 'Custom', 'codeweber' ),
				),
				'default' => '1',
			),

			array(
				'id'       => 'global-page-header-aligns',
				'type'     => 'button_set',
				'title'    => esc_html__( 'Alignment', 'codeweber' ),
				'options'  => array(
					'1' => esc_html__( 'Left', 'codeweber' ),
					'2' => esc_html__( 'Center', 'codeweber' ),
					'3' => esc_html__( 'Right', 'codeweber' ),
				),
				'default'  => '1',
				'required' => array( 'global_page_header_type', '=', '1' ),
			),

			array(
				'id'       => 'custom_page_header',
				'type'     => 'select',
				'title'    => esc_html__( 'Custom Page-Header', 'codeweber' ),
				'data'     => 'posts',
				'args'     => array(
					'post_type'      => 'page-header',
					'posts_per_page' => -1,
				),
				'required' => array( 'global_page_header_type', '=', '2' ),
				'desc'     => $no_page_headers_message,
			),

			array(
				'id'       => 'global_page_header_model',
				'type'     => 'image_select',
				'title'    => esc_html__( 'Base Page Header Models', 'codeweber' ),
				'options'  => array(
					'1' => array( 'title' => esc_html__( 'Page Header Type 1', 'codeweber' ), 'img' => get_template_directory_uri() . '/redux-framework/sample/patterns/pageheader_1.jpg', 'class' => 'header_viewport' ),
					'2' => array( 'title' => esc_html__( 'Page Header Type 2', 'codeweber' ), 'img' => get_template_directory_uri() . '/redux-framework/sample/patterns/pageheader_2.jpg', 'class' => 'header_viewport' ),
					'3' => array( 'title' => esc_html__( 'Page Header Type 3', 'codeweber' ), 'img' => get_template_directory_uri() . '/redux-framework/sample/patterns/pageheader_3.jpg', 'class' => 'header_viewport' ),
					'4' => array( 'title' => esc_html__( 'Page Header Type 4', 'codeweber' ), 'img' => get_template_directory_uri() . '/redux-framework/sample/patterns/pageheader_4.jpg', 'class' => 'header_viewport' ),
					'5' => array( 'title' => esc_html__( 'Page Header Type 5', 'codeweber' ), 'img' => get_template_directory_uri() . '/redux-framework/sample/patterns/pageheader_5.jpg', 'class' => 'header_viewport' ),
					'6' => array( 'title' => esc_html__( 'Page Header Type 6', 'codeweber' ), 'img' => get_template_directory_uri() . '/redux-framework/sample/patterns/pageheader_6.jpg', 'class' => 'header_viewport' ),
					'7' => array( 'title' => esc_html__( 'Page Header Type 7', 'codeweber' ), 'img' => get_template_directory_uri() . '/redux-framework/sample/patterns/pageheader_7.jpg', 'class' => 'header_viewport' ),
					'8' => array( 'title' => esc_html__( 'Page Header Type 8', 'codeweber' ), 'img' => get_template_directory_uri() . '/redux-framework/sample/patterns/pageheader_8.jpg', 'class' => 'header_viewport' ),
					'9' => array( 'title' => esc_html__( 'Page Header Type 9', 'codeweber' ), 'img' => get_template_directory_uri() . '/redux-framework/sample/patterns/pageheader_9.jpg', 'class' => 'header_viewport' ),
				),
				'default'  => '1',
				'required' => array( 'global_page_header_type', '=', '1' ),
			),

		),
	)
);

// ── Подсекция: Background ─────────────────────────────────────────────────────
Redux::set_section(
	$opt_name,
	array(
		'title'      => esc_html__( 'Background', 'codeweber' ),
		'id'         => 'global-page-header-background-sub',
		'subsection' => true,
		'parent'     => 'global-page-header',
		'fields'     => array(

			array(
				'id'       => 'global-page-header-background',
				'type'     => 'button_set',
				'title'    => esc_html__( 'Background Type', 'codeweber' ),
				'options'  => array(
					'1' => esc_html__( 'Solid', 'codeweber' ),
					'2' => esc_html__( 'Soft', 'codeweber' ),
					'3' => esc_html__( 'Image', 'codeweber' ),
					'4' => esc_html__( 'Pattern', 'codeweber' ),
					'5' => esc_html__( 'None', 'codeweber' ),
				),
				'default'  => '1',
				'required' => array( 'global_page_header_type', '=', '1' ),
			),

			array(
				'id'       => 'global-page-header-bg-solid-color',
				'type'     => 'select',
				'title'    => esc_html__( 'Solid Color', 'codeweber' ),
				'options'  => $color_options,
				'default'  => 'primary',
				'required' => array(
					array( 'global-page-header-background', '=', '1' ),
					array( 'global_page_header_type', '=', '1' ),
				),
			),

			array(
				'id'       => 'global-page-header-bg-soft-color',
				'type'     => 'select',
				'title'    => esc_html__( 'Soft Color', 'codeweber' ),
				'options'  => $soft_color_options,
				'default'  => 'soft-primary',
				'required' => array(
					array( 'global-page-header-background', '=', '2' ),
					array( 'global_page_header_type', '=', '1' ),
				),
			),

			array(
				'id'       => 'global-page-header-image',
				'type'     => 'media',
				'title'    => esc_html__( 'Background Image', 'codeweber' ),
				'required' => array(
					array( 'global-page-header-background', '=', '3' ),
					array( 'global_page_header_type', '=', '1' ),
				),
			),

			array(
				'id'       => 'global-page-header-pattern',
				'type'     => 'media',
				'title'    => esc_html__( 'Pattern Image', 'codeweber' ),
				'required' => array(
					array( 'global-page-header-background', '=', '4' ),
					array( 'global_page_header_type', '=', '1' ),
				),
			),

		),
	)
);

// ── Подсекция: Breadcrumbs ────────────────────────────────────────────────────
Redux::set_section(
	$opt_name,
	array(
		'title'      => esc_html__( 'Breadcrumbs', 'codeweber' ),
		'id'         => 'global-page-header-breadcrumbs-sub',
		'subsection' => true,
		'parent'     => 'global-page-header',
		'fields'     => array(

			array(
				'id'       => 'global-page-header-breadcrumb-enable',
				'type'     => 'switch',
				'title'    => esc_html__( 'Breadcrumbs', 'codeweber' ),
				'default'  => 1,
				'on'       => esc_html__( 'On', 'codeweber' ),
				'off'      => esc_html__( 'Off', 'codeweber' ),
				'required' => array( 'global_page_header_type', '=', '1' ),
			),

			array(
				'id'       => 'global-bredcrumbs-aligns',
				'type'     => 'button_set',
				'title'    => esc_html__( 'Alignment', 'codeweber' ),
				'options'  => array(
					'1' => esc_html__( 'Left', 'codeweber' ),
					'2' => esc_html__( 'Center', 'codeweber' ),
					'3' => esc_html__( 'Right', 'codeweber' ),
				),
				'default'  => '1',
				'required' => array(
					array( 'global-page-header-breadcrumb-enable', '=', '1' ),
					array( 'global_page_header_type', '=', '1' ),
				),
			),

			array(
				'id'       => 'global-page-header-breadcrumb-color',
				'type'     => 'button_set',
				'title'    => esc_html__( 'Breadcrumbs Color', 'codeweber' ),
				'options'  => array(
					'1' => esc_html__( 'Dark', 'codeweber' ),
					'2' => esc_html__( 'Light', 'codeweber' ),
					'3' => esc_html__( 'Muted', 'codeweber' ),
				),
				'default'  => '1',
				'required' => array(
					array( 'global-page-header-breadcrumb-enable', '=', '1' ),
					array( 'global_page_header_type', '=', '1' ),
				),
			),

			array(
				'id'       => 'global-page-header-breadcrumb-bg-color',
				'type'     => 'select',
				'title'    => esc_html__( 'Breadcrumb Background Color', 'codeweber' ),
				'options'  => $color_options,
				'default'  => 'primary',
				'required' => array(
					array( 'global-page-header-breadcrumb-enable', '=', '1' ),
					array( 'global_page_header_type', '=', '1' ),
				),
			),

			// ── Обычные страницы (записи, CPT, страницы) ──────────────────────────
			array(
				'id'   => 'breadcrumb_info_pages',
				'type' => 'info',
				'desc' => '<strong>' . esc_html__( 'Posts, pages, CPT', 'codeweber' ) . '</strong>',
			),

			array(
				'id'      => 'breadcrumb_show_home',
				'type'    => 'switch',
				'title'   => esc_html__( 'Show Home link', 'codeweber' ),
				'default' => true,
				'on'      => esc_html__( 'On', 'codeweber' ),
				'off'     => esc_html__( 'Off', 'codeweber' ),
				'required' => array(
					array( 'global-page-header-breadcrumb-enable', '=', '1' ),
				),
			),

			array(
				'id'          => 'breadcrumb_home_label',
				'type'        => 'text',
				'title'       => esc_html__( 'Home link label', 'codeweber' ),
				'subtitle'    => esc_html__( 'Leave empty to use default translation', 'codeweber' ),
				'placeholder' => esc_html__( 'Главная', 'codeweber' ),
				'default'     => '',
				'required'    => array(
					array( 'global-page-header-breadcrumb-enable', '=', '1' ),
					array( 'breadcrumb_show_home', '=', '1' ),
				),
			),

			array(
				'id'       => 'breadcrumb_hide_last_single',
				'type'     => 'switch',
				'title'    => esc_html__( 'Hide current page on single', 'codeweber' ),
				'subtitle' => esc_html__( 'Remove the last breadcrumb item (current page title) on single post/page/CPT', 'codeweber' ),
				'default'  => false,
				'on'       => esc_html__( 'On', 'codeweber' ),
				'off'      => esc_html__( 'Off', 'codeweber' ),
				'required' => array(
					array( 'global-page-header-breadcrumb-enable', '=', '1' ),
				),
			),

		),
	)
);

if ( class_exists( 'WooCommerce' ) ) {
// ── Подсекция: Breadcrumbs WooCommerce ───────────────────────────────────────
Redux::set_section(
	$opt_name,
	array(
		'title'      => esc_html__( 'Breadcrumbs: WooCommerce', 'codeweber' ),
		'id'         => 'global-page-header-breadcrumbs-woo-sub',
		'subsection' => true,
		'parent'     => 'global-page-header',
		'fields'     => array(

			array(
				'id'      => 'breadcrumb_woo_show_home',
				'type'    => 'switch',
				'title'   => esc_html__( 'Show Home link', 'codeweber' ),
				'default' => true,
				'on'      => esc_html__( 'On', 'codeweber' ),
				'off'     => esc_html__( 'Off', 'codeweber' ),
			),

			array(
				'id'          => 'breadcrumb_woo_home_label',
				'type'        => 'text',
				'title'       => esc_html__( 'Home link label', 'codeweber' ),
				'subtitle'    => esc_html__( 'Leave empty to use default translation', 'codeweber' ),
				'placeholder' => esc_html__( 'Главная', 'codeweber' ),
				'default'     => '',
				'required'    => array(
					array( 'breadcrumb_woo_show_home', '=', '1' ),
				),
			),

			array(
				'id'       => 'breadcrumb_woo_hide_last_single',
				'type'     => 'switch',
				'title'    => esc_html__( 'Hide current product on single', 'codeweber' ),
				'subtitle' => esc_html__( 'Remove the last breadcrumb item (product title) on single product page', 'codeweber' ),
				'default'  => false,
				'on'       => esc_html__( 'On', 'codeweber' ),
				'off'      => esc_html__( 'Off', 'codeweber' ),
			),

		),
	)
);
} // end if WooCommerce

<?php

/**
 * Возвращает список доступных шаблонов карточек товаров.
 * Автоматически сканирует templates/woocommerce/cards/.
 */
if ( ! function_exists( 'codeweber_get_product_card_options' ) ) {
	function codeweber_get_product_card_options() {
		$options = [];
		$dir     = get_template_directory() . '/templates/woocommerce/cards/';

		if ( is_dir( $dir ) ) {
			foreach ( scandir( $dir ) as $file ) {
				// Пропускаем вспомогательные файлы (начинаются с _)
				if ( pathinfo( $file, PATHINFO_EXTENSION ) === 'php' && substr( $file, 0, 1 ) !== '_' ) {
					$name           = pathinfo( $file, PATHINFO_FILENAME );
					$options[$name] = $name;
				}
			}
		}

		return $options ?: [ 'shop2' => 'shop2' ];
	}
}

// Родительский раздел WooCommerce (без полей — только контейнер для подразделов)
Redux::set_section(
	$opt_name,
	array(
		'title'            => esc_html__( 'Woocommerce', 'codeweber' ),
		'id'               => 'woocommerce-settings',
		'desc'             => '',
		'customizer_width' => '300px',
		'icon'             => 'el el-shopping-cart',
		'fields'           => array(),
	)
);

// ── Archive ───────────────────────────────────────────────────────────────────
Redux::set_section(
	$opt_name,
	array(
		'title'      => esc_html__( 'Archive', 'codeweber' ),
		'id'         => 'woocommerce-archive',
		'subsection' => true,
		'fields'     => array(

			array(
				'id'       => 'archive_template_select_product',
				'type'     => 'select',
				'title'    => esc_html__( 'Product Card Template', 'codeweber' ),
				'subtitle' => esc_html__( 'Select card style for WooCommerce shop archive', 'codeweber' ),
				'options'  => codeweber_get_product_card_options(),
				'default'  => 'shop2',
			),

			array(
				'id'       => 'woo_show_archive_title',
				'type'     => 'switch',
				'title'    => esc_html__( 'Archive Title', 'codeweber' ),
				'subtitle' => esc_html__( 'Show archive title (h2) above the product grid in the content area', 'codeweber' ),
				'default'  => false,
			),

			array(
				'id'       => 'woo_show_single_title',
				'type'     => 'switch',
				'title'    => esc_html__( 'Single Product Title in Page Header', 'codeweber' ),
				'subtitle' => esc_html__( 'When enabled, page header shows only breadcrumbs (title is in the product summary). When disabled, page header follows standard Redux settings.', 'codeweber' ),
				'default'  => true,
			),

			array(
				'id'       => 'woo_shop_load_more',
				'type'     => 'button_set',
				'title'    => esc_html__( 'Shop Navigation Mode', 'codeweber' ),
				'subtitle' => esc_html__( 'Choose how products are navigated on shop pages', 'codeweber' ),
				'options'  => array(
					'pagination' => esc_html__( 'Pagination', 'codeweber' ),
					'load_more'  => esc_html__( 'Load More', 'codeweber' ),
					'both'       => esc_html__( 'Both', 'codeweber' ),
				),
				'default'  => 'pagination',
			),

			// ── Per Page Switcher ─────────────────────────────────────────────
			array(
				'id'       => 'woo_show_per_page',
				'type'     => 'switch',
				'title'    => esc_html__( 'Per Page Switcher', 'codeweber' ),
				'subtitle' => esc_html__( 'Show buttons to switch number of products per page', 'codeweber' ),
				'default'  => true,
			),

			array(
				'id'       => 'woo_per_page_values',
				'type'     => 'text',
				'title'    => esc_html__( 'Per Page Values', 'codeweber' ),
				'subtitle' => esc_html__( 'Comma-separated list of values, e.g. 12,24,48', 'codeweber' ),
				'default'  => '12,24,48',
				'required' => [ 'woo_show_per_page', '=', true ],
			),

			// ── Default Columns per Breakpoint ───────────────────────────────
			array(
				'id'    => 'woo_cols_default_info',
				'type'  => 'info',
				'style' => 'info',
				'title' => esc_html__( 'Default Columns per Screen', 'codeweber' ),
				'desc'  => esc_html__( 'Initial column layout before the user switches. Applied when no column switcher is active.', 'codeweber' ),
			),

			array(
				'id'      => 'woo_cols_xs',
				'type'    => 'button_set',
				'title'   => esc_html__( 'Mobile (< 576px)', 'codeweber' ),
				'options' => [ '1' => '1', '2' => '2' ],
				'default' => '1',
			),

			array(
				'id'      => 'woo_cols_sm',
				'type'    => 'button_set',
				'title'   => esc_html__( 'SM (≥ 576px)', 'codeweber' ),
				'options' => [ '1' => '1', '2' => '2' ],
				'default' => '2',
			),

			array(
				'id'      => 'woo_cols_md',
				'type'    => 'button_set',
				'title'   => esc_html__( 'MD (≥ 768px)', 'codeweber' ),
				'options' => [ '1' => '1', '2' => '2', '3' => '3' ],
				'default' => '2',
			),

			array(
				'id'      => 'woo_cols_lg',
				'type'    => 'button_set',
				'title'   => esc_html__( 'LG (≥ 992px)', 'codeweber' ),
				'options' => [ '1' => '1', '2' => '2', '3' => '3', '4' => '4' ],
				'default' => '3',
			),

			array(
				'id'      => 'woo_cols_xl',
				'type'    => 'button_set',
				'title'   => esc_html__( 'XL (≥ 1200px)', 'codeweber' ),
				'options' => [ '1' => '1', '2' => '2', '3' => '3', '4' => '4' ],
				'default' => '4',
			),

			// ── Columns Switcher ──────────────────────────────────────────────
			array(
				'id'       => 'woo_show_per_row',
				'type'     => 'switch',
				'title'    => esc_html__( 'Columns Switcher', 'codeweber' ),
				'subtitle' => esc_html__( 'Show buttons to switch number of columns', 'codeweber' ),
				'default'  => true,
			),

			array(
				'id'       => 'woo_per_row_values',
				'type'     => 'checkbox',
				'title'    => esc_html__( 'Column Options', 'codeweber' ),
				'subtitle' => esc_html__( 'Which column options to display', 'codeweber' ),
				'options'  => array(
					'2' => '2',
					'3' => '3',
					'4' => '4',
				),
				'default'  => array( '2' => '1', '3' => '1', '4' => '1' ),
				'required' => [ 'woo_show_per_row', '=', true ],
			),

			// ── Sorting Dropdown ──────────────────────────────────────────────
			array(
				'id'       => 'woo_show_ordering',
				'type'     => 'switch',
				'title'    => esc_html__( 'Sorting Dropdown', 'codeweber' ),
				'subtitle' => esc_html__( 'Show the product ordering/sorting select', 'codeweber' ),
				'default'  => true,
			),

			array(
				'id'       => 'woo_ordering_options',
				'type'     => 'checkbox',
				'title'    => esc_html__( 'Sorting Options', 'codeweber' ),
				'subtitle' => esc_html__( 'Which sort options to show in the dropdown', 'codeweber' ),
				'options'  => array(
					'menu_order' => esc_html__( 'Default sorting', 'codeweber' ),
					'popularity' => esc_html__( 'Popularity', 'codeweber' ),
					'rating'     => esc_html__( 'Average rating', 'codeweber' ),
					'date'       => esc_html__( 'Latest', 'codeweber' ),
					'price'      => esc_html__( 'Price: low to high', 'codeweber' ),
					'price-desc' => esc_html__( 'Price: high to low', 'codeweber' ),
				),
				'default'  => array(
					'menu_order' => '1',
					'popularity' => '1',
					'rating'     => '1',
					'date'       => '1',
					'price'      => '1',
					'price-desc' => '1',
				),
				'required' => [ 'woo_show_ordering', '=', true ],
			),

		),
	)
);

// ── Badges ────────────────────────────────────────────────────────────────────
Redux::set_section(
	$opt_name,
	array(
		'title'      => esc_html__( 'Badges', 'codeweber' ),
		'id'         => 'woocommerce-badges',
		'subsection' => true,
		'fields'     => array(

			// ── Форма и позиция ───────────────────────────────────────────────
			array(
				'id'       => 'woo_badge_shape_use_theme',
				'type'     => 'switch',
				'title'    => esc_html__( 'Badge Shape', 'codeweber' ),
				'subtitle' => esc_html__( 'Follow the global Button Style from Theme Style settings.', 'codeweber' ),
				'on'       => esc_html__( 'Theme', 'codeweber' ),
				'off'      => esc_html__( 'Custom', 'codeweber' ),
				'default'  => true,
			),

			array(
				'id'       => 'woo_badge_shape',
				'type'     => 'image_select',
				'title'    => esc_html__( 'Custom Shape', 'codeweber' ),
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
				'required' => array( 'woo_badge_shape_use_theme', '=', false ),
			),

			array(
				'id'      => 'woo_badge_position',
				'type'    => 'button_set',
				'title'   => esc_html__( 'Badge Position', 'codeweber' ),
				'options' => array(
					'top-left'  => esc_html__( 'Top Left', 'codeweber' ),
					'top-right' => esc_html__( 'Top Right', 'codeweber' ),
				),
				'default' => 'top-left',
			),

			// ── Sale ──────────────────────────────────────────────────────────
			array(
				'id'    => 'woo_badge_sale_info',
				'type'  => 'info',
				'style' => 'info',
				'title' => esc_html__( 'Sale Badge', 'codeweber' ),
				'desc'  => esc_html__( 'Shown when product is on sale (WooCommerce).', 'codeweber' ),
			),

			array(
				'id'      => 'woo_badge_sale_enable',
				'type'    => 'switch',
				'title'   => esc_html__( 'Show Sale Badge', 'codeweber' ),
				'default' => true,
			),

			array(
				'id'       => 'woo_badge_sale_type',
				'type'     => 'button_set',
				'title'    => esc_html__( 'Sale Label Content', 'codeweber' ),
				'options'  => array(
					'text'    => esc_html__( 'Text', 'codeweber' ),
					'percent' => esc_html__( '−% Discount', 'codeweber' ),
				),
				'default'  => 'text',
				'required' => array( 'woo_badge_sale_enable', '=', true ),
			),

			array(
				'id'       => 'woo_badge_sale_text',
				'type'     => 'text',
				'title'    => esc_html__( 'Sale Text', 'codeweber' ),
				'default'  => esc_html__( 'Распродажа!', 'codeweber' ),
				'required' => array( 'woo_badge_sale_type', '=', 'text' ),
			),

			array(
				'id'       => 'woo_badge_sale_bg',
				'type'     => 'color',
				'title'    => esc_html__( 'Sale Background', 'codeweber' ),
				'default'  => '#d16b86',
				'class'    => 'xts-col-6',
				'required' => array( 'woo_badge_sale_enable', '=', true ),
			),

			array(
				'id'       => 'woo_badge_sale_color',
				'type'     => 'color',
				'title'    => esc_html__( 'Sale Text Color', 'codeweber' ),
				'default'  => '#ffffff',
				'class'    => 'xts-col-6',
				'required' => array( 'woo_badge_sale_enable', '=', true ),
			),

			// ── New ───────────────────────────────────────────────────────────
			array(
				'id'    => 'woo_badge_new_info',
				'type'  => 'info',
				'style' => 'info',
				'title' => esc_html__( 'New Badge', 'codeweber' ),
				'desc'  => esc_html__( 'Shown for featured products (WooCommerce "Featured" flag).', 'codeweber' ),
			),

			array(
				'id'      => 'woo_badge_new_enable',
				'type'    => 'switch',
				'title'   => esc_html__( 'Show New Badge', 'codeweber' ),
				'default' => true,
			),

			array(
				'id'       => 'woo_badge_new_text',
				'type'     => 'text',
				'title'    => esc_html__( 'New Text', 'codeweber' ),
				'default'  => esc_html__( 'Новинка!', 'codeweber' ),
				'required' => array( 'woo_badge_new_enable', '=', true ),
			),

			array(
				'id'       => 'woo_badge_new_bg',
				'type'     => 'color',
				'title'    => esc_html__( 'New Background', 'codeweber' ),
				'default'  => '#54a8c7',
				'class'    => 'xts-col-6',
				'required' => array( 'woo_badge_new_enable', '=', true ),
			),

			array(
				'id'       => 'woo_badge_new_color',
				'type'     => 'color',
				'title'    => esc_html__( 'New Text Color', 'codeweber' ),
				'default'  => '#ffffff',
				'class'    => 'xts-col-6',
				'required' => array( 'woo_badge_new_enable', '=', true ),
			),

		),
	)
);

// ── Single ────────────────────────────────────────────────────────────────────
Redux::set_section(
	$opt_name,
	array(
		'title'      => esc_html__( 'Single', 'codeweber' ),
		'id'         => 'woocommerce-single',
		'subsection' => true,
		'fields'     => array(

			// ── Галерея товара ───────────────────────────────────────────────
			array(
				'id'    => 'woo_gallery_section',
				'type'  => 'info',
				'style' => 'default',
				'title' => esc_html__( 'Product Gallery', 'codeweber' ),
			),

			array(
				'id'       => 'woo_gallery_thumbs_direction',
				'type'     => 'button_set',
				'title'    => esc_html__( 'Thumbnails Direction', 'codeweber' ),
				'subtitle' => esc_html__( 'Horizontal — thumbs below; Vertical — thumbs on the side', 'codeweber' ),
				'options'  => array(
					'horizontal' => esc_html__( 'Horizontal', 'codeweber' ),
					'vertical'   => esc_html__( 'Vertical', 'codeweber' ),
				),
				'default'  => 'horizontal',
			),

			array(
				'id'       => 'woo_gallery_thumbs_items',
				'type'     => 'slider',
				'title'    => esc_html__( 'Thumbnails Count', 'codeweber' ),
				'subtitle' => esc_html__( 'Number of visible thumbnails (3–8)', 'codeweber' ),
				'min'      => 3,
				'max'      => 8,
				'step'     => 1,
				'default'  => 5,
			),

			array(
				'id'      => 'woo_gallery_thumb_hover',
				'type'    => 'button_set',
				'title'   => esc_html__( 'Thumbnail Hover Effect', 'codeweber' ),
				'subtitle' => esc_html__( 'Hover effect on thumbnail images', 'codeweber' ),
				'options' => array(
					'none'               => esc_html__( 'None', 'codeweber' ),
					'hover-scale'        => esc_html__( 'Scale', 'codeweber' ),
					'hover-scale-rotate' => esc_html__( 'Scale+Rotate', 'codeweber' ),
					'lift'               => esc_html__( 'Lift', 'codeweber' ),
				),
				'default' => 'none',
			),

			array(
				'id'       => 'woo_gallery_thumbs_mousewheel',
				'type'     => 'switch',
				'title'    => esc_html__( 'Thumbnails Mousewheel', 'codeweber' ),
				'subtitle' => esc_html__( 'Scroll thumbnails with mouse wheel', 'codeweber' ),
				'default'  => false,
			),

			// ── Hover-эффект главного изображения ───────────────────────────
			array(
				'id'    => 'woo_gallery_hover_section',
				'type'  => 'info',
				'style' => 'default',
				'title' => esc_html__( 'Image Hover Effect', 'codeweber' ),
			),

			array(
				'id'      => 'woo_gallery_hover_type',
				'type'    => 'button_set',
				'title'   => esc_html__( 'Image Motion Effect', 'codeweber' ),
				'subtitle' => esc_html__( 'Movement effect on the main gallery image on hover', 'codeweber' ),
				'options' => array(
					''                   => esc_html__( 'None', 'codeweber' ),
					'hover-scale'        => esc_html__( 'Scale', 'codeweber' ),
					'hover-scale-rotate' => esc_html__( 'Scale+Rotate', 'codeweber' ),
					'lift'               => esc_html__( 'Lift', 'codeweber' ),
				),
				'default' => '',
			),

			array(
				'id'       => 'woo_gallery_hover_style',
				'type'     => 'select',
				'title'    => esc_html__( 'Hover Style', 'codeweber' ),
				'subtitle' => esc_html__( 'Overlay/icon style on the main gallery image', 'codeweber' ),
				'options'  => array(
					'none'    => esc_html__( 'None', 'codeweber' ),
					'style-1' => esc_html__( 'Overlay + uil-plus icon', 'codeweber' ),
					'style-2' => esc_html__( 'Overlay + SVG plus icon', 'codeweber' ),
					'style-3' => esc_html__( 'Overlay pale + SVG plus icon', 'codeweber' ),
					'style-4' => esc_html__( 'Item-link (corner icon)', 'codeweber' ),
				),
				'default'  => 'style-4',
			),

			// ── Ширина колонок ───────────────────────────────────────────────
			array(
				'id'    => 'woo_single_layout_section',
				'type'  => 'info',
				'style' => 'default',
				'title' => esc_html__( 'Layout', 'codeweber' ),
			),

			array(
				'id'       => 'woo_single_cols',
				'type'     => 'button_set',
				'title'    => esc_html__( 'Column Width', 'codeweber' ),
				'subtitle' => esc_html__( 'Gallery / Summary column ratio (Bootstrap 12-grid)', 'codeweber' ),
				'options'  => array(
					'5/7' => '5 / 7',
					'6/6' => '6 / 6',
					'7/5' => '7 / 5',
				),
				'default'  => '6/6',
			),

			// ── Видимость элементов страницы ────────────────────────────────
			array(
				'id'    => 'woo_single_visibility_section',
				'type'  => 'info',
				'style' => 'default',
				'title' => esc_html__( 'Page Elements Visibility', 'codeweber' ),
			),

			array(
				'id'      => 'woo_single_show_rating',
				'type'    => 'switch',
				'title'   => esc_html__( 'Rating', 'codeweber' ),
				'subtitle' => esc_html__( 'Show star rating below product title', 'codeweber' ),
				'default' => true,
			),

			array(
				'id'      => 'woo_single_show_excerpt',
				'type'    => 'switch',
				'title'   => esc_html__( 'Short Description', 'codeweber' ),
				'subtitle' => esc_html__( 'Show product short description', 'codeweber' ),
				'default' => true,
			),

			array(
				'id'      => 'woo_single_show_meta',
				'type'    => 'switch',
				'title'   => esc_html__( 'Product Meta', 'codeweber' ),
				'subtitle' => esc_html__( 'Show SKU, categories, tags', 'codeweber' ),
				'default' => true,
			),

			array(
				'id'      => 'woo_single_show_tabs',
				'type'    => 'switch',
				'title'   => esc_html__( 'Description Tabs', 'codeweber' ),
				'subtitle' => esc_html__( 'Show description / attributes tabs', 'codeweber' ),
				'default' => true,
			),

			array(
				'id'      => 'woo_single_show_related',
				'type'    => 'switch',
				'title'   => esc_html__( 'Related Products', 'codeweber' ),
				'subtitle' => esc_html__( 'Show related products slider', 'codeweber' ),
				'default' => true,
			),

			array(
				'id'      => 'woo_single_show_reviews',
				'type'    => 'switch',
				'title'   => esc_html__( 'Reviews', 'codeweber' ),
				'subtitle' => esc_html__( 'Show ratings distribution and customer reviews', 'codeweber' ),
				'default' => true,
			),

		),
	)
);

// ── Cart ──────────────────────────────────────────────────────────────────────
Redux::set_section(
	$opt_name,
	array(
		'title'      => esc_html__( 'Cart', 'codeweber' ),
		'id'         => 'woocommerce-cart',
		'subsection' => true,
		'fields'     => array(),
	)
);

// ── Checkout helpers ──────────────────────────────────────────────────────────
// Генерирует 3 Redux-поля (enable / required / width) для одного поля чекаута.

if ( ! function_exists( 'cw_redux_checkout_field_rows' ) ) {
	function cw_redux_checkout_field_rows( $prefix, $key, $label, $default_enabled, $default_required, $default_width, $show_required = true ) {
		$id   = "woo_co_{$prefix}_{$key}";
		$rows = array(
			array(
				'id'    => "{$id}_sep",
				'type'  => 'info',
				'style' => 'default',
				'title' => esc_html( $label ),
			),
			array(
				'id'      => "{$id}_enable",
				'type'    => 'switch',
				'title'   => esc_html__( 'Enable', 'codeweber' ),
				'default' => $default_enabled,
				'class'   => 'xts-col-4',
			),
		);
		if ( $show_required ) {
			$rows[] = array(
				'id'       => "{$id}_required",
				'type'     => 'switch',
				'title'    => esc_html__( 'Required', 'codeweber' ),
				'default'  => $default_required,
				'class'    => 'xts-col-4',
				'required' => array( "{$id}_enable", '=', true ),
			);
		}
		$rows[] = array(
			'id'       => "{$id}_width",
			'type'     => 'button_set',
			'title'    => esc_html__( 'Width', 'codeweber' ),
			'default'  => $default_width,
			'class'    => 'xts-col-4',
			'options'  => array(
				'full' => esc_html__( 'Full', 'codeweber' ),
				'half' => esc_html__( 'Half', 'codeweber' ),
			),
			'required' => array( "{$id}_enable", '=', true ),
		);
		return $rows;
	}
}

// ── Checkout: Billing ──────────────────────────────────────────────────────────

$_co_billing_fields = array();
$_co_billing_defs   = array(
	// key          label                        enabled  required  width
	array( 'first_name', __( 'First Name',    'codeweber' ), true,  true,  'half' ),
	array( 'last_name',  __( 'Last Name',     'codeweber' ), true,  true,  'half' ),
	array( 'company',    __( 'Company',       'codeweber' ), true,  false, 'half' ),
	array( 'country',    __( 'Country',       'codeweber' ), true,  true,  'half' ),
	array( 'address_1',  __( 'Address',       'codeweber' ), true,  true,  'half' ),
	array( 'address_2',  __( 'Address 2',     'codeweber' ), true,  false, 'half' ),
	array( 'city',       __( 'City',          'codeweber' ), true,  true,  'half' ),
	array( 'state',      __( 'State / Region','codeweber' ), true,  false, 'half' ),
	array( 'postcode',   __( 'Postcode',      'codeweber' ), true,  true,  'half' ),
	array( 'email',      __( 'Email',         'codeweber' ), true,  true,  'half' ),
	array( 'phone',      __( 'Phone',         'codeweber' ), true,  true,  'half' ),
);
foreach ( $_co_billing_defs as $_d ) {
	foreach ( cw_redux_checkout_field_rows( 'billing', $_d[0], $_d[1], $_d[2], $_d[3], $_d[4] ) as $_f ) {
		$_co_billing_fields[] = $_f;
	}
}

Redux::set_section(
	$opt_name,
	array(
		'title'      => esc_html__( 'Checkout: Billing', 'codeweber' ),
		'id'         => 'woocommerce-checkout-billing',
		'subsection' => true,
		'fields'     => $_co_billing_fields,
	)
);

// ── Checkout: Shipping ────────────────────────────────────────────────────────

$_co_shipping_fields = array();
$_co_shipping_defs   = array(
	array( 'first_name', __( 'First Name',    'codeweber' ), true,  false, 'half' ),
	array( 'last_name',  __( 'Last Name',     'codeweber' ), true,  false, 'half' ),
	array( 'company',    __( 'Company',       'codeweber' ), true,  false, 'half' ),
	array( 'country',    __( 'Country',       'codeweber' ), true,  false, 'half' ),
	array( 'address_1',  __( 'Address',       'codeweber' ), true,  false, 'half' ),
	array( 'address_2',  __( 'Address 2',     'codeweber' ), true,  false, 'half' ),
	array( 'city',       __( 'City',          'codeweber' ), true,  false, 'half' ),
	array( 'state',      __( 'State / Region','codeweber' ), true,  false, 'half' ),
	array( 'postcode',   __( 'Postcode',      'codeweber' ), true,  false, 'half' ),
);
foreach ( $_co_shipping_defs as $_d ) {
	foreach ( cw_redux_checkout_field_rows( 'shipping', $_d[0], $_d[1], $_d[2], $_d[3], $_d[4] ) as $_f ) {
		$_co_shipping_fields[] = $_f;
	}
}

Redux::set_section(
	$opt_name,
	array(
		'title'      => esc_html__( 'Checkout: Shipping', 'codeweber' ),
		'id'         => 'woocommerce-checkout-shipping',
		'subsection' => true,
		'fields'     => $_co_shipping_fields,
	)
);

// ── Checkout: Additional ──────────────────────────────────────────────────────

Redux::set_section(
	$opt_name,
	array(
		'title'      => esc_html__( 'Checkout: Additional', 'codeweber' ),
		'id'         => 'woocommerce-checkout-additional',
		'subsection' => true,
		'fields'     => array_merge(
			cw_redux_checkout_field_rows( 'order', 'comments', __( 'Order Notes', 'codeweber' ), true, false, 'full', false )
		),
	)
);

// ── Account ───────────────────────────────────────────────────────────────────
Redux::set_section(
	$opt_name,
	array(
		'title'      => esc_html__( 'Account', 'codeweber' ),
		'id'         => 'woocommerce-account',
		'subsection' => true,
		'fields'     => array(

			array(
				'id'       => 'woophonenumber',
				'type'     => 'switch',
				'title'    => esc_html__( 'Phone', 'codeweber' ),
				'subtitle' => esc_html__( 'Enable phone field in registration/account', 'codeweber' ),
				'default'  => false,
			),

			array(
				'id'       => 'woophonenumbersms',
				'type'     => 'switch',
				'title'    => esc_html__( 'Confirmation of phone number by SMS', 'codeweber' ),
				'subtitle' => esc_html__( 'SMS.RU API', 'codeweber' ),
				'desc'     => esc_html__( 'For this function to work, you must have a working API key from SMS.RU, it must be entered and saved in the API tab', 'codeweber' ),
				'default'  => false,
			),

			array(
				'id'       => 'hidedownloadmenu',
				'type'     => 'switch',
				'title'    => esc_html__( 'Hide Downloads Menu', 'codeweber' ),
				'subtitle' => esc_html__( 'Hide the "Downloads" tab in My Account sidebar', 'codeweber' ),
				'default'  => false,
			),

			array(
				'id'       => 'payment_methods_test_mode',
				'type'     => 'switch',
				'title'    => esc_html__( 'Payment Methods Test Mode', 'codeweber' ),
				'subtitle' => esc_html__( 'Show "Payment methods" in My Account and enable test gateway (no registration).', 'codeweber' ),
				'default'  => false,
			),

			array(
				'id'           => 'image_login_page',
				'type'         => 'media',
				'url'          => true,
				'title'        => esc_html__( 'Image for Login Page', 'codeweber' ),
				'compiler'     => 'true',
				'preview_size' => 'full',
			),

		),
	)
);

// ── Quick View ────────────────────────────────────────────────────────────────
Redux::set_section(
	$opt_name,
	array(
		'title'      => esc_html__( 'Quick View', 'codeweber' ),
		'id'         => 'woocommerce-quick-view',
		'subsection' => true,
		'fields'     => array(

			array(
				'id'       => 'quick_view_enable',
				'type'     => 'switch',
				'title'    => esc_html__( 'Enable Quick View', 'codeweber' ),
				'subtitle' => esc_html__( 'Show Quick View button on product cards (Bootstrap Modal with gallery, variations support)', 'codeweber' ),
				'default'  => true,
			),

		),
	)
);

// ── Wishlist ──────────────────────────────────────────────────────────────────
Redux::set_section(
	$opt_name,
	array(
		'title'      => esc_html__( 'Wishlist', 'codeweber' ),
		'id'         => 'woocommerce-wishlist',
		'subsection' => true,
		'fields'     => array(

			array(
				'id'       => 'wishlist_enable',
				'type'     => 'switch',
				'title'    => esc_html__( 'Enable Wishlist', 'codeweber' ),
				'subtitle' => esc_html__( 'Activate wishlist functionality (requires WooCommerce)', 'codeweber' ),
				'default'  => false,
			),

			array(
				'id'       => 'wishlist_page',
				'type'     => 'select',
				'title'    => esc_html__( 'Wishlist Page', 'codeweber' ),
				'subtitle' => esc_html__( 'Page with [cw_wishlist] shortcode. Use the "Create Wishlist Page" button to generate it automatically.', 'codeweber' ),
				'data'     => 'pages',
				'default'  => '',
				'required' => array( 'wishlist_enable', '=', true ),
			),

			array(
				'id'       => 'wishlist_guests',
				'type'     => 'switch',
				'title'    => esc_html__( 'Allow Guests', 'codeweber' ),
				'subtitle' => esc_html__( 'Guests can add products to wishlist (stored in cookie). Products move to DB after login.', 'codeweber' ),
				'default'  => true,
				'required' => array( 'wishlist_enable', '=', true ),
			),

			array(
				'id'       => 'wishlist_btn_on_loop',
				'type'     => 'switch',
				'title'    => esc_html__( 'Button on Product Cards', 'codeweber' ),
				'subtitle' => esc_html__( 'Show «Add to Wishlist» button on product cards in catalog', 'codeweber' ),
				'default'  => true,
				'required' => array( 'wishlist_enable', '=', true ),
			),

			array(
				'id'       => 'wishlist_btn_on_single',
				'type'     => 'switch',
				'title'    => esc_html__( 'Button on Single Product', 'codeweber' ),
				'subtitle' => esc_html__( 'Show «Add to Wishlist» button on single product page', 'codeweber' ),
				'default'  => true,
				'required' => array( 'wishlist_enable', '=', true ),
			),

			array(
				'id'       => 'wishlist_feedback',
				'type'     => 'select',
				'title'    => esc_html__( 'Add Feedback', 'codeweber' ),
				'subtitle' => esc_html__( 'Visual feedback when adding a product to wishlist', 'codeweber' ),
				'default'  => 'spinner',
				'options'  => array(
					'spinner' => esc_html__( 'Spinner on button', 'codeweber' ),
					'card'    => esc_html__( 'Spinner on card', 'codeweber' ),
					'modal'   => esc_html__( 'Modal confirmation', 'codeweber' ),
					'none'    => esc_html__( 'None', 'codeweber' ),
				),
				'required' => array( 'wishlist_enable', '=', true ),
			),

			array(
				'id'       => 'wishlist_toast',
				'type'     => 'checkbox',
				'title'    => esc_html__( 'Toast Notification', 'codeweber' ),
				'subtitle' => esc_html__( 'Show toast when adding or removing from wishlist', 'codeweber' ),
				'default'  => '0',
				'required' => array( 'wishlist_enable', '=', true ),
			),

		),
	)
);

// ── Compare ───────────────────────────────────────────────────────────────────
Redux::set_section(
	$opt_name,
	array(
		'title'      => esc_html__( 'Compare', 'codeweber' ),
		'id'         => 'woocommerce-compare',
		'subsection' => true,
		'fields'     => array(

			array(
				'id'       => 'compare_enable',
				'type'     => 'switch',
				'title'    => esc_html__( 'Enable Compare', 'codeweber' ),
				'subtitle' => esc_html__( 'Activate product comparison (cookie-based, no auth required)', 'codeweber' ),
				'default'  => false,
			),

			array(
				'id'       => 'compare_page',
				'type'     => 'select',
				'title'    => esc_html__( 'Compare Page', 'codeweber' ),
				'subtitle' => esc_html__( 'Page with [cw_compare] shortcode. Use "Create Compare Page" button to generate it automatically.', 'codeweber' ),
				'data'     => 'pages',
				'default'  => '',
				'required' => array( 'compare_enable', '=', true ),
			),

			array(
				'id'       => 'compare_limit',
				'type'     => 'slider',
				'title'    => esc_html__( 'Products Limit', 'codeweber' ),
				'subtitle' => esc_html__( 'Maximum number of products in comparison (2–6)', 'codeweber' ),
				'default'  => 4,
				'min'      => 2,
				'max'      => 6,
				'step'     => 1,
				'required' => array( 'compare_enable', '=', true ),
			),

			array(
				'id'       => 'compare_btn_loop',
				'type'     => 'switch',
				'title'    => esc_html__( 'Button on Product Cards', 'codeweber' ),
				'subtitle' => esc_html__( 'Show «Compare» button on product cards in catalog (alongside Wishlist and Quick View)', 'codeweber' ),
				'default'  => true,
				'required' => array( 'compare_enable', '=', true ),
			),

			array(
				'id'       => 'compare_btn_single',
				'type'     => 'switch',
				'title'    => esc_html__( 'Button on Single Product', 'codeweber' ),
				'subtitle' => esc_html__( 'Show «Compare» button on single product page (after Add to Cart). On variable products the selected variation ID is used.', 'codeweber' ),
				'default'  => true,
				'required' => array( 'compare_enable', '=', true ),
			),

			array(
				'id'    => 'compare_table_info',
				'type'  => 'info',
				'style' => 'info',
				'title' => esc_html__( 'Table Rows', 'codeweber' ),
				'desc'  => esc_html__( 'Choose which rows to display in the comparison table', 'codeweber' ),
				'required' => array( 'compare_enable', '=', true ),
			),

			array(
				'id'       => 'compare_show_rating',
				'type'     => 'switch',
				'title'    => esc_html__( 'Show Rating Row', 'codeweber' ),
				'default'  => true,
				'required' => array( 'compare_enable', '=', true ),
			),

			array(
				'id'       => 'compare_show_stock',
				'type'     => 'switch',
				'title'    => esc_html__( 'Show Stock Row', 'codeweber' ),
				'default'  => true,
				'required' => array( 'compare_enable', '=', true ),
			),

			array(
				'id'       => 'compare_show_sku',
				'type'     => 'switch',
				'title'    => esc_html__( 'Show SKU Row', 'codeweber' ),
				'default'  => true,
				'required' => array( 'compare_enable', '=', true ),
			),

		),
	)
);

// ── Demo Products (только если WooCommerce активен) ───────────────────────────
if ( class_exists( 'WooCommerce' ) ) {
	Redux::set_section(
		$opt_name,
		array(
			'title'      => esc_html__( 'Demo Products', 'codeweber' ),
			'id'         => 'woocommerce-demo',
			'desc'       => '',
			'subsection' => true,
			'fields'     => array(
				array(
					'id'      => 'woo-demo-products-controls',
					'type'    => 'raw',
					'content' => '
						<div class="demo-controls" style="margin: 20px 0;">
							<h3>' . esc_html__( 'Demo WooCommerce Products', 'codeweber' ) . '</h3>
							<p class="description">' . esc_html__( 'Create 9 demo products with categories, tags and images from the theme photos folder.', 'codeweber' ) . '</p>
							<div style="margin: 15px 0;">
								<button id="cw-demo-create-products" class="button button-primary" style="margin-right: 10px;">' . esc_html__( 'Create Demo Products', 'codeweber' ) . '</button>
								<button id="cw-demo-delete-products" class="button button-secondary">' . esc_html__( 'Delete Demo Products', 'codeweber' ) . '</button>
							</div>
							<div id="cw-demo-products-status" style="margin-top:10px;padding:10px;background:#f0f0f0;border-radius:4px;display:none;"></div>
						</div>
						<script>
						(function($) {
							"use strict";
							var createNonce = "' . wp_create_nonce( 'cw_demo_create_products' ) . '";
							var deleteNonce = "' . wp_create_nonce( 'cw_demo_delete_products' ) . '";
							function showStatus(msg, type) {
								var $s = $("#cw-demo-products-status");
								$s.removeClass("notice-success notice-error notice-info").addClass("notice-" + (type || "info")).html("<p>" + msg + "</p>").show();
							}
							function setDisabled(state) { $("#cw-demo-create-products, #cw-demo-delete-products").prop("disabled", state); }
							$("#cw-demo-create-products").on("click", function(e) {
								e.preventDefault();
								if (!confirm("' . esc_js( __( 'Create 9 demo products with categories, tags and images?', 'codeweber' ) ) . '")) return;
								setDisabled(true); showStatus("' . esc_js( __( 'Creating products...', 'codeweber' ) ) . '", "info");
								$.post(ajaxurl, { action: "cw_demo_create_products", nonce: createNonce }, function(r) {
									setDisabled(false);
									if (r.success) { var msg = r.data.message; if (r.data.errors && r.data.errors.length) { msg += "<br><ul>" + r.data.errors.map(function(e){return "<li>"+e+"</li>";}).join("") + "</ul>"; } showStatus(msg, "success"); }
									else { showStatus(r.data.message || "' . esc_js( __( 'An error occurred', 'codeweber' ) ) . '", "error"); }
								}).fail(function() { setDisabled(false); showStatus("' . esc_js( __( 'AJAX request error', 'codeweber' ) ) . '", "error"); });
							});
							$("#cw-demo-delete-products").on("click", function(e) {
								e.preventDefault();
								if (!confirm("' . esc_js( __( 'Delete all demo products? This action cannot be undone.', 'codeweber' ) ) . '")) return;
								setDisabled(true); showStatus("' . esc_js( __( 'Deleting products...', 'codeweber' ) ) . '", "info");
								$.post(ajaxurl, { action: "cw_demo_delete_products", nonce: deleteNonce }, function(r) {
									setDisabled(false);
									if (r.success) { var msg = r.data.message; if (r.data.errors && r.data.errors.length) { msg += "<br><ul>" + r.data.errors.map(function(e){return "<li>"+e+"</li>";}).join("") + "</ul>"; } showStatus(msg, "success"); }
									else { showStatus(r.data.message || "' . esc_js( __( 'An error occurred', 'codeweber' ) ) . '", "error"); }
								}).fail(function() { setDisabled(false); showStatus("' . esc_js( __( 'AJAX request error', 'codeweber' ) ) . '", "error"); });
							});
						})(jQuery);
						</script>
					',
				),
			),
		)
	);
}

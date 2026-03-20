<?php

/**
 * Возвращает список доступных шаблонов карточек товаров.
 * Автоматически сканирует templates/post-cards/product/.
 */
if ( ! function_exists( 'codeweber_get_product_card_options' ) ) {
	function codeweber_get_product_card_options() {
		$options = [];
		$dir     = get_template_directory() . '/templates/post-cards/product/';

		if ( is_dir( $dir ) ) {
			foreach ( scandir( $dir ) as $file ) {
				if ( pathinfo( $file, PATHINFO_EXTENSION ) === 'php' ) {
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
				'id'      => 'woo_badge_sale_type',
				'type'    => 'button_set',
				'title'   => esc_html__( 'Sale Label Content', 'codeweber' ),
				'options' => array(
					'text'    => esc_html__( 'Text', 'codeweber' ),
					'percent' => esc_html__( '−% Discount', 'codeweber' ),
				),
				'default' => 'text',
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
			),

			array(
				'id'      => 'woo_badge_sale_color',
				'type'    => 'color',
				'title'   => esc_html__( 'Sale Text Color', 'codeweber' ),
				'default' => '#ffffff',
				'class'   => 'xts-col-6',
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
				'id'      => 'woo_badge_new_text',
				'type'    => 'text',
				'title'   => esc_html__( 'New Text', 'codeweber' ),
				'default' => esc_html__( 'Новинка!', 'codeweber' ),
			),

			array(
				'id'      => 'woo_badge_new_bg',
				'type'    => 'color',
				'title'   => esc_html__( 'New Background', 'codeweber' ),
				'default' => '#54a8c7',
				'class'   => 'xts-col-6',
			),

			array(
				'id'      => 'woo_badge_new_color',
				'type'    => 'color',
				'title'   => esc_html__( 'New Text Color', 'codeweber' ),
				'default' => '#ffffff',
				'class'   => 'xts-col-6',
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
		'fields'     => array(),
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
				'subtitle' => esc_html__( 'Page with [cw_wishlist] shortcode. Create the page first, then select it here.', 'codeweber' ),
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
					'spinner'    => esc_html__( 'Spinner on button', 'codeweber' ),
					'card'       => esc_html__( 'Spinner on card', 'codeweber' ),
					'toast'      => esc_html__( 'Toast notification', 'codeweber' ),
					'both'       => esc_html__( 'Spinner + Toast', 'codeweber' ),
					'card-toast' => esc_html__( 'Spinner on card + Toast', 'codeweber' ),
					'none'       => esc_html__( 'None', 'codeweber' ),
				),
				'required' => array( 'wishlist_enable', '=', true ),
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

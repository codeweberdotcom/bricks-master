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

Redux::set_section(
	$opt_name,
	array(
		'title'            => esc_html__("Woocommerce", "codeweber"),
		'id'               => 'woocommerce-settings',
		'desc'             => esc_html__("Woocommerce Settings", "codeweber"),
		'customizer_width' => '300px',
		'icon'             => 'el el-home',
		'fields'     => array(

			array(
				'id'       => 'shop-archive-settings',
				'type'     => 'accordion',
				'title'    => esc_html__('Shop Archive Settings', 'codeweber'),
				'position' => 'start',
			),

			array(
				'id'       => 'archive_template_select_product',
				'type'     => 'select',
				'title'    => esc_html__('Product Card Template', 'codeweber'),
				'subtitle' => esc_html__('Select card style for WooCommerce shop archive', 'codeweber'),
				'options'  => codeweber_get_product_card_options(),
				'default'  => 'shop2',
			),

			array(
				'id'       => 'woo_shop_load_more',
				'type'     => 'switch',
				'title'    => esc_html__('Load More Button', 'codeweber'),
				'subtitle' => esc_html__('Show "Load More" button instead of pagination on shop pages', 'codeweber'),
				'default'  => false,
			),

			array(
				'id'       => 'shop-archive-settings',
				'type'     => 'accordion',
				'position' => 'end',
			),

			array(
				'id'       => 'my-account-settings',
				'type'     => 'accordion',
				'title'    => esc_html__('My Account Settings', 'codeweber'),
				'position' => 'start',
			),

			array(
				'id'       => 'woophonenumber',
				'type'     => 'switch',
				'title'    => esc_html__('Phone', 'codeweber'),
				'subtitle' => esc_html__('Enable phone display', 'codeweber'),
				'default'  => false,
			),

			array(
				'id'       => 'woophonenumbersms',
				'type'     => 'switch',
				'title'    => esc_html__('Confirmation of phone number by SMS', 'codeweber'),
				'subtitle' => esc_html__('SMS.RU API', 'codeweber'),
				'desc'             => esc_html__("For this function to work, you must have a working API key from SMS.RU, it must be entered and saved in the API tab", "codeweber"),
				'default'  => false,
			),

			array(
				'id'       => 'hidedownloadmenu',
				'type'     => 'switch',
				'title'    => esc_html__('Hide Download Menu', 'codeweber'),
				'default'  => false,
			),

			array(
				'id'       => 'payment_methods_test_mode',
				'type'     => 'switch',
				'title'    => esc_html__('Payment methods test mode', 'codeweber'),
				'subtitle' => esc_html__('Show "Payment methods" in My Account and enable test gateway (no registration).', 'codeweber'),
				'default'  => false,
			),

			array(
				'id'           => 'image_login_page',
				'type'         => 'media',
				'url'          => true,
				'title'        => esc_html__('Image for Login page', 'codeweber'),
				'compiler'     => 'true',
				'preview_size' => 'full',
			),

			array(
				'id'       => 'my-account-settings',
				'type'     => 'accordion',
				'position' => 'end',
			),

		),
	)
);

// Блок создания demo товаров — только если WooCommerce активен
if ( class_exists( 'WooCommerce' ) ) {
	Redux::set_section(
		$opt_name,
		array(
			'title'      => esc_html__( 'Demo Products', 'codeweber' ),
			'id'         => 'woocommerce-demo',
			'desc'       => '',
			'icon'       => 'el el-magic',
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

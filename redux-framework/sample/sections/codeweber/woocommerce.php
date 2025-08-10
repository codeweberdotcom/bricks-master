<?php

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
				'id'           => 'image_login_page',
				'type'         => 'media',
				'url'          => true,
				'title'        => esc_html__('Image for Login page', 'codeweber'),
				'compiler'     => 'true',
				'preview_size' => 'full',
			),


		),
	)
);


<?php

Redux::set_section(
	$opt_name,
	array(
		'title'            => esc_html__("Tracking & Metrics", "codeweber"),
		'id'               => 'tracking-metrics',
		'desc'             => esc_html__("Settings Tracking && Metrics", "codeweber"),
		'customizer_width' => '300px',
		'icon'             => 'el el-home',
		'fields'     => array(
			array(
				'id'       => 'yandex-on',
				'type'     => 'switch',
				'title'    => esc_html__('Yandex', 'codeweber'),
				'subtitle' => esc_html__('Look, it\'s on!', 'codeweber'),
				'default'  => false,
			),
			array(
				'id'       => 'yandex-metrics',
				'type'     => 'textarea',
				'title'    => esc_html__('Yandex Metric Settings', 'codeweber'),
				'subtitle' => esc_html__('Put Yandex Metric Code', 'codeweber'),
				//'desc'     => esc_html__('This is the description field, again good for additional info.', 'codeweber'),
				//'default'  => 'Default Text',
				'required' => array('yandex-on', '=', 'true'),
			),
			array(
				'id'       => 'google-analytics-on',
				'type'     => 'switch',
				'title'    => esc_html__('Google Analytics', 'codeweber'),
				'subtitle' => esc_html__('Look, it\'s on!', 'codeweber'),
				'default'  => false,
			),
			array(
				'id'       => 'google-analytics',
				'type'     => 'textarea',
				'title'    => esc_html__('Google Analytics Settings', 'codeweber'),
				'subtitle' => esc_html__('Put Google Analytics Code', 'codeweber'),
				//'desc'     => esc_html__('This is the description field, again good for additional info.', 'codeweber'),
				//'default'  => 'Default Text',
				'required' => array('google-analytics-on', '=', 'true'),
			),
			array(
				'id'       => 'google-tag-manager-on',
				'type'     => 'switch',
				'title'    => esc_html__('Google Tag Manager', 'codeweber'),
				'subtitle' => esc_html__('Look, it\'s on!', 'codeweber'),
				'default'  => false,
			),
			array(
				'id'       => 'google-tag-manager',
				'type'     => 'textarea',
				'title'    => esc_html__('Google Tag Manager Settings', 'codeweber'),
				'subtitle' => esc_html__('Put Google Tag Manager Container ID', 'codeweber'),
				//'desc'     => esc_html__('This is the description field, again good for additional info.', 'codeweber'),
				//'default'  => 'Default Text',
				'required' => array('google-tag-manager-on', '=', 'true'),
			),
			array(
				'id'       => 'facebook-pixel-on',
				'type'     => 'switch',
				'title'    => esc_html__('Facebook Pixel', 'codeweber'),
				'subtitle' => esc_html__('Look, it\'s on!', 'codeweber'),
				'default'  => false,
			),
			array(
				'id'       => 'facebook-pixel',
				'type'     => 'textarea',
				'title'    => esc_html__('Facebook Pixel Settings', 'codeweber'),
				'subtitle' => esc_html__('Put Facebook Pixel Code', 'codeweber'),
				//'desc'     => esc_html__('This is the description field, again good for additional info.', 'codeweber'),
				//'default'  => 'Default Text',
				'required' => array('facebook-pixel-on', '=', 'true'),
			),
			array(
				'id'       => 'hotjar-on',
				'type'     => 'switch',
				'title'    => esc_html__('Hotjar', 'codeweber'),
				'subtitle' => esc_html__('Look, it\'s on!', 'codeweber'),
				'default'  => false,
			),
			array(
				'id'       => 'hotjar',
				'type'     => 'textarea',
				'title'    => esc_html__('Hotjar Settings', 'codeweber'),
				'subtitle' => esc_html__('Put Hotjar Code', 'codeweber'),
				//'desc'     => esc_html__('This is the description field, again good for additional info.', 'codeweber'),
				//'default'  => 'Default Text',
				'required' => array('hotjar-on', '=', 'true'),
			),
			array(
				'id'       => 'other-analytics-on',
				'type'     => 'switch',
				'title'    => esc_html__('Other Analytics', 'codeweber'),
				'subtitle' => esc_html__('Look, it\'s on!', 'codeweber'),
				'default'  => false,
			),
			array(
				'id'       => 'other-analytics-code',
				'type'     => 'textarea',
				'title'    => esc_html__('Other Analytics Services', 'codeweber'),
				'subtitle' => esc_html__('Put Other Analytics Code', 'codeweber'),
				//'desc'     => esc_html__('This is the description field, again good for additional info.', 'codeweber'),
				//'default'  => 'Default Text',
				'required' => array('other-analytics-on', '=', 'true'),
			),





		),
	)
);




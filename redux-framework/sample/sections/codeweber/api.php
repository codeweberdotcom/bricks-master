<?php

Redux::set_section(
	$opt_name,
	array(
		'title'            => esc_html__("API", "codeweber"),
		'id'               => 'api',
		'desc'             => esc_html__("Settings API", "codeweber"),
		'customizer_width' => '300px',
		'icon'             => 'el el-home',
		'fields'     => array(

			array(
				'id'       => 'dadata',
				'type'     => 'password',
				'title'    => esc_html__('DaDaTa Settings', 'codeweber'),
				'subtitle' => esc_html__('Put DaDaTa API key', 'codeweber'),
				//'desc'     => esc_html__('This is the description field, again good for additional info.', 'codeweber'),
				//'default'  => 'Default Text',
			),
			array(
				'id'       => 'yandexapi',
				'type'     => 'password',
				'title'    => esc_html__('Yandex API Map Settings', 'codeweber'),
				'subtitle' => esc_html__('Put Yandex API Map key', 'codeweber'),
				'desc'     => '<a href="https://developer.tech.yandex.ru/services/3" target="_blank" >'. esc_html__('Link to Yandex Developer Console', 'codeweber'). '</a>',
				//'default'  => 'Default Text',
			),

		),
	)
);

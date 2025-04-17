<?php

Redux::set_section(
	$opt_name,
	array(
		'title'            => esc_html__("Contacts", "codeweber"),
		'id'               => 'contacts',
		'desc'             => esc_html__("Settings Contacts", "codeweber"),
		'customizer_width' => '300px',
		'icon'             => 'el el-home',
		'fields'           => array(
			array(
				'id'       => 'e-mail',
				'type'     => 'text',
				'title'    => esc_html__('E-Mail', 'codeweber'),
				'subtitle' => esc_html__('Subtitle', 'codeweber'),
			),
			array(
				'id'       => 'phone_01',
				'type'     => 'text',
				'title'    => esc_html__('Phone 01', 'codeweber'),
				'subtitle' => esc_html__('Subtitle', 'codeweber'),
				'desc'    => esc_html__('+7(495)XXX-XX-XX, 8(800)XXX-XX-XX, XXX', 'codeweber'),

			),
			array(
				'id'       => 'phone_02',
				'type'     => 'text',
				'title'    => esc_html__('Phone 02', 'codeweber'),
				'subtitle' => esc_html__('Subtitle', 'codeweber'),
				'desc'    => esc_html__('+7(495)XXX-XX-XX, 8(800)XXX-XX-XX, XXX', 'codeweber'),
			),
			array(
				'id'       => 'phone_03',
				'type'     => 'text',
				'title'    => esc_html__('Phone 03', 'codeweber'),
				'subtitle' => esc_html__('Subtitle', 'codeweber'),
				'desc'    => esc_html__('+7(495)XXX-XX-XX, 8(800)XXX-XX-XX, XXX', 'codeweber'),
			),
			array(
				'id'       => 'phone_04',
				'type'     => 'text',
				'title'    => esc_html__('Phone 04', 'codeweber'),
				'subtitle' => esc_html__('Subtitle', 'codeweber'),
				'desc'    => esc_html__('+7(495)XXX-XX-XX, 8(800)XXX-XX-XX, XXX', 'codeweber'),
			),
			array(
				'id'       => 'phone_05',
				'type'     => 'text',
				'title'    => esc_html__('Phone 05', 'codeweber'),
				'subtitle' => esc_html__('Subtitle', 'codeweber'),
				'desc'    => esc_html__('+7(495)XXX-XX-XX, 8(800)XXX-XX-XX, XXX', 'codeweber'),
			),

		),
	)
);

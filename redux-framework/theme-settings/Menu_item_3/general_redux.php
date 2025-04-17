<?php
return array(
	'title'  => 'General Settings',
	'id'     => 'general_settings',
	'desc'   => 'Настройки общего вида и типографики для сайта.',
	'icon'   => 'el el-cogs',
	'fields' => array(
		array(
			'id'       => 'site_logo',
			'type'     => 'media',
			'title'    => 'Logo',
			'subtitle' => 'Загрузите логотип вашего сайта.',
		),
		array(
			'id'       => 'logo_dark',
			'type'     => 'media',
			'title'    => 'Logo Dark',
			'subtitle' => 'Загрузите темную версию логотипа.',
		),
		array(
			'id'       => 'site_title',
			'type'     => 'text',
			'title'    => 'Site Title',
			'subtitle' => 'Введите название вашего сайта.',
			'default'  => 'Мой сайт WordPress',
		),
		array(
			'id'       => 'font_size',
			'type'     => 'slider',
			'title'    => 'Font Size (Heading)',
			'subtitle' => 'Настройте размер шрифта для заголовков.',
			'default'  => 24,
			'min'      => 12,
			'step'     => 1,
			'max'      => 72,
		),
		array(
			'id'       => 'font_family',
			'type'     => 'select',
			'title'    => 'Font Family',
			'subtitle' => 'Выберите шрифт из Google Fonts.',
			'options'  => array(
				'Roboto'       => 'Roboto',
				'Open Sans'    => 'Open Sans',
				'Lato'         => 'Lato',
				'Montserrat'   => 'Montserrat',
				'Poppins'      => 'Poppins',
				'Oswald'       => 'Oswald',
			),
			'default'  => 'Roboto',
		),
	),
);

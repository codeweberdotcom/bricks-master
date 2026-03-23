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
				'id'       => 'dadata_enabled',
				'type'     => 'switch',
				'title'    => esc_html__('DaData: включить стандартизацию адресов', 'codeweber'),
				'subtitle' => esc_html__('Проверка и автозаполнение адреса через DaData (только Россия)', 'codeweber'),
				'default'  => false,
			),
			array(
				'id'       => 'dadata',
				'type'     => 'password',
				'title'    => esc_html__('DaData API Token', 'codeweber'),
				'subtitle' => esc_html__('API ключ из кабинета DaData', 'codeweber'),
				'required' => array( 'dadata_enabled', '=', true ),
			),
			array(
				'id'       => 'dadata_secret',
				'type'     => 'password',
				'title'    => esc_html__('DaData Secret (X-Secret)', 'codeweber'),
				'subtitle' => esc_html__('Секретный ключ для clean/address (не передаётся в браузер)', 'codeweber'),
				'required' => array( 'dadata_enabled', '=', true ),
			),
			array(
				'id'       => 'dadata_scenarios',
				'type'     => 'checkbox',
				'title'    => esc_html__('Где показывать кнопку «Проверить адрес»', 'codeweber'),
				'subtitle' => esc_html__('Выберите страницы с формой адреса', 'codeweber'),
				'required' => array( 'dadata_enabled', '=', true ),
				'options'  => array(
					'edit_address' => esc_html__('Редактирование адреса (Мой аккаунт)', 'codeweber'),
					'checkout'     => esc_html__('Оформление заказа (чекаут)', 'codeweber'),
				),
				'default'  => array( 'edit_address' => true, 'checkout' => true ),
			),
			array(
				'id'       => 'dadata_checkout_phone_mask',
				'type'     => 'switch',
				'title'    => esc_html__('Чекаут: маска телефона', 'codeweber'),
				'subtitle' => esc_html__('Форматирование поля телефона: +7 (xxx) xxx-xx-xx', 'codeweber'),
				'required' => array( 'dadata_enabled', '=', true ),
				'default'  => true,
			),
			array(
				'id'       => 'yandexapi',
				'type'     => 'password',
				'title'    => esc_html__('Yandex API Map Settings', 'codeweber'),
				'subtitle' => esc_html__('Put Yandex API Map key', 'codeweber'),
				'desc'     => '<a href="https://developer.tech.yandex.ru/services/3" target="_blank" >'. esc_html__('Link to Yandex Developer Console', 'codeweber'). '</a>',
				//'default'  => 'Default Text',
			),

			array(
				'id'       => 'smsruapi',
				'type'     => 'password',
				'title'    => esc_html__('SMS RU Settings', 'codeweber'),
				'subtitle' => esc_html__('Put SMS RU API Map key', 'codeweber'),
				'desc'     => '<a href="https://sms.ru/?panel=api" target="_blank" >' . esc_html__('Link to SMS RU Documentation', 'codeweber') . '</a>',
				//'default'  => 'Default Text',
			),

		),
	)
);

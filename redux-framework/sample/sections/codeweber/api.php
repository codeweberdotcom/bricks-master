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
				'id'        => 'dadata_test_btn',
				'type'      => 'raw',
				'full_width' => false,
				'title'     => esc_html__('Тест DaData', 'codeweber'),
				'subtitle'  => esc_html__('Проверить соединение с DaData API (Suggest)', 'codeweber'),
				'required'  => array( 'dadata_enabled', '=', true ),
				'content'   => '<button type="button" class="button cw-api-test-btn" data-action="codeweber_api_test_dadata" data-field="dadata">' . esc_html__( 'Тест', 'codeweber' ) . '</button><span class="cw-api-test-result"></span>',
			),

			array(
				'id'       => 'yandexapi',
				'type'     => 'password',
				'title'    => esc_html__('Yandex API Map Settings', 'codeweber'),
				'subtitle' => esc_html__('Put Yandex API Map key', 'codeweber'),
				'desc'     => '<a href="https://developer.tech.yandex.ru/services/3" target="_blank" >'. esc_html__('Link to Yandex Developer Console', 'codeweber'). '</a>',
			),
			array(
				'id'        => 'yandexapi_test_btn',
				'type'      => 'raw',
				'full_width' => false,
				'title'     => esc_html__('Тест Yandex Maps', 'codeweber'),
				'subtitle'  => esc_html__('Проверить API ключ через Geocoder API', 'codeweber'),
				'content'   => '<button type="button" class="button cw-api-test-btn" data-action="codeweber_api_test_yandex" data-field="yandexapi">' . esc_html__( 'Тест', 'codeweber' ) . '</button><span class="cw-api-test-result"></span>',
			),

			array(
				'id'       => 'smsruapi',
				'type'     => 'password',
				'title'    => esc_html__('SMS RU Settings', 'codeweber'),
				'subtitle' => esc_html__('Put SMS RU API Map key', 'codeweber'),
				'desc'     => '<a href="https://sms.ru/?panel=api" target="_blank" >' . esc_html__('Link to SMS RU Documentation', 'codeweber') . '</a>',
			),
			array(
				'id'        => 'smsruapi_test_btn',
				'type'      => 'raw',
				'full_width' => false,
				'title'     => esc_html__('Тест SMS.RU', 'codeweber'),
				'subtitle'  => esc_html__('Проверить авторизацию SMS.RU API', 'codeweber'),
				'content'   => '<button type="button" class="button cw-api-test-btn" data-action="codeweber_api_test_smsru" data-field="smsruapi">' . esc_html__( 'Тест', 'codeweber' ) . '</button><span class="cw-api-test-result"></span>',
			),


			// ── Telegram Bot ─────────────────────────────────────────────────────
			array(
				'id'       => 'telegram_bot_enabled',
				'type'     => 'switch',
				'title'    => esc_html__( 'Telegram Bot: enable notifications', 'codeweber' ),
				'subtitle' => esc_html__( 'Send form submissions to a Telegram chat or channel', 'codeweber' ),
				'default'  => false,
			),
			array(
				'id'       => 'telegram_bot_token',
				'type'     => 'password',
				'title'    => esc_html__( 'Telegram Bot Token', 'codeweber' ),
				'subtitle' => esc_html__( 'Get token from @BotFather: /newbot', 'codeweber' ),
				'desc'     => '<a href="https://t.me/BotFather" target="_blank">@BotFather</a>',
				'required' => array( 'telegram_bot_enabled', '=', true ),
			),
			array(
				'id'       => 'telegram_bot_chat_id',
				'type'     => 'text',
				'title'    => esc_html__( 'Chat / Channel ID', 'codeweber' ),
				'subtitle' => esc_html__( 'Numeric ID (-100…) or @channel_username. The bot must be added to the chat.', 'codeweber' ),
				'required' => array( 'telegram_bot_enabled', '=', true ),
			),
			array(
				'id'       => 'telegram_bot_events',
				'type'     => 'checkbox',
				'title'    => esc_html__( 'Events to notify', 'codeweber' ),
				'subtitle' => esc_html__( 'Which events trigger a Telegram notification', 'codeweber' ),
				'required' => array( 'telegram_bot_enabled', '=', true ),
				'options'  => array(
					'form'       => esc_html__( 'Form submissions (CodeWeber Forms)', 'codeweber' ),
					'order'      => esc_html__( 'New WooCommerce orders', 'codeweber' ),
					'newsletter' => esc_html__( 'New newsletter subscriptions', 'codeweber' ),
				),
				'default'  => array( 'form' => true, 'order' => false, 'newsletter' => false ),
			),
			array(
				'id'         => 'telegram_bot_test_btn',
				'type'       => 'raw',
				'full_width' => false,
				'title'      => esc_html__( 'Test Telegram', 'codeweber' ),
				'subtitle'   => esc_html__( 'Send a test message to the configured chat', 'codeweber' ),
				'required'   => array( 'telegram_bot_enabled', '=', true ),
				'content'    => '<button type="button" class="button cw-api-test-btn" data-action="codeweber_api_test_telegram" data-field="telegram_bot_token" data-field2="telegram_bot_chat_id">' . esc_html__( 'Test', 'codeweber' ) . '</button><span class="cw-api-test-result"></span>',
			),

		),
	)
);

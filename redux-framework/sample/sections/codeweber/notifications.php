<?php
/**
 * Redux section: Уведомления (CWNotify)
 *
 * @package CodeWeber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Родительский раздел ───────────────────────────────────────────────────────

Redux::set_section(
	$opt_name,
	array(
		'title'  => esc_html__( 'Уведомления', 'codeweber' ),
		'id'     => 'notifications-settings',
		'desc'   => esc_html__( 'Управление всплывающими уведомлениями на сайте.', 'codeweber' ),
		'icon'   => 'el el-bell',
		'fields' => array(),
	)
);

// ── Основные настройки ────────────────────────────────────────────────────────

Redux::set_section(
	$opt_name,
	array(
		'title'      => esc_html__( 'Основные', 'codeweber' ),
		'id'         => 'notifications-general',
		'subsection' => true,
		'fields'     => array(

			array(
				'id'       => 'notify_enabled',
				'type'     => 'switch',
				'title'    => esc_html__( 'Включить уведомления', 'codeweber' ),
				'desc'     => esc_html__( 'Глобальное включение / отключение всех уведомлений.', 'codeweber' ),
				'default'  => 1,
				'on'       => esc_html__( 'Вкл', 'codeweber' ),
				'off'      => esc_html__( 'Выкл', 'codeweber' ),
			),

			array(
				'id'       => 'notify_position',
				'type'     => 'select',
				'title'    => esc_html__( 'Позиция на экране', 'codeweber' ),
				'desc'     => esc_html__( 'Угол экрана, в котором появляются уведомления.', 'codeweber' ),
				'default'  => 'bottom-end',
				'required' => array( 'notify_enabled', '=', 1 ),
				'options'  => array(
					'bottom-end'   => esc_html__( 'Снизу справа', 'codeweber' ),
					'bottom-start' => esc_html__( 'Снизу слева', 'codeweber' ),
					'top-end'      => esc_html__( 'Сверху справа', 'codeweber' ),
					'top-start'    => esc_html__( 'Сверху слева', 'codeweber' ),
				),
			),

			array(
				'id'       => 'notify_delay',
				'type'     => 'slider',
				'title'    => esc_html__( 'Время показа (мс)', 'codeweber' ),
				'desc'     => esc_html__( 'Уведомление автоматически закрывается через указанное время. 0 — не закрывать.', 'codeweber' ),
				'default'  => 3000,
				'min'      => 0,
				'max'      => 10000,
				'step'     => 500,
				'required' => array( 'notify_enabled', '=', 1 ),
			),

		),
	)
);

// ── Настройка событий ─────────────────────────────────────────────────────────

Redux::set_section(
	$opt_name,
	array(
		'title'      => esc_html__( 'События', 'codeweber' ),
		'id'         => 'notifications-events',
		'subsection' => true,
		'desc'       => esc_html__( 'Выберите, для каких действий показывать уведомления.', 'codeweber' ),
		'fields'     => array(

			array(
				'id'      => 'notify_event_wishlist',
				'type'    => 'switch',
				'title'   => esc_html__( 'Избранное (Wishlist)', 'codeweber' ),
				'desc'    => esc_html__( 'Уведомление при добавлении товара в избранное.', 'codeweber' ),
				'default' => 1,
				'on'      => esc_html__( 'Вкл', 'codeweber' ),
				'off'     => esc_html__( 'Выкл', 'codeweber' ),
			),

			array(
				'id'      => 'notify_event_cart',
				'type'    => 'switch',
				'title'   => esc_html__( 'Корзина (Add to cart)', 'codeweber' ),
				'desc'    => esc_html__( 'Уведомление при добавлении товара в корзину.', 'codeweber' ),
				'default' => 1,
				'on'      => esc_html__( 'Вкл', 'codeweber' ),
				'off'     => esc_html__( 'Выкл', 'codeweber' ),
			),

			array(
				'id'      => 'notify_event_form',
				'type'    => 'switch',
				'title'   => esc_html__( 'Формы (CodeWeber Forms)', 'codeweber' ),
				'desc'    => esc_html__( 'Уведомление после отправки формы.', 'codeweber' ),
				'default' => 1,
				'on'      => esc_html__( 'Вкл', 'codeweber' ),
				'off'     => esc_html__( 'Выкл', 'codeweber' ),
			),

			array(
				'id'      => 'notify_event_newsletter',
				'type'    => 'switch',
				'title'   => esc_html__( 'Подписка на рассылку', 'codeweber' ),
				'desc'    => esc_html__( 'Уведомление после подписки на рассылку.', 'codeweber' ),
				'default' => 1,
				'on'      => esc_html__( 'Вкл', 'codeweber' ),
				'off'     => esc_html__( 'Выкл', 'codeweber' ),
			),

			array(
				'id'      => 'notify_event_dadata',
				'type'    => 'switch',
				'title'   => esc_html__( 'DaData (ошибки стандартизации)', 'codeweber' ),
				'desc'    => esc_html__( 'Уведомление при ошибке стандартизации адреса.', 'codeweber' ),
				'default' => 1,
				'on'      => esc_html__( 'Вкл', 'codeweber' ),
				'off'     => esc_html__( 'Выкл', 'codeweber' ),
			),

			array(
				'id'      => 'notify_event_copy',
				'type'    => 'switch',
				'title'   => esc_html__( 'Копирование (Image Licenses и др.)', 'codeweber' ),
				'desc'    => esc_html__( 'Уведомление при копировании ссылки в буфер обмена.', 'codeweber' ),
				'default' => 1,
				'on'      => esc_html__( 'Вкл', 'codeweber' ),
				'off'     => esc_html__( 'Выкл', 'codeweber' ),
			),

		),
	)
);

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
		'title'  => esc_html__( 'Notifications', 'codeweber' ),
		'id'     => 'notifications-settings',
		'desc'   => esc_html__( 'Manage pop-up notifications on the site.', 'codeweber' ),
		'icon'   => 'el el-bell',
		'fields' => array(),
	)
);

// ── Основные настройки ────────────────────────────────────────────────────────

Redux::set_section(
	$opt_name,
	array(
		'title'      => esc_html__( 'General', 'codeweber' ),
		'id'         => 'notifications-general',
		'subsection' => true,
		'fields'     => array(

			array(
				'id'       => 'notify_enabled',
				'type'     => 'switch',
				'title'    => esc_html__( 'Enable notifications', 'codeweber' ),
				'desc'     => esc_html__( 'Globally enable / disable all notifications.', 'codeweber' ),
				'default'  => 1,
				'on'       => esc_html__( 'On', 'codeweber' ),
				'off'      => esc_html__( 'Off', 'codeweber' ),
			),

			array(
				'id'       => 'notify_position',
				'type'     => 'select',
				'title'    => esc_html__( 'Screen position', 'codeweber' ),
				'desc'     => esc_html__( 'Screen corner where notifications appear.', 'codeweber' ),
				'default'  => 'bottom-end',
				'required' => array( 'notify_enabled', '=', 1 ),
				'options'  => array(
					'bottom-end'   => esc_html__( 'Bottom right', 'codeweber' ),
					'bottom-start' => esc_html__( 'Bottom left', 'codeweber' ),
					'top-end'      => esc_html__( 'Top right', 'codeweber' ),
					'top-start'    => esc_html__( 'Top left', 'codeweber' ),
				),
			),

			array(
				'id'       => 'notify_delay',
				'type'     => 'slider',
				'title'    => esc_html__( 'Display time (ms)', 'codeweber' ),
				'desc'     => esc_html__( 'The notification closes automatically after the specified time. 0 — do not close.', 'codeweber' ),
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
		'title'      => esc_html__( 'Events', 'codeweber' ),
		'id'         => 'notifications-events',
		'subsection' => true,
		'desc'       => esc_html__( 'Choose which actions trigger notifications.', 'codeweber' ),
		'fields'     => array(

			array(
				'id'      => 'notify_event_wishlist',
				'type'    => 'switch',
				'title'   => esc_html__( 'Wishlist', 'codeweber' ),
				'desc'    => esc_html__( 'Notification when a product is added to the wishlist.', 'codeweber' ),
				'default' => 1,
				'on'      => esc_html__( 'On', 'codeweber' ),
				'off'     => esc_html__( 'Off', 'codeweber' ),
			),

			array(
				'id'      => 'notify_event_cart',
				'type'    => 'switch',
				'title'   => esc_html__( 'Cart (Add to cart)', 'codeweber' ),
				'desc'    => esc_html__( 'Notification when a product is added to the cart.', 'codeweber' ),
				'default' => 1,
				'on'      => esc_html__( 'On', 'codeweber' ),
				'off'     => esc_html__( 'Off', 'codeweber' ),
			),

			array(
				'id'      => 'notify_event_form',
				'type'    => 'switch',
				'title'   => esc_html__( 'Forms (CodeWeber Forms)', 'codeweber' ),
				'desc'    => esc_html__( 'Notification after a form is submitted.', 'codeweber' ),
				'default' => 1,
				'on'      => esc_html__( 'On', 'codeweber' ),
				'off'     => esc_html__( 'Off', 'codeweber' ),
			),

			array(
				'id'      => 'notify_event_newsletter',
				'type'    => 'switch',
				'title'   => esc_html__( 'Newsletter subscription', 'codeweber' ),
				'desc'    => esc_html__( 'Notification after subscribing to the newsletter.', 'codeweber' ),
				'default' => 1,
				'on'      => esc_html__( 'On', 'codeweber' ),
				'off'     => esc_html__( 'Off', 'codeweber' ),
			),

			array(
				'id'      => 'notify_event_dadata',
				'type'    => 'switch',
				'title'   => esc_html__( 'DaData (standardization errors)', 'codeweber' ),
				'desc'    => esc_html__( 'Notification on address standardization error.', 'codeweber' ),
				'default' => 1,
				'on'      => esc_html__( 'On', 'codeweber' ),
				'off'     => esc_html__( 'Off', 'codeweber' ),
			),

			array(
				'id'      => 'notify_event_copy',
				'type'    => 'switch',
				'title'   => esc_html__( 'Copy (Image Licenses, etc.)', 'codeweber' ),
				'desc'    => esc_html__( 'Notification when a link is copied to the clipboard.', 'codeweber' ),
				'default' => 1,
				'on'      => esc_html__( 'On', 'codeweber' ),
				'off'     => esc_html__( 'Off', 'codeweber' ),
			),

		),
	)
);

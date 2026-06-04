<?php
/**
 * Redux section: Proxy.
 *
 * Outbound proxy for server-side HTTP requests. Currently consumed by the
 * Stock Photos module (search / preview / import) so that hosts blocked for the
 * web server can be reached through an external proxy (e.g. a VPS).
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

Redux::set_section(
	$opt_name,
	array(
		'title'  => esc_html__( 'Proxy', 'codeweber' ),
		'id'     => 'proxy',
		'desc'   => esc_html__( 'Outbound proxy for server-side requests (e.g. reaching image stocks through a VPS).', 'codeweber' ),
		'icon'   => 'el el-globe',
		'fields' => array(

			array(
				'id'    => 'proxy_info',
				'type'  => 'info',
				'style' => 'info',
				'title' => esc_html__( 'Outbound proxy', 'codeweber' ),
				'desc'  => esc_html__( 'When enabled, the Stock Photos module routes its server-side requests (search, preview, import) through this proxy. Requires the cURL transport.', 'codeweber' ),
			),
			array(
				'id'       => 'proxy_enabled',
				'type'     => 'switch',
				'title'    => esc_html__( 'Enable proxy', 'codeweber' ),
				'subtitle' => esc_html__( 'Route stock photo requests through the proxy below', 'codeweber' ),
				'default'  => false,
			),
			array(
				'id'       => 'proxy_host',
				'type'     => 'text',
				'title'    => esc_html__( 'Proxy host', 'codeweber' ),
				'subtitle' => esc_html__( 'IP or hostname of the proxy server', 'codeweber' ),
				'placeholder' => '203.0.113.10',
				'required' => array( 'proxy_enabled', '=', true ),
			),
			array(
				'id'       => 'proxy_port',
				'type'     => 'text',
				'title'    => esc_html__( 'Proxy port', 'codeweber' ),
				'subtitle' => esc_html__( 'e.g. 8888', 'codeweber' ),
				'placeholder' => '8888',
				'required' => array( 'proxy_enabled', '=', true ),
			),
			array(
				'id'       => 'proxy_type',
				'type'     => 'select',
				'title'    => esc_html__( 'Proxy type', 'codeweber' ),
				'options'  => array(
					'http'   => 'HTTP / HTTPS',
					'socks5' => 'SOCKS5',
				),
				'default'  => 'http',
				'required' => array( 'proxy_enabled', '=', true ),
			),
			array(
				'id'       => 'proxy_user',
				'type'     => 'text',
				'title'    => esc_html__( 'Proxy username', 'codeweber' ),
				'subtitle' => esc_html__( 'Leave empty if the proxy has no authentication', 'codeweber' ),
				'required' => array( 'proxy_enabled', '=', true ),
			),
			array(
				'id'       => 'proxy_pass',
				'type'     => 'password',
				'title'    => esc_html__( 'Proxy password', 'codeweber' ),
				'required' => array( 'proxy_enabled', '=', true ),
			),
			array(
				'id'       => 'proxy_scope',
				'type'     => 'checkbox',
				'title'    => esc_html__( 'Use proxy for', 'codeweber' ),
				'subtitle' => esc_html__( 'Which modules route their outbound requests through the proxy', 'codeweber' ),
				'required' => array( 'proxy_enabled', '=', true ),
				'options'  => array(
					'stock_photos' => esc_html__( 'Stock Photos (Unsplash / Pexels / Pixabay / Openverse)', 'codeweber' ),
					'telegram'     => esc_html__( 'Telegram Bot', 'codeweber' ),
				),
				'default'  => array( 'stock_photos' => true, 'telegram' => false ),
			),
			array(
				'id'         => 'proxy_test_btn',
				'type'       => 'raw',
				'full_width' => false,
				'title'      => esc_html__( 'Test proxy', 'codeweber' ),
				'subtitle'   => esc_html__( 'Save settings first, then test — returns the egress IP seen by the internet', 'codeweber' ),
				'required'   => array( 'proxy_enabled', '=', true ),
				'content'    => '<button type="button" class="button cw-api-test-btn" data-action="codeweber_api_test_proxy" data-field="proxy_host">' . esc_html__( 'Test', 'codeweber' ) . '</button><span class="cw-api-test-result"></span>',
			),

		),
	)
);

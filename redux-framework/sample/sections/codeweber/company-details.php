<?php
Redux::set_section(
	$opt_name,
	array(
		'title'            => esc_html__("Company Details", "codeweber"),
		'id'               => 'company-details',
		'customizer_width' => '400px',
		'icon'             => 'el el-home',
		'fields'           => array(

			// О компании
			array(
				'id'     => 'section-about-company',
				'type'   => 'section',
				'title'  => esc_html__('About Company', 'codeweber'),
				'indent' => true,
			),
			array(
				'id'     => 'text-about-company',
				'type'   => 'textarea',
				'title'  => esc_html__('About Company', 'codeweber'),
				'desc'   => esc_html__('Shortcode: [redux_option key="text-about-company"]', 'codeweber'),
				'indent' => true,
			),

			// Юридический адрес
			array(
				'id'     => 'section-legal-address',
				'type'   => 'section',
				'title'  => esc_html__('Legal Address', 'codeweber'),
				'indent' => true,
			),
			array(
				'id'    => 'juri-country',
				'type'  => 'text',
				'title' => esc_html__('Country', 'codeweber'),
				'desc'  => esc_html__('Shortcode: [redux_option key="juri-country"]', 'codeweber'),
				'default' => 'Россия',
			),
			array(
				'id'    => 'juri-region',
				'type'  => 'text',
				'title' => esc_html__('Region', 'codeweber'),
				'desc'  => esc_html__('Shortcode: [redux_option key="juri-region"]', 'codeweber'),
				'default' => 'Краснодарский край',
			),
			array(
				'id'    => 'juri-city',
				'type'  => 'text',
				'title' => esc_html__('City', 'codeweber'),
				'desc'  => esc_html__('Shortcode: [redux_option key="juri-city"]', 'codeweber'),
				'default' => 'Краснодар',
			),
			array(
				'id'    => 'juri-street',
				'type'  => 'text',
				'title' => esc_html__('Street', 'codeweber'),
				'desc'  => esc_html__('Shortcode: [redux_option key="juri-street"]', 'codeweber'),
				'default' => 'ул. Ленина',
			),
			array(
				'id'    => 'juri-house',
				'type'  => 'text',
				'title' => esc_html__('House Number', 'codeweber'),
				'desc'  => esc_html__('Shortcode: [redux_option key="juri-house"]', 'codeweber'),
				'default' => 'д. 150',
			),
			array(
				'id'    => 'juri-office',
				'type'  => 'text',
				'title' => esc_html__('Office/Apartment', 'codeweber'),
				'desc'  => esc_html__('Shortcode: [redux_option key="juri-office"]', 'codeweber'),
				'default' => 'офис 15',
			),
			array(
				'id'    => 'juri-postal',
				'type'  => 'text',
				'title' => esc_html__('Postal Code', 'codeweber'),
				'desc'  => esc_html__('Shortcode: [redux_option key="juri-postal"]', 'codeweber'),
				'default' => '283048',
			),
			array(
				'id'     => 'section-legal-address-end',
				'type'   => 'section',
				'indent' => false,
			),

			// Фактический адрес
			array(
				'id'     => 'section-actual-address',
				'type'   => 'section',
				'title'  => esc_html__('Actual Address', 'codeweber'),
				'indent' => true,
			),
			array(
				'id'    => 'fact-country',
				'type'  => 'text',
				'title' => esc_html__('Country', 'codeweber'),
				'desc'  => esc_html__('Shortcode: [redux_option key="fact-country"]', 'codeweber'),
				'default' => 'Россия',
			),
			array(
				'id'    => 'fact-region',
				'type'  => 'text',
				'title' => esc_html__('Region', 'codeweber'),
				'desc'  => esc_html__('Shortcode: [redux_option key="fact-region"]', 'codeweber'),
				'default' => 'Краснодарский край',
			),
			array(
				'id'    => 'fact-city',
				'type'  => 'text',
				'title' => esc_html__('City', 'codeweber'),
				'desc'  => esc_html__('Shortcode: [redux_option key="fact-city"]', 'codeweber'),
				'default' => 'Краснодар',
			),
			array(
				'id'    => 'fact-street',
				'type'  => 'text',
				'title' => esc_html__('Street', 'codeweber'),
				'desc'  => esc_html__('Shortcode: [redux_option key="fact-street"]', 'codeweber'),
				'default' => 'ул. Ленина',
			),
			array(
				'id'    => 'fact-house',
				'type'  => 'text',
				'title' => esc_html__('House Number', 'codeweber'),
				'desc'  => esc_html__('Shortcode: [redux_option key="fact-house"]', 'codeweber'),
				'default' => 'д. 150',
			),
			array(
				'id'    => 'fact-office',
				'type'  => 'text',
				'title' => esc_html__('Office/Apartment', 'codeweber'),
				'desc'  => esc_html__('Shortcode: [redux_option key="fact-office"]', 'codeweber'),
				'default' => 'офис 15',
			),
			array(
				'id'    => 'fact-postal',
				'type'  => 'text',
				'title' => esc_html__('Postal Code', 'codeweber'),
				'desc'  => esc_html__('Shortcode: [redux_option key="fact-postal"]', 'codeweber'),
				'default' => '283048',
			),
			array(
				'id'     => 'section-actual-address-end',
				'type'   => 'section',
				'indent' => false,
			),

			// Банковские реквизиты
			array(
				'id'     => 'section-bank-details',
				'type'   => 'section',
				'title'  => esc_html__('Bank Account Details', 'codeweber'),
				'indent' => true,
			),
			array(
				'id'    => 'bank-name',
				'type'  => 'text',
				'title' => esc_html__('Bank Name', 'codeweber'),
				'desc'  => esc_html__('Shortcode: [redux_option key="bank-name"]', 'codeweber'),
				'default' => 'АО «ТБанк»',
			),
			array(
				'id'    => 'bank-bic',
				'type'  => 'text',
				'title' => esc_html__('BIC', 'codeweber'),
				'desc'  => esc_html__('Shortcode: [redux_option key="bank-bic"]', 'codeweber'),
				'default' => '044525974',
			),
			array(
				'id'    => 'bank-corr-account',
				'type'  => 'text',
				'title' => esc_html__('Corr. Account', 'codeweber'),
				'desc'  => esc_html__('Shortcode: [redux_option key="bank-corr-account"]', 'codeweber'),
				'default' => '30101810145250000974 в ГУ Банка России по ЦФО',
			),
			array(
				'id'    => 'bank-settlement-account',
				'type'  => 'text',
				'title' => esc_html__('Settlement Account', 'codeweber'),
				'desc'  => esc_html__('Shortcode: [redux_option key="bank-settlement-account"]', 'codeweber'),
				'default' => '30232810100000000004',
			),
			array(
				'id'    => 'bank-bank-tin',
				'type'  => 'text',
				'title' => esc_html__('Bank TIN', 'codeweber'),
				'desc'  => esc_html__('Shortcode: [redux_option key="bank-bank-tin"]', 'codeweber'),
				'default' => '7710140679',
			),
			array(
				'id'    => 'bank-bank-kpp',
				'type'  => 'text',
				'title' => esc_html__('Bank KPP', 'codeweber'),
				'desc'  => esc_html__('Shortcode: [redux_option key="bank-bank-kpp"]', 'codeweber'),
				'default' => '771301001',
			),
			array(
				'id'    => 'bank-bank-address',
				'type'  => 'text',
				'title' => esc_html__('Bank Address', 'codeweber'),
				'desc'  => esc_html__('Shortcode: [redux_option key="bank-bank-address"]', 'codeweber'),
				'default' => '127287, г. Москва, ул. Хуторская 2-я, д. 38А, стр. 26',
			),
			array(
				'id'     => 'section-bank-details-end',
				'type'   => 'section',
				'indent' => false,
			),
		),
	)
);

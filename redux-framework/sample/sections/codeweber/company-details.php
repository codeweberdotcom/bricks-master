<?php

/**
 * Generate the Opening Hours table HTML for the Redux raw field.
 * Uses standard Redux input names so values are saved automatically.
 */
function codeweber_redux_opening_hours_table(): string {
	$days = array(
		'monday'    => __( 'Monday', 'codeweber' ),
		'tuesday'   => __( 'Tuesday', 'codeweber' ),
		'wednesday' => __( 'Wednesday', 'codeweber' ),
		'thursday'  => __( 'Thursday', 'codeweber' ),
		'friday'    => __( 'Friday', 'codeweber' ),
		'saturday'  => __( 'Saturday', 'codeweber' ),
		'sunday'    => __( 'Sunday', 'codeweber' ),
	);

	$opts = get_option( 'redux_demo', array() );

	$html  = '<table class="widefat" style="max-width:600px;">';
	$html .= '<thead><tr>';
	$html .= '<th>' . esc_html__( 'Day', 'codeweber' ) . '</th>';
	$html .= '<th>' . esc_html__( 'Opens', 'codeweber' ) . '</th>';
	$html .= '<th>' . esc_html__( 'Break start', 'codeweber' ) . '</th>';
	$html .= '<th>' . esc_html__( 'Break end', 'codeweber' ) . '</th>';
	$html .= '<th>' . esc_html__( 'Closes', 'codeweber' ) . '</th>';
	$html .= '</tr></thead><tbody>';

	foreach ( $days as $key => $label ) {
		$o1 = isset( $opts[ 'opening_hours_' . $key . '_opens_1' ] )  ? $opts[ 'opening_hours_' . $key . '_opens_1' ]  : '';
		$c1 = isset( $opts[ 'opening_hours_' . $key . '_closes_1' ] ) ? $opts[ 'opening_hours_' . $key . '_closes_1' ] : '';
		$o2 = isset( $opts[ 'opening_hours_' . $key . '_opens_2' ] )  ? $opts[ 'opening_hours_' . $key . '_opens_2' ]  : '';
		$c2 = isset( $opts[ 'opening_hours_' . $key . '_closes_2' ] ) ? $opts[ 'opening_hours_' . $key . '_closes_2' ] : '';

		$html .= '<tr>';
		$html .= '<td><strong>' . esc_html( $label ) . '</strong></td>';
		$html .= '<td><input type="text" name="redux_demo[opening_hours_' . esc_attr( $key ) . '_opens_1]" value="' . esc_attr( $o1 ) . '" placeholder="09:00" style="width:70px;"></td>';
		$html .= '<td><input type="text" name="redux_demo[opening_hours_' . esc_attr( $key ) . '_closes_1]" value="' . esc_attr( $c1 ) . '" placeholder="13:00" style="width:70px;"></td>';
		$html .= '<td><input type="text" name="redux_demo[opening_hours_' . esc_attr( $key ) . '_opens_2]" value="' . esc_attr( $o2 ) . '" placeholder="14:00" style="width:70px;"></td>';
		$html .= '<td><input type="text" name="redux_demo[opening_hours_' . esc_attr( $key ) . '_closes_2]" value="' . esc_attr( $c2 ) . '" placeholder="18:00" style="width:70px;"></td>';
		$html .= '</tr>';
	}

	$html .= '</tbody></table>';

	return $html;
}

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

			// Информация о шорткоде адреса
			array(
				'id'     => 'info-address-shortcode',
				'type'   => 'info',
				'title'  => esc_html__('Address Shortcode', 'codeweber'),
				'style'  => 'info',
				'desc'   => esc_html__('Use the [address] shortcode to output the full address.', 'codeweber') . '<br><br>' .
					'<strong>' . esc_html__('Usage examples:', 'codeweber') . '</strong><br>' .
					'<code>[address]</code> - ' . esc_html__('Actual address', 'codeweber') . '<br>' .
					'<code>[address type="juri"]</code> - ' . esc_html__('Legal address', 'codeweber') . '<br>' .
					'<code>[address separator=", "]</code> - ' . esc_html__('With comma separator', 'codeweber') . '<br>' .
					'<code>[address type="juri" separator=", " fallback="Address not set"]</code> - ' . esc_html__('With custom fallback', 'codeweber'),
				'indent' => false,
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

			// Часы работы компании
			array(
				'id'       => 'opening_hours_table',
				'type'     => 'raw',
				'title'    => esc_html__( 'Opening Hours', 'codeweber' ),
				'subtitle' => esc_html__( 'Used in Schema.org structured data. Leave empty for days off. Second pair is optional (lunch break).', 'codeweber' ),
				'content'  => codeweber_redux_opening_hours_table(),
			),
		),
	)
);

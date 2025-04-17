<?php

Redux::set_section(
	$opt_name,
	array(
		'title'            => esc_html__("Company Details", "codeweber"),
		'id'               => 'company-details',
		'customizer_width' => '400px',
		'icon'             => 'el el-home',
		'fields'           => array(
			array(
				'id'          => 'company-name',
				'type'        => 'text',
				'title'       => esc_html__('Company Name', 'codeweber'),
				'placeholder' => array(
					'box1' => 'Limited Liability Company "Horns and Hoofs"',
					'box2' => 'LLC "Horns and Hoofs"',
				),
				'data'        => array(
					'box1' => esc_html__('Full Name', 'codeweber'),
					'box2' => esc_html__('Short Name', 'codeweber'),
				),
			),
			array(
				'id'       => 'company-description',
				'type'     => 'textarea',
				'title'    =>  esc_html__('Company Description', 'codeweber'),
				'default'  => esc_html__('Default text', 'codeweber'),
			),
			array(
				'id'          => 'juri-company-adress',
				'type'        => 'text',
				'title'       => esc_html__('Legal Address', 'codeweber'),
				'data'        => array(
					'box1' => esc_html__('Country', 'codeweber'),
					'box2' => esc_html__('Region', 'codeweber'),
					'box3' => esc_html__('City', 'codeweber'),
					'box4' => esc_html__('Street', 'codeweber'),
					'box5' => esc_html__('House Number', 'codeweber'),
					'box6' => esc_html__('Office/Apartment', 'codeweber'),
					'box7' => esc_html__('Postal Code', 'codeweber'),
				),
			),
			array(
				'id'          => 'fact-company-adress',
				'type'        => 'text',
				'title'       => esc_html__('Actual Address', 'codeweber'),
				'data'        => array(
					'box1' => esc_html__('Country', 'codeweber'),
					'box2' => esc_html__('Region', 'codeweber'),
					'box3' => esc_html__('City', 'codeweber'),
					'box4' => esc_html__('Street', 'codeweber'),
					'box5' => esc_html__('House Number', 'codeweber'),
					'box6' => esc_html__('Office/Apartment', 'codeweber'),
					'box7' => esc_html__('Postal Code', 'codeweber'),
				),
			),
			array(
				'id'          => 'rekvisity-company-bank',
				'type'        => 'text',
				'title'       => esc_html__('Bank Account Details', 'codeweber'),
				'data'        => array(
					'box1' => esc_html__('Company TIN', 'codeweber'),
					'box2' => esc_html__('Company PSRN', 'codeweber'),
					'box3' => esc_html__('Bank Name', 'codeweber'),
					'box4' => esc_html__('BIC', 'codeweber'),
					'box5' => esc_html__('Corr. Account', 'codeweber'),
					'box6' => esc_html__('Settlement Account', 'codeweber'),
					'box7' => esc_html__('Bank TIN', 'codeweber'),
					'box8' => esc_html__('Bank KPP', 'codeweber'),
					'box9' => esc_html__('Bank Address', 'codeweber'),
				),
			),
		),
	)
);

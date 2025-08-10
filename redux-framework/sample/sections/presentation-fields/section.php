<?php
/**
 * Redux Framework section config.
 * For full documentation, please visit: https://devs.redux.io/
 *
 * @package Redux Framework
 */

defined( 'ABSPATH' ) || exit;

Redux::set_section(
	$opt_name,
	array(
		'title'      => esc_html__( 'Section', 'your-textdomain-here' ),
		'id'         => 'presentation-section',
		'desc'       => esc_html__( 'For full documentation on this field, visit: ', 'your-textdomain-here' ) . '<a href="https://devs.redux.io/core-fields/section.html" target="_blank">https://devs.redux.io/core-fields/section.html</a>',
		'subsection' => true,
		'fields'     => array(
			array(
				'id'       => 'sectidon-start',
				'type'     => 'section',
				'title'    => esc_html__( 'Section Example', 'your-textdomain-here' ),
				'subtitle' => esc_html__( 'With the "section" field you can create indented option sections.', 'your-textdomain-here' ),
				'indent'   => true, // Indent all options below until the next 'section' option is set.
			),
			array(
				'id'       => 'sectidon-test',
				'type'     => 'text',
				'title'    => esc_html__( 'Field Title', 'your-textdomain-here' ),
				'subtitle' => esc_html__( 'Field Subtitle', 'your-textdomain-here' ),
			),
			array(
				'id'       => 'secdtion-test-media',
				'type'     => 'media',
				'title'    => esc_html__( 'Field Title', 'your-textdomain-here' ),
				'subtitle' => esc_html__( 'Field Subtitle', 'your-textdomain-here' ),
			),
			array(
				'id'     => 'sectiond-end',
				'type'   => 'section',
				'indent' => false, // Indent all options below until the next 'section' option is set.
			),
			array(
				'id'   => 'sectiodn-info',
				'type' => 'info',
				'desc' => esc_html__( 'And now you can add more fields below and outside of the indent.', 'your-textdomain-here' ),
			),

			array(
				'id'       => 'sectiodn-start',
				'type'     => 'section',
				'title'    => esc_html__('Section Example', 'your-textdomain-here'),
				'subtitle' => esc_html__('With the "section" field you can create indented option sections.', 'your-textdomain-here'),
				'indent'   => true, // Indent all options below until the next 'section' option is set.
			),
			array(
				'id'       => 'sectiond-test',
				'type'     => 'text',
				'title'    => esc_html__('Field Title', 'your-textdomain-here'),
				'subtitle' => esc_html__('Field Subtitle', 'your-textdomain-here'),
			),
			array(
				'id'       => 'sectiond-test-media',
				'type'     => 'media',
				'title'    => esc_html__('Field Title', 'your-textdomain-here'),
				'subtitle' => esc_html__('Field Subtitle', 'your-textdomain-here'),
			),
			array(
				'id'     => 'section-endd',
				'type'   => 'section',
				'indent' => false, // Indent all options below until the next 'section' option is set.
			),
			array(
				'id'   => 'section-infod',
				'type' => 'info',
				'desc' => esc_html__('And now you can add more fields below and outside of the indent.', 'your-textdomain-here'),
			),
		),
	)
);

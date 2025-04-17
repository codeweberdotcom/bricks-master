<?php
/**
 * Redux Repeater Sample config.
 * For full documentation, please visit: https://devs.redux.io
 *
 * @package Redux
 */

defined( 'ABSPATH' ) || exit;

Redux::set_section(
	$opt_name,
	array(
		'title'      => __('Phone', 'codeweber' ),
		'desc'       => esc_html__( 'For full documentation on this field, visit: ', 'codeweber' ) . '<a href="https://devs.redux.io/core-extensions/repeater.html" target="_blank">https://devs.redux.io/core-extensions/repeater.html</a>',
		'subsection' => true,
		'fields'     => array(
			array(
				'id'          => 'phone-field-id',
				'type'        => 'repeater',
				'title'       => esc_html__( 'Phone', 'codeweber' ),
				'full_width'  => true,
				'subtitle'    => esc_html__('Phone', 'codeweber' ),
				'item_name'   => '',
				'sortable'    => true,
				'active'      => false,
				'collapsible' => false,
				'fields'      => array(
					array(
						'id'          => 'title_field',
						'type'        => 'text',
						'placeholder' => esc_html__( 'Title', 'codeweber' ),
					),

				),
			),
		),
	)
);

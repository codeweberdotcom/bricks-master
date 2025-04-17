<?php

/**
 * https://developer.wordpress.org/themes/functionality/navigation-menus/
 */

if ( ! function_exists( 'codeweber_navmenus' ) ) {

	function codeweber_navmenus() {

		register_nav_menus(
			array(
				'header' => esc_html__( 'Header Menu', 'codeweber' ),
				'header_1' => esc_html__('Header Menu 1', 'codeweber'),
				'offcanvas' => esc_html__('Offcanvas Menu', 'codeweber'),
				'footer' => esc_html__( 'Footer Menu', 'codeweber' ),
				'footer_1' => esc_html__('Footer Menu 1', 'codeweber'),
				'footer_2' => esc_html__('Footer Menu 2', 'codeweber'),
				'footer_3' => esc_html__('Footer Menu 3', 'codeweber'),
				'footer_4' => esc_html__('Footer Menu 4', 'codeweber'),
			)
		);

	}
}

add_action( 'after_setup_theme', 'codeweber_navmenus' );

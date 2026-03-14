<?php
/**
 * Обёртки стилей Redux → Codeweber_Options::style().
 *
 * Все функции делегируют вызов в Codeweber_Options::style(),
 * который загружает все опции за один get_option() и кэширует на запрос.
 *
 * @package Codeweber
 */

if ( ! function_exists( 'getThemeButton' ) ) {
	function getThemeButton( $default_class = ' rounded-pill' ) {
		return Codeweber_Options::style( 'button', $default_class );
	}
}

if ( ! function_exists( 'getThemeCardImageRadius' ) ) {
	function getThemeCardImageRadius( $default_class = '' ) {
		return Codeweber_Options::style( 'card-radius', $default_class );
	}
}

if ( ! function_exists( 'getThemeAccordionCardRadius' ) ) {
	function getThemeAccordionCardRadius() {
		return Codeweber_Options::style( 'accordion-radius' );
	}
}

if ( ! function_exists( 'getThemeFormRadius' ) ) {
	function getThemeFormRadius( $default_class = ' rounded' ) {
		return Codeweber_Options::style( 'form-radius', $default_class );
	}
}

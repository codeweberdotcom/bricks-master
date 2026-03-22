<?php
/**
 * WooCommerce Checkout — Bootstrap form-floating fields
 *
 * Переопределяет вывод полей checkout через фильтр woocommerce_form_field_{type}.
 * Текстовые поля → Bootstrap form-floating.
 * Select-поля     → form-select-wrapper + form-select.
 * Country/State   → WC рендерит сам (select2), добавляем только form-select класс.
 */

defined( 'ABSPATH' ) || exit;

// ── Текстовые поля → form-floating ────────────────────────────────────────────

foreach ( array( 'text', 'email', 'tel', 'number', 'password' ) as $_type ) {
	add_filter( "woocommerce_form_field_{$_type}", 'cw_checkout_field_text', 10, 4 );
}

function cw_checkout_field_text( $field, $key, $args, $value ) {
	$sort        = $args['priority'] ?? '';
	$id          = $args['id'];
	$type        = $args['type'];
	$placeholder = $args['placeholder'] ?: wp_strip_all_tags( $args['label'] );

	$input_classes = array_unique( array_merge(
		array( 'input-text', 'form-control' ),
		(array) $args['input_class']
	) );

	$required_abbr = $args['required']
		? '&nbsp;<abbr class="required" title="' . esc_attr__( 'required', 'woocommerce' ) . '">*</abbr>'
		: '';

	$label = $args['label']
		? '<label for="' . esc_attr( $id ) . '">' . esc_html( $args['label'] ) . $required_abbr . '</label>'
		: '';

	$custom_attrs = '';
	if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
		foreach ( $args['custom_attributes'] as $attr => $attr_value ) {
			$custom_attrs .= ' ' . esc_attr( $attr ) . '="' . esc_attr( $attr_value ) . '"';
		}
	}

	$input = '<input type="' . esc_attr( $type ) . '"'
		. ' class="' . esc_attr( implode( ' ', $input_classes ) ) . '"'
		. ' name="' . esc_attr( $key ) . '"'
		. ' id="' . esc_attr( $id ) . '"'
		. ' placeholder="' . esc_attr( $placeholder ) . '"'
		. ' value="' . esc_attr( $value ) . '"'
		. ( $args['autocomplete'] ? ' autocomplete="' . esc_attr( $args['autocomplete'] ) . '"' : '' )
		. $custom_attrs
		. '>';

	return '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) . '"'
		. ' id="' . esc_attr( $id ) . '_field"'
		. ' data-priority="' . esc_attr( $sort ) . '">'
		. '<span class="woocommerce-input-wrapper">'
		. '<div class="form-floating">'
		. $input
		. $label
		. '</div>'
		. '</span>'
		. '</p>';
}

// ── Select → form-select-wrapper ──────────────────────────────────────────────

add_filter( 'woocommerce_form_field_select', 'cw_checkout_field_select', 10, 4 );

function cw_checkout_field_select( $field, $key, $args, $value ) {
	if ( empty( $args['options'] ) ) {
		return $field;
	}

	$sort = $args['priority'] ?? '';
	$id   = $args['id'];

	$select_classes = array_unique( array_merge(
		array( 'form-select' ),
		(array) $args['input_class']
	) );

	$required_abbr = $args['required']
		? '&nbsp;<abbr class="required" title="' . esc_attr__( 'required', 'woocommerce' ) . '">*</abbr>'
		: '';

	$label = $args['label']
		? '<label for="' . esc_attr( $id ) . '">' . esc_html( $args['label'] ) . $required_abbr . '</label>'
		: '';

	$custom_attrs = '';
	if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
		foreach ( $args['custom_attributes'] as $attr => $attr_value ) {
			$custom_attrs .= ' ' . esc_attr( $attr ) . '="' . esc_attr( $attr_value ) . '"';
		}
	}

	$options = '';
	foreach ( $args['options'] as $option_key => $option_text ) {
		$options .= '<option value="' . esc_attr( $option_key ) . '"'
			. selected( $value, $option_key, false )
			. '>' . esc_html( $option_text ) . '</option>';
	}

	return '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) . '"'
		. ' id="' . esc_attr( $id ) . '_field"'
		. ' data-priority="' . esc_attr( $sort ) . '">'
		. $label
		. '<span class="woocommerce-input-wrapper">'
		. '<div class="form-select-wrapper">'
		. '<select name="' . esc_attr( $key ) . '"'
		. ' id="' . esc_attr( $id ) . '"'
		. ' class="' . esc_attr( implode( ' ', $select_classes ) ) . '"'
		. $custom_attrs
		. '>'
		. $options
		. '</select>'
		. '</div>'
		. '</span>'
		. '</p>';
}

// ── Country / State → добавляем form-select класс (WC рендерит сам) ───────────

add_filter( 'woocommerce_form_field_args', 'cw_checkout_country_state_args', 10, 3 );

function cw_checkout_country_state_args( $args, $key, $value ) {
	if ( in_array( $args['type'], array( 'country', 'state' ), true ) ) {
		$args['input_class'][] = 'form-select';
	}
	return $args;
}

// ── Textarea → form-floating ───────────────────────────────────────────────────

add_filter( 'woocommerce_form_field_textarea', 'cw_checkout_field_textarea', 10, 4 );

function cw_checkout_field_textarea( $field, $key, $args, $value ) {
	$sort        = $args['priority'] ?? '';
	$id          = $args['id'];
	$placeholder = $args['placeholder'] ?: wp_strip_all_tags( $args['label'] );

	$input_classes = array_unique( array_merge(
		array( 'input-text', 'form-control' ),
		(array) $args['input_class']
	) );

	$required_abbr = $args['required']
		? '&nbsp;<abbr class="required" title="' . esc_attr__( 'required', 'woocommerce' ) . '">*</abbr>'
		: '';

	$label = $args['label']
		? '<label for="' . esc_attr( $id ) . '">' . esc_html( $args['label'] ) . $required_abbr . '</label>'
		: '';

	$rows = isset( $args['custom_attributes']['rows'] ) ? $args['custom_attributes']['rows'] : 4;

	$custom_attrs = '';
	if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
		foreach ( $args['custom_attributes'] as $attr => $attr_value ) {
			$custom_attrs .= ' ' . esc_attr( $attr ) . '="' . esc_attr( $attr_value ) . '"';
		}
	}

	return '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) . '"'
		. ' id="' . esc_attr( $id ) . '_field"'
		. ' data-priority="' . esc_attr( $sort ) . '">'
		. '<span class="woocommerce-input-wrapper">'
		. '<div class="form-floating">'
		. '<textarea name="' . esc_attr( $key ) . '"'
		. ' id="' . esc_attr( $id ) . '"'
		. ' class="' . esc_attr( implode( ' ', $input_classes ) ) . '"'
		. ' placeholder="' . esc_attr( $placeholder ) . '"'
		. ' rows="' . esc_attr( $rows ) . '"'
		. $custom_attrs
		. '>' . esc_textarea( $value ) . '</textarea>'
		. $label
		. '</div>'
		. '</span>'
		. '</p>';
}

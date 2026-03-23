<?php
/**
 * WooCommerce Checkout — Bootstrap form-floating fields
 *
 * Переопределяет вывод полей checkout через фильтр woocommerce_form_field_{type}.
 * Текстовые поля → Bootstrap form-floating.
 * Select-поля     → form-select-wrapper + form-select.
 * Country/State   → WC рендерит сам (select2), добавляем только form-select класс.
 *
 * Важно: обёртка полей — <div class="form-row">, НЕ <p>.
 * <div class="form-floating"> внутри <p> — невалидный HTML, браузер выталкивает
 * div наружу, и p остаётся пустым, ломая layout.
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

	return '<div class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) . '"'
		. ' id="' . esc_attr( $id ) . '_field"'
		. ' data-priority="' . esc_attr( $sort ) . '">'
		. '<span class="woocommerce-input-wrapper">'
		. '<div class="form-floating">'
		. $input
		. $label
		. '</div>'
		. '</span>'
		. '</div>';
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

	return '<div class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) . '"'
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
		. '</div>';
}

// ── Redux: управление полями чекаута ─────────────────────────────────────────
// Фильтр читает Redux-настройки и для каждого поля: включает/выключает,
// устанавливает required, применяет ширину (full/half с авто-парением).

add_filter( 'woocommerce_checkout_fields', 'cw_checkout_fields_from_redux', 20 );

function cw_checkout_fields_from_redux( $fields ) {
	$opts = get_option( 'redux_demo', array() );

	// Дефолты (зеркало Redux-дефолтов) — используются если Redux ещё не сохранён
	$defaults = array(
		'billing'  => array(
			'first_name' => array( 'enabled' => true,  'required' => true,  'width' => 'half' ),
			'last_name'  => array( 'enabled' => true,  'required' => true,  'width' => 'half' ),
			'company'    => array( 'enabled' => true,  'required' => false, 'width' => 'half' ),
			'country'    => array( 'enabled' => true,  'required' => true,  'width' => 'half' ),
			'address_1'  => array( 'enabled' => true,  'required' => true,  'width' => 'half' ),
			'address_2'  => array( 'enabled' => true,  'required' => false, 'width' => 'half' ),
			'city'       => array( 'enabled' => true,  'required' => true,  'width' => 'half' ),
			'state'      => array( 'enabled' => true,  'required' => false, 'width' => 'half' ),
			'postcode'   => array( 'enabled' => true,  'required' => true,  'width' => 'half' ),
			'email'      => array( 'enabled' => true,  'required' => true,  'width' => 'half' ),
			'phone'      => array( 'enabled' => true,  'required' => true,  'width' => 'half' ),
		),
		'shipping' => array(
			'first_name' => array( 'enabled' => true,  'required' => false, 'width' => 'half' ),
			'last_name'  => array( 'enabled' => true,  'required' => false, 'width' => 'half' ),
			'company'    => array( 'enabled' => true,  'required' => false, 'width' => 'half' ),
			'country'    => array( 'enabled' => true,  'required' => false, 'width' => 'half' ),
			'address_1'  => array( 'enabled' => true,  'required' => false, 'width' => 'half' ),
			'address_2'  => array( 'enabled' => true,  'required' => false, 'width' => 'half' ),
			'city'       => array( 'enabled' => true,  'required' => false, 'width' => 'half' ),
			'state'      => array( 'enabled' => true,  'required' => false, 'width' => 'half' ),
			'postcode'   => array( 'enabled' => true,  'required' => false, 'width' => 'half' ),
		),
	);

	foreach ( $defaults as $group => $keys ) {
		$half_counter = 0;

		foreach ( $keys as $key => $def ) {
			$field_key = "{$group}_{$key}";
			$id_base   = "woo_co_{$group}_{$key}";

			if ( ! isset( $fields[ $group ][ $field_key ] ) ) {
				continue;
			}

			// Enable / disable
			$enabled = isset( $opts["{$id_base}_enable"] ) ? (bool) $opts["{$id_base}_enable"] : $def['enabled'];
			if ( ! $enabled ) {
				unset( $fields[ $group ][ $field_key ] );
				continue;
			}

			// Required
			$required = isset( $opts["{$id_base}_required"] ) ? (bool) $opts["{$id_base}_required"] : $def['required'];
			$fields[ $group ][ $field_key ]['required'] = $required;

			// Width
			$width = isset( $opts["{$id_base}_width"] ) ? $opts["{$id_base}_width"] : $def['width'];
			$class = (array) ( $fields[ $group ][ $field_key ]['class'] ?? array() );

			if ( 'full' === $width ) {
				$class   = array_diff( $class, array( 'form-row-first', 'form-row-last' ) );
				$class[] = 'form-row-wide';
			} elseif ( 'half' === $width ) {
				$class   = array_diff( $class, array( 'form-row-wide' ) );
				$class[] = ( 0 === $half_counter % 2 ) ? 'form-row-first' : 'form-row-last';
				$half_counter++;
			}

			$fields[ $group ][ $field_key ]['class'] = array_values( array_unique( $class ) );
		}
	}

	// Order Notes
	$notes_enabled = isset( $opts['woo_co_order_comments_enable'] )
		? (bool) $opts['woo_co_order_comments_enable']
		: true;
	if ( ! $notes_enabled && isset( $fields['order']['order_comments'] ) ) {
		unset( $fields['order']['order_comments'] );
	}

	return $fields;
}

// ── Country / State → добавляем form-select класс (WC рендерит сам) ───────────

add_filter( 'woocommerce_form_field_args', 'cw_checkout_country_state_args', 10, 3 );

function cw_checkout_country_state_args( $args, $key, $value ) {
	if ( in_array( $args['type'], array( 'country', 'state' ), true ) ) {
		$args['input_class'][] = 'form-select';
	}
	return $args;
}

// ── Locale: убираем class-overrides WC для всех стран ─────────────────────────
// WC address-i18n.js применяет form-row-wide к address-полям при смене страны
// (данные берёт из wc_address_i18n_params.locale).
// Убираем ключ 'class' из locale — JS не трогает классы, наши PHP-классы остаются.

add_filter( 'woocommerce_get_country_locale', 'cw_checkout_strip_locale_classes' );

function cw_checkout_strip_locale_classes( $locale ) {
	foreach ( $locale as $country => $fields ) {
		foreach ( $fields as $field => $data ) {
			unset( $locale[ $country ][ $field ]['class'] );
		}
	}
	return $locale;
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

	return '<div class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) . '"'
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
		. '</div>';
}

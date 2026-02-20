<?php

/**
 * Шорткод [site_domain] выводит домен сайта (без http/https и www).
 *
 * Пример: example.com
 *
 * @return string
 */
add_shortcode('site_domain', function () {
   $host = parse_url(home_url(), PHP_URL_HOST);
   $host = preg_replace('/^www\./', '', $host); // убираем www
   return esc_html($host);
});


/**
 * Шорткод [site_domain_link]
 * Выводит ссылку на главную страницу сайта в виде <a href="...">...</a>
 *
 * Пример:
 * <a href="https://example.com">https://example.com</a>
 *
 * @return string
 */
add_shortcode('site_domain_link', function () {
   $url = home_url();
   return '<a href="' . esc_url($url) . '">' . esc_html($url) . '</a>';
});

/**
 * Шорткод [social_links] выводит список ссылок на социальные сети
 *
 * Параметры:
 * - type: тип отображения (type1, type2, type3, type4, type5, type6, type7, type8) - по умолчанию type1
 * - size: размер (lg, md, sm) - по умолчанию md
 * - class: дополнительные CSS-классы
 * - button-color: цвет кнопки для type8 (primary, red, blue, green и т.д.) - по умолчанию primary
 * - buttonstyle: стиль кнопки для type8 (solid или outline) - по умолчанию solid
 *
 * Типы отображения:
 * - type1: круглые кнопки с фоном, каждая соцсеть — свой стиль
 * - type2: иконки в muted-стиле (серые)
 * - type3: обычные цветные иконки без кнопок
 * - type4: белые иконки
 * - type5: тёмные круглые кнопки
 * - type6: кнопки с иконками и названиями соцсетей (широкие, белые)
 * - type7: кнопки с кастомным фоном соцсети (например, btn-telegram)
 * - type8: кнопки с настраиваемым цветом и стилем (solid/outline), без обертки nav social
 *
 * Для type8 доступны дополнительные параметры:
 * - button-color: цвет кнопки (primary, red, blue, green, purple, orange, yellow, navy, ash и т.д.)
 * - buttonstyle: стиль кнопки (solid - сплошная, outline - с обводкой)
 *
 * Примеры:
 * [social_links]
 * [social_links type="type1" size="md"]
 * [social_links type="type2" size="lg" class="my-custom-class"]
 * [social_links type="type8" button-color="primary" buttonstyle="solid"]
 * [social_links type="type8" button-color="red" buttonstyle="outline" size="lg"]
 *
 * @param array $atts Атрибуты шорткода
 * @return string HTML-код со ссылками на соцсети
 */
add_shortcode('social_links', function ($atts) {
	// Проверяем, что функция social_links существует
	if (!function_exists('social_links')) {
		return '<!-- Функция social_links не найдена -->';
	}
	
	// Парсим атрибуты
	$atts = shortcode_atts(array(
		'type' => 'type1',
		'size' => 'md',
		'class' => '',
		'button-color' => 'primary',
		'buttonstyle' => 'solid'
	), $atts, 'social_links');
	
	// Вызываем функцию social_links с новыми параметрами для type8
	return social_links($atts['class'], $atts['type'], $atts['size'], $atts['button-color'], $atts['buttonstyle']);
});

/**
 * Шорткод [address] выводит фактический или юридический адрес из настроек Redux
 *
 * Параметры:
 * - type: тип адреса ('fact' - фактический, 'juri' - юридический). По умолчанию 'fact'
 * - separator: разделитель между частями адреса. По умолчанию ', '
 * - fallback: текст по умолчанию, если адрес не заполнен
 *
 * Примеры:
 * [address] - фактический адрес с разделителем <br>
 * [address type="juri"] - юридический адрес
 * [address separator="<br> "] - адрес с разделителем перенос строки
 * [address type="juri" separator=", " fallback="Адрес не указан"]
 *
 * @param array $atts Атрибуты шорткода
 * @return string Отформатированный адрес
 */
add_shortcode('address', function ($atts) {
	// Проверяем, что функция codeweber_get_address существует
	if (!function_exists('codeweber_get_address')) {
		return '<!-- Функция codeweber_get_address не найдена -->';
	}
	
	// Парсим атрибуты
	$atts = shortcode_atts(array(
		'type' => 'fact',
		'separator' => ', ',
		'fallback' => 'Moonshine St. 14/05 Light City, London, United Kingdom'
	), $atts, 'address');
	
	// Вызываем функцию codeweber_get_address
	return codeweber_get_address($atts['type'], $atts['separator'], $atts['fallback']);
});

/**
 * Шорткод [menu_list] выводит список всех меню сайта (ID, slug, название и привязка к областям).
 *
 * Параметры:
 * - format: list (маркированный список) или table (таблица). По умолчанию list.
 *
 * Пример: [menu_list] или [menu_list format="table"]
 *
 * @param array $atts Атрибуты шорткода
 * @return string HTML списка меню
 */
add_shortcode( 'menu_list', function ( $atts ) {
	$atts = shortcode_atts( [
		'format' => 'list',
	], $atts, 'menu_list' );

	$menus = wp_get_nav_menus();
	if ( empty( $menus ) ) {
		return '<p>' . esc_html__( 'No menus created yet. Create menus in Appearance → Menus.', 'codeweber' ) . '</p>';
	}

	$locations = get_registered_nav_menus();
	$assigned  = get_nav_menu_locations();

	$out = '';
	if ( $atts['format'] === 'table' ) {
		$out .= '<table class="menu-list-table" border="1" cellpadding="8" style="border-collapse:collapse;"><thead><tr><th>ID</th><th>Slug</th><th>Name</th><th>Theme location (use in location="...")</th></tr></thead><tbody>';
		foreach ( $menus as $menu ) {
			$loc_names = [];
			foreach ( $assigned as $loc_slug => $menu_id ) {
				if ( (int) $menu_id === (int) $menu->term_id ) {
					$loc_names[] = $loc_slug . ( isset( $locations[ $loc_slug ] ) ? ' (' . $locations[ $loc_slug ] . ')' : '' );
				}
			}
			$out .= '<tr><td>' . (int) $menu->term_id . '</td><td><code>' . esc_html( $menu->slug ) . '</code></td><td>' . esc_html( $menu->name ) . '</td><td>' . ( $loc_names ? implode( ', ', $loc_names ) : '—' ) . '</td></tr>';
		}
		$out .= '</tbody></table>';
	} else {
		$out .= '<ul class="menu-list">';
		foreach ( $menus as $menu ) {
			$loc_names = [];
			foreach ( $assigned as $loc_slug => $menu_id ) {
				if ( (int) $menu_id === (int) $menu->term_id ) {
					$loc_names[] = $loc_slug;
				}
			}
			$loc_str = $loc_names ? ' → location: <code>' . implode( '</code>, <code>', $loc_names ) . '</code>' : ' (not assigned to any area)';
			$out .= '<li>ID: ' . (int) $menu->term_id . ', slug: <code>' . esc_html( $menu->slug ) . '</code>, name: ' . esc_html( $menu->name ) . $loc_str . '</li>';
		}
		$out .= '</ul>';
	}

	$out .= '<p><strong>' . esc_html__( 'Registered theme locations (for location="..."):', 'codeweber' ) . '</strong> <code>header</code>, <code>header_1</code>, <code>offcanvas</code>, <code>footer</code>, <code>footer_1</code>, <code>footer_2</code>, <code>footer_3</code>, <code>footer_4</code></p>';
	return $out;
} );
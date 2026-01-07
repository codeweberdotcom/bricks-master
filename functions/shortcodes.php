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
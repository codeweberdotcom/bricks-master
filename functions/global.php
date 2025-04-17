<?php

/**
 * Custom global functions.
 */



/**
 * Разрешает загрузку файлов форматов SVG и SVGZ в WordPress.
 *
 * По умолчанию WordPress запрещает загрузку SVG из соображений безопасности.
 * Эта функция добавляет поддержку MIME-типов для SVG и SVGZ.
 *
 * @param array $mimes Массив разрешенных типов файлов.
 * @return array Обновленный массив MIME-типов с добавленной поддержкой SVG.
 */

function codeweber_svg_upload($mimes)
{
	$mimes['svg']  = 'image/svg+xml';
	$mimes['svgz'] = 'image/svg+xml';

	return $mimes;
}
add_filter('upload_mimes', 'codeweber_svg_upload');



/**
 * Устанавливает корректный MIME-тип для SVG-файлов.
 *
 * WordPress по умолчанию блокирует загрузку SVG из соображений безопасности.
 * Эта функция исправляет MIME-тип, чтобы разрешить загрузку SVG и SVGZ файлов.
 *
 * @param array|null  $data     Данные о файле (тип, расширение).
 * @param string|null $file     Полный путь к файлу (необязательно).
 * @param string|null $filename Имя файла.
 * @param array|null  $mimes    Список разрешенных MIME-типов.
 * @return array|null Массив данных о файле с исправленным MIME-типом.
 */

function codeweber_svg_mimetype($data = null, $file = null, $filename = null, $mimes = null)
{
	$ext = isset($data['ext']) ? $data['ext'] : '';
	if (strlen($ext) < 1) {
		$exploded = explode('.', $filename);
		$ext      = strtolower(end($exploded));
	}
	if ('svg' === $ext) {
		$data['type'] = 'image/svg+xml';
		$data['ext']  = 'svg';
	} elseif ('svgz' === $ext) {
		$data['type'] = 'image/svg+xml';
		$data['ext']  = 'svgz';
	}

	return $data;
}
add_filter('wp_check_filetype_and_ext', 'codeweber_svg_mimetype', 10, 4);



/**
 * Изменяет длину отзыва (excerpt).
 *
 * Эта функция позволяет настроить количество слов в отзывах (excerpt) на 40 слов.
 *
 * @param int $length Длина отзыва в словах.
 * @return int Измененная длина отзыва.
 */
function codeweber_excerpt_length($length)
{
	return 40;
}
// add_filter( 'excerpt_length', 'codeweber_excerpt_length', 999 );



/**
 * Выводит атрибут "alt" для миниатюры записи.
 *
 * Функция получает альтернативный текст (alt) для миниатюры текущей записи и безопасно выводит его.
 * Полезно для улучшения SEO и доступности изображений.
 */
function codeweber_thumbnail_alt()
{
	$codeweber_thumbnail_alt = get_post_meta(get_post_thumbnail_id(), '_wp_attachment_image_alt', true);
	echo esc_attr($codeweber_thumbnail_alt);
}



/**
 * Отображает хлебные крошки с использованием Yoast SEO или Rank Math.
 *
 * Функция проверяет, установлен ли плагин Yoast SEO или Rank Math, и отображает соответствующие хлебные крошки.
 * Если установлен Yoast SEO, используются его стандартные функции для вывода навигации.
 * Если установлен Rank Math, применяется фильтр для настройки отображения хлебных крошек.
 */
function codeweber_breadcrumbs()
{

	if (function_exists('yoast_breadcrumb')) {
		// <!-- Yoast Breadcrumbs -->
		yoast_breadcrumb('<nav class="breadcrumb mt-3">', '</nav>');
	} elseif (function_exists('rank_math_the_breadcrumbs')) {
		// <!-- Rank Math Breadcrumbs -->
		add_filter(
			'rank_math/frontend/breadcrumb/args',
			function ($args) {
				$args = array(
					'delimiter'   => '&nbsp;&#47;&nbsp;',
					'wrap_before' => '<nav class="breadcrumb mt-3"><span>',
					'wrap_after'  => '</span></nav>',
					'before'      => '',
					'after'       => '',
				);
				return $args;
			}
		);

		rank_math_the_breadcrumbs();
	}
}

/**
 * Исправляет атрибуты переключения Bootstrap 5 в навигационном меню.
 * 
 * В Bootstrap 5 атрибут `data-toggle` был заменён на `data-bs-toggle`.
 * Эта функция удаляет устаревший атрибут `data-toggle` и заменяет его на `data-bs-toggle`.
 *
 * @param array $atts Атрибуты ссылки в меню.
 * @return array Изменённые атрибуты ссылки.
 */
function codeweber_bs5_toggle_fix($atts)
{
	if (array_key_exists('data-toggle', $atts)) {
		unset($atts['data-toggle']);
		$atts['data-bs-toggle'] = 'dropdown';
	}
	return $atts;
}
add_filter('nav_menu_link_attributes', 'codeweber_bs5_toggle_fix');


/**
 * Добавляет класс 'active' к активным ссылкам навигации.
 * 
 * Эта функция проверяет, является ли текущий пункт меню активным или содержит активный пункт в качестве потомка.
 * Если да, то добавляется класс 'active' к тегу <a> в меню.
 *
 * @param array    $atts Атрибуты ссылки.
 * @param WP_Post  $item Объект пункта меню.
 * @param stdClass $args Аргументы меню.
 * @return array Изменённые атрибуты ссылки.
 */

function codeweber_add_active_class_to_anchor($atts, $item, $args)
{
	if (! property_exists($args, 'walker') || ! is_a($args->walker, 'WP_Bootstrap_Navwalker')) {
		return $atts;
	}
	if ($item->current || $item->current_item_ancestor) {
		$atts['class'] = isset($atts['class']) ? $atts['class'] . ' active' : 'active';
	}
	return $atts;
}
add_filter('nav_menu_link_attributes', 'codeweber_add_active_class_to_anchor', 10, 3);

// <!-- Remove 'active' class from nav item <li> -->
function codeweber_remove_active_class_from_li($classes, $item, $args)
{
	if (property_exists($args, 'walker') && is_a($args->walker, 'WP_Bootstrap_Navwalker')) {
		return array_diff($classes, array('active'));
	}
	return $classes;
}
add_filter('nav_menu_css_class', 'codeweber_remove_active_class_from_li', 10, 3);



/**
 * Получает пользовательские логотипы.
 * 
 * Функция возвращает логотип в светлом, темном варианте или оба сразу.
 * Если пользовательские логотипы не заданы, используются стандартные изображения.
 *
 * @param string $type Тип логотипа: 'light' (светлый), 'dark' (тёмный) или 'both' (оба).
 * @return string HTML-код с логотипом (или логотипами).
 */

function get_custom_logo_type($type = 'both')
{
	$default_logos = array(
		'light' => get_template_directory_uri() . '/dist/img/logo-light.png',
		'dark'  => get_template_directory_uri() . '/dist/img/logo-dark.png',
	);

	$custom_logos = array(
		'light' => has_custom_logo() ? wp_get_attachment_image_src(get_theme_mod('custom_logo'), 'full')[0] : $default_logos['light'],
		'dark'  => ($dark_logo_id = get_theme_mod('custom_dark_logo')) ? wp_get_attachment_image_src($dark_logo_id, 'full')[0] : $default_logos['dark'],
	);

	$light_logo_html = sprintf(
		'<img class="logo-dark" src="%s" alt="">',
		esc_url($custom_logos['light'])
	);

	$dark_logo_html = sprintf(
		'<img class="logo-light" src="%s" alt="">',
		esc_url($custom_logos['dark'])
	);

	// Возвращаем в зависимости от типа
	if ($type === 'light') {
		return $light_logo_html;
	} elseif ($type === 'dark') {
		return $dark_logo_html;
	} elseif ($type === 'both') {
		return $light_logo_html . "\n" . $dark_logo_html;
	}

	return '';
}

/**
 * Форматирует номер телефона, оставляя только цифры.
 * - Если цифр больше трёх, добавляет в начале `+`.
 * - Если первая цифра `8` и номер длиннее трёх цифр, заменяет `8` на `7`.
 * - Если цифр три или меньше, оставляет их без изменений.
 *
 * @param string $text Входной текст, содержащий номер телефона.
 * @return string Отформатированный номер.
 */
function cleanNumber($text)
{
	// Удаляем все символы, кроме цифр
	$digits = preg_replace('/\D/', '', $text);

	// Если цифр больше трёх, обрабатываем номер
	if (strlen($digits) > 3) {
		// Если номер начинается с 8, заменяем на 7
		if ($digits[0] === '8') {
			$digits = '7' . substr($digits, 1);
		}
		return '+' . $digits;
	}

	return $digits;
}

/**
 * Функция для генерации социальных ссылок в зависимости от типа верстки и размера иконок.
 *
 * @param string $type Тип верстки. Может быть:
 *                    - 'type1' - цветные кнопки с белыми круглыми иконками.
 *                    - 'type2' - иконки без кнопок, в более сдержанном стиле.
 *                    - 'type3' - иконки без кнопок (аналогично 'type2').
 *                    - 'type4' - иконки без кнопок белого цвета.
 *                    - 'type5' - черные кнопки с круглыми белыми иконками.
 * @param string $size Размер иконок и кнопок. Допустимые значения:
 *                    - 'lg' (fs-40, btn-lg)
 *                    - 'md' (fs-35, btn-md)
 *                    - 'sm' (fs-30, btn-sm)
 * @return string Возвращает HTML верстку социальных ссылок.
 */
function social_links($class, $type, $size = 'md')
{
	$socials = get_option('socials_urls');
	if (empty($socials)) {
		return '';
	}

	// Фиксированные размеры
	$size_classes = [
		'lg' => ['fs-60', 'btn-lg'],
		'md' => ['fs-45', 'btn-md'],
		'sm' => ['', 'btn-sm'],
	];

	// Определение классов по переданному размеру
	$size_class = isset($size_classes[$size]) ? $size_classes[$size][0] : 'fs-35';
	$btn_size_class = isset($size_classes[$size]) ? $size_classes[$size][1] : 'btn-md';


	$nav_class = 'nav social';
	if ($type === 'type2') {
		$nav_class .= ' social-muted';
	}elseif($type === 'type4'){
		$nav_class .= ' social-white';
	}

	if(isset($class) && $class !== NULL){
      $nav_class .= ' '. $class;
	}

	$output = '<nav class="' . esc_attr($nav_class) . '">';
	foreach ($socials as $social => $url) {
		if (!empty($url)) {
			if(esc_attr($social) === 'telegram'){
				$social = 'telegram-alt';
			}elseif(esc_attr($social) === 'rutube'){
				$social = 'rutube-1';
			} elseif (esc_attr($social) === 'github') {
				$social = 'github-alt';
			} elseif (esc_attr($social) === 'ok') {
				$social = 'ok-1';
			} elseif (esc_attr($social) === 'vkmusic') {
				$social = 'vk-music';
			} elseif (esc_attr($social) === 'tik-tok') {
				$social = 'tiktok';
			} elseif (esc_attr($social) === 'googledrive') {
				$social = 'google-drive';
			} elseif (esc_attr($social) === 'googleplay') {
				$social = 'google-play';
			} elseif (esc_attr($social) === 'odnoklassniki') {
				$social = 'square-odnoklassniki';
			}


			if($type === 'type1'){
				$icon_class = 'uil uil-' . esc_attr($social);
			}else{
				$icon_class = 'uil uil-' . esc_attr($social) . ' ' . esc_attr($size_class);
			}
			if ($type === 'type1' ) {
				$output .= '<a href="' . esc_url($url) . '" class="btn btn-circle ' . esc_attr($btn_size_class) . ' btn-' . esc_attr($social) . '" target="_blank"><i class="' . $icon_class . '"></i></a>';
			} elseif ($type === 'type5') {
				$output .= '<a href="' . esc_url($url) . '" class="btn btn-circle ' . esc_attr($btn_size_class) . ' btn-dark" target="_blank"><i class="' . $icon_class . '"></i></a>';
			} elseif ($type === 'type2' || $type === 'type3' || $type = 'type4') {
				$output .= '<a href="' . esc_url($url) . '" target="_blank"><i class="' . $icon_class . '"></i></a>';
			} else {
				$output .= '<a href="' . esc_url($url) . '" target="_blank"><i class="' . $icon_class . '"></i></a>';
			}
		}
	}
	$output .= '</nav>';
	return $output;
}

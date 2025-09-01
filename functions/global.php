<?php

/**
 * Custom global functions.
 */

/**
 *  Bootstrap Integration
 */
require 'bootstrap/bootstrap_pagination.php';
require 'bootstrap/bootstrap_post-nav.php';
require 'bootstrap/bootstrap_share-page.php';
require 'bootstrap/bootstrap_nav-menu.php';

/**
 *  Shortcodes
 */
require 'shortcodes.php';

/**
 *  SEO Integration
 */
require 'integrations/yoast_rankmath.php';

/**
 *  Redux Integration
 */
require 'integrations/redux_framework/redux_framework.php';

/**
 *  Personal Data Integration - Registration Form WP
 */
//require 'personal-data.php';



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
 * Форматирует номер телефона, оставляя только цифры.
 * - Если цифр больше трёх, добавляет в начале `+`.
 * - Если первая цифра `8` и номер длиннее трёх цифр, заменяет `8` на `7`.
 * - Если цифр три или меньше, оставляет их без изменений.
 *
 * @param string $text Входной текст, содержащий номер телефона.
 * @return string Отформатированный номер.
 */
function cleanNumber($digits)
{
	// Удаляем все символы, кроме цифр
	$digits = preg_replace('/\D/', '', $digits);

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
 * Выводит список ссылок на социальные сети в разных стилях.
 *
 * Функция берет ссылки из настроек WordPress (`get_option('socials_urls')`)
 * и отображает их в виде иконок, кнопок или комбинированных блоков.
 *
 * Доступные типы отображения:
 * - `type1`: круглые кнопки с фоном, каждая соцсеть — свой стиль
 * - `type2`: иконки в muted-стиле (серые)
 * - `type3`: обычные цветные иконки без кнопок
 * - `type4`: белые иконки
 * - `type5`: тёмные круглые кнопки
 * - `type6`: кнопки с иконками и названиями соцсетей (широкие)
 * - `type7`: кнопки с кастомным фоном соцсети (например, `btn-telegram`)
 *
 * Размеры:
 * - `lg`: большие кнопки
 * - `md`: средние (по умолчанию)
 * - `sm`: маленькие
 *
 * @param string $class Дополнительные CSS-классы для обёртки `<nav>`.
 * @param string $type Тип отображения (например, `type1`, `type6`, и т.д.).
 * @param string $size Размер иконок или кнопок (`lg`, `md`, `sm`). По умолчанию `'md'`.
 *
 * @return string HTML-код со ссылками на соцсети.
 */
function social_links($class, $type, $size = 'md')
{
	$socials = get_option('socials_urls');
	if (empty($socials)) {
		return '';
	}

	$size_classes = [
		'lg' => ['fs-60', 'btn-lg'],
		'md' => ['fs-45', 'btn-md'],
		'sm' => ['', 'btn-sm'],
	];

	$size_class = isset($size_classes[$size]) ? $size_classes[$size][0] : 'fs-35';
	$btn_size_class = isset($size_classes[$size]) ? $size_classes[$size][1] : 'btn-md';

	$nav_class = 'nav social gap-1';
	if ($type === 'type2') {
		$nav_class .= ' social-muted';
	} elseif ($type === 'type4') {
		$nav_class .= ' social-white';
	} elseif ($type === 'type7') {
		$nav_class = '';
	}

	if (isset($class) && $class !== NULL) {
		$nav_class .= ' ' . $class;
	}

	$output = '<nav class="' . esc_attr($nav_class) . '">';
	foreach ($socials as $social => $url) {
		if (!empty($url)) {
			$original_social = $social;

			switch ($social) {
				case 'telegram':
					$social = 'telegram-alt';
					break;
				case 'rutube':
					$social = 'rutube-1';
					break;
				case 'github':
					$social = 'github-alt';
					break;
				case 'ok':
					$social = 'ok-1';
					break;
				case 'vkmusic':
					$social = 'vk-music';
					break;
				case 'tik-tok':
					$social = 'tiktok';
					break;
				case 'googledrive':
					$social = 'google-drive';
					break;
				case 'googleplay':
					$social = 'google-play';
					break;
				case 'odnoklassniki':
					$social = 'square-odnoklassniki';
					break;
			}

			$icon_class = 'uil uil-' . esc_attr($social);
			$label = $original_social; // Можно заменить на перевод, если нужно

			if (stripos($label, 'vk') === 0) {
				$btnlabel = strtoupper(substr($label, 0, 2)) . substr($label, 2);
			} else {
				$btnlabel = ucfirst($label);
			}

			if ($type === 'type1') {
				$output .= '<a href="' . esc_url($url) . '" class="btn btn-circle ' . esc_attr($btn_size_class) . ' btn-' . esc_attr($social) . '" target="_blank"><i class="' . $icon_class . '"></i></a>';
			} elseif ($type === 'type5') {
				$output .= '<a href="' . esc_url($url) . '" class="btn btn-circle ' . esc_attr($btn_size_class) . ' btn-dark" target="_blank"><i class="' . $icon_class . '"></i></a>';
			} elseif ($type === 'type2' || $type === 'type3' || $type === 'type4') {
				$output .= '<a href="' . esc_url($url) . '" target="_blank"><i class="' . $icon_class . ' ' . esc_attr($size_class) . '"></i></a>';
			} elseif ($type === 'type6') {
				$output .= '<a role="button" href="' . esc_url($url) . '" target="_blank" title="' . esc_attr($label) . '" class="btn btn-icon btn-sm border btn-icon-start btn-white justify-content-between w-100 mb-2 me-2 fs-16"><i class="fs-20 ' . $icon_class . '"></i>' . $btnlabel . '</a>';
			} elseif ($type === 'type7') {
				$output .= '<a role="button" href="' . esc_url($url) . '" target="_blank" title="' . esc_attr($label) . '" class="btn btn-icon btn-sm btn-icon-start btn-' . $label . ' justify-content-between w-100 mb-2 me-2"><i class="fs-20 ' . $icon_class . '"></i>' . $btnlabel . '</a>';
			} else {
				$output .= '<a href="' . esc_url($url) . '" target="_blank"><i class="' . $icon_class . '"></i></a>';
			}
		}
	}
	$output .= '</nav>';
	return $output;
}







/**
 * Удобная обёртка для вывода отформатированных данных с помощью print_r.
 *
 * Используется для отладки, позволяет красиво вывести массивы и объекты.
 *
 * @param mixed $data Данные для вывода (массив, объект, строка и т.д.).
 * @param bool $return Если true — функция вернёт строку, вместо вывода её на экран.
 * @return string|null Возвращает отформатированную строку, если $return = true, иначе null.
 */
function printr($data, $return = false)
{
	$output = '<pre>' . print_r($data, true) . '</pre>';
	if ($return) {
		return $output;
	} else {
		echo $output;
	}
}




/**
 * Получает универсальный заголовок текущей страницы WordPress.
 *
 * Эта функция автоматически определяет тип текущей страницы и возвращает
 * соответствующий заголовок:
 * - Для одиночных записей и страниц — заголовок записи.
 * - Для архивов категорий, тегов, авторов, дат, таксономий и других архивов — заголовок архива.
 * - Для главной страницы и страницы блога — название сайта.
 * - Для страницы поиска — строка поиска.
 * - Для 404 страницы — сообщение об ошибке.
 * - Для архива магазина WooCommerce — заголовок, заданный WooCommerce.
 *
 * @return string Заголовок текущей страницы.
 */
function universal_title()
{
	// Получаем текущую страницу/запись и тип
	if (is_singular()) {
		// Для одиночных записей и страниц
		$post_id = get_the_ID();
		$post_type = get_post_type($post_id);

		// Проверяем, какой тип записи и выводим соответствующий заголовок
		if ('post' === $post_type) {
			$title = get_the_title($post_id);
		} elseif ('page' === $post_type) {
			$title = get_the_title($post_id);
		} elseif ('product' === $post_type) {
			$title = get_the_title($post_id);
		} else {
			$title = get_the_title($post_id);
		}
	} elseif (is_archive()) {
		// Для архивов
		if (is_category()) {
			$title = single_cat_title('', false);
		} elseif (is_tag()) {
			$title = single_tag_title('', false);
		} elseif (is_author()) {
			$title = get_the_author_meta('display_name');
		} elseif (is_date()) {
			$title = get_the_date();
		} elseif (is_tax()) {
			$title = single_term_title('', false);
		} elseif (is_shop() && class_exists('WooCommerce')) {
			// Для страницы архива магазина WooCommerce
			$title = woocommerce_page_title(false); // Используем функцию WooCommerce для вывода правильного заголовка
		} else {
			$title = get_the_archive_title();
		}

		// Убираем тег <span>, если он есть, для архивных страниц
		$title = strip_tags($title);
	} elseif (is_home()) {
		$title = get_bloginfo('name');
	} elseif (is_front_page()) {
		$title = get_bloginfo('name');
	} elseif (is_search()) {
		$title = sprintf(__('Search Results for: %s', 'codeweber'), get_search_query());
	} elseif (is_404()) {
		$title = __('Page Not Found', 'codeweber');
	} else {
		$title = get_bloginfo('name');
	}

	return esc_html($title);
}
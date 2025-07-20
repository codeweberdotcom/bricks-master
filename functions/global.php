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
 * Получает пользовательские логотипы из Redux Framework.
 * 
 * Функция возвращает логотип в светлом, темном варианте или оба сразу.
 * Если пользовательские логотипы не заданы, используются стандартные изображения.
 *
 * @param string $type Тип логотипа: 'light' (светлый), 'dark' (тёмный) или 'both' (оба).
 * @return string HTML-код с логотипом (или логотипами).
 */
function get_custom_logo_type($type = 'both')
{
	// тут название твоей опции, укажи актуальное значение
	 global $opt_name;
    $options = get_option($opt_name);

    $default_logos = array(
        'light' => get_template_directory_uri() . '/dist/img/logo-light.png',
        'dark'  => get_template_directory_uri() . '/dist/img/logo-dark.png',
    );

    // определяем кастомные лого или дефолтные
   $light_logo  = !empty($options['opt-dark-logo']['url'])  ? $options['opt-dark-logo']['url']  : $default_logos['dark'];
	$dark_logo = !empty($options['opt-light-logo']['url']) ? $options['opt-light-logo']['url'] : $default_logos['light'];

    // HTML код логотипов
    $dark_logo_html = sprintf(
        '<img class="logo-dark" src="%s" alt="">',
        esc_url($dark_logo)
    );

    $light_logo_html = sprintf(
        '<img class="logo-light" src="%s" alt="">',
        esc_url($light_logo)
    );

    // Возвращаем в зависимости от типа
    if ($type === 'light') {
        return $light_logo_html;
    } elseif ($type === 'dark') {
        return $dark_logo_html;
    } elseif ($type === 'both') {
        return $dark_logo_html . "\n" . $light_logo_html;
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
 * Подключает файл шаблона pageheader из каталога /templates/pageheader/ темы.
 *
 * Работает аналогично get_header(), но подключает:
 * - templates/pageheader/pageheader-{name}.php
 * - или templates/pageheader/pageheader.php
 *
 * @param string|null $name Имя подшаблона (опционально).
 */
function get_pageheader($name = null)
{
	do_action('get_pageheader', $name);

	$base_dir = get_theme_file_path('templates/pageheader/');

	$templates = [];

	if (!empty($name)) {
		$templates[] = $base_dir . "pageheader-{$name}.php";
	}

	$templates[] = $base_dir . 'pageheader.php';

	foreach ($templates as $template) {
		if (file_exists($template)) {
			require $template;
			return;
		}
	}
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



/**
 * Изменяет заголовок архивной страницы для произвольных типов записей.
 * Заголовок берется из настроек Redux по ключу 'cpt-custom-title{PostType}'.
 *
 * Пример ключа: 'cpt-custom-titleFaq' для CPT с именем 'faq'.
 * Удаляет префикс "Архивы:" или "Archives:" из стандартного заголовка.
 *
 * @param string $title Стандартный заголовок архива.
 * @return string Новый заголовок архива.
 */
add_filter('get_the_archive_title', function ($title) {
	if (is_post_type_archive() && !is_admin()) {
		$post_type = get_post_type() ?: get_query_var('post_type');

		if ($post_type) {
			global $opt_name;

			$custom_title_id = 'cpt-custom-title' . ucwords($post_type);
			$custom_title = Redux::get_option($opt_name, $custom_title_id);

			if (!empty($custom_title)) {
				return $custom_title;
			}
		}

		$title = preg_replace('/^(Архивы|Archives):\s*/u', '', $title);
	}

	return $title;
});




/**
 * Возвращает подзаголовок для архивных страниц в зависимости от типа записи.
 * Подзаголовок берется из настроек Redux и выводится в заданной HTML-структуре.
 *
 * @global string $opt_name Имя настроек Redux.
 * @param string $html_structure Строка с HTML-разметкой, в которую будет вставлен подзаголовок.
 * 
 * @return string HTML-структура с подзаголовком.
 */
function the_subtitle($html_structure = '<p class="lead">%s</p>')
{
	// Проверяем, что это архивная страница и не админка
	if (is_archive() && !is_admin()) {
		// Получаем тип записи для текущего архива
		$post_type = get_post_type() ?: get_query_var('post_type');

		// Если тип записи определён
		if ($post_type) {
			global $opt_name;

			// Формируем ID для поля custom subtitle в зависимости от типа записи
			$custom_subtitle_id = 'cpt-custom-sub-title' . ucwords($post_type);

			// Получаем подзаголовок из настроек Redux
			$custom_subtitle = Redux::get_option($opt_name, $custom_subtitle_id);

			// Если подзаголовок найден, возвращаем его в указанной HTML-структуре
			if (!empty($custom_subtitle)) {
				return sprintf($html_structure, esc_html($custom_subtitle));
			}
		}
	}

	// Если подзаголовок не найден, возвращаем пустую строку в HTML-структуре
	return '';
}
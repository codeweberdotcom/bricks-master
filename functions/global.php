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
 * Очищает номер телефона, оставляя только цифры.
 * Удаляет все символы, кроме цифр.
 *
 * @param string $digits Входной текст, содержащий номер телефона.
 * @return string Очищенный номер, содержащий только цифры.
 */
function cleanNumber($digits)
{
	// Удаляем все символы, кроме цифр
	return preg_replace('/\D/', '', $digits);
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
 * Получает универсальный заголовок текущей страницы WordPress с возможностью форматирования.
 *
 * Эта функция автоматически определяет тип текущей страницы и возвращает
 * соответствующий заголовок в указанном формате:
 * - Для одиночных записей и страниц — заголовок записи.
 * - Для архивов категорий, тегов, авторов, дат, таксономий и других архивов — заголовок архива.
 * - Для главной страницы — название сайта.
 * - Для страницы блога — заголовок страницы блога.
 * - Для страницы поиска — строка поиска.
 * - Для 404 страницы — сообщение об ошибке.
 * - Для архива магазина WooCommerce — заголовок, заданный WooCommerce.
 *
 * @param string|false $tag HTML-тег для обертки заголовка (false - без обертки)
 * @param string|false $theme Класс для тега или 'theme' для получения из Redux
 * @return string Заголовок текущей страницы в указанном формате
 */
function universal_title($tag = false, $theme = false)
{
	// Получаем текст заголовка
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
		} elseif (is_post_type_archive()) {
			$title = post_type_archive_title('', false);
			// Применяем фильтр для кастомного заголовка CPT
			$post_type = get_query_var('post_type');
			$title = apply_filters('post_type_archive_title', $title, $post_type);
		} elseif (function_exists('is_shop') && is_shop() && class_exists('WooCommerce')) {
			// Для страницы архива магазина WooCommerce
			$title = function_exists('woocommerce_page_title') ? woocommerce_page_title(false) : __('Shop', 'codeweber');
		} else {
			$title = get_the_archive_title();
			// Убираем префикс "Архив: " если он есть
			$title = preg_replace('/^.*:\s*/', '', $title);
		}

		// Убираем тег <span>, если он есть, для архивных страниц
		$title = strip_tags($title);
	} elseif (is_home()) {
		// Для страницы блога - получаем заголовок страницы блога
		$blog_page_id = get_option('page_for_posts');
		if ($blog_page_id) {
			$title = get_the_title($blog_page_id);
		} else {
			$title = __('Blog', 'codeweber');
		}
	} elseif (is_front_page()) {
		$title = get_bloginfo('name');
	} elseif (is_search()) {
		$title = sprintf(__('Search Results for: %s', 'codeweber'), get_search_query());
	} elseif (is_404()) {
		$title = __('Page Not Found', 'codeweber');
	} else {
		$title = get_bloginfo('name');
	}

	$title = esc_html($title);

	// Если тег не указан, возвращаем просто текст
	if ($tag === false) {
		return $title;
	}

	// Определяем класс для тега
	if ($theme === 'theme') {
		// Получаем класс из Redux
		global $opt_name;
		$title_class = Redux::get_option($opt_name, 'opt-select-title-size');
	} elseif ($theme !== false) {
		// Используем переданный класс
		$title_class = $theme;
	} else {
		// Класс не указан
		$title_class = '';
	}

	// Формируем HTML с тегом и классом
	if (!empty($title_class)) {
		return '<' . $tag . ' class="' . esc_attr($title_class) . '">' . $title . '</' . $tag . '>';
	} else {
		return '<' . $tag . '>' . $title . '</' . $tag . '>';
	}
}


/**
 * Шорткод для вывода универсального заголовка страницы
 * 
 * Использует функцию universal_title() для получения заголовка с автоматическим определением типа контента
 * 
 * @param array $atts Атрибуты шорткода:
 *     - 'tag'    string  HTML-тег для обертки (по умолчанию: 'h1')
 *     - 'theme'  string  Класс для тега или 'theme' для получения из Redux (по умолчанию: 'theme')
 * 
 * @return string Заголовок текущей страницы в указанном формате
 * 
 * @example [universal_title] - заголовок в теге h1 с классом из Redux
 * @example [universal_title tag="h2" theme="custom-class"] - заголовок в h2 с кастомным классом
 * @example [universal_title tag="div"] - заголовок в div с классом из Redux
 */
function universal_title_shortcode($atts)
{
	$atts = shortcode_atts(array(
		'tag' => 'h1',
		'theme' => 'theme'
	), $atts);

	return universal_title($atts['tag'], $atts['theme']);
}
add_shortcode('universal_title', 'universal_title_shortcode');

/**
 * Кастомная пагинация для постов WordPress
 * 
 * Создает красивую пагинацию в стиле Bootstrap с иконками и гибкими настройками
 * 
 * @package CodeWeber
 * @version 1.0.0
 * 
 * @param array $args {
 *     Опциональные аргументы для настройки пагинации
 * 
 *     @type int    $mid_size       Количество страниц отображаемых по бокам от текущей страницы. По умолчанию: 2.
 *     @type string $prev_text      Текст/HTML для кнопки "Предыдущая". По умолчанию: '<span aria-hidden="true"><i class="uil uil-arrow-left"></i></span>'.
 *     @type string $next_text      Текст/HTML для кнопки "Следующая". По умолчанию: '<span aria-hidden="true"><i class="uil uil-arrow-right"></i></span>'.
 *     @type string $prev_class     CSS класс для кнопки "Предыдущая". По умолчанию: 'page-item'.
 *     @type string $next_class     CSS класс для кнопки "Следующая". По умолчанию: 'page-item'.
 *     @type string $page_class     CSS класс для элементов страниц. По умолчанию: 'page-item'.
 *     @type string $active_class   CSS класс для активной страницы. По умолчанию: 'active'.
 *     @type string $disabled_class CSS класс для неактивных элементов. По умолчанию: 'disabled'.
 *     @type string $nav_class      CSS класс для nav контейнера. По умолчанию: 'd-flex'.
 *     @type string $ul_class       CSS класс для ul элемента. По умолчанию: 'pagination'.
 *     @type string $link_class     CSS класс для ссылок. По умолчанию: 'page-link'.
 *     @type bool   $show_dots      Показывать многоточия для скрытых страниц. По умолчанию: false.
 *     @type string $dots_text      Текст для многоточий. По умолчанию: '...'.
 *     @type string $aria_label     ARIA-label для навигации. По умолчанию: 'pagination'.
 * }
 * 
 * @return void Выводит HTML пагинации
 * 
 * @example 
 * // Базовое использование
 * codeweber_posts_pagination();
 * 
 * // С кастомными настройками
 * codeweber_posts_pagination(array(
 *     'mid_size'  => 3,
 *     'show_dots' => true,
 *     'prev_text' => '<i class="fas fa-arrow-left"></i>',
 *     'next_text' => '<i class="fas fa-arrow-right"></i>'
 * ));
 * 
 * @since 1.0.0
 */
if (!function_exists('codeweber_posts_pagination')) {
    function codeweber_posts_pagination($args = array()) {
        global $wp_query, $wp_rewrite;
        
        // Default arguments
        $defaults = array(
            'mid_size'        => 2,
            'prev_text'       => '<span aria-hidden="true"><i class="uil uil-arrow-left"></i></span>',
            'next_text'       => '<span aria-hidden="true"><i class="uil uil-arrow-right"></i></span>',
            'prev_class'      => 'page-item',
            'next_class'      => 'page-item',
            'page_class'      => 'page-item',
            'active_class'    => 'active',
            'disabled_class'  => 'disabled',
            'nav_class'       => 'd-flex',
            'ul_class'        => 'pagination',
            'link_class'      => 'page-link',
            'show_dots'       => false,
            'dots_text'       => '...',
            'aria_label'      => 'pagination'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Получаем стиль кнопок из настроек темы
        $button_style = getThemeButton();
        
        $total_pages = $wp_query->max_num_pages;
        $current_page = max(1, get_query_var('paged'));
        
        if ($total_pages <= 1) return;
        
        $pagination = '<nav class="' . esc_attr($args['nav_class']) . '" aria-label="' . esc_attr($args['aria_label']) . '">';
        $pagination .= '<ul class="' . esc_attr($args['ul_class']) . '">';
        
        // Previous button
        $prev_class = $current_page > 1 ? $args['prev_class'] : $args['prev_class'] . ' ' . $args['disabled_class'];
        $pagination .= '<li class="' . esc_attr($prev_class) . '">';
        
        if ($current_page > 1) {
            $prev_link = get_previous_posts_page_link();
            $pagination .= '<a class="' . esc_attr($args['link_class'] . $button_style) . '" href="' . esc_url($prev_link) . '" aria-label="' . esc_attr__('Previous', 'codeweber') . '">';
            $pagination .= $args['prev_text'];
            $pagination .= '</a>';
        } else {
            $pagination .= '<span class="' . esc_attr($args['link_class'] . $button_style) . '" aria-label="' . esc_attr__('Previous', 'codeweber') . '">';
            $pagination .= $args['prev_text'];
            $pagination .= '</span>';
        }
        
        $pagination .= '</li>';
        
        // Page numbers with dots
        $start = max(1, $current_page - $args['mid_size']);
        $end = min($total_pages, $current_page + $args['mid_size']);
        
        // Show dots at the beginning if needed
        if ($args['show_dots'] && $start > 1) {
            $pagination .= '<li class="' . esc_attr($args['page_class'] . ' ' . $args['disabled_class']) . '">';
            $pagination .= '<span class="' . esc_attr($args['link_class'] . $button_style) . '">' . esc_html($args['dots_text']) . '</span>';
            $pagination .= '</li>';
        }
        
        // Page numbers
        for ($i = $start; $i <= $end; $i++) {
            $page_class = $args['page_class'];
            if ($i == $current_page) {
                $page_class .= ' ' . $args['active_class'];
            }
            
            $pagination .= '<li class="' . esc_attr($page_class) . '">';
            
            if ($i == $current_page) {
                $pagination .= '<span class="' . esc_attr($args['link_class'] . $button_style) . '">' . $i . '</span>';
            } else {
                $pagination .= '<a class="' . esc_attr($args['link_class'] . $button_style) . '" href="' . esc_url(get_pagenum_link($i)) . '">' . $i . '</a>';
            }
            
            $pagination .= '</li>';
        }
        
        // Show dots at the end if needed
        if ($args['show_dots'] && $end < $total_pages) {
            $pagination .= '<li class="' . esc_attr($args['page_class'] . ' ' . $args['disabled_class']) . '">';
            $pagination .= '<span class="' . esc_attr($args['link_class'] . $button_style) . '">' . esc_html($args['dots_text']) . '</span>';
            $pagination .= '</li>';
        }
        
        // Next button
        $next_class = $current_page < $total_pages ? $args['next_class'] : $args['next_class'] . ' ' . $args['disabled_class'];
        $pagination .= '<li class="' . esc_attr($next_class) . '">';
        
        if ($current_page < $total_pages) {
            $next_link = get_next_posts_page_link();
            $pagination .= '<a class="' . esc_attr($args['link_class'] . $button_style) . '" href="' . esc_url($next_link) . '" aria-label="' . esc_attr__('Next', 'codeweber') . '">';
            $pagination .= $args['next_text'];
            $pagination .= '</a>';
        } else {
            $pagination .= '<span class="' . esc_attr($args['link_class'] . $button_style) . '" aria-label="' . esc_attr__('Next', 'codeweber') . '">';
            $pagination .= $args['next_text'];
            $pagination .= '</span>';
        }
        
        $pagination .= '</li>';
        
        $pagination .= '</ul></nav>';
        
        echo apply_filters('codeweber_posts_pagination', $pagination, $args);
    }
}


/**
 * Универсальная функция для надежного определения post_type
 * 
 * @return string Post type
 */
function universal_get_post_type()
{
	// Специальная проверка для страницы блога (главной страницы постов)
	if (is_home() && !is_front_page()) {
		return 'post';
	}

	if (is_singular()) {
		return get_post_type();
	} elseif (is_post_type_archive()) {
		return get_queried_object()->name ?? '';
	} elseif (is_tax() || is_category() || is_tag()) {
		$taxonomy = get_queried_object()->taxonomy ?? '';
		$taxonomy_obj = get_taxonomy($taxonomy);
		$post_type = $taxonomy_obj->object_type[0] ?? 'post';

		// Если массив, берем первый элемент
		if (is_array($post_type)) {
			$post_type = $post_type[0];
		}
		return $post_type;
	} elseif (is_archive() || is_home() || is_author() || is_date()) {
		global $wp_query;
		$post_type = $wp_query->get('post_type') ?? 'post';

		// Если массив, берем первый элемент
		if (is_array($post_type)) {
			$post_type = $post_type[0];
		}
		return $post_type;
	} else {
		return 'post'; // Значение по умолчанию
	}
}
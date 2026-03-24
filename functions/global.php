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
require 'bootstrap/bootstrap-single-parts.php';
require 'bootstrap/bootstrap_nav-menu.php';
require 'bootstrap/bootstrap_floating-social-widget.php';

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
 *  Project Gallery (FilePond + SortableJS) — replaces Redux slides for CPT projects
 */
require 'integrations/project-gallery-metabox.php';

/**
 *  Social Links
 */
require_once get_template_directory() . '/functions/social-links.php';



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
function codeweber_clean_number($digits)
{
	// Удаляем все символы, кроме цифр
	return preg_replace('/\D/', '', $digits);
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
		// Получаем класс из Redux через Codeweber_Options
		$title_class = Codeweber_Options::get('opt-select-title-size', '');
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
 * Получение настройки хедера с учетом индивидуальных настроек страницы
 * Если для страницы выбран тип 'Base Settings' (4), возвращает индивидуальные настройки
 * Иначе возвращает глобальные настройки
 *
 * @param string $option_name Имя опции Redux
 * @param mixed $default Значение по умолчанию
 * @return mixed Значение настройки
 */
if (!function_exists('codeweber_get_header_option')) {
	function codeweber_get_header_option($option_name, $default = '') {
		// Проверяем, используется ли тип '4' для текущей страницы
		if (!empty($GLOBALS['codeweber_use_this_header_settings']) && $GLOBALS['codeweber_use_this_header_settings'] === true) {
			// Маппинг глобальных опций на индивидуальные
			$option_map = array(
				'header-rounded' => 'codeweber_this_header_rounded',
				'header-color-text' => 'codeweber_this_header_color_text',
				'header-background' => 'codeweber_this_header_background',
				'solid-color-header' => 'codeweber_this_solid_color_header',
				'soft-color-header' => 'codeweber_this_soft_color_header',
			);

			if (isset($option_map[$option_name])) {
				$global_var_name = $option_map[$option_name];
				// Проверяем, установлена ли глобальная переменная
				if (array_key_exists($global_var_name, $GLOBALS)) {
					$value = $GLOBALS[$global_var_name];
					// Используем значение, если оно не null
					// Пустая строка может быть валидным значением, поэтому проверяем только на null
					if ($value !== null) {
						return $value;
					}
				}
			}
		}

		// Возвращаем глобальную настройку через Codeweber_Options
		return Codeweber_Options::get($option_name, $default);
	}
}

/**
 * Кастомная пагинация для постов WordPress
 *
 * Создает красивую пагинацию в стиле Bootstrap с иконками и гибкими настройками
 *
 * @param array $args {
 *     Опциональные аргументы для настройки пагинации
 *
 *     @type int    $mid_size       Количество страниц отображаемых по бокам от текущей страницы. По умолчанию: 2.
 *     @type string $prev_text      Текст/HTML для кнопки "Предыдущая".
 *     @type string $next_text      Текст/HTML для кнопки "Следующая".
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
 */
if (!function_exists('codeweber_posts_pagination')) {
	function codeweber_posts_pagination($args = []) {
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
		$button_style = Codeweber_Options::style('button');

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

	// Страницы авторов и архивов по дате — используем тот же сайдбар, что и в архиве блога
	if (is_author() || is_date()) {
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

/**
 * Универсальная функция для вывода фактического или юридического адреса
 *
 * @param string $type Тип адреса: 'fact' (фактический) или 'juri' (юридический). По умолчанию 'fact'
 * @param string $separator Разделитель между частями адреса. По умолчанию ', '
 * @param string $fallback Текст по умолчанию, если адрес не заполнен.
 * @return string Отформатированный адрес
 */
function codeweber_get_address($type = 'fact', $separator = ', ', $fallback = 'Moonshine St. 14/05 Light City, London, United Kingdom')
{
	// Проверяем наличие Redux Framework
	if (!Codeweber_Options::is_ready()) {
		return $fallback;
	}

	// Определяем префикс для полей адреса
	$prefix = ($type === 'juri') ? 'juri' : 'fact';

	// Получаем данные адреса из Redux через Codeweber_Options
	$country = Codeweber_Options::get($prefix . '-country', '');
	$region = Codeweber_Options::get($prefix . '-region', '');
	$city = Codeweber_Options::get($prefix . '-city', '');
	$street = Codeweber_Options::get($prefix . '-street', '');
	$house = Codeweber_Options::get($prefix . '-house', '');
	$office = Codeweber_Options::get($prefix . '-office', '');
	$postal = Codeweber_Options::get($prefix . '-postal', '');

	// Формируем строку улицы с домом и офисом
	$street_line = trim(implode(' ', array_filter([$street, $house, $office])), ' ,');

	// Собираем части адреса в обратном порядке: индекс, страна, регион, город, улица
	$parts = [];
	if (!empty($postal)) $parts[] = $postal;
	if (!empty($country)) $parts[] = $country;
	if (!empty($region)) $parts[] = $region;
	if (!empty($city)) $parts[] = $city;
	if (!empty($street_line)) $parts[] = $street_line;

	// Если адрес не заполнен, возвращаем fallback
	if (empty($parts)) {
		return $fallback;
	}

	// Объединяем части адреса с указанным разделителем
	return implode($separator, $parts);
}

/**
 * Выводит колонку футера с проверкой виджета
 * Если виджет активен, выводит его содержимое, иначе выводит стандартное содержимое
 *
 * @param string $widget_id ID области виджета (footer-1, footer-2, footer-3, footer-4)
 * @param string $column_classes CSS классы для колонки
 * @param callable $default_content Функция для вывода стандартного содержимого
 */
function codeweber_footer_column($widget_id, $column_classes, $default_content) {
	if (is_active_sidebar($widget_id)) {
		// Если виджет активен, выводим его
		echo '<div class="' . esc_attr($column_classes) . '">';
		dynamic_sidebar($widget_id);
		echo '</div>';
		echo '<!-- /column -->';
	} else {
		// Если виджет не активен, выводим стандартное содержимое
		echo '<div class="' . esc_attr($column_classes) . '">';
		if (is_callable($default_content)) {
			call_user_func($default_content);
		}
		echo '</div>';
		echo '<!-- /column -->';
	}
}

/**
 * Page Loader — выводит прелоадер, если включён в настройках Redux.
 *
 * Типы: default (спиннер), logo-light, logo-dark, custom (SVG).
 */
function get_loader()
{
	if (!Codeweber_Options::is_ready()) {
		return;
	}

	if (!Codeweber_Options::get('page-loader', false)) {
		return;
	}

	$type         = Codeweber_Options::get('page-loader-type', 'default');
	$custom_class = trim(Codeweber_Options::get('page-loader-custom-class', ''));

	if ($custom_class) {
		$cls = 'page-loader ' . esc_attr($custom_class);
	} else {
		$bg  = Codeweber_Options::get('page-loader-bg', 'white');
		$cls = 'page-loader' . ($bg ? ' bg-' . esc_attr($bg) : '');
	}

	$logo_url = '';
	switch ($type) {
		case 'logo-light':
			$logo_url = Codeweber_Options::media_url('opt-light-logo');
			break;
		case 'logo-dark':
			$logo_url = Codeweber_Options::media_url('opt-dark-logo');
			break;
		case 'custom':
			$logo_url = Codeweber_Options::media_url('page-loader-custom-logo');
			break;
	}

	echo '<div class="' . $cls . '">';
	if ($logo_url) {
		echo '<img class="page-loader-logo" src="' . esc_url($logo_url) . '" alt="Loading...">';
	}
	echo '</div>';
}

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
	if (!function_exists('codeweber_social_links')) {
		return '<!-- Функция codeweber_social_links не найдена -->';
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
	return codeweber_social_links($atts['class'], $atts['type'], $atts['size'], $atts['button-color'], $atts['buttonstyle']);
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

/**
 * Демо-пункты меню для шорткода [menu_collapse] при demo=true.
 * Фильтр wp_nav_menu_objects подставляет эти пункты, все атрибуты шорткода применяются к выводу.
 *
 * @param array    $sorted_menu_items Элементы меню.
 * @param \stdClass $args             Аргументы wp_nav_menu.
 * @return array
 */
function codeweber_menu_collapse_demo_items( $sorted_menu_items, $args ) {
	if ( empty( $args->demo ) ) {
		return $sorted_menu_items;
	}
	// 4 уровня: Home → 1.1 → 1.1.1 → 1.1.1.1 (current); Services → 2.1 → 2.1.1; About, Contact без детей. Порядок: depth-first для Walker.
	// Свойства object, type, attr_title, target, xfn нужны для WP_Bootstrap_Navwalker (в т.ч. list_type=5).
	$demo_item_base = array(
		'object'               => 'custom',
		'type'                 => 'custom',
		'attr_title'           => '',
		'target'               => '',
		'xfn'                  => '',
		'current'              => false,
		'current_item_ancestor' => false,
	);
	$demo = array(
		(object) array_merge( $demo_item_base, array(
			'ID'               => 90001,
			'db_id'            => 90001,
			'menu_item_parent' => 0,
			'url'              => '#',
			'title'            => __( 'Home', 'codeweber' ),
			'classes'          => array( 'menu-item', 'menu-item-type-custom', 'menu-item-has-children' ),
		) ),
		(object) array_merge( $demo_item_base, array(
			'ID'               => 90002,
			'db_id'            => 90002,
			'menu_item_parent' => 0,
			'url'              => '#',
			'title'            => __( 'Services', 'codeweber' ),
			'classes'          => array( 'menu-item', 'menu-item-type-custom', 'menu-item-has-children' ),
		) ),
		(object) array_merge( $demo_item_base, array(
			'ID'               => 90003,
			'db_id'            => 90003,
			'menu_item_parent' => 0,
			'url'              => '#',
			'title'            => __( 'About', 'codeweber' ),
			'classes'          => array( 'menu-item', 'menu-item-type-custom' ),
		) ),
		(object) array_merge( $demo_item_base, array(
			'ID'               => 90004,
			'db_id'            => 90004,
			'menu_item_parent' => 0,
			'url'              => '#',
			'title'            => __( 'Contact', 'codeweber' ),
			'classes'          => array( 'menu-item', 'menu-item-type-custom' ),
		) ),
		(object) array_merge( $demo_item_base, array(
			'ID'               => 90005,
			'db_id'            => 90005,
			'menu_item_parent' => 90001,
			'url'              => '#',
			'title'            => __( 'Subitem 1.1', 'codeweber' ),
			'classes'          => array( 'menu-item', 'menu-item-type-custom', 'menu-item-has-children' ),
		) ),
		(object) array_merge( $demo_item_base, array(
			'ID'               => 90006,
			'db_id'            => 90006,
			'menu_item_parent' => 90001,
			'url'              => '#',
			'title'            => __( 'Subitem 1.2', 'codeweber' ),
			'classes'          => array( 'menu-item', 'menu-item-type-custom' ),
		) ),
		(object) array_merge( $demo_item_base, array(
			'ID'               => 90007,
			'db_id'            => 90007,
			'menu_item_parent' => 90002,
			'url'              => '#',
			'title'            => __( 'Subitem 2.1', 'codeweber' ),
			'classes'          => array( 'menu-item', 'menu-item-type-custom', 'menu-item-has-children' ),
		) ),
		(object) array_merge( $demo_item_base, array(
			'ID'               => 90008,
			'db_id'            => 90008,
			'menu_item_parent' => 90005,
			'url'              => '#',
			'title'            => __( 'Subitem 1.1.1', 'codeweber' ),
			'classes'          => array( 'menu-item', 'menu-item-type-custom', 'menu-item-has-children' ),
		) ),
		(object) array_merge( $demo_item_base, array(
			'ID'               => 90009,
			'db_id'            => 90009,
			'menu_item_parent' => 90005,
			'url'              => '#',
			'title'            => __( 'Subitem 1.1.2', 'codeweber' ),
			'classes'          => array( 'menu-item', 'menu-item-type-custom' ),
		) ),
		(object) array_merge( $demo_item_base, array(
			'ID'               => 90010,
			'db_id'            => 90010,
			'menu_item_parent' => 90007,
			'url'              => '#',
			'title'            => __( 'Subitem 2.1.1', 'codeweber' ),
			'classes'          => array( 'menu-item', 'menu-item-type-custom' ),
		) ),
		(object) array_merge( $demo_item_base, array(
			'ID'               => 90011,
			'db_id'            => 90011,
			'menu_item_parent' => 90008,
			'url'              => '#',
			'title'            => __( 'Subitem 1.1.1.1', 'codeweber' ),
			'classes'          => array( 'menu-item', 'menu-item-type-custom' ),
		) ),
		(object) array_merge( $demo_item_base, array(
			'ID'               => 90012,
			'db_id'            => 90012,
			'menu_item_parent' => 90008,
			'url'              => '#',
			'title'            => __( 'Subitem 1.1.1.2', 'codeweber' ),
			'classes'          => array( 'menu-item', 'menu-item-type-custom' ),
		) ),
	);
	return $demo;
}
add_filter( 'wp_nav_menu_objects', 'codeweber_menu_collapse_demo_items', 10, 2 );

/**
 * Шорткод [menu_collapse] — вертикальное меню с Bootstrap Collapse (accordion).
 *
 * Параметры:
 * - menu (обязательный при demo=false): ID или slug меню (например 4 или "main-menu")
 * - demo: true — вывести демо-меню (menu не нужен), все атрибуты применяются. По умолчанию false
 * - depth: глубина вложенности (0 = без ограничения). По умолчанию 0
 * - theme: цвет меню — default | dark | light. default = без класса, dark = navbar-dark, light = navbar-light (два варианта, как в горизонтальном меню)
 * - list_type: 1 | 2 | 3 | 4 | 5. 1–3 = collapse (стили), 4 = простой список, 5 = вертикальное меню с выпаданием вправо (dropend)
 * - container_class: дополнительные классы для контейнера <nav>
 * - top_level_class: дополнительные классы только для пунктов меню верхнего уровня (depth 0)
 * - top_level_class_start: класс только для первого пункта верхнего уровня (если задан, top_level_class для него не применяется)
 * - top_level_class_end: класс только для последнего пункта верхнего уровня (если задан, top_level_class для него не применяется)
 * - item_class: дополнительные классы для всех пунктов <li>
 * - link_class: дополнительные классы для ссылок <a>
 *
 * Примеры:
 * [menu_collapse menu="4" list_type="5"]
 * [menu_collapse menu="main-menu" list_type="5" theme="dark"]
 * [menu_collapse demo="true" list_type="5"]
 * [menu_collapse menu="4" list_type="5" depth="3"]
 */
add_shortcode( 'menu_collapse', function ( $atts ) {
	$atts = shortcode_atts( array(
		'menu'                 => '',
		'demo'                 => false,
		'depth'                => 0,
		'theme'                => 'default',
		'list_type'            => '1',
		'container_class'      => '',
		'top_level_class'      => '',
		'top_level_class_start' => '',
		'top_level_class_end'  => '',
		'item_class'           => '',
		'link_class'           => '',
	), $atts, 'menu_collapse' );

	$is_demo = filter_var( $atts['demo'], FILTER_VALIDATE_BOOLEAN );
	if ( ! $is_demo && empty( $atts['menu'] ) ) {
		return '<p>' . esc_html__( 'Shortcode [menu_collapse]: specify menu= (ID or slug) or demo="true".', 'codeweber' ) . '</p>';
	}

	// Цвет задаётся темой через .navbar-light/.navbar-dark .nav-link, text-dark/text-white не добавляем
	$theme_class = '';
	$theme_nav_class = ( 'dark' === $atts['theme'] ) ? 'navbar-dark' : ( ( 'light' === $atts['theme'] ) ? 'navbar-light' : '' );

	// Общий счётчик с блоком Menu (collapse), чтобы id не дублировались на странице
	global $codeweber_menu_collapse_instance;
	if ( ! isset( $codeweber_menu_collapse_instance ) ) {
		$codeweber_menu_collapse_instance = 0;
	}
	$codeweber_menu_collapse_instance++;
	$suffix = (string) $codeweber_menu_collapse_instance;

	$menu_id = $is_demo ? 999999 : $atts['menu'];
	$menu_id = is_numeric( $menu_id ) ? (int) $menu_id : $menu_id;
	$wrapper_id = 'menu-collapse-walker-' . ( $is_demo ? 'demo' : ( is_numeric( $menu_id ) ? $menu_id : preg_replace( '/[^a-z0-9_-]/i', '-', $menu_id ) ) ) . '-' . $suffix;

	$list_type_sanitized = preg_replace( '/[^a-z0-9_-]/i', '', (string) $atts['list_type'] );
	if ( '' === $list_type_sanitized ) {
		$list_type_sanitized = '1';
	}
	if ( '4' === $list_type_sanitized ) {
		$list_class_str = 'list-unstyled menu-list-type-4';
		$container_class = 'navbar-vertical' . ( $theme_nav_class !== '' ? ' ' . $theme_nav_class : '' );
		if ( ! empty( trim( (string) $atts['container_class'] ) ) ) {
			$container_class .= ' ' . trim( (string) $atts['container_class'] );
		}
	} elseif ( '5' === $list_type_sanitized ) {
		$list_class_str = 'navbar-nav flex-column';
		$container_class = 'navbar-vertical navbar-vertical-dropdown' . ( $theme_nav_class !== '' ? ' ' . $theme_nav_class : '' );
		if ( ! empty( trim( (string) $atts['container_class'] ) ) ) {
			$container_class .= ' ' . trim( (string) $atts['container_class'] );
		}
	} else {
		$list_classes   = array( 'navbar-nav', 'list-unstyled', 'menu-collapse-' . $list_type_sanitized );
		$list_class_str = implode( ' ', $list_classes );
		$container_class = 'navbar-vertical menu-collapse-nav' . ( $theme_nav_class !== '' ? ' ' . $theme_nav_class : '' );
		if ( ! empty( trim( (string) $atts['container_class'] ) ) ) {
			$container_class .= ' ' . trim( (string) $atts['container_class'] );
		}
	}

	// Количество пунктов верхнего уровня для Walker (первый/последний)
	$top_level_count = 0;
	if ( ! $is_demo ) {
		$menu_items = wp_get_nav_menu_items( $menu_id );
		if ( is_array( $menu_items ) ) {
			foreach ( $menu_items as $mi ) {
				if ( 0 === (int) $mi->menu_item_parent ) {
					$top_level_count++;
				}
			}
		}
	} else {
		// demo: пункты подставляются фильтром wp_nav_menu_objects — считаем после применения фильтра
		$menu_items = wp_get_nav_menu_items( 999999 );
		$demo_args  = (object) array( 'menu' => 999999, 'demo' => true );
		$menu_items = apply_filters( 'wp_nav_menu_objects', is_array( $menu_items ) ? $menu_items : [], $demo_args );
		if ( is_array( $menu_items ) ) {
			foreach ( $menu_items as $mi ) {
				if ( 0 === (int) $mi->menu_item_parent ) {
					$top_level_count++;
				}
			}
		}
	}

	$use_dropdown_walker = ( '5' === $list_type_sanitized );
	$nav_args = array(
		'menu'             => $menu_id,
		'depth'            => (int) $atts['depth'],
		'container'        => 'nav',
		'container_class'  => $container_class,
		'container_id'     => $wrapper_id,
		'menu_class'       => $list_class_str,
		'menu_id'          => '',
		'fallback_cb'      => $use_dropdown_walker ? 'WP_Bootstrap_Navwalker::fallback' : false,
		'echo'             => false,
		'items_wrap'       => '<ul id="%1$s" class="%2$s">%3$s</ul>',
		'item_spacing'     => 'discard',
		'walker'           => $use_dropdown_walker ? new CodeWeber_Vertical_Dropdown_Walker() : new CodeWeber_Menu_Collapse_Walker(),
		'wrapper_id'             => $wrapper_id,
		'instance_suffix'        => $suffix,
		'theme_class'            => $theme_class,
		'list_type'               => $list_type_sanitized,
		'top_level_class'        => trim( (string) $atts['top_level_class'] ),
		'top_level_class_start'  => trim( (string) $atts['top_level_class_start'] ),
		'top_level_class_end'   => trim( (string) $atts['top_level_class_end'] ),
		'top_level_count'       => $top_level_count,
		'item_class'             => trim( (string) $atts['item_class'] ),
		'link_class'             => trim( (string) $atts['link_class'] ),
		'demo'                   => $is_demo,
	);

	$output = wp_nav_menu( $nav_args );
	if ( ! $is_demo && empty( trim( strip_tags( $output ) ) ) ) {
		return '<p>' . esc_html__( 'Menu not found or empty.', 'codeweber' ) . '</p>';
	}
	return $output;
} );
<?php
/**
 * Универсальная функция вывода навигации из таксономии или CPT.
 * Разметка: Bootstrap Collapse (как CodeWeber_Menu_Collapse_Walker / [menu_collapse]).
 *
 * codeweber_nav( 'tax', 'category' );       — рубрики, все уровни, стандартная вёрстка
 * codeweber_nav( 'cpt', 'product', [] );   — записи типа product, стандартная вёрстка
 * codeweber_nav( 'tax', 'product_cat', [ 'depth' => 2, 'theme' => 'dark' ] );
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Дефолтные аргументы для codeweber_nav().
 * Если $args не передан или пустой — используется стандартная collapse-вёрстка со всеми уровнями.
 *
 * @return array<string, mixed>
 */
function codeweber_nav_default_args() {
	return array(
		'depth'                  => 0,
		'theme'                   => 'default',
		'list_type'               => '1',
		'container_class'         => '',
		'item_class'              => '',
		'link_class'              => '',
		'top_level_class'         => '',
		'top_level_class_start'   => '',
		'top_level_class_end'     => '',
		'hide_empty'              => false,
		'wrapper_id'              => '',
		'menu_class'              => '',
	);
}

/**
 * Строит дерево $by_parent из терминов таксономии.
 *
 * @param string $name Slug таксономии.
 * @param array  $args Аргументы (depth, hide_empty и т.д.).
 * @return array<int, array<int, array{id: string, text: string, url: string, wp_id: int, current: bool}>> Дерево по parent_id.
 */
function codeweber_nav_build_tree_tax( $name, array $args ) {
	if ( ! taxonomy_exists( $name ) ) {
		return [];
	}
	$hide_empty = ! empty( $args['hide_empty'] );
	$terms      = get_terms( array(
		'taxonomy'   => $name,
		'hide_empty' => $hide_empty,
		'orderby'    => 'name',
		'order'      => 'ASC',
	) );
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return [];
	}
	$queried_term_id = 0;
	$obj             = get_queried_object();
	if ( $obj && isset( $obj->term_id ) ) {
		$queried_term_id = (int) $obj->term_id;
	}
	$by_parent = [];
	foreach ( $terms as $term ) {
		$parent_id = (int) $term->parent;
		if ( ! isset( $by_parent[ $parent_id ] ) ) {
			$by_parent[ $parent_id ] = [];
		}
		$link = get_term_link( $term );
		$url  = is_wp_error( $link ) ? '#' : $link;
		$by_parent[ $parent_id ][] = array(
			'id'      => 'term-' . $term->term_id,
			'text'    => $term->name,
			'url'     => $url,
			'wp_id'   => $term->term_id,
			'current' => $queried_term_id > 0 && (int) $term->term_id === $queried_term_id,
		);
	}
	foreach ( $by_parent as $pid => $siblings ) {
		usort( $by_parent[ $pid ], function ( $a, $b ) {
			return strcasecmp( $a['text'], $b['text'] );
		} );
	}
	return $by_parent;
}

/**
 * Строит дерево $by_parent из записей CPT.
 * Для иерархических типов (page, при необходимости product) — по post_parent; для остальных — один уровень.
 *
 * @param string $name Slug типа записи.
 * @param array  $args Аргументы (depth и т.д.).
 * @return array<int, array<int, array{id: string, text: string, url: string, wp_id: int, current: bool}>> Дерево по parent_id.
 */
function codeweber_nav_build_tree_cpt( $name, array $args ) {
	$post_type_object = get_post_type_object( $name );
	if ( ! $post_type_object || ! $post_type_object->public ) {
		return [];
	}
	$is_hierarchical = is_post_type_hierarchical( $name );
	$posts           = get_posts( array(
		'post_type'      => $name,
		'posts_per_page' => -1,
		'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
		'order'          => 'ASC',
		'post_status'    => 'publish',
	) );
	if ( empty( $posts ) ) {
		return [];
	}
	$current_id = (int) get_queried_object_id();
	$by_parent  = [];
	foreach ( $posts as $post ) {
		$parent_id = $is_hierarchical ? (int) $post->post_parent : 0;
		if ( ! isset( $by_parent[ $parent_id ] ) ) {
			$by_parent[ $parent_id ] = [];
		}
		$by_parent[ $parent_id ][] = array(
			'id'      => 'post-' . $post->ID,
			'text'    => $post->post_title,
			'url'     => get_permalink( $post ),
			'wp_id'   => $post->ID,
			'current' => $current_id > 0 && (int) $post->ID === $current_id,
		);
	}
	foreach ( $by_parent as $pid => $siblings ) {
		usort( $by_parent[ $pid ], function ( $a, $b ) {
			return strcasecmp( $a['text'], $b['text'] );
		} );
	}
	return $by_parent;
}

/**
 * Проверяет, есть ли текущая страница/термин в поддереве (для раскрытия collapse).
 *
 * @param array $by_parent Дерево.
 * @param int   $parent_id Ключ родителя.
 * @return bool
 */
function codeweber_nav_has_current_in_subtree( array $by_parent, $parent_id ) {
	$children = isset( $by_parent[ $parent_id ] ) ? $by_parent[ $parent_id ] : [];
	foreach ( $children as $item ) {
		if ( ! empty( $item['current'] ) ) {
			return true;
		}
		if ( isset( $item['wp_id'] ) && ! empty( $by_parent[ $item['wp_id'] ] ) && codeweber_nav_has_current_in_subtree( $by_parent, $item['wp_id'] ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Рекурсивный вывод collapse-уровня (тот же HTML, что у CodeWeber_Menu_Collapse_Walker).
 *
 * @param array $by_parent       Дерево.
 * @param int   $parent_id       Родительский ключ (0 = корень).
 * @param array $args            Аргументы (wrapper_id, depth, list_type, theme_class, item_class, link_class, top_level_*, instance_suffix).
 * @param int   $current_lvl     Текущий уровень (1 = верхний).
 * @param int   $top_level_count Число пунктов верхнего уровня.
 * @param int   $top_level_index Текущий индекс верхнего уровня (0-based).
 * @return string HTML.
 */
/**
 * Рендер простого списка (тип 4): ul > li > a, вложенные ul с тем же классом. Без collapse.
 *
 * @param array $by_parent Дерево по parent_id.
 * @param int   $parent_id ID родителя (0 — корень).
 * @param array $args      Аргументы (depth, menu_class, link_class, theme_class).
 * @param int   $current_lvl Текущий уровень (1 = верхний).
 * @return string HTML фрагмент.
 */
function codeweber_nav_render_simple( array $by_parent, $parent_id, array $args, $current_lvl = 1 ) {
	$children   = isset( $by_parent[ $parent_id ] ) ? $by_parent[ $parent_id ] : [];
	$depth_limit = isset( $args['depth'] ) ? (int) $args['depth'] : 0;
	$list_class  = ( $current_lvl > 1 && ! empty( $args['menu_class_sub'] ) ) ? $args['menu_class_sub'] : ( isset( $args['menu_class'] ) ? $args['menu_class'] : 'list-unstyled fs-sm lh-sm' );
	$link_class  = isset( $args['link_class'] ) ? trim( (string) $args['link_class'] ) : '';
	$theme_class = isset( $args['theme_class'] ) ? trim( (string) $args['theme_class'] ) : '';

	if ( empty( $children ) ) {
		return '';
	}
	$html = '';
	foreach ( $children as $item ) {
		$has_children = ( $depth_limit === 0 || $current_lvl < $depth_limit ) && isset( $by_parent[ $item['wp_id'] ] ) && ! empty( $by_parent[ $item['wp_id'] ] );
		$is_current   = ! empty( $item['current'] );
		$link_classes = array_filter( array_merge( $theme_class ? explode( ' ', $theme_class ) : [], $link_class ? explode( ' ', $link_class ) : [], $is_current ? array( 'active' ) : [] ) );
		$a_class      = ! empty( $link_classes ) ? ' class="' . esc_attr( implode( ' ', $link_classes ) ) . '"' : '';
		$aria_current = $is_current ? ' aria-current="page"' : '';
		$html        .= '<li><a href="' . esc_url( $item['url'] ) . '"' . $a_class . $aria_current . '>' . esc_html( $item['text'] ) . '</a>';
		if ( $has_children ) {
			$sub_list_class = ( $current_lvl >= 1 && ! empty( $args['menu_class_sub'] ) ) ? $args['menu_class_sub'] : $list_class;
			$html .= '<ul class="' . esc_attr( $sub_list_class ) . '">';
			$html .= codeweber_nav_render_simple( $by_parent, $item['wp_id'], $args, $current_lvl + 1 );
			$html .= '</ul>';
		}
		$html .= '</li>';
	}
	return $html;
}

/**
 * Рендер вертикального меню с выпаданием вправо (Bootstrap dropdown/dropend).
 * Разметка как у WP меню с CodeWeber_Vertical_Dropdown_Walker: nav-item, dropdown, dropend, dropdown-submenu, nav-link dropdown-toggle, dropdown-item dropdown-toggle, dropdown-menu.
 *
 * @param array $by_parent  Дерево по parent_id.
 * @param int   $parent_id  ID родителя (0 — корень).
 * @param array $args       Аргументы (depth, link_class, theme_class).
 * @param int   $current_lvl Текущий уровень (1 = верхний).
 * @return string HTML фрагмент.
 */
function codeweber_nav_render_dropdown( array $by_parent, $parent_id, array $args, $current_lvl = 1 ) {
	$children    = isset( $by_parent[ $parent_id ] ) ? $by_parent[ $parent_id ] : [];
	$depth_limit = isset( $args['depth'] ) ? (int) $args['depth'] : 0;
	$link_class  = isset( $args['link_class'] ) ? trim( (string) $args['link_class'] ) : '';
	$theme_class = isset( $args['theme_class'] ) ? trim( (string) $args['theme_class'] ) : '';

	if ( empty( $children ) ) {
		return '';
	}
	$html = '';
	foreach ( $children as $item ) {
		$has_children = ( $depth_limit === 0 || $current_lvl < $depth_limit ) && isset( $by_parent[ $item['wp_id'] ] ) && ! empty( $by_parent[ $item['wp_id'] ] );
		$is_current   = ! empty( $item['current'] );

		$li_classes = array( 'nav-item' );
		if ( $current_lvl === 1 ) {
			$li_classes[] = 'parent-item';
		}
		if ( $has_children ) {
			$li_classes[] = 'dropdown';
			$li_classes[] = 'dropend';
			if ( $current_lvl >= 1 ) {
				$li_classes[] = 'dropdown-submenu';
			}
		}
		if ( $is_current ) {
			$li_classes[] = 'current-menu-item';
		}
		$html .= '<li class="' . esc_attr( implode( ' ', $li_classes ) ) . '">';

		if ( $current_lvl === 1 ) {
			$a_classes = array( 'nav-link' );
		} else {
			$a_classes = array( 'dropdown-item' );
		}
		if ( $has_children ) {
			$a_classes[] = 'dropdown-toggle';
		}
		if ( $link_class !== '' ) {
			$a_classes = array_merge( $a_classes, array_filter( explode( ' ', $link_class ) ) );
		}
		if ( $theme_class !== '' ) {
			$a_classes[] = $theme_class;
		}
		if ( $is_current ) {
			$a_classes[] = 'active';
		}
		$a_classes = array_filter( $a_classes );

		$href     = $has_children ? '#' : $item['url'];
		$aria     = $has_children ? ' data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true"' : '';
		$aria_cur = $is_current ? ' aria-current="page"' : '';
		$html    .= '<a href="' . esc_url( $href ) . '" class="' . esc_attr( implode( ' ', $a_classes ) ) . '"' . $aria . $aria_cur . '>' . esc_html( $item['text'] ) . '</a>';

		if ( $has_children ) {
			$html .= '<ul class="dropdown-menu" role="menu">';
			$html .= codeweber_nav_render_dropdown( $by_parent, $item['wp_id'], $args, $current_lvl + 1 );
			$html .= '</ul>';
		}
		$html .= '</li>';
	}
	return $html;
}

function codeweber_nav_render_collapse( array $by_parent, $parent_id, array $args, $current_lvl = 1, $top_level_count = 0, &$top_level_index = 0, $parent_collapse_id = '' ) {
	$children   = isset( $by_parent[ $parent_id ] ) ? $by_parent[ $parent_id ] : [];
	$depth_limit = isset( $args['depth'] ) ? (int) $args['depth'] : 0;
	$wrapper_id  = isset( $args['wrapper_id'] ) ? $args['wrapper_id'] : '';
	$suffix      = isset( $args['instance_suffix'] ) ? $args['instance_suffix'] : '';
	$data_parent = ( $parent_collapse_id !== '' ) ? $parent_collapse_id : $wrapper_id;
	$list_class  = isset( $args['menu_class'] ) ? $args['menu_class'] : 'navbar-nav list-unstyled menu-collapse-1';
	$item_class  = isset( $args['item_class'] ) ? trim( (string) $args['item_class'] ) : '';
	$link_class  = isset( $args['link_class'] ) ? trim( (string) $args['link_class'] ) : '';
	$theme_class = isset( $args['theme_class'] ) ? trim( (string) $args['theme_class'] ) : '';
	$top_start   = isset( $args['top_level_class_start'] ) ? trim( (string) $args['top_level_class_start'] ) : '';
	$top_end     = isset( $args['top_level_class_end'] ) ? trim( (string) $args['top_level_class_end'] ) : '';
	$top_class   = isset( $args['top_level_class'] ) ? trim( (string) $args['top_level_class'] ) : '';

	if ( empty( $children ) ) {
		return '';
	}

	$html = '';
	$last = count( $children ) - 1;
	foreach ( $children as $idx => $item ) {
		$has_children = ( $depth_limit === 0 || $current_lvl < $depth_limit ) && isset( $by_parent[ $item['wp_id'] ] ) && ! empty( $by_parent[ $item['wp_id'] ] );
		$collapse_id  = 'menu-collapse-item-' . $item['wp_id'] . ( $suffix !== '' ? '-' . $suffix : '' );
		$expand        = $has_children && codeweber_nav_has_current_in_subtree( $by_parent, $item['wp_id'] );
		$is_current    = ! empty( $item['current'] );

		$li_classes = array( 'nav-item', 'parent-collapse-item' );
		if ( $current_lvl === 1 ) {
			$li_classes[] = 'parent-item';
			$is_first = ( $idx === 0 );
			$is_last  = ( $top_level_count > 0 && $idx === $last );
			if ( $is_first && $top_start !== '' ) {
				$li_classes = array_merge( $li_classes, array_filter( explode( ' ', $top_start ) ) );
			} elseif ( $is_last && $top_end !== '' ) {
				$li_classes = array_merge( $li_classes, array_filter( explode( ' ', $top_end ) ) );
			} elseif ( $top_class !== '' ) {
				$li_classes = array_merge( $li_classes, array_filter( explode( ' ', $top_class ) ) );
			}
		}
		if ( $item_class !== '' ) {
			$li_classes = array_merge( $li_classes, array_filter( explode( ' ', $item_class ) ) );
		}
		if ( $is_current ) {
			$li_classes[] = 'current-menu-item';
		}
		if ( $has_children ) {
			$li_classes[] = 'collapse-has-children';
		}
		$li_classes = array_filter( $li_classes );
		$html      .= '<li class="' . esc_attr( implode( ' ', $li_classes ) ) . '">';

		$link_classes = array( 'nav-link', 'd-block' );
		if ( $link_class !== '' ) {
			$link_classes = array_merge( $link_classes, array_filter( explode( ' ', $link_class ) ) );
		}
		if ( $theme_class !== '' ) {
			$link_classes[] = $theme_class;
		}
		if ( $is_current ) {
			$link_classes[] = 'current-menu-item';
		}
		$link_classes = array_filter( $link_classes );
		$aria_current  = $is_current ? ' aria-current="page"' : '';

		if ( $has_children ) {
			$html .= '<div class="menu-collapse-row d-flex align-items-center justify-content-between">';
			$html .= '<a href="' . esc_url( $item['url'] ) . '" class="' . esc_attr( implode( ' ', $link_classes ) ) . ' flex-grow-1"' . $aria_current . '>' . esc_html( $item['text'] ) . '</a>';
			$btn_class = 'btn-collapse w-5 h-5' . ( $theme_class !== '' ? ' ' . $theme_class : '' );
			$html .= '<button type="button" class="' . esc_attr( $btn_class ) . '" data-bs-toggle="collapse" data-bs-target="#' . esc_attr( $collapse_id ) . '" aria-expanded="' . ( $expand ? 'true' : 'false' ) . '" aria-controls="' . esc_attr( $collapse_id ) . '" aria-label="' . esc_attr__( 'Expand submenu', 'codeweber' ) . '">';
			$html .= '<span class="toggle_block" aria-hidden="true"><i class="uil uil-angle-down sidebar-catalog-icon"></i></span>';
			$html .= '</button>';
			$html .= '</div>';
			$html .= '<div class="collapse' . ( $expand ? ' show' : '' ) . '" id="' . esc_attr( $collapse_id ) . '" data-bs-parent="#' . esc_attr( $data_parent ) . '">';
			$html .= '<ul class="' . esc_attr( $list_class ) . '">';
			$html .= codeweber_nav_render_collapse( $by_parent, $item['wp_id'], $args, $current_lvl + 1, $top_level_count, $top_level_index, $collapse_id );
			$html .= '</ul>';
			$html .= '</div>';
		} else {
			$html .= '<a href="' . esc_url( $item['url'] ) . '" class="' . esc_attr( implode( ' ', $link_classes ) ) . ' d-block"' . $aria_current . '>' . esc_html( $item['text'] ) . '</a>';
		}
		$html .= '</li>';
	}
	return $html;
}

/**
 * Универсальная навигация из таксономии или CPT.
 * При пустых/не заданных $args — стандартная collapse-вёрстка со всеми уровнями.
 *
 * @param string       $source 'tax' (таксономия) или 'cpt' (тип записи).
 * @param string       $name   Slug таксономии (например category, product_cat) или slug типа записи (post, product).
 * @param array|string $args   Необязательный массив аргументов. См. codeweber_nav_default_args().
 * @return string HTML навигации или пустая строка.
 */
function codeweber_nav( $source, $name, $args = [] ) {
	$source = strtolower( (string) $source );
	$name   = (string) $name;
	if ( $name === '' ) {
		return '';
	}
	if ( $source !== 'tax' && $source !== 'cpt' ) {
		return '';
	}

	$defaults = codeweber_nav_default_args();
	$args     = is_array( $args ) ? $args : [];
	$args     = array_merge( $defaults, $args );

	if ( $source === 'tax' && ! taxonomy_exists( $name ) ) {
		return '';
	}
	if ( $source === 'cpt' ) {
		$pto = get_post_type_object( $name );
		if ( ! $pto || ! $pto->public ) {
			return '';
		}
	}

	global $codeweber_nav_instance;
	if ( ! isset( $codeweber_nav_instance ) ) {
		$codeweber_nav_instance = 0;
	}
	$codeweber_nav_instance++;
	$suffix = (string) $codeweber_nav_instance;

	if ( (string) $args['wrapper_id'] === '' ) {
		$args['wrapper_id'] = 'codeweber-nav-' . $source . '-' . preg_replace( '/[^a-z0-9_-]/i', '-', $name ) . '-' . $suffix;
	}
	$args['instance_suffix'] = $suffix;

	$list_type  = preg_replace( '/[^1-5]/', '', (string) $args['list_type'] );
	$list_type  = $list_type !== '' ? $list_type : '1';
	$list_class = $list_type === '5' ? 'navbar-nav flex-column' : ( $list_type === '4' ? 'list-unstyled menu-list-type-4' : 'navbar-nav list-unstyled menu-collapse-' . $list_type );
	if ( (string) $args['menu_class'] !== '' ) {
		$list_class = trim( (string) $args['menu_class'] );
	}
	$args['menu_class'] = $list_class;

	// Цвет задаётся темой через .navbar-light/.navbar-dark .nav-link
	$args['theme_class'] = '';

	if ( $source === 'tax' ) {
		$by_parent = codeweber_nav_build_tree_tax( $name, $args );
	} else {
		$by_parent = codeweber_nav_build_tree_cpt( $name, $args );
	}

	if ( empty( $by_parent ) || ! isset( $by_parent[0] ) || empty( $by_parent[0] ) ) {
		return '';
	}

	$top_level_count = count( $by_parent[0] );
	$top_level_index = 0;

	$theme_nav_class = ( $args['theme'] === 'dark' ) ? 'navbar-dark' : ( ( $args['theme'] === 'light' ) ? 'navbar-light' : '' );
	if ( $list_type === '4' ) {
		$container_class = 'navbar-vertical' . ( $theme_nav_class !== '' ? ' ' . $theme_nav_class : '' );
		if ( (string) $args['container_class'] !== '' ) {
			$container_class .= ' ' . trim( (string) $args['container_class'] );
		}
		$args['menu_class_sub'] = 'list-unstyled menu-type-4-sub';
		$nav_id                 = esc_attr( $args['wrapper_id'] );
		$nav_class              = ' class="' . esc_attr( trim( $container_class ) ) . '"';
		$ul_class               = esc_attr( $list_class );
		$html                   = '<nav id="' . $nav_id . '"' . $nav_class . '>';
		$html                  .= '<ul class="' . $ul_class . '">';
		$html                  .= codeweber_nav_render_simple( $by_parent, 0, $args, 1 );
		$html     .= '</ul></nav>';
		return $html;
	}

	if ( $list_type === '5' ) {
		$container_class = 'navbar-vertical navbar-vertical-dropdown' . ( $theme_nav_class !== '' ? ' ' . $theme_nav_class : '' );
		if ( (string) $args['container_class'] !== '' ) {
			$container_class .= ' ' . trim( (string) $args['container_class'] );
		}
		$nav_id    = esc_attr( $args['wrapper_id'] );
		$nav_class = esc_attr( $container_class );
		$ul_class  = esc_attr( $list_class );
		$html      = '<nav id="' . $nav_id . '" class="' . $nav_class . '">';
		$html     .= '<ul class="' . $ul_class . '">';
		$html     .= codeweber_nav_render_dropdown( $by_parent, 0, $args, 1 );
		$html     .= '</ul></nav>';
		return $html;
	}

	$container_class = 'navbar-vertical menu-collapse-nav' . ( $theme_nav_class !== '' ? ' ' . $theme_nav_class : '' );
	if ( (string) $args['container_class'] !== '' ) {
		$container_class .= ' ' . trim( (string) $args['container_class'] );
	}

	$nav_id    = esc_attr( $args['wrapper_id'] );
	$nav_class = esc_attr( $container_class );
	$ul_class  = esc_attr( $list_class );

	$html  = '<nav id="' . $nav_id . '" class="' . $nav_class . '">';
	$html .= '<ul class="' . $ul_class . '">';
	$html .= codeweber_nav_render_collapse( $by_parent, 0, $args, 1, $top_level_count, $top_level_index );
	$html .= '</ul>';
	$html .= '</nav>';

	return $html;
}

<?php
/**
 * Walker для вертикального меню в виде Bootstrap Collapse (accordion).
 * Используется только для тестирования — блок Menu пока не переведён на этот Walker.
 *
 * Разметка: nav.menu-collapse-nav > ul > li.parent-collapse-item > [link + button] или [link],
 * при наличии детей — div.collapse > ul > ...
 *
 * Передавать в wp_nav_menu(): 'walker' => new CodeWeber_Menu_Collapse_Walker(),
 * и кастомные аргументы: wrapper_id, instance_suffix, item_class, link_class, theme_class, top_level_class, depth.
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CodeWeber_Menu_Collapse_Walker' ) ) {

	class CodeWeber_Menu_Collapse_Walker extends Walker_Nav_Menu {

		/** @var string ID контейнера (nav) для data-bs-parent верхнего уровня */
		private $wrapper_id = '';

		/** @var string Суффикс экземпляра для уникальных id на странице */
		private $instance_suffix = '';

		/** @var string[] Стек id collapse-родителей для вложенных уровней */
		private $parent_collapse_stack = array();

		/** @var string|null ID следующего div.collapse (заполняется в start_el, используется в start_lvl) */
		private $next_collapse_id = null;

		/** @var string|null data-bs-parent для следующего div.collapse */
		private $next_collapse_parent = null;

		/** @var bool Раскрыть следующий div.collapse по умолчанию (путь до current) */
		private $next_collapse_show = false;

		/** @var int Лимит глубины (0 = без ограничения) */
		private $depth_limit = 0;

		/** @var string Доп. класс для ссылок (тема: text-dark, text-white и т.д.) */
		private $theme_class = '';

		/** @var string Классы для li */
		private $item_class = '';

		/** @var string Классы для ссылок */
		private $link_class = '';

		/** @var string Классы только для пунктов верхнего уровня (depth 0) */
		private $top_level_class = '';

		/**
		 * Инициализация кастомных аргументов из $args при первом вызове start_el (wp_nav_menu передаёт их в объекте $args).
		 */
		private function maybe_init_from_args( $args ) {
			if ( '' !== $this->wrapper_id ) {
				return;
			}
			$this->wrapper_id      = isset( $args->wrapper_id ) ? $args->wrapper_id : '';
			$this->instance_suffix = isset( $args->instance_suffix ) ? (string) $args->instance_suffix : '';
			$this->depth_limit     = isset( $args->depth ) ? (int) $args->depth : 0;
			$this->theme_class       = isset( $args->theme_class ) ? $args->theme_class : '';
			$this->item_class        = isset( $args->item_class ) ? $args->item_class : '';
			$this->link_class        = isset( $args->link_class ) ? $args->link_class : '';
			$this->top_level_class   = isset( $args->top_level_class ) ? $args->top_level_class : '';
		}

		/**
		 * Starts the list before the elements are added.
		 * Выводим <div class="collapse" id="..." data-bs-parent="..."><ul>
		 */
		public function start_lvl( &$output, $depth = 0, $args = null ) {
			if ( ! isset( $args->item_spacing ) || 'discard' !== $args->item_spacing ) {
				$t = "\t";
				$n = "\n";
			} else {
				$t = '';
				$n = "\n";
			}
			$indent = str_repeat( $t, $depth );

			$collapse_id     = $this->next_collapse_id;
			$collapse_parent = $this->next_collapse_parent;
			$collapse_show   = $this->next_collapse_show;
			$this->next_collapse_id     = null;
			$this->next_collapse_parent = null;
			$this->next_collapse_show   = false;

			if ( $collapse_id && $collapse_parent ) {
				$show_class = $collapse_show ? ' collapse show' : ' collapse';
				$output   .= $n . $indent . '<div class="' . esc_attr( trim( $show_class ) ) . '" id="' . esc_attr( $collapse_id ) . '" data-bs-parent="#' . esc_attr( $collapse_parent ) . '">';
				$this->parent_collapse_stack[] = $collapse_id;
			}

			$list_class = isset( $args->menu_class ) ? $args->menu_class : '';
			$output   .= $n . $indent . '<ul class="' . esc_attr( $list_class ) . ' ps-3">' . $n;
		}

		/**
		 * Ends the list. Закрываем </ul></div>
		 */
		public function end_lvl( &$output, $depth = 0, $args = null ) {
			if ( ! isset( $args->item_spacing ) || 'discard' !== $args->item_spacing ) {
				$t = "\t";
				$n = "\n";
			} else {
				$t = '';
				$n = "\n";
			}
			$indent = str_repeat( $t, $depth );
			$output .= $indent . '</ul>' . $n;
			if ( ! empty( $this->parent_collapse_stack ) ) {
				array_pop( $this->parent_collapse_stack );
				$output .= $indent . '</div>' . $n;
			}
		}

		/**
		 * Starts the element output.
		 */
		public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
			if ( ! $args instanceof \stdClass ) {
				return;
			}
			$this->maybe_init_from_args( $args );

			$item_spacing = isset( $args->item_spacing ) && 'discard' === $args->item_spacing ? '' : "\t";
			$indent       = ( $depth ) ? str_repeat( $item_spacing, $depth ) : '';

			// Показывать подменю только если следующий уровень ещё в пределах depth (как в блоке)
			$depth_limit_ok = ( 0 === $this->depth_limit || ( $depth + 1 ) < $this->depth_limit );
			$has_children   = $depth_limit_ok && in_array( 'menu-item-has-children', $item->classes, true );
			// Раскрывать только если текущая страница в поддереве (предок current), не когда сам пункт current
			$is_ancestor    = ! empty( $item->current_item_ancestor ) || in_array( 'current-menu-ancestor', $item->classes, true );
			$expand         = $has_children && $is_ancestor;

			$collapse_id = 'menu-collapse-item-' . $item->ID . ( $this->instance_suffix !== '' ? '-' . $this->instance_suffix : '' );
			if ( $has_children ) {
				$this->next_collapse_id     = $collapse_id;
				$this->next_collapse_parent = ( 0 === $depth ) ? $this->wrapper_id : ( ! empty( $this->parent_collapse_stack ) ? end( $this->parent_collapse_stack ) : $this->wrapper_id );
				$this->next_collapse_show  = $expand;
			}

			$li_classes = array( 'parent-collapse-item' );
			if ( 0 === $depth ) {
				$li_classes[] = 'parent-item';
				if ( $this->top_level_class ) {
					$li_classes = array_merge( $li_classes, array_filter( explode( ' ', trim( $this->top_level_class ) ) ) );
				}
			}
			if ( $this->item_class ) {
				$li_classes = array_merge( $li_classes, array_filter( explode( ' ', trim( $this->item_class ) ) ) );
			}
			if ( in_array( 'current-menu-item', $item->classes, true ) ) {
				$li_classes[] = 'current-menu-item';
			}
			if ( $has_children ) {
				$li_classes[] = 'collapse-has-children';
			}
			$li_classes = array_filter( $li_classes );
			$class_attr = ! empty( $li_classes ) ? ' class="' . esc_attr( implode( ' ', $li_classes ) ) . '"' : '';

			$output .= $indent . '<li' . $class_attr . '>';

			$link_classes = array( 'nav-link', 'd-block' );
			if ( $has_children ) {
				$link_classes[] = 'flex-grow-1';
			}
			if ( $this->theme_class ) {
				$link_classes[] = $this->theme_class;
			}
			if ( $this->link_class ) {
				$link_classes = array_merge( $link_classes, array_filter( explode( ' ', trim( $this->link_class ) ) ) );
			}
			if ( in_array( 'current-menu-item', $item->classes, true ) ) {
				$link_classes[] = 'current-menu-item';
			}
			$link_classes = array_filter( $link_classes );
			$aria_current = in_array( 'current-menu-item', $item->classes, true ) ? ' aria-current="page"' : '';
			$title        = apply_filters( 'the_title', $item->title, $item->ID );

			if ( $has_children ) {
				$output .= '<div class="menu-collapse-row d-flex align-items-center justify-content-between">';
				$output .= '<a href="' . esc_url( $item->url ) . '" class="' . esc_attr( implode( ' ', $link_classes ) ) . '"' . $aria_current . '>' . esc_html( $title ) . '</a>';
				$btn_classes = array( 'btn-collapse', 'w-5', 'h-5' );
				if ( $this->theme_class ) {
					$btn_classes[] = $this->theme_class;
				}
				$output .= '<button type="button" class="' . esc_attr( implode( ' ', $btn_classes ) ) . '" data-bs-toggle="collapse" data-bs-target="#' . esc_attr( $collapse_id ) . '" aria-expanded="' . ( $expand ? 'true' : 'false' ) . '" aria-controls="' . esc_attr( $collapse_id ) . '" aria-label="' . esc_attr__( 'Expand submenu', 'codeweber' ) . '">';
				$output .= '<span class="toggle_block" aria-hidden="true"><i class="uil uil-angle-down sidebar-catalog-icon"></i></span>';
				$output .= '</button>';
				$output .= '</div>';
			} else {
				$output .= '<a href="' . esc_url( $item->url ) . '" class="' . esc_attr( implode( ' ', $link_classes ) ) . '"' . $aria_current . '>' . esc_html( $title ) . '</a>';
			}
		}

		/**
		 * Ends the element. Только закрываем </li>
		 */
		public function end_el( &$output, $item, $depth = 0, $args = null ) {
			$item_spacing = isset( $args->item_spacing ) && 'discard' === $args->item_spacing ? '' : "\t";
			$indent       = ( $depth ) ? str_repeat( $item_spacing, $depth ) : '';
			$output     .= $indent . '</li>' . "\n";
		}
	}
}

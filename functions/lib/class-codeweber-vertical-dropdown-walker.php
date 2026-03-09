<?php
/**
 * Walker для вертикального меню с выпадающими подменю вправо (dropend на всех уровнях).
 * Расширяет WP_Bootstrap_Navwalker: для пунктов первого уровня с детьми добавляет dropend,
 * чтобы подменю открывалось вправо, как на вложенных уровнях.
 *
 * Использование: 'walker' => new CodeWeber_Vertical_Dropdown_Walker()
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Bootstrap_Navwalker' ) ) {
	return;
}

class CodeWeber_Vertical_Dropdown_Walker extends WP_Bootstrap_Navwalker {

	/**
	 * Флаг: сейчас рендерится меню нашим Walker'ом (для фильтра nav_menu_css_class).
	 *
	 * @var bool
	 */
	public static $rendering_vertical = false;

	/**
	 * Инициализация: подключаем фильтр классов один раз.
	 */
	public static function init() {
		if ( has_filter( 'nav_menu_css_class', array( __CLASS__, 'add_dropend_for_vertical' ) ) ) {
			return;
		}
		add_filter( 'nav_menu_css_class', array( __CLASS__, 'add_dropend_for_vertical' ), 10, 4 );
	}

	/**
	 * Для вертикального меню добавляет dropend пунктам первого уровня с детьми.
	 *
	 * @param string[] $classes Классы элемента меню.
	 * @param WP_Post  $item    Элемент меню.
	 * @param stdClass $args    Аргументы wp_nav_menu().
	 * @param int      $depth   Глубина.
	 * @return string[]
	 */
	public static function add_dropend_for_vertical( $classes, $item, $args, $depth ) {
		if ( ! self::$rendering_vertical || $depth !== 0 ) {
			return $classes;
		}
		$classes[] = 'parent-item';
		if ( in_array( 'menu-item-has-children', $classes, true ) ) {
			$classes[] = 'dropend';
		}
		return $classes;
	}

	/**
	 * Starts the element output.
	 * Устанавливает флаг и вызывает родительский Walker.
	 *
	 * @param string   $output Used to append additional content (passed by reference).
	 * @param WP_Post  $item   Menu item data object.
	 * @param int      $depth  Depth of menu item. Used for padding.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 * @param int      $id     Current item ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		self::$rendering_vertical = true;
		parent::start_el( $output, $item, $depth, $args, $id );
		self::$rendering_vertical = false;
	}
}

CodeWeber_Vertical_Dropdown_Walker::init();

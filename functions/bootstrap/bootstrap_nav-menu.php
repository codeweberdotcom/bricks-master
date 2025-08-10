<?php

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
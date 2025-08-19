<?php

/**
 *  Redux Cookie Banner
 */
require 'redux_cookie.php';


/**
 *  Redux Custom Logos
 */
require 'redux_custom_logos.php';


/**
 *  Redux Scanner Cookie
 */
require 'redux_scanner_cookie.php';


/**
 *  Redux Scanner Cookie
 */
require 'redux_pageheader.php';


/**
 *  Redux Style
 */
require 'redux_style.php';


/**
 *  Redux Style
 */
require 'redux_cf7.php';


/**
 *  Redux Contacts
 */
require 'redux_contacts.php';

/**
 *  Redux Another Function
 */


/**
 * Шорткод [redux_option]
 * Создан для удобства, дает возможность вывести любые значения из Redux Framework
 * Возвращает значение из Redux Framework с возможностью вывода массива (checkbox) как строки или списка.
 * Позволяет выводить любые значения, сохраненные в Redux Framework, включая одиночные значения,
 * массивы, даты (с форматированием) и специальные поля вроде чекбоксов.
 *
 * Атрибуты:
 * - key     — ключ поля Redux (обязательный параметр).
 * - default — значение по умолчанию, если ключ не найден или пуст.
 * - format  — формат для даты, если значение является датой.
 * - list    — если указано "inline", значения массива объединяются через запятую;
 *             если указано "ul", выводятся как <ul><li>…</li></ul>.
 * 
 * Примеры использования:
 * [redux_option key="your_field_key"] - вывод простого значения
 * [redux_option key="checkbox_field" list="ul"] - вывод массива как списка
 * [redux_option key="date_field" format="d.m.Y"] - форматирование даты
 */
add_shortcode('redux_option', function ($atts) {
   global $opt_name;

   $atts = shortcode_atts(array(
      'key'     => '',
      'default' => '',
      'format'  => '',
      'list'    => '', // 'inline' | 'ul'
   ), $atts, 'redux_option');

   if (empty($atts['key']) || empty($opt_name)) {
      return '';
   }

   $value = Redux::get_option($opt_name, $atts['key']);

   if (empty($value)) {
      return esc_html($atts['default']);
   }

   // Массив опций с переводами для personal_data_actions
   $personal_data_actions_options = array(
      'collection'      => __('Collection Data', 'codeweber'),
      'recording'       => __('Recording Data', 'codeweber'),
      'systematization' => __('Systematization Data', 'codeweber'),
      'accumulation'    => __('Accumulation Data', 'codeweber'),
      'storage'         => __('Storage Data', 'codeweber'),
      'updating'        => __('Updating (clarification, modification) Data', 'codeweber'),
      'extraction'      => __('Extraction Data', 'codeweber'),
      'usage'           => __('Usage Data', 'codeweber'),
      'transfer'        => __('Transfer (distribution, provision, access) Data', 'codeweber'),
      'blocking'        => __('Blocking Data', 'codeweber'),
      'deletion'        => __('Deletion Data', 'codeweber'),
      'destruction'     => __('Destruction Data', 'codeweber'),
   );

   // Форматируем дату
   if (!empty($atts['format']) && strtotime($value)) {
      return esc_html(date($atts['format'], strtotime($value)));
   }

   // Если значение — массив (например, checkbox)
   if (is_array($value)) {
      // Для поля personal_data_actions подменяем ключи на переводы
      if ($atts['key'] === 'personal_data_actions') {
         // В $value ключи - выбранные опции, значения true/1
         $selected_keys = array_keys(array_filter($value));
         if ($atts['list'] === 'inline') {
            $items = array_map(function ($key) use ($personal_data_actions_options) {
               return $personal_data_actions_options[$key] ?? $key;
            }, $selected_keys);
            return esc_html(implode(', ', $items));
         } elseif ($atts['list'] === 'ul') {
            $out = '<ul>';
            foreach ($selected_keys as $key) {
               $label = $personal_data_actions_options[$key] ?? $key;
               $out .= '<li>' . esc_html($label) . '</li>';
            }
            $out .= '</ul>';
            return $out;
         }
      }

      // Если не personal_data_actions, просто выводим массив как строку
      if ($atts['list'] === 'inline') {
         return esc_html(implode(', ', $value));
      } elseif ($atts['list'] === 'ul') {
         $out = '<ul>';
         foreach ($value as $item) {
            $out .= '<li>' . esc_html($item) . '</li>';
         }
         $out .= '</ul>';
         return $out;
      }
   }

   // Обычное значение
   return $value;
});
<?php

/**
 * Регистрирует сайдбары для включенных CPT через Redux
 * 
 * Оптимизации:
 * - Убрано дублирование преобразований имени файла
 * - Использована более эффективная проверка состояния CPT
 * - Улучшена обработка строк и переводов
 * - Добавлена проверка существования функции Redux
 */
function codeweber_register_cpt_redux_sidebars()
{
   global $opt_name;

   // Правильная проверка доступности Redux Framework
   if (!class_exists('Redux') || !method_exists('Redux', 'get_option')) {
      if (defined('WP_DEBUG') && WP_DEBUG) {
         error_log('Redux Framework не доступен для регистрации сайдбаров CPT');
      }
      return;
   }

   $cpt_files = get_cpt_files_list();
   if (empty($cpt_files)) {
      return;
   }

   foreach ($cpt_files as $file) {
      // Единоразовое преобразование имени файла
      $base_name = str_replace(['cpt-', '.php'], '', $file);
      if (empty($base_name)) {
         $base_name = 'unnamed';
      }

      // Форматирование для интерфейса
      $display_name = str_replace(['-', '_'], ' ', $base_name);
      $display_name = mb_convert_case($display_name, MB_CASE_TITLE, 'UTF-8');
      $translated_label = __($display_name, 'codeweber');

      // Проверка состояния CPT
      $option_id = 'cpt_switch_' . sanitize_key($base_name);
      if (!Redux::get_option($opt_name, $option_id)) {
         continue;
      }

      // Регистрация сайдбара
      codeweber_sidebars(
         $translated_label,
         sanitize_key($base_name), // Используем безопасный ID
         sprintf(esc_html__('Widget area for %s', 'codeweber'), $translated_label),
         'h3',
         'custom-title-class'
      );
   }
}
add_action('widgets_init', 'codeweber_register_cpt_redux_sidebars', 20); // Повышенный приоритет
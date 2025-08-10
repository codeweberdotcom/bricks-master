<?php

/**
 * Регистрация сайдбаров для CPT с использованием Redux
 */
function codeweber_register_cpt_redux_sidebars()
{
   global $opt_name;

   // Получаем список всех файлов CPT
   $cpt_files = get_cpt_files_list();

   if (!empty($cpt_files)) {
      foreach ($cpt_files as $file) {
         // Преобразуем имя файла в читаемый формат
         $label = ucwords(str_replace(['cpt-', '.php'], '', $file));
         $label = $label ?: __('Unnamed', 'codeweber');
         $translated_label = __(ucwords(str_replace(['cpt-', '.php'], '', $file)), 'codeweber');

         // Получаем состояние переключателя для этого CPT из Redux
         $option_id = 'cpt_switch_' . $translated_label;
         $is_enabled = Redux::get_option($opt_name, $option_id);

         $sidebar_id = str_replace(['cpt-', '.php'], '', $file);

         if ($is_enabled) {
            codeweber_sidebars(
               $translated_label,
               $sidebar_id,
               sprintf(esc_html__('Widget area for %s', 'codeweber'), $translated_label),
               'h3',
               'custom-title-class'
            );
         }
      }
   }
}
add_action('widgets_init', 'codeweber_register_cpt_redux_sidebars');
<?php
global $opt_name;

// Получаем список всех файлов CPT из обеих тем
$cpt_files = get_cpt_files_list();
$child_cpt_files = get_child_cpt_files_list();
$cpt_status = [];

// Проверяем, есть ли файлы CPT
if (!empty($cpt_files)) {
   foreach ($cpt_files as $file) {
      // Базовое имя файла без расширения и префикса
      $base_name = str_replace(['cpt-', '.php'], '', $file);

      // Читаемое имя (без форматирования)
      $label = $base_name ?: __('Unnamed', 'codeweber');
      $translated_label = __($base_name, 'codeweber');

      // Безопасный ID для Redux
      $option_id = 'cpt_switch_' . sanitize_key($base_name);
      $is_enabled = Redux::get_option($opt_name, $option_id);

      // Для legal и notifications CPT принудительно включаем без проверки переключателя
      if ($file === 'cpt-legal.php' || $file === 'cpt-notifications.php') {
         $is_enabled = true;
      }

      if ($is_enabled) {
         // Определяем путь к файлу: сначала проверяем дочернюю тему, затем родительскую
         $file_path = '';

         // Проверяем, есть ли файл в дочерней теме
         if (in_array($file, $child_cpt_files)) {
            $file_path = get_stylesheet_directory() . '/functions/cpt/' . $file;
         }
         // Если нет в дочерней, проверяем в родительской
         else {
            $file_path = get_template_directory() . '/functions/cpt/' . $file;
         }

         if (file_exists($file_path)) {
            // Подключаем с буферизацией для отладки
            ob_start();
            require_once $file_path;

            if ($output = ob_get_clean()) {
               error_log("CPT file output detected: {$file} - " . substr($output, 0, 100));
            }

            $cpt_status[] = [
               'label'  => $translated_label,
               'status' => 'Enabled',
               'file'   => $file,
               'path'   => $file_path
            ];
         } else {
            error_log("CPT file not found: {$file_path}");
         }
      } else {
         $cpt_status[] = [
            'label'  => $translated_label,
            'status' => 'Disabled',
            'file'   => $file
         ];
      }
   }
} else {
   error_log('No CPT files found in directories: ' . get_template_directory() . '/functions/cpt/ and ' . get_stylesheet_directory() . '/functions/cpt/');
}

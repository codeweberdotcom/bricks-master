<?php

global $opt_name;

// Получаем список всех файлов CPT
$cpt_files = get_cpt_files_list();
$cpt_status = [];

// Проверяем, есть ли файлы CPT
if (!empty($cpt_files)) {
   foreach ($cpt_files as $file) {
      // Преобразуем имя файла в читаемый формат
      $label = ucwords(str_replace(array('cpt-', '.php'), '', $file));
      $label = $label ?: __('Unnamed', 'codeweber');
      $translated_label = __(ucwords(str_replace(array('cpt-', '.php'), '', $file)), 'codeweber');

      // Получаем состояние переключателя для этого CPT из Redux
      $option_id = 'cpt_switch_' . $translated_label; // Убираем sanitize
      $is_enabled = Redux::get_option($opt_name, $option_id);


      // Добавляем результат в список, если CPT включен
      if ($is_enabled) {
         // Путь к файлу CPT
         $file_path = get_template_directory() . '/functions/cpt/' . $file;

         // Проверяем, существует ли файл
         if (file_exists($file_path)) {
            // Подключаем файл
            // Подключаем файл с отладкой вывода
            ob_start(); // начать буферизацию вывода

            require_once $file_path;

            $output = ob_get_clean(); // забрать, что файл вывел

            if (!empty($output)) {
               error_log("Файл $file вывел: " . $output);
            }            //error_log('CPT file ' . $file . ' included successfully.');
         } else {
          //  error_log('CPT file ' . $file . ' not found.');
         }

         // Добавляем результат в список
         $cpt_status[] = [
            'label'  => $translated_label,
            'status' => 'Enabled'
         ];
      } else {
         // Логируем, если CPT отключён
         //error_log('CPT ' . $file . ' is disabled.');
      }
   }
} else {
  // error_log('No CPT files found.');
}
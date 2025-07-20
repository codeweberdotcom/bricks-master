<?php

// Подключаем скрипт в админке и передаём данные для AJAX
add_action('admin_enqueue_scripts', function ($hook) {
   wp_enqueue_script(
      'gulp-build-trigger',
      get_template_directory_uri() . '/admin/build-trigger.js',
      ['jquery'],
      null,
      true
   );

   wp_localize_script('gulp-build-trigger', 'gulpBuildAjax', [
      'ajax_url' => admin_url('admin-ajax.php'),
      'nonce'    => wp_create_nonce('gulp_build_nonce'),
   ]);
});

// AJAX обработчик для запуска Gulp
add_action('wp_ajax_run_gulp_build', 'run_gulp_build_callback');

function run_gulp_build_callback()
{
   // Проверка nonce для безопасности
   if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce($_POST['_ajax_nonce'], 'gulp_build_nonce')) {
      wp_send_json_error(['message' => 'Nonce verification failed.']);
      return;
   }

   // Получаем данные из Redux-поля
   global $opt_name;
   $global_header_model = Redux::get_option($opt_name, 'opt-gulp-sass-variation');

   // Путь к файлу _user-variables.scss
   $scss_file_path = get_template_directory() . '/src/assets/scss/_user-variables.scss';

   // Проверяем, существует ли файл
   if (!file_exists($scss_file_path)) {
      wp_send_json_error(['message' => 'Файл _user-variables.scss не найден']);
      return;
   }

   // Перезаписываем файл содержимым из поля Redux или пустой строкой
   $file_content = $global_header_model ?? '';
   $write_result = file_put_contents($scss_file_path, $file_content);

   if ($write_result === false) {
      wp_send_json_error(['message' => 'Не удалось записать в файл']);
      return;
   }

   // Выполнение команды Gulp
   $output = null;
   $retval = null;
   $cmd = 'cd ' . escapeshellarg(get_template_directory()) . ' && gulp build:dist 2>&1';

   exec($cmd, $output, $retval);

   if ($retval === 0) {
      wp_send_json_success(['output' => $output]);
   } else {
      wp_send_json_error(['output' => $output]);
   }
}

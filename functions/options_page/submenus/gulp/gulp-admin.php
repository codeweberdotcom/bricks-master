<?php


// Функция для отображения страницы с редактированием файла
function gulp_callback()
{
   // Путь к файлу
   $file_path = get_template_directory() . '/src/assets/scss/_user-variables.scss';

   // Проверка, существует ли файл
   if (file_exists($file_path)) {
      // Загружаем содержимое файла
      $file_content = file_get_contents($file_path);
   } else {
      $file_content = 'Файл не найден.';
   }

   // Если форма отправлена, сохраняем изменения
   if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_changes'])) {
      // Проверка на безопасность (nonce)
      if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'save_gulp_changes')) {
         // Сохраняем изменения в файл
         $new_content = sanitize_textarea_field($_POST['file_content']);
         file_put_contents($file_path, $new_content);
         echo '<div class="updated"><p>Файл успешно обновлен.</p></div>';
      }
   }

?>
   <div class="wrap">
      <h1>Запуск Gulp</h1>
      <!-- Форма для редактирования содержимого SCSS файла -->
      <h2>Редактирование файла user-variables.scss</h2>
      <form method="post">
         <?php wp_nonce_field('save_gulp_changes'); // Защита от CSRF 
         ?>
         <textarea name="file_content" rows="20" style="width: 100%;"><?php echo esc_textarea($file_content); ?></textarea>
         <p>
            <button type="submit" name="save_changes" class="button button-primary">Сохранить изменения</button>
         </p>
      </form>

      <!-- Форма для запуска Gulp -->
      <form id="gulp-form" method="post">
         <?php wp_nonce_field('run_gulp_task', 'gulp_nonce'); ?>
         <p>
            <button type="submit" name="gulp_action" class="button button-primary">
               Запустить Gulp
            </button>
         </p>
      </form>

      <div id="gulp-output"></div> <!-- Здесь будет выводиться информация -->
   </div>
<?php
 
}


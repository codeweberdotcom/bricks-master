<?php

// Регистрация CPT
function cptui_register_my_cpts_documents()
{
   $labels = [
      "name" => esc_html__("Docs", "codeweber"),
      "singular_name" => esc_html__("Doc", "codeweber"),
   ];

   $args = [
      "label" => esc_html__("Docs", "codeweber"),
      "labels" => $labels,
      "public" => true,
      "publicly_queryable" => false,
      "show_ui" => true,
      "show_in_rest" => true,
      "rest_base" => "",
      "rest_controller_class" => "WP_REST_Posts_Controller",
      "rest_namespace" => "wp/v2",
      "has_archive" => false,
      "show_in_menu" => true,
      "show_in_nav_menus" => true,
      "exclude_from_search" => true,
      "capability_type" => "post",
      "map_meta_cap" => true,
      "hierarchical" => false,
      "can_export" => true,
      "rewrite" => ["slug" => "documents", "with_front" => true],
      "query_var" => true,
      "supports" => ["title", "custom-fields"], // Поддержка custom-fields
      "show_in_graphql" => false,
   ];

   register_post_type("documents", $args);
}

add_action('init', 'cptui_register_my_cpts_documents');

// Регистрация метаполя для загрузки файла
function register_documents_file_meta()
{
   register_post_meta('documents', '_new_documents_file', [
      'show_in_rest' => true, // Показывать в REST API
      'single' => true,
      'type' => 'string',
      'sanitize_callback' => 'sanitize_text_field', // Для безопасности
   ]);
}
add_action('init', 'register_documents_file_meta');

// Функция для рендера метабокса для загрузки файла
function render_documents_file_upload_field($post)
{
   // Получаем URL файла (если файл уже загружен)
   $file_url = get_post_meta($post->ID, '_new_documents_file', true);

   // Если файл есть, получаем его имя
   $file_name = '';
   if ($file_url) {
      $file_name = basename($file_url); // Получаем имя файла
   }

   // Добавляем nonce поле для безопасности
   wp_nonce_field('documents_file_upload_nonce', 'documents_file_upload_nonce_field');
   wp_enqueue_media(); // Подключаем медиабиблиотеку
?>
   <div id="documents_file_upload" class="postbox">
      <div class="inside">
         <label for="documents_file">Upload a file:</label>
         <button type="button" class="button" id="documents_file_button">Select File</button>
         <input type="hidden" id="documents_file" name="documents_file" value="<?php echo esc_attr($file_url); ?>">

         <?php if ($file_name): ?>
            <p><strong>Current File:</strong> <?php echo esc_html($file_name); ?></p>
         <?php endif; ?>
      </div>
   </div>

   <script type="text/javascript">
      jQuery(document).ready(function($) {
         var mediaUploader;

         $('#documents_file_button').click(function(e) {
            e.preventDefault();

            // Если медиазагрузчик уже существует, откроем его
            if (mediaUploader) {
               mediaUploader.open();
               return;
            }

            // Создаем медиазагрузчик
            mediaUploader = wp.media({
               title: 'Select or Upload a Document',
               button: {
                  text: 'Use this file'
               },
               multiple: false // Ожидаем один файл
            });

            // Когда файл выбран
            mediaUploader.on('select', function() {
               var attachment = mediaUploader.state().get('selection').first().toJSON();
               $('#documents_file').val(attachment.url); // Записываем URL файла в скрытое поле
               $('#documents_file_button').text(attachment.filename); // Меняем текст кнопки на имя файла
            });

            mediaUploader.open();
         });
      });
   </script>
<?php
}


// Функция для добавления метабокса с полем для загрузки файла
function documents_file_upload_meta_box()
{
   add_meta_box(
      'documents_file_upload',        // ID метабокса
      'Upload File',                   // Заголовок метабокса
      'render_documents_file_upload_field', // Функция для рендера
      'documents',                     // Тип записи
      'normal',                        // Позиция метабокса
      'high'                           // Приоритет метабокса
   );
}
add_action('add_meta_boxes', 'documents_file_upload_meta_box');

// Обработка сохранения файла при сохранении записи
function save_documents_file_upload($post_id)
{
   // Отключаем авто-сохранение
   if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;

   // Проверяем nonce для безопасности
   if (!isset($_POST['documents_file_upload_nonce_field']) || !wp_verify_nonce($_POST['documents_file_upload_nonce_field'], 'documents_file_upload_nonce')) {
      return $post_id;
   }

   // Проверяем, что это нужный тип записи
   if ('documents' !== get_post_type($post_id)) return $post_id;

   // Если файл был загружен
   if (isset($_POST['documents_file']) && !empty($_POST['documents_file'])) {
      $file_url = esc_url_raw($_POST['documents_file']);

      // Обновляем метаполе с URL файла
      update_post_meta($post_id, '_new_documents_file', $file_url);
   } else {
      // Если файл не был загружен, не обновляем URL
      // Если файл был удален, очистим метаполе
      if (isset($_POST['_delete_documents_file']) && $_POST['_delete_documents_file'] === '1') {
         delete_post_meta($post_id, '_new_documents_file');
      }
   }

   return $post_id;
}
add_action('save_post', 'save_documents_file_upload');

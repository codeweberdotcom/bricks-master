<?php

function register_my_cpt_documents()
{
   $labels = [
      "name" => esc_html__("Documents", "codeweber"),
      "singular_name" => esc_html__("Document", "codeweber"),
      "menu_name" => esc_html__("Documents", "codeweber"),
      "all_items" => esc_html__("All Documents", "codeweber"),
      "add_new" => esc_html__("Add Document", "codeweber"),
      "add_new_item" => esc_html__("Add New Document", "codeweber"),
      "edit_item" => esc_html__("Edit Document", "codeweber"),
      "new_item" => esc_html__("New Document", "codeweber"),
      "view_item" => esc_html__("View Document", "codeweber"),
      "view_items" => esc_html__("View Documents", "codeweber"),
      "search_items" => esc_html__("Search Document", "codeweber"),
      "not_found" => esc_html__("No documents found", "codeweber"),
      "not_found_in_trash" => esc_html__("No documents found in Trash", "codeweber"),
      "parent_item_colon" => esc_html__("Parent Document", "codeweber"),
      "featured_image" => esc_html__("Featured Image", "codeweber"),
      "set_featured_image" => esc_html__("Set featured image", "codeweber"),
      "remove_featured_image" => esc_html__("Remove featured image", "codeweber"),
      "use_featured_image" => esc_html__("Use as featured image", "codeweber"),
      "archives" => esc_html__("Document Archives", "codeweber"),
      "items_list" => esc_html__("Documents List", "codeweber"),
      "name_admin_bar" => esc_html__("Document", "codeweber"),
      "item_published" => esc_html__("Document Published", "codeweber"),
      "item_reverted_to_draft" => esc_html__("Document Reverted to Draft", "codeweber"),
      "item_scheduled" => esc_html__("Document Scheduled", "codeweber"),
      "item_updated" => esc_html__("Document Updated", "codeweber"),
   ];

   $args = [
      "label" => __("Documents", "codeweber"),
      "labels" => $labels,
      "public" => true,
      "publicly_queryable" => true,
      "show_ui" => true,
      "show_in_rest" => true,
      "rest_base" => "",
      "rest_controller_class" => "WP_REST_Posts_Controller",
      "has_archive" => true,
      "show_in_menu" => true,
      "show_in_nav_menus" => true,
      "delete_with_user" => false,
      "exclude_from_search" => false,
      "capability_type" => "post",
      "map_meta_cap" => true,
      "hierarchical" => true,
      "can_export" => true,
      "rewrite" => ["slug" => "documents", "with_front" => true],
      "query_var" => true,
      "supports" => ["title", "revisions", "author"],
      "taxonomies" => ["document_category", "document_type"],
   ];

   register_post_type("documents", $args);
}
add_action('init', 'register_my_cpt_documents');

function register_taxonomy_document_category()
{
   $labels = [
      "name" => __("Document Categories", "codeweber"),
      "singular_name" => __("Document Category", "codeweber"),
   ];

   $args = [
      "label" => __("Document Categories", "codeweber"),
      "labels" => $labels,
      "public" => true,
      "publicly_queryable" => true,
      "hierarchical" => false,
      "show_ui" => true,
      "show_in_menu" => true,
      "show_in_nav_menus" => true,
      "query_var" => true,
      "rewrite" => ['slug' => 'document_category', 'with_front' => true],
      "show_admin_column" => false,
      "show_in_rest" => true,
      "show_tagcloud" => false,
      "rest_base" => "document_category",
      "rest_controller_class" => "WP_REST_Terms_Controller",
   ];

   register_taxonomy("document_category", ["documents"], $args);
}
add_action('init', 'register_taxonomy_document_category');

function register_taxonomy_document_type()
{
   $labels = [
      "name" => __("Document Types", "codeweber"),
      "singular_name" => __("Document Type", "codeweber"),
   ];

   $args = [
      "label" => __("Document Types", "codeweber"),
      "labels" => $labels,
      "public" => true,
      "publicly_queryable" => true,
      "hierarchical" => false,
      "show_ui" => true,
      "show_in_menu" => true,
      "show_in_nav_menus" => true,
      "query_var" => true,
      "rewrite" => ['slug' => 'document_type', 'with_front' => true],
      "show_admin_column" => false,
      "show_in_rest" => true,
      "show_tagcloud" => false,
      "rest_base" => "document_type",
      "rest_controller_class" => "WP_REST_Terms_Controller",
   ];

   register_taxonomy("document_type", ["documents"], $args);
}
add_action('init', 'register_taxonomy_document_type');


add_filter('use_block_editor_for_post_type', 'disable_gutenberg_for_documents', 10, 2);
function disable_gutenberg_for_documents($current_status, $post_type)
{
   if ($post_type === 'documents') {
      return false;
   }
   return $current_status;
}




/**
 * Добавляет мета-бокс для загрузки файлов к custom post type 'documents'
 * 
 * Функция создает мета-бокс в интерфейсе редактирования документов
 * для загрузки и управления файлами документов.
 * 
 * @hook add_meta_boxes
 * @return void
 * 
 * Примеры использования в child теме:
 * 
 * 1. ПЕРЕОПРЕДЕЛЕНИЕ РАЗРЕШЕННЫХ ТИПОВ ФАЙЛОВ:
 * 
 * function modify_allowed_document_types($default_types) {
 *     $new_types = array(
 *         'pdf'  => 'application/pdf',
 *         'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
 *         'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
 *         'jpg'  => 'image/jpeg',
 *         'png'  => 'image/png',
 *         'zip'  => 'application/zip'
 *     );
 *     return $new_types;
 * }
 * 
 * 2. ДОБАВЛЕНИЕ НОВЫХ ТИПОВ К СУЩЕСТВУЮЩИМ:
 * 
 * function modify_allowed_document_types($default_types) {
 *     $additional_types = array(
 *         'jpg' => 'image/jpeg',
 *         'png' => 'image/png',
 *         'mp4' => 'video/mp4'
 *     );
 *     return array_merge($default_types, $additional_types);
 * }
 * 
 * 3. УДАЛЕНИЕ ОПРЕДЕЛЕННЫХ ТИПОВ:
 * 
 * function modify_allowed_document_types($default_types) {
 *     unset($default_types['rar']);
 *     unset($default_types['ppt']);
 *     return $default_types;
 * }
 * 
 * 4. ИСПОЛЬЗОВАНИЕ ФИЛЬТРА (альтернативный способ):
 * 
 * add_filter('allowed_document_types', 'my_custom_document_types');
 * function my_custom_document_types($types) {
 *     $types['psd'] = 'image/vnd.adobe.photoshop';
 *     $types['ai'] = 'application/postscript';
 *     return $types;
 * }
 * 
 * 5. УТИЛИТНЫЕ ФУНКЦИИ ДЛЯ CHILD ТЕМЫ:
 * 
 * // Добавить один тип файла
 * add_document_type('psd', 'image/vnd.adobe.photoshop');
 * 
 * // Удалить тип файла
 * remove_document_type('rar');
 * 
 * // Получить текущие разрешенные типы
 * $current_types = get_current_allowed_document_types();
 */
function add_documents_file_meta_box()
{
   add_meta_box(
      'documents_file_meta',
      'Файл документа',
      'render_documents_file_meta_box',
      'documents',
      'normal',
      'high'
   );
}
add_action('add_meta_boxes', 'add_documents_file_meta_box');

/**
 * Возвращает массив разрешенных типов файлов с возможностью переопределения в child теме
 * 
 * @return array Ассоциативный массив в формате 'расширение' => 'MIME-тип'
 * 
 * @example
 * // В child теме можно переопределить эту функцию:
 * function modify_allowed_document_types($default_types) {
 *     // Ваша кастомизация здесь
 *     return $custom_types;
 * }
 */
function get_allowed_document_types()
{
   $default_types = array(
      'pdf'  => 'application/pdf',
      'doc'  => 'application/msword',
      'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      'xls'  => 'application/vnd.ms-excel',
      'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'ppt'  => 'application/vnd.ms-powerpoint',
      'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
      'txt'  => 'text/plain',
      'zip'  => 'application/zip',
      'rar'  => 'application/x-rar-compressed'
   );

   // Позволяем переопределить в child теме
   if (function_exists('modify_allowed_document_types')) {
      $default_types = modify_allowed_document_types($default_types);
   }

   // Также можно использовать фильтр для большей гибкости
   return apply_filters('allowed_document_types', $default_types);
}

/**
 * Возвращает строку с разрешенными расширениями файлов для отображения
 * 
 * @return string Строка с расширениями через запятую
 */
function get_allowed_document_extensions()
{
   $types = get_allowed_document_types();
   return implode(', ', array_keys($types));
}

/**
 * Отображает содержимое мета-бокса для загрузки файлов
 * 
 * @param WP_Post $post Объект текущего поста
 * @return void
 */
function render_documents_file_meta_box($post)
{
   $file_url = get_post_meta($post->ID, '_document_file', true);
   $file_name = basename($file_url);
   $allowed_extensions = get_allowed_document_extensions();

   wp_nonce_field('save_document_file', 'document_file_nonce');

   echo '<div class="document-file-upload">';

   if ($file_url) {
      echo '<p>Текущий файл: <strong>' . esc_html($file_name) . '</strong></p>';
      echo '<p><a href="' . esc_url($file_url) . '" target="_blank">Просмотреть файл</a></p>';
      echo '<p><label><input type="checkbox" name="remove_document_file" value="1"> Удалить файл</label></p>';
   }

   echo '<input type="file" name="document_file" id="document_file" accept=".' . esc_attr(implode(',.', array_keys(get_allowed_document_types()))) . '">';
   echo '<p class="description">Загрузите файл документа (' . esc_html($allowed_extensions) . ')</p>';
   echo '</div>';
}

/**
 * Сохраняет мета-данные файла документа
 * 
 * @param int $post_id ID поста
 * @return void
 */
function save_documents_file_meta($post_id)
{
   if (
      !isset($_POST['document_file_nonce']) ||
      !wp_verify_nonce($_POST['document_file_nonce'], 'save_document_file')
   ) {
      return;
   }

   if (!current_user_can('edit_post', $post_id)) {
      return;
   }

   // Обработка удаления файла
   if (isset($_POST['remove_document_file']) && $_POST['remove_document_file'] == '1') {
      delete_post_meta($post_id, '_document_file');
      return;
   }

   // Обработка загрузки файла
   if (!empty($_FILES['document_file']['name'])) {
      $allowed_types = get_allowed_document_types();
      $file_type = wp_check_filetype($_FILES['document_file']['name']);

      if (!in_array($file_type['type'], $allowed_types)) {
         $allowed_extensions = get_allowed_document_extensions();
         wp_die('Недопустимый тип файла. Разрешены: ' . $allowed_extensions);
      }

      require_once(ABSPATH . 'wp-admin/includes/file.php');

      $upload = wp_handle_upload($_FILES['document_file'], array('test_form' => false));

      if (isset($upload['error'])) {
         wp_die($upload['error']);
      }

      update_post_meta($post_id, '_document_file', $upload['url']);
   }
}
add_action('save_post_documents', 'save_documents_file_meta');

/**
 * Добавляет стили и скрипты для работы с загрузкой файлов в админке
 * 
 * @return void
 */
function allow_file_upload_in_admin()
{
   echo '<style type="text/css">
        .document-file-upload input[type="file"] {
            margin: 10px 0;
            padding: 5px;
        }
        .document-file-upload .description {
            font-style: italic;
            color: #666;
            margin-top: 5px;
        }
    </style>';

   echo '<script type="text/javascript">
        jQuery(document).ready(function($) {
            $("form#post").attr("enctype", "multipart/form-data");
            $("form#post").attr("encoding", "multipart/form-data");
        });
    </script>';
}
add_action('admin_head', 'allow_file_upload_in_admin');

/**
 * Добавляет колонку с файлом в список документов
 * 
 * @param array $columns Массив колонок
 * @return array Измененный массив колонок
 */
function add_documents_file_column($columns)
{
   $columns['document_file'] = 'Файл';
   return $columns;
}
add_filter('manage_documents_posts_columns', 'add_documents_file_column');

/**
 * Заполняет колонку с файлом данными
 * 
 * @param string $column Название колонки
 * @param int $post_id ID поста
 * @return void
 */
function display_documents_file_column($column, $post_id)
{
   if ($column === 'document_file') {
      $file_url = get_post_meta($post_id, '_document_file', true);
      if ($file_url) {
         $file_name = basename($file_url);
         echo '<a href="' . esc_url($file_url) . '" target="_blank">' . esc_html($file_name) . '</a>';
      } else {
         echo '—';
      }
   }
}
add_action('manage_documents_posts_custom_column', 'display_documents_file_column', 10, 2);
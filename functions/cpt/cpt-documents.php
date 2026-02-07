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
      "supports" => ["title", "revisions", "author", "thumbnail"],
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
      'csv'  => 'text/csv',
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
   $file_meta = get_post_meta($post->ID, '_document_file', true);
   $file_url = is_numeric($file_meta) ? wp_get_attachment_url((int) $file_meta) : $file_meta;
   $file_name = $file_url ? basename($file_url) : '';
   $allowed_extensions = get_allowed_document_extensions();
   $is_spreadsheet = codeweber_document_is_spreadsheet($post->ID);

   wp_nonce_field('save_document_file', 'document_file_nonce');

   echo '<div class="document-file-upload">';

   if ($file_url) {
      echo '<p>Текущий файл: <strong>' . esc_html($file_name) . '</strong></p>';
      echo '<p><a href="' . esc_url($file_url) . '" target="_blank">Просмотреть файл</a></p>';
      echo '<p><label><input type="checkbox" name="remove_document_file" value="1"> Удалить файл</label></p>';
      if ($is_spreadsheet && $post->ID > 0) {
         $ext = $file_url ? strtolower(pathinfo($file_url, PATHINFO_EXTENSION)) : '';
         $writable = in_array($ext, ['csv', 'xlsx'], true);
         echo '<div class="codeweber-doc-edit-tabulator-wrap" style="margin-top:15px;" data-writable="' . ($writable ? '1' : '0') . '">';
         echo '<div class="codeweber-doc-edit-toolbar">';
         echo '<button type="button" class="button button-primary codeweber-doc-edit-save"' . ($writable ? '' : ' disabled title="' . esc_attr__('XLS доступен только для чтения. Используйте CSV или XLSX.', 'codeweber') . '"') . '><span class="dashicons dashicons-saved"></span> ' . esc_html__('Сохранить в файл', 'codeweber') . '</button> ';
         if ($writable) {
            echo ' <button type="button" class="button button-small codeweber-doc-edit-add-row"><span class="dashicons dashicons-plus-alt"></span> ' . esc_html__('Добавить строку', 'codeweber') . '</button> ';
            echo ' <button type="button" class="button button-small codeweber-doc-edit-add-col"><span class="dashicons dashicons-plus-alt2"></span> ' . esc_html__('Добавить колонку', 'codeweber') . '</button> ';
         }
         echo '<button type="button" class="button button-small codeweber-doc-edit-settings-toggle"><span class="dashicons dashicons-admin-generic"></span> ' . esc_html__('Настройки таблицы', 'codeweber') . '</button>';
         echo '<div class="codeweber-doc-edit-settings" style="display:none; margin-top:8px; padding:10px; background:#f6f7f7; border-radius:4px;">';
         echo '<label><input type="checkbox" class="codeweber-edit-tab-resizable" checked> ' . esc_html__('Изменять ширину колонок', 'codeweber') . '</label> ';
         echo '<label><input type="checkbox" class="codeweber-edit-tab-sortable" checked> ' . esc_html__('Сортировка', 'codeweber') . '</label> ';
         echo '<label><input type="number" class="codeweber-edit-tab-minwidth" value="80" min="40" max="500" style="width:55px;"> ' . esc_html__('мин. px', 'codeweber') . '</label> ';
         echo '<label><input type="number" class="codeweber-edit-tab-maxwidth" value="400" min="0" max="1000" style="width:55px;"> ' . esc_html__('макс. px', 'codeweber') . '</label> ';
         echo '<button type="button" class="button button-small codeweber-doc-edit-apply">' . esc_html__('Применить', 'codeweber') . '</button>';
         echo '</div></div>';
         echo '<div id="codeweber-doc-edit-tabulator" data-doc-id="' . (int) $post->ID . '" class="codeweber-doc-edit-tabulator"></div>';
         echo '</div>';
      }
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
      
      // Генерируем превью для PDF файлов и устанавливаем как featured image
      // Примечание: для новых постов превью будет создано через JavaScript при сохранении
      if ($file_type['type'] === 'application/pdf' && $post_id && $post_id > 0) {
         codeweber_generate_document_pdf_thumbnail($post_id, $upload['file']);
      }
   }
   
   // Проверяем, есть ли отложенное превью из localStorage (обрабатывается через AJAX)
   // Это обрабатывается в pdf-thumbnail-js.php через JavaScript
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
        .codeweber-doc-edit-tabulator { border: 1px solid #ddd; border-radius: 4px; overflow: hidden; }
   .codeweber-doc-edit-toolbar { margin-bottom: 8px; }
   .codeweber-doc-edit-settings label { margin-right: 12px; }
   .codeweber-tab-del-row { cursor: pointer; color: #a00; font-size: 18px; }
   .codeweber-tab-del-row:hover { color: #d00; }
   .tabulator .tabulator-header .tabulator-col .tabulator-col-content .tabulator-col-title .tabulator-title-editor { background: #666; color: #fff; }
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
 * Проверяет, является ли файл документа CSV/XLS/XLSX
 * 
 * @param int $post_id ID поста документа
 * @return bool
 */
function codeweber_document_is_spreadsheet($post_id)
{
   $file_meta = get_post_meta($post_id, '_document_file', true);
   if (!$file_meta) return false;
   $file_url = is_numeric($file_meta) ? wp_get_attachment_url((int) $file_meta) : $file_meta;
   if (!$file_url) return false;
   $ext = strtolower(pathinfo($file_url, PATHINFO_EXTENSION));
   return in_array($ext, ['csv', 'xls', 'xlsx'], true);
}

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
      $file_meta = get_post_meta($post_id, '_document_file', true);
      if ($file_meta) {
         $file_url = is_numeric($file_meta) ? wp_get_attachment_url((int) $file_meta) : $file_meta;
         $file_name = $file_url ? basename($file_url) : '';
         echo '<a href="' . esc_url($file_url) . '" target="_blank">' . esc_html($file_name) . '</a>';
         if (codeweber_document_is_spreadsheet($post_id)) {
            echo ' <a href="#" class="codeweber-doc-preview" data-doc-id="' . (int) $post_id . '" title="' . esc_attr__('Просмотр в таблице', 'codeweber') . '">[' . esc_html__('Таблица', 'codeweber') . ']</a>';
         }
      } else {
         echo '—';
      }
   }
}
add_action('manage_documents_posts_custom_column', 'display_documents_file_column', 10, 2);

/**
 * Подключает Tabulator на странице документов в админке
 * Требует активный плагин codeweber-gutenberg-blocks для REST API
 */
function codeweber_documents_enqueue_tabulator()
{
   $screen = get_current_screen();
   if (!$screen || $screen->post_type !== 'documents') {
      return;
   }
   if (!class_exists('Codeweber\Blocks\Plugin') || !defined('GUTENBERG_BLOCKS_URL')) {
      return;
   }
   $tabulator_version = '6.3.0';
   $tabulator_base = GUTENBERG_BLOCKS_URL . 'assets/vendor/tabulator/';
   wp_enqueue_style(
      'tabulator',
      $tabulator_base . 'tabulator_midnight.min.css',
      [],
      $tabulator_version
   );
   wp_enqueue_script(
      'tabulator',
      $tabulator_base . 'tabulator.min.js',
      [],
      $tabulator_version,
      true
   );
   wp_add_inline_script('tabulator', codeweber_documents_tabulator_script(), 'after');
}
add_action('admin_enqueue_scripts', 'codeweber_documents_enqueue_tabulator');

/**
 * Возвращает JS для Tabulator превью документов
 * 
 * @return string
 */
/**
 * Возвращает настройки Tabulator по умолчанию (можно переопределить через фильтр)
 *
 * Доступные опции:
 * - layout: 'fitColumns' | 'fitData' | 'fitDataFill' — режим раскладки колонок
 * - resizableColumnFit: bool — при изменении ширины колонки подстраивать соседнюю
 * - columnMinWidth: int — минимальная ширина колонки (px)
 * - columnMaxWidth: int — максимальная ширина колонки (px), 0 = без ограничения
 * - columnResizable: bool — разрешить изменять ширину колонок перетаскиванием
 * - movableColumns: bool — разрешить перетаскивание колонок
 * - sortable: bool — разрешить сортировку по клику на заголовок
 *
 * Пример в functions.php темы:
 * add_filter('codeweber_documents_tabulator_options', function($opts) {
 *    $opts['columnMinWidth'] = 100;
 *    $opts['columnMaxWidth'] = 600;
 *    return $opts;
 * });
 *
 * @return array
 */
function codeweber_documents_tabulator_default_options()
{
   $defaults = [
      'layout' => 'fitColumns',
      'resizableColumnFit' => true,
      'columnMinWidth' => 80,
      'columnMaxWidth' => 400,
      'columnResizable' => true,
      'movableColumns' => true,
      'sortable' => true,
   ];
   return apply_filters('codeweber_documents_tabulator_options', $defaults);
}

function codeweber_documents_tabulator_script()
{
   $api_root = esc_url(rest_url('codeweber-gutenberg-blocks/v1'));
   $nonce = wp_create_nonce('wp_rest');
   $opts = codeweber_documents_tabulator_default_options();
   $opts_json = wp_json_encode($opts);
   return <<<JS
(function() {
   var tabulatorOpts = {$opts_json};

   function getTabulatorOptsFromUI() {
      var resizable = document.getElementById('codeweber-tab-resizable');
      var sortable = document.getElementById('codeweber-tab-sortable');
      var movable = document.getElementById('codeweber-tab-movable');
      var minW = document.getElementById('codeweber-tab-minwidth');
      var maxW = document.getElementById('codeweber-tab-maxwidth');
      var opts = Object.assign({}, tabulatorOpts);
      if (resizable) opts.columnResizable = resizable.checked;
      if (sortable) opts.sortable = sortable.checked;
      if (movable) opts.movableColumns = movable.checked;
      if (minW) opts.columnMinWidth = parseInt(minW.value, 10) || 80;
      if (maxW) opts.columnMaxWidth = parseInt(maxW.value, 10);
      return opts;
   }

   function buildHeaderMenu(tableRef, colIndex) {
      return [
         {
            label: 'Удалить колонку',
            action: function(e, column) {
               var tbl = column.getTable();
               var cols = tbl.getColumns().filter(function(c) { var f = c.getField(); return f && f.indexOf('col') === 0; });
               if (cols.length <= 1) { alert('Нельзя удалить последнюю колонку'); return; }
               tbl.deleteColumn(column.getField());
            }
         }
      ];
   }

   function loadTableData(docId, containerEl, height, tableRef, optsOverride, editable) {
      if (!containerEl) return;
      var opts = optsOverride || tabulatorOpts;
      if (tableRef && tableRef.table) { tableRef.table.destroy(); tableRef.table = null; }
      if (tableRef) tableRef.headers = null;
      containerEl.innerHTML = '<div class="codeweber-tabulator-loading">Загрузка...</div>';
      fetch('{$api_root}/documents/' + docId + '/csv', {
         headers: { 'X-WP-Nonce': '{$nonce}' }
      })
      .then(function(r) { return r.json(); })
      .then(function(data) {
         containerEl.innerHTML = '';
         if (data.rows && data.rows.length > 0) {
            var headers = data.rows[0];
            var rows = data.rows.slice(1);
            var minW = opts.columnMinWidth || 80;
            var maxW = opts.columnMaxWidth;
            if (maxW === undefined) maxW = 400;
            var resizable = opts.columnResizable !== false;
            var dataColumns = headers.map(function(h, i) {
               var col = {
                  title: String(h || 'Col' + (i+1)),
                  field: 'col' + i,
                  minWidth: minW,
                  resizable: resizable,
                  headerFilter: 'input',
                  headerFilterPlaceholder: 'Поиск...'
               };
               if (maxW > 0) col.maxWidth = maxW;
               if (editable) {
                  col.editor = 'input';
                  col.editableTitle = true;
                  col.headerMenu = buildHeaderMenu(tableRef, i);
               }
               return col;
            });
            var columns = dataColumns.slice();
            if (editable) {
               columns.push({
                  title: '',
                  field: '_del',
                  width: 44,
                  minWidth: 44,
                  maxWidth: 44,
                  resizable: false,
                  sortable: false,
                  formatter: function() { return '<span class="dashicons dashicons-trash codeweber-tab-del-row" title="Удалить строку"></span>'; },
                  cellClick: function(e, cell) {
                     if (e.target.closest('.codeweber-tab-del-row')) { cell.getRow().delete(); }
                  }
               });
            }
            var tableData = rows.map(function(row) {
               var obj = {};
               row.forEach(function(cell, i) { obj['col' + i] = cell; });
               return obj;
            });
            if (typeof Tabulator !== 'undefined') {
               var config = {
                  data: tableData,
                  columns: columns,
                  layout: opts.layout || 'fitColumns',
                  height: height || '400px',
                  resizableColumnFit: opts.resizableColumnFit !== false,
                  movableColumns: opts.movableColumns !== false,
                  sortable: opts.sortable !== false
               };
               var t = new Tabulator(containerEl, config);
               if (tableRef) {
                  tableRef.table = t;
                  tableRef.headers = headers;
                  tableRef.docId = docId;
               }
            }
         } else {
            containerEl.innerHTML = '<p>Нет данных</p>';
         }
      })
      .catch(function() {
         containerEl.innerHTML = '<p class="codeweber-tabulator-error">Ошибка загрузки</p>';
      });
   }

   function saveTableToFile(tableRef) {
      if (!tableRef || !tableRef.table || !tableRef.docId) return;
      var table = tableRef.table;
      var docId = tableRef.docId;
      var cols = table.getColumns().filter(function(c) { var f = c.getField(); return f && f.indexOf('col') === 0; });
      var headers = cols.map(function(c) { return c.getDefinition().title || ''; });
      var fields = cols.map(function(c) { return c.getField(); });
      var dataRows = table.getData().map(function(row) {
         return fields.map(function(f) { return row[f] != null ? String(row[f]) : ''; });
      });
      var rows = [headers].concat(dataRows);
      var btn = document.querySelector('.codeweber-doc-edit-save');
      if (btn) { btn.disabled = true; btn.textContent = 'Сохранение...'; }
      fetch('{$api_root}/documents/' + docId + '/spreadsheet', {
         method: 'POST',
         headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': '{$nonce}'
         },
         body: JSON.stringify({ rows: rows })
      })
      .then(function(r) { return r.json(); })
      .then(function(res) {
         if (btn) { btn.disabled = false; btn.innerHTML = '<span class="dashicons dashicons-saved"></span> Сохранить в файл'; }
         if (res.success) {
            alert('Сохранено');
         } else {
            alert(res.message || 'Ошибка сохранения');
         }
      })
      .catch(function(err) {
         if (btn) { btn.disabled = false; btn.innerHTML = '<span class="dashicons dashicons-saved"></span> Сохранить в файл'; }
         alert('Ошибка: ' + (err.message || 'Не удалось сохранить'));
      });
   }

   function getEditPageOpts() {
      var wrap = document.querySelector('.codeweber-doc-edit-tabulator-wrap');
      if (!wrap) return tabulatorOpts;
      var r = wrap.querySelector('.codeweber-edit-tab-resizable');
      var s = wrap.querySelector('.codeweber-edit-tab-sortable');
      var minW = wrap.querySelector('.codeweber-edit-tab-minwidth');
      var maxW = wrap.querySelector('.codeweber-edit-tab-maxwidth');
      var opts = Object.assign({}, tabulatorOpts);
      if (r) opts.columnResizable = r.checked;
      if (s) opts.sortable = s.checked;
      opts.movableColumns = false;
      if (minW) opts.columnMinWidth = parseInt(minW.value, 10) || 80;
      if (maxW) opts.columnMaxWidth = parseInt(maxW.value, 10);
      return opts;
   }

   document.addEventListener('DOMContentLoaded', function() {
      var editTableEl = document.getElementById('codeweber-doc-edit-tabulator');
      var editWrap = document.querySelector('.codeweber-doc-edit-tabulator-wrap');
      var editTableRef = { table: null, headers: null, docId: null };
      if (editTableEl && editWrap) {
         var docId = editTableEl.getAttribute('data-doc-id');
         var toggleBtn = editWrap.querySelector('.codeweber-doc-edit-settings-toggle');
         var settingsDiv = editWrap.querySelector('.codeweber-doc-edit-settings');
         var applyBtn = editWrap.querySelector('.codeweber-doc-edit-apply');
         var saveBtn = editWrap.querySelector('.codeweber-doc-edit-save');
         if (toggleBtn && settingsDiv) {
            toggleBtn.addEventListener('click', function() {
               settingsDiv.style.display = settingsDiv.style.display === 'none' ? 'block' : 'none';
            });
         }
         if (saveBtn) {
            saveBtn.addEventListener('click', function() { saveTableToFile(editTableRef); });
         }
         var addRowBtn = editWrap.querySelector('.codeweber-doc-edit-add-row');
         if (addRowBtn) {
            addRowBtn.addEventListener('click', function() {
               if (editTableRef.table) {
                  var cols = editTableRef.table.getColumns().filter(function(c) { var f = c.getField(); return f && f.indexOf('col') === 0; });
                  var row = {};
                  cols.forEach(function(c) { row[c.getField()] = ''; });
                  editTableRef.table.addRow(row);
               }
            });
         }
         var addColBtn = editWrap.querySelector('.codeweber-doc-edit-add-col');
         if (addColBtn) {
            addColBtn.addEventListener('click', function() {
               if (editTableRef.table) {
                  var cols = editTableRef.table.getColumns().filter(function(c) { var f = c.getField(); return f && f.indexOf('col') === 0; });
                  var maxIdx = -1;
                  cols.forEach(function(c) {
                     var m = c.getField().match(/^col(\d+)$/);
                     if (m) maxIdx = Math.max(maxIdx, parseInt(m[1], 10));
                  });
                  var nextIdx = maxIdx + 1;
                  var opts = getEditPageOpts();
                  var minW = opts.columnMinWidth || 80;
                  var maxW = opts.columnMaxWidth;
                  if (maxW === undefined) maxW = 400;
                  var colDef = {
                     title: 'Col' + (nextIdx + 1),
                     field: 'col' + nextIdx,
                     minWidth: minW,
                     resizable: opts.columnResizable !== false,
                     editor: 'input',
                     editableTitle: true,
                     headerMenu: buildHeaderMenu(editTableRef, nextIdx),
                     headerFilter: 'input',
                     headerFilterPlaceholder: 'Поиск...'
                  };
                  if (maxW > 0) colDef.maxWidth = maxW;
                  editTableRef.table.addColumn(colDef, true, '_del');
               }
            });
         }
         if (applyBtn && docId) {
            applyBtn.addEventListener('click', function() {
               var opts = getEditPageOpts();
               loadTableData(docId, editTableEl, '300px', editTableRef, opts, true);
            });
         }
         if (docId) {
            var opts = getEditPageOpts();
            loadTableData(docId, editTableEl, '300px', editTableRef, opts, true);
         }
      }

      var modal = document.getElementById('codeweber-doc-tabulator-modal');
      if (!modal) return;
      var tableEl = document.getElementById('codeweber-doc-tabulator-table');
      var closeBtn = modal.querySelector('.codeweber-doc-modal-close');
      var settingsPanel = modal.querySelector('.codeweber-doc-tabulator-settings');
      var toolbarToggle = modal.querySelector('.codeweber-doc-toolbar-toggle');
      var applyBtn = modal.querySelector('.codeweber-doc-apply-settings');
      var modalTableRef = { table: null };
      var currentDocId = null;

      if (toolbarToggle && settingsPanel) {
         toolbarToggle.addEventListener('click', function() {
            settingsPanel.style.display = settingsPanel.style.display === 'none' ? 'flex' : 'none';
         });
      }
      if (applyBtn && tableEl) {
         applyBtn.addEventListener('click', function() {
            if (currentDocId) {
               var opts = getTabulatorOptsFromUI();
               loadTableData(currentDocId, tableEl, '400px', modalTableRef, opts);
            }
         });
      }

      document.body.addEventListener('click', function(e) {
         var link = e.target.closest('.codeweber-doc-preview');
         if (link) {
            e.preventDefault();
            var docId = link.getAttribute('data-doc-id');
            if (docId) {
               currentDocId = docId;
               modal.classList.add('active');
               document.body.classList.add('codeweber-modal-open');
               var opts = getTabulatorOptsFromUI();
               loadTableData(docId, tableEl, '400px', modalTableRef, opts);
            }
         }
         if (e.target === modal || (closeBtn && e.target === closeBtn)) {
            modal.classList.remove('active');
            document.body.classList.remove('codeweber-modal-open');
         }
      });
   });
})();
JS;
}

/**
 * Выводит модальное окно для превью таблицы документов
 */
function codeweber_documents_tabulator_modal()
{
   $screen = get_current_screen();
   if (!$screen || $screen->post_type !== 'documents') {
      return;
   }
   ?>
   <div id="codeweber-doc-tabulator-modal" class="codeweber-doc-tabulator-modal">
      <div class="codeweber-doc-tabulator-modal-content">
         <button type="button" class="codeweber-doc-modal-close" aria-label="<?php esc_attr_e('Закрыть', 'codeweber'); ?>">&times;</button>
         <div class="codeweber-doc-tabulator-toolbar">
            <button type="button" class="button codeweber-doc-toolbar-toggle" title="<?php esc_attr_e('Настройки таблицы', 'codeweber'); ?>">
               <span class="dashicons dashicons-admin-generic"></span> <?php esc_html_e('Настройки', 'codeweber'); ?>
            </button>
            <div class="codeweber-doc-tabulator-settings" style="display:none;">
               <label><input type="checkbox" id="codeweber-tab-resizable" checked> <?php esc_html_e('Изменять ширину колонок', 'codeweber'); ?></label>
               <label><input type="checkbox" id="codeweber-tab-sortable" checked> <?php esc_html_e('Сортировка', 'codeweber'); ?></label>
               <label><input type="checkbox" id="codeweber-tab-movable" checked> <?php esc_html_e('Перетаскивание колонок', 'codeweber'); ?></label>
               <label><?php esc_html_e('Мин. ширина:', 'codeweber'); ?> <input type="number" id="codeweber-tab-minwidth" value="80" min="40" max="500" style="width:60px;"> px</label>
               <label><?php esc_html_e('Макс. ширина:', 'codeweber'); ?> <input type="number" id="codeweber-tab-maxwidth" value="400" min="0" max="1000" style="width:60px;"> px</label>
               <button type="button" class="button button-small codeweber-doc-apply-settings"><?php esc_html_e('Применить', 'codeweber'); ?></button>
            </div>
         </div>
         <div id="codeweber-doc-tabulator-table" class="codeweber-doc-tabulator-table"></div>
      </div>
   </div>
   <style>
   .codeweber-doc-tabulator-modal { display:none; position:fixed; z-index:100000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.6); }
   .codeweber-doc-tabulator-modal.active { display:flex; align-items:center; justify-content:center; }
   .codeweber-doc-tabulator-modal-content { position:relative; background:#fff; padding:20px; border-radius:8px; max-width:95%; max-height:90vh; overflow:auto; box-shadow:0 4px 20px rgba(0,0,0,0.3); }
   .codeweber-doc-modal-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; padding-bottom:12px; border-bottom:1px solid #ddd; }
   .codeweber-doc-modal-close { font-size:28px; background:none; border:none; cursor:pointer; color:#666; line-height:1; padding:0 8px; }
   .codeweber-doc-modal-close:hover { color:#000; }
   .codeweber-doc-tabulator-toolbar { margin-bottom:12px; }
   .codeweber-doc-toolbar-toggle { display:inline-flex; align-items:center; gap:4px; }
   .codeweber-doc-tabulator-settings { margin-top:10px; padding:10px; background:#f6f7f7; border-radius:4px; display:flex; flex-wrap:wrap; gap:12px 20px; align-items:center; }
   .codeweber-doc-tabulator-settings label { display:inline-flex; align-items:center; gap:4px; white-space:nowrap; }
   .codeweber-doc-tabulator-table { min-width:400px; min-height:200px; }
   .codeweber-tabulator-loading, .codeweber-tabulator-error { padding:20px; text-align:center; color:#666; }
   body.codeweber-modal-open { overflow:hidden; }
   </style>
   <?php
}
add_action('admin_footer', 'codeweber_documents_tabulator_modal');

/**
 * Регистрирует метаполе _document_file в REST API для типа записи documents
 */
function register_document_file_rest_field() {
	register_rest_field('documents', '_document_file', [
		'get_callback' => function($post) {
			return get_post_meta($post['id'], '_document_file', true);
		},
		'schema' => [
			'type' => 'string',
			'context' => ['view', 'edit'],
		],
	]);
}
add_action('rest_api_init', 'register_document_file_rest_field');

/**
 * REST API endpoint для получения URL файла документа для AJAX загрузки
 */
function register_document_download_endpoint() {
	register_rest_route('codeweber/v1', '/documents/(?P<id>\d+)/download-url', [
		'methods' => 'GET',
		'callback' => 'get_document_download_url',
		'permission_callback' => '__return_true',
		'args' => [
			'id' => [
				'required' => true,
				'type' => 'integer',
				'validate_callback' => function($param) {
					return is_numeric($param);
				}
			]
		]
	]);
}
add_action('rest_api_init', 'register_document_download_endpoint');

/**
 * REST API endpoint для модального окна отправки документа на email
 */
function register_document_email_modal_endpoint() {
	register_rest_route('wp/v2', '/modal/doc-(?P<id>\d+)', [
		'methods' => 'GET',
		'callback' => 'get_document_email_modal',
		'permission_callback' => '__return_true',
		'args' => [
			'id' => [
				'required' => true,
				'type' => 'integer',
				'validate_callback' => function($param) {
					return is_numeric($param);
				}
			]
		]
	]);
}
add_action('rest_api_init', 'register_document_email_modal_endpoint');

/**
 * Callback для получения HTML модального окна отправки документа на email
 * 
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function get_document_email_modal($request) {
	$post_id = $request->get_param('id');
	
	// Проверяем, что пост существует и это documents
	$post = get_post($post_id);
	if (!$post || $post->post_type !== 'documents') {
		return new WP_Error(
			'invalid_post',
			__('Document not found', 'codeweber'),
			['status' => 404]
		);
	}
	
	// Получаем URL файла
	$file_url = get_post_meta($post_id, '_document_file', true);
	
	if (empty($file_url)) {
		return new WP_Error(
			'no_file',
			__('File not found for this document', 'codeweber'),
			['status' => 404]
		);
	}
	
	$file_name = basename($file_url);
	$document_title = get_the_title($post_id);
	
	ob_start();
	?>
	<div class="document-email-form text-start">
		<h5 class="modal-title mb-4"><?php esc_html_e('Send Document by Email', 'codeweber'); ?></h5>
		<form id="document-email-form" class="document-email-form">
			<input type="hidden" name="document_id" value="<?php echo esc_attr($post_id); ?>">
			
			<div class="mb-4">
				<p class="mb-2"><strong><?php esc_html_e('Document:', 'codeweber'); ?></strong> <?php echo esc_html($document_title); ?></p>
				<p class="text-muted small mb-0"><?php echo esc_html($file_name); ?></p>
			</div>
			
			<div class="form-floating mb-4">
				<input 
					type="email" 
					class="form-control<?php echo getThemeFormRadius(); ?>" 
					name="email" 
					id="document_email" 
					placeholder="<?php esc_attr_e('Your email', 'codeweber'); ?>"
					required
				>
				<label for="document_email"><?php esc_html_e('Your Email *', 'codeweber'); ?></label>
			</div>
			
			<div class="modal-footer text-center justify-content-center mt-4 pt-0 pb-0">
				<button type="submit" class="btn btn-primary<?php echo getThemeButton(); ?>" data-loading-text="<?php esc_attr_e('Sending...', 'codeweber'); ?>">
					<?php esc_html_e('Send Document', 'codeweber'); ?>
				</button>
			</div>
		</form>
	</div>
	<?php
	$form_html = ob_get_clean();
	
	return rest_ensure_response([
		'id' => $post_id,
		'content' => [
			'rendered' => $form_html
		],
		'modal_size' => 'modal-md'
	]);
}

/**
 * REST API endpoint для отправки документа на email
 */
function register_document_email_send_endpoint() {
	register_rest_route('codeweber/v1', '/documents/send-email', [
		'methods' => 'POST',
		'callback' => 'send_document_email',
		'permission_callback' => '__return_true',
		'args' => [
			'document_id' => [
				'required' => true,
				'type' => 'integer',
				'validate_callback' => function($param) {
					return is_numeric($param);
				}
			],
			'email' => [
				'required' => true,
				'sanitize_callback' => 'sanitize_email',
				'validate_callback' => function($param) {
					return is_email($param);
				}
			]
		]
	]);
}
add_action('rest_api_init', 'register_document_email_send_endpoint');

/**
 * Callback для отправки документа на email
 * 
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function send_document_email($request) {
	// Загружаем переводы темы для REST API контекста
	// В REST API контексте переводы могут не загружаться автоматически
	$locale = get_locale();
	if (!$locale || $locale === 'en_US') {
		$locale = get_option('WPLANG') ?: 'ru_RU';
	}
	
	// Принудительно устанавливаем локаль
	if (function_exists('switch_to_locale') && $locale && $locale !== 'en_US') {
		switch_to_locale($locale);
	}
	
	// Принудительно загружаем переводы
	unload_textdomain('codeweber');
	load_theme_textdomain('codeweber', get_template_directory() . '/languages');
	
	// Принудительно загружаем .mo файл
	$mofile = get_template_directory() . '/languages/' . $locale . '.mo';
	if (file_exists($mofile)) {
		load_textdomain('codeweber', $mofile);
	}
	
	// Также пробуем загрузить ru_RU если локаль не совпадает
	if ($locale !== 'ru_RU') {
		$ru_mofile = get_template_directory() . '/languages/ru_RU.mo';
		if (file_exists($ru_mofile)) {
			load_textdomain('codeweber', $ru_mofile);
		}
	}
	
	// Проверка REST API nonce из заголовка
	$nonce = $request->get_header('X-WP-Nonce');
	
	// Проверяем REST API nonce
	if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
		return new WP_Error(
			'invalid_nonce',
			__('Security check failed.', 'codeweber'),
			['status' => 403]
		);
	}
	
	$post_id = $request->get_param('document_id');
	$email = $request->get_param('email');
	
	// Проверяем документ
	$post = get_post($post_id);
	if (!$post || $post->post_type !== 'documents') {
		return new WP_Error(
			'invalid_post',
			__('Document not found', 'codeweber'),
			['status' => 404]
		);
	}
	
	// Получаем URL файла
	$file_url = get_post_meta($post_id, '_document_file', true);
	if (empty($file_url)) {
		return new WP_Error(
			'no_file',
			__('File not found for this document', 'codeweber'),
			['status' => 404]
		);
	}
	
	$file_name = basename($file_url);
	$document_title = get_the_title($post_id);
	
	// Получаем путь к файлу для вложения
	$upload_dir = wp_upload_dir();
	$file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $file_url);
	
	// Проверяем, существует ли файл
	if (!file_exists($file_path)) {
		// Пробуем альтернативный способ получения пути
		$file_path = get_attached_file(get_post_meta($post_id, '_document_file_id', true));
		if (!$file_path || !file_exists($file_path)) {
			// Если файл не найден, отправляем ссылку на файл
			$file_path = null;
		}
	}
	
	// Отправляем email с вложением файла
	// Используем прямые переводы для гарантии русского языка
	$locale = get_locale();
	$is_russian = ($locale === 'ru_RU' || strpos($locale, 'ru') === 0);
	
	if ($is_russian) {
		$subject = sprintf('Документ: %s', $document_title);
		$email_message = sprintf(
			"Здравствуйте,\n\nВы запросили получить документ: %s\n\n",
			$document_title
		);
		
		if ($file_path && file_exists($file_path)) {
			$email_message .= "Пожалуйста, найдите документ во вложении к этому письму.\n\n";
		} else {
			$email_message .= "Ссылка для скачивания:\n" . $file_url . "\n\n";
		}
		
		$email_message .= "С уважением";
	} else {
		// Английская версия для других языков
		$subject = sprintf(__('Document: %s', 'codeweber'), $document_title);
		$email_message = sprintf(
			__("Hello,\n\nYou requested to receive the document: %s\n\n", 'codeweber'),
			$document_title
		);
		
		if ($file_path && file_exists($file_path)) {
			$email_message .= __("Please find the document attached to this email.\n\n", 'codeweber');
		} else {
			$email_message .= __("Download link:", 'codeweber') . "\n" . $file_url . "\n\n";
		}
		
		$email_message .= __("Best regards", 'codeweber');
	}
	
	$headers = array('Content-Type: text/html; charset=UTF-8');
	
	// Если файл существует, добавляем его как вложение
	$attachments = array();
	if ($file_path && file_exists($file_path)) {
		$attachments[] = $file_path;
	}
	
	// Отправляем email
	$sent = wp_mail($email, $subject, nl2br(esc_html($email_message)), $headers, $attachments);
	
	// Логируем результат для отладки
	if (!$sent) {
		error_log('Document email send failed. Post ID: ' . $post_id . ', Email: ' . $email);
	}
	
	if ($sent) {
		return new WP_REST_Response([
			'success' => true,
			'message' => __('Document sent successfully to your email.', 'codeweber')
		], 200);
	} else {
		return new WP_Error(
			'email_failed',
			__('Failed to send email. Please try again.', 'codeweber'),
			['status' => 500]
		);
	}
}

/**
 * Callback для получения URL файла документа
 * 
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function get_document_download_url($request) {
	$post_id = $request->get_param('id');
	
	// Проверяем, что пост существует и это documents
	$post = get_post($post_id);
	if (!$post || $post->post_type !== 'documents') {
		return new WP_Error(
			'invalid_post',
			__('Document not found', 'codeweber'),
			['status' => 404]
		);
	}
	
	// Получаем URL файла
	$file_url = get_post_meta($post_id, '_document_file', true);
	
	if (empty($file_url)) {
		return new WP_Error(
			'no_file',
			__('File not found for this document', 'codeweber'),
			['status' => 404]
		);
	}
	
	// Опционально: логирование загрузки
	do_action('document_downloaded', $post_id);
	
	return new WP_REST_Response([
		'success' => true,
		'file_url' => esc_url_raw($file_url),
		'file_name' => basename($file_url),
		'post_id' => $post_id
	], 200);
}

/**
 * Генерирует превью PDF файла и устанавливает его как featured image для документа
 * 
 * @param int $post_id ID поста документа
 * @param string $pdf_file_path Полный путь к загруженному PDF файлу
 * @return int|false ID вложения превью или false при ошибке
 */
function codeweber_generate_document_pdf_thumbnail($post_id, $pdf_file_path)
{
   if (!file_exists($pdf_file_path)) {
      return false;
   }
   
   // Проверяем, что это PDF
   $file_type = wp_check_filetype($pdf_file_path);
   if ($file_type['type'] !== 'application/pdf') {
      return false;
   }
   
   // Генерируем превью используя функцию из pdf-thumbnail.php
   if (!function_exists('codeweber_generate_pdf_thumbnail')) {
      return false;
   }
   
   // Создаем превью среднего размера
   $thumbnail_url = codeweber_generate_pdf_thumbnail($pdf_file_path, 'jpg', 90, 800, 0);
   
   if (!$thumbnail_url) {
      return false;
   }
   
   // Получаем путь к файлу превью
   $upload_dir = wp_upload_dir();
   $thumbnail_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $thumbnail_url);
   
   if (!file_exists($thumbnail_path)) {
      return false;
   }
   
   // Создаем вложение для превью
   $file_name = basename($thumbnail_path);
   $file_type = wp_check_filetype($file_name, null);
   
   $attachment = array(
      'guid'           => $thumbnail_url,
      'post_mime_type' => $file_type['type'],
      'post_title'     => get_the_title($post_id) . ' - PDF Preview',
      'post_content'   => '',
      'post_status'    => 'inherit'
   );
   
   // Вставляем вложение в базу данных
   $attachment_id = wp_insert_attachment($attachment, $thumbnail_path, $post_id);
   
   if (is_wp_error($attachment_id)) {
      return false;
   }
   
   // Генерируем метаданные для вложения
   require_once(ABSPATH . 'wp-admin/includes/image.php');
   $attachment_data = wp_generate_attachment_metadata($attachment_id, $thumbnail_path);
   wp_update_attachment_metadata($attachment_id, $attachment_data);
   
   // Устанавливаем превью как featured image
   set_post_thumbnail($post_id, $attachment_id);
   
   return $attachment_id;
}

/**
 * Отключаем single Documents страницы - возвращаем 404
 */
add_action('template_redirect', function() {
	if (is_singular('documents')) {
		global $wp_query;
		$wp_query->set_404();
		status_header(404);
	}
});
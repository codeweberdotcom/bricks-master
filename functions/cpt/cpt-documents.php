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
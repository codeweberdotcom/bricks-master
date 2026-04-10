<?php

/**
 * https://developer.wordpress.org/reference/functions/add_image_size/
 * add_image_size( $name:string, $width:integer, $height:integer, $crop:boolean|array )
 * array( 'x_crop_position', 'y_crop_position' )
 * x_crop_position > left center right
 * y_crop_position > top center bottom
 */

if (! function_exists('codeweber_image_settings')) {
	function codeweber_image_settings()
	{
		//CPT Projects
		add_image_size('codeweber_project_900-900', 900, 900, true);
		add_image_size('codeweber_project_900-718', 900, 718, true);
		add_image_size('codeweber_project_900-800', 900, 800, true);

		add_image_size('codeweber_staff', 400, 400, true);

		//CPT Vacancies (600x400 - пропорционально увеличенный размер 382x255)
		//CPT Vacancies
		add_image_size('codeweber_vacancy_1070-668', 1070, 668, true); // Single main
		add_image_size('codeweber_vacancy_383-250',  383,  250, true); // Sidebar image
		add_image_size('codeweber_vacancy_400-267',  400,  267, true); // Archive card
		add_image_size('codeweber_vacancy_600-600',  600,  600, true); // Square

		// CPT Events
		add_image_size('codeweber_event_1070-668', 1070, 668, true);
		add_image_size('codeweber_event_140-88',   140, 88,  true); // Swiper gallery thumbs
		add_image_size('codeweber_event_400-267', 400, 267, true); // Archive card
		add_image_size('codeweber_event_383-250', 383, 250, true); // Sidebar image
		add_image_size('codeweber_event_600-600',  600, 600, true); // Square

		//CPT Clients
		add_image_size('codeweber_clients_115-60', 115, 60, false);
		add_image_size('codeweber_clients_200-60', 200, 60, false);
		add_image_size('codeweber_clients_300-200', 300, 200, false);
		add_image_size('codeweber_clients_400-267', 400, 267, false);

		// Post (blog)
		add_image_size('codeweber_post_960-600', 960, 600, true);
		add_image_size('codeweber_post_600-600', 600, 600, true);
		add_image_size('codeweber_post_560-350', 560, 350, true);
		add_image_size('codeweber_post_100-100', 100, 100, true);

		
		

		//Universal
		add_image_size('codeweber_extralarge', 1600, 1200, true); 
		add_image_size('codeweber_avatar',     200,  200,  true); // Global: avatar/organizer (1:1)
		//add_image_size('codeweber_big', 1400, 800, true );
		//add_image_size('codeweber_square', 400, 400, true );
		//add_image_size( 'codeweber_single', 800, 500, true );

	}
}
add_action('after_setup_theme', 'codeweber_image_settings');


// --- Set image compression value ---
// https://developer.wordpress.org/reference/hooks/jpeg_quality/

function codeweber_image_quality()
{
	return 80;
}
add_filter('jpeg_quality', 'codeweber_image_quality');


/**
 * Универсальная функция для получения разрешённых размеров изображений с кэшированием
 * Использует WordPress Object Cache (wp_cache_*) для кроссзапросной кэширования.
 *
 * @param string $post_type Тип записи
 * @param int $post_id ID записи (опционально)
 * @return array Массив разрешённых размеров
 */
function codeweber_get_allowed_image_sizes($post_type = '', $post_id = 0)
{
	$cache_key = 'allowed_sizes_' . md5($post_type . '_' . $post_id);
	$cache_group = 'codeweber_image_sizes';

	// Проверяем кэш (работает и в памяти, и в Redis/Memcached)
	$cached_result = wp_cache_get($cache_key, $cache_group);
	if ($cached_result !== false) {
		return $cached_result;
	}

	// Базовые настройки размеров по типам записей
	$default_sizes = [
		'projects' => ['thumbnail', 'codeweber_project_900-900', 'codeweber_project_900-718', 'codeweber_extralarge', 'codeweber_project_900-800', 'woocommerce_gallery_thumbnail'],
		'staff' => ['thumbnail', 'codeweber_staff', 'woocommerce_gallery_thumbnail'],
		'vacancies' => ['thumbnail', 'codeweber_vacancy_1070-668', 'codeweber_vacancy_383-250', 'codeweber_vacancy_400-267', 'codeweber_vacancy_600-600', 'codeweber_avatar', 'woocommerce_gallery_thumbnail'],
		'events'    => ['thumbnail', 'codeweber_event_1070-668', 'codeweber_event_400-267', 'codeweber_event_140-88', 'codeweber_event_383-250', 'codeweber_event_600-600', 'codeweber_avatar'],
		'clients' => ['thumbnail', 'codeweber_clients_115-60', 'codeweber_clients_200-60', 'codeweber_clients_300-200', 'codeweber_clients_400-267', 'woocommerce_gallery_thumbnail'],
		'post' => ['thumbnail', 'codeweber_post_960-600', 'codeweber_post_600-600', 'codeweber_post_560-350', 'codeweber_post_100-100', 'codeweber_extralarge', 'woocommerce_gallery_thumbnail'],
		'product' => ['thumbnail', 'woocommerce_thumbnail', 'woocommerce_single', 'codeweber_extralarge', 'codeweber_post_100-100'],
		'default' => [] // По умолчанию пустой массив - не удаляем никакие размеры
	];

	// Фильтр для изменения базовых настроек
	$sizes = apply_filters('codeweber_allowed_image_sizes', $default_sizes, $post_type, $post_id);

	// Определяем какие размеры использовать
	$result = [];
	if ($post_type && isset($sizes[$post_type])) {
		$result = apply_filters("codeweber_allowed_image_sizes_{$post_type}", $sizes[$post_type], $post_id);
	} else {
		$result = apply_filters("codeweber_allowed_image_sizes_default", $sizes['default'], $post_id);
	}

	// Сохраняем в кэш с TTL 1 час
	wp_cache_set($cache_key, $result, $cache_group, HOUR_IN_SECONDS);
	return $result;
}

/**
 * Безопасное удаление файла с проверкой прав доступа
 * 
 * @param string $file_path Путь к файлу
 * @return bool Успешность операции
 */
function codeweber_safe_file_delete($file_path)
{
	// Проверяем что файл существует и доступен для записи
	if (!file_exists($file_path) || !is_writable($file_path)) {
		return false;
	}

	// Дополнительная проверка безопасности - убеждаемся что файл находится в uploads директории
	$upload_dir = wp_upload_dir();
	$upload_basedir = $upload_dir['basedir'];

	if (strpos($file_path, $upload_basedir) !== 0) {
		return false;
	}

	// Проверяем права пользователя
	if (!current_user_can('upload_files')) {
		return false;
	}

	return unlink($file_path);
}

/**
 * Получает ID родительского поста для вложения с использованием транзиентов для временного хранения
 * 
 * @param int $attachment_id ID вложения
 * @return int ID родительского поста или 0 если не найден
 */
function codeweber_get_attachment_parent_id($attachment_id)
{
	$parent_id = get_post_field('post_parent', $attachment_id);

	if ($parent_id) {
		return $parent_id;
	}

	// Проверяем временное хранилище для загрузок через Redux
	$temp_parent_id = get_transient('codeweber_current_upload_parent');
	if ($temp_parent_id) {
		// Устанавливаем родителя и очищаем временное значение
		wp_update_post([
			'ID' => $attachment_id,
			'post_parent' => $temp_parent_id
		]);
		delete_transient('codeweber_current_upload_parent');
		return $temp_parent_id;
	}

	// Альтернативные методы определения родителя (с проверкой безопасности)
	if (!empty($_REQUEST['post_id']) && is_numeric($_REQUEST['post_id'])) {
		$request_parent_id = intval($_REQUEST['post_id']);
		if (get_post($request_parent_id)) { // Проверяем что пост существует
			wp_update_post([
				'ID' => $attachment_id,
				'post_parent' => $request_parent_id
			]);
			return $request_parent_id;
		}
	}

	return 0;
}

/**
 * Фильтрует созданные размеры изображений по типу родительской записи.
 * Оставляет только разрешённые размеры для каждого типа записи.
 *
 * @param array $metadata      Метаданные вложения (изображения).
 * @param int   $attachment_id ID вложения.
 * @return array Отфильтрованные метаданные.
 */
function codeweber_filter_attachment_sizes_by_post_type($metadata, $attachment_id)
{
	if (empty($metadata['sizes']) || !is_array($metadata['sizes'])) {
		return $metadata;
	}

	$parent_id = codeweber_get_attachment_parent_id($attachment_id);

	// Если нет родителя, выходим
	if (!$parent_id) {
		return $metadata;
	}

	// Определяем тип записи
	$parent_type = get_post_type($parent_id);

	// Специальная обработка для WooCommerce
	if (!$parent_type && function_exists('wc_get_product')) {
		$product = wc_get_product($parent_id);
		if ($product) {
			$parent_type = 'product';
		}
	}

	// Если тип записи не определен, используем по умолчанию
	if (!$parent_type) {
		$parent_type = 'default';
	}

	// Получаем разрешённые размеры через универсальную функцию
	$allowed_sizes = codeweber_get_allowed_image_sizes($parent_type, $parent_id);

	// Фильтрация размеров изображений (только если есть разрешённые размеры)
	if (!empty($allowed_sizes)) {
		$upload_dir = wp_upload_dir();
		$base_dir   = $upload_dir['basedir'] . '/' . dirname($metadata['file']);

		// Список файлов, используемых разрешёнными размерами — их нельзя удалять.
		// Разные CPT могут регистрировать одинаковые размеры (например, codeweber_event_600-600
		// и codeweber_post_600-600 оба генерируют *-600x600.jpg). Удаление неразрешённого
		// размера иначе уничтожит файл, который нужен разрешённому.
		$protected_files = [];
		foreach ($metadata['sizes'] as $size_name => $size_info) {
			if (in_array($size_name, $allowed_sizes, true)) {
				$protected_files[] = $size_info['file'];
			}
		}

		foreach ($metadata['sizes'] as $size_name => $size_info) {
			if (!in_array($size_name, $allowed_sizes, true)) {
				// Удаляем файл только если ни один разрешённый размер его не использует
				if (!in_array($size_info['file'], $protected_files, true)) {
					codeweber_safe_file_delete( path_join($base_dir, $size_info['file']) );
				}
				unset($metadata['sizes'][$size_name]);
			}
		}
	}

	return $metadata;
}
add_filter('wp_generate_attachment_metadata', 'codeweber_filter_attachment_sizes_by_post_type', 10, 2);

/**
 * Устанавливает временного родителя для загрузок через Redux
 */
add_filter('redux/metaboxes/upload/prefilter', 'codeweber_set_parent_for_redux_uploads', 10, 3);
function codeweber_set_parent_for_redux_uploads($file, $field_id, $redux)
{
	// Получаем ID текущего поста безопасным способом
	global $post;

	if (!empty($post->ID) && current_user_can('edit_post', $post->ID)) {
		// Сохраняем ID поста во временном хранилище на 5 минут
		set_transient('codeweber_current_upload_parent', $post->ID, 5 * MINUTE_IN_SECONDS);
	}

	return $file;
}

/**
 * Обрабатывает установку родителя для вложения через стандартный механизм WordPress
 */
add_action('add_attachment', 'codeweber_set_attachment_parent_on_upload');
function codeweber_set_attachment_parent_on_upload($attachment_id)
{
	$temp_parent_id = get_transient('codeweber_current_upload_parent');

	if ($temp_parent_id && current_user_can('edit_post', $temp_parent_id)) {
		wp_update_post([
			'ID' => $attachment_id,
			'post_parent' => $temp_parent_id
		]);
		delete_transient('codeweber_current_upload_parent');
	}
}

/**
 * Резервный метод для установки родителя (сохраняет совместимость)
 */
add_filter('wp_insert_attachment_data', 'codeweber_force_attachment_parent_before_upload', 10, 2);
function codeweber_force_attachment_parent_before_upload($data, $postarr)
{
	// Используем только как резервный метод
	$temp_parent_id = get_transient('codeweber_current_upload_parent');

	if ($temp_parent_id && empty($data['post_parent']) && current_user_can('edit_post', $temp_parent_id)) {
		$data['post_parent'] = $temp_parent_id;
	}

	return $data;
}

// ── SVG ───────────────────────────────────────────────────────────────────────

/**
 * Разрешает загрузку файлов форматов SVG и SVGZ в WordPress.
 *
 * По умолчанию WordPress запрещает загрузку SVG из соображений безопасности.
 * Эта функция добавляет поддержку MIME-типов для SVG и SVGZ.
 *
 * @param array $mimes Массив разрешенных типов файлов.
 * @return array Обновленный массив MIME-типов с добавленной поддержкой SVG.
 */
function codeweber_svg_upload($mimes)
{
	$mimes['svg']  = 'image/svg+xml';
	$mimes['svgz'] = 'image/svg+xml';

	return $mimes;
}
add_filter('upload_mimes', 'codeweber_svg_upload');


/**
 * Устанавливает корректный MIME-тип для SVG-файлов.
 *
 * WordPress по умолчанию блокирует загрузку SVG из соображений безопасности.
 * Эта функция исправляет MIME-тип, чтобы разрешить загрузку SVG и SVGZ файлов.
 *
 * @param array|null  $data     Данные о файле (тип, расширение).
 * @param string|null $file     Полный путь к файлу (необязательно).
 * @param string|null $filename Имя файла.
 * @param array|null  $mimes    Список разрешенных MIME-типов.
 * @return array|null Массив данных о файле с исправленным MIME-типом.
 */
function codeweber_svg_mimetype($data = null, $file = null, $filename = null, $mimes = null)
{
	$ext = isset($data['ext']) ? $data['ext'] : '';
	if (strlen($ext) < 1) {
		$exploded = explode('.', $filename);
		$ext      = strtolower(end($exploded));
	}
	if ('svg' === $ext) {
		$data['type'] = 'image/svg+xml';
		$data['ext']  = 'svg';
	} elseif ('svgz' === $ext) {
		$data['type'] = 'image/svg+xml';
		$data['ext']  = 'svgz';
	}

	return $data;
}
add_filter('wp_check_filetype_and_ext', 'codeweber_svg_mimetype', 10, 4);

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
		

		//add_image_size('codeweber_big', 1400, 800, true );
		//add_image_size('codeweber_square', 400, 400, true );
		//add_image_size( 'codeweber_single', 800, 500, true );
		//add_image_size('codeweber_extralarge', 1600, 1200, true); // Без обрезки, до 1600px

		remove_image_size('large');
		remove_image_size('thumbnail');
		remove_image_size('medium');
		remove_image_size('medium_large');
		remove_image_size('1536x1536');
		remove_image_size('2048x2048');
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
 * Универсальная функция для получения разрешённых размеров изображений
 * 
 * @param string $post_type Тип записи
 * @param int $post_id ID записи (опционально)
 * @return array Массив разрешённых размеров
 */
function codeweber_get_allowed_image_sizes($post_type = '', $post_id = 0)
{
	// Базовые настройки размеров по типам записей (сохраняем ваши оригинальные настройки)
	$default_sizes = [
		'projects' => ['codeweber_project_900-900', 'codeweber_project_900-718', 'codeweber_project_900-800', 'woocommerce_gallery_thumbnail'],
		'staff' => ['codeweber_staff', 'woocommerce_gallery_thumbnail'],
		'default' => [] // По умолчанию пустой массив - не удаляем никакие размеры
	];

	// Фильтр для изменения базовых настроек
	$sizes = apply_filters('codeweber_allowed_image_sizes', $default_sizes, $post_type, $post_id);

	// Определяем какие размеры использовать
	if ($post_type && isset($sizes[$post_type])) {
		return apply_filters("codeweber_allowed_image_sizes_{$post_type}", $sizes[$post_type], $post_id);
	}

	return apply_filters("codeweber_allowed_image_sizes_default", $sizes['default'], $post_id);
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
	$parent_id = get_post_field('post_parent', $attachment_id);

	// Проверяем разные способы определения родителя
	if (!$parent_id && !empty($_REQUEST['post_id'])) {
		$parent_id = intval($_REQUEST['post_id']);
		wp_update_post([
			'ID' => $attachment_id,
			'post_parent' => $parent_id
		]);
	}

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

	// Получаем разрешённые размеры через универсальную функцию
	$allowed_sizes = codeweber_get_allowed_image_sizes($parent_type, $parent_id);

	// Фильтрация размеров изображений (только если есть разрешённые размеры)
	if (!empty($allowed_sizes) && !empty($metadata['sizes'])) {
		$upload_dir = wp_upload_dir();

		foreach ($metadata['sizes'] as $size_name => $size_info) {
			if (!in_array($size_name, $allowed_sizes, true)) {
				$file_path = path_join($upload_dir['basedir'], dirname($metadata['file']) . '/' . $size_info['file']);

				// Безопасное удаление файла
				if (file_exists($file_path) && is_writable($file_path)) {
					unlink($file_path);
				}

				unset($metadata['sizes'][$size_name]);
			}
		}
	}

	return $metadata;
}
add_filter('wp_generate_attachment_metadata', 'codeweber_filter_attachment_sizes_by_post_type', 10, 2);


add_filter('redux/metaboxes/upload/prefilter', 'codeweber_set_parent_for_redux_uploads', 10, 3);
function codeweber_set_parent_for_redux_uploads($file, $field_id, $redux)
{
	// Получаем ID текущего поста из глобальной переменной
	global $post;
	if (!empty($post->ID)) {
		$_POST['post_id'] = $post->ID; // Передаём post_id в обработчик
	}
	return $file;
}

add_filter('wp_insert_attachment_data', 'codeweber_force_attachment_parent_before_upload', 10, 2);
function codeweber_force_attachment_parent_before_upload($data, $postarr)
{
	// Если загружается через Redux и есть post_id
	if (!empty($_POST['post_id'])) {
		$data['post_parent'] = intval($_POST['post_id']);
	}
	return $data;
}

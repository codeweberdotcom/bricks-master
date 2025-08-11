<?php

/**
 * https://developer.wordpress.org/reference/functions/add_image_size/
 * add_image_size( $name:string, $width:integer, $height:integer, $crop:boolean|array )
 * array( 'x_crop_position', 'y_crop_position' )
 * x_crop_position > left center right
 * y_crop_position > top center bottom
 */

if ( ! function_exists( 'codeweber_image_settings' ) ) {
	function codeweber_image_settings() {
		//CPT Projects
		add_image_size('codeweber_project_900-900', 900, 900, true );
		add_image_size('codeweber_project_900-718', 900, 718, true);
		add_image_size('codeweber_project_900-800', 900, 800, true);



		//add_image_size('codeweber_big', 1400, 800, true );
		//add_image_size('codeweber_square', 400, 400, true );
		//add_image_size( 'codeweber_single', 800, 500, true );
		//add_image_size('codeweber_extralarge', 1600, 1200, true); // Без обрезки, до 1600px

		remove_image_size('large');
		remove_image_size('thumbnail');
		remove_image_size('medium');
		remove_image_size('medium_large');
		remove_image_size( '1536x1536' );
		remove_image_size( '2048x2048' );
	}
}
add_action( 'after_setup_theme', 'codeweber_image_settings' );


// --- Set image compression value ---
// https://developer.wordpress.org/reference/hooks/jpeg_quality/

 function codeweber_image_quality() {
 	return 80;
 }
 add_filter( 'jpeg_quality', 'codeweber_image_quality' );


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
	// Получаем ID и тип родительской записи
	$parent_id = get_post_field('post_parent', $attachment_id);

	// Проверяем разные способы определения родителя
	if (!$parent_id && !empty($_REQUEST['post_id'])) {
		$parent_id = intval($_REQUEST['post_id']);
		wp_update_post([
			'ID' => $attachment_id,
			'post_parent' => $parent_id
		]);
	}

	// Определяем тип записи
	$parent_type = $parent_id ? get_post_type($parent_id) : '';

	// Специальная обработка для WooCommerce
	if (!$parent_type && $parent_id && function_exists('wc_get_product')) {
		$product = wc_get_product($parent_id);
		if ($product) {
			$parent_type = 'product';
		}
	}

	// Массив разрешённых размеров изображений
	$allowed_sizes_by_post_type = [
		'projects' => ['codeweber_project_900-900', 'codeweber_project_900-718', 'codeweber_project_900-800'],
		// Раскомментируйте при необходимости:
		// 'product' => ['woocommerce_thumbnail', 'woocommerce_single'],
		// 'post' => ['thumbnail', 'medium', 'large'],
		// 'page' => ['thumbnail', 'medium'],
	];

	// Фильтрация размеров изображений
	if ($parent_type && isset($allowed_sizes_by_post_type[$parent_type]) && !empty($metadata['sizes'])) {
		$allowed_sizes = $allowed_sizes_by_post_type[$parent_type];
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
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
 * Фильтрует созданные размеры изображений по типу родительской записи (CPT или стандартного типа).
 * Оставляет только разрешённые размеры для каждого типа записи.
 * Если тип записи не указан в списке — все размеры сохраняются.
 *
 * @param array $metadata      Метаданные вложения (изображения).
 * @param int   $attachment_id ID вложения.
 * @return array Отфильтрованные метаданные.
 */
function codeweber_filter_attachment_sizes_by_post_type($metadata, $attachment_id)
{
	// Массив CPT и разрешённых для них размеров изображений
	$allowed_sizes_by_post_type = [
		'projects' => ['codeweber_project_900-900', 'codeweber_project_900-718', 'codeweber_project_900-800'],
		//'product'  => ['woocommerce_thumbnail', 'woocommerce_single'], // WooCommerce
		//'post'     => ['thumbnail', 'medium', 'large'], // пример для постов
		//'page'     => ['thumbnail', 'medium'],         // пример для страниц
		// Добавьте другие типы и размеры по необходимости
	];

	$parent_id = get_post_field('post_parent', $attachment_id);
	$parent_type = $parent_id ? get_post_type($parent_id) : '';

	// Если родитель не найден — можно пробовать получить attachment post type (для загруженных напрямую в библиотеку)
	if (!$parent_type) {
		$parent_type = get_post_type($attachment_id);
	}

	if (isset($allowed_sizes_by_post_type[$parent_type]) && !empty($metadata['sizes'])) {
		$allowed_sizes = $allowed_sizes_by_post_type[$parent_type];
		$upload_dir = wp_upload_dir();

		foreach ($metadata['sizes'] as $size_name => $size_info) {
			if (!in_array($size_name, $allowed_sizes, true)) {
				// Удаляем файл с диска
				$file_path = path_join($upload_dir['basedir'], dirname($metadata['file']) . '/' . $size_info['file']);
				if (file_exists($file_path)) {
					@unlink($file_path);
				}
				// Удаляем размер из массива метаданных
				unset($metadata['sizes'][$size_name]);
			}
		}
	}

	return $metadata;
}
add_filter('wp_generate_attachment_metadata', 'codeweber_filter_attachment_sizes_by_post_type', 10, 2);


/**
 * Убирает слово "Archive" из заголовков архивов в Rank Math SEO.
 */
add_filter('rank_math/frontend/title', function ($title) {
	if (is_archive()) {
		$title = preg_replace('/^\s*Archive\s*:?\s*/i', '', $title);
	}
	return $title;
});

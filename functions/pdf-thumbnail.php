<?php
/**
 * PDF Thumbnail Generator
 * 
 * Создает превью (скриншот) первой страницы PDF файла
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Создает изображение первой страницы PDF
 * 
 * @param string|int $pdf_path Путь к PDF файлу или ID вложения
 * @param string $output_format Формат выходного изображения (jpg, png, webp)
 * @param int $quality Качество изображения (1-100, только для jpg/webp)
 * @param int $width Ширина выходного изображения (0 = оригинальный размер)
 * @param int $height Высота выходного изображения (0 = оригинальный размер)
 * @return string|false Путь к созданному изображению или false при ошибке
 */
function codeweber_generate_pdf_thumbnail($pdf_path, $output_format = 'jpg', $quality = 90, $width = 0, $height = 0) {
	// Если передан ID вложения, получаем путь к файлу
	if (is_numeric($pdf_path)) {
		$attachment_id = (int) $pdf_path;
		$pdf_path = get_attached_file($attachment_id);
		
		if (!$pdf_path || !file_exists($pdf_path)) {
			return false;
		}
	}
	
	// Проверяем существование файла
	if (!file_exists($pdf_path)) {
		return false;
	}
	
	// Проверяем, что это PDF
	$mime_type = wp_check_filetype($pdf_path)['type'];
	if ($mime_type !== 'application/pdf') {
		return false;
	}
	
	// Нормализуем формат
	$output_format = strtolower($output_format);
	if (!in_array($output_format, ['jpg', 'jpeg', 'png', 'webp'])) {
		$output_format = 'jpg';
	}
	
	// Создаем имя выходного файла
	$upload_dir = wp_upload_dir();
	$pdf_basename = pathinfo($pdf_path, PATHINFO_FILENAME);
	$output_filename = $pdf_basename . '_thumbnail.' . $output_format;
	$output_path = $upload_dir['basedir'] . '/pdf-thumbnails/' . $output_filename;
	$output_url = $upload_dir['baseurl'] . '/pdf-thumbnails/' . $output_filename;
	
	// Создаем директорию, если не существует
	$output_dir = dirname($output_path);
	if (!file_exists($output_dir)) {
		wp_mkdir_p($output_dir);
	}
	
	// Если файл уже существует, возвращаем его
	if (file_exists($output_path)) {
		return $output_url;
	}
	
	// Метод 1: Используем WordPress Image Editor (требует Imagick)
	if (extension_loaded('imagick')) {
		try {
			$imagick = new Imagick();
			$imagick->setResolution(300, 300); // Высокое разрешение для качества
			$imagick->readImage($pdf_path . '[0]'); // [0] = первая страница
			$imagick->setImageFormat($output_format);
			
			// Устанавливаем качество для jpg/webp
			if (in_array($output_format, ['jpg', 'jpeg', 'webp'])) {
				$imagick->setImageCompressionQuality($quality);
			}
			
			// Изменяем размер, если указано
			if ($width > 0 || $height > 0) {
				$imagick->resizeImage(
					$width > 0 ? $width : 0,
					$height > 0 ? $height : 0,
					Imagick::FILTER_LANCZOS,
					1,
					$width > 0 && $height > 0
				);
			}
			
			$imagick->writeImage($output_path);
			$imagick->clear();
			$imagick->destroy();
			
			return $output_url;
		} catch (Exception $e) {
			error_log('PDF Thumbnail (Imagick) Error: ' . $e->getMessage());
		}
	}
	
	// Метод 2: Используем Ghostscript через exec (если доступен)
	if (function_exists('exec') && !ini_get('safe_mode')) {
		// Проверяем наличие Ghostscript (системного или портативного)
		$gs_command = null;
		
		// Используем функцию обнаружения, если доступна
		if (function_exists('codeweber_detect_ghostscript')) {
			$gs_command = codeweber_detect_ghostscript();
		} else {
			// Fallback: проверяем стандартные пути
			$gs_paths = [
				'gswin64c.exe', // Windows 64-bit
				'gswin32c.exe', // Windows 32-bit
				'gs', // Linux/Mac
			];
			
			// Также проверяем портативный Ghostscript в теме
			$theme_dir = get_template_directory();
			$portable_paths = [
				$theme_dir . '/bin/ghostscript/bin/gswin64c.exe',
				$theme_dir . '/bin/ghostscript/bin/gswin32c.exe',
				$theme_dir . '/vendor/ghostscript/bin/gswin64c.exe',
				$theme_dir . '/vendor/ghostscript/bin/gswin32c.exe',
			];
			
			$all_paths = array_merge($gs_paths, $portable_paths);
			
			foreach ($all_paths as $gs_path) {
				// Для портативного пути проверяем существование файла
				if (strpos($gs_path, $theme_dir) !== false) {
					if (file_exists($gs_path)) {
						$gs_command = $gs_path;
						break;
					}
				} else {
					// Для системных путей проверяем через exec
					$test = @exec("$gs_path -v 2>&1", $output, $return_var);
					if ($return_var === 0) {
						$gs_command = $gs_path;
						break;
					}
				}
			}
		}
		
		if ($gs_command) {
			$dpi = 300;
			$temp_output = $output_path . '.tmp';
			
			// Команда Ghostscript для конвертации первой страницы
			$cmd = sprintf(
				'%s -dNOPAUSE -dBATCH -sDEVICE=%s -r%d -dFirstPage=1 -dLastPage=1 -sOutputFile="%s" "%s"',
				$gs_command,
				$output_format === 'png' ? 'png16m' : 'jpeg',
				$dpi,
				escapeshellarg($temp_output),
				escapeshellarg($pdf_path)
			);
			
			exec($cmd, $output, $return_var);
			
			if ($return_var === 0 && file_exists($temp_output)) {
				// Если нужно изменить размер
				if ($width > 0 || $height > 0) {
					$editor = wp_get_image_editor($temp_output);
					if (!is_wp_error($editor)) {
						$editor->resize($width > 0 ? $width : null, $height > 0 ? $height : null, false);
						$editor->set_quality($quality);
						$saved = $editor->save($output_path);
						@unlink($temp_output);
						
						if (!is_wp_error($saved)) {
							return $output_url;
						}
					}
				} else {
					// Просто переименовываем/перемещаем файл
					rename($temp_output, $output_path);
					return $output_url;
				}
			}
		}
	}
	
	// Метод 3: Используем wp_get_image_editor (может работать с PDF, если Imagick установлен)
	$editor = wp_get_image_editor($pdf_path);
	if (!is_wp_error($editor)) {
		// Изменяем размер, если указано
		if ($width > 0 || $height > 0) {
			$editor->resize($width > 0 ? $width : null, $height > 0 ? $height : null, false);
		}
		
		$editor->set_quality($quality);
		$saved = $editor->save($output_path, 'image/' . $output_format);
		
		if (!is_wp_error($saved)) {
			return $output_url;
		}
	}
	
	return false;
}

/**
 * Получает или создает превью PDF вложения
 * 
 * @param int $attachment_id ID вложения PDF
 * @param string $size Размер изображения (thumbnail, medium, large или кастомный)
 * @return string|false URL превью или false
 */
function codeweber_get_pdf_thumbnail($attachment_id, $size = 'medium') {
	$attachment_id = (int) $attachment_id;
	
	// Проверяем, что это PDF
	$mime_type = get_post_mime_type($attachment_id);
	if ($mime_type !== 'application/pdf') {
		return false;
	}
	
	// Получаем размеры для запрошенного размера
	$image_sizes = wp_get_registered_image_subsizes();
	$size_data = isset($image_sizes[$size]) ? $image_sizes[$size] : null;
	
	$width = $size_data ? $size_data['width'] : 0;
	$height = $size_data ? $size_data['height'] : 0;
	
	// Генерируем превью
	$thumbnail_url = codeweber_generate_pdf_thumbnail($attachment_id, 'jpg', 90, $width, $height);
	
	return $thumbnail_url;
}

/**
 * Автоматически создает превью при загрузке PDF
 * 
 * @param int $attachment_id ID вложения
 */
function codeweber_auto_generate_pdf_thumbnail($attachment_id) {
	$mime_type = get_post_mime_type($attachment_id);
	
	if ($mime_type === 'application/pdf') {
		// Создаем превью среднего размера
		codeweber_get_pdf_thumbnail($attachment_id, 'medium');
	}
}
add_action('add_attachment', 'codeweber_auto_generate_pdf_thumbnail');

/**
 * Удаляет превью при удалении PDF
 * 
 * @param int $attachment_id ID вложения
 */
function codeweber_delete_pdf_thumbnail($attachment_id) {
	$mime_type = get_post_mime_type($attachment_id);
	
	if ($mime_type === 'application/pdf') {
		$upload_dir = wp_upload_dir();
		$pdf_path = get_attached_file($attachment_id);
		
		if ($pdf_path) {
			$pdf_basename = pathinfo($pdf_path, PATHINFO_FILENAME);
			$thumbnail_path = $upload_dir['basedir'] . '/pdf-thumbnails/' . $pdf_basename . '_thumbnail.*';
			
			// Удаляем все варианты превью
			$files = glob($thumbnail_path);
			foreach ($files as $file) {
				@unlink($file);
			}
		}
	}
}
add_action('delete_attachment', 'codeweber_delete_pdf_thumbnail');


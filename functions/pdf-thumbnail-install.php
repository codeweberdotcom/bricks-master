<?php
/**
 * PDF Thumbnail - Ghostscript Installer Helper
 * 
 * Помощник для установки портативного Ghostscript в теме
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Получает путь к портативному Ghostscript в теме
 * 
 * @return string|false Путь к gswin64c.exe или false
 */
function codeweber_get_portable_ghostscript_path() {
	$theme_dir = get_template_directory();
	$gs_paths = [
		$theme_dir . '/bin/ghostscript/bin/gswin64c.exe',
		$theme_dir . '/bin/ghostscript/bin/gswin32c.exe',
		$theme_dir . '/vendor/ghostscript/bin/gswin64c.exe',
		$theme_dir . '/vendor/ghostscript/bin/gswin32c.exe',
	];
	
	foreach ($gs_paths as $path) {
		if (file_exists($path)) {
			return $path;
		}
	}
	
	return false;
}

/**
 * Проверяет наличие Ghostscript (системного или портативного)
 * 
 * @return string|false Путь к Ghostscript или false
 */
function codeweber_detect_ghostscript() {
	// Сначала проверяем системный Ghostscript
	$gs_paths = [
		'gswin64c.exe',
		'gswin32c.exe',
		'gs',
	];
	
	foreach ($gs_paths as $gs_path) {
		$test = @exec("$gs_path -v 2>&1", $output, $return_var);
		if ($return_var === 0) {
			return $gs_path;
		}
	}
	
	// Проверяем портативный Ghostscript в теме
	$portable_path = codeweber_get_portable_ghostscript_path();
	if ($portable_path && file_exists($portable_path)) {
		return $portable_path;
	}
	
	return false;
}

/**
 * Скачивает и устанавливает портативный Ghostscript в теме
 * 
 * @return bool|WP_Error Успех или ошибка
 */
function codeweber_install_portable_ghostscript() {
	$theme_dir = get_template_directory();
	$bin_dir = $theme_dir . '/bin/ghostscript';
	
	// Создаем директорию, если не существует
	if (!file_exists($bin_dir)) {
		wp_mkdir_p($bin_dir);
	}
	
	// Проверяем, не установлен ли уже
	if (file_exists($bin_dir . '/bin/gswin64c.exe')) {
		return true;
	}
	
	// URL для скачивания Ghostscript (последняя версия)
	// Для Windows 64-bit
	$download_url = 'https://github.com/ArtifexSoftware/ghostpdl-downloads/releases/download/gs1000/gs1000w64.exe';
	
	$zip_file = $bin_dir . '/ghostscript-installer.exe';
	
	// Скачиваем установщик
	$response = wp_remote_get($download_url, [
		'timeout' => 300,
		'stream' => true,
		'filename' => $zip_file,
	]);
	
	if (is_wp_error($response)) {
		return new WP_Error('download_failed', 'Не удалось скачать Ghostscript: ' . $response->get_error_message());
	}
	
	// Для установщика нужен ручной запуск или распаковка
	// Альтернатива: использовать ZIP архив, если доступен
	return new WP_Error('manual_install_required', 
		'Ghostscript установщик скачан. Пожалуйста, запустите его вручную: ' . $zip_file . 
		' Или используйте портативную версию из https://www.ghostscript.com/download/gsdnld.html'
	);
}






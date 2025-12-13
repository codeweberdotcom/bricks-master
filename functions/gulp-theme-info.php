<?php
/**
 * Скрипт для определения активной дочерней темы
 * Используется Gulp для генерации dist в правильной директории
 * 
 * Запуск: php functions/gulp-theme-info.php
 */

// Подавляем все выводы ошибок и предупреждений, чтобы на выходе был только JSON
error_reporting(0);
ini_set('display_errors', '0');
ini_set('log_errors', '0');

// Устанавливаем обработчик ошибок для перехвата всех предупреждений
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // Подавляем все ошибки и предупреждения
    return true;
}, E_ALL);

// Перехватываем вывод ошибок
ob_start();

// Устанавливаем минимальные переменные $_SERVER для избежания предупреждений
// Это нужно, так как wp-config.php может проверять эти переменные
if (!isset($_SERVER['REQUEST_SCHEME'])) {
    $_SERVER['REQUEST_SCHEME'] = 'http';
}
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}
if (!isset($_SERVER['REQUEST_URI'])) {
    $_SERVER['REQUEST_URI'] = '/';
}
if (!isset($_SERVER['SERVER_NAME'])) {
    $_SERVER['SERVER_NAME'] = 'localhost';
}
if (!isset($_SERVER['SERVER_PORT'])) {
    $_SERVER['SERVER_PORT'] = '80';
}

// Определяем путь к WordPress
// Путь от functions/gulp-theme-info.php до wp-load.php:
// functions/gulp-theme-info.php -> codeweber -> themes -> wp-content -> wp-load.php
$wp_load_paths = [];

// 1. Стандартные пути относительно скрипта
$wp_load_paths[] = __DIR__ . '/../../../../wp-load.php'; // Из темы: functions -> codeweber -> themes -> wp-content -> wp-load.php
$wp_load_paths[] = __DIR__ . '/../../../../../wp-load.php'; // Альтернативный путь
$wp_load_paths[] = dirname(dirname(dirname(dirname(__DIR__)))) . '/wp-load.php'; // Абсолютный путь

// 2. Дополнительные попытки через текущую директорию скрипта
$script_dir = __DIR__;
$wp_load_paths[] = $script_dir . '/../../../../wp-load.php';
$wp_load_paths[] = $script_dir . '/../../../../../wp-load.php';
$wp_load_paths[] = dirname($script_dir) . '/../../../../wp-load.php';
$wp_load_paths[] = dirname($script_dir) . '/../../../../../wp-load.php';

// 3. Через переменную окружения
$wp_env_path = getenv('WP_LOAD_PATH');
if ($wp_env_path && file_exists($wp_env_path)) {
    $wp_load_paths[] = $wp_env_path;
}

// 4. Через DOCUMENT_ROOT (для веб-серверов)
if (isset($_SERVER['DOCUMENT_ROOT'])) {
    $doc_root = $_SERVER['DOCUMENT_ROOT'];
    $wp_load_paths[] = $doc_root . '/wp-load.php';
    // Для Laragon и других локальных серверов
    $wp_load_paths[] = dirname($doc_root) . '/wp-load.php';
    // Если WordPress в подпапке
    $wp_load_paths[] = $doc_root . '/../wp-load.php';
}

// 5. Пробуем найти через реальный путь
$real_script_dir = realpath(__DIR__);
if ($real_script_dir) {
    $wp_load_paths[] = dirname(dirname(dirname(dirname($real_script_dir)))) . '/wp-load.php';
    $wp_load_paths[] = dirname(dirname(dirname(dirname(dirname($real_script_dir))))) . '/wp-load.php';
}

// Пробуем загрузить WordPress из всех возможных путей
$wp_loaded = false;
foreach ($wp_load_paths as $wp_path) {
    $real_path = realpath($wp_path);
    if ($real_path && file_exists($real_path)) {
        try {
            // Очищаем буфер перед загрузкой
            ob_clean();
            // Используем @ для подавления всех предупреждений при загрузке
            @require_once $real_path;
            // Проверяем, что WordPress действительно загружен
            if (function_exists('wp_get_theme') && function_exists('is_child_theme')) {
                $wp_loaded = true;
                // Очищаем все возможные выводы перед JSON
                ob_end_clean();
                ob_start(); // Начинаем новый буфер для чистого вывода
                break;
            }
        } catch (Exception $e) {
            // Продолжаем поиск
            ob_clean();
        } catch (Error $e) {
            // Продолжаем поиск (для PHP 7+)
            ob_clean();
        }
    }
}

if (!$wp_loaded) {
    // Если WordPress не найден, выводим ошибку с информацией для отладки
    ob_end_clean();
    $result = [
        'error' => true,
        'message' => 'WordPress not loaded',
        'is_child' => false,
        'child_theme_name' => null,
        'child_theme_path' => null,
        'parent_theme_path' => realpath(__DIR__ . '/..') ?: __DIR__ . '/..',
        'debug' => [
            'script_dir' => __DIR__,
            'real_script_dir' => realpath(__DIR__),
            'tried_paths' => array_map(function($path) {
                return [
                    'path' => $path,
                    'realpath' => realpath($path),
                    'exists' => file_exists($path)
                ];
            }, array_unique($wp_load_paths))
        ]
    ];
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit(1);
}

// Получаем информацию об активной теме
$active_theme = wp_get_theme();
$is_child = is_child_theme();

$result = [
    'is_child' => $is_child,
    'child_theme_name' => $is_child ? $active_theme->get_stylesheet() : null,
    'child_theme_path' => $is_child ? get_stylesheet_directory() : null,
    'parent_theme_path' => get_template_directory(),
    'active_theme_name' => $active_theme->get('Name'),
    'stylesheet' => $active_theme->get_stylesheet(),
    'template' => $active_theme->get_template(),
];

// Очищаем весь буфер и выводим только JSON
ob_end_clean();
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);


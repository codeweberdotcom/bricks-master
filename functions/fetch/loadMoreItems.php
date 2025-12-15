<?php

namespace Codeweber\Functions\Fetch;

/**
 * Load More Items function for Fetch system
 * 
 * Integrates Load More functionality from codeweber-gutenberg-blocks plugin
 * with the Fetch AJAX system
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Load more items via Fetch system
 * 
 * @param array $params {
 *     Parameters for loading items
 *     @type string $block_id Block ID (required)
 *     @type string $block_type Block type (image-simple, post-grid)
 *     @type string $block_attributes JSON string with block attributes
 *     @type int $offset Current offset
 *     @type int $count Number of items to load
 *     @type int $post_id Post ID (optional)
 * }
 * @return array Response in Fetch format
 */
function loadMoreItems($params) {
    // Получаем параметры
    $block_id = isset($params['block_id']) ? sanitize_text_field($params['block_id']) : '';
    $block_type = isset($params['block_type']) ? sanitize_text_field($params['block_type']) : '';
    $offset = isset($params['offset']) ? absint($params['offset']) : 0;
    $count = isset($params['count']) ? absint($params['count']) : 6;
    $post_id = isset($params['post_id']) ? absint($params['post_id']) : 0;
    $block_attributes_json = isset($params['block_attributes']) ? $params['block_attributes'] : '';
    
    // Логирование для отладки
    error_log('Fetch LoadMoreItems: block_id=' . $block_id . ', block_type=' . $block_type . ', offset=' . $offset . ', count=' . $count);
    
    // Валидация
    if (!$block_id) {
        return [
            'status' => 'error',
            'message' => 'Block ID is required'
        ];
    }
    
    // Проверяем наличие класса LoadMoreAPI из плагина
    if (!class_exists('Codeweber\Blocks\LoadMoreAPI')) {
        return [
            'status' => 'error',
            'message' => 'LoadMoreAPI class not found. Please ensure codeweber-gutenberg-blocks plugin is active.'
        ];
    }
    
    // Создаем экземпляр LoadMoreAPI
    $load_more_api = new \Codeweber\Blocks\LoadMoreAPI();
    
    // Обрабатываем запрос в зависимости от типа блока
    if ($block_type === 'image-simple' && $block_attributes_json) {
        $result = $load_more_api->load_more_image_simple($block_attributes_json, $offset, $count);
        
        // Преобразуем формат ответа из REST API в Fetch формат
        if (isset($result['success']) && $result['success']) {
            return [
                'status' => 'success',
                'data' => $result['data'] ?? $result
            ];
        } else {
            return [
                'status' => 'error',
                'message' => $result['message'] ?? 'Failed to load images',
                'data' => $result['data'] ?? [
                    'html' => '',
                    'has_more' => false,
                    'offset' => $offset
                ]
            ];
        }
    }
    
    // Если это блок Post Grid
    if ($block_type === 'post-grid' && $block_attributes_json) {
        $result = $load_more_api->load_more_post_grid($block_attributes_json, $offset, $count);
        
        // Преобразуем формат ответа из REST API в Fetch формат
        if (isset($result['success']) && $result['success']) {
            return [
                'status' => 'success',
                'data' => $result['data'] ?? $result
            ];
        } else {
            return [
                'status' => 'error',
                'message' => $result['message'] ?? 'Failed to load posts',
                'data' => $result['data'] ?? [
                    'html' => '',
                    'has_more' => false,
                    'offset' => $offset
                ]
            ];
        }
    }
    
    // Для других типов блоков или если тип не указан
    return [
        'status' => 'error',
        'message' => 'Unsupported block type or missing attributes',
        'data' => [
            'html' => '',
            'has_more' => false,
            'offset' => $offset
        ]
    ];
}








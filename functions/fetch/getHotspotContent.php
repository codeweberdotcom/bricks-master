<?php

namespace Codeweber\Functions\Fetch;

/**
 * Get Hotspot Content function for Fetch system
 * 
 * Returns content for hotspot point (text or post with template)
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get hotspot content via Fetch system
 * 
 * @param array $params {
 *     Parameters for getting hotspot content
 *     @type int $hotspot_id Hotspot ID (required)
 *     @type string $point_id Point ID (required)
 * }
 * @return array Response in Fetch format
 */
function getHotspotContent($params) {
    // Получаем параметры
    $hotspot_id = isset($params['hotspot_id']) ? intval($params['hotspot_id']) : 0;
    $point_id = isset($params['point_id']) ? sanitize_text_field($params['point_id']) : '';
    
    if (!$hotspot_id || !$point_id) {
        return [
            'status' => 'error',
            'message' => 'Missing parameters: hotspot_id and point_id are required'
        ];
    }
    
    // Получаем данные hotspot
    $hotspot_data = get_post_meta($hotspot_id, '_hotspot_data', true);
    $hotspots = !empty($hotspot_data) ? json_decode($hotspot_data, true) : [];
    
    // Находим нужную точку
    $point = null;
    foreach ($hotspots as $h) {
        if (isset($h['id']) && $h['id'] === $point_id) {
            $point = $h;
            break;
        }
    }
    
    if (!$point) {
        return [
            'status' => 'error',
            'message' => 'Point not found'
        ];
    }
    
    $content = '';
    $title = !empty($point['title']) ? esc_html($point['title']) : '';
    
    // #region agent log
    $log_file = WP_CONTENT_DIR . '/../.cursor/debug.log';
    $log_data = json_encode([
        'location' => 'getHotspotContent.php:content_generation',
        'message' => 'Starting content generation',
        'data' => [
            'hotspot_id' => $hotspot_id,
            'point_id' => $point_id,
            'contentType' => $point['contentType'] ?? 'unknown',
            'hasPostId' => !empty($point['postId']),
            'postId' => $point['postId'] ?? null,
            'hasContent' => !empty($point['content']),
            'hasTitle' => !empty($title)
        ],
        'timestamp' => round(microtime(true) * 1000),
        'sessionId' => 'debug-session',
        'runId' => 'initial',
        'hypothesisId' => 'J'
    ]) . "\n";
    @file_put_contents($log_file, $log_data, FILE_APPEND);
    // #endregion
    
    // Если есть пост, загружаем его через шаблон PostCard
    if (!empty($point['postId']) && ($point['contentType'] === 'post' || $point['contentType'] === 'hybrid')) {
        $post_id = intval($point['postId']);
        $post_template = !empty($point['postTemplate']) ? sanitize_text_field($point['postTemplate']) : 'default';
        
        // #region agent log
        $log_data = json_encode([
            'location' => 'getHotspotContent.php:post_loading',
            'message' => 'Loading post data',
            'data' => [
                'post_id' => $post_id,
                'post_template' => $post_template,
                'contentType' => $point['contentType']
            ],
            'timestamp' => round(microtime(true) * 1000),
            'sessionId' => 'debug-session',
            'runId' => 'initial',
            'hypothesisId' => 'J'
        ]) . "\n";
        @file_put_contents($log_file, $log_data, FILE_APPEND);
        // #endregion
        
        $post = get_post($post_id);
        if ($post && $post->post_status === 'publish') {
            // #region agent log
            $log_data = json_encode([
                'location' => 'getHotspotContent.php:post_found',
                'message' => 'Post found and published',
                'data' => [
                    'post_id' => $post_id,
                    'post_title' => $post->post_title,
                    'function_exists_cw_render_post_card' => function_exists('cw_render_post_card')
                ],
                'timestamp' => round(microtime(true) * 1000),
                'sessionId' => 'debug-session',
                'runId' => 'initial',
                'hypothesisId' => 'J'
            ]) . "\n";
            @file_put_contents($log_file, $log_data, FILE_APPEND);
            // #endregion
            
            // Используем функцию рендеринга PostCard из темы
            if (function_exists('cw_render_post_card')) {
                // #region agent log
                $log_data = json_encode([
                    'location' => 'getHotspotContent.php:before_render',
                    'message' => 'Before calling cw_render_post_card',
                    'data' => [
                        'post_id' => $post_id,
                        'post_template' => $post_template,
                        'post_type' => $post->post_type,
                        'post_status' => $post->post_status
                    ],
                    'timestamp' => round(microtime(true) * 1000),
                    'sessionId' => 'debug-session',
                    'runId' => 'initial',
                    'hypothesisId' => 'J'
                ]) . "\n";
                @file_put_contents($log_file, $log_data, FILE_APPEND);
                // #endregion
                
                // Вызываем функцию напрямую (она уже использует ob_start/ob_get_clean внутри)
                $post_content = cw_render_post_card($post, $post_template, [], [
                    'enable_link' => false, // Отключаем ссылку в popover
                    'image_size' => 'medium'
                ]);
                
                // #region agent log
                $log_data = json_encode([
                    'location' => 'getHotspotContent.php:post_card_rendered',
                    'message' => 'Post card rendered',
                    'data' => [
                        'post_id' => $post_id,
                        'post_content_length' => strlen($post_content),
                        'post_content_preview' => substr($post_content, 0, 100),
                        'post_content_empty' => empty($post_content)
                    ],
                    'timestamp' => round(microtime(true) * 1000),
                    'sessionId' => 'debug-session',
                    'runId' => 'initial',
                    'hypothesisId' => 'J'
                ]) . "\n";
                @file_put_contents($log_file, $log_data, FILE_APPEND);
                // #endregion
                
                if ($point['contentType'] === 'hybrid' && !empty($point['content'])) {
                    // Hybrid: текст + пост
                    $content = wp_kses_post($point['content']) . '<div class="cw-hotspot-post-content mt-3">' . $post_content . '</div>';
                } else {
                    // Только пост
                    $content = $post_content;
                }
            } else {
                // #region agent log
                $log_data = json_encode([
                    'location' => 'getHotspotContent.php:fallback_used',
                    'message' => 'Using fallback post rendering',
                    'data' => [
                        'post_id' => $post_id,
                        'has_thumbnail' => has_post_thumbnail($post_id)
                    ],
                    'timestamp' => round(microtime(true) * 1000),
                    'sessionId' => 'debug-session',
                    'runId' => 'initial',
                    'hypothesisId' => 'J'
                ]) . "\n";
                @file_put_contents($log_file, $log_data, FILE_APPEND);
                // #endregion
                
                // Fallback: простой вывод поста
                $content = '<h3>' . esc_html($post->post_title) . '</h3>';
                if (has_post_thumbnail($post_id)) {
                    $content .= get_the_post_thumbnail($post_id, 'medium');
                }
                $content .= '<p>' . wp_trim_words($post->post_excerpt ?: $post->post_content, 30) . '</p>';
            }
        } else {
            // #region agent log
            $log_data = json_encode([
                'location' => 'getHotspotContent.php:post_not_found',
                'message' => 'Post not found or not published',
                'data' => [
                    'post_id' => $post_id,
                    'post_exists' => $post ? true : false,
                    'post_status' => $post ? $post->post_status : 'not_found'
                ],
                'timestamp' => round(microtime(true) * 1000),
                'sessionId' => 'debug-session',
                'runId' => 'initial',
                'hypothesisId' => 'J'
            ]) . "\n";
            @file_put_contents($log_file, $log_data, FILE_APPEND);
            // #endregion
        }
    } else {
        // Простой текст
        if (!empty($point['content'])) {
            $content = wp_kses_post($point['content']);
            $content = do_shortcode($content);
            $content = apply_filters('the_content', $content);
        }
    }
    
    // #region agent log
    $log_data = json_encode([
        'location' => 'getHotspotContent.php:content_final',
        'message' => 'Final content generated',
        'data' => [
            'content_length' => strlen($content),
            'content_preview' => substr($content, 0, 100),
            'title' => $title
        ],
        'timestamp' => round(microtime(true) * 1000),
        'sessionId' => 'debug-session',
        'runId' => 'initial',
        'hypothesisId' => 'J'
    ]) . "\n";
    @file_put_contents($log_file, $log_data, FILE_APPEND);
    // #endregion
    
    // Добавляем ссылку, если есть
    if (!empty($point['link'])) {
        $link_target = isset($point['linkTarget']) ? esc_attr($point['linkTarget']) : '_self';
        $content .= '<br><a href="' . esc_url($point['link']) . '" target="' . $link_target . '">' . __('Learn more', 'codeweber') . '</a>';
    }
    
    return [
        'status' => 'success',
        'data' => [
            'title' => $title,
            'content' => $content
        ]
    ];
}


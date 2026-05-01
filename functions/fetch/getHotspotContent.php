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
    $wrapper_class = !empty($point['wrapperClass']) ? esc_attr($point['wrapperClass']) : '';

    // Если есть пост, загружаем его через шаблон PostCard
    if (!empty($point['postId']) && ($point['contentType'] === 'post' || $point['contentType'] === 'hybrid')) {
        $post_id = intval($point['postId']);
        $post_template = !empty($point['postTemplate']) ? sanitize_text_field($point['postTemplate']) : 'default';

        $post = get_post($post_id);
        if ($post && $post->post_status === 'publish') {
            // Используем функцию рендеринга PostCard из темы
            if (function_exists('cw_render_post_card')) {
                $GLOBALS['cw_shop_col_class'] = 'col-12';
                $post_content = cw_render_post_card($post, $post_template, [], [
                    'enable_link' => true,
                    'image_size' => 'medium'
                ]);
                unset($GLOBALS['cw_shop_col_class']);

                if ($point['contentType'] === 'hybrid' && !empty($point['content'])) {
                    // Hybrid: текст + пост
                    $content = wp_kses_post($point['content']) . '<div class="cw-hotspot-post-content mt-3">' . $post_content . '</div>';
                } else {
                    // Только пост
                    $content = $post_content;
                }
            } else {
                // Fallback: простой вывод поста
                $content = '<h3>' . esc_html($post->post_title) . '</h3>';
                if (has_post_thumbnail($post_id)) {
                    $content .= get_the_post_thumbnail($post_id, 'medium');
                }
                $content .= '<p>' . wp_trim_words($post->post_excerpt ?: $post->post_content, 30) . '</p>';
            }
        }
    } else {
        // Простой текст
        if (!empty($point['content'])) {
            $content = wp_kses_post($point['content']);
            $content = do_shortcode($content);
            $content = apply_filters('the_content', $content);
        }
    }

    // Добавляем ссылку, если есть
    if (!empty($point['link'])) {
        $link_target = isset($point['linkTarget']) ? esc_attr($point['linkTarget']) : '_self';
        $content .= '<br><a href="' . esc_url($point['link']) . '" target="' . $link_target . '">' . __('Learn more', 'codeweber') . '</a>';
    }

    // Оборачиваем весь контент в div с классом, если указан wrapperClass
    if (!empty($wrapper_class)) {
        $content = '<div class="' . esc_attr( $wrapper_class ) . '">' . $content . '</div>';
    }

    return [
        'status' => 'success',
        'data' => [
            'title' => $title,
            'content' => $content
        ]
    ];
}

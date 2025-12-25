<?php

namespace Codeweber\Functions\Fetch;

/**
 * Get Posts For Hotspot function for Fetch system
 * 
 * Returns list of posts for selection in hotspot admin
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get posts list for hotspot selection via Fetch system
 * 
 * @param array $params {
 *     Parameters (optional)
 * }
 * @return array Response in Fetch format
 */
function getPostsForHotspot($params) {
    // Получаем все типы постов, которые показываются в админке
    // Это включает: post, page, и все кастомные типы (clients, testimonials, products и т.д.)
    $post_types = get_post_types(['show_ui' => true], 'names');
    
    // Исключаем служебные типы
    $excluded_types = ['attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block', 'wp_template', 'wp_template_part', 'wp_global_styles', 'wp_navigation'];
    $post_types = array_diff($post_types, $excluded_types);
    
    $posts = [];
    $post_types_labels = [];
    
    // Получаем названия типов постов для отображения
    foreach ($post_types as $post_type) {
        $post_type_obj = get_post_type_object($post_type);
        if ($post_type_obj) {
            $post_types_labels[$post_type] = $post_type_obj->labels->singular_name ?: $post_type_obj->label;
        }
    }
    
    foreach ($post_types as $post_type) {
        $query = new \WP_Query([
            'post_type' => $post_type,
            'posts_per_page' => 100,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC'
        ]);
        
        if ($query->have_posts()) {
            $type_label = $post_types_labels[$post_type] ?? $post_type;
            
            while ($query->have_posts()) {
                $query->the_post();
                $posts[] = [
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'type' => $post_type,
                    'type_label' => $type_label
                ];
            }
        }
        wp_reset_postdata();
    }
    
    // Сортируем по типу, затем по названию
    usort($posts, function($a, $b) {
        if ($a['type'] === $b['type']) {
            return strcmp($a['title'], $b['title']);
        }
        return strcmp($a['type'], $b['type']);
    });
    
    return [
        'status' => 'success',
        'data' => [
            'posts' => $posts
        ]
    ];
}


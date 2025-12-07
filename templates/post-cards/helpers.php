<?php
/**
 * Post Card Templates Helpers
 * 
 * НОВАЯ система шаблонов карточек
 * Не конфликтует с существующими функциями темы
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Получить настройки отображения элементов карточки
 * 
 * @param array $args {
 *     Массив настроек
 *     @type bool   $show_title     Показывать заголовок (по умолчанию true)
 *     @type bool   $show_date      Показывать дату (по умолчанию true)
 *     @type bool   $show_category  Показывать категорию (по умолчанию true)
 *     @type bool   $show_comments  Показывать комментарии (по умолчанию true)
 *     @type int    $title_length   Максимальная длина заголовка (0 = без ограничения)
 *     @type int    $excerpt_length Длина описания (0 = не показывать)
 *     @type string $title_tag      HTML тег для заголовка (h1, h2, h3, h4, h5, h6, p, div, span) (по умолчанию h2)
 *     @type string $title_class    Дополнительный CSS класс для заголовка (по умолчанию пусто)
 * }
 * @return array Массив настроек
 */
function cw_get_post_card_display_settings($args = []) {
    $defaults = [
        'show_title' => true,
        'show_date' => true,
        'show_category' => true,
        'show_comments' => true,
        'title_length' => 0,
        'excerpt_length' => 0,
        'title_tag' => 'h2', // h1, h2, h3, h4, h5, h6, p, div, span
        'title_class' => '', // Дополнительный класс для заголовка
    ];
    return wp_parse_args($args, $defaults);
}

/**
 * Получить данные поста для карточки
 * 
 * @param WP_Post|int $post Объект поста или ID
 * @param string $image_size Размер изображения
 * @return array|null Массив данных поста или null
 */
function cw_get_post_card_data($post, $image_size = 'full') {
    if (is_numeric($post)) {
        $post = get_post($post);
    }
    
    if (!$post) {
        return null;
    }
    
    setup_postdata($post);
    
    $thumbnail_id = get_post_thumbnail_id($post->ID);
    $image_url = '';
    $image_alt = '';
    
    if ($thumbnail_id) {
        $image = wp_get_attachment_image_src($thumbnail_id, $image_size);
        $image_url = $image ? $image[0] : '';
        $image_alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
    }
    
    // Если нет alt, используем заголовок поста
    if (empty($image_alt)) {
        $image_alt = get_the_title($post->ID);
    }
    
    // Получаем категории (для разных типов записей)
    $categories = [];
    $category = null;
    $category_link = '';
    
    // Для стандартных постов
    if ($post->post_type === 'post') {
        $categories = get_the_category($post->ID);
        if (!empty($categories)) {
            $category = $categories[0];
            $category_link = get_category_link($category->term_id);
        }
    } else {
        // Для кастомных типов записей - получаем первую таксономию
        $taxonomies = get_object_taxonomies($post->post_type, 'objects');
        if (!empty($taxonomies)) {
            // Ищем таксономию с иерархией (категории) или первую доступную
            $selected_taxonomy = null;
            foreach ($taxonomies as $taxonomy) {
                if ($taxonomy->hierarchical) {
                    $selected_taxonomy = $taxonomy;
                    break;
                }
            }
            // Если не нашли иерархическую, берем первую
            if (!$selected_taxonomy) {
                $selected_taxonomy = reset($taxonomies);
            }
            
            if ($selected_taxonomy) {
                $terms = get_the_terms($post->ID, $selected_taxonomy->name);
                if ($terms && !is_wp_error($terms) && !empty($terms)) {
                    $category = $terms[0];
                    $category_link = get_term_link($category);
                }
            }
        }
    }
    
    return [
        'id' => $post->ID,
        'title' => get_the_title($post->ID),
        'excerpt' => get_the_excerpt($post->ID),
        'link' => get_permalink($post->ID),
        'date' => get_the_date('d M Y', $post->ID),
        'date_format' => get_the_date(get_option('date_format'), $post->ID),
        'comments_count' => get_comments_number($post->ID),
        'category' => $category,
        'category_link' => $category_link,
        'image_url' => $image_url,
        'image_alt' => $image_alt,
        'post_type' => $post->post_type,
    ];
}




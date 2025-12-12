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
 * @param bool $enable_link Включить ссылки (для clients использует Company URL если доступен)
 * @return array|null Массив данных поста или null
 */
function cw_get_post_card_data($post, $image_size = 'full', $enable_link = false) {
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
    
    // Если нет изображения, используем placeholder из плагина
    if (empty($image_url)) {
        // Получаем URL плагина codeweber-gutenberg-blocks
        $plugin_url = '';
        if (defined('GUTENBERG_BLOCKS_URL')) {
            $plugin_url = GUTENBERG_BLOCKS_URL;
        } else {
            // Fallback: получаем URL плагина через plugin_dir_url
            $plugin_path = WP_PLUGIN_DIR . '/codeweber-gutenberg-blocks/plugin.php';
            if (file_exists($plugin_path)) {
                $plugin_url = plugin_dir_url($plugin_path);
            } else {
                // Еще один fallback: используем plugins_url
                $plugin_url = plugins_url('', 'codeweber-gutenberg-blocks/plugin.php') . '/';
            }
        }
        // Убеждаемся, что URL заканчивается на слэш
        if ($plugin_url && !empty($plugin_url)) {
            $plugin_url = rtrim($plugin_url, '/') . '/';
            $image_url = $plugin_url . 'placeholder.jpg';
        }
    }
    
    // Если нет alt, используем заголовок поста
    if (empty($image_alt)) {
        $image_alt = get_the_title($post->ID);
    }
    
    // Специальная обработка для clients - упрощенные данные
    if ($post->post_type === 'clients') {
        // Для clients: если enable_link включен, используем Company URL из метаполя
        $link = get_permalink($post->ID);
        if ($enable_link) {
            $company_url = get_post_meta($post->ID, '_cw_clients_company_url', true);
            if (!empty($company_url)) {
                $link = esc_url($company_url);
            }
        }
        
        return [
            'id' => $post->ID,
            'title' => get_the_title($post->ID),
            'link' => $link,
            'image_url' => $image_url,
            'image_alt' => $image_alt,
            'post_type' => 'clients',
            // Упрощенные данные - без категорий, даты, комментариев, excerpt
        ];
    }
    
    // Специальная обработка для testimonials
    if ($post->post_type === 'testimonials') {
        // Загружаем функцию получения данных testimonials если доступна
        if (!function_exists('codeweber_get_testimonial_data')) {
            $testimonial_data_path = get_template_directory() . '/functions/cpt/cpt-testimonials.php';
            if (file_exists($testimonial_data_path)) {
                require_once $testimonial_data_path;
            }
        }
        
        $testimonial_data = function_exists('codeweber_get_testimonial_data') 
            ? codeweber_get_testimonial_data($post->ID) 
            : false;
        
        if (!$testimonial_data) {
            return null;
        }
        
        // Получаем аватар
        $avatar_url = '';
        $avatar_url_2x = '';
        $avatar_id = get_post_meta($post->ID, '_testimonial_avatar', true);
        
        if ($avatar_id) {
            // Получаем обычный thumbnail
            $avatar_src = wp_get_attachment_image_src($avatar_id, 'thumbnail');
            if ($avatar_src) {
                $avatar_url = esc_url($avatar_src[0]);
            }
            
            // Пытаемся получить @2x версию (medium size)
            $avatar_2x_src = wp_get_attachment_image_src($avatar_id, 'medium');
            if ($avatar_2x_src && $avatar_2x_src[0] !== $avatar_url) {
                $avatar_url_2x = esc_url($avatar_2x_src[0]);
            }
        } elseif (!empty($testimonial_data['author_avatar'])) {
            // Fallback к аватару из пользователя или кастомному URL
            $avatar_url = esc_url($testimonial_data['author_avatar']);
        }
        
        // Получаем рейтинг
        $rating = !empty($testimonial_data['rating']) ? intval($testimonial_data['rating']) : 0;
        $rating_class = '';
        if ($rating > 0 && $rating <= 5) {
            $rating_names = ['', 'one', 'two', 'three', 'four', 'five'];
            $rating_class = $rating_names[$rating];
        } else {
            $rating_class = 'five'; // По умолчанию 5 звезд
        }
        
        return [
            'id' => $post->ID,
            'title' => get_the_title($post->ID),
            'text' => !empty($testimonial_data['text']) ? wp_kses_post($testimonial_data['text']) : '',
            'author_name' => !empty($testimonial_data['author_name']) ? esc_html($testimonial_data['author_name']) : '',
            'author_role' => !empty($testimonial_data['author_role']) ? esc_html($testimonial_data['author_role']) : '',
            'company' => !empty($testimonial_data['company']) ? esc_html($testimonial_data['company']) : '',
            'rating' => $rating,
            'rating_class' => $rating_class,
            'avatar_url' => $avatar_url,
            'avatar_url_2x' => $avatar_url_2x,
            'link' => get_permalink($post->ID),
            'image_url' => $image_url, // Featured image если есть
            'image_alt' => $image_alt,
            'post_type' => 'testimonials',
        ];
    }
    
    // Специальная обработка для staff
    if ($post->post_type === 'staff') {
        // Получаем метаполя staff
        $position = get_post_meta($post->ID, '_staff_position', true);
        $name = get_post_meta($post->ID, '_staff_name', true);
        $surname = get_post_meta($post->ID, '_staff_surname', true);
        $email = get_post_meta($post->ID, '_staff_email', true);
        $phone = get_post_meta($post->ID, '_staff_phone', true);
        $company = get_post_meta($post->ID, '_staff_company', true);
        
        // Формируем полное имя из метаполей или используем title
        $full_name = trim($name . ' ' . $surname);
        if (empty($full_name)) {
            $full_name = get_the_title($post->ID);
        }
        
        // Получаем изображение @2x для retina (используем full для лучшего качества)
        $image_url_2x = '';
        if ($thumbnail_id && $image_url) {
            // Для @2x используем full размер или исходный размер изображения
            $image_2x = wp_get_attachment_image_src($thumbnail_id, 'full');
            if ($image_2x && $image_2x[0] !== $image_url) {
                $image_url_2x = esc_url($image_2x[0]);
            }
        }
        
        // Получаем отдел из таксономии
        $department_name = '';
        $departments = get_the_terms($post->ID, 'departments');
        if ($departments && !is_wp_error($departments) && !empty($departments)) {
            $department = reset($departments);
            $department_name = $department->name;
        }
        
        return [
            'id' => $post->ID,
            'title' => $full_name,
            'position' => !empty($position) ? esc_html($position) : '',
            'department' => $department_name,
            'company' => !empty($company) ? esc_html($company) : '',
            'name' => !empty($name) ? esc_html($name) : '',
            'surname' => !empty($surname) ? esc_html($surname) : '',
            'full_name' => $full_name,
            'email' => !empty($email) ? esc_html($email) : '',
            'phone' => !empty($phone) ? esc_html($phone) : '',
            'excerpt' => get_the_excerpt($post->ID),
            'link' => get_permalink($post->ID),
            'image_url' => $image_url,
            'image_url_2x' => $image_url_2x,
            'image_alt' => $image_alt,
            'post_type' => 'staff',
        ];
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




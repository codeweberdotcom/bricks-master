<?php
/**
 * Demo данные для CPT FAQ
 * 
 * Функции для создания demo записей типа faq
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Получить данные FAQ из JSON файла
 * 
 * @return array|false Массив данных или false при ошибке
 */
function cw_demo_get_faq_data() {
    $json_path = get_template_directory() . '/demo/faq/data.json';
    
    if (!file_exists($json_path)) {
        return false;
    }
    
    $json_content = file_get_contents($json_path);
    $data = json_decode($json_content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Demo FAQ: Ошибка парсинга JSON - ' . json_last_error_msg());
        return false;
    }
    
    return $data;
}

/**
 * Создать или получить категорию FAQ
 * 
 * @param string $category_name Название категории
 * @return int|false ID категории или false при ошибке
 */
function cw_demo_get_or_create_faq_category($category_name) {
    $taxonomy = 'faq_categories';
    
    // Проверяем, существует ли категория
    $term = get_term_by('name', $category_name, $taxonomy);
    
    if ($term) {
        return $term->term_id;
    }
    
    // Создаем новую категорию
    $term_data = wp_insert_term(
        $category_name,
        $taxonomy,
        array(
            'description' => '',
            'slug' => sanitize_title($category_name)
        )
    );
    
    if (is_wp_error($term_data)) {
        error_log('Demo FAQ: Ошибка создания категории - ' . $term_data->get_error_message());
        return false;
    }
    
    return $term_data['term_id'];
}

/**
 * Создать одну запись FAQ
 * 
 * @param array $faq_data Данные FAQ из JSON
 * @return int|false ID созданной записи или false при ошибке
 */
function cw_demo_create_faq_post($faq_data) {
    // Проверяем обязательные поля
    if (empty($faq_data['title']) || empty($faq_data['content'])) {
        error_log('Demo FAQ: Отсутствуют обязательные поля');
        return false;
    }
    
    // Подготавливаем данные для wp_insert_post
    $post_data = array(
        'post_title'    => sanitize_text_field($faq_data['title']),
        'post_content'  => wp_kses_post($faq_data['content']),
        'post_name'     => !empty($faq_data['slug']) ? sanitize_title($faq_data['slug']) : sanitize_title($faq_data['title']),
        'post_status'   => !empty($faq_data['status']) ? $faq_data['status'] : 'publish',
        'post_type'     => 'faq',
        'post_author'   => get_current_user_id(),
    );
    
    // Вставляем запись
    $post_id = wp_insert_post($post_data);
    
    if (is_wp_error($post_id)) {
        error_log('Demo FAQ: Ошибка создания записи - ' . $post_id->get_error_message());
        return false;
    }
    
    // Добавляем мета-поле для идентификации demo записей
    update_post_meta($post_id, '_demo_created', true);
    
    // Добавляем порядок, если указан
    if (!empty($faq_data['order'])) {
        update_post_meta($post_id, '_demo_order', intval($faq_data['order']));
    }
    
    // Назначаем категорию, если указана
    if (!empty($faq_data['category'])) {
        $category_id = cw_demo_get_or_create_faq_category($faq_data['category']);
        
        if ($category_id) {
            wp_set_post_terms($post_id, array($category_id), 'faq_categories');
        }
    }
    
    // Назначаем теги, если указаны
    if (!empty($faq_data['tags']) && is_array($faq_data['tags'])) {
        $tag_ids = array();
        foreach ($faq_data['tags'] as $tag_name) {
            $tag = get_term_by('name', $tag_name, 'faq_tag');
            if (!$tag) {
                $tag_data = wp_insert_term($tag_name, 'faq_tag');
                if (!is_wp_error($tag_data)) {
                    $tag_ids[] = $tag_data['term_id'];
                }
            } else {
                $tag_ids[] = $tag->term_id;
            }
        }
        if (!empty($tag_ids)) {
            wp_set_post_terms($post_id, $tag_ids, 'faq_tag');
        }
    }
    
    return $post_id;
}

/**
 * Создать все demo записи FAQ
 * 
 * @return array Результат выполнения ['created' => int, 'errors' => array]
 */
function cw_demo_create_faq() {
    $data = cw_demo_get_faq_data();
    
    if (!$data || empty($data['items'])) {
        return array(
            'success' => false,
            'message' => 'Данные не найдены или файл поврежден',
            'created' => 0,
            'errors' => array()
        );
    }
    
    $created = 0;
    $errors = array();
    $categories_created = array();
    
    // Собираем уникальные категории
    $unique_categories = array();
    foreach ($data['items'] as $item) {
        if (!empty($item['category'])) {
            $unique_categories[$item['category']] = true;
        }
    }
    
    // Создаем категории заранее
    foreach (array_keys($unique_categories) as $category_name) {
        $category_id = cw_demo_get_or_create_faq_category($category_name);
        if ($category_id) {
            $categories_created[] = $category_name;
        }
    }
    
    foreach ($data['items'] as $item) {
        $post_id = cw_demo_create_faq_post($item);
        
        if ($post_id) {
            $created++;
        } else {
            $errors[] = 'Не удалось создать: ' . (!empty($item['title']) ? $item['title'] : 'неизвестно');
        }
    }
    
    $message = sprintf('Создано записей: %d из %d', $created, count($data['items']));
    if (!empty($categories_created)) {
        $message .= '. Категории: ' . implode(', ', $categories_created);
    }
    
    return array(
        'success' => true,
        'message' => $message,
        'created' => $created,
        'total' => count($data['items']),
        'categories' => $categories_created,
        'errors' => $errors
    );
}

/**
 * Удалить все demo записи FAQ
 * 
 * @return array Результат выполнения
 */
function cw_demo_delete_faq() {
    $args = array(
        'post_type' => 'faq',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => '_demo_created',
                'value' => true,
                'compare' => '='
            )
        ),
        'fields' => 'ids'
    );
    
    $posts = get_posts($args);
    $deleted = 0;
    $errors = array();
    
    foreach ($posts as $post_id) {
        // Удаляем запись
        $result = wp_delete_post($post_id, true);
        
        if ($result) {
            $deleted++;
        } else {
            $errors[] = 'Не удалось удалить запись ID: ' . $post_id;
        }
    }
    
    return array(
        'success' => true,
        'message' => sprintf('Удалено записей: %d', $deleted),
        'deleted' => $deleted,
        'errors' => $errors
    );
}

<?php
/**
 * Demo данные для CPT Clients
 * 
 * Функции для создания demo записей типа clients
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Получить данные клиентов из JSON файла
 * 
 * @return array|false Массив данных или false при ошибке
 */
function cw_demo_get_clients_data() {
    $json_path = get_template_directory() . '/demo/clients/data.json';
    
    if (!file_exists($json_path)) {
        return false;
    }
    
    $json_content = file_get_contents($json_path);
    $data = json_decode($json_content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Demo Clients: Ошибка парсинга JSON - ' . json_last_error_msg());
        return false;
    }
    
    // Определяем язык сайта
    $locale = get_locale();
    $is_russian = (strpos($locale, 'ru') === 0);
    
    // Адаптируем данные в зависимости от языка
    if ($is_russian && !empty($data['items'])) {
        foreach ($data['items'] as &$item) {
            // Для русского языка используем company_name как title, если оно есть
            if (!empty($item['company_name'])) {
                $item['title'] = $item['company_name'];
            }
        }
        unset($item); // Сбрасываем ссылку
        
        // Адаптируем категории
        if (isset($data['categories'])) {
            $data['categories'] = array_map(function($cat) {
                $ru_categories = array(
                    'Primary Clients' => 'Основные клиенты',
                    'Partners' => 'Партнеры'
                );
                return isset($ru_categories[$cat]) ? $ru_categories[$cat] : $cat;
            }, $data['categories']);
        }
    }
    
    return $data;
}

/**
 * Импортировать изображение клиента в медиабиблиотеку
 * 
 * @param string $image_filename Имя файла изображения
 * @param int $post_id ID записи
 * @return int|false ID attachment или false при ошибке
 */
function cw_demo_import_client_image($image_filename, $post_id) {
    $source_path = get_template_directory() . '/src/assets/img/brands/' . $image_filename;
    
    if (!file_exists($source_path)) {
        error_log('Demo Clients: Файл изображения не найден - ' . $image_filename);
        return false;
    }
    
    // Получаем информацию о файле
    $file_type = wp_check_filetype(basename($source_path), null);
    
    if (!$file_type['type']) {
        error_log('Demo Clients: Неизвестный тип файла - ' . $image_filename);
        return false;
    }
    
    // Подготавливаем данные для загрузки
    $upload_dir = wp_upload_dir();
    $file_name = basename($source_path);
    $file_path = $upload_dir['path'] . '/' . $file_name;
    
    // Копируем файл во временную папку uploads
    if (!copy($source_path, $file_path)) {
        error_log('Demo Clients: Не удалось скопировать файл - ' . $image_filename);
        return false;
    }
    
    // Создаем массив для wp_handle_sideload
    $file_array = array(
        'name' => $file_name,
        'tmp_name' => $file_path,
    );
    
    // Загружаем файл в медиабиблиотеку
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    
    $attachment_id = media_handle_sideload($file_array, $post_id);
    
    // Удаляем временный файл, если он остался
    if (file_exists($file_path)) {
        @unlink($file_path);
    }
    
    if (is_wp_error($attachment_id)) {
        error_log('Demo Clients: Ошибка загрузки изображения - ' . $attachment_id->get_error_message());
        return false;
    }
    
    // Устанавливаем родителя для правильной работы системы размеров
    wp_update_post(array(
        'ID' => $attachment_id,
        'post_parent' => $post_id
    ));
    
    // Устанавливаем featured image
    set_post_thumbnail($post_id, $attachment_id);
    
    // Обновляем метаданные для генерации размеров
    $attachment_data = wp_generate_attachment_metadata($attachment_id, get_attached_file($attachment_id));
    wp_update_attachment_metadata($attachment_id, $attachment_data);
    
    return $attachment_id;
}

/**
 * Создать или получить категорию клиента
 * 
 * @param string $category_name Название категории
 * @return int|false ID категории или false при ошибке
 */
function cw_demo_get_or_create_client_category($category_name) {
    $taxonomy = 'clients_category';
    
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
        error_log('Demo Clients: Ошибка создания категории - ' . $term_data->get_error_message());
        return false;
    }
    
    return $term_data['term_id'];
}

/**
 * Определить категорию по имени файла изображения
 * 
 * @param string $image_filename Имя файла изображения
 * @return string Название категории
 */
function cw_demo_get_client_category_by_image($image_filename) {
    // Определяем язык сайта
    $locale = get_locale();
    $is_russian = (strpos($locale, 'ru') === 0);
    
    // Если файл начинается с "c" - первая категория, если с "z" - вторая
    $first_char = strtolower(substr(basename($image_filename), 0, 1));
    
    if ($first_char === 'c') {
        return $is_russian ? 'Основные клиенты' : 'Primary Clients';
    } elseif ($first_char === 'z') {
        return $is_russian ? 'Партнеры' : 'Partners';
    }
    
    // Fallback - по умолчанию
    return $is_russian ? 'Основные клиенты' : 'Primary Clients';
}

/**
 * Создать одну запись клиента
 * 
 * @param array $client_data Данные клиента из JSON
 * @return int|false ID созданной записи или false при ошибке
 */
function cw_demo_create_client_post($client_data) {
    // Проверяем обязательные поля
    if (empty($client_data['title']) || empty($client_data['image'])) {
        error_log('Demo Clients: Отсутствуют обязательные поля');
        return false;
    }
    
    // Подготавливаем данные для wp_insert_post
    $post_data = array(
        'post_title'    => sanitize_text_field($client_data['title']),
        'post_name'     => !empty($client_data['slug']) ? sanitize_title($client_data['slug']) : sanitize_title($client_data['title']),
        'post_status'   => !empty($client_data['status']) ? $client_data['status'] : 'publish',
        'post_type'     => 'clients',
        'post_author'   => get_current_user_id(),
    );
    
    // Вставляем запись
    $post_id = wp_insert_post($post_data);
    
    if (is_wp_error($post_id)) {
        error_log('Demo Clients: ' . __('Error creating record', 'codeweber') . ' - ' . $post_id->get_error_message());
        return false;
    }
    
    // Добавляем мета-поле для идентификации demo записей
    update_post_meta($post_id, '_demo_created', true);
    
    // Добавляем порядок, если указан
    if (!empty($client_data['order'])) {
        update_post_meta($post_id, '_demo_order', intval($client_data['order']));
    }
    
    // Сохраняем название компании, если указано
    if (!empty($client_data['company_name'])) {
        update_post_meta($post_id, '_cw_clients_company_name', sanitize_text_field($client_data['company_name']));
    }
    
    // Сохраняем URL компании, если указан
    if (!empty($client_data['company_url'])) {
        update_post_meta($post_id, '_cw_clients_company_url', esc_url_raw($client_data['company_url']));
    }
    
    // Определяем и назначаем категорию на основе имени файла
    $category_name = cw_demo_get_client_category_by_image($client_data['image']);
    $category_id = cw_demo_get_or_create_client_category($category_name);
    
    if ($category_id) {
        wp_set_post_terms($post_id, array($category_id), 'clients_category');
    }
    
    // Импортируем изображение
    $image_alt = !empty($client_data['image_alt']) ? $client_data['image_alt'] : $client_data['title'];
    $attachment_id = cw_demo_import_client_image($client_data['image'], $post_id);
    
    if ($attachment_id) {
        // Устанавливаем alt текст для изображения
        update_post_meta($attachment_id, '_wp_attachment_image_alt', sanitize_text_field($image_alt));
    }
    
    return $post_id;
}

/**
 * Создать все demo записи клиентов
 * 
 * @return array Результат выполнения ['created' => int, 'errors' => array]
 */
function cw_demo_create_clients() {
    $data = cw_demo_get_clients_data();
    
    if (!$data || empty($data['items'])) {
        return array(
            'success' => false,
            'message' => __('No data found or file is corrupted', 'codeweber'),
            'created' => 0,
            'errors' => array()
        );
    }
    
    $created = 0;
    $errors = array();
    $categories_created = array();
    
    // Определяем язык для категорий
    $locale = get_locale();
    $is_russian = (strpos($locale, 'ru') === 0);
    $category_primary = $is_russian ? 'Основные клиенты' : 'Primary Clients';
    $category_partners = $is_russian ? 'Партнеры' : 'Partners';
    
    // Создаем категории заранее
    $category_c = cw_demo_get_or_create_client_category($category_primary);
    $category_z = cw_demo_get_or_create_client_category($category_partners);
    
    if ($category_c) {
        $categories_created[] = $category_primary;
    }
    if ($category_z) {
        $categories_created[] = $category_partners;
    }
    
    foreach ($data['items'] as $item) {
        $post_id = cw_demo_create_client_post($item);
        
        if ($post_id) {
            $created++;
        } else {
            $errors[] = __('Failed to create:', 'codeweber') . ' ' . (!empty($item['title']) ? $item['title'] : __('unknown', 'codeweber'));
        }
    }
    
    $message = sprintf(__('%1$d of %2$d entries created', 'codeweber'), $created, count($data['items']));
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
 * Удалить все demo записи клиентов
 * 
 * @return array Результат выполнения
 */
function cw_demo_delete_clients() {
    $args = array(
        'post_type' => 'clients',
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
        // Удаляем featured image
        $thumbnail_id = get_post_thumbnail_id($post_id);
        if ($thumbnail_id) {
            wp_delete_attachment($thumbnail_id, true);
        }
        
        // Удаляем запись
        $result = wp_delete_post($post_id, true);
        
        if ($result) {
            $deleted++;
        } else {
            $errors[] = sprintf(__('Failed to delete record ID: %d', 'codeweber'), $post_id);
        }
    }
    
    return array(
        'success' => true,
        'message' => sprintf('Удалено записей: %d', $deleted),
        'deleted' => $deleted,
        'errors' => $errors
    );
}


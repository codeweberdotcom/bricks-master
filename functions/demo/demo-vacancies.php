<?php
/**
 * Demo данные для CPT Vacancies
 * 
 * Функции для создания demo записей типа vacancies
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Получить данные вакансий из JSON файла
 * 
 * @return array|false Массив данных или false при ошибке
 */
function cw_demo_get_vacancies_data() {
    $json_path = get_template_directory() . '/demo/vacancies/data.json';
    
    if (!file_exists($json_path)) {
        return false;
    }
    
    $json_content = file_get_contents($json_path);
    $data = json_decode($json_content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Demo Vacancies: Ошибка парсинга JSON - ' . json_last_error_msg());
        return false;
    }
    
    // Определяем язык сайта
    $locale = get_locale();
    $is_russian = (strpos($locale, 'ru') === 0);
    
    // Адаптируем данные в зависимости от языка
    if (!empty($data['items'])) {
        foreach ($data['items'] as &$item) {
            if ($is_russian) {
                $item['title'] = !empty($item['title_ru']) ? $item['title_ru'] : $item['title_en'];
                $item['company'] = !empty($item['company_ru']) ? $item['company_ru'] : $item['company_en'];
                $item['location'] = !empty($item['location_ru']) ? $item['location_ru'] : $item['location_en'];
                $item['introduction'] = !empty($item['introduction_ru']) ? $item['introduction_ru'] : $item['introduction_en'];
                $item['additional_info'] = !empty($item['additional_info_ru']) ? $item['additional_info_ru'] : $item['additional_info_en'];
                $item['requirements'] = !empty($item['requirements_ru']) ? $item['requirements_ru'] : $item['requirements_en'];
                $item['responsibilities'] = !empty($item['responsibilities_ru']) ? $item['responsibilities_ru'] : $item['responsibilities_en'];
                $item['image_alt'] = !empty($item['image_alt_ru']) ? $item['image_alt_ru'] : $item['image_alt_en'];
                $item['salary'] = !empty($item['salary_ru']) ? $item['salary_ru'] : (!empty($item['salary_en']) ? $item['salary_en'] : '');
            } else {
                $item['title'] = $item['title_en'];
                $item['company'] = $item['company_en'];
                $item['location'] = $item['location_en'];
                $item['introduction'] = $item['introduction_en'];
                $item['additional_info'] = $item['additional_info_en'];
                $item['requirements'] = $item['requirements_en'];
                $item['responsibilities'] = $item['responsibilities_en'];
                $item['image_alt'] = $item['image_alt_en'];
                $item['salary'] = !empty($item['salary_en']) ? $item['salary_en'] : '';
            }
        }
        unset($item); // Сбрасываем ссылку
    }
    
    // Адаптируем vacancy_types
    if (isset($data['vacancy_types'])) {
        foreach ($data['vacancy_types'] as &$type) {
            if ($is_russian) {
                $type['name'] = !empty($type['name_ru']) ? $type['name_ru'] : $type['name_en'];
            } else {
                $type['name'] = $type['name_en'];
            }
        }
        unset($type);
    }
    
    return $data;
}

/**
 * Импортировать изображение вакансии в медиабиблиотеку
 * 
 * @param string $image_filename Имя файла изображения
 * @param int $post_id ID записи
 * @return int|false ID attachment или false при ошибке
 */
function cw_demo_import_vacancy_image($image_filename, $post_id) {
    $source_path = get_template_directory() . '/src/assets/img/photos/' . $image_filename;
    
    if (!file_exists($source_path)) {
        error_log('Demo Vacancies: Файл изображения не найден - ' . $image_filename);
        return false;
    }
    
    // Получаем информацию о файле
    $file_type = wp_check_filetype(basename($source_path), null);
    
    if (!$file_type['type']) {
        error_log('Demo Vacancies: Неизвестный тип файла - ' . $image_filename);
        return false;
    }
    
    // Подготавливаем данные для загрузки
    $upload_dir = wp_upload_dir();
    $file_name = basename($source_path);
    $file_path = $upload_dir['path'] . '/' . $file_name;
    
    // Копируем файл во временную папку uploads
    if (!copy($source_path, $file_path)) {
        error_log('Demo Vacancies: Не удалось скопировать файл - ' . $image_filename);
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
        error_log('Demo Vacancies: Ошибка загрузки изображения - ' . $attachment_id->get_error_message());
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
 * Создать или получить тип вакансии (vacancy_type)
 * 
 * @param array $type_data Данные типа из JSON
 * @return int|false ID типа или false при ошибке
 */
function cw_demo_get_or_create_vacancy_type($type_data) {
    $taxonomy = 'vacancy_type';
    
    // Определяем язык сайта
    $locale = get_locale();
    $is_russian = (strpos($locale, 'ru') === 0);
    $type_name = $is_russian && !empty($type_data['name_ru']) ? $type_data['name_ru'] : $type_data['name_en'];
    
    // Проверяем, существует ли тип
    $term = get_term_by('slug', $type_data['slug'], $taxonomy);
    
    if ($term) {
        return $term->term_id;
    }
    
    // Создаем новый тип
    $term_data = wp_insert_term(
        $type_name,
        $taxonomy,
        array(
            'description' => '',
            'slug' => $type_data['slug']
        )
    );
    
    if (is_wp_error($term_data)) {
        error_log('Demo Vacancies: Ошибка создания типа - ' . $term_data->get_error_message());
        return false;
    }
    
    return $term_data['term_id'];
}

/**
 * Создать одну запись vacancy
 * 
 * @param array $vacancy_data Данные vacancy из JSON
 * @return int|false ID созданной записи или false при ошибке
 */
function cw_demo_create_vacancy_post($vacancy_data) {
    // Проверяем обязательные поля
    if (empty($vacancy_data['title'])) {
        error_log('Demo Vacancies: Отсутствует обязательное поле title');
        return false;
    }
    
    // Подготавливаем данные для wp_insert_post
    $post_data = array(
        'post_title'    => sanitize_text_field($vacancy_data['title']),
        'post_name'     => !empty($vacancy_data['slug']) ? sanitize_title($vacancy_data['slug']) : sanitize_title($vacancy_data['title']),
        'post_status'   => !empty($vacancy_data['status']) ? $vacancy_data['status'] : 'publish',
        'post_type'     => 'vacancies',
        'post_author'   => get_current_user_id(),
    );
    
    // Вставляем запись
    $post_id = wp_insert_post($post_data);
    
    if (is_wp_error($post_id)) {
        error_log('Demo Vacancies: Ошибка создания записи - ' . $post_id->get_error_message());
        return false;
    }
    
    // Добавляем мета-поле для идентификации demo записей
    update_post_meta($post_id, '_demo_created', true);
    
    // Сохраняем все метаполя
    $meta_fields = array(
        'vacancy_company' => !empty($vacancy_data['company']) ? $vacancy_data['company'] : '',
        'vacancy_location' => !empty($vacancy_data['location']) ? $vacancy_data['location'] : '',
        'vacancy_email' => !empty($vacancy_data['email']) ? $vacancy_data['email'] : '',
        'vacancy_apply_url' => !empty($vacancy_data['apply_url']) ? $vacancy_data['apply_url'] : '',
        'vacancy_salary' => !empty($vacancy_data['salary']) ? $vacancy_data['salary'] : '',
        'vacancy_linkedin_url' => !empty($vacancy_data['linkedin_url']) ? $vacancy_data['linkedin_url'] : '',
        'vacancy_telegram_url' => !empty($vacancy_data['telegram_url']) ? $vacancy_data['telegram_url'] : '',
        'vacancy_whatsapp_url' => !empty($vacancy_data['whatsapp_url']) ? $vacancy_data['whatsapp_url'] : '',
        'vacancy_introduction' => !empty($vacancy_data['introduction']) ? $vacancy_data['introduction'] : '',
        'vacancy_additional_info' => !empty($vacancy_data['additional_info']) ? $vacancy_data['additional_info'] : '',
        'vacancy_employment_type' => !empty($vacancy_data['employment_type']) ? $vacancy_data['employment_type'] : '',
        'vacancy_experience' => !empty($vacancy_data['experience']) ? $vacancy_data['experience'] : '',
        'vacancy_education' => !empty($vacancy_data['education']) ? $vacancy_data['education'] : '',
        'vacancy_status' => !empty($vacancy_data['status']) ? $vacancy_data['status'] : '',
    );
    
    foreach ($meta_fields as $key => $value) {
        if (!empty($value)) {
            update_post_meta($post_id, '_' . $key, sanitize_text_field($value));
        }
    }
    
    // Сохраняем массивы
    $array_fields = array(
        'vacancy_requirements' => !empty($vacancy_data['requirements']) && is_array($vacancy_data['requirements']) ? $vacancy_data['requirements'] : array(),
        'vacancy_responsibilities' => !empty($vacancy_data['responsibilities']) && is_array($vacancy_data['responsibilities']) ? $vacancy_data['responsibilities'] : array(),
        'vacancy_languages' => !empty($vacancy_data['languages']) && is_array($vacancy_data['languages']) ? $vacancy_data['languages'] : array(),
        'vacancy_skills' => !empty($vacancy_data['skills']) && is_array($vacancy_data['skills']) ? $vacancy_data['skills'] : array(),
    );
    
    foreach ($array_fields as $key => $value) {
        if (!empty($value)) {
            $values = array_map('sanitize_textarea_field', $value);
            $values = array_filter($values);
            update_post_meta($post_id, '_' . $key, $values);
        }
    }
    
    // Назначаем тип вакансии, если указан
    if (!empty($vacancy_data['vacancy_type_slug'])) {
        $term = get_term_by('slug', $vacancy_data['vacancy_type_slug'], 'vacancy_type');
        if ($term) {
            wp_set_post_terms($post_id, array($term->term_id), 'vacancy_type');
        }
    }
    
    // Импортируем изображение, если указано
    if (!empty($vacancy_data['image'])) {
        $image_alt = !empty($vacancy_data['image_alt']) ? $vacancy_data['image_alt'] : $vacancy_data['title'];
        $attachment_id = cw_demo_import_vacancy_image($vacancy_data['image'], $post_id);
        
        if ($attachment_id) {
            // Устанавливаем alt текст для изображения
            update_post_meta($attachment_id, '_wp_attachment_image_alt', sanitize_text_field($image_alt));
        }
    }
    
    return $post_id;
}

/**
 * Создать все demo записи vacancies
 * 
 * @return array Результат выполнения ['created' => int, 'errors' => array]
 */
function cw_demo_create_vacancies() {
    $data = cw_demo_get_vacancies_data();
    
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
    $types_created = array();
    
    // Сначала создаем типы вакансий
    if (!empty($data['vacancy_types'])) {
        foreach ($data['vacancy_types'] as $type) {
            $type_id = cw_demo_get_or_create_vacancy_type($type);
            if ($type_id) {
                $locale = get_locale();
                $is_russian = (strpos($locale, 'ru') === 0);
                $type_name = $is_russian && !empty($type['name_ru']) ? $type['name_ru'] : $type['name_en'];
                $types_created[] = $type_name;
            }
        }
    }
    
    // Затем создаем записи vacancies
    foreach ($data['items'] as $item) {
        $post_id = cw_demo_create_vacancy_post($item);
        
        if ($post_id) {
            $created++;
        } else {
            $errors[] = 'Не удалось создать: ' . (!empty($item['title']) ? $item['title'] : 'неизвестно');
        }
    }
    
    $message = sprintf('Создано записей: %d из %d', $created, count($data['items']));
    if (!empty($types_created)) {
        $message .= '. Типы вакансий: ' . implode(', ', $types_created);
    }
    
    return array(
        'success' => true,
        'message' => $message,
        'created' => $created,
        'total' => count($data['items']),
        'types' => $types_created,
        'errors' => $errors
    );
}

/**
 * Удалить все demo записи vacancies
 * 
 * @return array Результат выполнения
 */
function cw_demo_delete_vacancies() {
    $args = array(
        'post_type' => 'vacancies',
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


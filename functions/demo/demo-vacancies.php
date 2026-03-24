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
    $locale = get_locale();
    $is_russian = (strpos($locale, 'ru') === 0);
    $dir = get_template_directory() . '/demo/vacancies/';

    // Русский — data.json, английский — data-en.json (если есть)
    if ($is_russian) {
        $json_path = $dir . 'data.json';
    } else {
        $json_path = file_exists($dir . 'data-en.json') ? $dir . 'data-en.json' : $dir . 'data.json';
    }

    if (!file_exists($json_path)) {
        return false;
    }

    $json_content = file_get_contents($json_path);
    $data = json_decode($json_content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Demo Vacancies: Ошибка парсинга JSON - ' . json_last_error_msg());
        return false;
    }

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
                $item['experience'] = !empty($item['experience_ru']) ? $item['experience_ru'] : (isset($item['experience']) ? $item['experience'] : '');
                $item['education'] = !empty($item['education_ru']) ? $item['education_ru'] : (isset($item['education']) ? $item['education'] : '');
                $item['languages'] = !empty($item['languages_ru']) ? $item['languages_ru'] : (isset($item['languages_en']) ? $item['languages_en'] : (isset($item['languages']) ? $item['languages'] : []));
                $item['skills'] = !empty($item['skills_ru']) ? $item['skills_ru'] : (isset($item['skills_en']) ? $item['skills_en'] : (isset($item['skills']) ? $item['skills'] : []));
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
                $item['experience'] = !empty($item['experience_en']) ? $item['experience_en'] : (isset($item['experience']) ? $item['experience'] : '');
                $item['education'] = !empty($item['education_en']) ? $item['education_en'] : (isset($item['education']) ? $item['education'] : '');
                $item['languages'] = !empty($item['languages_en']) ? $item['languages_en'] : (isset($item['languages']) ? $item['languages'] : []);
                $item['skills'] = !empty($item['skills_en']) ? $item['skills_en'] : (isset($item['skills']) ? $item['skills'] : []);
            }
        }
        unset($item); // Сбрасываем ссылку
    }
    
    // Адаптируем vacancy_schedules по языку (только валидные элементы: массив с name_ru/name_en)
    if (isset($data['vacancy_schedules']) && is_array($data['vacancy_schedules'])) {
        $valid_schedules = [];
        foreach ($data['vacancy_schedules'] as $schedule) {
            if (!is_array($schedule)) {
                continue;
            }
            $name_ru = isset($schedule['name_ru']) ? trim((string) $schedule['name_ru']) : '';
            $name_en = isset($schedule['name_en']) ? trim((string) $schedule['name_en']) : '';
            if ($name_ru === '' && $name_en === '') {
                continue;
            }
            if (cw_demo_schedule_is_numeric_id($name_ru) || cw_demo_schedule_is_numeric_id($name_en)) {
                continue;
            }
            $schedule['name'] = $is_russian && $name_ru !== '' ? $name_ru : $name_en;
            $valid_schedules[] = $schedule;
        }
        $data['vacancy_schedules'] = $valid_schedules;
    }

    // Адаптируем vacancy_types по языку (только валидные элементы)
    if (isset($data['vacancy_types']) && is_array($data['vacancy_types'])) {
        $valid_types = [];
        foreach ($data['vacancy_types'] as $type) {
            if (!is_array($type)) {
                continue;
            }
            $name_ru = isset($type['name_ru']) ? trim((string) $type['name_ru']) : '';
            $name_en = isset($type['name_en']) ? trim((string) $type['name_en']) : '';
            if ($name_ru === '' && $name_en === '') {
                continue;
            }
            if (cw_demo_schedule_is_numeric_id($name_ru) || cw_demo_schedule_is_numeric_id($name_en)) {
                continue;
            }
            $type['name'] = $is_russian && $name_ru !== '' ? $name_ru : $name_en;
            $valid_types[] = $type;
        }
        $data['vacancy_types'] = $valid_types;
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
 * Импортировать PDF файл вакансии в медиабиблиотеку
 *
 * @param string $pdf_filename Имя файла PDF
 * @param int $post_id ID записи
 * @return int|false ID attachment или false при ошибке
 */
function cw_demo_import_vacancy_pdf($pdf_filename, $post_id) {
    $source_path = get_template_directory() . '/src/docs/' . $pdf_filename;

    if (!file_exists($source_path)) {
        error_log('Demo Vacancies: Файл PDF не найден - ' . $pdf_filename);
        return false;
    }

    $file_type = wp_check_filetype(basename($source_path), null);
    if (!$file_type['type']) {
        error_log('Demo Vacancies: Неизвестный тип файла - ' . $pdf_filename);
        return false;
    }

    $upload_dir = wp_upload_dir();
    $file_name = basename($source_path);
    $file_path = $upload_dir['path'] . '/' . $file_name;

    if (!copy($source_path, $file_path)) {
        error_log('Demo Vacancies: Не удалось скопировать файл - ' . $pdf_filename);
        return false;
    }

    $file_array = array(
        'name' => $file_name,
        'tmp_name' => $file_path,
    );

    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    $attachment_id = media_handle_sideload($file_array, $post_id);

    if (file_exists($file_path)) {
        @unlink($file_path);
    }

    if (is_wp_error($attachment_id)) {
        error_log('Demo Vacancies: Ошибка загрузки PDF - ' . $attachment_id->get_error_message());
        return false;
    }

    wp_update_post(array(
        'ID' => $attachment_id,
        'post_parent' => $post_id
    ));

    return $attachment_id;
}

/**
 * Проверка, что строка похожа на числовой ID (не допустима как имя/slug термина графика).
 *
 * @param string $value
 * @return bool
 */
function cw_demo_schedule_is_numeric_id($value) {
    return $value !== '' && is_numeric($value);
}

/**
 * Создать или получить термин графика работы (vacancy_schedule).
 * Не создаёт термины с именем или slug в виде числа (ID по ошибке: 153, 173, 176 и т.д.).
 *
 * @param array $schedule_data Данные из JSON: name_ru, name_en, slug
 * @return int|false ID термина или false при ошибке / пропуске
 */
function cw_demo_get_or_create_vacancy_schedule($schedule_data) {
    if (!is_array($schedule_data)) {
        return false;
    }
    $taxonomy = 'vacancy_schedule';
    $slug = isset($schedule_data['slug']) ? trim((string) $schedule_data['slug']) : '';
    $name_ru = isset($schedule_data['name_ru']) ? trim((string) $schedule_data['name_ru']) : '';
    $name_en = isset($schedule_data['name_en']) ? trim((string) $schedule_data['name_en']) : '';
    if (cw_demo_schedule_is_numeric_id($slug) || cw_demo_schedule_is_numeric_id($name_ru) || cw_demo_schedule_is_numeric_id($name_en)) {
        return false;
    }
    $locale = get_locale();
    $is_russian = (strpos($locale, 'ru') === 0);
    $name = $is_russian && $name_ru !== '' ? $name_ru : $name_en;
    if ($name === '' || cw_demo_schedule_is_numeric_id($name)) {
        return false;
    }
    $term = get_term_by('slug', $slug, $taxonomy);
    if ($term) {
        return $term->term_id;
    }
    $term_data = wp_insert_term($name, $taxonomy, array('slug' => $slug));
    if (is_wp_error($term_data)) {
        error_log('Demo Vacancies: Ошибка создания графика - ' . $term_data->get_error_message());
        return false;
    }
    return $term_data['term_id'];
}

/**
 * Создать или получить термин типа занятости (vacancy_type).
 *
 * @param array $type_data Данные из JSON: name_ru, name_en, slug
 * @return int|false ID термина или false при ошибке / пропуске
 */
function cw_demo_get_or_create_vacancy_type($type_data) {
    if (!is_array($type_data)) {
        return false;
    }
    $taxonomy = 'vacancy_type';
    $slug = isset($type_data['slug']) ? trim((string) $type_data['slug']) : '';
    $name_ru = isset($type_data['name_ru']) ? trim((string) $type_data['name_ru']) : '';
    $name_en = isset($type_data['name_en']) ? trim((string) $type_data['name_en']) : '';
    if (cw_demo_schedule_is_numeric_id($slug) || cw_demo_schedule_is_numeric_id($name_ru) || cw_demo_schedule_is_numeric_id($name_en)) {
        return false;
    }
    $locale = get_locale();
    $is_russian = (strpos($locale, 'ru') === 0);
    $name = $is_russian && $name_ru !== '' ? $name_ru : $name_en;
    if ($name === '' || cw_demo_schedule_is_numeric_id($name)) {
        return false;
    }
    $term = get_term_by('slug', $slug, $taxonomy);
    if ($term) {
        return $term->term_id;
    }
    $term_data = wp_insert_term($name, $taxonomy, array('slug' => $slug));
    if (is_wp_error($term_data)) {
        error_log('Demo Vacancies: Ошибка создания типа занятости - ' . $term_data->get_error_message());
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
    
    // Подготавливаем данные для wp_insert_post (post_status всегда publish; статус вакансии open/closed — в мета _vacancy_status)
    $post_data = array(
        'post_title'    => sanitize_text_field($vacancy_data['title']),
        'post_name'     => !empty($vacancy_data['slug']) ? sanitize_title($vacancy_data['slug']) : sanitize_title($vacancy_data['title']),
        'post_status'   => 'publish',
        'post_type'     => 'vacancies',
        'post_author'   => get_current_user_id(),
    );
    
    // Вставляем запись
    $post_id = wp_insert_post($post_data);
    
    if (is_wp_error($post_id)) {
        error_log('Demo Vacancies: ' . __('Error creating record', 'codeweber') . ' - ' . $post_id->get_error_message());
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
        'vacancy_requirements' => !empty($vacancy_data['requirements']) && is_array($vacancy_data['requirements']) ? $vacancy_data['requirements'] : [],
        'vacancy_responsibilities' => !empty($vacancy_data['responsibilities']) && is_array($vacancy_data['responsibilities']) ? $vacancy_data['responsibilities'] : [],
        'vacancy_languages' => !empty($vacancy_data['languages']) && is_array($vacancy_data['languages']) ? $vacancy_data['languages'] : [],
        'vacancy_skills' => !empty($vacancy_data['skills']) && is_array($vacancy_data['skills']) ? $vacancy_data['skills'] : [],
    );
    
    foreach ($array_fields as $key => $value) {
        if (!empty($value)) {
            $values = array_map('sanitize_textarea_field', $value);
            $values = array_filter($values);
            update_post_meta($post_id, '_' . $key, $values);
        }
    }
    
    // Назначаем график работы, если указан
    if (!empty($vacancy_data['vacancy_schedule_slug'])) {
        $term = get_term_by('slug', $vacancy_data['vacancy_schedule_slug'], 'vacancy_schedule');
        if ($term) {
            wp_set_post_terms($post_id, array($term->term_id), 'vacancy_schedule');
        }
    }

    // Назначаем тип занятости, если указан
    if (!empty($vacancy_data['vacancy_type_slug'])) {
        $type_term = get_term_by('slug', $vacancy_data['vacancy_type_slug'], 'vacancy_type');
        if ($type_term) {
            wp_set_post_terms($post_id, array($type_term->term_id), 'vacancy_type');
        }
    }
    
    // Карта Яндекса: сохраняем координаты и показ карты
    if (!empty($vacancy_data['show_map']) && $vacancy_data['show_map'] !== '0' && !empty($vacancy_data['latitude']) && !empty($vacancy_data['longitude'])) {
        update_post_meta($post_id, '_vacancy_show_map', '1');
        update_post_meta($post_id, '_vacancy_latitude', sanitize_text_field($vacancy_data['latitude']));
        update_post_meta($post_id, '_vacancy_longitude', sanitize_text_field($vacancy_data['longitude']));
        if (!empty($vacancy_data['zoom'])) {
            update_post_meta($post_id, '_vacancy_zoom', absint($vacancy_data['zoom']));
        }
        if (!empty($vacancy_data['yandex_address'])) {
            update_post_meta($post_id, '_vacancy_yandex_address', sanitize_text_field($vacancy_data['yandex_address']));
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
    
    // Импортируем PDF файл, если указан
    if (!empty($vacancy_data['pdf'])) {
        $pdf_attachment_id = cw_demo_import_vacancy_pdf($vacancy_data['pdf'], $post_id);
        
        if ($pdf_attachment_id) {
            // Сохраняем ID PDF файла в метаполе
            update_post_meta($post_id, '_vacancy_pdf', $pdf_attachment_id);
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
            'message' => __('No data found or file is corrupted', 'codeweber'),
            'created' => 0,
            'errors' => []
        );
    }
    
    $created = 0;
    $errors = [];

    // Создаём термины графика только из валидных объектов (name_ru/name_en + slug не число); данные уже отфильтрованы в cw_demo_get_vacancies_data
    if (!empty($data['vacancy_schedules']) && is_array($data['vacancy_schedules'])) {
        foreach ($data['vacancy_schedules'] as $schedule) {
            if (!is_array($schedule)) {
                continue;
            }
            cw_demo_get_or_create_vacancy_schedule($schedule);
        }
    }

    // Создаём термины типа занятости (vacancy_type)
    if (!empty($data['vacancy_types']) && is_array($data['vacancy_types'])) {
        foreach ($data['vacancy_types'] as $type) {
            if (!is_array($type)) {
                continue;
            }
            cw_demo_get_or_create_vacancy_type($type);
        }
    }
    
    // Создаем записи vacancies
    foreach ($data['items'] as $item) {
        $post_id = cw_demo_create_vacancy_post($item);
        
        if ($post_id) {
            $created++;
        } else {
            $errors[] = 'Не удалось создать: ' . (!empty($item['title']) ? $item['title'] : 'неизвестно');
        }
    }
    
    $message = sprintf(__('%1$d of %2$d entries created', 'codeweber'), $created, count($data['items']));
    
    return array(
        'success' => true,
        'message' => $message,
        'created' => $created,
        'total' => count($data['items']),
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
    $errors = [];
    
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
        'message' => sprintf(__('%d entries deleted', 'codeweber'), $deleted),
        'deleted' => $deleted,
        'errors' => $errors
    );
}


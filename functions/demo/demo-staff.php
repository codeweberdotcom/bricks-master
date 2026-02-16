<?php
/**
 * Demo данные для CPT Staff
 * 
 * Функции для создания demo записей типа staff
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Получить данные staff из JSON файла
 * 
 * @return array|false Массив данных или false при ошибке
 */
function cw_demo_get_staff_data() {
    $json_path = get_template_directory() . '/demo/staff/data.json';
    
    if (!file_exists($json_path)) {
        return false;
    }
    
    $json_content = file_get_contents($json_path);
    $data = json_decode($json_content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Demo Staff: Ошибка парсинга JSON - ' . json_last_error_msg());
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
                $item['position'] = !empty($item['position_ru']) ? $item['position_ru'] : $item['position_en'];
                $item['name'] = !empty($item['name_ru']) ? $item['name_ru'] : $item['name_en'];
                $item['surname'] = !empty($item['surname_ru']) ? $item['surname_ru'] : $item['surname_en'];
                $item['company'] = !empty($item['company_ru']) ? $item['company_ru'] : $item['company_en'];
                $item['country'] = !empty($item['country_ru']) ? $item['country_ru'] : $item['country_en'];
                $item['region'] = !empty($item['region_ru']) ? $item['region_ru'] : $item['region_en'];
                $item['city'] = !empty($item['city_ru']) ? $item['city_ru'] : $item['city_en'];
                $item['street'] = !empty($item['street_ru']) ? $item['street_ru'] : $item['street_en'];
                $item['image_alt'] = !empty($item['image_alt_ru']) ? $item['image_alt_ru'] : $item['image_alt_en'];
                // Используем русские версии полей, если они есть
                $item['email'] = !empty($item['email_ru']) ? $item['email_ru'] : $item['email'];
                $item['phone'] = !empty($item['phone_ru']) ? $item['phone_ru'] : $item['phone'];
                $item['job_phone'] = !empty($item['job_phone_ru']) ? $item['job_phone_ru'] : $item['job_phone'];
                $item['postal_code'] = !empty($item['postal_code_ru']) ? $item['postal_code_ru'] : $item['postal_code'];
                // Используем русские соцсети (VK, Telegram, WhatsApp)
                if (!empty($item['social_ru'])) {
                    $item['social'] = $item['social_ru'];
                } elseif (!empty($item['social_en'])) {
                    // Если русских нет, используем английские, но исключаем Facebook, LinkedIn, Instagram
                    $item['social'] = array();
                    if (!empty($item['social_en']['twitter'])) {
                        $item['social']['twitter'] = $item['social_en']['twitter'];
                    }
                    if (!empty($item['social_en']['telegram'])) {
                        $item['social']['telegram'] = $item['social_en']['telegram'];
                    }
                    if (!empty($item['social_en']['vk'])) {
                        $item['social']['vk'] = $item['social_en']['vk'];
                    }
                    if (!empty($item['social_en']['whatsapp'])) {
                        $item['social']['whatsapp'] = $item['social_en']['whatsapp'];
                    }
                    if (!empty($item['social_en']['skype'])) {
                        $item['social']['skype'] = $item['social_en']['skype'];
                    }
                    if (!empty($item['social_en']['website'])) {
                        $item['social']['website'] = $item['social_en']['website'];
                    }
                }
            } else {
                $item['title'] = $item['title_en'];
                $item['position'] = $item['position_en'];
                $item['name'] = $item['name_en'];
                $item['surname'] = $item['surname_en'];
                $item['company'] = $item['company_en'];
                $item['country'] = $item['country_en'];
                $item['region'] = $item['region_en'];
                $item['city'] = $item['city_en'];
                $item['street'] = $item['street_en'];
                $item['image_alt'] = $item['image_alt_en'];
                // Для английского используем базовые поля
                if (empty($item['email'])) {
                    $item['email'] = !empty($item['email_en']) ? $item['email_en'] : '';
                }
                if (empty($item['phone'])) {
                    $item['phone'] = !empty($item['phone_en']) ? $item['phone_en'] : '';
                }
                if (empty($item['job_phone'])) {
                    $item['job_phone'] = !empty($item['job_phone_en']) ? $item['job_phone_en'] : '';
                }
                if (empty($item['postal_code'])) {
                    $item['postal_code'] = !empty($item['postal_code_en']) ? $item['postal_code_en'] : '';
                }
                // Используем английские соцсети (Facebook, Twitter, LinkedIn, Instagram)
                if (!empty($item['social_en'])) {
                    $item['social'] = $item['social_en'];
                } elseif (!empty($item['social_ru'])) {
                    // Если английских нет, используем русские, но исключаем VK
                    $item['social'] = array();
                    if (!empty($item['social_ru']['telegram'])) {
                        $item['social']['telegram'] = $item['social_ru']['telegram'];
                    }
                    if (!empty($item['social_ru']['whatsapp'])) {
                        $item['social']['whatsapp'] = $item['social_ru']['whatsapp'];
                    }
                    if (!empty($item['social_ru']['skype'])) {
                        $item['social']['skype'] = $item['social_ru']['skype'];
                    }
                    if (!empty($item['social_ru']['website'])) {
                        $item['social']['website'] = $item['social_ru']['website'];
                    }
                }
            }
        }
        unset($item); // Сбрасываем ссылку
    }
    
    // Адаптируем departments
    if (isset($data['departments'])) {
        foreach ($data['departments'] as &$dept) {
            if ($is_russian) {
                $dept['name'] = !empty($dept['name_ru']) ? $dept['name_ru'] : $dept['name_en'];
            } else {
                $dept['name'] = $dept['name_en'];
            }
        }
        unset($dept);
    }
    
    return $data;
}

/**
 * Импортировать изображение staff в медиабиблиотеку
 * 
 * @param string $image_filename Имя файла изображения
 * @param int $post_id ID записи
 * @return int|false ID attachment или false при ошибке
 */
function cw_demo_import_staff_image($image_filename, $post_id) {
    $source_path = get_template_directory() . '/src/assets/img/avatars/' . $image_filename;
    
    if (!file_exists($source_path)) {
        error_log('Demo Staff: Файл изображения не найден - ' . $image_filename);
        return false;
    }
    
    // Получаем информацию о файле
    $file_type = wp_check_filetype(basename($source_path), null);
    
    if (!$file_type['type']) {
        error_log('Demo Staff: Неизвестный тип файла - ' . $image_filename);
        return false;
    }
    
    // Подготавливаем данные для загрузки
    $upload_dir = wp_upload_dir();
    $file_name = basename($source_path);
    $file_path = $upload_dir['path'] . '/' . $file_name;
    
    // Копируем файл во временную папку uploads
    if (!copy($source_path, $file_path)) {
        error_log('Demo Staff: Не удалось скопировать файл - ' . $image_filename);
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
        error_log('Demo Staff: Ошибка загрузки изображения - ' . $attachment_id->get_error_message());
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
 * Создать или получить отдел (department)
 * 
 * @param array $dept_data Данные отдела из JSON
 * @return int|false ID отдела или false при ошибке
 */
function cw_demo_get_or_create_department($dept_data) {
    $taxonomy = 'departments';
    
    // Определяем язык сайта
    $locale = get_locale();
    $is_russian = (strpos($locale, 'ru') === 0);
    $dept_name = $is_russian && !empty($dept_data['name_ru']) ? $dept_data['name_ru'] : $dept_data['name_en'];
    
    // Проверяем, существует ли отдел
    $term = get_term_by('slug', $dept_data['slug'], $taxonomy);
    
    if ($term) {
        return $term->term_id;
    }
    
    // Создаем новый отдел
    $term_data = wp_insert_term(
        $dept_name,
        $taxonomy,
        array(
            'description' => '',
            'slug' => $dept_data['slug']
        )
    );
    
    if (is_wp_error($term_data)) {
        error_log('Demo Staff: Ошибка создания отдела - ' . $term_data->get_error_message());
        return false;
    }
    
    return $term_data['term_id'];
}

/**
 * Создать одну запись staff
 * 
 * @param array $staff_data Данные staff из JSON
 * @return int|false ID созданной записи или false при ошибке
 */
function cw_demo_create_staff_post($staff_data) {
    // Проверяем обязательные поля
    if (empty($staff_data['title']) || empty($staff_data['image'])) {
        error_log('Demo Staff: Отсутствуют обязательные поля');
        return false;
    }
    
    // Подготавливаем данные для wp_insert_post
    $post_data = array(
        'post_title'    => sanitize_text_field($staff_data['title']),
        'post_name'     => !empty($staff_data['slug']) ? sanitize_title($staff_data['slug']) : sanitize_title($staff_data['title']),
        'post_status'   => !empty($staff_data['status']) ? $staff_data['status'] : 'publish',
        'post_type'     => 'staff',
        'post_author'   => get_current_user_id(),
    );
    
    // Вставляем запись
    $post_id = wp_insert_post($post_data);
    
    if (is_wp_error($post_id)) {
        error_log('Demo Staff: ' . __('Error creating record', 'codeweber') . ' - ' . $post_id->get_error_message());
        return false;
    }
    
    // Добавляем мета-поле для идентификации demo записей
    update_post_meta($post_id, '_demo_created', true);
    
    // Добавляем порядок, если указан
    if (!empty($staff_data['order'])) {
        update_post_meta($post_id, '_demo_order', intval($staff_data['order']));
    }
    
    // Сохраняем все метаполя
    $meta_fields = array(
        'staff_position' => !empty($staff_data['position']) ? $staff_data['position'] : '',
        'staff_name' => !empty($staff_data['name']) ? $staff_data['name'] : '',
        'staff_surname' => !empty($staff_data['surname']) ? $staff_data['surname'] : '',
        'staff_email' => !empty($staff_data['email']) ? $staff_data['email'] : '',
        'staff_phone' => !empty($staff_data['phone']) ? $staff_data['phone'] : '',
        'staff_company' => !empty($staff_data['company']) ? $staff_data['company'] : '',
        'staff_job_phone' => !empty($staff_data['job_phone']) ? $staff_data['job_phone'] : '',
        'staff_country' => !empty($staff_data['country']) ? $staff_data['country'] : '',
        'staff_region' => !empty($staff_data['region']) ? $staff_data['region'] : '',
        'staff_city' => !empty($staff_data['city']) ? $staff_data['city'] : '',
        'staff_street' => !empty($staff_data['street']) ? $staff_data['street'] : '',
        'staff_postal_code' => !empty($staff_data['postal_code']) ? $staff_data['postal_code'] : '',
    );
    
    foreach ($meta_fields as $key => $value) {
        if (!empty($value)) {
            update_post_meta($post_id, '_' . $key, sanitize_text_field($value));
        }
    }
    
    // Сохраняем соцсети, если они есть
    if (!empty($staff_data['social']) && is_array($staff_data['social'])) {
        $social_fields = ['facebook', 'twitter', 'linkedin', 'instagram', 'telegram', 'vk', 'whatsapp', 'skype', 'website'];
        foreach ($social_fields as $social_key) {
            if (!empty($staff_data['social'][$social_key])) {
                // Для Skype используем sanitize_text_field, для остальных - esc_url_raw
                if ($social_key === 'skype') {
                    update_post_meta($post_id, '_staff_' . $social_key, sanitize_text_field($staff_data['social'][$social_key]));
                } else {
                    update_post_meta($post_id, '_staff_' . $social_key, esc_url_raw($staff_data['social'][$social_key]));
                }
            }
        }
    }
    
    // Назначаем отдел, если указан
    if (!empty($staff_data['department_slug'])) {
        $term = get_term_by('slug', $staff_data['department_slug'], 'departments');
        if ($term) {
            wp_set_post_terms($post_id, array($term->term_id), 'departments');
            // Также сохраняем ID отдела в метаполе
            update_post_meta($post_id, '_staff_department', $term->term_id);
        }
    }
    
    // Импортируем изображение
    $image_alt = !empty($staff_data['image_alt']) ? $staff_data['image_alt'] : $staff_data['title'];
    $attachment_id = cw_demo_import_staff_image($staff_data['image'], $post_id);
    
    if ($attachment_id) {
        // Устанавливаем alt текст для изображения
        update_post_meta($attachment_id, '_wp_attachment_image_alt', sanitize_text_field($image_alt));
    }
    
    return $post_id;
}

/**
 * Создать все demo записи staff
 * 
 * @return array Результат выполнения ['created' => int, 'errors' => array]
 */
function cw_demo_create_staff() {
    $data = cw_demo_get_staff_data();
    
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
    $departments_created = array();
    
    // Сначала создаем отделы
    if (!empty($data['departments'])) {
        foreach ($data['departments'] as $dept) {
            $dept_id = cw_demo_get_or_create_department($dept);
            if ($dept_id) {
                $locale = get_locale();
                $is_russian = (strpos($locale, 'ru') === 0);
                $dept_name = $is_russian && !empty($dept['name_ru']) ? $dept['name_ru'] : $dept['name_en'];
                $departments_created[] = $dept_name;
            }
        }
    }
    
    // Затем создаем записи staff
    foreach ($data['items'] as $item) {
        $post_id = cw_demo_create_staff_post($item);
        
        if ($post_id) {
            $created++;
        } else {
            $errors[] = __('Failed to create:', 'codeweber') . ' ' . (!empty($item['title']) ? $item['title'] : __('unknown', 'codeweber'));
        }
    }
    
    $message = sprintf(__('%1$d of %2$d entries created', 'codeweber'), $created, count($data['items']));
    if (!empty($departments_created)) {
        $message .= '. Отделы: ' . implode(', ', $departments_created);
    }
    
    return array(
        'success' => true,
        'message' => $message,
        'created' => $created,
        'total' => count($data['items']),
        'departments' => $departments_created,
        'errors' => $errors
    );
}

/**
 * Удалить все demo записи staff
 * 
 * @return array Результат выполнения
 */
function cw_demo_delete_staff() {
    $args = array(
        'post_type' => 'staff',
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
        'message' => sprintf(__('%d entries deleted', 'codeweber'), $deleted),
        'deleted' => $deleted,
        'errors' => $errors
    );
}


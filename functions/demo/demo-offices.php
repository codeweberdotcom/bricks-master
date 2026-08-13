<?php
/**
 * Demo данные для CPT Offices
 * 
 * Функции для создания demo записей типа offices с городами России
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Создать города России (термины таксономии towns)
 * 
 * @return array Результат выполнения
 */
function cw_demo_create_towns() {
    $towns = array(
        'Москва',
        'Санкт-Петербург',
        'Новосибирск',
        'Екатеринбург',
        'Казань',
        'Нижний Новгород',
        'Челябинск',
        'Самара',
        'Омск',
        'Ростов-на-Дону',
        'Уфа',
        'Красноярск',
        'Воронеж',
        'Пермь',
        'Волгоград'
    );
    
    $created = 0;
    $errors = [];
    
    foreach ($towns as $town_name) {
        // Проверяем, существует ли уже такой термин
        $term = get_term_by('name', $town_name, 'towns');
        
        if ($term) {
            continue; // Пропускаем, если уже существует
        }
        
        // Создаем термин
        $term_data = wp_insert_term(
            $town_name,
            'towns',
            array(
                'description' => '',
                'slug' => sanitize_title($town_name)
            )
        );
        
        if (is_wp_error($term_data)) {
            $errors[] = __('Failed to create city:', 'codeweber') . ' ' . $town_name . ' - ' . $term_data->get_error_message();
        } else {
            $created++;
        }
    }
    
    return array(
        'success' => true,
        'message' => sprintf(__('%1$d of %2$d cities created', 'codeweber'), $created, count($towns)),
        'created' => $created,
        'total' => count($towns),
        'errors' => $errors
    );
}

/**
 * Получить данные офисов с адресами Москвы
 * 
 * @return array Массив данных офисов
 */
function cw_demo_get_legacy_moscow_offices_data() {
    // Адреса в Москве с координатами
    $moscow_offices = array(
        array(
            'title' => 'Офис на Тверской',
            'country' => 'Россия',
            'region' => 'Московская область',
            'city' => 'Москва',
            'street' => 'ул. Тверская, д. 10, офис 100',
            'postal_code' => '101000',
            'full_address' => 'Россия, Москва, ул. Тверская, д. 10, офис 100',
            'phone' => '+7 (495) 123-45-67',
            'phone_2' => '+7 (495) 123-45-68',
            'email' => 'moscow1@example.com',
            'website' => 'https://example.com',
            'working_hours' => 'Пн-Пт: 9:00-18:00',
            'latitude' => 55.7558,
            'longitude' => 37.6173,
            'zoom' => 15,
            'yandex_address' => 'Россия, Москва, Тверская улица, 10',
            'description' => 'Главный офис компании в центре Москвы'
        ),
        array(
            'title' => 'Офис на Арбате',
            'country' => 'Россия',
            'region' => 'Московская область',
            'city' => 'Москва',
            'street' => 'ул. Арбат, д. 25, офис 205',
            'postal_code' => '119002',
            'full_address' => 'Россия, Москва, ул. Арбат, д. 25, офис 205',
            'phone' => '+7 (495) 234-56-78',
            'phone_2' => '',
            'email' => 'moscow2@example.com',
            'website' => 'https://example.com',
            'working_hours' => 'Пн-Пт: 10:00-19:00',
            'latitude' => 55.7520,
            'longitude' => 37.5914,
            'zoom' => 15,
            'yandex_address' => 'Россия, Москва, Арбат, 25',
            'description' => 'Офис в историческом центре Москвы'
        ),
        array(
            'title' => 'Офис на Ленинском проспекте',
            'country' => 'Россия',
            'region' => 'Московская область',
            'city' => 'Москва',
            'street' => 'Ленинский проспект, д. 15, офис 301',
            'postal_code' => '119071',
            'full_address' => 'Россия, Москва, Ленинский проспект, д. 15, офис 301',
            'phone' => '+7 (495) 345-67-89',
            'phone_2' => '+7 (495) 345-67-90',
            'email' => 'moscow3@example.com',
            'website' => 'https://example.com',
            'working_hours' => 'Пн-Пт: 9:00-18:00, Сб: 10:00-15:00',
            'latitude' => 55.7000,
            'longitude' => 37.5833,
            'zoom' => 14,
            'yandex_address' => 'Россия, Москва, Ленинский проспект, 15',
            'description' => 'Офис на Ленинском проспекте'
        ),
        array(
            'title' => 'Офис на Кутузовском проспекте',
            'country' => 'Россия',
            'region' => 'Московская область',
            'city' => 'Москва',
            'street' => 'Кутузовский проспект, д. 32, офис 401',
            'postal_code' => '121165',
            'full_address' => 'Россия, Москва, Кутузовский проспект, д. 32, офис 401',
            'phone' => '+7 (495) 456-78-90',
            'phone_2' => '',
            'email' => 'moscow4@example.com',
            'website' => 'https://example.com',
            'working_hours' => 'Пн-Пт: 9:00-18:00',
            'latitude' => 55.7439,
            'longitude' => 37.5350,
            'zoom' => 15,
            'yandex_address' => 'Россия, Москва, Кутузовский проспект, 32',
            'description' => 'Офис на Кутузовском проспекте'
        ),
        array(
            'title' => 'Офис на Садовом кольце',
            'country' => 'Россия',
            'region' => 'Московская область',
            'city' => 'Москва',
            'street' => 'Садовое кольцо, д. 5, офис 501',
            'postal_code' => '101000',
            'full_address' => 'Россия, Москва, Садовое кольцо, д. 5, офис 501',
            'phone' => '+7 (495) 567-89-01',
            'phone_2' => '+7 (495) 567-89-02',
            'email' => 'moscow5@example.com',
            'website' => 'https://example.com',
            'working_hours' => 'Пн-Пт: 9:00-18:00',
            'latitude' => 55.7520,
            'longitude' => 37.6173,
            'zoom' => 14,
            'yandex_address' => 'Россия, Москва, Садовое кольцо, 5',
            'description' => 'Офис на Садовом кольце'
        )
    );
    
    return $moscow_offices;
}

/**
 * Get demo offices distributed across cities of the Rostov region.
 *
 * @return array
 */
function cw_demo_get_offices_data() {
    $offices = array(
        array('city' => 'Ростов-на-Дону', 'title' => 'Офис в Ростове-на-Дону', 'street' => 'ул. Большая Садовая, д. 51', 'postal_code' => '344002', 'latitude' => 47.222078, 'longitude' => 39.720349, 'phone' => '+7 (863) 200-10-01'),
        array('city' => 'Таганрог', 'title' => 'Офис в Таганроге', 'street' => 'ул. Петровская, д. 67', 'postal_code' => '347900', 'latitude' => 47.209490, 'longitude' => 38.935154, 'phone' => '+7 (8634) 61-10-02'),
        array('city' => 'Шахты', 'title' => 'Офис в Шахтах', 'street' => 'просп. Победа Революции, д. 111', 'postal_code' => '346500', 'latitude' => 47.708485, 'longitude' => 40.215958, 'phone' => '+7 (8636) 22-10-03'),
        array('city' => 'Новочеркасск', 'title' => 'Офис в Новочеркасске', 'street' => 'Платовский просп., д. 59', 'postal_code' => '346400', 'latitude' => 47.422052, 'longitude' => 40.093725, 'phone' => '+7 (8635) 22-10-04'),
        array('city' => 'Батайск', 'title' => 'Офис в Батайске', 'street' => 'ул. Кирова, д. 51', 'postal_code' => '346880', 'latitude' => 47.138333, 'longitude' => 39.744469, 'phone' => '+7 (8635) 44-10-05'),
        array('city' => 'Азов', 'title' => 'Офис в Азове', 'street' => 'Петровский бул., д. 7', 'postal_code' => '346780', 'latitude' => 47.112442, 'longitude' => 39.423581, 'phone' => '+7 (8634) 26-10-06'),
        array('city' => 'Волгодонск', 'title' => 'Офис в Волгодонске', 'street' => 'ул. Ленина, д. 52', 'postal_code' => '347360', 'latitude' => 47.516518, 'longitude' => 42.198453, 'phone' => '+7 (8639) 22-10-07'),
        array('city' => 'Каменск-Шахтинский', 'title' => 'Офис в Каменске-Шахтинском', 'street' => 'просп. Карла Маркса, д. 42', 'postal_code' => '347800', 'latitude' => 48.320515, 'longitude' => 40.268923, 'phone' => '+7 (8636) 57-10-08'),
        array('city' => 'Сальск', 'title' => 'Офис в Сальске', 'street' => 'ул. Ленина, д. 21', 'postal_code' => '347630', 'latitude' => 46.474897, 'longitude' => 41.541266, 'phone' => '+7 (8637) 25-10-09'),
        array('city' => 'Аксай', 'title' => 'Офис в Аксае', 'street' => 'ул. Ленина, д. 18', 'postal_code' => '346720', 'latitude' => 47.269804, 'longitude' => 39.862615, 'phone' => '+7 (8635) 05-10-10'),
    );

    $landmarks = array(
        'Ростов-на-Дону' => 'Рядом с парком имени Максима Горького, вход со стороны Большой Садовой',
        'Таганрог' => 'В квартале от Центрального городского рынка',
        'Шахты' => 'Рядом с площадью Ленина и зданием городской администрации',
        'Новочеркасск' => 'Недалеко от площади Ермака и Вознесенского собора',
        'Батайск' => 'Напротив городского парка культуры и отдыха',
        'Азов' => 'Рядом с Петровской площадью и Азовским музеем-заповедником',
        'Волгодонск' => 'В центральной части города, рядом с площадью Победы',
        'Каменск-Шахтинский' => 'Рядом с центральным рынком, вход с проспекта Карла Маркса',
        'Сальск' => 'Недалеко от администрации города и центральной площади',
        'Аксай' => 'В центре города, рядом с районной администрацией',
    );

    foreach ($offices as $index => &$office) {
        $office['country'] = 'Россия';
        $office['region'] = 'Ростовская область';
        $office['full_address'] = 'Россия, Ростовская область, ' . $office['city'] . ', ' . $office['street'];
        $office['phone_2'] = '+7 (800) 555-10-' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
        $office['email'] = 'office' . ($index + 1) . '@donstrax.ru';
        $office['website'] = home_url('/');
        $office['working_hours'] = 'Пн–Пт: 09:00–18:00; Сб–Вс: выходной';
        $office['zoom'] = 15;
        $office['yandex_address'] = $office['full_address'];
        $office['landmark'] = $landmarks[$office['city']];
        $office['description'] = '<p>Офис компании «ДонСтрах» в городе ' . $office['city'] . '. Здесь можно оформить страховые продукты, получить консультацию специалиста, внести изменения в действующий договор и обратиться по страховому случаю.</p><p><strong>Как нас найти:</strong> ' . $office['landmark'] . '.</p>';
    }
    unset($office);

    return $offices;
}

/**
 * Создать одну запись офиса
 * 
 * @param array $office_data Данные офиса
 * @return int|false ID созданной записи или false при ошибке
 */
function cw_demo_create_office_post($office_data) {
    // Проверяем обязательные поля
    if (empty($office_data['title']) || empty($office_data['city'])) {
        error_log('Demo Offices: Отсутствуют обязательные поля');
        return false;
    }
    
    // Подготавливаем данные для wp_insert_post
    $post_data = array(
        'post_title'    => sanitize_text_field($office_data['title']),
        'post_name'     => sanitize_title($office_data['title']),
        'post_status'   => 'publish',
        'post_type'     => 'offices',
        'post_author'   => get_current_user_id(),
    );
    
    // Вставляем запись
    $post_id = wp_insert_post($post_data);
    
    if (is_wp_error($post_id)) {
        error_log('Demo Offices: ' . __('Error creating record', 'codeweber') . ' - ' . $post_id->get_error_message());
        return false;
    }
    
    // Добавляем мета-поле для идентификации demo записей
    update_post_meta($post_id, '_demo_created', true);
    
    // Сохраняем все метаполя
    $meta_fields = array(
        'office_country' => sanitize_text_field($office_data['country'] ?? ''),
        'office_region' => sanitize_text_field($office_data['region'] ?? ''),
        'office_street' => sanitize_text_field($office_data['street'] ?? ''),
        'office_postal_code' => sanitize_text_field($office_data['postal_code'] ?? ''),
        'office_full_address' => sanitize_textarea_field($office_data['full_address'] ?? ''),
        'office_landmark' => sanitize_text_field($office_data['landmark'] ?? ''),
        'office_phone' => sanitize_text_field($office_data['phone'] ?? ''),
        'office_phone_2' => sanitize_text_field($office_data['phone_2'] ?? ''),
        'office_email' => sanitize_email($office_data['email'] ?? ''),
        'office_website' => esc_url_raw($office_data['website'] ?? ''),
        'office_working_hours' => sanitize_textarea_field($office_data['working_hours'] ?? ''),
        'office_latitude' => floatval($office_data['latitude'] ?? 0),
        'office_longitude' => floatval($office_data['longitude'] ?? 0),
        'office_zoom' => intval($office_data['zoom'] ?? 10),
        'office_yandex_address' => sanitize_text_field($office_data['yandex_address'] ?? ''),
        'office_description' => wp_kses_post($office_data['description'] ?? ''),
        'office_coordinates' => ($office_data['latitude'] ?? 0) . ', ' . ($office_data['longitude'] ?? 0)
    );
    
    foreach ($meta_fields as $key => $value) {
        if (!empty($value)) {
            update_post_meta($post_id, '_' . $key, $value);
        }
    }

    // Primary storage for the phone repeater; legacy fields above remain in sync.
    $phones = array_values(array_filter(array(
        sanitize_text_field($office_data['phone'] ?? ''),
        sanitize_text_field($office_data['phone_2'] ?? ''),
    )));
    update_post_meta($post_id, '_office_phones', $phones);

    $workday = wp_json_encode(array(
        'closed' => 0, 'opens_1' => '09:00', 'closes_1' => '18:00',
        'opens_2' => '', 'closes_2' => '',
    ));
    foreach (array('monday', 'tuesday', 'wednesday', 'thursday', 'friday') as $day) {
        update_post_meta($post_id, '_office_hours_' . $day, $workday);
    }
    foreach (array('saturday', 'sunday') as $day) {
        update_post_meta($post_id, '_office_hours_' . $day, wp_json_encode(array('closed' => 1)));
    }
    
    // Сохраняем город в метаполе для совместимости
    update_post_meta($post_id, '_office_city', sanitize_text_field($office_data['city']));
    
    // Получаем или создаем термин города
    $town_name = $office_data['city'];
    $town_term = get_term_by('name', $town_name, 'towns');
    
    if (!$town_term) {
        // Создаем термин, если его нет
        $term_data = wp_insert_term(
            $town_name,
            'towns',
            array(
                'description' => '',
                'slug' => sanitize_title($town_name)
            )
        );
        
        if (!is_wp_error($term_data)) {
            $town_term_id = $term_data['term_id'];
        } else {
            error_log('Demo Offices: Ошибка создания термина города - ' . $term_data->get_error_message());
            $town_term_id = null;
        }
    } else {
        $town_term_id = $town_term->term_id;
    }
    
    // Устанавливаем термин таксономии для офиса
    if ($town_term_id) {
        wp_set_object_terms($post_id, array($town_term_id), 'towns');
    }
    
    return $post_id;
}

/**
 * Создать все demo записи офисов
 * 
 * @return array Результат выполнения
 */
function cw_demo_create_offices() {
    // Сначала создаем города
    $towns_result = cw_demo_create_towns();
    
    // Получаем данные офисов
    $offices_data = cw_demo_get_offices_data();
    
    if (empty($offices_data)) {
        return array(
            'success' => false,
            'message' => 'Данные офисов не найдены',
            'created' => 0,
            'errors' => []
        );
    }
    
    $created = 0;
    $errors = [];
    
    foreach ($offices_data as $office_data) {
        $post_id = cw_demo_create_office_post($office_data);
        
        if ($post_id) {
            $created++;
        } else {
            $errors[] = __('Failed to create:', 'codeweber') . ' ' . (!empty($office_data['title']) ? $office_data['title'] : __('unknown', 'codeweber'));
        }
    }
    
    $message = sprintf(__('%1$d of %2$d offices created', 'codeweber'), $created, count($offices_data));
    if ($towns_result['created'] > 0) {
        $message .= '. ' . sprintf(__('%d cities created', 'codeweber'), $towns_result['created']);
    }
    
    // Объединяем ошибки
    $all_errors = array_merge($towns_result['errors'] ?? [], $errors);
    
    return array(
        'success' => true,
        'message' => $message,
        'created' => $created,
        'total' => count($offices_data),
        'towns_created' => $towns_result['created'] ?? 0,
        'errors' => $all_errors
    );
}

/**
 * Удалить все demo записи офисов
 * 
 * @return array Результат выполнения
 */
function cw_demo_delete_offices() {
    $args = array(
        'post_type' => 'offices',
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
        'message' => sprintf(__('%d offices deleted', 'codeweber'), $deleted),
        'deleted' => $deleted,
        'errors' => $errors
    );
}

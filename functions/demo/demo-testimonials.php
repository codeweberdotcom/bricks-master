<?php
/**
 * Demo данные для CPT Testimonials
 * 
 * Функции для создания demo записей типа testimonials
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Получить данные для demo отзывов
 * 
 * @return array Массив данных отзывов
 */
function cw_demo_get_testimonials_data() {
    // Определяем язык сайта
    $locale = get_locale();
    $is_russian = (strpos($locale, 'ru') === 0);
    
    if ($is_russian) {
        return cw_demo_get_testimonials_data_ru();
    } else {
        return cw_demo_get_testimonials_data_en();
    }
}

/**
 * Получить данные для demo отзывов (английский)
 * 
 * @return array Массив данных отзывов
 */
function cw_demo_get_testimonials_data_en() {
    return array(
        array(
            'title' => 'Excellent Service',
            'text' => 'Cum sociis natoque penatibus et magnis dis parturient montes. The team was professional and delivered exactly what we needed.',
            'author_name' => 'Coriss Ambady',
            'author_role' => 'Financial Analyst',
            'company' => 'Tech Corp',
            'rating' => '3', // 1 отзыв с рейтингом 3
            'status' => 'approved',
            'avatar' => 'te10.jpg'
        ),
        array(
            'title' => 'Outstanding Results',
            'text' => 'Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Vestibulum id ligula porta felis euismod semper.',
            'author_name' => 'Cory Zamora',
            'author_role' => 'Marketing Specialist',
            'company' => 'Digital Solutions',
            'rating' => '4', // 1 из 5 с рейтингом 4
            'status' => 'approved',
            'avatar' => 'te2.jpg'
        ),
        array(
            'title' => 'Highly Recommended',
            'text' => 'Donec id elit non porta gravida at eget metus. Duis mollis est commodo luctus, nisi erat porttitor.',
            'author_name' => 'Barclay Widerski',
            'author_role' => 'Sales Specialist',
            'company' => 'Business Inc',
            'rating' => '4', // 2 из 5 с рейтингом 4
            'status' => 'approved',
            'avatar' => 'te3.jpg'
        ),
        array(
            'title' => 'Great Experience',
            'text' => 'Nisi erat porttitor ligula, eget lacinia odio sem nec elit. Aenean eu leo pellentesque ornare.',
            'author_name' => 'Jackie Sanders',
            'author_role' => 'Investment Planner',
            'company' => 'Finance Group',
            'rating' => '4', // 3 из 5 с рейтингом 4
            'status' => 'approved',
            'avatar' => 'te4.jpg'
        ),
        array(
            'title' => 'Professional Team',
            'text' => 'Fusce dapibus, tellus ac cursus tortor mauris condimentum fermentum massa justo sit amet. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor.',
            'author_name' => 'Nikolas Brooten',
            'author_role' => 'Sales Manager',
            'company' => 'Sales Pro',
            'rating' => '4', // 4 из 5 с рейтингом 4
            'status' => 'approved',
            'avatar' => 'te5.jpg'
        ),
        array(
            'title' => 'Exceeded Expectations',
            'text' => 'Curabitur blandit tempus porttitor. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Nullam quis risus eget porta ac consectetur vestibulum.',
            'author_name' => 'Laura Widerski',
            'author_role' => 'Sales Specialist',
            'company' => 'Retail Solutions',
            'rating' => '4', // 5 из 5 с рейтингом 4
            'status' => 'approved',
            'avatar' => 'te6.jpg'
        ),
        array(
            'title' => 'Top Quality Work',
            'text' => 'Etiam adipiscing tincidunt elit convallis felis suscipit ut. Phasellus rhoncus tincidunt auctor. Nullam eu sagittis mauris.',
            'author_name' => 'Coriss Ambady',
            'author_role' => 'Financial Analyst',
            'company' => 'Analytics Co',
            'rating' => '5', // Остальные 9 с рейтингом 5
            'status' => 'approved',
            'avatar' => 'te7.jpg'
        ),
        array(
            'title' => 'Very Satisfied',
            'text' => 'Maecenas sed diam eget risus varius blandit sit amet non magna. Cum sociis natoque penatibus magnis dis montes, nascetur ridiculus mus.',
            'author_name' => 'Jackie Sanders',
            'author_role' => 'Investment Planner',
            'company' => 'Wealth Management',
            'rating' => '5',
            'status' => 'approved',
            'avatar' => 'te8.jpg'
        ),
        array(
            'title' => 'Impressive Service',
            'text' => 'Donec id elit non mi porta gravida at eget metus. Nulla vitae elit libero, a pharetra augue. Cum sociis natoque penatibus et magnis dis parturient montes.',
            'author_name' => 'Cory Zamora',
            'author_role' => 'Marketing Specialist',
            'company' => 'Brand Agency',
            'rating' => '5',
            'status' => 'approved',
            'avatar' => 'te9.jpg'
        ),
        array(
            'title' => 'Excellent Support',
            'text' => 'Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Vestibulum id ligula porta felis euismod semper. Cras justo odio dapibus facilisis sociis.',
            'author_name' => 'Barclay Widerski',
            'author_role' => 'Sales Specialist',
            'company' => 'Commerce Ltd',
            'rating' => '5',
            'status' => 'approved',
            'avatar' => 'te10.jpg'
        ),
        array(
            'title' => 'Great Partnership',
            'text' => 'Fusce dapibus, tellus ac cursus tortor mauris condimentum fermentum massa justo sit amet. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor.',
            'author_name' => 'Nikolas Brooten',
            'author_role' => 'Sales Manager',
            'company' => 'Enterprise Solutions',
            'rating' => '5',
            'status' => 'approved',
            'avatar' => 'te11.jpg'
        ),
        array(
            'title' => 'Outstanding Performance',
            'text' => 'Curabitur blandit tempus porttitor. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Cras mattis consectetur purus sit amet fermentum.',
            'author_name' => 'Laura Widerski',
            'author_role' => 'Sales Specialist',
            'company' => 'Growth Partners',
            'rating' => '5',
            'status' => 'approved',
            'avatar' => 'te12.jpg'
        ),
        array(
            'title' => 'Reliable Service',
            'text' => 'Donec id elit non porta gravida at eget metus. Duis mollis est commodo luctus, nisi erat porttitor. Cras mattis consectetur purus sit amet fermentum.',
            'author_name' => 'Coriss Ambady',
            'author_role' => 'Financial Analyst',
            'company' => 'Financial Services',
            'rating' => '5',
            'status' => 'approved',
            'avatar' => 't2.jpg'
        ),
        array(
            'title' => 'Professional Approach',
            'text' => 'Nisi erat porttitor ligula, eget lacinia odio sem nec elit. Aenean eu leo pellentesque ornare. Maecenas sed diam eget risus varius blandit sit amet non magna.',
            'author_name' => 'Jackie Sanders',
            'author_role' => 'Investment Planner',
            'company' => 'Investment Group',
            'rating' => '5',
            'status' => 'approved',
            'avatar' => 't2.jpg'
        ),
        array(
            'title' => 'Amazing Results',
            'text' => 'Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Vestibulum id ligula porta felis euismod semper. Donec sed odio dui.',
            'author_name' => 'Cory Zamora',
            'author_role' => 'Marketing Specialist',
            'company' => 'Marketing Pro',
            'rating' => '5',
            'status' => 'approved',
            'avatar' => 't3.jpg'
        ),
    );
}

/**
 * Получить данные для demo отзывов (русский)
 * 
 * @return array Массив данных отзывов
 */
function cw_demo_get_testimonials_data_ru() {
    return array(
        // Женские имена (8)
        array(
            'title' => 'Отличный сервис',
            'text' => 'Работа с командой была профессиональной, они выполнили именно то, что нам было нужно. Очень довольны результатом.',
            'author_name' => 'Анна Петрова',
            'author_role' => 'Финансовый аналитик',
            'company' => 'ТехноКорп',
            'rating' => '3', // 1 отзыв с рейтингом 3
            'status' => 'approved',
            'avatar' => 'te10.jpg' // Женский
        ),
        array(
            'title' => 'Высоко рекомендую',
            'text' => 'Отличная работа, все выполнено в срок и с высоким качеством. Обязательно обратимся снова.',
            'author_name' => 'Елена Иванова',
            'author_role' => 'Специалист по продажам',
            'company' => 'Бизнес Инк',
            'rating' => '4', // 2 из 5 с рейтингом 4
            'status' => 'approved',
            'avatar' => 'te2.jpg' // Женский
        ),
        array(
            'title' => 'Профессиональная команда',
            'text' => 'Очень довольна результатом. Команда показала высокий уровень профессионализма и ответственности.',
            'author_name' => 'Мария Волкова',
            'author_role' => 'Менеджер по продажам',
            'company' => 'Продажи Про',
            'rating' => '4', // 4 из 5 с рейтингом 4
            'status' => 'approved',
            'avatar' => 'te2.jpg' // Женский
        ),
        array(
            'title' => 'Качественная работа',
            'text' => 'Очень довольны результатом. Все выполнено в срок, качественно и с учетом всех наших требований.',
            'author_name' => 'Ольга Соколова',
            'author_role' => 'Финансовый аналитик',
            'company' => 'Аналитика Ко',
            'rating' => '5', // Остальные 9 с рейтингом 5
            'status' => 'approved',
            'avatar' => 'te4.jpg' // Женский
        ),
        array(
            'title' => 'Впечатляющий сервис',
            'text' => 'Команда проявила профессионализм и внимательность. Все выполнено точно в срок и с высоким качеством.',
            'author_name' => 'Татьяна Морозова',
            'author_role' => 'Маркетинговый специалист',
            'company' => 'Бренд агентство',
            'rating' => '5',
            'status' => 'approved',
            'avatar' => 'te5.jpg' // Женский
        ),
        array(
            'title' => 'Отличное партнерство',
            'text' => 'Работаем с командой уже не первый раз. Всегда качественно, быстро и профессионально. Рекомендуем!',
            'author_name' => 'Наталья Орлова',
            'author_role' => 'Менеджер по продажам',
            'company' => 'Корпоративные решения',
            'rating' => '5',
            'status' => 'approved',
            'avatar' => 'te6.jpg' // Женский
        ),
        array(
            'title' => 'Надежный сервис',
            'text' => 'Очень довольны сотрудничеством. Команда работает профессионально, всегда на связи и выполняет работу в срок.',
            'author_name' => 'Екатерина Павлова',
            'author_role' => 'Финансовый аналитик',
            'company' => 'Финансовые услуги',
            'rating' => '5',
            'status' => 'approved',
            'avatar' => 'te11.jpg' // Женский
        ),
        array(
            'title' => 'Потрясающие результаты',
            'text' => 'Результат превзошел все ожидания. Команда работает быстро, качественно и всегда готова помочь.',
            'author_name' => 'Юлия Григорьева',
            'author_role' => 'Маркетинговый специалист',
            'company' => 'Маркетинг Про',
            'rating' => '5',
            'status' => 'approved',
            'avatar' => 't2.jpg' // Женский
        ),
        
        // Мужские имена (7)
        array(
            'title' => 'Превосходные результаты',
            'text' => 'Команда превзошла все наши ожидания. Профессиональный подход и качественное выполнение работы.',
            'author_name' => 'Дмитрий Смирнов',
            'author_role' => 'Маркетинговый специалист',
            'company' => 'Цифровые решения',
            'rating' => '4', // 1 из 5 с рейтингом 4
            'status' => 'approved',
            'avatar' => 'u1.jpg' // Мужской
        ),
        array(
            'title' => 'Отличный опыт',
            'text' => 'Работа была выполнена на высшем уровне. Команда проявила профессионализм и внимательность к деталям.',
            'author_name' => 'Сергей Козлов',
            'author_role' => 'Инвестор',
            'company' => 'Финансовая группа',
            'rating' => '4', // 3 из 5 с рейтингом 4
            'status' => 'approved',
            'avatar' => 'u1.jpg' // Мужской
        ),
        array(
            'title' => 'Превысили ожидания',
            'text' => 'Результат превзошел все ожидания. Команда работает быстро, качественно и всегда на связи.',
            'author_name' => 'Александр Новиков',
            'author_role' => 'Специалист по продажам',
            'company' => 'Розничные решения',
            'rating' => '4', // 5 из 5 с рейтингом 4
            'status' => 'approved',
            'avatar' => 'u1.jpg' // Мужской
        ),
        array(
            'title' => 'Очень довольны',
            'text' => 'Отличная работа команды. Профессиональный подход, внимательность к деталям и высокое качество.',
            'author_name' => 'Игорь Лебедев',
            'author_role' => 'Инвестор',
            'company' => 'Управление капиталом',
            'rating' => '5',
            'status' => 'approved',
            'avatar' => 'te9.jpg' // Мужской
        ),
        array(
            'title' => 'Отличная поддержка',
            'text' => 'Очень довольны работой. Команда всегда на связи, быстро решает вопросы и предоставляет качественный сервис.',
            'author_name' => 'Владимир Федоров',
            'author_role' => 'Специалист по продажам',
            'company' => 'Коммерция Лтд',
            'rating' => '5',
            'status' => 'approved',
            'avatar' => 'te9.jpg' // Мужской
        ),
        array(
            'title' => 'Профессиональный подход',
            'text' => 'Отличная работа команды. Профессионализм, внимательность к деталям и высокое качество выполнения.',
            'author_name' => 'Андрей Семенов',
            'author_role' => 'Инвестор',
            'company' => 'Инвестиционная группа',
            'rating' => '5',
            'status' => 'approved',
            'avatar' => 'u1.jpg' // Мужской
        ),
        array(
            'title' => 'Выдающаяся производительность',
            'text' => 'Команда показала отличные результаты. Профессиональный подход и высокое качество выполнения работы.',
            'author_name' => 'Роман Зайцев',
            'author_role' => 'Специалист по продажам',
            'company' => 'Партнеры роста',
            'rating' => '5',
            'status' => 'approved',
            'avatar' => 'u1.jpg' // Мужской
        ),
    );
}

/**
 * Импортировать аватар в медиабиблиотеку
 * 
 * @param string $image_filename Имя файла изображения
 * @param int $post_id ID записи
 * @return int|false ID attachment или false при ошибке
 */
function cw_demo_import_testimonial_avatar($image_filename, $post_id) {
    $source_path = get_template_directory() . '/src/assets/img/avatars/' . $image_filename;
    
    if (!file_exists($source_path)) {
        error_log('Demo Testimonials: Файл изображения не найден - ' . $image_filename);
        return false;
    }
    
    // Получаем информацию о файле
    $file_type = wp_check_filetype(basename($source_path), null);
    
    if (!$file_type['type']) {
        error_log('Demo Testimonials: Неизвестный тип файла - ' . $image_filename);
        return false;
    }
    
    // Подготавливаем данные для загрузки
    $upload_dir = wp_upload_dir();
    $file_name = basename($source_path);
    $file_path = $upload_dir['path'] . '/' . $file_name;
    
    // Копируем файл во временную папку uploads
    if (!copy($source_path, $file_path)) {
        error_log('Demo Testimonials: Не удалось скопировать файл - ' . $image_filename);
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
        error_log('Demo Testimonials: Ошибка загрузки изображения - ' . $attachment_id->get_error_message());
        return false;
    }
    
    // Устанавливаем родителя для правильной работы системы размеров
    wp_update_post(array(
        'ID' => $attachment_id,
        'post_parent' => $post_id
    ));
    
    return $attachment_id;
}

/**
 * Создать demo записи testimonials
 * 
 * @return array Результат операции
 */
function cw_demo_create_testimonials() {
    $testimonials_data = cw_demo_get_testimonials_data();
    $created = 0;
    $errors = array();
    
    foreach ($testimonials_data as $index => $testimonial_data) {
        // Создаем запись
        $post_data = array(
            'post_title'   => $testimonial_data['title'],
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'testimonials',
            'post_author'  => get_current_user_id(),
        );
        
        $post_id = wp_insert_post($post_data, true);
        
        if (is_wp_error($post_id)) {
            $errors[] = sprintf(
                __('Ошибка создания записи "%s": %s', 'codeweber'),
                $testimonial_data['title'],
                $post_id->get_error_message()
            );
            continue;
        }
        
        // Сохраняем мета-поля
        update_post_meta($post_id, '_testimonial_text', $testimonial_data['text']);
        update_post_meta($post_id, '_testimonial_author_type', 'custom');
        update_post_meta($post_id, '_testimonial_author_name', $testimonial_data['author_name']);
        update_post_meta($post_id, '_testimonial_author_role', $testimonial_data['author_role']);
        update_post_meta($post_id, '_testimonial_company', $testimonial_data['company']);
        update_post_meta($post_id, '_testimonial_rating', $testimonial_data['rating']);
        update_post_meta($post_id, '_testimonial_status', $testimonial_data['status']);
        
        // Импортируем аватар
        if (!empty($testimonial_data['avatar'])) {
            $avatar_id = cw_demo_import_testimonial_avatar($testimonial_data['avatar'], $post_id);
            if ($avatar_id) {
                update_post_meta($post_id, '_testimonial_avatar', $avatar_id);
            } else {
                $errors[] = sprintf(
                    __('Не удалось загрузить аватар для "%s"', 'codeweber'),
                    $testimonial_data['title']
                );
            }
        }
        
        $created++;
    }
    
    $message = sprintf(
        _n(
            'Создана %d demo запись testimonials',
            'Создано %d demo записей testimonials',
            $created,
            'codeweber'
        ),
        $created
    );
    
    return array(
        'success' => true,
        'message' => $message,
        'created' => $created,
        'total' => count($testimonials_data),
        'errors' => $errors
    );
}

/**
 * Удалить все demo записи testimonials
 * 
 * @return array Результат операции
 */
function cw_demo_delete_testimonials() {
    $deleted = 0;
    $errors = array();
    
    // Получаем все записи testimonials
    $testimonials = get_posts(array(
        'post_type' => 'testimonials',
        'posts_per_page' => -1,
        'post_status' => 'any'
    ));
    
    foreach ($testimonials as $testimonial) {
        // Удаляем аватар, если он есть
        $avatar_id = get_post_meta($testimonial->ID, '_testimonial_avatar', true);
        if ($avatar_id) {
            wp_delete_attachment($avatar_id, true);
        }
        
        // Удаляем запись
        $result = wp_delete_post($testimonial->ID, true);
        
        if ($result) {
            $deleted++;
        } else {
            $errors[] = sprintf(
                __('Не удалось удалить запись ID: %d', 'codeweber'),
                $testimonial->ID
            );
        }
    }
    
    $message = sprintf(
        _n(
            'Удалена %d запись testimonials',
            'Удалено %d записей testimonials',
            $deleted,
            'codeweber'
        ),
        $deleted
    );
    
    return array(
        'success' => true,
        'message' => $message,
        'deleted' => $deleted,
        'errors' => $errors
    );
}


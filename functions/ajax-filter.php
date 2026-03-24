<?php
/**
 * Universal AJAX Filter Handler
 * 
 * Универсальный обработчик AJAX фильтрации для:
 * - Вакансий (vacancies)
 * - Статей (posts)
 * - WooCommerce товаров (products)
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Универсальный AJAX обработчик фильтрации
 */
function codeweber_ajax_filter() {
    // Проверка nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'codeweber_filter_nonce')) {
        wp_send_json_error(array(
            'message' => __('Security check failed', 'codeweber')
        ));
    }
    
    // Получаем параметры
    $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : 'post';
    
    // Обрабатываем filters - может быть JSON строка или массив
    $filters = array();
    if (isset($_POST['filters'])) {
        if (is_string($_POST['filters'])) {
            $filters = json_decode(stripslashes($_POST['filters']), true);
            if (!is_array($filters)) {
                $filters = array();
            }
        } elseif (is_array($_POST['filters'])) {
            $filters = $_POST['filters'];
        }
    }
    
    $template = isset($_POST['template']) ? sanitize_text_field($_POST['template']) : '';
    $container_selector = isset($_POST['container_selector']) ? sanitize_text_field($_POST['container_selector']) : '';
    
    // Валидация post_type
    $allowed_post_types = array('post', 'vacancies', 'products', 'staff', 'events');
    if (!in_array($post_type, $allowed_post_types)) {
        wp_send_json_error(array(
            'message' => __('Invalid post type', 'codeweber')
        ));
    }
    
    // Подготавливаем аргументы для WP_Query
    $args = array(
        'post_type' => $post_type,
        'post_status' => 'publish',
        'posts_per_page' => -1,
    );
    
    // Применяем фильтры в зависимости от типа контента
    if ($post_type === 'vacancies') {
        $args = codeweber_apply_vacancy_filters($args, $filters);
    } elseif ($post_type === 'post') {
        $args = codeweber_apply_post_filters($args, $filters);
    } elseif ($post_type === 'products' && class_exists('WooCommerce')) {
        $args = codeweber_apply_product_filters($args, $filters);
    } elseif ($post_type === 'staff') {
        $args = codeweber_apply_staff_filters($args, $filters);
    } elseif ($post_type === 'events') {
        $args = codeweber_apply_events_filters($args, $filters);
    }
    
    // Выполняем запрос
    $query = new WP_Query($args);
    
    // Генерируем HTML
    ob_start();
    
    if ($query->have_posts()) {
        // Загружаем соответствующий шаблон
        if ($post_type === 'vacancies' && $template === 'vacancies_1') {
            codeweber_render_vacancies_filtered($query, $filters);
        } elseif ($post_type === 'vacancies' && in_array($template, array('vacancies_2', 'vacancies_3', 'vacancies_4', 'vacancies_5', 'vacancies_6'), true)) {
            $backup_query = $GLOBALS['wp_query'];
            $GLOBALS['wp_query'] = $query;
            get_template_part('templates/archives/vacancies/' . $template);
            $GLOBALS['wp_query'] = $backup_query;
        } elseif ($post_type === 'post' && $template) {
            codeweber_render_posts_filtered($query, $filters, $template);
        } elseif ($post_type === 'products' && $template) {
            codeweber_render_products_filtered($query, $filters, $template);
        } elseif ($post_type === 'events') {
            codeweber_render_events_filtered($query, $filters);
        } else {
            // Дефолтный вывод
            while ($query->have_posts()) {
                $query->the_post();
                get_template_part('template-parts/content', get_post_type());
            }
        }
    } else {
        echo '<div class="py-14"><p>' . __('No items found.', 'codeweber') . '</p></div>';
    }
    
    wp_reset_postdata();
    
    $html = ob_get_clean();
    
    wp_send_json_success(array(
        'html' => $html,
        'found_posts' => $query->found_posts
    ));
}
add_action('wp_ajax_codeweber_filter', 'codeweber_ajax_filter');
add_action('wp_ajax_nopriv_codeweber_filter', 'codeweber_ajax_filter');

/**
 * Применяет фильтры для вакансий
 */
function codeweber_apply_vacancy_filters($args, $filters) {
    $meta_query = array();
    $tax_query = array();
    
    // Фильтр по типу вакансии (taxonomy)
    $vacancy_type_id = !empty($filters['vacancy_type']) ? $filters['vacancy_type'] : (!empty($filters['position']) ? $filters['position'] : null);
    if (!empty($vacancy_type_id)) {
        $tax_query[] = array(
            'taxonomy' => 'vacancy_type',
            'field' => 'term_id',
            'terms' => intval($vacancy_type_id),
        );
    }
    
    // Фильтр по локации (meta)
    if (!empty($filters['location'])) {
        $meta_query[] = array(
            'key' => '_vacancy_location',
            'value' => sanitize_text_field($filters['location']),
            'compare' => '='
        );
    }
    
    if (!empty($meta_query)) {
        if (count($meta_query) > 1) {
            $meta_query['relation'] = 'AND';
        }
        $args['meta_query'] = $meta_query;
    }
    
    if (!empty($tax_query)) {
        if (count($tax_query) > 1) {
            $tax_query['relation'] = 'AND';
        }
        $args['tax_query'] = $tax_query;
    }
    
    return $args;
}

/**
 * Применяет фильтры для статей
 */
function codeweber_apply_post_filters($args, $filters) {
    $tax_query = array();
    
    // Фильтр по категории
    if (!empty($filters['category'])) {
        $tax_query[] = array(
            'taxonomy' => 'category',
            'field' => 'term_id',
            'terms' => intval($filters['category']),
        );
    }
    
    // Фильтр по тегу
    if (!empty($filters['tag'])) {
        $tax_query[] = array(
            'taxonomy' => 'post_tag',
            'field' => 'term_id',
            'terms' => intval($filters['tag']),
        );
    }
    
    if (!empty($tax_query)) {
        if (count($tax_query) > 1) {
            $tax_query['relation'] = 'AND';
        }
        $args['tax_query'] = $tax_query;
    }
    
    return $args;
}

/**
 * Применяет фильтры для товаров WooCommerce
 */
function codeweber_apply_product_filters($args, $filters) {
    $tax_query = array();
    $meta_query = array();
    
    // Фильтр по категории товара
    if (!empty($filters['category'])) {
        $tax_query[] = array(
            'taxonomy' => 'product_cat',
            'field' => 'term_id',
            'terms' => intval($filters['category']),
        );
    }
    
    // Фильтр по тегу товара
    if (!empty($filters['tag'])) {
        $tax_query[] = array(
            'taxonomy' => 'product_tag',
            'field' => 'term_id',
            'terms' => intval($filters['tag']),
        );
    }
    
    // Фильтр по цене
    if (!empty($filters['price_min']) || !empty($filters['price_max'])) {
        $meta_query[] = array(
            'key' => '_price',
            'value' => array(
                floatval($filters['price_min'] ?: 0),
                floatval($filters['price_max'] ?: 999999)
            ),
            'compare' => 'BETWEEN',
            'type' => 'NUMERIC'
        );
    }
    
    if (!empty($meta_query)) {
        if (count($meta_query) > 1) {
            $meta_query['relation'] = 'AND';
        }
        $args['meta_query'] = $meta_query;
    }
    
    if (!empty($tax_query)) {
        if (count($tax_query) > 1) {
            $tax_query['relation'] = 'AND';
        }
        $args['tax_query'] = $tax_query;
    }
    
    return $args;
}

/**
 * Применяет фильтры для staff
 */
function codeweber_apply_staff_filters($args, $filters) {
    $tax_query = array();
    
    // Фильтр по департаменту
    if (!empty($filters['department'])) {
        $tax_query[] = array(
            'taxonomy' => 'staff_department',
            'field' => 'term_id',
            'terms' => intval($filters['department']),
        );
    }
    
    if (!empty($tax_query)) {
        $args['tax_query'] = $tax_query;
    }
    
    return $args;
}

/**
 * Рендерит отфильтрованные вакансии для шаблона vacancies_1
 */
function codeweber_render_vacancies_filtered($query, $filters) {
    // Группируем вакансии по типам
    $vacancies_by_type = array();
    
    while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();
        $vacancy_data = get_vacancy_data_array($post_id);
        
        $types = get_the_terms($post_id, 'vacancy_type');
        if ($types && !is_wp_error($types)) {
            foreach ($types as $type) {
                if (!isset($vacancies_by_type[$type->term_id])) {
                    $vacancies_by_type[$type->term_id] = array(
                        'term' => $type,
                        'vacancies' => array()
                    );
                }
                $vacancies_by_type[$type->term_id]['vacancies'][] = array(
                    'post_id' => $post_id,
                    'data' => $vacancy_data
                );
            }
        } else {
            if (!isset($vacancies_by_type['no-type'])) {
                $vacancies_by_type['no-type'] = array(
                    'term' => null,
                    'vacancies' => array()
                );
            }
            $vacancies_by_type['no-type']['vacancies'][] = array(
                'post_id' => $post_id,
                'data' => $vacancy_data
            );
        }
    }
    
    // Массив цветов для аватаров
    $avatar_colors = array('bg-red', 'bg-green', 'bg-yellow', 'bg-purple', 'bg-orange', 'bg-pink', 'bg-blue');
    $color_index = 0;
    $archive_card_radius = class_exists('Codeweber_Options') ? Codeweber_Options::style('card-radius') : '';
    
    if (!empty($vacancies_by_type)) {
        foreach ($vacancies_by_type as $type_id => $type_data) {
            $term = $type_data['term'];
            $vacancies_list = $type_data['vacancies'];
            ?>
            <div class="job-list mb-10" data-type-id="<?php echo esc_attr($type_id); ?>">
                <?php if ($term) : ?>
                    <h3 class="mb-4"><?php echo esc_html($term->name); ?></h3>
                <?php else : ?>
                    <h3 class="mb-4"><?php _e('Other Vacancies', 'codeweber'); ?></h3>
                <?php endif; ?>
                
                <?php foreach ($vacancies_list as $vacancy) :
                    $avatar_color = $avatar_colors[$color_index % count($avatar_colors)];
                    $color_index++;
                    set_query_var('vacancy_list_item_post_id', $vacancy['post_id']);
                    set_query_var('vacancy_list_item_data', $vacancy['data']);
                    set_query_var('vacancy_list_item_avatar_color', $avatar_color);
                    set_query_var('vacancy_list_item_card_radius', $archive_card_radius);
                    get_template_part('templates/post-cards/vacancies/list-item');
                endforeach; ?>
            </div>
        <?php
        }
    } else {
        echo '<div class="py-14"><p>' . __('No vacancies found.', 'codeweber') . '</p></div>';
    }
}

/**
 * Рендерит отфильтрованные статьи
 */
function codeweber_render_posts_filtered($query, $filters, $template) {
    // Здесь можно добавить логику для разных шаблонов статей
    while ($query->have_posts()) {
        $query->the_post();
        get_template_part('template-parts/content', get_post_format());
    }
}

/**
 * Рендерит отфильтрованные товары WooCommerce
 */
function codeweber_render_products_filtered($query, $filters, $template) {
    // Здесь можно добавить логику для разных шаблонов товаров
    while ($query->have_posts()) {
        $query->the_post();
        wc_get_template_part('content', 'product');
    }
}

/**
 * Применяет фильтры для событий
 */
function codeweber_apply_events_filters($args, $filters) {
    $tax_query = array();

    if (!empty($filters['event_category'])) {
        $tax_query[] = array(
            'taxonomy' => 'event_category',
            'field'    => 'term_id',
            'terms'    => intval($filters['event_category']),
        );
    }

    if (!empty($tax_query)) {
        $args['tax_query'] = $tax_query;
    }

    // Сортировка по дате начала события
    $args['meta_key'] = '_event_date_start';
    $args['orderby']  = 'meta_value';
    $args['order']    = 'ASC';

    return $args;
}

/**
 * Рендерит отфильтрованные события (таблица для events_1)
 */
function codeweber_render_events_filtered($query, $filters) {
    if (!function_exists('codeweber_events_get_registration_status')) {
        echo '<p>' . esc_html__('No events found.', 'codeweber') . '</p>';
        return;
    }

    echo '<div class="table-responsive">';
    echo '<table class="table table-hover align-middle events-table">';
    echo '<thead class="table-light"><tr>';
    echo '<th>' . esc_html__('Date', 'codeweber') . '</th>';
    echo '<th>' . esc_html__('Event', 'codeweber') . '</th>';
    echo '<th class="d-none d-md-table-cell">' . esc_html__('Location', 'codeweber') . '</th>';
    echo '<th class="d-none d-lg-table-cell">' . esc_html__('Format', 'codeweber') . '</th>';
    echo '<th class="d-none d-md-table-cell">' . esc_html__('Price', 'codeweber') . '</th>';
    echo '<th></th>';
    echo '</tr></thead>';
    echo '<tbody>';

    while ($query->have_posts()) {
        $query->the_post();
        $post_id    = get_the_ID();
        $date_start = get_post_meta($post_id, '_event_date_start', true);
        $date_end   = get_post_meta($post_id, '_event_date_end', true);
        $location   = get_post_meta($post_id, '_event_location', true);
        $price      = get_post_meta($post_id, '_event_price', true);
        $reg_status = codeweber_events_get_registration_status($post_id);
        $formats    = get_the_terms($post_id, 'event_format');

        $status_map = array(
            'open'                => 'badge bg-soft-green text-green rounded-pill',
            'not_open_yet'        => 'badge bg-soft-yellow text-yellow rounded-pill',
            'registration_closed' => 'badge bg-soft-ash text-muted rounded-pill',
            'no_seats'            => 'badge bg-soft-red text-red rounded-pill',
            'event_ended'         => 'badge bg-soft-ash text-muted rounded-pill',
        );
        $status_class = isset($status_map[$reg_status['status']]) ? $status_map[$reg_status['status']] : '';

        echo '<tr>';

        // Date
        echo '<td class="event-date-cell">';
        if ($date_start) {
            echo '<span class="fw-semibold">' . esc_html(date_i18n(get_option('date_format'), strtotime($date_start))) . '</span>';
            if ($date_end && $date_end !== $date_start) {
                echo '<br><small class="text-muted">' . esc_html(date_i18n(get_option('date_format'), strtotime($date_end))) . '</small>';
            }
        } else {
            echo '<span class="text-muted">—</span>';
        }
        echo '</td>';

        // Title + status badge
        echo '<td>';
        echo '<a href="' . esc_url(get_permalink()) . '" class="fw-semibold text-reset text-decoration-none">' . esc_html(get_the_title()) . '</a>';
        if ($status_class && !empty($reg_status['label'])) {
            echo '<br><span class="event-status-badge ' . esc_attr($status_class) . ' mt-1">' . esc_html($reg_status['label']) . '</span>';
        }
        echo '</td>';

        // Location
        echo '<td class="d-none d-md-table-cell">';
        echo $location ? esc_html($location) : '<span class="text-muted">—</span>';
        echo '</td>';

        // Format
        echo '<td class="d-none d-lg-table-cell">';
        if ($formats && !is_wp_error($formats)) {
            foreach ($formats as $fmt) {
                echo '<span class="badge bg-soft-ash text-navy event-format-badge">' . esc_html($fmt->name) . '</span> ';
            }
        } else {
            echo '<span class="text-muted">—</span>';
        }
        echo '</td>';

        // Price
        echo '<td class="d-none d-md-table-cell event-card-price">';
        echo $price ? esc_html($price) : '<span class="text-muted">—</span>';
        echo '</td>';

        // Details button
        echo '<td class="text-end">';
        echo '<a href="' . esc_url(get_permalink()) . '" class="btn btn-sm btn-primary rounded-pill">' . esc_html__('Details', 'codeweber') . '</a>';
        echo '</td>';

        echo '</tr>';
    }

    echo '</tbody></table></div>';
}


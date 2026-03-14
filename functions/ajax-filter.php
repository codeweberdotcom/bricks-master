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
    $allowed_post_types = array('post', 'vacancies', 'products', 'staff');
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


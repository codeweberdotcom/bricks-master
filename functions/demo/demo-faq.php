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
    // Определяем язык сайта
    $locale = get_locale();
    $is_russian = (strpos($locale, 'ru') === 0);
    
    if ($is_russian) {
        return cw_demo_get_faq_data_ru();
    } else {
        return cw_demo_get_faq_data_en();
    }
}

/**
 * Получить данные FAQ (русский)
 * 
 * @return array|false Массив данных или false при ошибке
 */
function cw_demo_get_faq_data_ru() {
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
 * Получить данные FAQ (английский)
 * 
 * @return array|false Массив данных или false при ошибке
 */
function cw_demo_get_faq_data_en() {
    return array(
        'version' => '1.0.0',
        'post_type' => 'faq',
        'description' => 'Demo data for FAQ CPT',
        'items' => array(
            array(
                'title' => 'What does your company do?',
                'slug' => 'what-does-your-company-do',
                'status' => 'publish',
                'content' => 'We provide services and sell products in accordance with applicable laws. The current list of services and items is presented on the website.',
                'category' => 'General Information',
                'tags' => array('Popular', 'Important'),
                'order' => 1
            ),
            array(
                'title' => 'In which regions do you operate?',
                'slug' => 'in-which-regions-do-you-operate',
                'status' => 'publish',
                'content' => 'We operate in multiple regions. The possibility of providing services and delivering goods depends on the region.',
                'category' => 'General Information',
                'tags' => array('Important'),
                'order' => 2
            ),
            array(
                'title' => 'Is the information on the website a public offer?',
                'slug' => 'is-the-information-on-the-website-a-public-offer',
                'status' => 'publish',
                'content' => 'The information on the website is for reference only and is not a public offer unless otherwise expressly stated.',
                'category' => 'General Information',
                'tags' => array('Important'),
                'order' => 3
            ),
            array(
                'title' => 'Can I get a consultation before ordering?',
                'slug' => 'can-i-get-a-consultation-before-ordering',
                'status' => 'publish',
                'content' => 'Yes, you can get a consultation from our specialists through the contact form or by the contacts listed on the website.',
                'category' => 'General Information',
                'tags' => array('Consultation', 'Popular'),
                'order' => 4
            ),
            array(
                'title' => 'How can I find out current prices?',
                'slug' => 'how-can-i-find-out-current-prices',
                'status' => 'publish',
                'content' => 'Current prices for goods and services are indicated in the relevant sections of the website and may be changed without prior notice.',
                'category' => 'General Information',
                'tags' => array('Popular'),
                'order' => 5
            ),
            array(
                'title' => 'How do I place an order for a service?',
                'slug' => 'how-do-i-place-an-order-for-a-service',
                'status' => 'publish',
                'content' => 'Select the desired service on the website, fill out the application form or contact us in a convenient way.',
                'category' => 'Ordering Services and Purchasing Products',
                'tags' => array('Order', 'Popular'),
                'order' => 6
            ),
            array(
                'title' => 'How do I buy a product through the website?',
                'slug' => 'how-do-i-buy-a-product-through-the-website',
                'status' => 'publish',
                'content' => 'Add the product to the cart, place an order and choose a convenient payment and delivery method.',
                'category' => 'Ordering Services and Purchasing Products',
                'tags' => array('Order'),
                'order' => 7
            ),
            array(
                'title' => 'Can I place an order without registration?',
                'slug' => 'can-i-place-an-order-without-registration',
                'status' => 'publish',
                'content' => 'Yes, placing an order is possible without registration, however registration provides additional benefits and convenience in managing orders.',
                'category' => 'Ordering Services and Purchasing Products',
                'tags' => array('Order', 'Popular'),
                'order' => 8
            ),
            array(
                'title' => 'How can I find out the status of my order?',
                'slug' => 'how-can-i-find-out-the-status-of-my-order',
                'status' => 'publish',
                'content' => 'The order status can be checked in your personal account or by contacting customer support.',
                'category' => 'Ordering Services and Purchasing Products',
                'tags' => array('Order'),
                'order' => 9
            ),
            array(
                'title' => 'Can I change or cancel my order?',
                'slug' => 'can-i-change-or-cancel-my-order',
                'status' => 'publish',
                'content' => 'The possibility of changing or canceling an order depends on the stage of its processing. We recommend contacting us as early as possible.',
                'category' => 'Ordering Services and Purchasing Products',
                'tags' => array('Order', 'Important'),
                'order' => 10
            ),
            array(
                'title' => 'What payment methods do you accept?',
                'slug' => 'what-payment-methods-do-you-accept',
                'status' => 'publish',
                'content' => 'We accept payment by bank cards and other methods available in your region.',
                'category' => 'Payment and Delivery',
                'tags' => array('Payment', 'Popular'),
                'order' => 11
            ),
            array(
                'title' => 'Is payment on the website secure?',
                'slug' => 'is-payment-on-the-website-secure',
                'status' => 'publish',
                'content' => 'Yes, all payments are processed through secure payment systems using modern security protocols.',
                'category' => 'Payment and Delivery',
                'tags' => array('Payment', 'Security'),
                'order' => 12
            ),
            array(
                'title' => 'Do you provide documents for accounting?',
                'slug' => 'do-you-provide-documents-for-accounting',
                'status' => 'publish',
                'content' => 'Yes, upon request, the necessary closing documents are provided in electronic or printed form.',
                'category' => 'Payment and Delivery',
                'tags' => array('Documents'),
                'order' => 13
            ),
            array(
                'title' => 'What delivery methods are available?',
                'slug' => 'what-delivery-methods-are-available',
                'status' => 'publish',
                'content' => 'Delivery methods and terms depend on the region and the selected product. Detailed information is indicated when placing an order.',
                'category' => 'Payment and Delivery',
                'tags' => array('Delivery', 'Popular'),
                'order' => 14
            ),
            array(
                'title' => 'How much does delivery cost?',
                'slug' => 'how-much-does-delivery-cost',
                'status' => 'publish',
                'content' => 'The delivery cost is calculated individually and displayed at the order placement stage.',
                'category' => 'Payment and Delivery',
                'tags' => array('Delivery'),
                'order' => 15
            ),
            array(
                'title' => 'Do you provide warranty on products and services?',
                'slug' => 'do-you-provide-warranty-on-products-and-services',
                'status' => 'publish',
                'content' => 'Yes, warranty obligations are provided in accordance with the manufacturer\'s conditions and applicable laws.',
                'category' => 'Warranty, Returns and Security',
                'tags' => array('Warranty', 'Important'),
                'order' => 16
            ),
            array(
                'title' => 'Can I return a product?',
                'slug' => 'can-i-return-a-product',
                'status' => 'publish',
                'content' => 'Product return is possible in accordance with consumer protection laws and return conditions published on the website.',
                'category' => 'Warranty, Returns and Security',
                'tags' => array('Return', 'Popular'),
                'order' => 17
            ),
            array(
                'title' => 'What should I do if the product is of poor quality?',
                'slug' => 'what-should-i-do-if-the-product-is-of-poor-quality',
                'status' => 'publish',
                'content' => 'In this case, contact us through customer support for prompt resolution of the issue.',
                'category' => 'Warranty, Returns and Security',
                'tags' => array('Return', 'Warranty'),
                'order' => 18
            ),
            array(
                'title' => 'How are customer personal data processed?',
                'slug' => 'how-are-customer-personal-data-processed',
                'status' => 'publish',
                'content' => 'We process personal data in strict accordance with the requirements of data protection legislation.',
                'category' => 'Warranty, Returns and Security',
                'tags' => array('Security', 'Important'),
                'order' => 19
            ),
            array(
                'title' => 'Is my data shared with third parties?',
                'slug' => 'is-my-data-shared-with-third-parties',
                'status' => 'publish',
                'content' => 'Personal data is not shared with third parties without user consent, except in cases provided by law.',
                'category' => 'Warranty, Returns and Security',
                'tags' => array('Security'),
                'order' => 20
            ),
        )
    );
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
        error_log('Demo FAQ: ' . __('Error creating record', 'codeweber') . ' - ' . $post_id->get_error_message());
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
            'message' => __('No data found or file is corrupted', 'codeweber'),
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
            $errors[] = __('Failed to create:', 'codeweber') . ' ' . (!empty($item['title']) ? $item['title'] : __('unknown', 'codeweber'));
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


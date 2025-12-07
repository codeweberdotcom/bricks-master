<?php
/**
 * Post Card Templates System
 * 
 * НОВАЯ централизованная система для рендеринга карточек блога
 * Не конфликтует с существующими функциями темы
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Рендерить карточку поста по шаблону
 * 
 * @param WP_Post|int $post Объект поста или ID
 * @param string $template_name Имя шаблона (default, card, card-content, slider, default-clickable)
 * @param array $display_settings Настройки отображения элементов
 * @param array $template_args Дополнительные аргументы шаблона
 * @return string HTML карточки
 */
function cw_render_post_card($post, $template_name = 'default', $display_settings = [], $template_args = []) {
    // Загружаем helpers
    $helpers_path = get_template_directory() . '/templates/post-cards/helpers.php';
    if (file_exists($helpers_path)) {
        require_once $helpers_path;
    }
    
    // Получаем данные поста
    $post_data = cw_get_post_card_data($post, $template_args['image_size'] ?? 'full');
    if (!$post_data) {
        return '';
    }
    
    // Загружаем шаблон
    $template_path = get_template_directory() . '/templates/post-cards/' . sanitize_file_name($template_name) . '.php';
    if (!file_exists($template_path)) {
        // Fallback на default
        $template_path = get_template_directory() . '/templates/post-cards/default.php';
    }
    
    ob_start();
    include $template_path;
    return ob_get_clean();
}

/**
 * Новый шорткод для новой системы шаблонов
 * Старый шорткод blog_posts_slider остается без изменений
 * 
 * @param array $atts Атрибуты шорткода
 * @return string HTML слайдера
 */
function cw_blog_posts_slider_shortcode($atts) {
    // Сохраняем исходные атрибуты для проверки enable_lift
    $original_atts = $atts;
    
    // Атрибуты по умолчанию
    $atts = shortcode_atts([
        'posts_per_page' => 4,
        'category' => '',      // Фильтр по категориям (slug через запятую)
        'tag' => '',           // Фильтр по меткам (slug через запятую)
        'post_type' => 'post', // Тип записей
        'orderby' => 'date',
        'order' => 'DESC',
        'image_size' => 'codeweber_single',
        'excerpt_length' => 20,
        'title_length' => 0,
        'template' => 'default', // default, card, card-content, slider, default-clickable
        'enable_hover_scale' => 'false', // Включить hover-scale эффект для default шаблона (true/false)
        // Настройки отображения элементов
        'show_title' => 'true',
        'show_date' => 'true',
        'show_category' => 'true',
        'show_comments' => 'true',
        'title_tag' => 'h2', // h1, h2, h3, h4, h5, h6, p, div, span
        'title_class' => '',
        'enable_lift' => 'false', // Включить lift эффект для кликабельной карточки (true/false)
        // Настройки Swiper
        'items_xl' => '3',
        'items_lg' => '3',
        'items_md' => '2',
        'items_sm' => '2',
        'items_xs' => '1',
        'items_xxs' => '1',
        'margin' => '30',
        'dots' => 'true',
        'nav' => 'false',
        'autoplay' => 'false',
        'loop' => 'false'
    ], $atts);
    
    // Аргументы для WP_Query
    $args = [
        'post_type' => $atts['post_type'],
        'posts_per_page' => intval($atts['posts_per_page']),
        'orderby' => $atts['orderby'],
        'order' => $atts['order'],
        'post_status' => 'publish'
    ];
    
    // Добавляем фильтр по категориям если указан
    if (!empty($atts['category'])) {
        $category_slugs = array_map('trim', explode(',', $atts['category']));
        $args['tax_query'] = [
            [
                'taxonomy' => 'category',
                'field' => 'slug',
                'terms' => $category_slugs,
            ]
        ];
    }
    
    // Добавляем фильтр по меткам если указан
    if (!empty($atts['tag'])) {
        $tag_slugs = array_map('trim', explode(',', $atts['tag']));
        $args['tag_slug__in'] = $tag_slugs;
    }
    
    $blog_query = new WP_Query($args);
    
    if (!$blog_query->have_posts()) {
        return '';
    }
    
    // Настройки отображения
    $display_settings = [
        'show_title' => $atts['show_title'] === 'true',
        'show_date' => $atts['show_date'] === 'true',
        'show_category' => $atts['show_category'] === 'true',
        'show_comments' => $atts['show_comments'] === 'true',
        'title_length' => intval($atts['title_length']),
        'excerpt_length' => intval($atts['excerpt_length']),
        'title_tag' => $atts['title_tag'],
        'title_class' => $atts['title_class'],
    ];
    
    // Настройки шаблона
    $hover_classes = 'overlay overlay-1';
    // Добавляем hover-scale для соответствующих шаблонов
    if ($atts['template'] === 'slider') {
        $hover_classes .= ' hover-scale';
    }
    
    // Для default-clickable по умолчанию включаем lift, если явно не указано 'false'
    if ($atts['template'] === 'default-clickable') {
        // Проверяем, был ли параметр передан явно в исходных атрибутах
        $enable_lift_explicitly_set = isset($original_atts['enable_lift']);
        if ($enable_lift_explicitly_set) {
            // Если был передан явно, используем его значение
            $enable_lift = $atts['enable_lift'] === 'true';
        } else {
            // Если не был передан, по умолчанию true для default-clickable
            $enable_lift = true;
        }
    } else {
        // Для остальных шаблонов только если явно указано 'true'
        $enable_lift = $atts['enable_lift'] === 'true';
    }
    
    $template_args = [
        'image_size' => $atts['image_size'],
        'hover_classes' => $hover_classes,
        'border_radius' => 'rounded',
        'show_figcaption' => true,
        'enable_lift' => $enable_lift,
        'enable_hover_scale' => $atts['enable_hover_scale'] === 'true', // Для default шаблона
    ];
    
    // Данные для Swiper
    $swiper_data = [
        'data-margin' => esc_attr($atts['margin']),
        'data-dots' => esc_attr($atts['dots']),
        'data-nav' => esc_attr($atts['nav']),
        'data-autoplay' => esc_attr($atts['autoplay']),
        'data-loop' => esc_attr($atts['loop']),
        'data-items-xl' => esc_attr($atts['items_xl']),
        'data-items-lg' => esc_attr($atts['items_lg']),
        'data-items-md' => esc_attr($atts['items_md']),
        'data-items-sm' => esc_attr($atts['items_sm']),
        'data-items-xs' => esc_attr($atts['items_xs']),
        'data-items-xxs' => esc_attr($atts['items_xxs'])
    ];
    
    $swiper_attrs = '';
    foreach ($swiper_data as $key => $value) {
        $swiper_attrs .= $key . '="' . $value . '" ';
    }
    
    // Генерируем уникальный ID для этого слайдера
    $slider_id = 'cw-slider-' . uniqid();
    
    ob_start();
    ?>
    
    <style>
        .<?php echo esc_attr($slider_id); ?> .swiper-wrapper {
            align-items: stretch !important;
        }
        .<?php echo esc_attr($slider_id); ?> .swiper-slide {
            height: auto !important;
            display: flex !important;
        }
        .<?php echo esc_attr($slider_id); ?> .swiper-slide > div {
            width: 100%;
            display: flex;
            flex-direction: column;
        }
    </style>
    
    <div class="swiper-container dots-closer blog grid-view mb-12 <?php echo esc_attr($slider_id); ?>" <?php echo $swiper_attrs; ?>>
        <div class="swiper">
            <div class="swiper-wrapper">
                <?php while ($blog_query->have_posts()) : $blog_query->the_post(); ?>
                    <div class="swiper-slide">
                        <div class="mb-1 d-flex flex-column h-100">
                            <?php echo cw_render_post_card(get_post(), $atts['template'], $display_settings, $template_args); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}

// Регистрируем новый шорткод
add_shortcode('cw_blog_posts_slider', 'cw_blog_posts_slider_shortcode');


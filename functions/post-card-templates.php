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
    // Загружаем helpers (сначала из дочерней темы, затем из родительской)
    $helpers_path = get_theme_file_path('templates/post-cards/helpers.php');
    if ($helpers_path && file_exists($helpers_path)) {
        require_once $helpers_path;
    }
    
    // Получаем данные поста
    $enable_link = isset($template_args['enable_link']) ? $template_args['enable_link'] : false;
    $post_data = cw_get_post_card_data($post, $template_args['image_size'] ?? 'full', $enable_link);
    if (!$post_data) {
        return '';
    }
    
    // Определяем тип записи
    $post_type = is_object($post) ? $post->post_type : get_post_type($post);
    
    // Определяем папку для шаблонов
    $template_dir = 'post'; // По умолчанию
    $template_file = sanitize_file_name($template_name);
    
    // Если шаблон начинается с "client-", это шаблон для clients
    if (strpos($template_name, 'client-') === 0) {
        $template_dir = 'clients';
        $template_file = str_replace('client-', '', $template_file);
    } elseif (strpos($template_name, 'testimonial-') === 0) {
        // Если шаблон начинается с "testimonial-", это шаблон для testimonials
        $template_dir = 'testimonials';
        $template_file = str_replace('testimonial-', '', $template_file);
    } elseif (strpos($template_name, 'document-') === 0) {
        // Если шаблон начинается с "document-", это шаблон для documents
        $template_dir = 'documents';
        $template_file = str_replace('document-', '', $template_file);
    } elseif (strpos($template_name, 'faq-') === 0) {
        // Если шаблон начинается с "faq-", это шаблон для faq
        $template_dir = 'faq';
        $template_file = str_replace('faq-', '', $template_file);
    } elseif (strpos($template_name, 'staff-') === 0) {
        // Если шаблон начинается с "staff-", это шаблон для staff
        $template_dir = 'staff';
        $template_file = str_replace('staff-', '', $template_file);
    } elseif (strpos($template_name, 'office-') === 0) {
        // Если шаблон начинается с "office-", это шаблон для offices
        $template_dir = 'offices';
        $template_file = str_replace('office-', '', $template_file);
    } elseif ($post_type === 'clients') {
        // Если тип записи clients, ищем в папке clients
        $template_dir = 'clients';
    } elseif ($post_type === 'testimonials') {
        // Если тип записи testimonials, ищем в папке testimonials
        $template_dir = 'testimonials';
    } elseif ($post_type === 'documents') {
        // Если тип записи documents, ищем в папке documents
        $template_dir = 'documents';
    } elseif ($post_type === 'faq') {
        // Если тип записи faq, ищем в папке faq
        $template_dir = 'faq';
    } elseif ($post_type === 'staff') {
        // Если тип записи staff, ищем в папке staff
        $template_dir = 'staff';
    } elseif ($post_type === 'offices') {
        // Если тип записи offices, ищем в папке offices
        $template_dir = 'offices';
    } elseif (strpos($template_name, 'vacancy-') === 0) {
        // Если шаблон начинается с "vacancy-", это шаблон для vacancies
        $template_dir = 'post';
        $template_file = str_replace('vacancy-', '', $template_file);
    } elseif ($post_type === 'vacancies') {
        // Если тип записи vacancies, используем шаблоны из post
        $template_dir = 'post';
    }
    
    // Путь к шаблону: сначала дочерняя тема, затем родительская (get_theme_file_path)
    $template_path = get_theme_file_path('templates/post-cards/' . $template_dir . '/' . $template_file . '.php');
    
    // Fallback: проверяем старую структуру (для обратной совместимости)
    if (!$template_path || !file_exists($template_path)) {
        $old_template_path = get_theme_file_path('templates/post-cards/' . sanitize_file_name($template_name) . '.php');
        if ($old_template_path && file_exists($old_template_path)) {
            $template_path = $old_template_path;
        } else {
            // Fallback на default в соответствующей папке
            $default_path = get_theme_file_path('templates/post-cards/' . $template_dir . '/default.php');
            if ($default_path && file_exists($default_path)) {
                $template_path = $default_path;
            } else {
                // Последний fallback - default в post
                $template_path = get_theme_file_path('templates/post-cards/post/default.php');
            }
        }
    }
    
    // Передаем переменные в шаблон явно
    ob_start();
    // Делаем переменные доступными в шаблоне
    $template_file = $template_path;
    include $template_file;
    return ob_get_clean();
}

/**
 * Нормализация булева значения (строка/число из шорткода или bool из PHP).
 *
 * @param mixed $v
 * @return bool
 */
function _cw_blog_posts_slider_bool($v) {
    return ($v === true || $v === 'true' || $v === '1');
}

/**
 * Универсальная функция вывода слайдера постов блога.
 * Используется и шорткодом [cw_blog_posts_slider], и прямым вызовом из шаблонов.
 *
 * @param array $args Аргументы (аналогичны атрибутам шорткода). Поддерживаются и строки 'true'/'false', и bool.
 * @return string HTML слайдера или пустая строка, если постов нет.
 */
function cw_blog_posts_slider($args = []) {
    $defaults = [
        'posts_per_page' => 4,
        'category' => '',
        'tag' => '',
        'post_type' => 'post',
        'orderby' => 'date',
        'order' => 'DESC',
        'image_size' => 'codeweber_single',
        'excerpt_length' => 20,
        'title_length' => 0,
        'template' => 'default',
        'enable_hover_scale' => 'false',
        'show_title' => 'true',
        'show_date' => 'true',
        'show_category' => 'true',
        'show_comments' => 'true',
        'title_tag' => 'h2',
        'title_class' => '',
        'enable_lift' => 'false',
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
        'loop' => 'false',
        'layout' => 'swiper', // swiper | grid — слайдер или обычная Bootstrap-сетка
        'gap' => '30', // отступ между карточками в сетке (px, для layout="grid")
    ];

    $atts = array_merge($defaults, array_filter($args, function ($v) {
        return $v !== null && $v !== '';
    }));

    // Нормализация строковых значений из шорткода (shortcode_atts всё приводит к строкам при вызове из шорткода)
    foreach (['posts_per_page', 'excerpt_length', 'title_length', 'margin'] as $int_key) {
        $atts[$int_key] = is_numeric($atts[$int_key]) ? intval($atts[$int_key]) : $atts[$int_key];
    }

    $query_args = [
        'post_type' => $atts['post_type'],
        'posts_per_page' => is_numeric($atts['posts_per_page']) ? intval($atts['posts_per_page']) : 4,
        'orderby' => $atts['orderby'],
        'order' => $atts['order'],
        'post_status' => 'publish',
    ];

    if (!empty($atts['category'])) {
        $category_slugs = array_map('trim', explode(',', $atts['category']));
        $query_args['tax_query'] = [
            [
                'taxonomy' => 'category',
                'field' => 'slug',
                'terms' => $category_slugs,
            ],
        ];
    }

    if (!empty($atts['tag'])) {
        $tag_slugs = array_map('trim', explode(',', $atts['tag']));
        $query_args['tag_slug__in'] = $tag_slugs;
    }

    $blog_query = new WP_Query($query_args);

    if (!$blog_query->have_posts()) {
        return '';
    }

    $display_settings = [
        'show_title' => _cw_blog_posts_slider_bool($atts['show_title']),
        'show_date' => _cw_blog_posts_slider_bool($atts['show_date']),
        'show_category' => _cw_blog_posts_slider_bool($atts['show_category']),
        'show_comments' => _cw_blog_posts_slider_bool($atts['show_comments']),
        'title_length' => is_numeric($atts['title_length']) ? intval($atts['title_length']) : 0,
        'excerpt_length' => is_numeric($atts['excerpt_length']) ? intval($atts['excerpt_length']) : 20,
        'title_tag' => $atts['title_tag'],
        'title_class' => $atts['title_class'],
    ];

    $hover_classes = 'overlay overlay-1';
    if (isset($atts['template']) && $atts['template'] === 'overlay-5') {
        $hover_classes = 'overlay overlay-5';
    }
    if (isset($atts['template']) && $atts['template'] === 'slider') {
        $hover_classes .= ' hover-scale';
    }

    if (isset($atts['template']) && $atts['template'] === 'default-clickable') {
        $enable_lift = array_key_exists('enable_lift', $args)
            ? _cw_blog_posts_slider_bool($atts['enable_lift'])
            : true;
    } else {
        $enable_lift = _cw_blog_posts_slider_bool($atts['enable_lift']);
    }

    $template_args = [
        'image_size' => $atts['image_size'],
        'hover_classes' => $hover_classes,
        'border_radius' => function_exists('getThemeCardImageRadius') ? (getThemeCardImageRadius() ?: 'rounded') : 'rounded',
        'show_figcaption' => true,
        'enable_lift' => $enable_lift,
        'enable_hover_scale' => _cw_blog_posts_slider_bool($atts['enable_hover_scale']),
    ];

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
        'data-items-xxs' => esc_attr($atts['items_xxs']),
    ];

    $swiper_attrs = '';
    foreach ($swiper_data as $key => $value) {
        $swiper_attrs .= $key . '="' . $value . '" ';
    }

    $layout = isset($atts['layout']) ? sanitize_key($atts['layout']) : 'swiper';
    $slider_id = 'cw-slider-' . uniqid();

    ob_start();

    if ($layout === 'grid') {
        // Обычная Bootstrap-сетка (без Swiper)
        $gap = is_numeric($atts['gap']) ? (int) $atts['gap'] : 30;
        $row_classes = 'row row-cols-1';
        $row_classes .= ' row-cols-sm-' . esc_attr($atts['items_sm']);
        $row_classes .= ' row-cols-md-' . esc_attr($atts['items_md']);
        $row_classes .= ' row-cols-lg-' . esc_attr($atts['items_lg']);
        $row_classes .= ' row-cols-xl-' . esc_attr($atts['items_xl']);
        $gap_style = $gap > 0 ? ' style="--bs-gap: ' . esc_attr($gap) . 'px"' : '';
        ?>
        <div class="cw-blog-posts-grid mb-12 <?php echo esc_attr($slider_id); ?>">
            <div class="<?php echo $row_classes; ?>"<?php echo $gap_style; ?>>
                <?php while ($blog_query->have_posts()) : $blog_query->the_post(); ?>
                    <div class="col">
                        <div class="d-flex flex-column h-100">
                            <?php echo cw_render_post_card(get_post(), $atts['template'], $display_settings, $template_args); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php
    } else {
        // Swiper (по умолчанию)
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
    }

    wp_reset_postdata();
    return ob_get_clean();
}

/**
 * Шорткод [cw_blog_posts_slider] — обёртка над cw_blog_posts_slider().
 *
 * @param array $atts Атрибуты шорткода
 * @return string HTML слайдера
 */
function cw_blog_posts_slider_shortcode($atts) {
    $original_atts = $atts;
    $atts = shortcode_atts([
        'posts_per_page' => 4,
        'category' => '',
        'tag' => '',
        'post_type' => 'post',
        'orderby' => 'date',
        'order' => 'DESC',
        'image_size' => 'codeweber_single',
        'excerpt_length' => 20,
        'title_length' => 0,
        'template' => 'default',
        'enable_hover_scale' => 'false',
        'show_title' => 'true',
        'show_date' => 'true',
        'show_category' => 'true',
        'show_comments' => 'true',
        'title_tag' => 'h2',
        'title_class' => '',
        'enable_lift' => 'false',
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
        'loop' => 'false',
        'layout' => 'swiper',
        'gap' => '30',
    ], $atts);

    if (isset($atts['template']) && $atts['template'] === 'default-clickable' && !isset($original_atts['enable_lift'])) {
        $atts['enable_lift'] = 'true';
    }

    return cw_blog_posts_slider($atts);
}

add_shortcode('cw_blog_posts_slider', 'cw_blog_posts_slider_shortcode');

/**
 * Шорткод для отображения клиентов
 * 
 * @param array $atts Атрибуты шорткода
 * @return string HTML
 */
function cw_clients_shortcode($atts) {
    $atts = shortcode_atts([
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'template' => 'client-simple', // client-simple, client-grid, client-card
        'image_size' => 'codeweber_clients_300-200',
        'layout' => 'swiper', // swiper, grid, grid-cards
        'enable_link' => 'false', // Включить ссылки на записи (true/false)
        // Swiper настройки
        'items_xl' => '7',
        'items_lg' => '6',
        'items_md' => '4',
        'items_sm' => '2',
        'items_xs' => '2',
        'margin' => '0',
        'dots' => 'false',
        'nav' => 'false',
        'autoplay' => 'false',
        'loop' => 'true',
        // Grid настройки
        'columns_xl' => '4',
        'columns_md' => '2',
        'gap' => '12',
    ], $atts);
    
    $args = [
        'post_type' => 'clients',
        'posts_per_page' => intval($atts['posts_per_page']),
        'orderby' => $atts['orderby'],
        'order' => $atts['order'],
        'post_status' => 'publish'
    ];
    
    $query = new WP_Query($args);
    
    if (!$query->have_posts()) {
        return '';
    }
    
    $display_settings = [
        'show_title' => false,
        'show_date' => false,
        'show_category' => false,
        'show_comments' => false,
    ];
    
    $template_args = [
        'image_size' => $atts['image_size'],
        'enable_link' => $atts['enable_link'] === 'true',
    ];
    
    ob_start();
    
    if ($atts['layout'] === 'swiper') {
        // Swiper layout
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
        ];
        
        $swiper_attrs = '';
        foreach ($swiper_data as $key => $value) {
            $swiper_attrs .= $key . '="' . $value . '" ';
        }
        
        ?>
        <div class="swiper-container clients mb-0" <?php echo $swiper_attrs; ?>>
            <div class="swiper">
                <div class="swiper-wrapper">
                    <?php while ($query->have_posts()) : $query->the_post(); ?>
                        <div class="swiper-slide px-5">
                            <?php echo cw_render_post_card(get_post(), $atts['template'], $display_settings, $template_args); ?>
                        </div>
                    <?php endwhile; ?>
                </div>
                <!--/.swiper-wrapper -->
            </div>
            <!-- /.swiper -->
        </div>
        <!-- /.swiper-container -->
        <?php
    } elseif ($atts['layout'] === 'grid-cards') {
        // Grid with cards
        ?>
        <div class="row row-cols-2 row-cols-md-3 row-cols-xl-<?php echo esc_attr($atts['columns_xl']); ?> gx-lg-6 gy-6 justify-content-center">
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <div class="col">
                    <?php echo cw_render_post_card(get_post(), $atts['template'], $display_settings, $template_args); ?>
                </div>
                <!--/column -->
            <?php endwhile; ?>
        </div>
        <!--/.row -->
        <?php
    } else {
        // Simple grid
        ?>
        <div class="row row-cols-2 row-cols-md-<?php echo esc_attr($atts['columns_md']); ?> row-cols-xl-<?php echo esc_attr($atts['columns_xl']); ?> gx-0 gx-md-8 gx-xl-<?php echo esc_attr($atts['gap']); ?> gy-12">
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <div class="col">
                    <?php echo cw_render_post_card(get_post(), $atts['template'], $display_settings, $template_args); ?>
                </div>
                <!--/column -->
            <?php endwhile; ?>
        </div>
        <!--/.row -->
        <?php
    }
    
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('cw_clients', 'cw_clients_shortcode');


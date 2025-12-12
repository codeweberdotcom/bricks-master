<?php

/**
 * Регистрация сайдбаров в теме CodeWeber
 */

// Проверка наличия функции codeweber_sidebars
if (!function_exists('codeweber_sidebars')) {
    function codeweber_sidebars($sidebar_name, $sidebar_id, $sidebar_description, $title_tag = 'h3', $title_class = 'mb-4')
    {
        register_sidebar([
            'name'          => esc_html__($sidebar_name, 'codeweber'),
            'id'            => $sidebar_id,
            'description'   => esc_html__($sidebar_description, 'codeweber'),
            'before_widget' => '<div class="widget mb-4 %2$s clearfix">',
            'after_widget'  => '</div>',
            'before_title'  => "<{$title_tag} class=\"{$title_class}\">",
            'after_title'   => "</{$title_tag}>",
        ]);
    }
}

/**
 * Регистрация основного сайдбара
 */
function codeweber_register_main_sidebar()
{
    codeweber_sidebars(
        __('Main Sidebar', 'codeweber'),
        'sidebar-main',
        __('Main Sidebar', 'codeweber'),
        'h3',
        'custom-title-class'
    );
}
add_action('widgets_init', 'codeweber_register_main_sidebar');

/**
 * Регистрация сайдбара для WooCommerce, если плагин активен
 */
function codeweber_register_woo_sidebar()
{
    if (class_exists('WooCommerce')) {
        codeweber_sidebars(
            __('Woo Sidebar', 'codeweber'),
            'sidebar-woo',
            __('Woo Sidebar', 'codeweber'),
            'h3',
            'custom-title-class'
        );
    }
}
add_action('widgets_init', 'codeweber_register_woo_sidebar');

/**
 * Регистрация сайдбара в правой части заголовка
 */
function theme_register_header_widget()
{
    register_sidebar([
        'name'          => __('Header Right', 'codeweber'),
        'id'            => 'header-right',
        'description'   => __('Widget area on the right side of the Header', 'codeweber'),
        'before_widget' => '<div class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);

    register_sidebar([
        'name'          => __('Header Right 1', 'codeweber'),
        'id'            => 'header-right-1',
        'description'   => __('Widget area on the right side of the Header', 'codeweber'),
        'before_widget' => '<div class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);

    register_sidebar([
        'name'          => __('Mobile Menu Footer', 'codeweber'),
        'id'            => 'mobile-menu-footer',
        'description'   => __('Widget area on the right side of the Header', 'codeweber'),
        'before_widget' => '<div class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);

    register_sidebar([
        'name'          => __('Header Widget 1', 'codeweber'),
        'id'            => 'header-widget-1',
        'before_widget' => '<div class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);

    register_sidebar([
        'name'          => __('Header Widget 2', 'codeweber'),
        'id'            => 'header-widget-2',
        'before_widget' => '<div class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);

    register_sidebar([
        'name'          => __('Header Widget 3', 'codeweber'),
        'id'            => 'header-widget-3',
        'before_widget' => '<div class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);

}
add_action('widgets_init', 'theme_register_header_widget');


add_action('codeweber_after_widget', function ($sidebar_id) {
    if ($sidebar_id === 'legal') {
        // Проверяем, существует ли тип записи 'legal'
        if (!post_type_exists('legal')) {
            return; // Прекращаем выполнение, если тип записи не существует
        }

        $legal_posts = get_posts([
            'post_type'      => 'legal',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ]);

        if ($legal_posts) {
            echo '<div class="widget">
                    <nav id="sidebar-nav">
                        <ul class="list-unstyled text-reset">';

            $index = 1;
            $current_id = get_queried_object_id();

            foreach ($legal_posts as $post) {
                // Проверяем мета _hide_from_archive
                $hide = get_post_meta($post->ID, '_hide_from_archive', true);
                if ($hide === '1') {
                    continue; // пропускаем скрытую запись
                }

                $permalink = get_permalink($post);
                $active_class = ($current_id === $post->ID) ? ' active' : '';
                echo '<li><a class="nav-link' . $active_class . '" href="' . esc_url($permalink) . '">' . $index . '. ' . esc_html(get_the_title($post)) . '</a></li>';
                $index++;
            }

            echo '</ul>
                 </nav>
              </div>';
        }
    }
    
    if ($sidebar_id === 'vacancies') {
        // Проверяем, существует ли тип записи 'vacancies'
        if (!post_type_exists('vacancies')) {
            return;
        }

        // Проверяем, что мы на single странице вакансии
        if (!is_singular('vacancies')) {
            return;
        }

        $vacancy_data = get_vacancy_data_array();

        // Массив переводов для типа занятости
        $employment_types = array(
            'full-time'  => __('Full-time', 'codeweber'),
            'part-time'  => __('Part-time', 'codeweber'),
            'internship' => __('Internship', 'codeweber'),
            'contract'   => __('Contract', 'codeweber')
        );

        $type = $vacancy_data['employment_type'] ?? '';
        $display_type = isset($employment_types[$type]) ? $employment_types[$type] : $type;

        $user_id = get_the_author_meta('ID');
        
        // Проверяем оба возможных ключа для аватара
        $avatar_id = get_user_meta($user_id, 'avatar_id', true);
        if (empty($avatar_id)) {
            $avatar_id = get_user_meta($user_id, 'custom_avatar_id', true);
        }

        $job_title = get_user_meta($user_id, 'user_position', true);
        if (empty($job_title)) {
            $job_title = __('Writer', 'codeweber');
        }
        
        // Получаем стиль кнопок из Redux
        $button_style = function_exists('getThemeButton') ? getThemeButton('') : '';
        $card_radius = function_exists('getThemeCardImageRadius') ? getThemeCardImageRadius() : '';
?>
        <div class="widget">
            <div class="card<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
                <?php 
                $thumbnail_id = get_post_thumbnail_id();
                $image_url = '';
                if ($thumbnail_id) {
                    // Используем специальный размер для вакансий
                    $image_url = wp_get_attachment_image_url($thumbnail_id, 'codeweber_vacancy');
                }
                
                // Если нет картинки, используем fallback
                if (empty($image_url)) {
                    $image_url = get_template_directory_uri() . '/dist/assets/img/photos/about6.jpg';
                }
                ?>
                <figure<?php echo $card_radius ? ' class="' . esc_attr($card_radius) . '"' : ''; ?>>
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" class="img-fluid">
                </figure>

                <div class="card-body">
                    <div class="mb-6">
                        <h3 class="mb-4"><?php _e('Details', 'codeweber'); ?></h3>

                        <?php if (!empty($vacancy_data['location'])) : ?>
                            <p class="mb-1 d-flex align-items-center">
                                <i class="uil uil-map-marker-alt text-primary me-2"></i>
                                <span><?php echo esc_html($vacancy_data['location']); ?></span>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($vacancy_data['employment_type'])) : ?>
                            <p class="mb-1 d-flex align-items-center">
                                <i class="uil uil-calendar-alt text-primary me-2"></i>
                                <span><?php echo esc_html($display_type); ?></span>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($vacancy_data['salary'])) : ?>
                            <p class="mb-1 d-flex align-items-center">
                                <i class="uil uil-money-stack text-primary me-2"></i>
                                <span><?php echo esc_html($vacancy_data['salary']); ?></span>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="mb-6">
                        <div class="author-info d-flex align-items-center">
                            <div class="d-flex align-items-center">
                                <?php if (!empty($avatar_id)) : ?>
                                    <?php $avatar_src = wp_get_attachment_image_src($avatar_id, 'thumbnail'); ?>
                                    <figure class="user-avatar me-3">
                                        <img class="rounded-circle" alt="<?php the_author_meta('display_name'); ?>" src="<?php echo esc_url($avatar_src[0]); ?>">
                                    </figure>
                                <?php else : ?>
                                    <figure class="user-avatar me-3">
                                        <?php echo get_avatar(get_the_author_meta('user_email'), 96, '', '', ['class' => 'rounded-circle']); ?>
                                    </figure>
                                <?php endif; ?>

                                <div>
                                    <h6 class="mb-0">
                                        <a href="<?php echo esc_url(get_author_posts_url($user_id)); ?>" class="link-dark">
                                            <?php the_author_meta('first_name'); ?> <?php the_author_meta('last_name'); ?>
                                        </a>
                                    </h6>
                                    <span class="post-meta fs-15"><?php echo esc_html($job_title); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($vacancy_data['pdf_url'])) : ?>
                        <a href="javascript:void(0)" class="btn btn-primary btn-icon btn-icon-start w-100 mb-2<?php echo esc_attr($button_style); ?>" data-bs-toggle="download" data-value="vac-<?php echo esc_attr(get_the_ID()); ?>">
                            <i class="uil uil-file-download"></i>
                            <?php _e('Download document', 'codeweber'); ?>
                        </a>
                    <?php endif; ?>
                </div>
                <!--/.card-body -->
            </div>
            <!--/.card -->
        </div>
        <!--/.widget -->
        <?php
    }
});

// Также добавляем виджет для vacancies в хук codeweber_after_sidebar (когда есть активные виджеты)
add_action('codeweber_after_sidebar', function ($sidebar_id) {
    if ($sidebar_id === 'vacancies') {
        // Проверяем, существует ли тип записи 'vacancies'
        if (!post_type_exists('vacancies')) {
            return;
        }

        // Проверяем, что мы на single странице вакансии
        if (!is_singular('vacancies')) {
            return;
        }

        $vacancy_data = get_vacancy_data_array();

        // Массив переводов для типа занятости
        $employment_types = array(
            'full-time'  => __('Full-time', 'codeweber'),
            'part-time'  => __('Part-time', 'codeweber'),
            'internship' => __('Internship', 'codeweber'),
            'contract'   => __('Contract', 'codeweber')
        );

        $type = $vacancy_data['employment_type'] ?? '';
        $display_type = isset($employment_types[$type]) ? $employment_types[$type] : $type;

        $user_id = get_the_author_meta('ID');
        
        // Проверяем оба возможных ключа для аватара
        $avatar_id = get_user_meta($user_id, 'avatar_id', true);
        if (empty($avatar_id)) {
            $avatar_id = get_user_meta($user_id, 'custom_avatar_id', true);
        }

        $job_title = get_user_meta($user_id, 'user_position', true);
        if (empty($job_title)) {
            $job_title = __('Writer', 'codeweber');
        }
        
        // Получаем стиль кнопок из Redux
        $button_style = function_exists('getThemeButton') ? getThemeButton('') : '';
        $card_radius = function_exists('getThemeCardImageRadius') ? getThemeCardImageRadius() : '';
?>
        <div class="widget">
            <div class="card<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
                <?php 
                $thumbnail_id = get_post_thumbnail_id();
                $image_url = '';
                if ($thumbnail_id) {
                    // Используем специальный размер для вакансий
                    $image_url = wp_get_attachment_image_url($thumbnail_id, 'codeweber_vacancy');
                }
                
                // Если нет картинки, используем fallback
                if (empty($image_url)) {
                    $image_url = get_template_directory_uri() . '/dist/assets/img/photos/about6.jpg';
                }
                ?>
                <figure<?php echo $card_radius ? ' class="' . esc_attr($card_radius) . '"' : ''; ?>>
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" class="img-fluid">
                </figure>

                <div class="card-body">
                    <div class="mb-6">
                        <h3 class="mb-4"><?php _e('Details', 'codeweber'); ?></h3>

                        <?php if (!empty($vacancy_data['location'])) : ?>
                            <p class="mb-1 d-flex align-items-center">
                                <i class="uil uil-map-marker-alt text-primary me-2"></i>
                                <span><?php echo esc_html($vacancy_data['location']); ?></span>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($vacancy_data['employment_type'])) : ?>
                            <p class="mb-1 d-flex align-items-center">
                                <i class="uil uil-calendar-alt text-primary me-2"></i>
                                <span><?php echo esc_html($display_type); ?></span>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($vacancy_data['salary'])) : ?>
                            <p class="mb-1 d-flex align-items-center">
                                <i class="uil uil-money-stack text-primary me-2"></i>
                                <span><?php echo esc_html($vacancy_data['salary']); ?></span>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="mb-6">
                        <div class="author-info d-flex align-items-center">
                            <div class="d-flex align-items-center">
                                <?php if (!empty($avatar_id)) : ?>
                                    <?php $avatar_src = wp_get_attachment_image_src($avatar_id, 'thumbnail'); ?>
                                    <figure class="user-avatar me-3">
                                        <img class="rounded-circle" alt="<?php the_author_meta('display_name'); ?>" src="<?php echo esc_url($avatar_src[0]); ?>">
                                    </figure>
                                <?php else : ?>
                                    <figure class="user-avatar me-3">
                                        <?php echo get_avatar(get_the_author_meta('user_email'), 96, '', '', ['class' => 'rounded-circle']); ?>
                                    </figure>
                                <?php endif; ?>

                                <div>
                                    <h6 class="mb-0">
                                        <a href="<?php echo esc_url(get_author_posts_url($user_id)); ?>" class="link-dark">
                                            <?php the_author_meta('first_name'); ?> <?php the_author_meta('last_name'); ?>
                                        </a>
                                    </h6>
                                    <span class="post-meta fs-15"><?php echo esc_html($job_title); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($vacancy_data['pdf_url'])) : ?>
                        <a href="javascript:void(0)" class="btn btn-primary btn-icon btn-icon-start w-100 mb-2<?php echo esc_attr($button_style); ?>" data-bs-toggle="download" data-value="vac-<?php echo esc_attr(get_the_ID()); ?>">
                            <i class="uil uil-file-download"></i>
                            <?php _e('Download document', 'codeweber'); ?>
                        </a>
                    <?php endif; ?>
                </div>
                <!--/.card-body -->
            </div>
            <!--/.card -->
        </div>
        <!--/.widget -->
        <?php
    }
    
    if ($sidebar_id === 'faq') {
        // Проверяем, существует ли тип записи 'faq'
        if (!post_type_exists('faq')) {
            return; // Прекращаем выполнение, если тип записи не существует
        }

        // Получаем текущий якорь из URL (hash)
        $current_anchor = '';
        if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '#') !== false) {
            $current_anchor = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], '#') + 1);
        }

        // Получаем все категории FAQ
        $faq_categories = get_terms([
            'taxonomy'   => 'faq_categories',
            'hide_empty' => true,
        ]);

        if (!empty($faq_categories) && !is_wp_error($faq_categories)) {
            echo '<div class="widget">
                    <nav id="sidebar-nav">
                        <ul class="list-unstyled text-reset">';

            $index = 1;
            
            // Получаем FAQ записи без категорий для проверки наличия секции "Other Questions"
            $uncategorized_faqs = get_posts([
                'post_type'      => 'faq',
                'posts_per_page' => -1,
                'post_status'    => 'publish',
                'orderby'        => 'menu_order',
                'order'          => 'ASC',
                'tax_query'      => [
                    [
                        'taxonomy' => 'faq_categories',
                        'operator' => 'NOT EXISTS',
                    ],
                ],
            ]);

            foreach ($faq_categories as $category) {
                $category_anchor = sanitize_title($category->name);
                $anchor_url = '#' . $category_anchor;
                $active_class = ($current_anchor === $category_anchor) ? ' active' : '';
                
                echo '<li><a class="nav-link scroll' . $active_class . '" href="' . esc_attr($anchor_url) . '">' . $index . '. ' . esc_html($category->name) . '</a></li>';
                $index++;
            }

            // Добавляем пункт для некатегоризированных FAQ, если они есть
            if (!empty($uncategorized_faqs)) {
                $uncategorized_anchor = 'faq-uncategorized';
                $active_class = ($current_anchor === $uncategorized_anchor) ? ' active' : '';
                echo '<li><a class="nav-link scroll' . $active_class . '" href="#' . esc_attr($uncategorized_anchor) . '">' . $index . '. ' . esc_html__('Other Questions', 'codeweber') . '</a></li>';
            }

            echo '</ul>
                 </nav>
              </div>';
        } else {
            // Если нет категорий, выводим ссылку на секцию "faq-all"
            echo '<div class="widget">
                    <nav id="sidebar-nav">
                        <ul class="list-unstyled text-reset">
                            <li><a class="nav-link scroll active" href="#faq-all">' . esc_html__('All FAQs', 'codeweber') . '</a></li>
                        </ul>
                    </nav>
                  </div>';
        }
    }
});



/**
 * Получает позицию сайдбара для текущей страницы/записи
 * 
 * @param string $opt_name Имя опции Redux
 * @return string Позиция сайдбара (left|right|none)
 */
function get_sidebar_position($opt_name)
{
    $post_type = universal_get_post_type();
    $post_id = get_the_ID();
    // #region agent log
    $log_data = json_encode(['location' => 'sidebars.php:246', 'message' => 'Sidebar position check', 'data' => ['opt_name' => $opt_name ?? 'NOT_SET', 'post_type' => $post_type, 'post_id' => $post_id, 'is_singular' => is_singular($post_type)], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'F']);
    $log_file = ABSPATH . '.cursor/debug.log';
    @file_put_contents($log_file, $log_data . "\n", FILE_APPEND);
    // #endregion

    // Для архивов сразу возвращаем глобальную настройку
    if (!is_singular($post_type)) {
        $position = Redux::get_option($opt_name, 'sidebar_position_archive_' . $post_type);
        // #region agent log
        $log_data = json_encode(['location' => 'sidebars.php:250', 'message' => 'Sidebar position archive', 'data' => ['opt_name' => $opt_name ?? 'NOT_SET', 'position' => $position ?? 'EMPTY', 'option_key' => 'sidebar_position_archive_' . $post_type], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'F']);
        $log_file = ABSPATH . '.cursor/debug.log';
        @file_put_contents($log_file, $log_data . "\n", FILE_APPEND);
        // #endregion
        return $position;
    }

    // Для одиночных записей
    $custom_sidebar_enabled = Redux::get_post_meta($opt_name, $post_id, 'custom-page-sidebar-type') === '2';

    // Если кастомный сайдбар включен, получаем его позицию
    if ($custom_sidebar_enabled) {
        $custom_position = Redux::get_post_meta($opt_name, $post_id, 'custom-page-sidebar-position');
        if (!empty($custom_position)) {
            return $custom_position;
        }
    }

    // Возвращаем глобальную настройку по умолчанию
    return Redux::get_option($opt_name, 'sidebar_position_single_' . $post_type);
}
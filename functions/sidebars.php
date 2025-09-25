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
});



/**
 * Получает позицию сайдбара для текущей страницы/записи
 * 
 * @param string $opt_name Имя опции Redux
 * @return string Позиция сайдбара (left|right|none)
 */
function get_sidebar_position($opt_name)
{
    $post_type = get_post_type();
    $post_id = get_the_ID();

    // Для архивов сразу возвращаем глобальную настройку
    if (!is_singular($post_type)) {
        return Redux::get_option($opt_name, 'sidebar_position_archive_' . $post_type);
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
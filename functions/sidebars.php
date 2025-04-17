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
function theme_register_header_right_widget()
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
    
}
add_action('widgets_init', 'theme_register_header_right_widget');

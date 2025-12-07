<?php

/**
 * Blog Last Posts - Slider
 * 
 * Обновлен для использования новой системы шаблонов
 */

// Загружаем функцию рендеринга карточек
if (!function_exists('cw_render_post_card')) {
    $post_card_templates_path = get_template_directory() . '/functions/post-card-templates.php';
    if (file_exists($post_card_templates_path)) {
        require_once $post_card_templates_path;
    }
}

// Запрос постов
$blog_query = new WP_Query(array(
    'post_type' => 'post',
    'posts_per_page' => 6,
    'post_status' => 'publish',
    'post__not_in' => array(get_the_ID()), // Исключаем текущий пост
));

if (!$blog_query->have_posts()) {
    return;
}

// Настройки отображения
$display_settings = [
    'show_title' => true,
    'show_date' => true,
    'show_category' => true,
    'show_comments' => true,
    'title_length' => 0, // Без ограничения длины
    'excerpt_length' => 0, // Без excerpt
    'title_tag' => 'h2',
    'title_class' => '',
];

// Настройки шаблона
$template_args = [
    'image_size' => 'codeweber_single',
    'hover_classes' => 'overlay overlay-1 hover-scale',
    'border_radius' => 'rounded',
    'show_figcaption' => true,
    'enable_hover_scale' => true,
];
?>

<h3 class="mb-6"><?php esc_html_e('You Might Also Like', 'codeweber'); ?></h3>
<div class="swiper-container blog grid-view mb-16" data-margin="30" data-nav="false" data-dots="true" data-items-md="2" data-items-xs="1">
    <div class="swiper">
        <div class="swiper-wrapper">
            <?php while ($blog_query->have_posts()) : $blog_query->the_post(); ?>
                <div class="swiper-slide">
                    <?php echo cw_render_post_card(get_post(), 'default', $display_settings, $template_args); ?>
                </div>
                <!--/.swiper-slide -->
            <?php endwhile; ?>
            <?php wp_reset_postdata(); ?>
        </div>
        <!--/.swiper-wrapper -->
    </div>
    <!-- /.swiper -->
</div>
<!-- /.swiper-container -->

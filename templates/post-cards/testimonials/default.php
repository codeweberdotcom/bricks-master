<?php
/**
 * Template: Default Testimonial Card
 * 
 * Базовая карточка отзыва с рейтингом, текстом, аватаром и автором
 * 
 * @param array $post_data Данные отзыва (из cw_get_post_card_data)
 * @param array $display_settings Настройки отображения
 * @param array $template_args Дополнительные аргументы
 */

if (!isset($post_data) || !$post_data) {
    return;
}

$display = cw_get_post_card_display_settings($display_settings ?? []);
$template_args = wp_parse_args($template_args ?? [], [
    'show_rating' => true,
    'show_company' => false,
]);
?>

<div class="card h-100">
    <div class="card-body">
        <?php if ($template_args['show_rating'] && !empty($post_data['rating']) && $post_data['rating'] > 0) : ?>
            <span class="ratings <?php echo esc_attr($post_data['rating_class']); ?> mb-3"></span>
        <?php endif; ?>
        
        <?php if (!empty($post_data['text'])) : ?>
            <blockquote class="icon mb-0">
                <p><?php echo $post_data['text']; ?></p>
                
                <?php codeweber_testimonial_blockquote_details($post_data, [
                    'show_company' => $template_args['show_company'] && !empty($post_data['company']),
                    'echo' => true,
                ]); ?>
            </blockquote>
        <?php endif; ?>
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card -->


<?php
/**
 * Template: Testimonial Blockquote
 * 
 * Блок отзыва с цитатой и иконкой в стиле Sandbox
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
    'shadow' => true, // Добавить тень к карточке
]);
?>

<div class="card<?php echo $template_args['shadow'] ? ' shadow-lg' : ''; ?>">
    <div class="card-body">
        <?php if ($template_args['show_rating'] && !empty($post_data['rating']) && $post_data['rating'] > 0) : ?>
            <span class="ratings <?php echo esc_attr($post_data['rating_class']); ?> mb-3"></span>
        <?php endif; ?>
        
        <blockquote class="icon mb-0">
            <?php if (!empty($post_data['text'])) : ?>
                <p><?php echo $post_data['text']; ?></p>
            <?php endif; ?>
            
            <?php codeweber_testimonial_blockquote_details($post_data, [
                'show_company' => $template_args['show_company'] && !empty($post_data['company']),
                'echo' => true,
            ]); ?>
        </blockquote>
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card -->


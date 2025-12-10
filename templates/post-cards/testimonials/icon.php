<?php
/**
 * Template: Testimonial Icon
 * 
 * Блок отзыва с иконкой, без рейтинга
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
    'show_rating' => false,
    'show_company' => false,
]);
?>

<div class="item-inner">
    <div class="card">
        <div class="card-body">
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
</div>
<!-- /.item-inner -->


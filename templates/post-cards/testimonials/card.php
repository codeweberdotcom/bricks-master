<?php
/**
 * Template: Testimonial Card (Sandbox Style)
 * 
 * Карточка отзыва в стиле Sandbox с цветными фонами
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
    'bg_color' => '', // bg-pale-yellow, bg-pale-red, bg-pale-leaf, bg-pale-blue
]);

// Если цвет не указан, выбираем случайный на основе ID
if (empty($template_args['bg_color'])) {
    $bg_colors = ['bg-pale-yellow', 'bg-pale-red', 'bg-pale-leaf', 'bg-pale-blue'];
    $color_index = absint($post_data['id']) % count($bg_colors);
    $template_args['bg_color'] = $bg_colors[$color_index];
}
?>

<div class="card <?php echo esc_attr($template_args['bg_color']); ?>">
    <div class="card-body">
        <blockquote class="icon mb-0">
            <?php if (!empty($post_data['text'])) : ?>
                <p><?php echo $post_data['text']; ?></p>
            <?php endif; ?>
            
            <?php codeweber_testimonial_blockquote_details($post_data, [
                'show_company' => $template_args['show_company'] && !empty($post_data['company']),
                'show_avatar' => false, // Для этого шаблона не показываем аватар/инициалы, только имя и должность
                'info_class' => 'p-0', // Используем p-0 для шаблона с цветными фонами
                'echo' => true,
            ]); ?>
        </blockquote>
    </div>
    <!--/.card-body -->
</div>
<!--/.card -->


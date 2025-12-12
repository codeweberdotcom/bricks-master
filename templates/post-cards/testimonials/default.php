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
    'enable_link' => true, // Обернуть в ссылку (для гутенберг блоков), false для архивов
    'enable_lift' => false, // Включить/выключить lift эффект
]);

// Явно проверяем enable_lift (wp_parse_args может не сохранить boolean true)
$enable_lift = isset($template_args['enable_lift']) && $template_args['enable_lift'] === true;

$card_html = '<div class="card h-100">
    <div class="card-body">';

if ($template_args['show_rating'] && !empty($post_data['rating']) && $post_data['rating'] > 0) {
    $card_html .= '<span class="ratings ' . esc_attr($post_data['rating_class']) . ' mb-3"></span>';
}

if (!empty($post_data['text'])) {
    $card_html .= '<blockquote class="icon mb-0">
        <p>' . wp_kses_post($post_data['text']) . '</p>';
    
    ob_start();
    codeweber_testimonial_blockquote_details($post_data, [
        'show_company' => $template_args['show_company'] && !empty($post_data['company']),
        'echo' => true,
    ]);
    $card_html .= ob_get_clean();
    
    $card_html .= '</blockquote>';
}

$card_html .= '</div>
    <!-- /.card-body -->
</div>
<!-- /.card -->';

// Обертываем в ссылку, если нужно
if ($template_args['enable_link']) {
    $testimonial_url = home_url('/testimonials/#' . absint($post_data['id']));
    // Формируем классы для ссылки
    $link_classes = 'text-decoration-none link-body h-100 d-block';
    if ($enable_lift) {
        $link_classes .= ' lift';
    }
    echo '<a href="' . esc_url($testimonial_url) . '" class="' . esc_attr($link_classes) . '">' . $card_html . '</a>';
} else {
    echo $card_html;
}


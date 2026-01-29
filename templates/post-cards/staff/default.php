<?php
/**
 * Template: Default Staff Card
 * 
 * Базовая карточка сотрудника с изображением, именем и должностью
 * Основано на: https://sandbox.elemisthemes.com/docs/blocks/team.html
 * 
 * @param array $post_data Данные сотрудника (из cw_get_post_card_data)
 * @param array $display_settings Настройки отображения
 * @param array $template_args Дополнительные аргументы
 */

if (!isset($post_data) || !$post_data) {
    return;
}

$display = cw_get_post_card_display_settings($display_settings ?? []);
$template_args = wp_parse_args($template_args ?? [], [
    'show_description' => false, // Показывать описание (excerpt)
    'enable_link' => true, // Обернуть в ссылку (для гутенберг блоков), false для архивов
    'enable_lift' => false, // Включить/выключить lift эффект
    'image_size' => 'codeweber_staff',
]);

// Для staff всегда используем ссылку и lift эффект
// Явно проверяем enable_link и enable_lift (wp_parse_args может не сохранить boolean значения)
$enable_link = true; // Всегда оборачиваем в ссылку для staff
$enable_lift = true; // Всегда добавляем lift класс для staff

// Получаем изображение с правильным размером
$image_url = $post_data['image_url'];
$image_url_2x = '';
if (!empty($post_data['image_url_2x'])) {
    $image_url_2x = $post_data['image_url_2x'];
} elseif (!empty($post_data['image_url'])) {
    // Пытаемся получить @2x версию
    $thumbnail_id = get_post_thumbnail_id($post_data['id']);
    if ($thumbnail_id) {
        $image_2x = wp_get_attachment_image_src($thumbnail_id, $template_args['image_size']);
        if ($image_2x && $image_2x[0] !== $image_url) {
            $image_url_2x = esc_url($image_2x[0]);
        }
    }
}

// Получаем радиус скругления из настроек темы
$card_radius = function_exists('getThemeCardImageRadius') ? getThemeCardImageRadius() : '';

// Формируем классы для карточки
$card_classes = 'card h-100';
if ($enable_lift) {
    $card_classes .= ' lift';
}
if ($card_radius) {
    $card_classes .= ' ' . esc_attr($card_radius);
}

$card_html = '<div class="' . esc_attr($card_classes) . '">
    <figure class="card-img-top' . ($card_radius ? ' ' . esc_attr($card_radius) : '') . '">';

if (!empty($image_url)) {
    $image_srcset = '';
    if (!empty($image_url_2x)) {
        $image_srcset = ' srcset="' . esc_url($image_url_2x) . ' 2x"';
    }
    $img_classes = 'img-fluid';
    if ($card_radius) {
        $img_classes .= ' ' . esc_attr($card_radius);
    }
    $card_html .= '<img class="' . esc_attr($img_classes) . '" src="' . esc_url($image_url) . '"' . $image_srcset . ' alt="' . esc_attr($post_data['image_alt']) . '" />';
}

$card_html .= '</figure>
    <div class="card-body px-6 py-5">';

// Тег и класс заголовка (имя) из настроек блока
$staff_title_tag = isset($display['title_tag']) ? sanitize_html_class($display['title_tag']) : 'h4';
$staff_title_class = !empty($display['title_class']) ? esc_attr($display['title_class']) : 'mb-1';
// Имя
if (!empty($post_data['title'])) {
    $card_html .= '<' . $staff_title_tag . ' class="' . $staff_title_class . '">' . esc_html($post_data['title']) . '</' . $staff_title_tag . '>';
}

// Должность
if (!empty($post_data['position'])) {
    $card_html .= '<p class="mb-0">' . esc_html($post_data['position']) . '</p>';
}

// Описание (excerpt)
if ($template_args['show_description'] && !empty($post_data['excerpt'])) {
    $card_html .= '<p class="mb-0 mt-3">' . wp_kses_post($post_data['excerpt']) . '</p>';
}

$card_html .= '</div>
    <!--/.card-body -->
</div>
<!-- /.card -->';

// Обертываем в ссылку, если нужно
if ($enable_link) {
    $staff_url = get_permalink($post_data['id']);
    $link_classes = 'text-decoration-none link-body h-100 d-block';
    echo '<a href="' . esc_url($staff_url) . '" class="' . esc_attr($link_classes) . '">' . $card_html . '</a>';
} else {
    echo $card_html;
}


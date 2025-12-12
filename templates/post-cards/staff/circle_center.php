<?php
/**
 * Template: Staff Circle Card (Circular Avatar with Social Links)
 * 
 * Карточка сотрудника с круглым аватаром и социальными ссылками
 * Основано на: https://sandbox.elemisthemes.com/docs/blocks/team.html - "Meet the Team"
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
    'show_description' => true, // Показывать описание (excerpt) по умолчанию
    'show_social' => false, // Показывать социальные ссылки
    'enable_link' => true, // Обернуть в ссылку (для гутенберг блоков), false для архивов
    'enable_lift' => false, // Включить/выключить lift эффект
    'image_size' => 'codeweber_staff',
    'avatar_size' => 'w-20', // w-15 по умолчанию (как в примере)
]);

// Явно проверяем enable_link и enable_lift (wp_parse_args может не сохранить boolean значения)
$enable_link = (bool) $template_args['enable_link'];
$enable_lift = (bool) $template_args['enable_lift'];

// Для шаблона circle_center по умолчанию используем w-20, но не переопределяем явно переданный размер
// Переопределяем avatar_size только если он не указан (пустой)
if (empty($template_args['avatar_size'])) {
    $template_args['avatar_size'] = 'w-20';
}

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

// Формируем классы для карточки (lift на карточке)
$card_classes = 'card h-100 lift';
if ($card_radius) {
    $card_classes .= ' ' . esc_attr($card_radius);
}

// Начинаем вывод карточки
$staff_url = get_permalink($post_data['id']);
$link_classes = 'text-decoration-none link-body h-100 d-block';

// Обертываем карточку в ссылку
if ($enable_link) {
    echo '<a href="' . esc_url($staff_url) . '" class="' . esc_attr($link_classes) . '">';
}

echo '<div class="' . esc_attr($card_classes) . ' text-center">';
echo '<div class="card-body">';

// Круглый аватар
if (!empty($image_url)) {
    $image_srcset = '';
    if (!empty($image_url_2x)) {
        $image_srcset = ' srcset="' . esc_url($image_url_2x) . ' 2x"';
    }
    echo '<img class="rounded-circle mx-auto ' . esc_attr($template_args['avatar_size']) . ' mb-4" src="' . esc_url($image_url) . '"' . $image_srcset . ' alt="' . esc_attr($post_data['image_alt']) . '" />';
}

// Имя (без ссылки, так как вся карточка в ссылке)
if (!empty($post_data['title'])) {
    echo '<h3 class="h4 mb-1">' . esc_html($post_data['title']) . '</h3>';
}

// Должность
if (!empty($post_data['position'])) {
    echo '<div class="meta mb-1">' . esc_html($post_data['position']) . '</div>';
}

// Компания (под должностью) - в формате badge
if (!empty($post_data['company'])) {
    $badge_class = 'badge badge-lg bg-gray-300 mb-0 rounded-pill';
    if (function_exists('getThemeButton')) {
        $badge_class .= getThemeButton();
    } else {
        $badge_class .= ' rounded-pill';
    }
    echo '<div class="' . esc_attr($badge_class) . '">' . esc_html($post_data['company']) . '</div>';
}

// Описание (excerpt)
if ($template_args['show_description'] && !empty($post_data['excerpt'])) {
    echo '<p class="mb-2">' . wp_kses_post($post_data['excerpt']) . '</p>';
}

// Закрываем card-body и card
echo '</div>';
echo '<!--/.card-body -->';
echo '</div>';
echo '<!-- /.card -->';

// Закрываем ссылку (если была открыта)
if ($enable_link) {
    echo '</a>';
}


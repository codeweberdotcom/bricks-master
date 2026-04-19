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
    'show_social' => true, // Показывать социальные ссылки (по умолчанию для circle_center_alt)
    'enable_link' => true, // Обернуть в ссылку (для гутенберг блоков), false для архивов
    'enable_lift' => false, // Включить/выключить lift эффект
    'image_size' => 'cw_square_md',
    'avatar_size' => 'w-20', // w-20 по умолчанию для circle_center_alt
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

// Получаем URL сотрудника для ссылки на изображении
$staff_url = get_permalink($post_data['id']);

echo '<div class="text-center">';

// Круглый аватар с ссылкой и классом lift
if (!empty($image_url)) {
    $image_srcset = '';
    if (!empty($image_url_2x)) {
        $image_srcset = ' srcset="' . esc_url($image_url_2x) . ' 2x"';
    }
    
    // Обертываем изображение в ссылку, если enable_link включен
    if ($enable_link) {
        echo '<a href="' . esc_url($staff_url) . '" class="d-inline-block">';
    }
    
    echo '<img class="rounded-circle mx-auto lift ' . esc_attr($template_args['avatar_size']) . ' mb-4" src="' . esc_url($image_url) . '"' . $image_srcset . ' alt="' . esc_attr($post_data['image_alt']) . '" />';
    
    if ($enable_link) {
        echo '</a>';
    }
}

$staff_title_tag  = isset($display['title_tag']) ? sanitize_html_class($display['title_tag']) : 'h3';
$staff_title_class = !empty($display['title_class']) ? esc_attr($display['title_class']) : 'h4 mb-1';
$link_classes = 'text-decoration-none link-body';
// Заголовок — кликабельный
if (!empty($post_data['title'])) {
    if ($enable_link) {
        echo '<' . $staff_title_tag . ' class="' . $staff_title_class . '">';
        echo '<a href="' . esc_url($staff_url) . '" class="' . esc_attr($link_classes) . '">' . esc_html($post_data['title']) . '</a>';
        echo '</' . $staff_title_tag . '>';
    } else {
        echo '<' . $staff_title_tag . ' class="' . $staff_title_class . '">' . esc_html($post_data['title']) . '</' . $staff_title_tag . '>';
    }
}

// Должность
if (!empty($post_data['position'])) {
    echo '<div class="meta mb-1">' . esc_html($post_data['position']) . '</div>';
}

// Описание (excerpt)
if ($template_args['show_description'] && !empty($post_data['excerpt'])) {
    echo '<p class="mb-2">' . wp_kses_post($post_data['excerpt']) . '</p>';
}

// Социальные иконки (стили из Redux → Глобальный социальный стиль)
$show_social_flag = isset($template_args['show_social']) ? (bool) $template_args['show_social'] : true;

if ($show_social_flag && function_exists('staff_social_links') && function_exists('codeweber_global_social_style')) {
    $social_style = codeweber_global_social_style();
    // Добавляем gap-0, чтобы убрать стандартный gap-2 из nav
    $social_html = staff_social_links($post_data['id'], 'justify-content-center text-center mb-0 gap-0', $social_style['type'], $social_style['size'], 'primary', 'solid', $social_style['button_form']);
    if (!empty($social_html)) {
        echo '<div class="mt-3 mb-5">' . $social_html . '</div>';
    }
}

// Закрываем div
echo '</div>';


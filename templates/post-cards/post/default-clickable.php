<?php
/**
 * Template: Default Clickable Post Card
 * 
 * Вся карточка кликабельна, без overlay на изображении
 * 
 * @param array $post_data Данные поста (из cw_get_post_card_data)
 * @param array $display_settings Настройки отображения (из cw_get_post_card_display_settings)
 * @param array $template_args Дополнительные аргументы (hover classes, border radius, enable_lift и т.д.)
 */

if (!isset($post_data) || !$post_data) {
    return;
}

$display = cw_get_post_card_display_settings($display_settings ?? []);
$template_args = wp_parse_args($template_args ?? [], [
    'hover_classes' => '', // БЕЗ overlay
    'border_radius' => getThemeCardImageRadius() ?: 'rounded',
    'show_figcaption' => false, // БЕЗ figcaption
    'enable_lift' => false, // Включить/выключить lift эффект
]);

// Ограничение заголовка
$title = $post_data['title'];
if ($display['title_length'] > 0 && mb_strlen($title) > $display['title_length']) {
    $title = mb_substr($title, 0, $display['title_length']) . '...';
}

// Формируем тег и классы для заголовка
$title_tag = isset($display['title_tag']) ? sanitize_html_class($display['title_tag']) : 'h2';
if (!empty($display['title_class'])) {
    $title_class = esc_attr($display['title_class']);
} else {
    $title_class = 'post-title h3 mt-1 mb-3';
}

// Классы для карточки
$card_classes = 'card-link d-block text-decoration-none d-flex flex-column h-100';
if ($template_args['enable_lift']) {
    $card_classes .= ' lift';
}
?>

<article class="h-100">
    <a href="<?php echo esc_url($post_data['link']); ?>" class="<?php echo esc_attr($card_classes); ?>">
        <?php if ($post_data['image_url']) : ?>
            <figure class="<?php echo esc_attr($template_args['border_radius']); ?> mb-5">
                <img src="<?php echo esc_url($post_data['image_url']); ?>" alt="<?php echo esc_attr($post_data['image_alt']); ?>" class="<?php echo esc_attr($template_args['border_radius']); ?>" />
            </figure>
        <?php endif; ?>
        
        <div class="post-header p-4">
            <?php if ($display['show_category'] && $post_data['category']) : ?>
                <div class="post-category text-line">
                    <span class="hover" rel="category">
                        <?php echo esc_html($post_data['category']->name); ?>
                    </span>
                </div>
            <?php endif; ?>
            
            <?php if ($display['show_title']) : ?>
                <<?php echo esc_attr($title_tag); ?> class="<?php echo esc_attr(trim($title_class)); ?>">
                    <span class="link-dark">
                        <?php echo esc_html($title); ?>
                    </span>
                </<?php echo esc_attr($title_tag); ?>>
            <?php endif; ?>
        </div>
        
        <?php if ($display['show_date'] || $display['show_comments']) : ?>
            <div class="post-footer p-4 mt-auto">
                <ul class="post-meta">
                    <?php if ($display['show_date']) : ?>
                        <li class="post-date">
                            <i class="uil uil-calendar-alt"></i>
                            <span><?php echo esc_html($post_data['date']); ?></span>
                        </li>
                    <?php endif; ?>
                    
                    <?php if ($display['show_comments']) : ?>
                        <li class="post-comments">
                            <span>
                                <i class="uil uil-comment"></i>
                                <?php echo esc_html($post_data['comments_count']); ?>
                            </span>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>
    </a>
</article>


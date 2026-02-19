<?php
/**
 * Template: Overlay-5 Post Card
 * 
 * Шаблон с overlay-5 эффектом и bottom-overlay для даты
 * 
 * @param array $post_data Данные поста
 * @param array $display_settings Настройки отображения
 * @param array $template_args Дополнительные аргументы
 */

if (!isset($post_data) || !$post_data) {
    return;
}

$display = cw_get_post_card_display_settings($display_settings ?? []);
$template_args = wp_parse_args($template_args ?? [], [
    'hover_classes' => 'overlay overlay-5',
    'border_radius' => getThemeCardImageRadius() ?: 'rounded',
    'show_figcaption' => true,
]);

$title = $post_data['title'];
if ($display['title_length'] > 0 && mb_strlen($title) > $display['title_length']) {
    $title = mb_substr($title, 0, $display['title_length']) . '...';
}

$excerpt = '';
if ($display['excerpt_length'] > 0) {
    $excerpt = wp_trim_words($post_data['excerpt'], $display['excerpt_length'], '...');
    // Ограничиваем до 116 символов (как в примере)
    if (mb_strlen($excerpt) > 116) {
        $excerpt = mb_substr($excerpt, 0, 113) . '...';
    }
}

// Формируем тег и классы для заголовка
$title_tag = isset($display['title_tag']) ? sanitize_html_class($display['title_tag']) : 'h2';
if (!empty($display['title_class'])) {
    $title_class = esc_attr($display['title_class']);
} else {
    $title_class = 'h5 mb-0';
}

// Форматируем дату для badge
$date_badge = get_the_date('d M Y', $post_data['id']);
?>

<article>
    <?php if ($post_data['image_url']) : ?>
        <figure class="<?php echo esc_attr($template_args['hover_classes'] . ' ' . $template_args['border_radius']); ?>">
            <a href="<?php echo esc_url($post_data['link']); ?>">
                <div class="bottom-overlay post-meta fs-16 justify-content-between position-absolute zindex-1 d-flex flex-column h-100 w-100 p-5">
                    <?php if ($display['show_date']) : ?>
                        <div class="d-flex w-100 justify-content-end">
                            <span class="post-date badge bg-primary rounded-pill"><?php echo esc_html($date_badge); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($display['show_title']) : ?>
                        <<?php echo esc_attr($title_tag); ?> class="<?php echo esc_attr(trim($title_class)); ?>">
                            <?php echo esc_html($title); ?>
                        </<?php echo esc_attr($title_tag); ?>>
                    <?php endif; ?>
                </div>
                <img src="<?php echo esc_url($post_data['image_url']); ?>" alt="<?php echo esc_attr($post_data['image_alt']); ?>" class="<?php echo esc_attr($template_args['border_radius']); ?>">
            </a>
            
            <?php if ($template_args['show_figcaption']) : ?>
                <figcaption class="p-5">
                    <div class="post-body h-100 d-flex flex-column from-left justify-content-end">
                        <?php if ($excerpt) : ?>
                            <p class="mb-3"><?php echo esc_html($excerpt); ?></p>
                        <?php endif; ?>
                        <div class="d-block">
                            <a href="<?php echo esc_url($post_data['link']); ?>" class="hover-8 link-body label-s text-charcoal-blue me-4 post-read-more">
                                <?php esc_html_e('Read more', 'codeweber'); ?>
                            </a>
                        </div>
                    </div>
                </figcaption>
            <?php endif; ?>
        </figure>
    <?php endif; ?>
</article>

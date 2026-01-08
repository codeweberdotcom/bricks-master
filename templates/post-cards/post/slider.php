<?php
/**
 * Template: Slider Post Card
 * 
 * НОВЫЙ шаблон - для слайдера
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
    'hover_classes' => 'overlay overlay-1 hover-scale',
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
    // Ограничиваем до 116 символов (как в примере Sandbox)
    if (mb_strlen($excerpt) > 116) {
        $excerpt = mb_substr($excerpt, 0, 116) . '...';
    }
}

// Формируем тег и классы для заголовка
$title_tag = isset($display['title_tag']) ? sanitize_html_class($display['title_tag']) : 'h3';
$title_class = 'post-title';
if (in_array($title_tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'])) {
    $title_class .= ' h4'; // Сохраняем стиль h4 для совместимости
}
if (!empty($display['title_class'])) {
    $title_class .= ' ' . esc_attr($display['title_class']);
}
?>

<article>
    <div class="post-col">
        <?php if ($post_data['image_url']) : ?>
            <figure class="post-figure <?php echo esc_attr($template_args['hover_classes'] . ' ' . $template_args['border_radius']); ?> mb-5">
                <a href="<?php echo esc_url($post_data['link']); ?>">
                    <img src="<?php echo esc_url($post_data['image_url']); ?>" alt="<?php echo esc_attr($post_data['image_alt']); ?>" class="post-image <?php echo esc_attr($template_args['border_radius']); ?>" />
                    
                    <?php if ($display['show_category'] && $post_data['category']) : ?>
                        <div class="caption-wrapper p-7">
                            <div class="caption bg-matte-color mt-auto label-u text-neutral-50 px-4 py-2">
                                <?php echo esc_html($post_data['category']->name); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <span class="bg"></span>
                </a>
                <?php if ($template_args['show_figcaption']) : ?>
                    <figcaption>
                        <div class="from-top mb-0 label-u"><?php esc_html_e('Read', 'codeweber'); ?></div>
                    </figcaption>
                <?php endif; ?>
            </figure>
        <?php endif; ?>
        
        <div class="post-body mt-4">
            <?php if ($display['show_date'] || $display['show_comments']) : ?>
                <div class="post-meta d-flex mb-3 fs-16 justify-content-between">
                    <?php if ($display['show_date']) : ?>
                        <span class="post-date"><?php echo esc_html($post_data['date']); ?></span>
                    <?php endif; ?>
                    
                    <?php if ($display['show_comments']) : ?>
                        <a href="<?php echo esc_url($post_data['link'] . '#comments'); ?>" class="post-comments">
                            <i class="uil uil-comment"></i>
                            <?php echo esc_html($post_data['comments_count']); ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($display['show_title']) : ?>
                <<?php echo esc_attr($title_tag); ?> class="<?php echo esc_attr(trim($title_class)); ?>" title="<?php echo esc_attr($post_data['title']); ?>">
                    <?php echo esc_html($title); ?>
                </<?php echo esc_attr($title_tag); ?>>
            <?php endif; ?>
            
            <?php if ($excerpt) : ?>
                <div class="body-l-l mb-4 post-excerpt">
                    <?php echo esc_html($excerpt); ?>
                </div>
            <?php endif; ?>
            
            <a href="<?php echo esc_url($post_data['link']); ?>" class="hover-8 link-body label-s text-charcoal-blue me-4 post-read-more">
                <?php esc_html_e('Read more', 'codeweber'); ?>
            </a>
        </div>
    </div>
</article>


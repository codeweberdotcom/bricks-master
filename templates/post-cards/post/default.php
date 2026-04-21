<?php
/**
 * Template: Default Post Card
 * 
 * НОВЫЙ шаблон - не конфликтует с существующими
 * 
 * @param array $post_data Данные поста (из cw_get_post_card_data)
 * @param array $display_settings Настройки отображения (из cw_get_post_card_display_settings)
 * @param array $template_args Дополнительные аргументы (hover classes, border radius и т.д.)
 */

if (!isset($post_data) || !$post_data) {
    return;
}

$display = cw_get_post_card_display_settings($display_settings ?? []);
$template_args = wp_parse_args($template_args ?? [], [
    'hover_classes' => 'overlay overlay-1',
    'border_radius' => Codeweber_Options::style('card-radius') ?: 'rounded',
    'show_figcaption' => true,
    'enable_hover_scale' => false, // Включить hover-scale эффект
    'enable_lift' => false,
]);

// Добавляем hover-scale класс если включен
if ($template_args['enable_hover_scale']) {
    $template_args['hover_classes'] .= ' hover-scale';
}

$article_class = !empty($template_args['enable_lift']) ? 'lift' : '';

// Excerpt (опционально)
$excerpt = '';
if (!empty($display['show_excerpt']) && !empty($display['excerpt_length'])) {
    $excerpt = wp_trim_words($post_data['excerpt'], (int) $display['excerpt_length'], '...');
}

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
    $title_class = 'post-title';
    if (in_array($title_tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'])) {
        $title_class .= ' ' . $title_tag;
    } else {
        $title_class .= ' h3';
    }
    $title_class .= ' mt-1 mb-3';
}
?>

<article<?php echo $article_class ? ' class="' . esc_attr($article_class) . '"' : ''; ?>>
    <?php if ($post_data['image_url']) : ?>
        <figure class="<?php echo esc_attr($template_args['hover_classes'] . ' ' . $template_args['border_radius'] . ' mb-5'); ?>">
            <a href="<?php echo esc_url($post_data['link']); ?>">
                <img src="<?php echo esc_url($post_data['image_url']); ?>" alt="<?php echo esc_attr($post_data['image_alt']); ?>" class="<?php echo esc_attr($template_args['border_radius']); ?>" />
            </a>
            <?php if ($template_args['show_figcaption']) : ?>
                <figcaption>
                    <h5 class="from-top mb-0"><?php esc_html_e('Read More', 'codeweber'); ?></h5>
                </figcaption>
            <?php endif; ?>
        </figure>
    <?php endif; ?>
    
    <div class="post-header">
        <?php if ($display['show_category'] && $post_data['category']) : ?>
            <div class="post-category text-line">
                <a href="<?php echo esc_url($post_data['category_link']); ?>" rel="category">
                    <?php echo esc_html($post_data['category']->name); ?>
                </a>
            </div>
        <?php endif; ?>
        
        <?php if ($display['show_title']) : ?>
            <<?php echo esc_attr($title_tag); ?> class="<?php echo esc_attr(trim($title_class)); ?>">
                <a class="link-dark" href="<?php echo esc_url($post_data['link']); ?>">
                    <?php echo esc_html($title); ?>
                </a>
            </<?php echo esc_attr($title_tag); ?>>
        <?php endif; ?>
    </div>

    <?php if ($excerpt) : ?>
        <div class="post-content mb-3">
            <p class="mb-0"><?php echo esc_html($excerpt); ?></p>
        </div>
    <?php endif; ?>

    <?php
    $footer_has_date = !empty($display['show_date']);
    $footer_has_comments = !empty($display['show_comments']) && comments_open($post_data['id']);
    ?>
    <?php if ($footer_has_date || $footer_has_comments) : ?>
        <div class="post-footer">
            <ul class="post-meta">
                <?php if ($footer_has_date) : ?>
                    <li class="post-date">
                        <i class="uil uil-calendar-alt"></i>
                        <span><?php echo esc_html($post_data['date']); ?></span>
                    </li>
                <?php endif; ?>

                <?php if ($footer_has_comments) : ?>
                    <li class="post-comments">
                        <a href="<?php echo esc_url($post_data['link'] . '#comments'); ?>">
                            <i class="uil uil-comment"></i>
                            <?php echo esc_html($post_data['comments_count']); ?>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    <?php endif; ?>
</article>


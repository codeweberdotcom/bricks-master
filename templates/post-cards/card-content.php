<?php
/**
 * Template: Card Content Post Card
 * 
 * НОВЫЙ шаблон - карточка с описанием и footer (из Sandbox)
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
    'hover_classes' => 'overlay overlay-1 hover-scale',
    'border_radius' => '',
    'show_figcaption' => true,
]);

// Ограничение заголовка
$title = $post_data['title'];
if ($display['title_length'] > 0 && mb_strlen($title) > $display['title_length']) {
    $title = mb_substr($title, 0, $display['title_length']) . '...';
}

// Формируем excerpt
$excerpt = '';
if ($display['excerpt_length'] > 0) {
    $excerpt = wp_trim_words($post_data['excerpt'], $display['excerpt_length'], '...');
    // Ограничиваем до 116 символов (как в примере Sandbox)
    if (mb_strlen($excerpt) > 116) {
        $excerpt = mb_substr($excerpt, 0, 116) . '...';
    }
}

// Формируем тег и классы для заголовка
$title_tag = isset($display['title_tag']) ? sanitize_html_class($display['title_tag']) : 'h2';
$title_class = 'post-title';
if (in_array($title_tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'])) {
    $title_class .= ' h3'; // Всегда h3 для этого шаблона
}
$title_class .= ' mt-1 mb-3';
if (!empty($display['title_class'])) {
    $title_class .= ' ' . esc_attr($display['title_class']);
}

// Классы для figure
$figure_classes = 'card-img-top ' . $template_args['hover_classes'];
if (!empty($template_args['border_radius'])) {
    $figure_classes .= ' ' . $template_args['border_radius'];
}
?>

<article class="h-100 mb-6">
    <div class="card d-flex flex-column h-100">
        <?php if ($post_data['image_url']) : ?>
            <figure class="<?php echo esc_attr($figure_classes); ?>">
                <a href="<?php echo esc_url($post_data['link']); ?>">
                    <img src="<?php echo esc_url($post_data['image_url']); ?>" alt="<?php echo esc_attr($post_data['image_alt']); ?>" />
                    <span class="bg"></span>
                </a>
                <?php if ($template_args['show_figcaption']) : ?>
                    <figcaption>
                        <h5 class="from-top mb-0"><?php esc_html_e('Read More', 'codeweber'); ?></h5>
                    </figcaption>
                <?php endif; ?>
            </figure>
        <?php endif; ?>
        
        <div class="card-body">
            <div class="post-header">
                <?php if ($display['show_category'] && $post_data['category']) : ?>
                    <div class="post-category text-line">
                        <a href="<?php echo esc_url($post_data['category_link']); ?>" class="hover" rel="category">
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
                <div class="post-content">
                    <p class="mb-0"><?php echo esc_html($excerpt); ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($display['show_date'] || $display['show_comments']) : ?>
            <div class="card-footer mt-auto">
                <ul class="post-meta d-flex mb-0">
                    <?php if ($display['show_date']) : ?>
                        <li class="post-date">
                            <i class="uil uil-calendar-alt"></i>
                            <span><?php echo esc_html($post_data['date']); ?></span>
                        </li>
                    <?php endif; ?>
                    
                    <?php if ($display['show_comments']) : ?>
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
    </div>
</article>


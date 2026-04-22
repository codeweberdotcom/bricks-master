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
    'hover_classes'   => 'overlay overlay-5',
    'border_radius'   => Codeweber_Options::style('card-radius') ?: 'rounded',
    'show_figcaption' => true,
    'enable_lift'     => false,
    'show_card_arrow' => true,
    'card_read_more'  => 'more', // none | view | more | read
]);

$article_class = !empty($template_args['enable_lift']) ? 'lift' : '';

// Build localized read-more label (translatable — no Cyrillic source strings).
$read_more_labels = [
    'view'    => __( 'View',       'codeweber' ),
    'more'    => __( 'Read more',  'codeweber' ),
    'read'    => __( 'Read',       'codeweber' ),
    'go'      => __( 'Go',         'codeweber' ),
    'open'    => __( 'Open',       'codeweber' ),
    'details' => __( 'Details',    'codeweber' ),
    'learn'   => __( 'Learn more', 'codeweber' ),
    'buy'     => __( 'Buy',        'codeweber' ),
    'order'   => __( 'Order',      'codeweber' ),
];
$read_more_label = isset($read_more_labels[$template_args['card_read_more']])
    ? $read_more_labels[$template_args['card_read_more']]
    : '';

$title = $post_data['title'];
if ($display['title_length'] > 0 && mb_strlen($title) > $display['title_length']) {
    $title = mb_substr($title, 0, $display['title_length']) . '...';
}

$excerpt = '';
if (!empty($display['show_excerpt']) && $display['excerpt_length'] > 0) {
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

<article<?php echo $article_class ? ' class="' . esc_attr($article_class) . '"' : ''; ?>>
    <?php if ($post_data['image_url']) : ?>
        <figure class="<?php echo esc_attr($template_args['hover_classes'] . ' ' . $template_args['border_radius']); ?> card-interactive">
            <a href="<?php echo esc_url($post_data['link']); ?>">
                <div class="bottom-overlay post-meta fs-16 position-absolute zindex-1 d-flex flex-column h-100 w-100 p-5">
                    <?php if ($display['show_date']) : ?>
                        <div class="d-flex w-100 justify-content-end">
                            <span class="post-date badge bg-primary rounded-pill"><?php echo esc_html($date_badge); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($display['show_title']) : ?>
                        <div class="mt-auto">
                            <<?php echo esc_attr($title_tag); ?> class="<?php echo esc_attr(trim($title_class)); ?>">
                                <?php echo empty($display['use_html_title']) ? esc_html($title) : wp_kses_post($title); ?>
                            </<?php echo esc_attr($title_tag); ?>>
                        </div>
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
                        <?php if ($read_more_label) : ?>
                            <span class="hover more me-4"><?php echo esc_html($read_more_label); ?></span>
                        <?php endif; ?>
                    </div>
                </figcaption>
            <?php endif; ?>

            <?php if (!empty($template_args['show_card_arrow'])) : ?>
                <div class="hover_card_button_hide position-absolute top-0 end-0 p-5 zindex-10">
                    <i class="fs-25 uil uil-arrow-right lh-1"></i>
                </div>
            <?php endif; ?>
        </figure>
    <?php endif; ?>
</article>

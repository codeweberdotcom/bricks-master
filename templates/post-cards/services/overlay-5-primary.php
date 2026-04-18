<?php
/**
 * Template: Overlay-5 Primary Service Card
 *
 * Адаптация overlay-5-primary под CPT services. Структура идентична
 * post/overlay-5-primary.php — дата/категория не выводятся, только картинка,
 * заголовок и excerpt появляются на hover слева.
 *
 * @param array $post_data
 * @param array $display_settings
 * @param array $template_args
 */

if (!isset($post_data) || !$post_data) {
    return;
}

$display = cw_get_post_card_display_settings($display_settings ?? []);
$template_args = wp_parse_args($template_args ?? [], [
    'border_radius'   => Codeweber_Options::style('card-radius') ?: 'rounded',
    'enable_lift'     => false,
    'show_card_arrow' => true,
    'card_read_more'  => 'none',
]);

$article_class = !empty($template_args['enable_lift']) ? 'lift' : '';

$read_more_labels = [
    'view' => __('View', 'codeweber'),
    'more' => __('Read more', 'codeweber'),
    'read' => __('Read', 'codeweber'),
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
}

$title_tag = isset($display['title_tag']) ? sanitize_html_class($display['title_tag']) : 'h5';
if (!empty($display['title_class'])) {
    $title_class = esc_attr($display['title_class']);
} else {
    $title_class = 'from-left mb-1';
}

$figure_classes = 'overlay overlay-5 hover-scale color card-interactive ' . esc_attr($template_args['border_radius']);
?>

<article<?php echo $article_class ? ' class="' . esc_attr($article_class) . '"' : ''; ?>>
    <?php if ($post_data['image_url']) : ?>
        <figure class="<?php echo esc_attr($figure_classes); ?>">
            <a href="<?php echo esc_url($post_data['link']); ?>">
                <img src="<?php echo esc_url($post_data['image_url']); ?>" alt="<?php echo esc_attr($post_data['image_alt']); ?>" />
            </a>

            <figcaption>
                <?php if ($display['show_title']) : ?>
                    <<?php echo esc_attr($title_tag); ?> class="<?php echo esc_attr(trim($title_class)); ?>">
                        <?php echo esc_html($title); ?>
                    </<?php echo esc_attr($title_tag); ?>>
                <?php endif; ?>

                <?php if ($excerpt) : ?>
                    <p class="from-left mb-0"><?php echo esc_html($excerpt); ?></p>
                <?php endif; ?>

                <?php if ($read_more_label) : ?>
                    <span class="hover more from-left mt-3 d-inline-block"><?php echo esc_html($read_more_label); ?></span>
                <?php endif; ?>
            </figcaption>

            <?php if (!empty($template_args['show_card_arrow'])) : ?>
                <div class="hover_card_button_hide position-absolute top-0 end-0 p-5 zindex-10">
                    <i class="fs-25 uil uil-arrow-right lh-1"></i>
                </div>
            <?php endif; ?>
        </figure>
    <?php endif; ?>
</article>

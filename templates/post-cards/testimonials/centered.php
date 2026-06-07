<?php
/**
 * Template: Testimonial Centered (icon-top, no card)
 *
 * Variant 3: borderless centered quote, icon on top, no card and no avatar.
 *
 * @param array $post_data        Testimonial data (from cw_get_post_card_data)
 * @param array $display_settings Display settings (title_tag, title_class, ...)
 * @param array $template_args    Extra args (show_company, enable_link, ...)
 */

if (!isset($post_data) || !$post_data) {
    return;
}

$ds = (isset($display_settings) && is_array($display_settings)) ? $display_settings : [];

$template_args = wp_parse_args($template_args ?? [], [
    'show_company' => false,
    'enable_link'  => true,
]);

$allowed_tags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div', 'span'];
$name_tag   = (!empty($ds['title_tag']) && in_array($ds['title_tag'], $allowed_tags, true)) ? $ds['title_tag'] : 'h5';
$name_class = (isset($ds['title_class']) && $ds['title_class'] !== '') ? $ds['title_class'] : 'mb-1';

ob_start();
?>
<blockquote class="icon icon-top fs-lg text-center">
    <?php if (!empty($post_data['text'])) : ?>
        <p><?php echo wp_kses_post($post_data['text']); ?></p>
    <?php endif; ?>
    <div class="blockquote-details justify-content-center text-center">
        <div class="info ps-0">
            <?php if (!empty($post_data['author_name'])) : ?>
                <<?php echo $name_tag; ?> class="<?php echo esc_attr($name_class); ?>"><?php echo esc_html($post_data['author_name']); ?></<?php echo $name_tag; ?>>
            <?php endif; ?>
            <?php if (!empty($post_data['author_role'])) : ?>
                <p class="mb-0"><?php echo esc_html($post_data['author_role']); ?></p>
            <?php endif; ?>
            <?php if ($template_args['show_company'] && !empty($post_data['company'])) : ?>
                <p class="mb-0 text-muted"><?php echo esc_html($post_data['company']); ?></p>
            <?php endif; ?>
        </div>
    </div>
</blockquote>
<?php
$card_html = ob_get_clean();

if ($template_args['enable_link']) {
    $url = home_url('/testimonials/#' . absint($post_data['id']));
    echo '<a href="' . esc_url($url) . '" class="text-decoration-none link-body">' . $card_html . '</a>';
} else {
    echo $card_html;
}

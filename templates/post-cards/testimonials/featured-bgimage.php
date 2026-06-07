<?php
/**
 * Template: Testimonial Featured Background Image (card over photo, white text)
 *
 * Card placed over the featured image with a dark overlay, rating and a
 * borderless centered quote in white. Falls back to a dark card when the
 * testimonial has no featured image.
 *
 * @param array $post_data        Testimonial data (from cw_get_post_card_data)
 * @param array $display_settings Display settings (title_tag, title_class, ...)
 * @param array $template_args    Extra args (show_rating, show_company, enable_link, ...)
 */

if (!isset($post_data) || !$post_data) {
    return;
}

$ds = (isset($display_settings) && is_array($display_settings)) ? $display_settings : [];

$template_args = wp_parse_args($template_args ?? [], [
    'show_rating'  => true,
    'show_company' => false,
    'enable_link'  => true,
]);

$show_rating = $template_args['show_rating'] && !empty($post_data['rating']) && $post_data['rating'] > 0;

$allowed_tags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div', 'span'];
$name_tag   = (!empty($ds['title_tag']) && in_array($ds['title_tag'], $allowed_tags, true)) ? $ds['title_tag'] : 'h5';
$name_class = (isset($ds['title_class']) && $ds['title_class'] !== '') ? $ds['title_class'] : 'mb-1 text-white';

// Use the featured image as background; skip the plugin placeholder.
$bg_image = (!empty($post_data['image_url']) && strpos($post_data['image_url'], 'placeholder') === false)
    ? $post_data['image_url']
    : '';

if ($bg_image) {
    $card_classes = 'card image-wrapper bg-full bg-image bg-overlay bg-overlay-400';
    $card_attr    = ' data-image-src="' . esc_url($bg_image) . '"';
} else {
    $card_classes = 'card bg-dark';
    $card_attr    = '';
}

ob_start();
?>
<div class="<?php echo esc_attr($card_classes); ?> text-white text-center"<?php echo $card_attr; ?>>
    <div class="card-body p-9 p-xl-12">
        <?php if ($show_rating) : ?>
            <span class="ratings <?php echo esc_attr($post_data['rating_class']); ?> mb-3"></span>
        <?php endif; ?>
        <blockquote class="border-0 fs-lg mb-2">
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
                        <p class="mb-0"><?php echo esc_html($post_data['company']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </blockquote>
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card -->
<?php
$card_html = ob_get_clean();

if ($template_args['enable_link']) {
    $url = home_url('/testimonials/#' . absint($post_data['id']));
    echo '<a href="' . esc_url($url) . '" class="text-decoration-none d-block">' . $card_html . '</a>';
} else {
    echo $card_html;
}

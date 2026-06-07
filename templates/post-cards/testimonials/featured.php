<?php
/**
 * Template: Testimonial Featured (gray card + side photo)
 *
 * Variant 1: large gray card with rating, centered quote and a photo
 * positioned on the right. Designed for a single, full-width column.
 *
 * @param array $post_data        Testimonial data (from cw_get_post_card_data)
 * @param array $display_settings Display settings (title_tag, title_class, ...)
 * @param array $template_args    Extra args (show_rating, show_company, enable_link, enable_lift, ...)
 */

if (!isset($post_data) || !$post_data) {
    return;
}

$ds = (isset($display_settings) && is_array($display_settings)) ? $display_settings : [];

$template_args = wp_parse_args($template_args ?? [], [
    'show_rating'  => true,
    'show_company' => false,
    'enable_link'  => true,
    'enable_lift'  => false,
]);

$enable_lift = isset($template_args['enable_lift']) && $template_args['enable_lift'] === true;

$allowed_tags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div', 'span'];
$name_tag   = (!empty($ds['title_tag']) && in_array($ds['title_tag'], $allowed_tags, true)) ? $ds['title_tag'] : 'h4';
$name_class = (isset($ds['title_class']) && $ds['title_class'] !== '') ? $ds['title_class'] : 'mb-1';

$show_rating = $template_args['show_rating'] && !empty($post_data['rating']) && $post_data['rating'] > 0;

// Side photo: prefer the featured image, fall back to the avatar. Skip the
// plugin placeholder so an empty testimonial renders as a full-width card.
$photo    = '';
$photo_2x = '';
if (!empty($post_data['image_url']) && strpos($post_data['image_url'], 'placeholder') === false) {
    $photo = $post_data['image_url'];
} elseif (!empty($post_data['avatar_url_full'])) {
    // Крупный аватар в выбранном размере изображения (не 150×150 thumbnail)
    $photo = $post_data['avatar_url_full'];
} elseif (!empty($post_data['avatar_url'])) {
    $photo    = $post_data['avatar_url'];
    $photo_2x = $post_data['avatar_url_2x'] ?? '';
}

$col_class    = $photo ? 'col-lg-9' : 'col-lg-12';
$card_classes = 'card bg-gray';
if ($enable_lift) {
    $card_classes .= ' lift';
}

$card  = '<div class="card-body p-md-10 py-xxl-16">';
$card .= '<div class="row gx-0"><div class="col-lg-8 ps-xl-10">';
if ($show_rating) {
    $card .= '<span class="ratings ' . esc_attr($post_data['rating_class']) . ' fs-20 mb-3"></span>';
}
$card .= '<blockquote class="border-0 fs-lg mb-0">';
if (!empty($post_data['text'])) {
    $card .= '<p>' . wp_kses_post($post_data['text']) . '</p>';
}
$card .= '<div class="blockquote-details justify-content-center text-center"><div class="info p-0">';
if (!empty($post_data['author_name'])) {
    $card .= '<' . $name_tag . ' class="' . esc_attr($name_class) . '">' . esc_html($post_data['author_name']) . '</' . $name_tag . '>';
}
if (!empty($post_data['author_role'])) {
    $card .= '<p class="mb-0">' . esc_html($post_data['author_role']) . '</p>';
}
if ($template_args['show_company'] && !empty($post_data['company'])) {
    $card .= '<p class="mb-0 text-muted">' . esc_html($post_data['company']) . '</p>';
}
$card .= '</div></div>';
$card .= '</blockquote>';
$card .= '</div></div>';
$card .= '</div><!-- /.card-body -->';

if ($template_args['enable_link']) {
    $url      = home_url('/testimonials/#' . absint($post_data['id']));
    $card_box = '<a href="' . esc_url($url) . '" class="card ' . esc_attr(trim(str_replace('card', '', $card_classes))) . ' text-decoration-none link-body d-block">' . $card . '</a>';
} else {
    $card_box = '<div class="' . esc_attr($card_classes) . '">' . $card . '</div><!-- /.card -->';
}

ob_start();
?>
<div class="row position-relative">
    <?php if ($photo) : ?>
        <figure class="rounded position-absolute d-none d-lg-block" style="top:50%;right:0;width:45%;height:auto;transform:translateY(-50%);z-index:2">
            <img src="<?php echo esc_url($photo); ?>"<?php echo $photo_2x ? ' srcset="' . esc_url($photo_2x) . ' 2x"' : ''; ?> alt="<?php echo esc_attr($post_data['author_name'] ?? ''); ?>">
        </figure>
    <?php endif; ?>
    <div class="<?php echo esc_attr($col_class); ?> text-center">
        <?php echo $card_box; ?>
    </div>
    <!-- /column -->
</div>
<!-- /.row -->
<?php
echo ob_get_clean();

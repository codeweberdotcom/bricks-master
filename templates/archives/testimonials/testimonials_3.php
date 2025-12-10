<?php
/**
 * Template: Testimonials Archive - Style 3 (Blockquote)
 * 
 * Блок отзыва с цитатой и иконкой в стиле Sandbox, с тенью
 * Соответствует: templates/post-cards/testimonials/blockquote.php
 */

$testimonial_data = codeweber_get_testimonial_data(get_the_ID());

if (!$testimonial_data) {
    return;
}

$testimonial_text = !empty($testimonial_data['text']) ? wp_kses_post($testimonial_data['text']) : '';
$author_name = !empty($testimonial_data['author_name']) ? esc_html($testimonial_data['author_name']) : '';
$author_role = !empty($testimonial_data['author_role']) ? esc_html($testimonial_data['author_role']) : '';
$company = !empty($testimonial_data['company']) ? esc_html($testimonial_data['company']) : '';

$avatar_url = '';
$avatar_url_2x = '';
$avatar_id = get_post_meta(get_the_ID(), '_testimonial_avatar', true);

if ($avatar_id) {
    $avatar_src = wp_get_attachment_image_src($avatar_id, 'thumbnail');
    if ($avatar_src) {
        $avatar_url = esc_url($avatar_src[0]);
    }
    $avatar_2x_src = wp_get_attachment_image_src($avatar_id, 'medium');
    if ($avatar_2x_src && $avatar_2x_src[0] !== $avatar_url) {
        $avatar_url_2x = esc_url($avatar_2x_src[0]);
    }
} elseif (!empty($testimonial_data['author_avatar'])) {
    $avatar_url = esc_url($testimonial_data['author_avatar']);
}

$rating = !empty($testimonial_data['rating']) ? intval($testimonial_data['rating']) : 0;
$rating_class = '';
if ($rating > 0 && $rating <= 5) {
    $rating_names = ['', 'one', 'two', 'three', 'four', 'five'];
    $rating_class = $rating_names[$rating];
} else {
    $rating_class = 'five';
}

$card_radius = getThemeCardImageRadius();
?>

<div class="item col-md-6 col-xl-4">
    <div class="card shadow-lg<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
        <div class="card-body">
            <?php if ($rating > 0) : ?>
                <span class="ratings <?php echo esc_attr($rating_class); ?> mb-3"></span>
            <?php endif; ?>
            
            <blockquote class="icon mb-0">
                <?php if ($testimonial_text) : ?>
                    <p><?php echo $testimonial_text; ?></p>
                <?php endif; ?>
                
                <?php codeweber_testimonial_blockquote_details(get_the_ID(), ['show_company' => !empty($company)]); ?>
            </blockquote>
        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->
</div>
<!--/column -->


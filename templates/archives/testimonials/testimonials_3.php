<?php
/**
 * Template: Testimonials Archive - Style 3 (Blockquote)
 * 
 * Блок отзыва с цитатой и иконкой в стиле Sandbox, с тенью
 * Использует: templates/post-cards/testimonials/blockquote.php
 */

$testimonial_data = codeweber_get_testimonial_data(get_the_ID());

if (!$testimonial_data) {
    return;
}

$post_id = absint(get_the_ID());
$company = !empty($testimonial_data['company']) ? esc_html($testimonial_data['company']) : '';

$card_html = cw_render_post_card(get_post(), 'blockquote', [], [
    'show_rating' => true,
    'show_company' => !empty($company),
    'shadow' => true,
    'enable_link' => false, // На архивных страницах ссылка не нужна
]);

if (empty($card_html)) {
    return;
}
?>

<div id="<?php echo esc_attr($post_id); ?>" class="item col-md-6 col-xl-4">
    <?php echo $card_html; ?>
</div>
<!--/column -->


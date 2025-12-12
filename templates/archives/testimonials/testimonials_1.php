<?php
/**
 * Template: Testimonials Archive - Style 1 (Default)
 * 
 * Базовая карточка отзыва с рейтингом, текстом, аватаром и автором
 * Использует: templates/post-cards/testimonials/default.php
 */

$testimonial_data = codeweber_get_testimonial_data(get_the_ID());

if (!$testimonial_data) {
    return;
}

$post_id = absint(get_the_ID());
$company = !empty($testimonial_data['company']) ? esc_html($testimonial_data['company']) : '';

$card_html = cw_render_post_card(get_post(), 'default', [], [
    'show_rating' => true,
    'show_company' => !empty($company),
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


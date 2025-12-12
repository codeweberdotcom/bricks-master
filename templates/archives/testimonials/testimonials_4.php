<?php
/**
 * Template: Testimonials Archive - Style 4 (Icon)
 * 
 * Блок отзыва с иконкой, без рейтинга
 * Использует: templates/post-cards/testimonials/icon.php
 */

$testimonial_data = codeweber_get_testimonial_data(get_the_ID());

if (!$testimonial_data) {
    return;
}

$post_id = absint(get_the_ID());
$company = !empty($testimonial_data['company']) ? esc_html($testimonial_data['company']) : '';

$card_html = cw_render_post_card(get_post(), 'icon', [], [
    'show_rating' => false,
    'show_company' => !empty($company),
    'enable_link' => false, // На архивных страницах ссылка не нужна
]);

if (empty($card_html)) {
    return;
}
?>

<div id="<?php echo esc_attr($post_id); ?>" class="col">
    <?php echo $card_html; ?>
</div>
<!--/column -->


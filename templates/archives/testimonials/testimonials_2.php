<?php
/**
 * Template: Testimonials Archive - Style 2 (Card)
 * 
 * Карточка отзыва в стиле Sandbox с цветными фонами
 * Использует: templates/post-cards/testimonials/card.php
 */

$post_id = absint(get_the_ID());
$card_html = cw_render_post_card(get_post(), 'card', [], [
    'show_company' => false,
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


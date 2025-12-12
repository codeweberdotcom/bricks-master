<?php
/**
 * Template: Vacancies Archive - Style 2 (Default Post Card)
 * 
 * Карточка вакансии с изображением в стиле default
 * Использует: templates/post-cards/post/default.php
 */

$post_id = absint(get_the_ID());

$card_html = cw_render_post_card(get_post(), 'default', [], [
    'enable_link' => true,
    'image_size' => 'codeweber_vacancy',
    'show_date' => false,
    'show_category' => false,
]);

if (empty($card_html)) {
    return;
}
?>

<div id="<?php echo esc_attr($post_id); ?>" class="item col-md-6 col-xl-4">
    <?php echo $card_html; ?>
</div>
<!--/column -->


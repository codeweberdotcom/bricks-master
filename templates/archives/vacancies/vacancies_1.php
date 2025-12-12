<?php
/**
 * Template: Vacancies Archive - Style 1 (Default Card)
 * 
 * Карточка вакансии с изображением и заголовком
 * Использует: templates/post-cards/post/card.php
 */

$post_id = absint(get_the_ID());

$card_html = cw_render_post_card(get_post(), 'card', [], [
    'enable_link' => true,
    'image_size' => 'codeweber_vacancy',
]);

if (empty($card_html)) {
    return;
}
?>

<div id="<?php echo esc_attr($post_id); ?>" class="col">
    <?php echo $card_html; ?>
</div>
<!--/column -->


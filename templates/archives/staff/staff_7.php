<?php
/**
 * Template: Staff Archive - Style 7
 *
 * Базовая карточка сотрудника с изображением, именем и должностью
 * Использует: templates/post-cards/staff/default.php
 */

$post_id = absint(get_the_ID());

$card_html = cw_render_post_card(get_post(), 'default', [], [
    'show_description' => false,
    'enable_link' => false,
    'image_size' => 'cw_square_md',
]);

if (empty($card_html)) {
    return;
}
?>

<div id="<?php echo esc_attr($post_id); ?>" class="item col-md-6 col-xl-4">
    <?php echo $card_html; ?>
</div>
<!--/column -->

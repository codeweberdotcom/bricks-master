<?php
/**
 * Template: Staff Archive - Style 3 (Circle Avatar)
 * 
 * Карточка сотрудника с круглым аватаром
 * Использует: templates/post-cards/staff/circle.php
 */

$post_id = absint(get_the_ID());

$card_html = cw_render_post_card(get_post(), 'circle', [], [
    'show_description' => true,
    'show_social' => true, // Показываем социальные иконки
    'enable_link' => true, // На архивных страницах ссылка не нужна
    'image_size' => 'codeweber_staff',
    'avatar_size' => 'w-15',
]);

if (empty($card_html)) {
    return;
}
?>

<div id="<?php echo esc_attr($post_id); ?>" class="col">
    <?php echo $card_html; ?>
</div>
<!--/column -->


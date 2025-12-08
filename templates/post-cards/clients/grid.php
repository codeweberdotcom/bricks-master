<?php
/**
 * Template: Client Grid
 * 
 * Логотип в figure с адаптивным padding для Grid Layout
 * 
 * @param array $post_data Данные клиента (из cw_get_post_card_data)
 * @param array $display_settings Настройки отображения
 * @param array $template_args Дополнительные аргументы (enable_link, image_size)
 */

if (!isset($post_data) || !$post_data) {
    return;
}

$template_args = wp_parse_args($template_args ?? [], [
    'enable_link' => false, // По умолчанию без ссылки
]);

$card_radius = getThemeCardImageRadius();

if ($post_data['image_url']) : ?>
    <figure class="px-3 px-md-0 px-xxl-2<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
        <?php if ($template_args['enable_link'] && !empty($post_data['link'])) : ?>
            <a href="<?php echo esc_url($post_data['link']); ?>">
        <?php endif; ?>
        <img src="<?php echo esc_url($post_data['image_url']); ?>" 
             alt="<?php echo esc_attr($post_data['image_alt']); ?>" class="<?php echo esc_attr($card_radius); ?>" />
        <?php if ($template_args['enable_link'] && !empty($post_data['link'])) : ?>
            </a>
        <?php endif; ?>
    </figure>
<?php endif; ?>


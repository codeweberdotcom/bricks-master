<?php
/**
 * Template: Client Card
 * 
 * Логотип в карточке с тенью для Grid с карточками
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

if ($post_data['image_url']) : ?>
    <div class="card shadow-lg h-100 p-0 align-items-center">
        <div class="card-body align-items-center d-flex px-3 py-6 p-md-8">
            <figure class="px-md-3 px-xl-0 px-xxl-3 mb-0">
                <?php if ($template_args['enable_link'] && !empty($post_data['link'])) : ?>
                    <a href="<?php echo esc_url($post_data['link']); ?>">
                <?php endif; ?>
                <img src="<?php echo esc_url($post_data['image_url']); ?>" 
                     alt="<?php echo esc_attr($post_data['image_alt']); ?>" />
                <?php if ($template_args['enable_link'] && !empty($post_data['link'])) : ?>
                    </a>
                <?php endif; ?>
            </figure>
        </div>
        <!--/.card-body -->
    </div>
    <!--/.card -->
<?php endif; ?>


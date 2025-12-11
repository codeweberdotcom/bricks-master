<?php
/**
 * Template: FAQ Default
 * 
 * Шаблон карточки FAQ с иконкой
 * 
 * @param array $post_data Данные FAQ (из cw_get_post_card_data)
 * @param array $display_settings Настройки отображения
 * @param array $template_args Дополнительные аргументы
 */

if (!isset($post_data) || !$post_data) {
    return;
}

$display = cw_get_post_card_display_settings($display_settings ?? []);

$title = $post_data['title'];
if ($display['title_length'] > 0 && mb_strlen($title) > $display['title_length']) {
    $title = mb_substr($title, 0, $display['title_length']) . '...';
}

// Для FAQ используем excerpt или content
$excerpt = '';
if ($display['excerpt_length'] > 0) {
    // Сначала пробуем excerpt, если пусто - используем content
    $content = !empty($post_data['excerpt']) ? $post_data['excerpt'] : get_the_content();
    $excerpt = wp_trim_words($content, $display['excerpt_length'], '...');
}

// Формируем тег и классы для заголовка
$title_tag = isset($display['title_tag']) ? sanitize_html_class($display['title_tag']) : 'h4';
$title_class = '';
if (!empty($display['title_class'])) {
    $title_class = ' ' . esc_attr($display['title_class']);
}
?>

<div class="d-flex flex-row">
    <div>
        <span class="icon btn btn-sm btn-circle btn-primary pe-none me-5"><i class="uil uil-comment-exclamation"></i></span>
    </div>
    <div>
        <?php if ($display['show_title'] && !empty($title)) : ?>
            <<?php echo esc_attr($title_tag); ?> class="<?php echo esc_attr(trim($title_class)); ?>">
                <?php echo esc_html($title); ?>
            </<?php echo esc_attr($title_tag); ?>>
        <?php endif; ?>
        <?php if (!empty($excerpt)) : ?>
            <p class="mb-0"><?php echo wp_kses_post($excerpt); ?></p>
        <?php endif; ?>
    </div>
</div>


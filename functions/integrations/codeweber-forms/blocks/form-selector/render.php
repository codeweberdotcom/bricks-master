<?php
/**
 * Form Selector Block - Server-side render
 * 
 * Отображает форму из CPT codeweber_form
 * 
 * @package Codeweber
 * 
 * @var array    $attributes Block attributes.
 * @var string   $content    Block default content.
 * @var WP_Block $block      Block instance.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Получаем ID формы
$form_id = isset($attributes['formId']) ? $attributes['formId'] : '';

if (empty($form_id)) {
    if (current_user_can('edit_posts')) {
        echo '<p style="padding: 20px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px;">';
        echo esc_html__('Please select a form in the block settings.', 'codeweber');
        echo '</p>';
    }
    return;
}

// Проверяем, что форма существует
$form_post = get_post($form_id);
if (!$form_post || $form_post->post_type !== 'codeweber_form') {
    if (current_user_can('edit_posts')) {
        echo '<p style="padding: 20px; background: #f8d7da; border: 1px solid #dc3545; border-radius: 4px;">';
        echo esc_html__('Form not found. Please select another form.', 'codeweber');
        echo '</p>';
    }
    return;
}

// Используем шорткод для отображения формы
echo do_shortcode('[codeweber_form id="' . esc_attr($form_id) . '"]');











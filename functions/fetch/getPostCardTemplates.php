<?php

namespace Codeweber\Functions\Fetch;

/**
 * Get Post Card Templates function for Fetch system
 * 
 * Returns available post card templates for a specific post type
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get available post card templates for post type via Fetch system
 * 
 * @param array $params {
 *     Parameters
 *     @type string $post_type Post type (required)
 * }
 * @return array Response in Fetch format
 */
function getPostCardTemplates($params) {
    $post_type = isset($params['post_type']) ? sanitize_text_field($params['post_type']) : 'post';
    
    // Определяем шаблоны в зависимости от типа записи
    $templates = [];
    $default_template = 'default';
    
    switch ($post_type) {
        case 'clients':
            $templates = [
                ['value' => 'client-simple', 'label' => __('Client Simple', 'codeweber'), 'description' => __('Simple logo for Swiper slider', 'codeweber')],
                ['value' => 'client-grid', 'label' => __('Client Grid', 'codeweber'), 'description' => __('Logo in figure with adaptive padding for Grid layout', 'codeweber')],
                ['value' => 'client-card', 'label' => __('Client Card', 'codeweber'), 'description' => __('Logo in card with shadow for Grid with cards', 'codeweber')],
            ];
            $default_template = 'client-simple';
            break;
            
        case 'testimonials':
            $templates = [
                ['value' => 'default', 'label' => __('Default', 'codeweber'), 'description' => __('Basic testimonial card with rating, text, avatar and author', 'codeweber')],
                ['value' => 'card', 'label' => __('Card', 'codeweber'), 'description' => __('Card with colored backgrounds (Sandbox style)', 'codeweber')],
                ['value' => 'blockquote', 'label' => __('Blockquote', 'codeweber'), 'description' => __('Block with quote and icon', 'codeweber')],
                ['value' => 'icon', 'label' => __('Icon', 'codeweber'), 'description' => __('Simple blockquote with icon, without rating', 'codeweber')],
            ];
            $default_template = 'default';
            break;
            
        case 'documents':
            $templates = [
                ['value' => 'document-card', 'label' => __('Document Card', 'codeweber'), 'description' => __('Card layout with email button for documents', 'codeweber')],
                ['value' => 'document-card-download', 'label' => __('Document Card Download', 'codeweber'), 'description' => __('Card layout with AJAX download button for documents', 'codeweber')],
            ];
            $default_template = 'document-card';
            break;
            
        case 'faq':
            $templates = [
                ['value' => 'default', 'label' => __('Default', 'codeweber'), 'description' => __('FAQ card with icon, question and answer', 'codeweber')],
            ];
            $default_template = 'default';
            break;
            
        case 'staff':
            $templates = [
                ['value' => 'default', 'label' => __('Default', 'codeweber'), 'description' => __('Basic staff card with image, name and position', 'codeweber')],
                ['value' => 'card', 'label' => __('Card', 'codeweber'), 'description' => __('Card with colored backgrounds (Sandbox style)', 'codeweber')],
                ['value' => 'circle', 'label' => __('Circle', 'codeweber'), 'description' => __('Circular avatar with social links', 'codeweber')],
                ['value' => 'circle_center', 'label' => __('Circle Center', 'codeweber'), 'description' => __('Circular avatar centered with social links', 'codeweber')],
                ['value' => 'circle_center_alt', 'label' => __('Circle Center Alt', 'codeweber'), 'description' => __('Circular avatar centered with link on image and social links', 'codeweber')],
            ];
            $default_template = 'default';
            break;
            
        case 'offices':
            $templates = [
                ['value' => 'card', 'label' => __('Card', 'codeweber'), 'description' => __('Office card template', 'codeweber')],
            ];
            $default_template = 'card';
            break;
            
        case 'vacancies':
            $templates = [
                ['value' => 'card', 'label' => __('Card', 'codeweber'), 'description' => __('Vacancy card template', 'codeweber')],
            ];
            $default_template = 'card';
            break;
            
        default: // post, page и другие
            $templates = [
                ['value' => 'default', 'label' => __('Default', 'codeweber'), 'description' => __('Simple layout with figure overlay and post header/footer', 'codeweber')],
                ['value' => 'card', 'label' => __('Card', 'codeweber'), 'description' => __('Card layout with shadow and card body', 'codeweber')],
                ['value' => 'card-content', 'label' => __('Card Content', 'codeweber'), 'description' => __('Card with excerpt and footer', 'codeweber')],
                ['value' => 'slider', 'label' => __('Slider', 'codeweber'), 'description' => __('Slider layout with category on image and excerpt', 'codeweber')],
                ['value' => 'default-clickable', 'label' => __('Default Clickable', 'codeweber'), 'description' => __('Fully clickable card with lift effect', 'codeweber')],
                ['value' => 'overlay-5', 'label' => __('Overlay 5', 'codeweber'), 'description' => __('Overlay effect with 90% opacity and bottom overlay for date', 'codeweber')],
            ];
            $default_template = 'default';
            break;
    }
    
    return [
        'status' => 'success',
        'data' => [
            'templates' => $templates,
            'default_template' => $default_template
        ]
    ];
}


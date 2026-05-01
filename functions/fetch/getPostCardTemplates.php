<?php

namespace Codeweber\Functions\Fetch;

/**
 * Get Post Card Templates function for Fetch system
 *
 * Returns available post card templates for a specific post type
 * using the central registry (codeweber_get_post_card_templates_for).
 *
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get available post card templates for post type via Fetch system.
 *
 * @param array $params {
 *     @type string $post_type Post type (required)
 * }
 * @return array Response in Fetch format
 */
function getPostCardTemplates($params) {
    $post_type = isset($params['post_type']) ? sanitize_text_field($params['post_type']) : 'post';

    if (function_exists('codeweber_get_post_card_templates_for')) {
        $templates = codeweber_get_post_card_templates_for($post_type);
    } else {
        $templates = [];
    }

    $default_template = !empty($templates) ? $templates[0]['value'] : 'default';

    return [
        'status' => 'success',
        'data'   => [
            'templates'        => $templates,
            'default_template' => $default_template,
        ],
    ];
}

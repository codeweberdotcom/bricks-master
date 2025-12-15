<?php
/**
 * CodeWeber Forms Sanitizer
 * 
 * Form data sanitization
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

class CodeweberFormsSanitizer {
    /**
     * Sanitize field value
     */
    public static function sanitize($value, $field_type) {
        switch ($field_type) {
            case 'email':
                return sanitize_email($value);
            case 'url':
                return esc_url_raw($value);
            case 'textarea':
                return wp_kses_post($value);
            case 'number':
                return floatval($value);
            case 'tel':
                return sanitize_text_field($value);
            default:
                return sanitize_text_field($value);
        }
    }
}



<?php
/**
 * CodeWeber Forms CPT Registration
 * 
 * Custom Post Type for storing form configurations
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

class CodeweberFormsCPT {
    public function __construct() {
        add_action('init', [$this, 'register_post_type']);
        add_action('save_post_codeweber_form', [$this, 'save_form_meta'], 10, 3);
    }
    
    /**
     * Register Custom Post Type for forms
     */
    public function register_post_type() {
        $labels = [
            'name' => __('Forms', 'codeweber'),
            'singular_name' => __('Form', 'codeweber'),
            'menu_name' => __('Forms', 'codeweber'),
            'add_new' => __('Add New Form', 'codeweber'),
            'add_new_item' => __('Add New Form', 'codeweber'),
            'edit_item' => __('Edit Form', 'codeweber'),
            'new_item' => __('New Form', 'codeweber'),
            'view_item' => __('View Form', 'codeweber'),
            'search_items' => __('Search Forms', 'codeweber'),
            'not_found' => __('No forms found', 'codeweber'),
            'not_found_in_trash' => __('No forms found in Trash', 'codeweber'),
        ];
        
        $args = [
            'label' => __('Forms', 'codeweber'),
            'labels' => $labels,
            'description' => __('Contact forms created with CodeWeber Forms', 'codeweber'),
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_rest' => true, // Важно для Gutenberg!
            'rest_base' => 'codeweber',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'has_archive' => false,
            'show_in_menu' => true,
            'menu_position' => 30,
            'menu_icon' => 'dashicons-email-alt',
            'capability_type' => 'post',
            'supports' => ['title', 'editor'], // Editor для Gutenberg блока
            'can_export' => true,
        ];
        
        register_post_type('codeweber_form', $args);
    }
    
    /**
     * Save form meta fields
     */
    public function save_form_meta($post_id, $post, $update) {
        // Проверка nonce
        if (!isset($_POST['codeweber_forms_meta_nonce']) || 
            !wp_verify_nonce($_POST['codeweber_forms_meta_nonce'], 'save_form_meta')) {
            return;
        }
        
        // Проверка прав
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Проверка автосохранения
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Сохранение метаполей формы
        $meta_fields = [
            '_form_recipient_email',
            '_form_sender_email',
            '_form_sender_name',
            '_form_subject',
            '_form_success_message',
            '_form_error_message',
            '_form_enable_captcha',
            '_form_rate_limit_enabled',
            '_form_auto_reply_enabled',
        ];
        
        foreach ($meta_fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
            }
        }
    }
}




<?php
/**
 * CodeWeber Forms Core Class
 * 
 * Main class for forms module
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

class CodeweberFormsCore {
    private $db;
    
    public function __construct() {
        $this->db = new CodeweberFormsDatabase();
        
        // Подключаем скрипты и стили
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_assets() {
        wp_enqueue_script(
            'codeweber',
            CODEWEBER_FORMS_URL . '/assets/js/form-submit-universal.js',
            [],
            CODEWEBER_FORMS_VERSION,
            true
        );
        
        wp_localize_script('codeweber', 'codeweberForms', [
            'restUrl' => rest_url('codeweber-forms/v1/'),
            'restNonce' => wp_create_nonce('wp_rest'),
            'messages' => [
                'required' => __('This field is required.', 'codeweber'),
                'email' => __('Please enter a valid email address.', 'codeweber'),
                'success' => __('Form submitted successfully!', 'codeweber'),
                'error' => __('An error occurred. Please try again.', 'codeweber'),
            ]
        ]);
        
        wp_enqueue_style(
            'codeweber',
            CODEWEBER_FORMS_URL . '/assets/css/forms.css',
            [],
            CODEWEBER_FORMS_VERSION
        );
    }
    
    /**
     * Render form
     */
    public function render_form($form_id, $form_config) {
        if (is_numeric($form_id)) {
            // Форма из CPT
            $form_post = get_post($form_id);
            if ($form_post && $form_post->post_type === 'codeweber_form') {
                $renderer = new CodeweberFormsRenderer();
                return $renderer->render($form_id, $form_post);
            }
        } else {
            // Inline форма из блока
            $renderer = new CodeweberFormsRenderer();
            return $renderer->render($form_id, $form_config);
        }
        
        return '<p>' . __('Form not found.', 'codeweber') . '</p>';
    }
}


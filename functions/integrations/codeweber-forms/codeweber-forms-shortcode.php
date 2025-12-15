<?php
/**
 * CodeWeber Forms Shortcode
 * 
 * Shortcode for displaying forms: [codeweber_form id="123"]
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

class CodeweberFormsShortcode {
    public function __construct() {
        add_shortcode('codeweber_form', [$this, 'render_shortcode']);
    }
    
    /**
     * Render shortcode
     */
    public function render_shortcode($atts) {
        $atts = shortcode_atts([
            'id' => '',
            'name' => '',
        ], $atts, 'codeweber_form');
        
        if (empty($atts['id']) && empty($atts['name'])) {
            return '<p>' . __('Form ID or name is required.', 'codeweber') . '</p>';
        }
        
        // Получаем конфигурацию формы
        $form_config = $this->get_form_config($atts['id'], $atts['name']);
        
        if (!$form_config) {
            return '<p>' . __('Form not found.', 'codeweber') . '</p>';
        }
        
        // Получаем ID формы из конфигурации
        $form_id = !empty($atts['id']) && is_numeric($atts['id']) ? intval($atts['id']) : ($form_config['id'] ?? 0);
        
        $core = new CodeweberFormsCore();
        return $core->render_form($form_id, $form_config);
    }
    
    /**
     * Get form configuration
     */
    private function get_form_config($id, $name) {
        $form_post = null;
        
        // Получаем форму из CPT
        if (!empty($id) && is_numeric($id)) {
            $form_post = get_post($id);
            if ($form_post && $form_post->post_type === 'codeweber_form') {
                return $this->parse_form_config($form_post);
            }
        }
        
        // Или по названию
        if (!empty($name)) {
            $query = new WP_Query([
                'post_type' => 'codeweber_form',
                'title' => $name,
                'post_status' => 'publish',
                'posts_per_page' => 1,
                'no_found_rows' => true,
                'ignore_sticky_posts' => true,
                'update_post_term_cache' => false,
                'update_post_meta_cache' => false,
                'orderby' => 'post_date ID',
                'order' => 'ASC',
            ]);
            
            if (!empty($query->post)) {
                $form_post = $query->post;
                wp_reset_postdata();
                return $this->parse_form_config($form_post);
            }
            wp_reset_postdata();
        }
        
        return false;
    }
    
    /**
     * Parse form configuration from post
     */
    private function parse_form_config($post) {
        // Парсим конфигурацию из post_content (Gutenberg блоки или JSON)
        // И метаполей
        $config = [
            'id' => $post->ID,
            'name' => $post->post_title,
            'fields' => [], // Из post_content
            'settings' => [
                'formName' => $post->post_title,
                'recipientEmail' => get_post_meta($post->ID, '_form_recipient_email', true),
                'senderEmail' => get_post_meta($post->ID, '_form_sender_email', true),
                'senderName' => get_post_meta($post->ID, '_form_sender_name', true),
                'subject' => get_post_meta($post->ID, '_form_subject', true),
                'successMessage' => get_post_meta($post->ID, '_form_success_message', true),
                'errorMessage' => get_post_meta($post->ID, '_form_error_message', true),
            ]
        ];
        
        // Парсим Gutenberg блоки из post_content
        if (has_blocks($post->post_content)) {
            $blocks = parse_blocks($post->post_content);
            
            // Ищем блок формы
            $form_block = null;
            foreach ($blocks as $block) {
                if ($block['blockName'] === 'codeweber-blocks/form') {
                    $form_block = $block;
                    break;
                }
            }
            
            if ($form_block) {
                // Извлекаем настройки формы из атрибутов блока
                if (!empty($form_block['attrs'])) {
                    $config['settings'] = array_merge($config['settings'], $form_block['attrs']);
                }
                
                // Извлекаем поля из innerBlocks
                if (!empty($form_block['innerBlocks'])) {
                    foreach ($form_block['innerBlocks'] as $inner_block) {
                        if ($inner_block['blockName'] === 'codeweber-blocks/form-field') {
                            $config['fields'][] = $inner_block['attrs'];
                        }
                    }
                }
            } else {
                // Fallback: ищем поля напрямую (старый формат)
                foreach ($blocks as $block) {
                    if ($block['blockName'] === 'codeweber-blocks/form-field') {
                        $config['fields'][] = $block['attrs'];
                    }
                }
            }
        }
        
        return $config;
    }
}


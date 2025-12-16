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
     *
     * Поддерживает два варианта идентификатора:
     *  - числовой ID: [codeweber_form id="6119"] → CPT codeweber_form с ID 6119
     *  - строковый ключ встроенной формы: [codeweber_form id="newsletter"]
     */
    public function render_shortcode($atts) {
        $atts = shortcode_atts([
            'id'    => '',
            'name'  => '',
            'title' => '',
        ], $atts, 'codeweber_form');

        if ($atts['id'] === '') {
            return '<p>' . __('Form ID is required.', 'codeweber') . '</p>';
        }

        $raw_id      = (string) $atts['id'];
        $form_id     = $raw_id;
        $form_config = null;

        // Вариант 1: числовой ID → форма из CPT codeweber_form
        if (ctype_digit($raw_id)) {
            $form_id     = (int) $raw_id;
            $form_config = $this->get_form_config($form_id);

            if (!$form_config) {
                return '<p>' . __('Form not found.', 'codeweber') . '</p>';
            }
            
            // НОВОЕ: Получаем тип формы автоматически
            $form_type = CodeweberFormsCore::get_form_type($form_id, $form_config);
            $form_config['type'] = $form_type;
            
        } else {
            // Вариант 2: встроенная форма по строковому ключу (legacy)
            $builtin_labels = [
                'testimonial' => __('Testimonial Form', 'codeweber'),
                'resume'      => __('Resume Form', 'codeweber'),
                'newsletter'  => __('Newsletter Subscription', 'codeweber'),
                'callback'    => __('Callback Request', 'codeweber'),
            ];

            $form_title = $atts['title'] !== ''
                ? $atts['title']
                : ($builtin_labels[$raw_id] ?? $raw_id);

            $form_config = [
                'id'       => $raw_id,
                'name'     => $form_title,
                'type'     => $raw_id, // Для legacy встроенных форм тип = ID
                'fields'   => [],
                'settings' => [
                    // formTitle — заголовок формы во фронтенде
                    'formTitle' => $form_title,
                ],
            ];
        }

        // Логическое имя формы (внутренний идентификатор)
        if (!empty($atts['name'])) {
            $form_config['settings']['internalName'] = sanitize_text_field($atts['name']);
        }

        // Переопределяем отображаемый заголовок формы, если задан title
        if (!empty($atts['title'])) {
            $form_config['settings']['formTitle'] = $atts['title'];
        }
        
        $renderer = new CodeweberFormsRenderer();
        return $renderer->render($form_id, $form_config);
    }
    
    /**
     * Get form configuration
     */
    private function get_form_config($id) {
        if (empty($id) || !is_numeric($id)) {
            return false;
        }

        $form_post = get_post((int) $id);
        if ($form_post && $form_post->post_type === 'codeweber_form') {
            return $this->parse_form_config($form_post);
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
            'id'     => $post->ID,
            'name'   => $post->post_title,
            'fields' => [], // Из post_content
            'settings' => [
                // formTitle — заголовок формы
                'formTitle'       => $post->post_title,
                'recipientEmail'  => get_post_meta($post->ID, '_form_recipient_email', true),
                'senderEmail'     => get_post_meta($post->ID, '_form_sender_email', true),
                'senderName'      => get_post_meta($post->ID, '_form_sender_name', true),
                'subject'         => get_post_meta($post->ID, '_form_subject', true),
                'successMessage'  => get_post_meta($post->ID, '_form_success_message', true),
                'errorMessage'    => get_post_meta($post->ID, '_form_error_message', true),
            ],
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
                    
                    // НОВОЕ: Извлекаем тип формы из блока
                    if (!empty($form_block['attrs']['formType'])) {
                        $config['type'] = sanitize_text_field($form_block['attrs']['formType']);
                    }
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
        
        // НОВОЕ: Если тип не найден в блоке, получаем из метаполя
        if (empty($config['type'])) {
            $form_type = get_post_meta($post->ID, '_form_type', true);
            if (!empty($form_type)) {
                $config['type'] = $form_type;
            }
        }
        
        return $config;
    }
}


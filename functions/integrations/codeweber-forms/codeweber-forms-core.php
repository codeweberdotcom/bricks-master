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
    
    /**
     * Get form type from form ID or config
     * 
     * Priority:
     * 1. Explicit type from config/settings
     * 2. _form_type meta field (for CPT forms)
     * 3. Extract from block (if meta not set)
     * 4. Legacy: string ID detection (backward compatibility)
     * 5. Default: 'form'
     * 
     * @param int|string $form_id Form ID (numeric for CPT, string for built-in)
     * @param array|null $config Form configuration array
     * @return string Form type: 'form', 'newsletter', 'testimonial', 'resume', 'callback'
     */
    public static function get_form_type($form_id, $config = null) {
        // 1. Check explicit type in config
        if (!empty($config['type'])) {
            return sanitize_text_field($config['type']);
        }
        
        if (!empty($config['settings']['formType'])) {
            return sanitize_text_field($config['settings']['formType']);
        }
        
        // 2. Check meta field for CPT forms
        if (is_numeric($form_id)) {
            $type = get_post_meta((int) $form_id, '_form_type', true);
            if (!empty($type)) {
                return sanitize_text_field($type);
            }
            
            // 3. Also check in block (if meta not set yet)
            $post = get_post((int) $form_id);
            if ($post && $post->post_type === 'codeweber_form' && !empty($post->post_content)) {
                $type = self::extract_form_type_from_block($post->post_content);
                if ($type) {
                    return $type;
                }
            }
        }
        
        // 4. Legacy: backward compatibility for built-in forms
        if (is_string($form_id) && !is_numeric($form_id)) {
            $builtin_types = ['newsletter', 'testimonial', 'resume', 'callback'];
            $form_id_lower = strtolower($form_id);
            if (in_array($form_id_lower, $builtin_types, true)) {
                return $form_id_lower;
            }
        }
        
        // 5. Default
        return 'form';
    }
    
    /**
     * Extract form type from Gutenberg block content
     * 
     * @param string $content Post content
     * @return string|null Form type or null
     */
    private static function extract_form_type_from_block($content) {
        if (empty($content) || !has_blocks($content)) {
            return null;
        }
        
        $blocks = parse_blocks($content);
        
        foreach ($blocks as $block) {
            if ($block['blockName'] === 'codeweber-blocks/form') {
                if (!empty($block['attrs']['formType'])) {
                    $form_type = sanitize_text_field($block['attrs']['formType']);
                    // Валидация: разрешенные типы
                    $allowed_types = ['form', 'newsletter', 'testimonial', 'resume', 'callback'];
                    if (in_array($form_type, $allowed_types, true)) {
                        return $form_type;
                    }
                }
            }
        }
        
        return null;
    }
    
    /**
     * Извлечь согласия из блоков формы для CPT форм
     * 
     * @param int $form_id ID формы (CPT)
     * @return array Массив согласий в формате [['label' => '', 'document_id' => int, 'required' => bool], ...]
     */
    public static function extract_consents_from_blocks($form_id) {
        if (!is_numeric($form_id)) {
            return [];
        }
        
        $post = get_post((int) $form_id);
        if (!$post || $post->post_type !== 'codeweber_form' || empty($post->post_content)) {
            return [];
        }
        
        $blocks = parse_blocks($post->post_content);
        $consents = [];
        
        foreach ($blocks as $block) {
            if ($block['blockName'] === 'codeweber-blocks/form' && !empty($block['innerBlocks'])) {
                // Ищем блоки form-field с типом consents_block
                foreach ($block['innerBlocks'] as $inner_block) {
                    if ($inner_block['blockName'] === 'codeweber-blocks/form-field' && 
                        isset($inner_block['attrs']['fieldType']) && 
                        $inner_block['attrs']['fieldType'] === 'consents_block' &&
                        !empty($inner_block['attrs']['consents']) &&
                        is_array($inner_block['attrs']['consents'])) {
                        
                        // Добавляем согласия из блока
                        foreach ($inner_block['attrs']['consents'] as $consent) {
                            if (!empty($consent['label']) && !empty($consent['document_id'])) {
                                $consents[] = [
                                    'label' => sanitize_text_field($consent['label']),
                                    'document_id' => intval($consent['document_id']),
                                    'required' => !empty($consent['required']),
                                ];
                            }
                        }
                    }
                }
            }
        }
        
        return $consents;
    }
}


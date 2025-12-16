<?php
/**
 * CodeWeber Forms - Form Selector Block Registration
 * 
 * Регистрирует блок для выбора и отображения формы из CPT
 * Доступен только на обычных страницах/постах (не в CPT форм)
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

class CodeweberFormsBlockSelector {
    
    public function __construct() {
        // Регистрируем блок на раннем этапе, до того как JavaScript попытается его зарегистрировать
        add_action('init', [$this, 'register_block'], 5); // Приоритет 5, чтобы зарегистрировать раньше
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_block_assets']);
    }
    
    /**
     * Публичный метод для получения экземпляра класса (для API)
     */
    public static function get_instance() {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }
    
    /**
     * Регистрировать блок
     */
    public function register_block() {
        // Регистрируем блок в PHP с render_callback
        // JavaScript добавит edit компонент к уже зарегистрированному блоку
        $block_type = register_block_type('codeweber-blocks/form-selector', [
            'api_version' => 2,
            'attributes' => [
                'formId' => [
                    'type' => 'string',
                    'default' => '',
                ],
            ],
            'render_callback' => [$this, 'render_block'],
            'editor_script' => 'codeweber-forms-form-selector-block',
            'editor_style' => 'codeweber-forms-form-selector-block-editor',
            'style' => 'codeweber-forms-form-selector-block',
        ]);
        
        // Отладка: проверяем, что блок зарегистрирован как динамический
        if (defined('WP_DEBUG') && WP_DEBUG && $block_type) {
            error_log('Form Selector Block registered: ' . ($block_type->is_dynamic() ? 'DYNAMIC' : 'STATIC'));
            error_log('Form Selector Block render_callback: ' . (is_callable($block_type->render_callback) ? 'CALLABLE' : 'NOT CALLABLE'));
        }
    }
    
    /**
     * Подключить скрипты и стили блока
     */
    public function enqueue_block_assets() {
        $screen = get_current_screen();
        
        // Не подключаем в CPT форм (там используется другой блок)
        if ($screen && $screen->post_type === 'codeweber_form') {
            return;
        }
        
        // Проверяем, что мы в редакторе блоков
        if (!$screen || !in_array($screen->base, ['post', 'page', 'site-editor', 'appearance_page_gutenberg-edit-site'])) {
            return;
        }
        
        $block_path = CODEWEBER_FORMS_PATH . '/blocks/form-selector';
        $block_url = CODEWEBER_FORMS_URL . '/blocks/form-selector';
        
        // Проверяем существование файла
        if (!file_exists($block_path . '/index.js')) {
            return;
        }
        
        // Регистрируем и подключаем скрипт блока
        wp_register_script(
            'codeweber-forms-form-selector-block',
            $block_url . '/index.js',
            [
                'wp-blocks',
                'wp-element',
                'wp-block-editor',
                'wp-components',
                'wp-i18n',
                'wp-data',
            ],
            CODEWEBER_FORMS_VERSION,
            true
        );

        // Привязываем переводы к скрипту блока (для всех строк __() в JS)
        if (function_exists('wp_set_script_translations')) {
            wp_set_script_translations(
                'codeweber-forms-form-selector-block',
                'codeweber',
                CODEWEBER_FORMS_LANGUAGES
            );
        }
        
        // Локализуем служебные данные для JS (REST URL, nonce)
        wp_localize_script('codeweber-forms-form-selector-block', 'codeweberFormsBlock', [
            'restUrl' => rest_url('codeweber-forms/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);
        
        // Подключаем скрипт
        wp_enqueue_script('codeweber-forms-form-selector-block');
        
        // Регистрируем и подключаем стили редактора
        if (file_exists($block_path . '/editor.css')) {
            wp_register_style(
                'codeweber-forms-form-selector-block-editor',
                $block_url . '/editor.css',
                [],
                CODEWEBER_FORMS_VERSION
            );
            wp_enqueue_style('codeweber-forms-form-selector-block-editor');
        }
        
        // Регистрируем стили фронтенда
        if (file_exists($block_path . '/style.css')) {
            wp_register_style(
                'codeweber-forms-form-selector-block',
                $block_url . '/style.css',
                [],
                CODEWEBER_FORMS_VERSION
            );
            wp_enqueue_style('codeweber-forms-form-selector-block');
        }
    }
    
    /**
     * Рендеринг блока (через render.php)
     */
    public function render_block($attributes, $content, $block) {
        // Отладка (можно убрать после проверки)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('=== Form Selector Block Render START ===');
            error_log('Attributes: ' . print_r($attributes, true));
            error_log('Content: ' . $content);
            error_log('Block: ' . (is_object($block) ? get_class($block) : 'null'));
        }
        
        // Получаем formId из атрибутов (проверяем разные варианты)
        $form_id = '';
        if (isset($attributes['formId'])) {
            $form_id = $attributes['formId'];
        } elseif (isset($attributes['form_id'])) {
            $form_id = $attributes['form_id'];
        }
        
        // Преобразуем в строку и очищаем
        $form_id = trim((string) $form_id);
        
        // Убираем пустые значения
        if ($form_id === '' || $form_id === '0') {
            $form_id = '';
        }
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Form ID extracted: "' . $form_id . '" (type: ' . gettype($form_id) . ')');
        }
        
        // Если formId пустой, показываем сообщение
        if (empty($form_id)) {
            if (current_user_can('edit_posts')) {
                $message = '<p style="padding: 20px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px;">' 
                    . esc_html__('Please select a form in the block settings.', 'codeweber') 
                    . '</p>';
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('Returning: Form ID is empty');
                }
                return $message;
            }
            return '';
        }
        
        // Проверяем, что форма существует
        $form_post = get_post($form_id);
        if (!$form_post || $form_post->post_type !== 'codeweber_form') {
            if (current_user_can('edit_posts')) {
                return '<p style="padding: 20px; background: #f8d7da; border: 1px solid #dc3545; border-radius: 4px;">' 
                    . esc_html__('Form not found. Please select another form.', 'codeweber') 
                    . '</p>';
            }
            return '';
        }
        
        // Проверяем, что в форме есть блок формы
        $blocks = parse_blocks($form_post->post_content);
        $has_form_block = false;
        foreach ($blocks as $block) {
            if ($block['blockName'] === 'codeweber-blocks/form') {
                $has_form_block = true;
                break;
            }
        }
        
        if (!$has_form_block) {
            if (current_user_can('edit_posts')) {
                return '<p style="padding: 20px; background: #f8d7da; border: 1px solid #dc3545; border-radius: 4px;">' 
                    . esc_html__('Form block not found in the form. Please edit the form and add a Form block.', 'codeweber') 
                    . '</p>';
            }
            return '';
        }
        
        // Используем шорткод для отображения формы
        $shortcode = '[codeweber_form id="' . esc_attr($form_id) . '"]';
        $shortcode_output = do_shortcode($shortcode);
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Shortcode: ' . $shortcode);
            error_log('Shortcode output length: ' . strlen($shortcode_output));
            error_log('Shortcode output (first 200 chars): ' . substr($shortcode_output, 0, 200));
        }
        
        // Если шорткод вернул пустую строку или ошибку, проверяем
        if (empty($shortcode_output) || $shortcode_output === $shortcode) {
            // Пробуем напрямую через renderer
            $renderer = new CodeweberFormsRenderer();
            $form_config = $this->get_form_config_from_cpt($form_id);
            if ($form_config) {
                $shortcode_output = $renderer->render($form_id, $form_config);
            } else {
                if (current_user_can('edit_posts')) {
                    return '<p style="padding: 20px; background: #f8d7da; border: 1px solid #dc3545; border-radius: 4px;">' 
                        . esc_html__('Failed to render form. Please check form configuration.', 'codeweber') 
                        . '</p>';
                }
                return '';
            }
        }
        
        // Отладка
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Form Selector Block Render - Form ID: ' . $form_id);
            error_log('Form Selector Block Render - Shortcode output length: ' . strlen($shortcode_output));
        }
        
        return $shortcode_output;
    }
    
    /**
     * Получить конфигурацию формы из CPT
     */
    private function get_form_config_from_cpt($form_id) {
        $form_post = get_post($form_id);
        if (!$form_post || $form_post->post_type !== 'codeweber_form') {
            return false;
        }
        
        // Парсим Gutenberg блоки
        $blocks = parse_blocks($form_post->post_content);
        
        $form_block = null;
        foreach ($blocks as $block) {
            if ($block['blockName'] === 'codeweber-blocks/form') {
                $form_block = $block;
                break;
            }
        }
        
        if (!$form_block) {
            return false;
        }
        
        // Извлекаем поля из innerBlocks
        $fields = [];
        if (!empty($form_block['innerBlocks'])) {
            foreach ($form_block['innerBlocks'] as $inner_block) {
                if ($inner_block['blockName'] === 'codeweber-blocks/form-field') {
                    $fields[] = $inner_block['attrs'];
                }
            }
        }
        
        return [
            'id' => $form_id,
            'name' => $form_post->post_title,
            'fields' => $fields,
            'settings' => array_merge(
                $form_block['attrs'] ?? [],
                [
                    'formTitle' => $form_post->post_title,
                    'recipientEmail' => get_post_meta($form_id, '_form_recipient_email', true),
                    'senderEmail' => get_post_meta($form_id, '_form_sender_email', true),
                    'senderName' => get_post_meta($form_id, '_form_sender_name', true),
                    'subject' => get_post_meta($form_id, '_form_subject', true),
                ]
            ),
        ];
    }
}


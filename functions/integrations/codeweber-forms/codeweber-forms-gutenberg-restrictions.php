<?php
/**
 * CodeWeber Forms Gutenberg Editor Restrictions
 * 
 * Ограничивает доступные блоки в редакторе CPT форм
 * только блоками из плагина Codeweber Gutenberg Blocks
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

class CodeweberFormsGutenbergRestrictions {
    
    /**
     * Получить список всех блоков Codeweber Gutenberg Blocks
     */
    private function get_codeweber_blocks() {
        return [
            // Блоки форм (обязательные)
            'codeweber-blocks/form',
            'codeweber-blocks/form-field',
            'codeweber-blocks/submit-button',
            
            // Дополнительные блоки для структуры (опционально)
            'codeweber-blocks/section',
            'codeweber-blocks/columns',
            'codeweber-blocks/column',
            'codeweber-blocks/paragraph',
            'codeweber-gutenberg-blocks/heading-subtitle',
            'codeweber-blocks/button',
            'codeweber-blocks/icon',
            'codeweber-blocks/card',
            'codeweber-blocks/feature',
            'codeweber-blocks/image-simple',
            'codeweber-blocks/media',
            'codeweber-blocks/lists',
            'codeweber-blocks/accordion',
            'codeweber-blocks/tabs',
            'codeweber-blocks/post-grid',
            'codeweber-blocks/avatar',
            'codeweber-blocks/label-plus',
        ];
    }
    
    /**
     * Получить минимальный набор блоков (только для форм)
     */
    private function get_minimal_blocks() {
        return [
            'codeweber-blocks/form',
            'codeweber-blocks/form-field',
            'codeweber-blocks/submit-button',
            'codeweber-gutenberg-blocks/heading-subtitle',
        ];
    }
    
    /**
     * Ограничить доступные блоки для CPT codeweber_form
     */
    public function restrict_blocks($allowed_block_types, $block_editor_context) {
        // Проверяем, что это редактор CPT codeweber_form
        if (!empty($block_editor_context->post) && 
            $block_editor_context->post->post_type === 'codeweber_form') {
            
            // Получаем настройку: минимальный набор или все блоки Codeweber
            $restriction_mode = get_option('codeweber_forms_block_restriction', 'minimal');
            
            if ($restriction_mode === 'minimal') {
                // Только блоки форм
                return $this->get_minimal_blocks();
            } else {
                // Все блоки Codeweber Gutenberg Blocks
                return $this->get_codeweber_blocks();
            }
        }
        
        // Для обычных страниц/постов: скрываем блок codeweber-blocks/form (он только для CPT форм)
        // Вместо него будет использоваться блок codeweber-blocks/form-selector
        if (is_array($allowed_block_types)) {
            $allowed_block_types = array_filter($allowed_block_types, function($block) {
                return $block !== 'codeweber-blocks/form' 
                    && $block !== 'codeweber-blocks/form-field'
                    && $block !== 'codeweber-blocks/submit-button';
            });
        }
        
        return $allowed_block_types;
    }
    
    /**
     * Настроить редактор для CPT форм
     */
    public function setup_editor($editor_settings, $post) {
        if ($post && isset($post->post_type) && $post->post_type === 'codeweber_form') {
            // Скрываем ненужные панели
            if (isset($editor_settings['enableCustomFields'])) {
                $editor_settings['enableCustomFields'] = false;
            }
            if (isset($editor_settings['enableCustomSpacing'])) {
                $editor_settings['enableCustomSpacing'] = false;
            }
            
            // Настройки по умолчанию для форм
            $editor_settings['codeweberFormsMode'] = true;
        }
        
        return $editor_settings;
    }
    
    /**
     * Добавить подсказку в редакторе
     */
    public function add_editor_notice() {
        $screen = get_current_screen();
        
        if ($screen && $screen->post_type === 'codeweber_form') {
            ?>
            <div class="notice notice-info is-dismissible">
                <p>
                    <strong><?php _e('CodeWeber Forms Editor', 'codeweber'); ?></strong><br>
                    <?php _e('Only Codeweber Gutenberg Blocks are available in this editor. Add a "Form" block and "Form Field" blocks inside it to build the form.', 'codeweber'); ?>
                </p>
            </div>
            <?php
        }
    }
    
    public function __construct() {
        // Ограничение блоков через фильтр
        add_filter('allowed_block_types_all', [$this, 'restrict_blocks'], 10, 2);
        
        // Скрываем блок codeweber-blocks/form на обычных страницах (только для CPT форм)
        add_filter('block_editor_settings_all', [$this, 'hide_form_block_on_regular_pages'], 10, 2);
        
        // Настройка редактора (для WordPress 5.8+)
        add_filter('block_editor_settings_all', [$this, 'setup_editor'], 10, 2);
        
        // Подключение скрипта для автоматической вставки блока формы
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_auto_insert_script']);
        
        // Уведомление в админке
        add_action('admin_notices', [$this, 'add_editor_notice']);
    }
    
    /**
     * Скрыть блок codeweber-blocks/form на обычных страницах
     */
    public function hide_form_block_on_regular_pages($editor_settings, $post) {
        // Если это не CPT codeweber_form, скрываем блок формы
        if ($post && isset($post->post_type) && $post->post_type !== 'codeweber_form') {
            // Добавляем скрипт для скрытия блока через JavaScript
            if (!isset($editor_settings['codeweber_forms_hide_form_block'])) {
                $editor_settings['codeweber_forms_hide_form_block'] = true;
            }
        }
        
        return $editor_settings;
    }
    
    /**
     * Подключить скрипт для автоматической вставки блока формы
     */
    public function enqueue_auto_insert_script() {
        $screen = get_current_screen();
        
        if (!$screen) {
            return;
        }
        
        // Не загружаем скрипты в контексте редактора виджетов (WordPress 5.8+)
        // wp-editor несовместим с wp-edit-widgets или wp-customize-widgets
        if ($screen->id === 'widgets' || $screen->id === 'customize') {
            return;
        }
        
        // Скрипт для скрытия блока формы на обычных страницах
        if ($screen->post_type !== 'codeweber_form') {
            wp_enqueue_script(
                'codeweber-forms-hide-form-block',
                CODEWEBER_FORMS_URL . '/admin/assets/js/hide-form-block.js',
                ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-data'],
                CODEWEBER_FORMS_VERSION,
                true
            );
            return;
        }
        
        // Скрипт для автоматической вставки блока формы (только для CPT форм)
        
        $script_path = CODEWEBER_FORMS_PATH . '/admin/assets/js/auto-insert-form-block.js';
        
        if (file_exists($script_path)) {
            wp_enqueue_script(
                'codeweber-forms-auto-insert',
                CODEWEBER_FORMS_URL . '/admin/assets/js/auto-insert-form-block.js',
                ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-data'],
                CODEWEBER_FORMS_VERSION,
                true
            );
            
            // Передаем данные в скрипт
            wp_localize_script('codeweber-forms-auto-insert', 'codeweberFormsAutoInsert', [
                'postId' => get_the_ID(),
                'postTitle' => get_the_title() ?: __('Contact Form', 'codeweber'),
            ]);
        }
    }
}


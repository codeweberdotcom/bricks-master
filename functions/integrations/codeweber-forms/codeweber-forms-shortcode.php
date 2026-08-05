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
        add_shortcode('codeweber_form_steps', [$this, 'render_steps_shortcode']);
    }

    /**
     * Render shortcode: [codeweber_form_steps id="blockId"]
     *
     * Standalone step-navigation panel for a multipage Form block whose
     * "Sidebar Step Navigation" is set to shortcode placement. Finds the
     * matching Form block by its Block ID and renders the same panel used
     * for inline sidebar mode.
     *
     * The Form block itself isn't necessarily on the page where this
     * shortcode is placed — e.g. a CPT form (codeweber_form) embedded via
     * [codeweber_form id="128"] or the "Form Selector" block only stores a
     * reference (formId) on the page; the actual Form block with pages /
     * blockId lives in the CPT post's own content. So the search tries,
     * in order: (1) the auto-generated "form-{cptId}" pattern → direct CPT
     * post lookup, (2) the current page's content (inline forms), (3) a
     * scan of all codeweber_form CPT posts (manually-set custom Block ID).
     */
    public function render_steps_shortcode($atts) {
        $atts = shortcode_atts(['id' => ''], $atts, 'codeweber_form_steps');
        $block_id = sanitize_text_field($atts['id']);

        if ($block_id === '') {
            return '';
        }

        $form_block = null;

        // 1. Fast path: auto-generated id "form-{cptId}" → look up that CPT post directly.
        if (preg_match('/^form-(\d+)$/', $block_id, $matches)) {
            $cpt_post = get_post((int) $matches[1]);
            if ($cpt_post && $cpt_post->post_type === 'codeweber_form' && !empty($cpt_post->post_content)) {
                $form_block = $this->find_form_block_by_id(parse_blocks($cpt_post->post_content), $block_id);
            }
        }

        // 2. Current page's own content (inline, non-CPT forms).
        if (!$form_block) {
            global $post;
            if ($post && !empty($post->post_content)) {
                $form_block = $this->find_form_block_by_id(parse_blocks($post->post_content), $block_id);
            }
        }

        // 3. Last resort: scan all codeweber_form CPT posts (custom/manual Block ID).
        if (!$form_block) {
            $form_post_ids = get_posts([
                'post_type'      => 'codeweber_form',
                'posts_per_page' => -1,
                'post_status'    => 'publish',
                'fields'         => 'ids',
            ]);
            foreach ($form_post_ids as $form_post_id) {
                $form_post = get_post($form_post_id);
                if (!$form_post || empty($form_post->post_content)) {
                    continue;
                }
                $found = $this->find_form_block_by_id(parse_blocks($form_post->post_content), $block_id);
                if ($found) {
                    $form_block = $found;
                    break;
                }
            }
        }

        if (!$form_block || empty($form_block['attrs']['sidebarStepNav'])) {
            return '';
        }

        $pages = [];
        foreach ($form_block['innerBlocks'] ?? [] as $inner_block) {
            if (($inner_block['blockName'] ?? '') === 'codeweber-blocks/form-page') {
                $pages[] = $inner_block['attrs'] ?? [];
            }
        }
        if (empty($pages)) {
            return '';
        }

        $page_titles = array_map(function ($p) {
            return $p['pageTitle'] ?? '';
        }, $pages);

        if (!class_exists('CodeweberFormsRenderer')) {
            return '';
        }

        $renderer = new CodeweberFormsRenderer();
        return $renderer->render_sidebar_steps_html($page_titles, count($pages), $block_id);
    }

    /**
     * Recursively search parsed blocks for a codeweber-blocks/form block
     * whose blockId attribute matches $block_id.
     */
    private function find_form_block_by_id($blocks, $block_id) {
        foreach ($blocks as $block) {
            if (($block['blockName'] ?? '') === 'codeweber-blocks/form'
                && ($block['attrs']['blockId'] ?? '') === $block_id) {
                return $block;
            }
            if (!empty($block['innerBlocks'])) {
                $found = $this->find_form_block_by_id($block['innerBlocks'], $block_id);
                if ($found) {
                    return $found;
                }
            }
        }
        return null;
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
                    // Объединяем настройки, но не перезаписываем непустые значения из метаполей пустыми значениями из блока
                    foreach ($form_block['attrs'] as $key => $value) {
                        // Для successMessage и errorMessage: не перезаписываем, если значение из блока пустое
                        if (in_array($key, ['successMessage', 'errorMessage']) && (empty($value) || trim($value) === '')) {
                            // Пропускаем пустые значения, чтобы не перезаписать значения из метаполей
                            continue;
                        }
                        $config['settings'][$key] = $value;
                    }
                    
                    // НОВОЕ: Извлекаем тип формы из блока
                    if (!empty($form_block['attrs']['formType'])) {
                        $config['type'] = sanitize_text_field($form_block['attrs']['formType']);
                    }
                }
                
                // Извлекаем поля и кнопки из innerBlocks
                if (!empty($form_block['innerBlocks'])) {
                    $has_pages = false;
                    foreach ($form_block['innerBlocks'] as $inner_block) {
                        if ($inner_block['blockName'] === 'codeweber-blocks/form-page') {
                            $has_pages = true;
                            break;
                        }
                    }

                    // Рендерим прочие блоки (например, heading-subtitle), которые
                    // не являются полями/кнопками/страницами, чтобы они не терялись
                    // при выводе формы через шорткод / Form Selector.
                    $other_blocks_html = '';
                    foreach ($form_block['innerBlocks'] as $inner_block) {
                        $inner_name = $inner_block['blockName'] ?? '';
                        if (!in_array($inner_name, [
                            'codeweber-blocks/form-field',
                            'codeweber-blocks/submit-button',
                            'codeweber-blocks/form-page',
                        ], true)) {
                            $other_blocks_html .= render_block($inner_block);
                        }
                    }
                    if ($other_blocks_html !== '') {
                        $config['other_blocks_html'] = $other_blocks_html;
                    }

                    if ($has_pages) {
                        $pages = [];
                        foreach ($form_block['innerBlocks'] as $inner_block) {
                            if ($inner_block['blockName'] !== 'codeweber-blocks/form-page') {
                                continue;
                            }
                            $page_attrs   = $inner_block['attrs'] ?? [];
                            $page_fields  = [];
                            $page_submits = [];
                            foreach ($inner_block['innerBlocks'] ?? [] as $page_child) {
                                if ($page_child['blockName'] === 'codeweber-blocks/form-field') {
                                    $page_fields[] = $page_child['attrs'];
                                } elseif ($page_child['blockName'] === 'codeweber-blocks/submit-button') {
                                    $page_submits[] = $page_child['attrs'];
                                }
                            }
                            $pages[] = array_merge($page_attrs, [
                                'fields'         => $page_fields,
                                'submit_buttons' => $page_submits,
                            ]);
                        }
                        $config['pages'] = $pages;
                    } else {
                        foreach ($form_block['innerBlocks'] as $inner_block) {
                            if ($inner_block['blockName'] === 'codeweber-blocks/form-field') {
                                $config['fields'][] = $inner_block['attrs'];
                            } elseif ($inner_block['blockName'] === 'codeweber-blocks/submit-button') {
                                if (!isset($config['submit_buttons'])) {
                                    $config['submit_buttons'] = [];
                                }
                                $config['submit_buttons'][] = $inner_block['attrs'];
                            }
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
        
        // Устанавливаем дефолтные значения для сообщений, если они пустые
        if (empty($config['settings']['successMessage'])) {
            $config['settings']['successMessage'] = __('Thank you! Your message has been sent.', 'codeweber');
        }
        if (empty($config['settings']['errorMessage'])) {
            $config['settings']['errorMessage'] = __('An error occurred. Please try again.', 'codeweber');
        }
        
        return $config;
    }
}


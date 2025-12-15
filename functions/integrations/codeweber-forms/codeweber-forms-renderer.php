<?php
/**
 * CodeWeber Forms Renderer
 * 
 * Form rendering on frontend
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

class CodeweberFormsRenderer {
    /**
     * Render form
     */
    public function render($form_id, $form_config) {
        // Если передан объект поста CPT
        if (is_object($form_config) && $form_config instanceof WP_Post) {
            return $this->render_from_cpt($form_config);
        }
        
        // Если передан массив конфигурации
        if (is_array($form_config)) {
            return $this->render_from_config($form_id, $form_config);
        }
        
        return '<p>' . __('Invalid form configuration.', 'codeweber') . '</p>';
    }
    
    /**
     * Render form from CPT post
     */
    private function render_from_cpt($post) {
        // Парсим Gutenberg блоки из post_content
        $blocks = parse_blocks($post->post_content);
        
        $form_block = null;
        foreach ($blocks as $block) {
            if ($block['blockName'] === 'codeweber-blocks/form') {
                $form_block = $block;
                break;
            }
        }
        
        if (!$form_block) {
            return '<p>' . __('Form block not found.', 'codeweber') . '</p>';
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
        
        $form_config = [
            'fields' => $fields,
            'settings' => array_merge(
                $form_block['attrs'] ?? [],
                [
                    'recipientEmail' => get_post_meta($post->ID, '_form_recipient_email', true),
                    'senderEmail' => get_post_meta($post->ID, '_form_sender_email', true),
                    'senderName' => get_post_meta($post->ID, '_form_sender_name', true),
                    'subject' => get_post_meta($post->ID, '_form_subject', true),
                ]
            ),
        ];
        
        return $this->render_from_config($post->ID, $form_config);
    }
    
    /**
     * Render form from configuration array
     */
    private function render_from_config($form_id, $config) {
        $fields = $config['fields'] ?? [];
        $settings = $config['settings'] ?? [];
        
        // Получаем form_id из конфигурации, если не передан
        if (empty($form_id) && !empty($config['id'])) {
            $form_id = intval($config['id']);
        }
        
        // Генерируем уникальный ID формы
        $form_unique_id = 'form-' . ($form_id ?: uniqid());
        
        // Настройки формы
        $form_name = $settings['formName'] ?? 'Contact Form';
        $recipient_email = $settings['recipientEmail'] ?? get_option('admin_email');
        $success_message = $settings['successMessage'] ?? __('Thank you! Your message has been sent.', 'codeweber');
        $error_message = $settings['errorMessage'] ?? __('An error occurred. Please try again.', 'codeweber');
        
        // Определяем, является ли форма newsletter формой (нужно определить до использования)
        $is_newsletter_form = $this->is_newsletter_form($form_id, $form_name);
        
        // Для newsletter формы используем "Join" с переводом
        if ($is_newsletter_form) {
            // Всегда переводим "Join", даже если он указан в настройках
            if (!empty($settings['submitButtonText'])) {
                $button_text = trim($settings['submitButtonText']);
                // Если текст "Join" (в любом регистре), переводим его
                if (strtolower($button_text) === 'join') {
                    $submit_button_text = __('Join', 'codeweber');
                } else {
                    // Иначе используем как есть (может быть уже переведен или кастомный текст)
                    $submit_button_text = $button_text;
                }
            } else {
                $submit_button_text = __('Join', 'codeweber');
            }
        } else {
            $submit_button_text = $settings['submitButtonText'] ?? __('Send Message', 'codeweber');
        }
        $submit_button_class = $settings['submitButtonClass'] ?? 'btn btn-primary';
        
        // Honeypot для защиты от спама (не для newsletter форм)
        $honeypot_field = '';
        if (!$is_newsletter_form && !empty($settings['enableCaptcha']) && $settings['captchaType'] === 'honeypot') {
            $honeypot_field = '<input type="text" name="form_honeypot" value="" style="display:none !important; visibility:hidden !important;" tabindex="-1" autocomplete="off">';
        }
        
        ob_start();
        ?>
        <form 
            id="<?php echo esc_attr($form_unique_id); ?>" 
            class="codeweber-form needs-validation<?php echo $is_newsletter_form ? ' newsletter-subscription-form' : ''; ?>" 
            data-form-id="<?php echo esc_attr($form_id); ?>"
            data-handled-by="codeweber-forms-universal"
            method="post"
            enctype="multipart/form-data"
            novalidate
        >
            <?php wp_nonce_field('codeweber_form_submit', 'form_nonce'); ?>
            <?php echo $honeypot_field; ?>
            
            <input type="hidden" name="form_id" value="<?php echo esc_attr($form_id); ?>">
            <?php if (!$is_newsletter_form): ?>
                <input type="hidden" name="form_honeypot" value="">
            <?php endif; ?>
            <div class="form-messages" style="display: none;"></div>
            
            <?php 
            // Для newsletter формы с одним email полем используем специальную верстку
            $has_single_email = count($fields) === 1 && !empty($fields[0]) && ($fields[0]['fieldType'] ?? '') === 'email';
            
            // Если newsletter форма, но поля не найдены, создаем дефолтное email поле
            if ($is_newsletter_form && empty($fields)) {
                $fields = [[
                    'fieldType' => 'email',
                    'fieldName' => 'email',
                    'fieldLabel' => __('Email Address', 'codeweber'),
                    'placeholder' => __('Email Address', 'codeweber'),
                    'isRequired' => true
                ]];
                $has_single_email = true;
            }
            
            if ($is_newsletter_form && $has_single_email): 
            ?>
                <!-- Специальная верстка для newsletter формы: email + кнопка в одной строке -->
                <div class="newsletter-form-inner">
                    <div class="input-group form-floating">
                        <?php 
                        $email_field = $fields[0];
                        $field_id = 'field-' . ($email_field['fieldName'] ?? 'email');
                        
                        // Переводим fieldLabel и placeholder, если они указаны
                        $field_label = !empty($email_field['fieldLabel']) 
                            ? (trim($email_field['fieldLabel']) === 'Email Address' ? __('Email Address', 'codeweber') : $email_field['fieldLabel'])
                            : __('Email Address', 'codeweber');
                        $field_placeholder = !empty($email_field['placeholder'])
                            ? (trim($email_field['placeholder']) === 'Email Address' ? __('Email Address', 'codeweber') : $email_field['placeholder'])
                            : $field_label;
                        ?>
                        <?php 
                        // Получаем класс скругления формы из темы
                        $form_radius_class = function_exists('getThemeFormRadius') ? getThemeFormRadius() : '';
                        ?>
                        <input
                            type="email"
                            class="form-control required email<?php echo esc_attr($form_radius_class); ?>"
                            id="<?php echo esc_attr($field_id); ?>"
                            name="<?php echo esc_attr($email_field['fieldName'] ?? 'email'); ?>"
                            placeholder="<?php echo esc_attr($field_placeholder); ?>"
                            required
                            autocomplete="off"
                        >
                        <label for="<?php echo esc_attr($field_id); ?>">
                            <?php echo esc_html($field_label); ?>
                        </label>
                        <?php 
                        // Получаем класс кнопки из темы
                        $button_style = function_exists('getThemeButton') ? getThemeButton() : '';
                        ?>
                        <button 
                            type="submit" 
                            class="<?php echo esc_attr($submit_button_class . $button_style); ?>"
                            data-loading-text="<?php echo esc_attr(__('Sending...', 'codeweber')); ?>"
                        >
                            <?php echo esc_html($submit_button_text); ?>
                        </button>
                    </div>
                    
                    <?php
                    // Получаем согласия из builtin_form_consents для newsletter формы
                    if (function_exists('codeweber_forms_render_consent_checkbox')) {
                        $all_consents = get_option('builtin_form_consents', []);
                        $newsletter_consents = isset($all_consents['newsletter']) ? $all_consents['newsletter'] : [];
                        
                        if (!empty($newsletter_consents) && is_array($newsletter_consents)) {
                            $form_radius_class = function_exists('getThemeFormRadius') ? getThemeFormRadius() : '';
                            ?>
                            <div class="newsletter-consents mt-2">
                                <?php
                                foreach ($newsletter_consents as $index => $consent) {
                                    if (empty($consent['label']) || empty($consent['document_id'])) {
                                        continue;
                                    }
                                    
                                    $document_id = intval($consent['document_id']);
                                    $label_text = codeweber_forms_process_consent_label($consent['label'], $document_id, $form_id);
                                    $required = !empty($consent['required']);
                                    $checkbox_id = 'newsletter-consent-' . $document_id . '-' . $index;
                                    ?>
                                    <div class="form-check small-checkbox small-chekbox mb-1">
                                        <input 
                                            type="checkbox" 
                                            class="form-check-input<?php echo esc_attr($form_radius_class); ?>" 
                                            id="<?php echo esc_attr($checkbox_id); ?>" 
                                            name="newsletter_consents[<?php echo esc_attr($document_id); ?>]" 
                                            value="1"
                                            <?php echo $required ? 'required' : ''; ?>
                                        >
                                        <label class="form-check-label" for="<?php echo esc_attr($checkbox_id); ?>" style="font-size: 12px;">
                                            <?php echo wp_kses_post($label_text); ?>
                                        </label>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
            <?php else: ?>
                <!-- Стандартная верстка для обычных форм -->
                <div class="row g-4">
                    <?php foreach ($fields as $field): ?>
                        <?php echo $this->render_field($field); ?>
                    <?php endforeach; ?>
                </div>
                
                <div class="form-submit-wrapper mt-4">
                    <button 
                        type="submit" 
                        class="<?php echo esc_attr($submit_button_class); ?>"
                        data-loading-text="<?php echo esc_attr(__('Sending...', 'codeweber')); ?>"
                    >
                        <?php echo esc_html($submit_button_text); ?>
                    </button>
                </div>
            <?php endif; ?>
        </form>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Проверяет, является ли форма newsletter формой
     */
    private function is_newsletter_form($form_id, $form_name) {
        // Проверяем по названию
        $name_lower = strtolower($form_name);
        if (strpos($name_lower, 'newsletter') !== false) {
            return true;
        }
        
        // Проверяем по метаполю
        if ($form_id && is_numeric($form_id)) {
            $form_type = get_post_meta($form_id, '_form_type', true);
            if ($form_type === 'newsletter') {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Render single form field
     */
    private function render_field($field) {
        $field_type = $field['fieldType'] ?? 'text';
        $field_name = $field['fieldName'] ?? '';
        $field_label = $field['fieldLabel'] ?? '';
        $placeholder = $field['placeholder'] ?? '';
        $is_required = !empty($field['isRequired']);
        $width = $field['width'] ?? 'col-12';
        $help_text = $field['helpText'] ?? '';
        $default_value = $field['defaultValue'] ?? '';
        $max_length = !empty($field['maxLength']) ? intval($field['maxLength']) : 0;
        $min_length = !empty($field['minLength']) ? intval($field['minLength']) : 0;
        
        if (empty($field_name)) {
            return '';
        }
        
        $field_id = 'field-' . $field_name;
        $required_attr = $is_required ? 'required' : '';
        $required_mark = $is_required ? ' <span class="text-danger">*</span>' : '';
        
        ob_start();
        ?>
        <div class="<?php echo esc_attr($width); ?>">
            <?php
            switch ($field_type) {
                case 'textarea':
                    ?>
                    <div class="form-floating">
                        <textarea
                            class="form-control"
                            id="<?php echo esc_attr($field_id); ?>"
                            name="<?php echo esc_attr($field_name); ?>"
                            placeholder="<?php echo esc_attr($placeholder ?: $field_label); ?>"
                            <?php echo $required_attr; ?>
                            <?php echo $max_length > 0 ? 'maxlength="' . $max_length . '"' : ''; ?>
                            style="height: 120px;"
                        ><?php echo esc_textarea($default_value); ?></textarea>
                        <label for="<?php echo esc_attr($field_id); ?>">
                            <?php echo esc_html($field_label); ?><?php echo $required_mark; ?>
                        </label>
                    </div>
                    <?php
                    break;
                
                case 'select':
                    $options = $field['options'] ?? [];
                    ?>
                    <div class="form-floating">
                        <select
                            class="form-select"
                            id="<?php echo esc_attr($field_id); ?>"
                            name="<?php echo esc_attr($field_name); ?>"
                            <?php echo $required_attr; ?>
                        >
                            <option value=""><?php echo esc_html($placeholder ?: __('Select...', 'codeweber')); ?></option>
                            <?php foreach ($options as $option): ?>
                                <option value="<?php echo esc_attr($option['value'] ?? ''); ?>">
                                    <?php echo esc_html($option['label'] ?? ''); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label for="<?php echo esc_attr($field_id); ?>">
                            <?php echo esc_html($field_label); ?><?php echo $required_mark; ?>
                        </label>
                    </div>
                    <?php
                    break;
                
                case 'radio':
                    $options = $field['options'] ?? [];
                    ?>
                    <div>
                        <label class="form-label">
                            <?php echo esc_html($field_label); ?><?php echo $required_mark; ?>
                        </label>
                        <?php foreach ($options as $idx => $option): ?>
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="radio"
                                    id="<?php echo esc_attr($field_id . '-' . $idx); ?>"
                                    name="<?php echo esc_attr($field_name); ?>"
                                    value="<?php echo esc_attr($option['value'] ?? ''); ?>"
                                    <?php echo $required_attr; ?>
                                />
                                <label class="form-check-label" for="<?php echo esc_attr($field_id . '-' . $idx); ?>">
                                    <?php echo esc_html($option['label'] ?? ''); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php
                    break;
                
                case 'checkbox':
                    $options = $field['options'] ?? [];
                    if (!empty($options)) {
                        // Множественные чекбоксы
                        ?>
                        <div>
                            <label class="form-label">
                                <?php echo esc_html($field_label); ?><?php echo $required_mark; ?>
                            </label>
                            <?php foreach ($options as $idx => $option): ?>
                                <div class="form-check">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        id="<?php echo esc_attr($field_id . '-' . $idx); ?>"
                                        name="<?php echo esc_attr($field_name); ?>[]"
                                        value="<?php echo esc_attr($option['value'] ?? ''); ?>"
                                    />
                                    <label class="form-check-label" for="<?php echo esc_attr($field_id . '-' . $idx); ?>">
                                        <?php echo esc_html($option['label'] ?? ''); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php
                    } else {
                        // Одиночный чекбокс
                        ?>
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="<?php echo esc_attr($field_id); ?>"
                                name="<?php echo esc_attr($field_name); ?>"
                                value="1"
                                <?php echo $required_attr; ?>
                            />
                            <label class="form-check-label" for="<?php echo esc_attr($field_id); ?>">
                                <?php echo esc_html($field_label); ?><?php echo $required_mark; ?>
                            </label>
                        </div>
                        <?php
                    }
                    break;
                
                case 'file':
                    $accept = $field['accept'] ?? '';
                    $multiple = !empty($field['multiple']);
                    ?>
                    <div class="form-floating">
                        <input
                            type="file"
                            class="form-control"
                            id="<?php echo esc_attr($field_id); ?>"
                            name="<?php echo esc_attr($field_name); ?><?php echo $multiple ? '[]' : ''; ?>"
                            <?php echo $required_attr; ?>
                            <?php echo $accept ? 'accept="' . esc_attr($accept) . '"' : ''; ?>
                            <?php echo $multiple ? 'multiple' : ''; ?>
                        />
                        <label for="<?php echo esc_attr($field_id); ?>">
                            <?php echo esc_html($field_label); ?><?php echo $required_mark; ?>
                        </label>
                    </div>
                    <?php
                    break;
                
                case 'hidden':
                    ?>
                    <input
                        type="hidden"
                        id="<?php echo esc_attr($field_id); ?>"
                        name="<?php echo esc_attr($field_name); ?>"
                        value="<?php echo esc_attr($default_value); ?>"
                    />
                    <?php
                    break;
                
                default:
                    // text, email, tel, url, date, time, number
                    ?>
                    <div class="form-floating">
                        <input
                            type="<?php echo esc_attr($field_type); ?>"
                            class="form-control"
                            id="<?php echo esc_attr($field_id); ?>"
                            name="<?php echo esc_attr($field_name); ?>"
                            placeholder="<?php echo esc_attr($placeholder ?: $field_label); ?>"
                            value="<?php echo esc_attr($default_value); ?>"
                            <?php echo $required_attr; ?>
                            <?php echo $max_length > 0 ? 'maxlength="' . $max_length . '"' : ''; ?>
                            <?php echo $min_length > 0 ? 'minlength="' . $min_length . '"' : ''; ?>
                        />
                        <label for="<?php echo esc_attr($field_id); ?>">
                            <?php echo esc_html($field_label); ?><?php echo $required_mark; ?>
                        </label>
                    </div>
                    <?php
                    break;
            }
            ?>
            
            <?php if (!empty($help_text)): ?>
                <div class="form-text text-muted small">
                    <?php echo esc_html($help_text); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}


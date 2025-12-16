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
        
        // Извлекаем поля и кнопки из innerBlocks
        $fields = [];
        $submit_buttons = [];
        if (!empty($form_block['innerBlocks'])) {
            foreach ($form_block['innerBlocks'] as $inner_block) {
                if ($inner_block['blockName'] === 'codeweber-blocks/form-field') {
                    $fields[] = $inner_block['attrs'];
                } elseif ($inner_block['blockName'] === 'codeweber-blocks/submit-button') {
                    $submit_buttons[] = $inner_block['attrs'];
                }
            }
        }
        
        // Извлекаем все атрибуты блока (включая formGap*)
        $block_attrs = $form_block['attrs'] ?? [];
        
        $form_config = [
            'fields' => $fields,
            'submit_buttons' => $submit_buttons,
            'settings' => array_merge(
                $block_attrs, // Все атрибуты блока, включая formGap*, formGapType, и т.д.
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
        // form_id может быть как числом (CPT формы), так и строкой (встроенные формы)
        if (empty($form_id) && !empty($config['id'])) {
            $form_id = is_numeric($config['id']) ? intval($config['id']) : $config['id'];
        }
        
        // Генерируем уникальный ID формы для каждого экземпляра на странице
        // Используем статический счетчик, чтобы гарантировать уникальность
        static $form_instance_counter = 0;
        $form_instance_counter++;
        $form_unique_id = 'form-' . ($form_id ?: uniqid()) . '-' . $form_instance_counter;
        
        // Настройки формы
        // formTitle — заголовок формы для верстки (frontend),
        // internalName — логическое имя формы (если задано через шорткод name),
        // для статистики/идентификаторов внутри системы предпочтительнее internalName.
        $form_name = $settings['internalName'] ?? ($settings['formTitle'] ?? 'Contact Form');
        $recipient_email = $settings['recipientEmail'] ?? get_option('admin_email');
        $success_message = $settings['successMessage'] ?? __('Thank you! Your message has been sent.', 'codeweber');
        $error_message = $settings['errorMessage'] ?? __('An error occurred. Please try again.', 'codeweber');
        
        // Получаем кнопки из блоков submit-button
        $submit_buttons = $form_config['submit_buttons'] ?? [];
        
        // Определяем, является ли форма newsletter формой (нужно определить до использования)
        $is_newsletter_form = $this->is_newsletter_form($form_id, $form_name);
        
        // Для newsletter формы используем "Join" с переводом (fallback, если нет блоков submit-button)
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
        
        // Получаем blockClass, blockData, blockId из настроек
        $block_class = $settings['blockClass'] ?? '';
        $block_data = $settings['blockData'] ?? '';
        $block_id = $settings['blockId'] ?? '';
        
        // Формируем классы для формы
        $form_classes = ['codeweber-form', 'needs-validation'];
        if ($is_newsletter_form) {
            $form_classes[] = 'newsletter-subscription-form';
        }
        if ($block_class) {
            $form_classes[] = $block_class;
        }
        
        // Формируем ID для формы (приоритет: blockId > form_unique_id)
        $form_element_id = $block_id ?: $form_unique_id;
        
        // Формируем data-атрибуты
        $form_data_attrs = [
            'data-form-id' => $form_id,
            'data-form-name' => $settings['internalName'] ?? '',
            'data-handled-by' => 'codeweber-forms-universal',
        ];
        
        // Добавляем blockData как data-атрибуты
        if ($block_data) {
            // Пробуем распарсить как JSON
            $parsed_data = json_decode($block_data, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($parsed_data)) {
                // Если это валидный JSON массив, добавляем каждый ключ как data-атрибут
                foreach ($parsed_data as $key => $value) {
                    $form_data_attrs['data-' . sanitize_key($key)] = esc_attr($value);
                }
            } else {
                // Если не JSON, добавляем как data-custom
                $form_data_attrs['data-custom'] = esc_attr($block_data);
            }
        }
        
        ob_start();
        ?>
        <form 
            id="<?php echo esc_attr($form_element_id); ?>" 
            class="<?php echo esc_attr(implode(' ', $form_classes)); ?>"
            <?php foreach ($form_data_attrs as $attr_name => $attr_value): ?>
                <?php echo esc_attr($attr_name); ?>="<?php echo esc_attr($attr_value); ?>"
            <?php endforeach; ?>
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
                    
                    // Рендерим кнопки из блоков submit-button для newsletter формы
                    if (!empty($submit_buttons)) {
                        foreach ($submit_buttons as $button_attrs) {
                            $button_text = $button_attrs['buttonText'] ?? __('Join', 'codeweber');
                            // Для newsletter формы переводим "Join"
                            if (strtolower(trim($button_text)) === 'join') {
                                $button_text = __('Join', 'codeweber');
                            }
                            $button_class = $button_attrs['buttonClass'] ?? 'btn btn-primary';
                            $block_class = $button_attrs['blockClass'] ?? '';
                            ?>
                            <div class="form-submit-wrapper mt-3 <?php echo esc_attr($block_class); ?>">
                                <button
                                    type="submit"
                                    class="<?php echo esc_attr($button_class); ?> btn-icon btn-icon-start"
                                    data-loading-text="<?php echo esc_attr(__('Sending...', 'codeweber')); ?>"
                                >
                                    <i class="uil uil-send fs-13"></i>
                                    <span class="ms-1"><?php echo esc_html($button_text); ?></span>
                                </button>
                            </div>
                            <?php
                        }
                    } else {
                        // Fallback: используем старую кнопку из настроек
                        ?>
                        <div class="form-submit-wrapper mt-3">
                            <button
                                type="submit"
                                class="<?php echo esc_attr($submit_button_class); ?> btn-icon btn-icon-start"
                                data-loading-text="<?php echo esc_attr(__('Sending...', 'codeweber')); ?>"
                            >
                                <i class="uil uil-send fs-13"></i>
                                <span class="ms-1"><?php echo esc_html($submit_button_text); ?></span>
                            </button>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            <?php else: ?>
                <!-- Стандартная верстка для обычных форм -->
                <?php
                // Генерируем классы Gap (логика соответствует helpers.js getGapClasses)
                // getGapClasses генерирует классы для всех трех типов одновременно (g, gx, gy)
                $gap_classes = [];
                
                // Функция для генерации классов gap для одного типа (соответствует getGapClassesForType из helpers.js)
                $get_gap_classes_for_type = function($settings, $gap_type_prefix) {
                    $classes = [];
                    $suffix = $gap_type_prefix === 'g' ? '' : ($gap_type_prefix === 'gx' ? 'X' : 'Y');
                    
                    // Получаем значения для текущего типа (соответствует helpers.js строка 66-74)
                    $gap_default = $settings["formGap{$suffix}"] ?? '';
                    $gap_xs = $settings["formGap{$suffix}Xs"] ?? '';
                    $gap_sm = $settings["formGap{$suffix}Sm"] ?? '';
                    $gap_md = $settings["formGap{$suffix}Md"] ?? '';
                    $gap_lg = $settings["formGap{$suffix}Lg"] ?? '';
                    $gap_xl = $settings["formGap{$suffix}Xl"] ?? '';
                    $gap_xxl = $settings["formGap{$suffix}Xxl"] ?? '';
                    
                    // Базовое значение (default breakpoint) - соответствует helpers.js строка 77-79
                    // Важно: проверяем !== '', чтобы "0" обрабатывалось корректно
                    if ($gap_default !== '' && $gap_default !== null) {
                        $classes[] = "{$gap_type_prefix}-{$gap_default}";
                    }
                    // XS breakpoint - соответствует helpers.js строка 81-83
                    if ($gap_xs !== '' && $gap_xs !== null) {
                        $classes[] = "{$gap_type_prefix}-{$gap_xs}";
                    }
                    // Остальные breakpoints - соответствует helpers.js строка 85-99
                    if ($gap_sm !== '' && $gap_sm !== null) {
                        $classes[] = "{$gap_type_prefix}-sm-{$gap_sm}";
                    }
                    if ($gap_md !== '' && $gap_md !== null) {
                        $classes[] = "{$gap_type_prefix}-md-{$gap_md}";
                    }
                    if ($gap_lg !== '' && $gap_lg !== null) {
                        $classes[] = "{$gap_type_prefix}-lg-{$gap_lg}";
                    }
                    if ($gap_xl !== '' && $gap_xl !== null) {
                        $classes[] = "{$gap_type_prefix}-xl-{$gap_xl}";
                    }
                    if ($gap_xxl !== '' && $gap_xxl !== null) {
                        $classes[] = "{$gap_type_prefix}-xxl-{$gap_xxl}";
                    }
                    
                    return $classes;
                };
                
                // Собираем классы для всех трех типов одновременно (соответствует getGapClasses из helpers.js)
                $gap_classes = array_merge(
                    $get_gap_classes_for_type($settings, 'g'),   // General (g-*)
                    $get_gap_classes_for_type($settings, 'gx'),  // Horizontal (gx-*)
                    $get_gap_classes_for_type($settings, 'gy')   // Vertical (gy-*)
                );
                
                // Формируем классы для row (как в save.js)
                $row_classes = ['row'];
                if (!empty($gap_classes)) {
                    $row_classes = array_merge($row_classes, $gap_classes);
                } else {
                    // Если нет классов gap, проверяем formGap
                    // Важно: проверяем !== '', чтобы "0" обрабатывалось корректно
                    $form_gap_value = $settings['formGap'] ?? '';
                    if ($form_gap_value !== '' && $form_gap_value !== null) {
                        $row_classes[] = "g-{$form_gap_value}";
                    } else {
                        // Дефолт из block.json (не сохраняется, если не было изменено)
                        $row_classes[] = 'g-4';
                    }
                }
                
                $row_class = implode(' ', array_filter($row_classes));
                
                // Временная отладка для проверки значений gap
                // Раскомментируйте для проверки в error_log или wp_debug.log
                if (defined('WP_DEBUG') && WP_DEBUG && isset($_GET['debug_gap'])) {
                    error_log('=== Form Gap Debug ===');
                    error_log('formGap: ' . ($settings['formGap'] ?? 'NOT SET'));
                    error_log('formGapType: ' . ($settings['formGapType'] ?? 'NOT SET'));
                    error_log('gap_classes count: ' . count($gap_classes));
                    error_log('gap_classes: ' . print_r($gap_classes, true));
                    error_log('row_class: ' . $row_class);
                    $gap_settings = array_filter($settings, function($key) {
                        return strpos($key, 'formGap') === 0;
                    }, ARRAY_FILTER_USE_KEY);
                    error_log('All formGap* settings: ' . print_r($gap_settings, true));
                }
                ?>
                <div class="<?php echo esc_attr($row_class); ?>">
                    <?php foreach ($fields as $field): ?>
                        <?php echo $this->render_field($field, $form_id); ?>
                    <?php endforeach; ?>
                </div>
                
                <?php
                // Рендерим кнопки из блоков submit-button
                $submit_buttons = $form_config['submit_buttons'] ?? [];
                if (!empty($submit_buttons)) {
                    foreach ($submit_buttons as $button_attrs) {
                        $button_text = $button_attrs['buttonText'] ?? __('Send Message', 'codeweber');
                        $button_class = $button_attrs['buttonClass'] ?? 'btn btn-primary';
                        $block_class = $button_attrs['blockClass'] ?? '';
                        ?>
                        <div class="form-submit-wrapper mt-4 <?php echo esc_attr($block_class); ?>">
                            <button
                                type="submit"
                                class="<?php echo esc_attr($button_class); ?> btn-icon btn-icon-start"
                                data-loading-text="<?php echo esc_attr(__('Sending...', 'codeweber')); ?>"
                            >
                                <i class="uil uil-send fs-13"></i>
                                <span class="ms-1"><?php echo esc_html($button_text); ?></span>
                            </button>
                        </div>
                        <?php
                    }
                } else {
                    // Fallback: используем старую кнопку из настроек, если блоков submit-button нет
                    ?>
                    <div class="form-submit-wrapper mt-4">
                        <button
                            type="submit"
                            class="<?php echo esc_attr($submit_button_class); ?> btn-icon btn-icon-start"
                            data-loading-text="<?php echo esc_attr(__('Sending...', 'codeweber')); ?>"
                        >
                            <i class="uil uil-send fs-13"></i>
                            <span class="ms-1"><?php echo esc_html($submit_button_text); ?></span>
                        </button>
                    </div>
                    <?php
                }
                ?>
            <?php endif; ?>
        </form>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Проверяет, является ли форма newsletter формой
     */
    private function is_newsletter_form($form_id, $form_name) {
        // 1) Встроенная форма по строковому идентификатору (НЕ переводим, это технический ключ)
        if (is_string($form_id)) {
            $id_lower = strtolower($form_id);
            if ($id_lower === 'newsletter') {
                return true;
            }
        }

        // 2) Формы‑записи CPT: определяем по метаполю `_form_type = newsletter`
        if ($form_id && is_numeric($form_id)) {
            $form_type = get_post_meta((int) $form_id, '_form_type', true);
            if ($form_type === 'newsletter') {
                return true;
            }
        }

        // Название формы (`form_name`) в определении типа НЕ участвует,
        // чтобы переводы названия никак не влияли на логику.
        return false;
    }
    
    /**
     * Render single form field
     */
    private function render_field($field, $form_id = 0) {
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
        
        // Генерируем классы col-* из fieldColumns* атрибутов
        $get_col_classes = function($field) {
            $col_classes = [];
            $field_columns = $field['fieldColumns'] ?? '';
            $field_columns_xs = $field['fieldColumnsXs'] ?? '';
            $field_columns_sm = $field['fieldColumnsSm'] ?? '';
            $field_columns_md = $field['fieldColumnsMd'] ?? '';
            $field_columns_lg = $field['fieldColumnsLg'] ?? '';
            $field_columns_xl = $field['fieldColumnsXl'] ?? '';
            $field_columns_xxl = $field['fieldColumnsXxl'] ?? '';
            
            // Если есть fieldColumns* атрибуты, используем их
            if ($field_columns || $field_columns_xs || $field_columns_sm || $field_columns_md || $field_columns_lg || $field_columns_xl || $field_columns_xxl) {
                if ($field_columns) $col_classes[] = 'col-' . $field_columns;
                if ($field_columns_xs) $col_classes[] = 'col-' . $field_columns_xs;
                if ($field_columns_sm) $col_classes[] = 'col-sm-' . $field_columns_sm;
                if ($field_columns_md) $col_classes[] = 'col-md-' . $field_columns_md;
                if ($field_columns_lg) $col_classes[] = 'col-lg-' . $field_columns_lg;
                if ($field_columns_xl) $col_classes[] = 'col-xl-' . $field_columns_xl;
                if ($field_columns_xxl) $col_classes[] = 'col-xxl-' . $field_columns_xxl;
                
                return !empty($col_classes) ? implode(' ', $col_classes) : 'col-12';
            }
            
            // Fallback на старый атрибут width для обратной совместимости
            return $field['width'] ?? 'col-12';
        };
        
        $width_classes = $get_col_classes($field);
        
        // Получаем blockClass из атрибутов поля
        $block_class = !empty($field['blockClass']) ? esc_attr($field['blockClass']) : '';
        
        // Специальный тип поля: блок согласий, основанный на настройках блока (атрибут consents)
        // Для consents_block не требуется fieldName, поэтому проверяем его первым
        if ($field_type === 'consents_block') {
            // Получаем согласия из атрибутов блока
            // Атрибуты могут быть в разных местах в зависимости от того, как блок был сохранен
            $consents = [];
            
            // Способ 1: Прямо из массива $field (основной способ)
            if (isset($field['consents']) && is_array($field['consents'])) {
                $consents = $field['consents'];
            }
            
            // Способ 2: Если согласий нет в атрибутах, пробуем получить из метабокса (обратная совместимость)
            if (empty($consents)) {
                $numeric_form_id = is_numeric($form_id) ? (int) $form_id : 0;
                if ($numeric_form_id > 0) {
                    $meta_consents = get_post_meta($numeric_form_id, '_form_consents', true);
                    if (is_array($meta_consents) && !empty($meta_consents)) {
                        $consents = $meta_consents;
                    }
                }
            }
            
            // Отладочный вывод (можно убрать после проверки)
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Consents Block Debug - Field: ' . print_r($field, true));
                error_log('Consents Block Debug - Consents: ' . print_r($consents, true));
            }
            
            if (!empty($consents) && function_exists('codeweber_forms_render_consent_checkbox')) {
                $numeric_form_id = is_numeric($form_id) ? (int) $form_id : 0;
                ob_start();
                ?>
                <div class="<?php echo esc_attr($width_classes); ?>">
                    <div class="form-consents-block<?php echo $block_class ? ' ' . $block_class : ''; ?>">
                        <?php
                        foreach ($consents as $consent) {
                            // Пропускаем пустые согласия
                            if (empty($consent['label']) || empty($consent['document_id'])) {
                                continue;
                            }
                            // Рендерим чекбокс согласия на основе документа и текста метки
                            echo codeweber_forms_render_consent_checkbox(
                                $consent,
                                'newsletter_consents', // имя массива, которое уже обрабатывается в универсальной логике
                                $numeric_form_id
                            );
                        }
                        ?>
                    </div>
                </div>
                <?php
                return ob_get_clean();
            }
            
            // Если согласий нет — ничего не выводим
            return '';
        }
        
        // Для остальных типов полей требуется fieldName
        if (empty($field_name)) {
            return '';
        }
        
        $field_id = 'field-' . $field_name;
        $required_attr = $is_required ? 'required' : '';
        $required_mark = $is_required ? ' <span class="text-danger">*</span>' : '';
        
        ob_start();
        ?>
        <div class="<?php echo esc_attr($width_classes); ?>">
            <?php
            switch ($field_type) {
                case 'textarea':
                    ?>
                    <div class="form-floating<?php echo $block_class ? ' ' . $block_class : ''; ?>">
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
                    <div class="form-floating<?php echo $block_class ? ' ' . $block_class : ''; ?>">
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
                    <div<?php echo $block_class ? ' class="' . $block_class . '"' : ''; ?>>
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
                        <div<?php echo $block_class ? ' class="' . $block_class . '"' : ''; ?>>
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
                        <div class="form-check<?php echo $block_class ? ' ' . $block_class : ''; ?>">
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
                    <div class="form-floating<?php echo $block_class ? ' ' . $block_class : ''; ?>">
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
                    <div class="form-floating<?php echo $block_class ? ' ' . $block_class : ''; ?>">
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


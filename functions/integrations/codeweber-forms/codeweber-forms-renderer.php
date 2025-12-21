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
        $has_filepond = false;
        if (!empty($form_block['innerBlocks'])) {
            foreach ($form_block['innerBlocks'] as $inner_block) {
                if ($inner_block['blockName'] === 'codeweber-blocks/form-field') {
                    $field_attrs = $inner_block['attrs'] ?? [];
                    // Проверяем, есть ли file поле с FilePond
                    // FilePond всегда используется для полей типа file
                    if (($field_attrs['fieldType'] ?? '') === 'file') {
                        $has_filepond = true;
                    }
                    $fields[] = $field_attrs;
                } elseif ($inner_block['blockName'] === 'codeweber-blocks/submit-button') {
                    $submit_buttons[] = $inner_block['attrs'];
                }
            }
        }
        
        // Enqueue FilePond if needed
        if ($has_filepond) {
            if (class_exists('\Codeweber\Blocks\Plugin')) {
                \Codeweber\Blocks\Plugin::enqueue_filepond();
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
        
        // НОВОЕ: Извлекаем тип формы из блока или метаполя
        if (!empty($block_attrs['formType'])) {
            $form_config['type'] = sanitize_text_field($block_attrs['formType']);
        } else {
            $form_type = get_post_meta($post->ID, '_form_type', true);
            if (!empty($form_type)) {
                $form_config['type'] = $form_type;
            }
        }
        
        // Рендерим форму (заголовок теперь добавляется в render_from_config())
        return $this->render_from_config($post->ID, $form_config);
    }
    
    /**
     * Render form from configuration array
     */
    private function render_from_config($form_id, $config) {
        $fields = $config['fields'] ?? [];
        $settings = $config['settings'] ?? [];
        
        // Check for FilePond fields and enqueue scripts if needed
        // FilePond всегда используется для полей типа file
        $has_filepond = false;
        foreach ($fields as $field) {
            if (($field['fieldType'] ?? '') === 'file') {
                $has_filepond = true;
                break;
            }
        }
        
        if ($has_filepond) {
            if (class_exists('\Codeweber\Blocks\Plugin')) {
                \Codeweber\Blocks\Plugin::enqueue_filepond();
            }
        }
        
        // Получаем кнопки из блоков submit-button
        $submit_buttons = $config['submit_buttons'] ?? [];
        
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
        // internalName — логическое имя формы (если задано через шорткод name).
        // formTitle — заголовок формы (берётся из CPT title / настроек).
        $form_name = $settings['internalName'] ?? ($settings['formTitle'] ?? 'Contact Form');
        $recipient_email = $settings['recipientEmail'] ?? get_option('admin_email');
        $success_message = $settings['successMessage'] ?? __('Thank you! Your message has been sent.', 'codeweber');
        $error_message = $settings['errorMessage'] ?? __('An error occurred. Please try again.', 'codeweber');
        
        // НОВОЕ: Получаем тип формы через единую функцию
        $form_type = CodeweberFormsCore::get_form_type($form_id, $config);
        $is_newsletter_form = ($form_type === 'newsletter');
        
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
        // data-form-name: используем internalName приоритетно, иначе formTitle, иначе дефолт
        $data_form_name = $settings['internalName'] ?? $form_name;

        $form_data_attrs = [
            'data-form-id' => $form_id,
            'data-form-type' => $form_type, // НОВОЕ: Добавляем тип формы
            'data-form-name' => $data_form_name,
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
            // Проверяем, есть ли поле типа newsletter (которое рендерится с кнопкой внутри)
            $has_newsletter_field = false;
            foreach ($fields as $field) {
                if (($field['fieldType'] ?? '') === 'newsletter') {
                    $has_newsletter_field = true;
                    break;
                }
            }
            
            // Для newsletter формы с одним email полем (БЕЗ newsletter типа) используем специальную верстку
            // Проверяем как тип 'email', но НЕ 'newsletter' (newsletter рендерится через render.php с кнопкой)
            $has_single_email = count($fields) === 1 && !empty($fields[0]) && 
                ($fields[0]['fieldType'] ?? '') === 'email' && !$has_newsletter_field;
            
            // Если newsletter форма, но поля не найдены, создаем дефолтное email поле
            if ($is_newsletter_form && empty($fields) && !$has_newsletter_field) {
                $fields = [[
                    'fieldType' => 'email',
                    'fieldName' => 'email',
                    'fieldLabel' => __('Email Address', 'codeweber'),
                    'placeholder' => __('Email Address', 'codeweber'),
                    'isRequired' => true
                ]];
                $has_single_email = true;
            }
            
            // Если есть поле newsletter, НЕ используем специальную верстку - поле само рендерится с кнопкой
            // УБРАНО: Специальная верстка для newsletter формы больше не используется
            // Теперь newsletter поле рендерится через render.php с кнопкой внутри
            // Кнопка рендерится ТОЛЬКО если:
            // 1. Добавлена через блок submit-button в редакторе Gutenberg
            // 2. Или используется поле типа newsletter (которое содержит кнопку внутри)
            
            // Все формы (включая newsletter) рендерятся через стандартную верстку
            // Кнопка добавляется только через блок submit-button или поле newsletter
            ?>
                <!-- Стандартная верстка для всех форм -->
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
                
                // Добавляем классы выравнивания к элементу с row (для фронтенда)
                $alignment_classes = [];
                if (!empty($settings['formAlignItems'])) {
                    $alignment_classes[] = trim($settings['formAlignItems']);
                }
                if (!empty($settings['formJustifyContent'])) {
                    $alignment_classes[] = 'd-flex';
                    $alignment_classes[] = trim($settings['formJustifyContent']);
                }
                if (!empty($settings['formTextAlign'])) {
                    $alignment_classes[] = trim($settings['formTextAlign']);
                }
                if (!empty($settings['formPosition'])) {
                    $alignment_classes[] = trim($settings['formPosition']);
                }
                
                // Объединяем все классы
                $row_classes = array_merge($row_classes, $alignment_classes);
                $row_class = implode(' ', array_filter($row_classes));
                
                ?>
                <div class="<?php echo esc_attr($row_class); ?>">
                    <?php foreach ($fields as $field): ?>
                        <?php 
                        // Временная отладка для newsletter
                        if (current_user_can('manage_options') && !empty($_GET['debug_form']) && ($field['fieldType'] ?? '') === 'newsletter') {
                            echo '<!-- DEBUG: Rendering newsletter field: ' . print_r($field, true) . ' -->';
                        }
                        echo $this->render_field($field, $form_id); 
                        ?>
                    <?php endforeach; ?>
                </div>
                
                <?php
                // Получаем класс скругления кнопки из темы
                $button_radius_class = function_exists('getThemeButton') ? getThemeButton() : '';
                
                // Рендерим кнопки из блоков submit-button (добавленных в редакторе Gutenberg)
                // $submit_buttons уже получены из $config в начале метода render_from_config
                if (!empty($submit_buttons)) {
                    foreach ($submit_buttons as $button_attrs) {
                        $button_text = $button_attrs['buttonText'] ?? __('Send Message', 'codeweber');
                        $button_class = $button_attrs['buttonClass'] ?? 'btn btn-primary';
                        $block_class = $button_attrs['blockClass'] ?? '';
                        
                        // Объединяем классы кнопки с классом скругления из темы
                        $final_button_class = trim($button_class . ' ' . $button_radius_class);
                        ?>
                        <div class="form-submit-wrapper mt-4 <?php echo esc_attr($block_class); ?>">
                            <button
                                type="submit"
                                class="<?php echo esc_attr($final_button_class); ?> btn-icon btn-icon-start"
                                data-loading-text="<?php echo esc_attr(__('Sending', 'codeweber')); ?>"
                            >
                                <span><?php echo esc_html($button_text); ?></span>
                            </button>
                        </div>
                        <?php
                    }
                }
                // УБРАНО: Fallback кнопка больше не рендерится автоматически
                // Кнопка рендерится ТОЛЬКО если:
                // 1. Добавлена через блок submit-button в редакторе Gutenberg
                // 2. Или используется поле типа newsletter (которое содержит кнопку внутри через render.php)
                ?>
        </form>
        <?php
        $rendered_form = ob_get_clean();
        
        // Добавляем заголовок перед формой, если он указан
        $header_output = '';
        $form_title = $settings['formTitle'] ?? '';
        $form_subtitle = $settings['formSubtitle'] ?? '';
        $form_title_tag = $settings['formTitleTag'] ?? 'div';
        $form_subtitle_tag = $settings['formSubtitleTag'] ?? 'p';
        $form_title_class = $settings['formTitleClass'] ?? '';
        $form_subtitle_class = $settings['formSubtitleClass'] ?? '';
        
        if (!empty($form_title) || !empty($form_subtitle)) {
            $header_output = '<div class="form-header">';
            if (!empty($form_title)) {
                $title_classes = !empty($form_title_class) ? esc_attr(trim($form_title_class)) : 'h3 text-start';
                $title_tag = sanitize_key($form_title_tag);
                // Ограничиваем возможные теги для безопасности
                $allowed_tags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'p', 'span'];
                if (!in_array($title_tag, $allowed_tags)) {
                    $title_tag = 'div';
                }
                $header_output .= '<' . $title_tag . ' class="' . $title_classes . '">' . esc_html($form_title) . '</' . $title_tag . '>';
            }
            if (!empty($form_subtitle)) {
                $subtitle_classes = !empty($form_subtitle_class) ? esc_attr(trim($form_subtitle_class)) : 'lead mb-4 text-start';
                $subtitle_tag = sanitize_key($form_subtitle_tag);
                // Ограничиваем возможные теги для безопасности
                $allowed_tags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'p', 'span'];
                if (!in_array($subtitle_tag, $allowed_tags)) {
                    $subtitle_tag = 'p';
                }
                $header_output .= '<' . $subtitle_tag . ' class="' . $subtitle_classes . '">' . esc_html($form_subtitle) . '</' . $subtitle_tag . '>';
            }
            $header_output .= '</div>';
        }
        
        return $header_output . $rendered_form;
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
        // Check for inline button BEFORE other processing
        $enable_inline_button = isset($field['enableInlineButton']) && ($field['enableInlineButton'] === true || $field['enableInlineButton'] === 'true' || $field['enableInlineButton'] === 1);
        $field_type = $field['fieldType'] ?? 'text';
        $inline_button_supported_types = ['text', 'email', 'tel', 'url', 'number', 'date', 'time', 'author_role', 'company'];
        $inline_button_enabled = $enable_inline_button && in_array($field_type, $inline_button_supported_types) && $field_type !== 'newsletter';
        
        
        // For fields with inline button, use render.php (like newsletter)
        if ($inline_button_enabled) {
            
            // Use render.php for inline button fields
            $render_path = WP_PLUGIN_DIR . '/codeweber-gutenberg-blocks/build/blocks/form-field/render.php';
            if (file_exists($render_path)) {
                // Create a mock block object for render.php
                $block_instance = (object) [
                    'context' => ['codeweber/formId' => $form_id]
                ];
                
                // Pass variables to render.php scope
                $attributes = $field;
                $content = '';
                $block = $block_instance;
                $context = ['codeweber/formId' => $form_id];
                
                ob_start();
                require $render_path;
                $rendered = ob_get_clean();
                
                
                return $rendered;
            }
        }
        
        $field_name = $field['fieldName'] ?? '';
        
        $field_label = $field['fieldLabel'] ?? '';
        $placeholder = $field['placeholder'] ?? '';
        $is_required = !empty($field['isRequired']);
        $width = $field['width'] ?? 'col-12';
        $help_text = $field['helpText'] ?? '';
        $default_value = $field['defaultValue'] ?? '';
        $max_length = !empty($field['maxLength']) ? intval($field['maxLength']) : 0;
        $min_length = !empty($field['minLength']) ? intval($field['minLength']) : 0;
        
        // Получаем класс скругления формы из темы
        $form_radius_class = function_exists('getThemeFormRadius') ? getThemeFormRadius() : '';
        
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
                                'form_consents', // Универсальный префикс для всех форм
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
        
        // Для newsletter типа fieldName может быть пустым (по умолчанию 'email')
        // Для остальных типов полей требуется fieldName
        if ($field_type !== 'newsletter' && empty($field_name)) {
            return '';
        }
        
        // Для newsletter используем 'email' по умолчанию, если fieldName пустой
        if ($field_type === 'newsletter' && empty($field_name)) {
            $field_name = 'email';
        }
        
        // Генерируем уникальный ID поля с учетом form_id для избежания конфликтов на странице с несколькими формами
        $form_id_safe = $form_id ? sanitize_html_class($form_id) : 'default';
        $field_id = 'field-' . $form_id_safe . '-' . $field_name;
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
                            class="form-control<?php echo esc_attr($form_radius_class); ?>"
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
                    <div class="form-select-wrapper mb-4<?php echo $block_class ? ' ' . $block_class : ''; ?>">
                        <select
                            class="form-select<?php echo esc_attr($form_radius_class); ?>"
                            id="<?php echo esc_attr($field_id); ?>"
                            name="<?php echo esc_attr($field_name); ?>"
                            aria-label="<?php echo esc_attr($field_label ?: __('Select option', 'codeweber')); ?>"
                            <?php echo $required_attr; ?>
                        >
                            <option value=""><?php echo esc_html($placeholder ?: $field_label ?: __('Select...', 'codeweber')); ?></option>
                            <?php foreach ($options as $option): ?>
                                <option value="<?php echo esc_attr($option['value'] ?? ''); ?>" <?php echo ($default_value && $default_value === ($option['value'] ?? '')) ? 'selected' : ''; ?>>
                                    <?php echo esc_html($option['label'] ?? ''); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
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
                                    class="form-check-input<?php echo esc_attr($form_radius_class); ?>"
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
                                        class="form-check-input<?php echo esc_attr($form_radius_class); ?>"
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
                                class="form-check-input<?php echo esc_attr($form_radius_class); ?>"
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
                    // FilePond всегда используется для полей типа file
                    $use_filepond = true; // Всегда true для полей типа file
                    $max_files = !empty($field['maxFiles']) ? intval($field['maxFiles']) : 0;
                    // Use default values from block.json if not set
                    $max_file_size = $field['maxFileSize'] ?? '10MB';
                    $max_total_file_size = $field['maxTotalFileSize'] ?? '100MB';
                    $no_file_text = __('No file selected', 'codeweber');
                    $browse_text = __('Browse', 'codeweber');
                    ?>
                    <div<?php echo $block_class ? ' class="' . $block_class . '"' : ''; ?>>
                        <label for="<?php echo esc_attr($field_id); ?>" class="form-label">
                            <?php echo esc_html($field_label); ?><?php echo $required_mark; ?>
                        </label>
                        <!-- FilePond для всех файлов - обычный дизайн -->
                        <input
                            type="file"
                            class="filepond"
                            id="<?php echo esc_attr($field_id); ?>"
                            name="<?php echo esc_attr($field_name); ?><?php echo $multiple ? '[]' : ''; ?>"
                            data-filepond="true"
                            data-max-files="<?php echo ($multiple && $max_files > 0) ? esc_attr($max_files) : ($multiple ? '' : '1'); ?>"
                            data-max-file-size="<?php echo $max_file_size ? esc_attr($max_file_size) : ''; ?>"
                            data-max-total-file-size="<?php echo $max_total_file_size ? esc_attr($max_total_file_size) : ''; ?>"
                            <?php echo $required_attr; ?>
                            <?php echo $accept ? 'accept="' . esc_attr($accept) . '"' : ''; ?>
                            <?php echo $multiple ? 'multiple' : ''; ?>
                        />
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
                
                case 'newsletter':
                    // Поле newsletter рендерится с кнопкой внутри input-group
                    $field_name_newsletter = !empty($field_name) ? $field_name : 'email';
                    $field_id_newsletter = 'field-' . $form_id_safe . '-' . $field_name_newsletter;
                    $field_label_newsletter = !empty($field_label) ? $field_label : __('Email Address', 'codeweber');
                    $field_placeholder_newsletter = !empty($placeholder) ? $placeholder : $field_label_newsletter;
                    
                    // Получаем текст и класс кнопки из атрибутов
                    $button_text_newsletter = !empty($field['buttonText']) ? $field['buttonText'] : __('Join', 'codeweber');
                    $button_class_newsletter = !empty($field['buttonClass']) ? $field['buttonClass'] : 'btn btn-primary';
                    
                    // Получаем класс скругления кнопки из темы
                    $button_radius_class_newsletter = '';
                    if (function_exists('getThemeButton')) {
                        $button_radius_class_newsletter = getThemeButton();
                    }
                    $button_class_final = trim($button_class_newsletter . ' ' . $button_radius_class_newsletter);
                    
                    // Для newsletter типа: если стиль кнопки rounded-pill, применяем его к input полю
                    $input_radius_class = $form_radius_class;
                    if (strpos($button_radius_class_newsletter, 'rounded-pill') !== false) {
                        $input_radius_class = ' rounded-pill';
                    }
                    ?>
                    <div class="input-group form-floating<?php echo $block_class ? ' ' . $block_class : ''; ?>">
                        <input
                            type="email"
                            class="form-control required email <?php echo esc_attr($input_radius_class); ?>"
                            id="<?php echo esc_attr($field_id_newsletter); ?>"
                            name="<?php echo esc_attr($field_name_newsletter); ?>"
                            placeholder="<?php echo esc_attr($field_placeholder_newsletter); ?>"
                            <?php echo $required_attr; ?>
                            autocomplete="off"
                        >
                        <label for="<?php echo esc_attr($field_id_newsletter); ?>">
                            <?php echo esc_html($field_label_newsletter); ?><?php echo $required_mark; ?>
                        </label>
                        <input
                            type="submit"
                            value="<?php echo esc_attr($button_text_newsletter); ?>"
                            class="<?php echo esc_attr($button_class_final); ?>"
                            data-loading-text="<?php echo esc_attr(__('Sending...', 'codeweber')); ?>"
                        >
                    </div>
                    <?php
                    break;
                
                case 'rating':
                    $current_rating = !empty($default_value) ? intval($default_value) : 0;
                    if ($current_rating < 1 || $current_rating > 5) {
                        $current_rating = 0;
                    }
                    ?>
                    <div<?php echo $block_class ? ' class="' . $block_class . '"' : ''; ?>>
                        <label class="form-label d-block mb-2">
                            <?php echo esc_html($field_label); ?><?php echo $required_mark; ?>
                        </label>
                        <input
                            type="hidden"
                            id="<?php echo esc_attr($field_id); ?>"
                            name="<?php echo esc_attr($field_name); ?>"
                            value="<?php echo esc_attr($current_rating); ?>"
                            <?php echo $required_attr; ?>
                        />
                        <div class="rating-stars-wrapper d-flex gap-1 align-items-center p-0" data-rating-input="<?php echo esc_attr($field_id); ?>">
                            <?php for ($i = 1; $i <= 5; $i++): 
                                $is_active = $i <= $current_rating;
                            ?>
                                <span 
                                    class="rating-star-item <?php echo $is_active ? 'active' : ''; ?>" 
                                    data-rating="<?php echo esc_attr($i); ?>"
                                    style="cursor: pointer;"
                                >★</span>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <?php
                    break;
                
                default:
                    // text, email, tel, url, date, time, number
                    // Подготовка data-атрибутов для phone-mask (только для tel)
                    $mask_attrs = '';
                    if ($field_type === 'tel') {
                        $phone_mask = $field['phoneMask'] ?? '';
                        $phone_mask_caret = $field['phoneMaskCaret'] ?? '';
                        $phone_mask_soft_caret = $field['phoneMaskSoftCaret'] ?? '';
                        
                        if (!empty($phone_mask)) {
                            $mask_attrs .= ' data-mask="' . esc_attr($phone_mask) . '"';
                            
                            // Добавляем data-mask-caret, если указан
                            if (!empty($phone_mask_caret)) {
                                $caret_char = substr((string)$phone_mask_caret, 0, 1);
                                if ($caret_char) {
                                    $mask_attrs .= ' data-mask-caret="' . esc_attr($caret_char) . '"';
                                }
                            }
                            
                            // Добавляем data-mask-soft-caret, если указан
                            if (!empty($phone_mask_soft_caret)) {
                                $soft_caret_char = substr((string)$phone_mask_soft_caret, 0, 1);
                                if ($soft_caret_char) {
                                    $mask_attrs .= ' data-mask-soft-caret="' . esc_attr($soft_caret_char) . '"';
                                }
                            }
                            // data-mask-blur не добавляем (по умолчанию false)
                        }
                    }
                    ?>
                    <div class="form-floating<?php echo $block_class ? ' ' . $block_class : ''; ?>">
                        <input
                            type="<?php echo esc_attr($field_type); ?>"
                            class="form-control<?php echo esc_attr($form_radius_class); ?>"
                            id="<?php echo esc_attr($field_id); ?>"
                            name="<?php echo esc_attr($field_name); ?>"
                            placeholder="<?php echo esc_attr($placeholder ?: $field_label); ?>"
                            value="<?php echo esc_attr($default_value); ?>"
                            <?php echo $required_attr; ?>
                            <?php echo $max_length > 0 ? 'maxlength="' . $max_length . '"' : ''; ?>
                            <?php echo $min_length > 0 ? 'minlength="' . $min_length . '"' : ''; ?>
                            <?php echo $mask_attrs; ?>
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


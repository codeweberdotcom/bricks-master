<?php
/**
 * CodeWeber Forms Built-in Forms Settings
 * 
 * Admin page for managing consent settings for all built-in forms
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

class CodeweberFormsBuiltinSettings {
    private $option_name = 'builtin_form_consents';
    
    /**
     * Get list of available built-in forms
     */
    private function get_builtin_forms() {
        return [
            'testimonial' => __('Testimonial Form', 'codeweber'),
            'resume' => __('Resume Form', 'codeweber'),
            'newsletter' => __('Newsletter Subscription', 'codeweber'),
            'callback' => __('Callback Request', 'codeweber'),
        ];
    }
    
    /**
     * НОВОЕ: Получить формы из CPT, сгруппированные по типу
     * 
     * @return array Массив [form_type => [WP_Post, ...]]
     */
    private function get_cpt_forms_by_type() {
        $forms_by_type = [
            'newsletter' => [],
            'testimonial' => [],
            'resume' => [],
            'callback' => [],
        ];
        
        // Получаем все формы из CPT
        $cpt_forms = get_posts([
            'post_type' => 'codeweber_form',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ]);
        
        foreach ($cpt_forms as $form_post) {
            // Получаем тип формы
            $form_type = 'form'; // По умолчанию
            if (class_exists('CodeweberFormsCore')) {
                $form_type = CodeweberFormsCore::get_form_type($form_post->ID);
            } else {
                $form_type = get_post_meta($form_post->ID, '_form_type', true) ?: 'form';
            }
            
            // Добавляем только формы специальных типов (не обычные)
            if (isset($forms_by_type[$form_type])) {
                $forms_by_type[$form_type][] = $form_post;
            }
        }
        
        return $forms_by_type;
    }
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        // Migrate old testimonial settings if needed
        add_action('admin_init', [$this, 'migrate_old_settings']);
        // Add AJAX handler for default labels
        add_action('wp_ajax_codeweber_forms_get_default_label', [$this, 'ajax_get_default_label']);
    }
    
    /**
     * Migrate old testimonial settings to new structure
     */
    public function migrate_old_settings() {
        $old_option = get_option('testimonial_form_consents', []);
        if (!empty($old_option) && is_array($old_option)) {
            $new_option = get_option($this->option_name, []);
            if (empty($new_option['testimonial'])) {
                // Migrate old structure to new
                $migrated = [];
                foreach ($old_option as $consent) {
                    // Convert old structure to new (simplified)
                    $migrated[] = [
                        'label' => $consent['label'] ?? '',
                        'document_id' => !empty($consent['document_id']) ? intval($consent['document_id']) : 0,
                        'required' => !empty($consent['required']),
                    ];
                }
                if (!empty($migrated)) {
                    $new_option['testimonial'] = $migrated;
                    update_option($this->option_name, $new_option);
                    // Optionally delete old option after migration
                    // delete_option('testimonial_form_consents');
                }
            }
        }
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Делаем страницу настроек встроенных форм дочерним пунктом меню CPT "Формы"
        $parent_slug = 'edit.php?post_type=codeweber_form';

        add_submenu_page(
            $parent_slug,
            __('Настройки встроенных форм', 'codeweber'),
            __('Настройки встроенных форм', 'codeweber'),
            'manage_options',
            'codeweber-forms-builtin-settings',
            [$this, 'render_page']
        );
    }
    
    /**
     * Render built-in forms settings page
     */
    public function render_page() {
        // Handle form submission
        if (isset($_POST['save_builtin_settings']) && wp_verify_nonce($_POST['builtin_consents_nonce'], 'save_builtin_consents')) {
            $this->save_settings();
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved!', 'codeweber') . '</p></div>';
        }
        
        $builtin_forms = $this->get_builtin_forms();
        
        // НОВОЕ: Получаем формы из CPT с соответствующими типами
        $cpt_forms_by_type = $this->get_cpt_forms_by_type();
        
        // Get all saved consents for all forms
        $all_consents = get_option($this->option_name, []);
        if (!is_array($all_consents)) {
            $all_consents = [];
        }
        
        // Get available documents (Privacy Policy + Legal documents)
        $all_documents = $this->get_all_documents();
        
        ?>
        <div class="wrap">
            <h1><?php _e('Built-in Forms Settings', 'codeweber'); ?></h1>
            <p class="description"><?php _e('Configure consent settings for built-in form types. Forms from CPT with matching types are also shown here.', 'codeweber'); ?></p>
            
            <form method="post" action="">
                <?php wp_nonce_field('save_builtin_consents', 'builtin_consents_nonce'); ?>
                
                <div id="forms-container">
                    <?php
                    // Render legacy built-in forms
                    foreach ($builtin_forms as $form_key => $form_label) {
                        $consents = isset($all_consents[$form_key]) ? $all_consents[$form_key] : [];
                        if (!is_array($consents)) {
                            $consents = [];
                        }
                        $this->render_form_block($form_key, $form_label, $consents, $all_documents, $builtin_forms, true);
                    }
                    
                    // НОВОЕ: Render CPT forms grouped by type
                    foreach ($cpt_forms_by_type as $form_type => $forms) {
                        foreach ($forms as $form_post) {
                            $form_key = 'cpt_' . $form_post->ID;
                            $form_label = $form_post->post_title . ' (ID: ' . $form_post->ID . ')';
                            $consents = isset($all_consents[$form_key]) ? $all_consents[$form_key] : [];
                            if (!is_array($consents)) {
                                $consents = [];
                            }
                            $this->render_form_block($form_key, $form_label, $consents, $all_documents, $builtin_forms, false, $form_type, $form_post->ID);
                        }
                    }
                    ?>
                </div>
                
                <p>
                    <button type="button" class="button" id="add-form-block-btn">
                        <?php _e('+ Add Form', 'codeweber'); ?>
                    </button>
                </p>
                
                <p class="submit">
                    <input type="submit" name="save_builtin_settings" class="button button-primary" value="<?php _e('Save Settings', 'codeweber'); ?>">
                </p>
            </form>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            var allDocuments = <?php echo json_encode($all_documents); ?>;
            var builtinForms = <?php echo json_encode($builtin_forms); ?>;
            var formBlockIndex = <?php echo count($builtin_forms); ?>;

            // Add new form block
            $('#add-form-block-btn').on('click', function() {
                var formBlock = createFormBlock(formBlockIndex, '', [], allDocuments, builtinForms);
                $('#forms-container').append(formBlock);
                formBlockIndex++;
            });
            
            // Remove form block
            $(document).on('click', '.remove-form-block-btn', function() {
                var $formBlock = $(this).closest('.form-block');
                // Проверяем form_key из скрытого поля или из select
                var formKey = $formBlock.find('input[name*="[form_key]"]').val() || 
                              $formBlock.find('select[name*="[form_key]"]').val();
                
                // Запрещаем удаление встроенных форм
                var builtinFormKeys = ['testimonial', 'resume', 'newsletter', 'callback'];
                if (builtinFormKeys.indexOf(formKey) !== -1) {
                    alert('<?php _e('Built-in forms cannot be deleted. You can only modify their consent settings.', 'codeweber'); ?>');
                    return false;
                }
                
                if (confirm('<?php _e('Are you sure you want to remove this form and all its consents?', 'codeweber'); ?>')) {
                    $formBlock.remove();
                }
            });
            
            // Add consent to form block
            $(document).on('click', '.add-consent-btn', function() {
                var $formBlock = $(this).closest('.form-block');
                var formIndex = $formBlock.data('form-index');
                var consentIndex = $formBlock.find('.consent-row').length;
                var consentRow = createConsentRow(formIndex, consentIndex, null, allDocuments);
                $formBlock.find('.consents-list').append(consentRow);
            });
            
            // Remove consent row
            $(document).on('click', '.remove-consent-btn', function() {
                $(this).closest('.consent-row').remove();
            });
            
            // Обработка изменения выбора формы - скрываем/показываем кнопку удаления
            $(document).on('change', 'select.form-key-selector', function() {
                var $formBlock = $(this).closest('.form-block');
                var formKey = $(this).val();
                var builtinFormKeys = ['testimonial', 'resume', 'newsletter', 'callback'];
                var isBuiltin = builtinFormKeys.indexOf(formKey) !== -1;
                var $removeBtn = $formBlock.find('.remove-form-block-btn');
                
                if (isBuiltin) {
                    // Скрываем кнопку удаления и меняем стиль блока
                    $removeBtn.hide();
                    $formBlock.addClass('builtin-form').css('border-color', '#00a32a');
                    $formBlock.find('.cw-accordion-toggle').css('color', '#00a32a');
                } else {
                    // Показываем кнопку удаления и возвращаем обычный стиль
                    $removeBtn.show();
                    $formBlock.removeClass('builtin-form').css('border-color', '#2271b1');
                    $formBlock.find('.cw-accordion-toggle').css('color', '#2271b1');
                }
            });
            
            // Auto-fill label text when document is selected
            $(document).on('change', 'select[name*="[document_id]"]', function() {
                var $select = $(this);
                var $row = $select.closest('.consent-row');
                var $labelInput = $row.find('input[name*="[label]"]');
                var documentId = $select.val();
                
                if (!documentId) {
                    return;
                }
                
                // Ask for confirmation if field already has text
                var hasExistingText = $labelInput.val().trim();
                if (hasExistingText) {
                    if (!confirm('<?php _e('Replace existing label text with default text for this document?', 'codeweber'); ?>')) {
                        return;
                    }
                }
                
                // Show loading state
                var originalValue = $labelInput.val();
                $labelInput.prop('disabled', true);
                
                // Get AJAX URL (ajaxurl is available in WordPress admin)
                var ajaxUrl = (typeof ajaxurl !== 'undefined') ? ajaxurl : '<?php echo admin_url('admin-ajax.php'); ?>';
                
                // Get default text from server
                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'codeweber_forms_get_default_label',
                        document_id: documentId,
                        nonce: '<?php echo wp_create_nonce("codeweber_forms_default_label"); ?>'
                    },
                    success: function(response) {
                        if (response.success && response.data && response.data.label) {
                            $labelInput.val(response.data.label);
                        } else {
                            console.error('Failed to get default label:', response);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', status, error);
                        // Keep original value on error
                        $labelInput.val(originalValue);
                    },
                    complete: function() {
                        $labelInput.prop('disabled', false);
                    }
                });
            });

            // --- Accordion behaviour for form blocks ---

            // Initially collapse all existing blocks (JS управляет видимостью)
            var $existingBlocks = $('#forms-container .form-block');
            $existingBlocks.addClass('is-collapsed').find('.cw-form-body').hide();
            
            // Set initial toggle symbols (+)
            $existingBlocks.each(function() {
                var $block = $(this);
                var $toggle = $block.find('.cw-accordion-toggle').first();
                if (!$toggle.length) {
                    return;
                }
                $toggle.text('+');
            });

            // Toggle block on header click
            $(document).on('click', '.cw-form-header', function(e) {
                // Ignore clicks on interactive elements inside header
                if ($(e.target).closest('button, a, input, select, textarea, label').length) {
                    return;
                }
                var $block = $(this).closest('.form-block');
                var $body = $block.find('.cw-form-body');
                var $toggle = $block.find('.cw-accordion-toggle').first();
                var isCollapsed = $block.hasClass('is-collapsed');

                if (isCollapsed) {
                    // Открываем
                    $block.removeClass('is-collapsed');
                    if ($toggle.length) {
                        $toggle.text('−');
                    }
                    $body.stop(true, true).slideDown(150);
                } else {
                    // Закрываем
                    $block.addClass('is-collapsed');
                    if ($toggle.length) {
                        $toggle.text('+');
                    }
                    $body.stop(true, true).slideUp(150);
                }
            });

            function createFormBlock(formIndex, selectedFormKey, consents, allDocuments, builtinForms) {
                // Проверяем, является ли форма встроенной
                var builtinFormKeys = ['testimonial', 'resume', 'newsletter', 'callback'];
                var isBuiltin = builtinFormKeys.indexOf(selectedFormKey) !== -1;
                var blockClass = isBuiltin ? 'form-block builtin-form is-collapsed' : 'form-block is-collapsed';
                var borderColor = isBuiltin ? '#00a32a' : '#2271b1';
                
                var html = '<div class="' + blockClass + '" data-form-index="' + formIndex + '" data-form-key="' + (selectedFormKey || '') + '" style="border: 2px solid ' + borderColor + '; padding: 20px; margin-bottom: 20px; background: #f0f6fc; border-radius: 4px;">';

                // Clickable header
                html += '<div class="cw-form-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; cursor: pointer;">';
                html += '<div style="display:flex; align-items:center; gap:6px;">';
                html += '<span class="cw-accordion-toggle" aria-hidden="true">+</span>';
                html += '<h3 style="margin: 0;"><?php _e('Form', 'codeweber'); ?> #' + (formIndex + 1) + '</h3>';
                html += '</div>';
                // Показываем кнопку удаления только для не встроенных форм
                if (!isBuiltin) {
                    html += '<button type="button" class="button remove-form-block-btn"><?php _e('Remove Form', 'codeweber'); ?></button>';
                }
                html += '</div>';

                // Collapsible body
                html += '<div class="cw-form-body" style="display: none;">';

                // Form selector
                html += '<p>';
                html += '<label><strong><?php _e('Select Form:', 'codeweber'); ?></strong><br>';
                html += '<select name="form_blocks[' + formIndex + '][form_key]" class="form-key-selector" style="width: 100%; max-width: 400px;">';
                html += '<option value=""><?php _e('— Select Form —', 'codeweber'); ?></option>';
                for (var key in builtinForms) {
                    html += '<option value="' + key + '" ' + (selectedFormKey === key ? 'selected' : '') + '>' + builtinForms[key] + '</option>';
                }
                html += '</select>';
                html += '</label>';
                html += '</p>';
                
                // Consents list
                html += '<div class="consents-list" style="margin-top: 20px;">';
                if (consents && consents.length > 0) {
                    for (var i = 0; i < consents.length; i++) {
                        html += createConsentRow(formIndex, i, consents[i], allDocuments);
                    }
                }
                html += '</div>';

                // Add consent button
                html += '<p style="margin-top: 15px;">';
                html += '<button type="button" class="button add-consent-btn"><?php _e('+ Add Consent', 'codeweber'); ?></button>';
                html += '</p>';

                html += '</div>'; // .cw-form-body
                html += '</div>'; // .form-block
                return html;
            }
            
            function createConsentRow(formIndex, consentIndex, consent, allDocuments) {
                consent = consent || {};
                var html = '<div class="consent-row" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; background: #fff; border-radius: 4px;">';
                html += '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">';
                html += '<strong><?php _e('Consent', 'codeweber'); ?> #' + (consentIndex + 1) + '</strong>';
                html += '<button type="button" class="button remove-consent-btn"><?php _e('Remove', 'codeweber'); ?></button>';
                html += '</div>';
                
                // Two columns layout
                html += '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">';
                
                // Left column: Label text
                html += '<div>';
                html += '<label><strong><?php _e('Label Text', 'codeweber'); ?>:</strong><br>';
                html += '<input type="text" name="form_blocks[' + formIndex + '][consents][' + consentIndex + '][label]" value="' + (consent.label || '') + '" class="large-text" placeholder="<?php _e('I agree to the {document_title}', 'codeweber'); ?>" style="width: 100%;">';
                html += '</label><br>';
                html += '<small class="description"><?php _e('You can use placeholders: {document_url}, {document_title}, {document_title_url}, {form_id}. For example: "I agree to the {document_title_url}"', 'codeweber'); ?></small>';
                html += '</div>';
                
                // Right column: Document selection
                html += '<div>';
                html += '<label><strong><?php _e('Select Document', 'codeweber'); ?>:</strong><br>';
                html += '<select name="form_blocks[' + formIndex + '][consents][' + consentIndex + '][document_id]" style="width: 100%;">';
                html += '<option value=""><?php _e('— Select —', 'codeweber'); ?></option>';
                if (allDocuments && allDocuments.length > 0) {
                    allDocuments.forEach(function(doc) {
                        html += '<option value="' + doc.id + '" ' + (consent.document_id == doc.id ? 'selected' : '') + '>' + doc.title + (doc.type ? ' (' + doc.type + ')' : '') + '</option>';
                    });
                }
                html += '</select>';
                html += '</label>';
                html += '</div>';
                
                html += '</div>'; // End of grid
                
                // Required checkbox (full width)
                html += '<p>';
                html += '<label>';
                html += '<input type="checkbox" name="form_blocks[' + formIndex + '][consents][' + consentIndex + '][required]" value="1" ' + (consent.required ? 'checked' : '') + '>';
                html += ' <?php _e('Required (form cannot be submitted without this consent)', 'codeweber'); ?>';
                html += '</label>';
                html += '</p>';
                
                html += '</div>';
                return html;
            }
        });
        </script>
        
        <style>
            .form-block {
                border: 2px solid #2271b1;
                padding: 20px;
                margin-bottom: 20px;
                background: #f0f6fc;
                border-radius: 4px;
            }
            .form-block.builtin-form {
                border-color: #00a32a;
            }
            .form-block.builtin-form .cw-accordion-toggle {
                color: #00a32a;
            }
            .consent-row {
                border: 1px solid #ddd;
                padding: 15px;
                margin-bottom: 15px;
                background: #fff;
                border-radius: 4px;
            }
            .consent-row .consent-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
                margin-bottom: 15px;
            }
            @media (max-width: 782px) {
                .consent-row .consent-grid {
                    grid-template-columns: 1fr;
                }
            }
            .cw-form-header {
                cursor: pointer;
            }
            .cw-accordion-toggle {
                display: inline-block;
                font-weight: 600;
                margin-right: 2px;
                color: #2271b1;
                font-size: 20px;
            }
        </style>
        <?php
    }
    
    /**
     * Render form block with consents
     * 
     * @param string $form_key Ключ формы (legacy или cpt_ID)
     * @param string $form_label Название формы
     * @param array $consents Массив согласий
     * @param array $all_documents Все доступные документы
     * @param array $builtin_forms Список встроенных форм
     * @param bool $is_builtin Является ли форма встроенной (legacy)
     * @param string|null $form_type Тип формы (для CPT форм)
     * @param int|null $cpt_id ID формы в CPT (для CPT форм)
     */
    private function render_form_block($form_key, $form_label, $consents, $all_documents, $builtin_forms, $is_builtin = false, $form_type = null, $cpt_id = null) {
        static $form_index = 0;
        $form_index++;
        ?>
        <?php
        // Определяем, является ли форма встроенной (legacy)
        $builtin_form_keys = ['testimonial', 'resume', 'newsletter', 'callback'];
        if ($is_builtin === null) {
            $is_builtin = in_array($form_key, $builtin_form_keys);
        }
        $block_class = $is_builtin ? 'form-block builtin-form' : 'form-block';
        $block_style = $is_builtin 
            ? 'border: 2px solid #00a32a; padding: 20px; margin-bottom: 20px; background: #f0f6fc; border-radius: 4px; max-width: 100%;' 
            : 'border: 2px solid #2271b1; padding: 20px; margin-bottom: 20px; background: #f0f6fc; border-radius: 4px; max-width: 100%;';
        ?>
        <div class="<?php echo esc_attr($block_class); ?>" data-form-index="<?php echo $form_index; ?>" data-form-key="<?php echo esc_attr($form_key); ?>" style="<?php echo esc_attr($block_style); ?>">
            <div class="cw-form-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                <div style="display:flex; align-items:center; gap:6px;">
                    <span class="cw-accordion-toggle" aria-hidden="true">+</span>
                    <h3 style="margin: 0;">
                        <?php 
                        // Показываем название формы и её внутренний ключ (form_key),
                        // который используется как internalName и в плейсхолдере {form_id}.
                        echo esc_html($form_label); 
                        echo ' ';
                        printf('(ID: %s)', esc_html($form_key));
                        if ($is_builtin) {
                            echo ' <span style="color: #00a32a; font-size: 0.9em; font-weight: normal;">(' . __('Built-in form', 'codeweber') . ')</span>';
                        }
                        ?>
                    </h3>
                </div>
                <?php if (!$is_builtin): ?>
                <button type="button" class="button remove-form-block-btn"><?php _e('Remove Form', 'codeweber'); ?></button>
                <?php endif; ?>
            </div>

            <div class="cw-form-body">
                <p class="description" style="margin: 4px 0 12px 4px;">
                    <?php
                    // НОВОЕ: Шорткод зависит от типа формы (CPT или legacy)
                    $shortcode_id = $cpt_id ? $cpt_id : $form_key;
                    printf(
                        __('Shortcode for this form: %s', 'codeweber'),
                        '<code>[codeweber_form id=&quot;' . esc_attr($shortcode_id) . '&quot;]</code>'
                    );
                    echo '<br>';
                    // Пример с использованием name и title
                    printf(
                        __('With custom name and title: %s', 'codeweber'),
                        '<code>[codeweber_form id=&quot;' . esc_attr($shortcode_id) . '&quot; name=&quot;Form name here&quot; title=&quot;Form title here&quot;]</code>'
                    );
                    ?>
                </p>
                
                <input type="hidden" name="form_blocks[<?php echo $form_index; ?>][form_key]" value="<?php echo esc_attr($form_key); ?>">
                <?php if ($cpt_id): ?>
                <input type="hidden" name="form_blocks[<?php echo $form_index; ?>][cpt_id]" value="<?php echo esc_attr($cpt_id); ?>">
                <input type="hidden" name="form_blocks[<?php echo $form_index; ?>][form_type]" value="<?php echo esc_attr($form_type); ?>">
                <?php endif; ?>
                
                <div class="consents-list" style="margin-top: 20px;">
                    <?php if (!empty($consents)): ?>
                        <?php foreach ($consents as $consent_index => $consent): ?>
                            <?php $this->render_consent_row_in_block($form_index, $consent_index, $consent, $all_documents); ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <p style="margin-top: 15px;">
                    <button type="button" class="button add-consent-btn"><?php _e('+ Add Consent', 'codeweber'); ?></button>
                </p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render consent row in form block
     */
    private function render_consent_row_in_block($form_index, $consent_index, $consent, $all_documents) {
        ?>
        <div class="consent-row" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; background: #fff; border-radius: 4px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <strong><?php _e('Consent', 'codeweber'); ?> #<?php echo ($consent_index + 1); ?></strong>
                <button type="button" class="button remove-consent-btn"><?php _e('Remove', 'codeweber'); ?></button>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
                <div>
                    <label><strong><?php _e('Label Text', 'codeweber'); ?>:</strong><br>
                        <input type="text" name="form_blocks[<?php echo $form_index; ?>][consents][<?php echo $consent_index; ?>][label]" value="<?php echo esc_attr($consent['label'] ?? ''); ?>" class="large-text" placeholder="<?php _e('I agree to the {document_title}', 'codeweber'); ?>" style="width: 100%;">
                    </label><br>
                    <small class="description"><?php _e('You can use placeholders: {document_url}, {document_title}, {document_title_url}, {form_id}. For example: "I agree to the {document_title_url}"', 'codeweber'); ?></small>
                </div>
                
                <div>
                    <label><strong><?php _e('Select Document', 'codeweber'); ?>:</strong><br>
                        <select name="form_blocks[<?php echo $form_index; ?>][consents][<?php echo $consent_index; ?>][document_id]" style="width: 100%;">
                            <option value=""><?php _e('— Select —', 'codeweber'); ?></option>
                            <?php foreach ($all_documents as $doc): ?>
                                <option value="<?php echo esc_attr($doc['id']); ?>" <?php selected($consent['document_id'] ?? '', $doc['id']); ?>>
                                    <?php echo esc_html($doc['title']); ?><?php echo $doc['type'] ? ' (' . esc_html($doc['type']) . ')' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
            </div>
            
            <p>
                <label>
                    <input type="checkbox" name="form_blocks[<?php echo $form_index; ?>][consents][<?php echo $consent_index; ?>][required]" value="1" <?php checked(!empty($consent['required'])); ?>>
                    <?php _e('Required (form cannot be submitted without this consent)', 'codeweber'); ?>
                </label>
            </p>
        </div>
        <?php
    }
    
    /**
     * Get all available documents (Privacy Policy + Legal documents)
     */
    private function get_all_documents() {
        $documents = [];
        
        // Get Privacy Policy page
        $privacy_policy_page_id = (int) get_option('wp_page_for_privacy_policy');
        if ($privacy_policy_page_id) {
            $privacy_page = get_post($privacy_policy_page_id);
            if ($privacy_page) {
                $documents[] = [
                    'id' => $privacy_page->ID,
                    'title' => $privacy_page->post_title,
                    'type' => __('Privacy Policy', 'codeweber')
                ];
            }
        }
        
        // Get Legal documents
        $legal_documents = get_posts([
            'post_type' => 'legal',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC',
        ]);
        
        foreach ($legal_documents as $doc) {
            $documents[] = [
                'id' => $doc->ID,
                'title' => $doc->post_title,
                'type' => __('Legal Document', 'codeweber')
            ];
        }
        
        return $documents;
    }
    
    /**
     * Save settings
     */
    private function save_settings() {
        $builtin_forms = $this->get_builtin_forms();
        $all_consents = [];
        
        // Получаем существующие настройки для встроенных форм (защита от удаления)
        $existing_consents = get_option($this->option_name, []);
        if (!is_array($existing_consents)) {
            $existing_consents = [];
        }
        
        if (isset($_POST['form_blocks']) && is_array($_POST['form_blocks'])) {
            // Process each form block
            foreach ($_POST['form_blocks'] as $block) {
                $form_key = sanitize_text_field($block['form_key'] ?? '');
                $cpt_id = !empty($block['cpt_id']) ? intval($block['cpt_id']) : null;
                
                // НОВОЕ: Валидация для legacy и CPT форм
                if (empty($form_key)) {
                    continue;
                }
                
                // Проверяем, является ли это legacy формой
                $is_legacy = isset($builtin_forms[$form_key]);
                
                // Если это CPT форма, проверяем существование
                if (!$is_legacy && $cpt_id) {
                    $form_post = get_post($cpt_id);
                    if (!$form_post || $form_post->post_type !== 'codeweber_form') {
                        continue;
                    }
                } elseif (!$is_legacy) {
                    // Если не legacy и нет CPT ID, пропускаем
                    continue;
                }
                
                // Process consents for this form
                $consents = [];
                if (isset($block['consents']) && is_array($block['consents'])) {
                    foreach ($block['consents'] as $consent) {
                        if (!empty($consent['label'])) {
                            // Allow HTML tags like <a>, <strong>, <em> etc. for links in labels
                            $consents[] = [
                                'label' => wp_kses_post($consent['label']),
                                'document_id' => !empty($consent['document_id']) ? intval($consent['document_id']) : 0,
                                'required' => !empty($consent['required']),
                            ];
                        }
                    }
                }
                
                // Store consents for this form (overwrite if form already exists)
                $all_consents[$form_key] = $consents;
            }
        }
        
        // Всегда сохраняем встроенные формы, даже если они не были в POST
        // Это защищает от случайного удаления
        foreach ($builtin_forms as $form_key => $form_label) {
            if (!isset($all_consents[$form_key])) {
                // Если форма не была в POST, используем существующие настройки или пустой массив
                $all_consents[$form_key] = isset($existing_consents[$form_key]) ? $existing_consents[$form_key] : [];
            }
        }
        
        // Save all consents
        update_option($this->option_name, $all_consents);
    }
    
    /**
     * Get default consent label text for a document by ID
     * 
     * @param int $document_id Document ID
     * @return string Default label text with placeholders
     */
    private function get_default_consent_label($document_id) {
        if (empty($document_id)) {
            return '';
        }
        
        $document_id = intval($document_id);
        $document_type = codeweber_forms_get_document_type($document_id);
        $document_title = codeweber_forms_get_document_title($document_id);
        
        // Allow filtering for custom texts (for dynamic addition)
        $custom_texts = apply_filters('codeweber_forms_custom_consent_labels', []);
        if (isset($custom_texts[$document_id])) {
            return $custom_texts[$document_id];
        }
        
        // Check for specific documents by title (case-insensitive)
        if ($document_title) {
            $title_lower = mb_strtolower($document_title, 'UTF-8');
            
            // Check for mailing consent document
            if (strpos($title_lower, 'рассылк') !== false || 
                strpos($title_lower, 'mailing') !== false || 
                strpos($title_lower, 'newsletter') !== false ||
                (strpos($title_lower, 'информационн') !== false && strpos($title_lower, 'рекламн') !== false)) {
                return __('I agree to <a href="{document_url}">receive informational and advertising mailings</a>.', 'codeweber');
            }
            
            // User Agreement / Пользовательское соглашение
            if (strpos($title_lower, 'пользовательск') !== false || 
                strpos($title_lower, 'user agreement') !== false ||
                strpos($title_lower, 'terms of use') !== false ||
                strpos($title_lower, 'условия использован') !== false) {
                return __('I agree to the <a href="{document_url}">terms of use</a>.', 'codeweber');
            }
            
            // Public Offer Agreement / Договор публичной оферты
            if (strpos($title_lower, 'публичн') !== false && strpos($title_lower, 'оферт') !== false ||
                strpos($title_lower, 'public offer') !== false ||
                strpos($title_lower, 'договор') !== false && strpos($title_lower, 'оферт') !== false) {
                return __('I agree to the <a href="{document_url}">public offer agreement</a>.', 'codeweber');
            }
            
            // License Agreement / Лицензионное соглашение
            if (strpos($title_lower, 'лицензионн') !== false ||
                strpos($title_lower, 'license agreement') !== false ||
                strpos($title_lower, 'licensing') !== false) {
                return __('I agree to the <a href="{document_url}">license agreement</a>.', 'codeweber');
            }
            
            // Cookie Policy / Политика использования файлов Cookie
            if (strpos($title_lower, 'cookie') !== false ||
                strpos($title_lower, 'куки') !== false ||
                strpos($title_lower, 'файлов cookie') !== false) {
                return __('I agree to the <a href="{document_url}">cookie policy</a>.', 'codeweber');
            }
            
            // Return Policy / Условия возврата
            if (strpos($title_lower, 'возврат') !== false ||
                strpos($title_lower, 'return policy') !== false ||
                strpos($title_lower, 'refund') !== false) {
                return __('I agree to the <a href="{document_url}">return policy</a>.', 'codeweber');
            }
            
            // Delivery Terms / Условия доставки
            if (strpos($title_lower, 'доставк') !== false ||
                strpos($title_lower, 'delivery') !== false ||
                strpos($title_lower, 'shipping') !== false) {
                return __('I agree to the <a href="{document_url}">delivery terms</a>.', 'codeweber');
            }
            
            // Personal Data Processing Consent / Согласие на обработку персональных данных
            if ((strpos($title_lower, 'согласие') !== false && strpos($title_lower, 'персональн') !== false) ||
                strpos($title_lower, 'personal data') !== false && strpos($title_lower, 'consent') !== false) {
                return __('I <a href="{document_url}">consent</a> to the processing of my personal data.', 'codeweber');
            }
        }
        
        // Default texts based on document type
        $type_defaults = [
            'privacy_policy' => __('I have read the <a href="{document_url}">personal data processing policy document.</a>', 'codeweber'),
            'legal' => __('I <a href="{document_url}">consent</a> to the processing of my personal data.', 'codeweber'),
        ];
        
        if (isset($type_defaults[$document_type])) {
            return $type_defaults[$document_type];
        }
        
        // Ultimate fallback
        return __('I agree to the {document_title_url}.', 'codeweber');
    }
    
    /**
     * AJAX handler to get default consent label for a document
     */
    public function ajax_get_default_label() {
        // Check nonce - try both AJAX nonce and REST API nonce
        $nonce_valid = false;
        if (isset($_POST['nonce'])) {
            // Try AJAX nonce first
            $nonce_valid = wp_verify_nonce($_POST['nonce'], 'codeweber_forms_default_label');
            // If AJAX nonce fails, try REST API nonce (for Gutenberg editor)
            if (!$nonce_valid && function_exists('wp_verify_nonce')) {
                $nonce_valid = wp_verify_nonce($_POST['nonce'], 'wp_rest');
            }
        }
        
        // For Gutenberg editor, also check user capability
        if (!$nonce_valid && current_user_can('edit_posts')) {
            // Allow if user can edit posts (Gutenberg editor users)
            $nonce_valid = true;
        }
        
        if (!$nonce_valid) {
            wp_send_json_error(['message' => __('Security check failed', 'codeweber')]);
        }
        
        if (!current_user_can('manage_options') && !current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Unauthorized', 'codeweber')]);
        }
        
        // Загружаем переводы из основного файла темы перед получением метки
        // Это необходимо для правильной локализации текстов согласий
        $locale = get_locale();
        $theme_mofile = get_template_directory() . '/languages/' . $locale . '.mo';
        if (file_exists($theme_mofile)) {
            load_textdomain('codeweber', $theme_mofile);
        }
        
        $document_id = isset($_POST['document_id']) ? intval($_POST['document_id']) : 0;
        
        if (empty($document_id)) {
            wp_send_json_error(['message' => __('Invalid document ID', 'codeweber')]);
        }
        
        $default_label = $this->get_default_consent_label($document_id);
        
        wp_send_json_success(['label' => $default_label]);
    }
    
    /**
     * Get consents for a specific built-in form
     * 
     * @param string $form_key Form key (testimonial, resume, newsletter, callback)
     * @return array Array of consents
     */
    public static function get_form_consents($form_key) {
        $all_consents = get_option('builtin_form_consents', []);
        return isset($all_consents[$form_key]) ? $all_consents[$form_key] : [];
    }
}


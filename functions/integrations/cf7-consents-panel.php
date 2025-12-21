<?php
/**
 * CF7 Consents Panel
 * 
 * Добавляет вкладку "Согласия" в редактор Contact Form 7
 * с функционалом повторителя согласий, аналогичным блоку form-field типа consents_block
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

class CF7_Consents_Panel {
    
    private $meta_key = '_cf7_consents';
    
    public function __construct() {
        // Добавляем вкладку в редактор CF7
        add_filter('wpcf7_editor_panels', [$this, 'add_consents_panel'], 10, 1);
        
        // Сохраняем согласия при сохранении формы
        add_action('wpcf7_save_contact_form', [$this, 'save_consents'], 10, 1);
        
        // Подключаем скрипты и стили для админки
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts'], 10, 1);
        
        // Добавляем кнопку "Consents" в панель формы
        add_action('admin_footer', [$this, 'add_consents_button_to_form_panel'], 20);
        
        // AJAX обработчик для получения дефолтного текста метки
        add_action('wp_ajax_cf7_consents_get_default_label', [$this, 'ajax_get_default_label']);
        
        // Обработка шорткода [cf7_consent_checkbox] в форме CF7
        add_filter('wpcf7_form_elements', [$this, 'process_consent_checkbox_shortcode'], 20, 1);
        
        // Обертка для acceptance полей согласий после обработки CF7
        // ВРЕМЕННО ОТКЛЮЧЕНО для диагностики - раскомментируйте после проверки
        // add_filter('wpcf7_form_elements', [$this, 'wrap_consent_acceptance_fields'], 30, 1);
    }
    
    /**
     * Подключает скрипты и стили для админки
     */
    public function enqueue_scripts($hook) {
        // Только на странице редактирования CF7 формы
        $cf7_pages = ['contact_page_wpcf7-new', 'toplevel_page_wpcf7'];
        if (!in_array($hook, $cf7_pages)) {
            return;
        }
        
        // Получаем ID формы из URL
        $form_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
        
        // Получаем согласия и документы
        $consents = $form_id ? $this->get_consents($form_id) : [];
        $all_documents = $this->get_all_documents();
        
        // Регистрируем пустой скрипт для inline кода
        wp_register_script('cf7-consents-panel', '', ['jquery'], '1.0.0', true);
        wp_enqueue_script('cf7-consents-panel');
        
        // Добавляем inline скрипт
        $script = $this->get_inline_script($consents, $all_documents);
        wp_add_inline_script('cf7-consents-panel', $script);
        
        // Регистрируем пустой стиль для inline CSS
        wp_register_style('cf7-consents-panel', '', [], '1.0.0');
        wp_enqueue_style('cf7-consents-panel');
        
        // Добавляем inline стили
        $style = $this->get_inline_styles();
        wp_add_inline_style('cf7-consents-panel', $style);
    }
    
    /**
     * Генерирует inline JavaScript
     */
    private function get_inline_script($consents, $all_documents) {
        $consent_index = !empty($consents) ? count($consents) : 0;
        $documents_json = wp_json_encode($all_documents, JSON_UNESCAPED_UNICODE);
        $remove_confirm = esc_js(__('Are you sure you want to remove this consent?', 'codeweber'));
        $replace_confirm = esc_js(__('Replace existing label text with default text for this document?', 'codeweber'));
        $consent_label = esc_js(__('Consent', 'codeweber'));
        $remove_label = esc_js(__('Remove', 'codeweber'));
        $label_text_label = esc_js(__('Label Text', 'codeweber'));
        $label_placeholder = esc_js(__('I agree to the {document_title}', 'codeweber'));
        $label_description = esc_js(__('You can use placeholders: {document_url}, {document_title}, {document_title_url}, {form_id}. For example: "I agree to the {document_title_url}"', 'codeweber'));
        $select_document_label = esc_js(__('Select Document', 'codeweber'));
        $select_option = esc_js(__('— Select —', 'codeweber'));
        $required_label = esc_js(__('Required (form cannot be submitted without this consent)', 'codeweber'));
        $ajax_url = esc_js(admin_url('admin-ajax.php'));
        $ajax_nonce = wp_create_nonce('cf7_consents_default_label');
        
        return "
        jQuery(document).ready(function($) {
            var consentIndex = {$consent_index};
            var allDocuments = {$documents_json};
            
            // Добавление нового согласия
            $('#cf7-add-consent').on('click', function() {
                var consentRow = createConsentRow(consentIndex, {}, allDocuments);
                $('#cf7-consents-list').append(consentRow);
                consentIndex++;
            });
            
                // Удаление согласия
                $(document).on('click', '.cf7-remove-consent', function() {
                    if (confirm('{$remove_confirm}')) {
                        $(this).closest('.cf7-consent-row').remove();
                    }
                });
                
                // Автозаполнение метки при выборе документа
                $(document).on('change', 'select[name*=\"[document_id]\"]', function() {
                    var \$select = \$(this);
                    var \$row = \$select.closest('.cf7-consent-row');
                    var \$labelInput = \$row.find('input[name*=\"[label]\"]');
                    var documentId = \$select.val();
                    
                    if (!documentId) {
                        return;
                    }
                    
                    // Спрашиваем подтверждение, если поле уже заполнено
                    var hasExistingText = \$labelInput.val().trim();
                    if (hasExistingText) {
                        if (!confirm('{$replace_confirm}')) {
                            return;
                        }
                    }
                    
                    // Показываем состояние загрузки
                    var originalValue = \$labelInput.val();
                    \$labelInput.prop('disabled', true);
                    
                    // Получаем AJAX URL
                    var ajaxUrl = (typeof ajaxurl !== 'undefined') ? ajaxurl : '{$ajax_url}';
                    
                    // Получаем дефолтный текст с сервера
                    \$.ajax({
                        url: ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'cf7_consents_get_default_label',
                            document_id: documentId,
                            nonce: '{$ajax_nonce}'
                        },
                        success: function(response) {
                            if (response.success && response.data && response.data.label) {
                                \$labelInput.val(response.data.label);
                            } else {
                                console.error('Failed to get default label:', response);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX error:', status, error);
                            // Сохраняем оригинальное значение при ошибке
                            \$labelInput.val(originalValue);
                        },
                        complete: function() {
                            \$labelInput.prop('disabled', false);
                        }
                    });
                });
            
            // Функция создания строки согласия
            function createConsentRow(index, consent, documents) {
                consent = consent || {};
                var html = '<div class=\"cf7-consent-row\" style=\"border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; background: #fff; border-radius: 4px;\">';
                
                html += '<div style=\"display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;\">';
                html += '<strong>{$consent_label} #' + (index + 1) + '</strong>';
                html += '<button type=\"button\" class=\"button cf7-remove-consent\">{$remove_label}</button>';
                html += '</div>';
                
                html += '<div style=\"display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;\">';
                
                // Label Text
                html += '<div>';
                html += '<label><strong>{$label_text_label}:</strong><br>';
                var labelValue = consent.label ? $('<div>').text(consent.label).html() : '';
                html += '<input type=\"text\" name=\"cf7_consents[' + index + '][label]\" value=\"' + labelValue + '\" class=\"large-text\" placeholder=\"{$label_placeholder}\" style=\"width: 100%;\">';
                html += '</label><br>';
                html += '<small class=\"description\">{$label_description}</small>';
                html += '</div>';
                
                // Document Selection
                html += '<div>';
                html += '<label><strong>{$select_document_label}:</strong><br>';
                html += '<select name=\"cf7_consents[' + index + '][document_id]\" style=\"width: 100%;\">';
                html += '<option value=\"\">{$select_option}</option>';
                
                if (documents && documents.length > 0) {
                    documents.forEach(function(doc) {
                        var selected = (consent.document_id == doc.id) ? 'selected' : '';
                        var docTitle = $('<div>').text(doc.title).html();
                        var docType = doc.type ? ' (' + $('<div>').text(doc.type).html() + ')' : '';
                        html += '<option value=\"' + doc.id + '\" ' + selected + '>' + docTitle + docType + '</option>';
                    });
                }
                
                html += '</select>';
                html += '</label>';
                html += '</div>';
                
                html += '</div>';
                
                // Required checkbox
                html += '<p>';
                html += '<label>';
                html += '<input type=\"checkbox\" name=\"cf7_consents[' + index + '][required]\" value=\"1\" ' + (consent.required ? 'checked' : '') + '>';
                html += ' {$required_label}';
                html += '</label>';
                html += '</p>';
                
                html += '</div>';
                
                return html;
            }
        });
        ";
    }
    
    /**
     * Генерирует inline CSS
     */
    private function get_inline_styles() {
        return "
        .cf7-consent-row {
            margin-bottom: 15px;
        }
        .cf7-consent-row .description {
            color: #666;
            font-style: italic;
        }
        @media (max-width: 782px) {
            .cf7-consent-row > div[style*='grid'] {
                grid-template-columns: 1fr !important;
            }
        }
        ";
    }
    
    /**
     * Добавляет вкладку "Согласия" в редактор CF7
     * 
     * @param array $panels Массив существующих вкладок
     * @return array Массив вкладок с добавленной вкладкой согласий
     */
    public function add_consents_panel($panels) {
        $panels['consents-panel'] = [
            'title' => __('Consents', 'codeweber'),
            'callback' => [$this, 'render_consents_panel'],
        ];
        
        return $panels;
    }
    
    /**
     * Отображает панель согласий
     * 
     * @param WPCF7_ContactForm $contact_form Объект формы CF7
     */
    public function render_consents_panel($contact_form) {
        $form_id = $contact_form->id();
        
        // Получаем сохраненные согласия
        $consents = $this->get_consents($form_id);
        
        // Получаем список доступных документов
        $all_documents = $this->get_all_documents();
        
        // Nonce для безопасности
        wp_nonce_field('cf7_consents_save', 'cf7_consents_nonce');
        
        ?>
        <div id="cf7-consents-panel">
            <h2><?php _e('Consents', 'codeweber'); ?></h2>
            
            <p class="description">
                <?php _e('Configure consent checkboxes for this form. You can add multiple consents using the repeater below.', 'codeweber'); ?>
            </p>
            
            <div id="cf7-consents-list" style="margin-top: 20px;">
                <?php if (!empty($consents)): ?>
                    <?php foreach ($consents as $index => $consent): ?>
                        <?php $this->render_consent_row($index, $consent, $all_documents); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <p>
                <button type="button" id="cf7-add-consent" class="button button-secondary">
                    <?php _e('Add Consent', 'codeweber'); ?>
                </button>
            </p>
        </div>
        <?php
    }
    
    /**
     * Отображает строку согласия (для PHP-рендеринга)
     * 
     * @param int $index Индекс согласия
     * @param array $consent Данные согласия
     * @param array $all_documents Список всех документов
     */
    private function render_consent_row($index, $consent, $all_documents) {
        ?>
        <div class="cf7-consent-row" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; background: #fff; border-radius: 4px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <strong><?php _e('Consent', 'codeweber'); ?> #<?php echo ($index + 1); ?></strong>
                <button type="button" class="button cf7-remove-consent"><?php _e('Remove', 'codeweber'); ?></button>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
                <div>
                    <label><strong><?php _e('Label Text', 'codeweber'); ?>:</strong><br>
                        <input type="text" name="cf7_consents[<?php echo $index; ?>][label]" value="<?php echo esc_attr($consent['label'] ?? ''); ?>" class="large-text" placeholder="<?php _e('I agree to the {document_title}', 'codeweber'); ?>" style="width: 100%;">
                    </label><br>
                    <small class="description"><?php _e('You can use placeholders: {document_url}, {document_title}, {document_title_url}, {form_id}. For example: "I agree to the {document_title_url}"', 'codeweber'); ?></small>
                </div>
                
                <div>
                    <label><strong><?php _e('Select Document', 'codeweber'); ?>:</strong><br>
                        <select name="cf7_consents[<?php echo $index; ?>][document_id]" style="width: 100%;">
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
                    <input type="checkbox" name="cf7_consents[<?php echo $index; ?>][required]" value="1" <?php checked(!empty($consent['required'])); ?>>
                    <?php _e('Required (form cannot be submitted without this consent)', 'codeweber'); ?>
                </label>
            </p>
        </div>
        <?php
    }
    
    /**
     * Сохраняет согласия формы
     * 
     * @param WPCF7_ContactForm $contact_form Объект формы CF7
     */
    public function save_consents($contact_form) {
        // Проверка nonce
        if (!isset($_POST['cf7_consents_nonce']) || !wp_verify_nonce($_POST['cf7_consents_nonce'], 'cf7_consents_save')) {
            return;
        }
        
        // Проверка прав
        if (!current_user_can('wpcf7_edit_contact_form', $contact_form->id())) {
            return;
        }
        
        $form_id = $contact_form->id();
        $consents = [];
        
        // Получаем данные согласий из POST
        if (isset($_POST['cf7_consents']) && is_array($_POST['cf7_consents'])) {
            foreach ($_POST['cf7_consents'] as $consent_data) {
                // Валидация: должны быть заполнены label и document_id
                if (empty($consent_data['label']) || empty($consent_data['document_id'])) {
                    continue;
                }
                
                $consents[] = [
                    'label' => sanitize_text_field($consent_data['label']),
                    'document_id' => intval($consent_data['document_id']),
                    'required' => !empty($consent_data['required']),
                ];
            }
        }
        
        // Сохраняем в метаполе формы (всегда обновляем, даже если массив пустой)
        update_post_meta($form_id, $this->meta_key, $consents);
    }
    
    /**
     * Получает согласия формы
     * 
     * @param int $form_id ID формы CF7
     * @return array Массив согласий
     */
    public function get_consents($form_id) {
        $consents = get_post_meta($form_id, $this->meta_key, true);
        
        if (!is_array($consents)) {
            return [];
        }
        
        return $consents;
    }
    
    /**
     * Получает все доступные документы (Privacy Policy + Legal documents)
     * 
     * @return array Массив документов с ключами: id, title, type
     */
    private function get_all_documents() {
        $documents = [];
        $added_ids = []; // Для отслеживания добавленных ID и предотвращения дубликатов
        
        // Get Privacy Policy page
        $privacy_policy_page_id = (int) get_option('wp_page_for_privacy_policy');
        if ($privacy_policy_page_id && !in_array($privacy_policy_page_id, $added_ids)) {
            $privacy_page = get_post($privacy_policy_page_id);
            if ($privacy_page) {
                $documents[] = [
                    'id' => $privacy_page->ID,
                    'title' => $privacy_page->post_title,
                    'type' => __('Privacy Policy', 'codeweber')
                ];
                $added_ids[] = $privacy_page->ID;
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
            // Пропускаем дубликаты
            if (in_array($doc->ID, $added_ids)) {
                continue;
            }
            
            $documents[] = [
                'id' => $doc->ID,
                'title' => $doc->post_title,
                'type' => __('Legal Document', 'codeweber')
            ];
            $added_ids[] = $doc->ID;
        }
        
        return $documents;
    }
    
    /**
     * Получает дефолтный текст метки согласия для документа
     * 
     * @param int $document_id ID документа
     * @return string Дефолтный текст метки с плейсхолдерами
     */
    private function get_default_consent_label($document_id) {
        if (empty($document_id)) {
            return '';
        }
        
        // Проверяем, доступны ли функции из codeweber-forms
        if (!function_exists('codeweber_forms_get_document_type') || !function_exists('codeweber_forms_get_document_title')) {
            // Fallback: используем простой текст
            return __('I agree to the {document_title_url}.', 'codeweber');
        }
        
        $document_id = intval($document_id);
        $document_type = codeweber_forms_get_document_type($document_id);
        $document_title = codeweber_forms_get_document_title($document_id);
        
        // Allow filtering for custom texts
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
            if ((strpos($title_lower, 'публичн') !== false && strpos($title_lower, 'оферт') !== false) ||
                strpos($title_lower, 'public offer') !== false ||
                (strpos($title_lower, 'договор') !== false && strpos($title_lower, 'оферт') !== false)) {
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
                (strpos($title_lower, 'personal data') !== false && strpos($title_lower, 'consent') !== false)) {
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
     * AJAX обработчик для получения дефолтного текста метки
     */
    public function ajax_get_default_label() {
        // Проверка nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cf7_consents_default_label')) {
            wp_send_json_error(['message' => __('Security check failed', 'codeweber')]);
        }
        
        // Проверка прав
        if (!current_user_can('wpcf7_edit_contact_form')) {
            wp_send_json_error(['message' => __('Unauthorized', 'codeweber')]);
        }
        
        $document_id = isset($_POST['document_id']) ? intval($_POST['document_id']) : 0;
        
        if (empty($document_id)) {
            wp_send_json_error(['message' => __('Invalid document ID', 'codeweber')]);
        }
        
        $default_label = $this->get_default_consent_label($document_id);
        
        wp_send_json_success(['label' => $default_label]);
    }
    
    /**
     * Обрабатывает шорткод [cf7_consent_checkbox] в форме CF7
     * Заменяет его на верстку чекбоксов согласий
     * 
     * @param string $content Содержимое формы CF7
     * @return string Обработанное содержимое
     */
    public function process_consent_checkbox_shortcode($content) {
        // Проверяем, есть ли шорткод в содержимом
        if (strpos($content, '[cf7_consent_checkbox]') === false) {
            return $content;
        }
        
        // Получаем ID формы CF7 из объекта формы
        $contact_form = WPCF7_ContactForm::get_current();
        
        $form_id = null;
        if ($contact_form) {
            $form_id = $contact_form->id();
        }
        
        // Если не удалось получить из объекта формы, пытаемся из содержимого
        if (!$form_id) {
            // Ищем скрытое поле _wpcf7 с ID формы
            if (preg_match('/<input[^>]*name=["\']_wpcf7["\'][^>]*value=["\'](\d+)["\']/', $content, $matches)) {
                $form_id = intval($matches[1]);
            } else {
                // Если не удалось получить ID формы, просто удаляем шорткод
                return str_replace('[cf7_consent_checkbox]', '', $content);
            }
        }
        
        if (!$form_id) {
            return str_replace('[cf7_consent_checkbox]', '', $content);
        }
        
        // Получаем согласия для формы
        $consents = $this->get_consents($form_id);
        
        if (empty($consents)) {
            // Если согласий нет, просто удаляем шорткод
            return str_replace('[cf7_consent_checkbox]', '', $content);
        }
        
        // Генерируем верстку для всех согласий
        $consents_html = $this->render_cf7_consents_html($consents, $form_id);
        
        // Заменяем шорткод на верстку
        return str_replace('[cf7_consent_checkbox]', $consents_html, $content);
    }
    
    /**
     * Генерирует HTML верстку для чекбоксов согласий CF7
     * 
     * @param array $consents Массив согласий
     * @param int $form_id ID формы CF7
     * @return string HTML верстка
     */
    private function render_cf7_consents_html($consents, $form_id) {
        $html = '';
        
        foreach ($consents as $index => $consent) {
            if (empty($consent['label']) || empty($consent['document_id'])) {
                continue;
            }
            
            $document_id = intval($consent['document_id']);
            $required = !empty($consent['required']);
            
            // Получаем документ для slug
            $document = get_post($document_id);
            if (!$document) {
                continue;
            }
            
            // Генерируем slug для имени поля acceptance
            // Используем post_name, если есть, иначе создаем из post_title
            if (!empty($document->post_name)) {
                $document_slug = $document->post_name;
            } else {
                $document_slug = sanitize_title($document->post_title);
            }
            $acceptance_name = 'soglasie-' . $document_slug;
            
            // Генерируем ID для чекбокса
            $checkbox_id = 'flexCheckDefault-' . $document_id;
            
            // Обрабатываем текст метки (заменяем плейсхолдеры на ссылки)
            $label_text = '';
            if (function_exists('codeweber_forms_process_consent_label')) {
                $label_text = codeweber_forms_process_consent_label($consent['label'], $document_id, $form_id);
            } else {
                // Fallback: просто используем текст без обработки
                $label_text = esc_html($consent['label']);
            }
            
            // Формируем CF7 acceptance тег
            // Используем content для текста метки, чтобы CF7 обработал его
            $acceptance_attrs = [];
            $acceptance_attrs[] = $acceptance_name;
            $acceptance_attrs[] = 'id:' . $checkbox_id;
            $acceptance_attrs[] = 'class:form-check-input';
            if ($required) {
                $acceptance_attrs[] = 'required';
            }
            
            // Формируем acceptance тег с текстом метки в content
            // CF7 обработает тег и создаст HTML, затем мы обернем его через фильтр
            $acceptance_tag = '[acceptance ' . implode(' ', $acceptance_attrs) . ']' . $label_text . '[/acceptance]';
            
            // Выводим только тег, обертку добавим через фильтр
            $html .= $acceptance_tag . "\n";
        }
        
        return $html;
    }
    
    /**
     * Обертывает обработанные CF7 acceptance поля согласий в нужную HTML структуру
     * 
     * @param string $content Обработанное содержимое формы CF7
     * @return string Модифицированное содержимое
     */
    public function wrap_consent_acceptance_fields($content) {
        // ВАЖНО: Обернуто в try-catch, чтобы ошибки не ломали форму
        try {
            // Получаем ID формы
            $contact_form = WPCF7_ContactForm::get_current();
            if (!$contact_form) {
                return $content;
            }
            
            $form_id = $contact_form->id();
            $consents = $this->get_consents($form_id);
            
            if (empty($consents)) {
                return $content;
            }
        } catch (Exception $e) {
            // В случае ошибки возвращаем оригинальный контент
            error_log('CF7 Consents Panel: Error in wrap_consent_acceptance_fields: ' . $e->getMessage());
            return $content;
        } catch (Error $e) {
            // В случае фатальной ошибки возвращаем оригинальный контент
            error_log('CF7 Consents Panel: Fatal error in wrap_consent_acceptance_fields: ' . $e->getMessage());
            return $content;
        }
        
        // Для каждого согласия ищем обработанное acceptance поле и оборачиваем его
        foreach ($consents as $consent) {
            try {
                if (empty($consent['document_id'])) {
                    continue;
                }
                
                $document_id = intval($consent['document_id']);
                $document = get_post($document_id);
                if (!$document) {
                    continue;
                }
                
                // Генерируем имя поля и ID
                if (!empty($document->post_name)) {
                    $document_slug = $document->post_name;
                } else {
                    $document_slug = sanitize_title($document->post_title);
                }
                $acceptance_name = 'soglasie-' . $document_slug;
                $checkbox_id = 'flexCheckDefault-' . $document_id;
                
                // Ищем обработанное acceptance поле по data-name атрибуту
                // CF7 создает структуру: <span class="wpcf7-form-control-wrap" data-name="...">
                $pattern = '/(<span[^>]*class="wpcf7-form-control-wrap[^"]*"[^>]*data-name="' . preg_quote($acceptance_name, '/') . '"[^>]*>)(.*?)(<\/span>)/is';
                
                $content = preg_replace_callback($pattern, function($matches) use ($checkbox_id, $consent, $document_id, $form_id) {
                    $full_match = $matches[0]; // Полное совпадение
                    
                    // Извлекаем input из структуры CF7
                    $input_pattern = '/<input[^>]*id="' . preg_quote($checkbox_id, '/') . '"[^>]*>/i';
                    $input_html = '';
                    if (preg_match($input_pattern, $full_match, $input_matches)) {
                        $input_html = $input_matches[0];
                    } else {
                        // Если не нашли по ID, ищем любой input внутри
                        if (preg_match('/<input[^>]*>/i', $full_match, $input_matches)) {
                            $input_html = $input_matches[0];
                        }
                    }
                    
                    // Ищем текст label из span.wpcf7-list-item-label
                    $label_text = '';
                    $label_span_pattern = '/<span[^>]*class="[^"]*wpcf7-list-item-label[^"]*"[^>]*>(.*?)<\/span>/is';
                    if (preg_match($label_span_pattern, $full_match, $label_matches)) {
                        $label_text = trim($label_matches[1]);
                    }
                    
                    // Если label_text пустой, используем обработанный текст из согласия
                    if (empty($label_text) && !empty($consent['label'])) {
                        if (function_exists('codeweber_forms_process_consent_label')) {
                            $label_text = codeweber_forms_process_consent_label($consent['label'], $document_id, $form_id);
                        } else {
                            $label_text = esc_html($consent['label']);
                        }
                    }
                    
                    // Извлекаем только обертку wpcf7-form-control-wrap с input внутри, но без label
                    // Создаем упрощенную структуру с input
                    $wrap_attrs = '';
                    if (preg_match('/<span[^>]*class="wpcf7-form-control-wrap[^"]*"[^>]*>/i', $full_match, $wrap_matches)) {
                        $wrap_attrs = $wrap_matches[0];
                    } else {
                        $wrap_attrs = '<span class="wpcf7-form-control-wrap" data-name="soglasie-' . esc_attr(sanitize_title(get_post($document_id)->post_name ?: get_post($document_id)->post_title)) . '">';
                    }
                    
                    // Формируем новую структуру
                    $wrapped = '<div class="form-check mb-2 fs-12 small-chekbox wpcf7-acceptance">' . "\n";
                    $wrapped .= '  ' . $wrap_attrs . "\n";
                    $wrapped .= '    <span class="wpcf7-form-control wpcf7-acceptance">' . "\n";
                    $wrapped .= '      <span class="wpcf7-list-item">' . "\n";
                    $wrapped .= '        ' . $input_html . "\n";
                    $wrapped .= '      </span>' . "\n";
                    $wrapped .= '    </span>' . "\n";
                    $wrapped .= '  </span>' . "\n";
                    
                    // Добавляем label
                    if (!empty($label_text)) {
                        $wrapped .= '  <label for="' . esc_attr($checkbox_id) . '" class="form-check-label text-start">' . "\n";
                        $wrapped .= '    ' . wp_kses_post($label_text) . "\n";
                        $wrapped .= '  </label>' . "\n";
                    }
                    
                    $wrapped .= '</div>';
                    
                    return $wrapped;
                }, $content);
            } catch (Exception $e) {
                // Логируем ошибку, но продолжаем обработку других согласий
                error_log('CF7 Consents Panel: Error processing consent: ' . $e->getMessage());
                continue;
            } catch (Error $e) {
                // Логируем фатальную ошибку, но продолжаем обработку других согласий
                error_log('CF7 Consents Panel: Fatal error processing consent: ' . $e->getMessage());
                continue;
            }
        }
        
        return $content;
    }
    
    /**
     * Добавляет кнопку "Consents" в панель формы CF7
     */
    public function add_consents_button_to_form_panel() {
        // Проверяем, что мы на странице редактирования CF7 формы
        global $pagenow;
        if ($pagenow !== 'admin.php') {
            return;
        }
        
        // Проверяем параметры страницы
        $page = isset($_GET['page']) ? $_GET['page'] : '';
        if ($page !== 'wpcf7' && $page !== 'wpcf7-new') {
            return;
        }
        
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            function addConsentsButton() {
                var $tagGeneratorList = $('#tag-generator-list');
                
                if ($tagGeneratorList.length && $tagGeneratorList.find('button[data-taggen="consents-button"]').length === 0) {
                    // Создаем кнопку "Consents" в том же стиле, что и кнопки tag generator
                    var $consentsButton = $('<button>', {
                        type: 'button',
                        'data-taggen': 'consents-button',
                        title: '<?php echo esc_js(__('Insert consent checkboxes shortcode', 'codeweber')); ?>',
                        css: {
                            'font-size': '12px',
                            'height': '26px',
                            'line-height': '24px',
                            'margin': '2px',
                            'padding': '0 8px 1px'
                        }
                    }).text('<?php echo esc_js(__('Consents', 'codeweber')); ?>');
                    
                    // Обработчик клика
                    $consentsButton.on('click', function() {
                        var $textarea = $('#wpcf7-form');
                        if ($textarea.length) {
                            var currentValue = $textarea.val();
                            var shortcode = '[cf7_consent_checkbox]';
                            
                            // Добавляем шорткод в конец, если его там еще нет
                            if (currentValue.indexOf(shortcode) === -1) {
                                // Добавляем перенос строки, если текст не пустой
                                if (currentValue.trim()) {
                                    currentValue += '\n\n';
                                }
                                currentValue += shortcode;
                                $textarea.val(currentValue);
                                
                                // Триггерим событие change для сохранения
                                $textarea.trigger('change');
                                
                                // Показываем уведомление
                                if (typeof wp !== 'undefined' && wp.notices) {
                                    wp.notices.createNotice('success', '<?php echo esc_js(__('Consent checkboxes shortcode added to form template.', 'codeweber')); ?>', {
                                        isDismissible: true
                                    });
                                }
                            } else {
                                alert('<?php echo esc_js(__('The [cf7_consent_checkbox] shortcode is already in the form template.', 'codeweber')); ?>');
                            }
                        }
                    });
                    
                    // Добавляем кнопку внутрь списка кнопок tag generator, чтобы она выглядела как остальные
                    $tagGeneratorList.append($consentsButton);
                }
            }
            
            // Пытаемся добавить кнопку сразу
            addConsentsButton();
            
            // Если кнопки tag generator еще не загрузились, ждем и пробуем снова
            if ($('#tag-generator-list').length === 0) {
                var attempts = 0;
                var checkInterval = setInterval(function() {
                    attempts++;
                    addConsentsButton();
                    
                    // Если нашли кнопки или прошло 5 секунд, прекращаем проверку
                    if ($('#tag-generator-list').length > 0 || attempts > 50) {
                        clearInterval(checkInterval);
                    }
                }, 100);
            }
            
            // Также добавляем кнопку при переключении вкладок (если используется AJAX)
            $(document).on('click', '#contact-form-editor-tabs button', function() {
                setTimeout(addConsentsButton, 200);
            });
        });
        </script>
        <?php
    }
}

// Инициализация только если CF7 активен
if (class_exists('WPCF7')) {
    new CF7_Consents_Panel();
}


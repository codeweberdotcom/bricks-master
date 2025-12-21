<?php
/**
 * CodeWeber Forms Consent Metabox
 * 
 * Metabox for managing consents in form editor
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

class CodeweberFormsConsentMetabox {
    
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_metabox']);
        add_action('save_post_codeweber_form', [$this, 'save_consents'], 10, 3);
    }
    
    /**
     * Add metabox
     */
    public function add_metabox() {
        add_meta_box(
            'codeweber_forms_consents',
            __('Consents', 'codeweber'),
            [$this, 'render_metabox'],
            'codeweber_form',
            'normal',
            'high'
        );
    }
    
    /**
     * Render metabox
     */
    public function render_metabox($post) {
        wp_nonce_field('save_consents', 'consents_nonce');
        
        // Get saved consents
        $consents = get_post_meta($post->ID, '_form_consents', true);
        if (!is_array($consents)) {
            $consents = [];
        }
        
        // Get available legal documents
        $legal_documents = get_posts([
            'post_type' => 'legal',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC',
        ]);
        
        // Get privacy policy page
        $privacy_policy_page_id = (int) get_option('wp_page_for_privacy_policy');
        $privacy_policy_page = $privacy_policy_page_id ? get_post($privacy_policy_page_id) : null;
        
        ?>
        <div id="consents-container">
            <p class="description">
                <?php _e('Add consent checkboxes to your form. Each consent can have a custom label with shortcodes for document links.', 'codeweber'); ?>
            </p>
            
            <div id="consents-list">
                <?php if (!empty($consents)): ?>
                    <?php foreach ($consents as $index => $consent): ?>
                        <?php $this->render_consent_row($index, $consent, $legal_documents, $privacy_policy_page); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <p>
                <button type="button" class="button" id="add-consent-btn">
                    <?php _e('Add consent', 'codeweber'); ?>
                </button>
            </p>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            var consentIndex = <?php echo count($consents); ?>;
            var legalDocs = <?php echo json_encode(array_map(function($doc) {
                return ['id' => $doc->ID, 'title' => $doc->post_title];
            }, $legal_documents)); ?>;
            var privacyPage = <?php echo $privacy_policy_page ? json_encode(['id' => $privacy_policy_page->ID, 'title' => $privacy_policy_page->post_title]) : 'null'; ?>;
            
            $('#add-consent-btn').on('click', function() {
                var row = createConsentRow(consentIndex, null, legalDocs, privacyPage);
                $('#consents-list').append(row);
                consentIndex++;
            });
            
            $(document).on('click', '.remove-consent-btn', function() {
                $(this).closest('.consent-row').remove();
            });
            
            function createConsentRow(index, consent, legalDocs, privacyPage) {
                consent = consent || {};
                var html = '<div class="consent-row" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; background: #f9f9f9;">';
                html += '<p><strong><?php _e('Consent', 'codeweber'); ?> #' + (index + 1) + '</strong></p>';
                
                // Label text
                html += '<p>';
                html += '<label><strong><?php _e('Label Text', 'codeweber'); ?>:</strong><br>';
                html += '<input type="text" name="consents[' + index + '][label]" value="' + (consent.label || '') + '" class="large-text consent-label-input" placeholder="<?php _e('I agree to the {document_title}', 'codeweber'); ?>" style="width: 100%;">';
                html += '</label><br>';
                html += '<small class="description"><?php _e('You can use placeholders: {document_url}, {document_title}, {document_title_url}, {form_id}. For example: \"I agree to the {document_title_url}\"', 'codeweber'); ?></small>';
                html += '</p>';
                
                // Document selection (CPT legal documents)
                html += '<p class="consent-document-select">';
                html += '<label><strong><?php _e('Select Document', 'codeweber'); ?>:</strong><br>';
                html += '<select name="consents[' + index + '][document_id]" class="consent-document-id">';
                html += '<option value=""><?php _e('— Select —', 'codeweber'); ?></option>';
                if (legalDocs && legalDocs.length > 0) {
                    legalDocs.forEach(function(doc) {
                        html += '<option value="' + doc.id + '" ' + (consent.document_id == doc.id ? 'selected' : '') + '>' + doc.title + '</option>';
                    });
                }
                html += '</select>';
                html += '</label>';
                html += '</p>';
                
                // Required checkbox
                html += '<p>';
                html += '<label>';
                html += '<input type="checkbox" name="consents[' + index + '][required]" value="1" ' + (consent.required ? 'checked' : '') + '>';
                html += ' <?php _e('Required (form cannot be submitted without this consent)', 'codeweber'); ?>';
                html += '</label>';
                html += '</p>';
                
                // Remove button
                html += '<p>';
                html += '<button type="button" class="button remove-consent-btn"><?php _e('Remove', 'codeweber'); ?></button>';
                html += '</p>';
                
                html += '</div>';
                return html;
            }
            
            // Auto-fill label text when document is selected (similar to built-in forms settings)
            $(document).on('change', '.consent-document-id', function() {
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
                            // Keep original value on error
                            $labelInput.val(originalValue);
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
        });
        </script>
        
        <style>
            .consent-row {
                border: 1px solid #ddd;
                padding: 15px;
                margin-bottom: 15px;
                background: #f9f9f9;
            }
            .consent-row label {
                display: block;
                width: 100%;
            }
            .consent-row input.consent-label-input {
                width: 100% !important;
                max-width: 800px !important;
                box-sizing: border-box;
            }
        </style>
        <?php
    }
    
    /**
     * Render single consent row
     */
    private function render_consent_row($index, $consent, $legal_documents, $privacy_policy_page) {
        ?>
        <div class="consent-row" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; background: #f9f9f9;">
            <p><strong><?php _e('Consent', 'codeweber'); ?> #<?php echo ($index + 1); ?></strong></p>
            
            <p>
                <label><strong><?php _e('Label Text', 'codeweber'); ?>:</strong><br>
                    <input type="text" name="consents[<?php echo $index; ?>][label]" value="<?php echo esc_attr($consent['label'] ?? ''); ?>" class="large-text consent-label-input" placeholder="<?php _e('I agree to the {document_title}', 'codeweber'); ?>" style="width: 100%;">
                </label><br>
                <small class="description"><?php _e('You can use placeholders: {document_url}, {document_title}, {document_title_url}, {form_id}. For example: \"I agree to the {document_title_url}\"', 'codeweber'); ?></small>
            </p>
            
            <p class="consent-document-select">
                <label><strong><?php _e('Select Document', 'codeweber'); ?>:</strong><br>
                    <select name="consents[<?php echo $index; ?>][document_id]" class="consent-document-id">
                        <option value=""><?php _e('— Select —', 'codeweber'); ?></option>
                        <?php foreach ($legal_documents as $doc): ?>
                            <option value="<?php echo esc_attr($doc->ID); ?>" <?php selected($consent['document_id'] ?? '', $doc->ID); ?>>
                                <?php echo esc_html($doc->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </p>
            
            <p>
                <label>
                    <input type="checkbox" name="consents[<?php echo $index; ?>][required]" value="1" <?php checked(!empty($consent['required'])); ?>>
                    <?php _e('Required (form cannot be submitted without this consent)', 'codeweber'); ?>
                </label>
            </p>
            
            <p>
                <button type="button" class="button remove-consent-btn"><?php _e('Remove', 'codeweber'); ?></button>
            </p>
        </div>
        <?php
    }
    
    /**
     * Save consents
     */
    public function save_consents($post_id, $post, $update) {
        // Check nonce
        if (!isset($_POST['consents_nonce']) || !wp_verify_nonce($_POST['consents_nonce'], 'save_consents')) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Save consents
        if (isset($_POST['consents']) && is_array($_POST['consents'])) {
            $consents = [];
            foreach ($_POST['consents'] as $consent) {
                if (!empty($consent['label'])) {
                    $consents[] = [
                        'label' => sanitize_text_field($consent['label']),
                        'document_type' => sanitize_text_field($consent['document_type'] ?? ''),
                        'document_id' => !empty($consent['document_id']) ? intval($consent['document_id']) : 0,
                        'required' => !empty($consent['required']),
                    ];
                }
            }
            update_post_meta($post_id, '_form_consents', $consents);
        } else {
            delete_post_meta($post_id, '_form_consents');
        }
    }
}



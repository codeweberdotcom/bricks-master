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
                    <?php _e('+ Add Consent', 'codeweber'); ?>
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
                html += '<input type="text" name="consents[' + index + '][label]" value="' + (consent.label || '') + '" class="large-text" placeholder="<?php _e('I agree to the [cf7_privacy_policy id="{form_id}"]', 'codeweber'); ?>">';
                html += '</label><br>';
                html += '<small class="description"><?php _e('You can use shortcodes: [cf7_privacy_policy id="{form_id}"], [cf7_legal_consent_link id="{form_id}"], [cf7_mailing_consent_link id="{form_id}"]', 'codeweber'); ?></small>';
                html += '</p>';
                
                // Document type
                html += '<p>';
                html += '<label><strong><?php _e('Document Type', 'codeweber'); ?>:</strong><br>';
                html += '<select name="consents[' + index + '][document_type]" class="consent-document-type">';
                html += '<option value=""><?php _e('— Select —', 'codeweber'); ?></option>';
                if (privacyPage) {
                    html += '<option value="privacy_policy" ' + (consent.document_type === 'privacy_policy' ? 'selected' : '') + '><?php _e('Privacy Policy', 'codeweber'); ?></option>';
                }
                html += '<option value="legal_consent" ' + (consent.document_type === 'legal_consent' ? 'selected' : '') + '><?php _e('Legal Consent', 'codeweber'); ?></option>';
                html += '<option value="mailing_consent" ' + (consent.document_type === 'mailing_consent' ? 'selected' : '') + '><?php _e('Mailing Consent', 'codeweber'); ?></option>';
                html += '</select>';
                html += '</label>';
                html += '</p>';
                
                // Document selection (for legal documents)
                html += '<p class="consent-document-select" style="display: none;">';
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
            
            // Show/hide document select based on document type
            $(document).on('change', '.consent-document-type', function() {
                var $row = $(this).closest('.consent-row');
                var docType = $(this).val();
                var $docSelect = $row.find('.consent-document-select');
                
                if (docType === 'legal_consent' || docType === 'mailing_consent') {
                    $docSelect.show();
                } else {
                    $docSelect.hide();
                }
            });
            
            // Trigger change on existing selects
            $('.consent-document-type').trigger('change');
        });
        </script>
        
        <style>
            .consent-row {
                border: 1px solid #ddd;
                padding: 15px;
                margin-bottom: 15px;
                background: #f9f9f9;
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
                    <input type="text" name="consents[<?php echo $index; ?>][label]" value="<?php echo esc_attr($consent['label'] ?? ''); ?>" class="large-text" placeholder="<?php _e('I agree to the [cf7_privacy_policy id="{form_id}"]', 'codeweber'); ?>">
                </label><br>
                <small class="description"><?php _e('You can use shortcodes: [cf7_privacy_policy id="{form_id}"], [cf7_legal_consent_link id="{form_id}"], [cf7_mailing_consent_link id="{form_id}"]', 'codeweber'); ?></small>
            </p>
            
            <p>
                <label><strong><?php _e('Document Type', 'codeweber'); ?>:</strong><br>
                    <select name="consents[<?php echo $index; ?>][document_type]" class="consent-document-type">
                        <option value=""><?php _e('— Select —', 'codeweber'); ?></option>
                        <?php if ($privacy_policy_page): ?>
                            <option value="privacy_policy" <?php selected($consent['document_type'] ?? '', 'privacy_policy'); ?>><?php _e('Privacy Policy', 'codeweber'); ?></option>
                        <?php endif; ?>
                        <option value="legal_consent" <?php selected($consent['document_type'] ?? '', 'legal_consent'); ?>><?php _e('Legal Consent', 'codeweber'); ?></option>
                        <option value="mailing_consent" <?php selected($consent['document_type'] ?? '', 'mailing_consent'); ?>><?php _e('Mailing Consent', 'codeweber'); ?></option>
                    </select>
                </label>
            </p>
            
            <p class="consent-document-select" style="display: <?php echo (in_array($consent['document_type'] ?? '', ['legal_consent', 'mailing_consent'])) ? 'block' : 'none'; ?>;">
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



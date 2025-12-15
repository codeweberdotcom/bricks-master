<?php
/**
 * CodeWeber Forms Consent Settings
 * 
 * Admin page for managing consent settings for forms
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

class CodeweberFormsConsentSettings {
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'codeweber',
            __('Consent Settings', 'codeweber'),
            __('Consent Settings', 'codeweber'),
            'manage_options',
            'codeweber-forms-consent-settings',
            [$this, 'render_page']
        );
    }
    
    /**
     * Render consent settings page
     */
    public function render_page() {
        // Handle form submission
        if (isset($_POST['save_consent_settings']) && wp_verify_nonce($_POST['consent_nonce'], 'save_consent_settings')) {
            $this->save_settings();
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved!', 'codeweber') . '</p></div>';
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
        <div class="wrap">
            <h1><?php _e('Consent Settings', 'codeweber'); ?></h1>
            
            <div class="card" style="margin: 20px 0; padding: 20px; background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; max-width: 100%;">
                <h2><?php _e('Available Consent Documents', 'codeweber'); ?></h2>
                <p><?php _e('These documents can be used in forms. You can select them for each form individually.', 'codeweber'); ?></p>
                
                <?php if (empty($legal_documents) && !$privacy_policy_page): ?>
                    <p class="description" style="color: #d63638;">
                        <?php _e('No consent documents found. Please create documents in the Legal section.', 'codeweber'); ?>
                    </p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th style="width: 50px;"><?php _e('ID', 'codeweber'); ?></th>
                                <th><?php _e('Document Title', 'codeweber'); ?></th>
                                <th><?php _e('Type', 'codeweber'); ?></th>
                                <th><?php _e('Shortcode', 'codeweber'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($privacy_policy_page): ?>
                                <tr>
                                    <td><?php echo esc_html($privacy_policy_page->ID); ?></td>
                                    <td><strong><?php echo esc_html($privacy_policy_page->post_title); ?></strong></td>
                                    <td><?php _e('Privacy Policy', 'codeweber'); ?></td>
                                    <td>
                                        <code>{document_url}</code><br>
                                        <code>[cf7_privacy_policy id="{form_id}"]</code>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($legal_documents as $doc): ?>
                                <tr>
                                    <td><?php echo esc_html($doc->ID); ?></td>
                                    <td><strong><?php echo esc_html($doc->post_title); ?></strong></td>
                                    <td><?php _e('Legal Document', 'codeweber'); ?></td>
                                    <td>
                                        <code>{document_url}</code><br>
                                        <code>[cf7_legal_consent_link id="{form_id}"]</code><br>
                                        <code>[cf7_mailing_consent_link id="{form_id}"]</code>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
            <div class="card" style="margin: 20px 0; padding: 20px; background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; max-width: 100%;">
                <h2><?php _e('How to Use Consents in Forms', 'codeweber'); ?></h2>
                <ol>
                    <li><?php _e('Edit a form in the Forms section.', 'codeweber'); ?></li>
                    <li><?php _e('In the form editor, you will see a "Consents" metabox.', 'codeweber'); ?></li>
                    <li><?php _e('Add consent checkboxes by clicking "Add Consent".', 'codeweber'); ?></li>
                    <li><?php _e('For each consent, specify:', 'codeweber'); ?>
                        <ul style="list-style: disc; margin-left: 20px;">
                            <li><?php _e('Label text (you can use shortcodes for links)', 'codeweber'); ?></li>
                            <li><?php _e('Document to link to', 'codeweber'); ?></li>
                            <li><?php _e('Whether it is required', 'codeweber'); ?></li>
                        </ul>
                    </li>
                    <li><?php _e('The consent checkboxes will automatically appear in the form on the frontend.', 'codeweber'); ?></li>
                </ol>
                
                <h3><?php _e('Label Text Examples', 'codeweber'); ?></h3>
                <p><?php _e('In the consent label text, you can use these placeholders and shortcodes:', 'codeweber'); ?></p>
                <ul>
                    <li><code>{document_url}</code> - <?php _e('Will be replaced with the actual document URL', 'codeweber'); ?></li>
                    <li><code>{document_title}</code> - <?php _e('Will be replaced with the document title', 'codeweber'); ?></li>
                    <li><code>{form_id}</code> - <?php _e('Will be replaced with the form ID', 'codeweber'); ?></li>
                    <li><code>[cf7_privacy_policy id="{form_id}"]</code> - <?php _e('CF7-style shortcode (for backward compatibility)', 'codeweber'); ?></li>
                    <li><code>[cf7_legal_consent_link id="{form_id}"]</code> - <?php _e('CF7-style shortcode (for backward compatibility)', 'codeweber'); ?></li>
                    <li><code>[cf7_mailing_consent_link id="{form_id}"]</code> - <?php _e('CF7-style shortcode (for backward compatibility)', 'codeweber'); ?></li>
                </ul>
                <p><strong><?php _e('Example:', 'codeweber'); ?></strong></p>
                <p><code>Я согласен с <a href="{document_url}">{document_title}</a></code></p>
                <p class="description">
                    <?php _e('Note: All placeholders and shortcodes will be automatically processed when the form is rendered. The module uses its own helper functions, independent from Contact Form 7 integration.', 'codeweber'); ?>
                </p>
            </div>
        </div>
        
        <style>
            .card h2 {
                margin-top: 0;
            }
            .card h3 {
                margin-top: 20px;
            }
        </style>
        <?php
    }
    
    /**
     * Save settings
     */
    private function save_settings() {
        // Settings can be saved here if needed in the future
    }
}


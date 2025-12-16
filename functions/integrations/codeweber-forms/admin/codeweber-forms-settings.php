<?php
/**
 * CodeWeber Forms Settings
 * 
 * Module settings page
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

class CodeweberFormsSettings {
    private $option_group = 'codeweber_forms_settings';
    private $option_name = 'codeweber_forms_options';
    
    public function __construct() {
        add_action('admin_init', [$this, 'register_settings']);
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting($this->option_group, $this->option_name, [$this, 'sanitize_settings']);
        
        // Email Settings Section
        add_settings_section(
            'codeweber_forms_email_section',
            __('Email Settings', 'codeweber'),
            [$this, 'email_section_callback'],
            'codeweber-forms-settings'
        );
        
        add_settings_field(
            'default_recipient_email',
            __('Default Recipient Email', 'codeweber'),
            [$this, 'recipient_email_field'],
            'codeweber-forms-settings',
            'codeweber_forms_email_section'
        );
        
        add_settings_field(
            'default_sender_email',
            __('Default Sender Email', 'codeweber'),
            [$this, 'sender_email_field'],
            'codeweber-forms-settings',
            'codeweber_forms_email_section'
        );
        
        add_settings_field(
            'default_sender_name',
            __('Default Sender Name', 'codeweber'),
            [$this, 'sender_name_field'],
            'codeweber-forms-settings',
            'codeweber_forms_email_section'
        );
        
        add_settings_field(
            'default_subject',
            __('Default Email Subject', 'codeweber'),
            [$this, 'subject_field'],
            'codeweber-forms-settings',
            'codeweber_forms_email_section'
        );
        
        // Rate Limiting Section
        add_settings_section(
            'codeweber_forms_rate_limit_section',
            __('Rate Limiting', 'codeweber'),
            [$this, 'rate_limit_section_callback'],
            'codeweber-forms-settings'
        );
        
        add_settings_field(
            'rate_limit_enabled',
            __('Enable Rate Limiting', 'codeweber'),
            [$this, 'rate_limit_enabled_field'],
            'codeweber-forms-settings',
            'codeweber_forms_rate_limit_section'
        );
        
        add_settings_field(
            'rate_limit_max_submissions',
            __('Max Submissions per Period', 'codeweber'),
            [$this, 'rate_limit_max_field'],
            'codeweber-forms-settings',
            'codeweber_forms_rate_limit_section'
        );
        
        add_settings_field(
            'rate_limit_period',
            __('Rate Limit Period (minutes)', 'codeweber'),
            [$this, 'rate_limit_period_field'],
            'codeweber-forms-settings',
            'codeweber_forms_rate_limit_section'
        );
        
        // Messages Section
        add_settings_section(
            'codeweber_forms_messages_section',
            __('Default Messages', 'codeweber'),
            [$this, 'messages_section_callback'],
            'codeweber-forms-settings'
        );
        
        add_settings_field(
            'success_message',
            __('Success Message', 'codeweber'),
            [$this, 'success_message_field'],
            'codeweber-forms-settings',
            'codeweber_forms_messages_section'
        );
        
        add_settings_field(
            'error_message',
            __('Error Message', 'codeweber'),
            [$this, 'error_message_field'],
            'codeweber-forms-settings',
            'codeweber_forms_messages_section'
        );
        
        // Editor Settings Section
        add_settings_section(
            'codeweber_forms_editor_section',
            __('Gutenberg Editor Settings', 'codeweber'),
            [$this, 'editor_section_callback'],
            'codeweber-forms-settings'
        );
        
        add_settings_field(
            'block_restriction',
            __('Block Restriction Mode', 'codeweber'),
            [$this, 'block_restriction_field'],
            'codeweber-forms-settings',
            'codeweber_forms_editor_section'
        );
    }
    
    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = [];
        
        if (isset($input['default_recipient_email'])) {
            $sanitized['default_recipient_email'] = sanitize_email($input['default_recipient_email']);
        }
        
        if (isset($input['default_sender_email'])) {
            $sanitized['default_sender_email'] = sanitize_email($input['default_sender_email']);
        }
        
        if (isset($input['default_sender_name'])) {
            $sanitized['default_sender_name'] = sanitize_text_field($input['default_sender_name']);
        }
        
        if (isset($input['default_subject'])) {
            $sanitized['default_subject'] = sanitize_text_field($input['default_subject']);
        }
        
        if (isset($input['rate_limit_enabled'])) {
            $sanitized['rate_limit_enabled'] = (bool) $input['rate_limit_enabled'];
        } else {
            $sanitized['rate_limit_enabled'] = false;
        }
        
        if (isset($input['rate_limit_max_submissions'])) {
            $sanitized['rate_limit_max_submissions'] = absint($input['rate_limit_max_submissions']);
        }
        
        if (isset($input['rate_limit_period'])) {
            $sanitized['rate_limit_period'] = absint($input['rate_limit_period']);
        }
        
        if (isset($input['success_message'])) {
            $sanitized['success_message'] = sanitize_textarea_field($input['success_message']);
        }
        
        if (isset($input['error_message'])) {
            $sanitized['error_message'] = sanitize_textarea_field($input['error_message']);
        }
        
        // Editor settings
        if (isset($input['block_restriction'])) {
            $allowed_modes = ['minimal', 'all'];
            if (in_array($input['block_restriction'], $allowed_modes)) {
                update_option('codeweber_forms_block_restriction', $input['block_restriction']);
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Get option value
     */
    private function get_option($key, $default = '') {
        $options = get_option($this->option_name, []);
        return isset($options[$key]) ? $options[$key] : $default;
    }
    
    // Section callbacks
    public function email_section_callback() {
        echo '<p>' . __('Configure default email settings for form submissions.', 'codeweber') . '</p>';
    }
    
    public function rate_limit_section_callback() {
        echo '<p>' . __('Configure rate limiting to prevent spam submissions.', 'codeweber') . '</p>';
    }
    
    public function messages_section_callback() {
        echo '<p>' . __('Set default messages shown to users after form submission.', 'codeweber') . '</p>';
    }
    
    public function editor_section_callback() {
        echo '<p>' . __('Configure Gutenberg editor settings for form creation.', 'codeweber') . '</p>';
    }
    
    // Field callbacks
    public function recipient_email_field() {
        $value = $this->get_option('default_recipient_email', get_option('admin_email'));
        echo '<input type="email" name="' . $this->option_name . '[default_recipient_email]" value="' . esc_attr($value) . '" class="regular-text">';
        echo '<p class="description">' . __('Email address where form submissions will be sent by default.', 'codeweber') . '</p>';
    }
    
    public function sender_email_field() {
        $value = $this->get_option('default_sender_email', get_option('admin_email'));
        echo '<input type="email" name="' . $this->option_name . '[default_sender_email]" value="' . esc_attr($value) . '" class="regular-text">';
        echo '<p class="description">' . __('Email address used as sender for form submission emails.', 'codeweber') . '</p>';
    }
    
    public function sender_name_field() {
        $value = $this->get_option('default_sender_name', get_bloginfo('name'));
        echo '<input type="text" name="' . $this->option_name . '[default_sender_name]" value="' . esc_attr($value) . '" class="regular-text">';
        echo '<p class="description">' . __('Name used as sender for form submission emails.', 'codeweber') . '</p>';
    }
    
    public function subject_field() {
        $value = $this->get_option('default_subject', __('New Form Submission', 'codeweber'));
        echo '<input type="text" name="' . $this->option_name . '[default_subject]" value="' . esc_attr($value) . '" class="regular-text">';
        echo '<p class="description">' . __('Default subject line for form submission emails.', 'codeweber') . '</p>';
    }
    
    public function rate_limit_enabled_field() {
        $value = $this->get_option('rate_limit_enabled', true);
        echo '<label>';
        echo '<input type="checkbox" name="' . $this->option_name . '[rate_limit_enabled]" value="1" ' . checked(true, $value, false) . '>';
        echo ' ' . __('Enable rate limiting for form submissions', 'codeweber');
        echo '</label>';
    }
    
    public function rate_limit_max_field() {
        $value = $this->get_option('rate_limit_max_submissions', 5);
        echo '<input type="number" name="' . $this->option_name . '[rate_limit_max_submissions]" value="' . esc_attr($value) . '" min="1" class="small-text">';
        echo '<p class="description">' . __('Maximum number of submissions allowed per period.', 'codeweber') . '</p>';
    }
    
    public function rate_limit_period_field() {
        $value = $this->get_option('rate_limit_period', 60);
        echo '<input type="number" name="' . $this->option_name . '[rate_limit_period]" value="' . esc_attr($value) . '" min="1" class="small-text">';
        echo '<p class="description">' . __('Time period in minutes for rate limiting.', 'codeweber') . '</p>';
    }
    
    public function success_message_field() {
        $value = $this->get_option('success_message', __('Thank you! Your message has been sent.', 'codeweber'));
        echo '<textarea name="' . $this->option_name . '[success_message]" rows="3" class="large-text">' . esc_textarea($value) . '</textarea>';
        echo '<p class="description">' . __('Message shown to users after successful form submission.', 'codeweber') . '</p>';
    }
    
    public function error_message_field() {
        $value = $this->get_option('error_message', __('An error occurred. Please try again.', 'codeweber'));
        echo '<textarea name="' . $this->option_name . '[error_message]" rows="3" class="large-text">' . esc_textarea($value) . '</textarea>';
        echo '<p class="description">' . __('Message shown to users when form submission fails.', 'codeweber') . '</p>';
    }
    
    public function block_restriction_field() {
        $value = get_option('codeweber_forms_block_restriction', 'minimal');
        ?>
        <select name="<?php echo esc_attr($this->option_name); ?>[block_restriction]">
            <option value="minimal" <?php selected($value, 'minimal'); ?>>
                <?php _e('Minimal (only form blocks)', 'codeweber'); ?>
            </option>
            <option value="all" <?php selected($value, 'all'); ?>>
                <?php _e('All Codeweber Blocks', 'codeweber'); ?>
            </option>
        </select>
        <p class="description">
            <?php _e('Choose which blocks are available in the Gutenberg editor when creating forms. "Minimal" mode allows only form and form-field blocks, while "All Codeweber Blocks" allows all blocks from Codeweber Gutenberg Blocks plugin.', 'codeweber'); ?>
        </p>
        <?php
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'codeweber'));
        }
        
        // Handle form submission
        if (isset($_POST['submit']) && check_admin_referer($this->option_group . '-options')) {
            settings_errors('codeweber_forms_settings');
        }
        
        ?>
        <div class="wrap">
            <h1><?php _e('Form Settings', 'codeweber'); ?></h1>
            
            <?php settings_errors('codeweber_forms_settings'); ?>
            
            <form method="post" action="options.php">
                <?php
                settings_fields($this->option_group);
                do_settings_sections('codeweber-forms-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}


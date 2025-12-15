<?php
/**
 * CodeWeber Forms Email Templates
 * 
 * Email templates settings page
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

class CodeweberFormsEmailTemplates {
    private $option_group = 'codeweber_forms_email_templates';
    private $option_name = 'codeweber_forms_email_templates';
    
    public function __construct() {
        add_action('admin_init', [$this, 'register_settings']);
        // Меню добавляется в CodeweberFormsAdmin
        
        // Настройка TinyMCE для разрешения всех HTML тегов
        add_filter('tiny_mce_before_init', [$this, 'configure_tinymce_for_templates'], 10, 2);
        add_filter('wp_kses_allowed_html', [$this, 'allow_all_html_in_templates'], 10, 2);
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting($this->option_group, $this->option_name, [$this, 'sanitize_settings']);
    }
    
    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = [];
        
        if (isset($input['admin_notification_enabled'])) {
            $sanitized['admin_notification_enabled'] = (bool) $input['admin_notification_enabled'];
        } else {
            $sanitized['admin_notification_enabled'] = true;
        }
        
        if (isset($input['admin_notification_subject'])) {
            $sanitized['admin_notification_subject'] = sanitize_text_field($input['admin_notification_subject']);
        }
        
        if (isset($input['admin_notification_template'])) {
            // Минимальная санитизация - только убираем опасные скрипты
            $template = $input['admin_notification_template'];
            // Убираем только потенциально опасные теги script и iframe
            $template = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $template);
            $template = preg_replace('/<iframe[^>]*>.*?<\/iframe>/is', '', $template);
            $sanitized['admin_notification_template'] = $template;
        }
        
        if (isset($input['auto_reply_enabled'])) {
            $sanitized['auto_reply_enabled'] = (bool) $input['auto_reply_enabled'];
        } else {
            $sanitized['auto_reply_enabled'] = false;
        }
        
        if (isset($input['auto_reply_subject'])) {
            $sanitized['auto_reply_subject'] = sanitize_text_field($input['auto_reply_subject']);
        }
        
        if (isset($input['auto_reply_template'])) {
            // Минимальная санитизация - только убираем опасные скрипты
            $template = $input['auto_reply_template'];
            $template = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $template);
            $template = preg_replace('/<iframe[^>]*>.*?<\/iframe>/is', '', $template);
            $sanitized['auto_reply_template'] = $template;
        }
        
        if (isset($input['testimonial_reply_enabled'])) {
            $sanitized['testimonial_reply_enabled'] = (bool) $input['testimonial_reply_enabled'];
        } else {
            $sanitized['testimonial_reply_enabled'] = false;
        }
        
        if (isset($input['testimonial_reply_subject'])) {
            $sanitized['testimonial_reply_subject'] = sanitize_text_field($input['testimonial_reply_subject']);
        }
        
        if (isset($input['testimonial_reply_template'])) {
            // Минимальная санитизация - только убираем опасные скрипты
            $template = $input['testimonial_reply_template'];
            $template = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $template);
            $template = preg_replace('/<iframe[^>]*>.*?<\/iframe>/is', '', $template);
            $sanitized['testimonial_reply_template'] = $template;
        }
        
        if (isset($input['resume_reply_enabled'])) {
            $sanitized['resume_reply_enabled'] = (bool) $input['resume_reply_enabled'];
        } else {
            $sanitized['resume_reply_enabled'] = false;
        }
        
        if (isset($input['resume_reply_subject'])) {
            $sanitized['resume_reply_subject'] = sanitize_text_field($input['resume_reply_subject']);
        }
        
        if (isset($input['resume_reply_template'])) {
            // Минимальная санитизация - только убираем опасные скрипты
            $template = $input['resume_reply_template'];
            $template = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $template);
            $template = preg_replace('/<iframe[^>]*>.*?<\/iframe>/is', '', $template);
            $sanitized['resume_reply_template'] = $template;
        }
        
        if (isset($input['newsletter_reply_enabled'])) {
            $sanitized['newsletter_reply_enabled'] = (bool) $input['newsletter_reply_enabled'];
        } else {
            $sanitized['newsletter_reply_enabled'] = false;
        }
        
        if (isset($input['newsletter_reply_subject'])) {
            $sanitized['newsletter_reply_subject'] = sanitize_text_field($input['newsletter_reply_subject']);
        }
        
        if (isset($input['newsletter_reply_template'])) {
            // Минимальная санитизация - только убираем опасные скрипты
            $template = $input['newsletter_reply_template'];
            $template = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $template);
            $template = preg_replace('/<iframe[^>]*>.*?<\/iframe>/is', '', $template);
            $sanitized['newsletter_reply_template'] = $template;
        }
        
        return $sanitized;
    }
    
    /**
     * Get option value
     */
    private function get_option($key, $default = '') {
        $options = get_option($this->option_name, []);
        $value = isset($options[$key]) ? $options[$key] : $default;
        
        // Очищаем &nbsp; и нормализуем HTML для шаблонов
        if (strpos($key, '_template') !== false && is_string($value)) {
            $value = $this->clean_template_html($value);
        }
        
        return $value;
    }
    
    /**
     * Clean template HTML - remove &nbsp; and normalize
     */
    private function clean_template_html($html) {
        if (empty($html)) {
            return $html;
        }
        
        // Убираем &nbsp; в разных форматах
        $html = str_replace(["\xC2\xA0", '&nbsp;', chr(0xC2).chr(0xA0), '&#160;', '&#xA0;'], ' ', $html);
        
        // Декодируем остальные HTML entities, но сохраняем структуру
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Убираем лишние пробелы между тегами (но не внутри текста)
        $html = preg_replace('/>\s+</', '><', $html);
        
        // Убираем пробелы в начале и конце
        $html = trim($html);
        
        return $html;
    }
    
    /**
     * Sanitize template HTML - allow DOCTYPE and all HTML tags
     */
    private function sanitize_template_html($html) {
        if (empty($html)) {
            return $html;
        }
        
        // Сохраняем DOCTYPE отдельно, так как wp_kses его удалит
        $doctype = '';
        if (preg_match('/<!DOCTYPE[^>]*>/i', $html, $matches)) {
            $doctype = $matches[0];
            $html = str_replace($doctype, '', $html);
        }
        
        // Разрешаем все HTML теги
        $allowed = wp_kses_allowed_html('post');
        
        // Добавляем специфичные теги для email шаблонов
        $allowed['html'] = ['lang' => true, 'xmlns' => true];
        $allowed['head'] = true;
        $allowed['meta'] = [
            'charset' => true,
            'name' => true,
            'content' => true,
            'http-equiv' => true
        ];
        $allowed['style'] = [
            'type' => true,
            'media' => true
        ];
        $allowed['body'] = [
            'style' => true,
            'class' => true
        ];
        
        // Разрешаем все атрибуты для всех тегов
        foreach ($allowed as $tag => &$attrs) {
            if (is_array($attrs)) {
                $attrs['style'] = true;
                $attrs['class'] = true;
                $attrs['id'] = true;
            }
        }
        
        // Санитизируем HTML
        $html = wp_kses($html, $allowed);
        
        // Восстанавливаем DOCTYPE в начале
        if (!empty($doctype)) {
            $html = $doctype . "\n" . trim($html);
        }
        
        return $html;
    }
    
    /**
     * Configure TinyMCE for email templates - allow all HTML tags
     */
    public function configure_tinymce_for_templates($init, $editor_id) {
        // Применяем только к полям шаблонов
        $template_fields = [
            'admin_notification_template',
            'auto_reply_template',
            'testimonial_reply_template',
            'resume_reply_template'
        ];
        
        if (!in_array($editor_id, $template_fields)) {
            return $init;
        }
        
        // Разрешаем все HTML теги
        $init['valid_elements'] = '*[*]';
        $init['extended_valid_elements'] = '*[*]';
        
        // Отключаем автоматическую очистку HTML
        $init['cleanup'] = false;
        $init['verify_html'] = false;
        $init['remove_linebreaks'] = false;
        $init['forced_root_block'] = false;
        $init['force_br_newlines'] = false;
        $init['force_p_newlines'] = false;
        $init['convert_newlines_to_brs'] = false;
        $init['remove_redundant_brs'] = false;
        
        // Сохраняем структуру HTML как есть
        $init['entity_encoding'] = 'raw';
        $init['entities'] = false;
        $init['entity_encoding'] = 'raw';
        
        // Разрешаем DOCTYPE и комментарии
        $init['schema'] = 'html5';
        
        // Отключаем автоматическое форматирование
        $init['indent'] = true;
        $init['indent_before'] = '';
        $init['indent_after'] = '';
        
        // Сохраняем пробелы
        $init['preformatted'] = true;
        
        return $init;
    }
    
    /**
     * Allow all HTML tags in email templates when sanitizing
     */
    public function allow_all_html_in_templates($allowed, $context) {
        // Если это контекст post (wp_kses_post), разрешаем все теги для шаблонов
        if ($context === 'post') {
            // Проверяем, сохраняем ли мы шаблон
            if (isset($_POST['codeweber_forms_email_templates'])) {
                // Разрешаем все стандартные HTML теги
                $allowed = wp_kses_allowed_html('post');
                
                // Добавляем дополнительные теги, которые могут быть в email шаблонах
                $allowed['!doctype'] = ['html' => true];
                $allowed['html'] = ['lang' => true, 'xmlns' => true];
                $allowed['head'] = true;
                $allowed['meta'] = [
                    'charset' => true,
                    'name' => true,
                    'content' => true,
                    'http-equiv' => true
                ];
                $allowed['title'] = true;
                $allowed['style'] = [
                    'type' => true,
                    'media' => true
                ];
                $allowed['body'] = [
                    'style' => true,
                    'class' => true
                ];
                
                // Разрешаем все атрибуты для всех тегов
                foreach ($allowed as $tag => &$attrs) {
                    if (is_array($attrs)) {
                        $attrs['style'] = true;
                        $attrs['class'] = true;
                        $attrs['id'] = true;
                        $attrs['data-*'] = true;
                    }
                }
            }
        }
        
        return $allowed;
    }
    
    /**
     * Get default admin template
     */
    public function get_default_admin_template() {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #0073aa; color: white; padding: 20px; text-align: center; }
        .content { background-color: #f9f9f9; padding: 20px; }
        .footer { background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background-color: #f5f5f5; padding: 10px; text-align: left; border: 1px solid #ddd; }
        td { padding: 10px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0;">' . __('New Form Submission', 'codeweber') . '</h2>
        </div>
        <div class="content">
            <p>
                <strong>' . __('Form:', 'codeweber') . '</strong> {form_name}<br>
                <strong>' . __('Date:', 'codeweber') . '</strong> {submission_date} {submission_time}<br>
                <strong>' . __('From:', 'codeweber') . '</strong> {user_name} ({user_email})
            </p>
            <hr>
            <h3>' . __('Form Fields:', 'codeweber') . '</h3>
            {form_fields}
        </div>
        <div class="footer">
            <p><small>IP: {user_ip}<br>User Agent: {user_agent}</small></p>
        </div>
    </div>
</body>
</html>';
    }
    
    /**
     * Get default auto-reply template
     */
    public function get_default_auto_reply_template() {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #0073aa; color: white; padding: 20px; text-align: center; }
        .content { background-color: #f9f9f9; padding: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0;">' . __('Thank you for your message', 'codeweber') . '</h2>
        </div>
        <div class="content">
            <p>' . __('Hello,', 'codeweber') . ' {user_name}!</p>
            <p>' . __('We have received your message and will contact you soon.', 'codeweber') . '</p>
            <p>' . __('Best regards,', 'codeweber') . '<br>{site_name}</p>
        </div>
    </div>
</body>
</html>';
    }
    
    /**
     * Get default testimonial reply template
     */
    public function get_default_testimonial_reply_template() {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #28a745; color: white; padding: 20px; text-align: center; }
        .content { background-color: #f9f9f9; padding: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0;">' . __('Thank you for your testimonial', 'codeweber') . '</h2>
        </div>
        <div class="content">
            <p>' . __('Hello,', 'codeweber') . ' {user_name}!</p>
            <p>' . __('Thank you for leaving a testimonial. Your opinion is very important to us!', 'codeweber') . '</p>
            <p>' . __('After moderation, your testimonial will be published on our website.', 'codeweber') . '</p>
            <p>' . __('Best regards,', 'codeweber') . '<br>{site_name}</p>
        </div>
    </div>
</body>
    </html>';
    }
    
    /**
     * Get default newsletter reply template
     */
    public function get_default_newsletter_reply_template() {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #28a745; color: white; padding: 20px; text-align: center; }
        .content { background-color: #f9f9f9; padding: 20px; }
        .footer { background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0;">' . __('Thank you for subscribing', 'codeweber') . '</h2>
        </div>
        <div class="content">
            <p>' . __('Hello,', 'codeweber') . ' {user_name}!</p>
            <p>' . __('Thank you for subscribing to our newsletter!', 'codeweber') . '</p>
            <p>' . __('You will now receive our latest news, updates, and special offers.', 'codeweber') . '</p>
            <p>' . __('If you did not subscribe to our newsletter, please ignore this email.', 'codeweber') . '</p>
        </div>
        <div class="footer">
            <p><small>' . __('Best regards,', 'codeweber') . '<br>{site_name}</small></p>
        </div>
    </div>
</body>
</html>';
    }
    
    /**
     * Get default resume reply template
     */
    public function get_default_resume_reply_template() {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #17a2b8; color: white; padding: 20px; text-align: center; }
        .content { background-color: #f9f9f9; padding: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0;">' . __('Your resume has been received', 'codeweber') . '</h2>
        </div>
        <div class="content">
            <p>' . __('Hello,', 'codeweber') . ' {user_name}!</p>
            <p>' . __('We have received your resume and will review it carefully.', 'codeweber') . '</p>
            <p>' . __('If your candidacy interests us, we will contact you soon.', 'codeweber') . '</p>
            <p>' . __('Best regards,', 'codeweber') . '<br>' . __('HR Department', 'codeweber') . '<br>{site_name}</p>
        </div>
    </div>
</body>
</html>';
    }
    
    /**
     * Get available variables
     */
    private function get_available_variables() {
        return [
            '{form_name}' => __('Form name', 'codeweber'),
            '{user_name}' => __('User name from form', 'codeweber'),
            '{user_email}' => __('User email from form', 'codeweber'),
            '{submission_date}' => __('Submission date', 'codeweber'),
            '{submission_time}' => __('Submission time (24h format)', 'codeweber'),
            '{form_fields}' => __('All form fields in table format', 'codeweber'),
            '{user_ip}' => __('User IP address', 'codeweber'),
            '{user_agent}' => __('User browser/agent', 'codeweber'),
            '{site_name}' => __('Site name', 'codeweber'),
            '{site_url}' => __('Site URL', 'codeweber'),
            '{unsubscribe_url}' => __('Unsubscribe link for newsletter subscriptions', 'codeweber'),
        ];
    }
    
    /**
     * Render email templates page
     */
    public function render_email_templates_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'codeweber'));
        }
        
        // Handle form submission
        if (isset($_POST['submit']) && check_admin_referer($this->option_group . '-options')) {
            settings_errors('codeweber_forms_email_templates');
        }
        
        $admin_enabled = $this->get_option('admin_notification_enabled', true);
        $admin_subject = $this->get_option('admin_notification_subject', __('New Form Submission', 'codeweber'));
        $admin_template = $this->get_option('admin_notification_template', $this->get_default_admin_template());
        
        $auto_reply_enabled = $this->get_option('auto_reply_enabled', false);
        $auto_reply_subject = $this->get_option('auto_reply_subject', __('Thank you for your message', 'codeweber'));
        $auto_reply_template = $this->get_option('auto_reply_template', $this->get_default_auto_reply_template());
        
        $testimonial_reply_enabled = $this->get_option('testimonial_reply_enabled', false);
        $testimonial_reply_subject = $this->get_option('testimonial_reply_subject', __('Thank you for your testimonial', 'codeweber'));
        $testimonial_reply_template = $this->get_option('testimonial_reply_template', $this->get_default_testimonial_reply_template());
        
        $resume_reply_enabled = $this->get_option('resume_reply_enabled', false);
        $resume_reply_subject = $this->get_option('resume_reply_subject', __('Your resume has been received', 'codeweber'));
        $resume_reply_template = $this->get_option('resume_reply_template', $this->get_default_resume_reply_template());
        
        $newsletter_reply_enabled = $this->get_option('newsletter_reply_enabled', false);
        $newsletter_reply_subject = $this->get_option('newsletter_reply_subject', __('Thank you for subscribing', 'codeweber'));
        $newsletter_reply_template = $this->get_option('newsletter_reply_template', $this->get_default_newsletter_reply_template());
        
        $variables = $this->get_available_variables();
        
        ?>
        <div class="wrap">
            <h1><?php _e('Email Templates', 'codeweber'); ?></h1>
            
            <?php settings_errors('codeweber_forms_email_templates'); ?>
            
            <form method="post" action="options.php">
                <?php settings_fields($this->option_group); ?>
                
                <div class="codeweber-forms-email-templates">
                    <!-- Available Variables -->
                    <div class="card" style="max-width: 100%; margin-bottom: 20px;">
                        <h2><?php _e('Available Variables', 'codeweber'); ?></h2>
                        <p><?php _e('You can use these variables in your email templates:', 'codeweber'); ?></p>
                        <table class="widefat">
                            <thead>
                                <tr>
                                    <th><?php _e('Variable', 'codeweber'); ?></th>
                                    <th><?php _e('Description', 'codeweber'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($variables as $var => $desc): ?>
                                    <tr>
                                        <td><code><?php echo esc_html($var); ?></code></td>
                                        <td><?php echo esc_html($desc); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Admin Notification Template -->
                    <div class="card" style="max-width: 100%; margin-bottom: 20px;">
                        <h2><?php _e('Admin Notification Template', 'codeweber'); ?></h2>
                        <p><?php _e('This template is used for emails sent to administrators when a form is submitted.', 'codeweber'); ?></p>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="admin_notification_enabled">
                                        <?php _e('Enable Admin Notifications', 'codeweber'); ?>
                                    </label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" 
                                               name="<?php echo $this->option_name; ?>[admin_notification_enabled]" 
                                               value="1" 
                                               id="admin_notification_enabled"
                                               <?php checked($admin_enabled, true); ?>>
                                        <?php _e('Send email notifications to administrators', 'codeweber'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="admin_notification_subject">
                                        <?php _e('Email Subject', 'codeweber'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" 
                                           name="<?php echo $this->option_name; ?>[admin_notification_subject]" 
                                           id="admin_notification_subject"
                                           value="<?php echo esc_attr($admin_subject); ?>" 
                                           class="regular-text">
                                    <p class="description">
                                        <?php _e('Subject line for admin notification emails. You can use variables like {form_name}.', 'codeweber'); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="admin_notification_template">
                                        <?php _e('Email Template', 'codeweber'); ?>
                                    </label>
                                </th>
                                <td>
                                    <?php
                                    wp_editor(
                                        $admin_template,
                                        'admin_notification_template',
                                        [
                                            'textarea_name' => $this->option_name . '[admin_notification_template]',
                                            'textarea_rows' => 20,
                                            'media_buttons' => false,
                                            'teeny' => false,
                                            'quicktags' => true,
                                            'tinymce' => [
                                                'entity_encoding' => 'raw',
                                                'remove_linebreaks' => false,
                                                'forced_root_block' => false,
                                                'force_br_newlines' => false,
                                                'force_p_newlines' => false,
                                            ],
                                        ]
                                    );
                                    ?>
                                    <p class="description">
                                        <?php _e('HTML template for admin notification emails. Use variables from the list above.', 'codeweber'); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Auto-Reply Template -->
                    <div class="card" style="max-width: 100%; margin-bottom: 20px;">
                        <h2><?php _e('Auto-Reply Template', 'codeweber'); ?></h2>
                        <p><?php _e('This template is used for automatic reply emails sent to users after form submission.', 'codeweber'); ?></p>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="auto_reply_enabled">
                                        <?php _e('Enable Auto-Reply', 'codeweber'); ?>
                                    </label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" 
                                               name="<?php echo $this->option_name; ?>[auto_reply_enabled]" 
                                               value="1" 
                                               id="auto_reply_enabled"
                                               <?php checked($auto_reply_enabled, true); ?>>
                                        <?php _e('Send automatic reply emails to users', 'codeweber'); ?>
                                    </label>
                                    <p class="description">
                                        <?php _e('Note: Auto-reply will only be sent if the form contains an email field.', 'codeweber'); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="auto_reply_subject">
                                        <?php _e('Email Subject', 'codeweber'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" 
                                           name="<?php echo $this->option_name; ?>[auto_reply_subject]" 
                                           id="auto_reply_subject"
                                           value="<?php echo esc_attr($auto_reply_subject); ?>" 
                                           class="regular-text">
                                    <p class="description">
                                        <?php _e('Subject line for auto-reply emails. You can use variables like {form_name}, {user_name}.', 'codeweber'); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="auto_reply_template">
                                        <?php _e('Email Template', 'codeweber'); ?>
                                    </label>
                                </th>
                                <td>
                                    <?php
                                    wp_editor(
                                        $auto_reply_template,
                                        'auto_reply_template',
                                        [
                                            'textarea_name' => $this->option_name . '[auto_reply_template]',
                                            'textarea_rows' => 20,
                                            'media_buttons' => false,
                                            'teeny' => false,
                                            'quicktags' => true,
                                            'tinymce' => [
                                                'entity_encoding' => 'raw',
                                                'remove_linebreaks' => false,
                                                'forced_root_block' => false,
                                                'force_br_newlines' => false,
                                                'force_p_newlines' => false,
                                            ],
                                        ]
                                    );
                                    ?>
                                    <p class="description">
                                        <?php _e('HTML template for auto-reply emails. Use variables from the list above.', 'codeweber'); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Testimonial Reply Template -->
                    <div class="card" style="max-width: 100%; margin-bottom: 20px;">
                        <h2><?php _e('Testimonial Reply Template', 'codeweber'); ?></h2>
                        <p><?php _e('This template is used for automatic reply emails sent to users after testimonial submission.', 'codeweber'); ?></p>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="testimonial_reply_enabled">
                                        <?php _e('Enable Testimonial Reply', 'codeweber'); ?>
                                    </label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" 
                                               name="<?php echo $this->option_name; ?>[testimonial_reply_enabled]" 
                                               value="1" 
                                               id="testimonial_reply_enabled"
                                               <?php checked($testimonial_reply_enabled, true); ?>>
                                        <?php _e('Send automatic reply emails for testimonials', 'codeweber'); ?>
                                    </label>
                                    <p class="description">
                                        <?php _e('Note: Reply will be sent if form name contains "testimonial" or "отзыв".', 'codeweber'); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="testimonial_reply_subject">
                                        <?php _e('Email Subject', 'codeweber'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" 
                                           name="<?php echo $this->option_name; ?>[testimonial_reply_subject]" 
                                           id="testimonial_reply_subject"
                                           value="<?php echo esc_attr($testimonial_reply_subject); ?>" 
                                           class="regular-text">
                                    <p class="description">
                                        <?php _e('Subject line for testimonial reply emails. You can use variables like {form_name}, {user_name}.', 'codeweber'); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="testimonial_reply_template">
                                        <?php _e('Email Template', 'codeweber'); ?>
                                    </label>
                                </th>
                                <td>
                                    <?php
                                    wp_editor(
                                        $testimonial_reply_template,
                                        'testimonial_reply_template',
                                        [
                                            'textarea_name' => $this->option_name . '[testimonial_reply_template]',
                                            'textarea_rows' => 20,
                                            'media_buttons' => false,
                                            'teeny' => false,
                                            'quicktags' => true,
                                            'tinymce' => [
                                                'entity_encoding' => 'raw',
                                                'remove_linebreaks' => false,
                                                'forced_root_block' => false,
                                                'force_br_newlines' => false,
                                                'force_p_newlines' => false,
                                            ],
                                        ]
                                    );
                                    ?>
                                    <p class="description">
                                        <?php _e('HTML template for testimonial reply emails. Use variables from the list above.', 'codeweber'); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Resume Reply Template -->
                    <div class="card" style="max-width: 100%; margin-bottom: 20px;">
                        <h2><?php _e('Resume Reply Template', 'codeweber'); ?></h2>
                        <p><?php _e('This template is used for automatic reply emails sent to users after resume/CV submission.', 'codeweber'); ?></p>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="resume_reply_enabled">
                                        <?php _e('Enable Resume Reply', 'codeweber'); ?>
                                    </label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" 
                                               name="<?php echo $this->option_name; ?>[resume_reply_enabled]" 
                                               value="1" 
                                               id="resume_reply_enabled"
                                               <?php checked($resume_reply_enabled, true); ?>>
                                        <?php _e('Send automatic reply emails for resumes', 'codeweber'); ?>
                                    </label>
                                    <p class="description">
                                        <?php _e('Note: Reply will be sent if form name contains "resume", "резюме", "cv" or "вакансия".', 'codeweber'); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="resume_reply_subject">
                                        <?php _e('Email Subject', 'codeweber'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" 
                                           name="<?php echo $this->option_name; ?>[resume_reply_subject]" 
                                           id="resume_reply_subject"
                                           value="<?php echo esc_attr($resume_reply_subject); ?>" 
                                           class="regular-text">
                                    <p class="description">
                                        <?php _e('Subject line for resume reply emails. You can use variables like {form_name}, {user_name}.', 'codeweber'); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="resume_reply_template">
                                        <?php _e('Email Template', 'codeweber'); ?>
                                    </label>
                                </th>
                                <td>
                                    <?php
                                    wp_editor(
                                        $resume_reply_template,
                                        'resume_reply_template',
                                        [
                                            'textarea_name' => $this->option_name . '[resume_reply_template]',
                                            'textarea_rows' => 20,
                                            'media_buttons' => false,
                                            'teeny' => false,
                                            'quicktags' => true,
                                            'tinymce' => [
                                                'entity_encoding' => 'raw',
                                                'remove_linebreaks' => false,
                                                'forced_root_block' => false,
                                                'force_br_newlines' => false,
                                                'force_p_newlines' => false,
                                            ],
                                        ]
                                    );
                                    ?>
                                    <p class="description">
                                        <?php _e('HTML template for resume reply emails. Use variables from the list above.', 'codeweber'); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Newsletter Reply Template -->
                    <div class="card" style="max-width: 100%; margin-bottom: 20px;">
                        <h2><?php _e('Newsletter Reply Template', 'codeweber'); ?></h2>
                        <p><?php _e('This template is used for automatic reply emails sent to users after newsletter subscription.', 'codeweber'); ?></p>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="newsletter_reply_enabled">
                                        <?php _e('Enable Newsletter Reply', 'codeweber'); ?>
                                    </label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" 
                                               name="<?php echo $this->option_name; ?>[newsletter_reply_enabled]" 
                                               value="1" 
                                               id="newsletter_reply_enabled"
                                               <?php checked($newsletter_reply_enabled, true); ?>>
                                        <?php _e('Send automatic reply emails for newsletter subscriptions', 'codeweber'); ?>
                                    </label>
                                    <p class="description">
                                        <?php _e('Note: Reply will be sent if form name contains "newsletter" or "подписк".', 'codeweber'); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="newsletter_reply_subject">
                                        <?php _e('Email Subject', 'codeweber'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" 
                                           name="<?php echo $this->option_name; ?>[newsletter_reply_subject]" 
                                           id="newsletter_reply_subject"
                                           value="<?php echo esc_attr($newsletter_reply_subject); ?>" 
                                           class="regular-text">
                                    <p class="description">
                                        <?php _e('Subject line for newsletter reply emails. You can use variables like {form_name}, {user_name}.', 'codeweber'); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="newsletter_reply_template">
                                        <?php _e('Email Template', 'codeweber'); ?>
                                    </label>
                                </th>
                                <td>
                                    <?php
                                    wp_editor(
                                        $newsletter_reply_template,
                                        'newsletter_reply_template',
                                        [
                                            'textarea_name' => $this->option_name . '[newsletter_reply_template]',
                                            'textarea_rows' => 20,
                                            'media_buttons' => false,
                                            'teeny' => false,
                                            'quicktags' => true,
                                            'tinymce' => [
                                                'entity_encoding' => 'raw',
                                                'remove_linebreaks' => false,
                                                'forced_root_block' => false,
                                                'force_br_newlines' => false,
                                                'force_p_newlines' => false,
                                            ],
                                        ]
                                    );
                                    ?>
                                    <p class="description">
                                        <?php _e('HTML template for newsletter reply emails. Use variables from the list above.', 'codeweber'); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}


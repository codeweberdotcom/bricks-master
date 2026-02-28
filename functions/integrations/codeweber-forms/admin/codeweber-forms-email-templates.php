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
        add_action('wp_ajax_codeweber_forms_email_preview', [$this, 'ajax_email_preview']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_email_templates_assets']);
        add_filter('tiny_mce_before_init', [$this, 'configure_tinymce_for_templates'], 10, 2);
        add_filter('wp_kses_allowed_html', [$this, 'allow_all_html_in_templates'], 10, 2);
    }

    /**
     * List of template items for sidebar (id => label).
     *
     * @return array
     */
    public function get_template_items() {
        return [
            'admin_notification'   => __('Admin Notification', 'codeweber'),
            'auto_reply'           => __('Auto-Reply', 'codeweber'),
            'testimonial_reply'    => __('Testimonial Reply', 'codeweber'),
            'resume_reply'         => __('Resume Reply', 'codeweber'),
            'newsletter_reply'     => __('Newsletter Reply', 'codeweber'),
        ];
    }

    /**
     * AJAX: return preview HTML for an email template (variables replaced with sample data).
     */
    public function ajax_email_preview() {
        check_ajax_referer('codeweber_forms_email_preview', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Forbidden'], 403);
        }
        $template_id = isset($_POST['template_id']) ? sanitize_key($_POST['template_id']) : '';
        $items = $this->get_template_items();
        if (!isset($items[$template_id])) {
            wp_send_json_error(['message' => 'Invalid template'], 400);
        }
        $html = isset($_POST['html']) ? wp_unslash($_POST['html']) : '';
        if ($html === '') {
            $option_key = $template_id . '_template';
            $html = $this->get_option($option_key, $this->get_default_template_by_id($template_id));
        }
        $preview_html = $this->replace_variables_for_preview($html);
        wp_send_json_success(['html' => $preview_html]);
    }

    /**
     * Get default template content by template id.
     *
     * @param string $template_id
     * @return string
     */
    private function get_default_template_by_id($template_id) {
        $map = [
            'admin_notification' => 'get_default_admin_template',
            'auto_reply'         => 'get_default_auto_reply_template',
            'testimonial_reply'  => 'get_default_testimonial_reply_template',
            'resume_reply'       => 'get_default_resume_reply_template',
            'newsletter_reply'   => 'get_default_newsletter_reply_template',
        ];
        $method = isset($map[$template_id]) ? $map[$template_id] : '';
        if ($method && is_callable([$this, $method])) {
            return $this->$method();
        }
        return '';
    }

    /**
     * Replace template variables with sample data for preview.
     *
     * @param string $content
     * @return string
     */
    private function replace_variables_for_preview($content) {
        $sample_fields = '<table style="width:100%;border-collapse:collapse;"><thead><tr style="background:#f5f5f5;"><th style="padding:10px;border:1px solid #ddd;">' . __('Field', 'codeweber') . '</th><th style="padding:10px;border:1px solid #ddd;">' . __('Value', 'codeweber') . '</th></tr></thead><tbody>';
        $sample_fields .= '<tr><td style="padding:10px;border:1px solid #ddd;">' . __('Name', 'codeweber') . '</td><td style="padding:10px;border:1px solid #ddd;">' . __('Sample User', 'codeweber') . '</td></tr>';
        $sample_fields .= '<tr><td style="padding:10px;border:1px solid #ddd;">' . __('Email', 'codeweber') . '</td><td style="padding:10px;border:1px solid #ddd;">sample@example.com</td></tr>';
        $sample_fields .= '<tr><td style="padding:10px;border:1px solid #ddd;">' . __('Message', 'codeweber') . '</td><td style="padding:10px;border:1px solid #ddd;">' . __('Sample message text.', 'codeweber') . '</td></tr></tbody></table>';
        $replacements = [
            '{form_name}'       => __('Contact form', 'codeweber'),
            '{form_fields}'     => $sample_fields,
            '{user_name}'       => __('Sample User', 'codeweber'),
            '{user_email}'      => 'sample@example.com',
            '{submission_date}' => date_i18n(get_option('date_format'), time()),
            '{submission_time}' => date('H:i', time()),
            '{user_ip}'         => '127.0.0.1',
            '{user_agent}'      => 'Mozilla/5.0 (Preview)',
            '{site_name}'       => get_bloginfo('name'),
            '{site_url}'        => home_url(),
            '{unsubscribe_url}' => home_url('/unsubscribe/?token=sample'),
        ];
        foreach ($replacements as $key => $value) {
            $content = str_replace($key, $value, $content);
        }
        return $content;
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
            'resume_reply_template',
            'newsletter_reply_template',
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
        $template_items = $this->get_template_items();
        $current_template = isset($_GET['template']) && isset($template_items[$_GET['template']]) ? sanitize_key($_GET['template']) : array_key_first($template_items);
        $base_url = remove_query_arg(['template']);
        $option_name = $this->option_name;

        $this->localize_email_templates_script($current_template);

        ?>
        <div class="wrap">
            <h1><?php _e('Email Templates', 'codeweber'); ?></h1>
            <?php settings_errors('codeweber_forms_email_templates'); ?>

            <div class="codeweber-email-templates-layout">
                <aside class="codeweber-email-templates-sidebar">
                    <nav class="codeweber-email-templates-nav" aria-label="<?php esc_attr_e('Email types', 'codeweber'); ?>">
                        <ul>
                            <?php foreach ($template_items as $id => $label) : ?>
                                <li>
                                    <a href="<?php echo esc_url(add_query_arg('template', $id, $base_url)); ?>" class="<?php echo $id === $current_template ? 'active' : ''; ?>"><?php echo esc_html($label); ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </nav>
                    <details class="codeweber-email-templates-variables" style="margin-top:16px;">
                        <summary><?php _e('Available Variables', 'codeweber'); ?></summary>
                        <p style="margin:8px 0 4px;"><?php _e('You can use these variables in your email templates:', 'codeweber'); ?></p>
                        <table class="widefat" style="font-size:12px;">
                            <tbody>
                                <?php foreach ($variables as $var => $desc) : ?>
                                    <tr><td><code><?php echo esc_html($var); ?></code></td><td><?php echo esc_html($desc); ?></td></tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </details>
                </aside>

                <div class="codeweber-email-templates-main">
                    <form method="post" action="options.php">
                        <?php settings_fields($this->option_group); ?>
                        <input type="hidden" name="template" value="<?php echo esc_attr($current_template); ?>">

                        <?php
                        $panels = [
                            'admin_notification' => [
                                'enabled'  => $admin_enabled,
                                'subject'  => $admin_subject,
                                'template' => $admin_template,
                                'desc'     => __('This template is used for emails sent to administrators when a form is submitted.', 'codeweber'),
                                'enable_label' => __('Enable Admin Notifications', 'codeweber'),
                                'enable_help'  => __('Send email notifications to administrators', 'codeweber'),
                                'subject_help' => __('Subject line for admin notification emails. You can use variables like {form_name}.', 'codeweber'),
                            ],
                            'auto_reply' => [
                                'enabled'  => $auto_reply_enabled,
                                'subject'  => $auto_reply_subject,
                                'template' => $auto_reply_template,
                                'desc'     => __('This template is used for automatic reply emails sent to users after form submission.', 'codeweber'),
                                'enable_label' => __('Enable Auto-Reply', 'codeweber'),
                                'enable_help'  => __('Send automatic reply emails to users', 'codeweber') . ' ' . __('Note: Auto-reply will only be sent if the form contains an email field.', 'codeweber'),
                                'subject_help' => __('Subject line for auto-reply emails. You can use variables like {form_name}, {user_name}.', 'codeweber'),
                            ],
                            'testimonial_reply' => [
                                'enabled'  => $testimonial_reply_enabled,
                                'subject'  => $testimonial_reply_subject,
                                'template' => $testimonial_reply_template,
                                'desc'     => __('This template is used for automatic reply emails sent to users after testimonial submission.', 'codeweber'),
                                'enable_label' => __('Enable Testimonial Reply', 'codeweber'),
                                'enable_help'  => __('Send automatic reply emails for testimonials', 'codeweber') . ' ' . __('Note: Reply will be sent if form name contains "testimonial" or "отзыв".', 'codeweber'),
                                'subject_help' => __('Subject line for testimonial reply emails. You can use variables like {form_name}, {user_name}.', 'codeweber'),
                            ],
                            'resume_reply' => [
                                'enabled'  => $resume_reply_enabled,
                                'subject'  => $resume_reply_subject,
                                'template' => $resume_reply_template,
                                'desc'     => __('This template is used for automatic reply emails sent to users after resume/CV submission.', 'codeweber'),
                                'enable_label' => __('Enable Resume Reply', 'codeweber'),
                                'enable_help'  => __('Send automatic reply emails for resumes', 'codeweber') . ' ' . __('Note: Reply will be sent if form name contains "resume", "резюме", "cv" or "вакансия".', 'codeweber'),
                                'subject_help' => __('Subject line for resume reply emails. You can use variables like {form_name}, {user_name}.', 'codeweber'),
                            ],
                            'newsletter_reply' => [
                                'enabled'  => $newsletter_reply_enabled,
                                'subject'  => $newsletter_reply_subject,
                                'template' => $newsletter_reply_template,
                                'desc'     => __('This template is used for automatic reply emails sent to users after newsletter subscription.', 'codeweber'),
                                'enable_label' => __('Enable Newsletter Reply', 'codeweber'),
                                'enable_help'  => __('Send automatic reply emails for newsletter subscriptions', 'codeweber') . ' ' . __('Note: Reply will be sent if form name contains "newsletter" or "подписк".', 'codeweber'),
                                'subject_help' => __('Subject line for newsletter reply emails. You can use variables like {form_name}, {user_name}.', 'codeweber'),
                            ],
                        ];
                        $field_keys = [
                            'admin_notification'   => ['enabled' => 'admin_notification_enabled', 'subject' => 'admin_notification_subject', 'template' => 'admin_notification_template'],
                            'auto_reply'           => ['enabled' => 'auto_reply_enabled', 'subject' => 'auto_reply_subject', 'template' => 'auto_reply_template'],
                            'testimonial_reply'    => ['enabled' => 'testimonial_reply_enabled', 'subject' => 'testimonial_reply_subject', 'template' => 'testimonial_reply_template'],
                            'resume_reply'         => ['enabled' => 'resume_reply_enabled', 'subject' => 'resume_reply_subject', 'template' => 'resume_reply_template'],
                            'newsletter_reply'     => ['enabled' => 'newsletter_reply_enabled', 'subject' => 'newsletter_reply_subject', 'template' => 'newsletter_reply_template'],
                        ];
                        foreach ($template_items as $id => $label) :
                            $p = $panels[$id] ?? null;
                            $k = $field_keys[$id] ?? null;
                            if (!$p || !$k) continue;
                        ?>
                        <div class="codeweber-email-templates-panel <?php echo $id === $current_template ? 'is-active' : ''; ?>" id="panel-<?php echo esc_attr($id); ?>" data-template="<?php echo esc_attr($id); ?>">
                            <div class="codeweber-email-templates-editor card bg-light">
                                <h2 class="codeweber-email-templates-panel-title"><?php echo esc_html($label); ?></h2>
                                <p class="description"><?php echo esc_html($p['desc']); ?></p>
                                <table class="form-table">
                                    <tr>
                                        <th scope="row"><label for="<?php echo esc_attr($k['enabled']); ?>"><?php echo esc_html($p['enable_label']); ?></label></th>
                                        <td>
                                            <label>
                                                <input type="checkbox" name="<?php echo esc_attr($option_name); ?>[<?php echo esc_attr($k['enabled']); ?>]" value="1" id="<?php echo esc_attr($k['enabled']); ?>" <?php checked(!empty($p['enabled'])); ?>>
                                                <?php echo esc_html($p['enable_help']); ?>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="<?php echo esc_attr($k['subject']); ?>"><?php _e('Email Subject', 'codeweber'); ?></label></th>
                                        <td>
                                            <input type="text" name="<?php echo esc_attr($option_name); ?>[<?php echo esc_attr($k['subject']); ?>]" id="<?php echo esc_attr($k['subject']); ?>" value="<?php echo esc_attr($p['subject']); ?>" class="regular-text">
                                            <p class="description"><?php echo esc_html($p['subject_help']); ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="<?php echo esc_attr($k['template']); ?>"><?php _e('Email Template', 'codeweber'); ?></label></th>
                                        <td>
                                            <?php
                                            wp_editor($p['template'], $k['template'], [
                                                'textarea_name' => $option_name . '[' . $k['template'] . ']',
                                                'textarea_rows' => 16,
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
                                            ]);
                                            ?>
                                        </td>
                                    </tr>
                                </table>
                                <div class="codeweber-email-templates-preview-wrap">
                                    <p>
                                        <button type="button" class="button button-secondary codeweber-email-preview-btn"><?php _e('Update preview', 'codeweber'); ?></button>
                                    </p>
                                    <iframe class="codeweber-email-preview-iframe" title="<?php esc_attr_e('Email preview', 'codeweber'); ?>" style="width:100%; min-height:400px; border:1px solid #c3c4c7; background:#fff;"></iframe>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <?php submit_button(); ?>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Enqueue CSS/JS for the email templates page (sidebar + preview).
     */
    public function enqueue_email_templates_assets($hook) {
        $is_email_templates = ( $hook === 'codeweber_form_page_codeweber-forms-email-templates' )
            || ( strpos( (string) $hook, 'codeweber-forms-email-templates' ) !== false )
            || ( isset( $_GET['page'] ) && $_GET['page'] === 'codeweber-forms-email-templates' );
        if ( ! $is_email_templates ) {
            return;
        }
        wp_enqueue_style('codeweber-forms-email-templates', CODEWEBER_FORMS_URL . '/admin/assets/email-templates.css', [], CODEWEBER_FORMS_VERSION);
        wp_enqueue_script('codeweber-forms-email-templates', CODEWEBER_FORMS_URL . '/admin/assets/email-templates.js', ['jquery'], CODEWEBER_FORMS_VERSION, true);
    }

    private function localize_email_templates_script($current_template) {
        wp_localize_script('codeweber-forms-email-templates', 'codeweberEmailTemplates', [
            'ajaxUrl'   => admin_url('admin-ajax.php'),
            'nonce'     => wp_create_nonce('codeweber_forms_email_preview'),
            'current'   => $current_template,
            'editorIds' => [
                'admin_notification' => 'admin_notification_template',
                'auto_reply'         => 'auto_reply_template',
                'testimonial_reply'  => 'testimonial_reply_template',
                'resume_reply'       => 'resume_reply_template',
                'newsletter_reply'   => 'newsletter_reply_template',
            ],
        ]);
    }
}

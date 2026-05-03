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
            'document_email'       => __('Document Email', 'codeweber'),
            'event_notification'   => __('Event Notification', 'codeweber'),
            'email_wrapper'        => __('Email Wrapper', 'codeweber'),
        ];
    }

    /**
     * Whether email wrapper is enabled.
     */
    public static function is_wrapper_enabled() {
        $options = get_option('codeweber_forms_email_templates', []);
        return !empty($options['wrapper_enabled']);
    }

    /**
     * Get wrapper template HTML (from options or default Simple design).
     * Returns false/empty if saved template is the legacy CSS-class version.
     */
    public static function get_wrapper_html() {
        $options = get_option('codeweber_forms_email_templates', []);
        if (!empty($options['wrapper_template'])) {
            $tpl = $options['wrapper_template'];
            // Detect legacy template (CSS classes, not inline styles) — fall back to default
            if (strpos($tpl, 'class="wrapper"') !== false || strpos($tpl, '.wrapper {') !== false) {
                $inst = new self();
                return $inst->get_default_simple_wrapper_template();
            }
            return $tpl;
        }
        $inst = new self();
        return $inst->get_default_simple_wrapper_template();
    }

    /**
     * Build social links HTML for email footer (table-based, email-safe).
     */
    public static function get_social_links_html() {
        $socials = get_option('socials_urls', []);
        if (empty($socials) || !is_array($socials)) {
            return '';
        }
        $networks = [
            'vk'            => ['label' => 'VK',        'color' => '#4a76a8'],
            'telegram'      => ['label' => 'Telegram',  'color' => '#2ca5e0'],
            'whatsapp'      => ['label' => 'WhatsApp',  'color' => '#25d366'],
            'instagram'     => ['label' => 'Instagram', 'color' => '#c13584'],
            'facebook'      => ['label' => 'Facebook',  'color' => '#1877f2'],
            'youtube'       => ['label' => 'YouTube',   'color' => '#ff0000'],
            'rutube'        => ['label' => 'Rutube',    'color' => '#1f2226'],
            'odnoklassniki' => ['label' => 'OK',        'color' => '#f7931e'],
            'tik-tok'       => ['label' => 'TikTok',    'color' => '#010101'],
            'twitter'       => ['label' => 'X',         'color' => '#14171a'],
            'linkedin'      => ['label' => 'LinkedIn',  'color' => '#0a66c2'],
            'vkvideo'       => ['label' => 'VK Video',  'color' => '#4a76a8'],
            'yandex-dzen'   => ['label' => 'Dzen',      'color' => '#f73e2b'],
            'viber'         => ['label' => 'Viber',     'color' => '#7360f2'],
            'discord'       => ['label' => 'Discord',   'color' => '#5865f2'],
            'github'        => ['label' => 'GitHub',    'color' => '#24292e'],
            'pinterest'     => ['label' => 'Pinterest', 'color' => '#e60023'],
        ];
        $links = [];
        foreach ($networks as $key => $net) {
            if (!empty($socials[$key])) {
                $url   = esc_url($socials[$key]);
                $label = esc_html($net['label']);
                $color = $net['color'];
                $links[] = '<a href="' . $url . '" style="display:inline-block;margin:2px 3px;padding:4px 10px;background-color:' . $color . ';color:#ffffff;text-decoration:none;font-size:11px;font-family:Arial,Helvetica,sans-serif;border-radius:3px;line-height:1.4;">' . $label . '</a>';
            }
        }
        if (empty($links)) {
            return '';
        }
        return '<div style="margin:10px 0 0;text-align:center;">' . implode('', $links) . '</div>';
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
            // Map template_id to option key (some use different key names)
            $option_key_map = ['email_wrapper' => 'wrapper_template'];
            $option_key = $option_key_map[$template_id] ?? ($template_id . '_template');
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
            'email_wrapper'      => 'get_default_simple_wrapper_template',
            'document_email'     => 'get_default_document_email_template',
            'event_notification' => 'get_default_event_notification_template',
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
        $sample_content = '<p style="font-size:15px;color:#333;line-height:1.6;">' . __('Sample email content goes here.', 'codeweber') . '</p>';
        $sample_socials = '<div style="margin:10px 0 0;text-align:center;"><a href="#" style="display:inline-block;margin:2px 3px;padding:4px 10px;background-color:#4a76a8;color:#fff;text-decoration:none;font-size:11px;font-family:Arial,sans-serif;border-radius:3px;">VK</a><a href="#" style="display:inline-block;margin:2px 3px;padding:4px 10px;background-color:#2ca5e0;color:#fff;text-decoration:none;font-size:11px;font-family:Arial,sans-serif;border-radius:3px;">Telegram</a></div>';
        $site_name   = get_bloginfo('name');
        $img_style   = 'max-height:60px;max-width:200px;display:block;margin:0 auto;';
        $text_logo   = '<span style="color:#fff;font-size:22px;font-weight:bold;font-family:Arial,sans-serif;">' . esc_html($site_name) . '</span>';
        $redux     = get_option('redux_demo', []);
        $dark_url  = !empty($redux['opt-dark-logo']['url'])  ? $redux['opt-dark-logo']['url']  : '';
        $light_url = !empty($redux['opt-light-logo']['url']) ? $redux['opt-light-logo']['url'] : '';
        if (!$dark_url && !$light_url) {
            $logo_id = get_theme_mod('custom_logo');
            if ($logo_id) {
                $src = wp_get_attachment_image_src($logo_id, 'full');
                if ($src) { $dark_url = $light_url = $src[0]; }
            }
        }
        $sample_logo_dark  = $dark_url  ? '<img src="' . esc_url($dark_url)  . '" alt="' . esc_attr($site_name) . '" style="' . $img_style . '">' : $text_logo;
        $sample_logo_light = $light_url ? '<img src="' . esc_url($light_url) . '" alt="' . esc_attr($site_name) . '" style="' . $img_style . '">' : $sample_logo_dark;
        $sample_logo = $sample_logo_dark;
        $sample_reg_details = '<table style="border-collapse:collapse;width:100%;margin:12px 0;">'
            . '<tr><th align="left" style="padding:4px 12px 4px 0;">' . __('Name', 'codeweber') . ':</th><td>' . __('Sample User', 'codeweber') . '</td></tr>'
            . '<tr><th align="left" style="padding:4px 12px 4px 0;">' . __('Email', 'codeweber') . ':</th><td>sample@example.com</td></tr>'
            . '<tr><th align="left" style="padding:4px 12px 4px 0;">' . __('Phone', 'codeweber') . ':</th><td>+7 900 000-00-00</td></tr>'
            . '</table>';
        $replacements = [
            '{form_name}'       => __('Contact form', 'codeweber'),
            '{form_fields}'     => $sample_fields,
            '{user_name}'       => __('Sample User', 'codeweber'),
            '{user_email}'      => 'sample@example.com',
            '{submission_date}' => date_i18n(get_option('date_format'), time()),
            '{submission_time}' => date('H:i', time()),
            '{user_ip}'         => '127.0.0.1',
            '{user_agent}'      => 'Mozilla/5.0 (Preview)',
            '{site_name}'       => $site_name,
            '{site_url}'        => home_url(),
            '{unsubscribe_url}' => home_url('/unsubscribe/?token=sample'),
            '{content}'         => $sample_content,
            '{social_links}'    => $sample_socials,
            '{site_logo}'       => $sample_logo,
            '{site_logo_dark}'  => $sample_logo_dark,
            '{site_logo_light}' => $sample_logo_light,
            '{document_title}'  => __('Sample Document', 'codeweber'),
            '{document_link}'   => '<a href="#">' . __('Sample Document', 'codeweber') . '</a>',
            '{document_image}'  => '<a href="#" style="display:block;text-align:center;margin:16px 15% 16px;"><img src="https://via.placeholder.com/400x280/e9ecef/6c757d?text=Document+Preview" alt="' . esc_attr__('Sample Document', 'codeweber') . '" style="max-width:100%;height:auto;border:1px solid #ddd;border-radius:4px;display:inline-block;"></a>',
            '{event_title}'     => __('Sample Event', 'codeweber'),
            '{reg_details}'     => $sample_reg_details,
            '{reg_name}'        => __('Sample User', 'codeweber'),
            '{reg_email}'       => 'sample@example.com',
            '{reg_admin_url}'   => '#',
            '{event_admin_url}' => '#',
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

        if (isset($input['wrapper_enabled'])) {
            $sanitized['wrapper_enabled'] = (bool) $input['wrapper_enabled'];
        } else {
            $sanitized['wrapper_enabled'] = false;
        }

        if (isset($input['wrapper_template'])) {
            $template = $input['wrapper_template'];
            $template = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $template);
            $template = preg_replace('/<iframe[^>]*>.*?<\/iframe>/is', '', $template);
            $sanitized['wrapper_template'] = $template;
        }
        $valid_colors = array_keys($this->get_theme_colors());
        foreach (['wrapper_header_color', 'wrapper_footer_color'] as $color_key) {
            if (isset($input[$color_key]) && in_array($input[$color_key], $valid_colors, true)) {
                $sanitized[$color_key] = $input[$color_key];
            }
        }

        if (isset($input['document_email_enabled'])) {
            $sanitized['document_email_enabled'] = (bool) $input['document_email_enabled'];
        } else {
            $sanitized['document_email_enabled'] = false;
        }
        if (isset($input['document_email_subject'])) {
            $sanitized['document_email_subject'] = sanitize_text_field($input['document_email_subject']);
        }
        if (isset($input['document_email_template'])) {
            $template = $input['document_email_template'];
            $template = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $template);
            $template = preg_replace('/<iframe[^>]*>.*?<\/iframe>/is', '', $template);
            $sanitized['document_email_template'] = $template;
        }

        if (isset($input['event_notification_enabled'])) {
            $sanitized['event_notification_enabled'] = (bool) $input['event_notification_enabled'];
        } else {
            $sanitized['event_notification_enabled'] = false;
        }
        if (isset($input['event_notification_subject'])) {
            $sanitized['event_notification_subject'] = sanitize_text_field($input['event_notification_subject']);
        }
        if (isset($input['event_notification_template'])) {
            $template = $input['event_notification_template'];
            $template = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $template);
            $template = preg_replace('/<iframe[^>]*>.*?<\/iframe>/is', '', $template);
            $sanitized['event_notification_template'] = $template;
        }

        return $sanitized;
    }
    
    /**
     * Get option value
     */
    private function get_option($key, $default = '') {
        $options = get_option($this->option_name, []);
        $value = isset($options[$key]) ? $options[$key] : $default;
        
        // Очищаем &nbsp; и нормализуем HTML для шаблонов (кроме wrapper — он хранится как полный HTML-документ)
        if (strpos($key, '_template') !== false && $key !== 'wrapper_template' && is_string($value)) {
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
            'email_wrapper_template',
            'document_email_template',
            'event_notification_template',
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
     * Theme color palette for email color selectors.
     */
    private function get_theme_colors() {
        return [
            '#3f78e0' => 'Blue (Primary)',
            '#5eb9f0' => 'Sky',
            '#605dba' => 'Grape',
            '#747ed1' => 'Purple',
            '#a07cc5' => 'Violet',
            '#d16b86' => 'Pink',
            '#e668b3' => 'Fuchsia',
            '#e2626b' => 'Red',
            '#f78b77' => 'Orange',
            '#fab758' => 'Yellow',
            '#45c4a0' => 'Green',
            '#7cb798' => 'Leaf',
            '#54a8c7' => 'Aqua',
            '#343f52' => 'Navy',
            '#262b32' => 'Dark',
            '#9499a3' => 'Ash',
            '#ffffff' => 'White',
            '#f6f7f9' => 'Light Gray',
            '#333333' => 'Charcoal',
        ];
    }

    /**
     * Get default Simple email wrapper template (inline styles — TinyMCE-safe).
     */
    public function get_default_simple_wrapper_template() {
        $site_name = esc_html(get_bloginfo('name'));
        return '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
<body style="margin:0;padding:20px 0;background-color:#f4f4f4;font-family:Arial,Helvetica,sans-serif;">
<div style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:4px;overflow:hidden;">
<div style="padding:32px 40px;color:#333333;line-height:1.6;font-size:15px;">
{content}
</div>
<div style="padding:20px 40px;text-align:center;border-top:1px solid #eeeeee;color:#999999;font-size:12px;font-family:Arial,Helvetica,sans-serif;">
<p style="margin:0 0 8px;">' . $site_name . ' &bull; <a href="{site_url}" style="color:#999999;text-decoration:none;">{site_url}</a></p>
{social_links}
</div>
</div>
</body>
</html>';
    }

    /**
     * Get default Branded email wrapper template (inline styles + site logo).
     *
     * @param string|null $header_color Hex color for header bg. Null = read from saved options.
     * @param string|null $footer_color Hex color for footer bg. Null = read from saved options.
     */
    public function get_default_branded_wrapper_template($header_color = null, $footer_color = null) {
        if ($header_color === null) {
            $header_color = $this->get_option('wrapper_header_color', '#3f78e0');
        }
        if ($footer_color === null) {
            $footer_color = $this->get_option('wrapper_footer_color', '#343f52');
        }
        $site_name = esc_html(get_bloginfo('name'));
        return '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
<body style="margin:0;padding:20px 0;background-color:#f4f4f4;font-family:Arial,Helvetica,sans-serif;">
<div style="max-width:600px;margin:0 auto;background:#ffffff;overflow:hidden;">
<div style="background-color:' . esc_attr($header_color) . ';padding:24px 40px;text-align:center;">
{site_logo_dark}
</div>
<div style="padding:32px 40px;background-color:#f9f9f9;color:#333333;line-height:1.6;font-size:15px;">
{content}
</div>
<div style="background-color:' . esc_attr($footer_color) . ';padding:20px 40px;text-align:center;color:#cccccc;font-size:12px;font-family:Arial,Helvetica,sans-serif;">
<p style="margin:0 0 4px;">&copy; ' . $site_name . '</p>
<p style="margin:0 0 10px;"><a href="{site_url}" style="color:#cccccc;text-decoration:none;">{site_url}</a></p>
{social_links}
</div>
</div>
</body>
</html>';
    }

    /**
     * Get default Document Email template.
     */
    public function get_default_document_email_template() {
        return '<p>' . __('You requested {document_link} on {site_name}.', 'codeweber') . '</p>'
            . '<p>' . __('Best regards,', 'codeweber') . '<br>{site_name}</p>';
    }

    /**
     * Get default Event Notification template (admin email on new registration).
     */
    public function get_default_event_notification_template() {
        return '<p>' . __('New registration for event:', 'codeweber') . ' <strong>{event_title}</strong></p>'
            . '{reg_details}'
            . '<p style="margin-top:16px;">'
            . '<a href="{reg_admin_url}">' . __('View registration in admin', 'codeweber') . '</a>'
            . ' &nbsp;|&nbsp; '
            . '<a href="{event_admin_url}">' . __('View event', 'codeweber') . '</a>'
            . '</p>';
    }

    /**
     * Get available variables
     */
    private function get_available_variables() {
        return [
            '{form_name}'       => __('Form name', 'codeweber'),
            '{user_name}'       => __('User name from form', 'codeweber'),
            '{user_email}'      => __('User email from form', 'codeweber'),
            '{submission_date}' => __('Submission date', 'codeweber'),
            '{submission_time}' => __('Submission time (24h format)', 'codeweber'),
            '{form_fields}'     => __('All form fields in table format', 'codeweber'),
            '{user_ip}'         => __('User IP address', 'codeweber'),
            '{user_agent}'      => __('User browser/agent', 'codeweber'),
            '{site_name}'       => __('Site name', 'codeweber'),
            '{site_url}'        => __('Site URL', 'codeweber'),
            '{unsubscribe_url}' => __('Unsubscribe link for newsletter subscriptions', 'codeweber'),
            '{content}'         => __('Email body content (Email Wrapper only)', 'codeweber'),
            '{social_links}'    => __('Social links from theme settings (Email Wrapper only)', 'codeweber'),
            '{site_logo}'       => __('Dark logo (or site name fallback) — Email Wrapper only', 'codeweber'),
            '{site_logo_dark}'  => __('Dark logo (or site name fallback) — Email Wrapper only', 'codeweber'),
            '{site_logo_light}' => __('Light logo (or dark logo fallback) — Email Wrapper only', 'codeweber'),
            '{document_title}'  => __('Document title (Document Email only)', 'codeweber'),
            '{document_link}'   => __('Document download link (Document Email only)', 'codeweber'),
            '{document_image}'  => __('Document thumbnail linked to download (Document Email only)', 'codeweber'),
            '{event_title}'     => __('Event title (Event Notification only)', 'codeweber'),
            '{reg_details}'     => __('Registration details table: name, email, phone, seats, message (Event Notification only)', 'codeweber'),
            '{reg_name}'        => __('Registrant name (Event Notification only)', 'codeweber'),
            '{reg_email}'       => __('Registrant email (Event Notification only)', 'codeweber'),
            '{reg_admin_url}'   => __('Admin link to registration record (Event Notification only)', 'codeweber'),
            '{event_admin_url}' => __('Admin link to event (Event Notification only)', 'codeweber'),
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

        $wrapper_enabled      = $this->get_option('wrapper_enabled', false);
        $wrapper_template     = self::get_wrapper_html();
        $wrapper_header_color = $this->get_option('wrapper_header_color', '#3f78e0');
        $wrapper_footer_color = $this->get_option('wrapper_footer_color', '#343f52');

        $document_email_enabled  = $this->get_option('document_email_enabled', false);
        $document_email_subject  = $this->get_option('document_email_subject', '{site_name} — {document_title}');
        $document_email_template = $this->get_option('document_email_template', $this->get_default_document_email_template());

        $event_notification_enabled  = $this->get_option('event_notification_enabled', false);
        $event_notification_subject  = $this->get_option('event_notification_subject', __('New registration: {event_title}', 'codeweber'));
        $event_notification_template = $this->get_option('event_notification_template', $this->get_default_event_notification_template());

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
                            'document_email' => [
                                'enabled'      => $document_email_enabled,
                                'subject'      => $document_email_subject,
                                'template'     => $document_email_template,
                                'desc'         => __('Email sent to the user when they request a document. Available variables: {document_title}, {document_link}, {site_name}, {site_url}.', 'codeweber'),
                                'enable_label' => __('Use Custom Document Email Template', 'codeweber'),
                                'enable_help'  => __('Use this template instead of the default hardcoded email.', 'codeweber'),
                                'subject_help' => __('Subject line. You can use {site_name}, {document_title}.', 'codeweber'),
                            ],
                            'event_notification' => [
                                'enabled'      => $event_notification_enabled,
                                'subject'      => $event_notification_subject,
                                'template'     => $event_notification_template,
                                'desc'         => __('Admin notification email sent when someone registers for an event. Available variables: {event_title}, {reg_details}, {reg_name}, {reg_email}, {reg_admin_url}, {event_admin_url}.', 'codeweber'),
                                'enable_label' => __('Use Custom Event Notification Template', 'codeweber'),
                                'enable_help'  => __('Use this template instead of the default hardcoded email.', 'codeweber'),
                                'subject_help' => __('Subject line. You can use {event_title}, {site_name}.', 'codeweber'),
                            ],
                            'email_wrapper' => [
                                'enabled'      => $wrapper_enabled,
                                'template'     => $wrapper_template,
                                'desc'         => __('Universal email wrapper applied to all outgoing emails. Use {content} for the inner content, {social_links} for social links from theme settings, {site_logo_dark} / {site_logo_light} for logos.', 'codeweber'),
                                'enable_label' => __('Use Email Wrapper', 'codeweber'),
                                'enable_help'  => __('Wrap all outgoing emails in this template. The inner content replaces {content}.', 'codeweber'),
                                'no_subject'   => true,
                                'has_presets'  => true,
                                'use_textarea' => true,
                                'color_fields' => [
                                    'wrapper_header_color' => [
                                        'label'   => __('Header Background', 'codeweber'),
                                        'value'   => $wrapper_header_color,
                                        'default' => '#3f78e0',
                                        'target'  => 'header',
                                    ],
                                    'wrapper_footer_color' => [
                                        'label'   => __('Footer Background', 'codeweber'),
                                        'value'   => $wrapper_footer_color,
                                        'default' => '#343f52',
                                        'target'  => 'footer',
                                    ],
                                ],
                                'vars'         => [
                                    '{content}'         => __('Email body', 'codeweber'),
                                    '{site_logo_dark}'  => __('Logo (dark)', 'codeweber'),
                                    '{site_logo_light}' => __('Logo (light)', 'codeweber'),
                                    '{site_name}'       => __('Site name', 'codeweber'),
                                    '{site_url}'        => __('Site URL', 'codeweber'),
                                    '{social_links}'    => __('Social links', 'codeweber'),
                                ],
                            ],
                        ];
                        $field_keys = [
                            'admin_notification'   => ['enabled' => 'admin_notification_enabled', 'subject' => 'admin_notification_subject', 'template' => 'admin_notification_template'],
                            'auto_reply'           => ['enabled' => 'auto_reply_enabled', 'subject' => 'auto_reply_subject', 'template' => 'auto_reply_template'],
                            'testimonial_reply'    => ['enabled' => 'testimonial_reply_enabled', 'subject' => 'testimonial_reply_subject', 'template' => 'testimonial_reply_template'],
                            'resume_reply'         => ['enabled' => 'resume_reply_enabled', 'subject' => 'resume_reply_subject', 'template' => 'resume_reply_template'],
                            'newsletter_reply'     => ['enabled' => 'newsletter_reply_enabled', 'subject' => 'newsletter_reply_subject', 'template' => 'newsletter_reply_template'],
                            'document_email'       => ['enabled' => 'document_email_enabled', 'subject' => 'document_email_subject', 'template' => 'document_email_template'],
                            'event_notification'   => ['enabled' => 'event_notification_enabled', 'subject' => 'event_notification_subject', 'template' => 'event_notification_template'],
                            'email_wrapper'        => ['enabled' => 'wrapper_enabled', 'template' => 'wrapper_template'],
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
                            <?php if (empty($p['no_subject'])) : ?>
                            <tr>
                                        <th scope="row"><label for="<?php echo esc_attr($k['subject']); ?>"><?php _e('Email Subject', 'codeweber'); ?></label></th>
                                        <td>
                                            <input type="text" name="<?php echo esc_attr($option_name); ?>[<?php echo esc_attr($k['subject']); ?>]" id="<?php echo esc_attr($k['subject']); ?>" value="<?php echo esc_attr($p['subject']); ?>" class="regular-text">
                                            <p class="description"><?php echo esc_html($p['subject_help']); ?></p>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                        <th scope="row"><label for="<?php echo esc_attr($k['template']); ?>"><?php _e('Email Template', 'codeweber'); ?></label></th>
                                <td>
                                    <?php if (!empty($p['color_fields'])) : ?>
                                    <div style="margin-bottom:12px;display:flex;flex-wrap:wrap;gap:12px;align-items:center;">
                                        <?php foreach ($p['color_fields'] as $cf_key => $cf) : ?>
                                        <label style="display:flex;align-items:center;gap:6px;font-size:13px;">
                                            <span><?php echo esc_html($cf['label']); ?>:</span>
                                            <select
                                                name="<?php echo esc_attr($option_name); ?>[<?php echo esc_attr($cf_key); ?>]"
                                                id="<?php echo esc_attr($cf_key); ?>"
                                                class="codeweber-email-color-select"
                                                data-color-target="<?php echo esc_attr($cf['target']); ?>"
                                                data-default-color="<?php echo esc_attr($cf['default']); ?>"
                                            >
                                                <?php foreach ($this->get_theme_colors() as $hex => $cname) : ?>
                                                <option value="<?php echo esc_attr($hex); ?>" <?php selected($cf['value'], $hex); ?> style="background:<?php echo esc_attr($hex); ?>;"><?php echo esc_html($cname); ?> (<?php echo esc_html($hex); ?>)</option>
                                                <?php endforeach; ?>
                                            </select>
                                            <span style="display:inline-block;width:18px;height:18px;border-radius:3px;border:1px solid #ccc;background:<?php echo esc_attr($cf['value']); ?>;vertical-align:middle;" class="codeweber-color-swatch" data-for="<?php echo esc_attr($cf_key); ?>"></span>
                                        </label>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($p['has_presets'])) : ?>
                                    <div style="margin-bottom:12px;">
                                        <button type="button" class="button codeweber-email-preset-btn" data-preset="simple"><?php _e('Simple', 'codeweber'); ?></button>
                                        <button type="button" class="button codeweber-email-preset-btn" data-preset="branded" style="margin-left:6px;"><?php _e('Branded', 'codeweber'); ?></button>
                                        <span class="description" style="margin-left:10px;"><?php _e('Apply a preset design — overwrites current template.', 'codeweber'); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($p['use_textarea'])) : ?>
                                    <?php if (!empty($p['vars'])) : ?>
                                    <div style="margin-bottom:8px;display:flex;flex-wrap:wrap;gap:4px;align-items:center;">
                                        <span style="font-size:12px;color:#666;margin-right:4px;"><?php _e('Insert:', 'codeweber'); ?></span>
                                        <?php foreach ($p['vars'] as $var => $label) : ?>
                                        <button type="button" class="button button-small codeweber-email-var-btn" data-var="<?php echo esc_attr($var); ?>" title="<?php echo esc_attr($var); ?>"><?php echo esc_html($label); ?></button>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                    <textarea
                                        id="<?php echo esc_attr($k['template']); ?>"
                                        name="<?php echo esc_attr($option_name); ?>[<?php echo esc_attr($k['template']); ?>]"
                                        rows="22"
                                        class="codeweber-email-template-textarea"
                                        style="width:100%;font-family:monospace;font-size:12px;resize:vertical;"
                                    ><?php echo esc_textarea($p['template']); ?></textarea>
                                    <p class="description" style="margin-top:4px;"><?php _e('Full HTML document. Saved as-is — TinyMCE is not used here.', 'codeweber'); ?></p>
                                    <?php else : ?>
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
                                    <?php endif; ?>
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
            'ajaxUrl'       => admin_url('admin-ajax.php'),
            'nonce'         => wp_create_nonce('codeweber_forms_email_preview'),
            'current'       => $current_template,
            'editorIds'     => [
                'admin_notification' => 'admin_notification_template',
                'auto_reply'         => 'auto_reply_template',
                'testimonial_reply'  => 'testimonial_reply_template',
                'resume_reply'       => 'resume_reply_template',
                'newsletter_reply'   => 'newsletter_reply_template',
                'document_email'     => 'document_email_template',
                'event_notification' => 'event_notification_template',
                'email_wrapper'      => 'wrapper_template',
            ],
            'wrapperPresets' => [
                'simple'   => $this->get_default_simple_wrapper_template(),
                'branded'  => $this->get_default_branded_wrapper_template(),
            ],
            'wrapperColorDefaults' => [
                'header' => '#3f78e0',
                'footer' => '#343f52',
            ],
            'presetConfirm'  => __('This will replace the current template with the preset. Continue?', 'codeweber'),
        ]);
    }
}

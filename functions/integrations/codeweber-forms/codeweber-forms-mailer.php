<?php
/**
 * CodeWeber Forms Mailer
 * 
 * Email sending functionality with SMTP integration
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

class CodeweberFormsMailer {
    /**
     * Send email
     */
    public static function send($form_id, $form_data, $recipient, $subject, $message) {
        global $opt_name;
        
        // Получаем SMTP настройки из Redux
        $smtp_enabled = class_exists('Redux') ? Redux::get_option($opt_name, 'smtp_enabled') : false;
        
        if ($smtp_enabled) {
            // Используем SMTP
            return self::send_via_smtp($form_id, $form_data, $recipient, $subject, $message);
        } else {
            // Используем стандартный wp_mail
            return wp_mail($recipient, $subject, $message, ['Content-Type: text/html; charset=UTF-8']);
        }
    }
    
    /**
     * Send via SMTP
     */
    private static function send_via_smtp($form_id, $form_data, $recipient, $subject, $message) {
        global $opt_name;
        
        if (!class_exists('Redux')) {
            // Fallback to wp_mail if Redux not available
            return wp_mail($recipient, $subject, $message, ['Content-Type: text/html; charset=UTF-8']);
        }
        
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
            require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
            require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
        }
        
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            $mail->isSMTP();
            $mail->Host = Redux::get_option($opt_name, 'smtp_host');
            $mail->Port = Redux::get_option($opt_name, 'smtp_port');
            $mail->SMTPAuth = true;
            $mail->Username = Redux::get_option($opt_name, 'smtp_username');
            $mail->Password = Redux::get_option($opt_name, 'smtp_password');
            
            $encryption = Redux::get_option($opt_name, 'smtp_encryption');
            if ($encryption === 'ssl') {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($encryption === 'tls') {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            }
            
            // Отправитель из настроек формы или Redux
            $from_email = !empty($form_data['senderEmail']) 
                ? $form_data['senderEmail'] 
                : Redux::get_option($opt_name, 'smtp_from_email');
            $from_name = !empty($form_data['senderName']) 
                ? $form_data['senderName'] 
                : Redux::get_option($opt_name, 'smtp_from_name');
            
            $mail->setFrom($from_email, $from_name);
            $mail->addAddress($recipient);
            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->isHTML(true);
            
            // Прикрепляем файлы если есть
            if (!empty($form_data['files'])) {
                foreach ($form_data['files'] as $file) {
                    if (isset($file['path']) && file_exists($file['path'])) {
                        $mail->addAttachment($file['path'], $file['name'] ?? '');
                    }
                }
            }
            
            return $mail->send();
        } catch (\Exception $e) {
            error_log('Form Mailer Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Process email template
     */
    public static function process_template($template, $data) {
        // Заменяем переменные в шаблоне
        $submission_timestamp = strtotime($data['submission_date'] ?? 'now');
        
        // Форматируем поля формы
        $form_fields_html = self::format_form_fields($data['fields'] ?? []);
        
        // Добавляем UTM данные, если есть
        $utm_data = [];
        if (isset($data['fields']['_utm_data']) && is_array($data['fields']['_utm_data'])) {
            $utm_data = $data['fields']['_utm_data'];
        }
        
        $utm_html = '';
        if (!empty($utm_data)) {
            $utm_html = CodeweberFormsUTM::format_utm_for_email($utm_data);
        }
        
        $replacements = [
            '{form_name}'        => $data['form_name'] ?? '',
            '{form_fields}'      => $form_fields_html . $utm_html,
            '{user_name}'        => $data['user_name'] ?? '',
            '{user_email}'       => $data['user_email'] ?? '',
            '{submission_date}'  => date_i18n(get_option('date_format'), $submission_timestamp),
            '{submission_time}'  => date('H:i', $submission_timestamp), // 24-часовой формат
            '{user_ip}'          => $data['ip_address'] ?? '',
            '{user_agent}'       => $data['user_agent'] ?? '',
            '{site_name}'        => get_bloginfo('name'),
            '{site_url}'         => home_url(),
            // Для newsletter шаблонов может быть передан URL для отписки
            '{unsubscribe_url}'  => $data['unsubscribe_url'] ?? '',
        ];
        
        $content = $template;
        foreach ($replacements as $key => $value) {
            $content = str_replace($key, $value, $content);
        }
        
        return $content;
    }
    
    /**
     * Format form fields for email
     */
    private static function format_form_fields($fields) {
        if (empty($fields) || !is_array($fields)) {
            return '<p>' . __('No fields provided.', 'codeweber') . '</p>';
        }
        
        $html = '<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">';
        $html .= '<thead><tr style="background-color: #f5f5f5;"><th style="padding: 10px; text-align: left; border: 1px solid #ddd;">' . __('Field', 'codeweber') . '</th>';
        $html .= '<th style="padding: 10px; text-align: left; border: 1px solid #ddd;">' . __('Value', 'codeweber') . '</th></tr></thead>';
        $html .= '<tbody>';
        
        foreach ($fields as $field_name => $field_value) {
            // Пропускаем служебные поля UTM (они обрабатываются отдельно)
            if ($field_name === '_utm_data') {
                continue;
            }
            
            // Специальная обработка для newsletter_consents
            if ($field_name === 'newsletter_consents' && is_array($field_value)) {
                $consent_links = [];
                
                foreach ($field_value as $doc_id => $value) {
                    // Проверяем, является ли value массивом с информацией о версии
                    if (is_array($value) && isset($value['document_id'])) {
                        $doc_id = intval($value['document_id']);
                        $doc_version = $value['document_version'] ?? $value['document_version_timestamp'] ?? null;
                    } else {
                        $doc_id = intval($doc_id);
                        $doc_version = null;
                    }
                    
                    // Проверяем, что согласие дано (value === '1' или в массиве)
                    $is_consented = false;
                    if (is_array($value)) {
                        $is_consented = isset($value['value']) && ($value['value'] === '1' || $value['value'] === 1);
                    } else {
                        $is_consented = ($value === '1' || $value === 1);
                    }
                    
                    if ($is_consented) {
                        // Используем существующую функцию для получения URL документа с версией
                        if (function_exists('codeweber_forms_get_document_url')) {
                            $doc_url = codeweber_forms_get_document_url($doc_id, $doc_version);
                            $doc_title = codeweber_forms_get_document_title($doc_id);
                            
                            if ($doc_url && $doc_title) {
                                $consent_links[] = '<a href="' . esc_url($doc_url) . '" style="color: #0073aa; text-decoration: underline;">' . esc_html($doc_title) . '</a>';
                        } else {
                            // Fallback: пытаемся получить напрямую
                            $doc = get_post($doc_id);
                            if ($doc && $doc->post_status === 'publish') {
                                // Пытаемся найти ревизию, если указана версия
                                if ($doc_version && function_exists('codeweber_forms_find_revision_by_version')) {
                                    $revision_id = codeweber_forms_find_revision_by_version($doc_id, $doc_version);
                                    if ($revision_id) {
                                        $doc_url = admin_url('revision.php?revision=' . $revision_id);
                                    } else {
                                        // Ревизия не найдена - простая ссылка без параметра version
                                        $doc_url = get_permalink($doc_id);
                                    }
                                } else {
                                    $doc_url = get_permalink($doc_id);
                                }
                                $consent_links[] = '<a href="' . esc_url($doc_url) . '" style="color: #0073aa; text-decoration: underline;">' . esc_html($doc->post_title) . '</a>';
                            } else {
                                $consent_links[] = esc_html(sprintf(__('Document ID: %d (not found)', 'codeweber'), $doc_id));
                            }
                        }
                    } else {
                        // Fallback без функции
                        $doc = get_post($doc_id);
                        if ($doc && $doc->post_status === 'publish') {
                            // Пытаемся найти ревизию, если указана версия
                            if ($doc_version && function_exists('codeweber_forms_find_revision_by_version')) {
                                $revision_id = codeweber_forms_find_revision_by_version($doc_id, $doc_version);
                                if ($revision_id) {
                                    $doc_url = admin_url('revision.php?revision=' . $revision_id);
                                } else {
                                    // Ревизия не найдена - простая ссылка без параметра version
                                    $doc_url = get_permalink($doc_id);
                                }
                            } else {
                                $doc_url = get_permalink($doc_id);
                            }
                            $consent_links[] = '<a href="' . esc_url($doc_url) . '" style="color: #0073aa; text-decoration: underline;">' . esc_html($doc->post_title) . '</a>';
                        }
                    }
                    }
                }
                
                if (!empty($consent_links)) {
                    $label = __('Newsletter Consents', 'codeweber');
                    // Выводим каждое согласие с новой строки
                    $value = implode('<br>', $consent_links);
                    // Для newsletter_consents значение уже содержит HTML-ссылки, не экранируем
                    $display_value = $value;
                    $is_html = true; // Флаг, что это HTML
                } else {
                    continue; // Пропускаем, если нет согласий
                }
            } else {
                // Получаем переведенный label для поля
                $label = self::get_field_label($field_name);
                $value = is_array($field_value) ? implode(', ', $field_value) : $field_value;
                $is_html = false; // Обычное текстовое поле
            }
            
            // Для ссылок (view_testimonial, edit_testimonial, view_post и т.д.) выводим как HTML-ссылку
            if (!$is_html) {
                $display_value = $value;
                if (in_array($field_name, ['view_testimonial', 'edit_testimonial', 'view_post', 'view_submission']) && filter_var($value, FILTER_VALIDATE_URL)) {
                    $link_text = $value;
                    // Для edit_testimonial используем более понятный текст
                    if ($field_name === 'edit_testimonial') {
                        $link_text = __('Edit in admin', 'codeweber');
                    }
                    $display_value = '<a href="' . esc_url($value) . '" style="color: #0073aa; text-decoration: underline;">' . esc_html($link_text) . '</a>';
                    $is_html = true;
                } else {
                    $display_value = esc_html($value);
                }
            }
            
            $html .= '<tr>';
            $html .= '<td style="padding: 10px; border: 1px solid #ddd; font-weight: bold;">' . esc_html($label) . ':</td>';
            $html .= '<td style="padding: 10px; border: 1px solid #ddd;">' . $display_value . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table>';
        return $html;
    }
    
    /**
     * Get translated field label
     * 
     * @param string $field_name Field name
     * @return string Translated label
     */
    private static function get_field_label($field_name) {
        // Маппинг полей на переводы
        $labels = [
            'name' => __('Name', 'codeweber'),
            'email' => __('Email', 'codeweber'),
            'role' => __('Role', 'codeweber'),
            'company' => __('Company', 'codeweber'),
            'testimonial_text' => __('Testimonial text', 'codeweber'),
            'rating' => __('Rating', 'codeweber'),
            'edit_testimonial' => __('Edit testimonial', 'codeweber'),
            'view_testimonial' => __('View testimonial', 'codeweber'),
            'testimonial_id' => __('Testimonial id', 'codeweber'),
            'message' => __('Message', 'codeweber'),
            'phone' => __('Phone', 'codeweber'),
            'subject' => __('Subject', 'codeweber'),
        ];
        
        // Если есть перевод, используем его
        if (isset($labels[$field_name])) {
            return $labels[$field_name];
        }
        
        // Иначе используем ucfirst с заменой подчеркиваний и дефисов
        return ucfirst(str_replace(['_', '-'], ' ', $field_name));
    }
}


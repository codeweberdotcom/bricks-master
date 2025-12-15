<?php
/**
 * Codeweber Forms Data Provider
 * 
 * Провайдер для данных из модуля Codeweber Forms (Gutenberg блоки)
 * Получает данные из таблицы wp_codeweber_forms_submissions
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/../class-data-provider-interface.php';

class Codeweber_Forms_Data_Provider implements Personal_Data_Provider_Interface {
    
    private $table_name;
    
    /**
     * Конструктор
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'codeweber_forms_submissions';
    }
    
    /**
     * Получить идентификатор провайдера
     * 
     * @return string
     */
    public function get_provider_id(): string {
        return 'codeweber-forms';
    }
    
    /**
     * Получить название провайдера
     * 
     * @return string
     */
    public function get_provider_name(): string {
        return __('Codeweber Forms', 'codeweber');
    }
    
    /**
     * Получить описание провайдера
     * 
     * @return string
     */
    public function get_provider_description(): string {
        return __('Personal data from Codeweber Forms submissions', 'codeweber');
    }
    
    /**
     * Получить персональные данные
     * 
     * @param string $email Email адрес
     * @param int $page Номер страницы
     * @return array
     */
    public function get_personal_data(string $email, int $page = 1): array {
        global $wpdb;
        
        $email = sanitize_email($email);
        
        if (!is_email($email)) {
            return ['data' => [], 'done' => true];
        }
        
        // Ищем отправки, где в submission_data есть этот email
        // submission_data хранится как JSON
        $submissions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} 
             WHERE submission_data LIKE %s 
             ORDER BY created_at DESC",
            '%' . $wpdb->esc_like($email) . '%'
        ));
        
        if (empty($submissions)) {
            return ['data' => [], 'done' => true];
        }
        
        $export_items = [];
        
        foreach ($submissions as $submission) {
            // Парсим JSON данные
            $submission_data = json_decode($submission->submission_data, true);
            if (!is_array($submission_data)) {
                continue;
            }
            
            // Проверяем, что email действительно есть в данных
            $email_found = false;
            foreach ($submission_data as $value) {
                if (is_string($value) && strtolower($value) === strtolower($email)) {
                    $email_found = true;
                    break;
                }
            }
            
            if (!$email_found) {
                continue;
            }
            
            $group_id = 'codeweber-forms-submission';
            $group_label = __('Codeweber Forms Submission', 'codeweber');
            
            $data = [];
            
            // Название формы (с поддержкой перевода)
            if (!empty($submission->form_name)) {
                $form_name = $submission->form_name;

                // Пытаемся найти перевод по самому названию формы
                // Например, для "Newsletter Subscription" будет использован перевод из ru_RU.po
                $translated_form_name = __($form_name, 'codeweber');
                if (!empty($translated_form_name) && $translated_form_name !== $form_name) {
                    $form_name = $translated_form_name;
                }

                $data[] = [
                    'name'  => __('Form Name', 'codeweber'),
                    'value' => $form_name,
                ];
            }
            
            // ID формы
            if (!empty($submission->form_id)) {
                $form_title = get_the_title($submission->form_id);
                $data[] = [
                    'name' => __('Form ID', 'codeweber'),
                    'value' => $form_title ? sprintf('%s (ID: %s)', $form_title, $submission->form_id) : (string)$submission->form_id
                ];
            }
            
            // Все поля формы
            foreach ($submission_data as $field_name => $field_value) {
                if (empty($field_value)) {
                    continue;
                }
                
                // Пропускаем системные поля, они обрабатываются отдельно
                if (in_array($field_name, ['newsletter_consents', '_utm_data'])) {
                    continue;
                }
                
                // Получаем переведенное название поля
                $field_label = $this->get_translated_field_label($field_name);
                
                // Форматируем значение
                $display_value = $this->format_field_value($field_value);
                
                $data[] = [
                    'name' => $field_label,
                    'value' => $display_value
                ];
            }
            
            // Обработка newsletter_consents (специальная обработка)
            // Выводим каждый документ согласия отдельной строкой, в формате как в блоке "Пользовательские согласия"
            if (!empty($submission_data['newsletter_consents']) && is_array($submission_data['newsletter_consents'])) {
                foreach ($submission_data['newsletter_consents'] as $doc_id => $consent_data) {
                    $doc = get_post($doc_id);
                    if ($doc) {
                        $doc_title = $doc->post_title;
                        $doc_url = '';

                        // URL документа: если есть ревизия, используем ссылку на нее, иначе permalink документа
                        if (!empty($consent_data['document_revision_id'])) {
                            $doc_url = admin_url('revision.php?revision=' . $consent_data['document_revision_id']);
                        } else {
                            $doc_url = get_permalink($doc_id);
                        }

                        // Базовая часть: Название (ID: X)
                        $consent_info = sprintf(
                            '%s (ID: %d)',
                            $doc_title,
                            $doc_id
                        );

                        // Информация о ревизии
                        if (!empty($consent_data['document_revision_id'])) {
                            $consent_info .= sprintf(
                                ' - ' . __('Revision ID', 'codeweber') . ': %d',
                                $consent_data['document_revision_id']
                            );
                        }

                        // Информация о версии
                        if (!empty($consent_data['document_version'])) {
                            $consent_info .= ' - ' . __('Version', 'codeweber') . ': ' . $consent_data['document_version'];
                        }

                        // URL (делаем кликабельным)
                        if ($doc_url) {
                            $consent_info .= ' - ' . __('URL', 'codeweber') . ': '
                                . '<a href="' . esc_url($doc_url) . '" target="_blank" rel="noopener noreferrer">'
                                . esc_html($doc_url)
                                . '</a>';
                        }

                        $data[] = [
                            'name'  => __('Consented Document', 'codeweber'),
                            'value' => $consent_info,
                        ];
                    }
                }
            }
            
            // Обработка UTM данных
            if (!empty($submission_data['_utm_data']) && is_array($submission_data['_utm_data'])) {
                $utm_list = [];
                foreach ($submission_data['_utm_data'] as $utm_key => $utm_value) {
                    if (!empty($utm_value)) {
                        $utm_label = $this->get_translated_field_label($utm_key);
                        $utm_list[] = $utm_label . ': ' . $utm_value;
                    }
                }
                
                if (!empty($utm_list)) {
                    $data[] = [
                        'name' => __('UTM Data', 'codeweber'),
                        'value' => implode('; ', $utm_list)
                    ];
                }
            }
            
            // Файлы (если есть)
            if (!empty($submission->files_data)) {
                $files_data = json_decode($submission->files_data, true);
                if (is_array($files_data) && !empty($files_data)) {
                    $files_list = [];
                    foreach ($files_data as $field_name => $files) {
                        if (is_array($files)) {
                            foreach ($files as $file) {
                                if (isset($file['name'])) {
                                    $files_list[] = $file['name'];
                                }
                            }
                        }
                    }
                    
                    if (!empty($files_list)) {
                        $data[] = [
                            'name' => __('Uploaded Files', 'codeweber'),
                            'value' => implode(', ', $files_list)
                        ];
                    }
                }
            }
            
            // IP-адрес
            if (!empty($submission->ip_address)) {
                $data[] = [
                    'name' => __('IP Address', 'codeweber'),
                    'value' => $submission->ip_address
                ];
            }
            
            // User Agent
            if (!empty($submission->user_agent)) {
                $data[] = [
                    'name' => __('Browser User Agent', 'codeweber'),
                    'value' => $submission->user_agent
                ];
            }
            
            // Дата отправки
            $data[] = [
                'name' => __('Submission Date', 'codeweber'),
                'value' => date('d.m.Y H:i:s', strtotime($submission->created_at))
            ];
            
            // ID записи
            $data[] = [
                'name' => __('Record ID', 'codeweber'),
                'value' => (string)$submission->id
            ];
            
            $export_items[] = [
                'group_id' => $group_id,
                'group_label' => $group_label,
                'item_id' => 'codeweber-forms-submission-' . $submission->id,
                'data' => $data,
            ];
        }
        
        return [
            'data' => $export_items,
            'done' => true
        ];
    }
    
    /**
     * Удалить персональные данные
     * 
     * @param string $email Email адрес
     * @param int $page Номер страницы
     * @return array
     */
    public function erase_personal_data(string $email, int $page = 1): array {
        global $wpdb;
        
        $email = sanitize_email($email);
        
        if (!is_email($email)) {
            return [
                'items_removed' => false,
                'items_retained' => false,
                'messages' => [__('Invalid email address', 'codeweber')],
                'done' => true
            ];
        }
        
        // Находим отправки с этим email
        $submissions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} 
             WHERE submission_data LIKE %s",
            '%' . $wpdb->esc_like($email) . '%'
        ));
        
        if (empty($submissions)) {
            return [
                'items_removed' => false,
                'items_retained' => false,
                'messages' => [__('No Codeweber Forms data found for this email', 'codeweber')],
                'done' => true
            ];
        }
        
        $items_removed = false;
        
        foreach ($submissions as $submission) {
            // Парсим JSON данные
            $submission_data = json_decode($submission->submission_data, true);
            if (!is_array($submission_data)) {
                continue;
            }
            
            // Анонимизируем email и другие персональные данные
            $anonymized = false;
            foreach ($submission_data as $field_name => $field_value) {
                $field_lower = strtolower($field_name);
                $value_lower = is_string($field_value) ? strtolower($field_value) : '';
                
                // Анонимизируем email поля
                if (strpos($field_lower, 'email') !== false && is_email($field_value)) {
                    $submission_data[$field_name] = 'anonymized@example.com';
                    $anonymized = true;
                }
                
                // Анонимизируем телефон
                if (strpos($field_lower, 'phone') !== false || strpos($field_lower, 'tel') !== false) {
                    $submission_data[$field_name] = __('Anonymized', 'codeweber');
                    $anonymized = true;
                }
                
                // Анонимизируем имя
                if (strpos($field_lower, 'name') !== false && $value_lower === strtolower($email)) {
                    $submission_data[$field_name] = __('Anonymous', 'codeweber');
                    $anonymized = true;
                }
            }
            
            if ($anonymized) {
                // Обновляем данные в БД
                $wpdb->update(
                    $this->table_name,
                    [
                        'submission_data' => json_encode($submission_data, JSON_UNESCAPED_UNICODE),
                        'ip_address' => '0.0.0.0',
                        'user_agent' => 'anonymized',
                        'updated_at' => current_time('mysql')
                    ],
                    ['id' => $submission->id],
                    ['%s', '%s', '%s', '%s'],
                    ['%d']
                );
                
                $items_removed = true;
            }
        }
        
        return [
            'items_removed' => $items_removed,
            'items_retained' => false,
            'messages' => $items_removed ? 
                [__('Codeweber Forms submission data anonymized', 'codeweber')] : 
                [__('No personal data found to anonymize', 'codeweber')],
            'done' => true
        ];
    }
    
    /**
     * Проверить наличие данных
     * 
     * @param string $email Email адрес
     * @return bool
     */
    public function has_personal_data(string $email): bool {
        global $wpdb;
        
        $email = sanitize_email($email);
        
        if (!is_email($email)) {
            return false;
        }
        
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->table_name} 
             WHERE submission_data LIKE %s 
             LIMIT 1",
            '%' . $wpdb->esc_like($email) . '%'
        ));
        
        return !empty($exists);
    }
    
    /**
     * Получить список полей
     * 
     * @return array
     */
    public function get_personal_data_fields(): array {
        return [
            'form_id' => __('Form ID', 'codeweber'),
            'form_name' => __('Form Name', 'codeweber'),
            'submission_data' => __('Form Fields', 'codeweber'),
            'files_data' => __('Uploaded Files', 'codeweber'),
            'ip_address' => __('IP Address', 'codeweber'),
            'user_agent' => __('Browser User Agent', 'codeweber'),
            'user_id' => __('User ID', 'codeweber'),
            'status' => __('Status', 'codeweber'),
            'created_at' => __('Submission Date', 'codeweber')
        ];
    }
    
    /**
     * Получить переведенное название поля
     * 
     * @param string $field_name Имя поля
     * @return string Переведенное название
     */
    private function get_translated_field_label(string $field_name): string {
        $translations = [
            'email' => __('Email Address', 'codeweber'),
            'name' => __('Name', 'codeweber'),
            'first_name' => __('First Name', 'codeweber'),
            'last_name' => __('Last Name', 'codeweber'),
            'phone' => __('Phone', 'codeweber'),
            'tel' => __('Phone', 'codeweber'),
            'message' => __('Message', 'codeweber'),
            'subject' => __('Subject', 'codeweber'),
            'company' => __('Company', 'codeweber'),
            'utm_source' => __('UTM Source', 'codeweber'),
            'utm_medium' => __('UTM Medium', 'codeweber'),
            'utm_campaign' => __('UTM Campaign', 'codeweber'),
            'utm_term' => __('UTM Term', 'codeweber'),
            'utm_content' => __('UTM Content', 'codeweber'),
            'utm_id' => __('UTM ID', 'codeweber'),
            'referrer' => __('Referrer', 'codeweber'),
            'landing_page' => __('Landing Page', 'codeweber'),
        ];
        
        // Если есть перевод, используем его
        if (isset($translations[$field_name])) {
            return $translations[$field_name];
        }
        
        // Иначе создаем читаемое название из имени поля
        $label = str_replace(['_', '-'], ' ', $field_name);
        $label = ucwords($label);
        
        return $label;
    }
    
    /**
     * Форматировать значение поля для экспорта
     * 
     * @param mixed $value Значение поля
     * @return string Отформатированное значение
     */
    private function format_field_value($value): string {
        if (is_array($value)) {
            // Рекурсивно обрабатываем массивы
            $formatted = [];
            foreach ($value as $key => $item) {
                if (is_array($item)) {
                    // Если это массив массивов, форматируем каждый элемент
                    $item_str = [];
                    foreach ($item as $k => $v) {
                        if (!is_array($v)) {
                            $item_str[] = $k . ': ' . $v;
                        }
                    }
                    $formatted[] = implode(', ', $item_str);
                } else {
                    $formatted[] = $item;
                }
            }
            return implode('; ', $formatted);
        }
        
        return (string)$value;
    }
}



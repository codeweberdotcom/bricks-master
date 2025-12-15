<?php
/**
 * Contact Form 7 Data Provider
 * 
 * Провайдер для данных из Contact Form 7
 * Работает с плагином Flamingo (если установлен) или альтернативными хранилищами
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/../class-data-provider-interface.php';

class CF7_Data_Provider implements Personal_Data_Provider_Interface {
    
    /**
     * Получить идентификатор провайдера
     * 
     * @return string
     */
    public function get_provider_id(): string {
        return 'contact-form-7';
    }
    
    /**
     * Получить название провайдера
     * 
     * @return string
     */
    public function get_provider_name(): string {
        return __('Contact Form 7', 'codeweber');
    }
    
    /**
     * Получить описание провайдера
     * 
     * @return string
     */
    public function get_provider_description(): string {
        return __('Personal data from Contact Form 7 submissions', 'codeweber');
    }
    
    /**
     * Получить персональные данные
     * 
     * @param string $email Email адрес
     * @param int $page Номер страницы
     * @return array
     */
    public function get_personal_data(string $email, int $page = 1): array {
        $email = sanitize_email($email);
        
        if (!is_email($email)) {
            return ['data' => [], 'done' => true];
        }
        
        $export_items = [];
        
        // Проверяем наличие Flamingo
        if (class_exists('Flamingo_Inbound_Message')) {
            $export_items = $this->get_data_from_flamingo($email);
        } else {
            // Альтернативный способ: ищем в других местах
            // Например, если данные сохраняются в кастомную таблицу или опции
            $export_items = $this->get_data_from_alternatives($email);
        }
        
        return [
            'data' => $export_items,
            'done' => true
        ];
    }
    
    /**
     * Получить данные из Flamingo
     * 
     * @param string $email Email адрес
     * @return array
     */
    private function get_data_from_flamingo(string $email): array {
        $export_items = [];
        
        // Получаем сообщения из Flamingo
        $messages = get_posts([
            'post_type' => 'flamingo_inbound',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => '_from_email',
                    'value' => $email,
                    'compare' => '='
                ]
            ]
        ]);
        
        foreach ($messages as $message) {
            $group_id = 'cf7-submission';
            $group_label = __('Contact Form 7 Submission', 'codeweber');
            
            $data = [];
            
            // Email отправителя
            $data[] = [
                'name' => __('From Email', 'codeweber'),
                'value' => get_post_meta($message->ID, '_from_email', true) ?: $email
            ];
            
            // Имя отправителя
            $from_name = get_post_meta($message->ID, '_from_name', true);
            if ($from_name) {
                $data[] = [
                    'name' => __('From Name', 'codeweber'),
                    'value' => $from_name
                ];
            }
            
            // Тема
            $subject = get_post_meta($message->ID, '_subject', true);
            if ($subject) {
                $data[] = [
                    'name' => __('Subject', 'codeweber'),
                    'value' => $subject
                ];
            }
            
            // Телефон
            $phone = get_post_meta($message->ID, '_field_phone', true);
            if (!$phone) {
                $phone = get_post_meta($message->ID, '_field_tel', true);
            }
            if ($phone) {
                $data[] = [
                    'name' => __('Phone', 'codeweber'),
                    'value' => $phone
                ];
            }
            
            // Сообщение
            $message_content = get_post_meta($message->ID, '_message', true);
            if ($message_content) {
                $data[] = [
                    'name' => __('Message', 'codeweber'),
                    'value' => $message_content
                ];
            }
            
            // Все поля формы
            $fields = get_post_meta($message->ID, '_fields', true);
            if (is_array($fields) && !empty($fields)) {
                foreach ($fields as $field_name => $field_value) {
                    // Пропускаем уже добавленные поля
                    if (in_array($field_name, ['_from_email', '_from_name', '_subject', '_message', 'phone', 'tel'])) {
                        continue;
                    }
                    
                    $data[] = [
                        'name' => sprintf(__('Field: %s', 'codeweber'), $field_name),
                        'value' => is_array($field_value) ? implode(', ', $field_value) : $field_value
                    ];
                }
            }
            
            // IP-адрес
            $ip = get_post_meta($message->ID, '_ip', true);
            if ($ip) {
                $data[] = [
                    'name' => __('IP Address', 'codeweber'),
                    'value' => $ip
                ];
            }
            
            // User Agent
            $user_agent = get_post_meta($message->ID, '_user_agent', true);
            if ($user_agent) {
                $data[] = [
                    'name' => __('Browser User Agent', 'codeweber'),
                    'value' => $user_agent
                ];
            }
            
            // Дата отправки
            $data[] = [
                'name' => __('Submission Date', 'codeweber'),
                'value' => get_the_date('d.m.Y H:i:s', $message->ID)
            ];
            
            // ID формы CF7
            $form_id = get_post_meta($message->ID, '_form', true);
            if ($form_id) {
                $form_title = get_the_title($form_id);
                $data[] = [
                    'name' => __('Form', 'codeweber'),
                    'value' => $form_title ? sprintf('%s (ID: %s)', $form_title, $form_id) : sprintf(__('Form ID: %s', 'codeweber'), $form_id)
                ];
            }
            
            // ID записи
            $data[] = [
                'name' => __('Record ID', 'codeweber'),
                'value' => (string)$message->ID
            ];
            
            $export_items[] = [
                'group_id' => $group_id,
                'group_label' => $group_label,
                'item_id' => 'cf7-submission-' . $message->ID,
                'data' => $data,
            ];
        }
        
        return $export_items;
    }
    
    /**
     * Получить данные из альтернативных источников
     * (если Flamingo не установлен)
     * 
     * @param string $email Email адрес
     * @return array
     */
    private function get_data_from_alternatives(string $email): array {
        // Если Flamingo не установлен, можно искать в других местах
        // Например, в кастомных таблицах, опциях и т.д.
        // Пока возвращаем пустой массив
        return [];
    }
    
    /**
     * Удалить персональные данные
     * 
     * @param string $email Email адрес
     * @param int $page Номер страницы
     * @return array
     */
    public function erase_personal_data(string $email, int $page = 1): array {
        $email = sanitize_email($email);
        
        if (!is_email($email)) {
            return [
                'items_removed' => false,
                'items_retained' => false,
                'messages' => [__('Invalid email address', 'codeweber')],
                'done' => true
            ];
        }
        
        $items_removed = false;
        $messages = [];
        
        // Работаем с Flamingo
        if (class_exists('Flamingo_Inbound_Message')) {
            $messages = get_posts([
                'post_type' => 'flamingo_inbound',
                'posts_per_page' => -1,
                'meta_query' => [
                    [
                        'key' => '_from_email',
                        'value' => $email,
                        'compare' => '='
                    ]
                ]
            ]);
            
            foreach ($messages as $message) {
                // Анонимизируем данные
                update_post_meta($message->ID, '_from_name', __('Anonymous', 'codeweber'));
                update_post_meta($message->ID, '_from_email', 'anonymized@example.com');
                update_post_meta($message->ID, '_ip', '0.0.0.0');
                update_post_meta($message->ID, '_user_agent', 'anonymized');
                
                // Анонимизируем поля формы
                $fields = get_post_meta($message->ID, '_fields', true);
                if (is_array($fields)) {
                    foreach ($fields as $field_name => $field_value) {
                        if (strpos($field_name, 'email') !== false || strpos($field_name, 'phone') !== false || strpos($field_name, 'tel') !== false) {
                            $fields[$field_name] = __('Anonymized', 'codeweber');
                        }
                    }
                    update_post_meta($message->ID, '_fields', $fields);
                }
                
                $items_removed = true;
            }
            
            if ($items_removed) {
                $messages[] = __('Contact Form 7 submission data anonymized', 'codeweber');
            }
        }
        
        if (!$items_removed) {
            $messages[] = __('No Contact Form 7 data found for this email', 'codeweber');
        }
        
        return [
            'items_removed' => $items_removed,
            'items_retained' => false,
            'messages' => $messages,
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
        $email = sanitize_email($email);
        
        if (!is_email($email)) {
            return false;
        }
        
        // Проверяем Flamingo
        if (class_exists('Flamingo_Inbound_Message')) {
            $messages = get_posts([
                'post_type' => 'flamingo_inbound',
                'posts_per_page' => 1,
                'meta_query' => [
                    [
                        'key' => '_from_email',
                        'value' => $email,
                        'compare' => '='
                    ]
                ]
            ]);
            
            return !empty($messages);
        }
        
        return false;
    }
    
    /**
     * Получить список полей
     * 
     * @return array
     */
    public function get_personal_data_fields(): array {
        return [
            'from_email' => __('From Email', 'codeweber'),
            'from_name' => __('From Name', 'codeweber'),
            'subject' => __('Subject', 'codeweber'),
            'phone' => __('Phone', 'codeweber'),
            'message' => __('Message', 'codeweber'),
            'ip_address' => __('IP Address', 'codeweber'),
            'user_agent' => __('Browser User Agent', 'codeweber'),
            'form_id' => __('Form ID', 'codeweber'),
            'submission_date' => __('Submission Date', 'codeweber')
        ];
    }
}



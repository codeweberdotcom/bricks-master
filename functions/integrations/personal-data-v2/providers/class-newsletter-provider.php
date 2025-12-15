<?php
/**
 * Newsletter Subscription Data Provider
 * 
 * Провайдер для модуля подписки на рассылку
 * Получает данные из таблицы wp_newsletter_subscriptions
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/../class-data-provider-interface.php';

class Newsletter_Data_Provider implements Personal_Data_Provider_Interface {
    
    private $table_name;
    private $options_name = 'newsletter_subscription_settings';
    
    /**
     * Конструктор
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'newsletter_subscriptions';
    }
    
    /**
     * Получить идентификатор провайдера
     * 
     * @return string
     */
    public function get_provider_id(): string {
        return 'newsletter-subscription-v2';
    }
    
    /**
     * Получить название провайдера
     * 
     * @return string
     */
    public function get_provider_name(): string {
        return __('Newsletter Subscription', 'codeweber');
    }
    
    /**
     * Получить описание провайдера
     * 
     * @return string
     */
    public function get_provider_description(): string {
        return __('Personal data from newsletter subscriptions', 'codeweber');
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
        
        // Получаем все подписки по email
        $subscriptions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE email = %s ORDER BY created_at DESC",
            $email
        ));
        
        if (empty($subscriptions)) {
            return ['data' => [], 'done' => true];
        }
        
        $export_items = [];
        
        foreach ($subscriptions as $subscription) {
            $group_id = 'newsletter-subscription';
            $group_label = __('Newsletter Subscription', 'codeweber');
            
            $data = [];
            
            // Статус подписки
            $data[] = [
                'name' => __('Subscription Status', 'codeweber'),
                'value' => $this->get_status_label($subscription->status)
            ];
            
            // Email
            $data[] = [
                'name' => __('Subscriber Email', 'codeweber'),
                'value' => $subscription->email
            ];
            
            // Дата подписки
            $data[] = [
                'name' => __('Subscription Date & Time', 'codeweber'),
                'value' => date('d.m.Y H:i:s', strtotime($subscription->created_at))
            ];
            
            // IP-адрес
            if (!empty($subscription->ip_address)) {
                $data[] = [
                    'name' => __('IP Address', 'codeweber'),
                    'value' => $subscription->ip_address
                ];
            }
            
            // User Agent
            if (!empty($subscription->user_agent)) {
                $data[] = [
                    'name' => __('Browser User Agent', 'codeweber'),
                    'value' => $subscription->user_agent
                ];
            }
            
            // Форма подписки
            if (!empty($subscription->form_id)) {
                $data[] = [
                    'name' => __('Subscription Form', 'codeweber'),
                    'value' => $this->get_form_label($subscription->form_id)
                ];
            }
            
            // Имя
            if (!empty($subscription->first_name)) {
                $data[] = [
                    'name' => __('First Name', 'codeweber'),
                    'value' => $subscription->first_name
                ];
            }
            
            // Фамилия
            if (!empty($subscription->last_name)) {
                $data[] = [
                    'name' => __('Last Name', 'codeweber'),
                    'value' => $subscription->last_name
                ];
            }
            
            // Телефон
            if (!empty($subscription->phone)) {
                $data[] = [
                    'name' => __('Phone Number', 'codeweber'),
                    'value' => $subscription->phone
                ];
            }
            
            // Дата подтверждения
            if (!empty($subscription->confirmed_at) && $subscription->confirmed_at !== '0000-00-00 00:00:00') {
                $data[] = [
                    'name' => __('Confirmation Date', 'codeweber'),
                    'value' => date('d.m.Y H:i:s', strtotime($subscription->confirmed_at))
                ];
            }
            
            // Дата отписки
            if (!empty($subscription->unsubscribed_at) && $subscription->unsubscribed_at !== '0000-00-00 00:00:00') {
                $data[] = [
                    'name' => __('Unsubscribe Date', 'codeweber'),
                    'value' => date('d.m.Y H:i:s', strtotime($subscription->unsubscribed_at))
                ];
            }
            
            // ID записи
            $data[] = [
                'name' => __('Record ID', 'codeweber'),
                'value' => (string)$subscription->id
            ];
            
            $export_items[] = [
                'group_id' => $group_id,
                'group_label' => $group_label,
                'item_id' => 'newsletter-subscription-' . $subscription->id,
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
        
        // Анонимизируем данные (не удаляем запись)
        $result = $wpdb->update(
            $this->table_name,
            [
                'first_name' => __('Anonymous', 'codeweber'),
                'last_name' => __('User', 'codeweber'),
                'phone' => '',
                'ip_address' => '0.0.0.0',
                'user_agent' => 'anonymized',
                'updated_at' => current_time('mysql')
            ],
            ['email' => $email],
            ['%s', '%s', '%s', '%s', '%s', '%s'],
            ['%s']
        );
        
        if ($result !== false && $result > 0) {
            return [
                'items_removed' => true,
                'items_retained' => false,
                'messages' => [__('Newsletter subscription personal data anonymized', 'codeweber')],
                'done' => true
            ];
        }
        
        return [
            'items_removed' => false,
            'items_retained' => false,
            'messages' => [__('No newsletter subscription data found for this email', 'codeweber')],
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
            "SELECT id FROM {$this->table_name} WHERE email = %s LIMIT 1",
            $email
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
            'email' => __('Email', 'codeweber'),
            'first_name' => __('First Name', 'codeweber'),
            'last_name' => __('Last Name', 'codeweber'),
            'phone' => __('Phone Number', 'codeweber'),
            'ip_address' => __('IP Address', 'codeweber'),
            'user_agent' => __('Browser User Agent', 'codeweber'),
            'form_id' => __('Subscription Form', 'codeweber'),
            'status' => __('Subscription Status', 'codeweber'),
            'created_at' => __('Subscription Date', 'codeweber'),
            'confirmed_at' => __('Confirmation Date', 'codeweber'),
            'unsubscribed_at' => __('Unsubscribe Date', 'codeweber')
        ];
    }
    
    /**
     * Получить метку статуса
     * 
     * @param string $status Статус
     * @return string
     */
    private function get_status_label(string $status): string {
        $labels = [
            'pending' => __('Pending Confirmation', 'codeweber'),
            'confirmed' => __('Subscribed', 'codeweber'),
            'unsubscribed' => __('Unsubscribed', 'codeweber')
        ];
        
        return $labels[$status] ?? $status;
    }
    
    /**
     * Получить метку формы
     * 
     * @param string $form_id ID формы
     * @return string
     */
    private function get_form_label(string $form_id): string {
        // Для CF7 форм
        if (strpos($form_id, 'cf7_') === 0) {
            $parts = explode('_', $form_id);
            if (count($parts) >= 2 && is_numeric($parts[1])) {
                $form_title = get_the_title($parts[1]);
                if ($form_title) {
                    return sprintf(__('Contact Form 7: %s', 'codeweber'), $form_title);
                }
            }
        }
        
        $labels = [
            'default' => __('Default Subscription Form', 'codeweber'),
            'imported' => __('Imported Subscription', 'codeweber')
        ];
        
        return $labels[$form_id] ?? $form_id;
    }
}



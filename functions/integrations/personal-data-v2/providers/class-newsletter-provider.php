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
                // Название формы (человекочитаемое)
                $data[] = [
                    'name' => __('Subscription Form', 'codeweber'),
                    'value' => $this->get_form_label($subscription->form_id)
                ];
                
                // ID формы подписки
                $data[] = [
                    'name' => __('Subscription Form ID', 'codeweber'),
                    'value' => $subscription->form_id
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
            
            // История событий подписки/отписки (events_history)
            // Храним все события в одном массиве и выводим в экспорте в виде списка "События"
            $events = [];
            if (!empty($subscription->events_history)) {
                $decoded = json_decode($subscription->events_history, true);
                if (is_array($decoded)) {
                    $events = $decoded;
                }
            }

            // Фолбэк для старых записей без events_history: используем confirmed_at / unsubscribed_at
            if (empty($events)) {
                if (!empty($subscription->confirmed_at) && $subscription->confirmed_at !== '0000-00-00 00:00:00') {
                    $events[] = [
                        'type' => 'confirmed',
                        'date' => $subscription->confirmed_at,
                        'source' => 'legacy',
                    ];
                }
                if (!empty($subscription->unsubscribed_at) && $subscription->unsubscribed_at !== '0000-00-00 00:00:00') {
                    $events[] = [
                        'type' => 'unsubscribed',
                        'date' => $subscription->unsubscribed_at,
                        'source' => 'legacy',
                    ];
                }
            }

            if (!empty($events)) {
                foreach ($events as $event) {
                    if (empty($event['date'])) {
                        continue;
                    }
                    $timestamp = strtotime($event['date']);
                    if (!$timestamp) {
                        continue;
                    }

                    $date_str = date('d.m.Y H:i:s', $timestamp);
                    $label = '';

                    if (!empty($event['type']) && $event['type'] === 'confirmed') {
                        $label = __('Confirmation Date', 'codeweber');
                    } elseif (!empty($event['type']) && $event['type'] === 'unsubscribed') {
                        $label = __('Unsubscribe Date', 'codeweber');
                    } else {
                        $label = __('Event Date', 'codeweber');
                    }

                    // Собираем человекочитаемое значение события, включая форму и страницу
                    $parts = [];
                    $parts[] = sprintf('%s: %s', $label, $date_str);

                    if (!empty($event['form_id'])) {
                        $parts[] = sprintf(
                            '%s: %s',
                            __('Subscription Form', 'codeweber'),
                            $this->get_form_label($event['form_id'])
                        );
                    }

                    if (!empty($event['page_url'])) {
                        $parts[] = sprintf(
                            '%s: %s',
                            __('Page URL', 'codeweber'),
                            $event['page_url']
                        );
                    }

                    // Согласия, выданные в рамках этого события (если есть)
                    if (!empty($event['consents']) && is_array($event['consents'])) {
                        $consent_parts = [];
                        foreach ($event['consents'] as $consent) {
                            if (empty($consent['id']) || empty($consent['title'])) {
                                continue;
                            }

                            $doc_str = sprintf(
                                '%s (ID: %d)',
                                $consent['title'],
                                $consent['id']
                            );

                            if (!empty($consent['document_revision_id'])) {
                                $doc_str .= sprintf(
                                    ' - %s: %d',
                                    __('Revision ID', 'codeweber'),
                                    $consent['document_revision_id']
                                );
                            }

                            if (!empty($consent['document_version'])) {
                                $doc_str .= sprintf(
                                    ' - %s: %s',
                                    __('Version', 'codeweber'),
                                    $consent['document_version']
                                );
                            }

                            if (!empty($consent['url'])) {
                                $doc_str .= ' - ' . __('URL', 'codeweber') . ': '
                                    . '<a href="' . esc_url($consent['url']) . '" target="_blank" rel="noopener noreferrer">'
                                    . esc_html($consent['url'])
                                    . '</a>';
                            }

                            $consent_parts[] = $doc_str;
                        }

                        if (!empty($consent_parts)) {
                            $parts[] = sprintf(
                                '%s: %s',
                                __('Consented Document', 'codeweber'),
                                implode(' | ', $consent_parts)
                            );
                        }
                    }

                    // Выводим каждую часть события отдельной строкой "События"
                    foreach ($parts as $part) {
                        $data[] = [
                            'name'  => __('Events', 'codeweber'),
                            'value' => $part,
                        ];
                    }
                }
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
        // Встроенные формы (newsletter, testimonial, resume, callback)
        $builtin_forms = [
            'newsletter'  => __('Newsletter Subscription', 'codeweber'),
            'testimonial' => __('Testimonial Form', 'codeweber'),
            'resume'      => __('Resume Form', 'codeweber'),
            'callback'    => __('Callback Request', 'codeweber'),
        ];
        if (isset($builtin_forms[$form_id])) {
            return $builtin_forms[$form_id];
        }
        
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



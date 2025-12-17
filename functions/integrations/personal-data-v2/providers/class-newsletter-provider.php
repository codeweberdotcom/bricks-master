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
            
            // Email (with link to user profile if user exists)
            $email_value = $subscription->email;
            $user = get_user_by('email', $subscription->email);
            if ($user) {
                $user_profile_url = admin_url('user-edit.php?user_id=' . $user->ID);
                $email_value = sprintf(
                    '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                    esc_url($user_profile_url),
                    esc_html($subscription->email)
                );
            }
            $data[] = [
                'name' => __('Subscriber Email', 'codeweber'),
                'value' => $email_value
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
                // Название формы (человекочитаемое) с ссылкой на редактирование формы
                $form_label = $this->get_form_label($subscription->form_id);
                $form_name_value = $form_label;
                
                // Добавляем ссылку на редактирование формы, если form_id числовой
                if (is_numeric($subscription->form_id)) {
                    $form_edit_url = admin_url('post.php?post=' . (int) $subscription->form_id . '&action=edit');
                    $form_name_value = sprintf(
                        '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                        esc_url($form_edit_url),
                        esc_html($form_label)
                    );
                }
                
                $data[] = [
                    'name' => __('Subscription Form', 'codeweber'),
                    'value' => $form_name_value
                ];
                
                // ID формы подписки с ссылкой на редактирование
                $form_id_value = $subscription->form_id;
                if (is_numeric($subscription->form_id)) {
                    $form_edit_url = admin_url('post.php?post=' . (int) $subscription->form_id . '&action=edit');
                    $form_id_value = sprintf(
                        '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                        esc_url($form_edit_url),
                        esc_html($subscription->form_id)
                    );
                }
                
                $data[] = [
                    'name' => __('Subscription Form ID', 'codeweber'),
                    'value' => $form_id_value
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

                    if (!empty($event['type']) && $event['type'] === 'confirmed') {
                        $label = __('Confirmation Date', 'codeweber');
                    } elseif (!empty($event['type']) && $event['type'] === 'unsubscribed') {
                        $label = __('Unsubscribe Date', 'codeweber');
                    } else {
                        $label = __('Event Date', 'codeweber');
                    }

                    $event_parts   = [];
                    $event_parts[] = sprintf('%s: %s', $label, $date_str);

                    // Имя формы лучше брать из form_name, если оно сохранено в событии
                    $form_label = '';
                    if (!empty($event['form_name'])) {
                        $form_label = $event['form_name'];
                    } elseif (!empty($event['form_id'])) {
                        $form_label = $this->get_form_label($event['form_id']);
                    } elseif (!empty($subscription->form_id)) {
                        $form_label = $this->get_form_label($subscription->form_id);
                    }

                    if (!empty($form_label)) {
                        $event_parts[] = sprintf(
                            '%s: %s',
                            __('Subscription Form', 'codeweber'),
                            $form_label
                        );
                    }

                    // Способ отписки/ссылка на страницу
                    if (!empty($event['type']) && $event['type'] === 'unsubscribed') {
                        $actor_added = false;

                        if (!empty($event['source']) && $event['source'] === 'admin' && !empty($event['actor_user_id'])) {
                            $actor = get_user_by('id', (int) $event['actor_user_id']);
                            if ($actor) {
                                $profile_url = get_edit_user_link($actor->ID);
                                $event_parts[] = sprintf(
                                    '%s: <a href="%s" target="_blank" rel="noopener noreferrer">%s (%s)</a>',
                                    __('Unsubscribed by administrator', 'codeweber'),
                                    esc_url($profile_url),
                                    esc_html($actor->user_login),
                                    esc_html($actor->user_email)
                                );
                                $actor_added = true;
                            }
                        }

                        if (!empty($event['page_url'])) {
                            $event_parts[] = sprintf(
                                $actor_added ? __('Unsubscribe page', 'codeweber') . ': %s' : __('Unsubscribed via page', 'codeweber') . ': %s',
                                '<a href="' . esc_url($event['page_url']) . '" target="_blank" rel="noopener noreferrer">' . esc_html($event['page_url']) . '</a>'
                            );
                        }
                    } elseif (!empty($event['page_url'])) {
                        $event_parts[] = sprintf(
                            '%s: %s',
                            __('Page URL', 'codeweber'),
                            '<a href="' . esc_url($event['page_url']) . '" target="_blank" rel="noopener noreferrer">' . esc_html($event['page_url']) . '</a>'
                        );
                    }

                    if (!empty($event['ip_address'])) {
                        $event_parts[] = sprintf(
                            '%s: %s',
                            __('IP Address', 'codeweber'),
                            esc_html($event['ip_address'])
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
                            $event_parts[] = sprintf(
                                '%s: %s',
                                __('Consented Document', 'codeweber'),
                                implode(' | ', $consent_parts)
                            );
                        }
                    }

                    // Каждое событие — одной строкой
                    $data[] = [
                        'name'  => __('Events', 'codeweber'),
                        'value' => implode(' | ', $event_parts),
                    ];
                }
            }
            
            // ID записи (с ссылкой на просмотр подписки в админке)
            $record_id_value = (string)$subscription->id;
            $subscription_view_url = admin_url('admin.php?page=newsletter-subscriptions&action=view&email=' . urlencode($subscription->email));
            $record_id_value = sprintf(
                '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                esc_url($subscription_view_url),
                esc_html($record_id_value)
            );
            
            $data[] = [
                'name' => __('Record ID', 'codeweber'),
                'value' => $record_id_value
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
        // НОВОЕ: Используем единую функцию для получения типа формы
        if (class_exists('CodeweberFormsCore')) {
            $form_type = CodeweberFormsCore::get_form_type($form_id);
            
            // Маппинг типов на читаемые названия
            $type_labels = [
                'form' => __('Regular Form', 'codeweber'),
                'newsletter' => __('Newsletter Subscription', 'codeweber'),
                'testimonial' => __('Testimonial Form', 'codeweber'),
                'resume' => __('Resume Form', 'codeweber'),
                'callback' => __('Callback Request', 'codeweber'),
            ];
            
            // Если это CPT форма, получаем название из поста
            if (is_numeric($form_id)) {
                $form_post = get_post((int) $form_id);
                if ($form_post && $form_post->post_type === 'codeweber_form') {
                    return $form_post->post_title;
                }
            }
            
            // Для legacy встроенных форм используем тип
            if (isset($type_labels[$form_type])) {
                return $type_labels[$form_type];
            }
        }
        
        // LEGACY: Fallback для обратной совместимости
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



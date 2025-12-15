<?php
/**
 * Consent Data Provider
 * 
 * Провайдер для данных согласий пользователей
 * Примечание: CPT consent_subscriber удален, провайдер больше не экспортирует данные согласий
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/../class-data-provider-interface.php';

class Consent_Data_Provider implements Personal_Data_Provider_Interface {
    
    /**
     * Получить идентификатор провайдера
     * 
     * @return string
     */
    public function get_provider_id(): string {
        return 'user-consents-v2';
    }
    
    /**
     * Получить название провайдера
     * 
     * @return string
     */
    public function get_provider_name(): string {
        return __('User Consents', 'codeweber');
    }
    
    /**
     * Получить описание провайдера
     * 
     * @return string
     */
    public function get_provider_description(): string {
        return __('Personal data from user consent records', 'codeweber');
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
        
        // Find user by email
        $user = get_user_by('email', $email);
        if (!$user) {
            return ['data' => [], 'done' => true];
        }
        
        // Get consents from user meta
        $consents_history = get_user_meta($user->ID, '_codeweber_user_consents', true);
        
        if (empty($consents_history) || !is_array($consents_history)) {
            return ['data' => [], 'done' => true];
        }
        
        $export_items = [];
        
        foreach ($consents_history as $index => $record) {
            $group_id = 'user-consents';
            $group_label = __('User Consents', 'codeweber');
            
            $data = [];
            
            // Consent date and time
            if (!empty($record['context']['timestamp'])) {
                $data[] = [
                    'name' => __('Consent Date & Time', 'codeweber'),
                    'value' => $record['context']['timestamp']
                ];
            }
            
            // Form ID
            if (isset($record['context']['form_id'])) {
                $form_id_display = $record['context']['form_id'];
                // Если это строка (встроенная форма), показываем как есть
                if (is_string($form_id_display)) {
                    $form_id_display = $form_id_display;
                } else {
                    $form_id_display = (string) $form_id_display;
                }
                
                $data[] = [
                    'name' => __('Form ID', 'codeweber'),
                    'value' => $form_id_display
                ];
            }
            
            // Form Name / Form Title (with translation support)
            $form_name = '';
            
            // 1) Используем form_name из контекста (если есть)
            if (!empty($record['context']['form_name'])) {
                $form_name = $record['context']['form_name'];
            }
            // 2) Для встроенных форм используем переведенные названия
            elseif (!empty($record['context']['form_id']) && is_string($record['context']['form_id'])) {
                $builtin_labels = [
                    'testimonial' => __('Testimonial Form', 'codeweber'),
                    'newsletter' => __('Newsletter Subscription', 'codeweber'),
                    'resume' => __('Resume Form', 'codeweber'),
                    'callback' => __('Callback Request', 'codeweber'),
                ];
                if (isset($builtin_labels[$record['context']['form_id']])) {
                    $form_name = $builtin_labels[$record['context']['form_id']];
                }
            }
            // 3) Пытаемся получить из form_id (для CPT форм)
            elseif (!empty($record['context']['form_id']) && is_numeric($record['context']['form_id']) && $record['context']['form_id'] > 0) {
                $form_name = get_the_title($record['context']['form_id']);
            }
            
            if (!empty($form_name)) {
                $data[] = [
                    'name' => __('Form Name', 'codeweber'),
                    'value' => $form_name
                ];
            }
            
            // Submission ID
            if (!empty($record['context']['submission_id'])) {
                $data[] = [
                    'name' => __('Submission ID', 'codeweber'),
                    'value' => (string)$record['context']['submission_id']
                ];
            }
            
            // IP Address
            if (!empty($record['context']['ip_address'])) {
                $data[] = [
                    'name' => __('IP Address', 'codeweber'),
                    'value' => $record['context']['ip_address']
                ];
            }
            
            // User Agent
            if (!empty($record['context']['user_agent'])) {
                $data[] = [
                    'name' => __('Browser User Agent', 'codeweber'),
                    'value' => $record['context']['user_agent']
                ];
            }
            
            // Consents (documents)
            if (!empty($record['consents']) && is_array($record['consents'])) {
                foreach ($record['consents'] as $doc_id => $consent) {
                    $doc = get_post($doc_id);
                    if ($doc) {
                        $doc_title = $doc->post_title;
                        $doc_url = '';
                        
                        // Get document URL (with revision if available)
                        if (!empty($consent['document_revision_id'])) {
                            $doc_url = admin_url('revision.php?revision=' . $consent['document_revision_id']);
                        } else {
                            $doc_url = get_permalink($doc_id);
                        }
                        
                        $consent_info = sprintf(
                            '%s (ID: %d)',
                            $doc_title,
                            $doc_id
                        );
                        
                        // Add revision info
                        if (!empty($consent['document_revision_id'])) {
                            $consent_info .= sprintf(
                                ' - ' . __('Revision ID', 'codeweber') . ': %d',
                                $consent['document_revision_id']
                            );
                        }
                        
                        // Add version info
                        if (!empty($consent['document_version'])) {
                            $consent_info .= ' - ' . __('Version', 'codeweber') . ': ' . $consent['document_version'];
                        }
                        
                        // Add URL (make it clickable in export)
                        if ($doc_url) {
                            $consent_info .= ' - ' . __('URL', 'codeweber') . ': '
                                . '<a href="' . esc_url($doc_url) . '" target="_blank" rel="noopener noreferrer">'
                                . esc_html($doc_url)
                                . '</a>';
                        }
                        
                        // Add revocation status
                        if (!empty($consent['revoked_at'])) {
                            $consent_info .= ' - ' . __('Revoked', 'codeweber') . ': ' . $consent['revoked_at'];
                            if (!empty($consent['revoked_at_gmt'])) {
                                $consent_info .= ' (GMT: ' . $consent['revoked_at_gmt'] . ')';
                            }
                        }
                        
                        $data[] = [
                            'name' => __('Consented Document', 'codeweber'),
                            'value' => $consent_info
                        ];
                    }
                }
            }
            
            $export_items[] = [
                'group_id' => $group_id,
                'group_label' => $group_label,
                'item_id' => 'user-consent-' . $index,
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
        $email = sanitize_email($email);
        
        if (!is_email($email)) {
            return [
                'items_removed' => false,
                'items_retained' => false,
                'messages' => [__('Invalid email address', 'codeweber')],
                'done' => true
            ];
        }
        
        // Find user by email
        $user = get_user_by('email', $email);
        if (!$user) {
            return [
                'items_removed' => false,
                'items_retained' => false,
                'messages' => [__('User not found', 'codeweber')],
                'done' => true
            ];
        }
        
        // Get consents history
        $consents_history = get_user_meta($user->ID, '_codeweber_user_consents', true);
        
        if (empty($consents_history) || !is_array($consents_history)) {
            return [
                'items_removed' => false,
                'items_retained' => false,
                'messages' => [__('No consents found', 'codeweber')],
                'done' => true
            ];
        }
        
        /**
         * Политика хранения согласий (основывается ТОЛЬКО на параметре retention_days):
         * - Параметр задаётся в админке (страница теста personal-data-test) и сохраняется в опции
         *   codeweber_personal_data_retention_days.
         * - Если значение <= 0 — при запросе на удаление мы сразу удаляем всю историю согласий.
         * - Если значение > 0:
         *   1) При ПЕРВОМ запросе на удаление фиксируем дату запроса в user_meta
         *      _codeweber_user_consents_erase_requested_at и НИЧЕГО не удаляем.
         *   2) При последующих запросах сравниваем текущую дату с датой запроса.
         *      Если прошло больше retention_days — удаляем всю историю согласий.
         *      Если нет — оставляем и сообщаем, что период хранения ещё не истёк.
         */
        $retention_days = (int) get_option('codeweber_personal_data_retention_days', 0);
        
        // Немедленное удаление всех согласий
        if ($retention_days <= 0) {
            $deleted = delete_user_meta($user->ID, '_codeweber_user_consents');
            delete_user_meta($user->ID, '_codeweber_user_consents_erase_requested_at');
            
            return [
                'items_removed'  => (bool) $deleted,
                'items_retained' => false,
                'messages'       => [__('User consents deleted', 'codeweber')],
                'done'           => true,
            ];
        }
        
        // Режим хранения N дней с момента ПЕРВОГО запроса на удаление
        $requested_at = get_user_meta($user->ID, '_codeweber_user_consents_erase_requested_at', true);
        
        // Если дата запроса ещё не зафиксирована — фиксируем сейчас и НИЧЕГО не удаляем
        if (empty($requested_at)) {
            update_user_meta($user->ID, '_codeweber_user_consents_erase_requested_at', current_time('mysql'));
            
            return [
                'items_removed'  => false,
                'items_retained' => true,
                'messages'       => [
                    sprintf(
                        /* translators: %d: retention days */
                        __('User consents erase request registered (will be deletable after %d days)', 'codeweber'),
                        $retention_days
                    )
                ],
                'done' => true,
            ];
        }
        
        $requested_timestamp = strtotime($requested_at);
        $threshold_timestamp = time() - ($retention_days * DAY_IN_SECONDS);
        
        // Если с момента запроса на удаление прошло больше retention_days — удаляем всю историю согласий
        if ($requested_timestamp && $requested_timestamp <= $threshold_timestamp) {
            $deleted = delete_user_meta($user->ID, '_codeweber_user_consents');
            delete_user_meta($user->ID, '_codeweber_user_consents_erase_requested_at');
            
            return [
                'items_removed'  => (bool) $deleted,
                'items_retained' => false,
                'messages'       => [__('User consents deleted', 'codeweber')],
                'done'           => true,
            ];
        }
        
        // Период хранения ещё не истёк — историю пока сохраняем
        return [
            'items_removed'  => false,
            'items_retained' => true,
            'messages'       => [
                sprintf(
                    /* translators: %d: retention days */
                    __('User consents retained (within %d-day retention period)', 'codeweber'),
                    $retention_days
                )
            ],
            'done' => true,
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
        
        // Find user by email
        $user = get_user_by('email', $email);
        if (!$user) {
            return false;
        }
        
        // Check if user has consents
        $consents_history = get_user_meta($user->ID, '_codeweber_user_consents', true);
        
        return !empty($consents_history) && is_array($consents_history);
    }
    
    /**
     * Получить список полей
     * 
     * @return array
     */
    public function get_personal_data_fields(): array {
        return [
            'subscriber_email' => __('Subscriber Email', 'codeweber'),
            'subscriber_phone' => __('Subscriber Phone', 'codeweber'),
            'consent_type' => __('Consent Type', 'codeweber'),
            'document_title' => __('Document Title', 'codeweber'),
            'document_url' => __('Document URL', 'codeweber'),
            'acceptance_html' => __('Consent Text', 'codeweber'),
            'consent_date' => __('Consent Date', 'codeweber'),
            'ip_address' => __('IP Address', 'codeweber'),
            'user_agent' => __('Browser User Agent', 'codeweber'),
            'form_title' => __('Form', 'codeweber'),
            'page_url' => __('Page URL', 'codeweber'),
            'revision' => __('Document Revision', 'codeweber')
        ];
    }
}


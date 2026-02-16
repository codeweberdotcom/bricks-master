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
        
        // Вспомогательная функция для построения общих данных записи
        $build_record_base_data = function($record, $email, $user) {
            $data = [];
            
            // Consent date and time
            if (!empty($record['context']['timestamp'])) {
                $data[] = [
                    'name' => __('Consent Date & Time', 'codeweber'),
                    'value' => $record['context']['timestamp']
                ];
            }
            
            // Email Address (with link to user profile)
            $email_value = $email;
            if (is_email($email) && $user) {
                $user_profile_url = admin_url('user-edit.php?user_id=' . $user->ID);
                $email_value = sprintf(
                    '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                    esc_url($user_profile_url),
                    esc_html($email)
                );
            }
            $data[] = [
                'name' => __('Email Address', 'codeweber'),
                'value' => $email_value
            ];
            
            // Form ID
            if (isset($record['context']['form_id'])) {
                $form_id_display = $record['context']['form_id'];
                $form_id_for_link = null;
                
                // Если это не строка, конвертируем в строку
                if (!is_string($form_id_display)) {
                    $form_id_display = (string) $form_id_display;
                    $form_id_for_link = (int) $form_id_display;
                }
                
                // Добавляем ссылку на редактирование формы, если это числовой ID
                if ($form_id_for_link && is_numeric($form_id_display)) {
                    $form_edit_url = admin_url('post.php?post=' . $form_id_for_link . '&action=edit');
                    $form_id_value = sprintf(
                        '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                        esc_url($form_edit_url),
                        esc_html($form_id_display)
                    );
                } else {
                    $form_id_value = $form_id_display;
                }
                
                $data[] = [
                    'name' => __('Form ID', 'codeweber'),
                    'value' => $form_id_value
                ];
            }
            
            // Form Name / Form Title (with translation support)
            $form_name = '';
            $form_id_for_name = isset($record['context']['form_id']) ? $record['context']['form_id'] : null;
            
            // 1) Используем form_name из контекста (если есть)
            if (!empty($record['context']['form_name'])) {
                $form_name = $record['context']['form_name'];
            }
            // 2) НОВОЕ: Используем единую функцию для получения названия формы
            elseif (!empty($form_id_for_name)) {
                $form_id = $form_id_for_name;
                
                // Если это числовой ID, получаем название из CPT
                if (is_numeric($form_id)) {
                    $form_post = get_post((int) $form_id);
                    if ($form_post && $form_post->post_type === 'codeweber_form') {
                        $form_name = $form_post->post_title;
                    }
                }
                
                // Если название не получено, используем get_form_type() для определения типа
                if (empty($form_name) && class_exists('CodeweberFormsCore')) {
                    $form_type = CodeweberFormsCore::get_form_type($form_id);
                    
                    $type_labels = [
                        'form' => __('Regular Form', 'codeweber'),
                        'newsletter' => __('Newsletter Subscription', 'codeweber'),
                        'testimonial' => __('Testimonial Form', 'codeweber'),
                        'resume' => __('Resume Form', 'codeweber'),
                        'callback' => __('Callback Request', 'codeweber'),
                    ];
                    
                    if (isset($type_labels[$form_type])) {
                        $form_name = $type_labels[$form_type];
                    }
                }
                
                // LEGACY: Fallback для обратной совместимости
                if (empty($form_name) && is_string($form_id)) {
                    $builtin_labels = [
                        'testimonial' => __('Testimonial Form', 'codeweber'),
                        'newsletter' => __('Newsletter Subscription', 'codeweber'),
                        'resume' => __('Resume Form', 'codeweber'),
                        'callback' => __('Callback Request', 'codeweber'),
                    ];
                    if (isset($builtin_labels[$form_id])) {
                        $form_name = $builtin_labels[$form_id];
                    }
                }
            }
            
            if (!empty($form_name)) {
                // Добавляем ссылку на редактирование формы
                $form_name_value = $form_name;
                if ($form_id_for_name && is_numeric($form_id_for_name)) {
                    $form_edit_url = admin_url('post.php?post=' . (int) $form_id_for_name . '&action=edit');
                    $form_name_value = sprintf(
                        '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                        esc_url($form_edit_url),
                        esc_html($form_name)
                    );
                }
                
                $data[] = [
                    'name' => __('Form Name', 'codeweber'),
                    'value' => $form_name_value
                ];
            }
            
            // Submission ID
            if (!empty($record['context']['submission_id'])) {
                $submission_id = (string)$record['context']['submission_id'];
                $submission_view_url = admin_url('admin.php?page=codeweber&action=view&id=' . $submission_id);
                $submission_id_value = sprintf(
                    '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                    esc_url($submission_view_url),
                    esc_html($submission_id)
                );
                
                $data[] = [
                    'name' => __('Submission ID', 'codeweber'),
                    'value' => $submission_id_value
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
            
            return $data;
        };
        
        foreach ($consents_history as $index => $record) {
            if (empty($record['consents']) || !is_array($record['consents'])) {
                continue;
            }
            
            // Разделяем согласия на активные и отозванные
            $active_consents = [];
            $revoked_consents = [];
            
            foreach ($record['consents'] as $doc_id => $consent) {
                if (!empty($consent['revoked_at'])) {
                    $revoked_consents[$doc_id] = $consent;
                } else {
                    $active_consents[$doc_id] = $consent;
                }
            }
            
            // Добавляем запись с активными согласиями
            if (!empty($active_consents)) {
                $data = $build_record_base_data($record, $email, $user);
                
                foreach ($active_consents as $doc_id => $consent) {
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
                        
                        $data[] = [
                            'name' => __('Consented Document', 'codeweber'),
                            'value' => $consent_info
                        ];
                    }
                }
                
                $export_items[] = [
                    'group_id' => 'user-consents',
                    'group_label' => __('User Consents', 'codeweber'),
                    'item_id' => 'user-consent-' . $index,
                    'data' => $data,
                ];
            }
            
            // Добавляем запись с отозванными согласиями
            if (!empty($revoked_consents)) {
                $data = $build_record_base_data($record, $email, $user);
                
                foreach ($revoked_consents as $doc_id => $consent) {
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
                        
                        // Add revocation status with Russian translation
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
                
                $export_items[] = [
                    'group_id' => 'user-consents-revoked',
                    'group_label' => __('Revoked consents', 'codeweber'),
                    'item_id' => 'user-consent-revoked-' . $index,
                    'data' => $data,
                ];
            }
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


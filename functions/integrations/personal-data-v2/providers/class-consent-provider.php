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
            if (!empty($record['context']['form_id'])) {
                $form_title = get_the_title($record['context']['form_id']);
                $data[] = [
                    'name' => __('Form', 'codeweber'),
                    'value' => $form_title ? $form_title : __('Form ID', 'codeweber') . ': ' . $record['context']['form_id']
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
                        
                        // Add URL
                        if ($doc_url) {
                            $consent_info .= ' - ' . __('URL', 'codeweber') . ': ' . $doc_url;
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
        
        // Check if there are revoked consents older than 3 years
        // We should only delete consents that were revoked more than 3 years ago
        $three_years_ago_timestamp = strtotime('-3 years');
        $has_recent_consents = false;
        
        foreach ($consents_history as $record) {
            if (!empty($record['consents']) && is_array($record['consents'])) {
                foreach ($record['consents'] as $consent) {
                    // Keep if not revoked
                    if (empty($consent['revoked_at'])) {
                        $has_recent_consents = true;
                        break 2;
                    }
                    
                    // Keep if revoked less than 3 years ago
                    $revoked_timestamp = !empty($consent['revoked_at_gmt']) 
                        ? strtotime($consent['revoked_at_gmt']) 
                        : strtotime($consent['revoked_at']);
                    
                    if ($revoked_timestamp && $revoked_timestamp > $three_years_ago_timestamp) {
                        $has_recent_consents = true;
                        break 2;
                    }
                }
            }
        }
        
        // If all consents are older than 3 years, we can delete them
        if (!$has_recent_consents) {
            $deleted = delete_user_meta($user->ID, '_codeweber_user_consents');
            
            return [
                'items_removed' => $deleted,
                'items_retained' => false,
                'messages' => [__('User consents deleted (older than 3 years)', 'codeweber')],
                'done' => true
            ];
        }
        
        // Some consents are still within 3 years - we should keep them
        return [
            'items_removed' => false,
            'items_retained' => true,
            'messages' => [__('User consents retained (within 3 year retention period)', 'codeweber')],
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


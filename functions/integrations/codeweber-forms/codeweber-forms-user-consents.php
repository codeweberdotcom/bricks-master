<?php
/**
 * CodeWeber Forms User Consents
 * 
 * Functions for saving and managing user consents in user meta
 * Stores consents with document revisions and revocation dates for GDPR compliance
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get or create user by email
 * 
 * @param string $email Email address
 * @param array $user_data Additional user data (first_name, last_name, phone)
 * @return WP_User|WP_Error User object or error
 */
function codeweber_forms_get_or_create_user($email, $user_data = []) {
    // Validate email
    if (!is_email($email)) {
        return new WP_Error('invalid_email', __('Invalid email address', 'codeweber'));
    }
    
    // Try to find existing user
    $user = get_user_by('email', $email);
    if ($user) {
        // Update additional data if provided
        if (!empty($user_data['first_name'])) {
            update_user_meta($user->ID, 'first_name', sanitize_text_field($user_data['first_name']));
        }
        if (!empty($user_data['last_name'])) {
            update_user_meta($user->ID, 'last_name', sanitize_text_field($user_data['last_name']));
        }
        if (!empty($user_data['phone'])) {
            update_user_meta($user->ID, 'phone', sanitize_text_field($user_data['phone']));
        }
        return $user;
    }
    
    // Create new user
    $username = sanitize_user($email, true);
    
    // Generate unique username if taken
    $original_username = $username;
    $counter = 1;
    while (username_exists($username)) {
        $username = $original_username . $counter;
        $counter++;
    }
    
    // Generate random password
    $password = wp_generate_password(12, false);
    
    // Create user
    $user_id = wp_create_user($username, $password, $email);
    
    if (is_wp_error($user_id)) {
        return $user_id;
    }
    
    // Get user object
    $user = get_userdata($user_id);
    if (!$user) {
        return new WP_Error('user_creation_failed', __('Failed to create user', 'codeweber'));
    }
    
    // Set role (subscriber by default)
    $user->set_role('subscriber');
    
    // Save additional data
    if (!empty($user_data['first_name'])) {
        update_user_meta($user_id, 'first_name', sanitize_text_field($user_data['first_name']));
    }
    if (!empty($user_data['last_name'])) {
        update_user_meta($user_id, 'last_name', sanitize_text_field($user_data['last_name']));
    }
    if (!empty($user_data['phone'])) {
        update_user_meta($user_id, 'phone', sanitize_text_field($user_data['phone']));
    }
    
    return $user;
}

/**
 * Save user consents to user meta
 * 
 * @param int $user_id User ID
 * @param array $consents Array of consents with document versions
 * @param array $context Context data (form_id, submission_id, ip_address, user_agent)
 * @return bool|WP_Error True on success, error on failure
 */
function codeweber_forms_save_user_consents($user_id, $consents, $context = []) {
    if (!$user_id || !get_userdata($user_id)) {
        return new WP_Error('invalid_user', __('Invalid user ID', 'codeweber'));
    }
    
    if (empty($consents) || !is_array($consents)) {
        return new WP_Error('invalid_consents', __('Invalid consents data', 'codeweber'));
    }
    
    // Get existing consents history
    $existing_consents = get_user_meta($user_id, '_codeweber_user_consents', true);
    if (!is_array($existing_consents)) {
        $existing_consents = [];
    }
    
    // Process consents and find document revisions
    $processed_consents = [];
    
    foreach ($consents as $doc_id => $consent_data) {
        $doc_id = intval($doc_id);
        
        // Skip if not a valid consent
        if (empty($consent_data['value']) || $consent_data['value'] !== '1') {
            continue;
        }
        
        // Get document
        $doc = get_post($doc_id);
        if (!$doc) {
            continue;
        }
        
        // Get document version
        $document_version = $consent_data['document_version'] ?? $doc->post_modified;
        $document_version_timestamp = $consent_data['document_version_timestamp'] ?? strtotime($doc->post_modified);
        
        // Find revision ID for this version
        $document_revision_id = null;
        if (function_exists('codeweber_forms_find_revision_by_version')) {
            $document_revision_id = codeweber_forms_find_revision_by_version($doc_id, $document_version);
        }
        
        // Build consent record
        $processed_consents[$doc_id] = [
            'value' => '1',
            'document_id' => $doc_id,
            'document_version' => $document_version,
            'document_version_timestamp' => $document_version_timestamp,
            'document_revision_id' => $document_revision_id, // ID ревизии или null
            'revoked_at' => null, // Дата отзыва (заполняется при отзыве)
            'revoked_at_gmt' => null, // Дата отзыва GMT
        ];
    }
    
    if (empty($processed_consents)) {
        return new WP_Error('no_valid_consents', __('No valid consents to save', 'codeweber'));
    }
    
    // Build new consent record
    $new_consent_record = [
        'consents' => $processed_consents,
        'context' => [
            'form_id' => $context['form_id'] ?? 0,
            'form_name' => sanitize_text_field($context['form_name'] ?? ''),
            'submission_id' => $context['submission_id'] ?? 0,
            'ip_address' => sanitize_text_field($context['ip_address'] ?? ''),
            'user_agent' => sanitize_textarea_field($context['user_agent'] ?? ''),
            'timestamp' => current_time('mysql'),
            'timestamp_gmt' => current_time('mysql', true),
        ]
    ];
    
    // Add to history
    $existing_consents[] = $new_consent_record;
    
    // Save to user meta
    $result = update_user_meta($user_id, '_codeweber_user_consents', $existing_consents);
    
    if ($result === false) {
        return new WP_Error('save_failed', __('Failed to save consents to user meta', 'codeweber'));
    }
    
    return true;
}

/**
 * Revoke user consent for a specific document
 * 
 * @param int $user_id User ID
 * @param int $document_id Document ID
 * @return bool|WP_Error True on success, error on failure
 */
function codeweber_forms_revoke_user_consent($user_id, $document_id) {
    if (!$user_id || !get_userdata($user_id)) {
        return new WP_Error('invalid_user', __('Invalid user ID', 'codeweber'));
    }
    
    $document_id = intval($document_id);
    
    // Get existing consents
    $consents_history = get_user_meta($user_id, '_codeweber_user_consents', true);
    if (!is_array($consents_history) || empty($consents_history)) {
        return new WP_Error('no_consents', __('No consents found for this user', 'codeweber'));
    }
    
    // Find and revoke consent in the most recent record
    $revoked = false;
    $current_time = current_time('mysql');
    $current_time_gmt = current_time('mysql', true);
    
    // Process from newest to oldest
    $consents_history = array_reverse($consents_history);
    
    foreach ($consents_history as $index => $record) {
        if (isset($record['consents'][$document_id])) {
            // Check if already revoked
            if (!empty($record['consents'][$document_id]['revoked_at'])) {
                continue; // Already revoked, skip
            }
            
            // Revoke consent
            $consents_history[$index]['consents'][$document_id]['revoked_at'] = $current_time;
            $consents_history[$index]['consents'][$document_id]['revoked_at_gmt'] = $current_time_gmt;
            $revoked = true;
            break; // Revoke only the most recent active consent
        }
    }
    
    if (!$revoked) {
        return new WP_Error('consent_not_found', __('Active consent not found for this document', 'codeweber'));
    }
    
    // Reverse back to original order
    $consents_history = array_reverse($consents_history);
    
    // Save updated consents
    $result = update_user_meta($user_id, '_codeweber_user_consents', $consents_history);
    
    if ($result === false) {
        return new WP_Error('save_failed', __('Failed to save revoked consent', 'codeweber'));
    }
    
    return true;
}

/**
 * Clean up old revoked consents (older than 3 years)
 * 
 * @param int $user_id Optional. User ID. If not provided, cleans for all users
 * @return array Statistics: ['users_processed' => int, 'records_removed' => int]
 */
function codeweber_forms_cleanup_old_consents($user_id = null) {
    global $wpdb;
    
    $three_years_ago = date('Y-m-d H:i:s', strtotime('-3 years'));
    $three_years_ago_timestamp = strtotime('-3 years');
    
    $users_processed = 0;
    $records_removed = 0;
    
    // Get users to process
    if ($user_id) {
        $users = [get_userdata($user_id)];
        if (!$users[0]) {
            return ['users_processed' => 0, 'records_removed' => 0];
        }
    } else {
        // Get all users with consents
        $users = get_users([
            'meta_key' => '_codeweber_user_consents',
            'number' => -1,
        ]);
    }
    
    foreach ($users as $user) {
        if (!$user) {
            continue;
        }
        
        $consents_history = get_user_meta($user->ID, '_codeweber_user_consents', true);
        if (!is_array($consents_history) || empty($consents_history)) {
            continue;
        }
        
        $updated_history = [];
        $removed_count = 0;
        
        foreach ($consents_history as $record) {
            $should_keep = false;
            
            // Check if record has any non-revoked consents
            if (!empty($record['consents']) && is_array($record['consents'])) {
                foreach ($record['consents'] as $doc_id => $consent) {
                    // Keep if consent is not revoked
                    if (empty($consent['revoked_at'])) {
                        $should_keep = true;
                        break;
                    }
                    
                    // Keep if revoked less than 3 years ago
                    $revoked_timestamp = !empty($consent['revoked_at_gmt']) 
                        ? strtotime($consent['revoked_at_gmt']) 
                        : strtotime($consent['revoked_at']);
                    
                    if ($revoked_timestamp && $revoked_timestamp > $three_years_ago_timestamp) {
                        $should_keep = true;
                        break;
                    }
                }
            }
            
            if ($should_keep) {
                $updated_history[] = $record;
            } else {
                $removed_count++;
            }
        }
        
        if ($removed_count > 0) {
            if (empty($updated_history)) {
                // Remove meta if no records left
                delete_user_meta($user->ID, '_codeweber_user_consents');
            } else {
                // Update with cleaned history
                update_user_meta($user->ID, '_codeweber_user_consents', $updated_history);
            }
            $records_removed += $removed_count;
        }
        
        $users_processed++;
    }
    
    return [
        'users_processed' => $users_processed,
        'records_removed' => $records_removed
    ];
}

/**
 * Hook: Save consents when form is submitted
 * Support both hooks for compatibility
 */
add_action('codeweber_form_saved', 'codeweber_forms_save_consents_on_submit', 10, 3);
add_action('codeweber_form_after_saved', 'codeweber_forms_save_consents_on_submit', 10, 3);

function codeweber_forms_save_consents_on_submit($submission_id, $form_id, $form_data) {
    error_log('=== codeweber_forms_save_consents_on_submit START ===');
    error_log('Submission ID: ' . $submission_id);
    error_log('Form ID: ' . $form_id);
    error_log('Form data keys: ' . implode(', ', array_keys($form_data)));
    error_log('Form data (full): ' . print_r($form_data, true));
    
    // Check if form has consents
    if (empty($form_data['newsletter_consents']) || !is_array($form_data['newsletter_consents'])) {
        error_log('codeweber_forms_save_consents_on_submit: No newsletter_consents found in form_data');
        error_log('=== codeweber_forms_save_consents_on_submit END (no consents) ===');
        return; // No consents in this form submission
    }
    
    error_log('codeweber_forms_save_consents_on_submit: Found newsletter_consents: ' . print_r($form_data['newsletter_consents'], true));
    
    // Get email from form data
    $email = $form_data['email'] ?? 
             $form_data['email-address'] ?? 
             $form_data['EMAIL'] ?? 
             '';
    
    if (empty($email) || !is_email($email)) {
        error_log('codeweber_forms_save_consents_on_submit: Invalid or missing email');
        return;
    }
    
    // Get user data from form
    $user_data = [
        'first_name' => $form_data['first_name'] ?? $form_data['text-name'] ?? $form_data['name'] ?? '',
        'last_name' => $form_data['last_name'] ?? $form_data['text-surname'] ?? $form_data['surname'] ?? '',
        'phone' => $form_data['phone'] ?? $form_data['tel'] ?? '',
    ];
    
    // Get or create user
    $user = codeweber_forms_get_or_create_user($email, $user_data);
    
    if (is_wp_error($user)) {
        error_log('codeweber_forms_save_consents_on_submit: Failed to get/create user: ' . $user->get_error_message());
        return;
    }
    
    // Получаем название формы
    $form_name = '';
    
    // 1) Из служебного поля _form_name (приходит из шорткода name)
    if (!empty($form_data['_form_name'])) {
        $form_name = sanitize_text_field($form_data['_form_name']);
    }
    // 2) Из submission в базе данных
    elseif ($submission_id && class_exists('CodeweberFormsDatabase')) {
        $db = new CodeweberFormsDatabase();
        $submission = $db->get_submission($submission_id);
        if ($submission && !empty($submission->form_name)) {
            $form_name = $submission->form_name;
        }
    }
    // 3) Из form_id для встроенных форм (строковые ключи)
    if (empty($form_name) && is_string($form_id) && !empty($form_id)) {
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
    // 4) Из CPT формы по ID
    elseif (empty($form_name) && is_numeric($form_id) && $form_id > 0) {
        $form_post = get_post($form_id);
        if ($form_post && $form_post->post_type === 'codeweber_form') {
            $form_name = $form_post->post_title;
        }
    }
    
    // Prepare context
    $context = [
        'form_id' => $form_id,
        'form_name' => $form_name,
        'submission_id' => $submission_id,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
    ];
    
    // Save consents
    $result = codeweber_forms_save_user_consents($user->ID, $form_data['newsletter_consents'], $context);
    
    if (is_wp_error($result)) {
        error_log('codeweber_forms_save_consents_on_submit: Failed to save consents: ' . $result->get_error_message());
    } else {
        error_log('codeweber_forms_save_consents_on_submit: Consents saved successfully for user ID: ' . $user->ID);
        // Verify saved consents
        $saved_consents = get_user_meta($user->ID, '_codeweber_user_consents', true);
        error_log('codeweber_forms_save_consents_on_submit: Verified saved consents count: ' . (is_array($saved_consents) ? count($saved_consents) : 0));
    }
    error_log('=== codeweber_forms_save_consents_on_submit END ===');
}


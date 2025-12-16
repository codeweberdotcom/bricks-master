<?php
/**
 * CodeWeber Forms Consent Helper
 * 
 * Helper functions for working with consent documents
 * Independent from CF7 integration
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get document URL by document ID
 * 
 * @param int $document_id Document ID (can be Privacy Policy page ID or Legal CPT post ID)
 * @param string|int $version Optional. Document version (post_modified date or timestamp) to link to specific revision
 * @return string|false URL of the document or revision admin page, or false if not found
 */
function codeweber_forms_get_document_url($document_id, $version = null) {
    if (empty($document_id)) {
        return false;
    }
    
    $document_id = intval($document_id);
    
    // Check if document exists and is published
    $post = get_post($document_id);
    if (!$post || $post->post_status !== 'publish') {
        return false;
    }
    
    // Если указана версия, пытаемся найти ревизию
    if (!empty($version)) {
        $revision_id = codeweber_forms_find_revision_by_version($document_id, $version);
        
        if ($revision_id) {
            // Нашли ревизию - возвращаем ссылку на страницу ревизии в админке
            return admin_url('revision.php?revision=' . $revision_id);
        }
        // Если ревизия не найдена, возвращаем простую публичную ссылку без параметра version
    }
    
    // Возвращаем публичную ссылку на документ (без параметра version)
    return esc_url(get_permalink($document_id));
}

/**
 * Find revision ID by document version
 * 
 * @param int $document_id Document ID
 * @param string|int $version Document version (post_modified date or timestamp)
 * @return int|false Revision ID or false if not found
 */
function codeweber_forms_find_revision_by_version($document_id, $version) {
    if (empty($document_id) || empty($version)) {
        return false;
    }
    
    $document_id = intval($document_id);
    
    // Конвертируем версию в timestamp для сравнения
    $version_timestamp = null;
    if (is_numeric($version)) {
        $version_timestamp = intval($version);
    } else {
        $version_timestamp = strtotime($version);
    }
    
    if (!$version_timestamp) {
        return false;
    }
    
    // Получаем все ревизии поста
    $revisions = wp_get_post_revisions($document_id, [
        'numberposts' => -1,
        'order' => 'DESC',
    ]);
    
    if (empty($revisions)) {
        return false;
    }
    
    // Ищем ревизию, которая соответствует указанной версии
    // Сначала ищем точное совпадение по post_modified
    foreach ($revisions as $revision) {
        $revision_timestamp = strtotime($revision->post_modified);
        
        // Если ревизия была создана в момент указанной версии (с точностью до секунды)
        if (abs($revision_timestamp - $version_timestamp) <= 1) {
            return $revision->ID;
        }
    }
    
    // Если точного совпадения нет, ищем ближайшую ревизию, которая была создана до или в момент указанной версии
    $target_revision = null;
    foreach ($revisions as $revision) {
        $revision_timestamp = strtotime($revision->post_modified);
        
        // Если ревизия была создана до или в момент указанной версии
        if ($revision_timestamp <= $version_timestamp) {
            $target_revision = $revision;
            break;
        }
    }
    
    if ($target_revision) {
        return $target_revision->ID;
    }
    
    return false;
}

/**
 * Get document type
 * 
 * @param int $document_id Document ID
 * @return string|false Document type ('privacy_policy', 'legal', or false if not found)
 */
function codeweber_forms_get_document_type($document_id) {
    if (empty($document_id)) {
        return false;
    }
    
    $document_id = intval($document_id);
    $post = get_post($document_id);
    
    if (!$post) {
        return false;
    }
    
    // Check if it's Privacy Policy page
    $privacy_policy_page_id = (int) get_option('wp_page_for_privacy_policy');
    if ($document_id === $privacy_policy_page_id) {
        return 'privacy_policy';
    }
    
    // Check if it's Legal CPT
    if ($post->post_type === 'legal') {
        return 'legal';
    }
    
    return false;
}

/**
 * Get document title
 * 
 * @param int $document_id Document ID
 * @return string|false Document title or false if not found
 */
function codeweber_forms_get_document_title($document_id) {
    if (empty($document_id)) {
        return false;
    }
    
    $document_id = intval($document_id);
    $post = get_post($document_id);
    
    if (!$post || $post->post_status !== 'publish') {
        return false;
    }
    
    return get_the_title($document_id);
}

/**
 * Process consent label text
 * Replaces placeholders and shortcodes with actual document links
 * 
 * @param string $label Label text with placeholders/shortcodes
 * @param int $document_id Document ID to link to
 * @param int $form_id Form ID (optional, for compatibility with CF7 shortcodes)
 * @return string Processed label with actual links
 */
function codeweber_forms_process_consent_label($label, $document_id, $form_id = 0) {
    if (empty($label)) {
        return '';
    }
    
    $document_id = intval($document_id);
    $document_url = codeweber_forms_get_document_url($document_id);
    $document_title = codeweber_forms_get_document_title($document_id);
    
    if (!$document_url) {
        // If document not found, return label without links
        return $label;
    }
    
    $processed = $label;
    
    // Replace {document_title_url} placeholder (link with title)
    if ($document_url && $document_title) {
        $title_link = '<a href="' . esc_url($document_url) . '">' . esc_html($document_title) . '</a>';
        $processed = str_replace('{document_title_url}', $title_link, $processed);
    }
    
    // Replace {document_url} placeholder
    $processed = str_replace('{document_url}', $document_url, $processed);
    
    // Replace {document_title} placeholder
    if ($document_title) {
        $processed = str_replace('{document_title}', esc_html($document_title), $processed);
    }
    
    // Process CF7-style shortcodes for backward compatibility
    // [cf7_privacy_policy id="{form_id}"]
    $processed = preg_replace_callback(
        '/\[cf7_privacy_policy[^\]]*\]/i',
        function($matches) use ($document_url) {
            return $document_url;
        },
        $processed
    );
    
    // [cf7_legal_consent_link id="{form_id}"]
    $processed = preg_replace_callback(
        '/\[cf7_legal_consent_link[^\]]*\]/i',
        function($matches) use ($document_url) {
            return $document_url;
        },
        $processed
    );
    
    // [cf7_mailing_consent_link id="{form_id}"]
    $processed = preg_replace_callback(
        '/\[cf7_mailing_consent_link[^\]]*\]/i',
        function($matches) use ($document_url) {
            return $document_url;
        },
        $processed
    );
    
    // Process {form_id} placeholder (replace with actual form ID or 0)
    $processed = str_replace('{form_id}', $form_id, $processed);
    
    return $processed;
}

/**
 * Get all available consent documents
 * Returns array of all available documents (Privacy Policy + Legal documents)
 * 
 * @return array Array of documents with 'id', 'title', 'type', 'url'
 */
function codeweber_forms_get_all_documents() {
    $documents = [];
    
    // Get Privacy Policy page
    $privacy_policy_page_id = (int) get_option('wp_page_for_privacy_policy');
    if ($privacy_policy_page_id) {
        $privacy_page = get_post($privacy_policy_page_id);
        if ($privacy_page && $privacy_page->post_status === 'publish') {
            $documents[] = [
                'id' => $privacy_page->ID,
                'title' => $privacy_page->post_title,
                'type' => 'privacy_policy',
                'url' => get_permalink($privacy_page->ID),
            ];
        }
    }
    
    // Get Legal documents
    $legal_documents = get_posts([
        'post_type' => 'legal',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC',
    ]);
    
    foreach ($legal_documents as $doc) {
        $documents[] = [
            'id' => $doc->ID,
            'title' => $doc->post_title,
            'type' => 'legal',
            'url' => get_permalink($doc->ID),
        ];
    }
    
    return $documents;
}

/**
 * Render consent checkbox HTML
 * 
 * @param array $consent Consent data with 'label', 'document_id', 'required'
 * @param string $field_name Field name for the checkbox
 * @param int $form_id Form ID (optional)
 * @return string HTML for consent checkbox
 */
function codeweber_forms_render_consent_checkbox($consent, $field_name = 'consent', $form_id = 0) {
    if (empty($consent['label']) || empty($consent['document_id'])) {
        return '';
    }
    
    $document_id = intval($consent['document_id']);
    $label_text = codeweber_forms_process_consent_label($consent['label'], $document_id, $form_id);
    $required = !empty($consent['required']);
    $checkbox_id = 'consent-' . $document_id . '-' . uniqid();
    
    // Получаем класс скругления формы из темы
    $form_radius_class = function_exists('getThemeFormRadius') ? getThemeFormRadius() : '';
    
    $html = '<div class="form-check small-checkbox small-chekbox mb-1">';
    $html .= '<input type="checkbox" class="form-check-input' . esc_attr($form_radius_class) . '" id="' . esc_attr($checkbox_id) . '" name="' . esc_attr($field_name) . '[' . esc_attr($document_id) . ']" value="1"';
    if ($required) {
        $html .= ' required';
    }
    $html .= '>';
    $html .= '<label class="form-check-label" for="' . esc_attr($checkbox_id) . '">';
    $html .= wp_kses_post($label_text);
    $html .= '</label>';
    $html .= '</div>';
    
    return $html;
}

/**
 * Validate consent checkboxes
 * 
 * @param array $submitted_consents Submitted consent data
 * @param array $required_consents Array of required consent IDs
 * @return array ['valid' => bool, 'missing' => array] Validation result
 */
function codeweber_forms_validate_consents($submitted_consents, $required_consents) {
    $missing = [];
    
    foreach ($required_consents as $consent_id) {
        if (empty($submitted_consents[$consent_id]) || $submitted_consents[$consent_id] !== '1') {
            $missing[] = $consent_id;
        }
    }
    
    return [
        'valid' => empty($missing),
        'missing' => $missing,
    ];
}


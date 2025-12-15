<?php
/**
 * CodeWeber Forms Document Version Handler
 * 
 * Handles display of specific document versions when version parameter is in URL
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handle document version display
 * When version parameter is present in URL, try to find and display that version
 */
add_action('template_redirect', 'codeweber_forms_handle_document_version', 5);

function codeweber_forms_handle_document_version() {
    // Проверяем, есть ли параметр version в URL
    if (!isset($_GET['version']) || empty($_GET['version'])) {
        return;
    }
    
    $version = sanitize_text_field($_GET['version']);
    
    // Получаем текущий пост
    global $post;
    if (!$post || !is_singular()) {
        return;
    }
    
    // Проверяем, что это документ (Privacy Policy или Legal CPT)
    $is_document = false;
    
    // Проверяем Privacy Policy
    $privacy_policy_page_id = (int) get_option('wp_page_for_privacy_policy');
    if ($post->ID === $privacy_policy_page_id) {
        $is_document = true;
    }
    
    // Проверяем Legal CPT
    if ($post->post_type === 'legal') {
        $is_document = true;
    }
    
    if (!$is_document) {
        return;
    }
    
    // Пытаемся найти ревизию, которая соответствует указанной версии
    // Версия может быть в формате timestamp или даты (post_modified)
    
    // Конвертируем версию в timestamp для сравнения
    $version_timestamp = null;
    if (is_numeric($version)) {
        $version_timestamp = intval($version);
    } else {
        $version_timestamp = strtotime($version);
    }
    
    if (!$version_timestamp) {
        return; // Не удалось распарсить версию
    }
    
    // Получаем timestamp текущей версии документа
    $current_timestamp = strtotime($post->post_modified);
    
    // Получаем все ревизии поста
    $revisions = wp_get_post_revisions($post->ID, [
        'numberposts' => -1,
        'order' => 'DESC',
    ]);
    
    // Ищем ревизию, которая была актуальна на момент указанной версии
    $target_revision = null;
    if (!empty($revisions)) {
        foreach ($revisions as $revision) {
            $revision_timestamp = strtotime($revision->post_modified);
            
            // Если ревизия была создана до или в момент указанной версии
            if ($revision_timestamp <= $version_timestamp) {
                $target_revision = $revision;
                break;
            }
        }
    }
    
    // Определяем, нужно ли показывать старую версию
    $is_old_version = false;
    if ($target_revision) {
        // Нашли ревизию - показываем её
        $is_old_version = true;
    } elseif ($current_timestamp > $version_timestamp) {
        // Текущая версия новее, чем сохраненная - значит документ был изменен
        // Пытаемся найти ближайшую ревизию к указанной версии
        if (!empty($revisions)) {
            // Ищем самую старую ревизию, которая была создана после указанной версии
            $closest_revision = null;
            foreach (array_reverse($revisions) as $revision) {
                $revision_timestamp = strtotime($revision->post_modified);
                if ($revision_timestamp > $version_timestamp) {
                    $closest_revision = $revision;
                    break;
                }
            }
            
            if ($closest_revision) {
                // Нашли ближайшую ревизию - показываем её
                $target_revision = $closest_revision;
                $is_old_version = true;
            }
        }
    }
    
    // Если нашли ревизию для отображения, заменяем контент
    if ($target_revision && $is_old_version) {
        // Заменяем на контент ревизии
        $post->post_content = $target_revision->post_content;
        $post->post_title = $target_revision->post_title;
        $post->post_excerpt = $target_revision->post_excerpt;
        
        // Добавляем уведомление о том, что отображается старая версия
        add_filter('the_content', function($content) use ($version_timestamp) {
            $version_date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $version_timestamp);
            $notice = '<div class="document-version-notice" style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; margin-bottom: 20px; border-radius: 4px;">';
            $notice .= '<strong>' . __('Внимание:', 'codeweber') . '</strong> ';
            $notice .= sprintf(__('Отображается версия документа от %s (версия, актуальная на момент вашего согласия).', 'codeweber'), $version_date);
            $notice .= ' <a href="' . esc_url(remove_query_arg('version')) . '">' . __('Показать текущую версию', 'codeweber') . '</a>';
            $notice .= '</div>';
            return $notice . $content;
        }, 5);
    } elseif ($current_timestamp <= $version_timestamp) {
        // Текущая версия соответствует или старше указанной - документ не изменялся
        // Можно показать уведомление, что отображается актуальная версия
        add_filter('the_content', function($content) use ($version_timestamp) {
            $version_date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $version_timestamp);
            $notice = '<div class="document-version-notice" style="background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; margin-bottom: 20px; border-radius: 4px;">';
            $notice .= '<strong>' . __('Информация:', 'codeweber') . '</strong> ';
            $notice .= sprintf(__('Отображается актуальная версия документа (версия от %s).', 'codeweber'), $version_date);
            $notice .= '</div>';
            return $notice . $content;
        }, 5);
    }
}


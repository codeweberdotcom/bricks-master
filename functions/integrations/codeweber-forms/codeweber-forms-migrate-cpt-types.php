<?php
/**
 * CodeWeber Forms - CPT Forms Type Migration
 * 
 * Миграция существующих CPT форм: добавление _form_type для форм без типа
 * 
 * ВАЖНО: Эта миграция затрагивает ТОЛЬКО CPT формы (тип поста codeweber_form).
 * Legacy встроенные формы (testimonial, newsletter, resume, callback) НЕ мигрируются
 * и продолжают работать через строковые ID.
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

class CodeweberFormsCPTMigration {
    
    /**
     * Мигрировать все CPT формы без типа
     * 
     * @return array Результат миграции
     */
    public static function migrate_all_forms() {
        $results = [
            'total' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
            'errors_list' => [],
        ];
        
        // Получаем все CPT формы
        // ВАЖНО: Только CPT формы, legacy формы не трогаем
        $forms = get_posts([
            'post_type' => 'codeweber_form',
            'posts_per_page' => -1,
            'post_status' => ['publish', 'draft', 'private'],
        ]);
        
        $results['total'] = count($forms);
        
        foreach ($forms as $form) {
            $result = self::migrate_single_form($form->ID);
            
            if ($result === true) {
                $results['updated']++;
            } elseif ($result === 'skipped') {
                $results['skipped']++;
            } else {
                $results['errors']++;
                $results['errors_list'][] = [
                    'form_id' => $form->ID,
                    'form_title' => $form->post_title,
                    'error' => $result,
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Мигрировать одну форму
     * 
     * @param int $form_id ID формы
     * @return bool|string true - обновлено, 'skipped' - пропущено, string - ошибка
     */
    public static function migrate_single_form($form_id) {
        // Проверяем, есть ли уже тип
        $existing_type = get_post_meta($form_id, '_form_type', true);
        if (!empty($existing_type)) {
            return 'skipped'; // Уже есть тип
        }
        
        // Пытаемся извлечь тип из блока
        $form_type = null;
        
        if (class_exists('CodeweberFormsCore')) {
            $form_type = CodeweberFormsCore::get_form_type($form_id);
        } else {
            // Fallback: извлекаем из блока напрямую
            $post = get_post($form_id);
            if ($post && !empty($post->post_content)) {
                $form_type = self::extract_type_from_block($post->post_content);
            }
        }
        
        // Если тип не найден - устанавливаем 'form' по умолчанию
        if (empty($form_type)) {
            $form_type = 'form';
        }
        
        // Сохраняем тип
        $result = update_post_meta($form_id, '_form_type', $form_type);
        
        if ($result !== false) {
            return true;
        }
        
        return 'Failed to save form type';
    }
    
    /**
     * Извлечь тип формы из блока Gutenberg
     * 
     * @param string $content Содержимое поста
     * @return string|null Тип формы или null
     */
    private static function extract_type_from_block($content) {
        if (empty($content) || !has_blocks($content)) {
            return null;
        }
        
        $blocks = parse_blocks($content);
        foreach ($blocks as $block) {
            if ($block['blockName'] === 'codeweber-blocks/form' && !empty($block['attrs']['formType'])) {
                return sanitize_text_field($block['attrs']['formType']);
            }
        }
        
        return null;
    }
    
    /**
     * Запустить миграцию через WP-CLI или админку
     * 
     * @return array Результат миграции
     */
    public static function run_migration() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to run this migration.', 'codeweber'));
        }
        
        $results = self::migrate_all_forms();
        
        // Логируем результаты
        error_log('Codeweber Forms CPT Migration Results: ' . print_r($results, true));
        
        return $results;
    }
}

// Хук для запуска миграции через админку (опционально)
add_action('admin_init', function() {
    if (isset($_GET['codeweber_forms_migrate_cpt']) && 
        current_user_can('manage_options') &&
        isset($_GET['_wpnonce']) &&
        wp_verify_nonce($_GET['_wpnonce'], 'codeweber_forms_migrate_cpt')) {
        
        $results = CodeweberFormsCPTMigration::run_migration();
        
        // Показываем результаты
        add_action('admin_notices', function() use ($results) {
            $message = sprintf(
                __('Migration completed: %d total, %d updated, %d skipped, %d errors.', 'codeweber'),
                $results['total'],
                $results['updated'],
                $results['skipped'],
                $results['errors']
            );
            $notice_class = $results['errors'] > 0 ? 'notice-warning' : 'notice-success';
            echo '<div class="notice ' . esc_attr($notice_class) . ' is-dismissible"><p>' . esc_html($message) . '</p></div>';
            
            if (!empty($results['errors_list'])) {
                echo '<div class="notice notice-error is-dismissible"><p><strong>' . __('Errors:', 'codeweber') . '</strong></p><ul>';
                foreach ($results['errors_list'] as $error) {
                    echo '<li>' . sprintf(
                        __('Form ID %d (%s): %s', 'codeweber'),
                        $error['form_id'],
                        esc_html($error['form_title']),
                        esc_html($error['error'])
                    ) . '</li>';
                }
                echo '</ul></div>';
            }
        });
        
        // Редирект для очистки URL
        wp_safe_redirect(admin_url('edit.php?post_type=codeweber_form'));
        exit;
    }
});













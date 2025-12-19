<?php
/**
 * CodeWeber Forms Database Class
 * 
 * Handles database operations for form submissions
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

class CodeweberFormsDatabase {
    private $table_name;
    private $version = '1.0.4'; // Добавлена колонка form_type для быстрого поиска и фильтрации
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'codeweber_forms_submissions';
        $this->create_table();
    }
    
    /**
     * Create submissions table
     */
    private function create_table() {
        global $wpdb;
        
        $current_version = get_option('codeweber_forms_db_version', '0');
        
        if ($current_version !== $this->version) {
            $charset_collate = $wpdb->get_charset_collate();
            
            // Проверяем, существует ли таблица
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") === $this->table_name;
            
            // Миграция 1.0.2 -> 1.0.3: изменяем тип поля form_id с BIGINT на VARCHAR
            if ($table_exists && version_compare($current_version, '1.0.3', '<')) {
                $wpdb->query("ALTER TABLE {$this->table_name} MODIFY COLUMN form_id VARCHAR(255) NOT NULL DEFAULT '0'");
            }
            
            // Миграция 1.0.3 -> 1.0.4: добавляем колонку form_type
            if ($table_exists && version_compare($current_version, '1.0.4', '<')) {
                // Проверяем, существует ли колонка
                $column_exists = $wpdb->get_results(
                    "SHOW COLUMNS FROM {$this->table_name} LIKE 'form_type'"
                );
                
                if (empty($column_exists)) {
                    // Добавляем колонку
                    $wpdb->query(
                        "ALTER TABLE {$this->table_name} 
                         ADD COLUMN form_type VARCHAR(50) DEFAULT NULL AFTER form_name,
                         ADD KEY form_type (form_type)"
                    );
                    
                    // Попытка заполнить существующие записи
                    $this->migrate_existing_form_types();
                }
            }
            
            $sql = "CREATE TABLE {$this->table_name} (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                form_id VARCHAR(255) NOT NULL DEFAULT '0',
                form_name VARCHAR(255) DEFAULT '',
                form_type VARCHAR(50) DEFAULT NULL,
                submission_data LONGTEXT NOT NULL,
                files_data LONGTEXT DEFAULT NULL,
                ip_address VARCHAR(45) DEFAULT '',
                user_agent TEXT,
                user_id BIGINT(20) UNSIGNED DEFAULT 0,
                status ENUM('new', 'read', 'archived', 'trash') DEFAULT 'new',
                email_sent TINYINT(1) DEFAULT 0,
                email_error TEXT DEFAULT NULL,
                auto_reply_sent TINYINT(1) DEFAULT 0,
                auto_reply_error TEXT DEFAULT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY form_id (form_id(191)),
                KEY form_type (form_type),
                KEY status (status),
                KEY user_id (user_id),
                KEY created_at (created_at)
            ) $charset_collate;";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            
            update_option('codeweber_forms_db_version', $this->version);
        }
    }
    
    /**
     * Save submission to database
     */
    public function save_submission($data) {
        global $wpdb;
        
        $defaults = [
            'form_id' => 0,
            'form_name' => '',
            'submission_data' => '',
            'files_data' => null,
            'ip_address' => '',
            'user_agent' => '',
            'user_id' => 0,
            'status' => 'new',
            'email_sent' => 0,
            'email_error' => null,
            'auto_reply_sent' => 0,
            'auto_reply_error' => null,
        ];
        
        $data = wp_parse_args($data, $defaults);
        
        // Подготовка данных
        // form_id может быть как числом (для CPT форм), так и строкой (для встроенных форм: testimonial, newsletter и т.д.)
        $form_id_value = $data['form_id'];
        if (is_numeric($form_id_value)) {
            $form_id_value = (string) $form_id_value; // Преобразуем число в строку для единообразия
        } else {
            $form_id_value = sanitize_text_field($form_id_value); // Очищаем строку
        }
        
        // НОВОЕ: Определяем form_type (поддержка legacy + нового функционала)
        $form_type = 'form'; // По умолчанию
        
        // Приоритет 1: Явно переданный тип (для новых форм)
        if (!empty($data['form_type'])) {
            $form_type = sanitize_text_field($data['form_type']);
        } 
        // Приоритет 2: Автоматическое определение через единую функцию
        else if (class_exists('CodeweberFormsCore')) {
            $form_type = CodeweberFormsCore::get_form_type($form_id_value);
        } 
        // Приоритет 3: LEGACY - поддержка старых встроенных форм
        else {
            // Legacy: строковые ID (testimonial, newsletter, resume, callback)
            if (!is_numeric($form_id_value)) {
                $form_id_lower = strtolower($form_id_value);
                $builtin_types = ['newsletter', 'testimonial', 'resume', 'callback'];
                if (in_array($form_id_lower, $builtin_types)) {
                    // Для legacy форм тип = ID (обратная совместимость)
                    $form_type = $form_id_lower;
                }
            } 
            // Legacy: числовые ID без метаполя
            else {
                $meta_type = get_post_meta((int) $form_id_value, '_form_type', true);
                if (!empty($meta_type)) {
                    $form_type = $meta_type;
                }
            }
        }
        
        $insert_data = [
            'form_id' => $form_id_value,
            'form_name' => sanitize_text_field($data['form_name']),
            'form_type' => $form_type, // НОВОЕ: сохраняем тип для быстрого поиска
            'submission_data' => is_string($data['submission_data']) ? $data['submission_data'] : json_encode($data['submission_data'], JSON_UNESCAPED_UNICODE),
            'files_data' => !empty($data['files_data']) ? (is_string($data['files_data']) ? $data['files_data'] : json_encode($data['files_data'], JSON_UNESCAPED_UNICODE)) : null,
            'ip_address' => sanitize_text_field($data['ip_address']),
            'user_agent' => sanitize_textarea_field($data['user_agent']),
            'user_id' => intval($data['user_id']),
            'status' => sanitize_text_field($data['status']),
            'email_sent' => intval($data['email_sent']),
            'email_error' => !empty($data['email_error']) ? sanitize_textarea_field($data['email_error']) : null,
            'auto_reply_sent' => intval($data['auto_reply_sent'] ?? 0),
            'auto_reply_error' => !empty($data['auto_reply_error']) ? sanitize_textarea_field($data['auto_reply_error']) : null,
        ];
        
        $result = $wpdb->insert($this->table_name, $insert_data);
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Get submission by ID
     */
    public function get_submission($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            intval($id)
        ));
    }
    
    /**
     * Get submissions with filters
     */
    public function get_submissions($args = []) {
        global $wpdb;
        
        $defaults = [
            'form_id'        => '',
            'form_type'      => '', // НОВОЕ: Фильтрация по типу формы
            'status'         => '',
            'exclude_status' => '',
            'search'         => '',
            'limit'          => 20,
            'offset'         => 0,
            'orderby'        => 'created_at',
            'order'          => 'DESC'
        ];
        $args = wp_parse_args($args, $defaults);
        
        $where = [];
        // НОВОЕ: Исправлен баг - form_id теперь VARCHAR, используем %s вместо %d
        // Поддержка legacy (строковые ID) и новых (числовые ID) форм
        if (isset($args['form_id']) && $args['form_id'] !== '') {
            $form_id = $args['form_id'];
            // form_id может быть как числом (CPT формы), так и строкой (legacy built-in формы)
            if (is_numeric($form_id)) {
                $where[] = $wpdb->prepare("form_id = %s", (string) $form_id);
            } else {
                $where[] = $wpdb->prepare("form_id = %s", sanitize_text_field($form_id));
            }
        }
        // НОВОЕ: Фильтрация по типу формы
        if (!empty($args['form_type'])) {
            $where[] = $wpdb->prepare("form_type = %s", sanitize_text_field($args['form_type']));
        }
        if (!empty($args['status'])) {
            $where[] = $wpdb->prepare("status = %s", sanitize_text_field($args['status']));
        } elseif (!empty($args['exclude_status'])) {
            $where[] = $wpdb->prepare("status != %s", sanitize_text_field($args['exclude_status']));
        }
        if (!empty($args['search'])) {
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $where[] = $wpdb->prepare(
                "(form_name LIKE %s OR submission_data LIKE %s OR ip_address LIKE %s)",
                $search_term,
                $search_term,
                $search_term
            );
        }
        
        $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Валидация и обработка orderby
        $allowed_orderby = array('id', 'form_id', 'form_name', 'form_type', 'status', 'created_at', 'updated_at');
        $orderby_field = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'created_at';
        $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';
        
        // Безопасная вставка имени колонки через whitelist (уже валидировано выше)
        // Используем обратные кавычки для имен колонок
        $orderby = '`' . $orderby_field . '` ' . $order;
        
        $limit = intval($args['limit']);
        $offset = intval($args['offset']);
        
        $query = "SELECT * FROM {$this->table_name} {$where_clause} 
                  ORDER BY {$orderby} 
                  LIMIT {$limit} OFFSET {$offset}";
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Count submissions
     */
    public function count_submissions($args = []) {
        global $wpdb;
        
        $where = [];
        // НОВОЕ: Исправлен баг - form_id теперь VARCHAR, используем %s вместо %d
        // Поддержка legacy (строковые ID) и новых (числовые ID) форм
        if (!empty($args['form_id'])) {
            $form_id = $args['form_id'];
            // form_id может быть как числом (CPT формы), так и строкой (legacy built-in формы)
            if (is_numeric($form_id)) {
                $where[] = $wpdb->prepare("form_id = %s", (string) $form_id);
            } else {
                $where[] = $wpdb->prepare("form_id = %s", sanitize_text_field($form_id));
            }
        }
        // НОВОЕ: Фильтрация по типу формы
        if (!empty($args['form_type'])) {
            $where[] = $wpdb->prepare("form_type = %s", sanitize_text_field($args['form_type']));
        }
        if (!empty($args['status'])) {
            $where[] = $wpdb->prepare("status = %s", sanitize_text_field($args['status']));
        } elseif (!empty($args['exclude_status'])) {
            $where[] = $wpdb->prepare("status != %s", sanitize_text_field($args['exclude_status']));
        }
        if (!empty($args['search'])) {
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $where[] = $wpdb->prepare(
                "(form_name LIKE %s OR submission_data LIKE %s OR ip_address LIKE %s)",
                $search_term,
                $search_term,
                $search_term
            );
        }
        
        $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        return $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} {$where_clause}");
    }
    
    /**
     * Update submission status
     */
    public function update_submission_status($id, $status) {
        global $wpdb;
        return $wpdb->update(
            $this->table_name,
            ['status' => sanitize_text_field($status)],
            ['id' => intval($id)],
            ['%s'],
            ['%d']
        );
    }
    
    /**
     * Update submission data
     */
    public function update_submission($id, $data) {
        global $wpdb;
        
        $update_data = [];
        $format = [];
        
        if (isset($data['status'])) {
            $update_data['status'] = sanitize_text_field($data['status']);
            $format[] = '%s';
        }
        
        if (isset($data['email_sent'])) {
            $update_data['email_sent'] = intval($data['email_sent']);
            $format[] = '%d';
        }
        
        if (isset($data['email_error'])) {
            $update_data['email_error'] = sanitize_textarea_field($data['email_error']);
            $format[] = '%s';
        }
        
        if (isset($data['auto_reply_sent'])) {
            $update_data['auto_reply_sent'] = intval($data['auto_reply_sent']);
            $format[] = '%d';
        }
        
        if (isset($data['auto_reply_error'])) {
            $update_data['auto_reply_error'] = sanitize_textarea_field($data['auto_reply_error']);
            $format[] = '%s';
        }
        
        if (empty($update_data)) {
            return false;
        }
        
        return $wpdb->update(
            $this->table_name,
            $update_data,
            ['id' => intval($id)],
            $format,
            ['%d']
        );
    }
    
    /**
     * Soft delete submission (change status to trash)
     */
    public function delete_submission($id) {
        return $this->update_submission_status($id, 'trash');
    }
    
    /**
     * Permanently delete submission from database
     */
    public function permanently_delete_submission($id) {
        global $wpdb;
        return $wpdb->delete($this->table_name, ['id' => intval($id)], ['%d']);
    }

    /**
     * Permanently delete all submissions that are in trash
     */
    public function empty_trash() {
        global $wpdb;
        return $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$this->table_name} WHERE status = %s",
                'trash'
            )
        );
    }
    
    /**
     * Bulk delete submissions (move to trash)
     */
    public function bulk_delete_submissions($ids) {
        global $wpdb;
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids);
        
        if (empty($ids)) {
            return false;
        }
        
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $query = $wpdb->prepare(
            "UPDATE {$this->table_name} SET status = 'trash' WHERE id IN ($placeholders)",
            ...$ids
        );
        return $wpdb->query($query);
    }
    
    /**
     * Bulk permanently delete submissions
     */
    public function bulk_permanently_delete_submissions($ids) {
        global $wpdb;
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids);
        
        if (empty($ids)) {
            return false;
        }
        
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $query = $wpdb->prepare(
            "DELETE FROM {$this->table_name} WHERE id IN ($placeholders)",
            ...$ids
        );
        return $wpdb->query($query);
    }
    
    /**
     * Bulk update submission status
     */
    public function bulk_update_status($ids, $status) {
        global $wpdb;
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids);
        
        if (empty($ids)) {
            return false;
        }
        
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $query = $wpdb->prepare(
            "UPDATE {$this->table_name} SET status = %s WHERE id IN ($placeholders)",
            array_merge([sanitize_text_field($status)], $ids)
        );
        return $wpdb->query($query);
    }
    
    /**
     * Миграция существующих записей: заполнение form_type для старых submissions
     * 
     * ВАЖНО: Этот метод вызывается автоматически при обновлении БД до версии 1.0.4
     * Он заполняет form_type для существующих записей, не ломая работу legacy форм
     */
    private function migrate_existing_form_types() {
        global $wpdb;
        
        // Получаем все записи без form_type
        $submissions = $wpdb->get_results(
            "SELECT id, form_id FROM {$this->table_name} WHERE form_type IS NULL"
        );
        
        if (empty($submissions)) {
            return;
        }
        
        foreach ($submissions as $submission) {
            $form_type = 'form'; // По умолчанию
            
            // LEGACY: Если form_id - строка (старые встроенные формы)
            // Сохраняем обратную совместимость - эти формы продолжают работать
            if (!is_numeric($submission->form_id)) {
                $form_id_str = strtolower($submission->form_id);
                $builtin_types = ['newsletter', 'testimonial', 'resume', 'callback'];
                if (in_array($form_id_str, $builtin_types)) {
                    // Для legacy форм тип = ID
                    $form_type = $form_id_str;
                }
            } 
            // НОВОЕ: Если form_id - число (CPT форма)
            else {
                if (class_exists('CodeweberFormsCore')) {
                    $form_type = CodeweberFormsCore::get_form_type((int) $submission->form_id);
                } else {
                    // Fallback: проверяем метаполе
                    $meta_type = get_post_meta((int) $submission->form_id, '_form_type', true);
                    if (!empty($meta_type)) {
                        $form_type = $meta_type;
                    }
                }
            }
            
            // Обновляем запись (не ломая существующие данные)
            $wpdb->update(
                $this->table_name,
                ['form_type' => $form_type],
                ['id' => $submission->id],
                ['%s'],
                ['%d']
            );
        }
    }
}


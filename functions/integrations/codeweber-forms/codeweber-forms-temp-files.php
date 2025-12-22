<?php
/**
 * CodeWeber Forms Temp Files Class
 * 
 * Handles temporary file uploads and cleanup
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

class CodeweberFormsTempFiles {
    private $table_name;
    private $version = '1.0.0';
    private $temp_dir;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'codeweber_forms_temp_files';
        $this->temp_dir = wp_upload_dir()['basedir'] . '/codeweber-forms/temp';
        $this->create_table();
        $this->ensure_temp_directory();
    }
    
    /**
     * Create temp files table
     */
    private function create_table() {
        global $wpdb;
        
        $current_version = get_option('codeweber_forms_temp_files_db_version', '0');
        
        if ($current_version !== $this->version) {
            $charset_collate = $wpdb->get_charset_collate();
            
            $sql = "CREATE TABLE {$this->table_name} (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                file_id VARCHAR(255) NOT NULL,
                file_path VARCHAR(500) NOT NULL,
                file_name VARCHAR(255) NOT NULL,
                file_size BIGINT(20) UNSIGNED DEFAULT 0,
                file_type VARCHAR(100) DEFAULT '',
                uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                expires_at DATETIME NOT NULL,
                submission_id BIGINT(20) UNSIGNED DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY file_id (file_id),
                KEY expires_at (expires_at),
                KEY submission_id (submission_id)
            ) $charset_collate;";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            
            update_option('codeweber_forms_temp_files_db_version', $this->version);
        }
    }
    
    /**
     * Ensure temp directory exists
     */
    private function ensure_temp_directory() {
        if (!file_exists($this->temp_dir)) {
            wp_mkdir_p($this->temp_dir);
            // Add .htaccess for security
            $htaccess_content = "Order deny,allow\nDeny from all\n";
            file_put_contents($this->temp_dir . '/.htaccess', $htaccess_content);
        }
    }
    
    /**
     * Generate unique file ID
     */
    private function generate_file_id() {
        return wp_generate_uuid4();
    }
    
    /**
     * Save temp file record
     */
    public function save_temp_file($file_path, $file_name, $file_size, $file_type = '') {
        global $wpdb;
        
        error_log('save_temp_file: Starting. file_path: ' . $file_path . ', file_name: ' . $file_name . ', file_size: ' . $file_size . ', file_type: ' . $file_type);
        error_log('save_temp_file: Table name: ' . $this->table_name);
        error_log('save_temp_file: File exists: ' . (file_exists($file_path) ? 'YES' : 'NO'));
        
        $file_id = $this->generate_file_id();
        $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        error_log('save_temp_file: Generated file_id: ' . $file_id . ', expires_at: ' . $expires_at);
        
        $result = $wpdb->insert(
            $this->table_name,
            [
                'file_id' => $file_id,
                'file_path' => $file_path,
                'file_name' => $file_name,
                'file_size' => $file_size,
                'file_type' => $file_type,
                'expires_at' => $expires_at
            ],
            ['%s', '%s', '%s', '%d', '%s', '%s']
        );
        
        error_log('save_temp_file: wpdb->insert result: ' . ($result ? 'SUCCESS (ID: ' . $wpdb->insert_id . ')' : 'FAILED'));
        if (!$result) {
            error_log('save_temp_file: wpdb error: ' . $wpdb->last_error);
        }
        
        if ($result) {
            return [
                'file_id' => $file_id,
                'file_path' => $file_path,
                'file_name' => $file_name,
                'file_size' => $file_size,
                'file_type' => $file_type,
                'expires_at' => $expires_at
            ];
        }
        
        return false;
    }
    
    /**
     * Get temp file by ID
     */
    public function get_temp_file($file_id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE file_id = %s",
            $file_id
        ));
    }
    
    /**
     * Link temp file to submission
     */
    public function link_to_submission($file_id, $submission_id) {
        global $wpdb;
        return $wpdb->update(
            $this->table_name,
            ['submission_id' => intval($submission_id)],
            ['file_id' => $file_id],
            ['%d'],
            ['%s']
        );
    }
    
    /**
     * Delete temp file
     */
    public function delete_temp_file($file_id) {
        global $wpdb;
        
        $file = $this->get_temp_file($file_id);
        if (!$file) {
            return false;
        }
        
        // Delete physical file
        if (file_exists($file->file_path)) {
            @unlink($file->file_path);
        }
        
        // Delete database record
        return $wpdb->delete(
            $this->table_name,
            ['file_id' => $file_id],
            ['%s']
        );
    }

    /**
     * Delete all temp files linked to a submission
     */
    public function delete_by_submission($submission_id) {
        global $wpdb;
        $files = $wpdb->get_results($wpdb->prepare(
            "SELECT file_id FROM {$this->table_name} WHERE submission_id = %d",
            intval($submission_id)
        ));
        if (empty($files)) {
            return 0;
        }
        $deleted = 0;
        foreach ($files as $f) {
            if ($this->delete_temp_file($f->file_id)) {
                $deleted++;
            }
        }
        return $deleted;
    }
    
    /**
     * Move temp file to permanent location
     * Uses WordPress standard functions for file handling
     */
    public function move_to_permanent($file_id, $submission_id) {
        global $wpdb;
        
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        
        $file = $this->get_temp_file($file_id);
        if (!$file || !file_exists($file->file_path)) {
            return false;
        }
        
        // Create permanent directory structure: wp-content/uploads/codeweber-forms/YYYY/MM/
        $upload_dir = wp_upload_dir();
        $permanent_dir = $upload_dir['basedir'] . '/codeweber-forms/' . date('Y/m');
        wp_mkdir_p($permanent_dir);
        
        // Generate unique filename
        $file_ext = pathinfo($file->file_name, PATHINFO_EXTENSION);
        $file_base = sanitize_file_name(pathinfo($file->file_name, PATHINFO_FILENAME));
        $unique_filename = $file_base . '_' . time() . '_' . wp_generate_password(8, false) . '.' . $file_ext;
        $permanent_path = $permanent_dir . '/' . $unique_filename;
        
        // Use WordPress Filesystem API if available, otherwise use rename
        if (function_exists('WP_Filesystem') && WP_Filesystem()) {
            global $wp_filesystem;
            if ($wp_filesystem && $wp_filesystem->move($file->file_path, $permanent_path)) {
                // Success
            } else {
                // Fallback to rename
                if (!@rename($file->file_path, $permanent_path)) {
                    return false;
                }
            }
        } else {
            // Use standard rename (works in most cases)
            if (!@rename($file->file_path, $permanent_path)) {
                return false;
            }
        }
        
        // Set correct file permissions (WordPress standard)
        $stat = stat(dirname($permanent_path));
        if ($stat) {
            $perms = $stat['mode'] & 0000666;
            @chmod($permanent_path, $perms);
        }
        
        // Update database record
        $wpdb->update(
            $this->table_name,
            [
                'file_path' => $permanent_path,
                'submission_id' => intval($submission_id)
            ],
            ['file_id' => $file_id],
            ['%s', '%d'],
            ['%s']
        );
        
        return [
            'file_id' => $file_id,
            'file_path' => $permanent_path,
            'file_url' => $upload_dir['baseurl'] . '/codeweber-forms/' . date('Y/m') . '/' . $unique_filename,
            'file_name' => $file->file_name,
            'file_size' => $file->file_size,
            'file_type' => $file->file_type
        ];
    }
    
    /**
     * Cleanup expired temp files
     */
    public function cleanup_expired_files($limit = 100) {
        global $wpdb;
        
        // Get expired files that are not linked to any submission
        $expired_files = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} 
             WHERE expires_at < NOW() 
             AND submission_id IS NULL 
             LIMIT %d",
            $limit
        ));
        
        $deleted_count = 0;
        
        foreach ($expired_files as $file) {
            if ($this->delete_temp_file($file->file_id)) {
                $deleted_count++;
            }
        }
        
        return $deleted_count;
    }
    
    /**
     * Get temp directory path
     */
    public function get_temp_dir() {
        return $this->temp_dir;
    }
    
    /**
     * Get temp directory URL
     */
    public function get_temp_dir_url() {
        $upload_dir = wp_upload_dir();
        return $upload_dir['baseurl'] . '/codeweber-forms/temp';
    }
}







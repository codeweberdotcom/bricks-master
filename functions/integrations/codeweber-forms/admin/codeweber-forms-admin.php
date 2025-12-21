<?php
/**
 * CodeWeber Forms Admin
 * 
 * Admin panel for form submissions
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

// Load List Table class
require_once dirname(__FILE__) . '/class-codeweber-forms-list-table.php';

class CodeweberFormsAdmin {
    private $db;
    
    public function __construct() {
        $this->db = new CodeweberFormsDatabase();
        add_action('admin_menu', [$this, 'add_admin_menu']);
        // Screen options: количество элементов на странице и колонки
        add_filter('set-screen-option', [$this, 'set_screen_option'], 10, 3);
        // Обработка массовых действий до вывода (чтобы не было ошибок заголовков)
        add_action('admin_init', [$this, 'process_bulk_actions_early']);
        // Обработка удаления файлов до вывода
        add_action('admin_init', [$this, 'handle_file_deletion']);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Делаем раздел отправок форм дочерним пунктом меню CPT "Form"
        // Родительский slug для CPT: Form (`codeweber_form`)
        $parent_slug = 'edit.php?post_type=codeweber_form';

        // Основная страница "Отправки форм" как подпункт CPT "Form"
        $hook = add_submenu_page(
            $parent_slug,
            __('Form Submissions', 'codeweber'),
            __('Form Submissions', 'codeweber'),
            'manage_options',
            'codeweber',
            [$this, 'render_list_page']
        );

        // Регистрируем экранные опции для страницы списка отправок
        if ($hook) {
            add_action("load-{$hook}", [$this, 'add_screen_options']);
        }
        
        // Остальные дочерние пункты (настройки и шаблоны писем) также переносим под CPT "Form"
        add_submenu_page(
            $parent_slug,
            __('Settings', 'codeweber'),
            __('Settings', 'codeweber'),
            'manage_options',
            'codeweber-forms-settings',
            [$this, 'render_settings_page']
        );
        
        add_submenu_page(
            $parent_slug,
            __('Email Templates', 'codeweber'),
            __('Email Templates', 'codeweber'),
            'manage_options',
            'codeweber-forms-email-templates',
            [$this, 'render_email_templates_page']
        );
    }

    /**
     * Register screen options for submissions list
     */
    public function add_screen_options() {
        $screen = get_current_screen();
        if (!$screen) {
            return;
        }

        // Количество элементов на странице
        add_screen_option('per_page', array(
            'label'   => __('Number of submissions per page', 'codeweber'),
            'default' => 20,
            'option'  => 'codeweber_forms_per_page',
        ));

        // Регистрация колонок для "Настройки экрана → Столбцы"
        // Делаем, как в newsletter-subscriptions: создаём инстанс таблицы
        // и вешаем его get_columns на manage_{$screen->id}_columns.
        $list_table = new Codeweber_Forms_List_Table($this);
        add_filter("manage_{$screen->id}_columns", array($list_table, 'get_columns'));
    }

    /**
     * Handle saving of screen options
     */
    public function set_screen_option($status, $option, $value) {
        if ($option === 'codeweber_forms_per_page') {
            return (int) $value;
        }

        return $status;
    }
    
    /**
     * Process bulk actions early (до любого вывода), как в newsletter-subscriptions
     */
    public function process_bulk_actions_early() {
        // Проверяем, есть ли выбранные элементы (bulk actions)
        // Проверяем как по page параметру, так и по наличию submission массива
        $is_codeweber_page = (isset($_GET['page']) && $_GET['page'] === 'codeweber') || 
                             (isset($_POST['page']) && $_POST['page'] === 'codeweber') ||
                             (isset($_REQUEST['page']) && $_REQUEST['page'] === 'codeweber');
        
        // Если есть выбранные элементы для bulk actions, обрабатываем независимо от URL
        if (isset($_REQUEST['submission']) && is_array($_REQUEST['submission']) && !empty($_REQUEST['submission'])) {
            $list_table = new Codeweber_Forms_List_Table($this);
            $list_table->process_bulk_action();
            // process_bulk_action() делает wp_redirect и exit при успехе
        }
        
        // Также проверяем по page параметру для других действий
        if (!$is_codeweber_page) {
            return;
        }
    }
    
    /**
     * Handle file deletion (до любого вывода)
     */
    public function handle_file_deletion() {
        // Только на нашей странице просмотра
        if (!isset($_GET['page']) || $_GET['page'] !== 'codeweber' || !isset($_POST['action']) || $_POST['action'] !== 'delete_file') {
            return;
        }
        
        if (!check_admin_referer('codeweber_forms_delete_file', 'codeweber_forms_delete_file_nonce')) {
            wp_die(__('Security check failed', 'codeweber'));
        }
        
        $submission_id = isset($_POST['submission_id']) ? intval($_POST['submission_id']) : 0;
        $file_index = isset($_POST['file_index']) ? $_POST['file_index'] : '';
        
        if (!$submission_id || $file_index === '') {
            wp_safe_redirect(add_query_arg(['page' => 'codeweber', 'action' => 'view', 'id' => $submission_id, 'error' => 'invalid_params'], admin_url('admin.php')));
            exit;
        }
        
        $submission = $this->db->get_submission($submission_id);
        if (!$submission || empty($submission->files_data)) {
            wp_safe_redirect(add_query_arg(['page' => 'codeweber', 'action' => 'view', 'id' => $submission_id, 'error' => 'no_files'], admin_url('admin.php')));
            exit;
        }
        
        $files_data = json_decode($submission->files_data, true);
        if (!is_array($files_data) || empty($files_data)) {
            wp_safe_redirect(add_query_arg(['page' => 'codeweber', 'action' => 'view', 'id' => $submission_id, 'error' => 'invalid_format'], admin_url('admin.php')));
            exit;
        }
        
        // Преобразуем файлы в плоский массив, сохраняя оригинальные ключи
        $files_flat = [];
        $original_keys = [];
        
        foreach ($files_data as $key => $value) {
            if (is_array($value) && isset($value[0]) && is_array($value[0])) {
                // Вложенный массив
                foreach ($value as $inner_key => $file) {
                    $files_flat[] = $file;
                    $original_keys[] = ['parent' => $key, 'child' => $inner_key];
                }
            } else {
                // Плоский массив или один файл
                $files_flat[] = $value;
                $original_keys[] = ['parent' => $key, 'child' => null];
            }
        }
        
        $numeric_index = intval($file_index);
        
        if ($numeric_index < 0 || $numeric_index >= count($files_flat)) {
            wp_safe_redirect(add_query_arg(['page' => 'codeweber', 'action' => 'view', 'id' => $submission_id, 'error' => 'file_not_found'], admin_url('admin.php')));
            exit;
        }
        
        $target_file = $files_flat[$numeric_index];
        $target_key_info = $original_keys[$numeric_index];
        
        // Определяем путь к файлу
        $file_path = $target_file['file_path'] ?? $target_file['temp_path'] ?? '';
        
        if (empty($file_path) && !empty($target_file['file_url'])) {
            $upload_dir = wp_upload_dir();
            $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $target_file['file_url']);
            // Нормализуем путь для Windows/Unix совместимости
            $file_path = wp_normalize_path($file_path);
        }
        
        // Физически удаляем файл с сервера (если существует)
        $file_deleted_physically = false;
        if (!empty($file_path)) {
            $file_path_normalized = wp_normalize_path($file_path);
            if (file_exists($file_path_normalized)) {
                $file_deleted_physically = @unlink($file_path_normalized);
            } else {
                // Файл уже не существует - это нормально, продолжаем удаление из БД
                // Это может быть битая ссылка, которую нужно очистить
                $file_deleted_physically = true;
            }
        }
        
        // ВСЕГДА удаляем файл из базы данных, даже если физически файл уже был удален
        // Удаляем файл из оригинального массива
        if ($target_key_info['child'] !== null) {
            unset($files_data[$target_key_info['parent']][$target_key_info['child']]);
            if (empty($files_data[$target_key_info['parent']])) {
                unset($files_data[$target_key_info['parent']]);
            } else {
                $files_data[$target_key_info['parent']] = array_values($files_data[$target_key_info['parent']]);
            }
        } else {
            unset($files_data[$target_key_info['parent']]);
            $keys = array_keys($files_data);
            $is_numeric_array = true;
            foreach ($keys as $k) {
                if (!is_numeric($k)) {
                    $is_numeric_array = false;
                    break;
                }
            }
            if ($is_numeric_array) {
                $files_data = array_values($files_data);
            }
        }
        
        // Обновляем данные в базе (ВСЕГДА, даже если остается 0 файлов)
        $files_data_json = !empty($files_data) ? json_encode($files_data, JSON_UNESCAPED_UNICODE) : null;
        
        // ВСЕГДА обновляем базу данных принудительно через прямой SQL запрос
        // Это гарантирует обновление даже когда осталось 0 файлов (null)
        global $wpdb;
        
        if ($files_data_json === null) {
            // Устанавливаем NULL для пустого массива файлов (последний файл удален)
            $updated = $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->prefix}codeweber_forms_submissions 
                 SET files_data = NULL 
                 WHERE id = %d",
                $submission_id
            ));
        } else {
            // Обновляем с JSON данными
            $updated = $wpdb->update(
                $wpdb->prefix . 'codeweber_forms_submissions',
                ['files_data' => $files_data_json],
                ['id' => $submission_id],
                ['%s'],
                ['%d']
            );
        }
        
        // $wpdb->update возвращает false при ошибке или число строк (может быть 0, если данные не изменились)
        // $wpdb->query возвращает количество затронутых строк или false при ошибке
        if ($updated !== false) {
            $message = $file_deleted_physically ? 'deleted' : 'removed_from_db';
            wp_safe_redirect(add_query_arg(['page' => 'codeweber', 'action' => 'view', 'id' => $submission_id, 'deleted' => $message], admin_url('admin.php')));
        } else {
            wp_safe_redirect(add_query_arg(['page' => 'codeweber', 'action' => 'view', 'id' => $submission_id, 'error' => 'delete_failed'], admin_url('admin.php')));
        }
        exit;
    }
    
    /**
     * Render submissions list page
     */
    public function render_list_page() {
        // Handle actions first (including file deletion on view page)
        $this->handle_actions();
        
        // Проверяем, нужно ли показать детальный просмотр отправки
        if (isset($_GET['action']) && $_GET['action'] === 'view' && isset($_GET['id'])) {
            $this->render_view_page(intval($_GET['id']));
            return;
        }
        
        // Create instance of list table
        $list_table = new Codeweber_Forms_List_Table($this);

        // Prepare items
        $list_table->prepare_items();
        
        ?>
        <div class="wrap">
            <h1><?php _e('Form Submissions', 'codeweber'); ?></h1>

            <?php settings_errors('codeweber_forms_messages'); ?>
            
            <?php
            // Показываем сообщение об успешном массовом действии
            if (isset($_GET['bulk_updated']) && $_GET['bulk_updated'] > 0) {
                $count = intval($_GET['bulk_updated']);
                $action = isset($_GET['bulk_action']) ? sanitize_text_field($_GET['bulk_action']) : '';
                
                // Получаем метку действия
                $action_labels = array(
                    'mark_read' => __('marked as read', 'codeweber'),
                    'mark_unread' => __('marked as unread', 'codeweber'),
                    'archive' => __('archived', 'codeweber'),
                    'unarchive' => __('unarchived', 'codeweber'),
                    'delete' => __('moved to trash', 'codeweber'),
                );
                
                $action_label = isset($action_labels[$action]) ? $action_labels[$action] : __('updated', 'codeweber');
                
                $message = sprintf(
                    _n('%d submission %s', '%d submissions %s', $count, 'codeweber'),
                    $count,
                    $action_label
                );
                
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
            }
            ?>

            <?php
            // Вкладки-фильтры (Все, Новые, Прочитанные, Архив, Корзина)
            $list_table->views();
            ?>

            <?php
            // Показать кнопку "Empty Trash" только во вкладке Корзина
            $current_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
            if ($current_status === 'trash') : ?>
                <form method="post" style="margin: 10px 0;">
                    <?php wp_nonce_field('codeweber_forms_action', 'codeweber_forms_nonce'); ?>
                    <input type="hidden" name="action" value="empty_trash">
                    <button type="submit" class="button button-secondary" onclick="return confirm('<?php echo esc_js(__('Empty trash? This will permanently delete items.', 'codeweber')); ?>');">
                        <?php _e('Empty Trash', 'codeweber'); ?>
                    </button>
                </form>
            <?php endif; ?>

            <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
                <input type="hidden" name="page" value="codeweber">
                <?php
                // Preserve status filter
                if (isset($_GET['status'])) {
                    echo '<input type="hidden" name="status" value="' . esc_attr($_GET['status']) . '">';
                }
                
                $list_table->search_box(__('Search', 'codeweber'), 'submission');
                $list_table->display();
                ?>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Handle "View all" link click
            $(document).on('click', '.view-full', function(e) {
                e.preventDefault();
                var $link = $(this);
                var id = $link.data('id');
                var $preview = $('#submission-preview-' + id);
                var $full = $('#submission-' + id);
                
                if ($full.is(':visible')) {
                    // Скрываем полные данные, показываем превью
                    $full.hide();
                    $preview.show();
                    $link.text('<?php echo esc_js(__('Show all', 'codeweber')); ?>');
                } else {
                    // Показываем полные данные, скрываем превью
                    $preview.hide();
                    $full.show();
                    $link.text('<?php echo esc_js(__('Hide', 'codeweber')); ?>');
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * Handle admin actions
     */
    private function handle_actions() {
        // 1) GET single-row actions (View/Delete)
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            $submission_id = intval($_GET['id']);
            $nonce = isset($_GET['_wpnonce']) ? $_GET['_wpnonce'] : '';
            if (!$submission_id || !wp_verify_nonce($nonce, 'delete_submission_' . $submission_id)) {
                return;
            }

            // Soft delete -> move to trash
            $deleted = $this->db->delete_submission($submission_id);
            if ($deleted !== false) {
                add_settings_error(
                    'codeweber_forms_messages',
                    'codeweber_forms_message',
                    __('Submission moved to trash', 'codeweber'),
                    'success'
                );
            } else {
                add_settings_error(
                    'codeweber_forms_messages',
                    'codeweber_forms_message',
                    __('Failed to move submission to trash', 'codeweber'),
                    'error'
                );
            }
            return;
        }

        // 2) POST actions (trash empty, delete file, etc.)
        if (!isset($_POST['action']) || !check_admin_referer('codeweber_forms_action', 'codeweber_forms_nonce')) {
            return;
        }
        
        $action = $_POST['action'];
        $submission_id = isset($_POST['submission_id']) ? intval($_POST['submission_id']) : 0;
        
        switch ($action) {
            case 'delete':
                // soft delete -> trash
                if ($this->db->delete_submission($submission_id)) {
                    add_settings_error(
                        'codeweber_forms_messages',
                        'codeweber_forms_message',
                        __('Submission moved to trash', 'codeweber'),
                        'success'
                    );
                } else {
                    add_settings_error(
                        'codeweber_forms_messages',
                        'codeweber_forms_message',
                        __('Failed to move submission to trash', 'codeweber'),
                        'error'
                    );
                }
                break;

            case 'empty_trash':
                $result = $this->db->empty_trash();
                if ($result !== false) {
                    add_settings_error(
                        'codeweber_forms_messages',
                        'codeweber_forms_message',
                        __('Trash has been emptied', 'codeweber'),
                        'success'
                    );
                }
                break;
        }
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        $settings = new CodeweberFormsSettings();
        $settings->render_settings_page();
    }
    
    /**
     * Render email templates page
     */
    public function render_email_templates_page() {
        $email_templates = new CodeweberFormsEmailTemplates();
        $email_templates->render_email_templates_page();
    }
    
    /**
     * Render submission view page
     */
    private function render_view_page($submission_id) {
        // Показываем сообщения об успехе/ошибке
        if (isset($_GET['deleted'])) {
            $deleted_value = sanitize_text_field($_GET['deleted']);
            if ($deleted_value === '1' || $deleted_value === 'deleted') {
                add_settings_error(
                    'codeweber_forms_messages',
                    'codeweber_forms_message',
                    __('File deleted successfully', 'codeweber'),
                    'success'
                );
            } elseif ($deleted_value === 'removed_from_db') {
                add_settings_error(
                    'codeweber_forms_messages',
                    'codeweber_forms_message',
                    __('File reference removed from database (file was already deleted)', 'codeweber'),
                    'success'
                );
            }
        } elseif (isset($_GET['error'])) {
            $error_messages = [
                'invalid_params' => __('Invalid request parameters', 'codeweber'),
                'no_files' => __('No files found in submission', 'codeweber'),
                'invalid_format' => __('Invalid files data format', 'codeweber'),
                'file_not_found' => __('File not found', 'codeweber'),
                'delete_failed' => __('Failed to delete file', 'codeweber'),
            ];
            $error_key = sanitize_text_field($_GET['error']);
            $error_message = isset($error_messages[$error_key]) ? $error_messages[$error_key] : __('An error occurred', 'codeweber');
            add_settings_error(
                'codeweber_forms_messages',
                'codeweber_forms_message',
                $error_message,
                'error'
            );
        }
        
        $submission = $this->db->get_submission($submission_id);
        
        if (!$submission) {
            wp_die(__('Submission not found.', 'codeweber'));
        }
        
        $data = json_decode($submission->submission_data, true);
        $files_data = !empty($submission->files_data) ? json_decode($submission->files_data, true) : null;
        
        ?>
        <div class="wrap">
            <h1><?php _e('View Submission', 'codeweber'); ?></h1>
            
            <p>
                <a href="<?php echo admin_url('admin.php?page=codeweber'); ?>" class="button">
                    <?php _e('← Back to Submissions', 'codeweber'); ?>
                </a>
            </p>
            
            <div class="codeweber-forms-submission-detail">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 200px;"><?php _e('Field', 'codeweber'); ?></th>
                            <th><?php _e('Value', 'codeweber'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong><?php _e('ID', 'codeweber'); ?></strong></td>
                            <td><?php echo esc_html($submission->id); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('Form', 'codeweber'); ?></strong></td>
                            <td>
                                <strong><?php echo esc_html($submission->form_name ?: ($submission->form_id == 0 ? __('Testimonial Form', 'codeweber') : $submission->form_id)); ?></strong>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('Status', 'codeweber'); ?></strong></td>
                            <td>
                                <span class="status-badge status-<?php echo esc_attr($submission->status); ?>">
                                    <?php echo esc_html(ucfirst($submission->status)); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('Email Admin', 'codeweber'); ?></strong></td>
                            <td>
                                <?php if ($submission->email_sent): ?>
                                    <span class="dashicons dashicons-yes-alt" style="color: green;"></span>
                                    <?php _e('Sent', 'codeweber'); ?>
                                <?php else: ?>
                                    <span class="dashicons dashicons-dismiss" style="color: red;"></span>
                                    <?php _e('Not sent', 'codeweber'); ?>
                                    <?php if ($submission->email_error): ?>
                                        <br><small style="color: red;"><?php echo esc_html($submission->email_error); ?></small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('Email User', 'codeweber'); ?></strong></td>
                            <td>
                                <?php if ($submission->auto_reply_sent): ?>
                                    <span class="dashicons dashicons-yes-alt" style="color: green;"></span>
                                    <?php _e('Sent', 'codeweber'); ?>
                                <?php else: ?>
                                    <span class="dashicons dashicons-dismiss" style="color: red;"></span>
                                    <?php _e('Not sent', 'codeweber'); ?>
                                    <?php if ($submission->auto_reply_error): ?>
                                        <br><small style="color: red;"><?php echo esc_html($submission->auto_reply_error); ?></small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('Date', 'codeweber'); ?></strong></td>
                            <td><?php echo date_i18n(get_option('date_format') . ' H:i', strtotime($submission->created_at)); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('IP Address', 'codeweber'); ?></strong></td>
                            <td><?php echo esc_html($submission->ip_address); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('User Agent', 'codeweber'); ?></strong></td>
                            <td><?php echo esc_html($submission->user_agent); ?></td>
                        </tr>
                        <?php if ($submission->user_id > 0): ?>
                        <tr>
                            <td><strong><?php _e('User', 'codeweber'); ?></strong></td>
                            <td>
                                <?php 
                                $user = get_userdata($submission->user_id);
                                if ($user) {
                                    echo esc_html($user->display_name) . ' (' . esc_html($user->user_email) . ')';
                                } else {
                                    echo esc_html($submission->user_id);
                                }
                                ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <?php if ($files_data && is_array($files_data) && !empty($files_data)): ?>
                    <h2><?php _e('Attached Files', 'codeweber'); ?></h2>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
                        <?php 
                        $image_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
                        foreach ($files_data as $file_index => $file): 
                            $file_url = $file['file_url'] ?? '';
                            $file_path = $file['file_path'] ?? '';
                            $file_name = $file['file_name'] ?? $file['name'] ?? __('Unknown', 'codeweber');
                            $file_type = $file['file_type'] ?? $file['type'] ?? '';
                            
                            // Если есть URL, используем его
                            if ($file_url) {
                                $download_url = $file_url;
                                // Если file_path не указан, пытаемся получить его из URL
                                if (empty($file_path)) {
                                    $upload_dir = wp_upload_dir();
                                    $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $file_url);
                                }
                            } elseif ($file_path) {
                                // Конвертируем путь в URL
                                $upload_dir = wp_upload_dir();
                                $download_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $file_path);
                            } else {
                                $download_url = '';
                            }
                            
                            // Проверяем, существует ли файл физически
                            $file_exists = false;
                            if (!empty($file_path)) {
                                // Нормализуем путь (убираем лишние слеши и обрабатываем относительные пути)
                                $file_path_normalized = wp_normalize_path($file_path);
                                if (file_exists($file_path_normalized)) {
                                    $file_exists = true;
                                }
                            }
                            // Если файл не найден по пути, но есть URL - это нормально (файл может быть удален)
                            // В этом случае $file_exists останется false, и мы покажем предупреждение
                            
                            $is_image = in_array(strtolower($file_type), $image_types);
                        ?>
                            <div style="border: 1px solid #ddd; padding: 10px; border-radius: 4px; background: #fff;">
                                <?php if ($is_image && $download_url && $file_exists): ?>
                                    <div style="margin-bottom: 10px; position: relative;">
                                        <img src="<?php echo esc_url($download_url); ?>" 
                                             alt="<?php echo esc_attr($file_name); ?>"
                                             style="width: 100%; height: 150px; object-fit: cover; border-radius: 4px;"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div style="display: none; width: 100%; height: 150px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; align-items: center; justify-content: center; flex-direction: column; position: absolute; top: 0; left: 0;">
                                            <span class="dashicons dashicons-warning" style="font-size: 32px; color: #856404; margin-bottom: 5px;"></span>
                                            <span style="font-size: 11px; color: #856404; text-align: center; padding: 0 5px;"><?php _e('File not found', 'codeweber'); ?></span>
                                        </div>
                                    </div>
                                <?php elseif ($is_image && $download_url && !$file_exists): ?>
                                    <div style="width: 100%; height: 150px; background: #fff3cd; border: 1px solid #ffc107; display: flex; align-items: center; justify-content: center; border-radius: 4px; margin-bottom: 10px; flex-direction: column;">
                                        <span class="dashicons dashicons-warning" style="font-size: 32px; color: #856404; margin-bottom: 5px;"></span>
                                        <span style="font-size: 11px; color: #856404; text-align: center; padding: 0 5px;"><?php _e('File not found', 'codeweber'); ?></span>
                                    </div>
                                <?php else: ?>
                                    <div style="width: 100%; height: 150px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 4px; margin-bottom: 10px;">
                                        <span class="dashicons dashicons-media-document" style="font-size: 48px; color: #666;"></span>
                                    </div>
                                <?php endif; ?>
                                <div style="font-size: 12px;">
                                    <strong><?php echo esc_html($file_name); ?></strong><br>
                                    <span style="color: #666;">
                                        <?php 
                                        $file_size = $file['file_size'] ?? $file['size'] ?? 0;
                                        echo size_format($file_size, 2);
                                        ?>
                                    </span>
                                </div>
                                <?php if ($download_url): ?>
                                    <div style="margin-top: 10px; display: flex; gap: 5px;">
                                        <a href="<?php echo esc_url($download_url); ?>" target="_blank" class="button button-small" style="flex: 1; text-align: center;">
                                            <span class="dashicons dashicons-download" style="vertical-align: middle;"></span>
                                            <?php _e('Download', 'codeweber'); ?>
                                        </a>
                                        <form method="post" style="flex: 1; margin: 0;">
                                            <?php wp_nonce_field('codeweber_forms_delete_file', 'codeweber_forms_delete_file_nonce'); ?>
                                            <input type="hidden" name="action" value="delete_file">
                                            <input type="hidden" name="submission_id" value="<?php echo esc_attr($submission_id); ?>">
                                            <input type="hidden" name="file_index" value="<?php echo esc_attr($file_index); ?>">
                                            <button type="submit" class="button button-small button-link-delete" style="width: 100%; text-align: center; color: #b32d2e;" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete this file?', 'codeweber')); ?>');">
                                                <span class="dashicons dashicons-trash" style="vertical-align: middle;"></span>
                                                <?php _e('Delete', 'codeweber'); ?>
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <h2><?php _e('Submission Data', 'codeweber'); ?></h2>
                <?php if ($data): ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th style="width: 30%;"><?php _e('Field', 'codeweber'); ?></th>
                                <th><?php _e('Value', 'codeweber'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Первая строка: имя формы (используем логическое имя, если оно сохранено)
                            $form_label = '';
                            if (!empty($submission->form_name)) {
                                $form_label = $submission->form_name;
                            } else {
                                // Пытаемся вычислить человекочитаемое имя по form_id
                                if (!empty($submission->form_id)) {
                                    $form_id = $submission->form_id;
                                    if (is_numeric($form_id)) {
                                        $post = get_post((int) $form_id);
                                        if ($post && $post->post_type === 'codeweber_form') {
                                            $form_label = $post->post_title;
                                        }
                                    } elseif (is_string($form_id)) {
                                        $builtin_labels = array(
                                            'newsletter'  => __('Newsletter Subscription', 'codeweber'),
                                            'testimonial' => __('Testimonial Form', 'codeweber'),
                                            'resume'      => __('Resume Form', 'codeweber'),
                                            'callback'    => __('Callback Request', 'codeweber'),
                                        );
                                        if (isset($builtin_labels[$form_id])) {
                                            $form_label = $builtin_labels[$form_id];
                                        } else {
                                            $form_label = $form_id;
                                        }
                                    }
                                }
                            }
                            if (!empty($form_label)): ?>
                                <tr>
                                    <td><strong><?php _e('Form', 'codeweber'); ?></strong></td>
                                    <td><?php echo esc_html($form_label); ?></td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($data as $key => $value): ?>
                                <?php if ($key === '_utm_data') continue; // Пропускаем UTM данные, они обрабатываются отдельно ?>
                                <?php 
                                // Пропускаем отдельные поля form_consents_{id} (например form_consents_4981, form_consents_4976)
                                // Эти поля дублируют информацию из массива form_consents/newsletter_consents
                                if (preg_match('/^form_consents_\d+$/', $key)) {
                                    continue;
                                }
                                ?>
                                <tr>
                                    <td>
                                        <strong>
                                            <?php
                                            // Убираем квадратные скобки из ключа
                                            $clean_key = str_replace(['[', ']'], '', $key);
                                            // Переводим некоторые служебные ключи в человекочитаемые и переводимые названия
                                            // Нормализуем ключ для сравнения (приводим к нижнему регистру и заменяем пробелы/дефисы на подчеркивания)
                                            $normalized_key = strtolower(str_replace([' ', '-'], '_', trim($clean_key)));
                                            
                                            if ($normalized_key === 'newsletter_consents' || $normalized_key === 'form_consents' || strtolower($clean_key) === 'form consents') {
                                                echo esc_html(__('Consents', 'codeweber'));
                                            } elseif ($normalized_key === 'form_name' || strtolower(trim($clean_key)) === 'form name') {
                                                echo esc_html(__('Form Name', 'codeweber'));
                                            } elseif ($normalized_key === 'file') {
                                                echo esc_html(__('File', 'codeweber'));
                                            } elseif ($normalized_key === 'name' && strpos($normalized_key, 'form') === false) {
                                                echo esc_html(__('Name', 'codeweber'));
                                            } elseif ($normalized_key === 'lastname' || strtolower($clean_key) === 'lastname') {
                                                echo esc_html(__('Lastname', 'codeweber'));
                                            } elseif ($normalized_key === 'patronymic' || strtolower($clean_key) === 'patronymic') {
                                                echo esc_html(__('Patronymic', 'codeweber'));
                                            } elseif ($normalized_key === 'phone' || strtolower($clean_key) === 'phone') {
                                                echo esc_html(__('Phone', 'codeweber'));
                                            } elseif ($clean_key === 'role' || $normalized_key === 'role') {
                                                echo esc_html(__('Role', 'codeweber'));
                                            } elseif ($clean_key === 'company' || $normalized_key === 'company') {
                                                echo esc_html(__('Company', 'codeweber'));
                                            } elseif ($clean_key === 'testimonial_text' || $clean_key === 'testimonial-text' || $normalized_key === 'testimonial_text') {
                                                echo esc_html(__('Testimonial Text', 'codeweber'));
                                            } elseif ($clean_key === 'rating' || $normalized_key === 'rating') {
                                                echo esc_html(__('Rating', 'codeweber'));
                                            } elseif ($clean_key === 'message' || $clean_key === 'Message' || $normalized_key === 'message') {
                                                echo esc_html(__('Message', 'codeweber'));
                                            } elseif ($clean_key === 'user_id' || $clean_key === 'User id' || $clean_key === 'User ID' || $clean_key === 'user id' || $normalized_key === 'user_id') {
                                                echo esc_html(__('User ID', 'codeweber'));
                                            } else {
                                                // Пытаемся перевести ключ через систему переводов
                                                $translated_key = __(ucfirst(str_replace(['_', '-'], ' ', $clean_key)), 'codeweber');
                                                // Если перевод вернул исходную строку (перевода нет), используем её как есть
                                                if ($translated_key === ucfirst(str_replace(['_', '-'], ' ', $clean_key))) {
                                                    echo esc_html(ucfirst(str_replace(['_', '-'], ' ', $clean_key)));
                                                } else {
                                                    echo esc_html($translated_key);
                                                }
                                            }
                                            ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <?php 
                                        // Специальная обработка newsletter_consents и form_consents, чтобы избежать "Array to string conversion"
                                        if (($key === 'newsletter_consents' || $key === 'form_consents') && is_array($value)) {
                                            $consent_lines = [];

                                            // Подключаем helper для получения корректного URL документа/ревизии
                                            if (!function_exists('codeweber_forms_get_document_url')) {
                                                require_once get_template_directory() . '/functions/integrations/codeweber-forms/codeweber-forms-consent-helper.php';
                                            }

                                            foreach ($value as $doc_id => $consent_data) {
                                                $doc = get_post($doc_id);
                                                if (!$doc) {
                                                    continue;
                                                }

                                                $doc_title = $doc->post_title;
                                                // Для CF7 форм consent_data может быть строкой "1" или массивом с document_version
                                                if (is_array($consent_data)) {
                                                    $version = $consent_data['document_version'] ?? null;
                                                } else {
                                                    $version = null;
                                                }
                                                $doc_url = codeweber_forms_get_document_url($doc_id, $version);

                                                $line = sprintf(
                                                    '%s (ID: %d)',
                                                    esc_html($doc_title),
                                                    (int) $doc_id
                                                );

                                                if (!empty($version)) {
                                                    $line .= sprintf(
                                                        ' - %s: %s',
                                                        __('Version', 'codeweber'),
                                                        esc_html($version)
                                                    );
                                                }

                                                if (!empty($doc_url)) {
                                                    $url = esc_url($doc_url);
                                                    $line .= ' - ' . __('URL', 'codeweber') . ': '
                                                        . '<a href="' . $url . '" target="_blank" rel="noopener noreferrer">' . $url . '</a>';
                                                }

                                                $consent_lines[] = $line;
                                            }

                                            if (!empty($consent_lines)) {
                                                echo wp_kses_post(implode('<br>', $consent_lines));
                                            } else {
                                                echo '—';
                                            }
                                        } elseif (is_array($value)) {
                                            // Общая обработка массивов (простые значения)
                                            $flat = [];
                                            foreach ($value as $k => $v) {
                                                if (is_array($v)) {
                                                    continue;
                                                }
                                                $flat[] = $v;
                                            }
                                            echo esc_html(implode(', ', $flat));
                                        } elseif (filter_var($value, FILTER_VALIDATE_URL)) {
                                            echo '<a href="' . esc_url($value) . '" target="_blank">' . esc_html($value) . '</a>';
                                        } else {
                                            echo esc_html($value);
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            
                            <?php if (isset($data['_utm_data']) && is_array($data['_utm_data'])): ?>
                                <tr>
                                    <td colspan="2">
                                        <h3><?php _e('UTM Parameters', 'codeweber'); ?></h3>
                                        <table class="wp-list-table widefat fixed">
                                            <thead>
                                                <tr>
                                                    <th><?php _e('Parameter', 'codeweber'); ?></th>
                                                    <th><?php _e('Value', 'codeweber'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($data['_utm_data'] as $utm_key => $utm_value): ?>
                                                    <tr>
                                                        <td><strong><?php echo esc_html(ucfirst(str_replace('_', ' ', $utm_key))); ?></strong></td>
                                                        <td>
                                                            <?php 
                                                            if (in_array($utm_key, ['referrer', 'landing_page']) && filter_var($utm_value, FILTER_VALIDATE_URL)) {
                                                                echo '<a href="' . esc_url($utm_value) . '" target="_blank">' . esc_html($utm_value) . '</a>';
                                                            } else {
                                                                echo esc_html($utm_value);
                                                            }
                                                            ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p><?php _e('No data available.', 'codeweber'); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}


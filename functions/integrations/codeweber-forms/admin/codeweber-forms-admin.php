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
            'label'   => __('Количество отправок на странице', 'codeweber'),
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
        // Только на нашей странице
        if (!isset($_GET['page']) || $_GET['page'] !== 'codeweber') {
            return;
        }

        // Проверяем, есть ли выбранные элементы
        if (isset($_REQUEST['submission']) && is_array($_REQUEST['submission'])) {
            $list_table = new Codeweber_Forms_List_Table($this);
            $list_table->process_bulk_action();
            // process_bulk_action() делает wp_redirect и exit при успехе
        }
    }
    
    /**
     * Render submissions list page
     */
    public function render_list_page() {
        // Проверяем, нужно ли показать детальный просмотр отправки
        if (isset($_GET['action']) && $_GET['action'] === 'view' && isset($_GET['id'])) {
            $this->render_view_page(intval($_GET['id']));
            return;
        }
        
        // Handle single delete action (from action column)
        $this->handle_actions();
        
        // Create instance of list table
        $list_table = new Codeweber_Forms_List_Table($this);

        // Prepare items
        $list_table->prepare_items();
        
        ?>
        <div class="wrap">
            <h1><?php _e('Form Submissions', 'codeweber'); ?></h1>

            <?php settings_errors('codeweber_forms_messages'); ?>

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

            <form method="get">
                <input type="hidden" name="page" value="codeweber">
                <?php
                // Preserve search query
                if (isset($_GET['s'])) {
                    echo '<input type="hidden" name="s" value="' . esc_attr($_GET['s']) . '">';
                }
                // Preserve status filter
                if (isset($_GET['status'])) {
                    echo '<input type="hidden" name="status" value="' . esc_attr($_GET['status']) . '">';
                }
                // Preserve form_id filter
                if (isset($_GET['form_id'])) {
                    echo '<input type="hidden" name="form_id" value="' . esc_attr($_GET['form_id']) . '">';
                }
                // НОВОЕ: Preserve form_type filter
                if (isset($_GET['form_type'])) {
                    echo '<input type="hidden" name="form_type" value="' . esc_attr($_GET['form_type']) . '">';
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
                    $link.text('<?php echo esc_js(__('Показать все', 'codeweber')); ?>');
                } else {
                    // Показываем полные данные, скрываем превью
                    $preview.hide();
                    $full.show();
                    $link.text('<?php echo esc_js(__('Скрыть', 'codeweber')); ?>');
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

        // 2) POST actions (trash empty, etc.)
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
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th style="width: 30%;"><?php _e('File Name', 'codeweber'); ?></th>
                                <th><?php _e('Size', 'codeweber'); ?></th>
                                <th><?php _e('Type', 'codeweber'); ?></th>
                                <th><?php _e('Actions', 'codeweber'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($files_data as $file): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html($file['file_name'] ?? $file['name'] ?? __('Unknown', 'codeweber')); ?></strong>
                                    </td>
                                    <td>
                                        <?php 
                                        $file_size = $file['file_size'] ?? $file['size'] ?? 0;
                                        echo size_format($file_size, 2);
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo esc_html($file['file_type'] ?? $file['type'] ?? '-'); ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $file_url = $file['file_url'] ?? '';
                                        $file_path = $file['file_path'] ?? '';
                                        
                                        // Если есть URL, используем его
                                        if ($file_url) {
                                            $download_url = $file_url;
                                        } elseif ($file_path) {
                                            // Конвертируем путь в URL
                                            $upload_dir = wp_upload_dir();
                                            $download_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $file_path);
                                        } else {
                                            $download_url = '';
                                        }
                                        
                                        if ($download_url && file_exists($file_path ?: str_replace(wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $file_url))):
                                        ?>
                                            <a href="<?php echo esc_url($download_url); ?>" target="_blank" class="button button-small">
                                                <span class="dashicons dashicons-download" style="vertical-align: middle;"></span>
                                                <?php _e('Download', 'codeweber'); ?>
                                            </a>
                                            <a href="<?php echo esc_url($download_url); ?>" target="_blank" class="button button-small">
                                                <span class="dashicons dashicons-external" style="vertical-align: middle;"></span>
                                                <?php _e('View', 'codeweber'); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="description"><?php _e('File not found', 'codeweber'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
                                <tr>
                                    <td>
                                        <strong>
                                            <?php
                                            // Переводим некоторые служебные ключи в человекочитаемые и переводимые названия
                                            // Нормализуем ключ для сравнения (приводим к нижнему регистру и заменяем пробелы/дефисы на подчеркивания)
                                            $normalized_key = strtolower(str_replace([' ', '-'], '_', trim($key)));
                                            
                                            if ($key === 'newsletter_consents' || $normalized_key === 'newsletter_consents') {
                                                echo esc_html(__('Newsletter Consents', 'codeweber'));
                                            } elseif ($key === 'form_name' || $normalized_key === 'form_name' || $key === 'form name' || $key === 'Form name') {
                                                echo esc_html(__('Название формы', 'codeweber'));
                                            } elseif ($key === 'name' || $normalized_key === 'name') {
                                                echo esc_html(__('Имя', 'codeweber'));
                                            } elseif ($key === 'role' || $normalized_key === 'role') {
                                                echo esc_html(__('Роль', 'codeweber'));
                                            } elseif ($key === 'company' || $normalized_key === 'company') {
                                                echo esc_html(__('Компания', 'codeweber'));
                                            } elseif ($key === 'testimonial_text' || $key === 'testimonial-text' || $normalized_key === 'testimonial_text') {
                                                echo esc_html(__('Текст отзыва', 'codeweber'));
                                            } elseif ($key === 'rating' || $normalized_key === 'rating') {
                                                echo esc_html(__('Рейтинг', 'codeweber'));
                                            } elseif ($key === 'message' || $key === 'Message' || $normalized_key === 'message') {
                                                echo esc_html(__('Сообщение', 'codeweber'));
                                            } elseif ($key === 'user_id' || $key === 'User id' || $key === 'User ID' || $key === 'user id' || $normalized_key === 'user_id') {
                                                echo esc_html(__('ID пользователя', 'codeweber'));
                                            } else {
                                                echo esc_html(ucfirst(str_replace(['_', '-'], ' ', $key)));
                                            }
                                            ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <?php 
                                        // Специальная обработка newsletter_consents, чтобы избежать "Array to string conversion"
                                        if ($key === 'newsletter_consents' && is_array($value)) {
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
                                                $version   = $consent_data['document_version'] ?? null;
                                                $doc_url   = codeweber_forms_get_document_url($doc_id, $version);

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
                
                <?php if ($files_data && is_array($files_data) && !empty($files_data)): ?>
                    <h2><?php _e('Uploaded Files', 'codeweber'); ?></h2>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('File Name', 'codeweber'); ?></th>
                                <th><?php _e('Size', 'codeweber'); ?></th>
                                <th><?php _e('Type', 'codeweber'); ?></th>
                                <th><?php _e('Actions', 'codeweber'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($files_data as $file): ?>
                                <tr>
                                    <td><?php echo esc_html($file['name'] ?? ''); ?></td>
                                    <td><?php echo esc_html(size_format($file['size'] ?? 0)); ?></td>
                                    <td><?php echo esc_html($file['type'] ?? ''); ?></td>
                                    <td>
                                        <?php if (isset($file['url'])): ?>
                                            <a href="<?php echo esc_url($file['url']); ?>" target="_blank" class="button button-small">
                                                <?php _e('Download', 'codeweber'); ?>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}


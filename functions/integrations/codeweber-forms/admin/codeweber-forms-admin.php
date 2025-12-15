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
        add_action('load-toplevel_page_codeweber', [$this, 'add_screen_options']);
        // Обработка массовых действий до вывода (чтобы не было ошибок заголовков)
        add_action('admin_init', [$this, 'process_bulk_actions_early']);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Form Submissions', 'codeweber'),
            __('Form Submissions', 'codeweber'),
            'manage_options',
            'codeweber',
            [$this, 'render_list_page'],
            'dashicons-email-alt',
            30
        );
        
        add_submenu_page(
            'codeweber',
            __('All Submissions', 'codeweber'),
            __('All Submissions', 'codeweber'),
            'manage_options',
            'codeweber',
            [$this, 'render_list_page']
        );
        
        add_submenu_page(
            'codeweber',
            __('Settings', 'codeweber'),
            __('Settings', 'codeweber'),
            'manage_options',
            'codeweber-forms-settings',
            [$this, 'render_settings_page']
        );
        
        add_submenu_page(
            'codeweber',
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
        if (!$screen || $screen->id !== 'toplevel_page_codeweber') {
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
                var id = $(this).data('id');
                $('#submission-' + id).toggle();
            });
        });
        </script>
        <?php
    }
    
    /**
     * Handle admin actions
     */
    private function handle_actions() {
        if (!isset($_POST['action']) || !check_admin_referer('codeweber_forms_action', 'codeweber_forms_nonce')) {
            return;
        }
        
        $action = $_POST['action'];
        $submission_id = isset($_POST['submission_id']) ? intval($_POST['submission_id']) : 0;
        
        switch ($action) {
            case 'delete':
                if ($this->db->permanently_delete_submission($submission_id)) {
                    add_settings_error(
                        'codeweber_forms_messages',
                        'codeweber_forms_message',
                        __('Submission deleted successfully', 'codeweber'),
                        'success'
                    );
                } else {
                    add_settings_error(
                        'codeweber_forms_messages',
                        'codeweber_forms_message',
                        __('Failed to delete submission', 'codeweber'),
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
                                            if ($key === 'newsletter_consents') {
                                                echo esc_html(__('Newsletter Consents', 'codeweber'));
                                            } elseif ($key === 'form_name') {
                                                echo esc_html(__('Form name', 'codeweber'));
                                            } elseif ($key === 'name') {
                                                echo esc_html(__('Name', 'codeweber'));
                                            } elseif ($key === 'role') {
                                                echo esc_html(__('Role', 'codeweber'));
                                            } elseif ($key === 'company') {
                                                echo esc_html(__('Company', 'codeweber'));
                                            } elseif ($key === 'testimonial_text' || $key === 'testimonial-text') {
                                                echo esc_html(__('Testimonial text', 'codeweber'));
                                            } elseif ($key === 'rating') {
                                                echo esc_html(__('Rating', 'codeweber'));
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


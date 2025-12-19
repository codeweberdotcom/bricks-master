<?php
/**
 * Codeweber Forms List Table Class
 * Extends WP_List_Table for form submissions management
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

// Load WP_List_Table class
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Codeweber_Forms_List_Table extends WP_List_Table
{
    private $db;
    private $admin_instance;

    public function __construct($admin_instance)
    {
        $this->db = new CodeweberFormsDatabase();
        $this->admin_instance = $admin_instance;

        parent::__construct(array(
            'singular' => __('Submission', 'codeweber'),
            'plural' => __('Submissions', 'codeweber'),
            'ajax' => false
        ));
    }

    /**
     * Get columns
     */
    public function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'id' => __('ID', 'codeweber'),
            'form' => __('Форма', 'codeweber'),
            'form_type' => __('Тип формы', 'codeweber'),
            'form_name' => __('Form name', 'codeweber'),
            'data' => __('Submission Data', 'codeweber'),
            'status' => __('Status', 'codeweber'),
            'email_admin' => __('Email Admin', 'codeweber'),
            'email_user' => __('Email User', 'codeweber'),
            'date' => __('Date', 'codeweber'),
            'actions' => __('Actions', 'codeweber'),
        );

        return $columns;
    }

    /**
     * Get sortable columns
     */
    protected function get_sortable_columns()
    {
        return array(
            'id' => array('id', true),
            'form' => array('form_name', false),
            'form_type' => array('form_type', false),
            'form_name' => array('form_name', false),
            'status' => array('status', false),
            'date' => array('created_at', true),
        );
    }

    /**
     * Get bulk actions
     */
    
    public function get_bulk_actions()
    {
        $actions = array(
            'mark_read' => __('Mark as Read', 'codeweber'),
            'mark_unread' => __('Mark as Unread', 'codeweber'),
            'archive' => __('Archive', 'codeweber'),
            'unarchive' => __('Unarchive', 'codeweber'),
            'delete' => __('Delete', 'codeweber'),
        );

        return $actions;
    }

    /**
     * Process bulk actions
     */
    public function process_bulk_action()
    {
        // WP_List_Table bulk forms чаще всего используют method="get", поэтому берём данные из $_REQUEST
        if (!isset($_REQUEST['submission']) || !is_array($_REQUEST['submission'])) {
            return;
        }

        $action = $this->current_action();
        if (!$action) {
            return;
        }

        check_admin_referer('bulk-' . $this->_args['plural']);

        $submission_ids = array_map('intval', $_REQUEST['submission']);

        // Debug log for bulk actions
        error_log('Forms List Table - bulk action: ' . $action . ' ids: ' . print_r($submission_ids, true));

        switch ($action) {
            case 'mark_read':
                foreach ($submission_ids as $id) {
                    $this->db->update_submission($id, array('status' => 'read'));
                }
                break;
            case 'mark_unread':
                foreach ($submission_ids as $id) {
                    $this->db->update_submission($id, array('status' => 'new'));
                }
                break;
            case 'archive':
                foreach ($submission_ids as $id) {
                    $this->db->update_submission($id, array('status' => 'archived'));
                }
                break;
            case 'unarchive':
                foreach ($submission_ids as $id) {
                    $this->db->update_submission($id, array('status' => 'read'));
                }
                break;
            case 'delete':
                foreach ($submission_ids as $id) {
                    // soft delete -> trash
                    $this->db->delete_submission($id);
                }
                break;
        }
    }

    /**
     * Prepare items
     */
    public function prepare_items()
    {
        $this->process_bulk_action();

        $per_page = $this->get_items_per_page('submissions_per_page', 20);
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;

        // Get filter parameters
        $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $form_id = isset($_GET['form_id']) ? sanitize_text_field($_GET['form_id']) : '';
        $form_type = isset($_GET['form_type']) ? sanitize_text_field($_GET['form_type']) : '';
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

        // Get order parameters
        $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'created_at';
        $order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'DESC';

        // By default, hide trashed items from main list (unless explicitly filtered)
        $exclude_status = '';
        if (empty($status)) {
            $exclude_status = 'trash';
        }

        // Get total count (exclude trash if no status filter)
        if (empty($status)) {
            $total_items = $this->db->count_submissions([
                'form_id'        => $form_id,
                'form_type'      => $form_type,
                'search'         => $search,
                'exclude_status' => 'trash',
            ]);
        } else {
            $total_items = $this->db->get_submissions_count($status, $form_id, $form_type, $search);
        }

        // Get items (exclude trash if no status filter)
        $this->items = $this->db->get_submissions([
            'limit'          => $per_page,
            'offset'         => $offset,
            'orderby'        => $orderby,
            'order'          => $order,
            'status'         => $status,
            'form_id'        => $form_id,
            'form_type'      => $form_type,
            'search'         => $search,
            'exclude_status' => $exclude_status,
        ]);

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ));
    }

    /**
     * Get views (status filters)
     */
    protected function get_views()
    {
        $views = array();
        $current = isset($_GET['status']) ? $_GET['status'] : 'all';

        $statuses = array(
            'all'      => __('All', 'codeweber'),
            'new'      => __('New', 'codeweber'),
            'read'     => __('Read', 'codeweber'),
            'archived' => __('Archived', 'codeweber'),
            'trash'    => __('Trash', 'codeweber'),
        );

        $base_url = admin_url('admin.php?page=codeweber');

        foreach ($statuses as $status => $label) {
            if ($status === 'all') {
                $count = $this->db->count_submissions(['exclude_status' => 'trash']);
            } else {
                $count = $this->db->get_submissions_count($status);
            }
            $class = ($current === $status) ? 'current' : '';
            $url = $status === 'all' ? $base_url : add_query_arg('status', $status, $base_url);
            $views[$status] = sprintf(
                '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
                esc_url($url),
                $class,
                $label,
                $count
            );
        }

        return $views;
    }

    /**
     * Column checkbox
     */
    protected function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="submission[]" value="%s" />',
            $item->id
        );
    }

    /**
     * Column ID
     */
    protected function column_id($item)
    {
        return '<strong>#' . $item->id . '</strong>';
    }

    /**
     * Column Form
     */
    protected function column_form($item)
    {
        $form_id = $item->form_id;
        
        // Если form_id числовой и это CPT форма, показываем ID со ссылкой
        if (is_numeric($form_id) && (int) $form_id > 0) {
            $post = get_post((int) $form_id);
            if ($post && $post->post_type === 'codeweber_form') {
                $edit_link = get_edit_post_link($form_id);
                return '<a href="' . esc_url($edit_link) . '"><strong>#' . esc_html($form_id) . '</strong></a>';
            }
        }
        
        // Для встроенных форм (строковые ID) показываем ID без ссылки
        if (is_string($form_id) && !is_numeric($form_id)) {
            $builtin_labels = array(
                'newsletter'  => __('Newsletter Subscription', 'codeweber'),
                'testimonial' => __('Testimonial Form', 'codeweber'),
                'resume'      => __('Resume Form', 'codeweber'),
                'callback'    => __('Callback Request', 'codeweber'),
            );
            if (isset($builtin_labels[$form_id])) {
                return '<strong>' . esc_html($form_id) . '</strong>';
            }
        }
        
        // Если form_id = 0 – это старая запись формы отзыва
        if ((int) $form_id === 0) {
            return '<strong>0</strong>';
        }
        
        // Фоллбек: показываем сам form_id как есть
        return '<strong>' . esc_html($form_id) . '</strong>';
    }
    
    /**
     * Column Form Type
     */
    protected function column_form_type($item)
    {
        // ПРИОРИТЕТ 1: Получаем тип формы из базы данных
        $form_type = !empty($item->form_type) ? $item->form_type : null;
        
        // ПРИОРИТЕТ 2: Если тип не сохранен, пытаемся определить его
        if (empty($form_type) && !empty($item->form_id)) {
            if (class_exists('CodeweberFormsCore')) {
                $form_type = CodeweberFormsCore::get_form_type($item->form_id);
            } else {
                // Fallback: для числового ID проверяем метаполе
                if (is_numeric($item->form_id) && (int) $item->form_id > 0) {
                    $form_type = get_post_meta((int) $item->form_id, '_form_type', true);
                    if (empty($form_type)) {
                        // Если метаполе пустое, пытаемся извлечь из блока
                        $post = get_post((int) $item->form_id);
                        if ($post && $post->post_type === 'codeweber_form' && !empty($post->post_content)) {
                            $blocks = parse_blocks($post->post_content);
                            foreach ($blocks as $block) {
                                if ($block['blockName'] === 'codeweber-blocks/form' && !empty($block['attrs']['formType'])) {
                                    $form_type = sanitize_text_field($block['attrs']['formType']);
                                    break;
                                }
                            }
                        }
                    }
                } else {
                    // Для строковых ID (legacy формы)
                    $form_id_lower = strtolower($item->form_id);
                    $builtin_types = ['newsletter', 'testimonial', 'resume', 'callback'];
                    if (in_array($form_id_lower, $builtin_types)) {
                        $form_type = $form_id_lower;
                    }
                }
            }
        }
        
        // Если form_id = 0, это старая форма отзыва
        if (empty($form_type) && (int) $item->form_id === 0) {
            $form_type = 'testimonial';
        }
        
        // По умолчанию
        if (empty($form_type)) {
            $form_type = 'form';
        }
        
        // Маппинг типов на читаемые названия
        $type_labels = array(
            'form' => __('Regular Form', 'codeweber'),
            'newsletter' => __('Newsletter Subscription', 'codeweber'),
            'testimonial' => __('Testimonial Form', 'codeweber'),
            'resume' => __('Resume Form', 'codeweber'),
            'callback' => __('Callback Request', 'codeweber'),
        );
        
        $type_label = isset($type_labels[$form_type]) ? $type_labels[$form_type] : $form_type;
        $type_badge_color = array(
            'form' => '#2271b1',
            'newsletter' => '#00a32a',
            'testimonial' => '#d63638',
            'resume' => '#d54e21',
            'callback' => '#826eb4',
        );
        
        $badge_color = isset($type_badge_color[$form_type]) ? $type_badge_color[$form_type] : '#666';
        
        return sprintf(
            '<span style="display: inline-block; padding: 3px 8px; background-color: %s; color: white; border-radius: 3px; font-size: 11px; font-weight: 600;">%s</span>',
            esc_attr($badge_color),
            esc_html($type_label)
        );
    }

    /**
     * Column Form Name
     */
    protected function column_form_name($item)
    {
        return esc_html($item->form_name ?: '-');
    }

    /**
     * Column Status
     */
    protected function column_status($item)
    {
        $status_colors = array(
            'new' => '#d63638',
            'read' => '#2271b1',
            'archived' => '#646970',
        );

        $color = isset($status_colors[$item->status]) ? $status_colors[$item->status] : '#666';
        $label = ucfirst($item->status);

        return sprintf(
            '<span style="display: inline-block; padding: 3px 8px; background-color: %s; color: white; border-radius: 3px; font-size: 11px; font-weight: 600;">%s</span>',
            esc_attr($color),
            esc_html($label)
        );
    }

    /**
     * Column Email Admin
     */
    protected function column_email_admin($item)
    {
        if ($item->email_sent) {
            return '<span class="dashicons dashicons-yes-alt" style="color: green;"></span>';
        } else {
            $error = $item->email_error ? ' <small style="color: red;">(' . esc_html($item->email_error) . ')</small>' : '';
            return '<span class="dashicons dashicons-dismiss" style="color: red;"></span>' . $error;
        }
    }

    /**
     * Column Email User
     */
    protected function column_email_user($item)
    {
        if ($item->auto_reply_sent) {
            return '<span class="dashicons dashicons-yes-alt" style="color: green;"></span>';
        } else {
            $error = $item->auto_reply_error ? ' <small style="color: red;">(' . esc_html($item->auto_reply_error) . ')</small>' : '';
            return '<span class="dashicons dashicons-dismiss" style="color: red;"></span>' . $error;
        }
    }

    /**
     * Column Date
     */
    protected function column_date($item)
    {
        return date_i18n(get_option('date_format') . ' H:i', strtotime($item->created_at));
    }

    /**
     * Column Actions
     */
    protected function column_actions($item)
    {
        $view_url = admin_url('admin.php?page=codeweber&action=view&id=' . $item->id);
        $delete_url = wp_nonce_url(
            admin_url('admin.php?page=codeweber&action=delete&id=' . $item->id),
            'delete_submission_' . $item->id
        );

        $actions = array(
            'view' => '<a href="' . esc_url($view_url) . '">' . __('View', 'codeweber') . '</a>',
            'delete' => '<a href="' . esc_url($delete_url) . '" onclick="return confirm(\'' . esc_js(__('Are you sure?', 'codeweber')) . '\');">' . __('Delete', 'codeweber') . '</a>',
        );

        return $this->row_actions($actions);
    }

    /**
     * Column Data
     */
    protected function column_data($item)
    {
        $data = json_decode($item->submission_data, true);
        
        // Check for files
        $has_files = false;
        $files_count = 0;
        if (!empty($item->files_data)) {
            $files_data = json_decode($item->files_data, true);
            if (is_array($files_data) && !empty($files_data)) {
                $has_files = true;
                $files_count = count($files_data);
            }
        }
        
        $output = '';
        
        // Show files indicator
        if ($has_files) {
            $output .= '<span class="dashicons dashicons-paperclip" style="color: #2271b1; vertical-align: middle; margin-right: 5px;" title="' . esc_attr(sprintf(_n('%d file attached', '%d files attached', $files_count, 'codeweber'), $files_count)) . '"></span> ';
        }
        
        if (!$data) {
            return $output . ($has_files ? '' : '—');
        }

        $preview = [];
        $count = 0;
        foreach (array_slice($data, 0, 3) as $key => $value) {
            if ($key === '_utm_data') {
                continue;
            }

            $label = $this->get_field_label($key);

            // Special handling for newsletter consents to avoid "Array" output
            if ($key === 'newsletter_consents' && is_array($value)) {
                $display_value = $this->format_newsletter_consents($value);
            } else {
                $display_value = $this->format_submission_value($value);
            }

            if (mb_strlen($display_value) > 50) {
                $display_value = mb_substr($display_value, 0, 50) . '...';
            }
            $preview[] = '<strong>' . esc_html($label) . ':</strong> ' . esc_html($display_value);
            $count++;
        }

        $output .= '<div class="submission-preview" id="submission-preview-' . $item->id . '">';
        $output .= implode('<br>', $preview);
        
        if (count($data) > $count) {
            $output .= ' <a href="#" class="view-full" data-id="' . $item->id . '">' . __('Показать все', 'codeweber') . '</a>';
        }
        
        $output .= '</div>';
        
        // Full data (hidden)
        $output .= '<div class="submission-full" id="submission-' . $item->id . '" style="display:none;">';
        foreach ($data as $key => $value) {
            if ($key === '_utm_data') {
                continue;
            }

            $label = $this->get_field_label($key);
            $output .= '<strong>' . esc_html($label) . ':</strong> ';

            if ($key === 'newsletter_consents' && is_array($value)) {
                $display_value = $this->format_newsletter_consents($value);
            } else {
                $display_value = $this->format_submission_value($value);
            }

            $output .= esc_html($display_value);
            $output .= '<br>';
        }
        $output .= '</div>';

        return $output;
    }

    /**
     * Format generic submission value (handles nested arrays safely)
     *
     * @param mixed $value
     * @return string
     */
    protected function format_submission_value($value): string
    {
        if (is_array($value)) {
            $formatted = [];
            foreach ($value as $k => $v) {
                if (is_array($v)) {
                    // Skip deeply nested arrays to avoid "Array to string conversion"
                    continue;
                }

                // Keep key for associative arrays for better readability
                if (!is_int($k)) {
                    $formatted[] = $k . ': ' . $v;
                } else {
                    $formatted[] = $v;
                }
            }

            return implode(', ', $formatted);
        }

        return (string) $value;
    }

    /**
     * Format newsletter consents value for display in admin table
     *
     * @param array $consents
     * @return string
     */
    protected function format_newsletter_consents(array $consents): string
    {
        $consents_list = [];

        foreach ($consents as $doc_id => $consent_data) {
            $doc_title = '';
            $doc = get_post($doc_id);
            if ($doc) {
                $doc_title = $doc->post_title;
            }

            $consent_info = $doc_title ? $doc_title : sprintf(__('Document ID: %d', 'codeweber'), $doc_id);

            if (!empty($consent_data['document_version'])) {
                $consent_info .= ' (' . __('Version', 'codeweber') . ': ' . $consent_data['document_version'] . ')';
            }

            if (!empty($consent_data['document_revision_id'])) {
                $consent_info .= ' [' . __('Revision ID', 'codeweber') . ': ' . $consent_data['document_revision_id'] . ']';
            }

            $consents_list[] = $consent_info;
        }

        return implode(', ', $consents_list);
    }

    /**
     * Get field label
     */
    protected function get_field_label($field_name)
    {
        $labels = array(
            'name' => __('Name', 'codeweber'),
            'email' => __('Email', 'codeweber'),
            'phone' => __('Phone', 'codeweber'),
            'message' => __('Message', 'codeweber'),
            'subject' => __('Subject', 'codeweber'),
            'newsletter_consents' => __('Newsletter Consents', 'codeweber'),
        );

        return isset($labels[$field_name]) ? $labels[$field_name] : ucfirst(str_replace('_', ' ', $field_name));
    }
}

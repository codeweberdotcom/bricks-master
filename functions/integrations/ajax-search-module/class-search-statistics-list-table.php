<?php
/**
 * Search Statistics List Table Class
 * Extends WP_List_Table for search queries management
 */

if (!defined('ABSPATH')) {
    exit;
}

// Load WP_List_Table class
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Search_Statistics_List_Table extends WP_List_Table
{
    private $table_name;

    public function __construct()
    {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'search_statistics';

        parent::__construct(array(
            'singular' => __('Search', 'codeweber'),
            'plural' => __('Searches', 'codeweber'),
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
            'search_query' => __('Query', 'codeweber'),
            'user' => __('User', 'codeweber'),
            'search_date' => __('Date', 'codeweber'),
            'results_count' => __('Results', 'codeweber'),
            'form_id' => __('Form ID', 'codeweber'),
            'page' => __('Page', 'codeweber'),
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
            'search_query' => array('search_query', false),
            'search_date' => array('search_date', true),
            'results_count' => array('results_count', false),
        );
    }

    /**
     * Get bulk actions
     */
    public function get_bulk_actions()
    {
        $actions = array(
            'delete' => __('Delete', 'codeweber'),
        );

        return $actions;
    }

    /**
     * Process bulk actions
     */
    public function process_bulk_action()
    {
        if (!isset($_POST['search']) || !is_array($_POST['search'])) {
            return;
        }

        $action = $this->current_action();
        if (!$action || $action === '-1') {
            return;
        }

        check_admin_referer('bulk-' . $this->_args['plural']);

        $search_ids = array_map('intval', $_POST['search']);

        switch ($action) {
            case 'delete':
                global $wpdb;
                if (!empty($search_ids)) {
                    $placeholders = implode(',', array_fill(0, count($search_ids), '%d'));
                    $wpdb->query($wpdb->prepare(
                        "DELETE FROM {$this->table_name} WHERE id IN ($placeholders)",
                        ...$search_ids
                    ));
                }
                break;
        }
        
        // Redirect after bulk action
        $redirect_url = admin_url('admin.php?page=search-statistics');
        // Preserve filters from POST (they're preserved in form hidden fields)
        if (isset($_POST['start_date']) && !empty($_POST['start_date'])) {
            $redirect_url = add_query_arg('start_date', sanitize_text_field($_POST['start_date']), $redirect_url);
        }
        if (isset($_POST['end_date']) && !empty($_POST['end_date'])) {
            $redirect_url = add_query_arg('end_date', sanitize_text_field($_POST['end_date']), $redirect_url);
        }
        if (isset($_POST['form_id']) && !empty($_POST['form_id'])) {
            $redirect_url = add_query_arg('form_id', sanitize_text_field($_POST['form_id']), $redirect_url);
        }
        wp_redirect($redirect_url);
        exit;
    }

    /**
     * Prepare items
     */
    public function prepare_items()
    {
        global $wpdb;
        
        $this->process_bulk_action();

        $per_page = $this->get_items_per_page('search_statistics_per_page', 20);
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;

        // Get filter parameters
        $start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '';
        $end_date = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '';
        $form_id = isset($_GET['form_id']) ? sanitize_text_field($_GET['form_id']) : '';
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

        // Get order parameters
        $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'search_date';
        $order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'DESC';

        // Validate orderby
        $allowed_orderby = array('id', 'search_query', 'search_date', 'results_count');
        if (!in_array($orderby, $allowed_orderby)) {
            $orderby = 'search_date';
        }

        // Sanitize order
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        
        // Sanitize column name
        $orderby = preg_replace('/[^a-z0-9_]/i', '', $orderby);

        // Build WHERE clause
        $where_conditions = array();
        $query_params = array();
        $use_prepare = false;

        if (!empty($start_date)) {
            $where_conditions[] = "DATE(search_date) >= %s";
            $query_params[] = $start_date;
            $use_prepare = true;
        }

        if (!empty($end_date)) {
            $where_conditions[] = "DATE(search_date) <= %s";
            $query_params[] = $end_date;
            $use_prepare = true;
        }

        if (!empty($form_id)) {
            if ($form_id === '_none') {
                $where_conditions[] = "(form_id = '' OR form_id IS NULL)";
            } else {
                $where_conditions[] = "form_id = %s";
                $query_params[] = $form_id;
                $use_prepare = true;
            }
        }

        if (!empty($search)) {
            $where_conditions[] = "(search_query LIKE %s OR page_title LIKE %s)";
            $search_like = '%' . $wpdb->esc_like($search) . '%';
            $query_params[] = $search_like;
            $query_params[] = $search_like;
            $use_prepare = true;
        }

        $where_sql = '';
        if (!empty($where_conditions)) {
            $where_sql = "WHERE " . implode(" AND ", $where_conditions);
        }

        // Get total count
        if (!empty($where_sql) && $use_prepare) {
            $total_items = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} {$where_sql}",
                $query_params
            ));
        } else {
            $total_items = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} {$where_sql}");
        }

        // Get items
        $query = "SELECT * FROM {$this->table_name} {$where_sql} ORDER BY {$orderby} {$order} LIMIT {$per_page} OFFSET {$offset}";
        
        if (!empty($where_sql) && $use_prepare) {
            $this->items = $wpdb->get_results($wpdb->prepare($query, $query_params));
        } else {
            $this->items = $wpdb->get_results($query);
        }

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ));
        
        // Explicitly set column headers
        $columns = $this->get_columns();
        $hidden = get_hidden_columns($this->screen);
        $sortable = $this->get_sortable_columns();
        
        // Get primary column name (default to search_query)
        if (method_exists($this, 'get_primary_column_name')) {
            $primary = $this->get_primary_column_name();
        } else {
            $primary = 'search_query';
        }
        
        $this->_column_headers = array($columns, $hidden, $sortable, $primary);
    }

    /**
     * Column checkbox
     */
    protected function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="search[]" value="%s" />',
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
     * Column Search Query
     */
    protected function column_search_query($item)
    {
        return '<strong>' . esc_html($item->search_query) . '</strong>';
    }

    /**
     * Column User
     */
    protected function column_user($item)
    {
        if ($item->user_id) {
            $user = get_user_by('id', $item->user_id);
            if ($user) {
                return '<a href="' . admin_url('user-edit.php?user_id=' . $user->ID) . '">' . esc_html($user->display_name) . '</a>';
            } else {
                return __('User #', 'codeweber') . $item->user_id;
            }
        } else {
            return '<span style="color: #646970;">' . __('Guest', 'codeweber') . '</span>';
        }
    }

    /**
     * Column Search Date
     */
    protected function column_search_date($item)
    {
        return date_i18n(get_option('date_format') . ' H:i', strtotime($item->search_date));
    }

    /**
     * Column Results Count
     */
    protected function column_results_count($item)
    {
        return number_format($item->results_count);
    }

    /**
     * Column Form ID
     */
    protected function column_form_id($item)
    {
        if (empty($item->form_id)) {
            return '<span style="color: #646970;">—</span>';
        }
        
        return '<span style="display: inline-block; padding: 2px 6px; background-color: #f0f0f1; border-radius: 3px; font-family: monospace; font-size: 13px;">' . esc_html($item->form_id) . '</span>';
    }

    /**
     * Column Page
     */
    protected function column_page($item)
    {
        $title = wp_trim_words($item->page_title, 5);
        return '<a href="' . esc_url($item->page_url) . '" target="_blank">' . esc_html($title) . '</a>';
    }

    /**
     * Column Actions
     */
    protected function column_actions($item)
    {
        $delete_url = wp_nonce_url(
            admin_url('admin.php?page=search-statistics&action=delete&id=' . $item->id),
            'delete_search_' . $item->id
        );

        $actions = array(
            'delete' => '<a href="' . esc_url($delete_url) . '" onclick="return confirm(\'' . esc_js(__('Are you sure?', 'codeweber')) . '\');">' . __('Delete', 'codeweber') . '</a>',
        );

        return $this->row_actions($actions);
    }

    /**
     * Column default
     */
    protected function column_default($item, $column_name)
    {
        return '—';
    }
}


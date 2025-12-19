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
    protected function get_bulk_actions()
    {
        $actions = array(
            'mark_read'     => __('Отметить как прочитанные', 'codeweber'),
            'mark_new'      => __('Отметить как новые', 'codeweber'),
        );

        // Если мы в корзине — показываем "Восстановить" и "Удалить навсегда",
        // иначе — "В корзину" (мягкое удаление).
        $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        if ($status === 'trash') {
            $actions['restore'] = __('Восстановить из корзины', 'codeweber');
            $actions['delete']  = __('Удалить навсегда', 'codeweber');
        } else {
            $actions['trash']   = __('В корзину', 'codeweber');
        }

        return $actions;
    }

    /**
     * Views (фильтры по статусу: Все, Новые, Прочитанные, Архив, Корзина)
     * Аналогично newsletter-subscriptions
     */
    protected function get_views()
    {
        $current_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';

        $views = array();

        // Все (кроме корзины)
        $all_count = $this->db->count_submissions(array(
            'exclude_status' => 'trash',
        ));
        $class = ($current_status === '') ? 'current' : '';
        $views['all'] = sprintf(
            '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
            admin_url('admin.php?page=codeweber'),
            $class,
            __('Все', 'codeweber'),
            $all_count
        );

        // Новые
        $new_count = $this->db->count_submissions(array('status' => 'new'));
        $class = ($current_status === 'new') ? 'current' : '';
        $views['new'] = sprintf(
            '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
            admin_url('admin.php?page=codeweber&status=new'),
            $class,
            __('Новые', 'codeweber'),
            $new_count
        );

        // Прочитанные
        $read_count = $this->db->count_submissions(array('status' => 'read'));
        $class = ($current_status === 'read') ? 'current' : '';
        $views['read'] = sprintf(
            '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
            admin_url('admin.php?page=codeweber&status=read'),
            $class,
            __('Прочитанные', 'codeweber'),
            $read_count
        );

        // Корзина (показываем всегда, даже если 0 — как в стандартных списках)
        $trash_count = $this->db->count_submissions(array('status' => 'trash'));
        $class = ($current_status === 'trash') ? 'current' : '';
        $views['trash'] = sprintf(
            '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
            admin_url('admin.php?page=codeweber&status=trash'),
            $class,
            __('Корзина', 'codeweber'),
            $trash_count
        );

        return $views;
    }

    /**
     * Column default
     */
    protected function column_default($item, $column_name)
    {
        // Явная обработка колонки form_name (на случай, если WordPress преобразует подчеркивание)
        if ($column_name === 'form_name' || $column_name === 'form-name') {
            return $this->column_form_name($item);
        }
        
        // Явная обработка колонки form_type
        if ($column_name === 'form_type' || $column_name === 'form-type') {
            return $this->column_form_type($item);
        }
        
        $method = 'column_' . str_replace('-', '_', $column_name);
        if (method_exists($this, $method)) {
            return call_user_func(array($this, $method), $item);
        }
        return '';
    }

    /**
     * Column checkbox
     */
    protected function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="submission[]" value="%s" />',
            esc_attr($item->id)
        );
    }

    /**
     * Column ID
     */
    protected function column_id($item)
    {
        return esc_html($item->id);
    }

    /**
     * Column Form
     * Показывает ID формы со ссылкой на форму
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
        
        return '<span style="display: inline-block; padding: 2px 8px; border-radius: 3px; background: ' . esc_attr($badge_color) . '; color: #fff; font-size: 11px; font-weight: 500;">' . esc_html($type_label) . '</span>';
    }

    /**
     * Column Form Name
     * Показывает значение поля form_name из базы данных
     * Если form_name пустое или это ID, получаем название из заголовка CPT записи
     */
    protected function column_form_name($item)
    {
        // ПРИОРИТЕТ 1: Поле form_name из таблицы submissions (если это не ID)
        if (!empty($item->form_name) && trim($item->form_name) !== '') {
            $form_name = trim($item->form_name);
            // Если это не чисто число (не ID), показываем
            if (!is_numeric($form_name)) {
                return esc_html($form_name);
            }
        }
        
        // ПРИОРИТЕТ 2: Если form_name пустое или это ID, получаем название из заголовка CPT записи
        if (!empty($item->form_id) && is_numeric($item->form_id)) {
            $form_post = get_post((int) $item->form_id);
            if ($form_post && $form_post->post_type === 'codeweber_form' && !empty($form_post->post_title)) {
                return esc_html($form_post->post_title);
            }
        }
        
        // ПРИОРИТЕТ 3: Извлекаем form_name из JSON данных отправки (fallback)
        if (!empty($item->submission_data)) {
            $data = json_decode($item->submission_data, true);
            if (is_array($data)) {
                // Проверяем разные варианты ключей
                if (!empty($data['form_name'])) {
                    $form_name = trim($data['form_name']);
                    if (!empty($form_name) && !is_numeric($form_name)) {
                        return esc_html($form_name);
                    }
                }
                // Также проверяем вариант с пробелом (form name)
                if (!empty($data['form name'])) {
                    $form_name = trim($data['form name']);
                    if (!empty($form_name) && !is_numeric($form_name)) {
                        return esc_html($form_name);
                    }
                }
            }
        }
        
        // Если form_name не найдено, показываем прочерк
        return '<span style="color: #999;">—</span>';
    }

    /**
     * Get translated field label
     */
    protected function get_field_label($key)
    {
        // Нормализуем ключ (убираем пробелы, приводим к нижнему регистру)
        $normalized_key = strtolower(str_replace([' ', '-'], '_', trim($key)));
        
        // Переводим некоторые служебные ключи в человекочитаемые и переводимые названия
        if ($normalized_key === 'newsletter_consents') {
            return __('Newsletter Consents', 'codeweber');
        } elseif ($normalized_key === 'form_name' || $normalized_key === 'formname' || $key === 'form name' || $key === 'Form name') {
            return __('Название формы', 'codeweber');
        } elseif ($normalized_key === 'user_id' || $normalized_key === 'userid' || $key === 'User id' || $key === 'User ID' || $key === 'user id') {
            return __('ID пользователя', 'codeweber');
        } elseif ($normalized_key === 'phone' || $key === 'Phone') {
            return __('Телефон', 'codeweber');
        } elseif ($normalized_key === 'message' || $key === 'Message') {
            return __('Сообщение', 'codeweber');
        } elseif ($normalized_key === 'name' || $key === 'Name') {
            return __('Имя', 'codeweber');
        } elseif ($normalized_key === 'email' || $key === 'Email') {
            return __('Email', 'codeweber');
        } elseif ($normalized_key === 'role' || $key === 'Role') {
            return __('Роль', 'codeweber');
        } elseif ($normalized_key === 'company' || $key === 'Company') {
            return __('Компания', 'codeweber');
        } elseif ($normalized_key === 'testimonial_text' || $normalized_key === 'testimonial-text' || $key === 'Testimonial text') {
            return __('Текст отзыва', 'codeweber');
        } elseif ($normalized_key === 'rating' || $key === 'Rating') {
            return __('Рейтинг', 'codeweber');
        } elseif ($normalized_key === 'subject' || $key === 'Subject') {
            return __('Тема', 'codeweber');
        } elseif ($normalized_key === 'comment' || $key === 'Comment') {
            return __('Комментарий', 'codeweber');
        } else {
            // Если ключ не найден в списке, пытаемся перевести через ucfirst
            // Но сначала проверяем, может быть это уже переведенная строка
            $translated = __(ucfirst(str_replace(['_', '-'], ' ', $key)), 'codeweber');
            return $translated;
        }
    }

    /**
     * Column Submission Data
     */
    protected function column_data($item)
    {
        $data = json_decode($item->submission_data, true);
        
        if (!$data) {
            return '—';
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

        $output = '<div class="submission-preview" id="submission-preview-' . $item->id . '">';
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

        return implode('; ', $consents_list);
    }

    /**
     * Column Status
     */
    protected function column_status($item)
    {
        $status = $item->status;
        $status_class = 'status-' . $status;

        switch ($status) {
            case 'new':
                $label = __('New', 'codeweber');
                break;
            case 'read':
                $label = __('Read', 'codeweber');
                break;
            case 'archived':
                $label = __('Archived', 'codeweber');
                break;
            case 'trash':
                $label = __('Trash', 'codeweber');
                break;
            default:
                $label = ucfirst($status);
                break;
        }

        return '<span class="status-badge ' . esc_attr($status_class) . '">' . esc_html($label) . '</span>';
    }

    /**
     * Column Email Admin
     */
    protected function column_email_admin($item)
    {
        if ($item->email_sent) {
            return '<span class="dashicons dashicons-yes-alt" style="color: green;" title="' . esc_attr__('Sent', 'codeweber') . '"></span>';
        } else {
            $output = '<span class="dashicons dashicons-dismiss" style="color: red;" title="' . esc_attr__('Not sent', 'codeweber') . '"></span>';
            if ($item->email_error) {
                $output .= ' <span class="dashicons dashicons-warning" title="' . esc_attr($item->email_error) . '"></span>';
            }
            return $output;
        }
    }

    /**
     * Column Email User
     */
    protected function column_email_user($item)
    {
        if ($item->auto_reply_sent) {
            return '<span class="dashicons dashicons-yes-alt" style="color: green;" title="' . esc_attr__('Sent', 'codeweber') . '"></span>';
        } else {
            $output = '<span class="dashicons dashicons-dismiss" style="color: red;" title="' . esc_attr__('Not sent', 'codeweber') . '"></span>';
            if ($item->auto_reply_error) {
                $output .= ' <span class="dashicons dashicons-warning" title="' . esc_attr($item->auto_reply_error) . '"></span>';
            }
            return $output;
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
        ob_start();
        ?>
        <a href="<?php echo admin_url('admin.php?page=codeweber&action=view&id=' . $item->id); ?>" class="button button-small">
            <?php _e('View', 'codeweber'); ?>
        </a>
        <?php
        return ob_get_clean();
    }

    /**
     * Prepare items
     */
    public function prepare_items()
    {
        global $wpdb;

        $per_page = $this->get_items_per_page('codeweber_forms_per_page', 20);
        $current_page = $this->get_pagenum();

        // Get filters
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $form_id = isset($_GET['form_id']) ? sanitize_text_field($_GET['form_id']) : '';
        $form_type = isset($_GET['form_type']) ? sanitize_text_field($_GET['form_type']) : ''; // НОВОЕ: Фильтр по типу формы

        // Get sort order
        $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'created_at';
        $order = isset($_GET['order']) && strtoupper($_GET['order']) === 'ASC' ? 'ASC' : 'DESC';

        // Validate orderby
        $allowed_orderby = array('id', 'form_name', 'form_type', 'status', 'created_at');
        if (!in_array($orderby, $allowed_orderby)) {
            $orderby = 'created_at';
        }

        // Build args for database query
        $args = array(
            'limit' => $per_page,
            'offset' => ($current_page - 1) * $per_page,
            'orderby' => $orderby,
            'order' => $order,
        );

        if ($form_id !== '') {
            $args['form_id'] = $form_id;
        }
        // НОВОЕ: Фильтрация по типу формы
        if ($form_type !== '') {
            $args['form_type'] = $form_type;
        }
        if ($status) {
            $args['status'] = $status;
        } else {
            // По умолчанию не показываем корзину (trash), как в newsletter-subscriptions
            $args['exclude_status'] = 'trash';
        }
        if ($search) {
            $args['search'] = $search;
        }

        // Get items
        $items = $this->db->get_submissions($args);
        $total_items = $this->db->count_submissions($args);

        // Ensure items is an array
        if (!is_array($items)) {
            $this->items = array();
        } else {
            $this->items = array_map(function($item) {
                return is_object($item) ? $item : (object) $item;
            }, $items);
        }

        // Set pagination
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));

        // Set column headers (как в newsletter-subscriptions):
        // - колонки берем из get_columns()
        // - скрытые колонки — из Screen Options
        // - сортируемые — из get_sortable_columns()
        $columns  = $this->get_columns();
        $hidden   = function_exists('get_hidden_columns') && $this->screen
            ? get_hidden_columns($this->screen)
            : array();
        $sortable = $this->get_sortable_columns();
        $primary  = 'id';

        $this->_column_headers = array($columns, $hidden, $sortable, $primary);
    }

    /**
     * Process bulk actions
     */
    public function process_bulk_action()
    {
        // WordPress list tables используют $_REQUEST, поэтому
        // поддерживаем и GET, и POST (как это делает ядро).
        if (!isset($_REQUEST['submission']) || !is_array($_REQUEST['submission'])) {
            return;
        }

        // WP_List_Table выводит nonce вида 'bulk-' . $this->_args['plural'],
        // поэтому используем ту же схему для проверки.
        if (
            !isset($_REQUEST['_wpnonce']) ||
            !wp_verify_nonce($_REQUEST['_wpnonce'], 'bulk-' . $this->_args['plural'])
        ) {
            return;
        }

        $action = $this->current_action();
        if (!$action) {
            return;
        }

        $ids = array_map('intval', $_REQUEST['submission']);
        $ids = array_filter($ids);

        if (empty($ids)) {
            return;
        }

        $redirect_url = admin_url('admin.php?page=codeweber');
        
        // Preserve filter parameters
        $params = array();
        if (isset($_GET['status']) && !empty($_GET['status'])) {
            $params['status'] = sanitize_text_field($_GET['status']);
        }
        if (isset($_GET['form_id']) && !empty($_GET['form_id'])) {
            $params['form_id'] = sanitize_text_field($_GET['form_id']);
        }
        // НОВОЕ: Сохраняем фильтр по типу формы
        if (isset($_GET['form_type']) && !empty($_GET['form_type'])) {
            $params['form_type'] = sanitize_text_field($_GET['form_type']);
        }
        if (isset($_GET['s']) && !empty($_GET['s'])) {
            $params['s'] = sanitize_text_field($_GET['s']);
        }
        if (isset($_GET['paged']) && !empty($_GET['paged'])) {
            $params['paged'] = intval($_GET['paged']);
        }
        
        if (!empty($params)) {
            $redirect_url = add_query_arg($params, $redirect_url);
        }

        $updated = 0;
        $deleted = 0;

        switch ($action) {
            case 'mark_read':
                $result = $this->db->bulk_update_status($ids, 'read');
                if ($result !== false) {
                    $updated = $result;
                    add_settings_error(
                        'codeweber_forms_messages',
                        'codeweber_forms_message',
                        sprintf(__('%d submission(s) marked as read', 'codeweber'), $updated),
                        'success'
                    );
                }
                break;

            case 'mark_new':
                $result = $this->db->bulk_update_status($ids, 'new');
                if ($result !== false) {
                    $updated = $result;
                    add_settings_error(
                        'codeweber_forms_messages',
                        'codeweber_forms_message',
                        sprintf(__('%d submission(s) marked as new', 'codeweber'), $updated),
                        'success'
                    );
                }
                break;

            case 'trash':
                // Переместить в корзину
                $result = $this->db->bulk_update_status($ids, 'trash');
                if ($result !== false) {
                    $updated = $result;
                    add_settings_error(
                        'codeweber_forms_messages',
                        'codeweber_forms_message',
                        sprintf(__('%d submission(s) moved to trash', 'codeweber'), $updated),
                        'success'
                    );
                }
                break;

            case 'restore':
                // Восстановить из корзины: по умолчанию делаем статус new
                $result = $this->db->bulk_update_status($ids, 'new');
                if ($result !== false) {
                    $updated = $result;
                    add_settings_error(
                        'codeweber_forms_messages',
                        'codeweber_forms_message',
                        sprintf(__('%d submission(s) restored from trash', 'codeweber'), $updated),
                        'success'
                    );
                }
                break;

            case 'delete':
                $result = $this->db->bulk_permanently_delete_submissions($ids);
                if ($result !== false) {
                    $deleted = $result;
                    add_settings_error(
                        'codeweber_forms_messages',
                        'codeweber_forms_message',
                        sprintf(__('%d submission(s) deleted permanently', 'codeweber'), $deleted),
                        'success'
                    );
                }
                break;
        }

        if ($updated > 0 || $deleted > 0) {
            wp_redirect($redirect_url);
            exit;
        }
    }

    /**
     * Extra controls to be displayed between bulk actions and pagination
     */
    protected function extra_tablenav($which)
    {
        if ($which !== 'top') {
            return;
        }

        global $wpdb;
        $forms = $wpdb->get_results(
            "SELECT DISTINCT form_id, form_name FROM {$wpdb->prefix}codeweber_forms_submissions ORDER BY form_id DESC"
        );
        
        // НОВОЕ: Получаем список уникальных типов форм из базы данных
        // Используем прямой запрос, так как нет пользовательского ввода
        $form_types = $wpdb->get_col(
            "SELECT DISTINCT form_type FROM {$wpdb->prefix}codeweber_forms_submissions WHERE form_type IS NOT NULL AND form_type != '' ORDER BY form_type ASC"
        );
        
        // Если типов нет в базе, используем пустой массив (фильтр все равно покажется с дефолтными типами)
        if (!is_array($form_types)) {
            $form_types = array();
        }

        $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $form_id = isset($_GET['form_id']) ? sanitize_text_field($_GET['form_id']) : '';
        $form_type = isset($_GET['form_type']) ? sanitize_text_field($_GET['form_type']) : ''; // НОВОЕ: Фильтр по типу формы
        
        // Маппинг типов на читаемые названия
        $type_labels = array(
            'form' => __('Regular Form', 'codeweber'),
            'newsletter' => __('Newsletter Subscription', 'codeweber'),
            'testimonial' => __('Testimonial Form', 'codeweber'),
            'resume' => __('Resume Form', 'codeweber'),
            'callback' => __('Callback Request', 'codeweber'),
        );
        ?>
        <div class="alignleft actions">
            <select name="status">
                <option value=""><?php _e('All statuses', 'codeweber'); ?></option>
                <option value="new" <?php selected($status, 'new'); ?>><?php _e('New', 'codeweber'); ?></option>
                <option value="read" <?php selected($status, 'read'); ?>><?php _e('Read', 'codeweber'); ?></option>
                <option value="archived" <?php selected($status, 'archived'); ?>><?php _e('Archived', 'codeweber'); ?></option>
            </select>
            
            <select name="form_id">
                <option value=""><?php _e('All forms', 'codeweber'); ?></option>
                <?php foreach ($forms as $form): ?>
                    <option value="<?php echo esc_attr($form->form_id); ?>" <?php selected($form_id, (string)$form->form_id); ?>>
                        <?php echo esc_html($form->form_name ?: ($form->form_id == 0 ? __('Testimonial Form', 'codeweber') : $form->form_id)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <select name="form_type">
                <option value=""><?php _e('All form types', 'codeweber'); ?></option>
                <?php if (!empty($form_types)): ?>
                    <?php foreach ($form_types as $type): ?>
                        <option value="<?php echo esc_attr($type); ?>" <?php selected($form_type, $type); ?>>
                            <?php echo esc_html(isset($type_labels[$type]) ? $type_labels[$type] : ucfirst($type)); ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php 
                    // Показываем все возможные типы, даже если их еще нет в базе
                    foreach ($type_labels as $type_key => $type_label): 
                    ?>
                        <option value="<?php echo esc_attr($type_key); ?>" <?php selected($form_type, $type_key); ?>>
                            <?php echo esc_html($type_label); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            
            <?php submit_button(__('Filter', 'codeweber'), 'secondary', 'filter_action', false); ?>
            
            <?php if ($status || $form_id || $form_type): ?>
                <a href="<?php echo admin_url('admin.php?page=codeweber'); ?>" class="button">
                    <?php _e('Reset', 'codeweber'); ?>
                </a>
            <?php endif; ?>
            
            <?php if ($status === 'trash'): ?>
                <form method="post" style="display: inline-block; margin-left: 5px;">
                    <input type="hidden" name="action" value="empty_trash">
                    <?php wp_nonce_field('codeweber_forms_action', 'codeweber_forms_nonce'); ?>
                    <button type="submit" class="button button-secondary button-link-delete"
                        onclick="return confirm('<?php echo esc_js(__('Are you sure you want to permanently delete all submissions from the trash?', 'codeweber')); ?>');">
                        <?php _e('Empty Trash', 'codeweber'); ?>
                    </button>
                </form>
            <?php endif; ?>
        </div>
        <?php
    }
}


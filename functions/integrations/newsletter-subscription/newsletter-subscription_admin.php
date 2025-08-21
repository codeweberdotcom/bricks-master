<?php

/**
 * Newsletter Subscription Admin Module
 */

if (!defined('ABSPATH')) {
   exit;
}

class NewsletterSubscriptionAdmin
{

   private $table_name;
   private $option_name = 'newsletter_subscription_columns';

   public function __construct()
   {
      global $wpdb;
      $this->table_name = $wpdb->prefix . 'newsletter_subscriptions';

      add_action('admin_menu', array($this, 'add_admin_menu'));
      add_action('admin_init', array($this, 'admin_init'));
   }

   public function add_admin_menu()
   {
      add_menu_page(
         __('Newsletter Subscriptions', 'codeweber'),
         __('Subscriptions', 'codeweber'),
         'manage_options',
         'newsletter-subscriptions',
         array($this, 'render_admin_page'),
         'dashicons-email-alt',
         30
      );

      add_submenu_page(
         'newsletter-subscriptions',
         __('Subscription Column Settings', 'codeweber'),
         __('Column Settings', 'codeweber'),
         'manage_options',
         'newsletter-subscriptions-column-settings',
         array($this, 'render_settings_page')
      );

      add_filter('manage_newsletter-subscriptions_page_newsletter-subscriptions_columns', array($this, 'add_user_column'));
      add_action('manage_newsletter-subscriptions_page_newsletter-subscriptions_custom_column', array($this, 'display_user_column'), 10, 2);
   }

   public function add_user_column($columns)
   {
      $new_columns = array();
      foreach ($columns as $key => $value) {
         $new_columns[$key] = $value;
         if ($key === 'email') {
            $new_columns['user_info'] = __('User Account', 'codeweber');
         }
      }
      return $new_columns;
   }

   public function display_user_column($column_name, $item_id)
   {
      if ($column_name === 'user_info') {
         global $wpdb;

         $subscription = $wpdb->get_row($wpdb->prepare(
            "SELECT email FROM {$this->table_name} WHERE id = %d",
            $item_id
         ));

         if ($subscription && !empty($subscription->email)) {
            $user = get_user_by('email', $subscription->email);

            if ($user) {
               echo '<div class="user-info-column">';
               echo '<strong>' . esc_html($user->display_name) . '</strong><br>';
               echo '<small>ID: ' . esc_html($user->ID) . '</small><br>';
               echo '<small>' . implode(', ', $user->roles) . '</small><br>';
               echo '<a href="' . admin_url('user-edit.php?user_id=' . $user->ID) . '" 
                    target="_blank" class="button button-small">' . __('View Profile', 'codeweber') . '</a>';
               echo '</div>';
            } else {
               echo '<span class="description">' . __('No user account', 'codeweber') . '</span>';
            }
         }
      }
   }

   public function admin_init()
   {
      add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));

      register_setting('newsletter_subscription_columns', $this->option_name, array(
         'sanitize_callback' => array($this, 'sanitize_column_settings')
      ));

      add_settings_section(
         'newsletter_columns_section',
         __('Column Display Settings', 'codeweber'),
         array($this, 'columns_section_callback'),
         'newsletter-subscriptions-column-settings'
      );

      $columns = $this->get_available_columns();
      foreach ($columns as $column => $label) {
         add_settings_field(
            $column . '_column',
            $label,
            array($this, 'column_field_callback'),
            'newsletter-subscriptions-column-settings',
            'newsletter_columns_section',
            array('column' => $column, 'label' => $label)
         );
      }

      add_action('admin_post_newsletter_export_csv', array($this, 'handle_export_csv'));
      add_action('admin_post_nopriv_newsletter_export_csv', array($this, 'handle_export_csv'));
   }

   public function sanitize_column_settings($input)
   {
      $available_columns = array_keys($this->get_available_columns());
      $sanitized_input = array();

      // Если ничего не передано, значит все чекбоксы сняты
      if (empty($_POST[$this->option_name])) {
         return array();
      }

      // Обрабатываем только существующие колонки
      foreach ($available_columns as $column) {
         if (isset($_POST[$this->option_name][$column]) && $_POST[$this->option_name][$column] === '1') {
            $sanitized_input[$column] = 1;
         } else {
            $sanitized_input[$column] = 0;
         }
      }

      return $sanitized_input;
   }

   public function column_field_callback($args)
   {
      $options = get_option($this->option_name, array());
      $column = $args['column'];

      // Определяем состояние чекбокса
      $is_checked = true; // По умолчанию включено

      if (is_array($options) && isset($options[$column])) {
         $is_checked = (bool) $options[$column];
      }

      echo '<label>';
      echo '<input type="checkbox" name="' . $this->option_name . '[' . $column . ']" value="1" ' . checked(true, $is_checked, false) . ' />';
      echo ' ' . sprintf(__('Show "%s" column', 'codeweber'), esc_html($args['label']));
      echo '</label>';
   }

   public function render_settings_page()
   {
?>
      <div class="wrap">
         <h1><?php _e('Subscription Column Settings', 'codeweber'); ?></h1>
         <form method="post" action="options.php">
            <?php
            settings_fields('newsletter_subscription_columns');
            do_settings_sections('newsletter-subscriptions-column-settings');
            submit_button();
            ?>
         </form>
      </div>
   <?php
   }

   public function columns_section_callback()
   {
      echo '<p>' . __('Select which columns to display in the subscriptions table:', 'codeweber') . '</p>';
   }

   public function get_available_columns()
   {
      return array(
         'email' => __('Email', 'codeweber'),
         'first_name' => __('First Name', 'codeweber'),
         'last_name' => __('Last Name', 'codeweber'),
         'phone' => __('Phone', 'codeweber'),
         'form_id' => __('Form', 'codeweber'),
         'ip_address' => __('IP Address', 'codeweber'),
         'user_agent' => __('User Agent', 'codeweber'),
         'status' => __('Status', 'codeweber'),
         'created_at' => __('Subscription Date', 'codeweber'),
         'unsubscribed_at' => __('Unsubscribe Date', 'codeweber'),
         'user_info' => __('User Account', 'codeweber'),
         'actions' => __('Actions', 'codeweber')
      );
   }

   public function is_column_enabled($column)
   {
      $options = get_option($this->option_name, array());

      // Если опция не существует, возвращаем true для всех колонок
      if ($options === false) {
         return true;
      }

      // Если опция пуста, значит все чекбоксы сняты
      if (empty($options)) {
         return false;
      }

      // Проверяем конкретную колонку
      return isset($options[$column]) ? (bool) $options[$column] : false;
   }

   public function enqueue_admin_styles($hook)
   {
      if ($hook !== 'toplevel_page_newsletter-subscriptions' && $hook !== 'newsletter-subscriptions_page_newsletter-subscriptions-column-settings') {
         return;
      }

      wp_enqueue_style(
         'newsletter-subscription-admin',
         get_template_directory_uri() . '/functions/integrations/newsletter-subscription/newsletter-subscription-admin.css',
         array(),
         '1.0.0'
      );
   }

   public function render_admin_page()
   {
      global $wpdb;

      $this->handle_actions();

      $per_page = 20;
      $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
      $offset = ($current_page - 1) * $per_page;

      $where = '';
      $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
      $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
      $form_filter = isset($_GET['form_id']) ? sanitize_text_field($_GET['form_id']) : '';

      if ($search) {
         $where .= $wpdb->prepare(
            " AND (email LIKE %s OR first_name LIKE %s OR last_name LIKE %s)",
            '%' . $wpdb->esc_like($search) . '%',
            '%' . $wpdb->esc_like($search) . '%',
            '%' . $wpdb->esc_like($search) . '%'
         );
      }

      if ($status && in_array($status, ['pending', 'confirmed', 'unsubscribed'])) {
         $where .= $wpdb->prepare(" AND status = %s", $status);
      }

      if ($form_filter) {
         $where .= $wpdb->prepare(" AND form_id = %s", $form_filter);
      }

      $total_items = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE 1=1 {$where}");
      $total_pages = ceil($total_items / $per_page);

      $subscriptions = $wpdb->get_results(
         "SELECT * FROM {$this->table_name} WHERE 1=1 {$where} 
          ORDER BY created_at DESC LIMIT {$per_page} OFFSET {$offset}"
      );

      $forms = $wpdb->get_col("SELECT DISTINCT form_id FROM {$this->table_name} ORDER BY form_id");

      $enabled_columns = array();
      $available_columns = $this->get_available_columns();
      foreach ($available_columns as $column => $label) {
         if ($this->is_column_enabled($column)) {
            $enabled_columns[$column] = $label;
         }
      }

   ?>
      <div class="wrap">
         <h1><?php _e('Newsletter Subscriptions', 'codeweber'); ?></h1>

         <?php settings_errors('newsletter_messages'); ?>

         <div class="newsletter-admin-filters">
            <form method="get">
               <input type="hidden" name="page" value="newsletter-subscriptions">

               <div class="tablenav top">
                  <div class="alignleft actions">
                     <select name="status" class="newsletter-status-filter">
                        <option value=""><?php _e('All statuses', 'codeweber'); ?></option>
                        <option value="pending" <?php selected($status, 'pending'); ?>><?php _e('Pending', 'codeweber'); ?></option>
                        <option value="confirmed" <?php selected($status, 'confirmed'); ?>><?php _e('Confirmed', 'codeweber'); ?></option>
                        <option value="unsubscribed" <?php selected($status, 'unsubscribed'); ?>><?php _e('Unsubscribed', 'codeweber'); ?></option>
                     </select>

                     <select name="form_id" class="newsletter-form-filter">
                        <option value=""><?php _e('All forms', 'codeweber'); ?></option>
                        <?php foreach ($forms as $form): ?>
                           <option value="<?php echo esc_attr($form); ?>" <?php selected($form_filter, $form); ?>>
                              <?php echo esc_html($this->get_form_label($form)); ?>
                           </option>
                        <?php endforeach; ?>
                     </select>

                     <input type="text" name="s" value="<?php echo esc_attr($search); ?>"
                        placeholder="<?php _e('Search by email or name', 'codeweber'); ?>" class="newsletter-search">

                     <input type="submit" class="button" value="<?php _e('Filter', 'codeweber'); ?>">

                     <?php if ($search || $status || $form_filter): ?>
                        <a href="<?php echo admin_url('admin.php?page=newsletter-subscriptions'); ?>"
                           class="button"><?php _e('Reset', 'codeweber'); ?></a>
                     <?php endif; ?>

                     <a href="<?php echo admin_url('admin.php?page=newsletter-subscriptions-column-settings'); ?>"
                        class="button button-secondary"><?php _e('Column Settings', 'codeweber'); ?></a>
                  </div>
               </div>
            </form>
         </div>

         <table class="wp-list-table widefat fixed striped newsletter-subscriptions-table">
            <thead>
               <tr>
                  <?php foreach ($enabled_columns as $column => $label): ?>
                     <th><?php echo esc_html($label); ?></th>
                  <?php endforeach; ?>
               </tr>
            </thead>
            <tbody>
               <?php if ($subscriptions): ?>
                  <?php foreach ($subscriptions as $subscription): ?>
                     <tr>
                        <?php foreach ($enabled_columns as $column => $label): ?>
                           <td>
                              <?php echo $this->render_column_content($column, $subscription); ?>
                           </td>
                        <?php endforeach; ?>
                     </tr>
                  <?php endforeach; ?>
               <?php else: ?>
                  <tr>
                     <td colspan="<?php echo count($enabled_columns); ?>" style="text-align: center;">
                        <?php _e('No subscriptions found', 'codeweber'); ?>
                     </td>
                  </tr>
               <?php endif; ?>
            </tbody>
         </table>

         <?php if ($total_pages > 1): ?>
            <div class="tablenav bottom">
               <div class="tablenav-pages">
                  <?php
                  echo paginate_links(array(
                     'base' => add_query_arg('paged', '%#%'),
                     'format' => '',
                     'prev_text' => '&laquo;',
                     'next_text' => '&raquo;',
                     'total' => $total_pages,
                     'current' => $current_page
                  ));
                  ?>
               </div>
            </div>
         <?php endif; ?>

         <div class="newsletter-export">
            <h2><?php _e('Export Subscriptions', 'codeweber'); ?></h2>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
               <input type="hidden" name="action" value="newsletter_export_csv">
               <?php wp_nonce_field('newsletter_export_csv', 'newsletter_export_nonce'); ?>
               <select name="export_status">
                  <option value="all"><?php _e('All subscriptions', 'codeweber'); ?></option>
                  <option value="confirmed"><?php _e('Active only', 'codeweber'); ?></option>
                  <option value="unsubscribed"><?php _e('Unsubscribed only', 'codeweber'); ?></option>
               </select>
               <select name="export_form">
                  <option value="all"><?php _e('All forms', 'codeweber'); ?></option>
                  <?php foreach ($forms as $form): ?>
                     <option value="<?php echo esc_attr($form); ?>">
                        <?php echo esc_html($this->get_form_label($form)); ?>
                     </option>
                  <?php endforeach; ?>
               </select>
               <button type="submit" class="button button-primary"><?php _e('Export to CSV', 'codeweber'); ?></button>
            </form>
         </div>
      </div>
      <?php
   }

   private function render_column_content($column, $subscription)
   {
      switch ($column) {
         case 'email':
            return esc_html($subscription->email);

         case 'first_name':
            return esc_html($subscription->first_name);

         case 'last_name':
            return esc_html($subscription->last_name);

         case 'phone':
            return esc_html($subscription->phone);

         case 'form_id':
            return '<span class="newsletter-form-id" title="' . esc_attr($subscription->form_id) . '">' .
               esc_html($this->get_form_label($subscription->form_id)) . '</span>';

         case 'ip_address':
            return esc_html($subscription->ip_address);

         case 'user_agent':
            return '<span title="' . esc_attr($subscription->user_agent) . '">' .
               esc_html(mb_substr($subscription->user_agent, 0, 30) . (mb_strlen($subscription->user_agent) > 30 ? '...' : '')) . '</span>';

         case 'status':
            return '<span class="newsletter-status status-' . esc_attr($subscription->status) . '">' .
               $this->get_status_label($subscription->status) . '</span>';

         case 'created_at':
            return date('d.m.Y H:i', strtotime($subscription->created_at));

         case 'unsubscribed_at':
            if ($subscription->unsubscribed_at && $subscription->unsubscribed_at !== '0000-00-00 00:00:00') {
               return date('d.m.Y H:i', strtotime($subscription->unsubscribed_at));
            } else {
               return '—';
            }

         case 'user_info':
            $user = get_user_by('email', $subscription->email);

            if ($user) {
               ob_start();
      ?>
               <div class="user-info-column">
                  <strong><?php echo esc_html($user->display_name); ?></strong><br>
                  <small>ID: <?php echo esc_html($user->ID); ?></small><br>
                  <small><?php echo implode(', ', $user->roles); ?></small><br>
                  <a href="<?php echo admin_url('user-edit.php?user_id=' . $user->ID); ?>"
                     target="_blank" class="button button-small"><?php _e('View Profile', 'codeweber'); ?></a>
               </div>
            <?php
               return ob_get_clean();
            } else {
               return '<span class="description">' . __('No user account', 'codeweber') . '</span>';
            }

         case 'actions':
            ob_start();
            ?>
            <div class="newsletter-actions">
               <?php if ($subscription->status !== 'unsubscribed'): ?>
                  <form method="post" style="display:inline;">
                     <input type="hidden" name="action" value="unsubscribe">
                     <input type="hidden" name="email" value="<?php echo esc_attr($subscription->email); ?>">
                     <?php wp_nonce_field('newsletter_admin_action', 'newsletter_nonce'); ?>
                     <button type="submit" class="button button-small"
                        onclick="return confirm('<?php _e('Are you sure you want to unsubscribe this user?', 'codeweber'); ?>')">
                        <?php _e('Unsubscribe', 'codeweber'); ?>
                     </button>
                  </form>
               <?php endif; ?>

               <form method="post" style="display:inline;">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="email" value="<?php echo esc_attr($subscription->email); ?>">
                  <?php wp_nonce_field('newsletter_admin_action', 'newsletter_nonce'); ?>
                  <button type="submit" class="button button-small button-link-delete"
                     onclick="return confirm('<?php _e('Are you sure you want to delete this subscription?', 'codeweber'); ?>')">
                     <?php _e('Delete', 'codeweber'); ?>
                  </button>
               </form>
            </div>
<?php
            return ob_get_clean();

         default:
            return '';
      }
   }

   private function handle_actions()
   {
      if (!isset($_POST['action']) || !check_admin_referer('newsletter_admin_action', 'newsletter_nonce')) {
         return;
      }

      global $wpdb;
      $action = $_POST['action'];
      $email = sanitize_email($_POST['email']);

      switch ($action) {
         case 'unsubscribe':
            $result = $wpdb->update($this->table_name, array(
               'status' => 'unsubscribed',
               'unsubscribed_at' => current_time('mysql'),
               'updated_at' => current_time('mysql')
            ), array('email' => $email));

            if ($result) {
               add_settings_error(
                  'newsletter_messages',
                  'newsletter_message',
                  __('User successfully unsubscribed from newsletter', 'codeweber'),
                  'success'
               );
            }
            break;

         case 'delete':
            $result = $wpdb->delete($this->table_name, array('email' => $email));

            if ($result) {
               add_settings_error(
                  'newsletter_messages',
                  'newsletter_message',
                  __('Subscription successfully deleted', 'codeweber'),
                  'success'
               );
            }
            break;
      }
   }

   public function handle_export_csv()
   {
      if (!wp_verify_nonce($_POST['newsletter_export_nonce'], 'newsletter_export_csv')) {
         wp_die(__('Invalid export link', 'codeweber'));
      }

      if (!current_user_can('manage_options')) {
         wp_die(__('Insufficient permissions for export', 'codeweber'));
      }

      global $wpdb;

      $status = $_POST['export_status'] ?? 'all';
      $form = $_POST['export_form'] ?? 'all';

      $where = " WHERE 1=1";

      if ($status === 'confirmed') {
         $where .= " AND status = 'confirmed'";
      } elseif ($status === 'unsubscribed') {
         $where .= " AND status = 'unsubscribed'";
      }

      if ($form !== 'all') {
         $where .= $wpdb->prepare(" AND form_id = %s", $form);
      }

      $subscriptions = $wpdb->get_results("SELECT * FROM {$this->table_name}{$where} ORDER BY created_at DESC");

      header('Content-Type: text/csv; charset=utf-8');
      header('Content-Disposition: attachment; filename=newsletter-subscriptions-' . date('Y-m-d') . '.csv');
      header('Pragma: no-cache');
      header('Expires: 0');

      $output = fopen('php://output', 'w');
      fwrite($output, "\xEF\xBB\xBF");

      fputcsv($output, array(
         __('Email', 'codeweber'),
         __('First Name', 'codeweber'),
         __('Last Name', 'codeweber'),
         __('Phone', 'codeweber'),
         __('Form', 'codeweber'),
         __('IP Address', 'codeweber'),
         __('User Agent', 'codeweber'),
         __('Status', 'codeweber'),
         __('Subscription Date', 'codeweber'),
         __('Unsubscribe Date', 'codeweber')
      ), ';');

      foreach ($subscriptions as $subscription) {
         fputcsv($output, array(
            $subscription->email,
            $subscription->first_name,
            $subscription->last_name,
            $subscription->phone,
            $this->get_form_label($subscription->form_id),
            $subscription->ip_address,
            $subscription->user_agent,
            $this->get_status_label($subscription->status),
            $subscription->created_at,
            $subscription->unsubscribed_at !== '0000-00-00 00:00:00' ? $subscription->unsubscribed_at : ''
         ), ';');
      }

      fclose($output);
      exit;
   }

   private function get_status_label($status)
   {
      $labels = array(
         'pending' => __('Pending', 'codeweber'),
         'confirmed' => __('Confirmed', 'codeweber'),
         'unsubscribed' => __('Unsubscribed', 'codeweber')
      );

      return $labels[$status] ?? $status;
   }

   private function get_form_label($form_id)
   {
      $form_labels = array(
         'default' => __('Subscription Form|Email', 'codeweber'),
         'cf7_1072' => __('Contact Form 7: Request Callback', 'codeweber')
      );

      if (strpos($form_id, 'cf7_') === 0) {
         $form_parts = explode('_', $form_id);
         if (count($form_parts) >= 2 && is_numeric($form_parts[1])) {
            $form_title = get_the_title($form_parts[1]);
            if ($form_title && !empty($form_title)) {
               return 'CF7: ' . $form_title;
            }
         }
      }

      return $form_labels[$form_id] ?? $form_id;
   }
}

new NewsletterSubscriptionAdmin();

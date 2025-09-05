<?php

/**
 * Newsletter Subscription Admin Class
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

      add_submenu_page(
         'newsletter-subscriptions',
         __('Import Subscribers', 'codeweber'),
         __('Import', 'codeweber'),
         'manage_options',
         'newsletter-subscriptions-import',
         array($this, 'render_import_page')
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

      add_action('admin_post_newsletter_import_csv', array($this, 'handle_import_csv'));
      add_action('admin_post_nopriv_newsletter_import_csv', array($this, 'handle_import_csv'));
   }

   public function sanitize_column_settings($input)
   {
      $available_columns = array_keys($this->get_available_columns());
      $sanitized_input = array();

      if (empty($_POST[$this->option_name])) {
         return array();
      }

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

      $is_checked = true;
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

      if ($options === false) {
         return true;
      }

      if (empty($options)) {
         return false;
      }

      return isset($options[$column]) ? (bool) $options[$column] : false;
   }

   public function enqueue_admin_styles($hook)
   {
      if ($hook !== 'toplevel_page_newsletter-subscriptions' && $hook !== 'newsletter-subscriptions_page_newsletter-subscriptions-column-settings' && $hook !== 'newsletter-subscriptions_page_newsletter-subscriptions-import') {
         return;
      }

      wp_enqueue_style(
         'newsletter-subscription-admin',
         get_template_directory_uri() . '/functions/integrations/newsletter-subscription/admin/css/admin.css',
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
                        <option value='unsubscribed' <?php selected($status, 'unsubscribed'); ?>><?php _e('Unsubscribed', 'codeweber'); ?></option>
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

                     <a href="<?php echo admin_url('admin.php?page=newsletter-subscriptions-import'); ?>"
                        class="button button-secondary"><?php _e('Import Subscribers', 'codeweber'); ?></a>
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
                  <strong><a href="<?php echo admin_url('user-edit.php?user_id=' . $user->ID); ?>"
                        target="_blank"><?php echo esc_html($user->display_name); ?></a></strong><br>
                  <small>ID: <?php echo esc_html($user->ID); ?></small><br>
                  <small><?php echo implode(', ', $user->roles); ?></small><br>
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
      } elseif ($status === 'pending') {
         $where .= " AND status = 'pending'";
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
         'email',
         'first_name',
         'last_name',
         'phone',
         'form_id',
         'ip_address',
         'user_agent',
         'status',
         'created_at',
         'unsubscribed_at'
      ), ';');

      foreach ($subscriptions as $subscription) {
         fputcsv($output, array(
            $subscription->email,
            $subscription->first_name,
            $subscription->last_name,
            $subscription->phone,
            $subscription->form_id,
            $subscription->ip_address,
            $subscription->user_agent,
            $subscription->status,
            $subscription->created_at,
            $subscription->unsubscribed_at !== '0000-00-00 00:00:00' ? $subscription->unsubscribed_at : ''
         ), ';');
      }

      fclose($output);
      exit;
   }

   public function render_import_page()
   {
      $import_results = get_transient('newsletter_import_results');
      delete_transient('newsletter_import_results');
      ?>
      <div class="wrap">
         <h1><?php _e('Import Subscribers', 'codeweber'); ?></h1>

         <?php if ($import_results): ?>
            <div class="notice notice-<?php echo $import_results['success'] ? 'success' : 'error'; ?>">
               <p><?php echo esc_html($import_results['message']); ?></p>
               <?php if (!empty($import_results['details'])): ?>
                  <ul>
                     <?php foreach ($import_results['details'] as $detail): ?>
                        <li><?php echo esc_html($detail); ?></li>
                     <?php endforeach; ?>
                  </ul>
               <?php endif; ?>
            </div>
         <?php endif; ?>

         <div class="card" style="min-width: 100%;">
            <h2><?php _e('CSV Import', 'codeweber'); ?></h2>
            <p><?php _e('Import subscribers from a CSV file. The file should have the following columns (email is required):', 'codeweber'); ?></p>

            <ul>
               <li><strong>email</strong> - <?php _e('Email address (required)', 'codeweber'); ?></li>
               <li><strong>first_name</strong> - <?php _e('First name', 'codeweber'); ?></li>
               <li><strong>last_name</strong> - <?php _e('Last name', 'codeweber'); ?></li>
               <li><strong>phone</strong> - <?php _e('Phone number', 'codeweber'); ?></li>
               <li><strong>form_id</strong> - <?php _e('Form identifier', 'codeweber'); ?></li>
               <li><strong>ip_address</strong> - <?php _e('IP address', 'codeweber'); ?></li>
               <li><strong>user_agent</strong> - <?php _e('User agent/browser info', 'codeweber'); ?></li>
               <li><strong>status</strong> - <?php _e('Status (confirmed/unsubscribed/pending)', 'codeweber'); ?></li>
               <li><strong>created_at</strong> - <?php _e('Subscription date (YYYY-MM-DD HH:MM:SS)', 'codeweber'); ?></li>
               <li><strong>unsubscribed_at</strong> - <?php _e('Unsubscribe date (YYYY-MM-DD HH:MM:SS)', 'codeweber'); ?></li>
            </ul>

            <p><strong><?php _e('Supported date formats:', 'codeweber'); ?></strong></p>
            <ul>
               <li>YYYY-MM-DD HH:MM:SS (2024-01-15 14:30:00)</li>
               <li>DD.MM.YYYY HH:MM:SS (15.01.2024 14:30:00)</li>
               <li>MM/DD/YYYY HH:MM:SS (01/15/2024 14:30:00)</li>
               <li>DD/MM/YYYY HH:MM:SS (15/01/2024 14:30:00)</li>
               <li>Unix timestamp (1705332600)</li>
               <li><?php _e('Any other format recognized by strtotime()', 'codeweber'); ?></li>
            </ul>

            <p><strong><?php _e('Example CSV structure:', 'codeweber'); ?></strong></p>
            <pre>email;first_name;last_name;phone;form_id;ip_address;user_agent;status;created_at;unsubscribed_at
user1@example.com;John;Doe;+123456789;imported;192.168.1.1;Mozilla/5.0;confirmed;2024-01-15 14:30:00;
user2@example.com;Jane;Smith;;imported;192.168.1.2;Chrome/120.0.0.0;unsubscribed;2024-01-10 10:00:00;2024-01-15 16:00:00</pre>

            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
               <input type="hidden" name="action" value="newsletter_import_csv">
               <?php wp_nonce_field('newsletter_import_csv', 'newsletter_import_nonce'); ?>

               <table class="form-table">
                  <tr>
                     <th scope="row">
                        <label for="csv_file"><?php _e('CSV File', 'codeweber'); ?></label>
                     </th>
                     <td>
                        <input type="file" name="csv_file" id="csv_file" accept=".csv,.txt" required>
                        <p class="description">
                           <?php _e('Select a CSV file to import. Maximum file size:', 'codeweber'); ?>
                           <?php echo size_format(wp_max_upload_size()); ?>
                        </p>
                     </td>
                  </tr>
                  <tr>
                     <th scope="row">
                        <label for="import_status"><?php _e('Default Status', 'codeweber'); ?></label>
                     </th>
                     <td>
                        <select name="import_status" id="import_status">
                           <option value="confirmed"><?php _e('Confirmed', 'codeweber'); ?></option>
                           <option value="unsubscribed"><?php _e('Unsubscribed', 'codeweber'); ?></option>
                           <option value="pending"><?php _e('Pending', 'codeweber'); ?></option>
                        </select>
                        <p class="description">
                           <?php _e('Status for subscribers without status field in CSV', 'codeweber'); ?>
                        </p>
                     </td>
                  </tr>
                  <tr>
                     <th scope="row">
                        <label for="import_form"><?php _e('Form ID', 'codeweber'); ?></label>
                     </th>
                     <td>
                        <input type="text" name="import_form" id="import_form" value="imported" placeholder="imported">
                        <p class="description">
                           <?php _e('Form identifier for imported subscribers', 'codeweber'); ?>
                        </p>
                     </td>
                  </tr>
                  <tr>
                     <th scope="row">
                        <label for="skip_duplicates"><?php _e('Duplicate Handling', 'codeweber'); ?></label>
                     </th>
                     <td>
                        <label>
                           <input type="checkbox" name="skip_duplicates" id="skip_duplicates" value="1" checked>
                           <?php _e('Skip duplicate emails (keep existing)', 'codeweber'); ?>
                        </label>
                        <p class="description">
                           <?php _e('If unchecked, duplicates will be updated with new data', 'codeweber'); ?>
                        </p>
                     </td>
                  </tr>
               </table>

               <p class="submit">
                  <button type="submit" class="button button-primary">
                     <?php _e('Import Subscribers', 'codeweber'); ?>
                  </button>
               </p>
            </form>
         </div>
      </div>
<?php
   }

   public function handle_import_csv()
   {
      if (!wp_verify_nonce($_POST['newsletter_import_nonce'], 'newsletter_import_csv')) {
         wp_die(__('Invalid import request', 'codeweber'));
      }

      if (!current_user_can('manage_options')) {
         wp_die(__('Insufficient permissions for import', 'codeweber'));
      }

      if (empty($_FILES['csv_file']['tmp_name'])) {
         $this->set_import_result(false, __('No file uploaded', 'codeweber'));
         wp_redirect(admin_url('admin.php?page=newsletter-subscriptions-import'));
         exit;
      }

      $file = $_FILES['csv_file']['tmp_name'];
      $default_status = sanitize_text_field($_POST['import_status']);
      $default_form = sanitize_text_field($_POST['import_form']);
      $skip_duplicates = isset($_POST['skip_duplicates']);

      $file_ext = strtolower(pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION));
      if (!in_array($file_ext, ['csv', 'txt'])) {
         $this->set_import_result(false, __('Invalid file format. Please upload a CSV file.', 'codeweber'));
         wp_redirect(admin_url('admin.php?page=newsletter-subscriptions-import'));
         exit;
      }

      $handle = fopen($file, 'r');
      if (!$handle) {
         $this->set_import_result(false, __('Cannot open uploaded file', 'codeweber'));
         wp_redirect(admin_url('admin.php?page=newsletter-subscriptions-import'));
         exit;
      }

      $headers = fgetcsv($handle, 0, ';');
      if (!$headers) {
         fclose($handle);
         $this->set_import_result(false, __('Invalid CSV format', 'codeweber'));
         wp_redirect(admin_url('admin.php?page=newsletter-subscriptions-import'));
         exit;
      }

      $headers = array_map('strtolower', $headers);
      $headers = array_map('trim', $headers);
      $headers = array_map(function ($header) {
         $mapping = [
            'email' => 'email',
            'e-mail' => 'email',
            'mail' => 'email',
            'first_name' => 'first_name',
            'firstname' => 'first_name',
            'name' => 'first_name',
            'last_name' => 'last_name',
            'lastname' => 'last_name',
            'surname' => 'last_name',
            'phone' => 'phone',
            'telephone' => 'phone',
            'mobile' => 'phone',
            'form_id' => 'form_id',
            'form' => 'form_id',
            'ip_address' => 'ip_address',
            'ip' => 'ip_address',
            'user_agent' => 'user_agent',
            'browser' => 'user_agent',
            'status' => 'status',
            'created_at' => 'created_at',
            'date' => 'created_at',
            'subscription_date' => 'created_at',
            'unsubscribed_at' => 'unsubscribed_at',
            'unsubscribe_date' => 'unsubscribed_at'
         ];
         return $mapping[$header] ?? $header;
      }, $headers);

      if (!in_array('email', $headers)) {
         fclose($handle);
         $this->set_import_result(false, __('CSV file must contain "email" column', 'codeweber'));
         wp_redirect(admin_url('admin.php?page=newsletter-subscriptions-import'));
         exit;
      }

      global $wpdb;
      $imported = 0;
      $updated = 0;
      $skipped = 0;
      $errors = array();

      $row_number = 1;
      while (($row = fgetcsv($handle, 0, ';')) !== false) {
         $row_number++;

         if (count($row) !== count($headers)) {
            $errors[] = sprintf(__('Row %d: incorrect number of columns', 'codeweber'), $row_number);
            continue;
         }

         $data = array_combine($headers, $row);

         $email = sanitize_email($data['email']);
         if (!is_email($email)) {
            $errors[] = sprintf(__('Row %d: invalid email address: %s', 'codeweber'), $row_number, $data['email']);
            continue;
         }

         $subscription_data = array(
            'email' => $email,
            'first_name' => isset($data['first_name']) ? sanitize_text_field($data['first_name']) : '',
            'last_name' => isset($data['last_name']) ? sanitize_text_field($data['last_name']) : '',
            'phone' => isset($data['phone']) ? sanitize_text_field($data['phone']) : '',
            'form_id' => isset($data['form_id']) ? sanitize_text_field($data['form_id']) : $default_form,
            'ip_address' => isset($data['ip_address']) ? sanitize_text_field($data['ip_address']) : '',
            'user_agent' => isset($data['user_agent']) ? sanitize_text_field($data['user_agent']) : 'imported',
            'status' => isset($data['status']) ? $this->validate_status($data['status']) : $default_status,
            'unsubscribe_token' => $this->generate_unsubscribe_token($email)
         );

         if (isset($data['created_at']) && !empty($data['created_at'])) {
            $created_at = $this->parse_date($data['created_at']);
            if ($created_at) {
               $subscription_data['created_at'] = $created_at;
            }
         }

         if (isset($data['unsubscribed_at']) && !empty($data['unsubscribed_at'])) {
            $unsubscribed_at = $this->parse_date($data['unsubscribed_at']);
            if ($unsubscribed_at) {
               $subscription_data['unsubscribed_at'] = $unsubscribed_at;
            }
         }

         $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE email = %s",
            $email
         ));

         if ($existing) {
            if ($skip_duplicates) {
               $skipped++;
               continue;
            } else {
               $subscription_data['updated_at'] = current_time('mysql');

               if (!isset($subscription_data['created_at'])) {
                  $subscription_data['created_at'] = $existing->created_at;
               }
               if (!isset($subscription_data['unsubscribed_at']) && $existing->unsubscribed_at !== '0000-00-00 00:00:00') {
                  $subscription_data['unsubscribed_at'] = $existing->unsubscribed_at;
               }

               $result = $wpdb->update($this->table_name, $subscription_data, array('email' => $email));
               if ($result !== false) {
                  $updated++;
               } else {
                  $errors[] = sprintf(__('Row %d: failed to update subscription', 'codeweber'), $row_number);
               }
            }
         } else {
            if (!isset($subscription_data['created_at'])) {
               $subscription_data['created_at'] = current_time('mysql');
            }

            $subscription_data['confirmed_at'] = ($subscription_data['status'] === 'confirmed') ?
               (isset($subscription_data['created_at']) ? $subscription_data['created_at'] : current_time('mysql')) :
               null;

            $subscription_data['updated_at'] = current_time('mysql');

            if ($subscription_data['status'] === 'confirmed' && isset($subscription_data['unsubscribed_at'])) {
               $subscription_data['unsubscribed_at'] = null;
            }

            $result = $wpdb->insert($this->table_name, $subscription_data);
            if ($result) {
               $imported++;
            } else {
               $errors[] = sprintf(__('Row %d: failed to create subscription', 'codeweber'), $row_number);
            }
         }
      }

      fclose($handle);

      $message = sprintf(
         __('Import completed: %d imported, %d updated, %d skipped', 'codeweber'),
         $imported,
         $updated,
         $skipped
      );

      $this->set_import_result(true, $message, $errors);
      wp_redirect(admin_url('admin.php?page=newsletter-subscriptions-import'));
      exit;
   }

   private function parse_date($date_string)
   {
      if (empty($date_string)) {
         return null;
      }

      $formats = [
         'Y-m-d H:i:s',
         'Y-m-d H:i',
         'Y-m-d',
         'd.m.Y H:i:s',
         'd.m.Y H:i',
         'd.m.Y',
         'm/d/Y H:i:s',
         'm/d/Y H:i',
         'm/d/Y',
         'd/m/Y H:i:s',
         'd/m/Y H:i',
         'd/m/Y',
         'Ymd His',
         'Ymd',
         'U'
      ];

      foreach ($formats as $format) {
         $date = DateTime::createFromFormat($format, $date_string);
         if ($date !== false) {
            return $date->format('Y-m-d H:i:s');
         }
      }

      $timestamp = strtotime($date_string);
      if ($timestamp !== false) {
         return date('Y-m-d H:i:s', $timestamp);
      }

      return null;
   }

   private function validate_status($status)
   {
      $status = strtolower(trim($status));
      $valid_statuses = array('pending', 'confirmed', 'unsubscribed');

      return in_array($status, $valid_statuses) ? $status : 'confirmed';
   }

   private function generate_unsubscribe_token($email)
   {
      return wp_hash($email . 'unsubscribe_salt' . time() . wp_rand());
   }

   private function set_import_result($success, $message, $details = array())
   {
      set_transient('newsletter_import_results', array(
         'success' => $success,
         'message' => $message,
         'details' => $details
      ), 30);
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
         'cf7_1072' => __('Contact Form 7: Request Callback', 'codeweber'),
         'imported' => __('Imported', 'codeweber')
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

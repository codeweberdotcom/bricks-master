<?php

/**
 * Newsletter Subscription List Table Class
 * Extends WP_List_Table for bulk actions support
 */

if (!defined('ABSPATH')) {
   exit;
}

if (!class_exists('WP_List_Table')) {
   require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Newsletter_Subscription_List_Table extends WP_List_Table
{
   private $table_name;
   private $admin_instance;

   public function __construct($admin_instance)
   {
      global $wpdb;
      $this->table_name = $wpdb->prefix . 'newsletter_subscriptions';
      $this->admin_instance = $admin_instance;

      parent::__construct(array(
         'singular' => __('Subscription', 'codeweber'),
         'plural' => __('Subscriptions', 'codeweber'),
         'ajax' => false
      ));
   }

   /**
    * Get columns
    * Note: This should return ALL columns (including hidden ones)
    * WordPress will hide them via CSS based on user preferences
    * WP_List_Table automatically registers this method via filter: manage_{$screen->id}_columns
    */
   public function get_columns()
   {
      $columns = array(
         'cb' => '<input type="checkbox" />',
      );

      $available_columns = $this->admin_instance->get_available_columns();
      
      // Add all available columns except cb (which is already added)
      foreach ($available_columns as $column => $label) {
         if ($column !== 'cb') {
            $columns[$column] = $label;
         }
      }
      
      // Return all columns - WordPress will handle hiding via CSS based on user preferences
      return $columns;
   }

   /**
    * Get sortable columns
    */
   protected function get_sortable_columns()
   {
      $sortable = array();
      
      // Get all available columns except 'actions', 'cb', and computed columns
      $available_columns = $this->admin_instance->get_available_columns();
      
      // Columns that cannot be sorted (computed or not in database)
      $non_sortable = array('actions', 'cb', 'user_info');
      
      foreach ($available_columns as $column => $label) {
         // Skip non-sortable columns
         if (in_array($column, $non_sortable)) {
            continue;
         }
         
         // Add sortable column
         // For date columns, set initial sort to descending (true)
         // For other columns, set to ascending (false)
         $is_date = ($column === 'created_at' || $column === 'unsubscribed_at' || $column === 'confirmed_at');
         $sortable[$column] = array($column, $is_date);
      }
      
      return $sortable;
   }

   /**
    * Get bulk actions
    */
   protected function get_bulk_actions()
   {
      $actions = array();
      
      // Check if we're viewing trash
      $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
      
      if ($status === 'trash') {
         // Actions for trash view
         $actions['untrash'] = 'Восстановить';
         $actions['delete'] = 'Удалить навсегда';
      } else {
         // Actions for normal view
         $actions['unsubscribe'] = 'Отписаться';
         $actions['trash'] = 'В корзину';
         $actions['confirm'] = 'Подтвердить';
         $actions['setpending'] = 'Установить статус "Ожидание"';
      }
      
      return $actions;
   }

   /**
    * Column default
    */
   protected function column_default($item, $column_name)
   {
      // Check if there's a specific column method
      $method = 'column_' . $column_name;
      if (method_exists($this, $method)) {
         return call_user_func(array($this, $method), $item);
      }
      
      // Use admin instance render method
      $content = $this->admin_instance->render_column_content($column_name, $item);
      
      // Return content or empty string if null
      return $content !== null ? $content : '';
   }

   /**
    * Column checkbox
    */
   protected function column_cb($item)
   {
      return sprintf(
         '<input type="checkbox" name="subscription[]" value="%s" />',
         esc_attr($item->email)
      );
   }

   /**
    * Column email
    */
   protected function column_email($item)
   {
      return esc_html($item->email);
   }

   /**
    * Column actions
    */
   protected function column_actions($item)
   {
      // Use admin instance method which handles trash logic
      return $this->admin_instance->render_column_content('actions', $item);
   }

   /**
    * Prepare items
    */
   public function prepare_items()
   {
      global $wpdb;

      $per_page = $this->get_items_per_page('subscriptions_per_page', 20);
      $current_page = $this->get_pagenum();

      // Get search, status, and form filters
      $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
      $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
      $form_filter = isset($_GET['form_id']) ? sanitize_text_field($_GET['form_id']) : '';

      // Build WHERE clause
      $where = ' WHERE 1=1';

      if ($search) {
         $where .= $wpdb->prepare(
            " AND (email LIKE %s OR first_name LIKE %s OR last_name LIKE %s)",
            '%' . $wpdb->esc_like($search) . '%',
            '%' . $wpdb->esc_like($search) . '%',
            '%' . $wpdb->esc_like($search) . '%'
         );
      }

      if ($status && in_array($status, ['pending', 'confirmed', 'unsubscribed', 'trash'])) {
         $where .= $wpdb->prepare(" AND status = %s", $status);
      } else {
         // By default, exclude trash from normal view
         $where .= " AND status != 'trash'";
      }

      if ($form_filter) {
         $where .= $wpdb->prepare(" AND form_id = %s", $form_filter);
      }

      // Get total items
      $total_items = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}{$where}");

      // Get sort order
      $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'created_at';
      $order = isset($_GET['order']) && strtoupper($_GET['order']) === 'ASC' ? 'ASC' : 'DESC';

      // Validate orderby - allow all database columns except cb, actions, and computed columns
      $available_columns = $this->admin_instance->get_available_columns();
      $allowed_orderby = array_keys($available_columns);
      // Remove non-sortable columns (cb, actions, user_info - computed column)
      $allowed_orderby = array_diff($allowed_orderby, array('cb', 'actions', 'user_info'));
      
      if (!in_array($orderby, $allowed_orderby)) {
         $orderby = 'created_at';
      }

      // Sanitize order
      $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
      
      // Sanitize column name to prevent SQL injection
      $orderby = preg_replace('/[^a-z0-9_]/i', '', $orderby);

      // Get items
      $offset = ($current_page - 1) * $per_page;
      $query = "SELECT * FROM {$this->table_name}{$where} ORDER BY {$orderby} {$order} LIMIT {$per_page} OFFSET {$offset}";
      $items = $wpdb->get_results($query, OBJECT);
      
      
      // Ensure items is an array and convert to objects if needed
      if (!is_array($items)) {
         $this->items = array();
      } else {
         // Ensure all items are objects
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
      
      // Explicitly set column headers
      $columns = $this->get_columns();
      // Get hidden columns from user preferences (WordPress Screen Options)
      $hidden = get_hidden_columns($this->screen);
      $sortable = $this->get_sortable_columns();
      
      // Get primary column name (default to email if method doesn't exist)
      if (method_exists($this, 'get_primary_column_name')) {
         $primary = $this->get_primary_column_name();
      } else {
         $primary = 'email';
      }
      
      $this->_column_headers = array($columns, $hidden, $sortable, $primary);
      
   }

   /**
    * Get views (All, Pending, Confirmed, Unsubscribed, Trash)
    */
   protected function get_views()
   {
      global $wpdb;
      
      $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
      
      $views = array();
      
      // All (excluding trash)
      $all_count = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE status != 'trash'");
      $class = ($status === '') ? 'current' : '';
      $views['all'] = sprintf(
         '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
         admin_url('admin.php?page=newsletter-subscriptions'),
         $class,
         'Все',
         $all_count
      );
      
      // Pending
      $pending_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$this->table_name} WHERE status = %s", 'pending'));
      $class = ($status === 'pending') ? 'current' : '';
      $views['pending'] = sprintf(
         '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
         admin_url('admin.php?page=newsletter-subscriptions&status=pending'),
         $class,
         'Ожидание',
         $pending_count
      );
      
      // Confirmed
      $confirmed_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$this->table_name} WHERE status = %s", 'confirmed'));
      $class = ($status === 'confirmed') ? 'current' : '';
      $views['confirmed'] = sprintf(
         '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
         admin_url('admin.php?page=newsletter-subscriptions&status=confirmed'),
         $class,
         'Подтверждено',
         $confirmed_count
      );
      
      // Unsubscribed
      $unsubscribed_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$this->table_name} WHERE status = %s", 'unsubscribed'));
      $class = ($status === 'unsubscribed') ? 'current' : '';
      $views['unsubscribed'] = sprintf(
         '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
         admin_url('admin.php?page=newsletter-subscriptions&status=unsubscribed'),
         $class,
         'Отписано',
         $unsubscribed_count
      );
      
      // Trash - always show if there are items in trash
      $trash_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$this->table_name} WHERE status = %s", 'trash'));
      $class = ($status === 'trash') ? 'current' : '';
      $views['trash'] = sprintf(
         '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
         admin_url('admin.php?page=newsletter-subscriptions&status=trash'),
         $class,
         'Корзина',
         $trash_count
      );
      
      return $views;
   }

   /**
    * Process bulk actions
    */
   public function process_bulk_action()
   {
      if (!isset($_POST['subscription']) || !is_array($_POST['subscription'])) {
         return;
      }

      // Check nonce - WordPress List Table uses 'bulk-' . $this->_args['plural']
      // $this->_args['plural'] is 'Subscriptions', so nonce is 'bulk-Subscriptions'
      $nonce_action = 'bulk-' . $this->_args['plural'];
      
      if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], $nonce_action)) {
         return;
      }

      // Get action from either action or action2 (top or bottom bulk actions dropdown)
      // WordPress uses action2 for the bottom dropdown, action for the top dropdown
      // Priority: action2 (bottom) > action (top) > current_action()
      $action = null;
      
      // Check action2 first (bottom bulk actions dropdown) - it has priority
      if (isset($_POST['action2']) && $_POST['action2'] !== '-1') {
         $action = sanitize_text_field($_POST['action2']);
      } elseif (isset($_POST['action']) && $_POST['action'] !== '-1') {
         $action = sanitize_text_field($_POST['action']);
      } elseif (isset($_REQUEST['action2']) && $_REQUEST['action2'] !== '-1') {
         $action = sanitize_text_field($_REQUEST['action2']);
      } elseif (isset($_REQUEST['action']) && $_REQUEST['action'] !== '-1') {
         $action = sanitize_text_field($_REQUEST['action']);
      } else {
         $action = $this->current_action();
      }
      
      if (!$action || $action === '-1') {
         return;
      }
      
      // WordPress may convert underscores to hyphens in action names
      // Convert old format to new format for backward compatibility
      if ($action === 'set-pending' || $action === 'set_pending') {
         $action = 'setpending';
      }
      
      // Debug: log action for troubleshooting
      if (defined('WP_DEBUG') && WP_DEBUG && WP_DEBUG_LOG) {
         error_log('Newsletter bulk action: ' . $action);
         error_log('POST action: ' . (isset($_POST['action']) ? $_POST['action'] : 'NOT SET'));
         error_log('POST action2: ' . (isset($_POST['action2']) ? $_POST['action2'] : 'NOT SET'));
      }

      $emails = array_map('sanitize_email', $_POST['subscription']);
      $emails = array_filter($emails, 'is_email');

      if (empty($emails)) {
         return;
      }

      global $wpdb;
      $updated = 0;
      $deleted = 0;

      $redirect_url = admin_url('admin.php?page=newsletter-subscriptions');
      
      // Preserve filter parameters
      $params = array();
      if (isset($_POST['status']) && !empty($_POST['status'])) {
         $params['status'] = sanitize_text_field($_POST['status']);
      }
      if (isset($_POST['form_id']) && !empty($_POST['form_id'])) {
         $params['form_id'] = sanitize_text_field($_POST['form_id']);
      }
      if (isset($_POST['s']) && !empty($_POST['s'])) {
         $params['s'] = sanitize_text_field($_POST['s']);
      }
      if (isset($_GET['paged']) && !empty($_GET['paged'])) {
         $params['paged'] = intval($_GET['paged']);
      }
      
      if (!empty($params)) {
         $redirect_url = add_query_arg($params, $redirect_url);
      }

      // Debug: log action for troubleshooting
      if (defined('WP_DEBUG') && WP_DEBUG && WP_DEBUG_LOG) {
         error_log('=== Newsletter Bulk Action Debug ===');
         error_log('Action determined: ' . $action);
         error_log('POST action: ' . (isset($_POST['action']) ? $_POST['action'] : 'NOT SET'));
         error_log('POST action2: ' . (isset($_POST['action2']) ? $_POST['action2'] : 'NOT SET'));
         error_log('Selected emails count: ' . count($emails));
         error_log('Selected emails: ' . implode(', ', $emails));
      }

      switch ($action) {
         case 'unsubscribe':
            // Unsubscribe - update each record individually for reliability
            $updated = 0;
            $unsubscribed_at = current_time('mysql');
            $updated_at = current_time('mysql');
            $actor_user_id = get_current_user_id();
            foreach ($emails as $email) {
               // Получаем текущую запись для обновления истории событий
               $subscription = $wpdb->get_row($wpdb->prepare(
                  "SELECT * FROM {$this->table_name} WHERE email = %s LIMIT 1",
                  $email
               ));
               
               if ($subscription) {
                  // Обновляем историю событий
                  $events = [];
                  if (!empty($subscription->events_history)) {
                     $decoded = json_decode($subscription->events_history, true);
                     if (is_array($decoded)) {
                        $events = $decoded;
                     }
                  }
                  
                  $events[] = [
                     'type'         => 'unsubscribed',
                     'date'         => $unsubscribed_at,
                     'source'       => 'admin',
                     'form_id'      => '',
                     'page_url'     => '',
                     'actor_user_id'=> $actor_user_id,
                  ];
                  
                  $result = $wpdb->update(
                     $this->table_name,
                     [
                        'status'          => 'unsubscribed',
                        'unsubscribed_at' => $unsubscribed_at,
                        'updated_at'      => $updated_at,
                        'events_history'  => wp_json_encode($events, JSON_UNESCAPED_UNICODE),
                     ],
                     ['email' => $email],
                     ['%s', '%s', '%s', '%s'],
                     ['%s']
                  );
                  
                  if ($result !== false && $result > 0) {
                     $updated += $result;
                     // Отзываем согласие на рассылку при отписке
                     $this->revoke_mailing_consent($email);
                  }
               }
            }
            if ($updated > 0) {
               add_settings_error(
                  'newsletter_messages',
                  'newsletter_message',
                  sprintf(__('%d subscription(s) successfully unsubscribed', 'codeweber'), $updated),
                  'success'
               );
            }
            wp_redirect($redirect_url);
            exit;
            break;

         case 'trash':
            // Move to trash - update each record individually for reliability
            if (defined('WP_DEBUG') && WP_DEBUG && WP_DEBUG_LOG) {
               error_log('=== Processing TRASH action ===');
            }
            
            $updated = 0;
            $updated_at = current_time('mysql');
            foreach ($emails as $email) {
               if (defined('WP_DEBUG') && WP_DEBUG && WP_DEBUG_LOG) {
                  error_log("Trashing email: {$email}");
               }
               
               $result = $wpdb->query($wpdb->prepare(
                  "UPDATE {$this->table_name} SET status = 'trash', updated_at = %s WHERE email = %s AND status != 'trash'",
                  $updated_at,
                  $email
               ));
               if ($result !== false && $result > 0) {
                  $updated += $result;
               }
            }
            
            if (defined('WP_DEBUG') && WP_DEBUG && WP_DEBUG_LOG) {
               error_log("Total trashed: {$updated}");
               error_log('=== End TRASH action ===');
            }
            
            if ($updated > 0) {
               add_settings_error(
                  'newsletter_messages',
                  'newsletter_message',
                  sprintf('%d подписка(и) перемещена(ы) в корзину', $updated),
                  'success'
               );
            }
            wp_redirect($redirect_url);
            exit;
            break;

         case 'untrash':
            // Restore from trash - restore to confirmed status
            $escaped_emails = array_map(function($email) use ($wpdb) {
               return $wpdb->prepare('%s', $email);
            }, $emails);
            $emails_list = implode(',', $escaped_emails);
            $updated_at = current_time('mysql');
            $query = "UPDATE {$this->table_name} SET status = 'confirmed', updated_at = %s WHERE email IN ($emails_list) AND status = 'trash'";
            $prepared_query = $wpdb->prepare($query, $updated_at);
            $result = $wpdb->query($prepared_query);
            if ($result !== false) {
               $updated = $result;
               add_settings_error(
                  'newsletter_messages',
                  'newsletter_message',
                  sprintf('%d подписка(и) восстановлена(ы) из корзины', $updated),
                  'success'
               );
            }
            // Redirect to main page (not trash view)
            wp_redirect(admin_url('admin.php?page=newsletter-subscriptions'));
            exit;
            break;

         case 'delete':
            // Permanent delete (only from trash)
            $escaped_emails = array_map(function($email) use ($wpdb) {
               return $wpdb->prepare('%s', $email);
            }, $emails);
            $emails_list = implode(',', $escaped_emails);
            $query = "DELETE FROM {$this->table_name} WHERE email IN ($emails_list) AND status = 'trash'";
            $result = $wpdb->query($query);
            if ($result !== false) {
               $deleted = $result;
               add_settings_error(
                  'newsletter_messages',
                  'newsletter_message',
                  sprintf(__('%d subscription(s) permanently deleted', 'codeweber'), $deleted),
                  'success'
               );
            }
            wp_redirect($redirect_url);
            exit;
            break;

         case 'confirm':
            // Confirm - update each record individually for reliability
            $updated = 0;
            $confirmed_at = current_time('mysql');
            $updated_at = current_time('mysql');
            foreach ($emails as $email) {
               $result = $wpdb->query($wpdb->prepare(
                  "UPDATE {$this->table_name} SET status = 'confirmed', confirmed_at = %s, updated_at = %s, unsubscribed_at = NULL WHERE email = %s",
                  $confirmed_at,
                  $updated_at,
                  $email
               ));
               if ($result !== false && $result > 0) {
                  $updated += $result;
               }
            }
            if ($updated > 0) {
               add_settings_error(
                  'newsletter_messages',
                  'newsletter_message',
                  sprintf(__('%d subscription(s) successfully confirmed', 'codeweber'), $updated),
                  'success'
               );
            }
            wp_redirect($redirect_url);
            exit;
            break;

         case 'setpending':
         case 'set_pending': // Backward compatibility
         case 'set-pending': // Backward compatibility
            // Set pending - update each record individually for reliability
            if (defined('WP_DEBUG') && WP_DEBUG && WP_DEBUG_LOG) {
               error_log('=== Processing SETPENDING action ===');
            }
            
            $updated = 0;
            $updated_at = current_time('mysql');
            
            foreach ($emails as $email) {
               // Check current status before update
               $current_status = $wpdb->get_var($wpdb->prepare(
                  "SELECT status FROM {$this->table_name} WHERE email = %s",
                  $email
               ));
               
               if (defined('WP_DEBUG') && WP_DEBUG && WP_DEBUG_LOG) {
                  error_log("Email: {$email}, Current status: {$current_status}");
               }
               
               // Explicitly set status to 'pending' (not 'trash')
               $sql = $wpdb->prepare(
                  "UPDATE {$this->table_name} SET status = 'pending', updated_at = %s WHERE email = %s",
                  $updated_at,
                  $email
               );
               
               if (defined('WP_DEBUG') && WP_DEBUG && WP_DEBUG_LOG) {
                  error_log("SQL: {$sql}");
               }
               
               $result = $wpdb->query($sql);
               
               if (defined('WP_DEBUG') && WP_DEBUG && WP_DEBUG_LOG) {
                  error_log("Query result: " . ($result !== false ? $result : 'FALSE'));
                  error_log("Last error: " . $wpdb->last_error);
               }
               
               // Verify status after update
               $new_status = $wpdb->get_var($wpdb->prepare(
                  "SELECT status FROM {$this->table_name} WHERE email = %s",
                  $email
               ));
               
               if (defined('WP_DEBUG') && WP_DEBUG && WP_DEBUG_LOG) {
                  error_log("Email: {$email}, New status: {$new_status}");
               }
               
               if ($result !== false && $result > 0) {
                  $updated += $result;
               }
            }
            
            if (defined('WP_DEBUG') && WP_DEBUG && WP_DEBUG_LOG) {
               error_log("Total updated: {$updated}");
               error_log('=== End SETPENDING action ===');
            }
            
            if ($updated > 0) {
               add_settings_error(
                  'newsletter_messages',
                  'newsletter_message',
                  sprintf(__('%d subscription(s) set to pending', 'codeweber'), $updated),
                  'success'
               );
            } else {
               // If no records updated, show error
               add_settings_error(
                  'newsletter_messages',
                  'newsletter_message',
                  __('No subscriptions were updated. Please try again.', 'codeweber'),
                  'error'
               );
            }
            wp_redirect($redirect_url);
            exit;
            break;
            
         default:
            // Unknown action - log for debugging
            if (defined('WP_DEBUG') && WP_DEBUG && WP_DEBUG_LOG) {
               error_log('=== UNHANDLED BULK ACTION ===');
               error_log('Action: ' . $action);
               error_log('POST action: ' . (isset($_POST['action']) ? $_POST['action'] : 'NOT SET'));
               error_log('POST action2: ' . (isset($_POST['action2']) ? $_POST['action2'] : 'NOT SET'));
               error_log('Available actions: ' . implode(', ', array_keys($this->get_bulk_actions())));
               error_log('=== End UNHANDLED action ===');
            }
            wp_redirect($redirect_url);
            exit;
            break;
      }
   }

   /**
    * Revoke mailing consent when user unsubscribes
    * 
    * @param string $email User email address
    */
   private function revoke_mailing_consent($email)
   {
      // 1. Get consent document ID from settings
      $consent_document_id = get_option('codeweber_legal_email_consent', 0);

      if (empty($consent_document_id)) {
         error_log('Newsletter unsubscribe: Mailing consent document ID not configured');
         return;
      }

      // 2. Find user by email
      $user = get_user_by('email', $email);
      if (!$user) {
         error_log('Newsletter unsubscribe: User not found for email: ' . $email);
         return;
      }

      // 3. Revoke mailing consent (only this document)
      if (function_exists('codeweber_forms_revoke_user_consent')) {
         $result = codeweber_forms_revoke_user_consent($user->ID, $consent_document_id);
         if (is_wp_error($result)) {
            error_log('Newsletter unsubscribe: Failed to revoke consent: ' . $result->get_error_message());
         } else {
            error_log('Newsletter unsubscribe: Consent revoked successfully for user ID: ' . $user->ID);
         }
      }
   }

}


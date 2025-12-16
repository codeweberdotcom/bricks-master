<?php

/**
 * Newsletter Subscription Admin Class
 */

if (!defined('ABSPATH')) {
   exit;
}

// Load WP_List_Table class
if (!class_exists('WP_List_Table')) {
   require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

// Load Newsletter List Table class
require_once dirname(__FILE__) . '/class-newsletter-list-table.php';

class NewsletterSubscriptionAdmin
{
   private $table_name;

   public function __construct()
   {
      global $wpdb;
      $this->table_name = $wpdb->prefix . 'newsletter_subscriptions';

      add_action('admin_menu', array($this, 'add_admin_menu'));
      add_action('admin_init', array($this, 'admin_init'));
      add_action('admin_init', array($this, 'process_bulk_actions_early'));
      
      // Register screen option for per page
      add_filter('set-screen-option', array($this, 'set_screen_option'), 10, 3);
      
      // Register columns filter for Screen Options (must be early)
      add_action('load-toplevel_page_newsletter-subscriptions', array($this, 'setup_screen_options'));
   }
   
   /**
    * Setup screen options (columns and per page)
    */
   public function setup_screen_options()
   {
      $screen = get_current_screen();
      if (!$screen) {
         return;
      }
      
      // Register per page option
      $screen->add_option('per_page', array(
         'label' => __('Subscriptions per page', 'codeweber'),
         'default' => 20,
         'option' => 'subscriptions_per_page'
      ));
      
      // Create list table instance to register columns
      $list_table = new Newsletter_Subscription_List_Table($this);
      
      // Register columns for Screen Options
      add_filter("manage_{$screen->id}_columns", array($list_table, 'get_columns'));
   }
   
   /**
    * Save screen option (per page)
    */
   public function set_screen_option($status, $option, $value)
   {
      if ('subscriptions_per_page' === $option) {
         return (int) $value;
      }
      return $status;
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
         __('Import Subscribers', 'codeweber'),
         __('Import', 'codeweber'),
         'manage_options',
         'newsletter-subscriptions-import',
         array($this, 'render_import_page')
      );

   }

   public function admin_init()
   {
      add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));

      add_action('admin_post_newsletter_export_csv', array($this, 'handle_export_csv'));
      add_action('admin_post_nopriv_newsletter_export_csv', array($this, 'handle_export_csv'));

      add_action('admin_post_newsletter_import_csv', array($this, 'handle_import_csv'));
      add_action('admin_post_nopriv_newsletter_import_csv', array($this, 'handle_import_csv'));
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
      // All columns are always enabled (settings page removed)
      return true;
   }

   public function enqueue_admin_styles($hook)
   {
      if ($hook !== 'toplevel_page_newsletter-subscriptions' && $hook !== 'newsletter-subscriptions_page_newsletter-subscriptions-import') {
         return;
      }

      wp_enqueue_style(
         'newsletter-subscription-admin',
         get_template_directory_uri() . '/functions/integrations/newsletter-subscription/admin/css/admin.css',
         array(),
         '1.0.0'
      );
   }

   /**
    * Process bulk actions early via admin_init hook (before any output)
    */
   public function process_bulk_actions_early()
   {
      // Only process on our admin page
      if (!isset($_GET['page']) || $_GET['page'] !== 'newsletter-subscriptions') {
         return;
      }
      
      // Check if this is a bulk action
      if (isset($_POST['subscription']) && is_array($_POST['subscription'])) {
         $list_table = new Newsletter_Subscription_List_Table($this);
         $list_table->process_bulk_action();
         // process_bulk_action() will redirect and exit
      }
   }

   public function render_admin_page()
   {
      global $wpdb;

      // Детальный просмотр подписки
      if (isset($_GET['action']) && $_GET['action'] === 'view' && !empty($_GET['email'])) {
         $this->render_view_page(sanitize_email($_GET['email']));
         return;
      }

      // Handle individual actions (unsubscribe, delete) only if not bulk action
      $this->handle_actions();
      
      // Now create list table instance and prepare items
      $list_table = new Newsletter_Subscription_List_Table($this);
      $list_table->prepare_items();
      

      $forms = $wpdb->get_col("SELECT DISTINCT form_id FROM {$this->table_name} ORDER BY form_id");
      $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
      $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
      $form_filter = isset($_GET['form_id']) ? sanitize_text_field($_GET['form_id']) : '';

   ?>
      <div class="wrap">
         <h1><?php _e('Newsletter Subscriptions', 'codeweber'); ?></h1>

         <?php settings_errors('newsletter_messages'); ?>

         <?php
         // Display views (All, Pending, Confirmed, etc.)
         $list_table->views();
         ?>

         <div class="newsletter-admin-filters">
            <form method="get" id="newsletter-filter-form">
               <input type="hidden" name="page" value="newsletter-subscriptions">

               <div class="tablenav top">
                  <div class="alignleft actions">
                     <select name="status" class="newsletter-status-filter">
                        <option value=""><?php _e('All statuses', 'codeweber'); ?></option>
                        <option value="pending" <?php selected($status, 'pending'); ?>><?php _e('Pending', 'codeweber'); ?></option>
                        <option value="confirmed" <?php selected($status, 'confirmed'); ?>><?php _e('Confirmed', 'codeweber'); ?></option>
                        <option value="unsubscribed" <?php selected($status, 'unsubscribed'); ?>><?php _e('Unsubscribed', 'codeweber'); ?></option>
                        <option value="trash" <?php selected($status, 'trash'); ?>>Корзина</option>
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

                     <?php submit_button(__('Filter', 'codeweber'), 'secondary', 'filter_action', false); ?>

                     <?php if ($search || $status || $form_filter): ?>
                        <a href="<?php echo admin_url('admin.php?page=newsletter-subscriptions'); ?>"
                           class="button"><?php _e('Reset', 'codeweber'); ?></a>
                     <?php endif; ?>

                     <a href="<?php echo admin_url('admin.php?page=newsletter-subscriptions-import'); ?>"
                        class="button button-secondary"><?php _e('Import Subscribers', 'codeweber'); ?></a>
                  </div>
               </div>
            </form>

            <?php if ($status === 'trash'): ?>
               <form method="post" style="margin: 10px 0;">
                  <input type="hidden" name="action" value="empty_trash">
                  <?php wp_nonce_field('newsletter_admin_action', 'newsletter_nonce'); ?>
                  <button type="submit" class="button button-secondary button-link-delete"
                     onclick="return confirm('Вы уверены, что хотите безвозвратно очистить корзину подписок?');">
                     <?php _e('Empty Trash', 'codeweber'); ?>
                  </button>
               </form>
            <?php endif; ?>
         </div>

         <form method="post" id="newsletter-bulk-form">
            <?php 
            // Preserve filter parameters in the form
            if ($status) {
               echo '<input type="hidden" name="status" value="' . esc_attr($status) . '">';
            }
            if ($form_filter) {
               echo '<input type="hidden" name="form_id" value="' . esc_attr($form_filter) . '">';
            }
            if ($search) {
               echo '<input type="hidden" name="s" value="' . esc_attr($search) . '">';
            }
            
            $list_table->display();
            ?>
         </form>

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

   public function render_column_content($column, $subscription)
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
               <?php
               // Кнопка "Просмотр" для детального просмотра истории подписки/отписки
               $view_url = admin_url('admin.php?page=newsletter-subscriptions&action=view&email=' . urlencode($subscription->email));
               ?>
               <a href="<?php echo esc_url($view_url); ?>" class="button button-small">
                  <?php _e('View', 'codeweber'); ?>
               </a>

               <?php if ($subscription->status === 'trash'): ?>
                  <form method="post" style="display:inline;">
                     <input type="hidden" name="action" value="untrash">
                     <input type="hidden" name="email" value="<?php echo esc_attr($subscription->email); ?>">
                     <?php wp_nonce_field('newsletter_admin_action', 'newsletter_nonce'); ?>
                     <button type="submit" class="button button-small">
                        <?php _e('Restore', 'codeweber'); ?>
                     </button>
                  </form>

                  <form method="post" style="display:inline;">
                     <input type="hidden" name="action" value="delete_permanent">
                     <input type="hidden" name="email" value="<?php echo esc_attr($subscription->email); ?>">
                     <?php wp_nonce_field('newsletter_admin_action', 'newsletter_nonce'); ?>
                     <button type="submit" class="button button-small button-link-delete"
                        onclick="return confirm('<?php _e('Are you sure you want to permanently delete this subscription?', 'codeweber'); ?>')">
                        <?php _e('Delete Permanently', 'codeweber'); ?>
                     </button>
                  </form>
               <?php else: ?>
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
                     <input type="hidden" name="action" value="trash">
                     <input type="hidden" name="email" value="<?php echo esc_attr($subscription->email); ?>">
                     <?php wp_nonce_field('newsletter_admin_action', 'newsletter_nonce'); ?>
                     <button type="submit" class="button button-small button-link-delete"
                        onclick="return confirm('Вы уверены, что хотите переместить эту подписку в корзину?')">
                        Корзина
                     </button>
                  </form>
               <?php endif; ?>
            </div>
      <?php
            return ob_get_clean();

         default:
            // Return empty string for unknown columns
            return '';
      }
   }

   private function handle_actions()
   {
      // Don't handle if this is a bulk action
      if (isset($_POST['subscription']) && is_array($_POST['subscription'])) {
         return;
      }
      
      if (!isset($_POST['action']) || !check_admin_referer('newsletter_admin_action', 'newsletter_nonce')) {
         return;
      }

      global $wpdb;
      $action = $_POST['action'];
      $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';

      switch ($action) {
         case 'unsubscribe':
            // Обновляем статус и историю событий при отписке из админки
            $subscription = $wpdb->get_row($wpdb->prepare(
               "SELECT * FROM {$this->table_name} WHERE email = %s LIMIT 1",
               $email
            ));

            if ($subscription) {
               $events = [];
               if (!empty($subscription->events_history)) {
                  $decoded = json_decode($subscription->events_history, true);
                  if (is_array($decoded)) {
                     $events = $decoded;
                  }
               }

               $now = current_time('mysql');
               $events[] = [
                  'type'         => 'unsubscribed',
                  'date'         => $now,
                  'source'       => 'admin',
                  'form_id'      => '', // ИСПРАВЛЕНО: при отписке form_id пустой (отписка не через форму)
                  'page_url'     => '', // отписка из админки, страницы нет
                  'actor_user_id'=> get_current_user_id(),
               ];

               $result = $wpdb->update($this->table_name, array(
                  'status'          => 'unsubscribed',
                  'unsubscribed_at' => $now,
                  'updated_at'      => $now,
                  'events_history'  => wp_json_encode($events, JSON_UNESCAPED_UNICODE),
               ), array('email' => $email));

               if ($result) {
                  add_settings_error(
                     'newsletter_messages',
                     'newsletter_message',
                     __('User successfully unsubscribed from newsletter', 'codeweber'),
                     'success'
                  );
               }
            }
            break;

         case 'trash':
            // Only move to trash if not already in trash
            $result = $wpdb->query($wpdb->prepare(
               "UPDATE {$this->table_name} SET status = 'trash', updated_at = %s WHERE email = %s AND status != 'trash'",
               current_time('mysql'),
               $email
            ));

            if ($result) {
               add_settings_error(
                  'newsletter_messages',
                  'newsletter_message',
                  'Подписка перемещена в корзину',
                  'success'
               );
            }
            break;

         case 'untrash':
            $result = $wpdb->update($this->table_name, array(
               'status' => 'confirmed',
               'updated_at' => current_time('mysql')
            ), array('email' => $email, 'status' => 'trash'));

            if ($result) {
               add_settings_error(
                  'newsletter_messages',
                  'newsletter_message',
                  'Подписка восстановлена из корзины',
                  'success'
               );
            }
            break;

         case 'delete_permanent':
            // Only delete if in trash
            $result = $wpdb->query($wpdb->prepare(
               "DELETE FROM {$this->table_name} WHERE email = %s AND status = 'trash'",
               $email
            ));

            if ($result) {
               add_settings_error(
                  'newsletter_messages',
                  'newsletter_message',
                  __('Subscription permanently deleted', 'codeweber'),
                  'success'
               );
            }
            break;

         case 'empty_trash':
            // Delete all subscriptions that are currently in trash
            $result = $wpdb->query(
               "DELETE FROM {$this->table_name} WHERE status = 'trash'"
            );

            if ($result !== false) {
               add_settings_error(
                  'newsletter_messages',
                  'newsletter_message',
                  __('Trash has been emptied', 'codeweber'),
                  'success'
               );
            }
            break;
      }
   }

   /**
    * Render detailed view page for a single subscription (with full events history)
    *
    * @param string $email
    */
   private function render_view_page($email)
   {
      global $wpdb;

      $subscription = $wpdb->get_row($wpdb->prepare(
         "SELECT * FROM {$this->table_name} WHERE email = %s LIMIT 1",
         $email
      ));

      if (!$subscription) {
         wp_die(__('Subscription not found', 'codeweber'));
      }

      // Декодируем историю событий
      $events = [];
      if (!empty($subscription->events_history)) {
         $decoded = json_decode($subscription->events_history, true);
         if (is_array($decoded)) {
            $events = $decoded;
         }
      }

      ?>
      <div class="wrap">
         <h1><?php _e('Newsletter Subscription', 'codeweber'); ?></h1>

         <p>
            <a href="<?php echo admin_url('admin.php?page=newsletter-subscriptions'); ?>" class="button">
               <?php _e('← Back to Subscriptions', 'codeweber'); ?>
            </a>
         </p>

         <h2><?php _e('Subscription Details', 'codeweber'); ?></h2>
         <table class="wp-list-table widefat fixed striped">
            <tbody>
               <tr>
                  <th style="width: 220px;"><?php _e('Email', 'codeweber'); ?></th>
                  <td><?php echo esc_html($subscription->email); ?></td>
               </tr>
               <tr>
                  <th><?php _e('Status', 'codeweber'); ?></th>
                  <td>
                     <span class="newsletter-status status-<?php echo esc_attr($subscription->status); ?>">
                        <?php echo esc_html($this->get_status_label($subscription->status)); ?>
                     </span>
                  </td>
               </tr>
               <?php if (!empty($subscription->first_name) || !empty($subscription->last_name)): ?>
               <tr>
                  <th><?php _e('Name', 'codeweber'); ?></th>
                  <td><?php echo esc_html(trim($subscription->first_name . ' ' . $subscription->last_name)); ?></td>
               </tr>
               <?php endif; ?>
               <?php if (!empty($subscription->phone)): ?>
               <tr>
                  <th><?php _e('Phone', 'codeweber'); ?></th>
                  <td><?php echo esc_html($subscription->phone); ?></td>
               </tr>
               <?php endif; ?>
               <tr>
                  <th><?php _e('Subscription Form', 'codeweber'); ?></th>
                  <td>
                     <span title="<?php echo esc_attr($subscription->form_id); ?>">
                        <?php echo esc_html($this->get_form_label($subscription->form_id)); ?>
                     </span>
                  </td>
               </tr>
               <tr>
                  <th><?php _e('IP Address', 'codeweber'); ?></th>
                  <td><?php echo esc_html($subscription->ip_address); ?></td>
               </tr>
               <tr>
                  <th><?php _e('User Agent', 'codeweber'); ?></th>
                  <td><code><?php echo esc_html($subscription->user_agent); ?></code></td>
               </tr>
               <tr>
                  <th><?php _e('Created At', 'codeweber'); ?></th>
                  <td><?php echo esc_html($subscription->created_at); ?></td>
               </tr>
               <?php if (!empty($subscription->confirmed_at)): ?>
               <tr>
                  <th><?php _e('Confirmation Date', 'codeweber'); ?></th>
                  <td><?php echo esc_html($subscription->confirmed_at); ?></td>
               </tr>
               <?php endif; ?>
               <?php if ($subscription->status === 'unsubscribed' && !empty($subscription->unsubscribed_at) && $subscription->unsubscribed_at !== '0000-00-00 00:00:00'): ?>
               <tr>
                  <th><?php _e('Unsubscribe Date', 'codeweber'); ?></th>
                  <td><?php echo esc_html($subscription->unsubscribed_at); ?></td>
               </tr>
               <?php endif; ?>
            </tbody>
         </table>

         <h2 style="margin-top: 30px;"><?php _e('Subscription / Unsubscribe History', 'codeweber'); ?></h2>
         <p class="description">
            <?php _e('The table below shows the full history of subscription and unsubscribe events for this email address.', 'codeweber'); ?>
         </p>

         <table class="wp-list-table widefat fixed striped">
            <thead>
               <tr>
                  <th style="width: 160px;"><?php _e('Event Type', 'codeweber'); ?></th>
                  <th style="width: 190px;"><?php _e('Date & Time', 'codeweber'); ?></th>
                  <th style="width: 150px;"><?php _e('Form ID', 'codeweber'); ?></th>
                  <th style="width: 220px;"><?php _e('Subscription Form', 'codeweber'); ?></th>
                  <th style="width: 220px;"><?php _e('Author', 'codeweber'); ?></th>
                  <th style="width: 130px;"><?php _e('IP Address', 'codeweber'); ?></th>
                  <th style="width: 260px;"><?php _e('Page URL', 'codeweber'); ?></th>
                  <th><?php _e('Consents', 'codeweber'); ?></th>
               </tr>
            </thead>
            <tbody>
               <?php if (empty($events)): ?>
                  <tr>
                     <td colspan="8">
                        <em><?php _e('No events history recorded for this subscription.', 'codeweber'); ?></em>
                     </td>
                  </tr>
               <?php else: ?>
                  <?php foreach ($events as $event): ?>
                     <?php
                     $date_str = !empty($event['date']) ? esc_html($event['date']) : '—';
                     $type_label = '';
                     if (!empty($event['type']) && $event['type'] === 'confirmed') {
                        // Показываем более понятное название для подписки
                        $type_label = __('Subscribe', 'codeweber');
                     } elseif (!empty($event['type']) && $event['type'] === 'unsubscribed') {
                        $type_label = __('Unsubscribe', 'codeweber');
                     } else {
                        $type_label = __('Event', 'codeweber');
                     }

                     // Форма
                     // Если в событии сохранено человекочитаемое имя формы (form_name),
                     // показываем его. Иначе берём красивое имя по form_id.
                     $form_label = '—';
                     if (!empty($event['form_name'])) {
                        $form_label = $event['form_name'];
                     } elseif (!empty($event['form_id'])) {
                        $form_label = $this->get_form_label($event['form_id']);
                     }

                     // Автор
                     // Если отписка/подписка через пользователя (frontend, cf7, codeweber_form),
                     // показываем email подписчика. Если действие сделал админ (source=admin),
                     // показываем email администратора со ссылкой на его профиль, если actor_user_id задан.
                     $author_cell = '—';
                     if (!empty($event['source']) && $event['source'] === 'admin' && !empty($event['actor_user_id'])) {
                        $actor = get_user_by('id', (int) $event['actor_user_id']);
                        if ($actor) {
                           $profile_url = get_edit_user_link($actor->ID);
                           $author_cell = '<a href="' . esc_url($profile_url) . '" target="_blank" rel="noopener noreferrer">'
                              . esc_html($actor->user_email) . '</a>';
                        }
                     } else {
                        // По умолчанию считаем, что действие совершил сам подписчик
                        $author_cell = esc_html($subscription->email);
                     }

                     // IP-адрес
                     $ip_cell = '—';
                     if (!empty($event['ip_address'])) {
                        $ip_cell = esc_html($event['ip_address']);
                     } elseif (!empty($subscription->ip_address) && $event['type'] === 'confirmed') {
                        // Для старых событий без IP в истории, используем IP из основной записи (только для подписок)
                        $ip_cell = esc_html($subscription->ip_address);
                     }

                     // Страница
                     $page_cell = '—';
                     if (!empty($event['page_url'])) {
                        $url = esc_url($event['page_url']);
                        $page_cell = '<a href="' . $url . '" target="_blank" rel="noopener noreferrer">' . $url . '</a>';
                     }

                     // Согласия
                     $consents_cell = '';
                     if (!empty($event['consents']) && is_array($event['consents'])) {
                        $consent_lines = [];
                        foreach ($event['consents'] as $consent) {
                           if (empty($consent['id']) || empty($consent['title'])) {
                              continue;
                           }
                           $line = sprintf(
                              '%s (ID: %d)',
                              esc_html($consent['title']),
                              (int) $consent['id']
                           );
                           if (!empty($consent['document_revision_id'])) {
                              $line .= sprintf(
                                 ' - %s: %d',
                                 __('Revision ID', 'codeweber'),
                                 (int) $consent['document_revision_id']
                              );
                           }
                           if (!empty($consent['document_version'])) {
                              $line .= sprintf(
                                 ' - %s: %s',
                                 __('Version', 'codeweber'),
                                 esc_html($consent['document_version'])
                              );
                           }
                           if (!empty($consent['url'])) {
                              $url = esc_url($consent['url']);
                              $line .= ' - ' . __('URL', 'codeweber') . ': '
                                 . '<a href="' . $url . '" target="_blank" rel="noopener noreferrer">' . $url . '</a>';
                           }
                           $consent_lines[] = $line;
                        }

                        if (!empty($consent_lines)) {
                           $consents_cell = implode('<br>', $consent_lines);
                        }
                     }
                     ?>
                     <tr>
                        <td><?php echo esc_html($type_label); ?></td>
                        <td><?php echo $date_str ? esc_html(date('d.m.Y H:i:s', strtotime($date_str))) : '—'; ?></td>
                        <td><?php echo !empty($event['form_id']) ? esc_html($event['form_id']) : '—'; ?></td>
                        <td><?php echo esc_html($form_label); ?></td>
                        <td><?php echo $author_cell; ?></td>
                        <td><?php echo $ip_cell; ?></td>
                        <td><?php echo $page_cell; ?></td>
                        <td><?php echo $consents_cell ?: '—'; ?></td>
                     </tr>
                  <?php endforeach; ?>
               <?php endif; ?>
            </tbody>
         </table>
      </div>
      <?php
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
                           Пропускать дубликаты email (оставлять существующие)
                        </label>
                        <p class="description">
                           Если снято, дубликаты будут обновлены новыми данными
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
      $valid_statuses = array('pending', 'confirmed', 'unsubscribed', 'trash');

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
         'unsubscribed' => __('Unsubscribed', 'codeweber'),
         'trash' => 'Корзина'
      );

      return $labels[$status] ?? $status;
   }

   public function get_form_label($form_id)
   {
      $form_labels = array(
         'default'   => __('Subscription Form|Email', 'codeweber'),
         'cf7_1072'  => __('Contact Form 7: Request Callback', 'codeweber'),
         'imported'  => __('Imported', 'codeweber'),
      );

      // НОВОЕ: Используем единую функцию для получения типа формы
      if (class_exists('CodeweberFormsCore')) {
         // Нормализуем form_id (может быть с префиксом codeweber_form_)
         $normalized_id = $form_id;
         if (strpos($form_id, 'codeweber_form_') === 0) {
            $normalized_id = substr($form_id, strlen('codeweber_form_'));
         }
         
         // ВАЖНО: Если это числовой ID, ВСЕГДА пытаемся получить название из CPT
         // Это приоритетнее, чем тип формы, так как пользователь видит название формы, а не тип
         if (is_numeric($normalized_id) && (int) $normalized_id > 0) {
            $form_post = get_post((int) $normalized_id);
            if ($form_post && $form_post->post_type === 'codeweber_form' && !empty($form_post->post_title)) {
               return $form_post->post_title;
            }
         }
         
         // Только если не удалось получить название из CPT, используем тип формы
         // Получаем тип формы
         $form_type = CodeweberFormsCore::get_form_type($normalized_id);
         
         // Маппинг типов на читаемые названия
         $type_labels = [
            'form' => __('Regular Form', 'codeweber'),
            'newsletter' => __('Newsletter Subscription', 'codeweber'),
            'testimonial' => __('Testimonial Form', 'codeweber'),
            'resume' => __('Resume Form', 'codeweber'),
            'callback' => __('Callback Request', 'codeweber'),
         ];
         
         if (isset($type_labels[$form_type])) {
            return $type_labels[$form_type];
         }
      }

      // LEGACY: Fallback для обратной совместимости
      // 0) Встроенные формы, сохранённые без префикса (newsletter, testimonial, resume, callback)
      $builtin_plain = array(
         'newsletter'  => __('Newsletter Subscription', 'codeweber'),
         'testimonial' => __('Testimonial Form', 'codeweber'),
         'resume'      => __('Resume Form', 'codeweber'),
         'callback'    => __('Callback Request', 'codeweber'),
      );
      if (isset($builtin_plain[$form_id])) {
         return $builtin_plain[$form_id];
      }

      // 1) Интеграции с Contact Form 7
      if (strpos($form_id, 'cf7_') === 0) {
         $form_parts = explode('_', $form_id);
         if (count($form_parts) >= 2 && is_numeric($form_parts[1])) {
            $form_title = get_the_title($form_parts[1]);
            if ($form_title && !empty($form_title)) {
               return 'CF7: ' . $form_title;
            }
         }
      }

      // 2) Формы Codeweber Forms: codeweber_form_6119 или codeweber_form_newsletter
      if (strpos($form_id, 'codeweber_form_') === 0) {
         $suffix = substr($form_id, strlen('codeweber_form_'));

         // Вариант 2.1: числовой ID -> заголовок записи CPT
         if (ctype_digit($suffix)) {
            $post = get_post((int) $suffix);
            if ($post && $post->post_type === 'codeweber_form') {
               return $post->post_title;
            }
         }

         // Вариант 2.2: встроенные формы по строковому ключу (newsletter, testimonial, resume, callback)
         $builtin_labels = array(
            'newsletter'  => __('Newsletter Subscription', 'codeweber'),
            'testimonial' => __('Testimonial Form', 'codeweber'),
            'resume'      => __('Resume Form', 'codeweber'),
            'callback'    => __('Callback Request', 'codeweber'),
         );

         if (isset($builtin_labels[$suffix])) {
            return $builtin_labels[$suffix];
         }

         // Если не распознали — возвращаем сам form_id
         return $form_id;
      }

      // 3) Статические ярлыки
      if (isset($form_labels[$form_id])) {
         return $form_labels[$form_id];
      }
      
      // 4) КРИТИЧНО: Если form_id числовой (строка или число), пытаемся получить название из CPT
      // Это должно сработать даже если предыдущие проверки не сработали
      if (is_numeric($form_id) && (int) $form_id > 0) {
         $form_post = get_post((int) $form_id);
         if ($form_post && $form_post->post_type === 'codeweber_form' && !empty($form_post->post_title)) {
            return $form_post->post_title;
         }
      }
      
      // 5) Финальный fallback: возвращаем сам form_id
      return $form_id;
   }
}

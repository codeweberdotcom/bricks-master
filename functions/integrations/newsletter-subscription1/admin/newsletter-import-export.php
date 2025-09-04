<?php

/**
 * Newsletter Subscription Import/Export Class
 */

if (!defined('ABSPATH')) {
   exit;
}

class NewsletterSubscriptionImportExport
{
   private $table_name;

   public function __construct()
   {
      global $wpdb;
      $this->table_name = $wpdb->prefix . 'newsletter_subscriptions';
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
            'unsubscribe_token' => wp_hash($email . 'unsubscribe_salt' . time() . wp_rand())
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

   private function set_import_result($success, $message, $details = array())
   {
      set_transient('newsletter_import_results', array(
         'success' => $success,
         'message' => $message,
         'details' => $details
      ), 30);
   }
}

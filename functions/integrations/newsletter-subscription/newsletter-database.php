<?php

/**
 * Newsletter Subscription Database Class
 */

if (!defined('ABSPATH')) {
   exit;
}

class NewsletterSubscriptionDatabase
{
   private $table_name;
   private $version = '1.0.4';

   public function __construct()
   {
      global $wpdb;
      $this->table_name = $wpdb->prefix . 'newsletter_subscriptions';
      $this->create_table();
   }

   private function create_table()
   {
      global $wpdb;

      if (get_option('newsletter_subscription_version') !== $this->version) {
         $charset_collate = $wpdb->get_charset_collate();

         $sql = "CREATE TABLE {$this->table_name} (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                email VARCHAR(100) NOT NULL,
                first_name VARCHAR(100) DEFAULT '',
                last_name VARCHAR(100) DEFAULT '',
                phone VARCHAR(20) DEFAULT '',
                ip_address VARCHAR(45) DEFAULT '',
                user_agent TEXT,
                form_id VARCHAR(50) DEFAULT '',
                status ENUM('pending', 'confirmed', 'unsubscribed', 'trash') DEFAULT 'confirmed',
                created_at DATETIME DEFAULT '0000-00-00 00:00:00',
                confirmed_at DATETIME DEFAULT NULL,
                unsubscribed_at DATETIME DEFAULT NULL,
                updated_at DATETIME DEFAULT '0000-00-00 00:00:00',
                unsubscribe_token VARCHAR(100) DEFAULT '',
                events_history LONGTEXT DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY email (email),
                KEY status (status),
                KEY form_id (form_id),
                KEY unsubscribed_at (unsubscribed_at),
                KEY unsubscribe_token (unsubscribe_token)
            ) $charset_collate;";

         require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
         dbDelta($sql);

         update_option('newsletter_subscription_version', $this->version);
      }
   }

   public function get_subscription($email)
   {
      global $wpdb;
      return $wpdb->get_row($wpdb->prepare(
         "SELECT * FROM {$this->table_name} WHERE email = %s",
         $email
      ));
   }

   public function add_subscription($data)
   {
      global $wpdb;
      return $wpdb->insert($this->table_name, $data);
   }

   public function update_subscription($email, $data)
   {
      global $wpdb;
      return $wpdb->update($this->table_name, $data, array('email' => $email));
   }

   public function delete_subscription($email)
   {
      global $wpdb;
      return $wpdb->delete($this->table_name, array('email' => $email));
   }

   public function get_subscriptions($where = '', $limit = '', $offset = '')
   {
      global $wpdb;

      $query = "SELECT * FROM {$this->table_name}";
      if ($where) {
         $query .= " WHERE {$where}";
      }
      $query .= " ORDER BY created_at DESC";

      if ($limit) {
         $query .= " LIMIT {$limit}";
         if ($offset) {
            $query .= " OFFSET {$offset}";
         }
      }

      return $wpdb->get_results($query);
   }

   public function count_subscriptions($where = '')
   {
      global $wpdb;

      $query = "SELECT COUNT(*) FROM {$this->table_name}";
      if ($where) {
         $query .= " WHERE {$where}";
      }

      return $wpdb->get_var($query);
   }
}

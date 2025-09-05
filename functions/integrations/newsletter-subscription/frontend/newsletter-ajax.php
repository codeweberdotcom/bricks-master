<?php

/**
 * Newsletter Subscription AJAX Handlers
 */

if (!defined('ABSPATH')) {
   exit;
}

class NewsletterSubscriptionAjax
{
   private $table_name;
   private $options_name = 'newsletter_subscription_settings';

   public function __construct()
   {
      global $wpdb;
      $this->table_name = $wpdb->prefix . 'newsletter_subscriptions';

      add_action('wp_ajax_newsletter_unsubscribe', array($this, 'handle_unsubscribe'));
      add_action('wp_ajax_nopriv_newsletter_unsubscribe', array($this, 'handle_unsubscribe'));
   }

   public function handle_unsubscribe()
   {
      check_ajax_referer('newsletter_nonce', 'newsletter_nonce');

      $email = sanitize_email($_POST['email'] ?? '');
      $token = sanitize_text_field($_POST['token'] ?? '');

      $result = $this->process_unsubscribe($email, $token);

      if ($result) {
         wp_send_json_success(__('You have successfully unsubscribed from the newsletter', 'codeweber'));
      } else {
         wp_send_json_error(__('Invalid unsubscribe link', 'codeweber'));
      }
   }

   private function process_unsubscribe($email, $token)
   {
      global $wpdb;

      if (!is_email($email) || !$this->verify_unsubscribe_token($email, $token)) {
         return false;
      }

      $result = $wpdb->update($this->table_name, array(
         'status' => 'unsubscribed',
         'unsubscribed_at' => current_time('mysql'),
         'updated_at' => current_time('mysql')
      ), array(
         'email' => $email,
         'unsubscribe_token' => $token,
         'status' => 'confirmed'
      ));

      return $result !== false;
   }

   private function verify_unsubscribe_token($email, $token)
   {
      global $wpdb;

      $valid = $wpdb->get_var($wpdb->prepare(
         "SELECT id FROM {$this->table_name} WHERE email = %s AND unsubscribe_token = %s AND status = 'confirmed'",
         $email,
         $token
      ));

      return !empty($valid);
   }

   private function get_client_ip()
   {
      $ip = '';
      if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
         $ip = $_SERVER['HTTP_CLIENT_IP'];
      } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
         $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
      } else {
         $ip = $_SERVER['REMOTE_ADDR'] ?? '';
      }
      return $ip;
   }
}

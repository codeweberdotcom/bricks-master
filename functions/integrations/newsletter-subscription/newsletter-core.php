<?php

/**
 * Newsletter Subscription Core Class
 */

if (!defined('ABSPATH')) {
   exit;
}

class NewsletterSubscription
{
   private $table_name;
   private $version = '1.0.2';
   private $options_name = 'newsletter_subscription_settings';

   public function __construct()
   {
      global $wpdb;
      $this->table_name = $wpdb->prefix . 'newsletter_subscriptions';

      $this->init();
   }

   private function init()
   {
      add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
   }

   public function enqueue_scripts()
   {
      // Старый скрипт newsletter.js удален
      // Все формы подписки теперь обрабатываются через универсальный скрипт codeweber-forms
      // Метод оставлен для совместимости, но ничего не делает
   }

   private function generate_unsubscribe_token($email)
   {
      return wp_hash($email . 'unsubscribe_salt' . time() . wp_rand());
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

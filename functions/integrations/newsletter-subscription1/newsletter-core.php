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
      add_action('wpcf7_mail_sent', array($this, 'handle_cf7_submission'));
   }

   public function enqueue_scripts()
   {
      wp_enqueue_script(
         'newsletter-subscription',
         get_template_directory_uri() . '/functions/integrations/newsletter-subscription1/frontend/js/newsletter.js',
         array('jquery'),
         $this->version,
         true
      );

      wp_localize_script('newsletter-subscription', 'newsletter_ajax', array(
         'ajax_url' => admin_url('admin-ajax.php'),
         'nonce' => wp_create_nonce('newsletter_nonce'),
         'translations' => array(
            'sending' => __('Sending...', 'codeweber'),
            'invalid_email' => __('Please enter a valid email address', 'codeweber'),
            'mailing_consent_required' => __('Consent to receive information and advertising mailings is required', 'codeweber'),
            'data_processing_consent_required' => __('Consent to process personal data is required', 'codeweber'),
            'privacy_policy_required' => __('You must agree to the privacy policy', 'codeweber'), // ← ДОБАВЬТЕ ЭТУ СТРОЧКУ
            'invalid_form' => __('Invalid form', 'codeweber'),
            'error_occurred' => __('An error occurred. Please try again later.', 'codeweber'),
         )
      ));
   }

   public function handle_cf7_submission($contact_form)
   {
      $options = get_option($this->options_name, array());
      $enable_cf7 = isset($options['enable_cf7_integration']) ? $options['enable_cf7_integration'] : true;

      if (!$enable_cf7) {
         return;
      }

      $submission = WPCF7_Submission::get_instance();

      if (!$submission) {
         return;
      }

      $posted_data = $submission->get_posted_data();

      $has_consent = false;

      if (isset($posted_data['soglasie-na-rassilku'])) {
         if (is_array($posted_data['soglasie-na-rassilku'])) {
            $has_consent = !empty($posted_data['soglasie-na-rassilku']) &&
               in_array('1', $posted_data['soglasie-na-rassilku']);
         } else {
            $has_consent = $posted_data['soglasie-na-rassilku'] === 'on' ||
               $posted_data['soglasie-na-rassilku'] === '1' ||
               $posted_data['soglasie-na-rassilku'] === true;
         }
      }

      if ($has_consent) {
         $email = isset($posted_data['email-address']) ? sanitize_email($posted_data['email-address']) : '';

         if (!is_email($email)) {
            return;
         }

         global $wpdb;
         $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->table_name} WHERE email = %s",
            $email
         ));

         if ($exists) {
            return;
         }

         $first_name = isset($posted_data['text-name']) ? sanitize_text_field($posted_data['text-name']) : '';
         $last_name = isset($posted_data['text-lastname']) ? sanitize_text_field($posted_data['text-lastname']) : '';
         $phone = isset($posted_data['tel-463']) ? sanitize_text_field($posted_data['tel-463']) : '';

         $unsubscribe_token = $this->generate_unsubscribe_token($email);

         $result = $wpdb->insert($this->table_name, array(
            'email' => $email,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'phone' => $phone,
            'ip_address' => $this->get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'form_id' => 'cf7_' . $contact_form->id(),
            'status' => 'confirmed',
            'created_at' => current_time('mysql'),
            'confirmed_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
            'unsubscribe_token' => $unsubscribe_token
         ));

         if ($result) {
            $this->save_subscription_consents($email, $first_name, $last_name, $phone, 'cf7_' . $contact_form->id());

            $send_email = isset($options['send_confirmation_email']) ? $options['send_confirmation_email'] : true;
            if ($send_email) {
               $this->send_confirmation_email($email, $first_name, $last_name, $unsubscribe_token);
            }
         }
      }
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

   private function save_subscription_consents($email, $first_name, $last_name, $phone, $form_id)
   {
      // Реализация будет в отдельном файле согласий
      return true;
   }

   private function send_confirmation_email($email, $first_name, $last_name, $unsubscribe_token)
   {
      // Реализация будет в отдельном файле
   }
}

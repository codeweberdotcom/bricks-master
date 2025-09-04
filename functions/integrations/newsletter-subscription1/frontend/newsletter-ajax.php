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

      // ✅ ДОБАВЬТЕ ЭТИ ХУКИ В КОНСТРУКТОР
      add_action('wp_ajax_newsletter_subscription', array($this, 'handle_subscription'));
      add_action('wp_ajax_nopriv_newsletter_subscription', array($this, 'handle_subscription'));
      add_action('wp_ajax_newsletter_unsubscribe', array($this, 'handle_unsubscribe'));
      add_action('wp_ajax_nopriv_newsletter_unsubscribe', array($this, 'handle_unsubscribe'));
   }

   public function handle_subscription()
   {
      check_ajax_referer('newsletter_nonce', 'newsletter_nonce');

      $options = get_option($this->options_name, array());

      // Получаем сообщения с проверкой на пустоту и переводимыми значениями по умолчанию
      $error_message = isset($options['error_message']) && !empty($options['error_message'])
         ? $options['error_message']
         : __('An error occurred. Please try again later.', 'codeweber');

      $email_error = __('Please enter a valid email address.', 'codeweber');
      $mailing_consent_error = __('Consent to receive information and advertising mailings is required', 'codeweber');
      $data_processing_error = __('Consent to process personal data is required', 'codeweber');
      $exists_error = __('This email is already subscribed to the newsletter.', 'codeweber');
      $success_message = isset($options['success_message']) && !empty($options['success_message'])
         ? $options['success_message']
         : __('You have successfully subscribed to the newsletter!', 'codeweber');

      $response = array('success' => false, 'message' => '');

      $email = sanitize_email($_POST['email'] ?? '');
      $mailing_consent = isset($_POST['soglasie-na-rassilku']);
      $data_processing_consent = isset($_POST['soglasie-na-obrabotku']);
      $privacy_policy_read = isset($_POST['privacy-policy-read']);
      $form_id = sanitize_text_field($_POST['form_id'] ?? 'default');
      $first_name = sanitize_text_field($_POST['text-name'] ?? '');
      $last_name = sanitize_text_field($_POST['text-surname'] ?? '');
      $phone = sanitize_text_field($_POST['tel'] ?? '');

      if (!is_email($email)) {
         $response['message'] = $email_error;
         wp_send_json($response);
      }

      // Проверяем оба согласия
      if (!$mailing_consent) {
         $response['message'] = $mailing_consent_error;
         wp_send_json($response);
      }

      if (!$data_processing_consent) {
         $response['message'] = $data_processing_error;
         wp_send_json($response);
      }

      global $wpdb;

      $exists = $wpdb->get_var($wpdb->prepare(
         "SELECT id FROM {$this->table_name} WHERE email = %s",
         $email
      ));

      if ($exists) {
         $response['message'] = $exists_error;
         wp_send_json($response);
      }

      $unsubscribe_token = wp_hash($email . 'unsubscribe_salt' . time() . wp_rand());

      $result = $wpdb->insert($this->table_name, array(
         'email' => $email,
         'first_name' => $first_name,
         'last_name' => $last_name,
         'phone' => $phone,
         'ip_address' => $this->get_client_ip(),
         'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
         'form_id' => $form_id,
         'status' => 'confirmed',
         'created_at' => current_time('mysql'),
         'confirmed_at' => current_time('mysql'),
         'updated_at' => current_time('mysql'),
         'unsubscribe_token' => $unsubscribe_token
      ));

      if ($result) {
         // Сохраняем согласия в CPT
         $consent_saved = $this->save_subscription_consents($email, $first_name, $last_name, $phone, $form_id);

         if (!$consent_saved) {
            error_log('Failed to save consents for: ' . $email);
         }

         $this->send_confirmation_email($email, $first_name, $last_name, $unsubscribe_token);
         $response['success'] = true;
         $response['message'] = $success_message;
      } else {
         $response['message'] = $error_message;
      }

      wp_send_json($response);
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

   private function save_subscription_consents($email, $first_name, $last_name, $phone, $form_id)
   {
      // Реализация сохранения согласий будет в отдельном файле
      // Эта функция должна интегрироваться с вашей системой управления согласиями
      return true;
   }

   private function send_confirmation_email($email, $first_name, $last_name, $unsubscribe_token)
   {
      $options = get_option($this->options_name, array());

      $subject = isset($options['email_subject']) && !empty($options['email_subject'])
         ? $options['email_subject']
         : __('Confirming your subscription to the newsletter', 'codeweber');

      $from_email = isset($options['from_email']) && !empty($options['from_email'])
         ? $options['from_email']
         : get_option('admin_email');

      $from_name = isset($options['from_name']) && !empty($options['from_name'])
         ? $options['from_name']
         : get_bloginfo('name');

      $email_template = isset($options['email_template']) && !empty($options['email_template'])
         ? $options['email_template']
         : '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            line-height: 1.6; 
            color: #333; 
            margin: 0;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        a {
            word-break: break-all;
            overflow-wrap: anywhere;
            word-wrap: break-word;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .button { 
            display: inline-block; 
            padding: 12px 24px; 
            background-color: #dc3545; 
            color: white; 
            text-decoration: none; 
            border-radius: 4px; 
            margin: 20px 0; 
            font-weight: bold;
        }
        .button:hover { 
            background-color: #c82333; 
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .text-center {
            text-align: center;
        }
        .divider {
            margin: 25px 0;
            border-top: 1px solid #eee;
        }
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>{email_subject}</h1>
        </div>

        <h2>' . __('Hello', 'codeweber') . ', {first_name} {last_name}!</h2>
        <p>' . __('You have successfully subscribed to our newsletter.', 'codeweber') . '</p>
        
        <p>' . __('If you want to unsubscribe from the newsletter, click the button below:', 'codeweber') . '</p>
        
        <div class="text-center">
            <a href="{unsubscribe_url}" class="button">' . __('Unsubscribe', 'codeweber') . '</a>
        </div>
        
        <div class="divider"></div>
        
        <p>' . __('Or copy and paste the following link into your browser:', 'codeweber') . '</p>
        <p><a href="{unsubscribe_url}">{unsubscribe_url}</a></p>

        <div class="footer">
            <p style="font-size: 12px; color: #666;">
                ' . __('Best regards,', 'codeweber') . '<br>
                <strong>' . __('Team', 'codeweber') . ' {site_name}</strong>
            </p>
            <p style="font-size: 11px; color: #999;">
                ' . __('This email was sent to {email} because you subscribed to our newsletter.', 'codeweber') . '<br>
                ' . __('If you have any questions, please contact our support team.', 'codeweber') . '
            </p>
        </div>
    </div>
</body>
</html>';

      $unsubscribe_url = add_query_arg(array(
         'action' => 'newsletter_unsubscribe',
         'email' => urlencode($email),
         'token' => urlencode($unsubscribe_token)
      ), home_url('/'));

      $message = str_replace(
         array(
            '{email_subject}',
            '{first_name}',
            '{last_name}',
            '{email}',
            '{unsubscribe_url}',
            '{site_name}'
         ),
         array(
            esc_html($subject),
            esc_html($first_name),
            esc_html($last_name),
            esc_html($email),
            esc_url($unsubscribe_url),
            esc_html(get_bloginfo('name'))
         ),
         $email_template
      );

      $headers = array(
         'Content-Type: text/html; charset=UTF-8',
         'From: ' . $from_name . ' <' . $from_email . '>'
      );

      wp_mail($email, $subject, $message, $headers);
   }
}

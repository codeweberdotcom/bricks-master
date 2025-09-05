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
      add_action('wp_ajax_newsletter_subscription', array($this, 'handle_ajax_subscription'));
      add_action('wp_ajax_nopriv_newsletter_subscription', array($this, 'handle_ajax_subscription'));
   }

   public function enqueue_scripts()
   {
      wp_enqueue_script(
         'newsletter-subscription',
         get_template_directory_uri() . '/functions/integrations/newsletter-subscription/frontend/js/newsletter.js',
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
            'privacy_policy_required' => __('You must agree to the privacy policy', 'codeweber'),
            'invalid_form' => __('Invalid form', 'codeweber'),
            'error_occurred' => __('An error occurred. Please try again later.', 'codeweber'),
         )
      ));
   }

   /**
    * Обработка AJAX подписки
    */
   public function handle_ajax_subscription()
   {
      check_ajax_referer('newsletter_nonce', 'nonce');

      $options = get_option($this->options_name, array());

      // Получаем сообщения с проверкой на пустоту и переводимыми значениями по умолчанию
      $error_message = isset($options['error_message']) && !empty($options['error_message'])
         ? $options['error_message']
         : __('An error occurred. Please try again later.', 'codeweber');

      $email_error = __('Please enter a valid email address.', 'codeweber');
      $mailing_consent_error = __('Consent to receive information and advertising mailings is required', 'codeweber');
      $data_processing_error = __('Consent to process personal data is required', 'codeweber');
      $privacy_policy_error = __('You must agree to the privacy policy', 'codeweber');
      $exists_error = __('This email is already subscribed to the newsletter.', 'codeweber');
      $success_message = isset($options['success_message']) && !empty($options['success_message'])
         ? $options['success_message']
         : __('You have successfully subscribed to the newsletter!', 'codeweber');

      $response = array('success' => false, 'message' => '');

      // Логируем полученные данные
      error_log('Newsletter subscription POST data: ' . print_r($_POST, true));

      $email = sanitize_email($_POST['email'] ?? '');
      $mailing_consent = isset($_POST['soglasie-na-rassilku']);
      $data_processing_consent = isset($_POST['soglasie-na-obrabotku']);
      $privacy_policy_read = isset($_POST['privacy-policy-read']);
      $form_id = sanitize_text_field($_POST['form_id'] ?? 'default');
      $first_name = sanitize_text_field($_POST['text-name'] ?? '');
      $last_name = sanitize_text_field($_POST['text-surname'] ?? '');
      $phone = sanitize_text_field($_POST['tel'] ?? '');

      error_log("Parsed data - Email: $email, Mailing: $mailing_consent, Data: $data_processing_consent, Privacy: $privacy_policy_read");

      if (!is_email($email)) {
         $response['message'] = $email_error;
         wp_send_json($response);
      }

      // Проверяем согласия
      if (!$mailing_consent) {
         $response['message'] = $mailing_consent_error;
         wp_send_json($response);
      }

      if (!$data_processing_consent) {
         $response['message'] = $data_processing_error;
         wp_send_json($response);
      }

      if (!$privacy_policy_read) {
         $response['message'] = $privacy_policy_error;
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

      $unsubscribe_token = $this->generate_unsubscribe_token($email);

      $insert_data = array(
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
      );

      error_log('Inserting into database: ' . print_r($insert_data, true));

      $result = $wpdb->insert($this->table_name, $insert_data);

      if ($result === false) {
         error_log('Database insert failed: ' . $wpdb->last_error);
         $response['message'] = $error_message;
         wp_send_json($response);
      }

      if ($result) {
         // Сохраняем согласия в CPT
         $consent_saved = $this->save_subscription_consents($email, $first_name, $last_name, $phone, $form_id);

         if (!$consent_saved) {
            error_log('Failed to save consents for: ' . $email);
            // Не прерываем процесс, так как подписка уже сохранена
         }

         // Отправляем email подтверждения
         $send_email = isset($options['send_confirmation_email']) ? $options['send_confirmation_email'] : true;
         if ($send_email) {
            $email_sent = $this->send_confirmation_email($email, $first_name, $last_name, $unsubscribe_token);
            if (!$email_sent) {
               error_log('Failed to send confirmation email to: ' . $email);
            }
         }

         $response['success'] = true;
         $response['message'] = $success_message;
      } else {
         error_log('Database insert returned false but no error');
         $response['message'] = $error_message;
      }

      wp_send_json($response);
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

      error_log('CF7 submission data: ' . print_r($posted_data, true));

      $has_consent = false;

      // Проверяем согласие на рассылку
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

      error_log('CF7 consent check: ' . ($has_consent ? 'true' : 'false'));

      if ($has_consent) {
         $email = isset($posted_data['email-address']) ? sanitize_email($posted_data['email-address']) : '';

         if (!is_email($email)) {
            error_log('CF7 invalid email: ' . $email);
            return;
         }

         global $wpdb;
         $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->table_name} WHERE email = %s",
            $email
         ));

         if ($exists) {
            error_log('CF7 email already exists: ' . $email);
            return;
         }

         // Получаем другие поля, если они есть в форме
         $first_name = isset($posted_data['text-name']) ? sanitize_text_field($posted_data['text-name']) : '';
         $last_name = isset($posted_data['text-lastname']) ? sanitize_text_field($posted_data['text-lastname']) : '';
         $phone = isset($posted_data['tel-463']) ? sanitize_text_field($posted_data['tel-463']) : '';

         $unsubscribe_token = $this->generate_unsubscribe_token($email);

         $insert_data = array(
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
         );

         error_log('CF7 inserting: ' . print_r($insert_data, true));

         $result = $wpdb->insert($this->table_name, $insert_data);

         if ($result === false) {
            error_log('CF7 database insert failed: ' . $wpdb->last_error);
            return;
         }

         if ($result) {
            // ✅ ПРОВЕРКА ТОЛЬКО ДЛЯ СОХРАНЕНИЯ СОГЛАСИЙ
            if (!function_exists('get_acceptance_label_html')) {
               $consent_saved = $this->save_subscription_consents($email, $first_name, $last_name, $phone, 'cf7_' . $contact_form->id());
               if (!$consent_saved) {
                  error_log('CF7 failed to save consents for: ' . $email);
               }
            } else {
               error_log('Personal-data module detected, skipping consent saving for CF7 form');
            }

            // ✅ ОТПРАВКА EMAIL - ВСЕГДА, независимо от наличия personal-data модуля
            $send_email = isset($options['send_confirmation_email']) ? $options['send_confirmation_email'] : true;
            if ($send_email) {
               $email_sent = $this->send_confirmation_email($email, $first_name, $last_name, $unsubscribe_token);
               if ($email_sent) {
                  error_log('Confirmation email sent to: ' . $email);
               } else {
                  error_log('Failed to send confirmation email to: ' . $email);
               }
            } else {
               error_log('Email sending disabled in settings');
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

   /**
    * Сохраняем согласия подписчика в CPT
    */
   public function save_subscription_consents($email, $first_name, $last_name, $phone, $form_id)
   {
      if (!class_exists('Consent_Manager')) {
         error_log('Consent Manager not available for saving consents');
         return false;
      }

      $options = get_option($this->options_name, array());

      // Получаем ID документов из настроек
      $privacy_policy_id = isset($options['privacy_policy_legal']) ? $options['privacy_policy_legal'] : '';
      $mailing_consent_id = isset($options['mailing_consent_legal']) ? $options['mailing_consent_legal'] : '';
      $data_processing_id = isset($options['data_processing_consent_legal']) ? $options['data_processing_consent_legal'] : '';

      error_log("Consent document IDs - Privacy: $privacy_policy_id, Mailing: $mailing_consent_id, Data: $data_processing_id");

      // Если нет ID документов, выходим
      if (!$privacy_policy_id && !$mailing_consent_id && !$data_processing_id) {
         error_log('No consent documents configured');
         return false;
      }

      $ip_address = $this->get_client_ip();
      $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
      $timestamp = current_time('mysql');
      $session_id = uniqid('newsletter_', true);

      // Получаем экземпляр менеджера CPT для согласий
      if (!class_exists('Consent_CPT')) {
         error_log('Consent_CPT class not found');
         return false;
      }

      $cpt_manager = Consent_CPT::get_instance();
      if (!$cpt_manager) {
         error_log('Consent CPT manager not available');
         return false;
      }

      // Находим или создаем подписчика в CPT
      $subscriber_id = $cpt_manager->find_or_create_subscriber(
         $email,
         $phone,
         0 // user_id = 0, так как это не пользователь WordPress
      );

      if (!$subscriber_id || is_wp_error($subscriber_id)) {
         error_log('Failed to find or create subscriber in CPT: ' . $email);
         if (is_wp_error($subscriber_id)) {
            error_log('WP Error: ' . $subscriber_id->get_error_message());
         }
         return false;
      }

      error_log("Subscriber ID: $subscriber_id");

      $consent_data = array();

      // Согласие на рассылку
      if ($mailing_consent_id) {
         if (method_exists('Consent_Manager', 'ensure_revision_exists')) {
            Consent_Manager::ensure_revision_exists($mailing_consent_id);
         }
         $revision_link = method_exists('Consent_Manager', 'get_latest_revision_link') ?
            Consent_Manager::get_latest_revision_link($mailing_consent_id) : '';

         $consent_data['mailing_consent'] = array(
            'type' => 'mailing_consent',
            'document_title' => get_the_title($mailing_consent_id),
            'document_url' => get_permalink($mailing_consent_id),
            'ip' => $ip_address,
            'user_agent' => $user_agent,
            'form_title' => __('Newsletter Subscription Form:', 'codeweber') . ' ' . $form_id,
            'session_id' => $session_id,
            'revision' => $revision_link,
            'acceptance_html' => __('I give my consent to receive informational and advertising mailings', 'codeweber'),
            'page_url' => esc_url($_SERVER['HTTP_REFERER'] ?? home_url('/')),
            'phone' => $phone,
            'date' => $timestamp
         );
      }

      // Согласие на обработку данных
      if ($data_processing_id) {
         if (method_exists('Consent_Manager', 'ensure_revision_exists')) {
            Consent_Manager::ensure_revision_exists($data_processing_id);
         }
         $revision_link = method_exists('Consent_Manager', 'get_latest_revision_link') ?
            Consent_Manager::get_latest_revision_link($data_processing_id) : '';

         $consent_data['data_processing'] = array(
            'type' => 'data_processing',
            'document_title' => get_the_title($data_processing_id),
            'document_url' => get_permalink($data_processing_id),
            'ip' => $ip_address,
            'user_agent' => $user_agent,
            'form_title' => __('Newsletter Subscription Form:', 'codeweber') . ' ' . $form_id,
            'session_id' => $session_id,
            'revision' => $revision_link,
            'acceptance_html' => __('I give my consent for processing my personal data', 'codeweber'),
            'page_url' => esc_url($_SERVER['HTTP_REFERER'] ?? home_url('/')),
            'phone' => $phone,
            'date' => $timestamp
         );
      }

      // Согласие с политикой конфиденциальности
      if ($privacy_policy_id) {
         if (method_exists('Consent_Manager', 'ensure_revision_exists')) {
            Consent_Manager::ensure_revision_exists($privacy_policy_id);
         }
         $revision_link = method_exists('Consent_Manager', 'get_latest_revision_link') ?
            Consent_Manager::get_latest_revision_link($privacy_policy_id) : '';

         $consent_data['privacy_policy'] = array(
            'type' => 'privacy_policy',
            'document_title' => get_the_title($privacy_policy_id),
            'document_url' => get_permalink($privacy_policy_id),
            'ip' => $ip_address,
            'user_agent' => $user_agent,
            'form_title' => __('Newsletter Subscription Form:', 'codeweber') . ' ' . $form_id,
            'session_id' => $session_id,
            'revision' => $revision_link,
            'acceptance_html' => __('I am familiar with the personal data processing policy', 'codeweber'),
            'page_url' => esc_url($_SERVER['HTTP_REFERER'] ?? home_url('/')),
            'phone' => $phone,
            'date' => $timestamp
         );
      }

      error_log('Consent data to save: ' . print_r($consent_data, true));

      // Сохраняем все согласия напрямую в CPT
      $all_saved = true;
      foreach ($consent_data as $consent_type => $consent) {
         if (method_exists($cpt_manager, 'add_consent')) {
            $result = $cpt_manager->add_consent($subscriber_id, $consent);
            if (!$result) {
               error_log("Failed to save $consent_type consent for subscriber: $subscriber_id");
               $all_saved = false;
            } else {
               error_log("Successfully saved $consent_type consent for: $email");
            }
         } else {
            error_log("Method add_consent not found in CPT manager");
            $all_saved = false;
         }
      }

      return $all_saved;
   }

   public function send_confirmation_email($email, $first_name, $last_name, $unsubscribe_token)
   {

      // ✅ Убедимся, что переводы темы загружены
      if (!is_textdomain_loaded('codeweber')) {
         load_theme_textdomain('codeweber', get_template_directory() . '/languages');
      }

      
      $options = get_option($this->options_name, array());

      // Получаем настройки с проверкой на пустоту и переводимыми значениями по умолчанию
      $subject = isset($options['email_subject']) && !empty($options['email_subject'])
         ? $options['email_subject']
         : __('Confirming your subscription to the newsletter', 'codeweber');

      $from_email = isset($options['from_email']) && !empty($options['from_email'])
         ? $options['from_email']
         : get_option('admin_email');

      $from_name = isset($options['from_name']) && !empty($options['from_name'])
         ? $options['from_name']
         : get_bloginfo('name');

      // Получаем HTML шаблон из настроек или используем стандартный с переводимыми текстами
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
    word-break: break-all; /* разрешает разрыв внутри слова */
    overflow-wrap: anywhere; /* перенос в любом месте */
    word-wrap: break-word; /* для старых клиентов */
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
            <h1 style="text-align: left;">{email_subject}</h1>
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

      // Заменяем плейсхолдеры в шаблоне
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

      // Устанавливаем заголовки для HTML письма
      $headers = array(
         'Content-Type: text/html; charset=UTF-8',
         'From: ' . $from_name . ' <' . $from_email . '>'
      );

      return wp_mail($email, $subject, $message, $headers);
   }
}

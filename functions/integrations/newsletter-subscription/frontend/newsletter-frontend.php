<?php

/**
 * Newsletter Subscription Frontend Class
 */

if (!defined('ABSPATH')) {
   exit;
}

class NewsletterSubscriptionFrontend
{
   private $table_name;
   private $options_name = 'newsletter_subscription_settings';

   public function __construct()
   {
      global $wpdb;
      $this->table_name = $wpdb->prefix . 'newsletter_subscriptions';

      add_action('template_redirect', array($this, 'handle_unsubscribe_redirect'));
      add_action('wp_footer', array($this, 'unsubscribe_notice'));
   }

   public function handle_unsubscribe_redirect()
   {
      if (
         isset($_GET['action']) && $_GET['action'] === 'newsletter_unsubscribe' &&
         isset($_GET['email']) && isset($_GET['token'])
      ) {

         $email = sanitize_email($_GET['email']);
         $token = sanitize_text_field($_GET['token']);

         $result = $this->process_unsubscribe($email, $token);

         if ($result) {
            wp_redirect(add_query_arg('unsubscribe', 'success', home_url('/')));
         } else {
            wp_redirect(add_query_arg('unsubscribe', 'error', home_url('/')));
         }
         exit;
      }
   }

   public function unsubscribe_notice()
   {
      if (isset($_GET['unsubscribe'])) {
         $options = get_option($this->options_name, array());

         $unsubscribe_success = isset($options['unsubscribe_success']) && !empty($options['unsubscribe_success'])
            ? $options['unsubscribe_success']
            : __('You have successfully unsubscribed from the newsletter', 'codeweber');

         $unsubscribe_message = isset($options['unsubscribe_message']) && !empty($options['unsubscribe_message'])
            ? $options['unsubscribe_message']
            : __('We will no longer send you email notifications.', 'codeweber');

         $unsubscribe_error = isset($options['unsubscribe_error']) && !empty($options['unsubscribe_error'])
            ? $options['unsubscribe_error']
            : __('Unsubscribe Error', 'codeweber');

         $unsubscribe_error_message = isset($options['unsubscribe_error_message']) && !empty($options['unsubscribe_error_message'])
            ? $options['unsubscribe_error_message']
            : __('Failed to unsubscribe from the newsletter. The link may have expired.', 'codeweber');

         // Используем универсальное модальное окно #modal, чтобы избежать дублирования разметки
         $is_success = ($_GET['unsubscribe'] === 'success');

         if ($is_success) {
            $modal_content = '<div class="text-center"><h3 class="text-center">' . esc_html($unsubscribe_success) . '</h3><p>' . esc_html($unsubscribe_message) . '</p></div>';
            $modal_type_class = 'newsletter-unsubscribe-success';
         } elseif ($_GET['unsubscribe'] === 'error') {
            $modal_content = '<div class="text-center"><h3 class="text-center">' . esc_html($unsubscribe_error) . '</h3><p>' . esc_html($unsubscribe_error_message) . '</p></div>';
            $modal_type_class = 'newsletter-unsubscribe-error';
         } else {
            return;
         }

         echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
               var modalElement = document.getElementById("modal");
               var modalContent = document.getElementById("modal-content");

               if (modalElement && modalContent) {
                  // Устанавливаем контент
                  modalContent.innerHTML = ' . json_encode($modal_content) . ';

                  // Добавляем класс для стилизации
                  modalElement.classList.add("' . esc_js($modal_type_class) . '");

                  // Настраиваем размер и выравнивание
                  var modalDialog = modalElement.querySelector(".modal-dialog");
                  if (modalDialog) {
                     modalDialog.classList.add("modal-sm", "modal-dialog-centered", "text-center");
                  }

                  // Открываем модальное окно
                  if (typeof bootstrap !== "undefined" && bootstrap.Modal) {
                     var modalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                     modalInstance.show();
                  }

                  // После закрытия очищаем URL и классы
                  modalElement.addEventListener("hidden.bs.modal", function cleanup() {
                     modalElement.classList.remove("' . esc_js($modal_type_class) . '");
                     if (modalDialog) {
                        modalDialog.classList.remove("modal-sm", "modal-dialog-centered", "text-center");
                     }

                     if (window.history.replaceState && window.location.search.includes("unsubscribe=")) {
                        var newUrl = window.location.href.replace(/([?&])unsubscribe=[^&]*(&|$)/, "$1");
                        newUrl = newUrl.replace(/[?&]$/, "");
                        window.history.replaceState({}, document.title, newUrl);
                     }

                     modalElement.removeEventListener("hidden.bs.modal", cleanup);
                  }, { once: true });
               }
            });
         </script>';
      }
   }

   private function process_unsubscribe($email, $token)
   {
      global $wpdb;

      if (!is_email($email) || !$this->verify_unsubscribe_token($email, $token)) {
         return false;
      }

      // Получаем текущую запись для обновления истории событий
      $subscription = $wpdb->get_row($wpdb->prepare(
         "SELECT * FROM {$this->table_name} WHERE email = %s AND unsubscribe_token = %s AND status = 'confirmed'",
         $email,
         $token
      ));

      $events = [];
      if ($subscription && !empty($subscription->events_history)) {
         $decoded = json_decode($subscription->events_history, true);
         if (is_array($decoded)) {
            $events = $decoded;
         }
      }

      $now = current_time('mysql');
      // Получаем IP-адрес пользователя
      $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
      if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
         // Если используется прокси, берем первый IP из списка
         $forwarded_ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
         $ip_address = trim($forwarded_ips[0]);
      } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
         $ip_address = $_SERVER['HTTP_X_REAL_IP'];
      }
      $ip_address = sanitize_text_field($ip_address);
      
      $events[] = [
         'type'       => 'unsubscribed',
         'date'       => $now,
         'source'     => 'frontend',
         'form_id'    => '', // ИСПРАВЛЕНО: при отписке form_id пустой (отписка не через форму)
         'page_url'   => wp_get_referer() ?: home_url($_SERVER['REQUEST_URI'] ?? '/'),
         'ip_address'=> $ip_address, // Добавляем IP-адрес пользователя
      ];

      $result = $wpdb->update($this->table_name, array(
         'status' => 'unsubscribed',
         'unsubscribed_at' => $now,
         'updated_at' => $now,
         'events_history' => wp_json_encode($events, JSON_UNESCAPED_UNICODE),
      ), array(
         'email' => $email,
         'unsubscribe_token' => $token,
         'status' => 'confirmed'
      ));

      // Отзываем согласие на рассылку при отписке
      if ($result !== false) {
         $this->revoke_mailing_consent($email);
      }

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

   public function send_confirmation_email($email, $first_name, $last_name, $unsubscribe_token)
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

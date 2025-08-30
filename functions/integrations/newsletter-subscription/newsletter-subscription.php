<?php

/**
 * Newsletter Subscription Module
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
      if (!class_exists('WPCF7_Submission')) {
         error_log('WPCF7_Submission class not found!');
         return;
      }

      global $wpdb;
      $this->table_name = $wpdb->prefix . 'newsletter_subscriptions';

      // Эти действия должны работать на фронтенде
      add_action('init', array($this, 'init'));
      add_action('wp_ajax_newsletter_subscription', array($this, 'handle_subscription'));
      add_action('wp_ajax_nopriv_newsletter_subscription', array($this, 'handle_subscription'));
      add_action('wp_ajax_newsletter_unsubscribe', array($this, 'handle_unsubscribe'));
      add_action('wp_ajax_nopriv_newsletter_unsubscribe', array($this, 'handle_unsubscribe'));

      add_action('template_redirect', array($this, 'handle_unsubscribe_redirect'));
      add_action('wp_footer', array($this, 'unsubscribe_notice'));
      add_action('wpcf7_mail_sent', array($this, 'handle_cf7_submission'));

      // Админские функции только в админке
      if (is_admin()) {
         add_action('admin_menu', array($this, 'add_admin_menu'));
         add_action('admin_init', array($this, 'admin_init'));
      }
   }

   public function add_admin_menu()
   {
      // Проверяем, существует ли уже меню "Подписки"
      $menu_exists = false;
      global $menu;
      foreach ($menu as $item) {
         if (isset($item[2]) && $item[2] === 'newsletter-subscriptions') {
            $menu_exists = true;
            break;
         }
      }

      // Если меню существует, добавляем подменю
      if ($menu_exists) {
         add_submenu_page(
            'newsletter-subscriptions',
            __('Mailing Module Settings', 'codeweber'),
            __('Module Settings', 'codeweber'),
            'manage_options',
            'newsletter-subscriptions-module-settings',
            array($this, 'render_settings_page')
         );

         // Добавляем подменю с инструкциями
         add_submenu_page(
            'newsletter-subscriptions',
            __('Form Creation Instructions', 'codeweber'),
            __('Instructions', 'codeweber'),
            'manage_options',
            'newsletter-subscriptions-instructions',
            array($this, 'render_instructions_page')
         );
      }
   }

   public function admin_init()
   {
      register_setting('newsletter_subscription_settings', $this->options_name);

      add_settings_section(
         'newsletter_general_section',
         __('General Module Settings', 'codeweber'),
         array($this, 'general_section_callback'),
         'newsletter-subscriptions-module-settings'
      );

      add_settings_field(
         'enable_cf7_integration',
         __('Contact Form 7 Integration', 'codeweber'),
         array($this, 'checkbox_field_callback'),
         'newsletter-subscriptions-module-settings',
         'newsletter_general_section',
         array(
            'label' => __('Enable automatic subscription from Contact Form 7 forms', 'codeweber'),
            'name' => 'enable_cf7_integration',
            'default' => true
         )
      );

      add_settings_field(
         'send_confirmation_email',
         __('Confirmation Email', 'codeweber'),
         array($this, 'checkbox_field_callback'),
         'newsletter-subscriptions-module-settings',
         'newsletter_general_section',
         array(
            'label' => __('Send subscription confirmation email', 'codeweber'),
            'name' => 'send_confirmation_email',
            'default' => true
         )
      );

      add_settings_field(
         'email_subject',
         __('Email Subject', 'codeweber'),
         array($this, 'text_field_callback'),
         'newsletter-subscriptions-module-settings',
         'newsletter_general_section',
         array(
            'label' => __('Subscription confirmation email subject', 'codeweber'),
            'name' => 'email_subject',
            'default' => __('Subscription Confirmation', 'codeweber'),
            'placeholder' => __('Subscription Confirmation', 'codeweber')
         )
      );

      // В метод admin_init() добавить:
      add_settings_field(
         'email_template',
         __('Email Template', 'codeweber'),
         array($this, 'textarea_field_callback'),
         'newsletter-subscriptions-module-settings',
         'newsletter_general_section',
         array(
            'label' => __('HTML email confirmation template', 'codeweber'),
            'name' => 'email_template',
            'default' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>' . __('Confirming your subscription to the newsletter', 'codeweber') . '</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .button { 
            display: inline-block; 
            padding: 12px 24px; 
            background-color: #dc3545; 
            color: white; 
            text-decoration: none; 
            border-radius: 4px; 
            margin: 20px 0; 
        }
        .button:hover { background-color: #c82333; }
    </style>
</head>
<body>
    <div style="max-width: 600px; margin: 0 auto;">
        <h2>' . __('Hello', 'codeweber') . ', {first_name} {last_name}!</h2>
        <p>' . __('You have successfully subscribed to our newsletter.', 'codeweber') . '</p>
        <p>' . __('If you want to unsubscribe from the newsletter, click the button below:', 'codeweber') . '</p>
        <div style="text-align: center;">
            <a href="{unsubscribe_url}" class="button">' . __('Unsubscribe', 'codeweber') . '</a>
        </div>
        <p>' . __('Or copy and paste the following link into your browser:', 'codeweber') . '<br>
        <a href="{unsubscribe_url}">{unsubscribe_url}</a></p>
        <hr>
        <p style="font-size: 12px; color: #666;">
            ' . __('Best regards,', 'codeweber') . '<br>' . __('Team', 'codeweber') . ' {site_name}
        </p>
    </div>
</body>
</html>',
            'placeholder' => __('HTML email template', 'codeweber'),
            'rows' => 15
         )
      );

      // Добавляем новые поля для выбора legal документов
      add_settings_field(
         'privacy_policy_legal',
         __('Privacy Policy', 'codeweber'),
         array($this, 'legal_dropdown_callback'),
         'newsletter-subscriptions-module-settings',
         'newsletter_general_section',
         array(
            'label' => __('Select privacy policy document', 'codeweber'),
            'name' => 'privacy_policy_legal',
            'default' => ''
         )
      );

      add_settings_field(
         'mailing_consent_legal',
         __('Mailing Consent', 'codeweber'),
         array($this, 'legal_dropdown_callback'),
         'newsletter-subscriptions-module-settings',
         'newsletter_general_section',
         array(
            'label' => __('Select mailing consent document', 'codeweber'),
            'name' => 'mailing_consent_legal',
            'default' => ''
         )
      );

      add_settings_field(
         'data_processing_consent_legal',
         __('Data Processing Consent', 'codeweber'),
         array($this, 'legal_dropdown_callback'),
         'newsletter-subscriptions-module-settings',
         'newsletter_general_section',
         array(
            'label' => __('Select data processing consent document', 'codeweber'),
            'name' => 'data_processing_consent_legal',
            'default' => ''
         )
      );

      add_settings_field(
         'from_email',
         __('Sender Email', 'codeweber'),
         array($this, 'text_field_callback'),
         'newsletter-subscriptions-module-settings',
         'newsletter_general_section',
         array(
            'label' => __('Sender email address', 'codeweber'),
            'name' => 'from_email',
            'default' => get_option('admin_email'),
            'placeholder' => 'noreply@example.com'
         )
      );

      add_settings_field(
         'from_name',
         __('Sender Name', 'codeweber'),
         array($this, 'text_field_callback'),
         'newsletter-subscriptions-module-settings',
         'newsletter_general_section',
         array(
            'label' => __('Sender name in emails', 'codeweber'),
            'name' => 'from_name',
            'default' => get_bloginfo('name'),
            'placeholder' => __('Site Name', 'codeweber')
         )
      );
   }


   // Добавляем метод для выпадающих списков legal документов
   public function legal_dropdown_callback($args)
   {
      $options = get_option($this->options_name, array());
      $selected = isset($options[$args['name']]) ? $options[$args['name']] : $args['default'];

      $legal_documents = get_posts(array(
         'post_type' => 'legal',
         'post_status' => 'publish',
         'numberposts' => -1,
         'orderby' => 'title',
         'order' => 'ASC'
      ));

      echo '<select name="' . $this->options_name . '[' . $args['name'] . ']" class="regular-text">';
      echo '<option value="">' . __('— Select —', 'codeweber') . '</option>';

      foreach ($legal_documents as $document) {
         $is_selected = selected($selected, $document->ID, false);
         echo '<option value="' . esc_attr($document->ID) . '" ' . $is_selected . '>';
         echo esc_html($document->post_title);
         echo '</option>';
      }

      echo '</select>';
      echo '<p class="description">' . esc_html($args['label']) . '</p>';
   }



   // Добавить метод для textarea
   public function textarea_field_callback($args)
   {
      $options = get_option($this->options_name, array());
      $value = isset($options[$args['name']]) ? $options[$args['name']] : $args['default'];

      echo '<textarea name="' . $this->options_name . '[' . $args['name'] . ']" 
          placeholder="' . esc_attr($args['placeholder']) . '" 
          class="large-text code" 
          rows="' . esc_attr($args['rows'] ?? 5) . '" 
          style="font-family: monospace;">' . esc_textarea($value) . '</textarea>';
      echo '<p class="description">' . esc_html($args['label']) . '</p>';
      echo '<p class="description">' . __('Available variables:', 'codeweber') . ' {first_name}, {last_name}, {email}, {unsubscribe_url}, {site_name}</p>';
   }

   public function general_section_callback()
   {
      echo '<p>' . __('Main settings for the newsletter subscription module.', 'codeweber') . '</p>';
   }

   public function checkbox_field_callback($args)
   {
      $options = get_option($this->options_name, array());
      $value = isset($options[$args['name']]) ? $options[$args['name']] : $args['default'];

      echo '<label>';
      echo '<input type="checkbox" name="' . $this->options_name . '[' . $args['name'] . ']" value="1" ' . checked(1, $value, false) . ' />';
      echo ' ' . esc_html($args['label']);
      echo '</label>';
   }

   public function text_field_callback($args)
   {
      $options = get_option($this->options_name, array());
      $value = isset($options[$args['name']]) ? $options[$args['name']] : $args['default'];

      echo '<input type="text" name="' . $this->options_name . '[' . $args['name'] . ']" value="' . esc_attr($value) . '" placeholder="' . esc_attr($args['placeholder']) . '" class="regular-text" />';
      echo '<p class="description">' . esc_html($args['label']) . '</p>';
   }

   public function render_settings_page()
   {
      // Проверяем права пользователя
      if (!current_user_can('manage_options')) {
         wp_die(__('You do not have sufficient permissions to access this page.', 'codeweber'));
      }
?>
      <div class="wrap">
         <h1><?php _e('Mailing Module Settings', 'codeweber'); ?></h1>
         <form method="post" action="options.php">
            <?php
            settings_fields('newsletter_subscription_settings');
            do_settings_sections('newsletter-subscriptions-module-settings');
            submit_button();
            ?>
         </form>

         <div class="card" style="margin-top: 20px;">
            <h2><?php _e('Module Information', 'codeweber'); ?></h2>
            <p><strong><?php _e('Version:', 'codeweber'); ?></strong> <?php echo esc_html($this->version); ?></p>
            <p><strong><?php _e('Database table:', 'codeweber'); ?></strong> <?php echo esc_html($this->table_name); ?></p>
            <p><strong><?php _e('Number of subscribers:', 'codeweber'); ?></strong>
               <?php
               global $wpdb;
               $count = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
               echo esc_html($count);
               ?>
            </p>
            <p><strong><?php _e('Active subscribers:', 'codeweber'); ?></strong>
               <?php
               $active_count = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'confirmed'");
               echo esc_html($active_count);
               ?>
            </p>
         </div>
      </div>
   <?php
   }


   public function init()
   {
      $this->create_table();
      $this->register_shortcode();
      add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
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
                status ENUM('pending', 'confirmed', 'unsubscribed') DEFAULT 'confirmed',
                created_at DATETIME DEFAULT '0000-00-00 00:00:00',
                confirmed_at DATETIME DEFAULT NULL,
                unsubscribed_at DATETIME DEFAULT NULL,
                updated_at DATETIME DEFAULT '0000-00-00 00:00:00',
                unsubscribe_token VARCHAR(100) DEFAULT '',
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


   public function enqueue_scripts()
   {
      wp_enqueue_script(
         'newsletter-subscription',
         get_template_directory_uri() . '/functions/integrations/newsletter-subscription/newsletter-subscription.js',
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
            $send_email = isset($options['send_confirmation_email']) ? $options['send_confirmation_email'] : true;
            if ($send_email) {
               $this->send_confirmation_email($email, $first_name, $last_name, $unsubscribe_token);
            }
         }
      }
   }

   private function register_shortcode()
   {
      add_shortcode('newsletter_form', array($this, 'render_form'));
   }

   public function render_form($atts)
   {
      $options = get_option($this->options_name, array());

      // Получаем ID выбранных legal документов
      $privacy_policy_id = isset($options['privacy_policy_legal']) ? $options['privacy_policy_legal'] : '';
      $mailing_consent_id = isset($options['mailing_consent_legal']) ? $options['mailing_consent_legal'] : '';
      $data_processing_id = isset($options['data_processing_consent_legal']) ? $options['data_processing_consent_legal'] : '';

      // Получаем ссылки на legal документы
      $privacy_policy_link = $privacy_policy_id ? get_permalink($privacy_policy_id) : '#';
      $mailing_consent_link = $mailing_consent_id ? get_permalink($mailing_consent_id) : '#';
      $data_processing_link = $data_processing_id ? get_permalink($data_processing_id) : '#';

      // Фиксированные значения с переводом
      $email_placeholder = __('Your Email', 'codeweber');
      $submit_text = __('Subscribe', 'codeweber');
      $success_message = __('Thank you for subscribing!', 'codeweber');
      $error_message = __('Error occurred. Please try again.', 'codeweber');

      $atts = shortcode_atts(array(
         'id' => 'default',
         'class' => ''
      ), $atts);

      ob_start();
   ?>
      <form action="" method="post" id="newsletter-form-<?php echo esc_attr($atts['id']); ?>"
         class="newsletter-subscription-form <?php echo esc_attr($atts['class']); ?>" novalidate>
         <div class="newsletter-form-inner">
            <div class="form-floating mc-field-group input-group">
               <input type="email" name="email" class="required email form-control"
                  placeholder="<?php echo esc_attr($email_placeholder); ?>"
                  id="newsletter-email-<?php echo esc_attr($atts['id']); ?>"
                  required autocomplete="off">
               <label for="newsletter-email-<?php echo esc_attr($atts['id']); ?>">
                  <?php echo esc_html($email_placeholder); ?>
               </label>

               <button type="submit" name="subscribe" class="btn btn-primary newsletter-submit-btn">
                  <?php echo esc_html($submit_text); ?>
               </button>
            </div>

            <?php
            // Проверяем, выбраны ли обязательные документы
            $has_mailing_consent = !empty($mailing_consent_id);
            $has_data_processing = !empty($data_processing_id);
            $has_privacy_policy = !empty($privacy_policy_id);

            // Если не выбраны обязательные документы
            if (!$has_mailing_consent && !$has_data_processing) {
               echo '<div class="alert alert-danger p-2 mt-2" style="font-size: 12px;">';
               echo __('Documents not selected. Please contact administrator.', 'codeweber');
               echo '</div>';
               echo '<style>#newsletter-form-' . esc_attr($atts['id']) . ' .form-check { display: none; }</style>';
               echo '<style>#newsletter-form-' . esc_attr($atts['id']) . ' button[type="submit"] { opacity: 0.5; pointer-events: none; }</style>';
            } else {
               // Отображаем чекбоксы в зависимости от выбранных документов

               // Первый чекбокс - согласие на рассылку (обязательный)
               if ($has_mailing_consent) {
            ?>
                  <div class="form-check mt-2 small-chekbox">
                     <input type="checkbox" class="form-check-input"
                        id="newsletter-mailing-consent-<?php echo esc_attr($atts['id']); ?>"
                        name="soglasie-na-rassilku" required>
                     <label class="form-check-label" for="newsletter-mailing-consent-<?php echo esc_attr($atts['id']); ?>"
                        style="font-size: 12px;">
                        <?php
                        printf(
                           __('I give my <a class="text-primary" href="%s" target="_blank">consent</a> to receive informational and advertising mailings.', 'codeweber'),
                           esc_url($mailing_consent_link)
                        );
                        ?>
                     </label>
                  </div>
               <?php
               }

               // Второй чекбокс - согласие на обработку данных (обязательный)
               if ($has_data_processing) {
               ?>
                  <div class="form-check mt-0 small-chekbox">
                     <input type="checkbox" class="form-check-input"
                        id="newsletter-data-processing-<?php echo esc_attr($atts['id']); ?>"
                        name="soglasie-na-obrabotku" required>
                     <label class="form-check-label" for="newsletter-data-processing-<?php echo esc_attr($atts['id']); ?>"
                        style="font-size: 12px;">
                        <?php
                        printf(
                           __('I give my <a class="text-primary" href="%s" target="_blank">consent</a> for processing my personal data.', 'codeweber'),
                           esc_url($data_processing_link)
                        );
                        ?>
                     </label>
                  </div>
               <?php
               }

               // Третий чекбокс - ознакомление с политикой (необязательный, только информация)
               if ($has_privacy_policy) {
               ?>
                  <div class="form-check mt-2 small-chekbox">
                     <input type="checkbox" class="form-check-input"
                        id="newsletter-privacy-policy-<?php echo esc_attr($atts['id']); ?>"
                        name="privacy-policy-read" required>
                     <label class="form-check-label" for="newsletter-privacy-policy-<?php echo esc_attr($atts['id']); ?>"
                        style="font-size: 12px;">
                        <?php
                        printf(
                           __('I am familiar with the document <a class="text-primary" href="%s" target="_blank">personal data processing policy</a>.', 'codeweber'),
                           esc_url($privacy_policy_link)
                        );
                        ?>
                     </label>
                  </div>
            <?php
               }
            }
            ?>

            <input type="hidden" name="form_id" value="<?php echo esc_attr($atts['id']); ?>">
            <input type="hidden" name="action" value="newsletter_subscription">
            <?php wp_nonce_field('newsletter_nonce', 'newsletter_nonce'); ?>
            <input type="hidden" name="_wp_http_referer" value="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">

            <div class="newsletter-responses mt-2 fs-12">
               <div class="newsletter-error-response alert alert-danger p-2" style="display: none;">
                  <?php echo esc_html($error_message); ?>
               </div>
               <div class="newsletter-success-response alert alert-success p-2" style="display: none;">
                  <?php echo esc_html($success_message); ?>
               </div>
            </div>
         </div>
      </form>
   <?php
      return ob_get_clean();
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

      $unsubscribe_token = $this->generate_unsubscribe_token($email);

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
         $this->send_confirmation_email($email, $first_name, $last_name, $unsubscribe_token);
         $response['success'] = true;
         $response['message'] = $success_message;
      } else {
         $response['message'] = $error_message;
      }

      wp_send_json($response);
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

         // Перенаправляем на главную с параметром успеха
         if ($result) {
            wp_redirect(add_query_arg('unsubscribe', 'success', home_url('/')));
         } else {
            wp_redirect(add_query_arg('unsubscribe', 'error', home_url('/')));
         }
         exit;
      }
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

   private function send_confirmation_email($email, $first_name, $last_name, $unsubscribe_token)
   {
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

      wp_mail($email, $subject, $message, $headers);
   }

   public function unsubscribe_notice()
   {
      if (isset($_GET['unsubscribe'])) {
         $options = get_option($this->options_name, array());

         // Получаем тексты из настроек или используем переводимые значения по умолчанию
         $close_text = __('Close', 'codeweber');

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

         if ($_GET['unsubscribe'] === 'success') {
            echo '<div class="modal fade modal-popup newsletter-unsubscribe-notice newsletter-unsubscribe-success" id="modal-01" tabindex="-1">
              <div class="modal-dialog modal-dialog-centered modal-sm">
                 <div class="modal-content text-center">
                   <div class="modal-body">
                     <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="' . esc_attr($close_text) . '"></button>
                     <h3 class="text-center">' . esc_html($unsubscribe_success) . '</h3>
                     <p>' . esc_html($unsubscribe_message) . '</p>
                   </div>
                   <!--/.modal-content -->
                 </div>
                 <!--/.modal-body -->
               </div>
              </div>
            <!--/.modal -->
             <script>
                 document.addEventListener("DOMContentLoaded", function() {
                     // Удаляем параметр из URL без перезагрузки
                     if (window.history.replaceState && window.location.search.includes("unsubscribe=")) {
                         var newUrl = window.location.href.replace(/([?&])unsubscribe=[^&]*(&|$)/, "$1");
                         newUrl = newUrl.replace(/[?&]$/, "");
                         window.history.replaceState({}, document.title, newUrl);
                     }
                 });
             </script>
             ';
         } elseif ($_GET['unsubscribe'] === 'error') {
            echo '<div class="modal fade modal-popup newsletter-unsubscribe-notice newsletter-unsubscribe-error" id="modal-02" tabindex="-1">
              <div class="modal-dialog modal-dialog-centered modal-sm">
                 <div class="modal-content text-center">
                   <div class="modal-body">
                     <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="' . esc_attr($close_text) . '"></button>
                     <h3 class="text-center">' . esc_html($unsubscribe_error) . '</h3>
                     <p>' . esc_html($unsubscribe_error_message) . '</p>
                   </div>
                   <!--/.modal-content -->
                 </div>
                 <!--/.modal-body -->
               </div>
              </div>
            <!--/.modal -->
             ';
         }
      }
   }

   private function generate_unsubscribe_token($email)
   {
      return wp_hash($email . 'unsubscribe_salt' . time() . wp_rand());
   }

   private function verify_unsubscribe_token($email, $token)
   {
      global $wpdb;

      // Проверяем, существует ли токен для этого email
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

   // Новый метод для отображения страницы с инструкциями
   public function render_instructions_page()
   {
      if (!current_user_can('manage_options')) {
         wp_die(__('You do not have sufficient permissions to access this page.', 'codeweber'));
      }
   ?>
      <div class="wrap">
         <h1><?php _e('Newsletter Form Creation Instructions', 'codeweber'); ?></h1>

         <div class="notice notice-info">
            <p><?php _e('Detailed guide for creating and configuring email newsletter forms.', 'codeweber'); ?></p>
         </div>

         <div class="card">
            <h2>1. <?php _e('Shortcode form', 'codeweber'); ?></h2>
            <p><?php _e('Use shortcode for quick form addition:', 'codeweber'); ?></p>
            <pre>[newsletter_form id="unique_id" class="your-css-class"]</pre>
            <p><strong><?php _e('Parameters:', 'codeweber'); ?></strong></p>
            <ul>
               <li><code>id</code> - <?php _e('unique form identifier (required)', 'codeweber'); ?></li>
               <li><code>class</code> - <?php _e('additional CSS classes', 'codeweber'); ?></li>
            </ul>
         </div>

         <div class="card">
            <h2>2. <?php _e('HTML form structure', 'codeweber'); ?></h2>
            <p><?php _e('For custom forms use the following HTML structure:', 'codeweber'); ?></p>
            <pre>
&lt;form class="newsletter-subscription-form" method="post"&gt;
   &lt;input type="email" name="email" placeholder="Email" required&gt;
   &lt;input type="text" name="text-name" placeholder="<?php _e('First Name', 'codeweber'); ?>"&gt;
   &lt;input type="text" name="text-surname" placeholder="<?php _e('Last Name', 'codeweber'); ?>"&gt;
   &lt;input type="tel" name="tel" placeholder="<?php _e('Phone', 'codeweber'); ?>"&gt;
   
   &lt;div class="form-check"&gt;
      &lt;input type="checkbox" name="soglasie-na-rassilku" required&gt;
      &lt;label&gt;<?php _e('Consent to receive mailings', 'codeweber'); ?>&lt;/label&gt;
   &lt;/div&gt;
   &lt;div class="form-check"&gt;
      &lt;input type="checkbox" name="soglasie-na-obrabotku" required&gt;
      &lt;label&gt;<?php _e('Consent to data processing', 'codeweber'); ?>&lt;/label&gt;
   &lt;/div&gt;
   
   &lt;input type="hidden" name="action" value="newsletter_subscription"&gt;
   &lt;?php wp_nonce_field('newsletter_nonce', 'newsletter_nonce'); ?&gt;
   
   &lt;button type="submit"&gt;<?php _e('Subscribe', 'codeweber'); ?>&lt;/button&gt;
&lt;/form&gt;
         </pre>
         </div>

         <div class="card">
            <h2>3. <?php _e('Contact Form 7 Integration', 'codeweber'); ?></h2>
            <p><?php _e('For automatic subscription from CF7 forms add checkboxes:', 'codeweber'); ?></p>
            <pre>[checkbox soglasie-na-rassilku use_label_element "1" "<?php _e('I agree to receive newsletter', 'codeweber'); ?>"]</pre>
            <pre>[checkbox soglasie-na-obrabotku use_label_element "1" "<?php _e('I agree to data processing', 'codeweber'); ?>"]</pre>
            <p><strong><?php _e('Required fields in CF7:', 'codeweber'); ?></strong></p>
            <ul>
               <li><code>email-address</code> - <?php _e('email field', 'codeweber'); ?></li>
               <li><code>text-name</code> - <?php _e('name field', 'codeweber'); ?></li>
               <li><code>soglasie-na-rassilku</code> - <?php _e('mailing consent checkbox', 'codeweber'); ?></li>
               <li><code>soglasie-na-obrabotku</code> - <?php _e('data processing consent checkbox', 'codeweber'); ?></li>
            </ul>

            <p><strong><?php _e('Complete CF7 form example:', 'codeweber'); ?></strong></p>
            <pre><code>&lt;h2 class="mb-3 text-start"&gt;<?php _e('Request a callback', 'codeweber'); ?>&lt;/h2&gt;
&lt;p class="lead mb-6 text-start"&gt;<?php _e('We will call back within 15 minutes', 'codeweber'); ?>&lt;/p&gt;

&lt;div class="form-floating mb-3 text-dark"&gt; 
  [text* text-name id:floatingName class:form-control placeholder "<?php _e('Your Name', 'codeweber'); ?>"]
  &lt;label for="floatingName"&gt;<?php _e('Your Name', 'codeweber'); ?>&lt;/label&gt;
&lt;/div&gt;
&lt;div class="form-floating mb-3 text-dark"&gt; 
  [text* text-lastname id:floatingName1 class:form-control placeholder "<?php _e('Your Last Name', 'codeweber'); ?>"]
  &lt;label for="floatingName1"&gt;<?php _e('Your Last Name', 'codeweber'); ?>&lt;/label&gt;
&lt;/div&gt;
&lt;div class="form-floating mb-3 text-dark"&gt; 
  [email* email-address id:floatingEmail class:form-control placeholder "<?php _e('Your Email', 'codeweber'); ?>"]
  &lt;label for="floatingEmail"&gt;<?php _e('Your Email', 'codeweber'); ?>&lt;/label&gt;
&lt;/div&gt;
&lt;div class="form-floating mb-3 text-dark"&gt; 
  [tel* tel-463 id:floatingTel class:phone-mask class:form-control placeholder "+7(000)123-45-67"]
  &lt;label for="floatingTel"&gt;+7(000)123-45-67&lt;/label&gt;
&lt;/div&gt;
&lt;div class="form-check mb-2 fs-12 small-chekbox wpcf7-acceptance"&gt;
  [acceptance soglasie-na-obrabotku id:flexCheckDefault1 class:form-check-input use_label_element]
  &lt;label for="flexCheckDefault1" class="form-check-label text-start"&gt;
    <?php _e('I give my', 'codeweber'); ?> &lt;a class="text-primary" href="[cf7_data_processing_link]" target="_blank"&gt;<?php _e('consent', 'codeweber'); ?>&lt;/a&gt; <?php _e('for processing my personal data.', 'codeweber'); ?>
  &lt;/label&gt;
&lt;/div&gt;
&lt;div class="form-check mb-3 fs-12 small-chekbox"&gt;
  [acceptance soglasie-na-rassilku id:flexCheckDefault14 class:form-check-input class:optional use_label_element optional]
  &lt;label for="flexCheckDefault14" class="form-check-label text-start"&gt;
    <?php _e('I give my', 'codeweber'); ?> &lt;a class="text-primary" href="[cf7_mailing_consent_link]" target="_blank"&gt;<?php _e('consent', 'codeweber'); ?>&lt;/a&gt; <?php _e('to receive informational and promotional newsletters', 'codeweber'); ?>
  &lt;/label&gt;
&lt;/div&gt;
&lt;div class="form-check mb-3 fs-12 small-chekbox"&gt;
  [acceptance privacy-policy-read id:flexCheckDefault15 class:form-check-input use_label_element]
  &lt;label for="flexCheckDefault15" class="form-check-label text-start"&gt;
    <?php _e('I am familiar with the document', 'codeweber'); ?> &lt;a href="[cf7_privacy_policy]" target="_blank"&gt;<?php _e('personal data processing policy', 'codeweber'); ?>&lt;/a&gt;.
  &lt;/label&gt;
&lt;/div&gt;
&lt;button type="submit" class="wpcf7-submit has-ripple btn [getthemebutton] btn-md btn-primary mx-5 mx-md-0"&gt;
  <?php _e('Send', 'codeweber'); ?>
&lt;/button&gt;</code></pre>
         </div>

         <div class="card">
            <h2>4. <?php _e('Custom form fields', 'codeweber'); ?></h2>
            <table class="widefat fixed" style="margin: 15px 0;">
               <thead>
                  <tr>
                     <th><?php _e('Field name', 'codeweber'); ?></th>
                     <th><?php _e('Type', 'codeweber'); ?></th>
                     <th><?php _e('Required', 'codeweber'); ?></th>
                     <th><?php _e('Description', 'codeweber'); ?></th>
                  </tr>
               </thead>
               <tbody>
                  <tr>
                     <td><code>email</code></td>
                     <td>email</td>
                     <td><?php _e('Yes', 'codeweber'); ?></td>
                     <td><?php _e('Subscriber email address', 'codeweber'); ?></td>
                  </tr>
                  <tr>
                     <td><code>text-name</code></td>
                     <td>text</td>
                     <td><?php _e('No', 'codeweber'); ?></td>
                     <td><?php _e('Subscriber first name', 'codeweber'); ?></td>
                  </tr>
                  <tr>
                     <td><code>text-surname</code></td>
                     <td>text</td>
                     <td><?php _e('No', 'codeweber'); ?></td>
                     <td><?php _e('Subscriber last name', 'codeweber'); ?></td>
                  </tr>
                  <tr>
                     <td><code>tel</code></td>
                     <td>tel</td>
                     <td><?php _e('No', 'codeweber'); ?></td>
                     <td><?php _e('Subscriber phone', 'codeweber'); ?></td>
                  </tr>
                  <tr>
                     <td><code>soglasie-na-rassilku</code></td>
                     <td>checkbox</td>
                     <td><?php _e('Yes', 'codeweber'); ?></td>
                     <td><?php _e('Consent to receive mailings', 'codeweber'); ?></td>
                  </tr>
                  <tr>
                     <td><code>soglasie-na-obrabotku</code></td>
                     <td>checkbox</td>
                     <td><?php _e('Yes', 'codeweber'); ?></td>
                     <td><?php _e('Consent to data processing', 'codeweber'); ?></td>
                  </tr>
               </tbody>
            </table>
         </div>

         <div class="card">
            <h2>5. <?php _e('Email Template', 'codeweber'); ?></h2>
            <p><?php _e('HTML template for confirmation emails:', 'codeweber'); ?></p>
            <pre>&lt;!DOCTYPE html&gt;
&lt;html&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;style&gt;
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
    &lt;/style&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;div class="email-container"&gt;
        &lt;div class="header"&gt;
            &lt;h1&gt;{email_subject}&lt;/h1&gt;
        &lt;/div&gt;

        &lt;h2&gt;Hello, {first_name} {last_name}!&lt;/h2&gt;
        &lt;p&gt;You have successfully subscribed to our newsletter.&lt;/p&gt;
        
        &lt;p&gt;If you want to unsubscribe from the newsletter, click the button below:&lt;/p&gt;
        
        &lt;div class="text-center"&gt;
            &lt;a href="{unsubscribe_url}" class="button"&gt;Unsubscribe&lt;/a&gt;
        &lt;/div&gt;
        
        &lt;div class="divider"&gt;&lt;/div&gt;
        
        &lt;p&gt;Or copy and paste the following link into your browser:&lt;/p&gt;
        &lt;p&gt;&lt;a href="{unsubscribe_url}"&gt;{unsubscribe_url}&lt;/a&gt;&lt;/p&gt;

        &lt;div class="footer"&gt;
            &lt;p style="font-size: 12px; color: #666;"&gt;
                Best regards,&lt;br&gt;
                &lt;strong&gt;Team {site_name}&lt;/strong&gt;
            &lt;/p&gt;
            &lt;p style="font-size: 11px; color: #999;"&gt;
                This email was sent to {email} because you subscribed to our newsletter.&lt;br&gt;
                If you have any questions, please contact our support team.
            &lt;/p&gt;
        &lt;/div&gt;
    &lt;/div&gt;
&lt;/body&gt;
&lt;/html&gt;</pre>
            <p><strong><?php _e('Available variables:', 'codeweber'); ?></strong></p>
            <ul>
               <li><code>{email_subject}</code> - <?php _e('Email subject', 'codeweber'); ?></li>
               <li><code>{first_name}</code> - <?php _e('Subscriber first name', 'codeweber'); ?></li>
               <li><code>{last_name}</code> - <?php _e('Subscriber last name', 'codeweber'); ?></li>
               <li><code>{email}</code> - <?php _e('Subscriber email', 'codeweber'); ?></li>
               <li><code>{unsubscribe_url}</code> - <?php _e('Unsubscribe link', 'codeweber'); ?></li>
               <li><code>{site_name}</code> - <?php _e('Website name', 'codeweber'); ?></li>
            </ul>
         </div>

         <div class="card">
            <h2>6. <?php _e('JavaScript events', 'codeweber'); ?></h2>
            <p><?php _e('For custom processing you can use events:', 'codeweber'); ?></p>
            <pre>
document.addEventListener('newsletter_subscription_success', function(e) {
    console.log('<?php _e('Subscription successful', 'codeweber'); ?>', e.detail);
    // <?php _e('Your code for successful subscription', 'codeweber'); ?>
});

document.addEventListener('newsletter_subscription_error', function(e) {
    console.log('<?php _e('Subscription error', 'codeweber'); ?>', e.detail);
    // <?php _e('Your code for subscription error', 'codeweber'); ?>
});

// <?php _e('Example of showing notification', 'codeweber'); ?>
document.addEventListener('newsletter_subscription_success', function(e) {
    alert('<?php _e('Thank you for subscribing! A confirmation email has been sent to your email.', 'codeweber'); ?>');
});
         </pre>
         </div>
      </div>

      <style>
         .card {
            background: #fff;
            border: 1px solid #ccd0d4;
            margin: 20px 0;
            padding: 20px;
            border-radius: 4px;
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
         }

         .card h2 {
            border-bottom: 2px solid #0073aa;
            padding-bottom: 10px;
            margin-top: 0;
         }

         .card h3 {
            color: #0073aa;
            margin: 20px 0 10px;
         }

         pre {
            background: #f6f8fa;
            padding: 15px;
            border-radius: 5px;
            overflow: auto;
            border: 1px solid #e1e4e8;
            font-family: 'Consolas', 'Monaco', monospace;
            line-height: 1.4;
         }

         table.widefat {
            margin: 15px 0;
            border-collapse: collapse;
            width: 100%;
         }

         table.widefat th {
            background: #f8f9fa;
            font-weight: 600;
            padding: 10px;
            border: 1px solid #e1e4e8;
         }

         table.widefat td {
            padding: 10px;
            border: 1px solid #e1e4e8;
         }

         code {
            background: #f6f8fa;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Consolas', 'Monaco', monospace;
         }

         ul {
            line-height: 1.6;
         }
      </style>
<?php
   }
}

new NewsletterSubscription();

add_action('init', function () {
   new NewsletterSubscription();
}, 20);

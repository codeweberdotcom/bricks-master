<?php

/**
 * Newsletter Subscription Settings Class
 */

if (!defined('ABSPATH')) {
   exit;
}

class NewsletterSubscriptionSettings
{
   private $options_name = 'newsletter_subscription_settings';

   public function __construct()
   {
      add_action('admin_menu', array($this, 'add_admin_menu'));
      add_action('admin_init', array($this, 'admin_init'));
   }

   public function add_admin_menu()
   {
      add_submenu_page(
         'newsletter-subscriptions',
         __('Mailing Module Settings', 'codeweber'),
         __('Module Settings', 'codeweber'),
         'manage_options',
         'newsletter-subscriptions-module-settings',
         array($this, 'render_settings_page')
      );

      add_submenu_page(
         'newsletter-subscriptions',
         __('Form Creation Instructions', 'codeweber'),
         __('Instructions', 'codeweber'),
         'manage_options',
         'newsletter-subscriptions-instructions',
         array($this, 'render_instructions_page')
      );
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
            <p><strong><?php _e('Version:', 'codeweber'); ?></strong> 1.0.2</p>
            <p><strong><?php _e('Database table:', 'codeweber'); ?></strong> <?php echo esc_html($GLOBALS['wpdb']->prefix . 'newsletter_subscriptions'); ?></p>
            <p><strong><?php _e('Number of subscribers:', 'codeweber'); ?></strong>
               <?php
               global $wpdb;
               $count = $wpdb->get_var("SELECT COUNT(*) FROM {$GLOBALS['wpdb']->prefix}newsletter_subscriptions");
               echo esc_html($count);
               ?>
            </p>
            <p><strong><?php _e('Active subscribers:', 'codeweber'); ?></strong>
               <?php
               $active_count = $wpdb->get_var("SELECT COUNT(*) FROM {$GLOBALS['wpdb']->prefix}newsletter_subscriptions WHERE status = 'confirmed'");
               echo esc_html($active_count);
               ?>
            </p>
         </div>
      </div>
   <?php
   }

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

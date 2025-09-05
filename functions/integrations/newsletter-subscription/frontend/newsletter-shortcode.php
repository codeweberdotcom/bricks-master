<?php

/**
 * Newsletter Subscription Shortcode Class
 */

if (!defined('ABSPATH')) {
   exit;
}

class NewsletterSubscriptionShortcode
{
   private $options_name = 'newsletter_subscription_settings';

   public function __construct()
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
}

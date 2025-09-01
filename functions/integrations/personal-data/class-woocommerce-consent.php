<?php

/**
 * Обработка согласий в WooCommerce
 */

class WooCommerce_Consent
{

   private static $instance = null;

   public static function get_instance()
   {
      if (null === self::$instance) {
         self::$instance = new self();
      }
      return self::$instance;
   }

   private function __construct()
   {
      $this->init_hooks();
   }

   private function init_hooks()
   {
      remove_action('woocommerce_register_form', 'wc_registration_privacy_policy_text', 20);

      add_action('woocommerce_register_form', [$this, 'custom_privacy_policy_text'], 20);
      add_action('woocommerce_register_form', [$this, 'add_consent_fields']);
      add_filter('woocommerce_registration_errors', [$this, 'validate_consents'], 10, 3);
   }

   /**
    * Кастомный текст политики конфиденциальности
    */
   public function custom_privacy_policy_text()
   {
      if (wc_get_privacy_policy_text('registration')) : ?>
         <div class="woocommerce-privacy-policy-text custom-privacy-text fs-12">
            <?php wc_privacy_policy_text('registration'); ?>
         </div>
      <?php endif;
   }

   /**
    * Добавить поля согласий
    */
   public function add_consent_fields()
   {
      $privacy_page_id = (int) get_option('wp_page_for_privacy_policy');
      $privacy_url = $privacy_page_id ? get_permalink($privacy_page_id) : '';

      $urls = Consent_Manager::get_document_urls();
      $processing_url = $urls['processing']['url']($urls['processing']['id']);

      // Проверка наличия ошибок Woo
      $errors = wc_get_notices('error');
      $has_privacy_error = false;
      $has_pdn_error = false;

      foreach ($errors as $error) {
         if (strpos($error['notice'], __('You must agree to the Privacy Policy.', 'codeweber')) !== false) {
            $has_privacy_error = true;
         }
         if (strpos($error['notice'], __('You must agree to the processing of personal data.', 'codeweber')) !== false) {
            $has_pdn_error = true;
         }
      }
      ?>

      <p class="form-row-wide woocommerce-FormRow form-check mb-2 small-chekbox fs-12">
         <input type="checkbox"
            class="form-check-input <?php echo $has_privacy_error ? 'is-invalid' : ''; ?>"
            name="privacy_policy_consent"
            id="privacy_policy_consent"
            value="1"
            <?php checked(!empty($_POST['privacy_policy_consent'])); ?>
            required>
         <label for="privacy_policy_consent" class="form-check-label">
            <?php
            if ($privacy_url) {
               printf(
                  __('I have read and agree to the <a href="%s" target="_blank">Privacy Policy</a>', 'codeweber'),
                  esc_url($privacy_url)
               );
            } else {
               echo __('I have read and agree to the Privacy Policy', 'codeweber');
               echo ' <span style="color:red; font-weight:bold;">(' . __('No Privacy Policy page selected by administrator', 'codeweber') . ')</span>';
            }
            ?>
         </label>
         <?php if ($has_privacy_error) : ?>
      <div class="invalid-feedback d-block">
         <?php _e('You must agree to the Privacy Policy.', 'codeweber'); ?>
      </div>
   <?php endif; ?>
   </p>

   <p class="form-row-wide woocommerce-FormRow form-check mb-2 small-chekbox fs-12">
      <input type="checkbox"
         class="form-check-input <?php echo $has_pdn_error ? 'is-invalid' : ''; ?>"
         name="pdn_consent"
         id="pdn_consent"
         value="1"
         <?php checked(!empty($_POST['pdn_consent'])); ?>
         required>
      <label for="pdn_consent" class="form-check-label">
         <?php
         if ($processing_url) {
            printf(
               __('I agree to the <a href="%s" target="_blank">processing of personal data</a>', 'codeweber'),
               esc_url($processing_url)
            );
         } else {
            echo __('I agree to the processing of personal data', 'codeweber');
            echo ' <span style="color:red; font-weight:bold;">(' . __('No consent document selected by administrator', 'codeweber') . ')</span>';
         }
         ?>
      </label>
      <?php if ($has_pdn_error) : ?>
   <div class="invalid-feedback d-block">
      <?php _e('You must agree to the processing of personal data.', 'codeweber'); ?>
   </div>
<?php endif; ?>
</p>

<?php
   }

   /**
    * Проверить согласия
    */
   public function validate_consents($errors, $username, $email)
   {
      if (empty($_POST['privacy_policy_consent'])) {
         $errors->add('privacy_policy_consent_error', __('You must agree to the Privacy Policy.', 'codeweber'));
      }
      if (empty($_POST['pdn_consent'])) {
         $errors->add('pdn_consent_error', __('You must agree to the processing of personal data.', 'codeweber'));
      }
      return $errors;
   }
}

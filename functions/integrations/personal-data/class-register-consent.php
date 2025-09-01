<?php

/**
 * Обработка согласий в форме регистрации WordPress
 */

class Register_Consent
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
      add_action('register_form', [$this, 'add_consent_fields']);
      add_filter('registration_errors', [$this, 'validate_consents'], 10, 3);
      add_action('user_register', [$this, 'save_consents']);
   }

   /**
    * Добавить поля согласий на форму регистрации
    */
   public function add_consent_fields()
   {
      $urls = Consent_Manager::get_document_urls();

      $privacy_url = $urls['privacy']['url']($urls['privacy']['id']);
      $processing_url = $urls['processing']['url']($urls['processing']['id']);
?>
      <p>
         <label>
            <input type="checkbox" name="privacy_policy_consent" value="1" <?php checked(!empty($_POST['privacy_policy_consent'])); ?> required>
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
      </p>
      <p>
         <label>
            <input type="checkbox" name="pdn_consent" value="1" <?php checked(!empty($_POST['pdn_consent'])); ?> required>
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
      </p>
<?php
   }

   /**
    * Проверить согласия
    */
   public function validate_consents($errors, $sanitized_user_login, $user_email)
   {
      if (empty($_POST['privacy_policy_consent'])) {
         $errors->add('privacy_policy_consent_error', __('You must agree to the Privacy Policy.', 'codeweber'));
      }
      if (empty($_POST['pdn_consent'])) {
         $errors->add('pdn_consent_error', __('You must agree to the processing of personal data.', 'codeweber'));
      }
      return $errors;
   }

   /**
    * Сохранить согласия
    */
   public function save_consents($user_id)
   {
      $consent_data = [
         'privacy_policy_consent' => !empty($_POST['privacy_policy_consent']),
         'pdn_consent' => !empty($_POST['pdn_consent'])
      ];

      Consent_Manager::save_user_consents($user_id, $consent_data);
   }
}

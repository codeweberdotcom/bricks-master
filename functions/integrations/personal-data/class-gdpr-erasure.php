<?php

/**
 * GDPR удаление пользовательских согласий
 */

class GDPR_Erasure
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
      add_filter('wp_privacy_personal_data_erasers', [$this, 'register_eraser']);
   }

   /**
    * Зарегистрировать eraser
    */
   public function register_eraser($erasers)
   {
      $erasers['codeweber_user_consents'] = [
         'eraser_friendly_name' => __('User Consents', 'codeweber'),
         'callback'             => [$this, 'erase_consents'],
      ];
      return $erasers;
   }

   /**
    * Удалить согласия (GDPR compliant)
    */
   public function erase_consents($email_address, $page = 1)
   {
      $user = get_user_by('email', $email_address);

      if (!$user) {
         return [
            'items_removed'  => false,
            'items_retained' => false,
            'messages'       => [__('User not found', 'codeweber')],
            'done'           => true,
         ];
      }

      // Удаляем согласия через менеджер
      $result = Consent_Manager::delete_user_consents($user->ID);

      return [
         'items_removed'  => $result,
         'items_retained' => false,
         'messages'       => $result ?
            [__('All user consents have been removed.', 'codeweber')] :
            [__('No user consents found to remove.', 'codeweber')],
         'done'           => true,
      ];
   }
}

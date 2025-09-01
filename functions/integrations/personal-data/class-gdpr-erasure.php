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
    * Удалить согласия
    */
   public function erase_consents($email_address, $page = 1)
   {
      $user = get_user_by('email', $email_address);
      if (!$user) {
         return [
            'items_removed'  => [],
            'items_retained' => [],
            'done'           => true,
         ];
      }

      $meta = get_user_meta($user->ID, 'codeweber_user_consents', true);

      if (empty($meta)) {
         return [
            'items_removed'  => [],
            'items_retained' => [],
            'done'           => true,
         ];
      }

      $deleted = delete_user_meta($user->ID, 'codeweber_user_consents');

      return [
         'items_removed'  => $deleted ? [
            [
               'id'    => 'codeweber_user_consents',
               'name'  => __('User Consents', 'codeweber'),
            ]
         ] : [],
         'items_retained' => [],
         'done'           => true,
      ];
   }
}

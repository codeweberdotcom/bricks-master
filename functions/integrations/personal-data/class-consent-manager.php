<?php

/**
 * Менеджер пользовательских согласий
 */

class Consent_Manager
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
      $this->init();
   }

   private function init()
   {
      // Подключаем компоненты
      require_once __DIR__ . '/class-consent-cpt.php';
      require_once __DIR__ . '/class-register-consent.php';
      require_once __DIR__ . '/class-woocommerce-consent.php';
      require_once __DIR__ . '/class-gdpr-export.php';
      require_once __DIR__ . '/class-gdpr-erasure.php';

      // Инициализируем компоненты
      $this->initialize_components();
   }

   /**
    * Инициализация всех компонентов
    */
   private function initialize_components()
   {
      // Инициализируем CPT с приоритетом 5
      add_action('init', [Consent_CPT::get_instance(), 'register_post_type'], 5);

      // Инициализируем остальные компоненты
      add_action('init', function () {
         Register_Consent::get_instance();
         WooCommerce_Consent::get_instance();
         GDPR_Export::get_instance();
         GDPR_Erasure::get_instance();
      }, 6);
   }

   /**
    * Получить ссылку на последнюю ревизию документа
    */
   public static function get_latest_revision_link($post_id)
   {
      if (!$post_id) {
         return '';
      }

      $revisions = wp_get_post_revisions($post_id, [
         'numberposts' => 1,
         'order' => 'DESC'
      ]);

      if (empty($revisions)) {
         return '';
      }

      // Получаем самую последнюю ревизию
      $last_revision = reset($revisions);
      $rev_id = $last_revision->ID;
      $rev_date = date_i18n('Y-m-d H:i', strtotime($last_revision->post_modified));
      $link = admin_url('revision.php?revision=' . $rev_id);

      return sprintf(
         '<a href="%s" target="_blank">%s</a>',
         esc_url($link),
         sprintf(
            __('Version from %s', 'codeweber'),
            esc_html($rev_date)
         )
      );
   }

   /**
    * Сохранить согласия пользователя
    */
   public static function save_user_consents($user_id, $consent_data)
   {
      if (!is_numeric($user_id) || $user_id <= 0) {
         error_log('Invalid user ID in save_user_consents: ' . $user_id);
         return;
      }

      $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
      $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
      $timestamp = current_time('mysql');

      $privacy_doc_id = (int) get_option('codeweber_legal_consent_privacy');
      $processing_doc_id = (int) get_option('codeweber_legal_consent_processing');

      $consents = [];
      $session_id = uniqid('wp_', true);

      // Согласие на политику конфиденциальности
      if (!empty($consent_data['privacy_policy_consent'])) {
         $consent_entry = [
            'title'      => $privacy_doc_id ? get_the_title($privacy_doc_id) : __('Privacy Policy', 'codeweber'),
            'url'        => $privacy_doc_id ? get_permalink($privacy_doc_id) : '',
            'ip'         => $ip_address,
            'user_agent' => $user_agent,
            'date'       => $timestamp,
            'session_id'  => $session_id,
            'revision'    => $privacy_doc_id ? self::get_latest_revision_link($privacy_doc_id) : '',
            'form_title'  => __('WordPress Register Form', 'codeweber'),
            'page_url'   => esc_url(site_url('/wp-login.php?action=register')),
         ];

         $consents['privacy_policy'] = $consent_entry;

         // Сохраняем в CPT
         self::save_consent_to_cpt($user_id, array_merge($consent_entry, [
            'type' => 'privacy_policy',
            'acceptance_html' => __('I have read and agree to the Privacy Policy', 'codeweber')
         ]));
      }

      // Согласие на обработку персональных данных
      if (!empty($consent_data['pdn_consent'])) {
         $consent_entry = [
            'title'      => $processing_doc_id ? get_the_title($processing_doc_id) : __('Data Processing Agreement', 'codeweber'),
            'url'        => $processing_doc_id ? get_permalink($processing_doc_id) : '',
            'ip'         => $ip_address,
            'user_agent' => $user_agent,
            'date'       => $timestamp,
            'session_id'  => $session_id,
            'revision'    => $processing_doc_id ? self::get_latest_revision_link($processing_doc_id) : '',
            'form_title'  => __('WordPress Register Form', 'codeweber'),
            'page_url'   => esc_url(site_url('/wp-login.php?action=register')),
         ];

         $consents['pdn_processing'] = $consent_entry;

         // Сохраняем в CPT
         self::save_consent_to_cpt($user_id, array_merge($consent_entry, [
            'type' => 'pdn_processing',
            'acceptance_html' => __('I agree to the processing of personal data', 'codeweber')
         ]));
      }

      if (!empty($consents)) {
         update_user_meta($user_id, 'codeweber_user_consents', $consents);
      }
   }

   /**
    * Получить URL документов
    */
   public static function get_document_urls()
   {
      return [
         'privacy' => [
            'id' => (int) get_option('codeweber_legal_privacy-policy'),
            'url' => function ($id) {
               return ($id && get_post_status($id) === 'publish') ? get_permalink($id) : '';
            }
         ],
         'processing' => [
            'id' => (int) get_option('codeweber_legal_consent_processing'),
            'url' => function ($id) {
               return ($id && get_post_status($id) === 'publish') ? get_permalink($id) : '';
            }
         ]
      ];
   }

   /**
    * Сохранить согласия в CPT подписчика
    */
   public static function save_consent_to_cpt($user_id, $consent_data)
   {
      $user = get_user_by('id', $user_id);
      if (!$user) {
         error_log('User not found in save_consent_to_cpt: ' . $user_id);
         return false;
      }

      $cpt_manager = Consent_CPT::get_instance();
      if (!$cpt_manager) {
         error_log('Consent_CPT instance not available');
         return false;
      }

      // Получаем телефон пользователя
      $phone = get_user_meta($user_id, 'phone', true);
      if (!$phone) {
         $phone = get_user_meta($user_id, 'billing_phone', true);
      }

      // Находим или создаем подписчика
      $subscriber_id = $cpt_manager->find_or_create_subscriber(
         $user->user_email,
         $phone,
         $user_id
      );

      if (!$subscriber_id || is_wp_error($subscriber_id)) {
         error_log('Failed to find or create subscriber for user: ' . $user_id);
         return false;
      }

      // Подготавливаем данные для сохранения
      $consent_data_cpt = [
         'type' => $consent_data['type'] ?? '',
         'document_title' => $consent_data['title'] ?? '',
         'document_url' => $consent_data['url'] ?? '',
         'ip' => $consent_data['ip'] ?? '',
         'user_agent' => $consent_data['user_agent'] ?? '',
         'form_title' => $consent_data['form_title'] ?? '',
         'session_id' => $consent_data['session_id'] ?? '',
         'revision' => $consent_data['revision'] ?? '',
         'acceptance_html' => $consent_data['acceptance_html'] ?? '',
         'page_url' => $consent_data['page_url'] ?? '',
         'phone' => $phone,
         'date' => $consent_data['date'] ?? current_time('mysql')
      ];

      // Добавляем согласие
      $result = $cpt_manager->add_consent($subscriber_id, $consent_data_cpt);

      if (!$result) {
         error_log('Failed to add consent for subscriber: ' . $subscriber_id);
      }

      return $result;
   }

   /**
    * Получить согласия пользователя
    */
   public static function get_user_consents($user_id)
   {
      return get_user_meta($user_id, 'codeweber_user_consents', true) ?: [];
   }

   /**
    * Удалить согласия пользователя
    */
   public static function delete_user_consents($user_id)
   {
      // Удаляем из user meta
      delete_user_meta($user_id, 'codeweber_user_consents');

      // Также можно удалить из CPT если нужно
      // $cpt_manager = Consent_CPT::get_instance();
      // $subscriber = $cpt_manager->get_subscriber_by_user_id($user_id);
      // if ($subscriber) {
      //     wp_delete_post($subscriber->ID, true);
      // }
   }
}

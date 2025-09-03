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
      require_once __DIR__ . '/class-cf7-consent.php';
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
    * Получить ссылку на последнюю ревизию документа или текущую версию
    */
   public static function get_latest_revision_link($post_id)
   {
      if (!$post_id) {
         return '';
      }

      // Пытаемся получить ревизии
      $revisions = wp_get_post_revisions($post_id, [
         'numberposts' => 1,
         'order' => 'DESC'
      ]);

      if (!empty($revisions)) {
         // Получаем самую последнюю ревизию
         $last_revision = reset($revisions);
         $rev_id = $last_revision->ID;
         $rev_date = date_i18n('Y-m-d H:i', strtotime($last_revision->post_modified));
         $link = admin_url('revision.php?revision=' . $rev_id);


         $result = sprintf(
            '<a href="%s" target="_blank">%s</a>',
            esc_url($link),
            sprintf(
               __('Version from %s', 'codeweber'),
               esc_html($rev_date)
            )
         );
      } else {
         // Если ревизий нет, используем текущую версию документа
         $post = get_post($post_id);
         if ($post) {
            $post_date = date_i18n('Y-m-d H:i', strtotime($post->post_modified));
            $link = get_permalink($post_id);


            $result = sprintf(
               '<a href="%s" target="_blank">%s</a>',
               esc_url($link),
               sprintf(
                  __('Current version from %s', 'codeweber'),
                  esc_html($post_date)
               )
            );
         } else {
            $result = '';
         }
      }

      return $result;
   }

   /**
    * Сохранить согласия пользователя (только в CPT)
    */
   public static function save_user_consents($user_id, $consent_data)
   {
      if (!is_numeric($user_id) || $user_id <= 0) {
         return;
      }

      $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
      $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
      $timestamp = current_time('mysql');

      $privacy_doc_id = (int) get_option('codeweber_legal_privacy-policy');
      $processing_doc_id = (int) get_option('codeweber_legal_consent_processing');

      $session_id = uniqid('wp_', true);

      // Согласие на политику конфиденциальности
      if (!empty($consent_data['privacy_policy_consent'])) {
         // Убедимся что есть ревизия
         if ($privacy_doc_id) {
            self::ensure_revision_exists($privacy_doc_id);
         }

         $revision_link = $privacy_doc_id ? self::get_latest_revision_link($privacy_doc_id) : '';

         $consent_entry = [
            'title'      => $privacy_doc_id ? get_the_title($privacy_doc_id) : __('Privacy Policy', 'codeweber'),
            'url'        => $privacy_doc_id ? get_permalink($privacy_doc_id) : '',
            'ip'         => $ip_address,
            'user_agent' => $user_agent,
            'date'       => $timestamp,
            'session_id'  => $session_id,
            'revision'    => $revision_link,
            'form_title'  => __('WordPress Register Form', 'codeweber'),
            'page_url'   => esc_url(site_url('/wp-login.php?action=register')),
         ];

         // Сохраняем в CPT
         self::save_consent_to_cpt($user_id, array_merge($consent_entry, [
            'type' => 'privacy_policy',
            'acceptance_html' => __('I have read and agree to the Privacy Policy', 'codeweber'),
            'revision' => $revision_link
         ]));
      }

      // Согласие на обработку персональных данных
      if (!empty($consent_data['pdn_consent'])) {
         // Убедимся что есть ревизия
         if ($processing_doc_id) {
            self::ensure_revision_exists($processing_doc_id);
         }

         $revision_link = $processing_doc_id ? self::get_latest_revision_link($processing_doc_id) : '';

         $consent_entry = [
            'title'      => $processing_doc_id ? get_the_title($processing_doc_id) : __('Data Processing Agreement', 'codeweber'),
            'url'        => $processing_doc_id ? get_permalink($processing_doc_id) : '',
            'ip'         => $ip_address,
            'user_agent' => $user_agent,
            'date'       => $timestamp,
            'session_id'  => $session_id,
            'revision'    => $revision_link,
            'form_title'  => __('WordPress Register Form', 'codeweber'),
            'page_url'   => esc_url(site_url('/wp-login.php?action=register')),
         ];

         // Сохраняем в CPT
         self::save_consent_to_cpt($user_id, array_merge($consent_entry, [
            'type' => 'pdn_processing',
            'acceptance_html' => __('I agree to the processing of personal data', 'codeweber'),
            'revision' => $revision_link
         ]));
      }
   }


   /**
    * Создать ревизию для документа если ее нет
    */
   public static function ensure_revision_exists($post_id)
   {
      if (!$post_id) {
         return false;
      }

      $revisions = wp_get_post_revisions($post_id, ['numberposts' => 1]);

      if (empty($revisions)) {
         // Создаем ревизию вручную
         $post = get_post($post_id);
         if ($post) {
            $revision_id = wp_save_post_revision($post_id);
            if ($revision_id) {
               return true;
            } else {
            }
         }
      } else {
      }

      return false;
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
         return false;
      }

      $cpt_manager = Consent_CPT::get_instance();
      if (!$cpt_manager) {
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
         return false;
      }

      // Подготавливаем данные для сохранения - УБЕДИМСЯ ЧТО REVISION ПЕРЕДАЕТСЯ
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

      return $result;
   }

   /**
    * Получить согласия пользователя из CPT
    */
   public static function get_user_consents($user_id)
   {
      $cpt_manager = Consent_CPT::get_instance();
      if (!$cpt_manager) {
         return [];
      }

      $subscriber = $cpt_manager->get_subscriber_by_user_id($user_id);
      if (!$subscriber) {
         return [];
      }

      return get_post_meta($subscriber->ID, '_subscriber_consents', true) ?: [];
   }

   /**
    * Удалить согласия пользователя (GDPR compliant)
    * Удаляет только данные согласий из CPT, но оставляет запись подписчика
    */
   public static function delete_user_consents($user_id)
   {
      if (!is_numeric($user_id) || $user_id <= 0) {
         return false;
      }

      // Очищаем согласия в CPT (оставляем запись подписчика)
      $deleted_cpt_consents = false;
      $cpt_manager = Consent_CPT::get_instance();

      if ($cpt_manager) {
         $subscriber = $cpt_manager->get_subscriber_by_user_id($user_id);
         if ($subscriber && $subscriber->ID) {
            // Удаляем только мета-данные с согласиями, оставляя запись подписчика
            $deleted_cpt_consents = delete_post_meta($subscriber->ID, '_subscriber_consents');
         }
      }

      return $deleted_cpt_consents; // Возвращаем только результат удаления из CPT
   }
}

add_action('admin_head', function () {
   global $post_type;
   if ($post_type === 'consent_subscriber') {
?>
      <script>
         document.addEventListener('DOMContentLoaded', function() {
            const title = document.getElementById('title');
            if (title) {
               title.setAttribute('readonly', 'readonly');
               title.style.background = '#f9f9f9';
            }
         });
      </script>
<?php
   }
});

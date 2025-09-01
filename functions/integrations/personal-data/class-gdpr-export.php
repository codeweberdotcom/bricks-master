<?php

/**
 * GDPR экспорт пользовательских согласий
 */

class GDPR_Export
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
      add_filter('wp_privacy_personal_data_exporters', [$this, 'register_exporter']);
   }

   /**
    * Зарегистрировать экспортер
    */
   public function register_exporter($exporters)
   {
      $exporters['user_consents'] = [
         'exporter_friendly_name'  => __('User Consents', 'codeweber'),
         'exporter_description'    => __(
            'This section includes all user consents stored in the system.',
            'codeweber'
         ),
         'callback' => [$this, 'export_consents'],
      ];
      return $exporters;
   }
   public function export_consents($email_address)
   {
      $user = get_user_by('email', $email_address);

      // Получаем подписчика из CPT независимо от наличия пользователя
      $cpt_manager = Consent_CPT::get_instance();
      if (!$cpt_manager) {
         return ['data' => [], 'done' => true];
      }

      $subscriber = $cpt_manager->get_subscriber_by_email($email_address);
      if (!$subscriber) {
         // Ни WP user, ни подписчика нет
         return ['data' => [], 'done' => true];
      }

      // Получаем согласия из CPT
      $consents = get_post_meta($subscriber->ID, '_subscriber_consents', true);
      if (!is_array($consents) || empty($consents)) {
         return ['data' => [], 'done' => true];
      }

      $export_items = [];

      foreach ($consents as $index => $data) {
         $label = !empty($data['type']) ? ucfirst(str_replace('_', ' ', $data['type'])) : __('Consent', 'codeweber');

         $url = esc_url($data['document_url'] ?? '');
         $url_display = $url;

         // Преобразуем page_id в permalink
         if (!empty($url) && preg_match('/[?&]page_id=(\d+)/', $url, $matches)) {
            $page_id = (int)$matches[1];
            $permalink = get_permalink($page_id);

            if ($permalink) {
               $url = esc_url($permalink);
               $url_display = $permalink;
            }

            if (empty($data['document_title'])) {
               $page = get_post($page_id);
               if ($page) {
                  $data['document_title'] = get_the_title($page_id);
               }
            }
         }

         $phone = $data['phone'] ?? '';
         $page_url = !empty($data['page_url'])
            ? esc_url($data['page_url'])
            : esc_url(site_url('/wp-login.php?action=register'));

         $entry_data = [
            ['name' => __('Consent Label', 'codeweber'), 'value' => $label],
            ['name' => __('Session ID', 'codeweber'), 'value' => $data['session_id'] ?? ''],
            ['name' => __('Form Title', 'codeweber'), 'value' => $data['form_title'] ?? ''],
            ['name' => __('Agreed on', 'codeweber'), 'value' => $data['date'] ?? ''],
            ['name' => __('IP Address', 'codeweber'), 'value' => $data['ip'] ?? ''],
            ['name' => __('User Agent', 'codeweber'), 'value' => $data['user_agent'] ?? __('Not provided', 'codeweber')],
            ['name' => __('Document', 'codeweber'), 'value' => $data['document_title'] ?? ''],
            ['name' => __('Consent Html', 'codeweber'), 'value' => $data['acceptance_html'] ?? ''],
            ['name' => __('Document Link', 'codeweber'), 'value' => $url_display],
            ['name' => __('Agreed on Page', 'codeweber'), 'value' => $page_url],
            ['name' => __('Phone', 'codeweber'), 'value' => $phone ?: __('Not provided', 'codeweber')],
         ];

         if (!empty($data['revision'])) {
            $entry_data[] = [
               'name'  => __('Revision', 'codeweber'),
               'value' => $data['revision'],
            ];
         }

         $export_items[] = [
            'group_id'    => 'user-consents',
            'group_label' => __('User Consents', 'codeweber'),
            'item_id'     => "user-consent-{$index}",
            'data'        => $entry_data,
         ];
      }

      return ['data' => $export_items, 'done' => true];
   }


   /**
    * Альтернативный метод: экспорт из CPT по user_id
    */
   public function export_consents_by_user_id($user_id)
   {
      $user = get_user_by('id', $user_id);
      if (!$user) {
         return ['data' => [], 'done' => true];
      }

      return $this->export_consents($user->user_email);
   }
}

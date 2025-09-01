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

   /**
    * Экспорт согласий
    */
   public function export_consents($email_address)
   {
      $user = get_user_by('email', $email_address);
      if (!$user) {
         return ['data' => [], 'done' => true];
      }

      $consents = get_user_meta($user->ID, 'codeweber_user_consents', true);
      if (!is_array($consents)) {
         return ['data' => [], 'done' => true];
      }

      $export_items = [];

      foreach ($consents as $key => $data) {
         $label_key = preg_replace('/_\d+$/', '', $key);
         $label = ucfirst(str_replace('_', ' ', $label_key));

         $url = esc_url($data['url'] ?? '');
         $url_display = $url;

         if (preg_match('/[?&]page_id=(\d+)/', $url, $matches)) {
            $page_id = (int)$matches[1];
            $permalink = get_permalink($page_id);

            if ($permalink) {
               $url = esc_url($permalink);
               $url_display = $permalink;
            }

            if (empty($data['title'])) {
               $page = get_post($page_id);
               if ($page) {
                  $data['title'] = get_the_title($page_id);
               }
            }
         }

         $phone = $data['phone'] ?? '';
         $page_url = !empty($data['page_url'])
            ? esc_url($data['page_url'])
            : esc_url(site_url('/wp-login.php?action=register'));

         $entry_data = [
            [
               'name'  => __('Consent Label', 'codeweber'),
               'value' => $label,
            ],
            [
               'name'  => __('Session ID', 'codeweber'),
               'value' => $data['session_id'] ?? '',
            ],
            [
               'name'  => __('Form Title', 'codeweber'),
               'value' => $data['form_title'] ?? '',
            ],
            [
               'name'  => __('Agreed on', 'codeweber'),
               'value' => $data['date'] ?? '',
            ],
            [
               'name'  => __('IP Address', 'codeweber'),
               'value' => $data['ip'] ?? '',
            ],
            [
               'name'  => __('User Agent', 'codeweber'),
               'value' => $data['user_agent'] ?? __('Not provided', 'codeweber'),
            ],
            [
               'name'  => __('Document', 'codeweber'),
               'value' => $data['title'] ?? '',
            ],
            [
               'name'  => __('Consent Html', 'codeweber'),
               'value' => $data['acceptance_html'] ?? '',
            ],
            [
               'name'  => __('Document Link', 'codeweber'),
               'value' => $url_display,
            ],
            [
               'name'  => __('Agreed on Page', 'codeweber'),
               'value' => $page_url,
            ],
            [
               'name'  => __('Phone', 'codeweber'),
               'value' => $phone ?: __('Not provided', 'codeweber'),
            ],
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
            'item_id'     => "user-consent-{$key}",
            'data'        => $entry_data,
         ];
      }

      return ['data' => $export_items, 'done' => true];
   }
}

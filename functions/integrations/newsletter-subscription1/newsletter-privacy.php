<?php

/**
 * Newsletter Subscription Privacy (GDPR) Functions
 */

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Экспортер персональных данных для подписки на рассылку
 */
function newsletter_personal_data_exporter($email_address, $page = 1)
{
   global $wpdb;

   $export_items = array();
   $table_name = $wpdb->prefix . 'newsletter_subscriptions';

   // Получаем все подписки по email
   $subscriptions = $wpdb->get_results($wpdb->prepare(
      "SELECT * FROM {$table_name} WHERE email = %s ORDER BY created_at DESC",
      $email_address
   ));

   if (!empty($subscriptions)) {
      foreach ($subscriptions as $subscription) {
         // Основная группа данных - подписка на рассылку
         $group_id = 'newsletter-subscription';
         $group_label = __('Newsletter Subscription', 'codeweber');

         $data = array();

         // 1. Статус подписки
         $data[] = array(
            'name' => __('Subscription Status', 'codeweber'),
            'value' => newsletter_get_status_label($subscription->status)
         );

         // 2. Email подписчика
         $data[] = array(
            'name' => __('Subscriber Email', 'codeweber'),
            'value' => $subscription->email
         );

         // 3. Дата и точное время подписки
         $data[] = array(
            'name' => __('Subscription Date & Time', 'codeweber'),
            'value' => date('d.m.Y H:i:s', strtotime($subscription->created_at))
         );

         // 4. IP-адрес
         $data[] = array(
            'name' => __('IP Address', 'codeweber'),
            'value' => $subscription->ip_address ?: __('Not recorded', 'codeweber')
         );

         // 5. User Agent
         $data[] = array(
            'name' => __('Browser User Agent', 'codeweber'),
            'value' => $subscription->user_agent ?: __('Not recorded', 'codeweber')
         );

         // 6. Форма подписки
         $data[] = array(
            'name' => __('Subscription Form', 'codeweber'),
            'value' => newsletter_get_form_label($subscription->form_id)
         );

         // 7. Дата подтверждения
         if ($subscription->confirmed_at && $subscription->confirmed_at !== '0000-00-00 00:00:00') {
            $data[] = array(
               'name' => __('Confirmation Date', 'codeweber'),
               'value' => date('d.m.Y H:i:s', strtotime($subscription->confirmed_at))
            );
         }

         // 8. Дата отписки (если есть)
         if ($subscription->unsubscribed_at && $subscription->unsubscribed_at !== '0000-00-00 00:00:00') {
            $data[] = array(
               'name' => __('Unsubscribe Date', 'codeweber'),
               'value' => date('d.m.Y H:i:s', strtotime($subscription->unsubscribed_at))
            );
         }

         // 9. Ссылка на документ согласия (из настроек)
         $consent_link = newsletter_get_consent_document_link();
         if ($consent_link) {
            $data[] = array(
               'name' => __('Mailing Consent Document', 'codeweber'),
               'value' => $consent_link
            );
         }

         // 10. Дополнительные данные
         if ($subscription->first_name) {
            $data[] = array(
               'name' => __('First Name', 'codeweber'),
               'value' => $subscription->first_name
            );
         }

         if ($subscription->last_name) {
            $data[] = array(
               'name' => __('Last Name', 'codeweber'),
               'value' => $subscription->last_name
            );
         }

         if ($subscription->phone) {
            $data[] = array(
               'name' => __('Phone Number', 'codeweber'),
               'value' => $subscription->phone
            );
         }

         // Добавляем элемент экспорта
         $export_items[] = array(
            'group_id' => $group_id,
            'group_label' => $group_label,
            'item_id' => 'newsletter-subscription-' . $subscription->id,
            'data' => $data,
         );

         // Добавляем отдельную группу для технических данных
         $tech_group_id = 'newsletter-technical';
         $tech_group_label = __('Newsletter Technical Data', 'codeweber');

         $tech_data = array();

         $tech_data[] = array(
            'name' => __('Record ID', 'codeweber'),
            'value' => (string)$subscription->id
         );

         $tech_data[] = array(
            'name' => __('Database Table', 'codeweber'),
            'value' => $table_name
         );

         $tech_data[] = array(
            'name' => __('Last Updated', 'codeweber'),
            'value' => date('d.m.Y H:i:s', strtotime($subscription->updated_at))
         );

         $tech_data[] = array(
            'name' => __('Unsubscribe Token', 'codeweber'),
            'value' => $subscription->unsubscribe_token ?: __('Not generated', 'codeweber')
         );

         $export_items[] = array(
            'group_id' => $tech_group_id,
            'group_label' => $tech_group_label,
            'item_id' => 'newsletter-tech-' . $subscription->id,
            'data' => $tech_data,
         );
      }
   }

   return array(
      'data' => $export_items,
      'done' => true,
   );
}

/**
 * Получаем ссылку на документ согласия из настроек
 */
function newsletter_get_consent_document_link()
{
   $options = get_option('newsletter_subscription_settings', array());

   if (!empty($options['mailing_consent_legal'])) {
      $document_id = $options['mailing_consent_legal'];
      $document_url = get_permalink($document_id);
      $document_title = get_the_title($document_id);

      if ($document_url && $document_title) {
         return sprintf(
            '%s (%s)',
            $document_url,
            $document_title
         );
      }
   }

   return __('Consent document not specified in settings', 'codeweber');
}

/**
 * Получаем понятное название статуса
 */
function newsletter_get_status_label($status)
{
   $labels = array(
      'pending' => __('Pending Confirmation', 'codeweber'),
      'confirmed' => __('Subscribed', 'codeweber'),
      'unsubscribed' => __('Unsubscribed', 'codeweber')
   );

   return $labels[$status] ?? $status;
}

/**
 * Получаем понятное название формы
 */
function newsletter_get_form_label($form_id)
{
   $labels = array(
      'default' => __('Default Subscription Form', 'codeweber'),
      'imported' => __('Imported Subscription', 'codeweber')
   );

   // Для CF7 форм
   if (strpos($form_id, 'cf7_') === 0) {
      $parts = explode('_', $form_id);
      if (count($parts) >= 2 && is_numeric($parts[1])) {
         $form_title = get_the_title($parts[1]);
         if ($form_title) {
            return sprintf(__('Contact Form 7: %s', 'codeweber'), $form_title);
         }
      }
   }

   return $labels[$form_id] ?? $form_id;
}

/**
 * Регистрируем эraser для удаления данных
 */
add_filter('wp_privacy_personal_data_erasers', 'newsletter_register_data_eraser');

function newsletter_register_data_eraser($erasers)
{
   $erasers['newsletter-subscription'] = array(
      'eraser_friendly_name' => __('Newsletter Subscription Data', 'codeweber'),
      'callback' => 'newsletter_personal_data_eraser',
   );
   return $erasers;
}


/**
 * Обработчик удаления данных (анонимизация)
 */
function newsletter_personal_data_eraser($email_address, $page = 1)
{
   global $wpdb;

   $table_name = $wpdb->prefix . 'newsletter_subscriptions';
   $messages = array();
   $items_removed = false;
   $items_retained = false;

   // Анонимизируем данные, но сохраняем факт подписки
   $result = $wpdb->update(
      $table_name,
      array(
         'first_name' => __('Anonymous', 'codeweber'),
         'last_name' => __('User', 'codeweber'),
         'phone' => '',
         'ip_address' => '0.0.0.0',
         'user_agent' => 'anonymized',
         'updated_at' => current_time('mysql')
      ),
      array('email' => $email_address),
      array('%s', '%s', '%s', '%s', '%s', '%s'),
      array('%s')
   );

   if ($result !== false) {
      $items_removed = true;
      $messages[] = __('Newsletter subscription personal data anonymized', 'codeweber');
   } else {
      $items_retained = true;
      $messages[] = __('No newsletter subscription data found for this email', 'codeweber');
   }

   return array(
      'items_removed' => $items_removed,
      'items_retained' => $items_retained,
      'messages' => $messages,
      'done' => true,
   );
}

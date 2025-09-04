<?php

/**
 * Newsletter Subscription Module Initialization
 */

if (!defined('ABSPATH')) {
   exit;
}

// Define module constants
define('NEWSLETTER_SUBSCRIPTION_PATH', __DIR__);
define('NEWSLETTER_SUBSCRIPTION_URL', get_template_directory_uri() . '/functions/integrations/newsletter-subscription1');

// Подключаем основные файлы модуля
require_once NEWSLETTER_SUBSCRIPTION_PATH . '/newsletter-core.php';
require_once NEWSLETTER_SUBSCRIPTION_PATH . '/newsletter-database.php';
require_once NEWSLETTER_SUBSCRIPTION_PATH . '/admin/newsletter-admin.php';
require_once NEWSLETTER_SUBSCRIPTION_PATH . '/admin/newsletter-settings.php';
require_once NEWSLETTER_SUBSCRIPTION_PATH . '/admin/newsletter-import-export.php';
require_once NEWSLETTER_SUBSCRIPTION_PATH . '/frontend/newsletter-frontend.php';
require_once NEWSLETTER_SUBSCRIPTION_PATH . '/frontend/newsletter-shortcode.php';
require_once NEWSLETTER_SUBSCRIPTION_PATH . '/frontend/newsletter-ajax.php';
require_once NEWSLETTER_SUBSCRIPTION_PATH . '/newsletter-privacy.php';


// newsletter-init.php
add_action('init', function () {
   // Инициализируем базу данных
   new NewsletterSubscriptionDatabase();

   // Инициализируем админ-панель
   if (is_admin()) {
      new NewsletterSubscriptionAdmin();
      new NewsletterSubscriptionSettings();
      new NewsletterSubscriptionImportExport();
   }

   // Инициализируем фронтенд
   new NewsletterSubscriptionFrontend();
   new NewsletterSubscriptionShortcode();
   new NewsletterSubscriptionAjax(); // ✅ ДОБАВЬТЕ ЭТУ СТРОЧКУ

   // Основной класс модуля
   new NewsletterSubscription();
}, 20);

add_filter('wp_privacy_personal_data_exporters', 'newsletter_register_data_exporter');

function newsletter_register_data_exporter($exporters)
{
   $exporters['newsletter-subscription'] = array(
      'exporter_friendly_name' => __('Newsletter Subscription Data', 'codeweber'),
      'callback' => 'newsletter_personal_data_exporter',
   );
   return $exporters;
}



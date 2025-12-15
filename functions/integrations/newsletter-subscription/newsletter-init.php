<?php

/**
 * Newsletter Subscription Module Initialization
 */

if (!defined('ABSPATH')) {
   exit;
}

// Define module constants
define('NEWSLETTER_SUBSCRIPTION_PATH', __DIR__);
define('NEWSLETTER_SUBSCRIPTION_URL', get_template_directory_uri() . '/functions/integrations/newsletter-subscription');

// Подключаем основные файлы модуля
require_once NEWSLETTER_SUBSCRIPTION_PATH . '/newsletter-core.php';
require_once NEWSLETTER_SUBSCRIPTION_PATH . '/newsletter-database.php';
require_once NEWSLETTER_SUBSCRIPTION_PATH . '/admin/newsletter-admin.php';
require_once NEWSLETTER_SUBSCRIPTION_PATH . '/admin/newsletter-settings.php';
require_once NEWSLETTER_SUBSCRIPTION_PATH . '/admin/newsletter-import-export.php';
require_once NEWSLETTER_SUBSCRIPTION_PATH . '/frontend/newsletter-frontend.php';


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

   // Основной класс модуля
   new NewsletterSubscription();
}, 20);

// Регистрация провайдера в Personal Data V2 (универсальный модуль)
add_action('personal_data_v2_ready', function($manager) {
   if (file_exists(__DIR__ . '/../personal-data-v2/providers/class-newsletter-provider.php')) {
      require_once __DIR__ . '/../personal-data-v2/providers/class-newsletter-provider.php';
      $provider = new Newsletter_Data_Provider();
      $manager->register_provider($provider);
   }
}, 10);



<?php

/**
 * Вывод кодов метрик из Redux (Отслеживание и метрики) в head.
 * Коды выводятся только на фронте, не в админке.
 */
if (!defined('ABSPATH')) {
   exit;
}

add_action('wp_head', function () {
   if (is_admin()) {
      return;
   }

   $opt_name = 'redux_demo';
   $opts = get_option($opt_name, []);
   if (!is_array($opts)) {
      $opts = [];
   }

   // Отладка: раскомментируйте, чтобы в исходном коде страницы увидеть, доходят ли опции.
   // echo "\n<!-- redux_tracking: keys=" . implode(',', array_keys(array_intersect_key($opts, array_flip(['yandex-on','google-analytics-on','google-tag-manager-on','facebook-pixel-on','hotjar-on','other-analytics-on'])))) . " -->\n";

   // GDPR-режим: не выводить трекинг до согласия пользователя
   if ( isset( $opts['cookie_compliance_mode'] ) && $opts['cookie_compliance_mode'] === 'gdpr' ) {
      $host        = parse_url( home_url(), PHP_URL_HOST );
      $version     = ! empty( $opts['cookie_version'] ) ? (int) $opts['cookie_version'] : 1;
      $cookie_name = 'user_cookie_consent_' . md5( $host ) . '_v' . $version;
      if ( empty( $_COOKIE[ $cookie_name ] ) ) {
         return;
      }
   }

   $codes = [];

   if (!empty($opts['yandex-on'])) {
      $code = isset($opts['yandex-metrics']) ? $opts['yandex-metrics'] : '';
      if (is_string($code) && trim($code) !== '') {
         $codes[] = trim($code);
      }
   }

   if (!empty($opts['google-analytics-on'])) {
      $code = isset($opts['google-analytics']) ? $opts['google-analytics'] : '';
      if (is_string($code) && trim($code) !== '') {
         $codes[] = trim($code);
      }
   }

   if (!empty($opts['google-tag-manager-on'])) {
      $code = isset($opts['google-tag-manager']) ? $opts['google-tag-manager'] : '';
      if (is_string($code) && trim($code) !== '') {
         $codes[] = trim($code);
      }
   }

   if (!empty($opts['facebook-pixel-on'])) {
      $code = isset($opts['facebook-pixel']) ? $opts['facebook-pixel'] : '';
      if (is_string($code) && trim($code) !== '') {
         $codes[] = trim($code);
      }
   }

   if (!empty($opts['hotjar-on'])) {
      $code = isset($opts['hotjar']) ? $opts['hotjar'] : '';
      if (is_string($code) && trim($code) !== '') {
         $codes[] = trim($code);
      }
   }

   if (!empty($opts['other-analytics-on'])) {
      $code = isset($opts['other-analytics-code']) ? $opts['other-analytics-code'] : '';
      if (is_string($code) && trim($code) !== '') {
         $codes[] = trim($code);
      }
   }

   foreach ($codes as $code) {
      echo "\n" . $code . "\n";
   }
}, 5);

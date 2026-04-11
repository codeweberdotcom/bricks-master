<?php

/**
 * AJAX-обработчик: трекинг события согласия с куки в Matomo.
 * Вызывается из JS при нажатии Accept — работает в обоих режимах (РФ и GDPR).
 */
add_action('wp_ajax_nopriv_codeweber_cookie_consent', 'codeweber_cookie_consent_track_matomo');
add_action('wp_ajax_codeweber_cookie_consent',        'codeweber_cookie_consent_track_matomo');

function codeweber_cookie_consent_track_matomo() {
   check_ajax_referer('codeweber_cookie_consent_nonce', 'nonce');

   // Только если Matomo-плагин активен
   if (!function_exists('is_plugin_active') || !is_plugin_active('matomo/matomo.php')) {
      wp_send_json_success();
   }

   $current_url = isset($_POST['url']) ? esc_url_raw(wp_unslash($_POST['url'])) : home_url();
   $referrer    = isset($_POST['ref']) ? esc_url_raw(wp_unslash($_POST['ref'])) : home_url();

   // Visitor ID из куки Matomo (_pk_id_*) или фоллбэк по IP+UA
   $visitor_id = '';
   foreach ($_COOKIE as $name => $value) {
      if (strpos($name, '_pk_id_') === 0) {
         $parts = explode('.', sanitize_text_field($value));
         if (!empty($parts[0]) && strlen($parts[0]) === 16) {
            $visitor_id = $parts[0];
            break;
         }
      }
   }
   if (empty($visitor_id)) {
      $visitor_id = substr(md5(($_SERVER['REMOTE_ADDR'] ?? '') . ($_SERVER['HTTP_USER_AGENT'] ?? '')), 0, 16);
   }

   wp_remote_post(home_url('/wp-json/matomo/v1/hit/'), [
      'timeout'   => 2,
      'blocking'  => false,
      'sslverify' => false,
      'body'      => [
         'idsite'     => defined('CODEWEBER_FORMS_MATOMO_SITE_ID') ? CODEWEBER_FORMS_MATOMO_SITE_ID : 1,
         'rec'        => 1,
         'ua'         => sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? ''),
         '_id'        => $visitor_id,
         'e_c'        => __('Cookie Consent', 'codeweber'),
         'e_a'        => __('Accept', 'codeweber'),
         'e_n'        => $current_url,
         'e_v'        => 1,
         'url'        => $current_url,
         'urlref'     => $referrer,
         'send_image' => 0,
      ],
   ]);

   wp_send_json_success();
}

add_action('wp_footer', function () {
   global $opt_name;

   // Включен ли баннер
   $cookieBool = Redux::get_option($opt_name, 'enable_cookie_banner');

   // Текст из редактора
   $cookietext = do_shortcode(wp_kses_post(Redux::get_option($opt_name, 'welcome_text_cookie_banneer') ?? ''));

   // Кол-во дней хранения куки
   $cookie_days = (int) Redux::get_option($opt_name, 'cookie_expiration_date');
   if ($cookie_days <= 0) $cookie_days = 180;

   // Уникальное имя куки (на основе домена + версии)
   $host = parse_url(home_url(), PHP_URL_HOST);
   $cookie_version = (int) Redux::get_option($opt_name, 'cookie_version') ?: 1;
   $cookie_name = 'user_cookie_consent_' . md5($host) . '_v' . $cookie_version;

   // Текущий URL
   $current_url = home_url(add_query_arg([], $_SERVER['REQUEST_URI']));
   // URL политики
   $cookie_policy_url = trim(do_shortcode('[url_cookie-policy]'));

   // 🧠 Проверка на поискового робота
   $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
   $is_bot = preg_match('/bot|crawl|slurp|spider|yandex|google|bing|baidu|duckduckgo/i', $user_agent);

   // Условия показа баннера
   if ($cookieBool && !$is_bot && !isset($_COOKIE[$cookie_name]) && $current_url !== $cookie_policy_url) {
?>
      <?php $card_radius = Codeweber_Options::style('card-radius'); ?>
      <!-- Cookie Modal -->
      <div class="modal fade modal-popup modal-bottom-center" id="cookieModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
         <div class="modal-dialog modal-xl">
            <div class="modal-content<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
               <div class="modal-body p-6">
                  <div class="row">
                     <div class="col-md-12 col-lg-10 mb-4 mb-lg-0 my-auto align-items-center">
                        <div class="cookie-modal-text fs-sm"><?php echo $cookietext; ?></div>
                     </div>
                     <div class="col-md-5 col-lg-2 text-lg-end my-auto">
                        <a href="#" class="btn btn-primary<?php echo Codeweber_Options::style('button'); ?>" id="acceptCookie" data-bs-dismiss="modal" aria-label="<?php esc_attr_e('Close', 'codeweber'); ?>">
                           <?php _e('Accept', 'codeweber'); ?>
                        </a>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <!-- JS логика -->
      <script>
         document.getElementById('acceptCookie')?.addEventListener('click', function() {
            const days = <?php echo (int) $cookie_days; ?>;
            const now = new Date();
            const fd = now.toISOString().replace('T', ' ').substring(0, 19);
            const ep = location.href;
            const rf = document.referrer;
            const value = `fd=${fd}|||ep=${ep}|||rf=${rf}`;
            const expires = new Date(Date.now() + days * 864e5).toUTCString();
            document.cookie = "<?php echo $cookie_name; ?>=" + encodeURIComponent(value) + "; expires=" + expires + "; path=/";

            // Matomo: клиентский трекинг (РФ-режим — _paq уже загружен)
            if (typeof _paq !== 'undefined') {
               _paq.push(['trackEvent', '<?php echo esc_js(__('Cookie Consent', 'codeweber')); ?>', '<?php echo esc_js(__('Accept', 'codeweber')); ?>', ep]);
            }

            // Matomo: серверный трекинг через AJAX (GDPR-режим — _paq ещё не загружен)
            const formData = new FormData();
            formData.append('action', 'codeweber_cookie_consent');
            formData.append('nonce',  '<?php echo esc_js(wp_create_nonce('codeweber_cookie_consent_nonce')); ?>');
            formData.append('url',    ep);
            formData.append('ref',    rf);
            fetch('<?php echo esc_js(admin_url('admin-ajax.php')); ?>', {
               method: 'POST',
               body:   formData,
               keepalive: true,
            });
         });
      </script>
<?php
   }
});

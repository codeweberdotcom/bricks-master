<?php

add_action('wp_footer', function () {
   global $opt_name;

   // Включен ли баннер
   $cookieBool = Redux::get_option($opt_name, 'enable_cookie_banner');

   // Текст из редактора
   $cookietext = do_shortcode(wp_kses_post(Redux::get_option($opt_name, 'welcome_text_cookie_banneer') ?? ''));

   // Кол-во дней хранения куки
   $cookie_days = (int) Redux::get_option($opt_name, 'cookie_expiration_date');
   if ($cookie_days <= 0) $cookie_days = 180;

   // Уникальное имя куки (на основе домена)
   $host = parse_url(home_url(), PHP_URL_HOST);
   $cookie_name = 'user_cookie_consent_' . md5($host);

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
      <?php $card_radius = getThemeCardImageRadius(); ?>
      <!-- Cookie Modal -->
      <div class="modal fade modal-popup modal-bottom-center" id="cookieModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
         <div class="modal-dialog modal-xl">
            <div class="modal-content<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
               <div class="modal-body p-6">
                  <div class="row">
                     <div class="col-md-12 col-lg-10 mb-4 mb-lg-0 my-auto align-items-center">
                        <div class="cookie-modal-text fs-13"><?php echo $cookietext; ?></div>
                     </div>
                     <div class="col-md-5 col-lg-2 text-lg-end my-auto">
                        <a href="#" class="btn btn-primary<?php echo getThemeButton(); ?>" id="acceptCookie" data-bs-dismiss="modal" aria-label="<?php esc_attr_e('Close', 'codeweber'); ?>">
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
            const fd = now.toISOString().replace('T', ' ').substring(0, 19); // Дата согласия
            const ep = location.href; // Страница согласия
            const rf = document.referrer; // Откуда пришёл
            const value = `fd=${fd}|||ep=${ep}|||rf=${rf}`;
            const expires = new Date(Date.now() + days * 864e5).toUTCString();
            document.cookie = "<?php echo $cookie_name; ?>=" + encodeURIComponent(value) + "; expires=" + expires + "; path=/";
         });
      </script>
<?php
   }
});

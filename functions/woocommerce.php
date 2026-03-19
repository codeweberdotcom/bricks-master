<?php

/**
 * Вспомогательная функция: получить допустимые значения per_page из Redux.
 */
function codeweber_get_allowed_per_page() {
	global $opt_name;
	if ( class_exists( 'Redux' ) && ! empty( $opt_name ) ) {
		$raw = Redux::get_option( $opt_name, 'woo_per_page_values', '12,24,48' );
		if ( ! empty( $raw ) ) {
			$parsed = array_values( array_filter( array_map( 'intval', explode( ',', $raw ) ) ) );
			if ( ! empty( $parsed ) ) {
				return $parsed;
			}
		}
	}
	return [ 12, 24, 48 ];
}

// Количество товаров на странице через URL-параметр ?per_page=N (значения из Redux).
add_filter( 'loop_shop_per_page', function ( $default ) {
	$allowed = codeweber_get_allowed_per_page();
	if ( isset( $_GET['per_page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$requested = (int) $_GET['per_page']; // phpcs:ignore WordPress.Security.NonceVerification
		if ( in_array( $requested, $allowed, true ) ) {
			return $requested;
		}
	}
	return $default;
}, 20 );

// Rank Math: вставляем страницу магазина в крошки для product_cat и product_tag.
// Было: Home → Косметика
// Стало: Home → Каталог → Косметика
add_filter( 'rank_math/frontend/breadcrumb/items', function ( $crumbs ) {
	if ( ! function_exists( 'is_product_category' ) ) {
		return $crumbs;
	}
	if ( ! is_product_category() && ! is_product_tag() ) {
		return $crumbs;
	}
	$shop_id = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'shop' ) : 0;
	if ( $shop_id < 1 ) {
		return $crumbs;
	}
	// Rank Math crumb format: [0 => name, 1 => url, 'hide_in_schema' => bool]
	$shop_crumb = array( get_the_title( $shop_id ), get_permalink( $shop_id ), 'hide_in_schema' => false );
	// Вставляем после первого элемента (Home → индекс 0)
	array_splice( $crumbs, 1, 0, array( $shop_crumb ) );
	return $crumbs;
} );

// Yoast SEO: вставляем страницу магазина в крошки для product_cat и product_tag.
// Было: Главная > Косметика
// Стало: Главная > Каталог > Косметика
add_filter( 'wpseo_breadcrumb_links', function ( $crumbs ) {
	if ( ! function_exists( 'is_product_category' ) ) {
		return $crumbs;
	}
	if ( ! is_product_category() && ! is_product_tag() ) {
		return $crumbs;
	}
	$shop_id = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'shop' ) : 0;
	if ( $shop_id < 1 ) {
		return $crumbs;
	}
	$shop_crumb = array(
		'url'  => get_permalink( $shop_id ),
		'text' => get_the_title( $shop_id ),
	);
	// Вставляем после первого элемента (Главная → индекс 0)
	array_splice( $crumbs, 1, 0, array( $shop_crumb ) );
	return $crumbs;
} );

// Убираем префикс «Категория:» / «Category:» из заголовка архива product_cat и product_tag.
// Используется в get_the_archive_title() и в fallback-крошках.
add_filter( 'get_the_archive_title', function ( $title ) {
	if ( is_product_category() || is_product_tag() ) {
		return single_term_title( '', false );
	}
	return $title;
} );

// Плейсхолдер изображения товара — используем тематический вместо стандартного WooCommerce.
add_filter( 'woocommerce_placeholder_img_src', function () {
	return get_template_directory_uri() . '/dist/assets/img/image-placeholder.jpg';
} );

// Регионы РФ для WooCommerce (на английском, с возможностью перевода).
add_filter( 'woocommerce_states', function ( $states ) {
	$states['RU'] = require get_template_directory() . '/functions/woocommerce-states-ru.php';
	return $states;
} );

// Тестовый режим способов оплаты: подключается только при включённой опции в Redux (WooCommerce → Payment methods test mode).
add_filter( 'woocommerce_payment_gateways', function ( $gateways ) {
	global $opt_name;
	if ( class_exists( 'Redux' ) && Redux::get_option( $opt_name, 'payment_methods_test_mode' ) ) {
		require_once get_template_directory() . '/functions/woocommerce-gateway-test.php';
		$gateways[] = 'WC_Gateway_Codeweber_Test';
	}
	return $gateways;
} );

// Стилизация полей формы «Редактировать адрес» (form-control, колонки).
add_filter( 'woocommerce_form_field_args', function ( $args, $key, $value = null ) {
	if ( ! is_wc_endpoint_url( 'edit-address' ) ) {
		return $args;
	}
	$is_address_field = ( strpos( $key, 'billing_' ) === 0 || strpos( $key, 'shipping_' ) === 0 );
	if ( ! $is_address_field ) {
		return $args;
	}
	if ( ! is_array( $args['input_class'] ) ) {
		$args['input_class'] = array_filter( array( $args['input_class'] ) );
	}
	$args['input_class'][] = 'form-control';
	$wrap_class = isset( $args['class'] ) ? $args['class'] : array();
	if ( ! is_array( $wrap_class ) ) {
		$wrap_class = array_filter( array( $wrap_class ) );
	}
	$wrap_wide = in_array( 'form-row-wide', $wrap_class, true );
	$half_width_keys = array( 'billing_phone', 'billing_email', 'billing_city', 'billing_postcode', 'shipping_phone', 'shipping_email', 'shipping_city', 'shipping_postcode' );
	$use_half = ! $wrap_wide || in_array( $key, $half_width_keys, true );
	$wrap_class[] = $use_half ? 'col-md-6' : 'col-12';
	$args['class'] = $wrap_class;
	return $args;
}, 10, 3 );

// На странице «Редактировать адрес» используем нативный select (form-select-wrapper + form-select), без Select2.
add_action( 'wp_enqueue_scripts', function () {
	if ( is_account_page() && is_wc_endpoint_url( 'edit-address' ) ) {
		wp_dequeue_script( 'selectWoo' );
		wp_dequeue_style( 'select2' );
	}
}, 20 );

// Порядок полей: Адрес → Страна → Штат/область → остальные.
add_filter( 'woocommerce_default_address_fields', function ( $fields ) {
	if ( isset( $fields['address_1'], $fields['country'] ) ) {
		$fields['address_1']['priority'] = 38;
		$fields['country']['priority']   = 45;
	}
	if ( isset( $fields['state'] ) ) {
		$fields['state']['priority'] = 46;
	}
	return $fields;
} );

// Добавление поля телефона и интерфейса в личный кабинет
add_action('woocommerce_edit_account_form_fields', function () {
   $user_id  = get_current_user_id();
   $phone    = get_user_meta($user_id, 'phone', true);
   $verified = get_user_meta($user_id, 'phone_verified', true);

   $form_radius = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style('form-radius') : ' rounded';

   global $opt_name;
   $woophonenumber     = Redux::get_option($opt_name, 'woophonenumber');
   $woophonenumbersms  = Redux::get_option($opt_name, 'woophonenumbersms');
   if ($woophonenumber && $woophonenumbersms) {
?>
      <div class="row">

         <p id="verification-status" class="small mb-2">
            <?php if (empty($phone)) : ?>
               ❌ <?php esc_html_e('Phone number is not specified', 'codeweber'); ?>
            <?php elseif ($verified) : ?>
               ✅ <?php esc_html_e('Phone number is verified', 'codeweber'); ?>
            <?php else : ?>
               ❌ <?php esc_html_e('Phone number is not verified', 'codeweber'); ?>
            <?php endif; ?>
         </p>
         <div class="col-md-6">

            <div class="form-floating mb-2 d-flex align-items-start gap-2">
               <input type="text" name="account_phone" id="account_phone" class="form-control<?php echo esc_attr( $form_radius ); ?> phone-mask" placeholder="+7(000)000-00-00" value="<?php echo esc_attr($phone); ?>">
               <label for="account_phone"><?php esc_html_e('Phone number', 'codeweber'); ?></label>
               <button type="button" class="btn btn-navy btn-lg" id="send-verification-code"><span class="d-block d-md-none"><i class="uil uil-angle-right"></i></span><span class="d-none d-md-block"><?php esc_html_e('Verify', 'codeweber'); ?></span></button>
            </div>
            <div id="sms-timer-info" class="text-muted small mb-3"></div>
         </div>

         <div id="verify-section" class="mb-3 col-md-6" style="display: none;">
            <div class="form-floating mb-2 d-flex align-items-start gap-2">
               <input type="text" id="phone_verification_code" class="form-control<?php echo esc_attr( $form_radius ); ?>" placeholder="<?php esc_attr_e('Code from SMS', 'codeweber'); ?>">
               <label for="phone_verification_code"><?php esc_html_e('Code from SMS', 'codeweber'); ?></label>
               <button type="button" class="btn btn-green btn-lg" id="confirm-verification-code"><?php esc_html_e('Confirm', 'codeweber'); ?></button>
            </div>

         </div>

      </div>
   <?php } elseif ($woophonenumber && !$woophonenumbersms) {
   ?>
      <div class="form-floating mb-4">
         <input type="text" class="form-control<?php echo esc_attr( $form_radius ); ?> phone-mask" name="account_phone" id="account_phone" value="<?php echo esc_attr($phone); ?>" placeholder="+7 (___) ___-__-__">
         <label for="account_phone"><?php esc_html_e('Phone number', 'codeweber'); ?></label>
      </div>

   <?php
   }
});

add_action('wp_ajax_send_verification_code', function () {
   global $opt_name;
   $sms_api_key = Redux::get_option($opt_name, 'smsruapi');
   $user_id     = get_current_user_id();
   $phone       = sanitize_text_field($_POST['phone'] ?? '');

   if (!$sms_api_key || !$user_id || !$phone) {
      wp_send_json(['success' => false, 'message' => 'Ошибка конфигурации или недопустимые данные.']);
   }

   $attempts  = (int) get_user_meta($user_id, 'phone_sms_attempts', true);
   $last_sent = (int) get_user_meta($user_id, 'phone_sms_last_sent', true);
   $now       = time();

   $delays = [0, 30, 60, 900]; // сек
   $delay  = $delays[min($attempts, count($delays) - 1)];

   if ($last_sent && ($now - $last_sent < $delay)) {
      $remaining = $delay - ($now - $last_sent);

      if ($remaining >= 86400) {
         $msg = 'Попробуйте завтра';
      } elseif ($remaining >= 3600) {
         $msg = 'Попробуйте через ' . floor($remaining / 3600) . ' ч. ' . floor(($remaining % 3600) / 60) . ' мин.';
      } elseif ($remaining >= 60) {
         $msg = 'Попробуйте через ' . ceil($remaining / 60) . ' мин.';
      } else {
         $msg = 'Попробуйте через ' . $remaining . ' сек.';
      }

      // Важно: показываем поле ввода кода даже если задержка
      wp_send_json([
         'success'        => false,
         'message'        => $msg,
         'retry_after'    => $remaining,
         'show_code_input' => true
      ]);
   }

   $code = rand(1000, 9999);
   update_user_meta($user_id, 'phone_verification_code', $code);
   update_user_meta($user_id, 'phone_sms_last_sent', $now);
   update_user_meta($user_id, 'phone_sms_attempts', $attempts + 1);
   update_user_meta($user_id, 'phone', $phone);

   $url = "https://sms.ru/sms/send?api_id={$sms_api_key}&to={$phone}&msg=" . urlencode("Код подтверждения: $code") . "&json=1";
   $response = wp_remote_get($url);
   $body = wp_remote_retrieve_body($response);
   $data = json_decode($body, true);

   if (!empty($data['status']) && $data['status'] == 'OK') {
      wp_send_json([
         'success' => true,
         'message' => 'Код отправлен.',
         'show_code_input' => true
      ]);
   } else {
      wp_send_json([
         'success' => false,
         'message' => 'Ошибка отправки SMS: ' . ($data['status_text'] ?? 'Неизвестная ошибка'),
         'show_code_input' => false
      ]);
   }
});


// Подтверждение кода
add_action('wp_ajax_confirm_verification_code', function () {
   $code    = sanitize_text_field($_POST['code'] ?? '');
   $user_id = get_current_user_id();

   $stored = get_user_meta($user_id, 'phone_verification_code', true);
   if ($code === $stored) {
      update_user_meta($user_id, 'phone_verified', 1);
      delete_user_meta($user_id, 'phone_verification_code');
      wp_send_json(['success' => true, 'message' => 'Номер подтверждён.']);
   }

   wp_send_json(['success' => false, 'message' => 'Неверный код.']);
});


add_action('wp_footer', function () {
   if (!is_account_page()) return;
   global $opt_name;

   $woophonenumber    = Redux::get_option($opt_name, 'woophonenumber');
   $woophonenumbersms = Redux::get_option($opt_name, 'woophonenumbersms');
   $user_id           = get_current_user_id();
   $verified          = get_user_meta($user_id, 'phone_verified', true);

   if ($woophonenumber && $woophonenumbersms) {
   ?>
      <script>
         document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.getElementById('account_phone');
            const sendBtn = document.getElementById('send-verification-code');
            const confirmBtn = document.getElementById('confirm-verification-code');
            const codeInput = document.getElementById('phone_verification_code');
            const status = document.getElementById('verification-status');
            const verifySection = document.getElementById('verify-section');
            const timerInfo = document.getElementById('sms-timer-info');

            let retryTimeout = null;
            const timerEnabled = true; // ⚙️ переключи на false, чтобы отключить таймер
            let isPhoneVerified = <?php echo $verified ? 'true' : 'false'; ?>;
            let originalPhone = phoneInput ? phoneInput.value : '';

            if (isPhoneVerified) {
               verifySection.style.display = 'none';
               status.innerHTML = '✅ Номер подтверждён';
            }

            if (phoneInput) {
               phoneInput.addEventListener('input', () => {
                  const current = phoneInput.value;
                  if (current !== originalPhone && isPhoneVerified) {
                     fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                           method: 'POST',
                           headers: {
                              'Content-Type': 'application/x-www-form-urlencoded'
                           },
                           body: new URLSearchParams({
                              action: 'reset_phone_verification'
                           })
                        })
                        .then(res => res.json())
                        .then(data => {
                           if (data.success) {
                              isPhoneVerified = false;
                              status.innerHTML = '❌ Номер не подтверждён';
                              verifySection.style.display = 'none';
                              originalPhone = current;
                           }
                        });
                  }
               });
            }

            sendBtn.addEventListener('click', () => {
               const phone = phoneInput.value;
               sendBtn.disabled = true;

               fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                     method: 'POST',
                     headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                     },
                     body: new URLSearchParams({
                        action: 'send_verification_code',
                        phone: phone
                     })
                  })
                  .then(res => res.json())
                  .then(data => {
                     if (!isPhoneVerified) {
                        verifySection.style.display = 'block';
                     }

                     if (timerEnabled && data.retry_after && !isPhoneVerified) {
                        startRetryTimer(data.retry_after);
                     } else {
                        // Если таймер отключен, сразу разблокируем кнопку и очищаем таймер
                        sendBtn.disabled = false;
                        timerInfo.textContent = '';
                        if (retryTimeout) clearTimeout(retryTimeout);
                     }
                  });
            });

            confirmBtn.addEventListener('click', () => {
               const code = codeInput.value;

               fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                     method: 'POST',
                     headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                     },
                     body: new URLSearchParams({
                        action: 'confirm_verification_code',
                        code: code
                     })
                  })
                  .then(res => res.json())
                  .then(data => {
                     if (data.success) {
                        isPhoneVerified = true;
                        status.innerHTML = '✅ Номер подтверждён';
                        verifySection.innerHTML = '';
                        timerInfo.textContent = '';
                        if (retryTimeout) clearTimeout(retryTimeout);
                     }
                  });
            });

            function startRetryTimer(seconds) {
               let remaining = seconds;
               sendBtn.disabled = true;

               const tick = () => {
                  if (isPhoneVerified || remaining <= 0) {
                     timerInfo.textContent = '';
                     sendBtn.disabled = false;
                     return;
                  }

                  if (remaining >= 86400) {
                     timerInfo.textContent = 'Следующая попытка: завтра';
                  } else if (remaining >= 3600) {
                     const h = Math.floor(remaining / 3600);
                     const m = Math.floor((remaining % 3600) / 60);
                     timerInfo.textContent = `Следующая попытка: через ${h} ч. ${m} мин.`;
                  } else if (remaining >= 60) {
                     timerInfo.textContent = `Следующая попытка: через ${Math.ceil(remaining / 60)} мин.`;
                  } else {
                     timerInfo.textContent = `Следующая попытка: через ${remaining} сек.`;
                  }

                  remaining--;
                  retryTimeout = setTimeout(tick, 1000);
               };

               tick();
            }
         });
      </script>
   <?php
   }
});


add_action('wp_ajax_reset_phone_verification', function () {
   $user_id = get_current_user_id();
   if ($user_id) {
      delete_user_meta($user_id, 'phone_verified');
      delete_user_meta($user_id, 'phone_verification_code');
      wp_send_json(['success' => true]);
   } else {
      wp_send_json(['success' => false, 'message' => 'Ошибка пользователя']);
   }
});

add_action('wp_ajax_check_phone_unique', function () {
   $phone = sanitize_text_field($_POST['phone'] ?? '');
   $user_id = get_current_user_id();

   if (!$phone) {
      wp_send_json(['success' => false, 'message' => 'Номер телефона не указан']);
      return;
   }

   global $wpdb;
   // Таблица мета-полей пользователей
   $meta_table = $wpdb->usermeta;
   $phone_meta_key = 'phone';

   // Проверяем есть ли у другого пользователя такой номер
   $query = $wpdb->prepare("
        SELECT user_id FROM $meta_table
        WHERE meta_key = %s AND meta_value = %s AND user_id != %d
        LIMIT 1
    ", $phone_meta_key, $phone, $user_id);

   $existing_user = $wpdb->get_var($query);

   if ($existing_user) {
      wp_send_json(['success' => false, 'message' => 'Этот номер принадлежит к другому аккаунту']);
   } else {
      wp_send_json(['success' => true]);
   }
});


add_filter('woocommerce_account_menu_items', 'remove_downloads_tab_my_account');
function remove_downloads_tab_my_account($items)
{
   global $opt_name;
   $hidedownloadmenu = Redux::get_option($opt_name, 'hidedownloadmenu');

   if ($hidedownloadmenu) {
      unset($items['downloads']);
   }

   return $items; // ✅ всегда возвращаем $items
}

/**
 * При включённом «Payment methods test mode» в Redux: показывать пункт «Способы оплаты» в меню даже если ни один шлюз не поддерживает добавление метода.
 */
add_filter('woocommerce_account_menu_items', function ($items) {
	global $opt_name;
	if ( ! class_exists( 'Redux' ) || ! Redux::get_option( $opt_name, 'payment_methods_test_mode' ) ) {
		return $items;
	}
	if ( ! isset( $items['payment-methods'] ) ) {
		$items['payment-methods'] = __('Payment methods', 'woocommerce');
		$order = array( 'dashboard', 'orders', 'downloads', 'edit-address', 'payment-methods', 'edit-account', 'customer-logout' );
		$ordered = array();
		foreach ( $order as $key ) {
			if ( isset( $items[ $key ] ) ) {
				$ordered[ $key ] = $items[ $key ];
			}
		}
		$items = $ordered;
	}
	return $items;
}, 15);

/**
 * Иконки для карточек дашборда «Мой аккаунт» (endpoint => Unicons class).
 * Добавление пунктов меню через woocommerce_account_menu_items автоматически даёт карточку; иконку можно задать фильтром.
 */
function codeweber_my_account_dashboard_cards()
{
   if (!function_exists('wc_get_account_menu_items')) {
      return;
   }
   $items = wc_get_account_menu_items();
   unset($items['dashboard'], $items['customer-logout']);
   if (empty($items)) {
      return;
   }
   $icon_map = array(
      'orders'          => 'uil-shopping-bag',
      'downloads'       => 'uil-import',
      'edit-address'    => 'uil-map-marker',
      'payment-methods' => 'uil-credit-card',
      'edit-account'    => 'uil-file-edit-alt',
   );
   $card_items = array();
   foreach ($items as $endpoint => $label) {
      $card_items[$endpoint] = array(
         'label' => $label,
         'icon'  => isset($icon_map[$endpoint]) ? $icon_map[$endpoint] : 'uil-apps',
      );
   }
   $card_items = apply_filters('codeweber_my_account_dashboard_card_items', $card_items);
   $card_class = 'card lift text-decoration-none text-body d-block rounded-0';
   ?>
   <div class="row g-3 g-md-3 mt-4">
      <?php foreach ($card_items as $endpoint => $data) :
         $url = wc_get_account_endpoint_url($endpoint);
         $icon_class = esc_attr($data['icon']);
         $label = esc_html($data['label']);
      ?>
         <div class="col-md-4">
            <a href="<?php echo esc_url($url); ?>" class="<?php echo esc_attr( $card_class ); ?>">
               <div class="card-body p-6 text-center">
                  <div class="icon text-primary">
                     <i class="uil <?php echo $icon_class; ?> text-primary fs-35"></i>
                  </div>
                  <div class="d-flex flex-column">
                     <div class="text-left h4"><?php echo $label; ?></div>
                  </div>
               </div>
            </a>
         </div>
      <?php endforeach; ?>
   </div>
   <?php
}

add_action('woocommerce_before_account_navigation', function () {
   echo '<div class="row gx-0">';
}, 5); // низкий приоритет, чтобы обернуть как можно больше

add_action('woocommerce_after_account_content', function () {
   echo '</div><!-- .myaccount-inner-wrapper -->';
}, 999); // высокий приоритет, чтобы закрыть в самом конце


add_action('woocommerce_before_account_navigation', function () {
   echo '<aside class="col-xl-3 sidebar sticky-sidebar mt-md-0 d-xl-block">
          <div class="widget">';
}, 10); // по умолчанию

add_action('woocommerce_after_account_navigation', function () {
   echo '</div></aside><!-- .myaccount-nav-wrapper -->';
}, 10);

/**
 * Подключить shop-pjax.js на страницах магазина.
 * Отдельный файл — не входит в theme.js/plugins.js.
 * Загружается только при активном WooCommerce.
 */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_shop() && ! is_product_category() && ! is_product_tag() ) {
		return;
	}

	$dist_path = codeweber_get_dist_file_path( 'dist/assets/js/shop-pjax.js' );
	$dist_url  = codeweber_get_dist_file_url( 'dist/assets/js/shop-pjax.js' );

	if ( ! $dist_path || ! $dist_url ) {
		return;
	}

	wp_enqueue_script(
		'shop-pjax',
		$dist_url,
		[],
		codeweber_asset_version( $dist_path ),
		true
	);
}, 30 );

// ── WooCommerce Filters ────────────────────────────────────────────────────────
require_once get_template_directory() . '/functions/woocommerce-filters.php';

/**
 * Variation Swatches JS — загружается только на странице одиночного товара.
 */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_product() ) {
		return;
	}

	$dist_path = codeweber_get_dist_file_path( 'dist/assets/js/woo-swatches.js' );
	$dist_url  = codeweber_get_dist_file_url( 'dist/assets/js/woo-swatches.js' );

	if ( ! $dist_path || ! $dist_url ) {
		return;
	}

	wp_enqueue_script(
		'cw-woo-swatches',
		$dist_url,
		[ 'jquery', 'wc-add-to-cart-variation' ],
		codeweber_asset_version( $dist_path ),
		true
	);

	wp_localize_script( 'cw-woo-swatches', 'cwSwatchesSettings', [
		'oos_behavior' => cw_swatches_get_oos_behavior(),
	] );
}, 30 );

// ── WooCommerce Variation Swatches ─────────────────────────────────────────────
require_once get_template_directory() . '/functions/woocommerce-swatches.php';

// ── WooCommerce Quick View ──────────────────────────────────────────────────────
require_once get_template_directory() . '/functions/woocommerce-quick-view.php';

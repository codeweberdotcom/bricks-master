<?php


// Добавление поля телефона и интерфейса в личный кабинет
add_action('woocommerce_edit_account_form_fields', function () {
   $user_id  = get_current_user_id();
   $phone    = get_user_meta($user_id, 'phone', true);
   $verified = get_user_meta($user_id, 'phone_verified', true);

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
               <input type="text" name="account_phone" id="account_phone" class="form-control phone-mask" placeholder="+7(000)000-00-00" value="<?php echo esc_attr($phone); ?>">
               <label for="account_phone"><?php esc_html_e('Phone number', 'codeweber'); ?></label>
               <button type="button" class="btn btn-navy btn-lg" id="send-verification-code"><span class="d-block d-md-none"><i class="uil uil-angle-right"></i></span><span class="d-none d-md-block"><?php esc_html_e('Verify', 'codeweber'); ?></span></button>
            </div>
            <div id="sms-timer-info" class="text-muted small mb-3"></div>
         </div>

         <div id="verify-section" class="mb-3 col-md-6" style="display: none;">
            <div class="form-floating mb-2 d-flex align-items-start gap-2">
               <input type="text" id="phone_verification_code" class="form-control" placeholder="<?php esc_attr_e('Code from SMS', 'codeweber'); ?>">
               <label for="phone_verification_code"><?php esc_html_e('Code from SMS', 'codeweber'); ?></label>
               <button type="button" class="btn btn-green btn-lg" id="confirm-verification-code"><?php esc_html_e('Confirm', 'codeweber'); ?></button>
            </div>

         </div>

      </div>
   <?php } elseif ($woophonenumber && !$woophonenumbersms) {
   ?>
      <div class="form-floating mb-4">
         <input type="text" class="form-control phone-mask" name="account_phone" id="account_phone" value="<?php echo esc_attr($phone); ?>" placeholder="+7 (___) ___-__-__">
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


remove_action('woocommerce_register_form', 'wc_registration_privacy_policy_text', 20);


add_action('woocommerce_register_form', 'custom_wc_registration_privacy_policy_text', 20);

function custom_wc_registration_privacy_policy_text()
{
   if (wc_get_privacy_policy_text('registration')) : ?>
      <div class="woocommerce-privacy-policy-text custom-privacy-text fs-12">
         <?php wc_privacy_policy_text('registration'); ?>
      </div>
   <?php endif;
}



add_action('woocommerce_register_form', function () {
   $privacy_page_id = (int) get_option('wp_page_for_privacy_policy');
   $privacy_url = $privacy_page_id ? get_permalink($privacy_page_id) : '';

   $processing_doc_id = (int) get_option('codeweber_legal_consent_processing');
   $processing_url = ($processing_doc_id && get_post_status($processing_doc_id) === 'publish') ? get_permalink($processing_doc_id) : '';

   // Проверка наличия ошибок Woo
   $errors = wc_get_notices('error');
   $has_privacy_error = false;
   $has_pdn_error = false;

   foreach ($errors as $error) {
      if (strpos($error['notice'], __('You must agree to the Privacy Policy.', 'codeweber')) !== false) {
         $has_privacy_error = true;
      }
      if (strpos($error['notice'], __('You must agree to the processing of personal data.', 'codeweber')) !== false) {
         $has_pdn_error = true;
      }
   }
   ?>

   <p class="form-row-wide woocommerce-FormRow form-check mb-2 small-chekbox fs-12">
      <input type="checkbox"
         class="form-check-input <?php echo $has_privacy_error ? 'is-invalid' : ''; ?>"
         name="privacy_policy_consent"
         id="privacy_policy_consent"
         value="1"
         <?php checked(!empty($_POST['privacy_policy_consent'])); ?>
         required>
      <label for="privacy_policy_consent" class="form-check-label">
         <?php
         if ($privacy_url) {
            printf(
               __('I have read and agree to the <a href="%s" target="_blank">Privacy Policy</a>', 'codeweber'),
               esc_url($privacy_url)
            );
         } else {
            echo __('I have read and agree to the Privacy Policy', 'codeweber');
            echo ' <span style="color:red; font-weight:bold;">(' . __('No Privacy Policy page selected by administrator', 'codeweber') . ')</span>';
         }
         ?>
      </label>
      <?php if ($has_privacy_error) : ?>
   <div class="invalid-feedback d-block">
      <?php _e('You must agree to the Privacy Policy.', 'codeweber'); ?>
   </div>
<?php endif; ?>
</p>

<p class="form-row-wide woocommerce-FormRow form-check mb-2 small-chekbox fs-12">
   <input type="checkbox"
      class="form-check-input <?php echo $has_pdn_error ? 'is-invalid' : ''; ?>"
      name="pdn_consent"
      id="pdn_consent"
      value="1"
      <?php checked(!empty($_POST['pdn_consent'])); ?>
      required>
   <label for="pdn_consent" class="form-check-label">
      <?php
      if ($processing_url) {
         printf(
            __('I agree to the <a href="%s" target="_blank">processing of personal data</a>', 'codeweber'),
            esc_url($processing_url)
         );
      } else {
         echo __('I agree to the processing of personal data', 'codeweber');
         echo ' <span style="color:red; font-weight:bold;">(' . __('No consent document selected by administrator', 'codeweber') . ')</span>';
      }
      ?>
   </label>
   <?php if ($has_pdn_error) : ?>
<div class="invalid-feedback d-block">
   <?php _e('You must agree to the processing of personal data.', 'codeweber'); ?>
</div>
<?php endif; ?>
</p>

<?php
});


add_filter('woocommerce_registration_errors', function ($errors, $username, $email) {
   if (empty($_POST['privacy_policy_consent'])) {
      $errors->add('privacy_policy_consent_error', __('You must agree to the Privacy Policy.', 'codeweber'));
   }
   if (empty($_POST['pdn_consent'])) {
      $errors->add('pdn_consent_error', __('You must agree to the processing of personal data.', 'codeweber'));
   }
   return $errors;
}, 10, 3);



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

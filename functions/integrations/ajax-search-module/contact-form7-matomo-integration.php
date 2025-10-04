<?php

/**
 * Plugin Name: CF7 to Matomo Tracking
 * Description: Tracks Contact Form 7 submissions in Matomo
 * Text Domain: codeweber
 */

// Хук для инициализации плагина
add_action('init', 'init_cf7_matomo_integration');

function init_cf7_matomo_integration()
{
   // Только JavaScript отслеживание
   add_action('wp_footer', 'add_matomo_tracking_script');

   // Хуки для админки
   add_action('admin_menu', 'add_cf7_matomo_admin_submenu');
}

/**
 * Добавляем скрипт для отслеживания событий
 */
function add_matomo_tracking_script()
{
   if (is_admin()) {
      return;
   }

   // Локализация для JavaScript
   $localized_data = array(
      'rest_url' => 'https://bricksnew.test/wp-json/custom/v1/cf7-title/',
      'no_name_text' => __('No Name Form', 'codeweber'),
      'events' => array(
         'successful_submission' => __('Successful submission', 'codeweber'),
         'validation_error' => __('Validation error', 'codeweber'),
         'server_error' => __('Server error', 'codeweber'),
         'spam_detected' => __('Spam detected', 'codeweber'),
         'submit_button_clicked' => __('Submit button clicked', 'codeweber'),
      )
   );

?>
   <script type="text/javascript">
      // Локализованные данные
      const cf7MatomoData = <?php echo json_encode($localized_data); ?>;

      // Функция для получения названия формы через REST API
      async function getCF7FormTitle(formId) {
         if (!formId || formId === 'unknown') return cf7MatomoData.no_name_text;
         try {
            const response = await fetch(`${cf7MatomoData.rest_url}${formId}`);
            if (response.ok) {
               const data = await response.json();
               return data.title || cf7MatomoData.no_name_text;
            }
            return cf7MatomoData.no_name_text;
         } catch (error) {
            return cf7MatomoData.no_name_text;
         }
      }

      // Функция для отправки события в Matomo
      function trackCF7MatomoEvent(formId, formTitle, action, value = 0) {
         if (typeof _paq !== 'undefined') {
            _paq.push([
               'trackEvent',
               'Contact Form 7',
               action,
               formTitle + ' (ID: ' + formId + ')',
               value
            ]);
            console.log('CF7 Matomo Event: Contact Form 7 - ' + action + ' - ' + formTitle + ' (ID: ' + formId + ') - Value: ' + value);
         }
      }

      // ПРОСТОЙ КОД - только события CF7
      document.addEventListener('DOMContentLoaded', function() {

         // 1. КЛИК ПО КНОПКЕ (через делегирование кликов)
         document.addEventListener('click', async function(e) {
            if (e.target.type === 'submit' || e.target.classList.contains('wpcf7-submit')) {
               const form = e.target.closest('form.wpcf7-form');
               if (form) {
                  const formId = form.querySelector('input[name="_wpcf7"]')?.value;
                  if (formId) {
                     const formTitle = await getCF7FormTitle(formId);
                     trackCF7MatomoEvent(formId, formTitle, cf7MatomoData.events.submit_button_clicked, 1);
                  }
               }
            }
         });

         // 2. УСПЕШНАЯ ОТПРАВКА
         document.addEventListener('wpcf7mailsent', async function(event) {
            const formId = event.detail.contactFormId;
            const formTitle = await getCF7FormTitle(formId);
            trackCF7MatomoEvent(formId, formTitle, cf7MatomoData.events.successful_submission, 1);
         });

         // 3. ОШИБКИ ВАЛИДАЦИИ
         document.addEventListener('wpcf7invalid', async function(event) {
            const formId = event.detail.contactFormId;
            const formTitle = await getCF7FormTitle(formId);
            const errorsCount = event.detail.apiResponse?.invalid_fields?.length || 1;
            trackCF7MatomoEvent(formId, formTitle, cf7MatomoData.events.validation_error, errorsCount);
         });

         // 4. ОШИБКА СЕРВЕРА
         document.addEventListener('wpcf7mailfailed', async function(event) {
            const formId = event.detail.contactFormId;
            const formTitle = await getCF7FormTitle(formId);
            trackCF7MatomoEvent(formId, formTitle, cf7MatomoData.events.server_error, 0);
         });

         // 5. СПАМ
         document.addEventListener('wpcf7spam', async function(event) {
            const formId = event.detail.contactFormId;
            const formTitle = await getCF7FormTitle(formId);
            trackCF7MatomoEvent(formId, formTitle, cf7MatomoData.events.spam_detected, 1);
         });

      });
   </script>
<?php
}

// Добавляем подменю в меню CF7
function add_cf7_matomo_admin_submenu()
{
   add_submenu_page(
      'wpcf7',
      __('Matomo Tracking Settings', 'codeweber'),
      __('Matomo Tracking', 'codeweber'),
      'manage_options',
      'cf7-matomo-settings',
      'display_cf7_matomo_settings_page'
   );
}

function display_cf7_matomo_settings_page()
{
   $forms_count = class_exists('WPCF7') ? count(get_posts([
      'post_type' => 'wpcf7_contact_form',
      'numberposts' => -1,
      'post_status' => 'publish'
   ])) : 0;
?>
   <div class="wrap">
      <h1><?php _e('Contact Form 7 - Matomo Tracking Settings', 'codeweber'); ?></h1>

      <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-top: 20px;">
         <div>
            <div style="background: #d4edda; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745;">
               <h3><?php _e('JavaScript Tracking Active', 'codeweber'); ?></h3>
               <p><?php _e('All Contact Form 7 events are tracked via JavaScript with REST API.', 'codeweber'); ?></p>
            </div>

            <div style="margin-top: 20px; background: #f8f9fa; padding: 20px; border-radius: 5px;">
               <h3><?php _e('Tracked Events', 'codeweber'); ?></h3>
               <ul>
                  <li><?php _e('Submit button clicked', 'codeweber'); ?></li>
                  <li><?php _e('Successful submission', 'codeweber'); ?></li>
                  <li><?php _e('Server error', 'codeweber'); ?></li>
                  <li><?php _e('Validation error (with error count)', 'codeweber'); ?></li>
                  <li><?php _e('Spam detected', 'codeweber'); ?></li>
               </ul>

               <h4><?php _e('How it works:', 'codeweber'); ?></h4>
               <p><?php _e('Form titles are fetched from REST API:', 'codeweber'); ?> <code>/wp-json/custom/v1/cf7-title/{id}</code></p>
               <p><strong><?php _e('All forms:', 'codeweber'); ?></strong> <?php _e('Tracked via CF7 events (works for regular and modal forms)', 'codeweber'); ?></p>
            </div>
         </div>

         <div style="background: #f0f6fc; padding: 20px; border-radius: 5px; border-left: 4px solid #2271b1;">
            <h3><?php _e('Integration Status', 'codeweber'); ?></h3>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; font-size: 14px;">
               <h4 style="margin-top: 0;"><?php _e('Matomo JavaScript Tracking Status', 'codeweber'); ?></h4>
               <ul style="margin: 0;">
                  <li style="margin-bottom: 5px;"><?php _e('JavaScript tracking enabled', 'codeweber'); ?></li>
                  <li style="margin-bottom: 5px;"><?php _e('REST API integration', 'codeweber'); ?></li>
                  <li style="margin-bottom: 5px;"><?php echo sprintf(_n('%d CF7 form found', '%d CF7 forms found', $forms_count, 'codeweber'), $forms_count); ?></li>
                  <li style="margin-bottom: 5px;"><?php _e('CF7 events tracking', 'codeweber'); ?></li>
               </ul>
            </div>

            <h3><?php _e('REST API Endpoint', 'codeweber'); ?></h3>
            <p><code>GET /wp-json/custom/v1/cf7-title/{form_id}</code></p>
            <p><?php _e('Returns:', 'codeweber'); ?> <code>{"id":"1072","title":"Form Title","slug":"form-slug"}</code></p>
         </div>
      </div>
   </div>
<?php
}

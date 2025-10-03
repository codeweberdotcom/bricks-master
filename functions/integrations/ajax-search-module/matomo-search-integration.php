<?php

/**
 * Matomo Search Integration Module
 * Отправляет поисковые запросы в Matomo для анализа
 * Domain: codeweber
 */

// Проверяем установлен ли плагин Matomo
function matomo_is_plugin_active()
{
   return function_exists('is_plugin_active') && is_plugin_active('matomo/matomo.php');
}

// Добавляем меню в админку только если Matomo установлен
add_action('admin_menu', 'matomo_search_integration_admin_menu');
function matomo_search_integration_admin_menu()
{
   if (!matomo_is_plugin_active()) return;

   add_submenu_page(
      'search-statistics',
      __('Matomo Integration', 'codeweber'),
      __('Matomo Integration', 'codeweber'),
      'manage_options',
      'matomo-search-integration',
      'matomo_search_integration_page'
   );
}

// Основной хук - только если Matomo активен
if (matomo_is_plugin_active()) {
   add_action('before_save_search_query', 'matomo_track_search_from_hook', 10, 4);
}

// Жестко прописанные настройки
define('MATOMO_SITE_ID', 1);

// Функция: Получение visitor_id из cookies Matomo
function matomo_get_consistent_visitor_id()
{
   foreach ($_COOKIE as $name => $value) {
      if (strpos($name, '_pk_id_') === 0) {
         $parts = explode('.', $value);
         if (!empty($parts[0]) && strlen($parts[0]) === 16) {
            return $parts[0];
         }
      }
   }

   $user_ip = $_SERVER['REMOTE_ADDR'] ?? '';
   $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
   return substr(md5($user_ip . $user_agent), 0, 16);
}

// Функция: Отправка события поиска в Matomo
function matomo_track_search_event($search_query, $results_count)
{
   if (!get_option('matomo_track_searches', 1) || !matomo_is_plugin_active()) {
      return false;
   }

   $visitor_id = matomo_get_consistent_visitor_id();
   if (empty($visitor_id)) return false;

   $params = [
      'idsite' => 1,
      'rec' => 1,
      'ua' => $_SERVER['HTTP_USER_AGENT'] ?? '',
      '_id' => $visitor_id,
      'e_c' => 'Search',
      'e_a' => 'Query',
      'e_n' => $search_query,
      'e_v' => $results_count,
      'url' => home_url('/?s=' . urlencode($search_query)),
      'urlref' => $_SERVER['HTTP_REFERER'] ?? home_url(),
      'send_image' => 0,
   ];

   $response = wp_remote_post(
      home_url('/wp-json/matomo/v1/hit/'),
      [
         'timeout' => 10,
         'sslverify' => false,
         'body' => $params,
      ]
   );

   if (is_wp_error($response)) return false;

   $response_code = wp_remote_retrieve_response_code($response);
   return in_array($response_code, [200, 204]);
}

function matomo_track_search_from_hook($search_query, $results_count, $form_id, $matomo_data)
{
   matomo_track_search_event($search_query, $results_count);
}

// Страница настройки интеграции
function matomo_search_integration_page()
{
   $matomo_active = matomo_is_plugin_active();

   if (isset($_POST['save_matomo_settings']) && wp_verify_nonce($_POST['matomo_nonce'], 'save_matomo_settings')) {
      update_option('matomo_track_searches', isset($_POST['matomo_track_searches']) ? 1 : 0);
      update_option('matomo_debug_mode', isset($_POST['matomo_debug_mode']) ? 1 : 0);
      echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved!', 'codeweber') . '</p></div>';
   }

   $matomo_track_searches = get_option('matomo_track_searches', 1);
   $matomo_debug_mode = get_option('matomo_debug_mode', 0);
?>

   <div class="wrap">
      <h1><?php _e('Matomo Search Integration', 'codeweber'); ?></h1>

      <?php if (!$matomo_active): ?>
         <div class="notice notice-warning is-dismissible">
            <p><?php _e('Matomo plugin is not active. Search tracking is disabled.', 'codeweber'); ?></p>
         </div>
      <?php endif; ?>

      <div class="card">
         <h2><?php _e('Settings', 'codeweber'); ?></h2>
         <form method="post">
            <?php wp_nonce_field('save_matomo_settings', 'matomo_nonce'); ?>
            <table class="form-table">
               <tr>
                  <th scope="row"><?php _e('Track Searches', 'codeweber'); ?></th>
                  <td>
                     <input type="checkbox" id="matomo_track_searches" name="matomo_track_searches" value="1"
                        <?php checked($matomo_track_searches, 1); ?>>
                     <label for="matomo_track_searches"><?php _e('Send search queries to Matomo as Events', 'codeweber'); ?></label>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><?php _e('Debug Mode', 'codeweber'); ?></th>
                  <td>
                     <input type="checkbox" id="matomo_debug_mode" name="matomo_debug_mode" value="1" <?php checked($matomo_debug_mode, 1); ?>>
                     <label for="matomo_debug_mode"><?php _e('Enable debug logging', 'codeweber'); ?></label>
                  </td>
               </tr>
            </table>
            <p class="submit">
               <input type="submit" name="save_matomo_settings" class="button button-primary" value="<?php _e('Save Settings', 'codeweber'); ?>">
            </p>
         </form>
      </div>
   </div>

   <style>
      .card {
         margin: 20px 0;
         padding: 20px;
         background: #fff;
         border: 1px solid #ccd0d4;
         border-radius: 4px
      }

      .card h2 {
         margin-top: 0
      }
   </style>
<?php
}

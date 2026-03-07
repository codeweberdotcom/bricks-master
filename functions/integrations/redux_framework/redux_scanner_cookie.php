<?php

/**
 * Добавляет скрипт на фронтенд (все страницы сайта),
 * который после полной загрузки страницы отправляет cookies текущего пользователя
 * в окно-родитель (если страница открыта как popup из админки) через postMessage.
 *
 * Сканер в админке может открывать любую URL сайта — так собираются cookie с главной,
 * корзины, личного кабинета и т.д.
 */
add_action('wp_footer', function () {
   if (is_admin()) {
      return;
   }
   ?>
      <script>
         window.addEventListener('load', function() {
            if (window.opener) {
               window.opener.postMessage({
                  type: "frontend_cookies",
                  cookies: document.cookie
               }, window.location.origin);
            }
         });
      </script>
<?php
});

/**
 * Сохранение результатов скана в components/cookies-found[−ru].json по AJAX.
 * Файл выбирается по текущему языку: cookies-found-ru.json для ru_RU, иначе cookies-found.json.
 */
add_action('wp_ajax_codeweber_save_known_cookies', function () {
   if (!current_user_can('manage_options')) {
      wp_send_json_error(['message' => 'Forbidden']);
   }
   $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
   if (!wp_verify_nonce($nonce, 'codeweber_save_known_cookies')) {
      wp_send_json_error(['message' => 'Invalid nonce']);
   }
   $raw = isset($_POST['cookies_json']) ? wp_unslash($_POST['cookies_json']) : '';
   $incoming = json_decode($raw, true);
   if (!is_array($incoming)) {
      wp_send_json_error(['message' => 'Invalid JSON']);
   }
   $replace_entirely = !empty($_POST['replace_entirely']);
   $deleted_raw = isset($_POST['deleted_keys']) ? wp_unslash($_POST['deleted_keys']) : '[]';
   $deleted_keys = json_decode($deleted_raw, true);
   if (!is_array($deleted_keys)) {
      $deleted_keys = [];
   }

   $suffix = (strpos(get_locale(), 'ru') !== false) ? '-ru' : '';
   $basename = 'cookies-found' . $suffix . '.json';
   $dir = get_template_directory() . '/components';
   $file = $dir . '/' . $basename;
   $allowed_basenames = ['cookies-found.json', 'cookies-found-ru.json'];
   if (strpos($file, get_template_directory()) !== 0 || !in_array(basename($file), $allowed_basenames, true)) {
      wp_send_json_error(['message' => 'Invalid path']);
   }
   if (!is_dir($dir)) {
      wp_mkdir_p($dir);
   }

   if ($replace_entirely) {
      $data = $incoming;
   } else {
      $existing = [];
      if (file_exists($file)) {
         $content = file_get_contents($file);
         $decoded = json_decode($content, true);
         if (is_array($decoded)) {
            $existing = $decoded;
         }
      }
      $data = array_merge($existing, $incoming);
      foreach ($deleted_keys as $key) {
         if (is_string($key)) {
            unset($data[$key]);
         }
      }
   }
   $encoded = wp_json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
   if (file_put_contents($file, $encoded) === false) {
      wp_send_json_error(['message' => 'Could not write file']);
   }
   wp_send_json_success(['message' => sprintf(__('Saved to components/%s', 'codeweber'), $basename)]);
});

/**
 * Возвращает объединённые данные cookie (библиотека + сохранённые) для текущей локали.
 * Используется шорткодом [codeweber_cookie_table] и может использоваться в других местах.
 *
 * @return array<string, array{owner?: string, storage_duration?: string, type?: string, description?: string}>
 */
function codeweber_get_cookie_policy_table_data() {
   $suffix = (strpos(get_locale(), 'ru') !== false) ? '-ru' : '';
   $dir = get_template_directory() . '/components';

   $known = [];
   $file_known = $dir . '/cookies-known' . $suffix . '.json';
   if (!file_exists($file_known)) {
      $file_known = $dir . '/cookies-known.json';
   }
   if (file_exists($file_known)) {
      $json = file_get_contents($file_known);
      $decoded = json_decode($json, true);
      if (is_array($decoded)) {
         $known = $decoded;
      }
   }

   $found = [];
   $file_found = $dir . '/cookies-found' . $suffix . '.json';
   if (!file_exists($file_found)) {
      $file_found = $dir . '/cookies-found.json';
   }
   if (file_exists($file_found)) {
      $json = file_get_contents($file_found);
      $decoded = json_decode($json, true);
      if (is_array($decoded)) {
         $found = $decoded;
      }
   }

   return array_merge($found, $known);
}

/**
 * Шорткод [codeweber_cookie_table] — таблица cookie для документа «Политика cookie».
 * Выводит таблицу: идентификатор, назначение, срок хранения, тип.
 * Данные и язык берутся из cookies-known[−ru].json и cookies-found[−ru].json по текущей локали.
 */
add_shortcode('codeweber_cookie_table', function () {
   $data = codeweber_get_cookie_policy_table_data();
   if (empty($data)) {
      return '<p class="codeweber-cookie-table-empty">' . esc_html__('No cookie data available.', 'codeweber') . '</p>';
   }

   $type_labels = [
      'necessary' => __('Necessary', 'codeweber'),
      'analytics' => __('Analytics', 'codeweber'),
      'marketing' => __('Marketing', 'codeweber'),
      'functional' => __('Functional', 'codeweber'),
      'other' => __('Other', 'codeweber'),
   ];

   $html = '<div class="table-responsive codeweber-cookie-policy-table-wrap"><table class="table table-bordered codeweber-cookie-policy-table">';
   $html .= '<thead><tr>';
   $html .= '<th scope="col">' . esc_html__('Identifier', 'codeweber') . '</th>';
   $html .= '<th scope="col">' . esc_html__('Purpose', 'codeweber') . '</th>';
   $html .= '<th scope="col">' . esc_html__('Storage duration', 'codeweber') . '</th>';
   $html .= '<th scope="col">' . esc_html__('Cookie type', 'codeweber') . '</th>';
   $html .= '</tr></thead><tbody>';

   foreach ($data as $name => $info) {
      $description = isset($info['description']) ? $info['description'] : (isset($info['owner']) ? $info['owner'] : '');
      $storage = isset($info['storage_duration']) ? $info['storage_duration'] : '';
      $type_key = isset($info['type']) ? $info['type'] : 'other';
      $type_label = isset($type_labels[$type_key]) ? $type_labels[$type_key] : $type_labels['other'];

      $html .= '<tr>';
      $html .= '<td class="text-break">' . esc_html($name) . '</td>';
      $html .= '<td class="text-break">' . esc_html($description) . '</td>';
      $html .= '<td>' . esc_html($storage) . '</td>';
      $html .= '<td>' . esc_html($type_label) . '</td>';
      $html .= '</tr>';
   }

   $html .= '</tbody></table></div>';
   return $html;
});

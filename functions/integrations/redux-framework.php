<?php
/**
 * Получает пользовательские логотипы из Redux Framework.
 * 
 * Функция возвращает логотип в светлом, темном варианте или оба сразу.
 * Если пользовательские логотипы не заданы, используются стандартные изображения.
 *
 * @param string $type Тип логотипа: 'light' (светлый), 'dark' (тёмный) или 'both' (оба).
 * @return string HTML-код с логотипом (или логотипами).
 */
function get_custom_logo_type($type = 'both')
{
   global $opt_name;
   $options = get_option($opt_name);

   $post_id = get_the_ID(); // ID текущего поста или страницы
   $custom_dark_logo = get_post_meta($post_id, 'custom-logo-dark-header', true);
   $custom_light_logo = get_post_meta($post_id, 'custom-logo-light-header', true);

   $default_logos = array(
      'light' => get_template_directory_uri() . '/dist/assets/img/logo-light.png',
      'dark'  => get_template_directory_uri() . '/dist/assets/img/logo-dark.png',
   );

   // Если кастомные лого заданы, используем их, иначе берем из Redux или дефолт
   $dark_logo = !empty($custom_dark_logo['url'])
       ? $custom_dark_logo['url']
       : (!empty($options['opt-dark-logo']['url']) ? $options['opt-dark-logo']['url'] : $default_logos['dark']);

   $light_logo = !empty($custom_light_logo['url'])
       ? $custom_light_logo['url']
       : (!empty($options['opt-light-logo']['url']) ? $options['opt-light-logo']['url'] : $default_logos['light']);

   // Формируем HTML
   $dark_logo_html = sprintf(
      '<img class="logo-dark" src="%s" alt="">',
      esc_url($dark_logo)
   );

   $light_logo_html = sprintf(
      '<img class="logo-light" src="%s" alt="">',
      esc_url($light_logo)
   );

   if ($type === 'dark') {
      return $light_logo_html;
   } elseif ($type === 'light') {
      return $dark_logo_html;
   } elseif ($type === 'both') {
      return $dark_logo_html . "\n" . $light_logo_html;
   }

   return '';
}




/**
 * Шорткод [redux_option]
 * Возвращает значение из Redux Framework с возможностью вывода массива (checkbox) как строки или списка.
 *
 * Атрибуты:
 * - key     — ключ поля Redux.
 * - default — значение по умолчанию, если ключ не найден или пуст.
 * - format  — формат для даты, если значение является датой.
 * - list    — если указано "inline", значения массива объединяются через запятую;
 *             если указано "ul", выводятся как <ul><li>…</li></ul>.
 */
add_shortcode('redux_option', function ($atts) {
   global $opt_name;

   $atts = shortcode_atts(array(
      'key'     => '',
      'default' => '',
      'format'  => '',
      'list'    => '', // 'inline' | 'ul'
   ), $atts, 'redux_option');

   if (empty($atts['key']) || empty($opt_name)) {
      return '';
   }

   $value = Redux::get_option($opt_name, $atts['key']);

   if (empty($value)) {
      return esc_html($atts['default']);
   }

   // Массив опций с переводами для personal_data_actions
   $personal_data_actions_options = array(
      'collection'      => __('Collection Data', 'codeweber'),
      'recording'       => __('Recording Data', 'codeweber'),
      'systematization' => __('Systematization Data', 'codeweber'),
      'accumulation'    => __('Accumulation Data', 'codeweber'),
      'storage'         => __('Storage Data', 'codeweber'),
      'updating'        => __('Updating (clarification, modification) Data', 'codeweber'),
      'extraction'      => __('Extraction Data', 'codeweber'),
      'usage'           => __('Usage Data', 'codeweber'),
      'transfer'        => __('Transfer (distribution, provision, access) Data', 'codeweber'),
      'blocking'        => __('Blocking Data', 'codeweber'),
      'deletion'        => __('Deletion Data', 'codeweber'),
      'destruction'     => __('Destruction Data', 'codeweber'),
   );

   // Форматируем дату
   if (!empty($atts['format']) && strtotime($value)) {
      return esc_html(date($atts['format'], strtotime($value)));
   }

   // Если значение — массив (например, checkbox)
   if (is_array($value)) {
      // Для поля personal_data_actions подменяем ключи на переводы
      if ($atts['key'] === 'personal_data_actions') {
         // В $value ключи - выбранные опции, значения true/1
         $selected_keys = array_keys(array_filter($value));
         if ($atts['list'] === 'inline') {
            $items = array_map(function ($key) use ($personal_data_actions_options) {
               return $personal_data_actions_options[$key] ?? $key;
            }, $selected_keys);
            return esc_html(implode(', ', $items));
         } elseif ($atts['list'] === 'ul') {
            $out = '<ul>';
            foreach ($selected_keys as $key) {
               $label = $personal_data_actions_options[$key] ?? $key;
               $out .= '<li>' . esc_html($label) . '</li>';
            }
            $out .= '</ul>';
            return $out;
         }
      }

      // Если не personal_data_actions, просто выводим массив как строку
      if ($atts['list'] === 'inline') {
         return esc_html(implode(', ', $value));
      } elseif ($atts['list'] === 'ul') {
         $out = '<ul>';
         foreach ($value as $item) {
            $out .= '<li>' . esc_html($item) . '</li>';
         }
         $out .= '</ul>';
         return $out;
      }
   }

   // Обычное значение
   return $value;
});



/**
 * Добавляет скрипт на фронтенд (только на главную страницу),
 * который после полной загрузки страницы отправляет cookies текущего пользователя
 * в окно-родитель (если оно открыто) через postMessage.
 * 
 * Используется для получения cookies с фронтенда в админке через окно popup.
 */
add_action('wp_footer', function () {
   if (is_front_page()) : ?>
      <script>
         window.addEventListener('load', function() {
            if (window.opener) {
               window.opener.postMessage({
                  type: "frontend_cookies",
                  cookies: document.cookie
               }, "*");
            }
         });
      </script>
<?php
   endif;
});

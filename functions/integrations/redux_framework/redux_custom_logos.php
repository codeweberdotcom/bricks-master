<?php

/**
 * Получает URL из массива Redux media-поля, используя attachment ID.
 *
 * Использует wp_get_attachment_url() по ID вложения, что возвращает URL
 * с текущим доменом сайта. Это позволяет безболезненно переносить сайт
 * на другой домен без потери логотипов и медиа.
 *
 * @param array|string $media_data Данные из Redux media-поля (массив с 'id' и 'url') или строка URL.
 * @return string URL файла или пустая строка.
 */
function codeweber_get_media_url($media_data)
{
   if (!is_array($media_data)) {
      return (string) $media_data;
   }
   if (!empty($media_data['id'])) {
      $url = wp_get_attachment_url($media_data['id']);
      if ($url) {
         return $url;
      }
   }
   return $media_data['url'] ?? '';
}

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
   $dark_logo_url = codeweber_get_media_url($custom_dark_logo);
   if (empty($dark_logo_url)) {
      $dark_logo_url = !empty($options['opt-dark-logo']) ? codeweber_get_media_url($options['opt-dark-logo']) : '';
   }
   if (empty($dark_logo_url)) {
      $dark_logo_url = $default_logos['dark'];
   }

   $light_logo_url = codeweber_get_media_url($custom_light_logo);
   if (empty($light_logo_url)) {
      $light_logo_url = !empty($options['opt-light-logo']) ? codeweber_get_media_url($options['opt-light-logo']) : '';
   }
   if (empty($light_logo_url)) {
      $light_logo_url = $default_logos['light'];
   }

   // Формируем HTML
   $dark_logo_html = sprintf(
      '<img class="logo-dark" src="%s" alt="">',
      esc_url($dark_logo_url)
   );

   $light_logo_html = sprintf(
      '<img class="logo-light" src="%s" alt="">',
      esc_url($light_logo_url)
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
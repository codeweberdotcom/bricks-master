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
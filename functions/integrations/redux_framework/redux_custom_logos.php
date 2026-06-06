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
 * Список разрешённых SVG-тегов и атрибутов для wp_kses().
 *
 * Используется для безопасного вывода inline-SVG, введённого администратором
 * в настройках логотипа (Theme style → Logo SVG).
 *
 * @return array Карта допустимых тегов и их атрибутов.
 */
function codeweber_logo_svg_allowed_tags()
{
   $common = array(
      'class'        => true,
      'id'           => true,
      'style'        => true,
      'fill'         => true,
      'fill-rule'    => true,
      'clip-rule'    => true,
      'stroke'       => true,
      'stroke-width' => true,
      'stroke-linecap'  => true,
      'stroke-linejoin' => true,
      'opacity'      => true,
      'transform'    => true,
   );

   return array(
      'svg'      => array_merge($common, array(
         'xmlns'   => true,
         'viewbox' => true,
         'width'   => true,
         'height'  => true,
         'role'    => true,
         'aria-hidden' => true,
         'focusable'   => true,
         'preserveaspectratio' => true,
      )),
      'g'        => array_merge($common, array('clip-path' => true)),
      'path'     => array_merge($common, array('d' => true)),
      'circle'   => array_merge($common, array('cx' => true, 'cy' => true, 'r' => true)),
      'ellipse'  => array_merge($common, array('cx' => true, 'cy' => true, 'rx' => true, 'ry' => true)),
      'rect'     => array_merge($common, array('x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true)),
      'line'     => array_merge($common, array('x1' => true, 'y1' => true, 'x2' => true, 'y2' => true)),
      'polygon'  => array_merge($common, array('points' => true)),
      'polyline' => array_merge($common, array('points' => true)),
      'defs'     => $common,
      'use'      => array_merge($common, array('href' => true, 'xlink:href' => true)),
      'title'    => array(),
      'desc'     => array(),
      'lineargradient' => array_merge($common, array('x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'gradientunits' => true)),
      'radialgradient' => array_merge($common, array('cx' => true, 'cy' => true, 'r' => true, 'fx' => true, 'fy' => true, 'gradientunits' => true)),
      'stop'     => array_merge($common, array('offset' => true, 'stop-color' => true, 'stop-opacity' => true)),
      'clippath' => array_merge($common, array('clippathunits' => true)),
   );
}

/**
 * Формирует HTML для логотипа в виде «текст + SVG-иконка».
 *
 * @param string $svg        Inline-SVG разметка (будет очищена через wp_kses).
 * @param string $text       Текст логотипа.
 * @param string $variant    CSS-класс варианта: 'logo-dark' или 'logo-light'.
 * @param string $text_class Доп. CSS-классы для текста логотипа.
 * @param string $svg_class  Доп. CSS-классы (цвет) для иконки; SVG использует currentColor.
 * @return string HTML-код или пустая строка, если оба значения пусты.
 */
function codeweber_render_logo_textsvg($svg, $text, $variant, $text_class = '', $svg_class = '')
{
   $svg  = is_string($svg) ? trim($svg) : '';
   $text = is_string($text) ? trim($text) : '';

   if ($svg === '' && $text === '') {
      return '';
   }

   $html  = sprintf('<span class="logo-text-svg %s">', esc_attr($variant));
   $html .= '<span class="logo-inner d-inline-flex align-items-center">';
   if ($svg !== '') {
      $svg_class  = is_string($svg_class) ? trim($svg_class) : '';
      $svg_cls    = 'logo-svg d-inline-flex align-items-center' . ($svg_class !== '' ? ' ' . $svg_class : '');
      $html .= '<span class="' . esc_attr($svg_cls) . '">' . wp_kses($svg, codeweber_logo_svg_allowed_tags()) . '</span>';
   }
   if ($text !== '') {
      $text_class = is_string($text_class) ? trim($text_class) : '';
      $class      = 'logo-text' . ($text_class !== '' ? ' ' . $text_class : '');
      $html .= '<span class="' . esc_attr($class) . '">' . esc_html($text) . '</span>';
   }
   $html .= '</span></span>';

   return $html;
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

   $post_id = get_queried_object_id();
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

   // Режим «текст + SVG»: заменяем картинки на inline-SVG с текстом.
   // Если вариант пуст — остаётся картинка (fallback), чтобы логотип всегда отображался.
   $logo_type = isset($options['opt-logo-type']) ? $options['opt-logo-type'] : 'image';
   if ($logo_type === 'text_svg') {
      $dark_textsvg = codeweber_render_logo_textsvg(
         isset($options['opt-logo-dark-svg']) ? $options['opt-logo-dark-svg'] : '',
         isset($options['opt-logo-dark-text']) ? $options['opt-logo-dark-text'] : '',
         'logo-dark',
         isset($options['opt-logo-dark-text-class']) ? $options['opt-logo-dark-text-class'] : '',
         isset($options['opt-logo-dark-svg-class']) ? $options['opt-logo-dark-svg-class'] : ''
      );
      if ($dark_textsvg !== '') {
         $dark_logo_html = $dark_textsvg;
      }

      $light_textsvg = codeweber_render_logo_textsvg(
         isset($options['opt-logo-light-svg']) ? $options['opt-logo-light-svg'] : '',
         isset($options['opt-logo-light-text']) ? $options['opt-logo-light-text'] : '',
         'logo-light',
         isset($options['opt-logo-light-text-class']) ? $options['opt-logo-light-text-class'] : '',
         isset($options['opt-logo-light-svg-class']) ? $options['opt-logo-light-svg-class'] : ''
      );
      if ($light_textsvg !== '') {
         $light_logo_html = $light_textsvg;
      }
   }

   if ($type === 'dark') {
      return $light_logo_html;
   } elseif ($type === 'light') {
      return $dark_logo_html;
   } elseif ($type === 'both') {
      return $dark_logo_html . "\n" . $light_logo_html;
   }

   return '';
}
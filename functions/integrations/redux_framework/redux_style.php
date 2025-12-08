<?php

/**
 * Получение стиля формы кнопки из Redux Framework с поддержкой класса по умолчанию
 * Также доступно как шорткод: [getthemebutton default=" rounded-pill"]
 *
 * @param string $default_class Класс по умолчанию
 * @return string CSS-класс формы кнопки
 */
if (! function_exists('getThemeButton')) {
   function getThemeButton($default_class = ' rounded-pill')
   {
      global $opt_name;

      // Карта соответствий опций Redux → CSS классы
      $style_map = [
         '1' => ' rounded-pill',
         '2' => '',
         '3' => ' rounded-xl',
         '4' => ' rounded-0',
      ];

      // Получаем значение из Redux (по умолчанию '1')
      $style_key = Redux::get_option($opt_name, 'opt_button_select_style', '1');

      // Возвращаем класс из карты или переданный по умолчанию
      return isset($style_map[$style_key]) ? $style_map[$style_key] : $default_class;
   }
}

/**
 * Получение стиля скругления карточек и изображений из Redux Framework
 * Использование: <?php echo getThemeCardImageRadius(); ?> - вернет стандартный Bootstrap класс
 *
 * @param string $default_class Класс по умолчанию
 * @return string Стандартный Bootstrap CSS-класс скругления
 */
if (! function_exists('getThemeCardImageRadius')) {
   function getThemeCardImageRadius($default_class = '')
   {
      global $opt_name;

      // Карта соответствий опций Redux → стандартные Bootstrap классы
      $style_map = [
         '2' => '',
         '3' => 'rounded-xl',
         '4' => 'rounded-0',
      ];

      // Получаем значение из Redux (по умолчанию '2')
      $style_key = Redux::get_option($opt_name, 'opt_card_image_border_radius', '2');

      // Возвращаем стандартный Bootstrap класс
      return isset($style_map[$style_key]) ? $style_map[$style_key] : $default_class;
   }
}
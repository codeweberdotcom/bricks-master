<?php

/**
 * Шорткод для вывода контактных данных из Redux Framework
 * 
 * Позволяет выводить email и телефоны как простым текстом, так и с кликабельными ссылками.
 * Поддерживает кастомные классы, альтернативный текст и защиту от спама для email.
 * 
 * ### Примеры использования:
 * 
 * 1. Простой вывод телефона:
 *    [get_contact field="phone_01"]
 *    Вывод: <span>+7(495)000-00-00</span>
 * 
 * 2. Телефон с кликабельной ссылкой:
 *    [get_contact field="phone_01" type="link"]
 *    Вывод: <a href="tel:+74950000000">+7(495)000-00-00</a>
 * 
 * 3. Телефон со ссылкой и кастомным классом:
 *    [get_contact field="phone_01" type="link" class="phone-link"]
 *    Вывод: <a href="tel:+74950000000" class="phone-link">+7(495)000-00-00</a>
 * 
 * 4. Email без ссылки:
 *    [get_contact field="e-mail"]
 *    Вывод: <span>test@mail.com</span>
 * 
 * 5. Email с кликабельной ссылкой:
 *    [get_contact field="e-mail" type="link"]
 *    Вывод: <a href="mailto:test@mail.com">test@mail.com</a>
 * 
 * 6. Email с ссылкой и кастомным текстом:
 *    [get_contact field="e-mail" type="link" text="Напишите нам"]
 *    Вывод: <a href="mailto:test@mail.com">Напишите нам</a>
 * 
 * 7. С кастомным классом для plain-текста:
 *    [get_contact field="phone_01" wrapper_class="text-muted"]
 *    Вывод: <span class="text-muted">+7(495)000-00-00</span>
 * 
 * **
 * Шорткод для вывода контактных данных из Redux Framework
 * 
 * Позволяет выводить email и телефоны как простым текстом, так и с кликабельными ссылками
 * Поддерживает кастомные классы, альтернативный текст и защиту от спама для email
 * 
 * Обработчик шорткода [get_contact]
 *
 * @param array $atts Атрибуты шорткода:
 *    - field (string) - ID поля в Redux (обязательный)
 *    - type (string) - Тип вывода: 'plain' (по умолчанию) или 'link'
 *    - text (string) - Альтернативный текст для ссылки
 *    - class (string) - CSS класс для элемента (для type="link")
 *    - wrapper_class (string) - CSS класс для обертки (для type="plain")
 * 
 * @return string HTML-код контакта или пустая строка, если поле не найдено
 */

   function get_contact_shortcode($atts)
   {
      // Получаем параметры шорткода
      $atts = shortcode_atts(
         array(
            'field' => '', // ID поля (например, 'e-mail', 'phone_01')
            'type' => 'plain', // Тип вывода: plain (просто текст), link (ссылка)
            'text' => '', // Альтернативный текст для ссылки
            'class' => '', // Дополнительные классы
            'wrapper_class' => '' // Класс для span (если type="plain")
         ),
         $atts,
         'get_contact'
      );

   global $opt_name;

      // Получаем значение поля из Redux (замените YOUR_OPT_NAME на вашу переменную $opt_name)
      $value = Redux::get_option($opt_name, $atts['field']);

      // Если значение пустое, возвращаем пустую строку
      if (empty($value)) {
         return '';
      }

      // Обрабатываем вывод в зависимости от типа
      switch ($atts['type']) {
         case 'link':
            // Определяем, email это или телефон
            if ($atts['field'] === 'e-mail') {
               $href = 'mailto:' . antispambot($value);
               $link_text = !empty($atts['text']) ? $atts['text'] : antispambot($value);
            } else {
               // Удаляем все нецифровые символы для tel ссылки
               $phone_number = preg_replace('/[^0-9+]/', '', $value);
               $href = 'tel:' . $phone_number;
               $link_text = !empty($atts['text']) ? $atts['text'] : $value;
            }

            $class_attr = !empty($atts['class']) ? ' class="' . esc_attr($atts['class']) . '"' : '';
            return '<a href="' . esc_attr($href) . '"' . $class_attr . '>' . esc_html($link_text) . '</a>';

         case 'plain':
         default:
            $wrapper_class = !empty($atts['wrapper_class']) ? ' class="' . esc_attr($atts['wrapper_class']) . '"' : '';
            return '<span' . $wrapper_class . '>' . esc_html($value) . '</span>';
      }
   }
   add_shortcode('get_contact', 'get_contact_shortcode');
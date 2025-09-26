<?php

/**
 * Генерирует навигационную цепочку (хлебные крошки) для WordPress
 * 
 * Поддерживает популярные SEO-плагины: Rank Math, Yoast SEO, SEOPress, All in One SEO
 * Имеет fallback-реализацию для случаев, когда плагины не установлены
 * 
 * @param string|null $align  Выравнивание элементов: 'left', 'center', 'right'
 * @param string|null $color  Цвет текста: 'white', 'dark'
 * @param string|null $class  Дополнительные CSS классы для контейнера
 * 
 * @return void Выводит HTML-разметку хлебных крошек
 * 
 * @example get_breadcrumbs('center', 'white', 'my-custom-class');
 * @example get_breadcrumbs('right'); // Только выравнивание
 * @example get_breadcrumbs(); // Все параметры по умолчанию
 * 
 * @since 1.0.0
 */
if (!function_exists('get_breadcrumbs')) {
   function get_breadcrumbs($align = null, $color = null, $class = null)
   {
      // Классы для <ol>
      $ol_classes = ['breadcrumb'];

      $class_nav = array();
      if (!empty($class)) {
         $class_nav[] = $class;
      }

      if ($align === 'center') {
         $ol_classes[] = 'justify-content-center ' . implode(" ", $class_nav);
      } elseif ($align === 'right') {
         $ol_classes[] = 'justify-content-end ' . implode(" ", $class_nav);
      } else {
         $ol_classes[] = 'justify-content-start ' . implode(" ", $class_nav);
      }

      // Добавляем классы цвета только если цвет указан
      if ($color === 'white') {
         $ol_classes[] = 'text-white';
      } elseif ($color === 'dark') {
         $ol_classes[] = 'text-dark';
      }

      $classes_str = implode(' ', $ol_classes);

      // Обёртка
      $wrap_before = '<nav class="d-inline-block w-100" aria-label="breadcrumb"><ol class="' . esc_attr($classes_str) . '">';
      $wrap_after  = '</ol></nav>';

      // Если доступна Rank Math
      if (function_exists('rank_math_the_breadcrumbs')) {
         $args = [
            'delimiter'   => '',
            'separator'   => '',
            'wrap_before' => $wrap_before,
            'wrap_after'  => $wrap_after,
            'before'      => '<li class="breadcrumb-item">',
            'after'       => '</li>',
         ];

         add_filter('rank_math/frontend/breadcrumb/args', function () use ($args) {
            return $args;
         });

         add_filter('rank_math/frontend/breadcrumb/html', function ($html, $crumbs, $class) use ($color) {
            $html = str_replace(['<span class="separator">', '</span>', '<span class="text-muted">'], '', $html);

            // Применяем стили только если цвет указан
            if ($color === 'white') {
               $html = str_replace('<span class="last">', '<span class="text-white">', $html);
               $html = str_replace('<a class="last"', '<a class="text-white"', $html);
            }

            return $html;
         }, 10, 3);

         rank_math_the_breadcrumbs();
      }

      // Yoast
      elseif (function_exists('yoast_breadcrumb')) {

         add_filter('wpseo_breadcrumb_separator', function () {
            return '';
         });

         add_filter('wpseo_breadcrumb_single_link', function ($link_output, $link) {
            if (!empty($link['url'])) {
               return '<li class="breadcrumb-item"><a href="' . esc_url($link['url']) . '">' . esc_html($link['text']) . '</a></li>';
            } else {
               return '<li class="breadcrumb-item active" aria-current="page">' . esc_html($link['text']) . '</li>';
            }
         }, 10, 2);

         add_filter('wpseo_breadcrumb_output', function ($output) {
            // Удаляем обёртки <span> вокруг всей строки
            $output = preg_replace('#^<span[^>]*>#', '', $output); // открывающий <span>
            $output = preg_replace('#</span>$#', '', $output);     // закрывающий </span>
            return $output;
         });

         yoast_breadcrumb($wrap_before, $wrap_after);
      }

      // SEOPress
      elseif (function_exists('seopress_display_breadcrumbs')) {
         seopress_display_breadcrumbs();
      }

      // All in One SEO
      elseif (function_exists('aioseo_breadcrumbs')) {

         ob_start();
         aioseo_breadcrumbs();
         $html = ob_get_clean();

         // Убираем обертку
         $html = str_replace('<div class="aioseo-breadcrumbs">', '', $html);
         $html = str_replace('</div>', '', $html);

         // Удаляем разделители
         $html = preg_replace('/<span class="aioseo-breadcrumb-separator">.*?<\/span>/', '', $html);

         // Разбиваем на элементы
         preg_match_all('/<span class="aioseo-breadcrumb">(.*?)<\/span>/s', $html, $matches);

         if (!empty($matches[1])) {
            echo '<ol class="' . esc_attr($classes_str) . '">';
            $total = count($matches[1]);
            foreach ($matches[1] as $index => $crumb) {
               $is_last = ($index === $total - 1);
               if ($is_last) {
                  echo '<li class="breadcrumb-item active" aria-current="page">' . $crumb . '</li>';
               } else {
                  echo '<li class="breadcrumb-item">' . $crumb . '</li>';
               }
            }
            echo '</ol>';
         }
      }

      // Fallback - собственная реализация
      else {
         echo $wrap_before;

         echo '<li class="breadcrumb-item"><a href="' . esc_url(home_url('/')) . '">' . __('Home', 'codeweber') . '</a></li>';

         if (is_category() || is_single()) {
            if (is_category()) {
               echo '<li class="breadcrumb-item active" aria-current="page">' . single_cat_title('', false) . '</li>';
            } elseif (is_single()) {
               $post_type = universal_get_post_type();

               if ($post_type === 'product' && function_exists('wc_get_page_id')) {
                  // WooCommerce product
                  $shop_page_url = get_permalink(wc_get_page_id('shop'));
                  echo '<li class="breadcrumb-item"><a href="' . esc_url($shop_page_url) . '">' . esc_html__('Shop', 'woocommerce') . '</a></li>';

                  // Получаем категории товара
                  $terms = get_the_terms(get_the_ID(), 'product_cat');
                  if ($terms && !is_wp_error($terms)) {
                     // Получаем самую "глубокую" категорию
                     $term = $terms[0];
                     foreach ($terms as $t) {
                        if ($t->parent > 0) {
                           $term = $t;
                           break;
                        }
                     }

                     // Выводим родительские категории
                     $parents = get_ancestors($term->term_id, 'product_cat');
                     $parents = array_reverse($parents);
                     foreach ($parents as $parent_id) {
                        $parent = get_term($parent_id, 'product_cat');
                        echo '<li class="breadcrumb-item"><a href="' . get_term_link($parent) . '">' . esc_html($parent->name) . '</a></li>';
                     }

                     // Выводим текущую категорию
                     echo '<li class="breadcrumb-item"><a href="' . get_term_link($term) . '">' . esc_html($term->name) . '</a></li>';
                  }
               } else {
                  $post_type_obj = get_post_type_object($post_type);

                  if ($post_type_obj && !empty($post_type_obj->has_archive)) {
                     echo '<li class="breadcrumb-item"><a href="' . get_post_type_archive_link($post_type) . '">' . esc_html($post_type_obj->labels->name) . '</a></li>';
                  }
               }

               echo '<li class="breadcrumb-item active" aria-current="page">' . get_the_title() . '</li>';
            }
         } elseif (is_category() || is_tag()) {
            // Добавляем "Shop" для категорий и меток
            if (function_exists('wc_get_page_id')) {
               $shop_page_url = get_permalink(wc_get_page_id('shop'));
               echo '<li class="breadcrumb-item"><a href="' . esc_url($shop_page_url) . '">' . esc_html__('Shop', 'woocommerce') . '</a></li>';
            }

            // Категория
            if (is_category()) {
               echo '<li class="breadcrumb-item active" aria-current="page">' . single_cat_title('', false) . '</li>';
            }

            // Метка
            elseif (is_tag()) {
               echo '<li class="breadcrumb-item active" aria-current="page">' . single_tag_title('', false) . '</li>';
            }
         } elseif (is_page()) {
            $parents = [];
            $parent_id = wp_get_post_parent_id(get_the_ID());

            while ($parent_id) {
               $page = get_post($parent_id);
               $parents[] = '<li class="breadcrumb-item"><a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a></li>';
               $parent_id = $page->post_parent;
            }

            echo implode('', array_reverse($parents));
            echo '<li class="breadcrumb-item active" aria-current="page">' . get_the_title() . '</li>';
         } elseif (is_search()) {
            echo '<li class="breadcrumb-item active" aria-current="page">' . sprintf(__('Search results for "%s"', 'codeweber'), get_search_query()) . '</li>';
         } elseif (is_404()) {
            echo '<li class="breadcrumb-item active" aria-current="page">' . __('Page not found', 'codeweber') . '</li>';
         } elseif (is_archive()) {
            echo '<li class="breadcrumb-item active" aria-current="page">' . get_the_archive_title() . '</li>';
         }

         echo $wrap_after;
      }
   }
}


/**
 * Шорткод для вывода хлебных крошек
 * 
 * @param array $atts Атрибуты шорткода:
 *                    - align: выравнивание ('left', 'center', 'right')
 *                    - color: цвет текста ('white', 'dark').
 *                             Для стандартного цвета темы - не указывайте этот параметр
 *                    - class: дополнительные CSS классы
 * 
 * @return string HTML-разметка хлебных крошек
 * 
 * @example [breadcrumbs] // Стандартные настройки
 * @example [breadcrumbs align="center"] // Только выравнивание по центру
 * @example [breadcrumbs class="mb-4 custom-class"] // Только дополнительные классы
 * @example [breadcrumbs align="right" color="white"] // Белый текст по правому краю
 * 
 * @since 1.0.0
 */
add_shortcode('breadcrumbs', 'breadcrumbs_shortcode');

if (!function_exists('breadcrumbs_shortcode')) {
   function breadcrumbs_shortcode($atts)
   {
      // Извлекаем атрибуты
      $atts = shortcode_atts(array(
         'align' => 'left',
         'color' => '',
         'class' => ''
      ), $atts, 'breadcrumbs');

      // Запускаем вывод в буфер
      ob_start();

      // Вызываем вашу функцию с переданными параметрами
      get_breadcrumbs(
         $atts['align'],
         $atts['color'],
         $atts['class']
      );

      // Возвращаем содержимое буфера
      return ob_get_clean();
   }
}
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
      // Redux breadcrumb settings
      $is_woo_context = function_exists('is_woocommerce') && is_woocommerce();

      $show_home  = true;
      $home_label = '';
      $hide_last  = false;
      if (class_exists('Codeweber_Options')) {
         if ($is_woo_context) {
            $show_home  = (bool) Codeweber_Options::get('breadcrumb_woo_show_home', true);
            $home_label = (string) Codeweber_Options::get('breadcrumb_woo_home_label', '');
            $hide_last  = (bool) Codeweber_Options::get('breadcrumb_woo_hide_last_single', false);
         } else {
            $show_home  = (bool) Codeweber_Options::get('breadcrumb_show_home', true);
            $home_label = (string) Codeweber_Options::get('breadcrumb_home_label', '');
            $hide_last  = (bool) Codeweber_Options::get('breadcrumb_hide_last_single', false);
         }
      }
      if (empty($home_label)) {
         $home_label = __('Главная', 'codeweber');
      }
      $is_single_context = is_singular() && ! is_page();

      // Классы для <ol>
      $ol_classes = ['breadcrumb'];

      $class_nav = [];
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

      // WooCommerce: на странице My Account используем крошки WooCommerce в стиле темы
      if (class_exists('WooCommerce') && function_exists('is_account_page') && is_account_page()) {
         $breadcrumbs = new WC_Breadcrumb();
         $breadcrumbs->add_crumb(
            $home_label,
            apply_filters('woocommerce_breadcrumb_home_url', home_url())
         );
         $crumbs = $breadcrumbs->generate();
         if (!empty($crumbs)) {
            if (!$show_home) {
               array_shift($crumbs);
            }
            if ($hide_last && $is_single_context && count($crumbs) > 0) {
               array_pop($crumbs);
            }
            echo $wrap_before;
            $total = count($crumbs);
            foreach ($crumbs as $key => $crumb) {
               $is_last = ($key === $total - 1);
               $title   = isset($crumb[0]) ? $crumb[0] : '';
               $url     = isset($crumb[1]) ? $crumb[1] : '';
               if ($is_last || empty($url)) {
                  echo '<li class="breadcrumb-item active" aria-current="page">' . esc_html($title) . '</li>';
               } else {
                  echo '<li class="breadcrumb-item"><a href="' . esc_url($url) . '">' . esc_html($title) . '</a></li>';
               }
            }
            echo $wrap_after;
         }
         return;
      }

      // ── Общий контекст для всех плагинов ─────────────────────────────────────
      $is_any_woo = function_exists('is_woocommerce') && (
         is_woocommerce()    ||
         ( function_exists('is_cart')         && is_cart() )         ||
         ( function_exists('is_checkout')     && is_checkout() )     ||
         ( function_exists('is_account_page') && is_account_page() )
      );
      $needs_blog_crumb = ! $is_any_woo && (
         is_category() || is_date() ||
         ( is_tag() && ! ( function_exists('is_product_tag') && is_product_tag() ) ) ||
         ( is_single() && get_post_type() === 'post' )
      );
      $blog_page_id = ( $needs_blog_crumb || is_home() ) ? (int) get_option('page_for_posts') : 0;
      $is_blog_page = is_home() && $blog_page_id > 0;
      $shop_url = ( ! $is_any_woo && function_exists('wc_get_page_id') )
         ? trailingslashit( (string) get_permalink( wc_get_page_id('shop') ) )
         : '';

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

         static $rank_math_filters_registered = false;
         if ( ! $rank_math_filters_registered ) {
            $rank_math_filters_registered = true;

            add_filter('rank_math/frontend/breadcrumb/args', function ($a) use ($args, $home_label) {
               $result = $args;
               $result['home_label'] = $home_label;
               return $result;
            });

            // WooCommerce product tag: крошка содержит "Products tagged «tagname»" — оставляем только название тега
            add_filter('rank_math/frontend/breadcrumb/items', function ($crumbs) use ($show_home, $hide_last, $is_single_context, $is_any_woo, $needs_blog_crumb, $blog_page_id, $shop_url, $is_blog_page) {
               if (function_exists('is_product_tag') && is_product_tag()) {
                  $last = count($crumbs) - 1;
                  if (isset($crumbs[$last][0])) {
                     $crumbs[$last][0] = single_term_title('', false);
                  }
               }

               // Убираем Shop-крошку вне WooCommerce-контекста
               if ( $shop_url ) {
                  $crumbs = array_values( array_filter( $crumbs, function ( $c ) use ( $shop_url ) {
                     return trailingslashit( $c[1] ?? '' ) !== $shop_url;
                  } ) );
               }

               // Вставляем крошку «Блог» для стандартных архивов WordPress
               if ( $needs_blog_crumb && $blog_page_id ) {
                  $blog_crumb = [ get_the_title( $blog_page_id ), get_permalink( $blog_page_id ) ];
                  array_splice( $crumbs, 1, 0, [ $blog_crumb ] );
               }

               // Страница блога — добавляем как активную последнюю крошку
               if ( $is_blog_page ) {
                  $crumbs[] = [ get_the_title( $blog_page_id ), '' ];
               }

               if (!$show_home && !empty($crumbs)) {
                  array_shift($crumbs);
               }
               if ($hide_last && $is_single_context && count($crumbs) > 0) {
                  array_pop($crumbs);
               }
               return $crumbs;
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
         }

         rank_math_the_breadcrumbs();
      }

      // Yoast
      elseif (function_exists('yoast_breadcrumb')) {

         add_filter('wpseo_breadcrumb_separator', function () {
            return '';
         });

         add_filter('wpseo_breadcrumb_single_link', function ($link_output, $link) use ($home_label) {
            $text = esc_html($link['text']);
            if (!empty($link['url'])) {
               // Подменяем текст первой крошки (Home) на кастомный label
               if (trailingslashit($link['url']) === trailingslashit(home_url('/'))) {
                  $text = esc_html($home_label);
               }
               return '<li class="breadcrumb-item"><a href="' . esc_url($link['url']) . '">' . $text . '</a></li>';
            } else {
               return '<li class="breadcrumb-item active" aria-current="page">' . $text . '</li>';
            }
         }, 10, 2);

         add_filter('wpseo_breadcrumb_output', function ($output) use ($show_home, $hide_last, $is_single_context, $needs_blog_crumb, $blog_page_id, $shop_url, $is_blog_page) {
            // Удаляем обёртки <span> вокруг всей строки
            $output = preg_replace('#^<span[^>]*>#', '', $output);
            $output = preg_replace('#</span>$#', '', $output);
            // Убираем Shop-крошку вне WooCommerce-контекста
            if ( $shop_url ) {
               $output = preg_replace(
                  '#<li class="breadcrumb-item">\s*<a[^>]*href="' . preg_quote( rtrim( $shop_url, '/' ), '#' ) . '[/]?"[^>]*>.*?</a>\s*</li>#s',
                  '',
                  $output
               );
            }
            // Вставляем крошку «Блог» после первого <li> (Home)
            if ( $needs_blog_crumb && $blog_page_id ) {
               $blog_li = '<li class="breadcrumb-item"><a href="' . esc_url( get_permalink( $blog_page_id ) ) . '">' . esc_html( get_the_title( $blog_page_id ) ) . '</a></li>';
               $output  = preg_replace( '#(<li[^>]*>.*?</li>)#s', '$1' . $blog_li, $output, 1 );
            }
            // Страница блога — добавляем как активную последнюю крошку
            if ( $is_blog_page ) {
               $output .= '<li class="breadcrumb-item active" aria-current="page">' . esc_html( get_the_title( $blog_page_id ) ) . '</li>';
            }
            // Скрыть Home
            if (!$show_home) {
               $output = preg_replace('#<li class="breadcrumb-item"[^>]*>.*?</li>#s', '', $output, 1);
            }
            // Скрыть последнюю крошку на сингле
            if ($hide_last && $is_single_context) {
               $output = preg_replace('#<li class="breadcrumb-item active"[^>]*>.*?</li>\s*$#s', '', $output);
            }
            return $output;
         });

         yoast_breadcrumb($wrap_before, $wrap_after);
      }

      // SEOPress
      elseif (function_exists('seopress_display_breadcrumbs')) {
         ob_start();
         seopress_display_breadcrumbs();
         $html = ob_get_clean();
         // Убираем Shop-крошку вне WooCommerce-контекста
         if ( $shop_url ) {
            $html = preg_replace(
               '#<li[^>]*>\s*<a[^>]*href="' . preg_quote( rtrim( $shop_url, '/' ), '#' ) . '[/]?"[^>]*>.*?</a>\s*</li>#s',
               '',
               $html
            );
         }
         // Вставляем крошку «Блог» после первого <li>
         if ( $needs_blog_crumb && $blog_page_id ) {
            $blog_li = '<li><a href="' . esc_url( get_permalink( $blog_page_id ) ) . '">' . esc_html( get_the_title( $blog_page_id ) ) . '</a></li>';
            $html    = preg_replace( '#(<li[^>]*>.*?</li>)#s', '$1' . $blog_li, $html, 1 );
         }
         // Страница блога — добавляем как активную последнюю крошку
         if ( $is_blog_page ) {
            $html .= '<li class="breadcrumb-item active" aria-current="page">' . esc_html( get_the_title( $blog_page_id ) ) . '</li>';
         }
         // Скрыть Home: убираем первую ссылку-крошку
         if (!$show_home) {
            $html = preg_replace('#<li[^>]*>.*?</li>#s', '', $html, 1);
         }
         // Скрыть последнюю на сингле
         if ($hide_last && $is_single_context) {
            $html = preg_replace('#<li[^>]*>[^<]*</li>\s*$#s', '', $html);
         }
         echo $html;
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
            $items = $matches[1];
            // Убираем Shop-элемент вне WooCommerce-контекста
            if ( $shop_url ) {
               $items = array_values( array_filter( $items, function( $item ) use ( $shop_url ) {
                  return strpos( $item, rtrim( $shop_url, '/' ) ) === false;
               } ) );
            }
            // Вставляем Blog-элемент после Home (позиция 1)
            if ( $needs_blog_crumb && $blog_page_id ) {
               $blog_item = '<a href="' . esc_url( get_permalink( $blog_page_id ) ) . '">' . esc_html( get_the_title( $blog_page_id ) ) . '</a>';
               array_splice( $items, 1, 0, [ $blog_item ] );
            }
            // Страница блога — добавляем как активную последнюю крошку
            if ( $is_blog_page ) {
               $items[] = esc_html( get_the_title( $blog_page_id ) );
            }
            if (!$show_home) {
               array_shift($items);
            }
            if ($hide_last && $is_single_context && count($items) > 0) {
               array_pop($items);
            }
            echo '<ol class="' . esc_attr($classes_str) . '">';
            $total = count($items);
            foreach ($items as $index => $crumb) {
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

         if ($show_home) {
            echo '<li class="breadcrumb-item"><a href="' . esc_url(home_url('/')) . '">' . esc_html($home_label) . '</a></li>';
         }

         if ( $is_blog_page ) {
            echo '<li class="breadcrumb-item active" aria-current="page">' . esc_html( get_the_title( $blog_page_id ) ) . '</li>';
         } elseif (is_category() || is_single()) {
            if (is_category()) {
               if ( $needs_blog_crumb && $blog_page_id ) {
                  echo '<li class="breadcrumb-item"><a href="' . esc_url( get_permalink( $blog_page_id ) ) . '">' . esc_html( get_the_title( $blog_page_id ) ) . '</a></li>';
               }
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
                  if ( $post_type === 'post' && $blog_page_id ) {
                     echo '<li class="breadcrumb-item"><a href="' . esc_url( get_permalink( $blog_page_id ) ) . '">' . esc_html( get_the_title( $blog_page_id ) ) . '</a></li>';
                  } else {
                     $post_type_obj = get_post_type_object($post_type);
                     if ($post_type_obj && !empty($post_type_obj->has_archive)) {
                        echo '<li class="breadcrumb-item"><a href="' . get_post_type_archive_link($post_type) . '">' . esc_html($post_type_obj->labels->name) . '</a></li>';
                     }
                  }
               }

               if (!$hide_last || !$is_single_context) {
                  echo '<li class="breadcrumb-item active" aria-current="page">' . get_the_title() . '</li>';
               }
            }
         } elseif (is_tag()) {
            if ( $blog_page_id ) {
               echo '<li class="breadcrumb-item"><a href="' . esc_url( get_permalink( $blog_page_id ) ) . '">' . esc_html( get_the_title( $blog_page_id ) ) . '</a></li>';
            }
            echo '<li class="breadcrumb-item active" aria-current="page">' . single_tag_title('', false) . '</li>';
         } elseif (is_date()) {
            if ( $blog_page_id ) {
               echo '<li class="breadcrumb-item"><a href="' . esc_url( get_permalink( $blog_page_id ) ) . '">' . esc_html( get_the_title( $blog_page_id ) ) . '</a></li>';
            }
            echo '<li class="breadcrumb-item active" aria-current="page">' . get_the_archive_title() . '</li>';
         } elseif (is_page()) {
            $parents = [];
            $parent_id = wp_get_post_parent_id(get_the_ID());

            while ($parent_id) {
               $page = get_post($parent_id);
               $parents[] = '<li class="breadcrumb-item"><a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a></li>';
               $parent_id = $page->post_parent;
            }

            echo implode('', array_reverse($parents));
            if (!$hide_last || !$is_single_context) {
               echo '<li class="breadcrumb-item active" aria-current="page">' . get_the_title() . '</li>';
            }
         } elseif (is_search()) {
            echo '<li class="breadcrumb-item active" aria-current="page">' . sprintf(__('Search results for "%s"', 'codeweber'), get_search_query()) . '</li>';
         } elseif (is_404()) {
            echo '<li class="breadcrumb-item active" aria-current="page">' . __('Page not found', 'codeweber') . '</li>';
         } elseif ( function_exists( 'is_product_category' ) && is_product_category() ) {
            // WooCommerce product category: Главная > Каталог > [родители] > Категория
            $shop_id    = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'shop' ) : 0;
            $shop_url   = $shop_id > 0 ? get_permalink( $shop_id ) : home_url( '/' );
            $shop_title = $shop_id > 0 ? get_the_title( $shop_id ) : __( 'Shop', 'woocommerce' );
            echo '<li class="breadcrumb-item"><a href="' . esc_url( $shop_url ) . '">' . esc_html( $shop_title ) . '</a></li>';
            $current_term = get_queried_object();
            if ( $current_term ) {
               $ancestors = array_reverse( get_ancestors( $current_term->term_id, 'product_cat' ) );
               foreach ( $ancestors as $ancestor_id ) {
                  $ancestor = get_term( $ancestor_id, 'product_cat' );
                  echo '<li class="breadcrumb-item"><a href="' . esc_url( get_term_link( $ancestor ) ) . '">' . esc_html( $ancestor->name ) . '</a></li>';
               }
               echo '<li class="breadcrumb-item active" aria-current="page">' . esc_html( $current_term->name ) . '</li>';
            }
         } elseif ( function_exists( 'is_product_tag' ) && is_product_tag() ) {
            // WooCommerce product tag: Главная > Каталог > Метка
            $shop_id    = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'shop' ) : 0;
            $shop_url   = $shop_id > 0 ? get_permalink( $shop_id ) : home_url( '/' );
            $shop_title = $shop_id > 0 ? get_the_title( $shop_id ) : __( 'Shop', 'woocommerce' );
            echo '<li class="breadcrumb-item"><a href="' . esc_url( $shop_url ) . '">' . esc_html( $shop_title ) . '</a></li>';
            echo '<li class="breadcrumb-item active" aria-current="page">' . esc_html( single_term_title( '', false ) ) . '</li>';
         } elseif (is_archive()) {
            echo '<li class="breadcrumb-item active" aria-current="page">' . get_the_archive_title() . '</li>';
         }

         echo $wrap_after;
      }
   }
}

/**
 * WooCommerce My Account: исправляем крошки и заголовки.
 * - Второй пункт крошек — всегда заголовок страницы «Мой аккаунт», не эндпоинта.
 * - На view-order добавляем «Заказы» перед номером заказа: Главная → Мой аккаунт → Заказы → Заказ № XXX
 */
if (class_exists('WooCommerce')) {
   add_filter('woocommerce_get_breadcrumb', function ($crumbs, $breadcrumb) {
      if (!is_account_page() || empty($crumbs)) {
         return $crumbs;
      }
      $myaccount_page_id = wc_get_page_id('myaccount');
      $myaccount_title   = $myaccount_page_id ? get_the_title($myaccount_page_id) : _x('My account', 'breadcrumb', 'woocommerce');

      // Второй пункт (ссылка на my-account) — подменяем заголовок на заголовок страницы.
      if (isset($crumbs[1]) && is_array($crumbs[1])) {
         $crumbs[1][0] = $myaccount_title;
      }

      $endpoint = WC()->query->get_current_endpoint();
      if ($endpoint === 'view-order') {
         $orders_url   = wc_get_account_endpoint_url('orders');
         $orders_label = __('Orders', 'woocommerce');
         $orders_crumb = array($orders_label, $orders_url);
         $last         = array_pop($crumbs);
         $crumbs[]     = $orders_crumb;
         $crumbs[]     = $last;
      }
      return $crumbs;
   }, 10, 2);
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
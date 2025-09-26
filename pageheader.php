<?php
// Проверка, чтобы не выводить заголовок на главной, блоге и 404
if (!is_front_page() && !is_home() && !is_404()) {
   global $opt_name;
   $post_type = universal_get_post_type();
   $post_id = get_the_ID();

   $global_pagehaeder_type = Redux::get_option($opt_name, 'global_page_header_type');
   $global_template_pageheader = Redux::get_option($opt_name, 'global_page_header_model');
   $global_custom_template_pageheader = Redux::get_option($opt_name, 'custom_page_header');

   $page_header_for_this_page_bool = Redux::get_post_meta($opt_name, $post_id, 'this-page-header-type');
   $page_header_for_this_page = Redux::get_post_meta($opt_name, $post_id, 'this-custom-page-header');

   $single_pageheader_id = Redux::get_option($opt_name, 'single_page_header_select_' . $post_type);
   $archive_pageheader_id = Redux::get_option($opt_name, 'archive_page_header_select_' . $post_type);

   // Флаг для определения, нужно ли выводить заголовок
   $show_page_header = true;
   $template_pageheader_id = '';

   if (is_single() || is_singular($post_type)) {
      // Проверяем, не отключен ли заголовок для этого типа записи
      if ($single_pageheader_id === 'disabled') {
         $show_page_header = false;
      } elseif (!empty($page_header_for_this_page) && $page_header_for_this_page_bool == '2') {
         $template_pageheader_id = $page_header_for_this_page;
      } elseif (!empty($single_pageheader_id) && $single_pageheader_id !== 'default' && $page_header_for_this_page_bool == '1') {
         $template_pageheader_id = $single_pageheader_id;
      } elseif ($global_pagehaeder_type === '2') {
         $template_pageheader_id = $global_custom_template_pageheader;
      } else {
         $template_pageheader_id = '';
      }
   } elseif (is_archive() || is_post_type_archive($post_type)) {
      // Проверяем, не отключен ли заголовок для архива этого типа
      if ($archive_pageheader_id === 'disabled') {
         $show_page_header = false;
      } elseif (!empty($archive_pageheader_id) && $archive_pageheader_id !== 'default') {
         $template_pageheader_id = $archive_pageheader_id;
      } elseif ($global_pagehaeder_type === '2') {
         $template_pageheader_id = $global_custom_template_pageheader;
      } else {
         $template_pageheader_id = '';
      }
   }

   // Если заголовок отключен - пропускаем вывод
   if (!$show_page_header) {
      return;
   }

   // Функция подготовки всех необходимых переменных для pageheader
   if (!function_exists('get_pageheader_vars')) {
      function get_pageheader_vars()
      {
         if (!class_exists('Redux')) {
            return [];
         }
         global $opt_name;

         $breadcrumbs_color = Redux::get_option($opt_name, 'global-page-header-breadcrumb-color');
         $breadcrumbs_enable = Redux::get_option($opt_name, 'global-page-header-breadcrumb-enable');
         $breadcrumbs_bg = Redux::get_option($opt_name, 'global-page-header-breadcrumb-bg-color');
         $breadcrumbs_align = Redux::get_option($opt_name, 'global-bredcrumbs-aligns');

         if ($breadcrumbs_align === '1') {
            $breadcrumbs_align = 'left';
         } elseif ($breadcrumbs_align === '2') {
            $breadcrumbs_align = 'center';
         } elseif ($breadcrumbs_align === '3') {
            $breadcrumbs_align = 'right';
         } else {
            $breadcrumbs_align = 'left';
         }

         if ($breadcrumbs_bg) {
            $breadcrumbs_bg = ' bg-' . $breadcrumbs_bg;
         } else {
            $breadcrumbs_bg = ' bg-soft-primary';
         }

         if ($breadcrumbs_color === '1') {
            $breadcrumbs_color = 'dark';
         } elseif ($breadcrumbs_color === '2') {
            $breadcrumbs_color = 'white';
         } elseif ($breadcrumbs_color === '3') {
            $breadcrumbs_color = 'muted';
         } else {
            $breadcrumbs_color = 'muted';
         }

         $row_class = [];

         // Заголовок и стили
         $page_header_align = Redux::get_option($opt_name, 'global-page-header-aligns');
         $page_header_title_color = Redux::get_option($opt_name, 'global-page-header-title-color');
         $page_header_bg_type = Redux::get_option($opt_name, 'global-page-header-background');
         $page_header_bg_solid = Redux::get_option($opt_name, 'global-page-header-bg-solid-color');
         $page_header_bg_soft = Redux::get_option($opt_name, 'global-page-header-bg-soft-color');
         $page_header_bg_image_url = Redux::get_option($opt_name, 'global-page-header-image')['url'] ?? '';
         $page_header_pattern_image_url = Redux::get_option($opt_name, 'global-page-header-pattern')['url'] ?? '';
         $page_header_title_size = Redux::get_option($opt_name, 'opt-select-title-size') ?? '';

         $global_page_header_model = Redux::get_option($opt_name, 'global_page_header_model');
         $header_background = Redux::get_option($opt_name, 'header-background');

         // Логика классов контейнера
         $container_class = [];

         if ($global_page_header_model === '1') {
            $container_class[] = ($header_background === '3') ? 'pt-18 pt-md-18' : 'pt-10 pt-md-14';
         } elseif ($global_page_header_model === '2') {
            $container_class[] = ($header_background === '3') ? 'pt-18 pt-md-18' : 'pt-10 pt-md-14';
         } elseif ($global_page_header_model === '3') {
            $container_class[] = ($header_background === '3') ? 'pt-18 pt-md-20' : 'pt-10 pt-md-14';
         } elseif ($global_page_header_model === '4') {
            $container_class[] = ($header_background === '3') ? 'pb-12 pb-md-16 pt-20 pt-md-21' : 'py-12 py-md-16';
         } elseif ($global_page_header_model === '5') {
            $container_class[] = ($header_background === '3') ? 'pt-17 pb-10' : 'py-10';
         } elseif ($global_page_header_model === '6') {
            $container_class[] = ($header_background === '3') ? 'pt-20 pb-18 pb-md-20 pt-md-21 pb-lg-21' : 'pt-16 pb-18 pb-md-20 pt-md-16 pb-lg-21';
         } elseif ($global_page_header_model === '7') {
            $container_class[] = ($header_background === '3') ? 'pt-19 pt-md-23 pb-18 pb-md-20' : 'pt-19 pt-md-20 pb-18 pb-md-20';
         } elseif ($global_page_header_model === '8') {
            $container_class[] = ($header_background === '3') ? 'pt-19 pt-md-24 pb-18 pb-md-20' : 'pt-19 pt-md-21 pb-18 pb-md-20';
         } elseif ($global_page_header_model === '9') {
            $container_class[] = ($header_background === '3') ? 'py-14' : 'py-12';
         } else {
            $container_class[] = ($header_background === '3') ? 'pt-18 pt-md-20' : 'pt-10 pt-md-14';
         }

         // Логика классов секции
         $section_class = [];
         $data_section = [];
         if ($page_header_bg_type === '1') {
            $section_class[] = 'bg-' . $page_header_bg_solid;
         } elseif ($page_header_bg_type === '2') {
            $section_class[] = 'bg-' . $page_header_bg_soft;
         } elseif ($page_header_bg_type === '3') {
            $section_class[] = 'bg-image bg-cover bg-overlay image-wrapper';
            if ($page_header_bg_image_url) {
               $data_section[] = 'data-image-src="' . esc_url($page_header_bg_image_url) . '"';
            }
         } elseif ($page_header_bg_type === '4') {
            $section_class[] = 'pattern-wrapper bg-image';
            if ($page_header_pattern_image_url) {
               $data_section[] = 'data-image-src="' . esc_url($page_header_pattern_image_url) . '"';
            }
         }

         // Цвет заголовка
         $title_class = [];
         $subtitle_class = [];
         if ($page_header_title_color === '2') {
            $title_class[] = 'text-white';
            $title_class[] = $page_header_title_size;
            $subtitle_class[] = 'text-white';
         } elseif ($page_header_title_color === '1') {
            $title_class[] = 'text-dark';
            $title_class[] = $page_header_title_size;
            $subtitle_class[] = 'text-dark';
         } else {
            $title_class[] = 'text-dark';
            $title_class[] = $page_header_title_size;
            $subtitle_class[] = 'text-dark';
         }

         // Выравнивание и классы для колонок
         $col_class = [];
         if ($page_header_align === '1') {
            $container_class[] = '';
            $col_class[] = 'col-lg-10 col-xxl-8';
            $row_class[] = '';
         } elseif ($page_header_align === '2') {
            $container_class[] = 'text-center';
            $col_class[] = 'col-md-7 col-lg-6 col-xl-5 mx-auto';
            $row_class[] = 'd-flex justify-content-center';
         } elseif ($page_header_align === '3') {
            $container_class[] = 'text-end';
            $col_class[] = 'col-lg-10 col-xxl-8';
            $row_class[] = 'd-flex justify-content-end';
            $title_class[] = 'text-end';
         } else {
            $container_class[] = '';
            $col_class[] = 'col-lg-10 col-xxl-8';
            $row_class[] = '';
         }

         // Формируем подзаголовок HTML
         $subtitle_html = '';
         if (function_exists('the_subtitle')) {
            if ($page_header_align === '1') {
               $subtitle_html = the_subtitle('<p class="lead ' . implode(" ", $subtitle_class) . '">%s</p>');
            } elseif ($page_header_align === '2') {
               $subtitle_html = the_subtitle('<p class="lead px-lg-5 px-xxl-8  ' . implode(" ", $subtitle_class) . '">%s</p>');
            } elseif ($page_header_align === '3') {
               $subtitle_html = the_subtitle('<p class="lead ' . implode(" ", $subtitle_class) . '">%s</p>');
            }
         }

         return [
            // Хлебные крошки
            'breadcrumbs_color' => $breadcrumbs_color,
            'breadcrumbs_enable' => $breadcrumbs_enable,
            'breadcrumbs_bg' => $breadcrumbs_bg,
            'breadcrumbs_align' => $breadcrumbs_align,
            // Заголовок
            'page_header_align' => $page_header_align,
            'page_header_title_color' => $page_header_title_color,
            'page_header_bg_type' => $page_header_bg_type,
            'page_header_bg_solid' => $page_header_bg_solid,
            'page_header_bg_soft' => $page_header_bg_soft,
            'page_header_bg_image_url' => $page_header_bg_image_url,
            'page_header_pattern_image_url' => $page_header_pattern_image_url,
            'global_page_header_model' => $global_page_header_model,
            'header_background' => $header_background,
            'container_class' => $container_class,
            'section_class' => $section_class,
            'col_class' => $col_class,
            'title_class' => $title_class,
            'subtitle_class' => $subtitle_class,
            'data_section' => $data_section,
            // Подзаголовок уже готовый HTML
            'subtitle_html' => $subtitle_html,
            'row_class' => $row_class,
         ];
      }
   }

   // Получаем переменные для шаблона
   $pageheader_vars = get_pageheader_vars();

   if ($template_pageheader_id && $template_pageheader_id !== 'default') {
      $post = get_post($template_pageheader_id);
      if ($post) {
         $content = $post->post_content;
         $content = apply_filters('the_content', $content);
         $content = do_shortcode($content); // Обрабатываем шорткоды
         echo $content;
      }
   } else {
      if (!empty($global_template_pageheader)) {
         $template_part = get_theme_file_path("templates/pageheader/pageheader-{$global_template_pageheader}.php");
         if (file_exists($template_part)) {
            // Подключаем шаблон с переменными
            require $template_part;
         }
      }
   }
}

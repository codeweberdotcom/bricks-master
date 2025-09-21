<?php

/**
 * Подключает файл шаблона pageheader из каталога /templates/pageheader/ темы.
 * Если выбрана конкретная запись pageheader, выводит ее контент.
 */
function get_pageheader($name = null)
{
    do_action('get_pageheader', $name);

    $pageheader_content = null;

    // Если имя не передано — берем из Redux Framework
    if (empty($name) && class_exists('Redux')) {
        global $opt_name;

        // Определяем тип страницы и получаем соответствующую опцию
        if (is_singular()) {
            // Для одиночных записей
            $post_type = get_post_type();
            $sanitized_id = sanitize_title($post_type);
            $option_name = 'single_page_header_select_' . $sanitized_id;
        } elseif (is_archive() || is_home() || is_search()) {
            // Для архивных страниц
            if (is_post_type_archive()) {
                $post_type = get_post_type();
            } elseif (is_category() || is_tag() || is_tax()) {
                $taxonomy = get_queried_object()->taxonomy;
                $post_type = get_taxonomy($taxonomy)->object_type[0] ?? 'post';
            } else {
                $post_type = 'post';
            }
            $sanitized_id = sanitize_title($post_type);
            $option_name = 'archive_page_header_select_' . $sanitized_id;
        } else {
            // Для других страниц (главная, 404 и т.д.)
            $option_name = 'global_page_header_model';
        }

        // Получаем значение опции
        $selected_option = Redux::get_option($opt_name, $option_name);

        // Если выбрано "default" или опция не найдена, используем глобальную
        if ($selected_option === 'default' || empty($selected_option)) {
            $selected_option = Redux::get_option($opt_name, 'global_page_header_model');
        }

        // Если выбрано "disabled", возвращаем пустоту
        if ($selected_option === 'disabled') {
            return;
        }

        // Если выбрана конкретная запись (числовой ID)
        if (is_numeric($selected_option)) {
            $post_id = intval($selected_option);
            
            // Проверяем, существует ли запись и является ли она pageheader
            $post = get_post($post_id);
            if ($post && $post->post_type === 'page-header' && $post->post_status === 'publish') {
                // Выводим контент записи
                echo apply_filters('the_content', $post->post_content);
                return;
            }
        }

        // Если это не числовой ID, используем как имя шаблона
        $name = $selected_option;
    }

    // Если передано имя шаблона, ищем файл
    if (!empty($name)) {
        $template = get_theme_file_path('pageheader.php');

        if (file_exists($template)) {
            // Подготавливаем переменные, которые хотим передать
            $pageheader_vars = [
                'name' => $name,
            ];

            // Распаковываем переменные в локальную область видимости шаблона
            extract($pageheader_vars);

            // Подключаем шаблон
            require $template;
        }
    }
}


/**
 * Возвращает подзаголовок для архивных страниц в зависимости от типа записи.
 * Подзаголовок берется из настроек Redux и выводится в заданной HTML-структуре.
 *
 * @global string $opt_name Имя настроек Redux.
 * @param string $html_structure Строка с HTML-разметкой, в которую будет вставлен подзаголовок.
 * 
 * @return string HTML-структура с подзаголовком.
 */
function the_subtitle($html_structure = '<p class="lead">%s</p>')
{
   // Проверяем, что это архивная страница и не админка
   if (is_archive() && !is_admin()) {
      // Получаем тип записи для текущего архива
      $post_type = get_post_type() ?: get_query_var('post_type');

      // Если тип записи определён
      if ($post_type) {
         global $opt_name;

         // Формируем ID для поля custom subtitle в зависимости от типа записи
         $custom_subtitle_id = 'custom_subtitle_' . $post_type;

         // Получаем подзаголовок из настроек Redux
         $custom_subtitle = Redux::get_option($opt_name, $custom_subtitle_id);

         // Если подзаголовок найден, возвращаем его в указанной HTML-структуре
         if (!empty($custom_subtitle)) {
            return sprintf($html_structure, esc_html($custom_subtitle));
         }
      }
   }

   // Если подзаголовок не найден, возвращаем пустую строку в HTML-структуре
   return '';
}



/**
 * Изменяет заголовок архивной страницы для произвольных типов записей.
 * Заголовок берется из настроек Redux по ключу 'cpt-custom-title{PostType}'.
 *
 * Пример ключа: 'cpt-custom-titleFaq' для CPT с именем 'faq'.
 * Удаляет префикс "Архивы:" или "Archives:" из стандартного заголовка.
 *
 * @param string $title Стандартный заголовок архива.
 * @return string Новый заголовок архива.
 */
add_filter('get_the_archive_title', function ($title) {
   if (is_post_type_archive() && !is_admin()) {
      $post_type = get_post_type() ?: get_query_var('post_type');

      if ($post_type) {
         global $opt_name;

         $custom_title_id = 'custom_title_' . $post_type;
         $custom_title = Redux::get_option($opt_name, $custom_title_id);

         if (!empty($custom_title)) {
            return $custom_title;
         }
      }

      $title = preg_replace('/^(Архивы|Archives):\s*/u', '', $title);
   }

   return $title;
});


/**
 * Изменяет заголовок архивной страницы для произвольных типов записей.
 * Работает с функцией post_type_archive_title().
 *
 * @param string $title Стандартный заголовок архива CPT
 * @param string $post_type Тип записи
 * @return string Новый заголовок архива
 */
add_filter('post_type_archive_title', function ($title, $post_type) {
    if (!is_admin()) {
        global $opt_name;

        $custom_title_id = 'custom_title_' . $post_type;
        $custom_title = Redux::get_option($opt_name, $custom_title_id);

        if (!empty($custom_title)) {
            return $custom_title;
        }
    }

    return $title;
}, 10, 2);
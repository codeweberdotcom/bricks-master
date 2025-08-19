<?php


/**
 * Подключает файл шаблона pageheader из каталога /templates/pageheader/ темы.
 *
 * Работает аналогично get_header(), но подключает:
 * - templates/pageheader/pageheader-{name}.php
 * - или templates/pageheader/pageheader.php
 *
 * Шорткод [pageheader name="название"] подключает шаблон pageheader.
 *
 * Пример использования: [pageheader name="main"]
 * @param string|null $name Имя подшаблона (опционально).
 */
function get_pageheader($name = null)
{
   do_action('get_pageheader', $name);

   // Если имя не передано — берем из Redux Framework
   if (empty($name) && class_exists('Redux')) {
      global $opt_name;
      $name = Redux::get_option($opt_name, 'global_page_header_model');
   }

   // Путь к шаблону в корне темы
   $template = get_theme_file_path('pageheader.php');

   if (file_exists($template)) {

      // Подготавливаем переменные, которые хотим передать
      $pageheader_vars = [
         'name' => $name,
         // Здесь можно добавить любые другие переменные,
         // например, из Redux
      ];

      // Распаковываем переменные в локальную область видимости шаблона
      extract($pageheader_vars);

      // Подключаем шаблон
      require $template;
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

<?php global $opt_name;
$global_page_header_model = Redux::get_option($opt_name, 'global-page-header-model');

// Проверка на главную (фронтальную) и 404 страницу, чтобы не выводить заголовок
if (!is_front_page() && !is_home() && !is_404()) {
   if ($global_page_header_model === '1') {
      get_template_part('templates/pageheader/pageheader', '1');
   } elseif ($global_page_header_model === '2') {
      get_template_part('templates/pageheader/pageheader', '2');
   } elseif ($global_page_header_model === '3') {
      get_template_part('templates/pageheader/pageheader', '3');
   } elseif ($global_page_header_model === '4') {
      get_template_part('templates/pageheader/pageheader', '4');
   } elseif ($global_page_header_model === '5') {
      get_template_part('templates/pageheader/pageheader', '5');
   } elseif ($global_page_header_model === '6') {
      get_template_part('templates/pageheader/pageheader', '6');
   } elseif ($global_page_header_model === '7') {
      get_template_part('templates/pageheader/pageheader', '7');
   }
}
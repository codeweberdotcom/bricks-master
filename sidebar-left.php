<?php
global $opt_name;

$post_type = get_post_type();

// Определяем, что за страница — архив или запись
if (is_singular($post_type)) {
   $sidebar_position = Redux::get_option($opt_name, 'sidebar_position_single_' . $post_type);
} else {
   $sidebar_position = Redux::get_option($opt_name, 'sidebar_position_archive_' . $post_type);
}


// Левый сайдбар
if ($sidebar_position === 'left' && is_active_sidebar($post_type)) {
   dynamic_sidebar($post_type);
} else {
   if ($sidebar_position === 'left' && !is_active_sidebar($post_type)) {
   do_action('codeweber_after_widget', $post_type);
   }
}
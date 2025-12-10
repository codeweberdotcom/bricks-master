<?php
global $opt_name;

$post_type = universal_get_post_type();
$post_type_lc = strtolower($post_type);

$sidebar_position = get_sidebar_position($opt_name);

// Правый сайдбар
if ($sidebar_position === 'right') {
   if ($post_type === 'post') {
      // Используем стандартный сайдбар WordPress с нужными классами
?>
      <aside class="col-xl-3 sidebar sticky-sidebar mt-md-0 py-14 d-none d-xl-block">
         <?php
         do_action('codeweber_before_sidebar', 'sidebar-1');
         get_sidebar();
         do_action('codeweber_after_sidebar', 'sidebar-1');
         ?>
      </aside>
      <?php
   } else {
      // Используем кастомный сайдбар для других типов записей
      if (is_active_sidebar($post_type)) { ?>
         <aside class="col-xl-3 sidebar sticky-sidebar mt-md-0 py-14 d-none d-xl-block">
            <?php
            do_action('codeweber_before_sidebar', $post_type);
            dynamic_sidebar($post_type);
            do_action('codeweber_after_sidebar', $post_type);
            ?>
         </aside>
      <?php
      } else { ?>
         <aside class="col-xl-3 sidebar sticky-sidebar mt-md-0 py-14 d-none d-xl-block">
            <?php do_action('codeweber_after_widget', $post_type); ?>
         </aside>
<?php
      }
   }
}

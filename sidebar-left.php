<?php
global $opt_name;

$post_type = universal_get_post_type();
$post_type_lc = strtolower($post_type);

$sidebar_position = get_sidebar_position($opt_name);

// Левый сайдбар
if ($sidebar_position === 'left') {
   $bp = get_sidebar_breakpoint($opt_name);
   if ($post_type === 'post') {
      // Используем стандартный сайдбар WordPress с нужными классами
?>
      <aside class="col-<?php echo esc_attr( $bp ); ?>-4 sidebar sticky-sidebar mt-md-0 py-14 d-none d-<?php echo esc_attr( $bp ); ?>-block">
         <?php
         do_action('codeweber_before_sidebar', 'sidebar-1');
         get_sidebar();
         do_action('codeweber_after_sidebar', 'sidebar-1');
         ?>
      </aside>
      <?php
   } else {
      // Используем кастомный сайдбар для других типов записей
?>
         <aside class="col-<?php echo esc_attr( $bp ); ?>-4 sidebar sticky-sidebar mt-md-0 py-14 d-none d-<?php echo esc_attr( $bp ); ?>-block">
            <?php
            do_action('codeweber_before_sidebar', $post_type);
            if (is_active_sidebar($post_type)) {
               dynamic_sidebar($post_type);
            }
            do_action('codeweber_after_sidebar', $post_type);
            ?>
         </aside>
<?php
   }
}

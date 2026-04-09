<?php
global $opt_name;

$post_type = universal_get_post_type();
$post_type_lc = strtolower($post_type);

$sidebar_position = get_sidebar_position($opt_name);

// Левый сайдбар
if ($sidebar_position === 'left') {
   $bp      = get_sidebar_breakpoint($opt_name);
   $padding = get_content_padding_classes();
   $aside_class = $bp === 'always'
      ? 'col-12 col-md-4 sidebar mt-md-0 ' . $padding
      : 'col-12 col-' . $bp . '-4 sidebar sticky-sidebar mt-md-0 ' . $padding . ' d-none d-' . $bp . '-block';
   if ($post_type === 'post') {
      // Используем стандартный сайдбар WordPress с нужными классами
?>
      <aside class="<?php echo esc_attr( $aside_class ); ?>">
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
         <aside class="<?php echo esc_attr( $aside_class ); ?>">
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

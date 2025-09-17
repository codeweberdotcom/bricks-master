<?php
// $pageheader_vars доступен здесь
$enable = $pageheader_vars['breadcrumbs_enable'] ?? false;
$color = $pageheader_vars['breadcrumbs_color'] ?? 'muted';
$bg = $pageheader_vars['breadcrumbs_bg'] ?? 'bg-soft-primary';
$align = $pageheader_vars['breadcrumbs_align'] ?? 'left';

if ($enable):
?>

   <section class="wrapper <?= esc_attr($bg) ?>">
      <div class="container py-4">
         <div class="row">
            <?php get_breadcrumbs($align, $color, 'mb-0'); ?>
         </div>
      </div>
   </section>
<?php endif; ?>
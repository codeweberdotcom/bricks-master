<?php
global $opt_name;
$breadcrumbs_color = Redux::get_option($opt_name, 'global-page-header-breadcrumb-color');
$breadcrumbs_enable  =  Redux::get_option($opt_name, 'global-page-header-breadcrumb-enable');
$breadcrumbs_bg  =  Redux::get_option($opt_name, 'global-page-header-breadcrumb-bg-color');
$breadcrumbs_align =  Redux::get_option($opt_name, 'global-bredcrumbs-aligns');

if ($breadcrumbs_align === '1') {
   $breadcrumbs_align = 'left';
} elseif ($breadcrumbs_align === '2') {
   $breadcrumbs_align = 'center';
} elseif ($breadcrumbs_align === '3') {
   $breadcrumbs_align = 'right';
}

if ($breadcrumbs_bg) {
   $breadcrumbs_bg = ' bg-' . $breadcrumbs_bg;
} else {
   $breadcrumbs_bg = 'bg-soft-primary';
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
?>

<?php if ($breadcrumbs_enable) { ?>
   <section class="wrapper<?= $breadcrumbs_bg; ?> pageheader-1">
      <div class="container py-4">
         <div class="row">
            <?php get_breadcrumbs($breadcrumbs_align, $breadcrumbs_color, 'mb-0'); ?>
         </div>
         <!-- /.row -->
      </div>
      <!-- /.container -->
   </section>
<?php }; ?>
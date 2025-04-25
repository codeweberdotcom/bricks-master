<?php
global $opt_name;
$breadcrumbs_color = Redux::get_option($opt_name, 'global-page-header-breadcrumb-color');
$breadcrumbs_enable  =  Redux::get_option($opt_name, 'global-page-header-breadcrumb-enable');
$breadcrumbs_bg  =  Redux::get_option($opt_name, 'global-page-header-breadcrumb-bg-color');
$bredcrumbs_align =  Redux::get_option($opt_name, 'global-bredcrumbs-aligns');

if ($bredcrumbs_align === '1') {
   $breadcrumbs_align = 'left';
} elseif ($bredcrumbs_align === '2') {
   $breadcrumbs_align = 'center';
} elseif ($bredcrumbs_align === '3') {
   $breadcrumbs_align = 'right';
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
?>

<?php
$page_header_align = Redux::get_option($opt_name, 'global-page-header-aligns');
$page_header_title_color = Redux::get_option($opt_name, 'global-page-header-title-color');
$page_header_bg_type = Redux::get_option($opt_name, 'global-page-header-background');
$page_header_bg_solid = Redux::get_option($opt_name, 'global-page-header-bg-solid-color');
$page_header_bg_soft = Redux::get_option($opt_name, 'global-page-header-bg-soft-color');
$page_header_bg_image_url = Redux::get_option($opt_name, 'global-page-header-image')['url'];
$page_header_pattern_image_url = Redux::get_option($opt_name, 'global-page-header-pattern')['url'];

$global_header_model = Redux::get_option($opt_name, 'global-header-model');
$header_background = Redux::get_option($opt_name, 'header-background');

$container_class = array();
$section_class = array();
$col_class = array();
$title_class = array();
$subtitle_class = array();
$data_section = array();


if ($global_header_model === '7' || $global_header_model === '8') {
   if ($header_background === '3') {
      $container_class[] = 'pt-19 pt-md-24 pb-18 pb-md-20';
   } else {
      $container_class[] = 'pt-19 pt-md-21 pb-18 pb-md-20';
   }
} elseif ($global_header_model === '1' || $global_header_model === '2' ||  $global_header_model === '3') {
   if ($header_background === '3') {
      $container_class[] = 'pt-19 pt-md-21 pb-18 pb-md-20';
   } else {
      $container_class[] = 'pt-19 pt-md-18 pb-18 pb-md-20';
   }
} elseif ($global_header_model === '4' || $global_header_model === '5' ||  $global_header_model === '6') {
   if ($header_background === '3') {
      $container_class[] = 'pt-19 pt-md-22 pb-18 pb-md-20';
   } else {
      $container_class[] = 'pt-19 pt-md-20 pb-18 pb-md-20';
   }
} else {
   if ($header_background === '3') {
      $container_class[] = 'pt-19 pt-md-21 pb-18 pb-md-20';
   } else {
      $container_class[] = 'pt-19 pt-md-21 pb-18 pb-md-20';
   }
}

if ($page_header_bg_type === '1') {
   $section_class[] = 'bg-' . $page_header_bg_solid;
} elseif ($page_header_bg_type === '2') {
   $section_class[] = 'bg-' . $page_header_bg_soft;
} elseif ($page_header_bg_type === '3') {
   $section_class[] = 'bg-image bg-cover bg-overlay image-wrapper';
   $data_section[] = 'data-image-src="' . $page_header_bg_image_url . '"';
} elseif ($page_header_bg_type === '4') {
   $section_class[] = 'pattern-wrapper bg-image';
   $data_section[] = 'data-image-src="' . $page_header_pattern_image_url . '"';
} elseif ($page_header_bg_type === '5') {
}

if ($page_header_title_color === '2') {
   $title_class[] = ' text-white';
   $subtitle_class[] = ' text-white';
} elseif ($page_header_title_color === '1') {
   $title_class[] = ' text-dark';
   $subtitle_class[] = ' text-dark';
}

if ($page_header_align === '1') {
   $container_class[] = 'text-start';
   $col_class[] = 'col-lg-10 col-xxl-8';
   $subtitle = the_subtitle('<p class="lead col-lg-10 col-xxl-8 mb-0' . implode(" ", $title_class) . '">%s</p>');
} elseif ($page_header_align === '2') {
   $container_class[] = 'text-center';
   $col_class[] = 'col-md-7 col-lg-6 col-xl-5 mx-auto';
   $subtitle = the_subtitle('<p class="lead px-lg-5 px-xxl-8 mb-0' . implode(" ", $title_class) . '">%s</p>');
} elseif ($page_header_align === '3') {
   $container_class[] = 'text-right';
   $col_class[] = '';
   $title_class[] = 'text-end';
   $subtitle = the_subtitle('<p class="lead col-lg-10 col-xxl-8 mb-0' . implode(" ", $title_class) . '">%s</p>');
}
?>




<section class="wrapper <?= implode(" ", $section_class); ?>" <?= implode(" ", $data_section); ?>>
   <div class="container <?= implode(" ", $container_class); ?>">
      <div class="row">
         <div class="<?= implode(" ", $col_class); ?>">
            <h1 class="display-1 <?= implode(" ", $title_class); ?>"><?= universal_title(); ?></h1>
            <?= $subtitle; ?>
            <?php get_breadcrumbs($breadcrumbs_align, $breadcrumbs_color, 'mb-0'); ?>
         </div>
         <!-- /column -->
      </div>
      <!-- /.row -->
   </div>
   <!-- /.container -->
</section>
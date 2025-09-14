<?php
// Безопасное извлечение переменных из $pageheader_vars с дефолтами
$breadcrumbs_enable = $pageheader_vars['breadcrumbs_enable'] ?? false;
$breadcrumbs_color  = $pageheader_vars['breadcrumbs_color'] ?? 'muted';
$breadcrumbs_bg     = $pageheader_vars['breadcrumbs_bg'] ?? ' bg-soft-primary';
$breadcrumbs_align  = $pageheader_vars['breadcrumbs_align'] ?? 'left';

// Заголовок и стили
$container_class = $pageheader_vars['container_class'] ?? [];
$section_class   = $pageheader_vars['section_class'] ?? [];
$col_class       = $pageheader_vars['col_class'] ?? [];
$title_class     = $pageheader_vars['title_class'] ?? [];
$data_section    = $pageheader_vars['data_section'] ?? [];
$row_class       = $pageheader_vars['row_class'] ?? [];

// Подзаголовок уже готовый HTML из $pageheader_vars
$subtitle_html   = $pageheader_vars['subtitle_html'] ?? '';

// Готовим строки классов
$section_class_str   = esc_attr(implode(' ', $section_class));
$container_class_str = esc_attr(implode(' ', $container_class));
$col_class_str       = esc_attr(implode(' ', $col_class));
$title_class_str     = esc_attr(implode(' ', $title_class));
$data_section_str    = implode(' ', $data_section);
$row_class_str       = esc_attr(implode(' ', $row_class));


// Хлебные крошки
if ($breadcrumbs_enable) { ?>
   <section class="wrapper<?= esc_attr($breadcrumbs_bg); ?>">
      <div class="container py-4">
         <div class="row">
            <?php get_breadcrumbs($breadcrumbs_align, $breadcrumbs_color, 'mb-0'); ?>
         </div>
      </div>
   </section>
<?php } ?>

<section class="wrapper <?= $section_class_str; ?>" <?= $data_section_str; ?>>
   <div class="container <?= $container_class_str; ?>">
      <div class="row <?= $row_class_str; ?>">
         <div class="<?= $col_class_str; ?>">
            <h1 class="mb-2 <?= $title_class_str; ?>"><?= esc_html(universal_title(false, false)); ?></h1>
            <?= $subtitle_html; ?>
         </div>
      </div>
   </div>
</section>
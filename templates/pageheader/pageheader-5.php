<?php
// Проверяем, что переменная есть
if (!empty($pageheader_vars) && is_array($pageheader_vars)) {
   // Извлекаем переменные с дефолтами
   $breadcrumbs_enable  = $pageheader_vars['breadcrumbs_enable'] ?? false;
   $breadcrumbs_color   = $pageheader_vars['breadcrumbs_color'] ?? 'muted';
   $breadcrumbs_bg      = $pageheader_vars['breadcrumbs_bg'] ?? ' bg-soft-primary';
   $breadcrumbs_align   = $pageheader_vars['breadcrumbs_align'] ?? 'left';

   $page_header_align       = $pageheader_vars['page_header_align'] ?? '1';
   $page_header_title_color = $pageheader_vars['page_header_title_color'] ?? '1';
   $page_header_bg_type     = $pageheader_vars['page_header_bg_type'] ?? '1';
   $page_header_bg_solid    = $pageheader_vars['page_header_bg_solid'] ?? '';
   $page_header_bg_soft     = $pageheader_vars['page_header_bg_soft'] ?? '';
   $page_header_bg_image_url = $pageheader_vars['page_header_bg_image_url'] ?? '';
   $page_header_pattern_url = $pageheader_vars['page_header_pattern_url'] ?? '';

   $global_header_model     = $pageheader_vars['global_header_model'] ?? '';
   $header_background       = $pageheader_vars['header_background'] ?? '';

   $container_class = $pageheader_vars['container_class'] ?? [];
   $section_class   = $pageheader_vars['section_class'] ?? [];
   $col_class       = $pageheader_vars['col_class'] ?? [];
   $title_class     = $pageheader_vars['title_class'] ?? [];
   $subtitle_class  = $pageheader_vars['subtitle_class'] ?? [];
   $data_section    = $pageheader_vars['data_section'] ?? [];
   $subtitle_html   = $pageheader_vars['subtitle_html'] ?? '';
   $row_class       = $pageheader_vars['row_class'] ?? [];

   // Преобразуем массивы в строки с esc_attr
   $section_class_str   = esc_attr(implode(' ', (array) $section_class));
   $container_class_str = esc_attr(implode(' ', (array) $container_class));
   $col_class_str       = esc_attr(implode(' ', (array) $col_class));
   $title_class_str     = esc_attr(implode(' ', (array) $title_class));
   $row_class_str       = esc_attr(implode(' ', (array) $row_class));

   // Формируем data-атрибуты из массива
   $data_attrs = '';
   if (!empty($data_section) && is_array($data_section)) {
      $is_assoc = array_values($data_section) !== $data_section;
      if ($is_assoc) {
         foreach ($data_section as $attr => $val) {
            $data_attrs .= ' ' . esc_attr($attr) . '="' . esc_attr($val) . '"';
         }
      } else {
         foreach ($data_section as $item) {
            if (!is_string($item)) continue;
            if (preg_match('/^([\w:-]+)\s*=\s*"(.*)"$/u', $item, $m)) {
               $data_attrs .= ' ' . esc_attr($m[1]) . '="' . esc_attr($m[2]) . '"';
            } else {
               $data_attrs .= ' ' . esc_attr($item);
            }
         }
      }
   }
?>
   <section class="wrapper pageheader-5 <?= $section_class_str; ?>" <?= $data_attrs; ?>>
      <div class="container <?= $container_class_str; ?>">
         <div class="row <?= $row_class_str; ?>">
            <div class="<?= $col_class_str; ?>">
               <h1 class="mb-2 <?= $title_class_str; ?>"><?= esc_html(universal_title()); ?></h1>
               <?= $subtitle_html; ?>
               <?php if ($breadcrumbs_enable): ?>
                  <?php get_breadcrumbs($breadcrumbs_align, $breadcrumbs_color, 'mb-0'); ?>
               <?php endif; ?>
            </div>
         </div>
      </div>
   </section>
<?php
} else {
   // Если переменные не переданы — ничего не выводим
}

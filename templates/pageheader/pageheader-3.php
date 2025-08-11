<?php
// Проверяем, что переменная есть
if (!empty($pageheader_vars) && is_array($pageheader_vars)) {
   // Безопасное извлечение переменных из $pageheader_vars с дефолтами
   $breadcrumbs_enable = $pageheader_vars['breadcrumbs_enable'] ?? false;
   $breadcrumbs_color  = $pageheader_vars['breadcrumbs_color'] ?? 'muted';
   $breadcrumbs_bg     = $pageheader_vars['breadcrumbs_bg'] ?? ' bg-soft-primary';
   $breadcrumbs_align  = $pageheader_vars['breadcrumbs_align'] ?? 'left';

   $section_class   = $pageheader_vars['section_class'] ?? [];
   $container_class = $pageheader_vars['container_class'] ?? [];
   $col_class       = $pageheader_vars['col_class'] ?? [];
   $title_class     = $pageheader_vars['title_class'] ?? [];
   $data_section_raw = $pageheader_vars['data_section'] ?? [];
   $subtitle_html   = $pageheader_vars['subtitle_html'] ?? '';

   $row_class       = $pageheader_vars['row_class'] ?? [];

   // Преобразуем массивы классов в строки
   $section_class_str   = esc_attr(implode(' ', (array) $section_class));
   $container_class_str = esc_attr(implode(' ', (array) $container_class));
   $col_class_str       = esc_attr(implode(' ', (array) $col_class));
   $title_class_str     = esc_attr(implode(' ', (array) $title_class));
   $row_class_str       = esc_attr(implode(' ', (array) $row_class));

   // Собираем data-* атрибуты (поддерживаем два формата: ассоц. массив или строки 'data-foo="..."')
   $data_attrs = '';
   if (!empty($data_section_raw) && is_array($data_section_raw)) {
      // определяем — ассоциативный массив или числовой список
      $is_assoc = array_values($data_section_raw) !== $data_section_raw;
      if ($is_assoc) {
         foreach ($data_section_raw as $attr => $val) {
            // ключ — имя атрибута, значение — его значение
            $data_attrs .= ' ' . esc_attr($attr) . '="' . esc_attr($val) . '"';
         }
      } else {
         foreach ($data_section_raw as $item) {
            if (!is_string($item)) continue;
            // формат: key="value"
            if (preg_match('/^([\w:-]+)\s*=\s*"(.*)"$/u', $item, $m)) {
               $data_attrs .= ' ' . esc_attr($m[1]) . '="' . esc_attr($m[2]) . '"';
            } else {
               // если просто имя атрибута — экранируем и выводим как есть
               $data_attrs .= ' ' . esc_attr($item);
            }
         }
      }
   }
?>
   <section class="wrapper <?= $section_class_str; ?>" <?= $data_attrs; ?>>
      <div class="container <?= $container_class_str; ?>">
         <div class="row <?= $row_class_str; ?>">
            <div class="<?= $col_class_str; ?>">
               <h1 class="display-1 mb-3 <?= $title_class_str; ?>"><?= esc_html(universal_title()); ?></h1>
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
   // на случай отсутствия данных — можно ничего не выводить или дефолт
   // echo '<!-- Pageheader vars empty -->';
}

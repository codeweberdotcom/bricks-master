<?php

/**
 * Добавляет новую колонку "Image Sizes" в таблицу медиафайлов в админке WordPress.
 * Эта колонка выводит список всех размеров изображения (thumbnail, medium, large и т.д.),
 * которые были созданы для каждого вложения (attachment).
 *
 * Фильтр `manage_upload_columns` расширяет набор колонок в медиабиблиотеке.
 *
 * Хук `manage_media_custom_column` выводит содержимое для колонки "Image Sizes",
 * получая метаданные вложения и отображая размеры с их шириной и высотой.
 *
 * Если у вложения нет дополнительных размеров, выводится текст "No sizes".
 *
 * Размеры выводятся с переносом строк (тег <br>) для удобного чтения.
 *
 * @param array $columns Массив колонок таблицы медиафайлов.
 * @return array Массив колонок с добавленной "Image Sizes".
 */
add_filter('manage_upload_columns', function ($columns) {
   $columns['image_sizes'] = __('Image Sizes', 'codeweber');
   return $columns;
});

/**
 * Заполняет колонку "Image Sizes" в таблице медиафайлов.
 *
 * @param string $column_name Имя текущей колонки.
 * @param int    $post_id     ID вложения (attachment).
 */
add_action('manage_media_custom_column', function ($column_name, $post_id) {
   if ($column_name === 'image_sizes') {
      $meta = wp_get_attachment_metadata($post_id);
      if (!$meta || empty($meta['sizes'])) {
         echo __('No sizes', 'codeweber');
         return;
      }

      $sizes = $meta['sizes'];
      $output = [];

      foreach ($sizes as $size_name => $size_info) {
         if (isset($size_info['width'], $size_info['height'])) {
            $output[] = sprintf(
               '%s (%d×%d)',
               esc_html($size_name),
               intval($size_info['width']),
               intval($size_info['height'])
            );
         } else {
            $output[] = esc_html($size_name);
         }
      }

      echo implode('<br>', $output);
   }
}, 10, 2);

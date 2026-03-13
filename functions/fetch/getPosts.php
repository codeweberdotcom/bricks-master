<?php

namespace Codeweber\Functions\Fetch;

function getPosts($params)
{
   $allowed_types = get_post_types(['public' => true]);
   $type = sanitize_key($params['type'] ?? 'post');
   if (!in_array($type, $allowed_types, true)) {
      $type = 'post';
   }

   $perpage = absint($params['perpage'] ?? 5);
   if ($perpage < 1 || $perpage > 100) {
      $perpage = 5;
   }

   $query = new \WP_Query([
      'post_type'      => $type,
      'posts_per_page' => $perpage,
      'post_status'    => 'publish',
   ]);

   if ($query->have_posts()) {
      ob_start(); // включаем буферизацию вывода
      while ($query->have_posts()) {
         $query->the_post();
         get_template_part('templates/content/single'); // подключаем шаблон
      }
      wp_reset_postdata();
      $html = ob_get_clean(); // получаем HTML из буфера

      return [
         'status' => 'success',
         'data' => $html,
      ];
   }

   return [
      'status' => 'error',
      'message' => 'Посты не найдены.',
   ];
}

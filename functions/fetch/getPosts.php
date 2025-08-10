<?php

namespace Codeweber\Functions\Fetch;

function getPosts($params)
{
   $type = $params['type'] ?? 'post';
   $perpage = $params['perpage'] ?? 5;

   $query = new \WP_Query([
      'post_type' => $type,
      'posts_per_page' => $perpage,
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

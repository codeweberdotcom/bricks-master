<?php

/**
 * WordPress Bootstrap Pagination
 * https://github.com/talentedaamer/Bootstrap-wordpress-pagination/blob/master/wp_bootstrap_pagination.php
 */

function codeweber_pagination($args = array())
{

   $defaults = array(
      'range'           => 4,
      'custom_query'    => FALSE,
      'previous_string' => '<span aria-hidden="true"><i class="uil uil-arrow-left"></i></span>',
      'next_string'     => '<span aria-hidden="true"><i class="uil uil-arrow-right"></i></span>',
      'before_output'   => '<nav class="d-flex justify-content-center" aria-label="pagination"><ul class="pagination">',
      'after_output'    => '</ul></nav>'
   );

   $args = wp_parse_args(
      $args,
      apply_filters('wp_bootstrap_pagination_defaults', $defaults)
   );

   $args['range'] = (int) $args['range'] - 1;
   if (!$args['custom_query'])
      $args['custom_query'] = @$GLOBALS['wp_query'];
   $count = (int) $args['custom_query']->max_num_pages;
   $page  = intval(get_query_var('paged'));
   $ceil  = ceil($args['range'] / 2);

   if ($count <= 1)
      return FALSE;

   if (!$page)
      $page = 1;

   if ($count > $args['range']) {
      if ($page <= $args['range']) {
         $min = 1;
         $max = $args['range'] + 1;
      } elseif ($page >= ($count - $ceil)) {
         $min = $count - $args['range'];
         $max = $count;
      } elseif ($page >= $args['range'] && $page < ($count - $ceil)) {
         $min = $page - $ceil;
         $max = $page + $ceil;
      }
   } else {
      $min = 1;
      $max = $count;
   }

   $echo = '';
   $previous = intval($page) - 1;
   $previous = esc_attr(get_pagenum_link($previous));



   if ($previous && (1 != $page))
      $echo .= '<li class="page-item"><a href="' . $previous . '" class="page-link" aria-label="Previous">' . $args['previous_string'] . '</a></li>';

   if (!empty($min) && !empty($max)) {
      for ($i = $min; $i <= $max; $i++) {
         if ($page == $i) {
            $echo .= '<li class="page-item disabled"><a href="#" class="page-link ">' . str_pad((int)$i, 2, '0', STR_PAD_LEFT) . '</a></li>';
         } else {
            $echo .= sprintf('<li class="page-item "><a class="page-link" href="%s">%002d</a></li>', esc_attr(get_pagenum_link($i)), $i);
         }
      }
   }

   $next = intval($page) + 1;
   $next = esc_attr(get_pagenum_link($next));
   if ($next && ($count != $page))
      $echo .= '<li class="page-item"><a class="page-link" href="' . $next . '"  aria-label="Next">' . $args['next_string'] . '</a></li>';



   if (isset($echo))
      echo $args['before_output'] . $echo . $args['after_output'];
}

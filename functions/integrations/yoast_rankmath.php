<?php

/**
 * Отображает хлебные крошки с использованием Yoast SEO или Rank Math.
 *
 * Функция проверяет, установлен ли плагин Yoast SEO или Rank Math, и отображает соответствующие хлебные крошки.
 * Если установлен Yoast SEO, используются его стандартные функции для вывода навигации.
 * Если установлен Rank Math, применяется фильтр для настройки отображения хлебных крошек.
 */
// Fix "Undefined array key width/height" warning when SVG is used as RankMath logo.
add_filter( 'wp_get_attachment_metadata', function ( $metadata, $attachment_id ) {
	if ( empty( $metadata['width'] ) || empty( $metadata['height'] ) ) {
		$file = get_attached_file( $attachment_id );
		if ( $file && preg_match( '/\.svg$/i', $file ) ) {
			$metadata['width']  = $metadata['width']  ?? 0;
			$metadata['height'] = $metadata['height'] ?? 0;
		}
	}
	return $metadata;
}, 10, 2 );

function codeweber_breadcrumbs()
{

   if (function_exists('yoast_breadcrumb')) {
      // <!-- Yoast Breadcrumbs -->
      yoast_breadcrumb('<nav class="breadcrumb mt-3">', '</nav>');
   } elseif (function_exists('rank_math_the_breadcrumbs')) {
      // <!-- Rank Math Breadcrumbs -->
      add_filter(
         'rank_math/frontend/breadcrumb/args',
         function ($args) {
            $args = array(
               'delimiter'   => '&nbsp;&#47;&nbsp;',
               'wrap_before' => '<nav class="breadcrumb mt-3"><span>',
               'wrap_after'  => '</span></nav>',
               'before'      => '',
               'after'       => '',
            );
            return $args;
         }
      );

      rank_math_the_breadcrumbs();
   }
}
<?php
/**
 * Body Background — управление фоном страницы.
 *
 * Приоритет применения:
 *   1. Per-post Redux-метабокс `page-body-bg` (поле в "This Post Settings")
 *   2. Redux `body_bg_single_{post_type}` / `body_bg_archive_{post_type}`
 *   3. Default (прозрачный, без класса)
 *
 * Подход: добавляет класс `cw-page-bg-{value}` на <body>.
 * SCSS (.cw-page-bg-* .content-wrapper) применяет фон.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Определяет нужный класс фона для текущей страницы.
 * Возвращает строку (без 'default') или '' если фон не задан.
 */
function cw_get_body_bg(): string {

	if ( is_singular() || is_page() ) {
		$post_id   = get_queried_object_id();
		$post_type = get_post_type( $post_id );

		// 1. Per-post Redux-метабокс
		$meta = Codeweber_Options::get_post_meta( $post_id, 'page-body-bg' );
		if ( $meta && $meta !== 'default' ) {
			return sanitize_key( $meta );
		}

		// 2. Redux global for this post type
		$redux_key = 'body_bg_single_' . sanitize_key( $post_type );
		$redux_val = Codeweber_Options::get( $redux_key );
		if ( $redux_val && $redux_val !== 'default' ) {
			return sanitize_key( $redux_val );
		}

		return '';
	}

	if ( is_post_type_archive() || is_tax() ) {
		$post_type = get_query_var( 'post_type' );

		if ( is_tax() ) {
			$tax_obj = get_queried_object();
			if ( $tax_obj ) {
				$tax_info = get_taxonomy( $tax_obj->taxonomy );
				if ( $tax_info && ! empty( $tax_info->object_type ) ) {
					$post_type = $tax_info->object_type[0];
				}
			}
		}

		$redux_key = 'body_bg_archive_' . sanitize_key( $post_type );
		$redux_val = Codeweber_Options::get( $redux_key );
		if ( $redux_val && $redux_val !== 'default' ) {
			return sanitize_key( $redux_val );
		}

		return '';
	}

	if ( is_home() || is_archive() ) {
		$redux_val = Codeweber_Options::get( 'body_bg_archive_post' );
		if ( $redux_val && $redux_val !== 'default' ) {
			return sanitize_key( $redux_val );
		}
	}

	return '';
}

/**
 * Добавляет класс cw-page-bg-{value} на <body>.
 */
add_filter( 'body_class', function ( array $classes ): array {
	$bg = cw_get_body_bg();
	if ( $bg ) {
		$classes[] = 'cw-page-bg-' . $bg;
	}
	return $classes;
} );

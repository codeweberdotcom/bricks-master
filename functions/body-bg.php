<?php
/**
 * Body Background — управление фоном страницы.
 *
 * Приоритет (color):
 *   1. Per-post Redux-метабокс `page-body-bg`
 *   2. Redux `body_bg_single_{post_type}` / `body_bg_archive_{post_type}`
 *   3. Redux `body_bg_global_color`
 *
 * Приоритет (image/pattern):
 *   1. Per-post Redux-метабокс `page-body-bg-image`
 *   2. Redux `body_bg_image_single_{post_type}` / `body_bg_image_archive_{post_type}`
 *   3. Redux `body_bg_global_image`
 *
 * Color: класс `cw-page-bg-{value}` на <body> → SCSS задаёт фон .content-wrapper.
 * Image: классы `bg-image image-wrapper` / `pattern-wrapper` + `data-image-src`
 *        на <main class="content-wrapper"> → JS theme.backgroundImage() применяет фон.
 */

defined( 'ABSPATH' ) || exit;

// ── Helpers ───────────────────────────────────────────────────────────────────

/**
 * Определяет контекстный префикс Redux-ключа (single_post / archive_projects и т.п.)
 */
function cw_body_bg_context_prefix(): string {
	if ( is_singular() || is_page() ) {
		$post_id   = get_queried_object_id();
		$post_type = get_post_type( $post_id );
		return 'single_' . sanitize_key( $post_type );
	}

	if ( is_post_type_archive() ) {
		return 'archive_' . sanitize_key( get_query_var( 'post_type' ) );
	}

	if ( is_tax() ) {
		$tax_obj = get_queried_object();
		if ( $tax_obj ) {
			$tax_info = get_taxonomy( $tax_obj->taxonomy );
			if ( $tax_info && ! empty( $tax_info->object_type ) ) {
				return 'archive_' . sanitize_key( $tax_info->object_type[0] );
			}
		}
	}

	if ( is_home() || is_archive() ) {
		return 'archive_post';
	}

	return '';
}

// ── Color ─────────────────────────────────────────────────────────────────────

/**
 * Возвращает CSS-класс цвета фона (без префикса 'cw-page-bg-') или ''.
 */
function cw_get_body_bg(): string {
	if ( ! class_exists( 'Codeweber_Options' ) || ! Codeweber_Options::is_ready() ) {
		return '';
	}

	$prefix = cw_body_bg_context_prefix();

	// 1. Per-post метабокс
	if ( is_singular() || is_page() ) {
		$meta = Codeweber_Options::get_post_meta( get_queried_object_id(), 'page-body-bg' );
		if ( $meta && $meta !== 'default' ) {
			return sanitize_key( $meta );
		}
	}

	// 2. Per-CPT Redux
	if ( $prefix ) {
		$val = Codeweber_Options::get( 'body_bg_' . $prefix );
		if ( $val && $val !== 'default' ) {
			return sanitize_key( $val );
		}
	}

	// 3. Глобальный Redux
	$global = Codeweber_Options::get( 'body_bg_global_color' );
	if ( $global && $global !== 'default' ) {
		return sanitize_key( $global );
	}

	return '';
}

add_filter( 'body_class', function ( array $classes ): array {
	$bg = cw_get_body_bg();
	if ( $bg ) {
		$classes[] = 'cw-page-bg-' . $bg;
	}
	return $classes;
} );

// ── Image / Pattern ───────────────────────────────────────────────────────────

/**
 * Возвращает массив ['class' => '...', 'data' => '...'] для <main class="content-wrapper">.
 * Пустые строки если изображение не задано.
 */
function cw_content_wrapper_bg_attrs(): array {
	if ( ! class_exists( 'Codeweber_Options' ) || ! Codeweber_Options::is_ready() ) {
		return [ 'class' => '', 'data' => '' ];
	}

	$image_url = '';
	$mode      = 'image';
	$repeat    = 'repeat';
	$size      = 'cover';

	$prefix = cw_body_bg_context_prefix();

	// 1. Per-post метабокс
	if ( is_singular() || is_page() ) {
		$post_id    = get_queried_object_id();
		$meta_image = Codeweber_Options::get_post_meta( $post_id, 'page-body-bg-image' );
		if ( ! empty( $meta_image['url'] ) ) {
			$image_url = $meta_image['url'];
			$mode      = Codeweber_Options::get_post_meta( $post_id, 'page-body-bg-mode' ) ?: 'image';
			$size      = Codeweber_Options::get_post_meta( $post_id, 'page-body-bg-size' ) ?: 'cover';
			$repeat    = Codeweber_Options::get_post_meta( $post_id, 'page-body-bg-repeat' ) ?: 'repeat';
		}
	}

	// 2. Per-CPT Redux
	if ( empty( $image_url ) && $prefix ) {
		$redux_image = Codeweber_Options::get( 'body_bg_image_' . $prefix );
		if ( ! empty( $redux_image['url'] ) ) {
			$image_url = $redux_image['url'];
			$mode      = Codeweber_Options::get( 'body_bg_mode_' . $prefix ) ?: 'image';
			$size      = Codeweber_Options::get( 'body_bg_size_' . $prefix ) ?: 'cover';
			$repeat    = Codeweber_Options::get( 'body_bg_repeat_' . $prefix ) ?: 'repeat';
		}
	}

	// 3. Глобальный Redux
	if ( empty( $image_url ) ) {
		$global_image = Codeweber_Options::get( 'body_bg_global_image' );
		if ( ! empty( $global_image['url'] ) ) {
			$image_url = $global_image['url'];
			$mode      = Codeweber_Options::get( 'body_bg_global_mode' ) ?: 'image';
			$size      = Codeweber_Options::get( 'body_bg_global_size' ) ?: 'cover';
			$repeat    = Codeweber_Options::get( 'body_bg_global_repeat' ) ?: 'repeat';
		}
	}

	if ( empty( $image_url ) ) {
		return [ 'class' => '', 'data' => '' ];
	}

	// Строим CSS-классы по аналогии с page-header
	$classes = [ 'bg-image' ];
	if ( $mode === 'pattern' ) {
		$classes[] = 'pattern-wrapper';
		if ( $repeat !== 'repeat' ) {
			$classes[] = 'cw-bg-repeat-' . sanitize_key( $repeat );
		}
	} else {
		$classes[] = 'image-wrapper';
		$size_map  = [ 'cover' => 'bg-cover', 'auto' => 'bg-auto', 'full' => 'bg-full' ];
		$classes[] = $size_map[ $size ] ?? 'bg-cover';
	}

	return [
		'class' => implode( ' ', $classes ),
		'data'  => 'data-image-src="' . esc_url( $image_url ) . '"',
	];
}

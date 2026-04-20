<?php
/**
 * Media Library: фильтр по типу родительской записи (CPT).
 *
 * Добавляет дополнительный dropdown в toolbar медиатеки ("Тип записи")
 * и расширяет AJAX-запрос query-attachments параметром parent_post_type.
 *
 * Работает глобально во всех admin-контекстах, где открывается WP Media Library:
 * /wp-admin/upload.php, любой MediaUpload в Gutenberg, metabox featured image,
 * Customizer и т.д.
 *
 * @package Codeweber
 */

defined( 'ABSPATH' ) || exit;

/**
 * Базовый blacklist служебных post_type, которые не показываем в фильтре.
 *
 * @return string[]
 */
function cw_media_cpt_filter_blacklist(): array {
	$default = [
		// WordPress core служебные
		'attachment', 'revision', 'nav_menu_item',
		'wp_block', 'wp_template', 'wp_template_part',
		'wp_navigation', 'wp_global_styles',
		'wp_font_family', 'wp_font_face',
		// Служебные нашей темы
		'header', 'footer', 'modal', 'html_blocks',
		'page-header', 'notifications', 'codeweber_form',
	];

	/**
	 * Позволяет child-теме/плагину расширить blacklist.
	 *
	 * @param string[] $default Массив slug-ов служебных CPT.
	 */
	return (array) apply_filters( 'codeweber_media_cpt_blacklist', $default );
}

/**
 * Возвращает список публичных CPT, доступных для фильтрации в медиатеке.
 * Результат — массив объектов WP_Post_Type, упорядоченный по label.
 *
 * @return \WP_Post_Type[]
 */
function cw_media_cpt_filter_types(): array {
	$cache_key   = 'cw_media_cpt_types';
	$cache_group = 'cw_media';
	$cached      = wp_cache_get( $cache_key, $cache_group );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$types = get_post_types(
		[
			'public'  => true,
			'show_ui' => true,
		],
		'objects'
	);

	$blacklist = cw_media_cpt_filter_blacklist();
	foreach ( $blacklist as $slug ) {
		unset( $types[ $slug ] );
	}

	// Сортировка по видимому label.
	uasort(
		$types,
		static function ( $a, $b ) {
			return strcmp( $a->labels->name ?? $a->name, $b->labels->name ?? $b->name );
		}
	);

	/**
	 * Итоговый список CPT для фильтра медиатеки.
	 *
	 * @param \WP_Post_Type[] $types
	 */
	$types = (array) apply_filters( 'codeweber_media_cpt_filter_types', $types );

	wp_cache_set( $cache_key, $types, $cache_group, 5 * MINUTE_IN_SECONDS );
	return $types;
}

/**
 * Enqueue JS-расширение AttachmentFilters в admin.
 */
add_action( 'admin_enqueue_scripts', 'cw_media_cpt_filter_enqueue' );
function cw_media_cpt_filter_enqueue(): void {
	if ( ! is_admin() ) {
		return;
	}

	// НЕ вызываем wp_enqueue_media() здесь: в WP 6.9.4 это на страницах
	// Gutenberg-редактора ломает загрузку wordcount.min.js и вешает editor.
	// media-views тянется как dependency скрипта ниже — этого достаточно
	// для страниц, где WP и так подгружает media (upload.php, post.php).

	$handle = 'cw-media-cpt-filter';
	$src    = get_template_directory_uri() . '/functions/admin/media-cpt-filter.js';
	$path   = get_template_directory() . '/functions/admin/media-cpt-filter.js';
	$ver    = file_exists( $path ) ? (string) filemtime( $path ) : '1.0.0';

	wp_enqueue_script( $handle, $src, [ 'media-views', 'jquery' ], $ver, true );

	// Inline-CSS: переносим .media-toolbar-secondary на вторую строку,
	// чтобы в узких media-фреймах (Gallery Create, Featured Image и т.п.)
	// селекты CPT/поста/тега не уезжали за правый край и не обрезались.
	$css = '.media-toolbar-secondary{display:flex;flex-wrap:wrap;align-items:center;gap:4px 6px;}'
		. '.media-toolbar-secondary select.attachment-filters{max-width:220px;}'
		. '.mode-select .media-toolbar-secondary select.attachment-filters{max-width:170px;}';
	wp_register_style( 'cw-media-cpt-filter', false, [], $ver );
	wp_enqueue_style( 'cw-media-cpt-filter' );
	wp_add_inline_style( 'cw-media-cpt-filter', $css );

	$types_data = [];
	foreach ( cw_media_cpt_filter_types() as $pt ) {
		$types_data[] = [
			'slug'  => $pt->name,
			'label' => $pt->labels->name ?? $pt->name,
		];
	}

	// Теги изображений — передаём сразу, список обычно небольшой.
	$tags_data = [];
	if ( taxonomy_exists( 'image_tag' ) ) {
		$terms = get_terms(
			[
				'taxonomy'   => 'image_tag',
				'hide_empty' => false,
				'orderby'    => 'name',
			]
		);
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $t ) {
				$tags_data[] = [
					'slug' => $t->slug,
					'name' => $t->name,
				];
			}
		}
	}

	wp_localize_script(
		$handle,
		'CW_MediaCptFilter',
		[
			'types'   => $types_data,
			'tags'    => $tags_data,
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'cw_media_cpt_posts' ),
			'i18n'    => [
				'all'         => __( 'All post types', 'codeweber' ),
				'allPosts'    => __( 'All posts', 'codeweber' ),
				'allTags'     => __( 'All image tags', 'codeweber' ),
				'label'       => __( 'Post type', 'codeweber' ),
				'filter'      => __( 'Filter by post type', 'codeweber' ),
				'filterPost'  => __( 'Filter by post', 'codeweber' ),
				'filterTag'   => __( 'Filter by image tag', 'codeweber' ),
				'loading'     => __( 'Loading…', 'codeweber' ),
				'truncated'   => __( 'Showing latest 200. Refine by post type.', 'codeweber' ),
				'noPosts'     => __( 'No posts found', 'codeweber' ),
			],
		]
	);
}

/**
 * Фильтрует AJAX-выборку attachments по parent_post_type.
 *
 * @param array $args Аргументы WP_Query для attachments.
 * @return array
 */
add_filter( 'ajax_query_attachments_args', 'cw_media_cpt_filter_ajax_args' );
function cw_media_cpt_filter_ajax_args( $args ) {
	$query = isset( $_REQUEST['query'] ) && is_array( $_REQUEST['query'] ) ? $_REQUEST['query'] : [];

	// Фильтр по тегу изображения — применяется независимо от CPT.
	if ( ! empty( $query['image_tag'] ) && taxonomy_exists( 'image_tag' ) ) {
		$tag_slug = sanitize_title( $query['image_tag'] );
		if ( $tag_slug !== '' ) {
			$args['tax_query'] = array_merge(
				(array) ( $args['tax_query'] ?? [] ),
				[
					[
						'taxonomy' => 'image_tag',
						'field'    => 'slug',
						'terms'    => [ $tag_slug ],
					],
				]
			);
		}
	}

	// Конкретный пост имеет приоритет над типом: если задан parent_post_id — фильтруем по нему.
	if ( ! empty( $query['parent_post_id'] ) ) {
		$parent_id = (int) $query['parent_post_id'];
		if ( $parent_id > 0 && get_post( $parent_id ) ) {
			$args['post_parent'] = $parent_id;
			return $args;
		}
	}

	if ( empty( $query['parent_post_type'] ) ) {
		return $args;
	}

	$pt = sanitize_key( $query['parent_post_type'] );
	if ( $pt === 'all' || $pt === '' ) {
		return $args;
	}
	if ( ! post_type_exists( $pt ) ) {
		return $args;
	}

	// Защита — разрешаем только те CPT, которые реально есть в нашем фильтре.
	$allowed = array_keys( cw_media_cpt_filter_types() );
	if ( ! in_array( $pt, $allowed, true ) ) {
		return $args;
	}

	$cache_key   = 'cw_parents_' . $pt;
	$cache_group = 'cw_media';
	$parents     = wp_cache_get( $cache_key, $cache_group );
	if ( $parents === false ) {
		$parents = get_posts(
			[
				'post_type'      => $pt,
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'suppress_filters' => true,
			]
		);
		wp_cache_set( $cache_key, $parents, $cache_group, MINUTE_IN_SECONDS );
	}

	$args['post_parent__in'] = ! empty( $parents ) ? $parents : [ 0 ];
	return $args;
}

/**
 * AJAX: список постов указанного CPT для второго dropdown.
 */
add_action( 'wp_ajax_cw_media_cpt_posts', 'cw_media_cpt_filter_posts_ajax' );
function cw_media_cpt_filter_posts_ajax(): void {
	if ( ! current_user_can( 'upload_files' ) ) {
		wp_send_json_error( [ 'message' => __( 'Insufficient permissions.', 'codeweber' ) ], 403 );
	}
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'cw_media_cpt_posts' ) ) {
		wp_send_json_error( [ 'message' => __( 'Security error.', 'codeweber' ) ], 403 );
	}

	$pt = isset( $_POST['post_type'] ) ? sanitize_key( $_POST['post_type'] ) : '';
	if ( $pt === '' || ! post_type_exists( $pt ) ) {
		wp_send_json_error( [ 'message' => __( 'Invalid post type.', 'codeweber' ) ], 400 );
	}
	$allowed = array_keys( cw_media_cpt_filter_types() );
	if ( ! in_array( $pt, $allowed, true ) ) {
		wp_send_json_error( [ 'message' => __( 'Post type not allowed.', 'codeweber' ) ], 400 );
	}

	$limit = 200;
	$cache_key   = 'cw_cpt_posts_' . $pt;
	$cache_group = 'cw_media';
	$cached      = wp_cache_get( $cache_key, $cache_group );
	if ( is_array( $cached ) ) {
		wp_send_json_success( $cached );
	}

	$q = new \WP_Query(
		[
			'post_type'      => $pt,
			'post_status'    => [ 'publish', 'private', 'draft', 'pending', 'future' ],
			'posts_per_page' => $limit + 1, // +1 чтобы понять, есть ли ещё.
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
			'suppress_filters' => true,
			'ignore_sticky_posts' => true,
		]
	);

	$items     = [];
	$truncated = false;
	foreach ( $q->posts as $i => $p ) {
		if ( $i >= $limit ) {
			$truncated = true;
			break;
		}
		$title   = get_the_title( $p );
		$items[] = [
			'id'    => (int) $p->ID,
			'title' => $title !== '' ? $title : sprintf( '#%d', $p->ID ),
		];
	}

	$payload = [
		'items'     => $items,
		'truncated' => $truncated,
	];
	wp_cache_set( $cache_key, $payload, $cache_group, 2 * MINUTE_IN_SECONDS );
	wp_send_json_success( $payload );
}

/**
 * Сбрасывает кэш parents при публикации/удалении любого поста.
 */
add_action( 'save_post', 'cw_media_cpt_filter_bust_cache', 20, 1 );
add_action( 'deleted_post', 'cw_media_cpt_filter_bust_cache', 20, 1 );
function cw_media_cpt_filter_bust_cache( $post_id ): void {
	$pt = get_post_type( $post_id );
	if ( $pt ) {
		wp_cache_delete( 'cw_parents_' . $pt, 'cw_media' );
		wp_cache_delete( 'cw_cpt_posts_' . $pt, 'cw_media' );
	}
}

/**
 * ── List mode (/wp-admin/upload.php?mode=list) ──────────────────────────────
 *
 * В List mode используется классический WP_List_Table, а не Backbone —
 * JS-фильтры не применяются. Добавляем два <select> в toolbar через
 * restrict_manage_posts и фильтруем через parse_query.
 */
add_action( 'restrict_manage_posts', 'cw_media_cpt_filter_list_dropdowns' );
function cw_media_cpt_filter_list_dropdowns( string $post_type ): void {
	if ( $post_type !== 'attachment' ) {
		return;
	}

	$current_type = isset( $_GET['parent_post_type'] ) ? sanitize_key( $_GET['parent_post_type'] ) : '';
	$current_post = isset( $_GET['parent_post_id'] )   ? (int) $_GET['parent_post_id']             : 0;
	$types        = cw_media_cpt_filter_types();

	// CPT dropdown.
	echo '<label class="screen-reader-text" for="cw-filter-parent-type">' . esc_html__( 'Filter by post type', 'codeweber' ) . '</label>';
	echo '<select id="cw-filter-parent-type" name="parent_post_type">';
	echo '<option value="">' . esc_html__( 'All post types', 'codeweber' ) . '</option>';
	foreach ( $types as $pt ) {
		printf(
			'<option value="%s"%s>%s</option>',
			esc_attr( $pt->name ),
			selected( $current_type, $pt->name, false ),
			esc_html( $pt->labels->name ?? $pt->name )
		);
	}
	echo '</select>';

	// Image Tag dropdown — виден всегда, независим от CPT.
	if ( taxonomy_exists( 'image_tag' ) ) {
		$current_tag = isset( $_GET['image_tag'] ) ? sanitize_title( $_GET['image_tag'] ) : '';
		$terms       = get_terms(
			[
				'taxonomy'   => 'image_tag',
				'hide_empty' => false,
				'orderby'    => 'name',
			]
		);
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			echo '<label class="screen-reader-text" for="cw-filter-image-tag">' . esc_html__( 'Filter by image tag', 'codeweber' ) . '</label>';
			echo '<select id="cw-filter-image-tag" name="image_tag">';
			echo '<option value="">' . esc_html__( 'All image tags', 'codeweber' ) . '</option>';
			foreach ( $terms as $t ) {
				printf(
					'<option value="%s"%s>%s</option>',
					esc_attr( $t->slug ),
					selected( $current_tag, $t->slug, false ),
					esc_html( $t->name )
				);
			}
			echo '</select>';
		}
	}

	// Post dropdown — только если CPT выбран и он в whitelist.
	if ( $current_type !== '' && isset( $types[ $current_type ] ) ) {
		$q = new \WP_Query(
			[
				'post_type'           => $current_type,
				'post_status'         => [ 'publish', 'private', 'draft', 'pending', 'future' ],
				'posts_per_page'      => 200,
				'orderby'             => 'date',
				'order'               => 'DESC',
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
				'suppress_filters'    => true,
			]
		);

		echo '<label class="screen-reader-text" for="cw-filter-parent-post">' . esc_html__( 'Filter by post', 'codeweber' ) . '</label>';
		echo '<select id="cw-filter-parent-post" name="parent_post_id">';
		echo '<option value="0">' . esc_html__( 'All posts', 'codeweber' ) . '</option>';
		foreach ( $q->posts as $p ) {
			$title = get_the_title( $p );
			printf(
				'<option value="%d"%s>%s</option>',
				(int) $p->ID,
				selected( $current_post, (int) $p->ID, false ),
				esc_html( $title !== '' ? $title : sprintf( '#%d', $p->ID ) )
			);
		}
		echo '</select>';
	}
}

add_action( 'parse_query', 'cw_media_cpt_filter_list_query' );
function cw_media_cpt_filter_list_query( \WP_Query $query ): void {
	global $pagenow;
	if ( ! is_admin() || $pagenow !== 'upload.php' ) {
		return;
	}
	if ( ! $query->is_main_query() ) {
		return;
	}
	if ( ( $query->get( 'post_type' ) ?: 'attachment' ) !== 'attachment' ) {
		return;
	}

	$current_post = isset( $_GET['parent_post_id'] )   ? (int) $_GET['parent_post_id']             : 0;
	$current_type = isset( $_GET['parent_post_type'] ) ? sanitize_key( $_GET['parent_post_type'] ) : '';
	$current_tag  = isset( $_GET['image_tag'] )        ? sanitize_title( $_GET['image_tag'] )     : '';

	// Фильтр по тегу изображения — применяется независимо от CPT/поста.
	if ( $current_tag !== '' && taxonomy_exists( 'image_tag' ) ) {
		$existing = (array) $query->get( 'tax_query', [] );
		$existing[] = [
			'taxonomy' => 'image_tag',
			'field'    => 'slug',
			'terms'    => [ $current_tag ],
		];
		$query->set( 'tax_query', $existing );
	}

	if ( $current_post > 0 && get_post( $current_post ) ) {
		$query->set( 'post_parent', $current_post );
		return;
	}

	if ( $current_type === '' ) {
		return;
	}
	$allowed = array_keys( cw_media_cpt_filter_types() );
	if ( ! in_array( $current_type, $allowed, true ) ) {
		return;
	}

	$cache_key   = 'cw_parents_' . $current_type;
	$cache_group = 'cw_media';
	$parents     = wp_cache_get( $cache_key, $cache_group );
	if ( $parents === false ) {
		$parents = get_posts(
			[
				'post_type'        => $current_type,
				'post_status'      => 'any',
				'posts_per_page'   => -1,
				'fields'           => 'ids',
				'suppress_filters' => true,
			]
		);
		wp_cache_set( $cache_key, $parents, $cache_group, MINUTE_IN_SECONDS );
	}
	$query->set( 'post_parent__in', ! empty( $parents ) ? $parents : [ 0 ] );
}

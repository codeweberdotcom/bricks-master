<?php
/**
 * Demo данные для CPT Header
 *
 * Функции для создания demo хедеров (Header_01 … Header_08) — все типы navbar.
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Получить данные demo хедеров.
 * Header_01 = Classic Center Nav, Header_02 = Classic Right Nav, … Header_08 = Extended Center Logo.
 *
 * @return array
 */
function cw_demo_get_headers_data() {
	return array(
		array(
			'title'   => 'Header_01',
			'slug'    => 'header-01',
			'content' => '<!-- wp:codeweber-blocks/navbar {"navbarType":"navbar-1"} /-->',
		),
		array(
			'title'   => 'Header_02',
			'slug'    => 'header-02',
			'content' => '<!-- wp:codeweber-blocks/navbar {"navbarType":"navbar-2"} /-->',
		),
		array(
			'title'   => 'Header_03',
			'slug'    => 'header-03',
			'content' => '<!-- wp:codeweber-blocks/navbar {"navbarType":"navbar-3"} /-->',
		),
		array(
			'title'   => 'Header_04',
			'slug'    => 'header-04',
			'content' => '<!-- wp:codeweber-blocks/navbar {"navbarType":"navbar-4"} /-->',
		),
		array(
			'title'   => 'Header_05',
			'slug'    => 'header-05',
			'content' => '<!-- wp:codeweber-blocks/navbar {"navbarType":"navbar-5"} /-->',
		),
		array(
			'title'   => 'Header_06',
			'slug'    => 'header-06',
			'content' => '<!-- wp:codeweber-blocks/navbar {"navbarType":"navbar-6"} /-->',
		),
		array(
			'title'   => 'Header_07',
			'slug'    => 'header-07',
			'content' => '<!-- wp:codeweber-blocks/navbar {"navbarType":"navbar-7"} /-->',
		),
		array(
			'title'   => 'Header_08',
			'slug'    => 'header-08',
			'content' => '<!-- wp:codeweber-blocks/navbar {"navbarType":"navbar-8"} /-->',
		),
		array(
			'title'   => 'Header_09',
			'slug'    => 'header-09',
			'content' => '<!-- wp:codeweber-blocks/top-header {"showAddress":true,"showEmail":true,"showPhone":true,"phones":["phone_01"],"backgroundColor":"primary","textColor":"white"} /--><!-- wp:codeweber-blocks/navbar {"navbarType":"navbar-1"} /-->',
		),
	);
}

/**
 * Создать один demo хедер.
 *
 * @param array $header_data Данные хедера.
 * @return int|false ID созданной записи или false при ошибке.
 */
function cw_demo_create_header_post( $header_data ) {
	if ( empty( $header_data['title'] ) || empty( $header_data['content'] ) ) {
		return false;
	}

	$post_data = array(
		'post_title'   => sanitize_text_field( $header_data['title'] ),
		'post_name'    => ! empty( $header_data['slug'] ) ? sanitize_title( $header_data['slug'] ) : sanitize_title( $header_data['title'] ),
		'post_status'  => 'publish',
		'post_type'    => 'header',
		'post_author'  => get_current_user_id(),
		'post_content' => $header_data['content'],
	);

	$post_id = wp_insert_post( $post_data );

	if ( is_wp_error( $post_id ) ) {
		return false;
	}

	update_post_meta( $post_id, '_demo_created', true );

	return $post_id;
}

/**
 * Создать все demo хедеры.
 *
 * @return array
 */
function cw_demo_create_headers() {
	$data = cw_demo_get_headers_data();

	if ( empty( $data ) ) {
		return array(
			'success' => false,
			'message' => __( 'No data found', 'codeweber' ),
			'created' => 0,
			'errors'  => array(),
		);
	}

	$created = 0;
	$errors  = array();

	foreach ( $data as $item ) {
		$post_id = cw_demo_create_header_post( $item );
		if ( $post_id ) {
			$created++;
		} else {
			$errors[] = __( 'Failed to create:', 'codeweber' ) . ' ' . ( ! empty( $item['title'] ) ? $item['title'] : '—' );
		}
	}

	return array(
		'success' => true,
		'message' => sprintf( __( '%1$d of %2$d headers created', 'codeweber' ), $created, count( $data ) ),
		'created' => $created,
		'total'   => count( $data ),
		'errors'  => $errors,
	);
}

/**
 * Удалить все demo хедеры.
 *
 * @return array
 */
function cw_demo_delete_headers() {
	$query = new WP_Query(
		array(
			'post_type'      => 'header',
			'posts_per_page' => -1,
			'meta_query'     => array(
				array(
					'key'     => '_demo_created',
					'value'   => true,
					'compare' => '=',
				),
			),
			'fields'         => 'ids',
		)
	);

	$ids     = $query->posts;
	$deleted = 0;

	foreach ( $ids as $post_id ) {
		if ( wp_delete_post( $post_id, true ) ) {
			$deleted++;
		}
	}

	return array(
		'success' => true,
		'message' => sprintf( __( '%d headers deleted', 'codeweber' ), $deleted ),
		'deleted' => $deleted,
		'errors'  => array(),
	);
}

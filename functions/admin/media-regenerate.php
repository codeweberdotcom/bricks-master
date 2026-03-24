<?php
/**
 * AJAX-обработчики для регенерации миниатюр изображений
 *
 * @package Codeweber
 */

defined( 'ABSPATH' ) || exit;

/**
 * Получить общее количество изображений в медиатеке
 */
function cw_media_regen_ajax_count() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( [ 'message' => __( 'Insufficient permissions.', 'codeweber' ) ] );
	}
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'cw_media_regen' ) ) {
		wp_send_json_error( [ 'message' => __( 'Security error. Please refresh the page and try again.', 'codeweber' ) ] );
	}

	$query = new WP_Query( [
		'post_type'      => 'attachment',
		'post_mime_type' => 'image',
		'post_status'    => 'inherit',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'no_found_rows'  => false,
	] );

	wp_send_json_success( [ 'total' => (int) $query->found_posts ] );
}
add_action( 'wp_ajax_cw_media_regen_count', 'cw_media_regen_ajax_count' );

/**
 * Регенерировать пакет миниатюр
 */
function cw_media_regen_ajax_batch() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( [ 'message' => __( 'Insufficient permissions.', 'codeweber' ) ] );
	}
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'cw_media_regen' ) ) {
		wp_send_json_error( [ 'message' => __( 'Security error. Please refresh the page and try again.', 'codeweber' ) ] );
	}

	$offset = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0;
	$limit  = isset( $_POST['limit'] )  ? absint( $_POST['limit'] )  : 10;
	$total  = isset( $_POST['total'] )  ? absint( $_POST['total'] )  : 0;

	@set_time_limit( 120 );

	$ids = get_posts( [
		'post_type'      => 'attachment',
		'post_mime_type' => 'image',
		'post_status'    => 'inherit',
		'posts_per_page' => $limit,
		'offset'         => $offset,
		'fields'         => 'ids',
		'orderby'        => 'ID',
		'order'          => 'ASC',
	] );

	$attempted = count( $ids );
	$errors    = [];
	$lost      = [];

	foreach ( $ids as $id ) {
		$file = get_attached_file( $id );
		if ( ! $file || ! file_exists( $file ) ) {
			$attachment = get_post( $id );
			$parent_id  = $attachment ? (int) $attachment->post_parent : 0;
			$parent     = $parent_id  ? get_post( $parent_id )         : null;

			$lost_item = [
				'attachment_id' => $id,
				'filename'      => $file ? basename( $file ) : __( '(unknown)', 'codeweber' ),
				'parent_id'     => $parent_id,
				'parent_title'  => $parent ? $parent->post_title : __( '(no parent)', 'codeweber' ),
				'parent_url'    => $parent_id ? (string) get_permalink( $parent_id )     : '',
				'edit_url'      => $parent_id ? (string) get_edit_post_link( $parent_id ) : '',
			];

			$lost[]   = $lost_item;
			/* translators: %d: attachment ID */
			$msg      = sprintf( __( 'File not found for attachment #%d', 'codeweber' ), $id );
			$errors[] = $msg;
			error_log( '[CW Media Regen] ' . $msg . ' | file: ' . ( $file ?: 'empty' ) );
			continue;
		}

		// Определяем тип родительской записи для фильтрации размеров
		$attachment  = get_post( $id );
		$parent_id   = $attachment ? (int) $attachment->post_parent : 0;
		$parent_type = $parent_id ? get_post_type( $parent_id ) : 'default';
		if ( ! $parent_type ) {
			$parent_type = 'default';
		}

		$allowed_sizes = codeweber_get_allowed_image_sizes( $parent_type, $parent_id );

		// Применяем фильтр только если для данного CPT заданы конкретные размеры
		$size_filter = null;
		if ( ! empty( $allowed_sizes ) ) {
			$size_filter = static function( array $sizes ) use ( $allowed_sizes ): array {
				return array_intersect_key( $sizes, array_flip( $allowed_sizes ) );
			};
			add_filter( 'intermediate_image_sizes_advanced', $size_filter );
		}

		$metadata = wp_generate_attachment_metadata( $id, $file );

		if ( $size_filter ) {
			remove_filter( 'intermediate_image_sizes_advanced', $size_filter );
		}

		if ( is_wp_error( $metadata ) ) {
			/* translators: 1: attachment ID, 2: error message */
			$msg      = sprintf( __( 'Error for #%d: %s', 'codeweber' ), $id, $metadata->get_error_message() );
			$errors[] = $msg;
			error_log( '[CW Media Regen] ' . $msg . ' | file: ' . $file );
		} else {
			wp_update_attachment_metadata( $id, $metadata );
		}
	}

	$next_offset = $offset + $attempted;
	$done        = ( $attempted < $limit ) || ( $total > 0 && $next_offset >= $total );

	wp_send_json_success( [
		'next_offset' => $next_offset,
		'done'        => $done,
		'errors'      => $errors,
		'lost'        => $lost,
	] );
}
add_action( 'wp_ajax_cw_media_regen_batch', 'cw_media_regen_ajax_batch' );

/**
 * Удалить потерянные вложения из базы данных
 */
function cw_media_delete_lost_ajax() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( [ 'message' => __( 'Insufficient permissions.', 'codeweber' ) ] );
	}
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'cw_media_regen' ) ) {
		wp_send_json_error( [ 'message' => __( 'Security error. Please refresh the page and try again.', 'codeweber' ) ] );
	}

	$ids = isset( $_POST['ids'] ) ? array_map( 'absint', (array) $_POST['ids'] ) : [];
	if ( empty( $ids ) ) {
		wp_send_json_error( [ 'message' => __( 'No attachment IDs provided.', 'codeweber' ) ] );
	}

	$deleted = 0;
	$errors  = [];

	foreach ( $ids as $id ) {
		$result = wp_delete_attachment( $id, true );
		if ( $result ) {
			$deleted++;
			error_log( '[CW Media Regen] Deleted orphaned attachment #' . $id );
		} else {
			$errors[] = sprintf( __( 'Failed to delete attachment #%d', 'codeweber' ), $id );
			error_log( '[CW Media Regen] Failed to delete attachment #' . $id );
		}
	}

	wp_send_json_success( [
		'deleted' => $deleted,
		'errors'  => $errors,
	] );
}
add_action( 'wp_ajax_cw_media_delete_lost', 'cw_media_delete_lost_ajax' );

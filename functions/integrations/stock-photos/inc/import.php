<?php
/**
 * Stock Photos — import a chosen image into the Media Library (sideload).
 *
 * Downloads the file server-side, creates an attachment, stores attribution
 * meta and alt text. For Unsplash, pings the download endpoint as required by
 * their API guidelines.
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_ajax_cw_stock_photos_import', 'cw_stock_photos_ajax_import' );

/**
 * Allowed download hosts per provider (anti-SSRF: only provider CDNs).
 *
 * @return array<string,array<string>>
 */
function cw_stock_photos_allowed_hosts() {
	return array(
		'unsplash'  => array( 'images.unsplash.com', 'plus.unsplash.com' ),
		// Pexels mp4 files are served from videos.pexels.com (current API) or
		// player.vimeo.com (legacy responses); posters live on images.pexels.com.
		'pexels'    => array( 'images.pexels.com', 'www.pexels.com', 'videos.pexels.com', 'player.vimeo.com' ),
		// i.vimeocdn.com serves video poster thumbnails (proxied for previews).
		'pixabay'   => array( 'pixabay.com', 'cdn.pixabay.com', 'i.pixabay.com', 'i.vimeocdn.com' ),
		// Openverse previews are served from its own host; full files live on
		// arbitrary source hosts and are validated via wp_http_validate_url().
		'openverse' => array( 'api.openverse.org' ),
		'freepik'   => array( 'img.freepik.com', 'cdn.freepik.com' ),
	);
}

/**
 * AJAX: import the selected photo.
 */
function cw_stock_photos_ajax_import() {
	check_ajax_referer( 'cw_stock_photos', 'nonce' );

	if ( ! current_user_can( 'upload_files' ) ) {
		wp_send_json_error( array( 'message' => __( 'Permission denied', 'codeweber' ) ), 403 );
	}

	$provider   = sanitize_key( wp_unslash( $_POST['provider'] ?? '' ) );
	$media_type = sanitize_key( wp_unslash( $_POST['media_type'] ?? 'photo' ) );
	$is_video   = ( 'video' === $media_type );
	$url        = esc_url_raw( wp_unslash( $_POST['url'] ?? '' ) );
	$alt        = sanitize_text_field( wp_unslash( $_POST['alt'] ?? '' ) );
	$author     = sanitize_text_field( wp_unslash( $_POST['author'] ?? '' ) );
	$author_url = esc_url_raw( wp_unslash( $_POST['author_url'] ?? '' ) );
	$source_url = esc_url_raw( wp_unslash( $_POST['source_url'] ?? '' ) );
	$dl_loc     = esc_url_raw( wp_unslash( $_POST['download_location'] ?? '' ) );

	$providers = cw_stock_photos_providers();
	if ( ! isset( $providers[ $provider ] ) ) {
		wp_send_json_error( array( 'message' => __( 'Provider not available', 'codeweber' ) ) );
	}

	if ( empty( $url ) ) {
		wp_send_json_error( array( 'message' => __( 'No image URL', 'codeweber' ) ) );
	}

	// Validate the download URL.
	if ( 'openverse' === $provider ) {
		// Openverse full files live on arbitrary source hosts — guard against
		// SSRF (blocks localhost / private / reserved ranges) instead of an
		// impossible host allowlist.
		if ( ! wp_http_validate_url( $url ) ) {
			wp_send_json_error( array( 'message' => __( 'Image URL is not allowed', 'codeweber' ) ) );
		}
	} else {
		// Host must belong to the provider's CDN allowlist.
		$host    = wp_parse_url( $url, PHP_URL_HOST );
		$allowed = cw_stock_photos_allowed_hosts();
		if ( ! $host || empty( $allowed[ $provider ] ) || ! in_array( strtolower( $host ), $allowed[ $provider ], true ) ) {
			wp_send_json_error( array( 'message' => __( 'Image host is not allowed', 'codeweber' ) ) );
		}
	}

	// Unsplash: trigger the download endpoint (API guidelines).
	if ( 'unsplash' === $provider && ! empty( $dl_loc ) ) {
		$dl_host = wp_parse_url( $dl_loc, PHP_URL_HOST );
		if ( 'api.unsplash.com' === strtolower( (string) $dl_host ) ) {
			wp_remote_get(
				$dl_loc,
				cw_stock_photos_request_args(
					array(
						'headers'  => array( 'Authorization' => 'Client-ID ' . $providers['unsplash']['key'] ),
						'timeout'  => 8,
						'blocking' => false,
					)
				)
			);
		}
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	// Download to a temp file (stream) so the request can go through the proxy.
	$tmp = wp_tempnam( $url );
	if ( ! $tmp ) {
		wp_send_json_error( array( 'message' => __( 'Could not create temp file', 'codeweber' ) ) );
	}

	$download = wp_remote_get(
		$url,
		cw_stock_photos_request_args(
			array(
				'timeout'  => $is_video ? 120 : 30,
				'stream'   => true,
				'filename' => $tmp,
				'headers'  => array( 'User-Agent' => 'Mozilla/5.0' ),
			)
		)
	);

	if ( is_wp_error( $download ) ) {
		wp_delete_file( $tmp );
		wp_send_json_error( array( 'message' => __( 'Download failed: ', 'codeweber' ) . $download->get_error_message() ) );
	}

	$dl_code = (int) wp_remote_retrieve_response_code( $download );
	if ( 200 !== $dl_code ) {
		wp_delete_file( $tmp );
		wp_send_json_error( array( 'message' => __( 'Download failed: HTTP ', 'codeweber' ) . $dl_code ) );
	}

	// Build a friendly filename (provider extensions sometimes hide behind query args).
	$ext = pathinfo( wp_parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION );
	if ( $is_video ) {
		$ext = preg_match( '/^(mp4|webm|mov)$/i', $ext ) ? strtolower( $ext ) : 'mp4';
	} else {
		$ext = preg_match( '/^(jpe?g|png|gif|webp)$/i', $ext ) ? strtolower( $ext ) : 'jpg';
	}
	$slug = sanitize_title( $alt ? $alt : ( $provider . '-' . wp_generate_password( 6, false ) ) );
	$slug = $slug ? $slug : $provider;

	$file_array = array(
		'name'     => substr( $slug, 0, 60 ) . '-' . $provider . '.' . $ext,
		'tmp_name' => $tmp,
	);

	$attachment_id = media_handle_sideload( $file_array, 0, $alt );

	if ( is_wp_error( $attachment_id ) ) {
		if ( file_exists( $tmp ) ) {
			wp_delete_file( $tmp );
		}
		wp_send_json_error( array( 'message' => __( 'Import failed: ', 'codeweber' ) . $attachment_id->get_error_message() ) );
	}

	// Alt text + attribution meta.
	if ( $alt ) {
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
	}
	update_post_meta( $attachment_id, '_cw_stock_provider', $provider );
	update_post_meta( $attachment_id, '_cw_stock_media_type', $is_video ? 'video' : 'photo' );
	update_post_meta( $attachment_id, '_cw_stock_author', $author );
	update_post_meta( $attachment_id, '_cw_stock_author_url', $author_url );
	update_post_meta( $attachment_id, '_cw_stock_source_url', $source_url );

	// Auto-create a media_license record and link it to the attachment.
	$license_text = $providers[ $provider ]['license'] ?? '';
	$license_id   = cw_stock_photos_create_license( $provider, $alt, $author, $author_url, $source_url, $license_text );
	if ( $license_id ) {
		update_post_meta( $attachment_id, '_media_license_id', $license_id );
	}

	$thumb = wp_get_attachment_image_url( $attachment_id, 'thumbnail' );
	$full  = wp_get_attachment_image_url( $attachment_id, 'full' );

	wp_send_json_success(
		array(
			'id'       => $attachment_id,
			'url'      => $full,
			'thumb'    => $thumb ? $thumb : $full,
			'editLink' => get_edit_post_link( $attachment_id, 'raw' ),
		)
	);
}

/**
 * Auto-create a media_license CPT record for an imported stock photo.
 *
 * Creates a licensor_author taxonomy term for the photographer if it doesn't
 * exist yet, then inserts a media_license post and returns its ID.
 *
 * @param string $provider     Provider slug (unsplash, pexels, etc.).
 * @param string $alt          Photo title / alt text.
 * @param string $author       Photographer's display name.
 * @param string $author_url   Photographer's profile URL.
 * @param string $source_url   Photo page URL on the provider's site.
 * @param string $license_text Human-readable license description.
 * @return int|null New post ID, or null on failure.
 */
function cw_stock_photos_create_license( $provider, $alt, $author, $author_url, $source_url, $license_text ) {
	$provider_labels = array(
		'unsplash'  => 'Unsplash',
		'pexels'    => 'Pexels',
		'pixabay'   => 'Pixabay',
		'openverse' => 'Openverse',
		'freepik'   => 'Freepik',
	);
	$label = $provider_labels[ $provider ] ?? ucfirst( $provider );
	$title = $alt ? $label . ' — ' . $alt : $label;
	$title = mb_substr( $title, 0, 200 );

	$license_id = wp_insert_post( array(
		'post_type'   => 'media_license',
		'post_title'  => $title,
		'post_status' => 'publish',
	) );

	if ( is_wp_error( $license_id ) || ! $license_id ) {
		return null;
	}

	update_post_meta( $license_id, '_license_type', $license_text );
	update_post_meta( $license_id, '_item_url', $source_url );
	update_post_meta( $license_id, '_download_date', gmdate( 'Y-m-d' ) );

	if ( $author ) {
		$term = get_term_by( 'name', $author, 'licensor_author' );
		if ( $term ) {
			$term_id = (int) $term->term_id;
		} else {
			$inserted = wp_insert_term( $author, 'licensor_author', array( 'description' => $author_url ) );
			$term_id  = is_wp_error( $inserted ) ? null : (int) $inserted['term_id'];
		}
		if ( $term_id ) {
			wp_set_object_terms( $license_id, array( $term_id ), 'licensor_author', false );
		}
	}

	return $license_id;
}

/**
 * Surface attribution in the attachment "Edit Media" details fields.
 *
 * @param array   $fields Existing fields.
 * @param WP_Post $post   Attachment.
 * @return array
 */
function cw_stock_photos_attachment_fields( $fields, $post ) {
	$provider = get_post_meta( $post->ID, '_cw_stock_provider', true );
	if ( ! $provider ) {
		return $fields;
	}

	$author     = get_post_meta( $post->ID, '_cw_stock_author', true );
	$author_url = get_post_meta( $post->ID, '_cw_stock_author_url', true );
	$source_url = get_post_meta( $post->ID, '_cw_stock_source_url', true );

	$html  = '<strong>' . esc_html( ucfirst( $provider ) ) . '</strong>';
	if ( $author ) {
		$html .= '<br>' . esc_html__( 'Photo by', 'codeweber' ) . ' ';
		$html .= $author_url ? '<a href="' . esc_url( $author_url ) . '" target="_blank" rel="noopener">' . esc_html( $author ) . '</a>' : esc_html( $author );
	}
	if ( $source_url ) {
		$html .= '<br><a href="' . esc_url( $source_url ) . '" target="_blank" rel="noopener">' . esc_html__( 'View original', 'codeweber' ) . '</a>';
	}

	$fields['cw_stock_attribution'] = array(
		'label' => esc_html__( 'Stock attribution', 'codeweber' ),
		'input' => 'html',
		'html'  => $html,
	);

	return $fields;
}
add_filter( 'attachment_fields_to_edit', 'cw_stock_photos_attachment_fields', 10, 2 );

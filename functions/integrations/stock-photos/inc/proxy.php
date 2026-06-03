<?php
/**
 * Stock Photos — server-side search proxy.
 *
 * Receives a query from the browser, calls the selected provider's API with the
 * secret key (kept server-side), and returns a normalized result set.
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_ajax_cw_stock_photos_search', 'cw_stock_photos_ajax_search' );
add_action( 'wp_ajax_cw_stock_photos_thumb', 'cw_stock_photos_ajax_thumb' );

/**
 * AJAX: stream a provider thumbnail through the server.
 *
 * Some networks reset direct browser connections to the provider CDNs while the
 * server can still reach them. This proxy fetches the image server-side and
 * streams it back so previews render. Admin-only, host-allowlisted.
 */
function cw_stock_photos_ajax_thumb() {
	if ( ! current_user_can( 'upload_files' ) ) {
		status_header( 403 );
		exit;
	}

	$nonce = sanitize_text_field( wp_unslash( $_GET['nonce'] ?? '' ) );
	if ( ! wp_verify_nonce( $nonce, 'cw_stock_photos' ) ) {
		status_header( 403 );
		exit;
	}

	$url = esc_url_raw( wp_unslash( $_GET['url'] ?? '' ) );
	if ( ! $url ) {
		status_header( 400 );
		exit;
	}

	// Host must belong to a known provider CDN.
	$host    = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
	$allowed = array();
	foreach ( cw_stock_photos_allowed_hosts() as $hosts ) {
		$allowed = array_merge( $allowed, $hosts );
	}
	if ( ! in_array( $host, $allowed, true ) ) {
		status_header( 403 );
		exit;
	}

	$response = wp_remote_get(
		$url,
		array(
			'timeout' => 15,
			'headers' => array( 'User-Agent' => 'Mozilla/5.0' ),
		)
	);

	if ( is_wp_error( $response ) ) {
		status_header( 502 );
		exit;
	}

	$code = (int) wp_remote_retrieve_response_code( $response );
	if ( 200 !== $code ) {
		status_header( $code ? $code : 502 );
		exit;
	}

	$ct = wp_remote_retrieve_header( $response, 'content-type' );
	if ( ! $ct || 0 !== strpos( $ct, 'image/' ) ) {
		$ct = 'image/jpeg';
	}

	$body = wp_remote_retrieve_body( $response );

	nocache_headers();
	header( 'Content-Type: ' . $ct );
	header( 'Content-Length: ' . strlen( $body ) );
	header( 'Cache-Control: private, max-age=3600' );
	header( 'X-Content-Type-Options: nosniff' );

	echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- raw image binary.
	exit;
}

/**
 * AJAX: search a single provider.
 */
function cw_stock_photos_ajax_search() {
	check_ajax_referer( 'cw_stock_photos', 'nonce' );

	if ( ! current_user_can( 'upload_files' ) ) {
		wp_send_json_error( array( 'message' => __( 'Permission denied', 'codeweber' ) ), 403 );
	}

	$provider = sanitize_key( wp_unslash( $_POST['provider'] ?? '' ) );
	$query    = sanitize_text_field( wp_unslash( $_POST['query'] ?? '' ) );
	$page     = max( 1, (int) ( $_POST['page'] ?? 1 ) );
	$per_page = min( 50, max( 1, (int) ( $_POST['per_page'] ?? 24 ) ) );

	$providers = cw_stock_photos_providers();
	if ( ! isset( $providers[ $provider ] ) ) {
		wp_send_json_error( array( 'message' => __( 'Provider not available', 'codeweber' ) ) );
	}

	if ( '' === $query ) {
		wp_send_json_error( array( 'message' => __( 'Empty query', 'codeweber' ) ) );
	}

	$key = $providers[ $provider ]['key'];

	switch ( $provider ) {
		case 'unsplash':
			$result = cw_stock_photos_fetch_unsplash( $key, $query, $page, $per_page );
			break;
		case 'pexels':
			$result = cw_stock_photos_fetch_pexels( $key, $query, $page, $per_page );
			break;
		case 'pixabay':
			$result = cw_stock_photos_fetch_pixabay( $key, $query, $page, $per_page );
			break;
		default:
			$result = new WP_Error( 'cw_stock', __( 'Unknown provider', 'codeweber' ) );
	}

	if ( is_wp_error( $result ) ) {
		wp_send_json_error( array( 'message' => $result->get_error_message() ) );
	}

	wp_send_json_success( $result );
}

/**
 * Unsplash search → normalized.
 */
function cw_stock_photos_fetch_unsplash( $key, $query, $page, $per_page ) {
	$url = add_query_arg(
		array(
			'query'    => rawurlencode( $query ),
			'page'     => $page,
			'per_page' => $per_page,
		),
		'https://api.unsplash.com/search/photos'
	);

	$response = wp_remote_get(
		$url,
		array(
			'headers' => array( 'Authorization' => 'Client-ID ' . $key ),
			'timeout' => 15,
		)
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$code = wp_remote_retrieve_response_code( $response );
	$body = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( 200 !== $code || ! isset( $body['results'] ) ) {
		$msg = isset( $body['errors'][0] ) ? $body['errors'][0] : ( 'HTTP ' . $code );
		return new WP_Error( 'cw_stock', 'Unsplash: ' . $msg );
	}

	$items = array();
	foreach ( $body['results'] as $p ) {
		$items[] = array(
			'provider'          => 'unsplash',
			'id'                => (string) ( $p['id'] ?? '' ),
			'thumb'             => $p['urls']['small'] ?? '',
			'preview'           => $p['urls']['regular'] ?? '',
			'full'              => $p['urls']['full'] ?? ( $p['urls']['raw'] ?? '' ),
			'width'             => (int) ( $p['width'] ?? 0 ),
			'height'            => (int) ( $p['height'] ?? 0 ),
			'alt'               => (string) ( $p['alt_description'] ?? ( $p['description'] ?? $query ) ),
			'author'            => (string) ( $p['user']['name'] ?? '' ),
			'author_url'        => (string) ( $p['user']['links']['html'] ?? '' ),
			'source_url'        => (string) ( $p['links']['html'] ?? '' ),
			// Required by Unsplash API guidelines: ping on download.
			'download_location' => (string) ( $p['links']['download_location'] ?? '' ),
		);
	}

	return array(
		'items'    => $items,
		'total'    => (int) ( $body['total'] ?? 0 ),
		'has_more' => $page < (int) ( $body['total_pages'] ?? 0 ),
	);
}

/**
 * Pexels search → normalized.
 */
function cw_stock_photos_fetch_pexels( $key, $query, $page, $per_page ) {
	$url = add_query_arg(
		array(
			'query'    => rawurlencode( $query ),
			'page'     => $page,
			'per_page' => $per_page,
		),
		'https://api.pexels.com/v1/search'
	);

	$response = wp_remote_get(
		$url,
		array(
			'headers' => array( 'Authorization' => $key ),
			'timeout' => 15,
		)
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$code = wp_remote_retrieve_response_code( $response );
	$body = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( 200 !== $code || ! isset( $body['photos'] ) ) {
		$msg = isset( $body['error'] ) ? $body['error'] : ( 'HTTP ' . $code );
		return new WP_Error( 'cw_stock', 'Pexels: ' . $msg );
	}

	$items = array();
	foreach ( $body['photos'] as $p ) {
		$items[] = array(
			'provider'   => 'pexels',
			'id'         => (string) ( $p['id'] ?? '' ),
			'thumb'      => $p['src']['medium'] ?? '',
			'preview'    => $p['src']['large'] ?? '',
			'full'       => $p['src']['original'] ?? '',
			'width'      => (int) ( $p['width'] ?? 0 ),
			'height'     => (int) ( $p['height'] ?? 0 ),
			'alt'        => (string) ( $p['alt'] ?? $query ),
			'author'     => (string) ( $p['photographer'] ?? '' ),
			'author_url' => (string) ( $p['photographer_url'] ?? '' ),
			'source_url' => (string) ( $p['url'] ?? '' ),
		);
	}

	return array(
		'items'    => $items,
		'total'    => (int) ( $body['total_results'] ?? 0 ),
		'has_more' => ! empty( $body['next_page'] ),
	);
}

/**
 * Pixabay search → normalized.
 */
function cw_stock_photos_fetch_pixabay( $key, $query, $page, $per_page ) {
	$url = add_query_arg(
		array(
			'key'        => $key,
			'q'          => rawurlencode( $query ),
			'image_type' => 'photo',
			'page'       => $page,
			'per_page'   => $per_page,
			'safesearch' => 'true',
		),
		'https://pixabay.com/api/'
	);

	$response = wp_remote_get( $url, array( 'timeout' => 15 ) );

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$code = wp_remote_retrieve_response_code( $response );
	$body = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( 200 !== $code || ! isset( $body['hits'] ) ) {
		return new WP_Error( 'cw_stock', 'Pixabay: ' . wp_remote_retrieve_body( $response ) );
	}

	$items = array();
	foreach ( $body['hits'] as $p ) {
		$items[] = array(
			'provider'   => 'pixabay',
			'id'         => (string) ( $p['id'] ?? '' ),
			'thumb'      => $p['webformatURL'] ?? '',
			'preview'    => $p['largeImageURL'] ?? ( $p['webformatURL'] ?? '' ),
			// largeImageURL (max 1280) is the safe public download; fullHD/original need extra perms.
			'full'       => $p['largeImageURL'] ?? ( $p['webformatURL'] ?? '' ),
			'width'      => (int) ( $p['imageWidth'] ?? 0 ),
			'height'     => (int) ( $p['imageHeight'] ?? 0 ),
			'alt'        => (string) ( $p['tags'] ?? $query ),
			'author'     => (string) ( $p['user'] ?? '' ),
			'author_url' => isset( $p['user'] ) ? 'https://pixabay.com/users/' . rawurlencode( $p['user'] ) . '-' . (int) ( $p['user_id'] ?? 0 ) . '/' : '',
			'source_url' => (string) ( $p['pageURL'] ?? '' ),
		);
	}

	$total = (int) ( $body['totalHits'] ?? 0 );

	return array(
		'items'    => $items,
		'total'    => $total,
		'has_more' => ( $page * $per_page ) < $total,
	);
}

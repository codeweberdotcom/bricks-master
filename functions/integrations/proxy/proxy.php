<?php
/**
 * Outbound Proxy — shared helper for routing server-side HTTP requests through
 * an external proxy (e.g. a VPS) when the web server cannot reach a host.
 *
 * Settings live in the Redux "Proxy" section (option `redux_demo`). Consumers
 * (Stock Photos, Telegram, …) opt in per-module via `proxy_scope` and tag their
 * requests with {@see cw_proxy_request_args()}; only tagged cURL requests are
 * proxied, so the rest of WordPress is untouched.
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read a Redux option.
 *
 * @param string $key     Option id.
 * @param mixed  $default Fallback.
 * @return mixed
 */
function cw_proxy_option( $key, $default = '' ) {
	$opts = get_option( 'redux_demo' );
	return ( is_array( $opts ) && isset( $opts[ $key ] ) ) ? $opts[ $key ] : $default;
}

/**
 * Proxy connection config, or null when disabled / incomplete.
 *
 * @return array{host:string,port:int,type:int,auth:string}|null
 */
function cw_proxy_config() {
	if ( ! cw_proxy_option( 'proxy_enabled', false ) ) {
		return null;
	}

	$host = trim( (string) cw_proxy_option( 'proxy_host', '' ) );
	$port = (int) cw_proxy_option( 'proxy_port', 0 );
	if ( '' === $host || $port <= 0 ) {
		return null;
	}

	$type = ( 'socks5' === cw_proxy_option( 'proxy_type', 'http' ) )
		? ( defined( 'CURLPROXY_SOCKS5' ) ? CURLPROXY_SOCKS5 : 5 )
		: ( defined( 'CURLPROXY_HTTP' ) ? CURLPROXY_HTTP : 0 );

	$user = (string) cw_proxy_option( 'proxy_user', '' );
	$pass = (string) cw_proxy_option( 'proxy_pass', '' );
	$auth = ( '' !== $user ) ? ( $user . ':' . $pass ) : '';

	return array(
		'host' => $host,
		'port' => $port,
		'type' => $type,
		'auth' => $auth,
	);
}

/**
 * Whether the proxy is enabled and scoped to a given module.
 *
 * @param string $module Module slug (e.g. 'stock_photos', 'telegram').
 * @return bool
 */
function cw_proxy_enabled_for( $module ) {
	if ( ! cw_proxy_config() ) {
		return false;
	}
	$scope = cw_proxy_option( 'proxy_scope', array() );
	$scope = is_array( $scope ) ? $scope : array();
	return ! empty( $scope[ $module ] );
}

/**
 * Build request args that carry the proxy flag when the module is in scope.
 *
 * @param string $module Module slug.
 * @param array  $extra  Extra request args to merge.
 * @return array
 */
function cw_proxy_request_args( $module, $extra = array() ) {
	$args = array_merge( array( 'timeout' => 15 ), $extra );
	if ( cw_proxy_enabled_for( $module ) ) {
		$args['cw_use_proxy'] = true;
	}
	return $args;
}

/**
 * Apply the proxy to outbound cURL requests tagged with `cw_use_proxy`.
 *
 * @param resource|CurlHandle $handle cURL handle.
 * @param array               $args   Parsed request args.
 * @param string              $url    Request URL.
 */
function cw_proxy_apply_curl( $handle, $args, $url ) {
	if ( empty( $args['cw_use_proxy'] ) ) {
		return;
	}

	$cfg = cw_proxy_config();
	if ( ! $cfg ) {
		return;
	}

	curl_setopt( $handle, CURLOPT_PROXY, $cfg['host'] );
	curl_setopt( $handle, CURLOPT_PROXYPORT, $cfg['port'] );
	curl_setopt( $handle, CURLOPT_PROXYTYPE, $cfg['type'] );
	if ( '' !== $cfg['auth'] ) {
		curl_setopt( $handle, CURLOPT_PROXYUSERPWD, $cfg['auth'] );
	}
}
add_action( 'http_api_curl', 'cw_proxy_apply_curl', 10, 3 );

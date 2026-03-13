<?php
/**
 * SMS.ru callback handler — delivery status notifications.
 *
 * Configure callback URL in: https://sms.ru/?panel=api&subpanel=cb
 * If this feature is not needed, this file can be deleted.
 *
 * Security: requests are verified by IP whitelist (sms.ru server ranges).
 * If sms.ru changes its IP ranges, update $allowed_ips below.
 */

// sms.ru server IP ranges (source: sms.ru API documentation)
$allowed_ips = [
	'217.107.239.0/24',
];

$client_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) $_SERVER['REMOTE_ADDR'] : '';

if ( ! codeweber_smsru_ip_allowed( $client_ip, $allowed_ips ) ) {
	http_response_code( 403 );
	exit( 'Forbidden' );
}

if ( ! isset( $_POST['data'] ) || ! is_array( $_POST['data'] ) ) {
	http_response_code( 400 );
	exit( 'Bad Request' );
}

foreach ( $_POST['data'] as $entry ) {
	if ( ! is_string( $entry ) ) {
		continue;
	}

	$lines = explode( "\n", $entry );

	if ( ! isset( $lines[0] ) ) {
		continue;
	}

	$type = preg_replace( '/[^a-z_]/', '', strtolower( trim( $lines[0] ) ) );

	if ( $type === 'sms_status' ) {
		$sms_id     = isset( $lines[1] ) ? preg_replace( '/[^a-zA-Z0-9\-]/', '', trim( $lines[1] ) ) : '';
		$sms_status = isset( $lines[2] ) ? preg_replace( '/[^a-zA-Z0-9\-_]/', '', trim( $lines[2] ) ) : '';

		if ( $sms_id && $sms_status ) {
			// Process delivery status here.
			// "Status change. SMS ID: $sms_id. New status: $sms_status"
		}
	}
}

echo '100'; // Required by sms.ru — absence signals handler failure.

/**
 * Check if an IP address falls within any of the given CIDR ranges.
 *
 * @param string   $ip    Client IP address.
 * @param string[] $cidrs Array of CIDR strings (e.g. '217.107.239.0/24') or plain IPs.
 * @return bool
 */
function codeweber_smsru_ip_allowed( string $ip, array $cidrs ): bool {
	foreach ( $cidrs as $cidr ) {
		if ( strpos( $cidr, '/' ) === false ) {
			if ( $ip === $cidr ) {
				return true;
			}
			continue;
		}

		[ $subnet, $prefix ] = explode( '/', $cidr, 2 );
		$prefix = (int) $prefix;

		if ( $prefix < 0 || $prefix > 32 ) {
			continue;
		}

		$ip_long     = ip2long( $ip );
		$subnet_long = ip2long( $subnet );

		if ( $ip_long === false || $subnet_long === false ) {
			continue;
		}

		$mask = $prefix === 0 ? 0 : ( ~0 << ( 32 - $prefix ) );

		if ( ( $ip_long & $mask ) === ( $subnet_long & $mask ) ) {
			return true;
		}
	}

	return false;
}

<?php
/**
 * Add to Calendar — ICS download endpoint.
 *
 * Triggered by ?ics=1 on any single event post.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'template_redirect', 'codeweber_event_ics_download' );

function codeweber_event_ics_download(): void {
	if ( ! is_singular( 'events' ) || ! isset( $_GET['ics'] ) ) {
		return;
	}

	$post_id = get_the_ID();

	if ( get_post_meta( $post_id, '_event_hide_add_to_calendar', true ) ) {
		return;
	}

	$date_start = get_post_meta( $post_id, '_event_date_start', true );
	if ( empty( $date_start ) ) {
		return;
	}

	$date_end  = get_post_meta( $post_id, '_event_date_end', true ) ?: $date_start;
	$location  = get_post_meta( $post_id, '_event_location', true );
	$address   = get_post_meta( $post_id, '_event_address', true );
	$full_loc  = trim( implode( ', ', array_filter( [ $location, $address ] ) ) );

	$title       = html_entity_decode( get_the_title( $post_id ), ENT_QUOTES, 'UTF-8' );
	$description = wp_strip_all_tags( get_the_excerpt( $post_id ) );
	$url         = get_permalink( $post_id );
	$uid         = 'event-' . $post_id . '@' . wp_parse_url( home_url(), PHP_URL_HOST );

	$tz      = wp_timezone();
	$dtstart = codeweber_ics_format_date( $date_start, $tz );
	$dtend   = codeweber_ics_format_date( $date_end, $tz );

	$lines = [
		'BEGIN:VCALENDAR',
		'VERSION:2.0',
		'PRODID:-//CodeWeber//Events//RU',
		'CALSCALE:GREGORIAN',
		'METHOD:PUBLISH',
		'BEGIN:VEVENT',
		'UID:' . $uid,
		'DTSTART:' . $dtstart,
		'DTEND:' . $dtend,
		'SUMMARY:' . codeweber_ics_escape( $title ),
	];

	if ( $description ) {
		$lines[] = 'DESCRIPTION:' . codeweber_ics_escape( $description );
	}
	if ( $full_loc ) {
		$lines[] = 'LOCATION:' . codeweber_ics_escape( $full_loc );
	}

	$lines[] = 'URL:' . $url;
	$lines[] = 'END:VEVENT';
	$lines[] = 'END:VCALENDAR';

	$ics      = implode( "\r\n", $lines ) . "\r\n";
	$filename = sanitize_title( $title ) . '.ics';

	header( 'Content-Type: text/calendar; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	header( 'Cache-Control: no-cache, no-store, must-revalidate' );
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $ics;
	exit;
}

function codeweber_ics_format_date( string $datetime, \DateTimeZone $tz ): string {
	$dt = new \DateTime( $datetime, $tz );
	$dt->setTimezone( new \DateTimeZone( 'UTC' ) );
	return $dt->format( 'Ymd\THis\Z' );
}

function codeweber_ics_escape( string $str ): string {
	return str_replace(
		[ '\\', ';', ',', "\r\n", "\n", "\r" ],
		[ '\\\\', '\;', '\,', '\n', '\n', '\n' ],
		$str
	);
}

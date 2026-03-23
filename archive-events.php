<?php
/**
 * Archive Template: Events
 *
 * Dispatches to templates/archives/events/{template}.php based on Redux setting.
 *
 * @package Codeweber
 */

get_header();
get_pageheader();

global $opt_name;
$templateloop = Redux::get_option( $opt_name, 'archive_template_select_events' );
if ( empty( $templateloop ) ) {
	$templateloop = 'events_1';
}

if ( locate_template( "templates/archives/events/{$templateloop}.php" ) ) {
	get_template_part( "templates/archives/events/{$templateloop}" );
} else {
	get_template_part( 'templates/archives/events/events_1' );
}

get_footer();

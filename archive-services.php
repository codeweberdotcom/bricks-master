<?php
/**
 * Archive Template: Services
 *
 * @package Codeweber
 */

get_header();
get_pageheader();

global $opt_name;
$templateloop = class_exists( 'Redux' ) ? Redux::get_option( $opt_name, 'archive_template_select_services' ) : '';
if ( empty( $templateloop ) || $templateloop === 'default' ) {
	$templateloop = 'services_1';
}

if ( locate_template( "templates/archives/services/{$templateloop}.php" ) ) {
	get_template_part( "templates/archives/services/{$templateloop}" );
} else {
	get_template_part( 'templates/archives/services/services_1' );
}

get_footer();

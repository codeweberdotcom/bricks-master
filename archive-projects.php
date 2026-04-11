<?php
/**
 * Archive Template: Projects
 *
 * @package Codeweber
 */

get_header();
get_pageheader();

global $opt_name;
$templateloop = class_exists( 'Redux' ) ? Redux::get_option( $opt_name, 'archive_template_select_projects' ) : '';
if ( empty( $templateloop ) || $templateloop === 'default' ) {
	$templateloop = 'projects_3';
}

if ( locate_template( "templates/archives/projects/{$templateloop}.php" ) ) {
	get_template_part( "templates/archives/projects/{$templateloop}" );
} else {
	get_template_part( 'templates/archives/projects/projects_3' );
}

get_footer();

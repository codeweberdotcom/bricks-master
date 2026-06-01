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

// If IT / Web is selected in Redux, delegate to Projects Settings for the specific template
if ( $templateloop === 'project-it' && function_exists( 'codeweber_projects_settings_get' ) ) {
	$it_template = codeweber_projects_settings_get( 'it_archive_template', 'projects_it_1' );
	if ( $it_template && locate_template( "templates/archives/projects/{$it_template}.php" ) ) {
		$templateloop = $it_template;
	} else {
		$templateloop = 'projects_it_1';
	}
}

if ( locate_template( "templates/archives/projects/{$templateloop}.php" ) ) {
	get_template_part( "templates/archives/projects/{$templateloop}" );
} else {
	get_template_part( 'templates/archives/projects/projects_3' );
}

get_footer();

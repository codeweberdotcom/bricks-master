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

// IT override: if site is configured as IT/Web, use IT-specific archive template if set
if ( function_exists( 'codeweber_projects_settings_get' ) ) {
	$default_type = codeweber_projects_settings_get( 'default_template', 'project-construction' );
	if ( $default_type === 'project-it' ) {
		$it_template = codeweber_projects_settings_get( 'it_archive_template', '' );
		if ( $it_template && locate_template( "templates/archives/projects/{$it_template}.php" ) ) {
			$templateloop = $it_template;
		}
	}
}

if ( locate_template( "templates/archives/projects/{$templateloop}.php" ) ) {
	get_template_part( "templates/archives/projects/{$templateloop}" );
} else {
	get_template_part( 'templates/archives/projects/projects_3' );
}

get_footer();

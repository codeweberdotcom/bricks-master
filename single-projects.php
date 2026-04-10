<?php
/**
 * Single Projects
 *
 * Шаблон без контейнера — секции управляют шириной сами.
 * Шаблон контента выбирается в Redux: single_template_select_projects.
 *
 * @package Codeweber
 */

get_header();

while ( have_posts() ) :
	the_post();
	get_pageheader();

	global $opt_name;
	if ( empty( $opt_name ) ) {
		$opt_name = 'redux_demo';
	}

	$post_type      = 'projects';
	$templatesingle = class_exists( 'Redux' ) ? Redux::get_option( $opt_name, 'single_template_select_' . $post_type ) : '';
	$template_file  = "templates/singles/{$post_type}/{$templatesingle}.php";

	if ( ! empty( $templatesingle ) && locate_template( $template_file ) ) {
		get_template_part( "templates/singles/{$post_type}/{$templatesingle}" );
	} else {
		get_template_part( "templates/singles/{$post_type}/default" );
	}

	do_action( 'after_single_post', $post_type );

endwhile;

get_footer();

<?php
function cptui_register_my_cpts_projects()
{
	/**
	 * Post Type: Projects.
	 */

	$labels = [
		"name" => esc_html__("Projects", "codeweber"),
		"singular_name" => esc_html__("Project", "codeweber"),
		"menu_name" => esc_html__("Projects", "codeweber"),
		"all_items" => esc_html__("All Projects", "codeweber"),
		"add_new" => esc_html__("Add Project", "codeweber"),
		"add_new_item" => esc_html__("Add New Project", "codeweber"),
		"edit_item" => esc_html__("Edit Project", "codeweber"),
		"new_item" => esc_html__("New Project", "codeweber"),
		"view_item" => esc_html__("View Project", "codeweber"),
		"view_items" => esc_html__("View Projects", "codeweber"),
		"search_items" => esc_html__("Search Projects", "codeweber"),
		"not_found" => esc_html__("Projects Not Found", "codeweber"),
		"not_found_in_trash" => esc_html__("Projects Not Found in Trash", "codeweber"),
		"parent" => esc_html__("Parent Project", "codeweber"),
		"archives" => esc_html__("Projects Archives", "codeweber"),
		"item_updated" => esc_html__("Project Updated", "codeweber"),
		"parent_item_colon" => esc_html__("Parent Project:", "codeweber"),
	];

	$args = [
		"label" => esc_html__("Projects", "codeweber"),
		"labels" => $labels,
		"description" => esc_html__("This module allows you to display your work or cases on the website.", "codeweber"),
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"rest_namespace" => "wp/v2",
		"has_archive" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"can_export" => true,
		"rewrite" => ["slug" => "projects", "with_front" => true],
		"query_var" => true,
		"supports" => ["title", "editor", "thumbnail", "comments", "revisions", "author", "page-attributes"],
		"taxonomies" => ["projects_category"],
		"show_in_graphql" => false,
	];

	register_post_type("projects", $args);
}

add_action('init', 'cptui_register_my_cpts_projects');



function cptui_register_my_taxes_projects_category()
{

	/**
	 * Taxonomy: Project Categories.
	 */

	$labels = [
		"name" => esc_html__("Project Categories", "codeweber"),
		"singular_name" => esc_html__("Project Category", "codeweber"),
	];

	$args = [
		"label" => esc_html__("Project Categories", "codeweber"),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,  // Changed to hierarchical for better categorization
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => ['slug' => 'projects_category', 'with_front' => true],
		"show_admin_column" => true,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "projects_category",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => true,
		"show_in_graphql" => false,
	];
	register_taxonomy("projects_category", ["projects"], $args);
}
add_action('init', 'cptui_register_my_taxes_projects_category');

// Project type templates (registered programmatically, no PHP files needed)
add_filter( 'theme_projects_templates', function ( array $templates ): array {
	return [
		'project-it'           => __( 'IT / Web', 'codeweber' ),
		'project-design'       => __( 'Design Studio', 'codeweber' ),
		'project-construction' => __( 'Construction', 'codeweber' ),
		'project-photo'        => __( 'Photography', 'codeweber' ),
	];
} );

// Block editor per type (based on settings + _wp_page_template meta)
add_filter( 'use_block_editor_for_post', function ( bool $enabled, WP_Post $post ): bool {
	if ( $post->post_type !== 'projects' ) {
		return $enabled;
	}
	$gutenberg_types = (array) codeweber_projects_settings_get( 'gutenberg_types', [] );
	if ( empty( $gutenberg_types ) ) {
		return false;
	}
	$type = get_post_meta( $post->ID, '_wp_page_template', true );
	if ( empty( $type ) || $type === 'default' ) {
		$type = 'project-construction';
	}
	return in_array( $type, $gutenberg_types, true );
}, 10, 2 );

// Admin column: Type
add_filter( 'manage_projects_posts_columns', function ( array $columns ): array {
	$new = [];
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( $key === 'title' ) {
			$new['project_type'] = __( 'Type', 'codeweber' );
		}
	}
	return $new;
} );

add_action( 'manage_projects_posts_custom_column', function ( string $column, int $post_id ): void {
	if ( $column !== 'project_type' ) {
		return;
	}
	$type   = get_post_meta( $post_id, '_wp_page_template', true );
	$labels = [
		'project-it'           => __( 'IT / Web', 'codeweber' ),
		'project-design'       => __( 'Design Studio', 'codeweber' ),
		'project-construction' => __( 'Construction', 'codeweber' ),
		'project-photo'        => __( 'Photography', 'codeweber' ),
	];
	if ( empty( $type ) || $type === 'default' ) {
		$type = codeweber_projects_settings_get( 'default_template', 'project-construction' );
	}
	echo esc_html( $labels[ $type ] ?? $type );
}, 10, 2 );

// Two-column grid + hide classic editor textarea when Gutenberg is off
add_action( 'admin_enqueue_scripts', function ( string $hook ): void {
	if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || $screen->post_type !== 'projects' ) {
		return;
	}

	$post_id         = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
	$type            = $post_id
		? cw_project_get_type( $post_id )
		: codeweber_projects_settings_get( 'default_template', 'project-construction' );
	$gutenberg_types = (array) codeweber_projects_settings_get( 'gutenberg_types', [] );
	$gutenberg_on    = in_array( $type, $gutenberg_types, true );

	$css = '
		#normal-sortables {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 0 16px;
			align-items: start;
		}
		#normal-sortables .postbox {
			min-width: 0;
		}
	';

	if ( ! $gutenberg_on ) {
		$css .= '#postdivrich, #wp-content-wrap { display: none !important; }';
	}

	wp_add_inline_style( 'wp-admin', $css );
} );

require_once __DIR__ . '/cpt-projects-meta.php';
require_once get_template_directory() . '/functions/admin/projects-settings.php';

function codeweber_projects_archive_query( $query ) {
	if ( ! is_admin() && $query->is_main_query() && is_post_type_archive( 'projects' ) ) {
		global $opt_name;
		$template = class_exists( 'Redux' ) ? Redux::get_option( $opt_name, 'archive_template_select_projects' ) : '';
		if ( empty( $template ) || $template === 'default' ) {
			$template = 'projects_3';
		}
		if ( in_array( $template, [ 'projects_1', 'projects_2' ], true ) ) {
			$query->set( 'posts_per_page', 12 );
		}
	}
}
add_action( 'pre_get_posts', 'codeweber_projects_archive_query' );

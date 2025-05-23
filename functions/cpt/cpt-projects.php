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
		"supports" => ["title", "editor", "thumbnail", "comments", "revisions", "author"],
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

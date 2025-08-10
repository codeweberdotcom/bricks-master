<?php

function cptui_register_my_cpts_modal()
{
	/**
	 * Post Type: Modals.
	 */

	$labels = [
		"name" => esc_html__("Modals", "codeweber"),
		"singular_name" => esc_html__("Modal", "codeweber"),
		"menu_name" => esc_html__("Modals", "codeweber"),
		"all_items" => esc_html__("All Modals", "codeweber"),
		"add_new" => esc_html__("Add New", "codeweber"),
		"add_new_item" => esc_html__("Add New Modal", "codeweber"),
		"edit_item" => esc_html__("Edit Modal", "codeweber"),
		"new_item" => esc_html__("New Modal", "codeweber"),
		"view_item" => esc_html__("View Modal", "codeweber"),
		"search_items" => esc_html__("Search Modals", "codeweber"),
		"not_found" => esc_html__("No modals found", "codeweber"),
		"not_found_in_trash" => esc_html__("No modals found in Trash", "codeweber"),
	];

	$args = [
		"label" => esc_html__("Modals", "codeweber"),
		"labels" => $labels,
		"description" => esc_html__("Post type for modal windows", "codeweber"),
		"public" => false,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"rest_namespace" => "wp/v2",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"can_export" => true,
		"rewrite" => ["slug" => "modal", "with_front" => true],
		"query_var" => true,
		"supports" => ["title", "editor"],
		"show_in_graphql" => false,
	];

	register_post_type("modal", $args);
}

add_action('init', 'cptui_register_my_cpts_modal');
<?php

function cptui_register_my_cpts_clients()
{

	/**
	 * Post Type: Clients.
	 */

	$labels = [
		"name" => esc_html__("Clients", "codeweber"),
		"singular_name" => esc_html__("Client", "codeweber"),
		"menu_name" => esc_html__("Clients", "codeweber"),
		"all_items" => esc_html__("All Clients", "codeweber"),
		"add_new" => esc_html__("Add New Client", "codeweber"),
		"add_new_item" => esc_html__("Add Client", "codeweber"),
		"edit_item" => esc_html__("Edit Client", "codeweber"),
		"new_item" => esc_html__("New Client", "codeweber"),
		"view_item" => esc_html__("View Client", "codeweber"),
		"view_items" => esc_html__("View Clients", "codeweber"),
		"search_items" => esc_html__("Search Client", "codeweber"),
		"not_found" => esc_html__("No Client", "codeweber"),
		"items_list" => esc_html__("Clients list", "codeweber"),
		"name_admin_bar" => esc_html__("Client", "codeweber"),
		"item_published" => esc_html__("Client puplished", "codeweber"),
		"item_updated" => esc_html__("Client updated", "codeweber"),
	];

	$args = [
		"label" => esc_html__("Clients", "codeweber"),
		"labels" => $labels,
		"description" => "",
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
		"exclude_from_search" => true,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"can_export" => true,
		"rewrite" => ["slug" => "clients", "with_front" => true],
		"query_var" => true,
		"supports" => ["title"],
		"show_in_graphql" => false,
	];

	register_post_type("clients", $args);
}

add_action('init', 'cptui_register_my_cpts_clients');

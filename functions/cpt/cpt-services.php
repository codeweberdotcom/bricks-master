<?php

function cptui_register_my_cpts_services()
{

	/**
	 * Post Type: Services.
	 */

	$labels = [
		"name" => esc_html__("Services", "codeweber"),
		"singular_name" => esc_html__("Service", "codeweber"),
		"menu_name" => esc_html__("Services", "codeweber"),
		"all_items" => esc_html__("Services", "codeweber"),
		"add_new" => esc_html__("Add Service", "codeweber"),
		"add_new_item" => esc_html__("Add New Service", "codeweber"),
		"edit_item" => esc_html__("Edit Service", "codeweber"),
		"new_item" => esc_html__("New Service", "codeweber"),
		"view_item" => esc_html__("View Service", "codeweber"),
		"view_items" => esc_html__("View Services", "codeweber"),
		"search_items" => esc_html__("Search Service", "codeweber"),
		"not_found" => esc_html__("(e.g. No Service found)", "codeweber"),
		"not_found_in_trash" => esc_html__("(e.g. No Service found in Trash)", "codeweber"),
		"parent" => esc_html__("Parent Service", "codeweber"),
		"featured_image" => esc_html__("Featured Image for this service", "codeweber"),
		"set_featured_image" => esc_html__("Set featured Image for this service", "codeweber"),
		"remove_featured_image" => esc_html__("Remove featured Image for this service", "codeweber"),
		"use_featured_image" => esc_html__("Use as featured image", "codeweber"),
		"archives" => esc_html__("Service archive", "codeweber"),
		"items_list" => esc_html__("Service list", "codeweber"),
		"name_admin_bar" => esc_html__("Service", "codeweber"),
		"item_published" => esc_html__("Service published", "codeweber"),
		"item_reverted_to_draft" => esc_html__("Service reverted to draft", "codeweber"),
		"item_scheduled" => esc_html__("Service scheduled", "codeweber"),
		"item_updated" => esc_html__("Service updated", "codeweber"),
		"parent_item_colon" => esc_html__("Parent Service", "codeweber"),

	];

	$args = [
		"label" => __("Services", "codeweber"),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"has_archive" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => true,
		"can_export" => true,
		"rewrite" => ["slug" => "services", "with_front" => true],
		"query_var" => true,
		"supports" => ["title", "editor", "thumbnail", "excerpt", "revisions", "comments"],
		"taxonomies" => ["service_category", "types_of_services"],
		"show_in_graphql" => false,
	];

	register_post_type("services", $args);
}

add_action('init', 'cptui_register_my_cpts_services');
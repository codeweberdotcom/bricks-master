<?php

function cptui_register_my_cpts_staff()
{

	/**
	 * Post Type: Staff.
	 */

	$labels = [
		"name" => esc_html__("Staff", "codeweber"),
		"singular_name" => esc_html__("Staff", "codeweber"),
		"menu_name" => esc_html__("Staff", "codeweber"),
		"all_items" => esc_html__("All Staff", "codeweber"),
		"add_new" => esc_html__("Add Staff", "codeweber"),
		"add_new_item" => esc_html__("Add New Staff", "codeweber"),
		"edit_item" => esc_html__("Edit Staff", "codeweber"),
		"new_item" => esc_html__("New Staff", "codeweber"),
		"view_item" => esc_html__("View Staff", "codeweber"),
		"view_items" => esc_html__("View Staff", "codeweber"),
		"search_items" => esc_html__("Search Staff", "codeweber"),
		"not_found" => esc_html__("(e.g. No Staff found)", "codeweber"),
		"not_found_in_trash" => esc_html__("(e.g. No Staff found in Trash)", "codeweber"),
		"parent" => esc_html__("Parent Staff", "codeweber"),
		"featured_image" => esc_html__("Featured Image for this staff", "codeweber"),
		"set_featured_image" => esc_html__("Set featured Image for this staff", "codeweber"),
		"remove_featured_image" => esc_html__("Remove featured Image for this staff", "codeweber"),
		"use_featured_image" => esc_html__("Use as featured image", "codeweber"),
		"archives" => esc_html__("Staff archive", "codeweber"),
		"items_list" => esc_html__("Staff list", "codeweber"),
		"name_admin_bar" => esc_html__("Staff", "codeweber"),
		"item_published" => esc_html__("Staff published", "codeweber"),
		"item_reverted_to_draft" => esc_html__("Staff reverted to draft", "codeweber"),
		"item_scheduled" => esc_html__("Staff scheduled", "codeweber"),
		"item_updated" => esc_html__("Staff updated", "codeweber"),
		"parent_item_colon" => esc_html__("Parent Staff", "codeweber"),
	];

	$args = [
		"label" => esc_html__("Staff", "codeweber"),
		"labels" => $labels,
		"description" => "",
		"public" => true,
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
		"rewrite" => ["slug" => "staff", "with_front" => true],
		"query_var" => true,
		"supports" => ["title", "thumbnail", "excerpt", "revisions"],
		"show_in_graphql" => false,
	];

	register_post_type("staff", $args);
}

add_action('init', 'cptui_register_my_cpts_staff');

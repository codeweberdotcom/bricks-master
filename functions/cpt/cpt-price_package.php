<?php

function cptui_register_my_cpts_price()
{

	/**
	 * Post Type: Price Packages.
	 */

	$labels = [
		"name" => esc_html__("Price Packages", "codeweber"),
		"singular_name" => esc_html__("Price Package", "codeweber"),
		"menu_name" => esc_html__("Price Packages", "codeweber"),
		"all_items" => esc_html__("All Price Packages", "codeweber"),
		"add_new" => esc_html__("Add New Price Package", "codeweber"),
		"add_new_item" => esc_html__("Add New Price Package", "codeweber"),
		"edit_item" => esc_html__("Edit Price Package", "codeweber"),
		"new_item" => esc_html__("New Price Package", "codeweber"),
		"view_item" => esc_html__("View Price Package", "codeweber"),
		"view_items" => esc_html__("View Price Packages", "codeweber"),
		"search_items" => esc_html__("Search Price Packages", "codeweber"),
		"not_found" => esc_html__("No Price Packages found", "codeweber"),
		"not_found_in_trash" => esc_html__("No Price Packages found in Trash", "codeweber"),
		"parent" => esc_html__("Parent Price Package:", "codeweber"),
		"featured_image" => esc_html__("Featured image for this Price Package", "codeweber"),
		"set_featured_image" => esc_html__("Set Featured Image for this Price Package", "codeweber"),
		"remove_featured_image" => esc_html__("Remove Featured Image for this Price Package", "codeweber"),
		"use_featured_image" => esc_html__("Use as Featured Image for this Price Package", "codeweber"),
		"archives" => esc_html__("Price Package Archives", "codeweber"),
		"insert_into_item" => esc_html__("Insert into Price Package", "codeweber"),
		"uploaded_to_this_item" => esc_html__("Upload to this Price Package", "codeweber"),
		"filter_items_list" => esc_html__("Filter Price Packages", "codeweber"),
		"items_list_navigation" => esc_html__("Price Packages Navigation", "codeweber"),
		"items_list" => esc_html__("Price Packages List", "codeweber"),
		"attributes" => esc_html__("Price Package Attributes", "codeweber"),
		"name_admin_bar" => esc_html__("Price Package", "codeweber"),
		"item_published" => esc_html__("Price Package Published", "codeweber"),
		"item_published_privately" => esc_html__("Price Package Published Privately", "codeweber"),
		"item_reverted_to_draft" => esc_html__("Price Package Reverted to Draft", "codeweber"),
		"item_scheduled" => esc_html__("Price Package Scheduled", "codeweber"),
		"item_updated" => esc_html__("Price Package Updated", "codeweber"),
		"parent_item_colon" => esc_html__("Parent Price Package:", "codeweber"),
	];

	$args = [
		"label" => esc_html__("Price Packages", "codeweber"),
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
		"can_export" => false,
		"rewrite" => ["slug" => "price", "with_front" => true],
		"query_var" => true,
		"supports" => ["title"],
		"show_in_graphql" => false,
	];

	register_post_type("price", $args);
}

add_action('init', 'cptui_register_my_cpts_price');

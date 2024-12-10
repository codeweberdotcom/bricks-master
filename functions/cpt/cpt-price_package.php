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
		"add_new" => esc_html__("Add Price Packages", "codeweber"),
		"add_new_item" => esc_html__("Add New Price Package", "codeweber"),
		"edit_item" => esc_html__("Edit Price Package", "codeweber"),
		"new_item" => esc_html__("New Price Package", "codeweber"),
		"view_item" => esc_html__("View Price Package", "codeweber"),
		"view_items" => esc_html__("View Price Packages", "codeweber"),
		"search_items" => esc_html__("Search Price Package", "codeweber"),
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

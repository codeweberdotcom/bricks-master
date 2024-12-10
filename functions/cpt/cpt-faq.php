<?php

function cptui_register_my_cpts_faq()
{

	/**
	 * Post Type: Faq.
	 */

	$labels = [
		"name" => esc_html__("Faq", "codeweber"),
		"singular_name" => esc_html__("Faq", "codeweber"),
		"menu_name" => esc_html__("FAQ", "codeweber"),
	];

	$args = [
		"label" => esc_html__("Faq", "codeweber"),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => false,
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
		"rewrite" => ["slug" => "faq", "with_front" => true],
		"query_var" => true,
		"supports" => ["title",  "comments", "revisions", "author"],
		"show_in_graphql" => false,
	];

	register_post_type("faq", $args);
}

add_action('init', 'cptui_register_my_cpts_faq');

<?php
function cptui_register_my_taxes_projects_category()
{

	/**
	 * Taxonomy: Категории проектов.
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
		"hierarchical" => false,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => ['slug' => 'projects_category', 'with_front' => true,],
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

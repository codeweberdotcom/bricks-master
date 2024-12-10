<?php

function cptui_register_my_taxes_service_category()
{

	/**
	 * Taxonomy: Service Categories.
	 */

	$labels = [
		"name" => __("Service Categories", "codeweber"),
		"singular_name" => __("Service Category", "codeweber"),
	];


	$args = [
		"label" => __("Service Categories", "codeweber"),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => false,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => ['slug' => 'service_category', 'with_front' => true,],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "service_category",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"show_in_quick_edit" => false,
		"sort" => false,
		"show_in_graphql" => false,
	];
	register_taxonomy("service_category", ["services"], $args);
}
add_action('init', 'cptui_register_my_taxes_service_category');


/**/

function cptui_register_my_taxes_types_of_services()
{

	/**
	 * Taxonomy: Types.
	 */

	$labels = [
		"name" => __("Types", "codeweber"),
		"singular_name" => __("Type", "codeweber"),
	];


	$args = [
		"label" => __("Types", "codeweber"),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => false,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => ['slug' => 'types_of_services', 'with_front' => true,],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "types_of_services",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"show_in_quick_edit" => false,
		"sort" => false,
		"show_in_graphql" => false,
	];
	register_taxonomy("types_of_services", ["services"], $args);
}
add_action('init', 'cptui_register_my_taxes_types_of_services');

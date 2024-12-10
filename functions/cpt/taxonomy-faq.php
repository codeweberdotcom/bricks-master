<?php

function cptui_register_my_taxes_faq_categories()
{

	/**
	 * Taxonomy: Faq categories.
	 */

	$labels = [
		"name" => esc_html__("Faq categories", "codeweber"),
		"singular_name" => esc_html__("Faq category", "codeweber"),
	];


	$args = [
		"label" => esc_html__("Faq categories", "codeweber"),
		"labels" => $labels,
		"public" => false,
		"publicly_queryable" => true,
		"hierarchical" => false,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => ['slug' => 'faq_categories', 'with_front' => true,],
		"show_admin_column" => true,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "faq_categories",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => false,
		"sort" => false,
		"show_in_graphql" => false,
	];
	register_taxonomy("faq_categories", ["faq"], $args);
}
add_action('init', 'cptui_register_my_taxes_faq_categories');




function cptui_register_my_taxes_faq_tag()
{

	/**
	 * Taxonomy: FAQ Tags.
	 */

	$labels = [
		"name" => esc_html__("FAQ Tags", "codeweber"),
		"singular_name" => esc_html__("FAQ Tags", "codeweber"),
	];


	$args = [
		"label" => esc_html__("FAQ Tags", "codeweber"),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => false,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => ['slug' => 'faq_tag', 'with_front' => true,],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "faq_tag",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => false,
		"sort" => false,
		"show_in_graphql" => false,
	];
	register_taxonomy("faq_tag", ["faq"], $args);
}
add_action('init', 'cptui_register_my_taxes_faq_tag');

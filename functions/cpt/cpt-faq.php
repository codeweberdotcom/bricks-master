<?php

function cptui_register_my_cpts_faq()
{
	/**
	 * Post Type: FAQ.
	 */
	$labels = [
		"name" => esc_html__("FAQs", "codeweber"),
		"singular_name" => esc_html__("FAQ", "codeweber"),
		"menu_name" => esc_html__("Faq", "codeweber"),
		"add_new" => esc_html__("Add New FAQ", "codeweber"),
		"add_new_item" => esc_html__("Add New FAQ", "codeweber"),
		"edit_item" => esc_html__("Edit FAQ", "codeweber"),
		"new_item" => esc_html__("New FAQ", "codeweber"),
		"view_item" => esc_html__("View FAQ", "codeweber"),
		"view_items" => esc_html__("View FAQs", "codeweber"),
		"search_items" => esc_html__("Search FAQs", "codeweber"),
		"not_found" => esc_html__("No FAQs found", "codeweber"),
		"not_found_in_trash" => esc_html__("No FAQs found in Trash", "codeweber"),
		"all_items" => esc_html__("All FAQs", "codeweber"),
		"archives" => esc_html__("FAQ Archives", "codeweber"),
		"insert_into_item" => esc_html__("Insert into FAQ", "codeweber"),
		"uploaded_to_this_item" => esc_html__("Uploaded to this FAQ", "codeweber"),
	];

	$args = [
		"label" => esc_html__("FAQ", "codeweber"),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "faqs",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"has_archive" => true,
		"show_in_menu" => true,
		"rewrite" => ["slug" => "faq", "with_front" => true],
		"supports" => ["title", "editor", "comments", "revisions", "author"],
	];
	register_post_type("faq", $args);
}
add_action('init', 'cptui_register_my_cpts_faq');

function cptui_register_my_taxes_faq_categories()
{
	/**
	 * Taxonomy: FAQ Categories.
	 */
	$labels = [
		"name" => esc_html__("FAQ Categories", "codeweber"),
		"singular_name" => esc_html__("FAQ Category", "codeweber"),
		"menu_name" => esc_html__("FAQ Categories", "codeweber"),
		"all_items" => esc_html__("All FAQ Categories", "codeweber"),
		"edit_item" => esc_html__("Edit FAQ Category", "codeweber"),
		"view_item" => esc_html__("View FAQ Category", "codeweber"),
		"add_new_item" => esc_html__("Add New FAQ Category", "codeweber"),
		"new_item_name" => esc_html__("New FAQ Category Name", "codeweber"),
		"search_items" => esc_html__("Search FAQ Categories", "codeweber"),
		"not_found" => esc_html__("No FAQ Categories Found", "codeweber"),
	];

	$args = [
		"label" => esc_html__("FAQ Categories", "codeweber"),
		"labels" => $labels,
		"public" => false,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "faq_categories",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rewrite" => ["slug" => "faq-categories", "with_front" => true],
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
		"singular_name" => esc_html__("FAQ Tag", "codeweber"),
		"menu_name" => esc_html__("FAQ Tags", "codeweber"),
		"all_items" => esc_html__("All FAQ Tags", "codeweber"),
		"edit_item" => esc_html__("Edit FAQ Tag", "codeweber"),
		"view_item" => esc_html__("View FAQ Tag", "codeweber"),
		"update_item" => esc_html__("Update FAQ Tag", "codeweber"),
		"add_new_item" => esc_html__("Add New FAQ Tag", "codeweber"),
		"new_item_name" => esc_html__("New FAQ Tag Name", "codeweber"),
		"search_items" => esc_html__("Search FAQ Tags", "codeweber"),
		"not_found" => esc_html__("No FAQ Tags Found", "codeweber"),
	];

	$args = [
		"label" => esc_html__("FAQ Tags", "codeweber"),
		"labels" => $labels,
		"public" => true,
		"hierarchical" => false,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "faq_tag",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rewrite" => ["slug" => "faq-tag", "with_front" => true],
	];
	register_taxonomy("faq_tag", ["faq"], $args);
}
add_action('init', 'cptui_register_my_taxes_faq_tag');

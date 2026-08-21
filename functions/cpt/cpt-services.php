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
		"supports" => ["title", "editor", "thumbnail", "excerpt", "revisions", "comments", "page-attributes"],
		"taxonomies" => ["service_category", "types_of_services"],
		"show_in_graphql" => false,
	];

	register_post_type("services", $args);
}

add_action('init', 'cptui_register_my_cpts_services');

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
		"rewrite" => ['slug' => 'service_category', 'with_front' => true],
		"show_admin_column" => true,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "service_category",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => false,
	];

	register_taxonomy("service_category", ["services"], $args);
}

add_action('init', 'cptui_register_my_taxes_service_category');

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
		"rewrite" => ['slug' => 'types_of_services', 'with_front' => true],
		"show_admin_column" => true,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "types_of_services",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => false,
	];

	register_taxonomy("types_of_services", ["services"], $args);
}

add_action('init', 'cptui_register_my_taxes_types_of_services');

/**
 * Фильтры по таксономиям над таблицей услуг.
 */
function codeweber_services_taxonomy_filters()
{
	global $typenow;

	if ($typenow !== 'services') {
		return;
	}

	$filters = [
		'service_category'  => __('All Service Categories', 'codeweber'),
		'types_of_services' => __('All Types', 'codeweber'),
	];

	foreach ($filters as $taxonomy => $show_all_label) {
		if (!taxonomy_exists($taxonomy)) {
			continue;
		}

		$taxonomy_object = get_taxonomy($taxonomy);
		$selected = isset($_GET[$taxonomy]) ? sanitize_text_field(wp_unslash($_GET[$taxonomy])) : '';

		wp_dropdown_categories([
			'show_option_all' => $show_all_label,
			'taxonomy'        => $taxonomy,
			'name'            => $taxonomy,
			'value_field'     => 'slug',
			'selected'        => $selected,
			'hierarchical'    => $taxonomy_object ? (bool) $taxonomy_object->hierarchical : false,
			'show_count'      => true,
			'hide_empty'      => false,
			'orderby'         => 'name',
		]);
	}
}
add_action('restrict_manage_posts', 'codeweber_services_taxonomy_filters');


// Meta-поля, относящиеся к services. Подключаются только при активном CPT,
// чтобы соответствовать Redux-свитчеру `cpt_switch_services`.
require_once __DIR__ . '/services-meta-fields.php';

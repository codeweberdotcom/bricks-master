<?php
function cptui_register_my_cpts_page_header()
{

   /**
    * Post Type: Page Header.
    */

   $labels = [
      "name" => esc_html__("Page Headers", "codeweber"),
      "singular_name" => esc_html__("Page Header", "codeweber"),
      "menu_name" => esc_html__("Page Headers", "codeweber"),
      "add_new" => esc_html__("Add New Page Header", "codeweber"),
      "add_new_item" => esc_html__("Add New Page Header", "codeweber"),
      "edit_item" => esc_html__("Edit Page Header", "codeweber"),
      "new_item" => esc_html__("New Page Header", "codeweber"),
      "view_item" => esc_html__("View Page Header", "codeweber"),
      "view_items" => esc_html__("View Page Headers", "codeweber"),
      "search_items" => esc_html__("Search Page Headers", "codeweber"),
      "not_found" => esc_html__("No Page Headers found", "codeweber"),
      "not_found_in_trash" => esc_html__("No Page Headers found in Trash", "codeweber"),
      "parent" => esc_html__("Parent Page Header", "codeweber"),
   ];

   $args = [
      "label" => esc_html__("Page Header", "codeweber"),
      "labels" => $labels,
      "description" => "",
      "public" => true,
      "publicly_queryable" => false,
      "show_ui" => true,
      "show_in_rest" => true,
      "rest_base" => "",
      "rest_controller_class" => "WP_REST_Posts_Controller",
      "has_archive" => false, // Disable archive
      "show_in_menu" => true,
      "show_in_nav_menus" => false,
      "delete_with_user" => false,
      "exclude_from_search" => true,
      "capability_type" => "post",
      "map_meta_cap" => true,
      "hierarchical" => false, // Without parents
      "can_export" => true,
      "rewrite" => false,
      "query_var" => false,
      "supports" => ["title", "editor"],
      "show_in_graphql" => false,
   ];

   register_post_type("page-header", $args);
}

add_action('init', 'cptui_register_my_cpts_page_header');

/**
 * Запрет отображения Single и Archive Page Header на фронтенде — отдаём 404.
 */
add_action('template_redirect', function () {
	if (is_singular('page-header') || is_post_type_archive('page-header')) {
		global $wp_query;
		$wp_query->set_404();
		status_header(404);
	}
});

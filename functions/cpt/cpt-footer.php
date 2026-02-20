<?php
function cptui_register_my_cpts_footer()
{

   /**
    * Post Type: Footer.
    */

   $labels = [
      "name" => esc_html__("Footer", "codeweber"),
      "singular_name" => esc_html__("Footer", "codeweber"),
      "menu_name" => esc_html__("Footer", "codeweber"),
      "add_new" => esc_html__("Add New Footer", "codeweber"),
      "add_new_item" => esc_html__("Add New Footer", "codeweber"),
      "edit_item" => esc_html__("Edit Footer", "codeweber"),
      "new_item" => esc_html__("New Footer", "codeweber"),
      "view_item" => esc_html__("View Footer", "codeweber"),
      "view_items" => esc_html__("View Footers", "codeweber"),
      "search_items" => esc_html__("Search Footers", "codeweber"),
      "not_found" => esc_html__("No footers found", "codeweber"),
      "not_found_in_trash" => esc_html__("No footers found in Trash", "codeweber"),
      "parent" => esc_html__("Parent Footer", "codeweber"),
   ];

   $args = [
      "label" => esc_html__("Footer", "codeweber"),
      "labels" => $labels,
      "description" => "",
      "public" => true,
      "publicly_queryable" => false, // Не отображать Single и Archive на фронтенде
      "show_ui" => true,
      "show_in_rest" => true,
      "rest_base" => "",
      "rest_controller_class" => "WP_REST_Posts_Controller",
      "has_archive" => false,
      "show_in_menu" => true,
      "show_in_nav_menus" => true,
      "delete_with_user" => false,
      "exclude_from_search" => true,
      "capability_type" => "post",
      "map_meta_cap" => true,
      "hierarchical" => false,
      "can_export" => true,
      "rewrite" => false, // Отключаем URL на фронтенде
      "query_var" => false,
      "supports" => ["title", "editor"],
      "show_in_graphql" => false,
   ];

   register_post_type("footer", $args);
}

add_action('init', 'cptui_register_my_cpts_footer');

/**
 * Запрет отображения Single и Archive Footer на фронтенде — отдаём 404.
 */
add_action('template_redirect', function () {
	if (is_singular('footer') || is_post_type_archive('footer')) {
		global $wp_query;
		$wp_query->set_404();
		status_header(404);
	}
});

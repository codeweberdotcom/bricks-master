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
      "publicly_queryable" => true,
      "show_ui" => true,
      "show_in_rest" => true,
      "rest_base" => "",
      "rest_controller_class" => "WP_REST_Posts_Controller",
      "has_archive" => false, // Отключаем архив
      "show_in_menu" => true,
      "show_in_nav_menus" => true,
      "delete_with_user" => false,
      "exclude_from_search" => false,
      "capability_type" => "post",
      "map_meta_cap" => true,
      "hierarchical" => false, // Без родителей
      "can_export" => true,
      "rewrite" => ["slug" => "page-header", "with_front" => true],
      "query_var" => true,
      "supports" => ["title", "editor"],
      "show_in_graphql" => false,
   ];

   register_post_type("page-header", $args);
}

add_action('init', 'cptui_register_my_cpts_page_header');

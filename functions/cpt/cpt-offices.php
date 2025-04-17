<?php

function cptui_register_my_cpts()
{

   /**
    * Post Type: Offices.
    */

   $labels = [
      "name" => esc_html__("Offices", "codeweber"),
      "singular_name" => esc_html__("Office", "codeweber"),
      "menu_name" => esc_html__("Offices", "codeweber"),
      "all_items" => esc_html__("All Offices", "codeweber"),
      "add_new" => esc_html__("Add New Office", "codeweber"),
      "add_new_item" => esc_html__("Add New Office", "codeweber"),
      "edit_item" => esc_html__("Edit Office", "codeweber"),
      "new_item" => esc_html__("New Office", "codeweber"),
      "view_item" => esc_html__("View Office", "codeweber"),
      "view_items" => esc_html__("View Offices", "codeweber"),
      "search_items" => esc_html__("Search Offices", "codeweber"),
      "not_found" => esc_html__("No offices found", "codeweber"),
      "not_found_in_trash" => esc_html__("No offices found in Trash", "codeweber"),
      "parent_item_colon" => esc_html__("Parent Office:", "codeweber"),
   ];

   $args = [
      "label" => esc_html__("Offices", "codeweber"),
      "labels" => $labels,
      "description" => esc_html__("A custom post type for managing office locations", "codeweber"),
      "public" => false,
      "publicly_queryable" => false,
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
      "can_export" => true,
      "rewrite" => ["slug" => "offices", "with_front" => true],
      "query_var" => true,
      "supports" => ["title"],
      "show_in_graphql" => false,
   ];

   register_post_type("offices", $args);
}

add_action('init', 'cptui_register_my_cpts');

function cptui_register_my_taxes_towns()
{

   /**
    * Taxonomy: Towns.
    */

   $labels = [
      "name" => esc_html__("Towns", "codeweber"),
      "singular_name" => esc_html__("Town", "codeweber"),
   ];

   $args = [
      "label" => esc_html__("Towns", "codeweber"),
      "labels" => $labels,
      "public" => true,
      "publicly_queryable" => false,
      "hierarchical" => false,
      "show_ui" => true,
      "show_in_menu" => true,
      "show_in_nav_menus" => true,
      "query_var" => true,
      "rewrite" => ['slug' => 'towns', 'with_front' => true],
      "show_admin_column" => true,
      "show_in_rest" => true,
      "show_tagcloud" => false,
      "rest_base" => "towns",
      "rest_controller_class" => "WP_REST_Terms_Controller",
      "rest_namespace" => "wp/v2",
      "show_in_quick_edit" => true,
      "sort" => true,
      "show_in_graphql" => false,
   ];

   register_taxonomy("towns", ["offices"], $args);
}

add_action('init', 'cptui_register_my_taxes_towns');

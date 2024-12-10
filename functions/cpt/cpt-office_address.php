<?php

function cptui_register_my_cpts()
{

   /**
    * Post Type: Office addresses.
    */

   $labels = [
      "name" => esc_html__("Office addresses", "codeweber"),
      "singular_name" => esc_html__("Office address", "codeweber"),
      "menu_name" => esc_html__("Office addresses", "codeweber"),
   ];

   $args = [
      "label" => esc_html__("Office addresses", "codeweber"),
      "labels" => $labels,
      "description" => "",
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
      "rewrite" => ["slug" => "office_addresses", "with_front" => true],
      "query_var" => true,
      "supports" => ["title"],
      "show_in_graphql" => false,
   ];

   register_post_type("office_addresses", $args);
}

add_action('init', 'cptui_register_my_cpts');

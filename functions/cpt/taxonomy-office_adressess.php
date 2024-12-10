<?php
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
      "rewrite" => ['slug' => 'towns', 'with_front' => true,],
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
   register_taxonomy("towns", ["office_addresses"], $args);
}
add_action('init', 'cptui_register_my_taxes_towns');

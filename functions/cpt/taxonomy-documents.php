<?php

function cptui_register_my_taxes_category_doc()
{

   /**
    * Taxonomy: Category Docs.
    */

   $labels = [
      "name" => esc_html__("Category Docs", "codeweber"),
      "singular_name" => esc_html__("Category Doc", "codeweber"),
   ];


   $args = [
      "label" => esc_html__("Category Docs", "codeweber"),
      "labels" => $labels,
      "public" => false,
      "publicly_queryable" => true,
      "hierarchical" => false,
      "show_ui" => true,
      "show_in_menu" => true,
      "show_in_nav_menus" => true,
      "query_var" => true,
      "rewrite" => ['slug' => 'category_doc', 'with_front' => true,],
      "show_admin_column" => true,
      "show_in_rest" => true,
      "show_tagcloud" => false,
      "rest_base" => "category_doc",
      "rest_controller_class" => "WP_REST_Terms_Controller",
      "rest_namespace" => "wp/v2",
      "show_in_quick_edit" => true,
      "sort" => true,
      "show_in_graphql" => false,
   ];
   register_taxonomy("category_doc", ["documents"], $args);
}
add_action('init', 'cptui_register_my_taxes_category_doc');

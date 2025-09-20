<?php

function register_my_cpt_legal()
{
   $labels = [
      "name" => esc_html__("Legal", "codeweber"),
      "singular_name" => esc_html__("Legal", "codeweber"),
      "menu_name" => esc_html__("Legal", "codeweber"),
      "all_items" => esc_html__("All Legal", "codeweber"),
      "add_new" => esc_html__("Add Legal", "codeweber"),
      "add_new_item" => esc_html__("Add New Legal", "codeweber"),
      "edit_item" => esc_html__("Edit Legal", "codeweber"),
      "new_item" => esc_html__("New Legal", "codeweber"),
      "view_item" => esc_html__("View Legal", "codeweber"),
      "search_items" => esc_html__("Search Legal", "codeweber"),
      "not_found" => esc_html__("No Legal documents found", "codeweber"),
      "not_found_in_trash" => esc_html__("No Legal documents in Trash", "codeweber"),
      "archives" => esc_html__("Legal Archives", "codeweber"),
      "item_published" => esc_html__("Legal Published", "codeweber"),
      "item_updated" => esc_html__("Legal Updated", "codeweber"),
   ];

   $args = [
      "label" => __("Legal", "codeweber"),
      "labels" => $labels,
      "public" => true,
      "publicly_queryable" => true,
      "show_ui" => true,
      "show_in_rest" => true, // включен REST API
      "rest_base" => "legal",
      "rest_controller_class" => "WP_REST_Posts_Controller",
      "has_archive" => true, // архив отключен
      "show_in_menu" => true,
      "show_in_nav_menus" => true,
      "delete_with_user" => false,
      "exclude_from_search" => false,
      "capability_type" => "post",
      "map_meta_cap" => true,
      "hierarchical" => false,
      "can_export" => true,
      "rewrite" => ["slug" => "legal", "with_front" => true],
      "query_var" => true,
      "supports" => ["title", "editor", "excerpt", "revisions", "comments", "author"],
   ];

   register_post_type("legal", $args);
}
add_action('init', 'register_my_cpt_legal');






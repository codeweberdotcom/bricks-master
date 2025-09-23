<?php

function register_my_cpt_documents()
{
   $labels = [
      "name" => esc_html__("Documents", "codeweber"),
      "singular_name" => esc_html__("Document", "codeweber"),
      "menu_name" => esc_html__("Documents", "codeweber"),
      "all_items" => esc_html__("All Documents", "codeweber"),
      "add_new" => esc_html__("Add Document", "codeweber"),
      "add_new_item" => esc_html__("Add New Document", "codeweber"),
      "edit_item" => esc_html__("Edit Document", "codeweber"),
      "new_item" => esc_html__("New Document", "codeweber"),
      "view_item" => esc_html__("View Document", "codeweber"),
      "view_items" => esc_html__("View Documents", "codeweber"),
      "search_items" => esc_html__("Search Document", "codeweber"),
      "not_found" => esc_html__("No documents found", "codeweber"),
      "not_found_in_trash" => esc_html__("No documents found in Trash", "codeweber"),
      "parent_item_colon" => esc_html__("Parent Document", "codeweber"),
      "featured_image" => esc_html__("Featured Image", "codeweber"),
      "set_featured_image" => esc_html__("Set featured image", "codeweber"),
      "remove_featured_image" => esc_html__("Remove featured image", "codeweber"),
      "use_featured_image" => esc_html__("Use as featured image", "codeweber"),
      "archives" => esc_html__("Document Archives", "codeweber"),
      "items_list" => esc_html__("Documents List", "codeweber"),
      "name_admin_bar" => esc_html__("Document", "codeweber"),
      "item_published" => esc_html__("Document Published", "codeweber"),
      "item_reverted_to_draft" => esc_html__("Document Reverted to Draft", "codeweber"),
      "item_scheduled" => esc_html__("Document Scheduled", "codeweber"),
      "item_updated" => esc_html__("Document Updated", "codeweber"),
   ];

   $args = [
      "label" => __("Documents", "codeweber"),
      "labels" => $labels,
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
      "rewrite" => ["slug" => "documents", "with_front" => true],
      "query_var" => true,
      "supports" => ["title", "editor", "thumbnail", "excerpt", "revisions", "comments"],
      "taxonomies" => ["document_category", "document_type"],
   ];

   register_post_type("documents", $args);
}
add_action('init', 'register_my_cpt_documents');

function register_taxonomy_document_category()
{
   $labels = [
      "name" => __("Document Categories", "codeweber"),
      "singular_name" => __("Document Category", "codeweber"),
   ];

   $args = [
      "label" => __("Document Categories", "codeweber"),
      "labels" => $labels,
      "public" => true,
      "publicly_queryable" => true,
      "hierarchical" => false,
      "show_ui" => true,
      "show_in_menu" => true,
      "show_in_nav_menus" => true,
      "query_var" => true,
      "rewrite" => ['slug' => 'document_category', 'with_front' => true],
      "show_admin_column" => false,
      "show_in_rest" => true,
      "show_tagcloud" => false,
      "rest_base" => "document_category",
      "rest_controller_class" => "WP_REST_Terms_Controller",
   ];

   register_taxonomy("document_category", ["documents"], $args);
}
add_action('init', 'register_taxonomy_document_category');

function register_taxonomy_document_type()
{
   $labels = [
      "name" => __("Document Types", "codeweber"),
      "singular_name" => __("Document Type", "codeweber"),
   ];

   $args = [
      "label" => __("Document Types", "codeweber"),
      "labels" => $labels,
      "public" => true,
      "publicly_queryable" => true,
      "hierarchical" => false,
      "show_ui" => true,
      "show_in_menu" => true,
      "show_in_nav_menus" => true,
      "query_var" => true,
      "rewrite" => ['slug' => 'document_type', 'with_front' => true],
      "show_admin_column" => false,
      "show_in_rest" => true,
      "show_tagcloud" => false,
      "rest_base" => "document_type",
      "rest_controller_class" => "WP_REST_Terms_Controller",
   ];

   register_taxonomy("document_type", ["documents"], $args);
}
add_action('init', 'register_taxonomy_document_type');


add_filter('use_block_editor_for_post_type', 'disable_gutenberg_for_documents', 10, 2);
function disable_gutenberg_for_documents($current_status, $post_type)
{
   if ($post_type === 'documents') {
      return false;
   }
   return $current_status;
}

<?php
function cptui_register_my_cpts_price_lists()
{

    /**
     * Post Type: Price Lists.
     */

    $labels = [
        "name" => esc_html__("Price Lists", "codeweber"),
        "singular_name" => esc_html__("Price List", "codeweber"),
        "menu_name" => esc_html__("My Price lists", "codeweber"),
        "all_items" => esc_html__("All Price lists", "codeweber"),
        "add_new" => esc_html__("Add new", "codeweber"),
        "add_new_item" => esc_html__("Add new Price list", "codeweber"),
        "edit_item" => esc_html__("Edit Price list", "codeweber"),
        "new_item" => esc_html__("New Price list", "codeweber"),
        "view_item" => esc_html__("View Price list", "codeweber"),
        "view_items" => esc_html__("View Price lists", "codeweber"),
        "search_items" => esc_html__("Search Price lists", "codeweber"),
        "not_found" => esc_html__("No Price lists found", "codeweber"),
        "not_found_in_trash" => esc_html__("No Price lists found in trash", "codeweber"),
        "parent" => esc_html__("Parent Price list:", "codeweber"),
        "featured_image" => esc_html__("Featured image for this Price list", "codeweber"),
        "set_featured_image" => esc_html__("Set featured image for this Price list", "codeweber"),
        "remove_featured_image" => esc_html__("Remove featured image for this Price list", "codeweber"),
        "use_featured_image" => esc_html__("Use as featured image for this Price list", "codeweber"),
        "archives" => esc_html__("Price list archives", "codeweber"),
        "insert_into_item" => esc_html__("Insert into Price list", "codeweber"),
        "uploaded_to_this_item" => esc_html__("Upload to this Price list", "codeweber"),
        "filter_items_list" => esc_html__("Filter Price lists list", "codeweber"),
        "items_list_navigation" => esc_html__("Price lists list navigation", "codeweber"),
        "items_list" => esc_html__("Price lists list", "codeweber"),
        "attributes" => esc_html__("Price lists attributes", "codeweber"),
        "name_admin_bar" => esc_html__("Price list", "codeweber"),
        "item_published" => esc_html__("Price list published", "codeweber"),
        "item_published_privately" => esc_html__("Price list published privately.", "codeweber"),
        "item_reverted_to_draft" => esc_html__("Price list reverted to draft.", "codeweber"),
        "item_scheduled" => esc_html__("Price list scheduled", "codeweber"),
        "item_updated" => esc_html__("Price list updated.", "codeweber"),
        "parent_item_colon" => esc_html__("Parent Price list:", "codeweber"),
    ];

    $args = [
        "label" => esc_html__("Price Lists", "codeweber"),
        "labels" => $labels,
        "description" => "",
        "public" => false,
        "publicly_queryable" => false,
        "show_ui" => true,
        "show_in_rest" => false,
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
        "rewrite" => ["slug" => "price_lists", "with_front" => false],
        "query_var" => true,
        "supports" => ["title"],
        "show_in_graphql" => false,
    ];

    register_post_type("price_lists", $args);
}

add_action('init', 'cptui_register_my_cpts_price_lists');

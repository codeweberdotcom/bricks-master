<?php
function cptui_register_my_cpts_price_lists()
{

    /**
     * Post Type: Price Lists.
     */

    $labels = [
        "name" => esc_html__("Price Lists", "codeweber"),
        "singular_name" => esc_html__("Price List", "codeweber"),
        "menu_name" => esc_html__("My Price Lists", "codeweber"),
        "all_items" => esc_html__("All Price Lists", "codeweber"),
        "add_new" => esc_html__("Add New", "codeweber"),
        "add_new_item" => esc_html__("Add New Price List", "codeweber"),
        "edit_item" => esc_html__("Edit Price List", "codeweber"),
        "new_item" => esc_html__("New Price List", "codeweber"),
        "view_item" => esc_html__("View Price List", "codeweber"),
        "view_items" => esc_html__("View Price Lists", "codeweber"),
        "search_items" => esc_html__("Search Price Lists", "codeweber"),
        "not_found" => esc_html__("No Price Lists found", "codeweber"),
        "not_found_in_trash" => esc_html__("No Price Lists found in Trash", "codeweber"),
        "parent" => esc_html__("Parent Price List:", "codeweber"),
        "featured_image" => esc_html__("Featured image for this Price List", "codeweber"),
        "set_featured_image" => esc_html__("Set Featured Image for this Price List", "codeweber"),
        "remove_featured_image" => esc_html__("Remove Featured Image for this Price List", "codeweber"),
        "use_featured_image" => esc_html__("Use as Featured Image for this Price List", "codeweber"),
        "archives" => esc_html__("Price List Archives", "codeweber"),
        "insert_into_item" => esc_html__("Insert into Price List", "codeweber"),
        "uploaded_to_this_item" => esc_html__("Upload to this Price List", "codeweber"),
        "filter_items_list" => esc_html__("Filter Price Lists", "codeweber"),
        "items_list_navigation" => esc_html__("Price Lists Navigation", "codeweber"),
        "items_list" => esc_html__("Price Lists List", "codeweber"),
        "attributes" => esc_html__("Price List Attributes", "codeweber"),
        "name_admin_bar" => esc_html__("Price List", "codeweber"),
        "item_published" => esc_html__("Price List Published", "codeweber"),
        "item_published_privately" => esc_html__("Price List Published Privately", "codeweber"),
        "item_reverted_to_draft" => esc_html__("Price List Reverted to Draft", "codeweber"),
        "item_scheduled" => esc_html__("Price List Scheduled", "codeweber"),
        "item_updated" => esc_html__("Price List Updated", "codeweber"),
        "parent_item_colon" => esc_html__("Parent Price List:", "codeweber"),
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

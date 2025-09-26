<?php
function cptui_register_my_cpts_testimonials()
{

    /**
     * Post Type: Testimonials.
     */

    $labels = [
        "name" => esc_html__("Testimonials", "codeweber"),
        "singular_name" => esc_html__("Testimonial", "codeweber"),
        "menu_name" => esc_html__("Testimonials", "codeweber"),
        "new_item" => esc_html__("New Testimonial", "codeweber"),
    ];

    $args = [
        "label" => esc_html__("Testimonials", "codeweber"),
        "labels" => $labels,
        "description" => "",
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
        "exclude_from_search" => true,
        "capability_type" => "post",
        "map_meta_cap" => true,
        "hierarchical" => false,
        "can_export" => true,
        "rewrite" => ["slug" => "testimonials", "with_front" => true],
        "query_var" => true,
        "supports" => ["title", "comments", "revisions"],
        "show_in_graphql" => false,
    ];

    register_post_type("testimonials", $args);
}

add_action('init', 'cptui_register_my_cpts_testimonials');

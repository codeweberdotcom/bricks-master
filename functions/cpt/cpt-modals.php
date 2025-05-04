<?php

function cptui_register_my_cpts_modal()
{

	/**
	 * Post Type: modals.
	 */

	$labels = [
		"name" => esc_html__("Модальные окна", "codeweber"), // Переведено на русский
		"singular_name" => esc_html__("Модальное окно", "codeweber"), // Переведено на русский
		"menu_name" => esc_html__("Модальные окна", "codeweber"), // Переведено на русский
		"all_items" => esc_html__("Все модальные окна", "codeweber"), // Переведено на русский
		"add_new" => esc_html__("Добавить новое", "codeweber"), // Переведено на русский
		"add_new_item" => esc_html__("Добавить новое модальное окно", "codeweber"), // Переведено на русский
		"edit_item" => esc_html__("Редактировать модальное окно", "codeweber"), // Переведено на русский
		"new_item" => esc_html__("Новое модальное окно", "codeweber"), // Переведено на русский
		"view_item" => esc_html__("Просмотреть модальное окно", "codeweber"), // Переведено на русский
		"search_items" => esc_html__("Поиск модальных окон", "codeweber"), // Переведено на русский
		"not_found" => esc_html__("Модальные окна не найдены", "codeweber"), // Переведено на русский
		"not_found_in_trash" => esc_html__("Модальные окна в корзине не найдены", "codeweber"), // Переведено на русский
	];

	$args = [
		"label" => esc_html__("Модальные окна", "codeweber"), // Переведено на русский
		"labels" => $labels,
		"description" => esc_html__("Тип записи для модальных окон", "codeweber"), // Переведено на русский
		"public" => false,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"rest_namespace" => "wp/v2",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"can_export" => true,
		"rewrite" => ["slug" => "modal", "with_front" => true],
		"query_var" => true,
		"supports" => ["title", "editor"],
		"show_in_graphql" => false,
	];

	register_post_type("modal", $args);
}

add_action('init', 'cptui_register_my_cpts_modal');

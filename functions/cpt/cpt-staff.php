<?php

function cptui_register_my_cpts_staff()
{
	/**
	 * Post Type: Staff.
	 */
	$labels = [
		"name" => esc_html__("Staff", "codeweber"),
		"singular_name" => esc_html__("Staff", "codeweber"),
		"menu_name" => esc_html__("Staff", "codeweber"),
		"all_items" => esc_html__("All Staff", "codeweber"),
		"add_new" => esc_html__("Add Staff", "codeweber"),
		"add_new_item" => esc_html__("Add New Staff", "codeweber"),
		"edit_item" => esc_html__("Edit Staff", "codeweber"),
		"new_item" => esc_html__("New Staff", "codeweber"),
		"view_item" => esc_html__("View Staff", "codeweber"),
		"view_items" => esc_html__("View Staff", "codeweber"),
		"search_items" => esc_html__("Search Staff", "codeweber"),
		"not_found" => esc_html__("(e.g. No Staff found)", "codeweber"),
		"not_found_in_trash" => esc_html__("(e.g. No Staff found in Trash)", "codeweber"),
		"parent" => esc_html__("Parent Staff", "codeweber"),
		"featured_image" => esc_html__("Featured Image for this staff", "codeweber"),
		"set_featured_image" => esc_html__("Set featured Image for this staff", "codeweber"),
		"remove_featured_image" => esc_html__("Remove featured Image for this staff", "codeweber"),
		"use_featured_image" => esc_html__("Use as featured image", "codeweber"),
		"archives" => esc_html__("Staff archive", "codeweber"),
		"items_list" => esc_html__("Staff list", "codeweber"),
		"name_admin_bar" => esc_html__("Staff", "codeweber"),
		"item_published" => esc_html__("Staff published", "codeweber"),
		"item_reverted_to_draft" => esc_html__("Staff reverted to draft", "codeweber"),
		"item_scheduled" => esc_html__("Staff scheduled", "codeweber"),
		"item_updated" => esc_html__("Staff updated", "codeweber"),
		"parent_item_colon" => esc_html__("Parent Staff", "codeweber"),
	];

	$args = [
		"label" => esc_html__("Staff", "codeweber"),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"rest_namespace" => "wp/v2",
		"has_archive" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => true,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"can_export" => true,
		"rewrite" => ["slug" => "staff", "with_front" => true],
		"query_var" => true,
		"supports" => ["title", "thumbnail", "editor", "revisions"],
		"show_in_graphql" => false,
	];

	register_post_type("staff", $args);
}

add_action('init', 'cptui_register_my_cpts_staff');



add_filter('use_block_editor_for_post_type', 'disable_gutenberg_for_projects', 10, 2);
function disable_gutenberg_for_projects($current_status, $post_type)
{
	if ($post_type === 'staff') {
		return false;
	}
	return $current_status;
}


/**
 * Add metabox with additional fields for CPT staff
 */
function codeweber_add_staff_meta_boxes()
{
	add_meta_box(
		'staff_details',
		'Staff Information',
		'codeweber_staff_meta_box_callback',
		'staff',
		'normal',
		'high'
	);
}
add_action('add_meta_boxes', 'codeweber_add_staff_meta_boxes');

/**
 * Callback function for displaying the metabox
 */
function codeweber_staff_meta_box_callback($post)
{
	// Add nonce for security
	wp_nonce_field('staff_meta_box', 'staff_meta_box_nonce');

	// Get existing field values
	$position = get_post_meta($post->ID, '_staff_position', true);
	$name = get_post_meta($post->ID, '_staff_name', true);
	$email = get_post_meta($post->ID, '_staff_email', true);
	$phone = get_post_meta($post->ID, '_staff_phone', true);
?>

	<div style="display: grid; grid-template-columns: 100px 1fr; gap: 12px; align-items: center;">
		<label for="staff_position"><strong>Position:</strong></label>
		<input type="text" id="staff_position" name="staff_position" value="<?php echo esc_attr($position); ?>" style="width: 100%; padding: 8px;">

		<label for="staff_name"><strong>Name:</strong></label>
		<input type="text" id="staff_name" name="staff_name" value="<?php echo esc_attr($name); ?>" style="width: 100%; padding: 8px;">

		<label for="staff_email"><strong>E-Mail:</strong></label>
		<input type="email" id="staff_email" name="staff_email" value="<?php echo esc_attr($email); ?>" style="width: 100%; padding: 8px;">

		<label for="staff_phone"><strong>Phone:</strong></label>
		<input type="tel" id="staff_phone" name="staff_phone" value="<?php echo esc_attr($phone); ?>" style="width: 100%; padding: 8px;">
	</div>
<?php
}

/**
 * Save metadata fields
 */
function codeweber_save_staff_meta($post_id)
{
	// Check nonce
	if (!isset($_POST['staff_meta_box_nonce']) || !wp_verify_nonce($_POST['staff_meta_box_nonce'], 'staff_meta_box')) {
		return;
	}

	// Check user permissions
	if (!current_user_can('edit_post', $post_id)) {
		return;
	}

	// Check autosave
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	// Save fields
	$fields = ['staff_position', 'staff_name', 'staff_email', 'staff_phone'];

	foreach ($fields as $field) {
		if (isset($_POST[$field])) {
			update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
		}
	}
}
add_action('save_post_staff', 'codeweber_save_staff_meta');

/**
 * Add columns to admin for CPT staff
 */
function codeweber_add_staff_admin_columns($columns)
{
	$new_columns = [
		'cb' => $columns['cb'],
		'title' => $columns['title'],
		'staff_position' => 'Position',
		'staff_email' => 'E-Mail',
		'staff_phone' => 'Phone',
		'date' => $columns['date']
	];
	return $new_columns;
}
add_filter('manage_staff_posts_columns', 'codeweber_add_staff_admin_columns');

/**
 * Fill columns with data
 */
function codeweber_fill_staff_admin_columns($column, $post_id)
{
	switch ($column) {
		case 'staff_position':
			echo esc_html(get_post_meta($post_id, '_staff_position', true));
			break;
		case 'staff_email':
			echo esc_html(get_post_meta($post_id, '_staff_email', true));
			break;
		case 'staff_phone':
			echo esc_html(get_post_meta($post_id, '_staff_phone', true));
			break;
	}
}
add_action('manage_staff_posts_custom_column', 'codeweber_fill_staff_admin_columns', 10, 2);

/**
 * Make columns sortable
 */
function codeweber_make_staff_columns_sortable($columns)
{
	$columns['staff_position'] = 'staff_position';
	$columns['staff_email'] = 'staff_email';
	return $columns;
}
add_filter('manage_edit-staff_sortable_columns', 'codeweber_make_staff_columns_sortable');

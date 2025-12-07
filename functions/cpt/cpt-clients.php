<?php

function cptui_register_my_cpts_clients()
{
	/**
	 * Post Type: Clients.
	 */

	$labels = [
		"name" => esc_html__("Clients", "codeweber"),
		"singular_name" => esc_html__("Client", "codeweber"),
		"menu_name" => esc_html__("Clients", "codeweber"),
		"all_items" => esc_html__("All Clients", "codeweber"),
		"add_new" => esc_html__("Add New Client", "codeweber"),
		"add_new_item" => esc_html__("Add Client", "codeweber"),
		"edit_item" => esc_html__("Edit Client", "codeweber"),
		"new_item" => esc_html__("New Client", "codeweber"),
		"view_item" => esc_html__("View Client", "codeweber"),
		"view_items" => esc_html__("View Clients", "codeweber"),
		"search_items" => esc_html__("Search Client", "codeweber"),
		"not_found" => esc_html__("No Client", "codeweber"),
		"items_list" => esc_html__("Clients list", "codeweber"),
		"name_admin_bar" => esc_html__("Client", "codeweber"),
		"item_published" => esc_html__("Client published", "codeweber"),
		"item_updated" => esc_html__("Client updated", "codeweber"),
	];

	$args = [
		"label" => esc_html__("Clients", "codeweber"),
		"labels" => $labels,
		"description" => "",
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
		"exclude_from_search" => true,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"can_export" => true,
		"rewrite" => ["slug" => "clients", "with_front" => true],
		"query_var" => true,
		"supports" => ["title"],
		"show_in_graphql" => false,
	];

	register_post_type("clients", $args);
}

add_action('init', 'cptui_register_my_cpts_clients');

/**
 * Register taxonomy: Category for Clients
 */
function cptui_register_my_taxes_clients_category()
{
	/**
	 * Taxonomy: Client Categories.
	 */

	$labels = [
		"name" => esc_html__("Categories", "codeweber"),
		"singular_name" => esc_html__("Category", "codeweber"),
		"menu_name" => esc_html__("Categories", "codeweber"),
		"all_items" => esc_html__("All Categories", "codeweber"),
		"edit_item" => esc_html__("Edit Category", "codeweber"),
		"view_item" => esc_html__("View Category", "codeweber"),
		"update_item" => esc_html__("Update Category", "codeweber"),
		"add_new_item" => esc_html__("Add New Category", "codeweber"),
		"new_item_name" => esc_html__("New Category Name", "codeweber"),
		"parent_item" => esc_html__("Parent Category", "codeweber"),
		"parent_item_colon" => esc_html__("Parent Category:", "codeweber"),
		"search_items" => esc_html__("Search Categories", "codeweber"),
		"popular_items" => esc_html__("Popular Categories", "codeweber"),
		"separate_items_with_commas" => esc_html__("Separate categories with commas", "codeweber"),
		"add_or_remove_items" => esc_html__("Add or remove categories", "codeweber"),
		"choose_from_most_used" => esc_html__("Choose from the most used categories", "codeweber"),
		"not_found" => esc_html__("No categories found", "codeweber"),
		"no_terms" => esc_html__("No categories", "codeweber"),
		"items_list_navigation" => esc_html__("Categories list navigation", "codeweber"),
		"items_list" => esc_html__("Categories list", "codeweber"),
		"back_to_items" => esc_html__("Back to Categories", "codeweber"),
	];

	$args = [
		"label" => esc_html__("Categories", "codeweber"),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => ['slug' => 'clients/category', 'with_front' => true],
		"show_admin_column" => true,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "clients_category",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => true,
		"show_in_graphql" => false,
	];
	register_taxonomy("clients_category", ["clients"], $args);
}
add_action('init', 'cptui_register_my_taxes_clients_category');

/**
 * Add meta boxes for Clients CPT
 */
function cw_clients_add_meta_boxes() {
	add_meta_box(
		'cw_clients_company_info',
		__('Company Information', 'codeweber'),
		'cw_clients_company_info_callback',
		'clients',
		'normal',
		'high'
	);
}
add_action('add_meta_boxes', 'cw_clients_add_meta_boxes');

/**
 * Meta box callback for Company Information
 */
function cw_clients_company_info_callback($post) {
	// Add nonce for security
	wp_nonce_field('cw_clients_company_info_save', 'cw_clients_company_info_nonce');
	
	// Get existing values
	$company_name = get_post_meta($post->ID, '_cw_clients_company_name', true);
	$company_url = get_post_meta($post->ID, '_cw_clients_company_url', true);
	
	?>
	<table class="form-table">
		<tr>
			<th scope="row">
				<label for="cw_clients_company_name"><?php esc_html_e('Company Name', 'codeweber'); ?></label>
			</th>
			<td>
				<input 
					type="text" 
					id="cw_clients_company_name" 
					name="cw_clients_company_name" 
					value="<?php echo esc_attr($company_name); ?>" 
					class="regular-text"
					placeholder="<?php esc_attr_e('Enter company name', 'codeweber'); ?>"
				/>
				<p class="description"><?php esc_html_e('Enter the name of the company', 'codeweber'); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="cw_clients_company_url"><?php esc_html_e('Company URL', 'codeweber'); ?></label>
			</th>
			<td>
				<input 
					type="url" 
					id="cw_clients_company_url" 
					name="cw_clients_company_url" 
					value="<?php echo esc_url($company_url); ?>" 
					class="regular-text"
					placeholder="<?php esc_attr_e('https://example.com', 'codeweber'); ?>"
				/>
				<p class="description"><?php esc_html_e('Enter the company website URL', 'codeweber'); ?></p>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * Save meta box data
 */
function cw_clients_save_company_info($post_id) {
	// Check if nonce is set
	if (!isset($_POST['cw_clients_company_info_nonce'])) {
		return;
	}
	
	// Verify nonce
	if (!wp_verify_nonce($_POST['cw_clients_company_info_nonce'], 'cw_clients_company_info_save')) {
		return;
	}
	
	// Check if this is an autosave
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	
	// Check user permissions
	if (!current_user_can('edit_post', $post_id)) {
		return;
	}
	
	// Check post type
	if (get_post_type($post_id) !== 'clients') {
		return;
	}
	
	// Save company name
	if (isset($_POST['cw_clients_company_name'])) {
		update_post_meta(
			$post_id,
			'_cw_clients_company_name',
			sanitize_text_field($_POST['cw_clients_company_name'])
		);
	}
	
	// Save company URL
	if (isset($_POST['cw_clients_company_url'])) {
		$url = esc_url_raw($_POST['cw_clients_company_url']);
		update_post_meta(
			$post_id,
			'_cw_clients_company_url',
			$url
		);
	}
}
add_action('save_post', 'cw_clients_save_company_info');

/**
 * Add Company URL to REST API response for clients post type
 */
function cw_clients_add_company_url_to_rest_api() {
	register_rest_field('clients', 'company_url', [
		'get_callback' => function($post) {
			return get_post_meta($post['id'], '_cw_clients_company_url', true);
		},
		'schema' => [
			'description' => __('Company URL', 'codeweber'),
			'type' => 'string',
			'context' => ['view', 'edit'],
		],
	]);
}
add_action('rest_api_init', 'cw_clients_add_company_url_to_rest_api');
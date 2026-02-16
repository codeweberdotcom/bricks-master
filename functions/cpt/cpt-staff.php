<?php

// Подключаем функции для работы с QR кодами
require_once get_template_directory() . '/functions/qr-code.php';

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

function cptui_register_my_taxes_departments()
{
	/**
	 * Taxonomy: Departments.
	 */
	$labels = [
		"name" => esc_html__("Departments", "codeweber"),
		"singular_name" => esc_html__("Department", "codeweber"),
		"menu_name" => esc_html__("Departments", "codeweber"),
		"all_items" => esc_html__("All Departments", "codeweber"),
		"edit_item" => esc_html__("Edit Department", "codeweber"),
		"view_item" => esc_html__("View Department", "codeweber"),
		"update_item" => esc_html__("Update Department", "codeweber"),
		"add_new_item" => esc_html__("Add New Department", "codeweber"),
		"new_item_name" => esc_html__("New Department Name", "codeweber"),
		"parent_item" => esc_html__("Parent Department", "codeweber"),
		"parent_item_colon" => esc_html__("Parent Department:", "codeweber"),
		"search_items" => esc_html__("Search Departments", "codeweber"),
		"popular_items" => esc_html__("Popular Departments", "codeweber"),
		"separate_items_with_commas" => esc_html__("Separate departments with commas", "codeweber"),
		"add_or_remove_items" => esc_html__("Add or remove departments", "codeweber"),
		"choose_from_most_used" => esc_html__("Choose from the most used departments", "codeweber"),
		"not_found" => esc_html__("No departments found", "codeweber"),
		"no_terms" => esc_html__("No departments", "codeweber"),
		"items_list_navigation" => esc_html__("Departments list navigation", "codeweber"),
		"items_list" => esc_html__("Departments list", "codeweber"),
		"back_to_items" => esc_html__("Back to Departments", "codeweber"),
	];

	$args = [
		"label" => esc_html__("Departments", "codeweber"),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => ['slug' => 'departments', 'with_front' => true],
		"show_admin_column" => true,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "departments",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => false,
	];

	register_taxonomy("departments", ["staff"], $args);
}

add_action('init', 'cptui_register_my_taxes_departments');

add_filter('use_block_editor_for_post_type', 'disable_gutenberg_for_staff', 10, 2);
function disable_gutenberg_for_staff($current_status, $post_type)
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
		esc_html__('Staff Information', 'codeweber'),
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
	$surname = get_post_meta($post->ID, '_staff_surname', true);
	$email = get_post_meta($post->ID, '_staff_email', true);
	$phone = get_post_meta($post->ID, '_staff_phone', true);
	$company = get_post_meta($post->ID, '_staff_company', true);
	$department = get_post_meta($post->ID, '_staff_department', true);
	$job_phone = get_post_meta($post->ID, '_staff_job_phone', true);
	$country = get_post_meta($post->ID, '_staff_country', true);
	$region = get_post_meta($post->ID, '_staff_region', true);
	$city = get_post_meta($post->ID, '_staff_city', true);
	$street = get_post_meta($post->ID, '_staff_street', true);
	$postal_code = get_post_meta($post->ID, '_staff_postal_code', true);
	
	// Get departments taxonomy terms
	$departments = get_terms(array(
		'taxonomy' => 'departments',
		'hide_empty' => false,
	));
?>

	<div style="display: grid; grid-template-columns: 100px 1fr; gap: 12px; align-items: center;">
		<label for="staff_position"><strong><?php echo esc_html__('Position', 'codeweber'); ?>:</strong></label>
		<input type="text" id="staff_position" name="staff_position" value="<?php echo esc_attr($position); ?>" style="width: 100%; padding: 8px;">

		<label for="staff_name"><strong><?php echo esc_html__('Name', 'codeweber'); ?>:</strong></label>
		<input type="text" id="staff_name" name="staff_name" value="<?php echo esc_attr($name); ?>" style="width: 100%; padding: 8px;">

		<label for="staff_surname"><strong><?php echo esc_html__('Surname', 'codeweber'); ?>:</strong></label>
		<input type="text" id="staff_surname" name="staff_surname" value="<?php echo esc_attr($surname); ?>" style="width: 100%; padding: 8px;">

		<label for="staff_email"><strong><?php echo esc_html__('E-Mail', 'codeweber'); ?>:</strong></label>
		<input type="email" id="staff_email" name="staff_email" value="<?php echo esc_attr($email); ?>" style="width: 100%; padding: 8px;">

		<label for="staff_phone"><strong><?php echo esc_html__('Phone', 'codeweber'); ?>:</strong></label>
		<input type="tel" id="staff_phone" name="staff_phone" value="<?php echo esc_attr($phone); ?>" style="width: 100%; padding: 8px;">

		<label for="staff_company"><strong><?php echo esc_html__('Company', 'codeweber'); ?>:</strong></label>
		<input type="text" id="staff_company" name="staff_company" value="<?php echo esc_attr($company); ?>" style="width: 100%; padding: 8px;">

		<label for="staff_department"><strong><?php echo esc_html__('Department', 'codeweber'); ?>:</strong></label>
		<select id="staff_department" name="staff_department" style="width: 100%; padding: 8px;">
			<option value=""><?php echo esc_html__('Select Department', 'codeweber'); ?></option>
			<?php if (!empty($departments) && !is_wp_error($departments)) : ?>
				<?php foreach ($departments as $dept) : ?>
					<option value="<?php echo esc_attr($dept->term_id); ?>" <?php selected($department, $dept->term_id); ?>>
						<?php echo esc_html($dept->name); ?>
					</option>
				<?php endforeach; ?>
			<?php endif; ?>
		</select>

		<label for="staff_job_phone"><strong><?php echo esc_html__('Job Phone', 'codeweber'); ?>:</strong></label>
		<input type="tel" id="staff_job_phone" name="staff_job_phone" value="<?php echo esc_attr($job_phone); ?>" style="width: 100%; padding: 8px;">

		<label for="staff_country"><strong><?php echo esc_html__('Country', 'codeweber'); ?>:</strong></label>
		<input type="text" id="staff_country" name="staff_country" value="<?php echo esc_attr($country); ?>" style="width: 100%; padding: 8px;">

		<label for="staff_region"><strong><?php echo esc_html__('Region', 'codeweber'); ?>:</strong></label>
		<input type="text" id="staff_region" name="staff_region" value="<?php echo esc_attr($region); ?>" style="width: 100%; padding: 8px;">

		<label for="staff_city"><strong><?php echo esc_html__('City', 'codeweber'); ?>:</strong></label>
		<input type="text" id="staff_city" name="staff_city" value="<?php echo esc_attr($city); ?>" style="width: 100%; padding: 8px;">

		<label for="staff_street"><strong><?php echo esc_html__('Street, House Number and Office', 'codeweber'); ?>:</strong></label>
		<input type="text" id="staff_street" name="staff_street" value="<?php echo esc_attr($street); ?>" style="width: 100%; padding: 8px;">

		<label for="staff_postal_code"><strong><?php echo esc_html__('Postal Code', 'codeweber'); ?>:</strong></label>
		<input type="text" id="staff_postal_code" name="staff_postal_code" value="<?php echo esc_attr($postal_code); ?>" style="width: 100%; padding: 8px;">
	</div>

	<!-- Social Media Section -->
	<div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #ddd;">
		<h3 style="margin-bottom: 15px;"><?php echo esc_html__('Social Media', 'codeweber'); ?></h3>
		
		<div style="display: grid; grid-template-columns: 120px 1fr; gap: 12px; align-items: center;">
			<?php 
			$social_fields = [
				'facebook' => 'Facebook',
				'twitter' => 'Twitter',
				'linkedin' => 'LinkedIn',
				'instagram' => 'Instagram',
				'telegram' => 'Telegram',
				'vk' => 'VK',
				'whatsapp' => 'WhatsApp',
				'skype' => 'Skype',
				'website' => 'Website'
			];
			
			foreach ($social_fields as $social_key => $social_label) :
				$social_value = get_post_meta($post->ID, '_staff_' . $social_key, true);
			?>
				<label for="staff_<?php echo esc_attr($social_key); ?>"><strong><?php echo esc_html($social_label); ?>:</strong></label>
				<input type="url" id="staff_<?php echo esc_attr($social_key); ?>" name="staff_<?php echo esc_attr($social_key); ?>" value="<?php echo esc_attr($social_value); ?>" placeholder="https://..." style="width: 100%; padding: 8px;">
			<?php endforeach; ?>
		</div>
	</div>

	<!-- QR Code Section -->
	<div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #ddd;">
		<h3 style="margin-bottom: 15px;"><?php echo esc_html__('QR Code', 'codeweber'); ?></h3>
		
		<?php 
		$qrcode_id = get_post_meta($post->ID, '_staff_qrcode_id', true);
		$qrcode_url = '';
		if ($qrcode_id) {
			$qrcode_url = wp_get_attachment_image_url($qrcode_id, 'full');
		}
		?>
		
		<div id="staff-qrcode-container" style="margin-bottom: 15px;">
			<?php if ($qrcode_url) : ?>
				<div style="margin-bottom: 10px;">
					<img id="staff-qrcode-image" src="<?php echo esc_url($qrcode_url); ?>" alt="QR Code" style="max-width: 200px; height: auto; border: 1px solid #ddd; padding: 5px; background: #fff;">
				</div>
				<p style="margin: 5px 0;">
					<strong><?php echo esc_html__('QR Code ID', 'codeweber'); ?>:</strong> 
					<input type="hidden" id="staff_qrcode_id" name="staff_qrcode_id" value="<?php echo esc_attr($qrcode_id); ?>">
					<?php echo esc_html($qrcode_id); ?>
				</p>
			<?php else : ?>
				<p style="color: #666; font-style: italic;"><?php echo esc_html__('QR code not generated', 'codeweber'); ?></p>
			<?php endif; ?>
		</div>
		
		<div style="display: flex; gap: 10px; align-items: center;">
			<button type="button" id="generate-staff-qrcode-btn" class="button button-secondary" data-post-id="<?php echo esc_attr($post->ID); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('generate_staff_qrcode')); ?>">
				<?php echo esc_html__('Generate QR code', 'codeweber'); ?>
			</button>
			<?php if ($qrcode_id) : ?>
				<button type="button" id="delete-staff-qrcode-btn" class="button button-link-delete" data-post-id="<?php echo esc_attr($post->ID); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('delete_staff_qrcode')); ?>" style="color: #b32d2e;">
					<?php echo esc_html__('Delete QR code', 'codeweber'); ?>
				</button>
			<?php endif; ?>
			<span id="qrcode-generating-spinner" class="spinner" style="float: none; margin-left: 10px; visibility: hidden;"></span>
		</div>
		<p id="qrcode-message" style="margin-top: 10px; color: #46b450; display: none;"></p>
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
	$fields = [
		'staff_position', 
		'staff_name', 
		'staff_surname', 
		'staff_email', 
		'staff_phone',
		'staff_company',
		'staff_department',
		'staff_job_phone',
		'staff_country',
		'staff_region',
		'staff_city',
		'staff_street',
		'staff_postal_code',
		'staff_facebook',
		'staff_twitter',
		'staff_linkedin',
		'staff_instagram',
		'staff_telegram',
		'staff_vk',
		'staff_whatsapp',
		'staff_skype',
		'staff_website'
	];

	foreach ($fields as $field) {
		if (isset($_POST[$field])) {
			// Для URL полей (соцсети и website) используем esc_url_raw
			$social_fields = ['staff_facebook', 'staff_twitter', 'staff_linkedin', 'staff_instagram', 'staff_telegram', 'staff_vk', 'staff_whatsapp', 'staff_skype', 'staff_website'];
			if (in_array($field, $social_fields)) {
				update_post_meta($post_id, '_' . $field, esc_url_raw($_POST[$field]));
			} else {
				update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
			}
		} else {
			// Clear field if not set
			delete_post_meta($post_id, '_' . $field);
		}
	}
	
	// Сохраняем QR код ID, если передан
	if (isset($_POST['staff_qrcode_id'])) {
		update_post_meta($post_id, '_staff_qrcode_id', intval($_POST['staff_qrcode_id']));
	}
	
	// Автоматическая генерация QR кода при сохранении
	if (function_exists('codeweber_staff_generate_qrcode')) {
		// Удаляем старый QR код, если есть
		$old_qrcode_id = get_post_meta($post_id, '_staff_qrcode_id', true);
		if ($old_qrcode_id) {
			wp_delete_attachment($old_qrcode_id, true);
		}
		
		// Генерируем новый QR код
		codeweber_staff_generate_qrcode($post_id);
	}
}
add_action('save_post_staff', 'codeweber_save_staff_meta');

/**
 * Enqueue scripts for staff admin
 */
function codeweber_staff_admin_scripts($hook) {
	global $post_type;
	
	if ($post_type !== 'staff' || !in_array($hook, array('post.php', 'post-new.php'))) {
		return;
	}
	
	wp_enqueue_script(
		'staff-qrcode-admin',
		get_template_directory_uri() . '/functions/cpt/staff-qrcode-admin.js',
		array('jquery'),
		'1.0.0',
		true
	);
	
	wp_localize_script('staff-qrcode-admin', 'staffQrcode', array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('generate_staff_qrcode'),
		'generating' => esc_html__('Generating...', 'codeweber'),
		'success' => esc_html__('QR code generated successfully', 'codeweber'),
		'error' => esc_html__('QR code generation error', 'codeweber'),
		'delete_success' => esc_html__('QR code deleted successfully', 'codeweber'),
		'delete_error' => esc_html__('QR code deletion error', 'codeweber'),
	));
}
add_action('admin_enqueue_scripts', 'codeweber_staff_admin_scripts');

/**
 * Add columns to admin for CPT staff
 */
function codeweber_add_staff_admin_columns($columns)
{
	$new_columns = [
		'cb' => $columns['cb'],
		'title' => $columns['title'],
		'staff_position' => esc_html__('Position', 'codeweber'),
		'staff_name' => esc_html__('Name', 'codeweber'),
		'staff_surname' => esc_html__('Surname', 'codeweber'),
		'staff_email' => esc_html__('E-Mail', 'codeweber'),
		'staff_phone' => esc_html__('Phone', 'codeweber'),
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
		case 'staff_name':
			echo esc_html(get_post_meta($post_id, '_staff_name', true));
			break;
		case 'staff_surname':
			echo esc_html(get_post_meta($post_id, '_staff_surname', true));
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
	$columns['staff_name'] = 'staff_name';
	$columns['staff_surname'] = 'staff_surname';
	$columns['staff_email'] = 'staff_email';
	return $columns;
}
add_filter('manage_edit-staff_sortable_columns', 'codeweber_make_staff_columns_sortable');

/**
 * REST API endpoint для скачивания VCF файла сотрудника
 */
function register_staff_vcf_download_endpoint() {
	register_rest_route('codeweber/v1', '/staff/(?P<id>\d+)/download-vcf', [
		'methods' => 'GET',
		'callback' => 'get_staff_vcf_download',
		'permission_callback' => '__return_true',
		'args' => [
			'id' => [
				'required' => true,
				'type' => 'integer',
				'validate_callback' => function($param) {
					return is_numeric($param);
				}
			]
		]
	]);
}
add_action('rest_api_init', 'register_staff_vcf_download_endpoint');

/**
 * Callback для скачивания VCF файла сотрудника
 * 
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function get_staff_vcf_download($request) {
	$post_id = intval($request->get_param('id'));
	
	// Проверяем, что запись существует и это staff
	$post = get_post($post_id);
	if (!$post || $post->post_type !== 'staff') {
		return new WP_Error('not_found', 'Staff member not found', ['status' => 404]);
	}
	
	// Генерируем vCard
	$vcard = codeweber_staff_generate_vcard($post_id);
	
	// Формируем имя файла
	$name = get_post_meta($post_id, '_staff_name', true);
	$surname = get_post_meta($post_id, '_staff_surname', true);
	$full_name = trim($name . ' ' . $surname);
	if (empty($full_name)) {
		$full_name = sanitize_file_name(get_the_title($post_id));
	} else {
		$full_name = sanitize_file_name($full_name);
	}
	$filename = $full_name . '.vcf';
	
	// Возвращаем данные для скачивания
	return rest_ensure_response([
		'file_content' => base64_encode($vcard),
		'file_name' => $filename,
		'mime_type' => 'text/vcard',
		'post_id' => $post_id
	]);
}

/**
 * Регистрация REST API полей для staff метаданных
 */
function codeweber_register_staff_rest_fields() {
	// Список метаполей staff, которые нужно добавить в REST API
	$meta_fields = [
		'_staff_position',
		'_staff_company',
		'_staff_name',
		'_staff_surname',
		'_staff_email',
		'_staff_phone',
	];
	
	foreach ($meta_fields as $meta_field) {
		register_rest_field('staff', $meta_field, [
			'get_callback' => function($post) use ($meta_field) {
				return get_post_meta($post['id'], $meta_field, true);
			},
			'update_callback' => null, // Только для чтения
			'schema' => [
				'description' => sprintf(__('Staff meta field: %s', 'codeweber'), $meta_field),
				'type' => 'string',
				'context' => ['view', 'edit'],
			],
		]);
	}
}
add_action('rest_api_init', 'codeweber_register_staff_rest_fields');

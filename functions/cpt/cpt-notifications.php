<?php

function cptui_register_my_cpts_notifications()
{
	/**
	 * Post Type: Notifications.
	 */

	$labels = [
		"name" => esc_html__("Notifications", "codeweber"),
		"singular_name" => esc_html__("Notification", "codeweber"),
		"menu_name" => esc_html__("Notifications", "codeweber"),
		"all_items" => esc_html__("All Notifications", "codeweber"),
		"add_new" => esc_html__("Add New", "codeweber"),
		"add_new_item" => esc_html__("Add New Notification", "codeweber"),
		"edit_item" => esc_html__("Edit Notification", "codeweber"),
		"new_item" => esc_html__("New Notification", "codeweber"),
		"view_item" => esc_html__("View Notification", "codeweber"),
		"search_items" => esc_html__("Search Notifications", "codeweber"),
		"not_found" => esc_html__("No notifications found", "codeweber"),
		"not_found_in_trash" => esc_html__("No notifications found in Trash", "codeweber"),
	];

	$args = [
		"label" => esc_html__("Notifications", "codeweber"),
		"labels" => $labels,
		"description" => esc_html__("Post type for notifications", "codeweber"),
		"public" => false,
		"publicly_queryable" => false,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"rest_namespace" => "wp/v2",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => false,
		"delete_with_user" => false,
		"exclude_from_search" => true,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"can_export" => true,
		"rewrite" => false,
		"query_var" => false,
		"supports" => ["title"],
		"show_in_graphql" => false,
	];

	register_post_type("notifications", $args);
}

add_action('init', 'cptui_register_my_cpts_notifications');

/**
 * Add meta boxes for Notifications CPT
 */
function codeweber_add_notifications_meta_boxes() {
	add_meta_box(
		'codeweber_notifications_settings',
		__('Notification Settings', 'codeweber'),
		'codeweber_notifications_meta_box_callback',
		'notifications',
		'normal',
		'high'
	);
}
add_action('add_meta_boxes', 'codeweber_add_notifications_meta_boxes');

/**
 * Meta box callback for Notification Settings
 */
function codeweber_notifications_meta_box_callback($post) {
	// Add nonce for security
	wp_nonce_field('notifications_meta_box', 'notifications_meta_box_nonce');
	
	// Get existing values
	$modal_id = get_post_meta($post->ID, '_notification_modal_id', true);
	$start_date = get_post_meta($post->ID, '_notification_start_date', true);
	$end_date = get_post_meta($post->ID, '_notification_end_date', true);
	$wait_delay = get_post_meta($post->ID, '_notification_wait_delay', true);
	if (empty($wait_delay)) {
		$wait_delay = 200; // Значение по умолчанию
	}
	$position = get_post_meta($post->ID, '_notification_position', true);
	if (empty($position)) {
		$position = 'modal-bottom-center'; // Значение по умолчанию
	}
	
	// Trigger settings
	$trigger_type = get_post_meta($post->ID, '_notification_trigger_type', true);
	if (empty($trigger_type)) {
		$trigger_type = 'delay'; // Значение по умолчанию
	}
	$trigger_inactivity_delay = get_post_meta($post->ID, '_notification_trigger_inactivity_delay', true);
	if (empty($trigger_inactivity_delay)) {
		$trigger_inactivity_delay = 30000; // 30 секунд по умолчанию
	}
	$trigger_viewport_id = get_post_meta($post->ID, '_notification_trigger_viewport_id', true);
	$trigger_page_type = get_post_meta($post->ID, '_notification_trigger_page_type', true);
	$trigger_page_id = get_post_meta($post->ID, '_notification_trigger_page_id', true);
	
	// Convert stored values to datetime-local format (YYYY-MM-DDTHH:mm)
	$start_date_display = '';
	$end_date_display = '';
	if (!empty($start_date)) {
		$timestamp = strtotime($start_date);
		if ($timestamp) {
			// Convert to datetime-local format: YYYY-MM-DDTHH:mm
			$start_date_display = date('Y-m-d\TH:i', $timestamp);
		}
	}
	if (!empty($end_date)) {
		$timestamp = strtotime($end_date);
		if ($timestamp) {
			// Convert to datetime-local format: YYYY-MM-DDTHH:mm
			$end_date_display = date('Y-m-d\TH:i', $timestamp);
		}
	}
	
	// Get all modal posts
	$modals = get_posts(array(
		'post_type' => 'modal',
		'posts_per_page' => -1,
		'post_status' => 'publish',
		'orderby' => 'title',
		'order' => 'ASC'
	));
	
	?>
	<table class="form-table">
		<tr>
			<th scope="row">
				<label for="notification_modal_id"><?php esc_html_e('Modal', 'codeweber'); ?></label>
			</th>
			<td>
				<select 
					id="notification_modal_id" 
					name="notification_modal_id" 
					class="regular-text"
				>
					<option value=""><?php esc_html_e('— Select Modal —', 'codeweber'); ?></option>
					<?php foreach ($modals as $modal) : ?>
						<option value="<?php echo esc_attr($modal->ID); ?>" <?php selected($modal_id, $modal->ID); ?>>
							<?php echo esc_html($modal->post_title); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e('Select a modal to display for this notification', 'codeweber'); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="notification_start_date"><?php esc_html_e('Start Date', 'codeweber'); ?></label>
			</th>
			<td>
				<input 
					type="datetime-local" 
					id="notification_start_date" 
					name="notification_start_date" 
					value="<?php echo esc_attr($start_date_display); ?>" 
					class="regular-text"
				/>
				<p class="description">
					<?php esc_html_e('Date and time when the notification should start showing', 'codeweber'); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="notification_end_date"><?php esc_html_e('End Date', 'codeweber'); ?></label>
			</th>
			<td>
				<input 
					type="datetime-local" 
					id="notification_end_date" 
					name="notification_end_date" 
					value="<?php echo esc_attr($end_date_display); ?>" 
					class="regular-text"
				/>
				<p class="description">
					<?php esc_html_e('Date and time when the notification should stop showing', 'codeweber'); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="notification_wait_delay"><?php esc_html_e('Show Delay', 'codeweber'); ?></label>
			</th>
			<td>
				<input 
					type="number" 
					id="notification_wait_delay" 
					name="notification_wait_delay" 
					value="<?php echo esc_attr($wait_delay); ?>" 
					class="small-text"
					min="0"
					step="100"
				/>
				<span><?php esc_html_e('ms', 'codeweber'); ?></span>
				<p class="description">
					<?php esc_html_e('Delay before showing the notification modal (in milliseconds). Default: 200ms', 'codeweber'); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="notification_position"><?php esc_html_e('Position', 'codeweber'); ?></label>
			</th>
			<td>
				<select 
					id="notification_position" 
					name="notification_position" 
					class="regular-text"
				>
					<option value="modal-bottom-center" <?php selected($position, 'modal-bottom-center'); ?>>
						<?php esc_html_e('Bottom Center (Default)', 'codeweber'); ?>
					</option>
					<option value="modal-bottom-start" <?php selected($position, 'modal-bottom-start'); ?>>
						<?php esc_html_e('Bottom Left', 'codeweber'); ?>
					</option>
					<option value="modal-bottom-end" <?php selected($position, 'modal-bottom-end'); ?>>
						<?php esc_html_e('Bottom Right', 'codeweber'); ?>
					</option>
					<option value="modal-top-start" <?php selected($position, 'modal-top-start'); ?>>
						<?php esc_html_e('Top Left', 'codeweber'); ?>
					</option>
					<option value="modal-top-end" <?php selected($position, 'modal-top-end'); ?>>
						<?php esc_html_e('Top Right', 'codeweber'); ?>
					</option>
				</select>
				<p class="description">
					<?php esc_html_e('Position of the modal window on the screen', 'codeweber'); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="notification_trigger_type"><?php esc_html_e('Trigger Type', 'codeweber'); ?></label>
			</th>
			<td>
				<select 
					id="notification_trigger_type" 
					name="notification_trigger_type" 
					class="regular-text"
				>
					<option value="delay" <?php selected($trigger_type, 'delay'); ?>>
						<?php esc_html_e('Show Delay (Default)', 'codeweber'); ?>
					</option>
					<option value="inactivity" <?php selected($trigger_type, 'inactivity'); ?>>
						<?php esc_html_e('Inactivity (with delay)', 'codeweber'); ?>
					</option>
					<option value="viewport" <?php selected($trigger_type, 'viewport'); ?>>
						<?php esc_html_e('Viewport ID', 'codeweber'); ?>
					</option>
					<option value="scroll_middle" <?php selected($trigger_type, 'scroll_middle'); ?>>
						<?php esc_html_e('Scroll to Middle', 'codeweber'); ?>
					</option>
					<option value="scroll_end" <?php selected($trigger_type, 'scroll_end'); ?>>
						<?php esc_html_e('Scroll to End', 'codeweber'); ?>
					</option>
					<option value="codeweber_form" <?php selected($trigger_type, 'codeweber_form'); ?>>
						<?php esc_html_e('Codeweber Form Success', 'codeweber'); ?>
					</option>
					<option value="cf7_form" <?php selected($trigger_type, 'cf7_form'); ?>>
						<?php esc_html_e('CF7 Form Success', 'codeweber'); ?>
					</option>
					<option value="woocommerce_order" <?php selected($trigger_type, 'woocommerce_order'); ?>>
						<?php esc_html_e('WooCommerce Order Success', 'codeweber'); ?>
					</option>
					<option value="page" <?php selected($trigger_type, 'page'); ?>>
						<?php esc_html_e('Page/Post/Archive', 'codeweber'); ?>
					</option>
				</select>
				<p class="description">
					<?php esc_html_e('Select the trigger type for opening the modal', 'codeweber'); ?>
				</p>
			</td>
		</tr>
		<tr class="trigger-field trigger-inactivity" style="<?php echo ($trigger_type !== 'inactivity') ? 'display:none;' : ''; ?>">
			<th scope="row">
				<label for="notification_trigger_inactivity_delay"><?php esc_html_e('Inactivity Delay', 'codeweber'); ?></label>
			</th>
			<td>
				<input 
					type="number" 
					id="notification_trigger_inactivity_delay" 
					name="notification_trigger_inactivity_delay" 
					value="<?php echo esc_attr($trigger_inactivity_delay); ?>" 
					class="small-text"
					min="1000"
					step="1000"
				/>
				<span><?php esc_html_e('ms', 'codeweber'); ?></span>
				<p class="description">
					<?php esc_html_e('Time of inactivity before showing modal (in milliseconds). Default: 30000ms (30 seconds)', 'codeweber'); ?>
				</p>
			</td>
		</tr>
		<tr class="trigger-field trigger-viewport" style="<?php echo ($trigger_type !== 'viewport') ? 'display:none;' : ''; ?>">
			<th scope="row">
				<label for="notification_trigger_viewport_id"><?php esc_html_e('Viewport Element ID', 'codeweber'); ?></label>
			</th>
			<td>
				<input 
					type="text" 
					id="notification_trigger_viewport_id" 
					name="notification_trigger_viewport_id" 
					value="<?php echo esc_attr($trigger_viewport_id); ?>" 
					class="regular-text"
					placeholder="#element-id"
				/>
				<p class="description">
					<?php esc_html_e('ID of the element that should appear in viewport to trigger modal (with or without #)', 'codeweber'); ?>
				</p>
			</td>
		</tr>
		<tr class="trigger-field trigger-page" style="<?php echo ($trigger_type !== 'page') ? 'display:none;' : ''; ?>">
			<th scope="row">
				<label for="notification_trigger_page_type"><?php esc_html_e('Page Type', 'codeweber'); ?></label>
			</th>
			<td>
				<select 
					id="notification_trigger_page_type" 
					name="notification_trigger_page_type" 
					class="regular-text"
				>
					<option value=""><?php esc_html_e('— Select Type —', 'codeweber'); ?></option>
					<option value="page" <?php selected($trigger_page_type, 'page'); ?>>
						<?php esc_html_e('Page', 'codeweber'); ?>
					</option>
					<option value="post" <?php selected($trigger_page_type, 'post'); ?>>
						<?php esc_html_e('Post', 'codeweber'); ?>
					</option>
					<option value="archive" <?php selected($trigger_page_type, 'archive'); ?>>
						<?php esc_html_e('Archive', 'codeweber'); ?>
					</option>
					<?php
					// Get custom post types
					$custom_post_types = get_post_types(array('public' => true, '_builtin' => false), 'objects');
					foreach ($custom_post_types as $post_type) {
						if ($post_type->name !== 'modal' && $post_type->name !== 'notifications') {
							?>
							<option value="<?php echo esc_attr($post_type->name); ?>" <?php selected($trigger_page_type, $post_type->name); ?>>
								<?php echo esc_html($post_type->label); ?>
							</option>
							<?php
						}
					}
					?>
				</select>
				<p class="description">
					<?php esc_html_e('Select the type of page/post/archive', 'codeweber'); ?>
				</p>
			</td>
		</tr>
		<tr class="trigger-field trigger-page-id" style="<?php echo ($trigger_type !== 'page' || empty($trigger_page_type)) ? 'display:none;' : ''; ?>">
			<th scope="row">
				<label for="notification_trigger_page_id"><?php esc_html_e('Select Item', 'codeweber'); ?></label>
			</th>
			<td>
				<select 
					id="notification_trigger_page_id" 
					name="notification_trigger_page_id" 
					class="regular-text"
				>
					<option value=""><?php esc_html_e('— Select Item —', 'codeweber'); ?></option>
					<?php
					if (!empty($trigger_page_type)) {
						if ($trigger_page_type === 'archive') {
							// For archives, show available archive types
							?>
							<option value="all" <?php selected($trigger_page_id, 'all'); ?>>
								<?php esc_html_e('All Archives', 'codeweber'); ?>
							</option>
							<option value="category" <?php selected($trigger_page_id, 'category'); ?>>
								<?php esc_html_e('Category Archives', 'codeweber'); ?>
							</option>
							<option value="tag" <?php selected($trigger_page_id, 'tag'); ?>>
								<?php esc_html_e('Tag Archives', 'codeweber'); ?>
							</option>
							<?php
							// Get custom taxonomies
							$taxonomies = get_taxonomies(array('public' => true, '_builtin' => false), 'objects');
							foreach ($taxonomies as $taxonomy) {
								?>
								<option value="tax_<?php echo esc_attr($taxonomy->name); ?>" <?php selected($trigger_page_id, 'tax_' . $taxonomy->name); ?>>
									<?php echo esc_html($taxonomy->label . ' Archives'); ?>
								</option>
								<?php
							}
						} else {
							// For pages and posts, show list of items
							$posts = get_posts(array(
								'post_type' => $trigger_page_type,
								'posts_per_page' => -1,
								'post_status' => 'publish',
								'orderby' => 'title',
								'order' => 'ASC'
							));
							foreach ($posts as $item) {
								?>
								<option value="<?php echo esc_attr($item->ID); ?>" <?php selected($trigger_page_id, $item->ID); ?>>
									<?php echo esc_html($item->post_title); ?>
								</option>
								<?php
							}
						}
					}
					?>
				</select>
				<p class="description">
					<?php esc_html_e('Select specific page/post/archive', 'codeweber'); ?>
				</p>
			</td>
		</tr>
	</table>
	
	<script>
	jQuery(document).ready(function($) {
		// Show/hide trigger fields based on trigger type
		function toggleTriggerFields() {
			var triggerType = $('#notification_trigger_type').val();
			$('.trigger-field').hide();
			
			if (triggerType === 'inactivity') {
				$('.trigger-inactivity').show();
			} else if (triggerType === 'viewport') {
				$('.trigger-viewport').show();
			} else if (triggerType === 'page') {
				$('.trigger-page').show();
				// Load page items when type is selected
				loadPageItems();
			}
		}
		
		// Load page items based on page type
		function loadPageItems() {
			var pageType = $('#notification_trigger_page_type').val();
			var pageId = '<?php echo esc_js($trigger_page_id); ?>';
			var select = $('#notification_trigger_page_id');
			
			if (!pageType) {
				$('.trigger-page-id').hide();
				return;
			}
			
			$('.trigger-page-id').show();
			
			// Clear existing options except first
			select.find('option:not(:first)').remove();
			
			if (pageType === 'archive') {
				// Add archive options
				select.append('<option value="all"><?php echo esc_js(__('All Archives', 'codeweber')); ?></option>');
				select.append('<option value="category"><?php echo esc_js(__('Category Archives', 'codeweber')); ?></option>');
				select.append('<option value="tag"><?php echo esc_js(__('Tag Archives', 'codeweber')); ?></option>');
			} else {
				// Load posts via AJAX
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'codeweber_get_posts_for_trigger',
						post_type: pageType,
						nonce: '<?php echo wp_create_nonce('codeweber_trigger_nonce'); ?>'
					},
					success: function(response) {
						if (response.success && response.data) {
							$.each(response.data, function(id, title) {
								var selected = (id == pageId) ? 'selected' : '';
								select.append('<option value="' + id + '" ' + selected + '>' + title + '</option>');
							});
						}
					}
				});
			}
		}
		
		$('#notification_trigger_type').on('change', toggleTriggerFields);
		$('#notification_trigger_page_type').on('change', loadPageItems);
		
		// Initialize on page load
		toggleTriggerFields();
	});
	</script>
	<?php
}

/**
 * Save meta box data
 */
function codeweber_save_notifications_meta_box($post_id) {
	// Check if nonce is set
	if (!isset($_POST['notifications_meta_box_nonce'])) {
		return;
	}
	
	// Verify nonce
	if (!wp_verify_nonce($_POST['notifications_meta_box_nonce'], 'notifications_meta_box')) {
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
	if (get_post_type($post_id) !== 'notifications') {
		return;
	}
	
	// Save modal ID
	if (isset($_POST['notification_modal_id'])) {
		$modal_id = sanitize_text_field($_POST['notification_modal_id']);
		update_post_meta($post_id, '_notification_modal_id', $modal_id);
	} else {
		delete_post_meta($post_id, '_notification_modal_id');
	}
	
	// Save start date - convert from datetime-local format (YYYY-MM-DDTHH:mm) to standard format Y-m-d H:i:s for storage
	if (isset($_POST['notification_start_date']) && !empty($_POST['notification_start_date'])) {
		$start_date_input = sanitize_text_field($_POST['notification_start_date']);
		// datetime-local format: YYYY-MM-DDTHH:mm (e.g., 2024-01-15T14:30)
		// Parse the input date
		$date_obj = date_create_from_format('Y-m-d\TH:i', $start_date_input);
		if ($date_obj !== false) {
			// Store in standard format Y-m-d H:i:s for consistent storage
			$start_date = $date_obj->format('Y-m-d H:i:s');
			update_post_meta($post_id, '_notification_start_date', $start_date);
		} else {
			// Fallback: try strtotime (should handle datetime-local format)
			$timestamp = strtotime($start_date_input);
			if ($timestamp !== false) {
				$start_date = date('Y-m-d H:i:s', $timestamp);
				update_post_meta($post_id, '_notification_start_date', $start_date);
			}
		}
	} else {
		delete_post_meta($post_id, '_notification_start_date');
	}
	
	// Save end date - convert from datetime-local format (YYYY-MM-DDTHH:mm) to standard format Y-m-d H:i:s for storage
	if (isset($_POST['notification_end_date']) && !empty($_POST['notification_end_date'])) {
		$end_date_input = sanitize_text_field($_POST['notification_end_date']);
		// datetime-local format: YYYY-MM-DDTHH:mm (e.g., 2024-01-15T14:30)
		// Parse the input date
		$date_obj = date_create_from_format('Y-m-d\TH:i', $end_date_input);
		if ($date_obj !== false) {
			// Store in standard format Y-m-d H:i:s for consistent storage
			$end_date = $date_obj->format('Y-m-d H:i:s');
			update_post_meta($post_id, '_notification_end_date', $end_date);
		} else {
			// Fallback: try strtotime (should handle datetime-local format)
			$timestamp = strtotime($end_date_input);
			if ($timestamp !== false) {
				$end_date = date('Y-m-d H:i:s', $timestamp);
				update_post_meta($post_id, '_notification_end_date', $end_date);
			}
		}
	} else {
		delete_post_meta($post_id, '_notification_end_date');
	}
	
	// Save wait delay
	if (isset($_POST['notification_wait_delay']) && !empty($_POST['notification_wait_delay'])) {
		$wait_delay = absint($_POST['notification_wait_delay']);
		update_post_meta($post_id, '_notification_wait_delay', $wait_delay);
	} else {
		// Если не указано, используем значение по умолчанию
		update_post_meta($post_id, '_notification_wait_delay', 200);
	}
	
	// Save position
	if (isset($_POST['notification_position']) && !empty($_POST['notification_position'])) {
		$position = sanitize_text_field($_POST['notification_position']);
		// Validate position value
		$valid_positions = array('modal-bottom-center', 'modal-bottom-start', 'modal-bottom-end', 'modal-top-start', 'modal-top-end');
		if (in_array($position, $valid_positions)) {
			update_post_meta($post_id, '_notification_position', $position);
		} else {
			update_post_meta($post_id, '_notification_position', 'modal-bottom-center');
		}
	} else {
		update_post_meta($post_id, '_notification_position', 'modal-bottom-center');
	}
	
	// Save trigger type
	if (isset($_POST['notification_trigger_type'])) {
		$trigger_type = sanitize_text_field($_POST['notification_trigger_type']);
		$valid_triggers = array('delay', 'inactivity', 'viewport', 'scroll_middle', 'scroll_end', 'codeweber_form', 'cf7_form', 'woocommerce_order', 'page');
		if (in_array($trigger_type, $valid_triggers)) {
			update_post_meta($post_id, '_notification_trigger_type', $trigger_type);
		} else {
			update_post_meta($post_id, '_notification_trigger_type', 'delay');
		}
	} else {
		update_post_meta($post_id, '_notification_trigger_type', 'delay');
	}
	
	// Save trigger inactivity delay
	if (isset($_POST['notification_trigger_inactivity_delay']) && !empty($_POST['notification_trigger_inactivity_delay'])) {
		$inactivity_delay = absint($_POST['notification_trigger_inactivity_delay']);
		update_post_meta($post_id, '_notification_trigger_inactivity_delay', $inactivity_delay);
	} else {
		update_post_meta($post_id, '_notification_trigger_inactivity_delay', 30000);
	}
	
	// Save trigger viewport ID
	if (isset($_POST['notification_trigger_viewport_id'])) {
		$viewport_id = sanitize_text_field($_POST['notification_trigger_viewport_id']);
		update_post_meta($post_id, '_notification_trigger_viewport_id', $viewport_id);
	} else {
		delete_post_meta($post_id, '_notification_trigger_viewport_id');
	}
	
	// Save trigger page type
	if (isset($_POST['notification_trigger_page_type'])) {
		$page_type = sanitize_text_field($_POST['notification_trigger_page_type']);
		update_post_meta($post_id, '_notification_trigger_page_type', $page_type);
	} else {
		delete_post_meta($post_id, '_notification_trigger_page_type');
	}
	
	// Save trigger page ID
	if (isset($_POST['notification_trigger_page_id'])) {
		$page_id = sanitize_text_field($_POST['notification_trigger_page_id']);
		update_post_meta($post_id, '_notification_trigger_page_id', $page_id);
	} else {
		delete_post_meta($post_id, '_notification_trigger_page_id');
	}
}
add_action('save_post', 'codeweber_save_notifications_meta_box');

/**
 * Disable Gutenberg editor for notifications
 */
add_filter('use_block_editor_for_post_type', 'disable_gutenberg_for_notifications', 10, 2);
function disable_gutenberg_for_notifications($current_status, $post_type) {
	if ($post_type === 'notifications') {
		return false;
	}
	return $current_status;
}

/**
 * Отключаем single notifications страницы - возвращаем 404
 */
add_action('template_redirect', function() {
	if (is_singular('notifications')) {
		global $wp_query;
		$wp_query->set_404();
		status_header(404);
	}
});

/**
 * Проверяет активное уведомление и возвращает информацию о modal
 */
function codeweber_get_active_notification_modal() {
	// Не проверяем в админке
	if (is_admin()) {
		return false;
	}
	
	$current_time = current_time('timestamp');
	$debug_mode = current_user_can('manage_options') && isset($_GET['debug_notifications']);
	
	// Получаем все опубликованные уведомления
	$notifications = get_posts(array(
		'post_type' => 'notifications',
		'posts_per_page' => -1,
		'post_status' => 'publish',
		'meta_query' => array(
			array(
				'key' => '_notification_modal_id',
				'compare' => 'EXISTS'
			),
		)
	));
	
	if ($debug_mode) {
		error_log('DEBUG Notifications: Found ' . count($notifications) . ' notifications');
	}
	
	foreach ($notifications as $notification) {
		$modal_id = get_post_meta($notification->ID, '_notification_modal_id', true);
		$start_date = get_post_meta($notification->ID, '_notification_start_date', true);
		$end_date = get_post_meta($notification->ID, '_notification_end_date', true);
		$wait_delay = get_post_meta($notification->ID, '_notification_wait_delay', true);
		$position = get_post_meta($notification->ID, '_notification_position', true);
		if (empty($position)) {
			$position = 'modal-bottom-center'; // Значение по умолчанию
		}
		
		// Get trigger settings
		$trigger_type = get_post_meta($notification->ID, '_notification_trigger_type', true);
		if (empty($trigger_type)) {
			$trigger_type = 'delay'; // Значение по умолчанию
		}
		
		// Check page/post/archive trigger
		if ($trigger_type === 'page') {
			$trigger_page_type = get_post_meta($notification->ID, '_notification_trigger_page_type', true);
			$trigger_page_id = get_post_meta($notification->ID, '_notification_trigger_page_id', true);
			
			if (!empty($trigger_page_type) && !empty($trigger_page_id)) {
				$match = false;
				
				if ($trigger_page_type === 'archive') {
					// Check archive types
					if ($trigger_page_id === 'all' && (is_archive() || is_category() || is_tag() || is_tax())) {
						$match = true;
					} elseif ($trigger_page_id === 'category' && is_category()) {
						$match = true;
					} elseif ($trigger_page_id === 'tag' && is_tag()) {
						$match = true;
					} elseif (strpos($trigger_page_id, 'tax_') === 0) {
						$taxonomy = str_replace('tax_', '', $trigger_page_id);
						if (is_tax($taxonomy)) {
							$match = true;
						}
					}
				} else {
					// Check specific page/post
					if (is_singular($trigger_page_type) && get_the_ID() == $trigger_page_id) {
						$match = true;
					}
				}
				
				if (!$match) {
					continue; // Skip this notification if page doesn't match
				}
			} else {
				continue; // Skip if page trigger is not properly configured
			}
		}
		
		if ($debug_mode) {
			error_log('DEBUG Notification #' . $notification->ID . ': modal_id=' . $modal_id . ', start=' . $start_date . ', end=' . $end_date);
		}
		
		if (empty($modal_id)) {
			if ($debug_mode) {
				error_log('DEBUG Notification #' . $notification->ID . ': modal_id is empty, skipping');
			}
			continue;
		}
		
		// Проверяем даты
		$start_timestamp = 0;
		$end_timestamp = PHP_INT_MAX;
		
		if (!empty($start_date)) {
			// Пробуем разные форматы
			$start_timestamp = strtotime($start_date);
			if ($start_timestamp === false) {
				// Пробуем парсить как Y-m-d H:i:s
				$date_obj = date_create_from_format('Y-m-d H:i:s', $start_date);
				if ($date_obj !== false) {
					$start_timestamp = $date_obj->getTimestamp();
				} else {
					$start_timestamp = 0;
				}
			}
		}
		
		if (!empty($end_date)) {
			// Пробуем разные форматы
			$end_timestamp = strtotime($end_date);
			if ($end_timestamp === false) {
				// Пробуем парсить как Y-m-d H:i:s
				$date_obj = date_create_from_format('Y-m-d H:i:s', $end_date);
				if ($date_obj !== false) {
					$end_timestamp = $date_obj->getTimestamp();
				} else {
					$end_timestamp = PHP_INT_MAX;
				}
			}
		}
		
		if ($debug_mode) {
			error_log('DEBUG Notification #' . $notification->ID . ': current_time=' . $current_time . ' (' . date('Y-m-d H:i:s', $current_time) . '), start_ts=' . $start_timestamp . ' (' . ($start_timestamp > 0 ? date('Y-m-d H:i:s', $start_timestamp) : 'none') . '), end_ts=' . $end_timestamp . ' (' . ($end_timestamp < PHP_INT_MAX ? date('Y-m-d H:i:s', $end_timestamp) : 'none') . ')');
		}
		
		// Проверяем, находится ли текущее время в диапазоне
		if ($current_time >= $start_timestamp && $current_time <= $end_timestamp) {
			if ($debug_mode) {
				error_log('DEBUG Notification #' . $notification->ID . ': Date range is OK, MATCH FOUND!');
			}
			
			// Получаем размер modal из Redux
			$modal_size = '';
			if (class_exists('Redux')) {
				global $opt_name;
				if (isset($opt_name)) {
					$modal_size = Redux::get_post_meta($opt_name, $modal_id, 'modal-size');
					$modal_size = $modal_size ? $modal_size : '';
				}
			}
			
			// Get trigger settings
			$trigger_inactivity_delay = get_post_meta($notification->ID, '_notification_trigger_inactivity_delay', true);
			$trigger_viewport_id = get_post_meta($notification->ID, '_notification_trigger_viewport_id', true);
			
			return array(
				'notification_id' => $notification->ID,
				'modal_id' => $modal_id,
				'modal_content' => get_post_field('post_content', $modal_id),
				'wait_delay' => !empty($wait_delay) ? absint($wait_delay) : 200,
				'position' => $position,
				'size' => $modal_size,
				'trigger_type' => $trigger_type,
				'trigger_inactivity_delay' => !empty($trigger_inactivity_delay) ? absint($trigger_inactivity_delay) : 30000,
				'trigger_viewport_id' => $trigger_viewport_id
			);
		} else {
			if ($debug_mode) {
				error_log('DEBUG Notification #' . $notification->ID . ': Date range is OUT OF RANGE');
			}
		}
	}
	
	if ($debug_mode) {
		error_log('DEBUG Notifications: No active notification found');
	}
	
	return false;
}

/**
 * AJAX handler for loading posts for trigger selection
 */
add_action('wp_ajax_codeweber_get_posts_for_trigger', 'codeweber_get_posts_for_trigger_handler');
function codeweber_get_posts_for_trigger_handler() {
	check_ajax_referer('codeweber_trigger_nonce', 'nonce');
	
	$post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : '';
	
	if (empty($post_type)) {
		wp_send_json_error('Post type is required');
	}
	
	$posts = get_posts(array(
		'post_type' => $post_type,
		'posts_per_page' => -1,
		'post_status' => 'publish',
		'orderby' => 'title',
		'order' => 'ASC'
	));
	
	$result = array();
	foreach ($posts as $post) {
		$result[$post->ID] = $post->post_title;
	}
	
	wp_send_json_success($result);
}


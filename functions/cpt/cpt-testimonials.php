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
        "all_items" => esc_html__("All Testimonials", "codeweber"),
        "add_new" => esc_html__("Add New", "codeweber"),
        "add_new_item" => esc_html__("Add New Testimonial", "codeweber"),
        "edit_item" => esc_html__("Edit Testimonial", "codeweber"),
        "new_item" => esc_html__("New Testimonial", "codeweber"),
        "view_item" => esc_html__("View Testimonial", "codeweber"),
        "view_items" => esc_html__("View Testimonials", "codeweber"),
        "search_items" => esc_html__("Search Testimonials", "codeweber"),
        "not_found" => esc_html__("No testimonials found", "codeweber"),
        "not_found_in_trash" => esc_html__("No testimonials found in trash", "codeweber"),
        "parent_item_colon" => esc_html__("Parent Testimonial:", "codeweber"),
        "archives" => esc_html__("Testimonial Archives", "codeweber"),
        "attributes" => esc_html__("Testimonial Attributes", "codeweber"),
        "insert_into_item" => esc_html__("Insert into testimonial", "codeweber"),
        "uploaded_to_this_item" => esc_html__("Uploaded to this testimonial", "codeweber"),
        "featured_image" => esc_html__("Featured Image", "codeweber"),
        "set_featured_image" => esc_html__("Set featured image", "codeweber"),
        "remove_featured_image" => esc_html__("Remove featured image", "codeweber"),
        "use_featured_image" => esc_html__("Use as featured image", "codeweber"),
        "filter_items_list" => esc_html__("Filter testimonials list", "codeweber"),
        "items_list_navigation" => esc_html__("Testimonials list navigation", "codeweber"),
        "items_list" => esc_html__("Testimonials list", "codeweber"),
        "item_published" => esc_html__("Testimonial published", "codeweber"),
        "item_published_privately" => esc_html__("Testimonial published privately", "codeweber"),
        "item_reverted_to_draft" => esc_html__("Testimonial reverted to draft", "codeweber"),
        "item_scheduled" => esc_html__("Testimonial scheduled", "codeweber"),
        "item_updated" => esc_html__("Testimonial updated", "codeweber"),
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
        "supports" => ["title", "revisions"],
        "show_in_graphql" => false,
    ];

    register_post_type("testimonials", $args);
}

add_action('init', 'cptui_register_my_cpts_testimonials');

/**
 * Disable Gutenberg for testimonials
 */
add_filter('use_block_editor_for_post_type', 'disable_gutenberg_for_testimonials', 10, 2);
function disable_gutenberg_for_testimonials($current_status, $post_type)
{
    if ($post_type === 'testimonials') {
        return false;
    }
    return $current_status;
}

/**
 * Disable comments and discussions for testimonials
 */
function codeweber_disable_testimonials_comments($open, $post_id)
{
    $post = get_post($post_id);
    if ($post && $post->post_type === 'testimonials') {
        return false;
    }
    return $open;
}
add_filter('comments_open', 'codeweber_disable_testimonials_comments', 10, 2);
add_filter('pings_open', 'codeweber_disable_testimonials_comments', 10, 2);

/**
 * Remove comments and discussions meta boxes from testimonials
 */
function codeweber_remove_testimonials_comment_meta_boxes()
{
    remove_meta_box('commentstatusdiv', 'testimonials', 'normal');
    remove_meta_box('commentsdiv', 'testimonials', 'normal');
    remove_meta_box('trackbacksdiv', 'testimonials', 'normal');
}
add_action('admin_menu', 'codeweber_remove_testimonials_comment_meta_boxes');

/**
 * Disable comments column for testimonials
 */
function codeweber_remove_testimonials_comments_column($columns)
{
    unset($columns['comments']);
    return $columns;
}
add_filter('manage_testimonials_posts_columns', 'codeweber_remove_testimonials_comments_column');

/**
 * Add metabox with additional fields for CPT testimonials
 */
function codeweber_add_testimonials_meta_boxes()
{
    add_meta_box(
        'testimonials_details',
        __('Testimonial Information', 'codeweber'),
        'codeweber_testimonials_meta_box_callback',
        'testimonials',
        'normal',
        'high'
    );

    add_meta_box(
        'testimonials_status',
        __('Testimonial Status', 'codeweber'),
        'codeweber_testimonials_status_callback',
        'testimonials',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'codeweber_add_testimonials_meta_boxes');

/**
 * Callback function for displaying the testimonial details metabox
 */
function codeweber_testimonials_meta_box_callback($post)
{
    // Add nonce for security
    wp_nonce_field('testimonials_meta_box', 'testimonials_meta_box_nonce');

    // Get existing field values
    $testimonial_text = get_post_meta($post->ID, '_testimonial_text', true);
    $author_type = get_post_meta($post->ID, '_testimonial_author_type', true);
    if (empty($author_type)) {
        $author_type = 'custom'; // Default: custom
    }
    $author_user_id = get_post_meta($post->ID, '_testimonial_author_user_id', true);
    $author_name = get_post_meta($post->ID, '_testimonial_author_name', true);
    $author_role = get_post_meta($post->ID, '_testimonial_author_role', true);
    $company = get_post_meta($post->ID, '_testimonial_company', true);
    $rating = get_post_meta($post->ID, '_testimonial_rating', true);
    $avatar_id = get_post_meta($post->ID, '_testimonial_avatar', true);
    $avatar_url = $avatar_id ? wp_get_attachment_image_url($avatar_id, 'thumbnail') : '';

    // Get all users for dropdown
    $users = get_users(['orderby' => 'display_name']);
?>

    <div style="display: grid; grid-template-columns: 150px 1fr; gap: 15px; align-items: start; margin-bottom: 20px;">
        <label for="testimonial_text" style="font-weight: bold;"><strong><?php _e('Testimonial Text:', 'codeweber'); ?></strong></label>
        <div>
            <?php
            wp_editor($testimonial_text, 'testimonial_text', [
                'textarea_name' => 'testimonial_text',
                'textarea_rows' => 6,
                'media_buttons' => false,
                'teeny' => true
            ]);
            ?>
            <p class="description"><?php _e('Enter the testimonial text/quote', 'codeweber'); ?></p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 150px 1fr; gap: 15px; align-items: center; margin-bottom: 20px; padding: 15px; background: #f5f5f5; border-radius: 4px;">
        <label style="font-weight: bold;"><strong><?php _e('Author Type:', 'codeweber'); ?></strong></label>
        <div>
            <label style="margin-right: 20px;">
                <input type="radio" name="testimonial_author_type" value="custom" <?php checked($author_type, 'custom'); ?>>
                <?php _e('Custom (Manual Entry)', 'codeweber'); ?>
            </label>
            <label>
                <input type="radio" name="testimonial_author_type" value="user" <?php checked($author_type, 'user'); ?>>
                <?php _e('Registered User', 'codeweber'); ?>
            </label>
        </div>
    </div>

    <!-- Custom Author Fields -->
    <div id="testimonial_custom_fields" style="<?php echo ($author_type === 'user') ? 'display: none;' : ''; ?>">
        <div style="display: grid; grid-template-columns: 150px 1fr; gap: 15px; align-items: center; margin-bottom: 15px;">
            <label for="testimonial_author_name"><strong><?php _e('Author Name:', 'codeweber'); ?></strong></label>
            <input type="text" id="testimonial_author_name" name="testimonial_author_name" value="<?php echo esc_attr($author_name); ?>" style="width: 100%; padding: 8px;" placeholder="<?php _e('John Doe', 'codeweber'); ?>">
        </div>

        <div style="display: grid; grid-template-columns: 150px 1fr; gap: 15px; align-items: center; margin-bottom: 15px;">
            <label for="testimonial_author_role"><strong><?php _e('Author Role/Position:', 'codeweber'); ?></strong></label>
            <input type="text" id="testimonial_author_role" name="testimonial_author_role" value="<?php echo esc_attr($author_role); ?>" style="width: 100%; padding: 8px;" placeholder="<?php _e('Financial Analyst', 'codeweber'); ?>">
        </div>

        <div style="display: grid; grid-template-columns: 150px 1fr; gap: 15px; align-items: center; margin-bottom: 15px;">
            <label for="testimonial_company"><strong><?php _e('Company:', 'codeweber'); ?></strong></label>
            <input type="text" id="testimonial_company" name="testimonial_company" value="<?php echo esc_attr($company); ?>" style="width: 100%; padding: 8px;" placeholder="<?php _e('Company Name', 'codeweber'); ?>">
        </div>

        <div style="display: grid; grid-template-columns: 150px 1fr; gap: 15px; align-items: start; margin-bottom: 15px;">
            <label for="testimonial_avatar"><strong><?php _e('Author Avatar:', 'codeweber'); ?></strong></label>
            <div>
                <input type="hidden" id="testimonial_avatar_id" name="testimonial_avatar_id" value="<?php echo esc_attr($avatar_id); ?>">
                <div id="testimonial_avatar_preview" style="margin-bottom: 10px;">
                    <?php if ($avatar_url): ?>
                        <img src="<?php echo esc_url($avatar_url); ?>" style="max-width: 150px; height: auto; display: block; margin-bottom: 10px;">
                    <?php endif; ?>
                </div>
                <button type="button" id="upload_testimonial_avatar" class="button"><?php _e('Upload Avatar', 'codeweber'); ?></button>
                <button type="button" id="remove_testimonial_avatar" class="button" style="<?php echo empty($avatar_url) ? 'display: none;' : ''; ?>">
                    <?php _e('Remove Avatar', 'codeweber'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Registered User Fields -->
    <div id="testimonial_user_fields" style="<?php echo ($author_type === 'custom') ? 'display: none;' : ''; ?>">
        <div style="display: grid; grid-template-columns: 150px 1fr; gap: 15px; align-items: center; margin-bottom: 15px;">
            <label for="testimonial_author_user_id"><strong><?php _e('Select User:', 'codeweber'); ?></strong></label>
            <select id="testimonial_author_user_id" name="testimonial_author_user_id" style="width: 100%; padding: 8px;">
                <option value=""><?php _e('-- Select User --', 'codeweber'); ?></option>
                <?php foreach ($users as $user): 
                    $user_avatar = get_avatar_url($user->ID, ['size' => 32]);
                    $display_name = $user->display_name . ' (' . $user->user_email . ')';
                ?>
                    <option value="<?php echo esc_attr($user->ID); ?>" <?php selected($author_user_id, $user->ID); ?>>
                        <?php echo esc_html($display_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="description" style="grid-column: 2;"><?php _e('Select a registered user. Their name, email, and avatar will be used automatically.', 'codeweber'); ?></p>
        </div>

        <div id="testimonial_user_info" style="display: grid; grid-template-columns: 150px 1fr; gap: 15px; align-items: start; margin-bottom: 15px; padding: 15px; background: #f9f9f9; border-radius: 4px; <?php echo empty($author_user_id) ? 'display: none;' : ''; ?>">
            <label><strong><?php _e('User Info:', 'codeweber'); ?></strong></label>
            <div id="testimonial_user_info_content">
                <?php if ($author_user_id): 
                    $selected_user = get_userdata($author_user_id);
                    if ($selected_user):
                        $user_avatar_url = get_avatar_url($author_user_id, ['size' => 96]);
                        $user_role = $selected_user->roles ? implode(', ', $selected_user->roles) : '';
                ?>
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <img src="<?php echo esc_url($user_avatar_url); ?>" style="width: 64px; height: 64px; border-radius: 50%;">
                        <div>
                            <strong><?php echo esc_html($selected_user->display_name); ?></strong><br>
                            <small><?php echo esc_html($selected_user->user_email); ?></small><br>
                            <?php if ($user_role): ?>
                                <small style="color: #666;"><?php echo esc_html($user_role); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; endif; ?>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 150px 1fr; gap: 15px; align-items: center; margin-bottom: 15px;">
            <label for="testimonial_company_user"><strong><?php _e('Company:', 'codeweber'); ?></strong></label>
            <input type="text" id="testimonial_company_user" name="testimonial_company" value="<?php echo esc_attr($company); ?>" style="width: 100%; padding: 8px;" placeholder="<?php _e('Company Name (optional)', 'codeweber'); ?>">
            <p class="description" style="grid-column: 2;"><?php _e('Optional: Override company name for this testimonial', 'codeweber'); ?></p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 150px 1fr; gap: 15px; align-items: center; margin-bottom: 15px;">
        <label for="testimonial_rating"><strong><?php _e('Rating:', 'codeweber'); ?></strong></label>
        <select id="testimonial_rating" name="testimonial_rating" style="width: 100%; padding: 8px;">
            <option value=""><?php _e('Select rating', 'codeweber'); ?></option>
            <option value="1" <?php selected($rating, '1'); ?>>1 <?php _e('star', 'codeweber'); ?></option>
            <option value="2" <?php selected($rating, '2'); ?>>2 <?php _e('stars', 'codeweber'); ?></option>
            <option value="3" <?php selected($rating, '3'); ?>>3 <?php _e('stars', 'codeweber'); ?></option>
            <option value="4" <?php selected($rating, '4'); ?>>4 <?php _e('stars', 'codeweber'); ?></option>
            <option value="5" <?php selected($rating, '5'); ?>>5 <?php _e('stars', 'codeweber'); ?></option>
        </select>
    </div>

    <script>
        jQuery(document).ready(function($) {
            var frame;

            // Toggle between custom and user fields
            $('input[name="testimonial_author_type"]').change(function() {
                var authorType = $(this).val();
                if (authorType === 'user') {
                    $('#testimonial_custom_fields').hide();
                    $('#testimonial_user_fields').show();
                } else {
                    $('#testimonial_custom_fields').show();
                    $('#testimonial_user_fields').hide();
                }
            });

            // Update user info when user is selected
            $('#testimonial_author_user_id').change(function() {
                var userId = $(this).val();
                if (userId) {
                    // AJAX request to get user info
                    $.ajax({
                        url: typeof testimonialAjax !== 'undefined' ? testimonialAjax.ajaxurl : ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'get_testimonial_user_info',
                            user_id: userId,
                            nonce: typeof testimonialAjax !== 'undefined' ? testimonialAjax.nonce : '<?php echo wp_create_nonce('testimonial_user_info'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#testimonial_user_info').show();
                                $('#testimonial_user_info_content').html(response.data.html);
                            }
                        }
                    });
                } else {
                    $('#testimonial_user_info').hide();
                }
            });

            // Avatar upload for custom author
            $('#upload_testimonial_avatar').click(function(e) {
                e.preventDefault();

                if (frame) {
                    frame.open();
                    return;
                }

                frame = wp.media({
                    title: '<?php _e('Select Avatar Image', 'codeweber'); ?>',
                    button: {
                        text: '<?php _e('Use this image', 'codeweber'); ?>'
                    },
                    multiple: false,
                    library: {
                        type: 'image'
                    }
                });

                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#testimonial_avatar_id').val(attachment.id);
                    $('#testimonial_avatar_preview').html('<img src="' + attachment.sizes.thumbnail.url + '" style="max-width: 150px; height: auto; display: block; margin-bottom: 10px;">');
                    $('#remove_testimonial_avatar').show();
                });

                frame.open();
            });

            $('#remove_testimonial_avatar').click(function() {
                $('#testimonial_avatar_id').val('');
                $('#testimonial_avatar_preview').html('');
                $(this).hide();
            });
        });
    </script>
<?php
}

/**
 * Callback function for displaying the testimonial status metabox
 */
function codeweber_testimonials_status_callback($post)
{
    wp_nonce_field('testimonials_meta_box', 'testimonials_meta_box_nonce');

    $status = get_post_meta($post->ID, '_testimonial_status', true);
    if (empty($status)) {
        $status = 'pending'; // Default: pending
    }
?>
    <div>
        <label for="testimonial_status" style="display: block; margin-bottom: 10px; font-weight: bold;">
            <?php _e('Status:', 'codeweber'); ?>
        </label>
        <select id="testimonial_status" name="testimonial_status" style="width: 100%; padding: 8px;">
            <option value="pending" <?php selected($status, 'pending'); ?>><?php _e('Pending', 'codeweber'); ?></option>
            <option value="approved" <?php selected($status, 'approved'); ?>><?php _e('Approved', 'codeweber'); ?></option>
            <option value="rejected" <?php selected($status, 'rejected'); ?>><?php _e('Rejected', 'codeweber'); ?></option>
        </select>
        <p class="description" style="margin-top: 10px; font-size: 12px; color: #666;">
            <?php _e('Select the status of this testimonial', 'codeweber'); ?>
        </p>
    </div>
<?php
}

/**
 * AJAX handler to get user info
 */
function codeweber_get_testimonial_user_info() {
    check_ajax_referer('testimonial_user_info', 'nonce');
    
    $user_id = intval($_POST['user_id']);
    if (!$user_id) {
        wp_send_json_error(['message' => __('Invalid user ID', 'codeweber')]);
    }
    
    $user = get_userdata($user_id);
    if (!$user) {
        wp_send_json_error(['message' => __('User not found', 'codeweber')]);
    }
    
    $user_avatar_url = get_avatar_url($user_id, ['size' => 96]);
    $user_role = $user->roles ? implode(', ', $user->roles) : '';
    
    $html = '<div style="display: flex; align-items: center; gap: 15px;">';
    $html .= '<img src="' . esc_url($user_avatar_url) . '" style="width: 64px; height: 64px; border-radius: 50%;">';
    $html .= '<div>';
    $html .= '<strong>' . esc_html($user->display_name) . '</strong><br>';
    $html .= '<small>' . esc_html($user->user_email) . '</small><br>';
    if ($user_role) {
        $html .= '<small style="color: #666;">' . esc_html($user_role) . '</small>';
    }
    $html .= '</div>';
    $html .= '</div>';
    
    wp_send_json_success(['html' => $html]);
}
add_action('wp_ajax_get_testimonial_user_info', 'codeweber_get_testimonial_user_info');

/**
 * Save metadata fields
 */
function codeweber_save_testimonials_meta($post_id)
{
    // Skip if this is a REST API request (meta fields are handled by REST API)
    if (defined('REST_REQUEST') && REST_REQUEST) {
        return;
    }
    
    // Check nonce
    if (!isset($_POST['testimonials_meta_box_nonce']) || !wp_verify_nonce($_POST['testimonials_meta_box_nonce'], 'testimonials_meta_box')) {
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

    // Check post type
    if (get_post_type($post_id) !== 'testimonials') {
        return;
    }

    // Save author type
    if (isset($_POST['testimonial_author_type'])) {
        update_post_meta($post_id, '_testimonial_author_type', sanitize_text_field($_POST['testimonial_author_type']));
    }

    // Save user ID if user type is selected
    if (isset($_POST['testimonial_author_type']) && $_POST['testimonial_author_type'] === 'user') {
        if (isset($_POST['testimonial_author_user_id'])) {
            $user_id = intval($_POST['testimonial_author_user_id']);
            update_post_meta($post_id, '_testimonial_author_user_id', $user_id);
            
            // Auto-fill from user data if not set
            if ($user_id) {
                $user = get_userdata($user_id);
                if ($user) {
                    // Only update if custom fields are empty
                    if (empty(get_post_meta($post_id, '_testimonial_author_name', true))) {
                        update_post_meta($post_id, '_testimonial_author_name', $user->display_name);
                    }
                    // Get user role from meta or use first role
                    $user_role_meta = get_user_meta($user_id, 'user_role', true);
                    if (empty($user_role_meta) && !empty($user->roles)) {
                        $user_role_meta = ucfirst($user->roles[0]);
                    }
                    if (empty(get_post_meta($post_id, '_testimonial_author_role', true)) && $user_role_meta) {
                        update_post_meta($post_id, '_testimonial_author_role', $user_role_meta);
                    }
                }
            }
        }
    } else {
        // Clear user ID if custom type
        delete_post_meta($post_id, '_testimonial_author_user_id');
    }

    // Save fields
    $fields = [
        'testimonial_text',
        'testimonial_author_name',
        'testimonial_author_role',
        'testimonial_company',
        'testimonial_rating',
        'testimonial_status'
    ];

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            if ($field === 'testimonial_text') {
                update_post_meta($post_id, '_' . $field, wp_kses_post($_POST[$field]));
            } else {
                update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
            }
        }
    }

    // Save avatar (only for custom type)
    if (isset($_POST['testimonial_author_type']) && $_POST['testimonial_author_type'] === 'custom') {
        if (isset($_POST['testimonial_avatar_id'])) {
            update_post_meta($post_id, '_testimonial_avatar', intval($_POST['testimonial_avatar_id']));
        }
    } else {
        // Clear custom avatar if user type
        delete_post_meta($post_id, '_testimonial_avatar');
    }
}
add_action('save_post_testimonials', 'codeweber_save_testimonials_meta');

/**
 * Add columns to admin for CPT testimonials
 */
function codeweber_add_testimonials_admin_columns($columns)
{
    $new_columns = [
        'cb' => $columns['cb'],
        'title' => $columns['title'],
        'testimonial_author' => __('Author', 'codeweber'),
        'testimonial_company' => __('Company', 'codeweber'),
        'testimonial_rating' => __('Rating', 'codeweber'),
        'testimonial_status' => __('Status', 'codeweber'),
        'date' => $columns['date']
    ];
    return $new_columns;
}
add_filter('manage_testimonials_posts_columns', 'codeweber_add_testimonials_admin_columns');

/**
 * Fill columns with data
 */
function codeweber_fill_testimonials_admin_columns($column, $post_id)
{
    switch ($column) {
        case 'testimonial_author':
            $author_type = get_post_meta($post_id, '_testimonial_author_type', true);
            if (empty($author_type)) {
                $author_type = 'custom';
            }
            
            if ($author_type === 'user') {
                $user_id = get_post_meta($post_id, '_testimonial_author_user_id', true);
                if ($user_id) {
                    $user = get_userdata($user_id);
                    if ($user) {
                        echo '<strong>' . esc_html($user->display_name) . '</strong>';
                        echo '<br><small style="color: #666;">' . esc_html($user->user_email) . '</small>';
                        $user_role = get_post_meta($post_id, '_testimonial_author_role', true);
                        if ($user_role) {
                            echo '<br><small style="color: #999;">' . esc_html($user_role) . '</small>';
                        }
                        echo '<br><small style="color: #0073aa;">[' . __('User', 'codeweber') . ']</small>';
                    } else {
                        echo esc_html__('—', 'codeweber');
                    }
                } else {
                    echo esc_html__('—', 'codeweber');
                }
            } else {
                $name = get_post_meta($post_id, '_testimonial_author_name', true);
                $role = get_post_meta($post_id, '_testimonial_author_role', true);
                if ($name) {
                    echo '<strong>' . esc_html($name) . '</strong>';
                    if ($role) {
                        echo '<br><small style="color: #666;">' . esc_html($role) . '</small>';
                    }
                    echo '<br><small style="color: #999;">[' . __('Custom', 'codeweber') . ']</small>';
                } else {
                    echo esc_html__('—', 'codeweber');
                }
            }
            break;
        case 'testimonial_company':
            echo esc_html(get_post_meta($post_id, '_testimonial_company', true));
            break;
        case 'testimonial_rating':
            $rating = get_post_meta($post_id, '_testimonial_rating', true);
            if ($rating) {
                echo esc_html($rating) . ' ' . __('stars', 'codeweber');
            } else {
                echo esc_html__('—', 'codeweber');
            }
            break;
        case 'testimonial_status':
            $status = get_post_meta($post_id, '_testimonial_status', true);
            if (empty($status)) {
                $status = 'pending';
            }
            $status_labels = [
                'pending' => __('Pending', 'codeweber'),
                'approved' => __('Approved', 'codeweber'),
                'rejected' => __('Rejected', 'codeweber')
            ];
            $status_colors = [
                'pending' => '#f0ad4e',
                'approved' => '#5cb85c',
                'rejected' => '#d9534f'
            ];
            $label = isset($status_labels[$status]) ? $status_labels[$status] : $status;
            $color = isset($status_colors[$status]) ? $status_colors[$status] : '#666';
            echo '<span style="display: inline-block; padding: 3px 8px; background: ' . esc_attr($color) . '; color: #fff; border-radius: 3px; font-size: 11px;">' . esc_html($label) . '</span>';
            break;
    }
}
add_action('manage_testimonials_posts_custom_column', 'codeweber_fill_testimonials_admin_columns', 10, 2);

/**
 * Make columns sortable
 */
function codeweber_make_testimonials_columns_sortable($columns)
{
    $columns['testimonial_author'] = 'testimonial_author';
    $columns['testimonial_company'] = 'testimonial_company';
    $columns['testimonial_status'] = 'testimonial_status';
    return $columns;
}
add_filter('manage_edit-testimonials_sortable_columns', 'codeweber_make_testimonials_columns_sortable');

/**
 * Enqueue scripts for media uploader
 */
function codeweber_testimonials_admin_scripts($hook)
{
    global $post_type;

    if ($post_type === 'testimonials' && ($hook === 'post.php' || $hook === 'post-new.php')) {
        wp_enqueue_media();
        wp_enqueue_script('jquery');
        wp_localize_script('jquery', 'testimonialAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('testimonial_user_info')
        ]);
    }
}
add_action('admin_enqueue_scripts', 'codeweber_testimonials_admin_scripts');

/**
 * Display rating stars selector
 * 
 * @param int $current_rating Current rating value (1-5)
 * @param string $name Input name attribute
 * @param string $id Input id attribute
 * @param bool $required Is field required
 * @return string|void HTML output
 */
function codeweber_testimonial_rating_stars($current_rating = 0, $name = 'rating', $id = 'rating', $required = true) {
    $current_rating = intval($current_rating);
    if ($current_rating < 1 || $current_rating > 5) {
        $current_rating = 0;
    }
    
    ob_start();
    ?>
    <div class="testimonial-rating-selector">
        <label class="form-label d-block mb-0 mt-3"><?php esc_html_e('Rating *', 'codeweber'); ?></label>
        <input type="hidden" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($id); ?>" value="<?php echo esc_attr($current_rating); ?>" <?php echo $required ? 'required' : ''; ?>>
        <div class="rating-stars-wrapper d-flex gap-1 align-items-center p-0" data-rating-input="<?php echo esc_attr($id); ?>">
            <?php for ($i = 1; $i <= 5; $i++): 
                $is_active = $i <= $current_rating;
            ?>
                <span 
                    class="rating-star-item <?php echo $is_active ? 'active' : ''; ?>" 
                    data-rating="<?php echo esc_attr($i); ?>"
                    style="cursor: pointer;"
                >★</span>
            <?php endfor; ?>
        </div>
    </div>
    <style>
        .rating-stars-wrapper {
            font-size: 1.25rem;
            line-height: 1;
            border-radius: 0.375rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .rating-star-item {
            color: rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: color 0.2s ease;
            user-select: none;
            display: inline-block;
            font-size: 1.25rem;
        }
        .rating-star-item.active {
            color: #fcc032;
        }
        .rating-stars-wrapper:hover .rating-star-item {
            color: rgba(0, 0, 0, 0.1);
        }
        /* Bootstrap validation styles for stars */
        .rating-stars-wrapper.is-invalid {
            border: 1px solid #dc3545;
            background-color: rgba(220, 53, 69, 0.05);
        }
        .rating-stars-wrapper.is-invalid .rating-star-item {
            color: #dc3545;
            opacity: 0.6;
        }
        .rating-stars-wrapper.is-invalid:hover .rating-star-item {
            color: #dc3545;
        }
        /* Focus state */
        .rating-stars-wrapper:focus-within {
            border-color: #86b7fe;
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .rating-stars-wrapper.is-invalid:focus-within {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
        }
    </style>
    <script>
    (function() {
        function initRatingStars() {
            const ratingContainers = document.querySelectorAll('.rating-stars-wrapper');
            ratingContainers.forEach(function(container) {
                const stars = container.querySelectorAll('.rating-star-item');
                const inputId = container.dataset.ratingInput;
                let selectedRating = 0;
                
                // Get initial rating from input
                const input = document.getElementById(inputId);
                if (input && input.value) {
                    selectedRating = parseInt(input.value) || 0;
                    updateStarsVisual(stars, selectedRating);
                }
                
                stars.forEach(function(star) {
                    // Click handler
                    star.addEventListener('click', function(e) {
                        e.preventDefault();
                        const rating = parseInt(this.dataset.rating);
                        selectedRating = rating;
                        if (input) {
                            input.value = rating;
                            // Validate rating immediately when star is clicked
                            if (rating >= 1 && rating <= 5) {
                                // Remove validation error classes
                                input.classList.remove('is-invalid');
                                container.classList.remove('is-invalid');
                                
                                // Trigger validation event to update form state
                                const form = input.closest('form');
                                if (form) {
                                    // Trigger input event for HTML5 validation
                                    input.dispatchEvent(new Event('input', { bubbles: true }));
                                    input.dispatchEvent(new Event('change', { bubbles: true }));
                                    
                                    // If form has was-validated class, re-validate rating field
                                    if (form.classList.contains('was-validated')) {
                                        // Manually validate rating field
                                        if (rating >= 1 && rating <= 5) {
                                            input.setCustomValidity('');
                                        }
                                    }
                                }
                            }
                        }
                        // Update visual state immediately (show selected stars)
                        updateStarsVisual(stars, rating, false);
                        // Ensure stars show correct color
                        stars.forEach(function(s) {
                            if (parseInt(s.dataset.rating) <= rating) {
                                s.style.color = '#fcc032';
                            } else {
                                s.style.color = 'rgba(0, 0, 0, 0.1)';
                            }
                        });
                    });
                });
                
                // Hover handlers - highlight all stars from first to current
                stars.forEach(function(star, index) {
                    star.addEventListener('mouseenter', function() {
                        const hoverRating = parseInt(this.dataset.rating);
                        // Highlight all stars from 1 to hoverRating (left to right)
                        stars.forEach(function(s, idx) {
                            const sRating = parseInt(s.dataset.rating);
                            if (sRating <= hoverRating) {
                                s.style.color = '#fcc032';
                            } else {
                                s.style.color = 'rgba(0, 0, 0, 0.1)';
                            }
                        });
                    });
                });
                
                // Reset on mouse leave
                container.addEventListener('mouseleave', function() {
                    updateStarsVisual(stars, selectedRating);
                });
            });
        }
        
        function updateStarsVisual(stars, rating, isHover) {
            stars.forEach(function(star) {
                const starRating = parseInt(star.dataset.rating);
                if (starRating <= rating) {
                    star.classList.add('active');
                    if (!isHover) {
                        star.style.color = '#fcc032';
                    }
                } else {
                    if (!isHover) {
                        star.classList.remove('active');
                        star.style.color = 'rgba(0, 0, 0, 0.1)';
                    }
                }
            });
            // Don't remove is-invalid here - let form validation handle it on submit
        }
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initRatingStars);
        } else {
            initRatingStars();
        }
        
        // Reinitialize when modal is shown
        document.addEventListener('shown.bs.modal', function(e) {
            if (e.target.querySelector('.rating-stars-wrapper')) {
                setTimeout(initRatingStars, 100);
            }
        });
    })();
    </script>
    <?php
    return ob_get_clean();
}

/**
 * Filter testimonials archive query to show only approved testimonials
 */
function codeweber_filter_testimonials_archive_query($query) {
    if (!is_admin() && $query->is_main_query() && is_post_type_archive('testimonials')) {
        // Show only approved testimonials
        $meta_query = array(
            'relation' => 'OR',
            array(
                'key' => '_testimonial_status',
                'value' => 'approved',
                'compare' => '='
            ),
            array(
                'key' => '_testimonial_status',
                'compare' => 'NOT EXISTS'
            )
        );
        
        $query->set('meta_query', $meta_query);
        
        // Set posts per page to 9
        $query->set('posts_per_page', 9);
    }
}
add_action('pre_get_posts', 'codeweber_filter_testimonials_archive_query');

/**
 * Get testimonial data array (helper function for frontend)
 */
function codeweber_get_testimonial_data($post_id = null)
{
    if (!$post_id) {
        global $post;
        if (!$post) {
            return false;
        }
        $post_id = $post->ID;
    }

    if (get_post_type($post_id) !== 'testimonials') {
        return false;
    }

    $author_type = get_post_meta($post_id, '_testimonial_author_type', true);
    if (empty($author_type)) {
        $author_type = 'custom';
    }

    $data = [
        'text' => get_post_meta($post_id, '_testimonial_text', true),
        'rating' => get_post_meta($post_id, '_testimonial_rating', true),
        'company' => get_post_meta($post_id, '_testimonial_company', true),
        'status' => get_post_meta($post_id, '_testimonial_status', true),
        'author_type' => $author_type,
    ];

    if ($author_type === 'user') {
        $user_id = get_post_meta($post_id, '_testimonial_author_user_id', true);
        if ($user_id) {
            $user = get_userdata($user_id);
            if ($user) {
                $data['author_name'] = $user->display_name;
                $data['author_email'] = $user->user_email;
                $data['author_role'] = get_post_meta($post_id, '_testimonial_author_role', true);
                if (empty($data['author_role']) && !empty($user->roles)) {
                    $data['author_role'] = ucfirst($user->roles[0]);
                }
                $data['author_avatar'] = get_avatar_url($user_id, ['size' => 150]);
                $data['author_user_id'] = $user_id;
            }
        }
    } else {
        $data['author_name'] = get_post_meta($post_id, '_testimonial_author_name', true);
        $data['author_role'] = get_post_meta($post_id, '_testimonial_author_role', true);
        $avatar_id = get_post_meta($post_id, '_testimonial_avatar', true);
        if ($avatar_id) {
            $data['author_avatar'] = wp_get_attachment_image_url($avatar_id, 'thumbnail');
        } else {
            $data['author_avatar'] = '';
        }
    }

    return $data;
}

/**
 * Display testimonial author info template
 * 
 * @param int $post_id Post ID (optional, uses current post if not provided)
 * @param array $args Additional arguments:
 *   - 'show_link' (bool): Show author link (default: true)
 *   - 'link_url' (string): Custom link URL (default: author archive or #)
 *   - 'show_button' (bool): Show "All Posts" button (default: false)
 *   - 'button_text' (string): Button text (default: 'All Posts')
 *   - 'button_url' (string): Button URL (default: author archive or #)
 *   - 'avatar_size' (int): Avatar size in pixels (default: 64)
 * @return string|void HTML output or void if echo is true
 */
function codeweber_testimonial_author_info($post_id = null, $args = [])
{
    $testimonial_data = codeweber_get_testimonial_data($post_id);
    
    if (!$testimonial_data || empty($testimonial_data['author_name'])) {
        return '';
    }

    // Default arguments
    $defaults = [
        'show_link' => true,
        'link_url' => '',
        'show_button' => false,
        'button_text' => __('All Posts', 'codeweber'),
        'button_url' => '',
        'avatar_size' => 64,
        'echo' => true
    ];
    
    $args = wp_parse_args($args, $defaults);
    
    // Get author link
    if ($args['show_link']) {
        if (!empty($args['link_url'])) {
            $author_link = esc_url($args['link_url']);
        } elseif ($testimonial_data['author_type'] === 'user' && !empty($testimonial_data['author_user_id'])) {
            $author_link = get_author_posts_url($testimonial_data['author_user_id']);
        } else {
            $author_link = '#';
        }
    } else {
        $author_link = '';
    }
    
    // Get button URL
    if ($args['show_button']) {
        if (!empty($args['button_url'])) {
            $button_url = esc_url($args['button_url']);
        } elseif ($testimonial_data['author_type'] === 'user' && !empty($testimonial_data['author_user_id'])) {
            $button_url = get_author_posts_url($testimonial_data['author_user_id']);
        } else {
            $button_url = '#';
        }
    }
    
    // Get avatar
    $avatar_url = !empty($testimonial_data['author_avatar']) 
        ? esc_url($testimonial_data['author_avatar']) 
        : get_avatar_url(0, ['size' => $args['avatar_size']]);
    
    $author_name = esc_html($testimonial_data['author_name']);
    $author_role = !empty($testimonial_data['author_role']) ? esc_html($testimonial_data['author_role']) : '';
    
    // Build HTML
    ob_start();
    ?>
    <div class="author-info d-md-flex align-items-center mb-3">
        <div class="d-flex align-items-center">
            <figure class="user-avatar">
                <?php if ($args['avatar_size']): ?>
                    <img class="rounded-circle" alt="<?php echo esc_attr($author_name); ?>" src="<?php echo $avatar_url; ?>" width="<?php echo esc_attr($args['avatar_size']); ?>" height="<?php echo esc_attr($args['avatar_size']); ?>">
                <?php else: ?>
                    <img class="rounded-circle" alt="<?php echo esc_attr($author_name); ?>" src="<?php echo $avatar_url; ?>">
                <?php endif; ?>
            </figure>
            <div>
                <?php if ($args['show_link'] && $author_link): ?>
                    <div class="h6"><a href="<?php echo $author_link; ?>" class="link-dark"><?php echo $author_name; ?></a></div>
                <?php else: ?>
                    <div class="h6"><?php echo $author_name; ?></div>
                <?php endif; ?>
                <?php if ($author_role): ?>
                    <span class="post-meta fs-15"><?php echo $author_role; ?></span>
                <?php endif; ?>
            </div>
        </div>
        <?php if ($args['show_button']): ?>
            <div class="mt-3 mt-md-0 ms-auto">
                <a href="<?php echo $button_url; ?>" class="btn btn-sm btn-soft-ash rounded-pill btn-icon btn-icon-start mb-0">
                    <i class="uil uil-file-alt"></i> <?php echo esc_html($args['button_text']); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
    <?php
    $output = ob_get_clean();
    
    if ($args['echo']) {
        echo $output;
    } else {
        return $output;
    }
}

/**
 * Display full testimonial block with author info
 * 
 * @param int $post_id Post ID (optional, uses current post if not provided)
 * @param array $args Additional arguments:
 *   - 'show_author' (bool): Show author info (default: true)
 *   - 'show_rating' (bool): Show rating stars (default: true)
 *   - 'show_company' (bool): Show company name (default: false)
 *   - 'author_args' (array): Arguments for author info function
 *   - 'wrapper_class' (string): Additional CSS classes for wrapper
 *   - 'text_class' (string): Additional CSS classes for testimonial text
 * @return string|void HTML output or void if echo is true
 */
function codeweber_testimonial_block($post_id = null, $args = [])
{
    $testimonial_data = codeweber_get_testimonial_data($post_id);
    
    if (!$testimonial_data) {
        return '';
    }

    // Default arguments
    $defaults = [
        'show_author' => true,
        'show_rating' => true,
        'show_company' => false,
        'author_args' => [],
        'wrapper_class' => '',
        'text_class' => '',
        'echo' => true
    ];
    
    $args = wp_parse_args($args, $defaults);
    
    $testimonial_text = !empty($testimonial_data['text']) ? wp_kses_post($testimonial_data['text']) : '';
    $rating = !empty($testimonial_data['rating']) ? intval($testimonial_data['rating']) : 0;
    $company = !empty($testimonial_data['company']) ? esc_html($testimonial_data['company']) : '';
    
    ob_start();
    ?>
    <div class="testimonial-block <?php echo esc_attr($args['wrapper_class']); ?>">
        <?php if ($testimonial_text): ?>
            <blockquote class="testimonial-text <?php echo esc_attr($args['text_class']); ?>">
                <?php echo $testimonial_text; ?>
            </blockquote>
        <?php endif; ?>
        
        <?php if ($args['show_rating'] && $rating > 0): 
            $rating_names = ['', 'one', 'two', 'three', 'four', 'five'];
            $rating_class = isset($rating_names[$rating]) ? $rating_names[$rating] : 'five';
        ?>
            <div class="testimonial-rating mb-3">
                <span class="ratings <?php echo esc_attr($rating_class); ?>"></span>
            </div>
        <?php endif; ?>
        
        <?php if ($args['show_company'] && $company): ?>
            <div class="testimonial-company mb-3">
                <span class="text-muted"><?php echo $company; ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($args['show_author']): ?>
            <?php codeweber_testimonial_author_info($post_id, $args['author_args']); ?>
        <?php endif; ?>
    </div>
    <?php
    $output = ob_get_clean();
    
    if ($args['echo']) {
        echo $output;
    } else {
        return $output;
    }
}

/**
 * Display testimonial blockquote details (avatar/initials + author info)
 * 
 * @param int|array $post_id_or_data Post ID (optional, uses current post if not provided) OR array with post data (from cw_get_post_card_data)
 * @param array $args Additional arguments:
 *   - 'show_company' (bool): Show company name (default: false)
 *   - 'avatar_size' (string): Avatar size class - 'w-10', 'w-11', 'w-12', 'w-15' (default: 'w-11'). Height will be added automatically (h-10, h-11, h-12, h-15)
 *   - 'avatar_bg' (string): Avatar background class - 'bg-primary', 'bg-pale-primary', 'bg-soft-primary' (default: 'bg-primary')
 *   - 'avatar_text' (string): Avatar text color class - 'text-white', 'text-primary' (default: 'text-white')
 *   - 'initials_count' (int): Number of initials to show (1 or 2, default: 2)
 *   - 'echo' (bool): Echo output or return (default: true)
 * @return string|void HTML output or void if echo is true
 */
function codeweber_testimonial_blockquote_details($post_id_or_data = null, $args = []) {
    // Default arguments
    $defaults = [
        'show_company' => false,
        'show_avatar' => true, // Показывать аватар/инициалы (если false - не показывать ни аватар, ни инициалы)
        'avatar_size' => 'w-11',
        'avatar_bg' => 'bg-primary',
        'avatar_text' => 'text-white',
        'initials_count' => 2,
        'info_class' => '', // Дополнительный класс для info (например, 'ps-0' или 'p-0')
        'echo' => true,
    ];
    
    $args = wp_parse_args($args, $defaults);
    
    // Если передан массив данных (из post-cards шаблонов)
    if (is_array($post_id_or_data) && isset($post_id_or_data['id'])) {
        $post_data = $post_id_or_data;
        $post_id = $post_data['id'];
        
        $author_name = !empty($post_data['author_name']) ? esc_html($post_data['author_name']) : '';
        $author_role = !empty($post_data['author_role']) ? esc_html($post_data['author_role']) : '';
        $company = !empty($post_data['company']) ? esc_html($post_data['company']) : '';
        
        // Используем аватар из post_data
        $avatar_url = !empty($post_data['avatar_url']) ? esc_url($post_data['avatar_url']) : '';
        $avatar_url_2x = !empty($post_data['avatar_url_2x']) ? esc_url($post_data['avatar_url_2x']) : '';
    } else {
        // Обычный режим - получаем данные из поста
        $post_id = $post_id_or_data ? $post_id_or_data : get_the_ID();
        
        $testimonial_data = codeweber_get_testimonial_data($post_id);
        
        if (!$testimonial_data) {
            return '';
        }
        
        $author_name = !empty($testimonial_data['author_name']) ? esc_html($testimonial_data['author_name']) : '';
        $author_role = !empty($testimonial_data['author_role']) ? esc_html($testimonial_data['author_role']) : '';
        $company = !empty($testimonial_data['company']) ? esc_html($testimonial_data['company']) : '';
        
        // Get avatar
        $avatar_url = '';
        $avatar_url_2x = '';
        $avatar_id = get_post_meta($post_id, '_testimonial_avatar', true);
        
        if ($avatar_id) {
            $avatar_src = wp_get_attachment_image_src($avatar_id, 'thumbnail');
            if ($avatar_src) {
                $avatar_url = esc_url($avatar_src[0]);
            }
            $avatar_2x_src = wp_get_attachment_image_src($avatar_id, 'medium');
            if ($avatar_2x_src && $avatar_2x_src[0] !== $avatar_url) {
                $avatar_url_2x = esc_url($avatar_2x_src[0]);
            }
        } elseif (!empty($testimonial_data['author_avatar'])) {
            $avatar_url = esc_url($testimonial_data['author_avatar']);
        }
    }
    
    // Generate initials if no avatar (только если show_avatar включен)
    $initials = '';
    if ($args['show_avatar'] && empty($avatar_url) && !empty($author_name)) {
        $name_parts = explode(' ', trim($author_name));
        if ($args['initials_count'] === 1) {
            // Первая буква имени
            $initials = strtoupper(mb_substr($name_parts[0], 0, 1, 'UTF-8'));
        } else {
            // Первые буквы имени и фамилии (или первые две буквы имени)
            if (count($name_parts) >= 2) {
                $initials = strtoupper(mb_substr($name_parts[0], 0, 1, 'UTF-8') . mb_substr($name_parts[1], 0, 1, 'UTF-8'));
            } else {
                $initials = strtoupper(mb_substr($name_parts[0], 0, 2, 'UTF-8'));
            }
        }
    }
    
    // Определяем класс для info
    // Если указан явно info_class И есть аватар/инициалы, используем его
    // Если нет аватара/инициалов, всегда используем p-0 (даже если указан другой класс)
    if (!$args['show_avatar'] || (empty($avatar_url) && empty($initials))) {
        // Когда нет аватара, всегда используем p-0
        $info_class = ' p-0';
    } elseif (!empty($args['info_class'])) {
        // Когда есть аватар и указан явный класс, используем его
        $info_class = ' ' . esc_attr($args['info_class']);
    } else {
        // По умолчанию без дополнительных классов
        $info_class = '';
    }
    
    // Получаем класс высоты из класса ширины (w-10 -> h-10, w-12 -> h-12, w-15 -> h-15)
    $avatar_height = str_replace('w-', 'h-', $args['avatar_size']);
    
    // Build HTML
    ob_start();
    ?>
    <div class="blockquote-details">
        <?php if ($args['show_avatar'] && ($avatar_url || $initials)) : ?>
            <?php if ($avatar_url) : ?>
                <!-- Аватар с картинкой -->
                <div class="d-flex align-items-center">
                    <figure class="user-avatar">
                        <img 
                            class="rounded-circle" 
                            src="<?php echo $avatar_url; ?>" 
                            <?php if ($avatar_url_2x) : ?>
                                srcset="<?php echo $avatar_url_2x; ?> 2x" 
                            <?php endif; ?>
                            alt="<?php echo esc_attr($author_name); ?>" 
                        />
                    </figure>
                    <div>
                        <?php if ($author_name) : ?>
                            <div class="h6 mb-0"><?php echo $author_name; ?></div>
                        <?php endif; ?>
                        <?php if ($author_role) : ?>
                            <span class="post-meta fs-15"><?php echo $author_role; ?></span>
                        <?php endif; ?>
                        <?php if ($args['show_company'] && $company) : ?>
                            <span class="post-meta fs-15 text-muted"><?php echo $company; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif ($initials) : ?>
                <!-- Аватар с инициалами -->
                <div class="d-flex align-items-center">
                    <span class="avatar <?php echo esc_attr($args['avatar_bg'] . ' ' . $args['avatar_text'] . ' ' . $args['avatar_size'] . ' ' . $avatar_height); ?>">
                        <span><?php echo esc_html($initials); ?></span>
                    </span>
                    <div class="info<?php echo esc_attr($info_class); ?>">
                        <?php if ($author_name) : ?>
                            <div class="h5 mb-1"><?php echo $author_name; ?></div>
                        <?php endif; ?>
                        <?php if ($author_role) : ?>
                            <p class="mb-0"><?php echo $author_role; ?></p>
                        <?php endif; ?>
                        <?php if ($args['show_company'] && $company) : ?>
                            <p class="mb-0 text-muted small"><?php echo $company; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php else : ?>
            <!-- Без аватара -->
            <div class="info<?php echo esc_attr($info_class); ?>">
                <?php if ($author_name) : ?>
                    <div class="h5 mb-1"><?php echo $author_name; ?></div>
                <?php endif; ?>
                <?php if ($author_role) : ?>
                    <p class="mb-0"><?php echo $author_role; ?></p>
                <?php endif; ?>
                <?php if ($args['show_company'] && $company) : ?>
                    <p class="mb-0 text-muted small"><?php echo $company; ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
    $output = ob_get_clean();
    
    if ($args['echo']) {
        echo $output;
    } else {
        return $output;
    }
}

/**
 * Register testimonial meta fields for REST API
 */
function codeweber_register_testimonial_rest_fields() {
    // Регистрируем метаполя для REST API
    $meta_fields = [
        '_testimonial_text',
        '_testimonial_author_type',
        '_testimonial_author_user_id',
        '_testimonial_author_name',
        '_testimonial_author_role',
        '_testimonial_company',
        '_testimonial_rating',
        '_testimonial_avatar',
        '_testimonial_status',
    ];
    
    foreach ($meta_fields as $meta_field) {
        register_rest_field('testimonials', $meta_field, [
            'get_callback' => function($post) use ($meta_field) {
                return get_post_meta($post['id'], $meta_field, true);
            },
            'update_callback' => null, // Только для чтения
            'schema' => [
                'description' => sprintf(__('Testimonial meta field: %s', 'codeweber'), $meta_field),
                'type' => 'string',
                'context' => ['view', 'edit'],
            ],
        ]);
    }
}
add_action('rest_api_init', 'codeweber_register_testimonial_rest_fields');

/**
 * Отключаем single Testimonials страницы - возвращаем 404
 */
add_action('template_redirect', function() {
	if (is_singular('testimonials')) {
		global $wp_query;
		$wp_query->set_404();
		status_header(404);
	}
});
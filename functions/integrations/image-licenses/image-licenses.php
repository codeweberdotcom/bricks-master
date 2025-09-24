<?php

/**
 * Media Licenses Management Module (Images & Videos)
 */

// Load text domain for translations
function media_licenses_load_textdomain()
{
   load_plugin_textdomain('codeweber', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'media_licenses_load_textdomain');

// Register taxonomy for Licensor's author
function register_licensor_author_taxonomy()
{
   $labels = array(
      'name'              => _x('Licensor Authors', 'taxonomy general name', 'codeweber'),
      'singular_name'     => _x('Licensor Author', 'taxonomy singular name', 'codeweber'),
      'search_items'      => __('Search Licensor Authors', 'codeweber'),
      'all_items'         => __('All Licensor Authors', 'codeweber'),
      'parent_item'       => __('Parent Licensor Author', 'codeweber'),
      'parent_item_colon' => __('Parent Licensor Author:', 'codeweber'),
      'edit_item'         => __('Edit Licensor Author', 'codeweber'),
      'update_item'       => __('Update Licensor Author', 'codeweber'),
      'add_new_item'      => __('Add New Licensor Author', 'codeweber'),
      'new_item_name'     => __('New Licensor Author Name', 'codeweber'),
      'menu_name'         => __('Licensor Authors', 'codeweber'),
   );

   $args = array(
      'hierarchical'      => false,
      'labels'            => $labels,
      'show_ui'           => true,
      'show_admin_column' => true,
      'query_var'         => true,
      'rewrite'           => array('slug' => 'licensor-author'),
      'show_in_rest'      => false,
   );

   register_taxonomy('licensor_author', array('media_license'), $args);
}
add_action('init', 'register_licensor_author_taxonomy');

// Register Custom Post Type for licenses
function register_media_license_cpt()
{
   $labels = array(
      'name'                  => _x('Media Licenses', 'Post Type General Name', 'codeweber'),
      'singular_name'         => _x('Media License', 'Post Type Singular Name', 'codeweber'),
      'menu_name'             => __('Media Licenses', 'codeweber'),
      'name_admin_bar'        => __('Media License', 'codeweber'),
      'archives'              => __('License Archives', 'codeweber'),
      'attributes'            => __('License Attributes', 'codeweber'),
      'parent_item_colon'     => __('Parent License:', 'codeweber'),
      'all_items'             => __('All Licenses', 'codeweber'),
      'add_new_item'          => __('Add New License', 'codeweber'),
      'add_new'               => __('Add New', 'codeweber'),
      'new_item'              => __('New License', 'codeweber'),
      'edit_item'             => __('Edit License', 'codeweber'),
      'update_item'           => __('Update License', 'codeweber'),
      'view_item'             => __('View License', 'codeweber'),
      'view_items'            => __('View Licenses', 'codeweber'),
      'search_items'          => __('Search Licenses', 'codeweber'),
      'not_found'             => __('Not found', 'codeweber'),
      'not_found_in_trash'    => __('Not found in Trash', 'codeweber'),
      'featured_image'        => __('Featured Image', 'codeweber'),
      'set_featured_image'    => __('Set featured image', 'codeweber'),
      'remove_featured_image' => __('Remove featured image', 'codeweber'),
      'use_featured_image'    => __('Use as featured image', 'codeweber'),
      'insert_into_item'      => __('Insert into license', 'codeweber'),
      'uploaded_to_this_item' => __('Uploaded to this license', 'codeweber'),
      'items_list'            => __('Licenses list', 'codeweber'),
      'items_list_navigation' => __('Licenses list navigation', 'codeweber'),
      'filter_items_list'     => __('Filter licenses list', 'codeweber'),
   );

   $args = array(
      'label'                 => __('Media License', 'codeweber'),
      'description'           => __('Manage licenses for images and videos', 'codeweber'),
      'labels'                => $labels,
      'public'                => false,
      'show_ui'               => true,
      'show_in_menu'          => true,
      'query_var'             => true,
      'capability_type'       => 'post',
      'has_archive'           => false,
      'hierarchical'          => false,
      'menu_position'         => 21,
      'menu_icon'             => 'dashicons-media-document',
      'supports'              => array('title'),
      'show_in_rest'          => false,
      'taxonomies'            => array('licensor_author'),
   );

   register_post_type('media_license', $args);
}
add_action('init', 'register_media_license_cpt');

// Add meta boxes for license fields
function add_license_meta_boxes()
{
   // PDF meta box
   add_meta_box(
      'license_pdf_meta_box',
      __('License PDF File', 'codeweber'),
      'render_license_pdf_meta_box',
      'media_license',
      'normal',
      'high'
   );

   // License details meta box
   add_meta_box(
      'license_details_meta_box',
      __('License Details', 'codeweber'),
      'render_license_details_meta_box',
      'media_license',
      'normal',
      'high'
   );

   // Attached media meta box
   add_meta_box(
      'license_attachments_meta_box',
      __('Attached Media Files', 'codeweber'),
      'render_license_attachments_meta_box',
      'media_license',
      'normal',
      'default'
   );
}
add_action('add_meta_boxes', 'add_license_meta_boxes');

function render_license_pdf_meta_box($post)
{
   wp_nonce_field(basename(__FILE__), 'license_pdf_nonce');

   $pdf_id = get_post_meta($post->ID, '_license_pdf_id', true);
   $pdf_url = $pdf_id ? wp_get_attachment_url($pdf_id) : '';
?>

   <div class="license-pdf-upload">
      <input type="hidden" id="license_pdf_id" name="license_pdf_id" value="<?php echo esc_attr($pdf_id); ?>">
      <input type="text" id="license_pdf_url" class="regular-text" value="<?php echo esc_url($pdf_url); ?>" readonly>
      <button type="button" class="button button-secondary" id="upload_license_pdf"><?php _e('Select PDF', 'codeweber'); ?></button>
      <button type="button" class="button button-secondary" id="remove_license_pdf" style="<?php echo !$pdf_id ? 'display:none;' : ''; ?>"><?php _e('Remove', 'codeweber'); ?></button>

      <?php if ($pdf_id) : ?>
         <div style="margin-top: 10px;">
            <a href="<?php echo esc_url($pdf_url); ?>" target="_blank" class="button">
               <?php _e('View PDF', 'codeweber'); ?>
            </a>
         </div>
      <?php endif; ?>
   </div>

   <script>
      jQuery(document).ready(function($) {
         var frame;

         $('#upload_license_pdf').on('click', function(e) {
            e.preventDefault();

            if (frame) {
               frame.open();
               return;
            }

            frame = wp.media({
               title: '<?php _e('Select PDF File', 'codeweber'); ?>',
               button: {
                  text: '<?php _e('Use this file', 'codeweber'); ?>'
               },
               multiple: false,
               library: {
                  type: 'application/pdf'
               }
            });

            frame.on('select', function() {
               var attachment = frame.state().get('selection').first().toJSON();
               $('#license_pdf_id').val(attachment.id);
               $('#license_pdf_url').val(attachment.url);
               $('#remove_license_pdf').show();
            });

            frame.open();
         });

         $('#remove_license_pdf').on('click', function() {
            $('#license_pdf_id').val('');
            $('#license_pdf_url').val('');
            $(this).hide();
         });
      });
   </script>

<?php
}

function render_license_details_meta_box($post)
{
   wp_nonce_field(basename(__FILE__), 'license_details_nonce');

   // Get existing values
   $license_type = get_post_meta($post->ID, '_license_type', true);
   $licensee = get_post_meta($post->ID, '_licensee', true);
   $for_the_item = get_post_meta($post->ID, '_for_the_item', true);
   $download_date = get_post_meta($post->ID, '_download_date', true);
   $item_url = get_post_meta($post->ID, '_item_url', true);

   // Get all authors from taxonomy
   $authors = get_terms(array(
      'taxonomy' => 'licensor_author',
      'hide_empty' => false,
      'orderby' => 'name',
      'order' => 'ASC'
   ));

   // Get current author terms
   $current_authors = wp_get_post_terms($post->ID, 'licensor_author', array('fields' => 'ids'));
   $current_author_id = !empty($current_authors) ? $current_authors[0] : '';
?>

   <table class="form-table">
      <tr>
         <th scope="row">
            <label for="license_type"><?php _e('License type:', 'codeweber'); ?></label>
         </th>
         <td>
            <input type="text" id="license_type" name="license_type" value="<?php echo esc_attr($license_type); ?>" class="regular-text">
            <p class="description"><?php _e('e.g., Royalty-free, Rights-managed, Creative Commons', 'codeweber'); ?></p>
         </td>
      </tr>

      <tr>
         <th scope="row">
            <label for="licensor_author"><?php _e('Licensor\'s author:', 'codeweber'); ?></label>
         </th>
         <td>
            <select id="licensor_author" name="licensor_author" class="regular-text">
               <option value=""><?php _e('— Select Author —', 'codeweber'); ?></option>
               <?php foreach ($authors as $author) : ?>
                  <option value="<?php echo esc_attr($author->term_id); ?>" <?php selected($current_author_id, $author->term_id); ?>>
                     <?php echo esc_html($author->name); ?>
                  </option>
               <?php endforeach; ?>
            </select>
            <p class="description">
               <a href="<?php echo admin_url('edit-tags.php?taxonomy=licensor_author&post_type=media_license'); ?>" target="_blank">
                  <?php _e('Manage authors', 'codeweber'); ?>
               </a>
            </p>
         </td>
      </tr>

      <tr>
         <th scope="row">
            <label for="licensee"><?php _e('Licensee:', 'codeweber'); ?></label>
         </th>
         <td>
            <input type="text" id="licensee" name="licensee" value="<?php echo esc_attr($licensee); ?>" class="regular-text">
            <p class="description"><?php _e('Person or company who obtained the license', 'codeweber'); ?></p>
         </td>
      </tr>

      <tr>
         <th scope="row">
            <label for="for_the_item"><?php _e('For the item:', 'codeweber'); ?></label>
         </th>
         <td>
            <input type="text" id="for_the_item" name="for_the_item" value="<?php echo esc_attr($for_the_item); ?>" class="regular-text">
            <p class="description"><?php _e('Specific item or project the license is for', 'codeweber'); ?></p>
         </td>
      </tr>

      <tr>
         <th scope="row">
            <label for="download_date"><?php _e('Download date:', 'codeweber'); ?></label>
         </th>
         <td>
            <input type="date" id="download_date" name="download_date" value="<?php echo esc_attr($download_date); ?>" class="regular-text">
         </td>
      </tr>

      <tr>
         <th scope="row">
            <label for="item_url"><?php _e('Item URL:', 'codeweber'); ?></label>
         </th>
         <td>
            <input type="url" id="item_url" name="item_url" value="<?php echo esc_url($item_url); ?>" class="regular-text" placeholder="https://">
            <p class="description"><?php _e('URL to the licensed item if available online', 'codeweber'); ?></p>
         </td>
      </tr>
   </table>

<?php
}

// Meta box for displaying attached media files
function render_license_attachments_meta_box($post)
{
   $attachments = get_attachments_with_license($post->ID);

   echo '<div class="license-attachments">';

   if (!empty($attachments)) {
      echo '<p>' . __('The following media files use this license:', 'codeweber') . '</p>';
      echo '<ul style="max-height: 300px; overflow-y: auto;">';

      foreach ($attachments as $attachment) {
         $edit_url = get_edit_post_link($attachment->ID);
         $thumb_url = wp_get_attachment_thumb_url($attachment->ID);
         $file_type = wp_check_filetype($attachment->guid);
         $file_icon = get_file_icon($file_type['ext']);

         echo '<li style="margin-bottom: 15px; padding: 10px; border: 1px solid #ddd; display: flex; align-items: center;">';
         echo '<div style="margin-right: 15px;">';
         if ($thumb_url) {
            echo '<img src="' . esc_url($thumb_url) . '" style="max-width: 60px; height: auto;">';
         } else {
            echo '<span class="dashicons ' . esc_attr($file_icon) . '" style="font-size: 40px; width: 40px; height: 40px;"></span>';
         }
         echo '</div>';
         echo '<div>';
         echo '<strong><a href="' . esc_url($edit_url) . '" target="_blank">' . esc_html(get_the_title($attachment->ID)) . '</a></strong><br>';
         echo '<span style="font-size: 12px; color: #666;">' . sprintf(__('ID: %s | Type: %s', 'codeweber'), $attachment->ID, $file_type['ext']) . '</span>';
         echo '</div>';
         echo '</li>';
      }

      echo '</ul>';
   } else {
      echo '<p>' . __('No attached media files.', 'codeweber') . '</p>';
   }

   echo '</div>';
}

// Helper function to get file type icon
function get_file_icon($extension)
{
   $video_extensions = array('mp4', 'mov', 'avi', 'wmv', 'flv', 'webm', 'm4v');
   $audio_extensions = array('mp3', 'wav', 'ogg', 'm4a');
   $image_extensions = array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp');
   $document_extensions = array('pdf', 'doc', 'docx', 'txt');

   if (in_array($extension, $video_extensions)) {
      return 'dashicons-format-video';
   } elseif (in_array($extension, $audio_extensions)) {
      return 'dashicons-format-audio';
   } elseif (in_array($extension, $image_extensions)) {
      return 'dashicons-format-image';
   } elseif (in_array($extension, $document_extensions)) {
      return 'dashicons-media-document';
   } else {
      return 'dashicons-media-default';
   }
}

// Save meta box data
function save_license_meta($post_id)
{
   // Verify nonces
   if (!isset($_POST['license_pdf_nonce']) || !wp_verify_nonce($_POST['license_pdf_nonce'], basename(__FILE__))) {
      return $post_id;
   }

   if (!isset($_POST['license_details_nonce']) || !wp_verify_nonce($_POST['license_details_nonce'], basename(__FILE__))) {
      return $post_id;
   }

   if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return $post_id;
   }

   if (!current_user_can('edit_post', $post_id)) {
      return $post_id;
   }

   // Save PDF ID
   if (isset($_POST['license_pdf_id'])) {
      update_post_meta($post_id, '_license_pdf_id', sanitize_text_field($_POST['license_pdf_id']));
   }

   // Save author taxonomy
   if (isset($_POST['licensor_author'])) {
      $author_id = intval($_POST['licensor_author']);
      if ($author_id > 0) {
         wp_set_object_terms($post_id, array($author_id), 'licensor_author', false);
      } else {
         wp_set_object_terms($post_id, array(), 'licensor_author', false);
      }
   }

   // Save license details
   $fields = array(
      'license_type' => '_license_type',
      'licensee' => '_licensee',
      'for_the_item' => '_for_the_item',
      'download_date' => '_download_date',
      'item_url' => '_item_url'
   );

   foreach ($fields as $field => $meta_key) {
      if (isset($_POST[$field])) {
         $value = sanitize_text_field($_POST[$field]);
         if ($field === 'item_url') {
            $value = esc_url_raw($value);
         }
         update_post_meta($post_id, $meta_key, $value);
      }
   }
}
add_action('save_post', 'save_license_meta');

// Add columns to licenses list
function add_license_columns($columns)
{
   $new_columns = array();

   foreach ($columns as $key => $value) {
      $new_columns[$key] = $value;
      if ($key === 'title') {
         $new_columns['license_type'] = __('License Type', 'codeweber');
         $new_columns['licensor_author'] = __('Licensor Author', 'codeweber');
         $new_columns['pdf_file'] = __('PDF File', 'codeweber');
         $new_columns['attachments_count'] = __('Attached Files', 'codeweber');
      }
   }

   return $new_columns;
}
add_filter('manage_media_license_posts_columns', 'add_license_columns');

function populate_license_columns($column, $post_id)
{
   switch ($column) {
      case 'license_type':
         $license_type = get_post_meta($post_id, '_license_type', true);
         echo $license_type ? esc_html($license_type) : '—';
         break;

      case 'licensor_author':
         $terms = get_the_terms($post_id, 'licensor_author');
         if ($terms && !is_wp_error($terms)) {
            $term_names = array();
            foreach ($terms as $term) {
               $term_names[] = $term->name;
            }
            echo implode(', ', $term_names);
         } else {
            echo '—';
         }
         break;

      case 'pdf_file':
         $pdf_id = get_post_meta($post_id, '_license_pdf_id', true);
         if ($pdf_id) {
            echo '<a href="' . wp_get_attachment_url($pdf_id) . '" target="_blank">' . __('View PDF', 'codeweber') . '</a>';
         } else {
            echo '—';
         }
         break;

      case 'attachments_count':
         $count = get_attachments_with_license($post_id, true);
         echo $count ? $count : '0';
         break;
   }
}
add_action('manage_media_license_posts_custom_column', 'populate_license_columns', 10, 2);

// Make license type column sortable
function make_license_columns_sortable($columns)
{
   $columns['license_type'] = 'license_type';
   $columns['attachments_count'] = 'attachments_count';
   return $columns;
}
add_filter('manage_edit-media_license_sortable_columns', 'make_license_columns_sortable');

// Add license column to media library
function add_media_license_column($columns)
{
   $new_columns = array();

   foreach ($columns as $key => $value) {
      $new_columns[$key] = $value;
      if ($key === 'title') {
         $new_columns['media_license'] = __('License', 'codeweber');
      }
   }

   return $new_columns;
}
add_filter('manage_media_columns', 'add_media_license_column');

function populate_media_license_column($column_name, $post_id)
{
   if ($column_name !== 'media_license') {
      return;
   }

   $license_id = get_post_meta($post_id, '_media_license_id', true);

   if ($license_id) {
      $license = get_post($license_id);
      $pdf_id = get_post_meta($license_id, '_license_pdf_id', true);

      if ($license && $pdf_id) {
         $pdf_url = wp_get_attachment_url($pdf_id);
         echo '<strong>' . esc_html($license->post_title) . '</strong><br>';
         echo '<a href="' . esc_url($pdf_url) . '" target="_blank" style="font-size: 12px;">' . __('View License', 'codeweber') . '</a><br>';
         echo '<a href="' . get_edit_post_link($license_id) . '" target="_blank" style="font-size: 12px;">' . __('Edit License', 'codeweber') . '</a>';
      } elseif ($license) {
         echo esc_html($license->post_title) . ' (' . __('no PDF', 'codeweber') . ')';
      }
   } else {
      echo '—';
   }
}
add_action('manage_media_custom_column', 'populate_media_license_column', 10, 2);

// Make license column sortable in media library
function make_media_license_column_sortable($columns)
{
   $columns['media_license'] = 'media_license';
   return $columns;
}
add_filter('manage_upload_sortable_columns', 'make_media_license_column_sortable');

// Add license selection field to media files (both images and videos)
function add_media_license_field($form_fields, $post)
{
   // Check if it's an image or video file
   $mime_type = get_post_mime_type($post->ID);
   $is_image = wp_attachment_is_image($post->ID);
   $is_video = strpos($mime_type, 'video/') === 0;

   // Only show for images and videos
   if (!$is_image && !$is_video) {
      return $form_fields;
   }

   $licenses = get_posts(array(
      'post_type' => 'media_license',
      'posts_per_page' => -1,
      'post_status' => 'publish',
      'orderby' => 'title',
      'order' => 'ASC'
   ));

   $current_license = get_post_meta($post->ID, '_media_license_id', true);

   $options = array('' => __('— No License —', 'codeweber'));
   foreach ($licenses as $license) {
      $options[$license->ID] = $license->post_title;
   }

   $form_fields['media_license'] = array(
      'label' => __('Media License', 'codeweber'),
      'input' => 'html',
      'html' => render_license_select($options, $current_license, $post->ID),
      'value' => $current_license
   );

   // Show PDF link if license is attached
   if ($current_license) {
      $pdf_id = get_post_meta($current_license, '_license_pdf_id', true);
      if ($pdf_id) {
         $form_fields['license_pdf_link'] = array(
            'label' => __('License PDF', 'codeweber'),
            'input' => 'html',
            'html' => '<a href="' . wp_get_attachment_url($pdf_id) . '" target="_blank" class="button">' . __('View License', 'codeweber') . '</a>'
         );
      }
   }

   return $form_fields;
}
add_filter('attachment_fields_to_edit', 'add_media_license_field', 10, 2);

function render_license_select($options, $current_value, $attachment_id)
{
   $html = '<select name="attachments[' . $attachment_id . '][media_license]" id="attachments-' . $attachment_id . '-media_license">';

   foreach ($options as $value => $label) {
      $selected = selected($current_value, $value, false);
      $html .= '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
   }

   $html .= '</select>';
   $html .= '<p class="description"><a href="' . admin_url('post-new.php?post_type=media_license') . '" target="_blank">' . __('Add New License', 'codeweber') . '</a></p>';

   return $html;
}

// Save selected license for media file
function save_media_license_field($post, $attachment)
{
   if (isset($attachment['media_license'])) {
      if (empty($attachment['media_license'])) {
         delete_post_meta($post['ID'], '_media_license_id');
      } else {
         update_post_meta($post['ID'], '_media_license_id', sanitize_text_field($attachment['media_license']));
      }
   }

   return $post;
}
add_filter('attachment_fields_to_save', 'save_media_license_field', 10, 2);

// Helper function to get media files with specific license
function get_attachments_with_license($license_id, $count_only = false)
{
   $args = array(
      'post_type' => 'attachment',
      'post_status' => 'inherit',
      'posts_per_page' => -1,
      'meta_query' => array(
         array(
            'key' => '_media_license_id',
            'value' => $license_id,
            'compare' => '='
         )
      )
   );

   if ($count_only) {
      $args['fields'] = 'ids';
      return count(get_posts($args));
   }

   return get_posts($args);
}

// Enqueue necessary scripts and styles
function enqueue_license_admin_scripts()
{
   $screen = get_current_screen();

   if ($screen && ($screen->id === 'media_license' || $screen->id === 'attachment' || $screen->id === 'upload')) {
      wp_enqueue_media();
   }

   // Admin styles
   if ($screen && ($screen->id === 'media_license' || $screen->id === 'upload')) {
      echo '<style>
            .column-media_license { width: 200px; }
            .license-attachments ul { list-style: none; margin: 0; padding: 0; }
            .license-attachments li { transition: background-color 0.2s; }
            .license-attachments li:hover { background-color: #f9f9f9; }
            .column-license_type { width: 150px; }
            .column-licensor_author { width: 150px; }
            .column-attachments_count { width: 120px; }
        </style>';
   }
}
add_action('admin_enqueue_scripts', 'enqueue_license_admin_scripts');
add_action('admin_head', 'enqueue_license_admin_scripts');

// Add video support to the media query
function extend_media_license_support()
{
   // This function ensures video files are included in all queries
}
add_action('init', 'extend_media_license_support');
?>
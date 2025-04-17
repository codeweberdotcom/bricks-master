<?php

// Register CPT Docs
function cptui_register_my_cpts_documents()
{
   $labels = [
      "name" => esc_html__("Docs", "codeweber"),
      "singular_name" => esc_html__("Doc", "codeweber"),
      "add_new" => esc_html__("Add New Doc", "codeweber"),
      "add_new_item" => esc_html__("Add New Doc", "codeweber"),
      "edit_item" => esc_html__("Edit Doc", "codeweber"),
      "new_item" => esc_html__("New Doc", "codeweber"),
      "view_item" => esc_html__("View Doc", "codeweber"),
      "view_items" => esc_html__("View Docs", "codeweber"),
      "search_items" => esc_html__("Search Docs", "codeweber"),
      "not_found" => esc_html__("No Docs found", "codeweber"),
      "not_found_in_trash" => esc_html__("No Docs found in Trash", "codeweber"),
      "all_items" => esc_html__("All Docs", "codeweber"),
      "archives" => esc_html__("Docs Archives", "codeweber"),
   ];

   $args = [
      "label" => esc_html__("Docs", "codeweber"),
      "labels" => $labels,
      "public" => true,
      "publicly_queryable" => false,
      "show_ui" => true,
      "show_in_rest" => true,
      "rest_base" => "",
      "rest_controller_class" => "WP_REST_Posts_Controller",
      "rest_namespace" => "wp/v2",
      "has_archive" => false,
      "show_in_menu" => true,
      "show_in_nav_menus" => true,
      "exclude_from_search" => true,
      "capability_type" => "post",
      "map_meta_cap" => true,
      "hierarchical" => false,
      "can_export" => true,
      "rewrite" => ["slug" => "documents", "with_front" => true],
      "query_var" => true,
      "supports" => ["title", "custom-fields"],
      "show_in_graphql" => false,
   ];

   register_post_type("documents", $args);
}

add_action('init', 'cptui_register_my_cpts_documents');

// Register taxonomy
function cptui_register_my_taxes_category_doc()
{
   $labels = [
      "name" => esc_html__("Category Docs", "codeweber"),
      "singular_name" => esc_html__("Category Doc", "codeweber"),
      "search_items" => esc_html__("Search Category Docs", "codeweber"),
      "all_items" => esc_html__("All Category Docs", "codeweber"),
      "parent_item" => esc_html__("Parent Category Doc", "codeweber"),
      "parent_item_colon" => esc_html__("Parent Category Doc:", "codeweber"),
      "edit_item" => esc_html__("Edit Category Doc", "codeweber"),
      "update_item" => esc_html__("Update Category Doc", "codeweber"),
      "add_new_item" => esc_html__("Add New Category Doc", "codeweber"),
      "new_item_name" => esc_html__("New Category Doc Name", "codeweber"),
      "menu_name" => esc_html__("Category Docs", "codeweber"),
   ];

   $args = [
      "label" => esc_html__("Category Docs", "codeweber"),
      "labels" => $labels,
      "public" => false,
      "publicly_queryable" => true,
      "hierarchical" => false,
      "show_ui" => true,
      "show_in_menu" => true,
      "show_in_nav_menus" => true,
      "query_var" => true,
      "rewrite" => ['slug' => 'category_doc', 'with_front' => true],
      "show_admin_column" => true,
      "show_in_rest" => true,
      "show_tagcloud" => false,
      "rest_base" => "category_doc",
      "rest_controller_class" => "WP_REST_Terms_Controller",
      "rest_namespace" => "wp/v2",
      "show_in_quick_edit" => true,
      "sort" => true,
      "show_in_graphql" => false,
   ];

   register_taxonomy("category_doc", ["documents"], $args);
}

add_action('init', 'cptui_register_my_taxes_category_doc');

// Register meta field for file upload
function register_documents_file_meta()
{
   register_post_meta('documents', '_new_documents_file', [
      'show_in_rest' => true,
      'single' => true,
      'type' => 'string',
      'sanitize_callback' => 'sanitize_text_field',
   ]);
}
add_action('init', 'register_documents_file_meta');

// Render meta box for file upload
function render_documents_file_upload_field($post)
{
   $file_url = get_post_meta($post->ID, '_new_documents_file', true);
   $file_name = $file_url ? basename($file_url) : '';

   wp_nonce_field('documents_file_upload_nonce', 'documents_file_upload_nonce_field');
   wp_enqueue_media();
?>
   <div id="documents_file_upload" class="postbox">
      <div class="inside">
         <label for="documents_file"><?php esc_html_e("Upload a file:", "codeweber"); ?></label>
         <button type="button" class="button" id="documents_file_button"><?php esc_html_e("Select File", "codeweber"); ?></button>
         <input type="hidden" id="documents_file" name="documents_file" value="<?php echo esc_attr($file_url); ?>">

         <?php if ($file_name): ?>
            <p><strong><?php esc_html_e("Current File:", "codeweber"); ?></strong> <?php echo esc_html($file_name); ?></p>
         <?php endif; ?>
      </div>
   </div>

   <script type="text/javascript">
      jQuery(document).ready(function($) {
         var mediaUploader;

         $('#documents_file_button').click(function(e) {
            e.preventDefault();
            if (mediaUploader) {
               mediaUploader.open();
               return;
            }
            mediaUploader = wp.media({
               title: '<?php esc_attr_e("Select or Upload a Document", "codeweber"); ?>',
               button: {
                  text: '<?php esc_attr_e("Use this file", "codeweber"); ?>'
               },
               multiple: false
            });

            mediaUploader.on('select', function() {
               var attachment = mediaUploader.state().get('selection').first().toJSON();
               $('#documents_file').val(attachment.url);
               $('#documents_file_button').text(attachment.filename);
            });

            mediaUploader.open();
         });
      });
   </script>
<?php
}

// Add meta box for file upload
function documents_file_upload_meta_box()
{
   add_meta_box(
      'documents_file_upload',
      esc_html__('Upload File', 'codeweber'),
      'render_documents_file_upload_field',
      'documents',
      'normal',
      'high'
   );
}
add_action('add_meta_boxes', 'documents_file_upload_meta_box');

// Save file upload on post save
function save_documents_file_upload($post_id)
{
   if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;
   if (!isset($_POST['documents_file_upload_nonce_field']) || !wp_verify_nonce($_POST['documents_file_upload_nonce_field'], 'documents_file_upload_nonce')) {
      return $post_id;
   }
   if ('documents' !== get_post_type($post_id)) return $post_id;

   if (!empty($_POST['documents_file'])) {
      update_post_meta($post_id, '_new_documents_file', esc_url_raw($_POST['documents_file']));
   } else {
      if (isset($_POST['_delete_documents_file']) && $_POST['_delete_documents_file'] === '1') {
         delete_post_meta($post_id, '_new_documents_file');
      }
   }
   return $post_id;
}
add_action('save_post', 'save_documents_file_upload');

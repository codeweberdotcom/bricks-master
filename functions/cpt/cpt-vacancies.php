<?php

function cptui_register_my_cpts_vacancies()
{
   /**
    * Post Type: Vacancies.
    */

   $labels = [
      "name" => esc_html__("Vacancies", "codeweber"),
      "singular_name" => esc_html__("Vacancy", "codeweber"),
      "menu_name" => esc_html__("Vacancies", "codeweber"),
      "all_items" => esc_html__("All Vacancies", "codeweber"),
      "add_new" => esc_html__("Add New Vacancy", "codeweber"),
      "add_new_item" => esc_html__("Add Vacancy", "codeweber"),
      "edit_item" => esc_html__("Edit Vacancy", "codeweber"),
      "new_item" => esc_html__("New Vacancy", "codeweber"),
      "view_item" => esc_html__("View Vacancy", "codeweber"),
      "view_items" => esc_html__("View Vacancies", "codeweber"),
      "search_items" => esc_html__("Search Vacancy", "codeweber"),
      "not_found" => esc_html__("No Vacancies found", "codeweber"),
      "not_found_in_trash" => esc_html__("No Vacancies found in trash", "codeweber"),
      "items_list" => esc_html__("Vacancies list", "codeweber"),
      "name_admin_bar" => esc_html__("Vacancy", "codeweber"),
      "item_published" => esc_html__("Vacancy published", "codeweber"),
      "item_updated" => esc_html__("Vacancy updated", "codeweber"),
   ];

   $args = [
      "label" => esc_html__("Vacancies", "codeweber"),
      "labels" => $labels,
      "description" => "",
      "public" => true,
      "publicly_queryable" => true,
      "show_ui" => true,
      "show_in_rest" => true,
      "rest_base" => "",
      "rest_controller_class" => "WP_REST_Posts_Controller",
      "rest_namespace" => "wp/v2",
      "has_archive" => "vacancies",
      "show_in_menu" => true,
      "show_in_nav_menus" => true,
      "delete_with_user" => false,
      "exclude_from_search" => false,
      "capability_type" => "post",
      "map_meta_cap" => true,
      "hierarchical" => false,
      "can_export" => true,
      "rewrite" => ["slug" => "vacancies", "with_front" => true],
      "query_var" => true,
      "supports" => ["title",  "thumbnail", "revisions", "author"],
      "show_in_graphql" => false,
      "menu_icon" => "dashicons-businessperson",
   ];

   register_post_type("vacancies", $args);
}

add_action('init', 'cptui_register_my_cpts_vacancies');

// Таксономия: Vacancy Type
function cptui_register_my_taxes_vacancy_type()
{
   /**
    * Taxonomy: Vacancy Types.
    */

   $labels = [
      "name" => esc_html__("Vacancy Types", "codeweber"),
      "singular_name" => esc_html__("Vacancy Type", "codeweber"),
      "menu_name" => esc_html__("Vacancy Types", "codeweber"),
      "all_items" => esc_html__("All Vacancy Types", "codeweber"),
      "edit_item" => esc_html__("Edit Vacancy Type", "codeweber"),
      "view_item" => esc_html__("View Vacancy Type", "codeweber"),
      "update_item" => esc_html__("Update Vacancy Type", "codeweber"),
      "add_new_item" => esc_html__("Add New Vacancy Type", "codeweber"),
      "new_item_name" => esc_html__("New Vacancy Type Name", "codeweber"),
      "search_items" => esc_html__("Search Vacancy Types", "codeweber"),
      "popular_items" => esc_html__("Popular Vacancy Types", "codeweber"),
      "separate_items_with_commas" => esc_html__("Separate vacancy types with commas", "codeweber"),
      "add_or_remove_items" => esc_html__("Add or remove vacancy types", "codeweber"),
      "choose_from_most_used" => esc_html__("Choose from the most used vacancy types", "codeweber"),
      "not_found" => esc_html__("No vacancy types found", "codeweber"),
      "no_terms" => esc_html__("No vacancy types", "codeweber"),
      "items_list_navigation" => esc_html__("Vacancy types list navigation", "codeweber"),
      "items_list" => esc_html__("Vacancy types list", "codeweber"),
   ];

   $args = [
      "label" => esc_html__("Vacancy Types", "codeweber"),
      "labels" => $labels,
      "public" => false,
      "publicly_queryable" => false,
      "hierarchical" => false,
      "show_ui" => true,
      "show_in_menu" => true,
      "show_in_nav_menus" => false,
      "query_var" => false,
      "rewrite" => false,
      "show_admin_column" => true,
      "show_in_rest" => true,
      "show_tagcloud" => false,
      "rest_base" => "vacancy_type",
      "rest_controller_class" => "WP_REST_Terms_Controller",
      "rest_namespace" => "wp/v2",
      "show_in_quick_edit" => true,
      "sort" => true,
      "show_in_graphql" => false,
   ];

   register_taxonomy("vacancy_type", ["vacancies"], $args);
}

add_action('init', 'cptui_register_my_taxes_vacancy_type');

// Включение классического редактора для Vacancies
function enable_classic_editor_for_vacancies($use_block_editor, $post_type)
{
   if ($post_type === 'vacancies') {
      return false;
   }
   return $use_block_editor;
}
add_filter('use_block_editor_for_post_type', 'enable_classic_editor_for_vacancies', 10, 2);

function disable_gutenberg_for_vacancies($can_edit, $post)
{
   if (empty($post->ID)) {
      return $can_edit;
   }

   if ('vacancies' === get_post_type($post->ID)) {
      return false;
   }

   return $can_edit;
}
add_filter('gutenberg_can_edit_post_type', 'disable_gutenberg_for_vacancies', 10, 2);
add_filter('use_block_editor_for_post', 'disable_gutenberg_for_vacancies', 10, 2);

// Подключение классического редактора
function add_classic_editor_support()
{
   add_filter('user_can_richedit', function ($can) {
      global $post;
      if ($post && $post->post_type === 'vacancies') {
         return true;
      }
      return $can;
   });
}
add_action('admin_init', 'add_classic_editor_support');

// Метабоксы для вакансий
function vacancies_meta_boxes()
{
   add_meta_box(
      'vacancy_basic_info',
      __('Basic Vacancy Information', 'codeweber'),
      'vacancy_basic_info_callback',
      'vacancies',
      'normal',
      'high'
   );

   add_meta_box(
      'vacancy_content',
      __('Vacancy Content', 'codeweber'),
      'vacancy_content_callback',
      'vacancies',
      'normal',
      'high'
   );

   add_meta_box(
      'vacancy_attributes',
      __('Vacancy Attributes', 'codeweber'),
      'vacancy_attributes_callback',
      'vacancies',
      'normal',
      'default'
   );

   add_meta_box(
      'vacancy_pdf',
      __('Vacancy PDF File', 'codeweber'),
      'vacancy_pdf_callback',
      'vacancies',
      'side',
      'default'
   );

   add_meta_box(
      'vacancy_status',
      __('Vacancy Status', 'codeweber'),
      'vacancy_status_callback',
      'vacancies',
      'side',
      'high'
   );
}
add_action('add_meta_boxes', 'vacancies_meta_boxes');

// Колбэк для основной информации
function vacancy_basic_info_callback($post)
{
   wp_nonce_field('vacancy_save_data', 'vacancy_nonce');

   $company = get_post_meta($post->ID, '_vacancy_company', true);
   $location = get_post_meta($post->ID, '_vacancy_location', true);
   $email = get_post_meta($post->ID, '_vacancy_email', true);
   $apply_url = get_post_meta($post->ID, '_vacancy_apply_url', true);
   $salary = get_post_meta($post->ID, '_vacancy_salary', true);
   $linkedin_url = get_post_meta($post->ID, '_vacancy_linkedin_url', true); // Новое поле LinkedIn

?>
   <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
      <div>
         <label for="vacancy_company" style="display: block; margin-bottom: 5px; font-weight: bold;">
            <?php _e('Company', 'codeweber'); ?>
         </label>
         <input type="text" id="vacancy_company" name="vacancy_company" value="<?php echo esc_attr($company); ?>"
            style="width: 100%; padding: 8px;" placeholder="Horizons Corporate Advisory">
      </div>

      <div>
         <label for="vacancy_location" style="display: block; margin-bottom: 5px; font-weight: bold;">
            <?php _e('Location', 'codeweber'); ?>
         </label>
         <input type="text" id="vacancy_location" name="vacancy_location" value="<?php echo esc_attr($location); ?>"
            style="width: 100%; padding: 8px;" placeholder="Shanghai">
      </div>

      <div>
         <label for="vacancy_salary" style="display: block; margin-bottom: 5px; font-weight: bold;">
            <?php _e('Salary', 'codeweber'); ?>
         </label>
         <input type="text" id="vacancy_salary" name="vacancy_salary" value="<?php echo esc_attr($salary); ?>"
            style="width: 100%; padding: 8px;" placeholder="€50,000 - €70,000 per year">
      </div>

      <div>
         <label for="vacancy_email" style="display: block; margin-bottom: 5px; font-weight: bold;">
            <?php _e('Application Email', 'codeweber'); ?>
         </label>
         <input type="email" id="vacancy_email" name="vacancy_email" value="<?php echo esc_attr($email); ?>"
            style="width: 100%; padding: 8px;" placeholder="careers@horizons-advisory.com">
      </div>

      <div>
         <label for="vacancy_apply_url" style="display: block; margin-bottom: 5px; font-weight: bold;">
            <?php _e('Application URL', 'codeweber'); ?>
         </label>
         <input type="url" id="vacancy_apply_url" name="vacancy_apply_url" value="<?php echo esc_attr($apply_url); ?>"
            style="width: 100%; padding: 8px;" placeholder="https://...">
      </div>

      <div>
         <label for="vacancy_linkedin_url" style="display: block; margin-bottom: 5px; font-weight: bold;">
            <?php _e('LinkedIn URL', 'codeweber'); ?>
         </label>
         <input type="url" id="vacancy_linkedin_url" name="vacancy_linkedin_url" value="<?php echo esc_attr($linkedin_url); ?>"
            style="width: 100%; padding: 8px;" placeholder="https://linkedin.com/...">
      </div>
   </div>
<?php
}

// Колбэк для контента вакансии
function vacancy_content_callback($post)
{
   $introduction = get_post_meta($post->ID, '_vacancy_introduction', true);
   $additional_info = get_post_meta($post->ID, '_vacancy_additional_info', true);

   $responsibilities = get_post_meta($post->ID, '_vacancy_responsibilities', true);
   $requirements = get_post_meta($post->ID, '_vacancy_requirements', true);

   if (!is_array($responsibilities)) $responsibilities = [''];
   if (!is_array($requirements)) $requirements = [''];

?>
   <div style="margin-bottom: 20px;">
      <label for="vacancy_introduction" style="display: block; margin-bottom: 5px; font-weight: bold;">
         <?php _e('Introduction / Short Description', 'codeweber'); ?>
      </label>
      <?php
      wp_editor($introduction, 'vacancy_introduction', [
         'textarea_name' => 'vacancy_introduction',
         'textarea_rows' => 5,
         'media_buttons' => false,
         'teeny' => true
      ]);
      ?>
   </div>

   <div style="margin-bottom: 20px;">
      <h3><?php _e('Responsibilities', 'codeweber'); ?></h3>
      <div id="responsibilities-container">
         <?php foreach ($responsibilities as $index => $responsibility): ?>
            <div class="responsibility-item" style="margin-bottom: 10px;">
               <textarea name="vacancy_responsibilities[]"
                  placeholder="<?php _e('Enter responsibility...', 'codeweber'); ?>"
                  style="width: 100%; padding: 8px; min-height: 60px;"><?php echo esc_textarea($responsibility); ?></textarea>
               <button type="button" class="button remove-responsibility" style="margin-top: 5px;"><?php _e('Remove', 'codeweber'); ?></button>
            </div>
         <?php endforeach; ?>
      </div>
      <button type="button" id="add-responsibility" class="button"><?php _e('Add Responsibility', 'codeweber'); ?></button>
   </div>

   <div style="margin-bottom: 20px;">
      <h3><?php _e('Requirements', 'codeweber'); ?></h3>
      <div id="requirements-container">
         <?php foreach ($requirements as $index => $requirement): ?>
            <div class="requirement-item" style="margin-bottom: 10px;">
               <textarea name="vacancy_requirements[]"
                  placeholder="<?php _e('Enter requirement...', 'codeweber'); ?>"
                  style="width: 100%; padding: 8px; min-height: 60px;"><?php echo esc_textarea($requirement); ?></textarea>
               <button type="button" class="button remove-requirement" style="margin-top: 5px;"><?php _e('Remove', 'codeweber'); ?></button>
            </div>
         <?php endforeach; ?>
      </div>
      <button type="button" id="add-requirement" class="button"><?php _e('Add Requirement', 'codeweber'); ?></button>
   </div>

   <div>
      <label for="vacancy_additional_info" style="display: block; margin-bottom: 5px; font-weight: bold;">
         <?php _e('Additional Information', 'codeweber'); ?>
      </label>
      <?php
      wp_editor($additional_info, 'vacancy_additional_info', [
         'textarea_name' => 'vacancy_additional_info',
         'textarea_rows' => 5,
         'media_buttons' => false,
         'teeny' => true
      ]);
      ?>
   </div>

   <script>
      jQuery(document).ready(function($) {
         $('#add-responsibility').click(function() {
            var newItem = $('<div class="responsibility-item" style="margin-bottom: 10px;">' +
               '<textarea name="vacancy_responsibilities[]" placeholder="<?php _e('Enter responsibility...', 'codeweber'); ?>"' +
               ' style="width: 100%; padding: 8px; min-height: 60px;"></textarea>' +
               '<button type="button" class="button remove-responsibility" style="margin-top: 5px;"><?php _e('Remove', 'codeweber'); ?></button>' +
               '</div>');
            $('#responsibilities-container').append(newItem);
         });

         $('#add-requirement').click(function() {
            var newItem = $('<div class="requirement-item" style="margin-bottom: 10px;">' +
               '<textarea name="vacancy_requirements[]" placeholder="<?php _e('Enter requirement...', 'codeweber'); ?>"' +
               ' style="width: 100%; padding: 8px; min-height: 60px;"></textarea>' +
               '<button type="button" class="button remove-requirement" style="margin-top: 5px;"><?php _e('Remove', 'codeweber'); ?></button>' +
               '</div>');
            $('#requirements-container').append(newItem);
         });

         $(document).on('click', '.remove-responsibility, .remove-requirement', function() {
            if ($(this).closest('.responsibility-item, .requirement-item').siblings().length > 0) {
               $(this).closest('.responsibility-item, .requirement-item').remove();
            }
         });
      });
   </script>
<?php
}

// Колбэк для атрибутов вакансии
function vacancy_attributes_callback($post)
{
   $employment_type = get_post_meta($post->ID, '_vacancy_employment_type', true);
   $experience = get_post_meta($post->ID, '_vacancy_experience', true);
   $education = get_post_meta($post->ID, '_vacancy_education', true);
   $languages = get_post_meta($post->ID, '_vacancy_languages', true);
   $skills = get_post_meta($post->ID, '_vacancy_skills', true);

   if (!is_array($languages)) $languages = [''];
   if (!is_array($skills)) $skills = [''];

?>
   <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
      <div>
         <label for="vacancy_employment_type" style="display: block; margin-bottom: 5px; font-weight: bold;">
            <?php _e('Employment Type', 'codeweber'); ?>
         </label>
         <select id="vacancy_employment_type" name="vacancy_employment_type" style="width: 100%; padding: 8px;">
            <option value=""><?php _e('Select type', 'codeweber'); ?></option>
            <option value="full-time" <?php selected($employment_type, 'full-time'); ?>><?php _e('Full-time', 'codeweber'); ?></option>
            <option value="part-time" <?php selected($employment_type, 'part-time'); ?>><?php _e('Part-time', 'codeweber'); ?></option>
            <option value="internship" <?php selected($employment_type, 'internship'); ?>><?php _e('Internship', 'codeweber'); ?></option>
            <option value="contract" <?php selected($employment_type, 'contract'); ?>><?php _e('Contract', 'codeweber'); ?></option>
         </select>
      </div>

      <div>
         <label for="vacancy_experience" style="display: block; margin-bottom: 5px; font-weight: bold;">
            <?php _e('Work Experience', 'codeweber'); ?>
         </label>
         <input type="text" id="vacancy_experience" name="vacancy_experience" value="<?php echo esc_attr($experience); ?>"
            style="width: 100%; padding: 8px;" placeholder="1–3 years">
      </div>

      <div>
         <label for="vacancy_education" style="display: block; margin-bottom: 5px; font-weight: bold;">
            <?php _e('Education / Qualification', 'codeweber'); ?>
         </label>
         <textarea id="vacancy_education" name="vacancy_education"
            style="width: 100%; padding: 8px; min-height: 60px;"
            placeholder="University degree..."><?php echo esc_textarea($education); ?></textarea>
      </div>

      <div>
         <label style="display: block; margin-bottom: 5px; font-weight: bold;">
            <?php _e('Languages', 'codeweber'); ?>
         </label>
         <div id="languages-container">
            <?php foreach ($languages as $index => $language): ?>
               <div class="language-item" style="margin-bottom: 5px;">
                  <input type="text" name="vacancy_languages[]" value="<?php echo esc_attr($language); ?>"
                     placeholder="Chinese (native)" style="width: 100%; padding: 8px;">
               </div>
            <?php endforeach; ?>
         </div>
         <button type="button" id="add-language" class="button" style="margin-top: 5px;"><?php _e('Add Language', 'codeweber'); ?></button>
      </div>

      <div style="grid-column: 1 / -1;">
         <label style="display: block; margin-bottom: 5px; font-weight: bold;">
            <?php _e('Skills', 'codeweber'); ?>
         </label>
         <div id="skills-container">
            <?php foreach ($skills as $index => $skill): ?>
               <div class="skill-item" style="margin-bottom: 5px;">
                  <input type="text" name="vacancy_skills[]" value="<?php echo esc_attr($skill); ?>"
                     placeholder="Microsoft Excel" style="width: 100%; padding: 8px;">
               </div>
            <?php endforeach; ?>
         </div>
         <button type="button" id="add-skill" class="button" style="margin-top: 5px;"><?php _e('Add Skill', 'codeweber'); ?></button>
      </div>
   </div>

   <script>
      jQuery(document).ready(function($) {
         $('#add-language').click(function() {
            var newItem = $('<div class="language-item" style="margin-bottom: 5px;">' +
               '<input type="text" name="vacancy_languages[]" placeholder="<?php _e('Chinese (native)', 'codeweber'); ?>"' +
               ' style="width: 100%; padding: 8px;">' +
               '</div>');
            $('#languages-container').append(newItem);
         });

         $('#add-skill').click(function() {
            var newItem = $('<div class="skill-item" style="margin-bottom: 5px;">' +
               '<input type="text" name="vacancy_skills[]" placeholder="<?php _e('Microsoft Excel', 'codeweber'); ?>"' +
               ' style="width: 100%; padding: 8px;">' +
               '</div>');
            $('#skills-container').append(newItem);
         });
      });
   </script>
<?php
}

// Колбэк для загрузки PDF
function vacancy_pdf_callback($post)
{
   $pdf_id = get_post_meta($post->ID, '_vacancy_pdf', true);
   $pdf_url = $pdf_id ? wp_get_attachment_url($pdf_id) : '';
?>
   <div>
      <input type="hidden" id="vacancy_pdf_id" name="vacancy_pdf_id" value="<?php echo esc_attr($pdf_id); ?>">
      <input type="text" id="vacancy_pdf_url" value="<?php echo esc_url($pdf_url); ?>"
         style="width: 100%; margin-bottom: 10px;" readonly placeholder="<?php _e('No PDF selected', 'codeweber'); ?>">

      <button type="button" id="upload_pdf_button" class="button"><?php _e('Upload PDF', 'codeweber'); ?></button>
      <button type="button" id="remove_pdf_button" class="button" style="<?php echo empty($pdf_url) ? 'display: none;' : ''; ?>">
         <?php _e('Remove PDF', 'codeweber'); ?>
      </button>
   </div>

   <script>
      jQuery(document).ready(function($) {
         var frame;

         $('#upload_pdf_button').click(function(e) {
            e.preventDefault();

            if (frame) {
               frame.open();
               return;
            }

            frame = wp.media({
               title: '<?php _e('Select PDF File', 'codeweber'); ?>',
               button: {
                  text: '<?php _e('Use this PDF', 'codeweber'); ?>'
               },
               multiple: false,
               library: {
                  type: 'application/pdf'
               }
            });

            frame.on('select', function() {
               var attachment = frame.state().get('selection').first().toJSON();
               $('#vacancy_pdf_id').val(attachment.id);
               $('#vacancy_pdf_url').val(attachment.url);
               $('#remove_pdf_button').show();
            });

            frame.open();
         });

         $('#remove_pdf_button').click(function() {
            $('#vacancy_pdf_id').val('');
            $('#vacancy_pdf_url').val('');
            $(this).hide();
         });
      });
   </script>
<?php
}

// Колбэк для статуса вакансии
function vacancy_status_callback($post)
{
   $status = get_post_meta($post->ID, '_vacancy_status', true);
   $publish_date = get_the_date('Y-m-d', $post->ID);
?>
   <div>
      <label for="vacancy_status" style="display: block; margin-bottom: 5px; font-weight: bold;">
         <?php _e('Vacancy Status', 'codeweber'); ?>
      </label>
      <select id="vacancy_status" name="vacancy_status" style="width: 100%; padding: 8px; margin-bottom: 15px;">
         <option value="open" <?php selected($status, 'open'); ?>><?php _e('Open', 'codeweber'); ?></option>
         <option value="closed" <?php selected($status, 'closed'); ?>><?php _e('Closed', 'codeweber'); ?></option>
         <option value="archived" <?php selected($status, 'archived'); ?>><?php _e('Archived', 'codeweber'); ?></option>
      </select>

      <label for="vacancy_publish_date" style="display: block; margin-bottom: 5px; font-weight: bold;">
         <?php _e('Publish Date', 'codeweber'); ?>
      </label>
      <input type="date" id="vacancy_publish_date" value="<?php echo esc_attr($publish_date); ?>"
         style="width: 100%; padding: 8px;" readonly>
      <p style="font-size: 12px; color: #666; margin-top: 5px;">
         <?php _e('Use WordPress publish date', 'codeweber'); ?>
      </p>
   </div>
<?php
}

// Сохранение метаполей
function save_vacancy_meta($post_id)
{
   if (!isset($_POST['vacancy_nonce']) || !wp_verify_nonce($_POST['vacancy_nonce'], 'vacancy_save_data')) {
      return;
   }

   if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return;
   }

   if (!current_user_can('edit_post', $post_id)) {
      return;
   }

   if ('vacancies' !== $_POST['post_type']) {
      return;
   }

   // Основные поля
   $fields = [
      'vacancy_company',
      'vacancy_location',
      'vacancy_email',
      'vacancy_apply_url',
      'vacancy_salary',
      'vacancy_linkedin_url',
      'vacancy_telegram_url',
      'vacancy_whatsapp_url',
      'vacancy_introduction',
      'vacancy_additional_info',
      'vacancy_employment_type',
      'vacancy_experience',
      'vacancy_education',
      'vacancy_status'
   ];

   foreach ($fields as $field) {
      if (isset($_POST[$field])) {
         update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
      }
   }

   // Массивы
   $array_fields = [
      'vacancy_responsibilities',
      'vacancy_requirements',
      'vacancy_languages',
      'vacancy_skills'
   ];

   foreach ($array_fields as $field) {
      if (isset($_POST[$field])) {
         $values = array_map('sanitize_textarea_field', $_POST[$field]);
         $values = array_filter($values);
         update_post_meta($post_id, '_' . $field, $values);
      }
   }

   // PDF файл
   if (isset($_POST['vacancy_pdf_id'])) {
      update_post_meta($post_id, '_vacancy_pdf', intval($_POST['vacancy_pdf_id']));
   }
}
add_action('save_post', 'save_vacancy_meta');

// Подключение скриптов для медиазагрузчика
function vacancy_admin_scripts()
{
   global $post_type;

   if ($post_type === 'vacancies') {
      wp_enqueue_media();
      wp_enqueue_script('jquery');
   }
}
add_action('admin_enqueue_scripts', 'vacancy_admin_scripts');

function get_vacancy_data_array($post_id = null)
{
   if (!$post_id) {
      global $post;
      $post_id = $post->ID;
   }

   if (get_post_type($post_id) !== 'vacancies') {
      return false;
   }

   return [
      'company' => get_post_meta($post_id, '_vacancy_company', true),
      'location' => get_post_meta($post_id, '_vacancy_location', true),
      'email' => get_post_meta($post_id, '_vacancy_email', true),
      'apply_url' => get_post_meta($post_id, '_vacancy_apply_url', true),
      'salary' => get_post_meta($post_id, '_vacancy_salary', true),
      'linkedin_url' => get_post_meta($post_id, '_vacancy_linkedin_url', true), // Новое поле LinkedIn
      'introduction' => get_post_meta($post_id, '_vacancy_introduction', true),
      'additional_info' => get_post_meta($post_id, '_vacancy_additional_info', true),
      'employment_type' => get_post_meta($post_id, '_vacancy_employment_type', true),
      'experience' => get_post_meta($post_id, '_vacancy_experience', true),
      'education' => get_post_meta($post_id, '_vacancy_education', true),
      'status' => get_post_meta($post_id, '_vacancy_status', true),
      'responsibilities' => get_post_meta($post_id, '_vacancy_responsibilities', true),
      'requirements' => get_post_meta($post_id, '_vacancy_requirements', true),
      'languages' => get_post_meta($post_id, '_vacancy_languages', true),
      'skills' => get_post_meta($post_id, '_vacancy_skills', true),
      'pdf_url' => get_post_meta($post_id, '_vacancy_pdf', true) ? wp_get_attachment_url(get_post_meta($post_id, '_vacancy_pdf', true)) : '',
      'vacancy_types' => get_the_terms($post_id, 'vacancy_type')
   ];
}

/**
 * REST API endpoint для получения URL PDF файла вакансии для AJAX загрузки
 */
function register_vacancy_download_endpoint() {
   register_rest_route('codeweber/v1', '/vacancies/(?P<id>\d+)/download-url', [
      'methods' => 'GET',
      'callback' => 'get_vacancy_download_url',
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
add_action('rest_api_init', 'register_vacancy_download_endpoint');

/**
 * Callback для получения URL PDF файла вакансии
 * 
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function get_vacancy_download_url($request) {
   $post_id = $request->get_param('id');
   
   // Проверяем, что пост существует и это vacancies
   $post = get_post($post_id);
   if (!$post || $post->post_type !== 'vacancies') {
      return new WP_Error(
         'invalid_post',
         __('Vacancy not found', 'codeweber'),
         ['status' => 404]
      );
   }
   
   // Получаем ID вложения PDF файла
   $pdf_id = get_post_meta($post_id, '_vacancy_pdf', true);
   
   if (empty($pdf_id)) {
      return new WP_Error(
         'no_file',
         __('PDF file not found for this vacancy', 'codeweber'),
         ['status' => 404]
      );
   }
   
   // Получаем URL файла
   $file_url = wp_get_attachment_url($pdf_id);
   
   if (empty($file_url)) {
      return new WP_Error(
         'no_file',
         __('PDF file not found for this vacancy', 'codeweber'),
         ['status' => 404]
      );
   }
   
   // Получаем имя файла
   $file_name = basename(get_attached_file($pdf_id));
   if (empty($file_name)) {
      $file_name = 'vacancy-' . $post_id . '.pdf';
   }
   
   // Опционально: логирование загрузки
   do_action('vacancy_downloaded', $post_id);
   
   return new WP_REST_Response([
      'success' => true,
      'file_url' => esc_url_raw($file_url),
      'file_name' => $file_name,
      'post_id' => $post_id
   ], 200);
}
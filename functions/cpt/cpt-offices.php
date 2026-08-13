<?php

function cptui_register_my_offices()
{

   /**
    * Post Type: Offices.
    */

   $labels = [
      "name" => esc_html__("Offices", "codeweber"),
      "singular_name" => esc_html__("Office", "codeweber"),
      "menu_name" => esc_html__("Offices", "codeweber"),
      "all_items" => esc_html__("All Offices", "codeweber"),
      "add_new" => esc_html__("Add New Office", "codeweber"),
      "add_new_item" => esc_html__("Add New Office", "codeweber"),
      "edit_item" => esc_html__("Edit Office", "codeweber"),
      "new_item" => esc_html__("New Office", "codeweber"),
      "view_item" => esc_html__("View Office", "codeweber"),
      "view_items" => esc_html__("View Offices", "codeweber"),
      "search_items" => esc_html__("Search Offices", "codeweber"),
      "not_found" => esc_html__("No offices found", "codeweber"),
      "not_found_in_trash" => esc_html__("No offices found in Trash", "codeweber"),
      "parent_item_colon" => esc_html__("Parent Office:", "codeweber"),
   ];

   $args = [
      "label" => esc_html__("Offices", "codeweber"),
      "labels" => $labels,
      "description" => esc_html__("A custom post type for managing office locations", "codeweber"),
      "public" => true,
      "publicly_queryable" => true,
      "show_ui" => true,
      "show_in_rest" => true,
      "rest_base" => "",
      "rest_controller_class" => "WP_REST_Posts_Controller",
      "rest_namespace" => "wp/v2",
      "has_archive" => "offices",
      "show_in_menu" => true,
      "show_in_nav_menus" => true,
      "delete_with_user" => false,
      "exclude_from_search" => false,
      "capability_type" => "post",
      "map_meta_cap" => true,
      "hierarchical" => false,
      "can_export" => true,
      "rewrite" => ["slug" => "offices", "with_front" => true],
      "query_var" => true,
      "supports" => ["title", "thumbnail", "editor", "excerpt"],
      "show_in_graphql" => false,
   ];

   register_post_type("offices", $args);
}

add_action('init', 'cptui_register_my_offices');

function cptui_register_my_taxes_towns()
{

   /**
    * Taxonomy: Towns.
    */

   $labels = [
      "name" => esc_html__("Towns", "codeweber"),
      "singular_name" => esc_html__("Town", "codeweber"),
   ];

   $args = [
      "label" => esc_html__("Towns", "codeweber"),
      "labels" => $labels,
      "public" => true,
      "publicly_queryable" => false,
      "hierarchical" => false,
      "show_ui" => true,
      "show_in_menu" => true,
      "show_in_nav_menus" => true,
      "query_var" => true,
      "rewrite" => ['slug' => 'towns', 'with_front' => true],
      "show_admin_column" => true,
      "show_in_rest" => true,
      "show_tagcloud" => false,
      "rest_base" => "towns",
      "rest_controller_class" => "WP_REST_Terms_Controller",
      "rest_namespace" => "wp/v2",
      "show_in_quick_edit" => true,
      "sort" => true,
      "show_in_graphql" => false,
   ];

   register_taxonomy("towns", ["offices"], $args);
}

add_action('init', 'cptui_register_my_taxes_towns');

/**
 * Add metaboxes with additional fields for CPT offices
 */
function codeweber_add_offices_meta_boxes()
{
   add_meta_box(
      'office_information_location',
      esc_html__('Office Information & Location', 'codeweber'),
      'codeweber_office_information_location_callback',
      'offices',
      'normal',
      'high'
   );

   add_meta_box(
      'office_contact_schedule',
      esc_html__('Contact Details & Working Hours', 'codeweber'),
      'codeweber_office_contact_schedule_callback',
      'offices',
      'normal',
      'default'
   );

   add_meta_box(
      'office_relations',
      esc_html__('Office Relations', 'codeweber'),
      'codeweber_office_relations_callback',
      'offices',
      'normal',
      'default'
   );
}
add_action('add_meta_boxes', 'codeweber_add_offices_meta_boxes');

/**
 * Render office details and the Yandex map in a responsive two-column layout.
 */
function codeweber_office_information_location_callback($post)
{
   ?>
   <style>
      .codeweber-office-information-grid {
         display: grid;
         grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
         gap: 24px;
         align-items: start;
      }
      .codeweber-office-information-grid__column > h3 {
         margin: 0 0 16px;
         padding-bottom: 10px;
         border-bottom: 1px solid #dcdcde;
      }
      .codeweber-office-information-grid__map {
         padding-left: 24px;
         border-left: 1px solid #dcdcde;
      }
      @media screen and (max-width: 1100px) {
         .codeweber-office-information-grid {
            grid-template-columns: 1fr;
         }
         .codeweber-office-information-grid__map {
            padding-top: 24px;
            padding-left: 0;
            border-top: 1px solid #dcdcde;
            border-left: 0;
         }
      }
   </style>
   <div class="codeweber-office-information-grid">
      <section class="codeweber-office-information-grid__column">
         <h3><?php echo esc_html__('Office Information', 'codeweber'); ?></h3>
         <?php codeweber_office_basic_info_callback($post); ?>
      </section>
      <section class="codeweber-office-information-grid__column codeweber-office-information-grid__map">
         <h3><?php echo esc_html__('Location & Map', 'codeweber'); ?></h3>
         <?php codeweber_office_location_callback($post); ?>
      </section>
   </div>
   <?php
}

/**
 * Render services, staff and vacancies in one responsive three-column box.
 */
function codeweber_office_relations_callback($post)
{
   ?>
   <style>
      .codeweber-office-relations-grid {
         display: grid;
         grid-template-columns: repeat(4, minmax(0, 1fr));
         gap: 0;
      }
      .codeweber-office-relations-grid__column {
         min-width: 0;
         padding: 0 20px;
         border-left: 1px solid #dcdcde;
      }
      .codeweber-office-relations-grid__column:first-child {
         padding-left: 0;
         border-left: 0;
      }
      .codeweber-office-relations-grid__column:last-child {
         padding-right: 0;
      }
      .codeweber-office-relations-grid__column > h3 {
         margin: 0 0 12px;
         padding-bottom: 10px;
         border-bottom: 1px solid #dcdcde;
      }
      @media screen and (max-width: 1100px) {
         .codeweber-office-relations-grid {
            grid-template-columns: 1fr;
         }
         .codeweber-office-relations-grid__column,
         .codeweber-office-relations-grid__column:first-child,
         .codeweber-office-relations-grid__column:last-child {
            padding: 20px 0;
            border-top: 1px solid #dcdcde;
            border-left: 0;
         }
         .codeweber-office-relations-grid__column:first-child {
            padding-top: 0;
            border-top: 0;
         }
      }
   </style>
   <div class="codeweber-office-relations-grid">
      <section class="codeweber-office-relations-grid__column">
         <h3><?php echo esc_html__('Available Services', 'codeweber'); ?></h3>
         <?php codeweber_office_services_callback($post); ?>
      </section>
      <section class="codeweber-office-relations-grid__column">
         <h3><?php echo esc_html__('Staff Members', 'codeweber'); ?></h3>
         <?php codeweber_office_staff_callback($post); ?>
      </section>
      <section class="codeweber-office-relations-grid__column">
         <h3><?php echo esc_html__('Related Vacancies', 'codeweber'); ?></h3>
         <?php codeweber_office_vacancies_callback($post); ?>
      </section>
      <section class="codeweber-office-relations-grid__column">
         <h3><?php echo esc_html__('Events', 'codeweber'); ?></h3>
         <?php codeweber_office_events_callback($post); ?>
      </section>
   </div>
   <?php
}

// Force-load TinyMCE scripts for wp_editor() inside Gutenberg meta boxes.
add_action('admin_enqueue_scripts', function(string $hook): void {
	global $post;
	if (in_array($hook, ['post.php', 'post-new.php'], true) && isset($post) && $post->post_type === 'offices') {
		wp_enqueue_editor();
	}
});

/**
 * Callback function for basic office information
 */
function codeweber_office_basic_info_callback($post)
{
   wp_nonce_field('office_meta_box', 'office_meta_box_nonce');

   $country = get_post_meta($post->ID, '_office_country', true);
   $region = get_post_meta($post->ID, '_office_region', true);
   $street = get_post_meta($post->ID, '_office_street', true);
   $postal_code = get_post_meta($post->ID, '_office_postal_code', true);
   $full_address = get_post_meta($post->ID, '_office_full_address', true);
   $landmark = get_post_meta($post->ID, '_office_landmark', true);
   $description = get_post_meta($post->ID, '_office_description', true);
   $office_hours = codeweber_get_office_hours( $post->ID );

   // Get selected term from towns taxonomy
   $town_terms = wp_get_post_terms($post->ID, 'towns', array('fields' => 'ids'));
   $selected_town_id = !empty($town_terms) && !is_wp_error($town_terms) ? $town_terms[0] : '';
   $selected_town_name = '';
   if ($selected_town_id) {
      $selected_town = get_term($selected_town_id, 'towns');
      if ($selected_town && !is_wp_error($selected_town)) {
         $selected_town_name = $selected_town->name;
      }
   }
   if ($selected_town_name === '') {
      $selected_town_name = (string) get_post_meta($post->ID, '_office_city', true);
   }

   // Get list of towns taxonomy terms
   $towns = get_terms(array(
      'taxonomy' => 'towns',
      'hide_empty' => false,
      'orderby' => 'name',
      'order' => 'ASC'
   ));

?>

   <div style="display: grid; grid-template-columns: 150px 1fr; gap: 12px; align-items: center;">
      <label for="office_country"><strong><?php echo esc_html__('Country', 'codeweber'); ?>:</strong></label>
      <input type="text" id="office_country" name="office_country" value="<?php echo esc_attr($country); ?>" style="width: 100%; padding: 8px;">

      <label for="office_region"><strong><?php echo esc_html__('Region', 'codeweber'); ?>:</strong></label>
      <input type="text" id="office_region" name="office_region" value="<?php echo esc_attr($region); ?>" style="width: 100%; padding: 8px;">

      <label for="office_city"><strong><?php echo esc_html__('City', 'codeweber'); ?>:</strong></label>
      <div>
         <input type="text" id="office_city" name="office_city" list="office-city-list" value="<?php echo esc_attr($selected_town_name); ?>" style="width:100%;padding:8px;" autocomplete="off">
         <datalist id="office-city-list">
            <?php if (!empty($towns) && !is_wp_error($towns)) : ?>
               <?php foreach ($towns as $town) : ?>
                  <option value="<?php echo esc_attr($town->name); ?>"></option>
               <?php endforeach; ?>
            <?php endif; ?>
         </datalist>
         <p class="description"><?php echo esc_html__('Select an existing city or enter a new one. A new city will be created automatically when the office is saved.', 'codeweber'); ?></p>
      </div>

      <label for="office_street"><strong><?php echo esc_html__('Street, House, Office', 'codeweber'); ?>:</strong></label>
      <input type="text" id="office_street" name="office_street" value="<?php echo esc_attr($street); ?>" style="width: 100%; padding: 8px;">

      <label for="office_postal_code"><strong><?php echo esc_html__('Postal Code', 'codeweber'); ?>:</strong></label>
      <input type="text" id="office_postal_code" name="office_postal_code" value="<?php echo esc_attr($postal_code); ?>" style="width: 100%; padding: 8px;">

      <label for="office_full_address"><strong><?php echo esc_html__('Full Address', 'codeweber'); ?>:</strong></label>
      <textarea id="office_full_address" name="office_full_address" rows="3" style="width: 100%; padding: 8px;"><?php echo esc_textarea($full_address); ?></textarea>

      <label for="office_landmark"><strong><?php echo esc_html__('Landmark', 'codeweber'); ?>:</strong></label>
      <input type="text" id="office_landmark" name="office_landmark" value="<?php echo esc_attr($landmark); ?>" style="width: 100%; padding: 8px;" placeholder="<?php echo esc_attr__('For example: entrance from the courtyard', 'codeweber'); ?>">

      <div style="grid-column: 1 / -1;">
         <label for="office_description" style="display:block;margin-bottom:5px;font-weight:bold;">
            <?php echo esc_html__('Description', 'codeweber'); ?>:
         </label>
         <?php
         wp_editor($description, 'office_description', array(
            'textarea_name' => 'office_description',
            'textarea_rows' => 7,
            'media_buttons' => false,
            'teeny' => true,
         ));
         ?>
      </div>

      <div style="grid-column: 1 / -1;">
         <?php codeweber_office_additional_callback($post); ?>
      </div>

      <div id="office-hours-section" style="grid-column: 1 / -1;">
         <strong class="office-hours-inline-title"><?php echo esc_html__('Working Hours', 'codeweber'); ?>:</strong>
         <?php
         $days = codeweber_opening_hours_days();
         $schedule_groups = [];
         foreach ( $days as $day_key => $day_label ) {
            $schedule = $office_hours[ $day_key ] ?? [ 'closed' => 1 ];
            $schedule = [
               'closed'   => ! empty( $schedule['closed'] ) ? 1 : 0,
               'opens_1'  => $schedule['opens_1'] ?? '',
               'closes_1' => $schedule['closes_1'] ?? '',
               'opens_2'  => $schedule['opens_2'] ?? '',
               'closes_2' => $schedule['closes_2'] ?? '',
            ];
            $signature = wp_json_encode( $schedule );
            if ( ! isset( $schedule_groups[ $signature ] ) ) {
               $schedule_groups[ $signature ] = [ 'days' => [], 'schedule' => $schedule ];
            }
            $schedule_groups[ $signature ]['days'][] = $day_key;
         }
         $schedule_groups = array_values( $schedule_groups );
         ?>
         <input type="hidden" name="office_hours_groups_submitted" value="1">
         <div id="office-hours-repeater" style="margin-top:8px;display:flex;flex-direction:column;gap:12px;">
            <?php foreach ( $schedule_groups as $group_index => $group ) : ?>
               <div class="office-hours-group" style="padding:12px;border:1px solid #dcdcde;background:#fff;border-radius:4px;">
                  <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;">
                     <div class="office-hours-days" style="display:flex;flex-wrap:wrap;gap:8px 12px;">
                        <?php foreach ( $days as $day_key => $day_label ) : ?>
                           <label><input type="checkbox" name="office_hours_groups[<?php echo esc_attr( $group_index ); ?>][days][]" value="<?php echo esc_attr( $day_key ); ?>" <?php checked( in_array( $day_key, $group['days'], true ) ); ?>> <?php echo esc_html( $day_label ); ?></label>
                        <?php endforeach; ?>
                     </div>
                     <button type="button" class="button-link-delete office-hours-remove"><?php esc_html_e('Remove', 'codeweber'); ?></button>
                  </div>
                  <label style="display:block;margin-top:12px;font-weight:600;">
                     <input type="checkbox" class="office-hours-closed" name="office_hours_groups[<?php echo esc_attr( $group_index ); ?>][closed]" value="1" <?php checked( ! empty( $group['schedule']['closed'] ) ); ?>>
                     <?php esc_html_e('Day off', 'codeweber'); ?>
                  </label>
                  <div class="office-hours-times" style="display:flex;flex-wrap:wrap;gap:10px;margin-top:10px;">
                     <label><?php esc_html_e('Opens', 'codeweber'); ?><br><input type="time" name="office_hours_groups[<?php echo esc_attr( $group_index ); ?>][opens_1]" value="<?php echo esc_attr( $group['schedule']['opens_1'] ); ?>"></label>
                     <label><?php esc_html_e('Break start', 'codeweber'); ?><br><input type="time" name="office_hours_groups[<?php echo esc_attr( $group_index ); ?>][closes_1]" value="<?php echo esc_attr( $group['schedule']['closes_1'] ); ?>"></label>
                     <label><?php esc_html_e('Break end', 'codeweber'); ?><br><input type="time" name="office_hours_groups[<?php echo esc_attr( $group_index ); ?>][opens_2]" value="<?php echo esc_attr( $group['schedule']['opens_2'] ); ?>"></label>
                     <label><?php esc_html_e('Closes', 'codeweber'); ?><br><input type="time" name="office_hours_groups[<?php echo esc_attr( $group_index ); ?>][closes_2]" value="<?php echo esc_attr( $group['schedule']['closes_2'] ); ?>"></label>
                  </div>
               </div>
            <?php endforeach; ?>
         </div>
         <button type="button" class="button" id="office-hours-add" style="margin-top:10px;"><?php esc_html_e('Add schedule', 'codeweber'); ?></button>
         <p class="description"><?php esc_html_e('Select one or more days for each schedule. If a day appears in multiple rows, the last row takes priority.', 'codeweber'); ?></p>

         <script type="text/html" id="tmpl-office-hours-group">
            <div class="office-hours-group" style="padding:12px;border:1px solid #dcdcde;background:#fff;border-radius:4px;">
               <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;">
                  <div class="office-hours-days" style="display:flex;flex-wrap:wrap;gap:8px 12px;">
                     <?php foreach ( $days as $day_key => $day_label ) : ?>
                        <label><input type="checkbox" name="office_hours_groups[__INDEX__][days][]" value="<?php echo esc_attr( $day_key ); ?>"> <?php echo esc_html( $day_label ); ?></label>
                     <?php endforeach; ?>
                  </div>
                  <button type="button" class="button-link-delete office-hours-remove"><?php esc_html_e('Remove', 'codeweber'); ?></button>
               </div>
               <label style="display:block;margin-top:12px;font-weight:600;"><input type="checkbox" class="office-hours-closed" name="office_hours_groups[__INDEX__][closed]" value="1"> <?php esc_html_e('Day off', 'codeweber'); ?></label>
               <div class="office-hours-times" style="display:flex;flex-wrap:wrap;gap:10px;margin-top:10px;">
                  <label><?php esc_html_e('Opens', 'codeweber'); ?><br><input type="time" name="office_hours_groups[__INDEX__][opens_1]" value="09:00"></label>
                  <label><?php esc_html_e('Break start', 'codeweber'); ?><br><input type="time" name="office_hours_groups[__INDEX__][closes_1]"></label>
                  <label><?php esc_html_e('Break end', 'codeweber'); ?><br><input type="time" name="office_hours_groups[__INDEX__][opens_2]"></label>
                  <label><?php esc_html_e('Closes', 'codeweber'); ?><br><input type="time" name="office_hours_groups[__INDEX__][closes_2]" value="18:00"></label>
               </div>
            </div>
         </script>
         <script>
         (function() {
            var list = document.getElementById('office-hours-repeater');
            var add = document.getElementById('office-hours-add');
            var template = document.getElementById('tmpl-office-hours-group');
            if (!list || !add || !template) return;
            var nextIndex = <?php echo (int) count( $schedule_groups ); ?>;

            function syncClosed(group) {
               var closed = group.querySelector('.office-hours-closed').checked;
               group.querySelectorAll('.office-hours-times input').forEach(function(input) { input.disabled = closed; });
               group.querySelector('.office-hours-times').style.opacity = closed ? '.45' : '1';
            }
            list.querySelectorAll('.office-hours-group').forEach(syncClosed);
            add.addEventListener('click', function() {
               var holder = document.createElement('div');
               holder.innerHTML = template.innerHTML.replace(/__INDEX__/g, nextIndex++).trim();
               var group = holder.firstElementChild;
               list.appendChild(group);
               syncClosed(group);
            });
            list.addEventListener('change', function(event) {
               if (event.target.classList.contains('office-hours-closed')) syncClosed(event.target.closest('.office-hours-group'));
            });
            list.addEventListener('click', function(event) {
               if (!event.target.classList.contains('office-hours-remove')) return;
               event.target.closest('.office-hours-group').remove();
            });
         })();
         </script>
      </div>

   </div>

<?php
}

/**
 * Render contact details and working hours in a responsive 50/50 layout.
 */
function codeweber_office_contact_schedule_callback($post)
{
   ?>
   <style>
      .codeweber-office-contact-schedule-grid {
         display: grid;
         grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
         gap: 24px;
         align-items: start;
      }
      .codeweber-office-contact-schedule-grid__column > h3 {
         margin: 0 0 16px;
         padding-bottom: 10px;
         border-bottom: 1px solid #dcdcde;
      }
      .codeweber-office-contact-schedule-grid__hours {
         padding-left: 24px;
         border-left: 1px solid #dcdcde;
      }
      .codeweber-office-contact-schedule-grid__hours .office-hours-inline-title {
         display: none;
      }
      @media screen and (max-width: 1100px) {
         .codeweber-office-contact-schedule-grid {
            grid-template-columns: 1fr;
         }
         .codeweber-office-contact-schedule-grid__hours {
            padding-top: 24px;
            padding-left: 0;
            border-top: 1px solid #dcdcde;
            border-left: 0;
         }
      }
   </style>
   <div class="codeweber-office-contact-schedule-grid">
      <section class="codeweber-office-contact-schedule-grid__column">
         <h3><?php echo esc_html__('Contact Details', 'codeweber'); ?></h3>
         <?php codeweber_office_contact_callback($post); ?>
      </section>
      <section class="codeweber-office-contact-schedule-grid__column codeweber-office-contact-schedule-grid__hours">
         <h3><?php echo esc_html__('Working Hours', 'codeweber'); ?></h3>
         <div id="office-hours-section-target"></div>
      </section>
   </div>
   <script>
   (function() {
      function moveOfficeHours() {
         var hours = document.getElementById('office-hours-section');
         var target = document.getElementById('office-hours-section-target');
         if (hours && target && hours.parentNode !== target) target.appendChild(hours);
      }
      moveOfficeHours();
      document.addEventListener('DOMContentLoaded', moveOfficeHours, { once: true });
   })();
   </script>
   <?php
}

/**
 * Callback function for contact details
 */
function codeweber_office_contact_callback($post)
{
   $phone = get_post_meta($post->ID, '_office_phone', true);
   $phone_2 = get_post_meta($post->ID, '_office_phone_2', true);
   $phones = get_post_meta($post->ID, '_office_phones', true);
   if (!is_array($phones)) {
      $phones = array_values(array_filter([$phone, $phone_2]));
   }
   if (empty($phones)) {
      $phones = [''];
   }
   $email = get_post_meta($post->ID, '_office_email', true);
   $website = get_post_meta($post->ID, '_office_website', true);
?>

   <div style="display: grid; grid-template-columns: 150px 1fr; gap: 12px; align-items: center;">
      <label style="align-self: start; padding-top: 8px;"><strong><?php echo esc_html__('Phones', 'codeweber'); ?>:</strong></label>
      <div id="office-phones-repeater">
         <div id="office-phones-list" style="display:flex;flex-direction:column;gap:8px;">
            <?php foreach ($phones as $office_phone) : ?>
               <div class="office-phone-row" style="display:flex;gap:8px;align-items:center;">
                  <input type="tel" name="office_phones[]" value="<?php echo esc_attr($office_phone); ?>" style="width:100%;padding:8px;" placeholder="+7 (___) ___-__-__">
                  <button type="button" class="button office-phone-remove" aria-label="<?php echo esc_attr__('Remove phone', 'codeweber'); ?>">&times;</button>
               </div>
            <?php endforeach; ?>
         </div>
         <button type="button" class="button" id="office-phone-add" style="margin-top:8px;"><?php echo esc_html__('Add phone', 'codeweber'); ?></button>
      </div>

      <label for="office_email"><strong><?php echo esc_html__('Email', 'codeweber'); ?>:</strong></label>
      <input type="email" id="office_email" name="office_email" value="<?php echo esc_attr($email); ?>" style="width: 100%; padding: 8px;">

      <label for="office_website"><strong><?php echo esc_html__('Website', 'codeweber'); ?>:</strong></label>
      <input type="url" id="office_website" name="office_website" value="<?php echo esc_attr($website); ?>" placeholder="https://..." style="width: 100%; padding: 8px;">
   </div>

   <script>
   (function() {
      var list = document.getElementById('office-phones-list');
      var addButton = document.getElementById('office-phone-add');
      if (!list || !addButton) return;

      function addPhone(value) {
         var row = document.createElement('div');
         row.className = 'office-phone-row';
         row.style.cssText = 'display:flex;gap:8px;align-items:center;';
         row.innerHTML = '<input type="tel" name="office_phones[]" style="width:100%;padding:8px;" placeholder="+7 (___) ___-__-__">' +
            '<button type="button" class="button office-phone-remove" aria-label="<?php echo esc_js(__('Remove phone', 'codeweber')); ?>">&times;</button>';
         row.querySelector('input').value = value || '';
         list.appendChild(row);
         row.querySelector('input').focus();
      }

      addButton.addEventListener('click', function() { addPhone(''); });
      list.addEventListener('click', function(event) {
         if (!event.target.classList.contains('office-phone-remove')) return;
         var rows = list.querySelectorAll('.office-phone-row');
         if (rows.length === 1) {
            rows[0].querySelector('input').value = '';
         } else {
            event.target.closest('.office-phone-row').remove();
         }
      });
   })();
   </script>

<?php
}

/**
 * Callback function for location and map coordinates
 */
function codeweber_office_location_callback($post)
{
   // Get API key from Redux
   global $opt_name;
   if (empty($opt_name)) {
      $opt_name = 'redux_demo';
   }
   $yandex_api_key = '';
   if (class_exists('Redux')) {
      $yandex_api_key = Redux::get_option($opt_name, 'yandexapi');
   }

   $latitude = get_post_meta($post->ID, '_office_latitude', true);
   $longitude = get_post_meta($post->ID, '_office_longitude', true);
   $zoom = get_post_meta($post->ID, '_office_zoom', true);
   $address = get_post_meta($post->ID, '_office_yandex_address', true);

   // Format coordinates as string for map
   $coordinates = '';
   if (!empty($latitude) && !empty($longitude)) {
      $coordinates = $latitude . ', ' . $longitude;
   }

   if (empty($zoom)) {
      $zoom = '10';
   }
?>

   <div style="margin-bottom: 20px;">
      <label style="display: block; margin-bottom: 5px; font-weight: bold;">
         <?php echo esc_html__('Map', 'codeweber'); ?>
      </label>
      <?php if (!empty($yandex_api_key)) : ?>
      <div style="position:relative;margin-bottom:12px;">
         <input type="text" id="office-map-search" placeholder="<?php esc_attr_e( 'Search address...', 'codeweber' ); ?>"
            style="width:100%;padding:8px;border:1px solid #8c8f94;border-radius:4px;box-sizing:border-box;">
      </div>
      <?php endif; ?>
      <div id="office-yandex-map" style="width: 100%; height: 400px; margin-bottom: 15px;"></div>

      <?php if (!empty($yandex_api_key)) : ?>
         <script src="https://api-maps.yandex.ru/v3/?apikey=<?php echo esc_attr($yandex_api_key); ?>&lang=ru_RU"></script>
         <script>
         (function() {
            var apiKey = '<?php echo esc_js($yandex_api_key); ?>';
            var geocodeUrl = 'https://geocode-maps.yandex.ru/1.x/?apikey=' + encodeURIComponent(apiKey) + '&format=json&lang=ru_RU';
            ymaps3.ready.then(function() {
               var YMap = ymaps3.YMap, YMapDefaultSchemeLayer = ymaps3.YMapDefaultSchemeLayer,
                   YMapDefaultFeaturesLayer = ymaps3.YMapDefaultFeaturesLayer,
                   YMapMarker = ymaps3.YMapMarker, YMapListener = ymaps3.YMapListener;

               var coordField   = document.querySelector("input[name='office_coordinates']");
               var latField     = document.querySelector("input[name='office_latitude']");
               var lngField     = document.querySelector("input[name='office_longitude']");
               var zoomField    = document.querySelector("input[name='office_zoom']");
               var addressField = document.querySelector("input[name='office_yandex_address']");
               var searchInput  = document.getElementById('office-map-search');
               var countryField = document.getElementById('office_country');
               var regionField  = document.getElementById('office_region');
               var cityField    = document.getElementById('office_city');
               var streetField  = document.getElementById('office_street');
               var postalField  = document.getElementById('office_postal_code');
               var fullAddressField = document.getElementById('office_full_address');

               var lat = 55.76, lng = 37.64, zoom = <?php echo (int) ($zoom ?: 10); ?>;
               if (coordField && coordField.value) {
                  var p = coordField.value.split(',').map(parseFloat);
                  if (p.length === 2 && !p.some(isNaN)) { lat = p[0]; lng = p[1]; }
               } else if (latField && latField.value && lngField && lngField.value) {
                  lat = parseFloat(latField.value); lng = parseFloat(lngField.value);
               }
               if (zoomField && zoomField.value) zoom = parseInt(zoomField.value) || zoom;

               var map = new YMap(document.getElementById('office-yandex-map'), {
                  location: { center: [lng, lat], zoom: zoom }
               });
               map.addChild(new YMapDefaultSchemeLayer());
               map.addChild(new YMapDefaultFeaturesLayer());

               var el = document.createElement('div');
               el.style.cssText = 'cursor:grab;width:28px;height:28px;transform:translate(-50%,-100%)';
               el.innerHTML = '<svg viewBox="0 0 24 24" fill="#d63638" xmlns="http://www.w3.org/2000/svg"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>';

               var marker = new YMapMarker({
                  coordinates: [lng, lat],
                  draggable: true,
                  onDragEnd: function(coords) { syncFields(coords[1], coords[0]); }
               }, el);
               map.addChild(marker);

               map.addChild(new YMapListener({
                  onClick: function(obj, event) {
                     var coords = event && event.coordinates ? event.coordinates : null;
                     if (!coords) return;
                     marker.update({ coordinates: coords });
                     syncFields(coords[1], coords[0]);
                  }
               }));

               map.addChild(new YMapListener({
                  onActionEnd: function() {
                     if (zoomField) { zoomField.value = Math.round(map.zoom); zoomField.dispatchEvent(new Event('input',{bubbles:true})); }
                  }
               }));

               function syncFields(latVal, lngVal) {
                  if (coordField)   { coordField.value = latVal + ', ' + lngVal; coordField.dispatchEvent(new Event('input',{bubbles:true})); }
                  if (latField)     { latField.value = latVal;                   latField.dispatchEvent(new Event('input',{bubbles:true})); }
                  if (lngField)     { lngField.value = lngVal;                   lngField.dispatchEvent(new Event('input',{bubbles:true})); }
                  if (zoomField)    { zoomField.value = Math.round(map.zoom);    zoomField.dispatchEvent(new Event('input',{bubbles:true})); }
                  if (addressField) {
                     fetch(geocodeUrl + '&geocode=' + lngVal + ',' + latVal + '&results=1')
                        .then(function(r) { return r.json(); })
                        .then(function(d) {
                           var fm = d.response && d.response.GeoObjectCollection && d.response.GeoObjectCollection.featureMember;
                           if (fm && fm.length) { applyAddress(fm[0].GeoObject); }
                        });
                  }
               }

                function setField(field, value) {
                   if (!field || !value) return;
                   field.value = value;
                   field.dispatchEvent(new Event('input', { bubbles: true }));
                   field.dispatchEvent(new Event('change', { bubbles: true }));
                }

                function selectCity(city) {
                   if (!cityField || !city) return;
                   setField(cityField, city.trim());
                }

                function applyAddress(geoObject) {
                   var meta = geoObject && geoObject.metaDataProperty && geoObject.metaDataProperty.GeocoderMetaData;
                   if (!meta) return;
                   var address = meta.Address || {};
                   var components = address.Components || [];
                   var byKind = function(kinds) {
                      for (var i = components.length - 1; i >= 0; i--) {
                         if (kinds.indexOf(components[i].kind) !== -1) return components[i].name || '';
                      }
                      return '';
                   };
                   var country = byKind(['country']);
                   var region = byKind(['province']) || byKind(['area']);
                   var city = byKind(['locality']) || byKind(['district']);
                   var street = byKind(['street']);
                   var house = byKind(['house']);
                   var streetAndHouse = [street, house].filter(Boolean).join(', ');
                   var fullAddress = meta.text || address.formatted || '';

                   setField(countryField, country);
                   setField(regionField, region);
                   selectCity(city);
                   setField(streetField, streetAndHouse);
                   setField(postalField, address.postal_code || meta.AddressDetails && meta.AddressDetails.postal_code || '');
                   setField(fullAddressField, fullAddress);
                   setField(addressField, fullAddress);
                }

               function geocodeAndMove(query) {
                  if (!query) return;
                  fetch(geocodeUrl + '&geocode=' + encodeURIComponent(query) + '&results=1')
                     .then(function(r) { return r.json(); })
                     .then(function(d) {
                        var fm = d.response && d.response.GeoObjectCollection && d.response.GeoObjectCollection.featureMember;
                        if (!fm || !fm.length) return;
                        var pos = fm[0].GeoObject.Point.pos.split(' ');
                        var fLng = parseFloat(pos[0]), fLat = parseFloat(pos[1]);
                        if (isNaN(fLat) || isNaN(fLng)) return;
                        marker.update({ coordinates: [fLng, fLat] });
                        map.update({ location: { center: [fLng, fLat], zoom: 15 } });
                        syncFields(fLat, fLng);
                     }).catch(function() {});
               }

               function initSuggest(input) {
                  var wrap = input.parentNode;
                  var drop = document.createElement('div');
                  drop.style.cssText = 'display:none;position:absolute;z-index:99999;left:0;right:0;top:100%;background:#fff;border:1px solid #c3c4c7;border-top:none;border-radius:0 0 4px 4px;box-shadow:0 4px 8px rgba(0,0,0,.12);max-height:220px;overflow-y:auto;font-size:13px;';
                  wrap.appendChild(drop);
                  var timer, active = -1;
                  function hide() { drop.style.display = 'none'; active = -1; }
                  function hl(i) { active = i; Array.from(drop.children).forEach(function(c,j){c.style.background=j===i?'#f0f7ff':'';}); }
                  function pick(t, s) { input.value = t + (s ? ', '+s : ''); hide(); geocodeAndMove(input.value); }
                  function esc(s) { return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
                  input.addEventListener('input', function() {
                     clearTimeout(timer);
                     var q = input.value.trim();
                     if (q.length < 2) { hide(); return; }
                     timer = setTimeout(function() {
                        ymaps3.suggest({ text: q, lang: 'ru_RU', results: 5 })
                           .then(function(items) {
                              drop.innerHTML = '';
                              items = (items || []).filter(function(r) { return r.title && r.title.text; });
                              if (!items.length) { hide(); return; }
                              items.forEach(function(r, i) {
                                 var t = r.title.text, s = r.subtitle && r.subtitle.text ? r.subtitle.text : '';
                                 var div = document.createElement('div');
                                 div.style.cssText = 'padding:7px 12px;cursor:pointer;border-bottom:1px solid #f0f0f1;line-height:1.3;';
                                 div.innerHTML = '<span style="font-weight:600">'+esc(t)+'</span>'+(s?'<br><span style="color:#777;font-size:12px">'+esc(s)+'</span>':'');
                                 div.addEventListener('mousedown', function(e) { e.preventDefault(); pick(t, s); });
                                 div.addEventListener('mouseover', function() { hl(i); });
                                 drop.appendChild(div);
                              });
                              drop.style.display = 'block';
                           }).catch(function() {});
                     }, 250);
                  });
                  input.addEventListener('keydown', function(e) {
                     if (e.key === 'ArrowDown') { e.preventDefault(); hl(Math.min(active+1, drop.children.length-1)); }
                     else if (e.key === 'ArrowUp') { e.preventDefault(); hl(Math.max(active-1, 0)); }
                     else if (e.key === 'Enter') {
                        e.preventDefault();
                        if (active >= 0 && drop.children[active]) drop.children[active].dispatchEvent(new MouseEvent('mousedown',{bubbles:true}));
                        else geocodeAndMove(input.value.trim());
                        hide();
                     } else if (e.key === 'Escape') { hide(); }
                  });
                  input.addEventListener('blur', function() { setTimeout(hide, 200); });
               }

               if (searchInput) initSuggest(searchInput);
            });
         })();
         </script>
      <?php else : ?>
         <p style="color: #d63638; padding: 10px; background: #fcf0f1; border-left: 4px solid #d63638;">
            <?php echo esc_html__('Yandex Maps API key is not configured. Please set it in Redux Framework settings.', 'codeweber'); ?>
         </p>
      <?php endif; ?>
   </div>

   <style>
      .codeweber-office-map-fields {
         display: grid;
         grid-template-columns: repeat(3, minmax(0, 1fr));
         gap: 12px;
         margin-top: 15px;
      }
      .codeweber-office-map-fields__address {
         grid-column: 1 / -1;
      }
      @media screen and (max-width: 782px) {
         .codeweber-office-map-fields {
            grid-template-columns: 1fr;
         }
      }
   </style>
   <div class="codeweber-office-map-fields">
      <!-- Hidden field for coordinates in string format (for compatibility) -->
      <input type="hidden" id="office_coordinates" name="office_coordinates" value="<?php echo esc_attr($coordinates); ?>">

      <div>
         <label for="office_latitude" style="display: block; margin-bottom: 5px; font-weight: bold;">
            <?php echo esc_html__('Latitude', 'codeweber'); ?>
         </label>
         <input type="number" step="any" id="office_latitude" name="office_latitude" value="<?php echo esc_attr($latitude); ?>" style="width: 100%; padding: 8px;" placeholder="55.7558">
      </div>

      <div>
         <label for="office_longitude" style="display: block; margin-bottom: 5px; font-weight: bold;">
            <?php echo esc_html__('Longitude', 'codeweber'); ?>
         </label>
         <input type="number" step="any" id="office_longitude" name="office_longitude" value="<?php echo esc_attr($longitude); ?>" style="width: 100%; padding: 8px;" placeholder="37.6173">
      </div>

      <div>
         <label for="office_zoom" style="display: block; margin-bottom: 5px; font-weight: bold;">
            <?php echo esc_html__('Zoom Level', 'codeweber'); ?>
         </label>
         <input type="number" id="office_zoom" name="office_zoom" value="<?php echo esc_attr($zoom); ?>" min="1" max="19" style="width: 100%; padding: 8px;" placeholder="10">
      </div>

      <div class="codeweber-office-map-fields__address">
         <label for="office_yandex_address" style="display: block; margin-bottom: 5px; font-weight: bold;">
            <?php echo esc_html__('Address (from map)', 'codeweber'); ?>
         </label>
         <input type="text" id="office_yandex_address" name="office_yandex_address" value="<?php echo esc_attr($address); ?>" style="width: 100%; padding: 8px;" readonly>
         <p style="font-size: 12px; color: #666; margin-top: 5px;">
            <?php echo esc_html__('Address is automatically determined from the map', 'codeweber'); ?>
         </p>
      </div>
   </div>

<?php
}

/**
 * Callback function for related vacancies (multiple)
 */
function codeweber_office_vacancies_callback($post)
{
   $selected_vacancies = get_post_meta($post->ID, '_office_vacancies', true);
   if (!is_array($selected_vacancies)) {
      $selected_vacancies = [];
   }

   $vacancy_posts = get_posts(array(
      'post_type'      => 'vacancies',
      'post_status'    => 'publish',
      'posts_per_page' => -1,
      'orderby'        => 'title',
      'order'          => 'ASC',
   ));
?>
   <div>
      <?php if (!empty($vacancy_posts)) : ?>
         <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #fff;">
            <?php foreach ($vacancy_posts as $vacancy_post) : ?>
               <label style="display: block; margin-bottom: 8px; padding: 5px; cursor: pointer;">
                  <input type="checkbox" name="office_vacancies[]" value="<?php echo esc_attr($vacancy_post->ID); ?>" <?php checked(in_array($vacancy_post->ID, $selected_vacancies)); ?> style="margin-right: 8px;">
                  <?php echo esc_html(get_the_title($vacancy_post->ID)); ?>
               </label>
            <?php endforeach; ?>
         </div>
      <?php else : ?>
         <p style="color: #666; font-size: 12px;">
            <?php echo esc_html__('No vacancies found. Please create vacancies first.', 'codeweber'); ?>
         </p>
      <?php endif; ?>
   </div>
<?php
}

/**
 * Related events (multiple).
 */
function codeweber_office_events_callback($post)
{
   $selected_events = get_post_meta($post->ID, '_office_events', true);
   $selected_events = is_array($selected_events) ? $selected_events : [];
   $event_posts = get_posts([
      'post_type'      => 'events',
      'post_status'    => ['publish', 'future', 'draft'],
      'posts_per_page' => -1,
      'orderby'        => 'date',
      'order'          => 'DESC',
   ]);
   ?>
   <div>
      <?php if ($event_posts) : ?>
         <div style="max-height:300px;overflow-y:auto;border:1px solid #ddd;padding:10px;background:#fff;">
            <?php foreach ($event_posts as $event_post) : ?>
               <label style="display:block;margin-bottom:8px;padding:5px;cursor:pointer;">
                  <input type="checkbox" name="office_events[]" value="<?php echo esc_attr($event_post->ID); ?>" <?php checked(in_array($event_post->ID, $selected_events)); ?> style="margin-right:8px;">
                  <?php echo esc_html(get_the_title($event_post->ID)); ?>
               </label>
            <?php endforeach; ?>
         </div>
      <?php else : ?>
         <p style="color:#666;font-size:12px;"><?php echo esc_html__('No events found. Please create events first.', 'codeweber'); ?></p>
      <?php endif; ?>
   </div>
   <?php
}

/**
 * Callback function for staff members (multiple)
 */
function codeweber_office_staff_callback($post)
{
   $selected_staff = get_post_meta($post->ID, '_office_staff', true);
   if (!is_array($selected_staff)) {
      $selected_staff = [];
   }

   $staff_posts = get_posts(array(
      'post_type'      => 'staff',
      'post_status'    => 'publish',
      'posts_per_page' => -1,
      'orderby'        => 'title',
      'order'          => 'ASC',
   ));
?>
   <div>
      <?php if (!empty($staff_posts)) : ?>
         <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #fff;">
            <?php foreach ($staff_posts as $staff_post) : ?>
               <?php
               $name     = get_post_meta($staff_post->ID, '_staff_name', true);
               $surname  = get_post_meta($staff_post->ID, '_staff_surname', true);
               $position = get_post_meta($staff_post->ID, '_staff_position', true);
               $label    = trim($name . ' ' . $surname);
               if (empty($label)) {
                  $label = get_the_title($staff_post->ID);
               }
               if (!empty($position)) {
                  $label .= ' (' . $position . ')';
               }
               ?>
               <label style="display: block; margin-bottom: 8px; padding: 5px; cursor: pointer;">
                  <input type="checkbox" name="office_staff[]" value="<?php echo esc_attr($staff_post->ID); ?>" <?php checked(in_array($staff_post->ID, $selected_staff)); ?> style="margin-right: 8px;">
                  <?php echo esc_html($label); ?>
               </label>
            <?php endforeach; ?>
         </div>
      <?php else : ?>
         <p style="color: #666; font-size: 12px;">
            <?php echo esc_html__('No staff members found. Please create staff members first.', 'codeweber'); ?>
         </p>
      <?php endif; ?>
   </div>
<?php
}

/**
 * Callback function for available services
 */
function codeweber_office_services_callback($post)
{
   $selected_services = get_post_meta($post->ID, '_office_services', true);
   if (!is_array($selected_services)) {
      $selected_services = [];
   }

   // Get list of services
   $service_posts = get_posts(array(
      'post_type' => 'services',
      'post_status' => 'publish',
      'posts_per_page' => -1,
      'orderby' => 'title',
      'order' => 'ASC'
   ));
?>

   <div>
      <?php if (!empty($service_posts)) : ?>
         <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #fff;">
            <?php foreach ($service_posts as $service_post) : ?>
               <label style="display: block; margin-bottom: 8px; padding: 5px; cursor: pointer;">
                  <input type="checkbox" name="office_services[]" value="<?php echo esc_attr($service_post->ID); ?>" <?php checked(in_array($service_post->ID, $selected_services)); ?> style="margin-right: 8px;">
                  <?php echo esc_html(get_the_title($service_post->ID)); ?>
               </label>
            <?php endforeach; ?>
         </div>
      <?php else : ?>
         <p style="color: #666; font-size: 12px;">
            <?php echo esc_html__('No services found. Please create services first.', 'codeweber'); ?>
         </p>
      <?php endif; ?>
   </div>

<?php
}

/**
 * Callback function for additional information
 */
function codeweber_office_additional_callback($post)
{
   $image_id = get_post_meta($post->ID, '_office_image', true);
   $image_url = '';
   if ($image_id) {
      $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
   }
?>

   <div>
      <label style="display: block; margin-bottom: 5px; font-weight: bold;">
         <?php echo esc_html__('Office Image', 'codeweber'); ?>
      </label>
      <input type="hidden" id="office_image_id" name="office_image_id" value="<?php echo esc_attr($image_id); ?>">
      <div id="office_image_preview" style="margin-bottom: 10px;">
         <?php if ($image_url) : ?>
            <img src="<?php echo esc_url($image_url); ?>" alt="Office" style="max-width: 200px; height: auto; border: 1px solid #ddd; padding: 5px;">
         <?php endif; ?>
      </div>
      <button type="button" class="button" id="office_image_upload_btn">
         <?php echo esc_html__('Select Image', 'codeweber'); ?>
      </button>
      <button type="button" class="button" id="office_image_remove_btn" style="display: <?php echo $image_id ? 'inline-block' : 'none'; ?>;">
         <?php echo esc_html__('Remove Image', 'codeweber'); ?>
      </button>
   </div>

   <script>
      jQuery(document).ready(function($) {
         var frame;
         $('#office_image_upload_btn').click(function(e) {
            e.preventDefault();
            if (frame) {
               frame.open();
               return;
            }
            frame = wp.media({
               title: '<?php echo esc_js(__('Select Office Image', 'codeweber')); ?>',
               button: { text: '<?php echo esc_js(__('Use this image', 'codeweber')); ?>' },
               multiple: false
            });
            frame.on('select', function() {
               var attachment = frame.state().get('selection').first().toJSON();
               $('#office_image_id').val(attachment.id);
               $('#office_image_preview').html('<img src="' + attachment.url + '" style="max-width: 200px; height: auto; border: 1px solid #ddd; padding: 5px;">');
               $('#office_image_remove_btn').show();
            });
            frame.open();
         });
         $('#office_image_remove_btn').click(function() {
            $('#office_image_id').val('');
            $('#office_image_preview').html('');
            $(this).hide();
         });
      });
   </script>

<?php
}

/**
 * Save metadata fields
 */
function codeweber_save_office_meta($post_id)
{
   // Check nonce
   if (!isset($_POST['office_meta_box_nonce']) || !wp_verify_nonce($_POST['office_meta_box_nonce'], 'office_meta_box')) {
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
   if (get_post_type($post_id) !== 'offices') {
      return;
   }

   // Синхронизация города с таксономией towns
   if (isset($_POST['office_city'])) {
      $city_name = trim( sanitize_text_field( wp_unslash( $_POST['office_city'] ) ) );
      $town_id = 0;

      if ( $city_name !== '' ) {
         $existing = term_exists( $city_name, 'towns' );
         if ( $existing ) {
            $town_id = (int) ( is_array( $existing ) ? $existing['term_id'] : $existing );
         } else {
            $taxonomy = get_taxonomy( 'towns' );
            $capability = $taxonomy && ! empty( $taxonomy->cap->manage_terms )
               ? $taxonomy->cap->manage_terms
               : 'manage_categories';
            if ( current_user_can( $capability ) ) {
               $created = wp_insert_term( $city_name, 'towns' );
               if ( ! is_wp_error( $created ) ) {
                  $town_id = (int) $created['term_id'];
               } elseif ( 'term_exists' === $created->get_error_code() ) {
                  $town_id = (int) $created->get_error_data();
               }
            }
         }
      }
      
      if ($town_id) {
         // Устанавливаем термин таксономии для поста
         wp_set_object_terms($post_id, array($town_id), 'towns');
         
         // Получаем название термина и сохраняем в метаполе для совместимости
         $town_term = get_term($town_id, 'towns');
         if ($town_term && !is_wp_error($town_term)) {
            update_post_meta($post_id, '_office_city', $town_term->name);
         }
      } else {
         // Удаляем все термины towns для поста
         wp_set_object_terms($post_id, [], 'towns');
         delete_post_meta($post_id, '_office_city');
      }
   }

   // Save fields
   $fields = array(
      'office_country',
      'office_region',
      'office_street',
      'office_postal_code',
      'office_full_address',
      'office_landmark',
      'office_email',
      'office_website',
      'office_latitude',
      'office_longitude',
      'office_zoom',
      'office_yandex_address',
      'office_description',
   );

   foreach ($fields as $field) {
      if (isset($_POST[$field])) {
         if ($field === 'office_latitude' || $field === 'office_longitude') {
            update_post_meta($post_id, '_' . $field, floatval($_POST[$field]));
         } elseif ($field === 'office_zoom') {
            update_post_meta($post_id, '_' . $field, intval($_POST[$field]));
         } elseif ($field === 'office_description') {
            update_post_meta($post_id, '_' . $field, wp_kses_post($_POST[$field]));
         } elseif ($field === 'office_website') {
            update_post_meta($post_id, '_' . $field, esc_url_raw($_POST[$field]));
         } elseif ($field === 'office_email') {
            update_post_meta($post_id, '_' . $field, sanitize_email($_POST[$field]));
         } else {
            update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
         }
      } else {
         delete_post_meta($post_id, '_' . $field);
      }
   }

   // Save the phone repeater and keep the two legacy meta fields in sync.
   $phones = [];
   if (isset($_POST['office_phones']) && is_array($_POST['office_phones'])) {
      $phones = array_values(array_filter(array_map(
         static function ($value) {
            return sanitize_text_field(wp_unslash($value));
         },
         $_POST['office_phones']
      )));
   }

   if ($phones) {
      update_post_meta($post_id, '_office_phones', $phones);
   } else {
      delete_post_meta($post_id, '_office_phones');
   }

   if (isset($phones[0])) {
      update_post_meta($post_id, '_office_phone', $phones[0]);
   } else {
      delete_post_meta($post_id, '_office_phone');
   }

   if (isset($phones[1])) {
      update_post_meta($post_id, '_office_phone_2', $phones[1]);
   } else {
      delete_post_meta($post_id, '_office_phone_2');
   }

   // Expand schedule groups back to the existing per-day storage format.
   if ( isset( $_POST['office_hours_groups_submitted'] ) ) {
      $days = codeweber_opening_hours_days();
      $schedule_by_day = array_fill_keys( array_keys( $days ), [ 'closed' => 1 ] );
      $submitted_groups = isset( $_POST['office_hours_groups'] ) && is_array( $_POST['office_hours_groups'] )
         ? $_POST['office_hours_groups']
         : [];
      $sanitize_time = static function ( $value ): string {
         $value = sanitize_text_field( wp_unslash( $value ?? '' ) );
         return preg_match( '/^(?:[01]\d|2[0-3]):[0-5]\d$/', $value ) ? $value : '';
      };

      foreach ( $submitted_groups as $group ) {
         if ( ! is_array( $group ) || empty( $group['days'] ) || ! is_array( $group['days'] ) ) {
            continue;
         }
         $is_closed = ! empty( $group['closed'] );
         $schedule = $is_closed
            ? [ 'closed' => 1 ]
            : [
               'closed'   => 0,
               'opens_1'  => $sanitize_time( $group['opens_1'] ?? '' ),
               'closes_1' => $sanitize_time( $group['closes_1'] ?? '' ),
               'opens_2'  => $sanitize_time( $group['opens_2'] ?? '' ),
               'closes_2' => $sanitize_time( $group['closes_2'] ?? '' ),
            ];

         foreach ( $group['days'] as $day_key ) {
            $day_key = sanitize_key( $day_key );
            if ( array_key_exists( $day_key, $days ) ) {
               $schedule_by_day[ $day_key ] = $schedule;
            }
         }
      }

      foreach ( $schedule_by_day as $day_key => $schedule ) {
         update_post_meta( $post_id, '_office_hours_' . $day_key, wp_json_encode( $schedule ) );
      }
   }

   // Save services array
   if (isset($_POST['office_services']) && is_array($_POST['office_services'])) {
      $services = array_map('intval', $_POST['office_services']);
      update_post_meta($post_id, '_office_services', $services);
   } else {
      update_post_meta($post_id, '_office_services', []);
   }

   // Save staff array + bidirectional sync _staff_office.
   $prev_staff   = get_post_meta($post_id, '_office_staff', true);
   $prev_staff   = is_array($prev_staff) ? $prev_staff : [];
   $new_staff    = (isset($_POST['office_staff']) && is_array($_POST['office_staff']))
      ? array_values(array_filter(array_map('intval', $_POST['office_staff'])))
      : [];
   update_post_meta($post_id, '_office_staff', $new_staff);

   // Staff added to this office → set their _staff_office.
   foreach ($new_staff as $staff_id) {
      $old_office = get_post_meta($staff_id, '_staff_office', true);
      if ((int) $old_office !== $post_id) {
         // Remove from previous office's list.
         if ($old_office) {
            $other_list = get_post_meta($old_office, '_office_staff', true);
            if (is_array($other_list)) {
               $other_list = array_values(array_diff($other_list, [$staff_id]));
               update_post_meta($old_office, '_office_staff', $other_list);
            }
         }
         update_post_meta($staff_id, '_staff_office', $post_id);
      }
   }
   // Staff removed from this office → clear their _staff_office if it pointed here.
   foreach (array_diff($prev_staff, $new_staff) as $staff_id) {
      if ((int) get_post_meta($staff_id, '_staff_office', true) === $post_id) {
         delete_post_meta($staff_id, '_staff_office');
      }
   }

   // Save vacancies array + bidirectional sync _vacancy_office.
   $prev_vacancies = get_post_meta($post_id, '_office_vacancies', true);
   $prev_vacancies = is_array($prev_vacancies) ? $prev_vacancies : [];
   $new_vacancies  = (isset($_POST['office_vacancies']) && is_array($_POST['office_vacancies']))
      ? array_values(array_filter(array_map('intval', $_POST['office_vacancies'])))
      : [];
   update_post_meta($post_id, '_office_vacancies', $new_vacancies);

   // Vacancies added → set their _vacancy_office.
   foreach ($new_vacancies as $vacancy_id) {
      $old_office = get_post_meta($vacancy_id, '_vacancy_office', true);
      if ((int) $old_office !== $post_id) {
         if ($old_office) {
            $other_list = get_post_meta($old_office, '_office_vacancies', true);
            if (is_array($other_list)) {
               $other_list = array_values(array_diff($other_list, [$vacancy_id]));
               update_post_meta($old_office, '_office_vacancies', $other_list);
            }
         }
         update_post_meta($vacancy_id, '_vacancy_office', $post_id);
      }
   }
   // Vacancies removed → clear their _vacancy_office if it pointed here.
   foreach (array_diff($prev_vacancies, $new_vacancies) as $vacancy_id) {
      if ((int) get_post_meta($vacancy_id, '_vacancy_office', true) === $post_id) {
         delete_post_meta($vacancy_id, '_vacancy_office');
      }
   }

   // Save events array + bidirectional sync _event_office.
   $prev_events = get_post_meta($post_id, '_office_events', true);
   $prev_events = is_array($prev_events) ? $prev_events : [];
   $new_events = isset($_POST['office_events']) && is_array($_POST['office_events'])
      ? array_values(array_filter(array_map('intval', $_POST['office_events'])))
      : [];
   update_post_meta($post_id, '_office_events', $new_events);

   foreach ($new_events as $event_id) {
      $old_office = (int) get_post_meta($event_id, '_event_office', true);
      if ($old_office !== $post_id) {
         if ($old_office) {
            $old_list = get_post_meta($old_office, '_office_events', true);
            if (is_array($old_list)) {
               update_post_meta($old_office, '_office_events', array_values(array_diff($old_list, [$event_id])));
            }
         }
         update_post_meta($event_id, '_event_office', $post_id);
      }
   }
   foreach (array_diff($prev_events, $new_events) as $event_id) {
      if ((int) get_post_meta($event_id, '_event_office', true) === $post_id) {
         delete_post_meta($event_id, '_event_office');
      }
   }

   // Save image
   if (isset($_POST['office_image_id'])) {
      update_post_meta($post_id, '_office_image', intval($_POST['office_image_id']));
   }

   // Сохраняем координаты в формате строки для совместимости
   if (isset($_POST['office_coordinates'])) {
      update_post_meta($post_id, '_office_coordinates', sanitize_text_field($_POST['office_coordinates']));
   }
}
add_action('save_post_offices', 'codeweber_save_office_meta');

/**
 * Enqueue scripts for media uploader and map
 */
function codeweber_office_admin_scripts($hook)
{
   global $post_type;

   if ($post_type === 'offices' && in_array($hook, array('post.php', 'post-new.php'))) {
      wp_enqueue_media();
      wp_enqueue_script('jquery');
   }
}
add_action('admin_enqueue_scripts', 'codeweber_office_admin_scripts');

/**
 * Add columns to admin for CPT offices
 */
function codeweber_add_offices_admin_columns($columns)
{
   $new_columns = array(
      'cb' => $columns['cb'],
      'title' => $columns['title'],
      'office_city' => esc_html__('City', 'codeweber'),
      'office_phone' => esc_html__('Phone', 'codeweber'),
      'office_email' => esc_html__('Email', 'codeweber'),
      'office_vacancies' => esc_html__('Vacancies', 'codeweber'),
      'office_events'    => esc_html__('Events', 'codeweber'),
      'office_staff'     => esc_html__('Staff', 'codeweber'),
      'office_services'  => esc_html__('Services', 'codeweber'),
      'date' => $columns['date']
   );
   return $new_columns;
}
add_filter('manage_offices_posts_columns', 'codeweber_add_offices_admin_columns');

/**
 * Fill columns with data
 */
function codeweber_fill_offices_admin_columns($column, $post_id)
{
   switch ($column) {
      case 'office_city':
         $town_terms = wp_get_post_terms($post_id, 'towns', array('fields' => 'names'));
         if (!empty($town_terms) && !is_wp_error($town_terms)) {
            echo esc_html($town_terms[0]);
         } else {
            // Fallback на метаполе для обратной совместимости
            echo esc_html(get_post_meta($post_id, '_office_city', true));
         }
         break;
      case 'office_phone':
         echo esc_html(get_post_meta($post_id, '_office_phone', true));
         break;
      case 'office_email':
         $email = get_post_meta($post_id, '_office_email', true);
         if (!empty($email)) {
            echo '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>';
         }
         break;
      case 'office_vacancies':
         $vacancies = get_post_meta($post_id, '_office_vacancies', true);
         if (!empty($vacancies) && is_array($vacancies)) {
            $titles = array_map('get_the_title', $vacancies);
            echo esc_html(implode(', ', $titles));
         }
         break;
      case 'office_events':
         $events = get_post_meta($post_id, '_office_events', true);
         if (is_array($events) && $events) {
            echo esc_html(implode(', ', array_map('get_the_title', $events)));
         }
         break;
      case 'office_staff':
         $staff_ids = get_post_meta($post_id, '_office_staff', true);
         if (!empty($staff_ids) && is_array($staff_ids)) {
            $names = [];
            foreach ($staff_ids as $sid) {
               $n = trim(get_post_meta($sid, '_staff_name', true) . ' ' . get_post_meta($sid, '_staff_surname', true));
               $names[] = $n ?: get_the_title($sid);
            }
            echo esc_html(implode(', ', $names));
         }
         break;
      case 'office_services':
         $services = get_post_meta($post_id, '_office_services', true);
         if (!empty($services) && is_array($services)) {
            $service_titles = [];
            foreach ($services as $service_id) {
               $service_titles[] = get_the_title($service_id);
            }
            echo esc_html(implode(', ', $service_titles));
         } else {
            echo '—';
         }
         break;
   }
}
add_action('manage_offices_posts_custom_column', 'codeweber_fill_offices_admin_columns', 10, 2);

/**
 * Days of week for opening hours fields.
 *
 * @return array Associative array: key => translated label.
 */
function codeweber_opening_hours_days(): array {
	return [
		'monday'    => __( 'Monday', 'codeweber' ),
		'tuesday'   => __( 'Tuesday', 'codeweber' ),
		'wednesday' => __( 'Wednesday', 'codeweber' ),
		'thursday'  => __( 'Thursday', 'codeweber' ),
		'friday'    => __( 'Friday', 'codeweber' ),
		'saturday'  => __( 'Saturday', 'codeweber' ),
		'sunday'    => __( 'Sunday', 'codeweber' ),
	];
}

/**
 * Get structured opening hours for an office.
 *
 * @param int $post_id Office post ID.
 * @return array Associative array keyed by day, each value has opens_1, closes_1, opens_2, closes_2.
 */
function codeweber_get_office_hours( int $post_id ): array {
	$hours = [];
	$days  = codeweber_opening_hours_days();

	foreach ( array_keys( $days ) as $day_key ) {
		$raw = get_post_meta( $post_id, '_office_hours_' . $day_key, true );
		if ( ! empty( $raw ) ) {
			$decoded = json_decode( $raw, true );
			if ( is_array( $decoded ) ) {
				$hours[ $day_key ] = $decoded;
			}
		}
	}

	return $hours;
}

/**
 * Format office hours as human-readable string.
 *
 * @param int $post_id Office post ID.
 * @return string Formatted hours (e.g. "Mon-Fri: 09:00-13:00, 14:00-18:00").
 */
function codeweber_format_office_hours( int $post_id ): string {
	$hours = codeweber_get_office_hours( $post_id );
	$days  = codeweber_opening_hours_days();
	$groups = [];

	foreach ( $days as $day_key => $day_label ) {
		if ( empty( $hours[ $day_key ] ) ) {
			continue;
		}
		$h = $hours[ $day_key ];
		if ( ! empty( $h['closed'] ) ) {
			$value = str_starts_with( determine_locale(), 'ru' ) ? 'Выходной' : __( 'Day off', 'codeweber' );
		} else {
			$intervals = [];
			if ( ! empty( $h['opens_1'] ) ) {
				$closes = ! empty( $h['closes_1'] ) ? $h['closes_1'] : ( $h['closes_2'] ?? '' );
				if ( $closes ) {
					$intervals[] = $h['opens_1'] . '–' . $closes;
				}
			}
			if ( ! empty( $h['opens_2'] ) && ! empty( $h['closes_2'] ) && ! empty( $h['closes_1'] ) ) {
				$intervals[] = $h['opens_2'] . '–' . $h['closes_2'];
			}
			$value = implode( ', ', $intervals );
		}

		if ( $value === '' ) {
			continue;
		}
		$last_index = count( $groups ) - 1;
		if ( $last_index >= 0 && $groups[ $last_index ]['value'] === $value ) {
			$groups[ $last_index ]['end'] = $day_label;
		} else {
			$groups[] = [ 'start' => $day_label, 'end' => $day_label, 'value' => $value ];
		}
	}

	$lines = array_map( static function ( array $group ): string {
		global $wp_locale;
		$start = $wp_locale->get_weekday_abbrev( $group['start'] );
		$end   = $wp_locale->get_weekday_abbrev( $group['end'] );
		$label = $start === $end ? $start : $start . '–' . $end;
		return $label . ': ' . $group['value'];
	}, $groups );

	return implode( '; ', $lines );
}

/**
 * Offcanvas map panel for offices (triggered by [data-office-map]).
 * Outputs once per page via wp_footer, only when Codeweber_Yandex_Maps is active.
 */
function codeweber_offices_map_offcanvas() {
	static $rendered = false;
	if ( $rendered ) {
		return;
	}
	$rendered = true;

	if ( ! class_exists( 'Codeweber_Yandex_Maps' ) ) {
		return;
	}

	$offices = get_posts( [
		'post_type'      => 'offices',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_query'     => [
			'relation' => 'AND',
			[
				'key'     => '_office_latitude',
				'value'   => '',
				'compare' => '!=',
			],
			[
				'key'     => '_office_longitude',
				'value'   => '',
				'compare' => '!=',
			],
		],
	] );

	if ( empty( $offices ) ) {
		return;
	}

	$markers = [];
	foreach ( $offices as $pid ) {
		$lat   = get_post_meta( $pid, '_office_latitude', true );
		$lng   = get_post_meta( $pid, '_office_longitude', true );
		$addr  = get_post_meta( $pid, '_office_full_address', true ) ?: get_post_meta( $pid, '_office_street', true );
		$phone = get_post_meta( $pid, '_office_phone', true );
		$hours = get_post_meta( $pid, '_office_working_hours', true );

		$city = '';
		$town_terms = wp_get_post_terms( $pid, 'towns', [ 'fields' => 'names' ] );
		if ( ! empty( $town_terms ) && ! is_wp_error( $town_terms ) ) {
			$city = $town_terms[0];
		}

		$markers[] = [
			'id'           => $pid,
			'title'        => get_the_title( $pid ),
			'link'         => get_permalink( $pid ),
			'address'      => $addr,
			'city'         => $city,
			'phone'        => $phone,
			'workingHours' => $hours,
			'latitude'     => floatval( $lat ),
			'longitude'    => floatval( $lng ),
		];
	}

	$yandex_maps = Codeweber_Yandex_Maps::get_instance();

	ob_start();
	echo $yandex_maps->render_map(
		[
			'api_version'      => 3,
			'map_id'           => 'offices-all-map',
			'zoom'             => 10,
			'height'           => 600,
			'border_radius'    => 0,
			'auto_fit_bounds'  => true,
			'enable_drag'      => true,
			'enable_scroll_zoom' => true,
			'show_sidebar'     => true,
			'sidebar_position' => 'left',
			'sidebar_title'    => __( 'Offices', 'codeweber' ),
			'sidebar_fields'   => [
				'showCity'         => true,
				'showAddress'      => true,
				'showPhone'        => true,
				'showWorkingHours' => false,
				'showDescription'  => false,
			],
			'show_filters'     => true,
			'filter_by_city'   => true,
			'balloon_fields'   => [
				'showCity'         => true,
				'showAddress'      => true,
				'showPhone'        => true,
				'showWorkingHours' => true,
				'showLink'         => true,
				'showDescription'  => false,
			],
			'color_scheme'       => 'light',
			'color_scheme_custom' => '',
		],
		$markers
	);
	$map_html = ob_get_clean();
	?>
	<style>
	#offices-map-offcanvas {
		--bs-offcanvas-width: 85vw;
	}
	#offices-map-offcanvas .offcanvas-body {
		padding: 0;
		overflow: hidden;
	}
	#offices-map-offcanvas .codeweber-yandex-map-wrapper {
		height: 100%;
	}
	#offices-map-offcanvas #offices-all-map {
		height: 100% !important;
	}
	</style>
	<script>
	document.addEventListener('click', function(e) {
		var trigger = e.target.closest('[data-office-map]');
		if (!trigger) return;
		e.preventDefault();
		var officeId = trigger.dataset.officeId || '';
		var el = document.getElementById('offices-map-offcanvas');
		if (el && window.bootstrap) {
			if (officeId) el.dataset.currentOffice = officeId;
			bootstrap.Offcanvas.getOrCreateInstance(el).show();
		}
	});
	document.addEventListener('shown.bs.offcanvas', function(e) {
		if (e.target.id !== 'offices-map-offcanvas') return;
		var wrapper = e.target.querySelector('.codeweber-yandex-map-wrapper');
		if (!wrapper) return;
		var inst = wrapper._cwgbYandexMapInstance;
		if (!inst) return;
		if (typeof inst.invalidateSize === 'function') inst.invalidateSize();
		setTimeout(function() {
			var currentId = e.target.dataset.currentOffice;
			if (currentId && inst.markerEls && inst.markerEls[currentId]) {
				var entry = inst.markerEls[currentId];
				inst.onMarkerClick(entry.data, entry.el);
				if (typeof inst.highlightSidebarItem === 'function') inst.highlightSidebarItem(currentId);
			} else if (typeof inst.fitBounds === 'function') {
				inst.fitBounds();
			}
		}, 300);
	});
	</script>

	<div class="offcanvas offcanvas-end" id="offices-map-offcanvas" tabindex="-1" aria-labelledby="offices-map-offcanvas-label">
		<div class="offcanvas-body p-0">
			<?php echo $map_html; ?>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'codeweber_offices_map_offcanvas' );

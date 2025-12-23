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
      'office_basic_info',
      esc_html__('Office Information', 'codeweber'),
      'codeweber_office_basic_info_callback',
      'offices',
      'normal',
      'high'
   );

   add_meta_box(
      'office_contact',
      esc_html__('Contact Details', 'codeweber'),
      'codeweber_office_contact_callback',
      'offices',
      'normal',
      'default'
   );

   add_meta_box(
      'office_location',
      esc_html__('Location & Map', 'codeweber'),
      'codeweber_office_location_callback',
      'offices',
      'side',
      'default'
   );

   add_meta_box(
      'office_vacancy',
      esc_html__('Related Vacancy', 'codeweber'),
      'codeweber_office_vacancy_callback',
      'offices',
      'side',
      'default'
   );

   add_meta_box(
      'office_services',
      esc_html__('Available Services', 'codeweber'),
      'codeweber_office_services_callback',
      'offices',
      'side',
      'default'
   );

   add_meta_box(
      'office_additional',
      esc_html__('Additional Information', 'codeweber'),
      'codeweber_office_additional_callback',
      'offices',
      'side',
      'default'
   );
}
add_action('add_meta_boxes', 'codeweber_add_offices_meta_boxes');

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
   $working_hours = get_post_meta($post->ID, '_office_working_hours', true);
   $manager_id = get_post_meta($post->ID, '_office_manager', true);

   // Получаем выбранный термин таксономии towns
   $town_terms = wp_get_post_terms($post->ID, 'towns', array('fields' => 'ids'));
   $selected_town_id = !empty($town_terms) && !is_wp_error($town_terms) ? $town_terms[0] : '';

   // Получаем список терминов таксономии towns
   $towns = get_terms(array(
      'taxonomy' => 'towns',
      'hide_empty' => false,
      'orderby' => 'name',
      'order' => 'ASC'
   ));

   // Получаем список сотрудников
   $staff_posts = get_posts(array(
      'post_type' => 'staff',
      'post_status' => 'publish',
      'posts_per_page' => -1,
      'orderby' => 'title',
      'order' => 'ASC'
   ));
?>

   <div style="display: grid; grid-template-columns: 150px 1fr; gap: 12px; align-items: center;">
      <label for="office_country"><strong><?php echo esc_html__('Country', 'codeweber'); ?>:</strong></label>
      <input type="text" id="office_country" name="office_country" value="<?php echo esc_attr($country); ?>" style="width: 100%; padding: 8px;">

      <label for="office_region"><strong><?php echo esc_html__('Region', 'codeweber'); ?>:</strong></label>
      <input type="text" id="office_region" name="office_region" value="<?php echo esc_attr($region); ?>" style="width: 100%; padding: 8px;">

      <label for="office_city"><strong><?php echo esc_html__('City', 'codeweber'); ?>:</strong></label>
      <select id="office_city" name="office_city" style="width: 100%; padding: 8px;">
         <option value=""><?php echo esc_html__('— Select City —', 'codeweber'); ?></option>
         <?php if (!empty($towns) && !is_wp_error($towns)) : ?>
            <?php foreach ($towns as $town) : ?>
               <option value="<?php echo esc_attr($town->term_id); ?>" <?php selected($selected_town_id, $town->term_id); ?>>
                  <?php echo esc_html($town->name); ?>
               </option>
            <?php endforeach; ?>
         <?php endif; ?>
      </select>

      <label for="office_street"><strong><?php echo esc_html__('Street, House, Office', 'codeweber'); ?>:</strong></label>
      <input type="text" id="office_street" name="office_street" value="<?php echo esc_attr($street); ?>" style="width: 100%; padding: 8px;">

      <label for="office_postal_code"><strong><?php echo esc_html__('Postal Code', 'codeweber'); ?>:</strong></label>
      <input type="text" id="office_postal_code" name="office_postal_code" value="<?php echo esc_attr($postal_code); ?>" style="width: 100%; padding: 8px;">

      <label for="office_full_address"><strong><?php echo esc_html__('Full Address', 'codeweber'); ?>:</strong></label>
      <textarea id="office_full_address" name="office_full_address" rows="3" style="width: 100%; padding: 8px;"><?php echo esc_textarea($full_address); ?></textarea>

      <label for="office_working_hours"><strong><?php echo esc_html__('Working Hours', 'codeweber'); ?>:</strong></label>
      <textarea id="office_working_hours" name="office_working_hours" rows="3" style="width: 100%; padding: 8px;" placeholder="<?php echo esc_attr__('Mon-Fri: 9:00-18:00', 'codeweber'); ?>"><?php echo esc_textarea($working_hours); ?></textarea>

      <label for="office_manager"><strong><?php echo esc_html__('Manager', 'codeweber'); ?>:</strong></label>
      <select id="office_manager" name="office_manager" style="width: 100%; padding: 8px;">
         <option value=""><?php echo esc_html__('— Select Manager —', 'codeweber'); ?></option>
         <?php if (!empty($staff_posts)) : ?>
            <?php foreach ($staff_posts as $staff_post) : ?>
               <?php
               $name = get_post_meta($staff_post->ID, '_staff_name', true);
               $surname = get_post_meta($staff_post->ID, '_staff_surname', true);
               $position = get_post_meta($staff_post->ID, '_staff_position', true);

               $display_name = '';
               if (!empty($name) || !empty($surname)) {
                  $full_name = trim($name . ' ' . $surname);
                  if (!empty($position)) {
                     $display_name = $full_name . ' (' . $position . ')';
                  } else {
                     $display_name = $full_name;
                  }
               } else {
                  $display_name = get_the_title($staff_post->ID);
               }
               ?>
               <option value="<?php echo esc_attr($staff_post->ID); ?>" <?php selected($manager_id, $staff_post->ID); ?>>
                  <?php echo esc_html($display_name); ?>
               </option>
            <?php endforeach; ?>
         <?php endif; ?>
      </select>
      <?php if (empty($staff_posts)) : ?>
         <p style="margin-top: 5px; color: #666; font-size: 12px;">
            <?php echo esc_html__('No staff members found. Please create staff members first.', 'codeweber'); ?>
         </p>
      <?php endif; ?>
   </div>

<?php
}

/**
 * Callback function for contact details
 */
function codeweber_office_contact_callback($post)
{
   $phone = get_post_meta($post->ID, '_office_phone', true);
   $phone_2 = get_post_meta($post->ID, '_office_phone_2', true);
   $email = get_post_meta($post->ID, '_office_email', true);
   $fax = get_post_meta($post->ID, '_office_fax', true);
   $website = get_post_meta($post->ID, '_office_website', true);
?>

   <div style="display: grid; grid-template-columns: 150px 1fr; gap: 12px; align-items: center;">
      <label for="office_phone"><strong><?php echo esc_html__('Phone', 'codeweber'); ?>:</strong></label>
      <input type="tel" id="office_phone" name="office_phone" value="<?php echo esc_attr($phone); ?>" style="width: 100%; padding: 8px;">

      <label for="office_phone_2"><strong><?php echo esc_html__('Phone 2', 'codeweber'); ?>:</strong></label>
      <input type="tel" id="office_phone_2" name="office_phone_2" value="<?php echo esc_attr($phone_2); ?>" style="width: 100%; padding: 8px;">

      <label for="office_email"><strong><?php echo esc_html__('Email', 'codeweber'); ?>:</strong></label>
      <input type="email" id="office_email" name="office_email" value="<?php echo esc_attr($email); ?>" style="width: 100%; padding: 8px;">

      <label for="office_fax"><strong><?php echo esc_html__('Fax', 'codeweber'); ?>:</strong></label>
      <input type="tel" id="office_fax" name="office_fax" value="<?php echo esc_attr($fax); ?>" style="width: 100%; padding: 8px;">

      <label for="office_website"><strong><?php echo esc_html__('Website', 'codeweber'); ?>:</strong></label>
      <input type="url" id="office_website" name="office_website" value="<?php echo esc_attr($website); ?>" placeholder="https://..." style="width: 100%; padding: 8px;">
   </div>

<?php
}

/**
 * Callback function for location and map coordinates
 */
function codeweber_office_location_callback($post)
{
   // Получаем API ключ из Redux
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

   // Формируем координаты в формате строки для карты
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
      <div id="office-yandex-map" style="width: 100%; height: 400px; margin-bottom: 15px;"></div>

      <?php if (!empty($yandex_api_key)) : ?>
         <script src="https://api-maps.yandex.ru/2.1/?apikey=<?php echo esc_attr($yandex_api_key); ?>&lang=ru_RU"></script>
         <script>
            document.addEventListener("DOMContentLoaded", function() {
               ymaps.ready(function() {
                  var coordinatesField = document.querySelector("input[name='office_coordinates']");
                  var latitudeField = document.querySelector("input[name='office_latitude']");
                  var longitudeField = document.querySelector("input[name='office_longitude']");
                  var zoomField = document.querySelector("input[name='office_zoom']");
                  var addressField = document.querySelector("input[name='office_yandex_address']");

                  // Получаем координаты из поля или используем значения по умолчанию
                  var coords = [];
                  if (coordinatesField && coordinatesField.value) {
                     coords = coordinatesField.value.split(',').map(function(coord) {
                        return parseFloat(coord.trim());
                     });
                  } else if (latitudeField && longitudeField && latitudeField.value && longitudeField.value) {
                     coords = [parseFloat(latitudeField.value), parseFloat(longitudeField.value)];
                  }

                  // Проверяем валидность координат
                  if (!coords || coords.length !== 2 || coords.some(isNaN)) {
                     coords = [55.76, 37.64]; // Москва по умолчанию
                  }

                  var zoom = parseInt(zoomField?.value || "<?php echo esc_js($zoom); ?>") || 10;

                  // Создаем карту
                  var map = new ymaps.Map("office-yandex-map", {
                     center: coords,
                     zoom: zoom,
                     controls: ["zoomControl", "searchControl"]
                  });

                  // Создаем перетаскиваемый маркер
                  var placemark = new ymaps.Placemark(coords, {}, { draggable: true });
                  map.geoObjects.add(placemark);

                  // Функция обновления полей
                  function updateFields(coords, addressText = null) {
                     var coordString = coords[0] + ", " + coords[1];

                     if (coordinatesField) {
                        coordinatesField.value = coordString;
                        coordinatesField.dispatchEvent(new Event("input", { bubbles: true }));
                     }

                     if (latitudeField) {
                        latitudeField.value = coords[0];
                        latitudeField.dispatchEvent(new Event("input", { bubbles: true }));
                     }

                     if (longitudeField) {
                        longitudeField.value = coords[1];
                        longitudeField.dispatchEvent(new Event("input", { bubbles: true }));
                     }

                     if (zoomField) {
                        zoomField.value = map.getZoom();
                        zoomField.dispatchEvent(new Event("input", { bubbles: true }));
                     }

                     if (addressField) {
                        if (addressText) {
                           addressField.value = addressText;
                           addressField.dispatchEvent(new Event("input", { bubbles: true }));
                        } else {
                           // Автоматическое определение адреса по координатам
                           ymaps.geocode(coords).then(function (res) {
                              var first = res.geoObjects.get(0);
                              if (first) {
                                 addressField.value = first.getAddressLine();
                                 addressField.dispatchEvent(new Event("input", { bubbles: true }));
                              }
                           });
                        }
                     }
                  }

                  // Обработчик перетаскивания маркера
                  placemark.events.add("dragend", function () {
                     var newCoords = placemark.geometry.getCoordinates();
                     updateFields(newCoords);
                  });

                  // Обработчик клика по карте
                  map.events.add("click", function (e) {
                     var coords = e.get("coords");
                     placemark.geometry.setCoordinates(coords);
                     updateFields(coords);
                  });

                  // Обновление zoom при изменении масштаба
                  map.events.add("boundschange", function () {
                     if (zoomField) {
                        zoomField.value = map.getZoom();
                        zoomField.dispatchEvent(new Event("input", { bubbles: true }));
                     }
                  });

                  // Обработчик поиска по адресу
                  var searchControl = map.controls.get("searchControl");
                  searchControl.events.add("resultselect", function (e) {
                     var results = searchControl.getResultsArray();
                     var selectedIndex = e.get("index");
                     var selectedResult = results[selectedIndex];

                     if (selectedResult) {
                        var coords = selectedResult.geometry.getCoordinates();
                        placemark.geometry.setCoordinates(coords);
                        map.setCenter(coords, 16);
                        updateFields(coords, selectedResult.properties.get("name"));
                     }
                  });

                  // Инициализация полей при загрузке
                  updateFields(coords);
               });
            });
         </script>
      <?php else : ?>
         <p style="color: #d63638; padding: 10px; background: #fcf0f1; border-left: 4px solid #d63638;">
            <?php echo esc_html__('Yandex Maps API key is not configured. Please set it in Redux Framework settings.', 'codeweber'); ?>
         </p>
      <?php endif; ?>
   </div>

   <div style="display: grid; grid-template-columns: 1fr; gap: 12px; margin-top: 15px;">
      <!-- Скрытое поле для координат в формате строки (для совместимости) -->
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

      <div>
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
 * Callback function for related vacancy
 */
function codeweber_office_vacancy_callback($post)
{
   $vacancy_id = get_post_meta($post->ID, '_office_vacancy', true);

   // Получаем список вакансий
   $vacancy_posts = get_posts(array(
      'post_type' => 'vacancies',
      'post_status' => 'publish',
      'posts_per_page' => -1,
      'orderby' => 'title',
      'order' => 'ASC'
   ));
?>

   <div>
      <label for="office_vacancy" style="display: block; margin-bottom: 5px; font-weight: bold;">
         <?php echo esc_html__('Related Vacancy', 'codeweber'); ?>
      </label>
      <select id="office_vacancy" name="office_vacancy" style="width: 100%; padding: 8px;">
         <option value=""><?php echo esc_html__('— Select Vacancy —', 'codeweber'); ?></option>
         <?php if (!empty($vacancy_posts)) : ?>
            <?php foreach ($vacancy_posts as $vacancy_post) : ?>
               <option value="<?php echo esc_attr($vacancy_post->ID); ?>" <?php selected($vacancy_id, $vacancy_post->ID); ?>>
                  <?php echo esc_html(get_the_title($vacancy_post->ID)); ?>
               </option>
            <?php endforeach; ?>
         <?php endif; ?>
      </select>
      <?php if (empty($vacancy_posts)) : ?>
         <p style="margin-top: 5px; color: #666; font-size: 12px;">
            <?php echo esc_html__('No vacancies found. Please create vacancies first.', 'codeweber'); ?>
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
      $selected_services = array();
   }

   // Получаем список услуг
   $service_posts = get_posts(array(
      'post_type' => 'services',
      'post_status' => 'publish',
      'posts_per_page' => -1,
      'orderby' => 'title',
      'order' => 'ASC'
   ));
?>

   <div>
      <label style="display: block; margin-bottom: 10px; font-weight: bold;">
         <?php echo esc_html__('Available Services', 'codeweber'); ?>
      </label>
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
   $description = get_post_meta($post->ID, '_office_description', true);
   $image_id = get_post_meta($post->ID, '_office_image', true);
   $image_url = '';
   if ($image_id) {
      $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
   }
?>

   <div style="margin-bottom: 20px;">
      <label for="office_description" style="display: block; margin-bottom: 5px; font-weight: bold;">
         <?php echo esc_html__('Description', 'codeweber'); ?>
      </label>
      <?php
      wp_editor($description, 'office_description', array(
         'textarea_name' => 'office_description',
         'textarea_rows' => 5,
         'media_buttons' => false,
         'teeny' => true
      ));
      ?>
   </div>

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
      $town_id = !empty($_POST['office_city']) ? intval($_POST['office_city']) : '';
      
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
         wp_set_object_terms($post_id, array(), 'towns');
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
      'office_working_hours',
      'office_manager',
      'office_phone',
      'office_phone_2',
      'office_email',
      'office_fax',
      'office_website',
      'office_latitude',
      'office_longitude',
      'office_zoom',
      'office_yandex_address',
      'office_description',
      'office_vacancy'
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
         } elseif ($field === 'office_manager' || $field === 'office_vacancy') {
            $value = !empty($_POST[$field]) ? intval($_POST[$field]) : '';
            if ($value) {
               update_post_meta($post_id, '_' . $field, $value);
            } else {
               delete_post_meta($post_id, '_' . $field);
            }
         } else {
            update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
         }
      } else {
         // Для полей manager и vacancy удаляем, если не установлены
         if ($field === 'office_manager' || $field === 'office_vacancy') {
            delete_post_meta($post_id, '_' . $field);
         } else {
            delete_post_meta($post_id, '_' . $field);
         }
      }
   }

   // Save services array
   if (isset($_POST['office_services']) && is_array($_POST['office_services'])) {
      $services = array_map('intval', $_POST['office_services']);
      update_post_meta($post_id, '_office_services', $services);
   } else {
      update_post_meta($post_id, '_office_services', array());
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
      'office_manager' => esc_html__('Manager', 'codeweber'),
      'office_vacancy' => esc_html__('Vacancy', 'codeweber'),
      'office_services' => esc_html__('Services', 'codeweber'),
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
      case 'office_manager':
         $manager_id = get_post_meta($post_id, '_office_manager', true);
         if (!empty($manager_id)) {
            $name = get_post_meta($manager_id, '_staff_name', true);
            $surname = get_post_meta($manager_id, '_staff_surname', true);
            $position = get_post_meta($manager_id, '_staff_position', true);

            $display_name = '';
            if (!empty($name) || !empty($surname)) {
               $full_name = trim($name . ' ' . $surname);
               if (!empty($position)) {
                  $display_name = $full_name . ' (' . $position . ')';
               } else {
                  $display_name = $full_name;
               }
            } else {
               $display_name = get_the_title($manager_id);
            }
            echo esc_html($display_name);
         }
         break;
      case 'office_vacancy':
         $vacancy_id = get_post_meta($post_id, '_office_vacancy', true);
         if (!empty($vacancy_id)) {
            echo esc_html(get_the_title($vacancy_id));
         }
         break;
      case 'office_services':
         $services = get_post_meta($post_id, '_office_services', true);
         if (!empty($services) && is_array($services)) {
            $service_titles = array();
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
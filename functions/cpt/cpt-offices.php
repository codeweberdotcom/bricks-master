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
      'office_vacancies',
      esc_html__('Related Vacancies', 'codeweber'),
      'codeweber_office_vacancies_callback',
      'offices',
      'side',
      'default'
   );

   add_meta_box(
      'office_staff',
      esc_html__('Staff Members', 'codeweber'),
      'codeweber_office_staff_callback',
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
   $office_hours = codeweber_get_office_hours( $post->ID );
   $manager_id = get_post_meta($post->ID, '_office_manager', true);

   // Get selected term from towns taxonomy
   $town_terms = wp_get_post_terms($post->ID, 'towns', array('fields' => 'ids'));
   $selected_town_id = !empty($town_terms) && !is_wp_error($town_terms) ? $town_terms[0] : '';

   // Get list of towns taxonomy terms
   $towns = get_terms(array(
      'taxonomy' => 'towns',
      'hide_empty' => false,
      'orderby' => 'name',
      'order' => 'ASC'
   ));

   // Get list of staff members
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

      <div style="grid-column: 1 / -1;">
         <strong><?php echo esc_html__('Working Hours', 'codeweber'); ?>:</strong>
         <table class="widefat" style="margin-top: 8px;">
            <thead>
               <tr>
                  <th><?php esc_html_e('Day', 'codeweber'); ?></th>
                  <th><?php esc_html_e('Opens', 'codeweber'); ?></th>
                  <th><?php esc_html_e('Break start', 'codeweber'); ?></th>
                  <th><?php esc_html_e('Break end', 'codeweber'); ?></th>
                  <th><?php esc_html_e('Closes', 'codeweber'); ?></th>
               </tr>
            </thead>
            <tbody>
               <?php
               $days = codeweber_opening_hours_days();
               foreach ( $days as $day_key => $day_label ) :
                  $h = isset( $office_hours[ $day_key ] ) ? $office_hours[ $day_key ] : [];
               ?>
               <tr>
                  <td><strong><?php echo esc_html( $day_label ); ?></strong></td>
                  <td><input type="text" name="office_hours[<?php echo esc_attr( $day_key ); ?>][opens_1]" value="<?php echo esc_attr( $h['opens_1'] ?? '' ); ?>" placeholder="09:00" style="width:70px;"></td>
                  <td><input type="text" name="office_hours[<?php echo esc_attr( $day_key ); ?>][closes_1]" value="<?php echo esc_attr( $h['closes_1'] ?? '' ); ?>" placeholder="13:00" style="width:70px;"></td>
                  <td><input type="text" name="office_hours[<?php echo esc_attr( $day_key ); ?>][opens_2]" value="<?php echo esc_attr( $h['opens_2'] ?? '' ); ?>" placeholder="14:00" style="width:70px;"></td>
                  <td><input type="text" name="office_hours[<?php echo esc_attr( $day_key ); ?>][closes_2]" value="<?php echo esc_attr( $h['closes_2'] ?? '' ); ?>" placeholder="18:00" style="width:70px;"></td>
               </tr>
               <?php endforeach; ?>
            </tbody>
         </table>
      </div>

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
                           if (fm && fm.length) { addressField.value = fm[0].GeoObject.metaDataProperty.GeocoderMetaData.text; addressField.dispatchEvent(new Event('input',{bubbles:true})); }
                        });
                  }
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
                        fetch('https://suggest-maps.yandex.ru/v1/suggest?apikey=' + encodeURIComponent(apiKey) + '&text=' + encodeURIComponent(q) + '&lang=ru_RU&results=5&types=house,street,locality')
                           .then(function(r) { return r.json(); })
                           .then(function(d) {
                              drop.innerHTML = '';
                              var items = (d.results || []).filter(function(r) { return r.title && r.title.text; });
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

   <div style="display: grid; grid-template-columns: 1fr; gap: 12px; margin-top: 15px;">
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
         } elseif ($field === 'office_manager') {
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
         delete_post_meta($post_id, '_' . $field);
      }
   }

   // Save opening hours per day.
   if ( isset( $_POST['office_hours'] ) && is_array( $_POST['office_hours'] ) ) {
      $days = codeweber_opening_hours_days();
      foreach ( array_keys( $days ) as $day_key ) {
         $day_data = isset( $_POST['office_hours'][ $day_key ] ) ? $_POST['office_hours'][ $day_key ] : [];
         $clean = [
            'opens_1'  => sanitize_text_field( $day_data['opens_1'] ?? '' ),
            'closes_1' => sanitize_text_field( $day_data['closes_1'] ?? '' ),
            'opens_2'  => sanitize_text_field( $day_data['opens_2'] ?? '' ),
            'closes_2' => sanitize_text_field( $day_data['closes_2'] ?? '' ),
         ];
         // Only save if at least opens_1 is set.
         if ( ! empty( $clean['opens_1'] ) ) {
            update_post_meta( $post_id, '_office_hours_' . $day_key, wp_json_encode( $clean ) );
         } else {
            delete_post_meta( $post_id, '_office_hours_' . $day_key );
         }
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
      'office_manager'   => esc_html__('Manager', 'codeweber'),
      'office_vacancies' => esc_html__('Vacancies', 'codeweber'),
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
      case 'office_vacancies':
         $vacancies = get_post_meta($post_id, '_office_vacancies', true);
         if (!empty($vacancies) && is_array($vacancies)) {
            $titles = array_map('get_the_title', $vacancies);
            echo esc_html(implode(', ', $titles));
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
	$lines = [];

	foreach ( $days as $day_key => $day_label ) {
		if ( empty( $hours[ $day_key ] ) ) {
			continue;
		}

		$h    = $hours[ $day_key ];
		$time = '';

		if ( ! empty( $h['opens_1'] ) && ! empty( $h['closes_1'] ) ) {
			$time = $h['opens_1'] . '-' . $h['closes_1'];
		}

		if ( ! empty( $h['opens_2'] ) && ! empty( $h['closes_2'] ) ) {
			$time .= ', ' . $h['opens_2'] . '-' . $h['closes_2'];
		} elseif ( ! empty( $h['opens_1'] ) && ! empty( $h['closes_2'] ) && empty( $h['closes_1'] ) ) {
			$time = $h['opens_1'] . '-' . $h['closes_2'];
		}

		if ( ! empty( $time ) ) {
			$short = mb_substr( $day_label, 0, 2 );
			$lines[] = $short . ': ' . $time;
		}
	}

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
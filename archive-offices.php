<?php
/**
 * Template for Offices Archive Page
 * 
 * @package Codeweber
 */

get_header(); 
get_pageheader();
?>

<?php if (have_posts()) : ?>
<section id="content-wrapper" class="wrapper bg-light">
  <div class="container">
      <?php 
      // Получаем выбранный шаблон из настроек Redux
      $post_type = 'offices';
      global $opt_name;
      $templateloop = Redux::get_option($opt_name, 'archive_template_select_' . $post_type);
      // Если шаблон не выбран или равен 'default', используем по умолчанию offices_1
      if (empty($templateloop) || $templateloop === 'default') {
          $templateloop = 'offices_1';
      }
      $template_file = "templates/archives/offices/{$templateloop}.php";
      
      // Получаем позицию сайдбара
      $sidebar_position = Redux::get_option($opt_name, 'sidebar_position_archive_' . $post_type);
      $content_class = ($sidebar_position === 'none') ? 'col-12 py-14' : 'col-xl-9 pt-14';
      ?>
      
      <div class="row">
          <?php get_sidebar('left'); ?>
          
          <div class="<?php echo esc_attr($content_class); ?>">
      
      <?php 
      // Показываем карту для шаблонов offices_2 и offices_3
      if ($templateloop === 'offices_2' || $templateloop === 'offices_3') :
          // Получаем все офисы с координатами
          $offices_query = new WP_Query(array(
              'post_type'      => 'offices',
              'posts_per_page' => -1,
              'post_status'    => 'publish',
          ));

          $markers = array();
          if ($offices_query->have_posts()) {
              while ($offices_query->have_posts()) {
                  $offices_query->the_post();
                  $office_id     = get_the_ID();
                  $latitude      = get_post_meta($office_id, '_office_latitude', true);
                  $longitude     = get_post_meta($office_id, '_office_longitude', true);
                  $title         = get_the_title($office_id);
                  $link          = get_permalink($office_id);
                  $address       = get_post_meta($office_id, '_office_full_address', true);
                  if (empty($address)) {
                      $address = get_post_meta($office_id, '_office_street', true);
                  }
                  $phone         = get_post_meta($office_id, '_office_phone', true);
                  $working_hours = get_post_meta($office_id, '_office_working_hours', true);
                  $description   = get_post_meta($office_id, '_office_description', true);

                  // Город из таксономии towns или метаполя
                  $city = '';
                  $town_terms = wp_get_post_terms($office_id, 'towns', array('fields' => 'names'));
                  if (!empty($town_terms) && !is_wp_error($town_terms)) {
                      $city = $town_terms[0];
                  } else {
                      $city = get_post_meta($office_id, '_office_city', true);
                  }

                  $lat = $latitude !== '' ? floatval($latitude) : 0;
                  $lon = $longitude !== '' ? floatval($longitude) : 0;

                  if ($lat !== 0.0 && $lon !== 0.0 && $lat >= -90 && $lat <= 90 && $lon >= -180 && $lon <= 180) {
                      $markers[] = array(
                          'id'            => $office_id,
                          'title'         => $title,
                          'link'          => $link,
                          'address'       => $address ?: '',
                          'phone'         => $phone ?: '',
                          'workingHours'  => $working_hours ?: '',
                          'description'   => $description ?: '',
                          'city'          => $city ?: '',
                          'latitude'      => $lat,
                          'longitude'     => $lon,
                      );
                  }
              }
              wp_reset_postdata();
          }

          if (!empty($markers) && class_exists('Codeweber_Yandex_Maps')) {
              $yandex_maps = Codeweber_Yandex_Maps::get_instance();
              if ($yandex_maps && $yandex_maps->has_api_key()) {
                  // Явно пробрасываем ключевые настройки сайдбара и фильтров из Redux
                  global $opt_name;
                  if (empty($opt_name)) {
                      $opt_name = 'redux_demo';
                  }
                  $sidebar_enabled    = class_exists('Redux') ? Redux::get_option($opt_name, 'yandex_maps_sidebar_enabled') : null;
                  $filters_enabled    = class_exists('Redux') ? Redux::get_option($opt_name, 'yandex_maps_filters_enabled') : null;
                  $filter_by_city_on  = class_exists('Redux') ? Redux::get_option($opt_name, 'yandex_maps_filter_by_city') : null;
                  $sidebar_position   = class_exists('Redux') ? Redux::get_option($opt_name, 'yandex_maps_sidebar_position') : 'left';
                  $sidebar_title_opt  = class_exists('Redux') ? Redux::get_option($opt_name, 'yandex_maps_sidebar_title') : '';
                  $search_control_opt = class_exists('Redux') ? Redux::get_option($opt_name, 'yandex_maps_search_control') : true;

                  // Нормализуем позицию, чтобы избежать мусорных значений
                  $sidebar_position = ($sidebar_position === 'right') ? 'right' : 'left';

                  echo '<div class="mb-10">';
                  echo $yandex_maps->render_map(
                      array(
                          'show_sidebar'     => !empty($sidebar_enabled),
                          'show_filters'     => !empty($filters_enabled),
                          'filter_by_city'   => !empty($filter_by_city_on),
                          'sidebar_position' => $sidebar_position,
                          'sidebar_title'    => !empty($sidebar_title_opt) ? $sidebar_title_opt : '',
                          'search_control'   => (bool) $search_control_opt,
                      ),
                      $markers
                  );
                  echo '</div>';
              } else {
                  echo '<div class="alert alert-info"><p>' . esc_html__('Yandex Maps API key is not configured. Please set it in Redux Framework settings.', 'codeweber') . '</p></div>';
              }
          } else {
              echo '<div class="alert alert-info"><p>' . esc_html__('No offices with coordinates found.', 'codeweber') . '</p></div>';
          }
      endif;
      ?>
      
      <div class="row g-3 mb-5">
          <?php 
          while (have_posts()) : 
            the_post();
            
            // Используем шаблоны из папки templates/archives/offices
            if (!empty($templateloop) && locate_template($template_file)) {
                get_template_part("templates/archives/offices/{$templateloop}");
            } else {
                // Fallback: используем шаблон по умолчанию (offices_1)
                if (locate_template("templates/archives/offices/offices_1.php")) {
                    get_template_part("templates/archives/offices/offices_1");
                }
            }
          endwhile; ?>
      </div>
      <!-- /.row -->
      
      <?php 
      // Pagination
      codeweber_posts_pagination();
      ?>
          </div>
          <!-- /column -->
          
          <?php get_sidebar('right'); ?>
      </div>
      <!-- /.row -->
  </div>
  <!-- /.container -->
</section>
<!-- /section -->
<?php else : ?>
<section class="wrapper bg-light">
  <div class="container py-14">
      <div class="row">
          <div class="col-12 text-center">
              <p><?php _e('No offices found.', 'codeweber'); ?></p>
          </div>
      </div>
  </div>
</section>
<?php endif; ?>

<?php get_footer(); ?>

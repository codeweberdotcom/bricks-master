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
          // Получаем API ключ Яндекс карт из Redux
          $yandex_api_key = '';
          $site_logo_url = '';
          if (class_exists('Redux')) {
              $yandex_api_key = Redux::get_option($opt_name, 'yandexapi');
              // Получаем логотип сайта
              $options = get_option($opt_name);
              if (!empty($options['opt-dark-logo']['url'])) {
                  $site_logo_url = $options['opt-dark-logo']['url'];
              } else {
                  $site_logo_url = get_template_directory_uri() . '/dist/assets/img/logo-dark.png';
              }
          }
          
          // Получаем все офисы с координатами
          $offices_query = new WP_Query(array(
              'post_type' => 'offices',
              'posts_per_page' => -1,
              'post_status' => 'publish'
          ));
          
          $offices_data = array();
          if ($offices_query->have_posts()) {
              while ($offices_query->have_posts()) {
                  $offices_query->the_post();
                  $office_id = get_the_ID();
                  $latitude = get_post_meta($office_id, '_office_latitude', true);
                  $longitude = get_post_meta($office_id, '_office_longitude', true);
                  $title = get_the_title($office_id);
                  $link = get_permalink($office_id);
                  $address = get_post_meta($office_id, '_office_full_address', true);
                  if (empty($address)) {
                      $address = get_post_meta($office_id, '_office_street', true);
                  }
                  $phone = get_post_meta($office_id, '_office_phone', true);
                  $working_hours = get_post_meta($office_id, '_office_working_hours', true);
                  
                  // Получаем город из таксономии towns
                  $city = '';
                  $town_terms = wp_get_post_terms($office_id, 'towns', array('fields' => 'names'));
                  if (!empty($town_terms) && !is_wp_error($town_terms)) {
                      $city = $town_terms[0];
                  } else {
                      // Fallback на метаполе
                      $city = get_post_meta($office_id, '_office_city', true);
                  }
                  
                  // Преобразуем координаты в числа
                  $lat = !empty($latitude) ? floatval($latitude) : 0;
                  $lon = !empty($longitude) ? floatval($longitude) : 0;
                  
                  // Проверяем валидность координат
                  if ($lat != 0 && $lon != 0 && $lat >= -90 && $lat <= 90 && $lon >= -180 && $lon <= 180) {
                      $offices_data[] = array(
                          'id' => $office_id,
                          'title' => $title,
                          'link' => $link,
                          'address' => $address ? $address : '',
                          'phone' => $phone ? $phone : '',
                          'working_hours' => $working_hours ? $working_hours : '',
                          'city' => $city ? $city : '',
                          'latitude' => $lat,
                          'longitude' => $lon
                      );
                  }
              }
              wp_reset_postdata();
          }
          
          // Отладочная информация (можно удалить после проверки)
          if (empty($offices_data)) {
              error_log('Offices Archive Map: No offices with coordinates found. Total offices: ' . $offices_query->found_posts);
          }
          
          if (!empty($yandex_api_key)) :
      ?>
          <div class="mb-10 position-relative">
              <?php if (!empty($offices_data)) : ?>
                  <div id="offices-archive-map" style="width: 100%; height: 500px; border-radius: 8px; overflow: hidden;"></div>
                  <!-- Кнопка для открытия списка на мобильных -->
                  <button id="offices-list-toggle" class="btn btn-primary d-md-none" style="position: absolute; top: 10px; left: 10px; z-index: 1001; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                      <i class="uil uil-list-ul"></i> <?php _e('Offices', 'codeweber'); ?>
                  </button>
                  <!-- Список офисов внутри карты -->
                  <div id="offices-map-list" style="position: absolute; top: 10px; left: 10px; width: calc(100% - 20px); max-width: 300px; max-height: calc(100% - 20px); background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); overflow-y: auto; z-index: 1000; display: none;" class="d-md-block">
                      <div style="padding: 15px; border-bottom: 1px solid #e0e0e0; background: #f8f9fa; border-radius: 8px 8px 0 0; position: sticky; top: 0; z-index: 10; display: flex; justify-content: space-between; align-items: center;">
                          <h5 style="margin: 0; font-size: 16px; font-weight: 600;"><?php _e('Offices', 'codeweber'); ?></h5>
                          <button id="offices-list-close" class="btn btn-sm btn-link d-md-none" style="padding: 0; min-width: auto; color: #666;">
                              <i class="uil uil-times"></i>
                          </button>
                      </div>
                      <!-- Фильтр по городам -->
                      <?php 
                      // Получаем уникальные города из офисов
                      $cities = array();
                      foreach ($offices_data as $office) {
                          if (!empty($office['city'])) {
                              $cities[$office['city']] = $office['city'];
                          }
                      }
                      ksort($cities);
                      if (!empty($cities)) :
                      ?>
                      <div style="padding: 15px; border-bottom: 1px solid #e0e0e0; background: #fff; position: sticky; top: 57px; z-index: 9;">
                          <label for="office-city-filter" style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 600; color: #333;">
                              <?php echo esc_html__('Filter by City', 'codeweber'); ?>
                          </label>
                          <select id="office-city-filter" class="form-select form-select-sm" style="width: 100%;">
                              <option value=""><?php echo esc_html__('All Cities', 'codeweber'); ?></option>
                              <?php foreach ($cities as $city) : ?>
                                  <option value="<?php echo esc_attr($city); ?>"><?php echo esc_html($city); ?></option>
                              <?php endforeach; ?>
                          </select>
                      </div>
                      <?php endif; ?>
                      <div id="offices-list-content" style="padding: 10px;">
                          <?php foreach ($offices_data as $index => $office) : ?>
                              <div class="office-list-item" 
                                   data-office-id="<?php echo esc_attr($office['id']); ?>"
                                   data-city="<?php echo esc_attr($office['city']); ?>"
                                   data-latitude="<?php echo esc_attr($office['latitude']); ?>"
                                   data-longitude="<?php echo esc_attr($office['longitude']); ?>"
                                   style="padding: 12px; margin-bottom: 8px; border: 1px solid #e0e0e0; border-radius: 6px; cursor: pointer; transition: all 0.2s;"
                                   onmouseover="this.style.background='#f8f9fa'; this.style.borderColor='#0d6efd';"
                                   onmouseout="this.style.background='#fff'; this.style.borderColor='#e0e0e0';">
                                  <div style="font-weight: 600; margin-bottom: 4px; color: #333;"><?php echo esc_html($office['title']); ?></div>
                                  <?php if (!empty($office['city'])) : ?>
                                      <div style="font-size: 11px; color: #0d6efd; margin-bottom: 4px; font-weight: 500;">
                                          <i class="uil uil-map-marker-alt" style="font-size: 11px;"></i> <?php echo esc_html($office['city']); ?>
                                      </div>
                                  <?php endif; ?>
                                  <?php if (!empty($office['address'])) : ?>
                                      <div style="font-size: 12px; color: #666; margin-bottom: 4px;">
                                          <i class="uil uil-map-marker" style="font-size: 12px;"></i> <?php echo esc_html($office['address']); ?>
                                      </div>
                                  <?php endif; ?>
                                  <?php if (!empty($office['phone'])) : ?>
                                      <div style="font-size: 12px; color: #666;">
                                          <i class="uil uil-phone" style="font-size: 12px;"></i> <?php echo esc_html($office['phone']); ?>
                                      </div>
                                  <?php endif; ?>
                              </div>
                          <?php endforeach; ?>
                      </div>
                  </div>
              <?php else : ?>
                  <div class="alert alert-info">
                      <p><?php _e('No offices with coordinates found.', 'codeweber'); ?></p>
                  </div>
              <?php endif; ?>
          </div>
          
          <?php if (!empty($offices_data)) : ?>
          <script src="https://api-maps.yandex.ru/2.1/?apikey=<?php echo esc_attr($yandex_api_key); ?>&lang=ru_RU"></script>
          <script>
              document.addEventListener("DOMContentLoaded", function() {
                  ymaps.ready(function() {
                      var officesData = <?php echo json_encode($offices_data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
                      var siteLogoUrl = '<?php echo esc_js($site_logo_url); ?>';
                      
                      console.log('Offices data:', officesData);
                      console.log('Offices count:', officesData ? officesData.length : 0);
                      console.log('Site logo URL:', siteLogoUrl);
                      
                      if (!officesData || officesData.length === 0) {
                          console.log('No offices data found');
                          return;
                      }
                      
                      // Вычисляем центр карты (среднее значение координат)
                      var centerLat = 0;
                      var centerLon = 0;
                      officesData.forEach(function(office) {
                          centerLat += parseFloat(office.latitude);
                          centerLon += parseFloat(office.longitude);
                      });
                      centerLat = centerLat / officesData.length;
                      centerLon = centerLon / officesData.length;
                      
                      console.log('Map center:', centerLat, centerLon);
                      
                      // Создаем карту
                      var map = new ymaps.Map("offices-archive-map", {
                          center: [centerLat, centerLon],
                          zoom: officesData.length === 1 ? 15 : 10,
                          controls: ["zoomControl", "searchControl", "typeSelector", "fullscreenControl"]
                      });
                      
                      // Создаем коллекцию маркеров и связь с элементами списка
                      var placemarks = [];
                      var placemarksMap = {}; // Связь ID офиса с маркером
                      var allPlacemarks = []; // Все маркеры для фильтрации
                      
                      officesData.forEach(function(office) {
                          var lat = parseFloat(office.latitude);
                          var lon = parseFloat(office.longitude);
                          
                          console.log('Adding marker:', office.title, lat, lon);
                          
                          if (isNaN(lat) || isNaN(lon)) {
                              console.error('Invalid coordinates for office:', office.title, lat, lon);
                              return;
                          }
                          
                          // Формируем содержимое балуна
                          var balloonContent = '';
                          
                          if (office.address) {
                              balloonContent += '<div style="margin-bottom: 8px;"><strong><?php echo esc_js(__('Address', 'codeweber')); ?>:</strong><br>' + office.address + '</div>';
                          }
                          
                          if (office.phone) {
                              balloonContent += '<div style="margin-bottom: 8px;"><strong><?php echo esc_js(__('Phone', 'codeweber')); ?>:</strong><br><a href="tel:' + office.phone.replace(/[^0-9+]/g, '') + '">' + office.phone + '</a></div>';
                          }
                          
                          if (office.working_hours) {
                              balloonContent += '<div style="margin-bottom: 8px;"><strong><?php echo esc_js(__('Working Hours', 'codeweber')); ?>:</strong><br>' + office.working_hours + '</div>';
                          }
                          
                          balloonContent += '<div style="margin-top: 10px;"><a href="' + office.link + '" style="display: inline-block; padding: 6px 12px; background: #0d6efd; color: #fff; text-decoration: none; border-radius: 4px;"><?php echo esc_js(__('View Details', 'codeweber')); ?></a></div>';
                          
                          // Используем стандартные маркеры красного цвета
                          var iconOptions = {
                              preset: 'islands#redDotIcon'
                          };
                          
                          var placemark = new ymaps.Placemark(
                              [lat, lon],
                              {
                                  balloonContentHeader: '<strong style="color: #333; font-size: 16px;">' + office.title + '</strong>',
                                  balloonContentBody: balloonContent,
                                  hintContent: office.title
                              },
                              iconOptions
                          );
                          
                          // Добавляем клик на маркер для открытия балуна
                          placemark.events.add('click', function() {
                              placemark.balloon.open();
                              // Подсвечиваем элемент в списке
                              highlightOfficeInList(office.id);
                          });
                          
                          // Добавляем маркер на карту сразу
                          map.geoObjects.add(placemark);
                          placemarks.push(placemark);
                          allPlacemarks.push(placemark);
                          placemarksMap[office.id] = placemark;
                      });
                      
                      // Функция фильтрации офисов по городу
                      function filterOfficesByCity(city) {
                          var visiblePlacemarks = [];
                          
                          // Сначала удаляем все маркеры с карты
                          allPlacemarks.forEach(function(placemark) {
                              map.geoObjects.remove(placemark);
                          });
                          
                          // Фильтруем элементы списка и маркеры
                          document.querySelectorAll('.office-list-item').forEach(function(item) {
                              var itemCity = item.getAttribute('data-city');
                              var officeId = item.getAttribute('data-office-id');
                              var placemark = placemarksMap[officeId];
                              
                              if (!city || itemCity === city) {
                                  // Показываем элемент списка
                                  item.style.display = 'block';
                                  
                                  // Добавляем маркер на карту
                                  if (placemark) {
                                      map.geoObjects.add(placemark);
                                      visiblePlacemarks.push(placemark);
                                  }
                              } else {
                                  // Скрываем элемент списка
                                  item.style.display = 'none';
                              }
                          });
                          
                          // Подгоняем границы карты под видимые маркеры
                          if (visiblePlacemarks.length > 0) {
                              setTimeout(function() {
                                  if (visiblePlacemarks.length > 1) {
                                      // Получаем координаты всех видимых маркеров
                                      var coordinates = [];
                                      visiblePlacemarks.forEach(function(pm) {
                                          coordinates.push(pm.geometry.getCoordinates());
                                      });
                                      
                                      // Вычисляем минимальные и максимальные координаты
                                      var minLat = Math.min.apply(null, coordinates.map(function(c) { return c[0]; }));
                                      var maxLat = Math.max.apply(null, coordinates.map(function(c) { return c[0]; }));
                                      var minLon = Math.min.apply(null, coordinates.map(function(c) { return c[1]; }));
                                      var maxLon = Math.max.apply(null, coordinates.map(function(c) { return c[1]; }));
                                      
                                      // Вычисляем разницу (расстояние между точками)
                                      var latDiff = maxLat - minLat;
                                      var lonDiff = maxLon - minLon;
                                      
                                      // Если точки очень близки (в пределах одного города)
                                      if (latDiff < 0.01 && lonDiff < 0.01) {
                                          // Центрируем на среднем значении координат с фиксированным зумом
                                          var centerLat = (minLat + maxLat) / 2;
                                          var centerLon = (minLon + maxLon) / 2;
                                          map.setCenter([centerLat, centerLon], 14, {
                                              duration: 300
                                          });
                                      } else {
                                          // Для далеких точек используем setBounds с padding
                                          var bounds = [[minLat, minLon], [maxLat, maxLon]];
                                          
                                          // Добавляем padding (10% от размера области)
                                          var latPadding = latDiff * 0.1;
                                          var lonPadding = lonDiff * 0.1;
                                          
                                          map.setBounds([
                                              [minLat - latPadding, minLon - lonPadding],
                                              [maxLat + latPadding, maxLon + lonPadding]
                                          ], {
                                              checkZoomRange: true,
                                              duration: 300
                                          });
                                      }
                                  } else if (visiblePlacemarks.length === 1) {
                                      var coords = visiblePlacemarks[0].geometry.getCoordinates();
                                      map.setCenter(coords, 15, {
                                          duration: 300
                                      });
                                  }
                              }, 100);
                          }
                      }
                      
                      // Обработчик изменения фильтра по городу
                      var cityFilter = document.getElementById('office-city-filter');
                      if (cityFilter) {
                          cityFilter.addEventListener('change', function() {
                              var selectedCity = this.value;
                              filterOfficesByCity(selectedCity);
                          });
                      }
                      
                      // Функция для подсветки офиса в списке
                      function highlightOfficeInList(officeId) {
                          // Убираем подсветку со всех элементов
                          document.querySelectorAll('.office-list-item').forEach(function(item) {
                              item.style.background = '#fff';
                              item.style.borderColor = '#e0e0e0';
                          });
                          
                          // Подсвечиваем выбранный элемент
                          var selectedItem = document.querySelector('.office-list-item[data-office-id="' + officeId + '"]');
                          if (selectedItem) {
                              selectedItem.style.background = '#e7f3ff';
                              selectedItem.style.borderColor = '#0d6efd';
                              // Прокручиваем к элементу
                              selectedItem.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                          }
                      }
                      
                      // Управление отображением списка на мобильных
                      var listToggle = document.getElementById('offices-list-toggle');
                      var listClose = document.getElementById('offices-list-close');
                      var officesList = document.getElementById('offices-map-list');
                      
                      if (listToggle && listClose && officesList) {
                          // Открытие списка
                          listToggle.addEventListener('click', function() {
                              officesList.style.display = 'block';
                              listToggle.style.display = 'none';
                          });
                          
                          // Закрытие списка
                          listClose.addEventListener('click', function() {
                              officesList.style.display = 'none';
                              listToggle.style.display = 'block';
                          });
                          
                          // Закрытие при клике вне списка на мобильных
                          document.addEventListener('click', function(e) {
                              if (window.innerWidth < 768) {
                                  if (!officesList.contains(e.target) && !listToggle.contains(e.target) && officesList.style.display === 'block') {
                                      officesList.style.display = 'none';
                                      listToggle.style.display = 'block';
                                  }
                              }
                          });
                      }
                      
                      // Добавляем обработчики клика на элементы списка
                      document.querySelectorAll('.office-list-item').forEach(function(item) {
                          item.addEventListener('click', function() {
                              var officeId = this.getAttribute('data-office-id');
                              var lat = parseFloat(this.getAttribute('data-latitude'));
                              var lon = parseFloat(this.getAttribute('data-longitude'));
                              
                              // Получаем текущий зум карты
                              var currentZoom = map.getZoom();
                              var targetZoom = 15;
                              
                              // Проверяем расстояние от текущего центра до целевой точки
                              var currentCenter = map.getCenter();
                              var distance = Math.sqrt(
                                  Math.pow(currentCenter[0] - lat, 2) + 
                                  Math.pow(currentCenter[1] - lon, 2)
                              );
                              
                              // Если зум уже близок к целевому и точка близка, только центрируем без изменения зума
                              if (currentZoom >= 14 && distance < 0.01) {
                                  // Просто центрируем без изменения зума
                                  map.panTo([lat, lon], {
                                      duration: 300,
                                      flying: true
                                  });
                              } else {
                                  // Используем setCenter с зумом для плавного перехода
                                  map.setCenter([lat, lon], targetZoom, {
                                      duration: 300,
                                      flying: true
                                  });
                              }
                              
                              // Открываем балун маркера после небольшой задержки
                              setTimeout(function() {
                                  if (placemarksMap[officeId]) {
                                      placemarksMap[officeId].balloon.open();
                                  }
                              }, 350);
                              
                              // Подсвечиваем элемент в списке
                              highlightOfficeInList(officeId);
                              
                              // На мобильных закрываем список после выбора
                              if (window.innerWidth < 768 && officesList) {
                                  setTimeout(function() {
                                      officesList.style.display = 'none';
                                      if (listToggle) {
                                          listToggle.style.display = 'block';
                                      }
                                  }, 500);
                              }
                          });
                      });
                      
                      console.log('Total placemarks:', placemarks.length);
                      
                      // Если маркеров больше одного, подгоняем границы карты
                      if (placemarks.length > 1) {
                          map.setBounds(map.geoObjects.getBounds(), {
                              checkZoomRange: true,
                              duration: 300
                          });
                      } else if (placemarks.length === 1) {
                          // Если один маркер, просто центрируем на нем
                          var coords = placemarks[0].geometry.getCoordinates();
                          map.setCenter(coords, 15);
                      }
                  });
              });
          </script>
          <?php endif; ?>
      <?php 
          endif;
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


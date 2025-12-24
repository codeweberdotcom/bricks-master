<?php
/**
 * Template: Single Office Style 5
 * 
 * Шаблон для отображения страницы офиса с картой и кнопками-контактами с метками и значениями
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

$post_id = get_the_ID();

// Получаем основную информацию
$country = get_post_meta($post_id, '_office_country', true);
$region = get_post_meta($post_id, '_office_region', true);
$street = get_post_meta($post_id, '_office_street', true);
$postal_code = get_post_meta($post_id, '_office_postal_code', true);
$full_address = get_post_meta($post_id, '_office_full_address', true);
$working_hours = get_post_meta($post_id, '_office_working_hours', true);
$description = get_post_meta($post_id, '_office_description', true);

// Получаем город из таксономии
$town_terms = wp_get_post_terms($post_id, 'towns', array('fields' => 'names'));
$city = '';
if (!empty($town_terms) && !is_wp_error($town_terms)) {
    $city = $town_terms[0];
} else {
    $city = get_post_meta($post_id, '_office_city', true);
}

// Получаем контактную информацию
$phone = get_post_meta($post_id, '_office_phone', true);
$phone_2 = get_post_meta($post_id, '_office_phone_2', true);
$email = get_post_meta($post_id, '_office_email', true);
$fax = get_post_meta($post_id, '_office_fax', true);
$website = get_post_meta($post_id, '_office_website', true);

// Получаем координаты для карты
$latitude = get_post_meta($post_id, '_office_latitude', true);
$longitude = get_post_meta($post_id, '_office_longitude', true);
$zoom = get_post_meta($post_id, '_office_zoom', true);

// Получаем API ключ Яндекс карт из Redux
global $opt_name;
if (empty($opt_name)) {
    $opt_name = 'redux_demo';
}
// #region agent log
$log_data = json_encode(['location' => 'offices_5.php:47', 'message' => 'Redux check start', 'data' => ['opt_name' => $opt_name ?? 'NOT_SET', 'class_exists_Redux' => class_exists('Redux')], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A']);
$log_file = ABSPATH . '.cursor/debug.log';
@file_put_contents($log_file, $log_data . "\n", FILE_APPEND);
// #endregion
$yandex_api_key = '';
$show_directions_button = false;
if (class_exists('Redux')) {
    $yandex_api_key = Redux::get_option($opt_name, 'yandexapi');
    $route_button_option = Redux::get_option($opt_name, 'yandex_maps_route_button');
    // #region agent log
    $log_data = json_encode(['location' => 'offices_5.php:55', 'message' => 'Route button option retrieved', 'data' => ['route_button_option' => $route_button_option, 'route_button_option_type' => gettype($route_button_option), 'route_button_option_empty' => empty($route_button_option), 'route_button_option_bool' => (bool)$route_button_option], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A']);
    @file_put_contents($log_file, $log_data . "\n", FILE_APPEND);
    // #endregion
    // Проверяем, что опция включена
    $show_directions_button = !empty($route_button_option);
    // #region agent log
    $log_data = json_encode(['location' => 'offices_5.php:58', 'message' => 'Show directions button calculated', 'data' => ['show_directions_button' => $show_directions_button, 'show_directions_button_type' => gettype($show_directions_button)], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A']);
    @file_put_contents($log_file, $log_data . "\n", FILE_APPEND);
    // #endregion
} else {
    // #region agent log
    $log_data = json_encode(['location' => 'offices_5.php:62', 'message' => 'Redux class not found', 'data' => [], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'C']);
    @file_put_contents($log_file, $log_data . "\n", FILE_APPEND);
    // #endregion
}

// Формируем полный адрес для отображения
$address_parts = array();
if ($full_address) {
    $address_parts[] = $full_address;
} else {
    if ($street) $address_parts[] = $street;
    if ($city) $address_parts[] = $city;
    if ($region) $address_parts[] = $region;
    if ($country) $address_parts[] = $country;
    if ($postal_code) $address_parts[] = $postal_code;
}
$display_address = implode(', ', $address_parts);

// Формируем локацию для подзаголовка
$location_parts = array();
if ($city) $location_parts[] = $city;
if ($region) $location_parts[] = $region;
if ($country) $location_parts[] = $country;
$display_location = implode(', ', $location_parts);

// Получаем класс для кнопок из темы
$button_class = function_exists('getThemeButton') ? getThemeButton() : '';
?>

<div class="row g-3">
    <div class="col-md-5">
        <?php if (!empty($yandex_api_key) && !empty($latitude) && !empty($longitude)) : ?>
            <?php 
            $figure_radius = function_exists('getThemeCardImageRadius') ? getThemeCardImageRadius() : 'rounded';
            $figure_radius = $figure_radius ?: 'rounded';
            ?>
            <div class="mb-8 mb-md-0 <?php echo esc_attr($figure_radius); ?>">
                <div id="office-single-map-5" style="width: 100%; height: 400px; border-radius: <?php echo $figure_radius ? '8px' : '0'; ?>;"></div>
            </div>
        <?php endif; ?>
    </div>
    <!--/column -->

    <div class="col-md-7">
        <?php $card_radius = function_exists('getThemeCardImageRadius') ? getThemeCardImageRadius() : ''; ?>
        <div class="card h-100<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
            <div class="card-body">
                <h2 class="mb-1"><?php the_title(); ?></h2>
                
                <?php if ($display_location) : ?>
                    <p class="text-muted mb-4"><?php echo esc_html($display_location); ?></p>
                <?php endif; ?>
                
                <hr class="my-4">

                <div class="row g-3">
                    <!-- Левая колонка - Кнопки контактов -->
                    <div class="col-md-6">
                        <?php if (!empty($phone)) : ?>
                            <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone)); ?>" class="btn btn-icon btn-sm btn-icon-start btn-outline-dark justify-content-between d-flex w-100 mb-2 has-ripple<?php echo esc_attr($button_class); ?>">
                                <i class="uil uil-phone"></i>
                                <div class="d-flex flex-wrap text-end justify-content-end">
                                    <span class="fs-12 lh-1 mb-1 w-100"><?php esc_html_e('Phone', 'codeweber'); ?></span>
                                    <span class="lh-1"><?php echo esc_html($phone); ?></span>
                                </div>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($phone_2)) : ?>
                            <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone_2)); ?>" class="btn btn-icon btn-sm btn-icon-start btn-outline-dark justify-content-between d-flex w-100 mb-2 has-ripple<?php echo esc_attr($button_class); ?>">
                                <i class="uil uil-phone-alt"></i>
                                <div class="d-flex flex-wrap text-end justify-content-end">
                                    <span class="fs-12 lh-1 mb-1 w-100"><?php esc_html_e('Phone 2', 'codeweber'); ?></span>
                                    <span class="lh-1"><?php echo esc_html($phone_2); ?></span>
                                </div>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($email)) : ?>
                            <a href="mailto:<?php echo esc_attr($email); ?>" class="btn btn-icon btn-icon-start btn-sm btn-outline-dark justify-content-between d-flex w-100 mb-2 has-ripple<?php echo esc_attr($button_class); ?>">
                                <i class="uil uil-envelope"></i>
                                <div class="d-flex flex-wrap text-end justify-content-end">
                                    <span class="fs-12 lh-1 mb-1 w-100"><?php esc_html_e('Email', 'codeweber'); ?></span>
                                    <span class="lh-1"><?php echo esc_html($email); ?></span>
                                </div>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($website)) : ?>
                            <?php 
                            $website_display = preg_replace('#^https?://#', '', $website);
                            ?>
                            <a href="<?php echo esc_url($website); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-icon btn-icon-start btn-outline-dark justify-content-between d-flex w-100 mb-2 has-ripple<?php echo esc_attr($button_class); ?>">
                                <i class="uil uil-globe"></i>
                                <div class="d-flex flex-wrap text-end justify-content-end">
                                    <span class="fs-12 lh-1 mb-1 w-100"><?php esc_html_e('Website', 'codeweber'); ?></span>
                                    <span class="lh-1"><?php echo esc_html($website_display); ?></span>
                                </div>
                            </a>
                        <?php endif; ?>
                    </div>
                    <!--/column -->

                    <!-- Правая колонка - Адрес и часы работы -->
                    <div class="col-md-6">
                        <?php if ($display_address) : ?>
                            <div class="d-flex align-items-center mb-4">
                                <div class="icon btn btn-circle btn-md btn-soft-primary me-3">
                                    <i class="uil uil-map-marker"></i>
                                </div>
                                <div>
                                    <div class="mb-1 h6"><?php esc_html_e('Address', 'codeweber'); ?></div>
                                    <p class="mb-0"><?php echo esc_html($display_address); ?></p>
                                    <?php 
                                    // #region agent log
                                    $log_data = json_encode(['location' => 'offices_5.php:168', 'message' => 'Checking show_directions_button condition', 'data' => ['show_directions_button' => $show_directions_button, 'show_directions_button_type' => gettype($show_directions_button), 'condition_result' => (bool)$show_directions_button, 'display_address' => $display_address], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'E']);
                                    @file_put_contents($log_file, $log_data . "\n", FILE_APPEND);
                                    // #endregion
                                    if ($show_directions_button) : ?>
                                        <a href="https://yandex.ru/maps/?text=<?php echo urlencode($display_address); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary mt-2<?php echo esc_attr($button_class); ?>">
                                            <i class="uil uil-directions me-1"></i> <?php esc_html_e('Get Directions', 'codeweber'); ?>
                                        </a>
                                    <?php 
                                    // #region agent log
                                    $log_data = json_encode(['location' => 'offices_5.php:173', 'message' => 'Directions button rendered', 'data' => [], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'E']);
                                    @file_put_contents($log_file, $log_data . "\n", FILE_APPEND);
                                    // #endregion
                                    endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($working_hours)) : ?>
                            <div class="btn btn-sm btn-icon btn-icon-start btn-outline-primary justify-content-between d-flex w-100 mb-2 disabled<?php echo esc_attr($button_class); ?>">
                                <i class="uil uil-clock"></i>
                                <div class="d-flex flex-wrap text-end justify-content-end">
                                    <span class="fs-12 lh-1 mb-1 w-100"><?php esc_html_e('Working Hours', 'codeweber'); ?></span>
                                    <span class="lh-1 fs-11"><?php echo esc_html($working_hours); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($fax)) : ?>
                            <div class="btn btn-sm btn-icon btn-icon-start btn-outline-primary justify-content-between d-flex w-100 mb-2 disabled<?php echo esc_attr($button_class); ?>">
                                <i class="uil uil-fax"></i>
                                <div class="d-flex flex-wrap text-end justify-content-end">
                                    <span class="fs-12 lh-1 mb-1 w-100"><?php esc_html_e('Fax', 'codeweber'); ?></span>
                                    <span class="lh-1 fs-11"><?php echo esc_html($fax); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <!--/column -->
                </div>

                <hr class="my-4">

                <?php if (!empty($description)) : ?>
                    <div class="mb-8">
                        <h4 class="mb-4"><?php esc_html_e('Description', 'codeweber'); ?></h4>
                        <div class="lead">
                            <?php echo wp_kses_post(wpautop($description)); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <!--/.card-body -->
        </div>
        <!--/.card -->
    </div>
    <!--/column -->
</div>
<!--/.row -->

<?php if (get_the_content()) : ?>
    <div class="row mt-10">
        <div class="col-12">
            <div class="blog single">
                <?php the_content(); ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($yandex_api_key) && !empty($latitude) && !empty($longitude)) : ?>
    <script src="https://api-maps.yandex.ru/2.1/?apikey=<?php echo esc_attr($yandex_api_key); ?>&lang=ru_RU"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            ymaps.ready(function() {
                var lat = parseFloat(<?php echo esc_js($latitude); ?>);
                var lon = parseFloat(<?php echo esc_js($longitude); ?>);
                var zoom = parseInt(<?php echo esc_js($zoom ? $zoom : 15); ?>);
                
                if (isNaN(lat) || isNaN(lon) || lat < -90 || lat > 90 || lon < -180 || lon > 180) {
                    console.error('Invalid coordinates for office map');
                    return;
                }
                
                var map = new ymaps.Map("office-single-map-5", {
                    center: [lat, lon],
                    zoom: zoom,
                    controls: ["zoomControl", "searchControl", "typeSelector", "fullscreenControl"]
                });
                
                var balloonContent = '';
                <?php if ($display_address) : ?>
                    balloonContent += '<div style="margin-bottom: 8px;"><strong><?php echo esc_js(__('Address', 'codeweber')); ?>:</strong><br><?php echo esc_js($display_address); ?></div>';
                <?php endif; ?>
                <?php if ($phone) : ?>
                    balloonContent += '<div style="margin-bottom: 8px;"><strong><?php echo esc_js(__('Phone', 'codeweber')); ?>:</strong><br><a href="tel:<?php echo esc_js(preg_replace('/[^0-9+]/', '', $phone)); ?>"><?php echo esc_js($phone); ?></a></div>';
                <?php endif; ?>
                <?php if ($working_hours) : ?>
                    balloonContent += '<div style="margin-bottom: 8px;"><strong><?php echo esc_js(__('Working Hours', 'codeweber')); ?>:</strong><br><?php echo esc_js($working_hours); ?></div>';
                <?php endif; ?>
                
                var placemark = new ymaps.Placemark(
                    [lat, lon],
                    {
                        balloonContentHeader: '<strong style="color: #333; font-size: 16px;"><?php echo esc_js(get_the_title()); ?></strong>',
                        balloonContentBody: balloonContent,
                        hintContent: '<?php echo esc_js(get_the_title()); ?>'
                    },
                    {
                        preset: 'islands#redDotIcon'
                    }
                );
                
                map.geoObjects.add(placemark);
            });
        });
    </script>
<?php endif; ?>


<?php
/**
 * Template: Single Office Default
 * 
 * Шаблон для отображения страницы офиса с картой
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
$manager_id = get_post_meta($post_id, '_office_manager', true);

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
$yandex_address = get_post_meta($post_id, '_office_yandex_address', true);

// Получаем настройки из Redux
global $opt_name;
if (empty($opt_name)) {
    $opt_name = 'redux_demo';
}
$show_directions_button = false;
if (class_exists('Redux')) {
    $route_button_option = Redux::get_option($opt_name, 'yandex_maps_route_button');
    $show_directions_button = (bool) $route_button_option;
}

// Получаем экземпляр класса Yandex Maps
$yandex_maps = Codeweber_Yandex_Maps::get_instance();

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
?>

<section id="post-<?php the_ID(); ?>" <?php post_class('office single'); ?>>
    <div class="row g-3">
        <!-- Левая колонка - Карта -->
        <div class="col-lg-4 mb-10 mb-lg-0">
            <?php $card_radius = function_exists('getThemeCardImageRadius') ? getThemeCardImageRadius() : ''; ?>
            <?php if (!empty($latitude) && !empty($longitude)) : ?>
                <?php
                // Подготавливаем маркер для карты
                $marker = array(
                    'id' => $post_id,
                    'title' => get_the_title(),
                    'link' => get_permalink(),
                    'address' => $display_address,
                    'phone' => $phone,
                    'workingHours' => $working_hours,
                    'city' => $city,
                    'latitude' => floatval($latitude),
                    'longitude' => floatval($longitude),
                );
                
                // Формируем содержимое балуна
                $balloon_content = '';
                if ($display_address) {
                    $balloon_content .= '<div style="margin-bottom: 8px;"><strong>' . esc_html__('Address', 'codeweber') . ':</strong><br>' . esc_html($display_address) . '</div>';
                }
                if ($phone) {
                    $balloon_content .= '<div style="margin-bottom: 8px;"><strong>' . esc_html__('Phone', 'codeweber') . ':</strong><br><a href="tel:' . esc_attr(preg_replace('/[^0-9+]/', '', $phone)) . '">' . esc_html($phone) . '</a></div>';
                }
                if ($working_hours) {
                    $balloon_content .= '<div style="margin-bottom: 8px;"><strong>' . esc_html__('Working Hours', 'codeweber') . ':</strong><br>' . esc_html($working_hours) . '</div>';
                }
                $marker['balloonContentHeader'] = '<strong style="color: #333; font-size: 16px;">' . esc_html(get_the_title()) . '</strong>';
                $marker['balloonContent'] = $balloon_content;
                $marker['hintContent'] = get_the_title();
                
                // Получаем настройки search_control из Redux
                $search_control_enabled = true;
                if (class_exists('Redux')) {
                    $search_control_option = Redux::get_option($opt_name, 'yandex_maps_search_control');
                    $search_control_enabled = (bool) $search_control_option;
                }
                
                // Выводим карту через класс
                echo '<div class="card h-100' . ($card_radius ? ' ' . esc_attr($card_radius) : '') . '">';
                echo '<div class="card-body p-0 h-100 d-flex flex-column">';
                echo '<div class="flex-grow-1">';
                echo '<style>#office-single-map-default, .codeweber-yandex-map-wrapper { height: 100% !important; min-height: 400px; }</style>';
                echo $yandex_maps->render_map(
                    array(
                        'map_id' => 'office-single-map-default',
                        'center' => array(floatval($latitude), floatval($longitude)),
                        'zoom' => !empty($zoom) ? intval($zoom) : 15,
                        'height' => 500, // Будет переопределено через CSS
                        'width' => '100%',
                        'border_radius' => $card_radius ? 8 : 0,
                        'search_control' => $search_control_enabled,
                        'show_sidebar' => false, // Сайдбар отключен на single страницах
                        'marker_auto_open_balloon' => false,
                    ),
                    array($marker)
                );
                echo '</div>';
                echo '</div>';
                echo '</div>';
                ?>
                <!-- /.card -->
            <?php endif; ?>
        </div>
        <!--/column -->

        <!-- Правая колонка - Информация об офисе -->
        <div class="col-lg-8">
            <?php $card_radius = function_exists('getThemeCardImageRadius') ? getThemeCardImageRadius() : ''; ?>
            <div class="card h-100<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
                <div class="card-body px-6 py-5">
                    <!-- Заголовок -->
                    <h2 class="mb-1"><?php the_title(); ?></h2>
                    
                    <?php if ($display_location) : ?>
                        <p class="text-muted mb-4"><?php echo esc_html($display_location); ?></p>
                    <?php endif; ?>

                    <hr class="my-6">

                    <!-- Контент записи -->
                    <?php if (get_the_content()) : ?>
                        <div class="post-content mb-6">
                            <?php the_content(); ?>
                        </div>
                        <!-- /.post-content -->
                        <hr class="my-6">
                    <?php endif; ?>

                    <!-- Контактная информация -->
                    <?php if ($display_address || !empty($phone) || !empty($phone_2) || !empty($email) || !empty($fax) || !empty($working_hours)) : ?>
                        <h3 class="mb-4"><?php echo esc_html__('Contact Information', 'codeweber'); ?></h3>
                        <div class="row g-4 mb-6">
                            <?php if ($display_address) : ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="uil uil-map-marker fs-20 text-primary me-3"></i>
                                        <div>
                                            <strong><?php echo esc_html__('Address', 'codeweber'); ?>:</strong><br>
                                            <span class="text-body"><?php echo esc_html($display_address); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($phone)) : ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="uil uil-phone fs-20 text-primary me-3"></i>
                                        <div>
                                            <strong><?php echo esc_html__('Phone', 'codeweber'); ?>:</strong><br>
                                            <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone)); ?>" class="link-body">
                                                <?php echo esc_html($phone); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($phone_2)) : ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="uil uil-phone-alt fs-20 text-primary me-3"></i>
                                        <div>
                                            <strong><?php echo esc_html__('Phone 2', 'codeweber'); ?>:</strong><br>
                                            <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone_2)); ?>" class="link-body">
                                                <?php echo esc_html($phone_2); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($email)) : ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="uil uil-envelope fs-20 text-primary me-3"></i>
                                        <div>
                                            <strong><?php echo esc_html__('E-Mail', 'codeweber'); ?>:</strong><br>
                                            <a href="mailto:<?php echo esc_attr($email); ?>" class="link-body">
                                                <?php echo esc_html($email); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($fax)) : ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="uil uil-fax fs-20 text-primary me-3"></i>
                                        <div>
                                            <strong><?php echo esc_html__('Fax', 'codeweber'); ?>:</strong><br>
                                            <span class="text-body"><?php echo esc_html($fax); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($working_hours)) : ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="uil uil-clock fs-20 text-primary me-3"></i>
                                        <div>
                                            <strong><?php echo esc_html__('Working Hours', 'codeweber'); ?>:</strong><br>
                                            <span class="text-body"><?php echo esc_html($working_hours); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                </div>
                <!--/.card-body -->
            </div>
            <!-- /.card -->
        </div>
        <!--/column -->
    </div>
    <!--/.row -->
</section> <!-- #post-<?php the_ID(); ?> -->

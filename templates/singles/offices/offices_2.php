<?php
/**
 * Template: Single Office Style 2
 * 
 * Шаблон для отображения страницы офиса с картой и детальной информацией
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

// Получаем менеджера
$manager_name = '';
$manager_link = '';
$manager_position = '';
if ($manager_id) {
    $manager_name = get_the_title($manager_id);
    $manager_link = get_permalink($manager_id);
    $manager_name_meta = get_post_meta($manager_id, '_staff_name', true);
    $manager_surname_meta = get_post_meta($manager_id, '_staff_surname', true);
    $manager_position = get_post_meta($manager_id, '_staff_position', true);
    
    if (!empty($manager_name_meta) || !empty($manager_surname_meta)) {
        $manager_name = trim($manager_name_meta . ' ' . $manager_surname_meta);
        if (empty($manager_name)) {
            $manager_name = get_the_title($manager_id);
        }
    }
}

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

<div class="row g-3">
    <div class="col-md-5">
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
            
            $figure_radius = function_exists('getThemeCardImageRadius') ? getThemeCardImageRadius() : 'rounded';
            $card_radius = function_exists('getThemeCardImageRadius') ? getThemeCardImageRadius() : '';
            echo '<div class="card h-100' . ($card_radius ? ' ' . esc_attr($card_radius) : '') . '">';
            echo '<div class="card-body p-0 h-100 d-flex flex-column">';
            echo '<div class="flex-grow-1">';
            echo '<style>#office-single-map-2, .codeweber-yandex-map-wrapper { height: 100% !important; min-height: 400px; }</style>';
            echo $yandex_maps->render_map(
                array(
                    'map_id' => 'office-single-map-2',
                    'center' => array(floatval($latitude), floatval($longitude)),
                    'zoom' => !empty($zoom) ? intval($zoom) : 15,
                    'height' => 500, // Будет переопределено через CSS
                    'width' => '100%',
                    'border_radius' => $figure_radius ? 8 : 0,
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
        <?php endif; ?>
    </div>
    <!--/column -->

    <div class="col-md-7">
        <div class="p-md-8">
            <h2 class="mb-1"><?php the_title(); ?></h2>
            
            <?php if ($display_location) : ?>
                <p class="text-muted mb-4"><?php echo esc_html($display_location); ?></p>
            <?php endif; ?>
            
            <hr class="my-4">

            <div class="row g-4">
                <!-- Левая колонка - Контакты -->
                <div class="col-md-6">
                    <?php if (!empty($email)) : ?>
                        <div class="d-flex align-items-center mb-4">
                            <div class="icon btn btn-circle btn-md btn-soft-primary me-3 flex-shrink-0">
                                <i class="uil uil-envelope"></i>
                            </div>
                            <div>
                                <div class="mb-1 h6"><?php esc_html_e('Email', 'codeweber'); ?></div>
                                <a href="mailto:<?php echo esc_attr($email); ?>" class="link-body"><?php echo esc_html($email); ?></a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($phone)) : ?>
                        <div class="d-flex align-items-center mb-4">
                            <div class="icon btn btn-circle btn-md btn-soft-primary me-3 flex-shrink-0">
                                <i class="uil uil-phone"></i>
                            </div>
                            <div>
                                <div class="mb-1 h6"><?php esc_html_e('Phone', 'codeweber'); ?></div>
                                <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone)); ?>" class="link-body"><?php echo esc_html($phone); ?></a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($phone_2)) : ?>
                        <div class="d-flex align-items-center mb-4">
                            <div class="icon btn btn-circle btn-md btn-soft-primary me-3 flex-shrink-0">
                                <i class="uil uil-phone-alt"></i>
                            </div>
                            <div>
                                <div class="mb-1 h6"><?php esc_html_e('Phone 2', 'codeweber'); ?></div>
                                <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone_2)); ?>" class="link-body"><?php echo esc_html($phone_2); ?></a>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
                <!--/column -->

                <!-- Правая колонка - Адрес и часы работы -->
                <div class="col-md-6">
                    <?php if ($display_address) : ?>
                        <div class="d-flex align-items-center mb-4">
                            <div class="icon btn btn-circle btn-md btn-soft-primary me-3 flex-shrink-0">
                                <i class="uil uil-map-marker"></i>
                            </div>
                            <div>
                                <div class="mb-1 h6"><?php esc_html_e('Address', 'codeweber'); ?></div>
                                <p class="mb-0"><?php echo esc_html($display_address); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($working_hours)) : ?>
                        <div class="d-flex align-items-center mb-4">
                            <div class="icon btn btn-circle btn-md btn-soft-primary me-3 flex-shrink-0">
                                <i class="uil uil-clock"></i>
                            </div>
                            <div>
                                <div class="mb-1 h6"><?php esc_html_e('Working Hours', 'codeweber'); ?></div>
                                <p class="mb-0"><?php echo esc_html($working_hours); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($manager_name) && !empty($manager_link)) : ?>
                        <div class="d-flex align-items-center mb-4">
                            <div class="icon btn btn-circle btn-md btn-soft-primary me-3 flex-shrink-0">
                                <i class="uil uil-user"></i>
                            </div>
                            <div>
                                <div class="mb-1 h6"><?php esc_html_e('Office Manager', 'codeweber'); ?></div>
                                <a href="<?php echo esc_url($manager_link); ?>" class="link-body">
                                    <strong><?php echo esc_html($manager_name); ?></strong>
                                </a>
                                <?php if (!empty($manager_position)) : ?>
                                    <br><span class="text-muted"><?php echo esc_html($manager_position); ?></span>
                                <?php endif; ?>
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
        <!--/.p-md-8 -->
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


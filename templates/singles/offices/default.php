<?php
/**
 * Template: Single Office Default
 * 
 * Шаблон для отображения страницы офиса
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
    // Fallback на метаполе
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

// Получаем связанную вакансию
$vacancy_id = get_post_meta($post_id, '_office_vacancy', true);
$vacancy_title = '';
$vacancy_link = '';
if ($vacancy_id) {
    $vacancy_title = get_the_title($vacancy_id);
    $vacancy_link = get_permalink($vacancy_id);
}

// Получаем доступные услуги
$services_ids = get_post_meta($post_id, '_office_services', true);
$services = array();
if (is_array($services_ids) && !empty($services_ids)) {
    foreach ($services_ids as $service_id) {
        $service_title = get_the_title($service_id);
        $service_link = get_permalink($service_id);
        if ($service_title) {
            $services[] = array(
                'title' => $service_title,
                'link' => $service_link
            );
        }
    }
}

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
    
    // Формируем полное имя
    if (!empty($manager_name_meta) || !empty($manager_surname_meta)) {
        $manager_name = trim($manager_name_meta . ' ' . $manager_surname_meta);
        if (empty($manager_name)) {
            $manager_name = get_the_title($manager_id);
        }
    }
}

// Получаем описание
$description = get_post_meta($post_id, '_office_description', true);

// Получаем изображение офиса
$office_image_id = get_post_meta($post_id, '_office_image', true);
$thumbnail_id = get_post_thumbnail_id();

// Используем featured image, если есть, иначе метаполе
$image_id = $thumbnail_id ? $thumbnail_id : $office_image_id;

// Получаем API ключ Яндекс карт из Redux
global $opt_name;
if (empty($opt_name)) {
    $opt_name = 'redux_demo';
}
$yandex_api_key = '';
if (class_exists('Redux')) {
    $yandex_api_key = Redux::get_option($opt_name, 'yandexapi');
}

// Формируем полный адрес для отображения
$address_parts = array();
if ($full_address) {
    $address_parts[] = $full_address;
} else {
    if ($street) {
        $address_parts[] = $street;
    }
    if ($city) {
        $address_parts[] = $city;
    }
    if ($region) {
        $address_parts[] = $region;
    }
    if ($country) {
        $address_parts[] = $country;
    }
    if ($postal_code) {
        $address_parts[] = $postal_code;
    }
}
$display_address = implode(', ', $address_parts);
?>

<section id="post-<?php the_ID(); ?>" <?php post_class('office single'); ?>>
    <div class="row g-3">
        <!-- Левая колонка - Изображение и карта -->
        <div class="col-lg-4 mb-10 mb-lg-0">
            <?php $card_radius = function_exists('getThemeCardImageRadius') ? getThemeCardImageRadius() : ''; ?>
            
            <!-- Изображение офиса -->
            <?php if ($image_id) : ?>
                <div class="card mb-4<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
                    <figure class="card-img-top<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
                        <?php 
                        $large_image_url = wp_get_attachment_image_src($image_id, 'codeweber_extralarge');
                        if ($large_image_url) :
                        ?>
                            <a href="<?php echo esc_url($large_image_url[0]); ?>" data-glightbox data-gallery="office-gallery">
                                <?php echo wp_get_attachment_image($image_id, 'codeweber_extralarge', false, array('class' => 'img-fluid')); ?>
                            </a>
                        <?php else : ?>
                            <?php echo wp_get_attachment_image($image_id, 'codeweber_extralarge', false, array('class' => 'img-fluid')); ?>
                        <?php endif; ?>
                    </figure>
                </div>
                <!-- /.card -->
            <?php endif; ?>
            
            <!-- Карта -->
            <?php if (!empty($yandex_api_key) && !empty($latitude) && !empty($longitude)) : ?>
                <div class="card<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
                    <div class="card-body p-0">
                        <div id="office-single-map" style="width: 100%; height: 300px; border-radius: <?php echo $card_radius ? '8px' : '0'; ?>;"></div>
                    </div>
                </div>
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
                    
                    <?php if ($city || $region || $country) : ?>
                        <p class="text-muted mb-4">
                            <?php
                            $location_parts = array();
                            if ($city) $location_parts[] = $city;
                            if ($region) $location_parts[] = $region;
                            if ($country) $location_parts[] = $country;
                            echo esc_html(implode(', ', $location_parts));
                            ?>
                        </p>
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

                    <!-- Описание -->
                    <?php if (!empty($description)) : ?>
                        <div class="mb-6">
                            <h3 class="mb-3"><?php echo esc_html__('Description', 'codeweber'); ?></h3>
                            <div class="post-content">
                                <?php echo wp_kses_post($description); ?>
                            </div>
                        </div>
                        <hr class="my-6">
                    <?php endif; ?>

                    <!-- Адрес -->
                    <?php if ($display_address) : ?>
                        <div class="mb-6">
                            <h3 class="mb-4"><?php echo esc_html__('Address', 'codeweber'); ?></h3>
                            <div class="d-flex align-items-start">
                                <i class="uil uil-map-marker fs-20 text-primary me-3 mt-1"></i>
                                <div>
                                    <p class="mb-0"><?php echo esc_html($display_address); ?></p>
                                </div>
                            </div>
                        </div>
                        <hr class="my-6">
                    <?php endif; ?>

                    <!-- Контактная информация -->
                    <?php if (!empty($phone) || !empty($phone_2) || !empty($email) || !empty($fax) || !empty($website)) : ?>
                        <div class="mb-6">
                            <h3 class="mb-4"><?php echo esc_html__('Contact Information', 'codeweber'); ?></h3>
                            <div class="row g-4">
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

                                <?php if (!empty($website)) : ?>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <i class="uil uil-globe fs-20 text-primary me-3"></i>
                                            <div>
                                                <strong><?php echo esc_html__('Website', 'codeweber'); ?>:</strong><br>
                                                <a href="<?php echo esc_url($website); ?>" target="_blank" rel="noopener noreferrer" class="link-body">
                                                    <?php echo esc_html($website); ?>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <hr class="my-6">
                    <?php endif; ?>

                    <!-- Рабочие часы -->
                    <?php if (!empty($working_hours)) : ?>
                        <div class="mb-6">
                            <h3 class="mb-4"><?php echo esc_html__('Working Hours', 'codeweber'); ?></h3>
                            <div class="d-flex align-items-start">
                                <i class="uil uil-clock fs-20 text-primary me-3 mt-1"></i>
                                <div>
                                    <p class="mb-0"><?php echo esc_html($working_hours); ?></p>
                                </div>
                            </div>
                        </div>
                        <hr class="my-6">
                    <?php endif; ?>

                    <!-- Менеджер -->
                    <?php if (!empty($manager_name) && !empty($manager_link)) : ?>
                        <div class="mb-6">
                            <h3 class="mb-4"><?php echo esc_html__('Office Manager', 'codeweber'); ?></h3>
                            <div class="d-flex align-items-center">
                                <i class="uil uil-user fs-20 text-primary me-3"></i>
                                <div>
                                    <a href="<?php echo esc_url($manager_link); ?>" class="link-body">
                                        <strong><?php echo esc_html($manager_name); ?></strong>
                                    </a>
                                    <?php if (!empty($manager_position)) : ?>
                                        <br><span class="text-muted"><?php echo esc_html($manager_position); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <hr class="my-6">
                    <?php endif; ?>

                    <!-- Связанная вакансия -->
                    <?php if (!empty($vacancy_title) && !empty($vacancy_link)) : ?>
                        <div class="mb-6">
                            <h3 class="mb-4"><?php echo esc_html__('Related Vacancy', 'codeweber'); ?></h3>
                            <div class="d-flex align-items-center">
                                <i class="uil uil-briefcase fs-20 text-primary me-3"></i>
                                <div>
                                    <a href="<?php echo esc_url($vacancy_link); ?>" class="link-body">
                                        <?php echo esc_html($vacancy_title); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <hr class="my-6">
                    <?php endif; ?>

                    <!-- Доступные услуги -->
                    <?php if (!empty($services)) : ?>
                        <div class="mb-6">
                            <h3 class="mb-4"><?php echo esc_html__('Available Services', 'codeweber'); ?></h3>
                            <ul class="unordered-list bullet-primary">
                                <?php foreach ($services as $service) : ?>
                                    <li>
                                        <a href="<?php echo esc_url($service['link']); ?>" class="link-body">
                                            <?php echo esc_html($service['title']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
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

<?php if (!empty($yandex_api_key) && !empty($latitude) && !empty($longitude)) : ?>
    <script src="https://api-maps.yandex.ru/2.1/?apikey=<?php echo esc_attr($yandex_api_key); ?>&lang=ru_RU"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            ymaps.ready(function() {
                var lat = parseFloat(<?php echo esc_js($latitude); ?>);
                var lon = parseFloat(<?php echo esc_js($longitude); ?>);
                var zoom = parseInt(<?php echo esc_js($zoom ? $zoom : 15); ?>);
                
                // Проверяем валидность координат
                if (isNaN(lat) || isNaN(lon) || lat < -90 || lat > 90 || lon < -180 || lon > 180) {
                    console.error('Invalid coordinates for office map');
                    return;
                }
                
                // Создаем карту
                var map = new ymaps.Map("office-single-map", {
                    center: [lat, lon],
                    zoom: zoom,
                    controls: ["zoomControl", "searchControl", "typeSelector", "fullscreenControl"]
                });
                
                // Формируем содержимое балуна
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
                
                // Создаем маркер
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
                
                // Добавляем маркер на карту
                map.geoObjects.add(placemark);
                
                // Открываем балун по умолчанию
                placemark.balloon.open();
            });
        });
    </script>
<?php endif; ?>


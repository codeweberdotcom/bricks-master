<?php
/**
 * Template: Office Post Card
 * 
 * Карточка офиса на базе карточки вакансии
 * 
 * @package Codeweber
 */

$post_id = absint(get_the_ID());
$title = get_the_title($post_id);
$link = get_permalink($post_id);

// Получаем метаполя офиса
$city = '';
$town_terms = wp_get_post_terms($post_id, 'towns', array('fields' => 'names'));
if (!empty($town_terms) && !is_wp_error($town_terms)) {
    $city = $town_terms[0];
} else {
    // Fallback на метаполе
    $city = get_post_meta($post_id, '_office_city', true);
}

$country = get_post_meta($post_id, '_office_country', true);
$region = get_post_meta($post_id, '_office_region', true);
$street = get_post_meta($post_id, '_office_street', true);
$full_address = get_post_meta($post_id, '_office_full_address', true);
$phone = get_post_meta($post_id, '_office_phone', true);
$email = get_post_meta($post_id, '_office_email', true);
$working_hours = get_post_meta($post_id, '_office_working_hours', true);

// Формируем локацию
$location_parts = array();
if ($city) {
    $location_parts[] = $city;
}
if ($region) {
    $location_parts[] = $region;
}
if ($country) {
    $location_parts[] = $country;
}
$location = implode(', ', $location_parts);

// Получаем изображение офиса (featured image или метаполе)
$office_image_url = get_the_post_thumbnail_url($post_id, 'thumbnail');
$office_image_id = get_post_meta($post_id, '_office_image', true);
if (!$office_image_url && $office_image_id) {
    $office_image_url = wp_get_attachment_image_url($office_image_id, 'thumbnail');
}

// Если нет изображения офиса, получаем логотип сайта из Redux
if (!$office_image_url) {
    global $opt_name;
    $options = get_option($opt_name);
    
    // Проверяем кастомный логотип для поста
    $custom_dark_logo = get_post_meta($post_id, 'custom-logo-dark-header', true);
    if (!empty($custom_dark_logo['url'])) {
        $office_image_url = $custom_dark_logo['url'];
    } elseif (!empty($options['opt-dark-logo']['url'])) {
        // Логотип из Redux настроек
        $office_image_url = $options['opt-dark-logo']['url'];
    } else {
        // Дефолтный логотип из темы
        $office_image_url = get_template_directory_uri() . '/dist/assets/img/logo-dark.png';
    }
}

// Определяем, является ли изображение SVG
$is_svg = false;
if ($office_image_url) {
    $image_extension = strtolower(pathinfo(parse_url($office_image_url, PHP_URL_PATH), PATHINFO_EXTENSION));
    $is_svg = ($image_extension === 'svg');
}

// Получаем стили из настроек темы
$card_radius = function_exists('getThemeCardImageRadius') ? getThemeCardImageRadius() : '';
$button_style = function_exists('getThemeButton') ? getThemeButton() : ' rounded-pill';
?>

<div class="card shadow shadow-lg lift h-100<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
        <div class="card-body">
            <!-- Header: Logo and Location -->
            <div class="d-flex align-items-center mb-3">
                <?php if ($office_image_url) : ?>
                    <span class="avatar w-10 h-10 me-3 d-inline-flex align-items-center justify-content-center overflow-hidden bg-white rounded-circle flex-shrink-0 shadow-lg <?php echo $is_svg ? 'p-2' : ''; ?>">
                        <img src="<?php echo esc_url($office_image_url); ?>" alt="<?php echo esc_attr($title); ?>" class="w-100 h-100 <?php echo $is_svg ? 'object-fit-contain' : 'object-fit-cover'; ?>">
                    </span>
                <?php endif; ?>
                <?php if ($location) : ?>
                    <div>
                        <div class="fw-bold text-body"><?php echo esc_html($city ?: $title); ?></div>
                        <div class="text-muted small"><?php echo esc_html($location); ?></div>
                    </div>
                <?php else : ?>
                    <div>
                        <div class="fw-bold text-body"><?php echo esc_html($title); ?></div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Office Title -->
            <h4 class="mb-3">
                <a href="<?php echo esc_url($link); ?>" class="link-dark">
                    <?php echo esc_html($title); ?>
                </a>
            </h4>
            
            <!-- Address -->
            <?php if ($full_address || $street) : ?>
                <div class="mb-3">
                    <div class="d-flex align-items-start">
                        <i class="uil uil-map-marker fs-20 text-primary me-2 mt-1"></i>
                        <div class="text-body small">
                            <?php if ($full_address) : ?>
                                <?php echo esc_html($full_address); ?>
                            <?php elseif ($street) : ?>
                                <?php echo esc_html($street); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Contact Info -->
            <div class="mb-3">
                <?php if ($phone) : ?>
                    <div class="d-flex align-items-center mb-2">
                        <i class="uil uil-phone fs-20 text-primary me-2"></i>
                        <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone)); ?>" class="text-body small text-decoration-none">
                            <?php echo esc_html($phone); ?>
                        </a>
                    </div>
                <?php endif; ?>
                
                <?php if ($email) : ?>
                    <div class="d-flex align-items-center">
                        <i class="uil uil-envelope fs-20 text-primary me-2"></i>
                        <a href="mailto:<?php echo esc_attr($email); ?>" class="text-body small text-decoration-none">
                            <?php echo esc_html($email); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Working Hours -->
            <?php if ($working_hours) : ?>
                <div class="mb-3">
                    <div class="d-flex align-items-start">
                        <i class="uil uil-clock fs-20 text-primary me-2 mt-1"></i>
                        <div class="text-body small">
                            <strong><?php echo esc_html__('Working Hours', 'codeweber'); ?>:</strong><br>
                            <?php echo esc_html($working_hours); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- View Details Button -->
            <div class="mt-3">
                <a href="<?php echo esc_url($link); ?>" class="btn btn-outline-primary<?php echo esc_attr($button_style); ?> w-100 has-ripple">
                    <?php _e('View Details', 'codeweber'); ?>
                </a>
            </div>
        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->


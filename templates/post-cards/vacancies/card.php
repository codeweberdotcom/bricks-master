<?php
/**
 * Template: Vacancy Post Card
 * 
 * Vacancy card in Amazon job posting style
 * 
 * @package Codeweber
 */

$post_id = absint(get_the_ID());
$vacancy_data = get_vacancy_data_array($post_id);
$title = get_the_title($post_id);
$link = get_permalink($post_id);

// Company
$company = !empty($vacancy_data['company']) ? $vacancy_data['company'] : '';

// Get vacancy image (featured image)
$vacancy_image_url = get_the_post_thumbnail_url($post_id, 'thumbnail');

// If no vacancy image, get site logo from Redux
if (!$vacancy_image_url) {
    global $opt_name;
    $options = get_option($opt_name);
    
    // Check custom logo for post
    $custom_dark_logo = get_post_meta($post_id, 'custom-logo-dark-header', true);
    $vacancy_image_url = codeweber_get_media_url($custom_dark_logo);
    if (empty($vacancy_image_url) && !empty($options['opt-dark-logo'])) {
        $vacancy_image_url = codeweber_get_media_url($options['opt-dark-logo']);
    }
    if (empty($vacancy_image_url)) {
        $vacancy_image_url = get_template_directory_uri() . '/dist/assets/img/logo-dark.png';
    }
}

// Determine if image is SVG
$is_svg = false;
if ($vacancy_image_url) {
    $image_extension = strtolower(pathinfo(parse_url($vacancy_image_url, PHP_URL_PATH), PATHINFO_EXTENSION));
    $is_svg = ($image_extension === 'svg');
}

// Location (will be used instead of date)

// Level (senior, junior, etc.) - can be obtained from taxonomy or meta field
$vacancy_types = !empty($vacancy_data['vacancy_types']) && !is_wp_error($vacancy_data['vacancy_types']) ? $vacancy_data['vacancy_types'] : array();
$level_badge = '';
if (!empty($vacancy_types)) {
    foreach ($vacancy_types as $type) {
        $type_name_lower = strtolower($type->name);
        if (strpos($type_name_lower, 'senior') !== false) {
            $level_badge = __('Senior level', 'codeweber');
            break;
        } elseif (strpos($type_name_lower, 'junior') !== false) {
            $level_badge = __('Junior level', 'codeweber');
            break;
        } elseif (strpos($type_name_lower, 'mid') !== false || strpos($type_name_lower, 'middle') !== false) {
            $level_badge = __('Mid level', 'codeweber');
            break;
        }
    }
}

// Salary
$salary = !empty($vacancy_data['salary']) ? $vacancy_data['salary'] : '';

// Location
$location = !empty($vacancy_data['location']) ? $vacancy_data['location'] : '';

// Get styles from theme settings
$card_radius = class_exists('Codeweber_Options') ? Codeweber_Options::style('card-radius') : '';
$button_style = class_exists('Codeweber_Options') ? Codeweber_Options::style('button') : ' rounded-pill';
?>

<div class="col-md-6 col-lg-4">
    <div class="card shadow shadow-lg lift h-100<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
        <div class="card-body">
            <!-- Header: Logo and Company -->
            <div class="d-flex align-items-center mb-3">
                <?php if ($vacancy_image_url) : ?>
                    <span class="avatar w-10 h-10 me-3 d-inline-flex align-items-center justify-content-center overflow-hidden bg-white rounded-circle flex-shrink-0 shadow-lg <?php echo $is_svg ? 'p-2' : ''; ?>">
                        <img src="<?php echo esc_url($vacancy_image_url); ?>" alt="<?php echo esc_attr($company ?: $title); ?>" class="w-100 h-100 <?php echo $is_svg ? 'object-fit-contain' : 'object-fit-cover'; ?>">
                    </span>
                <?php endif; ?>
                <?php if ($company) : ?>
                    <div>
                        <div class="fw-bold text-body"><?php echo esc_html($company); ?></div>
                        <?php if ($location) : ?>
                            <div class="text-muted small"><?php echo esc_html($location); ?></div>
                        <?php endif; ?>
                    </div>
                <?php else : ?>
                    <?php if ($location) : ?>
                        <div>
                            <div class="text-muted small"><?php echo esc_html($location); ?></div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <!-- Job Title -->
            <h4 class="mb-3">
                <a href="<?php echo esc_url($link); ?>" class="link-dark">
                    <?php echo esc_html($title); ?>
                </a>
            </h4>
            
            <?php if ($level_badge) : ?>
            <!-- Badges -->
            <div class="mb-3">
                <span class="badge bg-pale-violet text-violet rounded py-1 me-2 mb-2"><?php echo esc_html($level_badge); ?></span>
            </div>
            <?php endif; ?>
            
            <!-- Salary -->
            <?php if ($salary) : ?>
                <div class="mb-2">
                    <strong class="text-body"><?php echo esc_html($salary); ?></strong>
                </div>
            <?php endif; ?>
            
            
            <!-- Apply Button -->
            <div class="mt-3">
                <a href="<?php echo esc_url($link); ?>" class="btn btn-outline-primary<?php echo esc_attr($button_style); ?> w-100 has-ripple">
                    <?php _e('Read more', 'codeweber'); ?>
                </a>
            </div>
        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->
</div>
<!--/column -->


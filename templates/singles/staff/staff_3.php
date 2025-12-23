<?php
/**
 * Template: Single Staff Default 1
 * 
 * Шаблон для отображения страницы сотрудника
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

// Получаем метаполя
$position = get_post_meta(get_the_ID(), '_staff_position', true);
$name = get_post_meta(get_the_ID(), '_staff_name', true);
$surname = get_post_meta(get_the_ID(), '_staff_surname', true);
$email = get_post_meta(get_the_ID(), '_staff_email', true);
$phone = get_post_meta(get_the_ID(), '_staff_phone', true);
$company = get_post_meta(get_the_ID(), '_staff_company', true);
$department_id = get_post_meta(get_the_ID(), '_staff_department', true);
$job_phone = get_post_meta(get_the_ID(), '_staff_job_phone', true);
$country = get_post_meta(get_the_ID(), '_staff_country', true);
$region = get_post_meta(get_the_ID(), '_staff_region', true);
$city = get_post_meta(get_the_ID(), '_staff_city', true);
$street = get_post_meta(get_the_ID(), '_staff_street', true);
$postal_code = get_post_meta(get_the_ID(), '_staff_postal_code', true);

// Получаем отдел из таксономии
$departments = get_the_terms(get_the_ID(), 'departments');
$department_name = '';
if ($departments && !is_wp_error($departments)) {
    $department = reset($departments);
    $department_name = $department->name;
}

// Получаем изображение
$image_id = get_post_thumbnail_id();
$image_url = $image_id ? wp_get_attachment_image_url($image_id, 'full') : '';

// Получаем QR код
$qrcode_id = get_post_meta(get_the_ID(), '_staff_qrcode_id', true);
$qrcode_url = '';
if ($qrcode_id) {
    $qrcode_url = wp_get_attachment_image_url($qrcode_id, 'full');
}

// Получаем Website
$website = get_post_meta(get_the_ID(), '_staff_website', true);

// Получаем ID записи для функции staff_social_links
$staff_post_id = get_the_ID();

// Получаем тип соцсетей из Redux
global $opt_name;
$social_icon_type = Redux::get_option($opt_name, 'social-icon-type');
$social_type = 'type' . ($social_icon_type ? $social_icon_type : '1'); // По умолчанию type1

// Получаем биографию
$bio = get_post_meta(get_the_ID(), '_staff_bio', true);

// Получаем навыки
$skills = get_post_meta(get_the_ID(), '_staff_skills', true);
$skills_list = [];
if (!empty($skills)) {
    $skills_list = is_array($skills) ? $skills : explode(',', $skills);
    $skills_list = array_map('trim', $skills_list);
}

// Формируем полное имя
$full_name = trim($name . ' ' . $surname);
if (empty($full_name)) {
    $full_name = get_the_title();
}
?>

<div class="row g-3">
    <div class="col-md-5">
        <?php if ($image_url) : ?>
            <?php $figure_radius = function_exists('getThemeCardImageRadius') ? getThemeCardImageRadius() : 'rounded'; ?>
            <figure class="mb-8 mb-md-0<?php echo $figure_radius ? ' ' . esc_attr($figure_radius) : ''; ?>">
                <img src="<?php echo esc_url($image_url); ?>" srcset="<?php echo esc_url($image_url); ?> 2x" alt="<?php echo esc_attr($full_name); ?>" />
            </figure>
        <?php endif; ?>
    </div>
    <!--/column -->

    <div class="col-md-7">
        <?php $card_radius = function_exists('getThemeCardImageRadius') ? getThemeCardImageRadius() : ''; ?>
        <div class="card h-100<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
            <div class="card-body">
                <h2 class="mb-1"><?php echo esc_html($full_name); ?></h2>
                
                <?php 
                // Объединяем должность и компанию
                $position_with_company = $position;
                if (!empty($company)) {
                    $position_with_company = trim($position . ' ' . $company);
                }
                if (!empty($position_with_company)) : 
                ?>
                    <p class="text-muted mb-4"><?php echo esc_html($position_with_company); ?></p>
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

                        <?php if (!empty($job_phone)) : ?>
                            <div class="d-flex align-items-center mb-4">
                                <div class="icon btn btn-circle btn-md btn-soft-primary me-3 flex-shrink-0">
                                    <i class="uil uil-phone-alt"></i>
                                </div>
                                <div>
                                    <div class="mb-1 h6"><?php esc_html_e('Work Phone', 'codeweber'); ?></div>
                                    <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $job_phone)); ?>" class="link-body"><?php echo esc_html($job_phone); ?></a>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                    <!--/column -->

                    <!-- Правая колонка - QR Code -->
                    <?php if ($qrcode_url) : ?>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="icon btn btn-circle btn-md btn-soft-primary me-3 flex-shrink-0">
                                    <i class="uil uil-qrcode-scan"></i>
                                </div>
                                <div>
                                    <div class="mb-1 h6"><?php esc_html_e('QR Code', 'codeweber'); ?></div>
                                    <a href="<?php echo esc_url($qrcode_url); ?>" data-glightbox data-gallery="staff-qrcode" title="<?php echo esc_attr($full_name . ' - ' . __('QR Code', 'codeweber')); ?>" class="link-body">
                                        <img class="w-18" src="<?php echo esc_url($qrcode_url); ?>" alt="<?php echo esc_attr($full_name . ' - ' . __('QR Code', 'codeweber')); ?>" />
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!--/column -->
                    <?php endif; ?>
                </div>

                <hr class="my-4">
                <div class="row g-4 mb-0">
                    <div class="col-md-6 align-self-end">
                        <?php 
                        // Выводим соцсети используя функцию staff_social_links с настройкой из Redux
                        if (function_exists('staff_social_links')) {
                            echo staff_social_links($staff_post_id, 'w-100', $social_type, 'sm');
                        }
                        ?>
                    </div>
                    <div class="col-md-6 d-flex justify-content-md-end">
                        <a href="javascript:void(0)" class="btn btn-primary btn-icon btn-sm btn-icon-start<?php echo function_exists('getThemeButton') ? getThemeButton() : ' rounded'; ?>" data-bs-toggle="download" data-value="staff-<?php echo esc_attr($staff_post_id); ?>">
                            <i class="uil uil-import"></i> <?php esc_html_e('Save to Contacts', 'codeweber'); ?>
                        </a>
                    </div>
                    <!--/column -->
                </div>

                <?php if (!empty($bio)) : ?>
                    <div class="mb-8">
                        <h4 class="mb-4"><?php esc_html_e('About', 'codeweber'); ?></h4>
                        <div class="lead">
                            <?php echo wp_kses_post(wpautop($bio)); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($skills_list)) : ?>
                    <div class="mb-8">
                        <h4 class="mb-4"><?php esc_html_e('Skills', 'codeweber'); ?></h4>
                        <ul class="icon-list bullet-bg bullet-soft-primary">
                            <?php foreach ($skills_list as $skill) : ?>
                                <li><span><?php echo esc_html($skill); ?></span></li>
                            <?php endforeach; ?>
                        </ul>
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


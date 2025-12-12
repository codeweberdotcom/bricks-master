<?php
/**
 * Template: Single Staff Style 5
 * 
 * Шаблон для отображения страницы сотрудника с кнопками-контактами в двух колонках
 * Контактные кнопки с меткой и значением
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

// Получаем социальные сети из метаполей
$telegram = get_post_meta(get_the_ID(), '_staff_telegram', true);
$whatsapp = get_post_meta(get_the_ID(), '_staff_whatsapp', true);
$vk = get_post_meta(get_the_ID(), '_staff_vk', true);
$facebook = get_post_meta(get_the_ID(), '_staff_facebook', true);
$twitter = get_post_meta(get_the_ID(), '_staff_twitter', true);
$linkedin = get_post_meta(get_the_ID(), '_staff_linkedin', true);
$instagram = get_post_meta(get_the_ID(), '_staff_instagram', true);
$skype = get_post_meta(get_the_ID(), '_staff_skype', true);

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

// Получаем класс для кнопок из темы
$button_class = function_exists('getThemeButton') ? getThemeButton() : '';
?>

<div class="row g-3">
    <div class="col-md-5">
        <?php if ($image_url) : ?>
            <?php 
            $figure_radius = function_exists('getThemeCardImageRadius') ? getThemeCardImageRadius() : 'rounded';
            $figure_radius = $figure_radius ?: 'rounded'; // Fallback если функция вернула пустую строку
            ?>
            <figure class="mb-8 mb-md-0 <?php echo esc_attr($figure_radius); ?>">
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

                <div class="row g-3">
                    <!-- Левая колонка - Кнопки контактов -->
                    <div class="col-md-6">
                        <?php if (!empty($job_phone)) : ?>
                            <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $job_phone)); ?>" class="btn btn-icon btn-sm btn-icon-start btn-outline-dark justify-content-between d-flex w-100 mb-2 has-ripple<?php echo esc_attr($button_class); ?>">
                                <i class="uil uil-phone-alt"></i>
                                <div class="d-flex flex-wrap text-end justify-content-end">
                                    <span class="fs-12 lh-1 mb-1 w-100"><?php esc_html_e('Work Phone', 'codeweber'); ?></span>
                                    <span class="lh-1"><?php echo esc_html($job_phone); ?></span>
                                </div>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($phone)) : ?>
                            <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone)); ?>" class="btn btn-icon btn-sm btn-icon-start btn-outline-dark justify-content-between d-flex w-100 mb-2 has-ripple<?php echo esc_attr($button_class); ?>">
                                <i class="uil uil-phone"></i>
                                <div class="d-flex flex-wrap text-end justify-content-end">
                                    <span class="fs-12 lh-1 mb-1 w-100"><?php esc_html_e('Phone', 'codeweber'); ?></span>
                                    <span class="lh-1"><?php echo esc_html($phone); ?></span>
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

                    <!-- Правая колонка - Социальные сети -->
                    <div class="col-md-6">
                        <?php if (!empty($telegram)) : ?>
                            <a href="<?php echo esc_url($telegram); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-icon btn-icon-start btn-outline-primary justify-content-between d-flex w-100 mb-2 has-ripple<?php echo esc_attr($button_class); ?>">
                                <i class="uil uil-telegram-alt"></i> <?php esc_html_e('Telegram', 'codeweber'); ?>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($whatsapp)) : ?>
                            <a href="<?php echo esc_url($whatsapp); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-icon btn-icon-start btn-outline-primary justify-content-between d-flex w-100 mb-2 has-ripple<?php echo esc_attr($button_class); ?>">
                                <i class="uil uil-whatsapp"></i> <?php esc_html_e('WhatsApp', 'codeweber'); ?>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($vk)) : ?>
                            <a href="<?php echo esc_url($vk); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-icon btn-icon-start btn-outline-primary justify-content-between d-flex w-100 mb-2 has-ripple<?php echo esc_attr($button_class); ?>">
                                <i class="uil uil-vk"></i> <?php esc_html_e('VKontakte', 'codeweber'); ?>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($facebook)) : ?>
                            <a href="<?php echo esc_url($facebook); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-icon btn-icon-start btn-outline-primary justify-content-between d-flex w-100 mb-2 has-ripple<?php echo esc_attr($button_class); ?>">
                                <i class="uil uil-facebook-f"></i> <?php esc_html_e('Facebook', 'codeweber'); ?>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($twitter)) : ?>
                            <a href="<?php echo esc_url($twitter); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-icon btn-icon-start btn-outline-primary justify-content-between d-flex w-100 mb-2 has-ripple<?php echo esc_attr($button_class); ?>">
                                <i class="uil uil-twitter"></i> <?php esc_html_e('Twitter', 'codeweber'); ?>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($linkedin)) : ?>
                            <a href="<?php echo esc_url($linkedin); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-icon btn-icon-start btn-outline-primary justify-content-between d-flex w-100 mb-2 has-ripple<?php echo esc_attr($button_class); ?>">
                                <i class="uil uil-linkedin"></i> <?php esc_html_e('LinkedIn', 'codeweber'); ?>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($instagram)) : ?>
                            <a href="<?php echo esc_url($instagram); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-icon btn-icon-start btn-outline-primary justify-content-between d-flex w-100 mb-2 has-ripple<?php echo esc_attr($button_class); ?>">
                                <i class="uil uil-instagram"></i> <?php esc_html_e('Instagram', 'codeweber'); ?>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($skype)) : ?>
                            <a href="<?php echo esc_url($skype); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-icon btn-icon-start btn-outline-primary justify-content-between d-flex w-100 mb-2 has-ripple<?php echo esc_attr($button_class); ?>">
                                <i class="uil uil-skype"></i> <?php esc_html_e('Skype', 'codeweber'); ?>
                            </a>
                        <?php endif; ?>

                        <?php if ($qrcode_url) : ?>
                            <a href="<?php echo esc_url($qrcode_url); ?>" data-glightbox data-gallery="staff-qrcode" title="<?php echo esc_attr($full_name . ' - ' . __('QR Code', 'codeweber')); ?>" class="btn btn-icon btn-icon-start btn-sm btn-outline-primary justify-content-between d-flex w-100 mb-2 has-ripple<?php echo esc_attr($button_class); ?>">
                                <i class="uil uil-qrcode-scan"></i> <?php esc_html_e('QR Code', 'codeweber'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    <!--/column -->
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end">
                    <a href="javascript:void(0)" class="btn btn-icon btn-icon-start btn-outline-primary justify-content-between d-flex has-ripple<?php echo esc_attr($button_class); ?>" data-bs-toggle="download" data-value="staff-<?php echo esc_attr($staff_post_id); ?>">
                        <i class="uil uil-import"></i> <?php esc_html_e('Save to Contacts', 'codeweber'); ?>
                    </a>
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


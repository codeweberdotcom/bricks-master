<?php
/**
 * Template: Single Staff
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
if ($departments && !is_wp_error($departments) && !empty($departments)) {
    $department_name = $departments[0]->name;
}

// Получаем изображение
$thumbnail_id = get_post_thumbnail_id();
?>

<section id="post-<?php the_ID(); ?>" <?php post_class('staff single'); ?>>
    <div class="row g-3">
        <!-- Левая колонка - Изображение -->
        <div class="col-lg-4 mb-10 mb-lg-0">
            <?php $card_radius = function_exists('getThemeCardImageRadius') ? getThemeCardImageRadius() : ''; ?>
            <div class="card h-100<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
                <?php if ($thumbnail_id) : ?>
                    <figure class="card-img-top<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
                        <?php 
                        $large_image_url = wp_get_attachment_image_src($thumbnail_id, 'codeweber_extralarge');
                        if ($large_image_url) :
                        ?>
                            <a href="<?php echo esc_url($large_image_url[0]); ?>" data-glightbox data-gallery="g1">
                                <?php the_post_thumbnail('codeweber_extralarge', array('class' => 'img-fluid')); ?>
                            </a>
                        <?php else : ?>
                            <?php the_post_thumbnail('codeweber_extralarge', array('class' => 'img-fluid')); ?>
                        <?php endif; ?>
                    </figure>
                <?php endif; ?>
            </div>
            <!-- /.card -->
        </div>
        <!--/column -->

        <!-- Правая колонка - Информация о сотруднике, контакты и адрес -->
        <div class="col-lg-8">
            <?php $card_radius = function_exists('getThemeCardImageRadius') ? getThemeCardImageRadius() : ''; ?>
            <div class="card h-100<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
                <div class="card-body px-6 py-5">
                    <!-- Информация о сотруднике -->
                    <?php if (!empty($name) || !empty($surname)) : ?>
                        <h2 class="mb-1">
                            <?php 
                            $name_val = !empty($name) ? $name : '';
                            $surname_val = !empty($surname) ? $surname : '';
                            $full_name = trim($name_val . ' ' . $surname_val);
                            echo esc_html(!empty($full_name) ? $full_name : get_the_title());
                            ?>
                        </h2>
                    <?php else : ?>
                        <h2 class="mb-1"><?php the_title(); ?></h2>
                    <?php endif; ?>

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
                    <?php if (!empty($email) || !empty($phone) || !empty($job_phone)) : ?>
                        <h3 class="mb-4"><?php echo esc_html__('Contact Information', 'codeweber'); ?></h3>
                        <div class="row g-4 mb-6">
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

                        <?php if (!empty($job_phone)) : ?>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="uil uil-phone-alt fs-20 text-primary me-3"></i>
                                    <div>
                                        <strong><?php echo esc_html__('Job Phone', 'codeweber'); ?>:</strong><br>
                                        <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $job_phone)); ?>" class="link-body">
                                            <?php echo esc_html($job_phone); ?>
                                        </a>
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


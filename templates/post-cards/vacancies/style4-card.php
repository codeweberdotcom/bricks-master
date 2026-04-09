<?php
/**
 * Template: Vacancy Style 4 card (структура как в блоге: card > figure > card-body, без row).
 *
 * Call inside the loop (uses get_the_ID()).
 *
 * @package Codeweber
 */

$post_id = absint(get_the_ID());
if (!$post_id) {
    return;
}

$vacancy_data = get_vacancy_data_array($post_id);
$title        = get_the_title($post_id);
$link         = get_permalink($post_id);
$company      = !empty($vacancy_data['company']) ? $vacancy_data['company'] : '';
$salary       = !empty($vacancy_data['salary']) ? $vacancy_data['salary'] : '';
$location     = !empty($vacancy_data['location']) ? $vacancy_data['location'] : '';
$experience   = !empty($vacancy_data['experience']) ? $vacancy_data['experience'] : '';
$vacancy_types = !empty($vacancy_data['vacancy_types']) && !is_wp_error($vacancy_data['vacancy_types']) ? $vacancy_data['vacancy_types'] : [];
$vacancy_schedules = !empty($vacancy_data['vacancy_schedules']) && !is_wp_error($vacancy_data['vacancy_schedules']) ? $vacancy_data['vacancy_schedules'] : [];
$category_name = !empty($vacancy_types) ? $vacancy_types[0]->name : '';
$schedule_name = !empty($vacancy_schedules) ? $vacancy_schedules[0]->name : '';

// Image: миниатюра записи или фото по умолчанию
$thumbnail_id = get_post_thumbnail_id($post_id);
$vacancy_image_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'codeweber_vacancy_600-600') : '';
if (empty($vacancy_image_url)) {
    $vacancy_image_url = get_template_directory_uri() . '/dist/assets/img/photos/about6.jpg';
}

$card_radius    = class_exists('Codeweber_Options') ? Codeweber_Options::style('card-radius') : '';
$show_hit_badge = get_post_meta($post_id, '_vacancy_featured', true) || get_post_meta($post_id, '_vacancy_hit', true);
?>
<div class="card overflow-hidden<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?><?php echo $show_hit_badge ? ' position-relative' : ''; ?>">
	<?php if ($show_hit_badge) : ?>
		<span class="position-absolute top-0 start-0 m-2 badge bg-warning text-dark px-2 py-1"><?php _e('HIT', 'codeweber'); ?></span>
	<?php endif; ?>
	<div class="row g-0 h-100">
		<div class="col-12 col-md-3">
			<figure class="mb-0 h-100 overlay overlay-1 hover-scale">
				<a href="<?php echo esc_url($link); ?>" class="d-block h-100">
					<img src="<?php echo esc_url($vacancy_image_url); ?>" alt="<?php echo esc_attr($title); ?>" class="w-100 h-100 object-fit-cover">
				</a>
				<figcaption>
					<h5 class="from-top mb-0"><?php esc_html_e('Read More', 'codeweber'); ?></h5>
				</figcaption>
			</figure>
		</div>
		<div class="col-12 col-md-9">
			<div class="card-body">
		<h2 class="mb-4 display-6"><?php echo esc_html($title); ?></h2>
		<div class="row g-0 mb-3">
				<div class="col-12 col-md-6">
					<?php if ($location) : ?>
					<div class="mb-1 d-flex">
						<i class="uil uil-map-marker-alt text-primary me-2"></i>
						<span><?php echo esc_html($location); ?></span>
					</div>
					<?php endif; ?>
					<?php if ($experience) : ?>
					<div class="mb-1 d-flex">
						<i class="uil uil-clock text-primary me-2"></i>
						<span><?php echo esc_html($experience); ?></span>
					</div>
					<?php endif; ?>
				</div>
				<div class="col-12 col-md-6">
					<?php if ($category_name) : ?>
					<div class="mb-1 d-flex">
						<i class="uil uil-briefcase-alt text-primary me-2"></i>
						<span><?php echo esc_html($category_name); ?></span>
					</div>
					<?php endif; ?>
					<?php if ($schedule_name) : ?>
					<div class="mb-1 d-flex">
						<i class="uil uil-calendar-alt text-primary me-2"></i>
						<span><?php echo esc_html($schedule_name); ?></span>
					</div>
					<?php endif; ?>
					<?php if ($company) : ?>
					<div class="mb-1 d-flex">
						<i class="uil uil-graduation-cap text-primary me-2"></i>
						<span><?php echo esc_html($company); ?></span>
					</div>
					<?php endif; ?>
					<?php if ($salary) : ?>
					<div class="mb-1 d-flex">
						<i class="uil uil-money-stack text-primary me-2"></i>
						<span><?php echo esc_html($salary); ?></span>
					</div>
					<?php endif; ?>
				</div>
			</div>
		<div data-group="page-title-buttons" class="text-end">
			<a href="<?php echo esc_url($link); ?>" class="btn btn-primary btn-icon btn-icon-start has-ripple<?php echo class_exists('Codeweber_Options') ? ' ' . esc_attr(trim(Codeweber_Options::style('button'))) : ''; ?>">
				<i class="uil uil-arrow-right"></i><?php _e('Go', 'codeweber'); ?>
			</a>
		</div>
		</div><!-- /.card-body -->
		</div><!-- /.col-md-9 -->
	</div><!-- /.row -->
</div>
<!-- /.card -->

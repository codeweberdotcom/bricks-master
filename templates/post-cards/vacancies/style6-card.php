<?php
/**
 * Template: Vacancy Style 6 card (horizontal, whole card clickable, no button, no hover on image).
 *
 * Same layout as style4: card-horizontal, figure + card-body. Root is <a> so the entire card is a link.
 * No overlay/hover on figure, no "Перейти" button.
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

$thumbnail_id = get_post_thumbnail_id($post_id);
$vacancy_image_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'codeweber_vacancy_600-600') : '';
if (empty($vacancy_image_url)) {
    $vacancy_image_url = get_template_directory_uri() . '/dist/assets/img/photos/about6.jpg';
}

$card_radius    = class_exists('Codeweber_Options') ? Codeweber_Options::style('card-radius') : '';
$show_hit_badge = get_post_meta($post_id, '_vacancy_featured', true) || get_post_meta($post_id, '_vacancy_hit', true);
?>
<a href="<?php echo esc_url($link); ?>" class="card lift overflow-hidden text-inherit text-decoration-none<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?><?php echo $show_hit_badge ? ' position-relative' : ''; ?>">
	<?php if ($show_hit_badge) : ?>
		<span class="position-absolute top-0 start-0 m-2 badge bg-warning text-dark px-2 py-1"><?php _e('HIT', 'codeweber'); ?></span>
	<?php endif; ?>
	<div class="row g-0 h-100">
		<div class="col-3">
			<figure class="mb-0 h-100">
				<img src="<?php echo esc_url($vacancy_image_url); ?>" alt="<?php echo esc_attr($title); ?>" class="w-100 h-100 object-fit-cover">
			</figure>
		</div>
		<div class="col-9">
			<div class="card-body position-relative">
		<h2 class="mb-4 display-6"><?php echo esc_html($title); ?></h2>
		<ul class="list-unstyled cc-2 mb-0">
			<?php if ($location) : ?>
			<li class="mb-1 d-flex align-items-center">
				<i class="uil uil-map-marker-alt text-primary me-2"></i>
				<span><?php echo esc_html($location); ?></span>
			</li>
			<?php endif; ?>
			<?php if ($category_name) : ?>
			<li class="mb-1 d-flex align-items-center">
				<i class="uil uil-briefcase-alt text-primary me-2"></i>
				<span><?php echo esc_html($category_name); ?></span>
			</li>
			<?php endif; ?>
			<?php if ($schedule_name) : ?>
			<li class="mb-1 d-flex align-items-center">
				<i class="uil uil-calendar-alt text-primary me-2"></i>
				<span><?php echo esc_html($schedule_name); ?></span>
			</li>
			<?php endif; ?>
			<?php if ($experience) : ?>
			<li class="mb-1 d-flex align-items-center">
				<i class="uil uil-clock text-primary me-2"></i>
				<span><?php echo esc_html($experience); ?></span>
			</li>
			<?php endif; ?>
			<?php if ($company) : ?>
			<li class="mb-1 d-flex align-items-center">
				<i class="uil uil-graduation-cap text-primary me-2"></i>
				<span><?php echo esc_html($company); ?></span>
			</li>
			<?php endif; ?>
			<?php if ($salary) : ?>
			<li class="mb-1 d-flex align-items-center">
				<i class="uil uil-money-stack text-primary me-2"></i>
				<span><?php echo esc_html($salary); ?></span>
			</li>
			<?php endif; ?>
		</ul>
		<div class="hover_card_button position-absolute p-7 top-0 end-0">
			<i class="fs-25 uil uil-arrow-right lh-1"></i>
		</div>
		</div><!-- /.card-body -->
		</div><!-- /.col-9 -->
	</div><!-- /.row -->
</a>
<!-- /.card -->

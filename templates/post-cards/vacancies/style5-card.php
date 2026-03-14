<?php
/**
 * Template: Vacancy Style 5 card (vertical: company, title, list, link)
 *
 * Card: company, title (h2.h3), list (location, schedule, salary), "Go" link at bottom.
 * Used by vacancies_4. Call inside the loop (uses get_the_ID()).
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
$vacancy_schedules = !empty($vacancy_data['vacancy_schedules']) && !is_wp_error($vacancy_data['vacancy_schedules']) ? $vacancy_data['vacancy_schedules'] : array();
$schedule_name = !empty($vacancy_schedules) ? $vacancy_schedules[0]->name : '';

$card_radius  = class_exists('Codeweber_Options') ? Codeweber_Options::style('card-radius') : '';
?>
<div class="card shadow shadow-lg h-100<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
	<div class="card-body d-flex flex-column justify-content-between h-100">
		<div>
			<?php if ($company) : ?>
			<div class="text-line-primary text-left fs-15 text-uppercase mb-3"><?php echo esc_html($company); ?></div>
			<?php endif; ?>
			<h2 class="h3 mb-3"><?php echo esc_html($title); ?></h2>
			<ul class="list-unstyled cc-1 mb-0">
				<?php if ($location) : ?>
				<li class="mb-1 d-flex align-items-center">
					<i class="uil uil-map-marker-alt text-primary me-2"></i>
					<span><?php echo esc_html($location); ?></span>
				</li>
				<?php endif; ?>
				<?php if ($schedule_name) : ?>
				<li class="mb-1 d-flex align-items-center">
					<i class="uil uil-calendar-alt text-primary me-2"></i>
					<span><?php echo esc_html($schedule_name); ?></span>
				</li>
				<?php endif; ?>
				<?php if ($salary) : ?>
				<li class="mb-1 d-flex align-items-center">
					<i class="uil uil-money-stack text-primary me-2"></i>
					<span><?php echo esc_html($salary); ?></span>
				</li>
				<?php endif; ?>
			</ul>
		</div>
		<div class="mt-3 text-end">
			<a href="<?php echo esc_url($link); ?>" class="hover"><?php _e('Go', 'codeweber'); ?></a>
		</div>
	</div>
</div>

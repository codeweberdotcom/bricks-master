<?php
/**
 * Template: Vacancy list item (one row card)
 *
 * Used by vacancies_1 and AJAX filter. Expects query vars:
 * - vacancy_list_item_post_id
 * - vacancy_list_item_data (from get_vacancy_data_array)
 * - vacancy_list_item_avatar_color
 * - vacancy_list_item_card_radius
 *
 * @package Codeweber
 */

$post_id       = (int) get_query_var('vacancy_list_item_post_id', 0);
$vacancy_data  = get_query_var('vacancy_list_item_data', array());
$avatar_color  = get_query_var('vacancy_list_item_avatar_color', 'bg-red');
$card_radius   = get_query_var('vacancy_list_item_card_radius', '');

if (!$post_id) {
    return;
}

$title   = get_the_title($post_id);
$link    = get_permalink($post_id);
$words   = explode(' ', $title);
$initials = (count($words) >= 2)
    ? mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1)
    : mb_substr($title, 0, 2);
$initials = strtoupper($initials);
$location = !empty($vacancy_data['location']) ? $vacancy_data['location'] : '';
$salary   = !empty($vacancy_data['salary']) ? $vacancy_data['salary'] : '';
?>
<a href="<?php echo esc_url($link); ?>" class="card mb-4 lift vacancy-item<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
    <div class="card-body p-5">
        <span class="row justify-content-between align-items-center">
            <span class="col-md-5 mb-2 mb-md-0 d-flex align-items-center text-body">
                <span class="avatar <?php echo esc_attr($avatar_color); ?> text-white w-9 h-9 fs-17 me-3"><?php echo esc_html($initials); ?></span>
                <?php echo esc_html($title); ?>
            </span>
            <?php if ($salary) : ?>
                <span class="col-7 col-md-3 col-lg-2 text-body d-flex align-items-center mb-2 mb-md-0">
                    <i class="uil uil-money-bill me-1"></i>
                    <?php echo esc_html($salary); ?>
                </span>
            <?php endif; ?>
            <?php if ($location) : ?>
                <span class="col-7 col-md-3 col-lg-2 text-body d-flex align-items-center text-nowrap">
                    <i class="uil uil-location-arrow me-1 flex-shrink-0"></i>
                    <?php echo esc_html($location); ?>
                </span>
            <?php endif; ?>
            <span class="d-none d-lg-block col-1 text-center text-body">
                <i class="uil uil-angle-right-b"></i>
            </span>
        </span>
    </div>
    <!-- /.card-body -->
</a>
<!-- /.card -->

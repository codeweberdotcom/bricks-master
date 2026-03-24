<?php
/**
 * Template: Vacancy grid card (Style 2)
 *
 * One card for vacancies_2. Call inside the loop (uses get_the_ID()).
 *
 * @package Codeweber
 */

$post_id = absint(get_the_ID());
if (!$post_id) {
    return;
}

$vacancy_data   = get_vacancy_data_array($post_id);
$title          = get_the_title($post_id);
$link           = get_permalink($post_id);
$archive_card_radius = class_exists('Codeweber_Options') ? Codeweber_Options::style('card-radius') : '';

$words   = explode(' ', $title);
$initials = (count($words) >= 2)
    ? mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1)
    : mb_substr($title, 0, 2);
$initials = strtoupper($initials);

$avatar_colors = array('bg-red', 'bg-green', 'bg-yellow', 'bg-purple', 'bg-orange', 'bg-pink', 'bg-blue');
$color_index   = abs(crc32($title)) % count($avatar_colors);
$avatar_color  = $avatar_colors[$color_index];

$location = !empty($vacancy_data['location']) ? $vacancy_data['location'] : __('Anywhere', 'codeweber');
$salary   = !empty($vacancy_data['salary']) ? $vacancy_data['salary'] : '';
?>
<a href="<?php echo esc_url($link); ?>" class="card lift h-100<?php echo $archive_card_radius ? ' ' . esc_attr($archive_card_radius) : ''; ?>">
    <div class="card-body p-5 d-flex flex-row">
        <div class="flex-shrink-0">
            <span class="avatar <?php echo esc_attr($avatar_color); ?> text-white w-11 h-11 fs-20 me-4"><?php echo esc_html($initials); ?></span>
        </div>
        <div class="min-width-0">
            <h4 class="mb-1"><?php echo esc_html($title); ?></h4>
            <?php if ($salary) : ?>
                <p class="mb-1 text-body fw-semibold"><?php echo esc_html($salary); ?></p>
            <?php endif; ?>
            <p class="mb-0 text-body"><?php echo esc_html($location); ?></p>
        </div>
    </div>
</a>

<?php
/**
 * Template: Vacancies Archive - Style 2 (Card Grid)
 * 
 * Карточка вакансии с аватаром, бейджем типа занятости и локацией
 */

$post_id = absint(get_the_ID());
$vacancy_data = get_vacancy_data_array($post_id);
$title = get_the_title($post_id);
$link = get_permalink($post_id);

// Генерируем инициалы для аватара
$words = explode(' ', $title);
$initials = '';
if (count($words) >= 2) {
    $initials = mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1);
} else {
    $initials = mb_substr($title, 0, 2);
}
$initials = strtoupper($initials);

// Получаем цвет для аватара (на основе хэша названия для стабильности)
$avatar_colors = array('bg-red', 'bg-green', 'bg-yellow', 'bg-purple', 'bg-orange', 'bg-pink', 'bg-blue');
$color_index = abs(crc32($title)) % count($avatar_colors);
$avatar_color = $avatar_colors[$color_index];

// Тип занятости
$employment_type = !empty($vacancy_data['employment_type']) ? $vacancy_data['employment_type'] : '';
$employment_types = array(
    'full-time' => __('Full Time', 'codeweber'),
    'part-time' => __('Part Time', 'codeweber'),
    'remote' => __('Remote', 'codeweber'),
    'contract' => __('Contract', 'codeweber')
);
$display_employment_type = isset($employment_types[$employment_type]) ? $employment_types[$employment_type] : $employment_type;

// Бейдж цвет на основе типа занятости
$badge_classes = 'bg-pale-blue text-blue'; // По умолчанию
if ($employment_type === 'remote') {
    $badge_classes = 'bg-pale-aqua text-aqua';
} elseif ($employment_type === 'part-time') {
    $badge_classes = 'bg-pale-violet text-violet';
}

// Локация
$location = !empty($vacancy_data['location']) ? $vacancy_data['location'] : __('Anywhere', 'codeweber');
?>

<div class="col-md-6 col-lg-4">
    <a href="<?php echo esc_url($link); ?>" class="card shadow-lg lift h-100">
        <div class="card-body p-5 d-flex flex-row">
            <div>
                <span class="avatar <?php echo esc_attr($avatar_color); ?> text-white w-11 h-11 fs-20 me-4"><?php echo esc_html($initials); ?></span>
            </div>
            <div>
                <?php if ($display_employment_type) : ?>
                    <span class="badge <?php echo esc_attr($badge_classes); ?> rounded py-1 mb-2"><?php echo esc_html($display_employment_type); ?></span>
                <?php endif; ?>
                <h4 class="mb-1"><?php echo esc_html($title); ?></h4>
                <p class="mb-0 text-body"><?php echo esc_html($location); ?></p>
            </div>
        </div>
    </a>
</div>
<!--/column -->

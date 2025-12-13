<?php
/**
 * Template: Vacancies Archive - Style 1 (List with Filters)
 * 
 * Список вакансий с фильтрами, сгруппированных по типам
 */

// Этот шаблон используется для вывода всего контента, включая фильтры
// Поэтому здесь должна быть вся логика

// Проверяем, что есть посты для обработки
if (!have_posts()) {
    return;
}

// Получаем все типы вакансий
$vacancy_types = get_terms(array(
    'taxonomy' => 'vacancy_type',
    'hide_empty' => true,
));

// Получаем все уникальные локации и типы занятости из вакансий
$all_vacancies = get_posts(array(
    'post_type' => 'vacancies',
    'posts_per_page' => -1,
    'post_status' => 'publish',
));

$locations = array();
$employment_types_list = array();
foreach ($all_vacancies as $vacancy) {
    $vacancy_data = get_vacancy_data_array($vacancy->ID);
    if (!empty($vacancy_data['location'])) {
        $locations[] = $vacancy_data['location'];
    }
    if (!empty($vacancy_data['employment_type'])) {
        $employment_types_list[] = $vacancy_data['employment_type'];
    }
}
$locations = array_unique($locations);
sort($locations);
$employment_types_list = array_unique($employment_types_list);
sort($employment_types_list);

// Функция для форматирования типа занятости
function format_employment_type($type) {
    if (empty($type)) {
        return '';
    }
    
    // Форматируем значение: заменяем дефисы на пробелы и делаем первую букву заглавной
    $formatted = ucfirst(str_replace('-', ' ', $type));
    
    // Используем перевод для форматированного значения
    return __($formatted, 'codeweber');
}

// Группируем вакансии по типам
$vacancies_by_type = array();
while (have_posts()) : 
    the_post();
    $post_id = get_the_ID();
    $vacancy_data = get_vacancy_data_array($post_id);
    
    $types = get_the_terms($post_id, 'vacancy_type');
    if ($types && !is_wp_error($types)) {
        foreach ($types as $type) {
            if (!isset($vacancies_by_type[$type->term_id])) {
                $vacancies_by_type[$type->term_id] = array(
                    'term' => $type,
                    'vacancies' => array()
                );
            }
            $vacancies_by_type[$type->term_id]['vacancies'][] = array(
                'post_id' => $post_id,
                'data' => $vacancy_data
            );
        }
    } else {
        // Вакансии без типа
        if (!isset($vacancies_by_type['no-type'])) {
            $vacancies_by_type['no-type'] = array(
                'term' => null,
                'vacancies' => array()
            );
        }
        $vacancies_by_type['no-type']['vacancies'][] = array(
            'post_id' => $post_id,
            'data' => $vacancy_data
        );
    }
endwhile;
?>

<div class="row">
    <div class="col-12">
    <form class="filter-form mb-10 codeweber-filter-form" id="vacancy-filter-form" data-post-type="vacancies" data-template="vacancies_1" data-container=".vacancies-results">
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="form-select-wrapper">
                    <select class="form-select" name="position" id="filter-position" data-filter-name="position" aria-label="<?php esc_attr_e('Position', 'codeweber'); ?>">
                        <option value=""><?php _e('Position', 'codeweber'); ?></option>
                        <?php foreach ($vacancy_types as $type) : ?>
                            <option value="<?php echo esc_attr($type->term_id); ?>"><?php echo esc_html($type->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="form-select-wrapper">
                    <select class="form-select" name="type" id="filter-type" data-filter-name="type" aria-label="<?php esc_attr_e('Type', 'codeweber'); ?>">
                        <option value=""><?php _e('Type', 'codeweber'); ?></option>
                        <?php 
                        foreach ($employment_types_list as $emp_type) : 
                            $label = format_employment_type($emp_type);
                        ?>
                            <option value="<?php echo esc_attr($emp_type); ?>"><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="form-select-wrapper">
                    <select class="form-select" name="location" id="filter-location" data-filter-name="location" aria-label="<?php esc_attr_e('Location', 'codeweber'); ?>">
                        <option value=""><?php _e('Location', 'codeweber'); ?></option>
                        <?php foreach ($locations as $location) : ?>
                            <option value="<?php echo esc_attr($location); ?>"><?php echo esc_html($location); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </form>
    
    <div class="vacancies-results">
    <?php if (!empty($vacancies_by_type)) : ?>
        <?php 
        // Массив цветов для аватаров
        $avatar_colors = array('bg-red', 'bg-green', 'bg-yellow', 'bg-purple', 'bg-orange', 'bg-pink', 'bg-blue');
        $color_index = 0;
        
        foreach ($vacancies_by_type as $type_id => $type_data) : 
            $term = $type_data['term'];
            $vacancies_list = $type_data['vacancies'];
        ?>
            <div class="job-list mb-10" data-type-id="<?php echo esc_attr($type_id); ?>">
                <?php if ($term) : ?>
                    <h3 class="mb-4"><?php echo esc_html($term->name); ?></h3>
                <?php else : ?>
                    <h3 class="mb-4"><?php _e('Other Vacancies', 'codeweber'); ?></h3>
                <?php endif; ?>
                
                <?php foreach ($vacancies_list as $vacancy) : 
                    $post_id = $vacancy['post_id'];
                    $vacancy_data = $vacancy['data'];
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
                    
                    // Получаем цвет для аватара
                    $avatar_color = $avatar_colors[$color_index % count($avatar_colors)];
                    $color_index++;
                    
                    // Тип занятости
                    $employment_type = !empty($vacancy_data['employment_type']) ? $vacancy_data['employment_type'] : '';
                    $display_employment_type = format_employment_type($employment_type);
                    
                    // Локация
                    $location = !empty($vacancy_data['location']) ? $vacancy_data['location'] : '';
                    
                ?>
                    <a href="<?php echo esc_url($link); ?>" class="card mb-4 lift vacancy-item">
                        <div class="card-body p-5">
                            <span class="row justify-content-between align-items-center">
                                <span class="col-md-5 mb-2 mb-md-0 d-flex align-items-center text-body">
                                    <span class="avatar <?php echo esc_attr($avatar_color); ?> text-white w-9 h-9 fs-17 me-3"><?php echo esc_html($initials); ?></span>
                                    <?php echo esc_html($title); ?>
                                </span>
                                <?php if ($display_employment_type) : ?>
                                    <span class="col-5 col-md-3 text-body d-flex align-items-center">
                                        <i class="uil uil-clock me-1"></i>
                                        <?php echo esc_html($display_employment_type); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($location) : ?>
                                    <span class="col-7 col-md-4 col-lg-3 text-body d-flex align-items-center">
                                        <i class="uil uil-location-arrow me-1"></i>
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
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <div class="py-14">
            <p><?php _e('No vacancies found.', 'codeweber'); ?></p>
        </div>
    <?php endif; ?>
    </div>
    <!-- /.vacancies-results -->
</div>
<!-- /column -->
    </div>
    <!-- /column -->
</div>
<!-- /.row -->

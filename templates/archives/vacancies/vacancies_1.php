<?php
/**
 * Template: Vacancies Archive - Style 1 (List with Filters)
 * 
 * List of vacancies with filters, grouped by types
 */

// This template is used to output all content, including filters
// Therefore, all logic should be here

// Check if there are posts to process
if (!have_posts()) {
    return;
}

// Get all vacancy types
$vacancy_types = get_terms(array(
    'taxonomy' => 'vacancy_type',
    'hide_empty' => true,
));

// Get all unique locations and employment types from vacancies
$all_vacancies = get_posts(array(
    'post_type' => 'vacancies',
    'posts_per_page' => -1,
    'post_status' => 'publish',
));

$locations = array();
foreach ($all_vacancies as $vacancy) {
    $vacancy_data = get_vacancy_data_array($vacancy->ID);
    if (!empty($vacancy_data['location'])) {
        $locations[] = $vacancy_data['location'];
    }
}
$locations = array_unique($locations);
sort($locations);

// Group vacancies by types
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
        // Vacancies without type
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

<?php
$archive_form_radius = class_exists('Codeweber_Options') ? Codeweber_Options::style('form-radius') : ' rounded';
$archive_card_radius = class_exists('Codeweber_Options') ? Codeweber_Options::style('card-radius') : '';
?>
<div class="row">
    <div class="col-12">
    <form class="filter-form mb-10 codeweber-filter-form<?php echo esc_attr($archive_form_radius); ?>" id="vacancy-filter-form" data-post-type="vacancies" data-template="vacancies_1" data-container=".vacancies-results">
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="form-select-wrapper">
                    <select class="form-select<?php echo esc_attr($archive_form_radius); ?>" name="vacancy_type" id="filter-vacancy-type" data-filter-name="vacancy_type" aria-label="<?php esc_attr_e('Vacancy Type', 'codeweber'); ?>">
                        <option value=""><?php _e('Vacancy Type', 'codeweber'); ?></option>
                        <?php foreach ($vacancy_types as $type) : ?>
                            <option value="<?php echo esc_attr($type->term_id); ?>"><?php echo esc_html($type->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="form-select-wrapper">
                    <select class="form-select<?php echo esc_attr($archive_form_radius); ?>" name="location" id="filter-location" data-filter-name="location" aria-label="<?php esc_attr_e('Location', 'codeweber'); ?>">
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
        // Array of colors for avatars
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
                    $avatar_color = $avatar_colors[$color_index % count($avatar_colors)];
                    $color_index++;
                    set_query_var('vacancy_list_item_post_id', $vacancy['post_id']);
                    set_query_var('vacancy_list_item_data', $vacancy['data']);
                    set_query_var('vacancy_list_item_avatar_color', $avatar_color);
                    set_query_var('vacancy_list_item_card_radius', $archive_card_radius);
                    get_template_part('templates/post-cards/vacancies/list-item');
                endforeach; ?>
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

<?php
/**
 * Template: Vacancies Archive - Style 3 (Grid with Post Cards)
 * 
 * Сетка карточек вакансий в стиле Amazon job posting
 * 
 * @package Codeweber
 */

$post_id = absint(get_the_ID());
?>

<?php
// Используем шаблон карточки из post-cards
$card_template = locate_template('templates/post-cards/vacancies/card.php');
if ($card_template) {
    include $card_template;
} else {
    // Fallback если шаблон не найден
    ?>
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card shadow-lg lift h-100">
            <div class="card-body p-5">
                <h4 class="mb-3">
                    <a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="link-dark">
                        <?php echo esc_html(get_the_title($post_id)); ?>
                    </a>
                </h4>
                <a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="btn btn-dark rounded-pill w-100">
                    <?php _e('Apply now', 'codeweber'); ?>
                </a>
            </div>
        </div>
    </div>
    <?php
}
?>


<?php
/**
 * Template: Testimonials Archive - Style 2 (Card)
 * 
 * Карточка отзыва в стиле Sandbox с цветными фонами
 * Соответствует: templates/post-cards/testimonials/card.php
 */

$testimonial_data = codeweber_get_testimonial_data(get_the_ID());

if (!$testimonial_data) {
    return;
}

$testimonial_text = !empty($testimonial_data['text']) ? wp_kses_post($testimonial_data['text']) : '';
$author_name = !empty($testimonial_data['author_name']) ? esc_html($testimonial_data['author_name']) : '';
$author_role = !empty($testimonial_data['author_role']) ? esc_html($testimonial_data['author_role']) : '';
$company = !empty($testimonial_data['company']) ? esc_html($testimonial_data['company']) : '';

// Выбираем цвет фона на основе ID поста
$bg_colors = ['bg-pale-yellow', 'bg-pale-red', 'bg-pale-leaf', 'bg-pale-blue'];
$color_index = absint(get_the_ID()) % count($bg_colors);
$bg_color = $bg_colors[$color_index];
$card_radius = getThemeCardImageRadius();
?>

<div class="item col-md-6 col-xl-4">
    <div class="card <?php echo esc_attr($bg_color); ?><?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
        <div class="card-body">
            <blockquote class="icon mb-0">
                <?php if ($testimonial_text) : ?>
                    <p><?php echo $testimonial_text; ?></p>
                <?php endif; ?>
                
                <?php codeweber_testimonial_blockquote_details(get_the_ID(), [
                    'show_company' => !empty($company),
                    'avatar_size' => 'w-12',
                    'avatar_bg' => 'bg-pale-primary',
                    'avatar_text' => 'text-primary',
                ]); ?>
            </blockquote>
        </div>
        <!--/.card-body -->
    </div>
    <!--/.card -->
</div>
<!--/column -->


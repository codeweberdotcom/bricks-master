<?php
/**
 * Template: Offices Archive - Style 3 (Card Grid with Map)
 *
 * Карточка офиса с изображением, адресом, контактами и часами работы
 * Использует: templates/post-cards/offices/card.php
 *
 * @package Codeweber
 */

$post_id = absint(get_the_ID());
$alt_title_val = get_post_meta( $post_id, '_alt_title', true );
?>
<div class="col-md-6 col-lg-4">
    <?php
    // Загружаем post card для офиса
    if (locate_template('templates/post-cards/offices/card.php')) {
        get_template_part('templates/post-cards/offices/card');
    } else {
        // Fallback: простая карточка если post card не найден
        $title = $alt_title_val ? wp_kses_post( $alt_title_val ) : esc_html( get_the_title( $post_id ) );
        $link = get_permalink($post_id);
        $city = '';
        $town_terms = wp_get_post_terms($post_id, 'towns', array('fields' => 'names'));
        if (!empty($town_terms) && !is_wp_error($town_terms)) {
            $city = $town_terms[0];
        }
        $fallback_card_radius = class_exists('Codeweber_Options') ? Codeweber_Options::style('card-radius') : '';
        $fallback_btn_style   = class_exists('Codeweber_Options') ? Codeweber_Options::style('button') : ' rounded-pill';
        ?>
        <div class="card shadow shadow-lg lift h-100<?php echo $fallback_card_radius ? ' ' . esc_attr($fallback_card_radius) : ''; ?>">
            <div class="card-body">
                <h4 class="mb-3">
                    <a href="<?php echo esc_url($link); ?>" class="link-dark">
                        <?php echo $title; ?>
                    </a>
                </h4>
                <?php if ($city) : ?>
                    <p class="text-muted mb-3"><?php echo esc_html($city); ?></p>
                <?php endif; ?>
                <a href="<?php echo esc_url($link); ?>" class="btn btn-outline-primary<?php echo esc_attr($fallback_btn_style); ?> w-100 has-ripple">
                    <?php _e('View Details', 'codeweber'); ?>
                </a>
            </div>
        </div>
        <?php
    }
    ?>
</div>
<!--/column -->

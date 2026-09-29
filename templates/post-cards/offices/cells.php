<?php
/**
 * Template: Office Post Card — Cell
 *
 * A single cell of a bordered grid: just the office name. The frame around the
 * grid and the cell borders come from the Post Grid block, which switches the
 * column classes and wraps the whole grid in a card for this template — see the
 * 'cells' branch in the block's render.php.
 *
 * Variables available from cw_render_post_card():
 *   $post      WP_Post object
 *   $post_data array  — id, title, link, image_url, image_alt, ...
 *
 * @package Codeweber
 */

defined( 'ABSPATH' ) || exit;

$title = $post_data['title'];
$link  = $post_data['link'];

$show_title       = isset( $display_settings['show_title'] ) ? (bool) $display_settings['show_title'] : true;
$title_class_attr = isset( $display_settings['title_class'] ) && $display_settings['title_class']
	? ' ' . $display_settings['title_class']
	: '';

if ( ! $show_title || '' === trim( (string) $title ) ) {
	return;
}
?>
<a href="<?php echo esc_url( $link ); ?>" class="link-dark<?php echo esc_attr( $title_class_attr ); ?>">
	<?php echo esc_html( $title ); ?>
</a>

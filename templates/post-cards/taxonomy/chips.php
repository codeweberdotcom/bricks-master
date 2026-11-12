<?php
/**
 * Card Template: Chips
 * Description: Compact pill-style link with the term name and optional count. Laid out in a flowing row instead of a column grid.
 * Supports: title
 * Order: 31
 *
 * Variables injected by cw_render_term_card():
 * @var WP_Term $term
 * @var string  $term_link
 * @var array   $display       cw_get_post_card_display_settings() result
 * @var array   $template_args parsed template args
 */

if ( ! isset( $term ) || ! $term ) {
	return;
}

if ( empty( $display['show_title'] ) ) {
	return;
}

$template_args = wp_parse_args( $template_args ?? [], [
	'show_term_count' => false,
] );

$title = $term->name;
if ( ! empty( $display['title_length'] ) && mb_strlen( $title ) > $display['title_length'] ) {
	$title = mb_substr( $title, 0, $display['title_length'] ) . '…';
}

$btn_radius = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button' ) : ' rounded-pill';
?>
<a href="<?php echo esc_url( $term_link ); ?>" class="btn btn-sm btn-soft-primary<?php echo esc_attr( $btn_radius ); ?>">
	<?php echo esc_html( $title ); ?>
	<?php if ( ! empty( $template_args['show_term_count'] ) ) : ?>
		<span class="ms-1 opacity-75">(<?php echo (int) $term->count; ?>)</span>
	<?php endif; ?>
</a>

<?php
/**
 * Template: Cells Taxonomy Term Card
 *
 * A single cell of a bordered grid: just the term name. The frame around the
 * grid and the cell borders come from the Post Grid block, which switches the
 * column classes and wraps the whole grid in a card for this template — see the
 * 'cells' handling in the block's render.php.
 *
 * Variables injected by cw_render_term_card():
 * @var WP_Term $term
 * @var string  $term_link
 * @var string  $image_url
 * @var string  $image_alt
 * @var array   $display       cw_get_post_card_display_settings() result
 * @var array   $template_args parsed template args
 */

if ( ! isset( $term ) || ! $term ) {
	return;
}

$template_args = wp_parse_args( $template_args ?? [], [
	'show_term_count' => false,
] );

if ( empty( $display['show_title'] ) ) {
	return;
}

// A town with no office attached has nothing to show on the map — leave it out
// of the grid. An empty card is dropped by the block before it is wrapped.
if ( 'towns' === $term->taxonomy
	&& function_exists( 'codeweber_towns_with_offices' )
	&& ! in_array( (int) $term->term_id, codeweber_towns_with_offices(), true )
) {
	return;
}

if ( ! empty( $display['use_html_title'] ) && ! empty( $display['html_title'] ) ) {
	$title = $display['html_title'];
} else {
	$title = $term->name;
	if ( ! empty( $display['title_length'] ) && mb_strlen( $title ) > $display['title_length'] ) {
		$title = mb_substr( $title, 0, $display['title_length'] ) . '…';
	}
}

$title_class = ! empty( $display['title_class'] ) ? ' ' . esc_attr( $display['title_class'] ) : '';

// Towns open the offices map panel filtered to that city instead of the term
// archive. The panel's city filter matches on the full term name, so the raw
// name goes into the attribute — not the possibly shortened $title.
$map_attrs = '';
if ( 'towns' === $term->taxonomy ) {
	$map_attrs = ' data-office-map data-office-city="' . esc_attr( $term->name ) . '"';
}
?>
<a href="<?php echo esc_url( $term_link ); ?>" class="link-dark<?php echo $title_class; ?>"<?php echo $map_attrs; ?>>
	<?php echo empty( $display['use_html_title'] ) ? esc_html( $title ) : wp_kses_post( $title ); ?>
	<?php if ( ! empty( $template_args['show_term_count'] ) ) : ?>
		<span class="text-muted ms-1">(<?php echo (int) $term->count; ?>)</span>
	<?php endif; ?>
</a>

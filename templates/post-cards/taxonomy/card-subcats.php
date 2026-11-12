<?php
/**
 * Card Template: Card with Child List
 * Description: Term card with an optional image, the term title and a list of its child terms (first 6, then a "View all" link).
 * Supports: title, image
 * Order: 32
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
	'border_radius'   => Codeweber_Options::style( 'card-radius' ) ?: 'rounded',
	'enable_lift'     => false,
	'show_term_count' => false,
	'hide_empty'      => true,
	'exclude'         => [],
	'children_limit'  => 6,
] );

if ( ! empty( $display['use_html_title'] ) && ! empty( $display['html_title'] ) ) {
	$title = $display['html_title'];
} else {
	$title = $term->name;
	if ( ! empty( $display['title_length'] ) && mb_strlen( $title ) > $display['title_length'] ) {
		$title = mb_substr( $title, 0, $display['title_length'] ) . '…';
	}
}

$title_tag   = isset( $display['title_tag'] ) ? sanitize_html_class( $display['title_tag'] ) : 'h3';
$title_class = ! empty( $display['title_class'] ) ? esc_attr( $display['title_class'] ) : 'h5 mb-3';

// Child terms. Product categories go through the shop helper so counts/ordering match WooCommerce.
if ( 'product_cat' === $term->taxonomy && function_exists( 'cw_subcats_get_terms' ) ) {
	$children = cw_subcats_get_terms( $term->term_id, [
		'hide_empty' => (bool) $template_args['hide_empty'],
		'exclude'    => (array) $template_args['exclude'],
	] );
} else {
	$children = get_terms( [
		'taxonomy'   => $term->taxonomy,
		'parent'     => $term->term_id,
		'hide_empty' => (bool) $template_args['hide_empty'],
		'exclude'    => (array) $template_args['exclude'],
	] );
	$children = is_wp_error( $children ) ? [] : $children;
}

$limit      = max( 1, (int) $template_args['children_limit'] );
$has_more   = count( $children ) > $limit;
$children   = array_slice( $children, 0, $limit );
$card_class = 'card h-100 overflow-hidden ' . $template_args['border_radius'] . ( ! empty( $template_args['enable_lift'] ) ? ' lift' : '' );
?>

<article class="<?php echo esc_attr( trim( $card_class ) ); ?>">
	<?php if ( $image_url ) : ?>
		<figure class="ratio ratio-16x9 m-0 overflow-hidden">
			<a href="<?php echo esc_url( $term_link ); ?>">
				<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" class="w-100 h-100 object-fit-cover" loading="lazy">
			</a>
		</figure>
	<?php endif; ?>
	<?php // text-dark (not link-dark): the card has a white background and text-inverse on <main> forces light links, which only an !important utility beats. ?>
	<div class="card-body p-5 text-dark">
		<?php if ( ! empty( $display['show_title'] ) ) : ?>
			<<?php echo esc_attr( $title_tag ); ?> class="<?php echo esc_attr( $title_class ); ?>">
				<a href="<?php echo esc_url( $term_link ); ?>" class="text-dark">
					<?php echo empty( $display['use_html_title'] ) ? esc_html( $title ) : wp_kses_post( $title ); ?>
					<?php if ( ! empty( $template_args['show_term_count'] ) ) : ?>
						<span class="text-muted fs-16">(<?php echo (int) $term->count; ?>)</span>
					<?php endif; ?>
				</a>
			</<?php echo esc_attr( $title_tag ); ?>>
		<?php endif; ?>

		<?php if ( $children ) : ?>
			<ul class="list-unstyled mb-0">
				<?php foreach ( $children as $child ) : ?>
					<?php $child_link = get_term_link( $child ); ?>
					<?php if ( is_wp_error( $child_link ) ) { continue; } ?>
					<li class="mb-1">
						<a href="<?php echo esc_url( $child_link ); ?>" class="text-dark"><?php echo esc_html( $child->name ); ?></a>
					</li>
				<?php endforeach; ?>
				<?php if ( $has_more ) : ?>
					<li class="mt-3">
						<a href="<?php echo esc_url( $term_link ); ?>" class="hover more text-primary"><?php esc_html_e( 'View all', 'codeweber' ); ?></a>
					</li>
				<?php endif; ?>
			</ul>
		<?php endif; ?>
	</div>
</article>

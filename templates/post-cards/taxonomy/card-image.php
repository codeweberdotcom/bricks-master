<?php
/**
 * Card Template: Card with Image
 * Description: Term card: image on top, title and item count below. Equal-height cards, placeholder when the term has no image.
 * Supports: title, image
 * Order: 30
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
$title_class = ! empty( $display['title_class'] ) ? esc_attr( $display['title_class'] ) : 'h5 mb-1';

$card_class = 'card h-100 overflow-hidden ' . $template_args['border_radius'] . ( ! empty( $template_args['enable_lift'] ) ? ' lift' : '' );
$img_src    = $image_url ?: get_template_directory_uri() . '/dist/assets/img/image-placeholder.jpg';
?>

<article class="<?php echo esc_attr( trim( $card_class ) ); ?>">
	<?php // text-dark: the card has a white background, so its text must not follow a page-level text-inverse. ?>
	<a href="<?php echo esc_url( $term_link ); ?>" class="d-flex flex-column h-100 text-dark text-decoration-none">
		<figure class="ratio ratio-4x3 m-0 overflow-hidden">
			<img src="<?php echo esc_url( $img_src ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" class="w-100 h-100 object-fit-cover" loading="lazy">
		</figure>
		<div class="card-body p-5 text-center">
			<?php if ( ! empty( $display['show_title'] ) ) : ?>
				<<?php echo esc_attr( $title_tag ); ?> class="<?php echo esc_attr( $title_class ); ?>">
					<?php echo empty( $display['use_html_title'] ) ? esc_html( $title ) : wp_kses_post( $title ); ?>
				</<?php echo esc_attr( $title_tag ); ?>>
			<?php endif; ?>
			<?php if ( ! empty( $template_args['show_term_count'] ) ) : ?>
				<p class="small text-muted mb-0">
					<?php
					echo esc_html( sprintf(
						/* translators: %d: number of items */
						_n( '%d item', '%d items', $term->count, 'codeweber' ),
						$term->count
					) );
					?>
				</p>
			<?php endif; ?>
		</div>
	</a>
</article>

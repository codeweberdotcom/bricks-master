<?php
/**
 * Template: Overlay-5 Primary Taxonomy Term Card
 *
 * Primary-colored overlay on hover; identical to overlay-5 but adds .color
 * to the figure class so the overlay uses the primary brand colour.
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
	'hover_classes'   => 'overlay overlay-5 color',
	'border_radius'   => Codeweber_Options::style( 'card-radius' ) ?: 'rounded',
	'show_figcaption' => true,
	'enable_lift'     => false,
	'show_card_arrow' => true,
	'card_read_more'  => 'none',
	'show_term_count' => false,
] );

$article_class = ! empty( $template_args['enable_lift'] ) ? 'lift' : '';

$read_more_labels = [
	'view'    => __( 'View',       'codeweber' ),
	'more'    => __( 'Read more',  'codeweber' ),
	'read'    => __( 'Read',       'codeweber' ),
	'go'      => __( 'Go',         'codeweber' ),
	'open'    => __( 'Open',       'codeweber' ),
	'details' => __( 'Details',    'codeweber' ),
	'learn'   => __( 'Learn more', 'codeweber' ),
	'buy'     => __( 'Buy',        'codeweber' ),
	'order'   => __( 'Order',      'codeweber' ),
];
$read_more_label = $read_more_labels[ $template_args['card_read_more'] ] ?? '';

$title = $term->name;
if ( $display['title_length'] > 0 && mb_strlen( $title ) > $display['title_length'] ) {
	$title = mb_substr( $title, 0, $display['title_length'] ) . '…';
}

$excerpt = '';
if ( ! empty( $display['show_excerpt'] ) && ! empty( $term->description ) ) {
	$desc = strip_tags( $term->description );
	if ( $display['excerpt_length'] > 0 ) {
		$excerpt = wp_trim_words( $desc, $display['excerpt_length'], '…' );
	} else {
		$excerpt = $desc;
	}
}

$title_tag   = isset( $display['title_tag'] ) ? sanitize_html_class( $display['title_tag'] ) : 'h2';
$title_class = ! empty( $display['title_class'] ) ? esc_attr( $display['title_class'] ) : 'h5 mb-0';
?>

<article<?php echo $article_class ? ' class="' . esc_attr( $article_class ) . '"' : ''; ?>>
	<?php if ( $image_url ) : ?>
		<figure class="<?php echo esc_attr( $template_args['hover_classes'] . ' ' . $template_args['border_radius'] ); ?> card-interactive">
			<a href="<?php echo esc_url( $term_link ); ?>">
				<div class="bottom-overlay post-meta fs-16 position-absolute zindex-1 d-flex flex-column h-100 w-100 p-5">
					<?php if ( $display['show_title'] ) : ?>
						<div class="mt-auto">
							<<?php echo esc_attr( $title_tag ); ?> class="<?php echo esc_attr( $title_class ); ?>">
								<?php echo esc_html( $title ); ?>
							</<?php echo esc_attr( $title_tag ); ?>>
						</div>
					<?php endif; ?>
				</div>
				<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" class="<?php echo esc_attr( $template_args['border_radius'] ); ?>">
			</a>

			<?php if ( $template_args['show_figcaption'] && ( $excerpt || $read_more_label || $template_args['show_term_count'] ) ) : ?>
				<figcaption class="p-5">
					<div class="post-body h-100 d-flex flex-column from-left justify-content-end">
						<?php if ( $excerpt ) : ?>
							<p class="mb-3"><?php echo esc_html( $excerpt ); ?></p>
						<?php endif; ?>
						<?php if ( $template_args['show_term_count'] ) : ?>
							<p class="mb-3 small opacity-75">
								<?php echo esc_html( sprintf(
									/* translators: %d: number of posts */
									_n( '%d item', '%d items', $term->count, 'codeweber' ),
									$term->count
								) ); ?>
							</p>
						<?php endif; ?>
						<?php if ( $read_more_label ) : ?>
							<span class="hover more me-4"><?php echo esc_html( $read_more_label ); ?></span>
						<?php endif; ?>
					</div>
				</figcaption>
			<?php endif; ?>

			<?php if ( ! empty( $template_args['show_card_arrow'] ) ) : ?>
				<div class="hover_card_button_hide position-absolute top-0 end-0 p-5 zindex-10">
					<i class="fs-25 uil uil-arrow-right lh-1"></i>
				</div>
			<?php endif; ?>
		</figure>
	<?php else : ?>
		<figure class="<?php echo esc_attr( $template_args['border_radius'] ); ?> card-interactive overflow-hidden h-100">
			<a href="<?php echo esc_url( $term_link ); ?>" class="d-block text-decoration-none h-100">
				<div
					class="bg-light d-flex align-items-center justify-content-center <?php echo esc_attr( $template_args['border_radius'] ); ?>"
					style="min-height: 220px;"
				>
					<div class="text-center text-muted px-4 py-5">
						<i class="uil uil-image d-block mb-3 opacity-50" style="font-size: 3rem; line-height: 1;"></i>
						<?php if ( $display['show_title'] ) : ?>
							<<?php echo esc_attr( $title_tag ); ?> class="<?php echo esc_attr( $title_class ); ?> mb-0">
								<?php echo esc_html( $title ); ?>
							</<?php echo esc_attr( $title_tag ); ?>>
						<?php endif; ?>
					</div>
				</div>
			</a>
		</figure>
	<?php endif; ?>
</article>

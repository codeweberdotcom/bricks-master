<?php
/**
 * Template: Overlay-5 Primary Project Card
 *
 * Image card with primary overlay on hover.
 * Figcaption shows architect and address meta fields.
 *
 * @param array $post_data
 * @param array $display_settings
 * @param array $template_args
 */

if ( ! isset( $post_data ) || ! $post_data ) {
	return;
}

$display       = cw_get_post_card_display_settings( $display_settings ?? [] );
$template_args = wp_parse_args( $template_args ?? [], [
	'hover_classes'   => 'overlay overlay-5 color',
	'border_radius'   => Codeweber_Options::style( 'card-radius' ) ?: 'rounded',
	'show_figcaption' => true,
	'enable_lift'     => false,
	'show_card_arrow' => true,
	'card_read_more'  => 'none',
] );

$article_class = ! empty( $template_args['enable_lift'] ) ? 'lift' : '';

$read_more_labels = [
	'view' => __( 'View', 'codeweber' ),
	'more' => __( 'Read more', 'codeweber' ),
	'read' => __( 'Read', 'codeweber' ),
];
$read_more_label = isset( $read_more_labels[ $template_args['card_read_more'] ] )
	? $read_more_labels[ $template_args['card_read_more'] ]
	: '';

$title = $post_data['title'];
if ( $display['title_length'] > 0 && mb_strlen( $title ) > $display['title_length'] ) {
	$title = mb_substr( $title, 0, $display['title_length'] ) . '...';
}

$title_tag = isset( $display['title_tag'] ) ? sanitize_html_class( $display['title_tag'] ) : 'h2';
if ( ! empty( $display['title_class'] ) ) {
	$title_class = esc_attr( $display['title_class'] );
} else {
	$title_class = 'h5 mb-0';
}

$date_badge   = get_the_date( 'd M Y', $post_data['id'] );
$title_description = get_post_meta( $post_data['id'], 'main_information_title_description', true );
$architector       = get_post_meta( $post_data['id'], 'main_information_architector', true );
$address           = get_post_meta( $post_data['id'], 'main_information_address', true );
?>

<article<?php echo $article_class ? ' class="' . esc_attr( $article_class ) . '"' : ''; ?>>
	<?php if ( $post_data['image_url'] ) : ?>
		<figure class="<?php echo esc_attr( $template_args['hover_classes'] . ' ' . $template_args['border_radius'] ); ?> card-interactive">
			<a href="<?php echo esc_url( $post_data['link'] ); ?>">
				<div class="bottom-overlay post-meta fs-16 position-absolute zindex-1 d-flex flex-column h-100 w-100 p-5">
					<?php if ( $display['show_date'] ) : ?>
						<div class="d-flex w-100 justify-content-end">
							<span class="post-date badge bg-primary rounded-pill"><?php echo esc_html( $date_badge ); ?></span>
						</div>
					<?php endif; ?>

					<?php if ( $display['show_title'] ) : ?>
						<div class="mt-auto">
							<<?php echo esc_attr( $title_tag ); ?> class="<?php echo esc_attr( trim( $title_class ) ); ?>">
								<?php echo esc_html( $title ); ?>
							</<?php echo esc_attr( $title_tag ); ?>>
						</div>
					<?php endif; ?>
				</div>
				<img src="<?php echo esc_url( $post_data['image_url'] ); ?>" alt="<?php echo esc_attr( $post_data['image_alt'] ); ?>" class="<?php echo esc_attr( $template_args['border_radius'] ); ?>">
			</a>

			<?php if ( $template_args['show_figcaption'] && ! empty( $display['show_excerpt'] ) ) : ?>
				<figcaption class="p-5">
					<div class="post-body h-100 d-flex flex-column from-left justify-content-end">
						<?php if ( $title_description ) : ?>
							<p class="mb-2"><?php echo esc_html( $title_description ); ?></p>
						<?php endif; ?>
						<?php if ( $architector ) : ?>
							<p class="mb-1">
								<span><?php esc_html_e( 'Architect', 'codeweber' ); ?></span><br>
								<?php echo esc_html( $architector ); ?>
							</p>
						<?php endif; ?>
						<?php if ( $address ) : ?>
							<p class="mb-0">
								<span><?php esc_html_e( 'Address', 'codeweber' ); ?></span><br>
								<?php echo esc_html( $address ); ?>
							</p>
						<?php endif; ?>
						<?php if ( $read_more_label ) : ?>
							<span class="hover more me-4 mt-3"><?php echo esc_html( $read_more_label ); ?></span>
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
	<?php endif; ?>
</article>

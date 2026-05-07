<?php
/**
 * Template: Services Archive — Style 2 (Overlay-5 grid, 4 columns)
 *
 * 4-column grid (col-12 col-md-3). Dark overlay on hover;
 * title pinned to bottom, short description slides in from left.
 *
 * @package Codeweber
 */

defined( 'ABSPATH' ) || exit;

$card_radius = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : 'rounded';
$grid_gap    = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'grid-gap' ) : 'g-6';
$placeholder = get_template_directory_uri() . '/dist/assets/img/image-placeholder.jpg';

$all_services = new WP_Query( [
	'post_type'      => 'services',
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	'orderby'        => 'menu_order date',
	'order'          => 'ASC',
] );
?>

<section class="wrapper">
	<div class="container py-14 py-md-16">

		<?php if ( $all_services->have_posts() ) : ?>
		<div class="row <?php echo esc_attr( $grid_gap ); ?>">
			<?php while ( $all_services->have_posts() ) : $all_services->the_post();
				$post_id    = get_the_ID();
				$alt_title  = get_post_meta( $post_id, '_alt_title', true );
				$thumb_id   = get_post_thumbnail_id( $post_id );
				$short_desc = get_post_meta( $post_id, '_service_short_description', true );
			?>
			<div class="col-12 col-md-3">
				<figure class="overlay overlay-5 <?php echo esc_attr( $card_radius ); ?> card-interactive mb-0">
					<a href="<?php the_permalink(); ?>">
						<div class="bottom-overlay post-meta fs-16 position-absolute zindex-1 d-flex flex-column h-100 w-100 p-5">
							<div class="mt-auto">
								<h3 class="h5 text-white mb-0"><?php echo $alt_title ? wp_kses_post( $alt_title ) : esc_html( get_the_title() ); ?></h3>
							</div>
						</div>
						<?php if ( $thumb_id ) : ?>
							<?php echo wp_get_attachment_image( $thumb_id, 'cw_square_md', false, [
								'class' => 'w-100 ' . esc_attr( $card_radius ),
								'alt'   => esc_attr( get_the_title() ),
							] ); ?>
						<?php else : ?>
							<img src="<?php echo esc_url( $placeholder ); ?>" alt="" class="w-100 <?php echo esc_attr( $card_radius ); ?>">
						<?php endif; ?>
					</a>

					<figcaption class="p-5">
						<div class="post-body h-100 d-flex flex-column from-left justify-content-end">
							<?php if ( $short_desc ) : ?>
								<p class="mb-3"><?php echo esc_html( $short_desc ); ?></p>
							<?php endif; ?>
							<span class="hover more me-4"><?php esc_html_e( 'More details', 'codeweber' ); ?></span>
						</div>
					</figcaption>

					<div class="hover_card_button_hide position-absolute top-0 end-0 p-5 zindex-10">
						<i class="fs-25 uil uil-arrow-right lh-1"></i>
					</div>
				</figure>
			</div>
			<!--/column -->
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
		<!--/.row -->

		<?php else : ?>
		<p class="text-muted"><?php esc_html_e( 'No services found.', 'codeweber' ); ?></p>
		<?php endif; ?>

	</div>
	<!--/.container -->
</section>

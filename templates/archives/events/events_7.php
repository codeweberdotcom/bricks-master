<?php
/**
 * Archive template: Events — view 7 (column grid, image top, whole card clickable)
 *
 * 3 cards per row on desktop, 2 on tablet, 1 on mobile.
 *
 * @package Codeweber
 */

$card_radius = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : '';
$grid_gap    = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'grid-gap' ) : 'gx-md-8 gy-6';
?>

<section id="content-wrapper" class="wrapper bg-light">
	<div class="container py-10 py-md-12">

		<?php if ( have_posts() ) : ?>
			<div class="row <?php echo esc_attr( $grid_gap ); ?> mb-5">
				<?php while ( have_posts() ) : the_post();
					$post_id    = get_the_ID();
					$date_start = get_post_meta( $post_id, '_event_date_start', true );
					$date_end   = get_post_meta( $post_id, '_event_date_end', true );
					$location   = get_post_meta( $post_id, '_event_location', true );
					$price      = get_post_meta( $post_id, '_event_price', true );
					$reg_status = codeweber_events_get_registration_status( $post_id );
					$formats    = get_the_terms( $post_id, 'event_format' );

					$thumbnail_id = get_post_thumbnail_id( $post_id );
					$image_url    = $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'codeweber_event_400-267' ) : '';
					if ( empty( $image_url ) ) {
						$image_url = get_template_directory_uri() . '/dist/assets/img/photos/about6.jpg';
					}

					$status_class = [
						'open'                => 'badge bg-soft-green text-green rounded-pill',
						'not_open_yet'        => 'badge bg-soft-yellow text-yellow rounded-pill',
						'registration_closed' => 'badge bg-soft-ash text-muted rounded-pill',
						'no_seats'            => 'badge bg-soft-red text-red rounded-pill',
						'event_ended'         => 'badge bg-soft-ash text-muted rounded-pill',
					][ $reg_status['status'] ] ?? '';
				?>
				<div class="col-12 col-sm-6 col-lg-4">
					<a href="<?php the_permalink(); ?>" class="card h-100 lift overflow-hidden text-inherit text-decoration-none<?php echo $card_radius ? ' ' . esc_attr( $card_radius ) : ''; ?>">

						<figure>
							<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" class="w-100 object-fit-cover">
						</figure>

						<div class="card-body d-flex flex-column p-4">

							<?php if ( $formats && ! is_wp_error( $formats ) ) : ?>
								<div class="mb-2">
									<?php foreach ( $formats as $fmt ) : ?>
										<span class="badge bg-soft-primary text-primary rounded-pill me-1">
											<?php echo esc_html( $fmt->name ); ?>
										</span>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>

							<?php if ( $date_start ) : ?>
								<p class="mb-1 text-muted">
									<i class="uil uil-calendar-alt me-1"></i>
									<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $date_start ) ) ); ?>
									<?php if ( $date_end && $date_end !== $date_start ) : ?>
										— <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $date_end ) ) ); ?>
									<?php endif; ?>
								</p>
							<?php endif; ?>

							<h5 class="card-title mb-2"><?php the_title(); ?></h5>

							<div class="mt-auto pt-3 d-flex flex-wrap align-items-center gap-2">

								<?php if ( $status_class && $reg_status['label'] ) : ?>
									<span class="event-status-badge <?php echo esc_attr( $status_class ); ?>">
										<?php echo esc_html( $reg_status['label'] ); ?>
									</span>
								<?php endif; ?>

								<?php if ( $location ) : ?>
									<span class="text-muted">
										<i class="uil uil-map-marker-alt me-1"></i><?php echo esc_html( $location ); ?>
									</span>
								<?php endif; ?>

								<?php if ( $price ) : ?>
									<span class="">
										<i class="uil uil-tag-alt me-1"></i><?php echo esc_html( $price ); ?>
									</span>
								<?php endif; ?>

							</div>
						</div><!-- /.card-body -->
					</a><!-- /.card -->
				</div>
				<?php endwhile; ?>
			</div>

			<?php codeweber_pagination(); ?>

		<?php else : ?>
			<p class="text-muted"><?php esc_html_e( 'No events found.', 'codeweber' ); ?></p>
		<?php endif; ?>

	</div>
</section>

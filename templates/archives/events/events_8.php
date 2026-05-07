<?php
/**
 * Archive template: Events — view 8 (horizontal card, square 1:1 photo, no filter)
 *
 * Same as events_6 but uses codeweber_event_600-600 (square crop) instead of 400-267.
 *
 * @package Codeweber
 */

$card_radius  = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : '';
$grid_gap     = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'grid-gap' ) : 'gx-md-8 gy-6';

?>

<section id="content-wrapper" class="wrapper">
	<div class="container py-10 py-md-12">

		<?php if ( have_posts() ) : ?>
			<div class="row <?php echo esc_attr( $grid_gap ); ?> mb-5">
				<?php while ( have_posts() ) : the_post();
					$post_id    = get_the_ID();
					$alt_title  = get_post_meta( $post_id, '_alt_title', true );
					$date_start = get_post_meta( $post_id, '_event_date_start', true );
					$date_end   = get_post_meta( $post_id, '_event_date_end', true );
					$location   = get_post_meta( $post_id, '_event_location', true );
					$price      = get_post_meta( $post_id, '_event_price', true );
					$reg_status = codeweber_events_get_registration_status( $post_id );
					$formats    = get_the_terms( $post_id, 'event_format' );

					$thumbnail_id = get_post_thumbnail_id( $post_id );
					$image_url    = $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'cw_square_lg' ) : '';
					if ( empty( $image_url ) ) {
						$image_url = get_template_directory_uri() . '/dist/assets/img/photos/about6.jpg';
					}

					$status_class = [
						'open'                 => 'badge bg-soft-green text-green rounded-pill',
						'not_open_yet'         => 'badge bg-soft-yellow text-yellow rounded-pill',
						'registration_closed'  => 'badge bg-soft-ash text-muted rounded-pill',
						'no_seats'             => 'badge bg-soft-red text-red rounded-pill',
						'event_ended'          => 'badge bg-soft-ash text-muted rounded-pill',
					][ $reg_status['status'] ] ?? '';
				?>
				<div class="col-12">
					<a href="<?php the_permalink(); ?>" class="card card-interactive lift overflow-hidden text-inherit text-decoration-none<?php echo $card_radius ? ' ' . esc_attr( $card_radius ) : ''; ?>">
						<div class="row g-0 h-100">
							<div class="col-3">
								<figure class="mb-0 h-100">
									<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" class="w-100 h-100 object-fit-cover">
								</figure>
							</div>
							<div class="col-9">
								<div class="card-body position-relative">
							<?php if ( $date_start ) : ?>
								<p class="mb-1 text-muted">
									<i class="uil uil-calendar-alt me-1"></i>
									<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $date_start ) ) ); ?>
									<?php if ( $date_end && $date_end !== $date_start ) : ?>
										— <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $date_end ) ) ); ?>
									<?php endif; ?>
								</p>
							<?php endif; ?>
							<h2 class="mb-3 display-6"><?php echo $alt_title ? wp_kses_post( $alt_title ) : esc_html( get_the_title() ); ?></h2>
							<?php if ( $status_class && $reg_status['label'] ) : ?>
								<p class="mb-3">
									<span class="event-status-badge <?php echo esc_attr( $status_class ); ?>">
										<?php echo esc_html( $reg_status['label'] ); ?>
									</span>
								</p>
							<?php endif; ?>
							<ul class="list-unstyled cc-2 mb-0">
								<?php if ( $location ) : ?>
									<li class="mb-1 d-flex align-items-center">
										<i class="uil uil-map-marker-alt text-primary me-2"></i>
										<span><?php echo esc_html( $location ); ?></span>
									</li>
								<?php endif; ?>
								<?php if ( $formats && ! is_wp_error( $formats ) ) : ?>
									<li class="mb-1 d-flex align-items-center">
										<i class="uil uil-presentation text-primary me-2"></i>
										<span><?php echo esc_html( implode( ', ', wp_list_pluck( $formats, 'name' ) ) ); ?></span>
									</li>
								<?php endif; ?>
								<?php if ( $price ) : ?>
									<li class="mb-1 d-flex align-items-center">
										<i class="uil uil-tag-alt text-primary me-2"></i>
										<span><?php echo esc_html( $price ); ?></span>
									</li>
								<?php endif; ?>
							</ul>
							<div class="hover_card_button position-absolute p-7 top-0 end-0">
								<i class="fs-25 uil uil-arrow-right lh-1"></i>
							</div>
								</div><!-- /.card-body -->
							</div><!-- /.col-9 -->
						</div><!-- /.row -->
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

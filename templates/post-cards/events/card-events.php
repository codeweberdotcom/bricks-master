<?php
/**
 * Post Card: Event
 *
 * Used in archive-events.php list/grid view and any block that renders events.
 *
 * @package Codeweber
 */

$post_id    = get_the_ID();
$date_start = get_post_meta( $post_id, '_event_date_start', true );
$location   = get_post_meta( $post_id, '_event_location', true );
$price      = get_post_meta( $post_id, '_event_price', true );
$reg_status = codeweber_events_get_registration_status( $post_id );
$formats     = get_the_terms( $post_id, 'event_format' );
$card_radius = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : '';
?>

<div class="col-md-6 col-lg-4">
	<a href="<?php the_permalink(); ?>" class="card event-card h-100 lift overflow-hidden text-inherit text-decoration-none<?php echo $card_radius ? ' ' . esc_attr( $card_radius ) : ''; ?>">

		<?php if ( has_post_thumbnail() ) : ?>
			<figure>
				<?php the_post_thumbnail( 'cw_card_3x2', [ 'class' => 'w-100 object-fit-cover' ] ); ?>
			</figure>
		<?php endif; ?>

		<div class="card-body d-flex flex-column p-4">

			<?php // Formats ?>
			<?php if ( $formats && ! is_wp_error( $formats ) ) : ?>
				<div class="mb-2">
					<?php foreach ( $formats as $fmt ) : ?>
						<span class="badge bg-soft-primary text-primary rounded-pill event-format-badge me-1">
							<?php echo esc_html( $fmt->name ); ?>
						</span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php // Date ?>
			<?php if ( $date_start ) : ?>
				<p class="event-card-date mb-1">
					<i class="uil uil-calendar-alt me-1"></i>
					<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $date_start ) ) ); ?>
				</p>
			<?php endif; ?>

			<?php // Title ?>
			<h5 class="card-title mb-2"><?php the_title(); ?></h5>

			<?php // Excerpt ?>
			<?php if ( has_excerpt() ) : ?>
				<p class="card-text text-muted mb-3"><?php echo wp_trim_words( get_the_excerpt(), 18 ); ?></p>
			<?php endif; ?>

			<div class="mt-auto d-flex justify-content-between align-items-center flex-wrap gap-2">

				<?php // Price ?>
				<?php if ( $price ) : ?>
					<span class="event-card-price"><?php echo esc_html( $price ); ?></span>
				<?php endif; ?>

				<?php // Location ?>
				<?php if ( $location ) : ?>
					<span class="event-card-location text-muted">
						<i class="uil uil-map-marker me-1"></i><?php echo esc_html( $location ); ?>
					</span>
				<?php endif; ?>

				<?php // Status badge ?>
				<?php
				$badge_map = [
					'open'                => 'badge bg-soft-green text-green rounded-pill',
					'not_open_yet'        => 'badge bg-soft-yellow text-yellow rounded-pill',
					'registration_closed' => 'badge bg-soft-ash text-muted rounded-pill',
					'no_seats'            => 'badge bg-soft-red text-red rounded-pill',
					'event_ended'         => 'badge bg-soft-ash text-muted rounded-pill',
				];
				$badge_class = $badge_map[ $reg_status['status'] ] ?? '';
				if ( $badge_class && $reg_status['label'] ) : ?>
					<span class="event-status-badge <?php echo esc_attr( $badge_class ); ?>">
						<?php echo esc_html( $reg_status['label'] ); ?>
					</span>
				<?php endif; ?>
			</div>
		</div>
	</a>
</div>

<?php
/**
 * Post Card: Event — default variant (compact horizontal)
 *
 * @package Codeweber
 */

$post_id    = get_the_ID();
$date_start = get_post_meta( $post_id, '_event_date_start', true );
$location   = get_post_meta( $post_id, '_event_location', true );
$price      = get_post_meta( $post_id, '_event_price', true );
$reg_status = codeweber_events_get_registration_status( $post_id );
?>

<div class="d-flex gap-3 align-items-start event-card-compact py-3 border-bottom">

	<?php if ( has_post_thumbnail() ) : ?>
		<a href="<?php the_permalink(); ?>" class="flex-shrink-0">
			<?php the_post_thumbnail( 'thumbnail', [ 'class' => 'rounded', 'style' => 'width:80px;height:60px;object-fit:cover;' ] ); ?>
		</a>
	<?php endif; ?>

	<div class="flex-grow-1">
		<h6 class="mb-1">
			<a href="<?php the_permalink(); ?>" class="text-reset text-decoration-none"><?php the_title(); ?></a>
		</h6>
		<?php if ( $date_start ) : ?>
			<small class="text-muted d-block">
				<i class="uil uil-calendar-alt me-1"></i>
				<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $date_start ) ) ); ?>
			</small>
		<?php endif; ?>
		<?php if ( $location ) : ?>
			<small class="text-muted d-block">
				<i class="uil uil-map-marker me-1"></i>
				<?php echo esc_html( $location ); ?>
			</small>
		<?php endif; ?>
	</div>

	<?php if ( $price ) : ?>
		<span class="flex-shrink-0 event-card-price small fw-semibold"><?php echo esc_html( $price ); ?></span>
	<?php endif; ?>

</div>

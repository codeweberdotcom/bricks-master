<?php
/**
 * Single template: Event (default)
 *
 * Loaded by single.php via get_template_part('templates/singles/events/default').
 * Runs inside single.php's while loop (after the_post()).
 * Sidebar content is rendered by codeweber_sidebar_widget_events() in functions/sidebars.php.
 *
 * @package Codeweber
 */

$event_id          = get_the_ID();
$gallery_ids       = codeweber_get_event_gallery_ids( $event_id );
$video             = codeweber_events_get_video_glightbox( $event_id );
$report_text       = get_post_meta( $event_id, '_event_report_text', true );
$date_end          = get_post_meta( $event_id, '_event_date_end', true );
$is_ended          = $date_end && strtotime( $date_end ) < current_time( 'timestamp' );
$show_report       = $is_ended && ! empty( $report_text );
$sidebar_disable_image = get_post_meta( $event_id, '_event_sidebar_disable_image', true );
$card_radius       = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : '';
$button_style      = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button', '' ) : '';
?>

<?php // Featured image (shown in content when sidebar image is disabled) ?>
<?php if ( has_post_thumbnail() && $sidebar_disable_image ) : ?>
	<figure class="mb-6 overflow-hidden<?php echo $card_radius ? ' ' . esc_attr( $card_radius ) : ' rounded'; ?>">
		<?php the_post_thumbnail( 'codeweber_event_1070-668', [ 'class' => 'img-fluid w-100' ] ); ?>
	</figure>
<?php endif; ?>

<?php // Content (or post-event report if available) ?>
<div class="post-content">
	<?php if ( $show_report ) : ?>
		<?php echo wp_kses_post( $report_text ); ?>
	<?php else : ?>
		<?php the_content(); ?>
	<?php endif; ?>
</div>

<?php // ---- Gallery ------------------------------------------ ?>
<?php if ( ! empty( $gallery_ids ) ) : ?>
	<div class="mt-8">
		<h4 class="mb-4"><?php esc_html_e( 'Gallery', 'codeweber' ); ?></h4>
		<div class="swiper-container swiper-thumbs-container dots-over" data-margin="10" data-dots="false" data-nav="true" data-thumbs="true">
			<div class="swiper">
				<div class="swiper-wrapper">
					<?php foreach ( $gallery_ids as $i => $aid ) :
						$_gal_url  = wp_get_attachment_image_url( $aid, 'codeweber_event_1070-668' );
						$_gal_full = wp_get_attachment_image_url( $aid, 'full' );
						$_gal_alt  = get_post_field( 'post_excerpt', $aid ) ?: get_the_title();
						if ( ! $_gal_url ) continue;
					?>
					<div class="swiper-slide">
						<figure class="overlay overlay-4 hover-scale hover-plus<?php echo $card_radius ? ' ' . esc_attr( $card_radius ) : ' rounded'; ?>">
							<a href="<?php echo esc_url( $_gal_full ); ?>" data-glightbox data-gallery="event-gallery-<?php echo esc_attr( $event_id ); ?>">
								<img src="<?php echo esc_url( $_gal_url ); ?>" class="w-100" alt="<?php echo esc_attr( $_gal_alt ); ?>">
								<span class="hover-icon text-white"><svg fill="currentColor" viewBox="0 0 256 256" xmlns="http://www.w3.org/2000/svg"><path d="M220,128a4.0002,4.0002,0,0,1-4,4H132v84a4,4,0,0,1-8,0V132H40a4,4,0,0,1,0-8h84V40a4,4,0,0,1,8,0v84h84A4.0002,4.0002,0,0,1,220,128Z"></path></svg></span>
							</a>
						</figure>
					</div>
					<!--/.swiper-slide -->
					<?php endforeach; ?>
				</div>
				<!--/.swiper-wrapper -->
			</div>
			<!-- /.swiper -->
			<div class="swiper swiper-thumbs">
				<div class="swiper-wrapper">
					<?php foreach ( $gallery_ids as $i => $aid ) :
						$_th_url = wp_get_attachment_image_url( $aid, 'codeweber_event_140-88' );
						if ( ! $_th_url ) continue;
					?>
					<div class="swiper-slide">
						<img src="<?php echo esc_url( $_th_url ); ?>" class="<?php echo $card_radius ? esc_attr( $card_radius ) : 'rounded'; ?>" alt="">
					</div>
					<?php endforeach; ?>
				</div>
				<!--/.swiper-wrapper -->
			</div>
			<!-- /.swiper -->
		</div>
		<!-- /.swiper-container -->
	</div>
<?php endif; ?>

<?php // ---- Video -------------------------------------------- ?>
<?php if ( $video ) :
	if ( $video['inline_html'] ) echo $video['inline_html'];
?>
	<div class="mt-8">
		<h4 class="mb-4"><?php esc_html_e( 'Video', 'codeweber' ); ?></h4>
		<a href="<?php echo esc_url( $video['href'] ); ?>"
			data-glightbox="<?php echo esc_attr( $video['glightbox'] ); ?>"
			class="btn btn-outline-primary btn-icon btn-icon-start<?php echo class_exists( 'Codeweber_Options' ) ? esc_attr( Codeweber_Options::style( 'button' ) ) : ' rounded-pill'; ?>">
			<i class="uil uil-play-circle"></i>
			<?php esc_html_e( 'Watch video', 'codeweber' ); ?>
		</a>
	</div>
<?php endif; ?>

<?php // ---- Share -------------------------------------------- ?>
<div class="event-share-row d-flex align-items-center gap-3 flex-wrap">
	<?php
	$share_button_class = 'btn btn-red btn-sm btn-icon btn-icon-start dropdown-toggle mb-0 me-0 has-ripple';
	if ( class_exists( 'Codeweber_Options' ) ) {
		$share_button_class .= Codeweber_Options::style( 'button' );
	}
	codeweber_share_page( [ 'region' => 'auto', 'button_class' => $share_button_class ] );
	?>
</div>

<?php
/**
 * Single template: Event (default)
 *
 * Loaded by single.php via get_template_part('templates/singles/events/default').
 * Runs inside single.php's while loop (after the_post()).
 *
 * @package Codeweber
 */

$event_id          = get_the_ID();
$date_start        = get_post_meta( $event_id, '_event_date_start', true );
$date_end          = get_post_meta( $event_id, '_event_date_end', true );
$location          = get_post_meta( $event_id, '_event_location', true );
$address           = get_post_meta( $event_id, '_event_address', true );
$organizer         = get_post_meta( $event_id, '_event_organizer', true );
$price             = get_post_meta( $event_id, '_event_price', true );
$external_reg_url  = get_post_meta( $event_id, '_event_registration_url', true );
$max_participants  = (int) get_post_meta( $event_id, '_event_max_participants', true );
$reg_status        = codeweber_events_get_registration_status( $event_id );
$gallery_ids       = codeweber_get_event_gallery_ids( $event_id );
$video             = codeweber_events_get_video_glightbox( $event_id );
$formats           = get_the_terms( $event_id, 'event_format' );
$categories        = get_the_terms( $event_id, 'event_category' );
$report_text       = get_post_meta( $event_id, '_event_report_text', true );
$is_ended          = $date_end && strtotime( $date_end ) < current_time( 'timestamp' );
$show_report       = $is_ended && ! empty( $report_text );
$settings          = get_option( 'codeweber_events_settings', [] );
$show_seats_taken  = ( $settings['show_seats_taken'] ?? '1' ) === '1';
$show_seats_left   = ( $settings['show_seats_left'] ?? '1' ) === '1';
$show_seats_bar    = ( $settings['show_seats_progress'] ?? '1' ) === '1';

$registered_count  = $max_participants > 0 ? codeweber_events_get_registration_count( $event_id ) : 0;
$seats_left        = $max_participants > 0 ? max( 0, $max_participants - $registered_count ) : null;
$seats_pct         = ( $max_participants > 0 ) ? min( 100, round( ( $registered_count / $max_participants ) * 100 ) ) : 0;
?>

<div class="row gx-lg-8 gx-xl-12">

	<?php // ============================================================ ?>
	<?php // Main content column ?>
	<?php // ============================================================ ?>
	<div class="col-lg-8">

		<?php // Breadcrumb ?>
		<nav aria-label="breadcrumb" class="mb-4">
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'codeweber' ); ?></a></li>
				<li class="breadcrumb-item"><a href="<?php echo esc_url( get_post_type_archive_link( 'events' ) ); ?>"><?php esc_html_e( 'Events', 'codeweber' ); ?></a></li>
				<li class="breadcrumb-item active" aria-current="page"><?php the_title(); ?></li>
			</ol>
		</nav>

		<?php // Categories ?>
		<?php if ( $categories && ! is_wp_error( $categories ) ) : ?>
			<div class="mb-3">
				<?php foreach ( $categories as $cat ) : ?>
					<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" class="badge bg-soft-primary text-primary rounded-pill me-1">
						<?php echo esc_html( $cat->name ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php // Title ?>
		<h1 class="display-4 mb-4"><?php the_title(); ?></h1>

		<?php // Featured image ?>
		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="mb-6 rounded overflow-hidden">
				<?php the_post_thumbnail( 'codeweber_event_900-450', [ 'class' => 'img-fluid w-100' ] ); ?>
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
				<div id="eventGalleryCarousel" class="carousel slide event-gallery-carousel" data-bs-ride="false">
					<div class="carousel-indicators">
						<?php foreach ( $gallery_ids as $i => $aid ) : ?>
							<button type="button" data-bs-target="#eventGalleryCarousel"
								data-bs-slide-to="<?php echo esc_attr( $i ); ?>"
								<?php echo $i === 0 ? 'class="active" aria-current="true"' : ''; ?>
								aria-label="<?php echo esc_attr( sprintf( __( 'Slide %d', 'codeweber' ), $i + 1 ) ); ?>">
							</button>
						<?php endforeach; ?>
					</div>
					<div class="carousel-inner rounded">
						<?php foreach ( $gallery_ids as $i => $aid ) :
							$img_url  = wp_get_attachment_image_url( $aid, 'codeweber_event_900-450' );
							$img_full = wp_get_attachment_image_url( $aid, 'full' );
							if ( ! $img_url ) continue;
						?>
							<div class="carousel-item <?php echo $i === 0 ? 'active' : ''; ?>">
								<a href="<?php echo esc_url( $img_full ); ?>" data-glightbox="image" data-gallery="event-gallery-<?php echo esc_attr( $event_id ); ?>">
									<img src="<?php echo esc_url( $img_url ); ?>" class="d-block w-100" alt="<?php echo esc_attr( get_post_field( 'post_excerpt', $aid ) ?: get_the_title() ); ?>">
								</a>
							</div>
						<?php endforeach; ?>
					</div>
					<?php if ( count( $gallery_ids ) > 1 ) : ?>
						<button class="carousel-control-prev" type="button" data-bs-target="#eventGalleryCarousel" data-bs-slide="prev">
							<span class="carousel-control-prev-icon" aria-hidden="true"></span>
							<span class="visually-hidden"><?php esc_html_e( 'Previous', 'codeweber' ); ?></span>
						</button>
						<button class="carousel-control-next" type="button" data-bs-target="#eventGalleryCarousel" data-bs-slide="next">
							<span class="carousel-control-next-icon" aria-hidden="true"></span>
							<span class="visually-hidden"><?php esc_html_e( 'Next', 'codeweber' ); ?></span>
						</button>
					<?php endif; ?>
				</div>

				<?php // Thumbnails with GLightbox ?>
				<div class="event-gallery-thumbs">
					<?php foreach ( $gallery_ids as $i => $aid ) :
						$thumb = wp_get_attachment_image_url( $aid, 'thumbnail' );
						$full  = wp_get_attachment_image_url( $aid, 'full' );
						if ( ! $thumb ) continue;
					?>
						<a href="<?php echo esc_url( $full ); ?>" data-glightbox="image" data-gallery="event-gallery-<?php echo esc_attr( $event_id ); ?>">
							<img src="<?php echo esc_url( $thumb ); ?>" alt="">
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<?php // ---- Video -------------------------------------------- ?>
		<?php if ( $video ) :
			if ( $video['inline_html'] ) echo $video['inline_html'];
		?>
			<div class="mt-8">
				<h4 class="mb-4"><?php esc_html_e( 'Video', 'codeweber' ); ?></h4>
				<a href="<?php echo esc_url( $video['href'] ); ?>"
					data-glightbox="<?php echo esc_attr( $video['type'] ); ?>"
					class="btn btn-outline-primary btn-icon btn-icon-start rounded-pill">
					<i class="uil uil-play-circle"></i>
					<?php esc_html_e( 'Watch video', 'codeweber' ); ?>
				</a>
			</div>
		<?php endif; ?>

		<?php // ---- Share -------------------------------------------- ?>
		<div class="event-share-row d-flex align-items-center gap-3 flex-wrap">
			<span class="text-muted small"><?php esc_html_e( 'Share:', 'codeweber' ); ?></span>
			<?php codeweber_share_page( [ 'region' => 'auto' ] ); ?>
		</div>

	</div>

	<?php // ============================================================ ?>
	<?php // Sidebar / Info column ?>
	<?php // ============================================================ ?>
	<div class="col-lg-4">
		<div class="card shadow-sm sticky-top" style="top:80px;">
			<div class="card-body p-5">

				<?php // Status badge ?>
				<?php
				$badge_map = [
					'open'                => 'status-open',
					'not_open_yet'        => 'status-not-open-yet',
					'registration_closed' => 'status-closed',
					'no_seats'            => 'status-no-seats',
					'event_ended'         => 'status-ended',
				];
				$badge_class = $badge_map[ $reg_status['status'] ] ?? '';
				if ( $badge_class && $reg_status['label'] ) :
				?>
					<span class="event-status-badge <?php echo esc_attr( $badge_class ); ?> mb-4 d-inline-block">
						<?php echo esc_html( $reg_status['label'] ); ?>
					</span>
				<?php endif; ?>

				<?php // Meta info ?>
				<ul class="single-event-meta list-unstyled mb-5">
					<?php if ( $date_start ) : ?>
						<li class="event-meta-item">
							<i class="uil uil-calendar-alt"></i>
							<div>
								<strong><?php esc_html_e( 'Start', 'codeweber' ); ?></strong><br>
								<span><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $date_start ) ) ); ?></span>
							</div>
						</li>
					<?php endif; ?>
					<?php if ( $date_end ) : ?>
						<li class="event-meta-item">
							<i class="uil uil-calendar-alt"></i>
							<div>
								<strong><?php esc_html_e( 'End', 'codeweber' ); ?></strong><br>
								<span><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $date_end ) ) ); ?></span>
							</div>
						</li>
					<?php endif; ?>
					<?php if ( $location ) : ?>
						<li class="event-meta-item">
							<i class="uil uil-map-marker"></i>
							<div>
								<strong><?php esc_html_e( 'Location', 'codeweber' ); ?></strong><br>
								<span><?php echo esc_html( $location ); ?></span>
								<?php if ( $address ) : ?>
									<br><small class="text-muted"><?php echo esc_html( $address ); ?></small>
								<?php endif; ?>
							</div>
						</li>
					<?php endif; ?>
					<?php if ( $organizer ) : ?>
						<li class="event-meta-item">
							<i class="uil uil-user"></i>
							<div>
								<strong><?php esc_html_e( 'Organizer', 'codeweber' ); ?></strong><br>
								<span><?php echo esc_html( $organizer ); ?></span>
							</div>
						</li>
					<?php endif; ?>
					<?php if ( $price ) : ?>
						<li class="event-meta-item">
							<i class="uil uil-tag-alt"></i>
							<div>
								<strong><?php esc_html_e( 'Price', 'codeweber' ); ?></strong><br>
								<span class="event-card-price"><?php echo esc_html( $price ); ?></span>
							</div>
						</li>
					<?php endif; ?>
					<?php if ( $formats && ! is_wp_error( $formats ) ) : ?>
						<li class="event-meta-item">
							<i class="uil uil-desktop"></i>
							<div>
								<strong><?php esc_html_e( 'Format', 'codeweber' ); ?></strong><br>
								<?php foreach ( $formats as $fmt ) : ?>
									<span class="badge bg-soft-ash text-navy me-1"><?php echo esc_html( $fmt->name ); ?></span>
								<?php endforeach; ?>
							</div>
						</li>
					<?php endif; ?>
				</ul>

				<?php // Seats counter
				// Progress bar + "seats left" require max_participants > 0.
				// "X registered" shows for any event that has registrations.
				$show_bar        = $max_participants > 0 && $show_seats_bar;
				$show_left       = $max_participants > 0 && $show_seats_left && $seats_left !== null;
				$show_taken      = $show_seats_taken && $registered_count > 0;
				$show_any_seats  = $show_bar || $show_left || $show_taken;
				?>
				<?php if ( $show_any_seats ) : ?>
					<div class="event-seats-counter mb-5"
						data-event-seats-counter="<?php echo esc_attr( $event_id ); ?>"
						data-seats-taken="<?php echo esc_attr( $registered_count ); ?>"
						data-seats-max="<?php echo esc_attr( $max_participants ); ?>">

						<?php if ( $show_bar ) : ?>
							<div class="progress event-seats-progress mb-2" role="progressbar"
								aria-valuenow="<?php echo esc_attr( $seats_pct ); ?>" aria-valuemin="0" aria-valuemax="100">
								<div class="progress-bar bg-primary event-seats-bar"
									style="width: <?php echo esc_attr( $seats_pct ); ?>%"></div>
							</div>
						<?php endif; ?>

						<p class="event-seats-label mb-0 small">
							<?php if ( $show_taken ) : ?>
								<?php printf(
									/* translators: %s: count */
									esc_html__( '%s registered', 'codeweber' ),
									'<strong><span class="event-seats-taken">' . esc_html( $registered_count ) . '</span></strong>'
								); ?>
							<?php endif; ?>
							<?php if ( $show_taken && $show_left ) : ?>
								&nbsp;&middot;&nbsp;
							<?php endif; ?>
							<?php if ( $show_left ) : ?>
								<?php printf(
									/* translators: %s: count */
									esc_html__( '%s seats left', 'codeweber' ),
									'<strong><span class="event-seats-left">' . esc_html( $seats_left ) . '</span></strong>'
								); ?>
							<?php endif; ?>
						</p>
					</div>
				<?php endif; ?>

				<?php // Registration button / form ?>
				<?php if ( $reg_status['status'] === 'external' && $external_reg_url ) : ?>
					<a href="<?php echo esc_url( $external_reg_url ); ?>" target="_blank" rel="noopener"
						class="btn btn-primary rounded-pill w-100 mb-2">
						<?php echo esc_html( $reg_status['label'] ); ?>
					</a>

				<?php elseif ( $reg_status['show_form'] ) : ?>
					<?php $nonce = wp_create_nonce( 'codeweber_event_register' ); ?>
					<div class="event-registration-wrap">
						<form class="event-registration-form needs-validation"
							data-event-id="<?php echo esc_attr( $event_id ); ?>"
							novalidate>

							<input type="hidden" name="event_id" value="<?php echo esc_attr( $event_id ); ?>">
							<input type="hidden" name="event_reg_nonce" value="<?php echo esc_attr( $nonce ); ?>">
							<input type="text" name="event_reg_honeypot" class="d-none" tabindex="-1" autocomplete="off">

							<div class="mb-3">
								<input type="text" name="reg_name" class="form-control"
									placeholder="<?php esc_attr_e( 'Your name *', 'codeweber' ); ?>"
									required>
								<div class="invalid-feedback"><?php esc_html_e( 'Please enter your name.', 'codeweber' ); ?></div>
							</div>

							<div class="mb-3">
								<input type="email" name="reg_email" class="form-control"
									placeholder="<?php esc_attr_e( 'Email *', 'codeweber' ); ?>"
									required>
								<div class="invalid-feedback"><?php esc_html_e( 'Please enter a valid email.', 'codeweber' ); ?></div>
							</div>

							<div class="mb-3">
								<input type="tel" name="reg_phone" class="form-control"
									placeholder="<?php esc_attr_e( 'Phone', 'codeweber' ); ?>">
							</div>

							<div class="mb-4">
								<textarea name="reg_message" class="form-control" rows="3"
									placeholder="<?php esc_attr_e( 'Comment (optional)', 'codeweber' ); ?>"></textarea>
							</div>

							<div class="event-reg-form-messages mb-3"></div>

							<button type="submit" class="btn btn-primary rounded-pill w-100"
								data-loading-text="<?php esc_attr_e( 'Sending...', 'codeweber' ); ?>">
								<?php echo esc_html( $reg_status['label'] ); ?>
							</button>
						</form>
					</div>

				<?php elseif ( $reg_status['label'] ) : ?>
					<div class="alert alert-light text-center mb-0 rounded-pill small fw-semibold">
						<?php echo esc_html( $reg_status['label'] ); ?>
					</div>
				<?php endif; ?>

			</div>
		</div>
	</div>

</div>

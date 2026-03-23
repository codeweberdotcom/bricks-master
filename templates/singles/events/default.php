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
$reg_open          = get_post_meta( $event_id, '_event_registration_open', true );
$reg_close         = get_post_meta( $event_id, '_event_registration_close', true );
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

$registered_count       = codeweber_events_get_registration_count( $event_id );
$seats_left             = $max_participants > 0 ? max( 0, $max_participants - $registered_count ) : null;
$seats_pct              = ( $max_participants > 0 ) ? min( 100, round( ( $registered_count / $max_participants ) * 100 ) ) : 0;
$sidebar_hide_author    = get_post_meta( $event_id, '_event_sidebar_hide_author', true );
$sidebar_disable_image  = get_post_meta( $event_id, '_event_sidebar_disable_image', true );
$hide_seats_counter     = get_post_meta( $event_id, '_event_hide_seats_counter', true );
$event_show_map         = get_post_meta( $event_id, '_event_show_map', true );
$event_latitude         = get_post_meta( $event_id, '_event_latitude', true );
$event_longitude        = get_post_meta( $event_id, '_event_longitude', true );
$event_zoom             = get_post_meta( $event_id, '_event_zoom', true );
$event_yandex_address   = get_post_meta( $event_id, '_event_yandex_address', true );
$card_radius      = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : '';
$button_style     = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button', '' ) : '';
$form_radius      = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'form-radius' ) : '';
$phone_mask       = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::get( 'opt_phone_mask', '' ) : '';
$reg_form_title   = get_post_meta( $event_id, '_event_reg_form_title', true );
$reg_button_label = get_post_meta( $event_id, '_event_reg_button_label', true );
?>

<div class="row gx-lg-8 gx-xl-12">

	<?php // ============================================================ ?>
	<?php // Main content column ?>
	<?php // ============================================================ ?>
	<div class="col-lg-8">

		<?php // Featured image ?>
		<?php if ( has_post_thumbnail() && $sidebar_disable_image ) : ?>
			<figure class="mb-6 overflow-hidden<?php echo $card_radius ? ' ' . esc_attr( $card_radius ) : ' rounded'; ?>">
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
				<div class="swiper-container swiper-thumbs-container dots-over" data-margin="10" data-dots="false" data-nav="true" data-thumbs="true">
					<div class="swiper">
						<div class="swiper-wrapper">
							<?php foreach ( $gallery_ids as $i => $aid ) :
								$_gal_url  = wp_get_attachment_image_url( $aid, 'codeweber_event_900-450' );
								$_gal_full = wp_get_attachment_image_url( $aid, 'full' );
								$_gal_alt  = get_post_field( 'post_excerpt', $aid ) ?: get_the_title();
								if ( ! $_gal_url ) continue;
							?>
							<div class="swiper-slide">
								<figure class="<?php echo $card_radius ? esc_attr( $card_radius ) : 'rounded'; ?>">
									<img src="<?php echo esc_url( $_gal_url ); ?>" class="w-100" alt="<?php echo esc_attr( $_gal_alt ); ?>">
									<a class="item-link" href="<?php echo esc_url( $_gal_full ); ?>" data-glightbox data-gallery="event-gallery-<?php echo esc_attr( $event_id ); ?>">
										<i class="uil uil-focus-add"></i>
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
								$_th_url = wp_get_attachment_image_url( $aid, 'thumbnail' );
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
					class="btn btn-outline-primary btn-icon btn-icon-start rounded-pill">
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

	</div>

	<?php // ============================================================ ?>
	<?php // Sidebar / Info column ?>
	<?php // ============================================================ ?>
	<div class="col-lg-4">
		<?php

		$badge_map = [
			'open'                => 'badge bg-soft-green text-green rounded-pill',
			'not_open_yet'        => 'badge bg-soft-yellow text-yellow rounded-pill',
			'registration_closed' => 'badge bg-soft-ash text-muted rounded-pill',
			'no_seats'            => 'badge bg-soft-red text-red rounded-pill',
			'event_ended'         => 'badge bg-soft-ash text-muted rounded-pill',
		];
		$badge_class = $badge_map[ $reg_status['status'] ] ?? '';

		// Countdown timer
		$countdown_until = null;
		$countdown_label = '';
		$now = current_time( 'timestamp' );
		if ( $reg_status['status'] === 'open' && $reg_close ) {
			$ts = strtotime( $reg_close );
			if ( $ts && $ts > $now ) {
				$countdown_until = $ts;
				$countdown_label = __( 'Registration closes:', 'codeweber' );
				$countdown_date  = date_i18n( get_option( 'date_format' ), $ts );
			}
		} elseif ( $reg_status['status'] === 'not_open_yet' && $reg_open ) {
			$ts = strtotime( $reg_open );
			if ( $ts && $ts > $now ) {
				$countdown_until = $ts;
				$countdown_label = __( 'Registration opens:', 'codeweber' );
				$countdown_date  = date_i18n( get_option( 'date_format' ), $ts );
			}
		}

		// Seats counter flags
		$show_bar       = $max_participants > 0 && $show_seats_bar;
		$show_left      = $max_participants > 0 && $show_seats_left && $seats_left !== null;
		$show_taken     = $show_seats_taken && $registered_count > 0;
		$show_any_seats = $show_bar || $show_left || $show_taken;
		?>
		<div class="card<?php echo $card_radius ? ' ' . esc_attr( $card_radius ) : ''; ?>">

			<?php if ( ! $sidebar_disable_image && has_post_thumbnail() ) :
				$sidebar_img = get_the_post_thumbnail_url( $event_id, 'codeweber_vacancy' );
				if ( $sidebar_img ) : ?>
				<figure<?php echo $card_radius ? ' class="' . esc_attr( $card_radius ) . '"' : ''; ?>>
					<img src="<?php echo esc_url( $sidebar_img ); ?>"
						alt="<?php echo esc_attr( get_the_title() ); ?>" class="img-fluid">
				</figure>
			<?php endif; endif; ?>

			<div class="card-body">

				<?php if ( ( $categories && ! is_wp_error( $categories ) ) || ( $formats && ! is_wp_error( $formats ) ) ) : ?>
					<div class="mb-3">
						<?php if ( $categories && ! is_wp_error( $categories ) ) : ?>
							<?php foreach ( $categories as $cat ) : ?>
								<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" class="badge bg-soft-primary text-primary rounded-pill me-1">
									<?php echo esc_html( $cat->name ); ?>
								</a>
							<?php endforeach; ?>
						<?php endif; ?>
						<?php if ( $formats && ! is_wp_error( $formats ) ) : ?>
							<?php foreach ( $formats as $fmt ) : ?>
								<span class="badge bg-soft-ash text-navy me-1"><?php echo esc_html( $fmt->name ); ?></span>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<div class="mb-6">
					<h3 class="mb-2"><?php esc_html_e( 'Event Details', 'codeweber' ); ?></h3>

					<?php if ( $countdown_until ) : ?>
					<p class="small text-muted mb-2">
						<?php echo esc_html( $countdown_label ); ?>
						<span class="fw-semibold text-reset"><?php echo esc_html( $countdown_date ); ?></span>
					</p>
					<div class="event-countdown d-flex align-items-start gap-1 mb-4"
						data-countdown="<?php echo esc_attr( $countdown_until ); ?>">
						<div class="event-countdown-unit">
							<div class="fw-bold lh-1 event-countdown-days">0</div>
							<div class="text-muted" style="font-size:0.6875rem;"><?php esc_html_e( 'days', 'codeweber' ); ?></div>
						</div>
						<div class="fw-bold lh-1 px-1">:</div>
						<div class="event-countdown-unit">
							<div class="fw-bold lh-1 event-countdown-hours">00</div>
							<div class="text-muted" style="font-size:0.6875rem;"><?php esc_html_e( 'hrs', 'codeweber' ); ?></div>
						</div>
						<div class="fw-bold lh-1 px-1">:</div>
						<div class="event-countdown-unit">
							<div class="fw-bold lh-1 event-countdown-mins">00</div>
							<div class="text-muted" style="font-size:0.6875rem;"><?php esc_html_e( 'min', 'codeweber' ); ?></div>
						</div>
						<div class="fw-bold lh-1 px-1">:</div>
						<div class="event-countdown-unit">
							<div class="fw-bold lh-1 event-countdown-secs">00</div>
							<div class="text-muted" style="font-size:0.6875rem;"><?php esc_html_e( 'sec', 'codeweber' ); ?></div>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( $date_start ) : ?>
						<p class="mb-1 d-flex align-items-baseline">
							<i class="uil uil-calendar-alt text-primary me-2 flex-shrink-0"></i>
							<span>
								<span class="text-muted me-1"><?php esc_html_e( 'Start:', 'codeweber' ); ?></span>
								<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $date_start ) ) ); ?>
							</span>
						</p>
					<?php endif; ?>

					<?php if ( $date_end ) : ?>
						<p class="mb-1 d-flex align-items-baseline">
							<i class="uil uil-calendar-alt text-primary me-2 flex-shrink-0"></i>
							<span>
								<span class="text-muted me-1"><?php esc_html_e( 'End:', 'codeweber' ); ?></span>
								<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $date_end ) ) ); ?>
							</span>
						</p>
					<?php endif; ?>

					<?php if ( $location ) : ?>
						<p class="mb-1 d-flex align-items-baseline">
							<i class="uil uil-map-marker-alt text-primary me-2 flex-shrink-0"></i>
							<span><?php echo esc_html( $address ? $location . ', ' . $address : $location ); ?></span>
						</p>
					<?php endif; ?>

					<?php if ( $organizer ) : ?>
						<p class="mb-1 d-flex align-items-center">
							<i class="uil uil-user text-primary me-2 flex-shrink-0"></i>
							<span><?php echo esc_html( $organizer ); ?></span>
						</p>
					<?php endif; ?>

					<?php if ( $price ) : ?>
						<p class="mb-1 d-flex align-items-center">
							<i class="uil uil-money-stack text-primary me-2 flex-shrink-0"></i>
							<span class="fw-semibold"><?php echo esc_html( $price ); ?></span>
						</p>
					<?php endif; ?>

					</div>

				<?php
				if ( ! $sidebar_hide_author ) :
					$evt_user_id  = get_the_author_meta( 'ID' );
					$evt_avatar   = get_user_meta( $evt_user_id, 'avatar_id', true );
					if ( empty( $evt_avatar ) ) $evt_avatar = get_user_meta( $evt_user_id, 'custom_avatar_id', true );
					$evt_job      = get_user_meta( $evt_user_id, 'user_position', true ) ?: __( 'Author', 'codeweber' );
				?>
				<hr class="my-4">
				<div class="author-info d-flex align-items-center">
					<div class="d-flex align-items-center">
						<?php if ( ! empty( $evt_avatar ) ) :
							$evt_avatar_src = wp_get_attachment_image_src( $evt_avatar, 'thumbnail' ); ?>
							<figure class="user-avatar me-3">
								<img class="rounded-circle" alt="<?php the_author_meta( 'display_name' ); ?>"
									src="<?php echo esc_url( $evt_avatar_src[0] ); ?>">
							</figure>
						<?php else : ?>
							<figure class="user-avatar me-3">
								<?php echo get_avatar( get_the_author_meta( 'user_email' ), 96, '', '', [ 'class' => 'rounded-circle' ] ); ?>
							</figure>
						<?php endif; ?>
						<div>
							<h6 class="mb-0">
								<a href="<?php echo esc_url( get_author_posts_url( $evt_user_id ) ); ?>" class="link-dark">
									<?php the_author_meta( 'first_name' ); ?> <?php the_author_meta( 'last_name' ); ?>
								</a>
							</h6>
							<span class="post-meta fs-15"><?php echo esc_html( $evt_job ); ?></span>
						</div>
					</div>
				</div>
				<?php endif; ?>

				<?php if ( $show_any_seats && ! $hide_seats_counter ) : ?>
					<hr class="my-4">
					<div class="event-seats-counter"
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
							<?php if ( $show_taken && $show_left ) : ?>&nbsp;&middot;&nbsp;<?php endif; ?>
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

			</div>
		</div>

		<div class="sticky-top" style="top:80px;">
		<?php if ( $reg_status['status'] === 'external' || $reg_status['show_form'] ) : ?>
		<div class="card mt-4<?php echo $card_radius ? ' ' . esc_attr( $card_radius ) : ''; ?>">
			<div class="card-body">

				<?php // Registration button / form ?>
				<?php if ( $reg_status['status'] === 'external' && $external_reg_url ) : ?>
					<a href="<?php echo esc_url( $external_reg_url ); ?>" target="_blank" rel="noopener"
						class="btn btn-primary btn-icon btn-icon-start has-ripple w-100<?php echo esc_attr( $button_style ); ?>">
						<i class="uil uil-external-link-alt"></i>
						<?php echo esc_html( __( ! empty( $reg_button_label ) ? $reg_button_label : 'Register', 'codeweber' ) ); ?>
					</a>

				<?php elseif ( $reg_status['show_form'] ) : ?>
					<?php $nonce = wp_create_nonce( 'codeweber_event_register' ); ?>
					<div class="event-registration-wrap">
						<h3 class="mb-4"><?php echo esc_html( __( ! empty( $reg_form_title ) ? $reg_form_title : 'Register', 'codeweber' ) ); ?></h3>
						<form class="event-registration-form needs-validation"
							data-event-id="<?php echo esc_attr( $event_id ); ?>"
							novalidate>

							<input type="hidden" name="event_id" value="<?php echo esc_attr( $event_id ); ?>">
							<input type="hidden" name="event_reg_nonce" value="<?php echo esc_attr( $nonce ); ?>">
							<input type="text" name="event_reg_honeypot" class="d-none" tabindex="-1" autocomplete="off">

							<div class="mb-3">
								<input type="text" name="reg_name" class="form-control<?php echo esc_attr( $form_radius ); ?>"
									placeholder="<?php esc_attr_e( 'Your name *', 'codeweber' ); ?>"
									required>
								<div class="invalid-feedback"><?php esc_html_e( 'Please enter your name.', 'codeweber' ); ?></div>
							</div>

							<div class="mb-3">
								<input type="email" name="reg_email" class="form-control<?php echo esc_attr( $form_radius ); ?>"
									placeholder="<?php esc_attr_e( 'Email *', 'codeweber' ); ?>"
									required>
								<div class="invalid-feedback"><?php esc_html_e( 'Please enter a valid email.', 'codeweber' ); ?></div>
							</div>

							<div class="mb-3">
								<input type="tel" name="reg_phone" class="form-control<?php echo esc_attr( $form_radius ); ?>"
									placeholder="<?php esc_attr_e( 'Phone', 'codeweber' ); ?>"
									<?php if ( ! empty( $phone_mask ) ) : ?>data-mask="<?php echo esc_attr( $phone_mask ); ?>"<?php endif; ?>>
							</div>
							<div class="mb-4">
								<textarea name="reg_message" class="form-control<?php echo esc_attr( $form_radius ); ?>" rows="3"
									placeholder="<?php esc_attr_e( 'Comment (optional)', 'codeweber' ); ?>"></textarea>
							</div>

							<div class="event-reg-form-messages mb-3"></div>

							<button type="submit"
								class="btn btn-primary has-ripple w-100<?php echo esc_attr( $button_style ); ?>"
								data-loading-text="<?php esc_attr_e( 'Sending...', 'codeweber' ); ?>">
								<?php echo esc_html( __( ! empty( $reg_button_label ) ? $reg_button_label : 'Register', 'codeweber' ) ); ?>
							</button>
						</form>
					</div>

				<?php endif; ?>

			</div>
		</div>
		<?php endif; ?>

		<?php if ( $event_show_map === '1' && ! empty( $event_latitude ) && ! empty( $event_longitude ) && class_exists( 'Codeweber_Yandex_Maps' ) ) :
			$_evt_maps = Codeweber_Yandex_Maps::get_instance();
			if ( $_evt_maps->has_api_key() ) :
				$_evt_zoom    = ! empty( $event_zoom ) ? absint( $event_zoom ) : 15;
				$_evt_map_args = [
					'map_id'                   => 'event-sidebar-map-' . $event_id,
					'center'                   => [ floatval( $event_latitude ), floatval( $event_longitude ) ],
					'zoom'                     => $_evt_zoom,
					'height'                   => 250,
					'width'                    => '100%',
					'controls'                 => [ 'zoomControl' ],
					'enable_scroll_zoom'       => false,
					'show_sidebar'             => false,
					'show_route'               => false,
					'clusterer'                => false,
					'marker_auto_open_balloon' => false,
				];
				$_evt_markers = [[
					'latitude'    => floatval( $event_latitude ),
					'longitude'   => floatval( $event_longitude ),
					'hintContent' => ! empty( $event_yandex_address ) ? $event_yandex_address : '',
				]];
			?>
			<div class="widget mt-4">
				<h3 class="mb-3"><?php esc_html_e( 'On the map', 'codeweber' ); ?></h3>
				<div class="card rounded-0">
					<?php echo $_evt_maps->render_map( $_evt_map_args, $_evt_markers ); ?>
				</div>
			</div>
			<?php
			endif;
		endif; ?>
		</div>
	</div>

</div>
<?php if ( $countdown_until ) : ?>
<script>
(function () {
	var target = <?php echo (int) $countdown_until; ?> * 1000;
	document.querySelectorAll('.event-countdown').forEach(function (el) {
		var dEl = el.querySelector('.event-countdown-days');
		var hEl = el.querySelector('.event-countdown-hours');
		var mEl = el.querySelector('.event-countdown-mins');
		var sEl = el.querySelector('.event-countdown-secs');
		function pad(n) { return String(n).padStart(2, '0'); }
		function tick() {
			var diff = Math.max(0, Math.floor((target - Date.now()) / 1000));
			var d = Math.floor(diff / 86400);
			var h = Math.floor((diff % 86400) / 3600);
			var m = Math.floor((diff % 3600) / 60);
			var s = diff % 60;
			if (dEl) dEl.textContent = d;
			if (hEl) hEl.textContent = pad(h);
			if (mEl) mEl.textContent = pad(m);
			if (sEl) sEl.textContent = pad(s);
			if (diff <= 0) clearInterval(timer);
		}
		tick();
		var timer = setInterval(tick, 1000);
	});
})();
</script>
<?php endif; ?>

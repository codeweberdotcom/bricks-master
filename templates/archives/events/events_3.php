<?php
/**
 * Archive template: Events — view 3 (card/lift)
 *
 * Job-list card pattern: <a class="card mb-4 lift"> with avatar (day number),
 * title + status, format, location, arrow. Category AJAX filter.
 *
 * @package Codeweber
 */

$event_categories = get_terms( [ 'taxonomy' => 'event_category', 'hide_empty' => true ] );
$btn_style        = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button' ) : ' rounded-pill';
$card_radius      = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : '';

// Avatar background colors — cycled by category term_id
$avatar_colors = [ 'red', 'green', 'yellow', 'purple', 'orange', 'pink', 'blue', 'grape', 'violet', 'fuchsia' ];
?>

<section id="content-wrapper" class="wrapper bg-light">
	<div class="container py-10 py-md-12">

		<?php // ---- Category filter ----------------------------------------- ?>
		<?php if ( ! empty( $event_categories ) && ! is_wp_error( $event_categories ) ) : ?>
			<div class="d-flex flex-wrap gap-2 events-category-filters mb-6">
				<button type="button" data-cat-id="0"
					class="btn btn-sm btn-soft-primary has-ripple<?php echo esc_attr( $btn_style ); ?> <?php echo ! is_tax( 'event_category' ) ? 'active' : ''; ?>">
					<?php esc_html_e( 'All', 'codeweber' ); ?>
				</button>
				<?php foreach ( $event_categories as $cat ) : ?>
					<button type="button" data-cat-id="<?php echo esc_attr( $cat->term_id ); ?>"
						class="btn btn-sm btn-soft-primary has-ripple<?php echo esc_attr( $btn_style ); ?> <?php echo is_tax( 'event_category', $cat->term_id ) ? 'active' : ''; ?>">
						<?php echo esc_html( $cat->name ); ?>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php // ---- Cards --------------------------------------------------- ?>
		<div id="events-table-results">
			<?php if ( have_posts() ) : ?>

				<?php while ( have_posts() ) : the_post();
					$date_start = get_post_meta( get_the_ID(), '_event_date_start', true );
					$date_end   = get_post_meta( get_the_ID(), '_event_date_end', true );
					$location   = get_post_meta( get_the_ID(), '_event_location', true );
					$price      = get_post_meta( get_the_ID(), '_event_price', true );
					$reg_status = codeweber_events_get_registration_status( get_the_ID() );
					$formats    = get_the_terms( get_the_ID(), 'event_format' );
					$cats       = get_the_terms( get_the_ID(), 'event_category' );

					// Avatar color — pick by first category term_id
					$cat_index    = ( $cats && ! is_wp_error( $cats ) ) ? ( $cats[0]->term_id % count( $avatar_colors ) ) : 0;
					$avatar_color = $avatar_colors[ $cat_index ];

					// Avatar label — day number, or "?" if no date
					$avatar_label = $date_start ? date_i18n( 'j', strtotime( $date_start ) ) : '?';

					// Month label shown next to avatar
					$month_label = $date_start ? date_i18n( 'M', strtotime( $date_start ) ) : '';

					// Registration status badge
					$status_class = [
						'open'                 => 'badge bg-soft-green text-green rounded-pill',
						'not_open_yet'         => 'badge bg-soft-yellow text-yellow rounded-pill',
						'registration_closed'  => 'badge bg-soft-ash text-muted rounded-pill',
						'no_seats'             => 'badge bg-soft-red text-red rounded-pill',
						'event_ended'          => 'badge bg-soft-ash text-muted rounded-pill',
					][ $reg_status['status'] ] ?? '';
				?>
				<a href="<?php the_permalink(); ?>" class="card mb-4 lift<?php echo $card_radius ? ' ' . esc_attr( trim( $card_radius ) ) : ''; ?>">
					<div class="card-body p-5">
						<span class="row justify-content-between align-items-center">

							<?php // Col 1: avatar (day) + title + status ?>
							<span class="col-md-5 mb-2 mb-md-0 d-flex align-items-center text-body">
								<span class="avatar bg-<?php echo esc_attr( $avatar_color ); ?> text-white w-9 h-9 fs-17 me-3 flex-shrink-0">
									<?php echo esc_html( $avatar_label ); ?>
								</span>
								<span>
									<?php the_title(); ?>
									<?php if ( $month_label ) : ?>
										<small class="text-muted ms-1"><?php echo esc_html( $month_label ); ?></small>
									<?php endif; ?>
									<?php if ( $status_class && $reg_status['label'] ) : ?>
										<br><span class="event-status-badge <?php echo esc_attr( $status_class ); ?> mt-1">
											<?php echo esc_html( $reg_status['label'] ); ?>
										</span>
									<?php endif; ?>
								</span>
							</span>

							<?php // Col 2: format ?>
							<span class="col-5 col-md-3 text-body d-flex align-items-center">
								<i class="uil uil-presentation me-1"></i>
								<?php if ( $formats && ! is_wp_error( $formats ) ) : ?>
									<?php echo esc_html( implode( ', ', wp_list_pluck( $formats, 'name' ) ) ); ?>
								<?php else : ?>
									<span class="text-muted">—</span>
								<?php endif; ?>
							</span>

							<?php // Col 3: location or price ?>
							<span class="col-7 col-md-4 col-lg-3 text-body d-flex align-items-center">
								<?php if ( $location ) : ?>
									<i class="uil uil-location-arrow me-1"></i>
									<?php echo esc_html( $location ); ?>
								<?php elseif ( $price ) : ?>
									<i class="uil uil-tag-alt me-1"></i>
									<?php echo esc_html( $price ); ?>
								<?php else : ?>
									<span class="text-muted">—</span>
								<?php endif; ?>
							</span>

							<?php // Col 4: arrow ?>
							<span class="d-none d-lg-block col-1 text-center text-body">
								<i class="uil uil-angle-right-b"></i>
							</span>

						</span>
					</div>
				</a>
				<?php endwhile; ?>

				<?php codeweber_pagination(); ?>

			<?php else : ?>
				<p class="text-muted"><?php esc_html_e( 'No events found.', 'codeweber' ); ?></p>
			<?php endif; ?>
		</div><!-- #events-table-results -->

	</div>
</section>

<script>
(function () {
	var catBtns     = document.querySelectorAll('.events-category-filters [data-cat-id]');
	var resultsWrap = document.getElementById('events-table-results');

	catBtns.forEach(function (btn) {
		btn.addEventListener('click', function () {
			var catId = btn.getAttribute('data-cat-id');

			catBtns.forEach(function (b) { b.classList.remove('active'); });
			btn.classList.add('active');

			if (!resultsWrap || typeof fetch_vars === 'undefined') return;

			resultsWrap.style.opacity       = '0.5';
			resultsWrap.style.pointerEvents = 'none';

			var filters = {};
			if (catId && catId !== '0') {
				filters.event_category = catId;
			}

			var body = new FormData();
			body.append('action',     'fetch_action');
			body.append('nonce',      fetch_vars.nonce);
			body.append('actionType', 'filterPosts');
			body.append('params',     JSON.stringify({ post_type: 'events', template: 'events_3', filters: filters }));

			fetch(fetch_vars.ajaxurl, { method: 'POST', body: body })
				.then(function (r) { return r.json(); })
				.then(function (data) {
					if (data.status === 'success' && resultsWrap) {
						resultsWrap.innerHTML = data.data.html;
					}
				})
				.catch(function (e) { console.error('Events filter error:', e); })
				.finally(function () {
					if (resultsWrap) {
						resultsWrap.style.opacity       = '';
						resultsWrap.style.pointerEvents = '';
					}
				});
		});
	});
})();
</script>

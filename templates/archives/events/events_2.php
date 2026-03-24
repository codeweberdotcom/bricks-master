<?php
/**
 * Archive template: Events — view 2 (table only)
 *
 * Table-only view with category AJAX filter. No calendar.
 *
 * @package Codeweber
 */

$event_categories = get_terms( [ 'taxonomy' => 'event_category', 'hide_empty' => true ] );
$btn_style        = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button' ) : ' rounded-pill';
?>

<section id="content-wrapper" class="wrapper bg-light">
	<div class="container py-10 py-md-12">

		<?php // ---- Category filter ----------------------------------------- ?>
		<?php if ( ! empty( $event_categories ) && ! is_wp_error( $event_categories ) ) : ?>
			<div class="d-flex flex-wrap gap-2 events-category-filters mb-6">
				<button type="button" data-cat-id="0"
					class="btn btn-xs btn-soft-primary has-ripple<?php echo esc_attr( $btn_style ); ?> <?php echo ! is_tax( 'event_category' ) ? 'active' : ''; ?>">
					<?php esc_html_e( 'All', 'codeweber' ); ?>
				</button>
				<?php foreach ( $event_categories as $cat ) : ?>
					<button type="button" data-cat-id="<?php echo esc_attr( $cat->term_id ); ?>"
						class="btn btn-xs btn-soft-primary has-ripple<?php echo esc_attr( $btn_style ); ?> <?php echo is_tax( 'event_category', $cat->term_id ) ? 'active' : ''; ?>">
						<?php echo esc_html( $cat->name ); ?>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php // ---- Table --------------------------------------------------- ?>
		<div id="events-table-results">
			<?php if ( have_posts() ) : ?>
				<div class="table-responsive">
					<table class="table table-hover align-middle events-table">
						<thead class="table-light">
							<tr>
								<th><?php esc_html_e( 'Date', 'codeweber' ); ?></th>
								<th><?php esc_html_e( 'Event', 'codeweber' ); ?></th>
								<th class="d-none d-md-table-cell"><?php esc_html_e( 'Location', 'codeweber' ); ?></th>
								<th class="d-none d-lg-table-cell"><?php esc_html_e( 'Format', 'codeweber' ); ?></th>
								<th class="d-none d-md-table-cell"><?php esc_html_e( 'Price', 'codeweber' ); ?></th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<?php while ( have_posts() ) : the_post();
								$date_start = get_post_meta( get_the_ID(), '_event_date_start', true );
								$date_end   = get_post_meta( get_the_ID(), '_event_date_end', true );
								$location   = get_post_meta( get_the_ID(), '_event_location', true );
								$price      = get_post_meta( get_the_ID(), '_event_price', true );
								$reg_status = codeweber_events_get_registration_status( get_the_ID() );
								$formats    = get_the_terms( get_the_ID(), 'event_format' );
							?>
							<tr>
								<td class="event-date-cell">
									<?php if ( $date_start ) : ?>
										<span class="fw-semibold"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $date_start ) ) ); ?></span>
										<?php if ( $date_end && $date_end !== $date_start ) : ?>
											<br><small class="text-muted"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $date_end ) ) ); ?></small>
										<?php endif; ?>
									<?php else : ?>
										<span class="text-muted">—</span>
									<?php endif; ?>
								</td>
								<td>
									<a href="<?php the_permalink(); ?>" class="fw-semibold text-reset text-decoration-none">
										<?php the_title(); ?>
									</a>
									<?php
									$status_class = [
										'open'                 => 'badge bg-soft-green text-green rounded-pill',
										'not_open_yet'         => 'badge bg-soft-yellow text-yellow rounded-pill',
										'registration_closed'  => 'badge bg-soft-ash text-muted rounded-pill',
										'no_seats'             => 'badge bg-soft-red text-red rounded-pill',
										'event_ended'          => 'badge bg-soft-ash text-muted rounded-pill',
									][ $reg_status['status'] ] ?? '';
									if ( $status_class && $reg_status['label'] ) :
									?>
										<br><span class="event-status-badge <?php echo esc_attr( $status_class ); ?> mt-1">
											<?php echo esc_html( $reg_status['label'] ); ?>
										</span>
									<?php endif; ?>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo $location ? esc_html( $location ) : '<span class="text-muted">—</span>'; ?>
								</td>
								<td class="d-none d-lg-table-cell">
									<?php if ( $formats && ! is_wp_error( $formats ) ) :
										foreach ( $formats as $fmt ) : ?>
											<span class="badge bg-soft-ash text-navy event-format-badge"><?php echo esc_html( $fmt->name ); ?></span>
										<?php endforeach;
									else : ?>
										<span class="text-muted">—</span>
									<?php endif; ?>
								</td>
								<td class="d-none d-md-table-cell event-card-price">
									<?php echo $price ? esc_html( $price ) : '<span class="text-muted">—</span>'; ?>
								</td>
								<td class="text-end">
									<a href="<?php the_permalink(); ?>" class="btn btn-sm btn-primary has-ripple<?php echo esc_attr( $btn_style ); ?>">
										<?php esc_html_e( 'Details', 'codeweber' ); ?>
									</a>
								</td>
							</tr>
							<?php endwhile; ?>
						</tbody>
					</table>
				</div>

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
			body.append('params',     JSON.stringify({ post_type: 'events', template: 'events_2', filters: filters }));

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

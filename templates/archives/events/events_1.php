<?php
/**
 * Archive template: Events — view 1 (table + FullCalendar)
 *
 * Dual view: FullCalendar (calendar) / Bootstrap table (list).
 * View preference stored in localStorage key: cw_events_view
 *
 * @package Codeweber
 */

$event_categories = get_terms( [ 'taxonomy' => 'event_category', 'hide_empty' => true ] );
$calendar_api_url = rest_url( 'codeweber/v1/events/calendar' );
?>

<section id="content-wrapper" class="wrapper bg-light">
	<div class="container py-10 py-md-12">

		<?php // ---- Filter + View Toggle ---------------------------------------- ?>
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-6">

			<?php // Category filter ?>
			<?php if ( ! empty( $event_categories ) && ! is_wp_error( $event_categories ) ) : ?>
				<div class="d-flex flex-wrap gap-2 events-category-filters">
					<a href="<?php echo esc_url( get_post_type_archive_link( 'events' ) ); ?>"
						class="btn btn-sm btn-soft-primary rounded-pill <?php echo ! is_tax( 'event_category' ) ? 'active' : ''; ?>">
						<?php esc_html_e( 'All', 'codeweber' ); ?>
					</a>
					<?php foreach ( $event_categories as $cat ) : ?>
						<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"
							class="btn btn-sm btn-soft-primary rounded-pill <?php echo is_tax( 'event_category', $cat->term_id ) ? 'active' : ''; ?>">
							<?php echo esc_html( $cat->name ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php // View toggle ?>
			<div class="btn-group events-view-toggle" role="group" aria-label="<?php esc_attr_e( 'View', 'codeweber' ); ?>">
				<button type="button" class="btn btn-sm btn-outline-primary" id="events-view-calendar" title="<?php esc_attr_e( 'Calendar view', 'codeweber' ); ?>">
					<i class="uil uil-calender"></i> <?php esc_html_e( 'Calendar', 'codeweber' ); ?>
				</button>
				<button type="button" class="btn btn-sm btn-outline-primary" id="events-view-table" title="<?php esc_attr_e( 'List view', 'codeweber' ); ?>">
					<i class="uil uil-list-ul"></i> <?php esc_html_e( 'List', 'codeweber' ); ?>
				</button>
			</div>
		</div>

		<?php // ---- Calendar view ------------------------------------------ ?>
		<div id="events-calendar-section" class="events-calendar-wrap" style="display:none;">
			<div id="events-fullcalendar"></div>
		</div>

		<?php // ---- Table / List view -------------------------------------- ?>
		<div id="events-table-section">
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
									<a href="<?php the_permalink(); ?>" class="btn btn-sm btn-primary rounded-pill">
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
		</div>

	</div>
</section>

<script>
(function () {
	var STORAGE_KEY = 'cw_events_view';
	var calSection  = document.getElementById('events-calendar-section');
	var tblSection  = document.getElementById('events-table-section');
	var btnCal      = document.getElementById('events-view-calendar');
	var btnTbl      = document.getElementById('events-view-table');
	var calInited   = false;

	function setView(view) {
		localStorage.setItem(STORAGE_KEY, view);
		if (view === 'calendar') {
			calSection.style.display = '';
			tblSection.style.display = 'none';
			btnCal.classList.add('active');
			btnTbl.classList.remove('active');
			if (!calInited) initCalendar();
		} else {
			calSection.style.display = 'none';
			tblSection.style.display = '';
			btnCal.classList.remove('active');
			btnTbl.classList.add('active');
		}
	}

	function initCalendar() {
		if (typeof FullCalendar === 'undefined') return;
		calInited = true;

		var calendarEl   = document.getElementById('events-fullcalendar');
		var apiUrl       = <?php echo wp_json_encode( $calendar_api_url ); ?>;
		var currentCat   = <?php echo wp_json_encode( is_tax( 'event_category' ) ? get_queried_object_id() : 0 ); ?>;

		var calendar = new FullCalendar.Calendar(calendarEl, {
			initialView:  'dayGridMonth',
			locale:       'ru',
			aspectRatio:  2,
			headerToolbar: {
				left:   'prev,next today',
				center: 'title',
				right:  'dayGridMonth,listMonth'
			},
			events: function (fetchInfo, successCallback, failureCallback) {
				var url = apiUrl
					+ '?start=' + encodeURIComponent(fetchInfo.startStr)
					+ '&end='   + encodeURIComponent(fetchInfo.endStr);
				if (currentCat) url += '&category=' + currentCat;

				fetch(url)
					.then(function (r) { return r.json(); })
					.then(successCallback)
					.catch(failureCallback);
			},
			eventClick: function (info) {
				if (info.event.url) {
					info.jsEvent.preventDefault();
					window.location.href = info.event.url;
				}
			},
			eventDidMount: function (info) {
				if (info.event.extendedProps.location) {
					info.el.setAttribute('title', info.event.extendedProps.location);
				}
			}
		});

		calendar.render();
	}

	if (btnCal) btnCal.addEventListener('click', function () { setView('calendar'); });
	if (btnTbl) btnTbl.addEventListener('click', function () { setView('table'); });

	// Restore last view — wait for footer scripts (FullCalendar) to load first
	window.addEventListener('load', function() {
		var saved = localStorage.getItem(STORAGE_KEY) || 'table';
		setView(saved);
	});
})();
</script>

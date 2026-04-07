<?php
/**
 * Archive template: Events — view 1 (table + FullCalendar)
 *
 * Dual view: FullCalendar (calendar) / Bootstrap table (list).
 * View preference stored in localStorage key: cw_events_view
 * Category filter works via AJAX (no page reload).
 *
 * @package Codeweber
 */

$event_categories = get_terms( [ 'taxonomy' => 'event_category', 'hide_empty' => true ] );
$calendar_api_url = rest_url( 'codeweber/v1/events/calendar' );
$btn_style        = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button' ) : ' rounded-pill';
?>

<section id="content-wrapper" class="wrapper">
	<div class="container py-10 py-md-12">

		<?php // ---- Filter + View Toggle ---------------------------------------- ?>
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-6">

			<?php // Category filter ?>
			<?php if ( ! empty( $event_categories ) && ! is_wp_error( $event_categories ) ) : ?>
				<div class="d-flex flex-wrap gap-2 events-category-filters">
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

			<?php // View toggle ?>
			<div class="btn-group events-view-toggle" role="group" aria-label="<?php esc_attr_e( 'View', 'codeweber' ); ?>">
				<button type="button" class="btn btn-sm btn-outline-primary has-ripple<?php echo esc_attr( $btn_style ); ?>" id="events-view-calendar" title="<?php esc_attr_e( 'Calendar view', 'codeweber' ); ?>">
					<i class="uil uil-calender"></i> <?php esc_html_e( 'Calendar', 'codeweber' ); ?>
				</button>
				<button type="button" class="btn btn-sm btn-outline-primary has-ripple<?php echo esc_attr( $btn_style ); ?>" id="events-view-table" title="<?php esc_attr_e( 'List view', 'codeweber' ); ?>">
					<i class="uil uil-list-ul"></i> <?php esc_html_e( 'List', 'codeweber' ); ?>
				</button>
			</div>
		</div>

		<?php // ---- Calendar view ------------------------------------------ ?>
		<div id="events-calendar-section" class="events-calendar-wrap position-relative" style="display:none;">
			<div id="events-calendar-loader" class="spinner spinner-overlay d-none" aria-hidden="true"></div>
			<div id="events-fullcalendar"></div>
		</div>

		<?php // ---- Table / List view -------------------------------------- ?>
		<div id="events-table-section">
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
		var btnStyleCls  = <?php echo wp_json_encode( array_values( array_filter( explode( ' ', trim( $btn_style ) ) ) ) ); ?>;

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
			eventClassNames: function (info) {
				// btn classes only for dayGrid — list view renders as <tr>, not a button
				if (info.view.type !== 'dayGridMonth') return [];
				return ['btn', 'btn-xs', 'has-ripple'].concat(btnStyleCls);
			},
			eventDidMount: function (info) {
				if (info.view.type === 'dayGridMonth') {
					// Remove FC inline styles — color applied via --event-color CSS var
					info.el.style.removeProperty('background-color');
					info.el.style.removeProperty('border-color');
					if (info.event.backgroundColor) {
						info.el.style.setProperty('--event-color', info.event.backgroundColor);
					}
				}
				if (info.event.extendedProps.location) {
					info.el.setAttribute('title', info.event.extendedProps.location);
				}
			},
			eventsSet: function () {
				if (typeof custom !== 'undefined' && typeof custom.rippleEffect === 'function') {
					custom.rippleEffect();
				}
			},
			// datesSet fires after every view render (including view switch) —
			// FullCalendar may recreate toolbar buttons, so re-apply classes here
			datesSet: function () {
				calendarEl.querySelectorAll('.fc-button-group').forEach(function (grp) {
					grp.classList.add('btn-group');
				});
				calendarEl.querySelectorAll('.fc-button').forEach(function (btn) {
					if (btn.classList.contains('btn')) return;
					btn.classList.add('btn', 'btn-sm', 'has-ripple');
					btnStyleCls.forEach(function (cls) { btn.classList.add(cls); });
				});
				if (typeof custom !== 'undefined' && typeof custom.rippleEffect === 'function') {
					custom.rippleEffect();
				}
			},
			loading: function (isLoading) {
				var loader = document.getElementById('events-calendar-loader');
				if (!loader) return;
				if (isLoading) {
					loader.classList.remove('d-none', 'done');
				} else {
					loader.classList.add('done');
					setTimeout(function () {
						loader.classList.add('d-none');
						loader.classList.remove('done');
					}, 300);
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

	// ---- Category AJAX filter ----
	var catBtns     = document.querySelectorAll('.events-category-filters [data-cat-id]');
	var resultsWrap = document.getElementById('events-table-results');

	catBtns.forEach(function (btn) {
		btn.addEventListener('click', function () {
			var catId = btn.getAttribute('data-cat-id');

			// Active state
			catBtns.forEach(function (b) { b.classList.remove('active'); });
			btn.classList.add('active');

			// Switch to table view if calendar is visible
			if (tblSection && tblSection.style.display === 'none') {
				setView('table');
			}

			if (!resultsWrap || typeof fetch_vars === 'undefined') return;

			// Loading state
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
			body.append('params',     JSON.stringify({ post_type: 'events', template: 'events_1', filters: filters }));

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

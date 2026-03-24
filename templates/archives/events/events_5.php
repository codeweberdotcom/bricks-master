<?php
/**
 * Archive template: Events — view 5 (horizontal card, whole card clickable)
 *
 * Pattern: <a class="card card-horizontal lift"> — entire card is a link.
 * No hover overlay on image, no button, arrow icon in top-right corner.
 * Based on vacancies_6 / style6-card.
 *
 * @package Codeweber
 */

$event_categories = get_terms( [ 'taxonomy' => 'event_category', 'hide_empty' => true ] );
$btn_style        = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button' ) : ' rounded-pill';
$card_radius      = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : '';
$grid_gap         = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'grid-gap' ) : 'gx-md-8 gy-6';

$figure_radius = $card_radius && $card_radius !== 'rounded-0' ? ' rounded-start' : ( $card_radius ? ' ' . trim( $card_radius ) : '' );
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
				<div class="row <?php echo esc_attr( $grid_gap ); ?> mb-5">
					<?php while ( have_posts() ) : the_post();
						$post_id    = get_the_ID();
						$date_start = get_post_meta( $post_id, '_event_date_start', true );
						$date_end   = get_post_meta( $post_id, '_event_date_end', true );
						$location   = get_post_meta( $post_id, '_event_location', true );
						$price      = get_post_meta( $post_id, '_event_price', true );
						$reg_status = codeweber_events_get_registration_status( $post_id );
						$formats    = get_the_terms( $post_id, 'event_format' );

						$thumbnail_id  = get_post_thumbnail_id( $post_id );
						$image_url     = $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'codeweber_event_400-267' ) : '';
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
						<a href="<?php the_permalink(); ?>" class="card card-horizontal lift text-inherit text-decoration-none<?php echo $card_radius ? ' ' . esc_attr( $card_radius ) : ''; ?>">
							<figure class="card-img mb-0<?php echo $figure_radius ? ' ' . esc_attr( trim( $figure_radius ) ) : ''; ?>">
								<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" class="img-fluid<?php echo $card_radius ? ' ' . esc_attr( $card_radius ) : ''; ?>">
							</figure>
							<div class="card-body position-relative">
								<?php if ( $date_start ) : ?>
									<p class="mb-1 text-muted small">
										<i class="uil uil-calendar-alt me-1"></i>
										<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $date_start ) ) ); ?>
										<?php if ( $date_end && $date_end !== $date_start ) : ?>
											— <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $date_end ) ) ); ?>
										<?php endif; ?>
									</p>
								<?php endif; ?>
								<h2 class="mb-3 display-6"><?php the_title(); ?></h2>
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
						</a><!-- /.card -->
					</div>
					<?php endwhile; ?>
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

			if (!resultsWrap || typeof codeweberFilter === 'undefined') return;

			resultsWrap.style.opacity       = '0.5';
			resultsWrap.style.pointerEvents = 'none';

			var filters = {};
			if (catId && catId !== '0') {
				filters.event_category = catId;
			}

			var body = new FormData();
			body.append('action',    'codeweber_filter');
			body.append('nonce',     codeweberFilter.nonce);
			body.append('post_type', 'events');
			body.append('template',  'events_5');
			body.append('filters',   JSON.stringify(filters));

			fetch(codeweberFilter.ajaxUrl, { method: 'POST', body: body })
				.then(function (r) { return r.json(); })
				.then(function (data) {
					if (data.success && resultsWrap) {
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

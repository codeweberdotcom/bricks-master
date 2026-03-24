<?php
/**
 * Archive template: Staff — Style 1 / Default (horizontal cards + city filter)
 *
 * Self-contained template. Filter by city: if staff has an office, city comes from
 * the office's `towns` taxonomy; otherwise from `_staff_city` meta.
 *
 * @package Codeweber
 */

$btn_style   = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button' ) : ' rounded-pill';
$card_radius = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : '';
$grid_gap    = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'grid-gap' ) : 'gx-md-8 gy-6';

// ---- Build unique city list ------------------------------------------------
$staff_cities = [];
$all_staff    = get_posts( [
	'post_type'      => 'staff',
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	'fields'         => 'ids',
] );

if ( ! empty( $all_staff ) ) {
	// Group by office
	$office_map     = []; // office_id => [staff_ids]
	$no_office_ids  = [];

	foreach ( $all_staff as $sid ) {
		$oid = get_post_meta( $sid, '_staff_office', true );
		if ( $oid ) {
			$office_map[ (int) $oid ][] = $sid;
		} else {
			$no_office_ids[] = $sid;
		}
	}

	// Cities from offices (via towns taxonomy)
	if ( post_type_exists( 'offices' ) && ! empty( $office_map ) ) {
		foreach ( array_keys( $office_map ) as $oid ) {
			$towns = wp_get_post_terms( $oid, 'towns', [ 'fields' => 'names' ] );
			if ( ! empty( $towns ) && ! is_wp_error( $towns ) ) {
				$staff_cities[] = $towns[0];
			} else {
				// Office without town — fall back to each staff member's own city
				foreach ( $office_map[ $oid ] as $sid ) {
					$c = get_post_meta( $sid, '_staff_city', true );
					if ( $c ) {
						$staff_cities[] = $c;
					}
				}
			}
		}
	}

	// Cities from staff without office
	foreach ( $no_office_ids as $sid ) {
		$c = get_post_meta( $sid, '_staff_city', true );
		if ( $c ) {
			$staff_cities[] = $c;
		}
	}

	$staff_cities = array_values( array_unique( array_filter( $staff_cities ) ) );
	sort( $staff_cities );
}

?>

<section id="content-wrapper" class="wrapper bg-light">
	<div class="container py-10 py-md-12">

		<?php // ---- City filter --------------------------------------------- ?>
		<?php if ( ! empty( $staff_cities ) ) : ?>
		<div class="d-flex flex-wrap gap-2 staff-city-filters mb-6">
			<button type="button" data-city=""
				class="btn btn-sm btn-soft-primary has-ripple<?php echo esc_attr( $btn_style ); ?> active">
				<?php esc_html_e( 'All', 'codeweber' ); ?>
			</button>
			<?php foreach ( $staff_cities as $city ) : ?>
			<button type="button" data-city="<?php echo esc_attr( $city ); ?>"
				class="btn btn-sm btn-soft-primary has-ripple<?php echo esc_attr( $btn_style ); ?>">
				<?php echo esc_html( $city ); ?>
			</button>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<?php // ---- Cards --------------------------------------------------- ?>
		<div id="staff-city-results">
			<?php if ( have_posts() ) : ?>
			<div class="row <?php echo esc_attr( $grid_gap ); ?> mb-5">
				<?php while ( have_posts() ) : the_post();
					$post_id   = get_the_ID();
					$card_html = cw_render_post_card( get_post(), 'horizontal', [], [
						'show_description' => true,
						'image_size'       => 'codeweber_staff',
					] );
					if ( empty( $card_html ) ) {
						continue;
					}
				?>
				<div id="<?php echo esc_attr( $post_id ); ?>" class="col-12">
					<?php echo $card_html; ?>
				</div>
				<?php endwhile; ?>
			</div>

			<?php codeweber_pagination(); ?>

			<?php else : ?>
			<p class="text-muted"><?php esc_html_e( 'No staff found.', 'codeweber' ); ?></p>
			<?php endif; ?>
		</div><!-- #staff-city-results -->

	</div>
</section>

<script>
(function () {
	var cityBtns    = document.querySelectorAll('.staff-city-filters [data-city]');
	var resultsWrap = document.getElementById('staff-city-results');

	cityBtns.forEach(function (btn) {
		btn.addEventListener('click', function () {
			var city = btn.getAttribute('data-city');

			cityBtns.forEach(function (b) { b.classList.remove('active'); });
			btn.classList.add('active');

			if (!resultsWrap || typeof fetch_vars === 'undefined') return;

			resultsWrap.style.opacity       = '0.5';
			resultsWrap.style.pointerEvents = 'none';

			var filters = {};
			if (city) {
				filters.city = city;
			}

			var body = new FormData();
			body.append('action',     'fetch_action');
			body.append('nonce',      fetch_vars.nonce);
			body.append('actionType', 'filterPosts');
			body.append('params',     JSON.stringify({ post_type: 'staff', template: 'staff_1', filters: filters }));

			fetch(fetch_vars.ajaxurl, { method: 'POST', body: body })
				.then(function (r) { return r.json(); })
				.then(function (data) {
					if (data.status === 'success' && resultsWrap) {
						resultsWrap.innerHTML = data.data.html;
					}
				})
				.catch(function (e) { console.error('Staff city filter error:', e); })
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

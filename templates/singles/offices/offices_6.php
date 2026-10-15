<?php
/**
 * Template: Single Office — Style 6 (contacts + map, services, visit form, nearby)
 *
 * Action buttons (call, route), a contacts card with a live open/closed badge
 * next to a single-marker map, the services offered at the office, a visit
 * request card and the nearest other offices by straight-line distance.
 *
 * The page title and breadcrumbs come from the theme's page header, so the
 * template does not repeat them.
 *
 * @package Codeweber
 */

defined( 'ABSPATH' ) || exit;

$post_id = get_the_ID();

$city  = '';
$towns = wp_get_post_terms( $post_id, 'towns', [ 'fields' => 'names' ] );
if ( $towns && ! is_wp_error( $towns ) ) {
	$city = $towns[0];
}
if ( '' === $city ) {
	$city = (string) get_post_meta( $post_id, '_office_city', true );
}

$address  = get_post_meta( $post_id, '_office_full_address', true ) ?: (string) get_post_meta( $post_id, '_office_street', true );
$landmark = (string) get_post_meta( $post_id, '_office_landmark', true );
$email    = (string) get_post_meta( $post_id, '_office_email', true );
$phones   = codeweber_get_office_phones( $post_id );
$hours    = codeweber_get_office_hours( $post_id );
$schedule = array_filter( array_map( 'trim', explode( ';', codeweber_format_office_hours( $post_id ) ) ) );
$lat      = (float) get_post_meta( $post_id, '_office_latitude', true );
$lng      = (float) get_post_meta( $post_id, '_office_longitude', true );
$has_geo  = $lat && $lng;

$route_url    = $has_geo ? 'https://yandex.ru/maps/?rtext=~' . $lat . ',' . $lng . '&rtt=auto' : '';
$yandex_url   = $has_geo ? 'https://yandex.ru/maps/?pt=' . $lng . ',' . $lat . '&z=17&l=map' : '';
$archive_link = get_post_type_archive_link( 'offices' );

$btn_style   = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button' ) : '';
$card_radius = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : '';
$card_class  = $card_radius ? ' ' . $card_radius : '';
$tel         = static function ( $phone ) {
	return 'tel:' . preg_replace( '/[^0-9+]/', '', $phone );
};

// Services offered at the office (the "Office Relations" metabox).
$service_ids = get_post_meta( $post_id, '_office_services', true );
$services    = is_array( $service_ids ) && $service_ids
	? get_posts(
		[
			'post_type'      => 'services',
			'post_status'    => 'publish',
			'post__in'       => array_map( 'absint', $service_ids ),
			'orderby'        => 'post__in',
			'posts_per_page' => -1,
		]
	)
	: [];

// Nearest other offices by straight-line (haversine) distance.
$nearby = [];
if ( $has_geo ) {
	$others = get_posts(
		[
			'post_type'      => 'offices',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'post__not_in'   => [ $post_id ],
			'fields'         => 'ids',
		]
	);
	foreach ( $others as $other_id ) {
		$o_lat = (float) get_post_meta( $other_id, '_office_latitude', true );
		$o_lng = (float) get_post_meta( $other_id, '_office_longitude', true );
		if ( ! $o_lat || ! $o_lng ) {
			continue;
		}
		$d_lat = deg2rad( $o_lat - $lat );
		$d_lng = deg2rad( $o_lng - $lng );
		$a     = sin( $d_lat / 2 ) ** 2 + cos( deg2rad( $lat ) ) * cos( deg2rad( $o_lat ) ) * sin( $d_lng / 2 ) ** 2;
		$nearby[] = [
			'id'       => $other_id,
			'distance' => 6371 * 2 * atan2( sqrt( $a ), sqrt( 1 - $a ) ),
		];
	}
	usort(
		$nearby,
		static function ( $x, $y ) {
			return $x['distance'] <=> $y['distance'];
		}
	);
	$nearby = array_slice( $nearby, 0, 3 );
}

/**
 * ID of a CodeWeber form shown in the "Book a visit" card. Without one the card
 * offers the office phone instead.
 *
 * @param int $form_id   Form post ID, 0 for none.
 * @param int $office_id Current office ID.
 */
$visit_form_id = (int) apply_filters( 'codeweber_office_single_visit_form', 0, $post_id );
?>
<div class="cw-office-single" data-cw-office-single>

	<!-- Back link, address, actions -->
	<div class="d-flex flex-wrap justify-content-between align-items-end gap-4 mb-8">
		<div class="d-flex flex-column gap-2">
			<?php if ( $archive_link ) : ?>
				<a href="<?php echo esc_url( $archive_link ); ?>" class="fs-15 fw-bold d-inline-flex align-items-center gap-1">
					<i class="uil uil-angle-left fs-20" aria-hidden="true"></i><?php esc_html_e( 'All offices', 'codeweber' ); ?>
				</a>
			<?php endif; ?>
			<?php if ( $address ) : ?>
				<p class="lead mb-0"><?php echo esc_html( $address ); ?></p>
			<?php endif; ?>
		</div>
		<div class="d-flex flex-wrap gap-2">
			<?php if ( $phones ) : ?>
				<a href="<?php echo esc_attr( $tel( $phones[0] ) ); ?>" class="btn btn-primary btn-icon btn-icon-start has-ripple<?php echo esc_attr( $btn_style ); ?>">
					<i class="uil uil-phone"></i><?php esc_html_e( 'Call', 'codeweber' ); ?>
				</a>
			<?php endif; ?>
			<?php if ( $route_url ) : ?>
				<a href="<?php echo esc_url( $route_url ); ?>" class="btn btn-soft-primary btn-icon btn-icon-start has-ripple<?php echo esc_attr( $btn_style ); ?>" target="_blank" rel="noopener">
					<i class="uil uil-navigator"></i><?php esc_html_e( 'Get directions', 'codeweber' ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>

	<!-- Contacts + map -->
	<div class="row gy-6 mb-12">
		<div class="col-lg-5">
			<div class="card h-100<?php echo esc_attr( $card_class ); ?>">
				<div class="card-body p-6 d-flex flex-column gap-5">
					<div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
						<h2 class="h4 mb-0"><?php esc_html_e( 'Contacts', 'codeweber' ); ?></h2>
						<span class="badge rounded-pill d-none" data-cw-status data-cw-hours="<?php echo esc_attr( wp_json_encode( $hours ) ); ?>"></span>
					</div>

					<?php if ( $address ) : ?>
						<div class="d-flex gap-3">
							<span class="btn btn-circle btn-soft-primary pe-none flex-shrink-0 align-self-start" aria-hidden="true"><i class="uil uil-map-marker"></i></span>
							<div>
								<span class="d-block fs-13 text-muted"><?php esc_html_e( 'Address', 'codeweber' ); ?></span>
								<span class="d-block fw-bold"><?php echo esc_html( $address ); ?></span>
								<?php if ( $landmark ) : ?>
									<span class="d-block fs-14 text-muted"><?php echo esc_html( $landmark ); ?></span>
								<?php endif; ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( $phones || $email ) : ?>
						<div class="d-flex gap-3">
							<span class="btn btn-circle btn-soft-primary pe-none flex-shrink-0 align-self-start" aria-hidden="true"><i class="uil uil-phone"></i></span>
							<div>
								<span class="d-block fs-13 text-muted"><?php esc_html_e( 'Phones', 'codeweber' ); ?></span>
								<?php foreach ( $phones as $phone ) : ?>
									<a href="<?php echo esc_attr( $tel( $phone ) ); ?>" class="d-block fw-bold link-dark"><?php echo esc_html( $phone ); ?></a>
								<?php endforeach; ?>
								<?php if ( $email ) : ?>
									<a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>" class="d-block fs-15"><?php echo esc_html( antispambot( $email ) ); ?></a>
								<?php endif; ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( $schedule ) : ?>
						<div class="d-flex gap-3">
							<span class="btn btn-circle btn-soft-primary pe-none flex-shrink-0 align-self-start" aria-hidden="true"><i class="uil uil-clock"></i></span>
							<div class="flex-grow-1">
								<span class="d-block fs-13 text-muted mb-1"><?php esc_html_e( 'Working hours', 'codeweber' ); ?></span>
								<?php
								foreach ( $schedule as $line ) :
									$parts = array_map( 'trim', explode( ':', $line, 2 ) );
									?>
									<div class="row g-0 fs-15">
										<span class="col-5 text-muted"><?php echo esc_html( $parts[0] ); ?></span>
										<span class="col-7 fw-bold"><?php echo esc_html( $parts[1] ?? '' ); ?></span>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>

					<?php
					$socials = function_exists( 'codeweber_social_links' ) ? codeweber_social_links( '', 'type3', 'sm' ) : '';
					if ( $socials ) :
						?>
						<div class="mt-auto pt-2"><?php echo $socials; // phpcs:ignore WordPress.Security.EscapeOutput -- built by the theme ?></div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="col-lg-7">
			<div class="card overflow-hidden<?php echo esc_attr( $card_class ); ?>">
				<?php if ( $has_geo && class_exists( 'Codeweber_Yandex_Maps' ) && Codeweber_Yandex_Maps::get_instance()->has_api_key() ) : ?>
					<div class="flex-grow-1">
						<?php
						echo Codeweber_Yandex_Maps::get_instance()->render_map( // phpcs:ignore WordPress.Security.EscapeOutput
							[
								'api_version'        => 3,
								'map_id'             => 'office-single-map-' . $post_id,
								'center'             => [ $lat, $lng ],
								'zoom'               => 16,
								'height'             => 420,
								'border_radius'      => 0,
								'auto_fit_bounds'    => false,
								'enable_scroll_zoom' => false,
								'show_sidebar'       => false,
								'show_filters'       => false,
								'balloon_fields'     => [
									'showCity'         => false,
									'showAddress'      => true,
									'showPhone'        => true,
									'showWorkingHours' => true,
									'showLink'         => false,
									'showDescription'  => false,
								],
							],
							[
								[
									'id'        => $post_id,
									'title'     => get_the_title( $post_id ),
									'address'   => $address,
									'city'      => $city,
									'phone'     => $phones[0] ?? '',
									'phones'    => $phones,
									'latitude'  => $lat,
									'longitude' => $lng,
								],
							]
						);
						?>
					</div>
				<?php endif; ?>
				<?php if ( $address || $yandex_url ) : ?>
					<div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-3 py-4">
						<span class="fs-14 text-muted"><?php echo esc_html( $address ); ?></span>
						<?php if ( $yandex_url ) : ?>
							<a href="<?php echo esc_url( $yandex_url ); ?>" class="fs-14 fw-bold" target="_blank" rel="noopener"><?php esc_html_e( 'Open in Yandex Maps', 'codeweber' ); ?></a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<!-- Services -->
	<?php if ( $services ) : ?>
		<section class="mb-12">
			<h2 class="h3 mb-5"><?php esc_html_e( 'What you can arrange at the office', 'codeweber' ); ?></h2>
			<div class="row g-4">
				<?php foreach ( $services as $service ) : ?>
					<?php $thumb = get_the_post_thumbnail_url( $service, 'thumbnail' ); ?>
					<div class="col-md-6 col-lg-3">
						<a href="<?php echo esc_url( get_permalink( $service ) ); ?>" class="card h-100 lift link-dark<?php echo esc_attr( $card_class ); ?>">
							<div class="card-body p-5 d-flex flex-column gap-3">
								<?php if ( $thumb ) : ?>
									<img src="<?php echo esc_url( $thumb ); ?>" alt="" class="rounded" width="48" height="48" style="object-fit: cover;" loading="lazy">
								<?php else : ?>
									<span class="btn btn-circle btn-soft-primary pe-none" aria-hidden="true"><i class="uil uil-shield-check"></i></span>
								<?php endif; ?>
								<span class="h5 mb-0"><?php echo esc_html( get_the_title( $service ) ); ?></span>
								<span class="mt-auto fs-14 fw-bold text-primary"><?php esc_html_e( 'Learn more →', 'codeweber' ); ?></span>
							</div>
						</a>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<!-- Visit request + nearby offices -->
	<div class="row gy-6">
		<div class="col-lg-6">
			<div class="card bg-primary h-100<?php echo esc_attr( $card_class ); ?>">
				<div class="card-body p-6 d-flex flex-column gap-3">
					<h2 class="h3 mb-0 text-white"><?php esc_html_e( 'Book a visit', 'codeweber' ); ?></h2>
					<p class="mb-0 text-white"><?php esc_html_e( 'Leave your phone number — an office specialist will call you back and prepare the documents for your visit.', 'codeweber' ); ?></p>
					<?php if ( $visit_form_id ) : ?>
						<div class="mt-2"><?php echo do_shortcode( '[codeweber_form id="' . $visit_form_id . '"]' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
					<?php elseif ( $phones ) : ?>
						<a href="<?php echo esc_attr( $tel( $phones[0] ) ); ?>" class="btn btn-white btn-icon btn-icon-start align-self-start mt-auto has-ripple<?php echo esc_attr( $btn_style ); ?>">
							<i class="uil uil-phone"></i><?php echo esc_html( $phones[0] ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<?php if ( $nearby ) : ?>
			<div class="col-lg-6">
				<div class="d-flex justify-content-between align-items-baseline gap-3 mb-4">
					<h2 class="h3 mb-0"><?php esc_html_e( 'Nearby offices', 'codeweber' ); ?></h2>
					<?php if ( $archive_link ) : ?>
						<a href="<?php echo esc_url( $archive_link ); ?>" class="fs-14 fw-bold"><?php esc_html_e( 'All offices', 'codeweber' ); ?></a>
					<?php endif; ?>
				</div>
				<div class="d-flex flex-column gap-3">
					<?php foreach ( $nearby as $item ) : ?>
						<?php $n_address = get_post_meta( $item['id'], '_office_full_address', true ) ?: (string) get_post_meta( $item['id'], '_office_street', true ); ?>
						<a href="<?php echo esc_url( get_permalink( $item['id'] ) ); ?>" class="card lift link-dark<?php echo esc_attr( $card_class ); ?>">
							<div class="card-body px-5 py-4 d-flex justify-content-between align-items-center gap-3">
								<span>
									<span class="d-block fw-bold"><?php echo esc_html( get_the_title( $item['id'] ) ); ?></span>
									<?php if ( $n_address ) : ?>
										<span class="d-block fs-14 text-muted"><?php echo esc_html( $n_address ); ?></span>
									<?php endif; ?>
								</span>
								<span class="fs-14 text-muted text-nowrap">
									<?php
									$km = $item['distance'];
									/* translators: %s: distance in kilometres, e.g. "15" or "0.8". */
									echo esc_html( sprintf( __( '~ %s km', 'codeweber' ), $km < 10 ? number_format_i18n( $km, 1 ) : number_format_i18n( round( $km ) ) ) );
									?>
								</span>
							</div>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>

<script>
( function () {
	// Live open/closed badge, computed by the map script's shared status logic.
	var badge = document.querySelector( '[data-cw-office-single] [data-cw-status]' );
	if ( ! badge ) return;
	var tz = <?php echo wp_json_encode( wp_timezone_string() ); ?>;
	var colors = { open: 'bg-pale-green text-green', soon: 'bg-pale-orange text-orange', 'break': 'bg-pale-orange text-orange', closed: 'bg-pale-red text-red' };
	var refresh = function () {
		var hours;
		try { hours = JSON.parse( badge.getAttribute( 'data-cw-hours' ) || '{}' ); } catch ( e ) { hours = {}; }
		var status = window.codeweberOfficeStatus( { officeHours: hours, timezone: tz } );
		if ( ! status ) return;
		badge.className = 'badge rounded-pill ' + ( colors[ status.state ] || colors.closed );
		badge.textContent = status.text;
	};
	var waited = 0;
	( function wait() {
		if ( typeof window.codeweberOfficeStatus === 'function' ) {
			refresh();
			setInterval( refresh, 60000 );
		} else if ( ( waited += 100 ) <= 10000 ) {
			setTimeout( wait, 100 );
		}
	} )();
} )();
</script>

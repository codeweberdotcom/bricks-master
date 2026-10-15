<?php
/**
 * Template: Offices Archive — Style 3 (Cities, list + map)
 *
 * A whole-page layout rather than a per-post card: offices grouped by city,
 * city chips and a search box that filter the list and the map together, a
 * sticky map column with the head office card, and live open/closed badges.
 *
 * Rendered once by archive-offices.php (not inside the posts loop) and queries
 * every office itself — filtering by city must see all of them, not one page.
 *
 * @package Codeweber
 */

defined( 'ABSPATH' ) || exit;

$offices = get_posts(
	[
		'post_type'      => 'offices',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => [ 'menu_order' => 'ASC', 'title' => 'ASC' ],
	]
);

if ( ! $offices ) {
	echo '<p class="text-center">' . esc_html__( 'No offices found.', 'codeweber' ) . '</p>';
	return;
}

$btn_style   = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button' ) : '';
$card_radius = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : '';

// ── Collect offices, grouped by city ─────────────────────────────────────────
$groups  = [];
$markers = [];
$slugs   = []; // city name => URL slug for ?city=

foreach ( $offices as $office ) {
	$id = $office->ID;

	$city  = '';
	$slug  = '';
	$towns = wp_get_post_terms( $id, 'towns' );
	if ( $towns && ! is_wp_error( $towns ) ) {
		$city = $towns[0]->name;
		$slug = $towns[0]->slug;
	}
	if ( '' === $city ) {
		$city = (string) get_post_meta( $id, '_office_city', true );
	}
	if ( '' === $city ) {
		$city = __( 'Other', 'codeweber' );
	}
	if ( ! isset( $slugs[ $city ] ) ) {
		$slugs[ $city ] = '' !== $slug ? $slug : sanitize_title( $city );
	}

	$address  = get_post_meta( $id, '_office_full_address', true ) ?: get_post_meta( $id, '_office_street', true );
	$lat      = (float) get_post_meta( $id, '_office_latitude', true );
	$lng      = (float) get_post_meta( $id, '_office_longitude', true );
	$has_geo  = $lat && $lng;
	$schedule = array_filter( array_map( 'trim', explode( ';', codeweber_format_office_hours( $id ) ) ) );
	$phones   = codeweber_get_office_phones( $id );

	$groups[ $city ][] = [
		'id'       => $id,
		'title'    => get_the_title( $id ),
		'link'     => get_permalink( $id ),
		'address'  => $address,
		'landmark' => get_post_meta( $id, '_office_landmark', true ),
		'phones'   => $phones,
		'schedule' => $schedule,
		'hours'    => codeweber_get_office_hours( $id ),
		'route'    => $has_geo ? 'https://yandex.ru/maps/?rtext=~' . $lat . ',' . $lng . '&rtt=auto' : '',
		'has_geo'  => $has_geo,
	];

	if ( $has_geo ) {
		$markers[] = [
			'id'        => $id,
			'title'     => get_the_title( $id ),
			'link'      => get_permalink( $id ),
			'address'   => $address,
			'city'      => $city,
			'phone'     => $phones[0] ?? '',
			'phones'    => $phones,
			'latitude'  => $lat,
			'longitude' => $lng,
		];
	}
}

// Busiest cities first, ties alphabetically — same order as the map's city filter.
uksort(
	$groups,
	static function ( $a, $b ) use ( $groups ) {
		return ( count( $groups[ $b ] ) <=> count( $groups[ $a ] ) ) ?: strcmp( $a, $b );
	}
);

$total_offices = count( $offices );
$total_cities  = count( $groups );

// A shared link like /offices/?city=doneck arrives with that city already
// selected; the script keeps the parameter in sync without reloading.
$active_city = '';
$city_param  = isset( $_GET['city'] ) ? sanitize_title( wp_unslash( $_GET['city'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
if ( '' !== $city_param ) {
	$active_city = (string) array_search( $city_param, $slugs, true );
	if ( ! isset( $groups[ $active_city ] ) ) {
		$active_city = '';
	}
}
$shown_count = $active_city ? count( $groups[ $active_city ] ) : $total_offices;

// ── Head office: company contacts from Redux ─────────────────────────────────
// The office marked "Main office" wins; without one, fall back to the
// company contacts from the theme settings.
$main_office_id = codeweber_get_main_office_id();
$main_link      = '';
if ( $main_office_id ) {
	$company_phones  = codeweber_get_office_phones( $main_office_id );
	$company_email   = (string) get_post_meta( $main_office_id, '_office_email', true );
	$company_address = get_post_meta( $main_office_id, '_office_full_address', true ) ?: (string) get_post_meta( $main_office_id, '_office_street', true );
	$company_hours   = array_filter( array_map( 'trim', explode( ';', codeweber_format_office_hours( $main_office_id ) ) ) );
	$main_link       = get_permalink( $main_office_id );
} else {
	$company_phones  = array_filter( [ class_exists( 'Redux' ) ? (string) Redux::get_option( 'redux_demo', 'phone_01' ) : '' ] );
	$company_email   = class_exists( 'Redux' ) ? (string) Redux::get_option( 'redux_demo', 'e-mail' ) : '';
	$company_address = function_exists( 'codeweber_get_address' ) ? codeweber_get_address( 'fact', ', ', '' ) : '';
	$company_hours   = array_filter( array_map( 'trim', explode( ';', codeweber_format_hours( codeweber_get_company_hours() ) ) ) );
}
$company_phone = (string) ( reset( $company_phones ) ?: '' );
$tel           = static function ( $phone ) {
	return 'tel:' . preg_replace( '/[^0-9+]/', '', $phone );
};

/**
 * Value for the theme modal that loads the callback form, e.g. "cf-123" for a
 * CodeWeber form or "cf7-45" for Contact Form 7. Empty hides the button.
 */
$callback_value = (string) apply_filters( 'codeweber_offices_archive_callback_form', '' );

// Plural forms handed to the script: [one, few, many].
$office_forms = [
	_x( '%d office', 'plural form for 1, 21, 31…', 'codeweber' ),
	_x( '%d offices', 'plural form for 2-4, 22-24…', 'codeweber' ),
	_x( '%d offices', 'plural form for 5-20, 25-30…', 'codeweber' ),
];
?>
<div class="cw-offices-archive" data-cw-offices>

	<!-- Summary + search -->
	<div class="row gy-4 align-items-end mb-6">
		<div class="col-lg-7">
			<p class="lead mb-0">
				<?php
				printf(
					/* translators: 1: number of offices, e.g. "29 offices"; 2: number of cities. */
					esc_html__( '%1$s in %2$s — choose the nearest one and come to get your policy', 'codeweber' ),
					esc_html( sprintf( _n( '%d office', '%d offices', $total_offices, 'codeweber' ), $total_offices ) ),
					esc_html( sprintf( _n( '%d city', '%d cities', $total_cities, 'codeweber' ), $total_cities ) )
				);
				?>
			</p>
		</div>
		<div class="col-lg-5">
			<label class="form-label fs-sm fw-bold mb-1" for="cw-offices-search"><?php esc_html_e( 'Search by city', 'codeweber' ); ?></label>
			<div class="position-relative">
				<i class="uil uil-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" aria-hidden="true"></i>
				<input type="search" id="cw-offices-search" class="form-control ps-10" placeholder="<?php echo esc_attr( sprintf( __( 'For example, %s', 'codeweber' ), (string) array_key_last( $groups ) ) ); ?>" autocomplete="off" data-cw-search>
			</div>
		</div>
	</div>

	<!-- City chips -->
	<div class="d-flex flex-wrap gap-2 mb-8" role="group" aria-label="<?php esc_attr_e( 'Filter by city', 'codeweber' ); ?>">
		<button type="button" class="btn btn-sm <?php echo $active_city ? 'btn-soft-primary' : 'btn-primary'; ?> rounded-pill d-inline-flex align-items-center gap-2" data-cw-chip="" data-cw-slug="" aria-pressed="<?php echo $active_city ? 'false' : 'true'; ?>">
			<?php esc_html_e( 'All cities', 'codeweber' ); ?>
			<span class="badge rounded-pill bg-white text-primary"><?php echo (int) $total_offices; ?></span>
		</button>
		<?php foreach ( $groups as $city => $list ) : ?>
			<?php $is_active = $city === $active_city; ?>
			<button type="button" class="btn btn-sm <?php echo $is_active ? 'btn-primary' : 'btn-soft-primary'; ?> rounded-pill d-inline-flex align-items-center gap-2" data-cw-chip="<?php echo esc_attr( $city ); ?>" data-cw-slug="<?php echo esc_attr( $slugs[ $city ] ); ?>" aria-pressed="<?php echo $is_active ? 'true' : 'false'; ?>">
				<?php echo esc_html( $city ); ?>
				<span class="badge rounded-pill bg-white text-primary"><?php echo count( $list ); ?></span>
			</button>
		<?php endforeach; ?>
	</div>

	<div class="row gx-lg-8 gy-8 flex-lg-row-reverse">

		<!-- Map + head office -->
		<aside class="col-lg-6">
			<div class="position-sticky d-flex flex-column gap-6" style="top: 2rem;">

				<?php if ( $markers && class_exists( 'Codeweber_Yandex_Maps' ) && Codeweber_Yandex_Maps::get_instance()->has_api_key() ) : ?>
					<div class="card overflow-hidden<?php echo $card_radius ? ' ' . esc_attr( $card_radius ) : ''; ?>">
						<div class="card-header d-flex justify-content-between align-items-center gap-3 py-4">
							<span class="fw-bold"><?php esc_html_e( 'Offices on the map', 'codeweber' ); ?></span>
						</div>
						<div data-cw-map>
							<?php
							echo Codeweber_Yandex_Maps::get_instance()->render_map( // phpcs:ignore WordPress.Security.EscapeOutput
								[
									'api_version'        => 3,
									'map_id'             => 'offices-archive-map',
									'height'             => 560,
									'border_radius'      => 0,
									'auto_fit_bounds'    => true,
									'enable_drag'        => true,
									'enable_scroll_zoom' => false,
									'show_sidebar'       => false,
									'show_filters'       => false,
									'balloon_fields'     => [
										'showCity'         => true,
										'showAddress'      => true,
										'showPhone'        => true,
										'showWorkingHours' => true,
										'showLink'         => true,
										'showDescription'  => false,
									],
								],
								$markers
							);
							?>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( $company_address || $company_phone ) : ?>
					<div class="card bg-primary text-inverse<?php echo $card_radius ? ' ' . esc_attr( $card_radius ) : ''; ?>">
						<div class="card-body p-6 d-flex flex-column gap-3">
							<span class="fs-13 fw-bold text-uppercase text-white opacity-75"><?php esc_html_e( 'Head office', 'codeweber' ); ?></span>
							<?php if ( $company_address ) : ?>
								<span class="h5 mb-0 text-white"><?php echo esc_html( $company_address ); ?></span>
							<?php endif; ?>
							<div class="d-flex flex-column gap-1 fs-15">
								<?php foreach ( $company_phones as $phone ) : ?>
									<a href="<?php echo esc_attr( $tel( $phone ) ); ?>" class="fw-bold text-white"><?php echo esc_html( $phone ); ?></a>
								<?php endforeach; ?>
								<?php if ( $company_email ) : ?>
									<a href="mailto:<?php echo esc_attr( antispambot( $company_email ) ); ?>" class="text-white"><?php echo esc_html( antispambot( $company_email ) ); ?></a>
								<?php endif; ?>
								<?php foreach ( $company_hours as $line ) : ?>
									<span class="text-white"><?php echo esc_html( $line ); ?></span>
								<?php endforeach; ?>
							</div>
							<?php if ( $callback_value || $main_link ) : ?>
								<div class="d-flex flex-wrap gap-3">
									<?php if ( $callback_value ) : ?>
										<a href="javascript:void(0)" class="btn btn-white has-ripple<?php echo esc_attr( $btn_style ); ?>" data-bs-toggle="modal" data-bs-target="#modal" data-value="<?php echo esc_attr( $callback_value ); ?>">
											<?php esc_html_e( 'Request a call back', 'codeweber' ); ?>
										</a>
									<?php endif; ?>
									<?php if ( $main_link ) : ?>
										<a href="<?php echo esc_url( $main_link ); ?>" class="btn btn-outline-white has-ripple<?php echo esc_attr( $btn_style ); ?>"><?php esc_html_e( 'More about the office', 'codeweber' ); ?></a>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>

			</div>
		</aside>

		<!-- Office list -->
		<div class="col-lg-6">
			<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-5">
				<span class="text-muted fs-15">
					<?php esc_html_e( 'Shown:', 'codeweber' ); ?>
					<strong class="text-dark" data-cw-shown><?php echo esc_html( sprintf( _n( '%d office', '%d offices', $shown_count, 'codeweber' ), $shown_count ) ); ?></strong>
				</span>
				<button type="button" class="btn btn-link p-0 fs-14 fw-bold<?php echo $active_city ? '' : ' d-none'; ?>" data-cw-reset><?php esc_html_e( 'Reset filter', 'codeweber' ); ?></button>
			</div>

			<div class="card text-center d-none<?php echo $card_radius ? ' ' . esc_attr( $card_radius ) : ''; ?>" data-cw-empty>
				<div class="card-body p-8">
					<h3 class="h4 mb-2"><?php esc_html_e( 'There is no office in this city yet', 'codeweber' ); ?></h3>
					<p class="text-muted mb-3"><?php esc_html_e( 'Call us — we will suggest the nearest one or arrange the policy remotely.', 'codeweber' ); ?></p>
					<?php if ( $company_phone ) : ?>
						<a href="<?php echo esc_attr( $tel( $company_phone ) ); ?>" class="h5 mb-0"><?php echo esc_html( $company_phone ); ?></a>
					<?php endif; ?>
				</div>
			</div>

			<?php foreach ( $groups as $city => $list ) : ?>
				<section class="mb-8<?php echo $active_city && $city !== $active_city ? ' d-none' : ''; ?>" data-cw-group="<?php echo esc_attr( $city ); ?>">
					<div class="d-flex align-items-baseline gap-3 mb-4">
						<h2 class="h3 mb-0"><?php echo esc_html( $city ); ?></h2>
						<span class="text-muted fs-14"><?php echo esc_html( sprintf( _n( '%d office', '%d offices', count( $list ), 'codeweber' ), count( $list ) ) ); ?></span>
					</div>

					<div class="d-flex flex-column gap-4">
						<?php foreach ( $list as $o ) : ?>
							<article class="card<?php echo $card_radius ? ' ' . esc_attr( $card_radius ) : ''; ?>" data-cw-office="<?php echo (int) $o['id']; ?>" data-cw-hours="<?php echo esc_attr( wp_json_encode( $o['hours'] ) ); ?>">
								<div class="card-body p-5">
									<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
										<div class="d-flex flex-column gap-2">
											<h3 class="h5 mb-0"><?php echo esc_html( $o['title'] ); ?></h3>
											<span class="badge rounded-pill align-self-start d-none" data-cw-status></span>
										</div>
										<?php if ( $o['has_geo'] ) : ?>
											<button type="button" class="btn btn-link p-0 fs-14 fw-bold d-inline-flex align-items-center gap-1" data-cw-show-on-map="<?php echo (int) $o['id']; ?>" data-cw-city="<?php echo esc_attr( $city ); ?>">
												<i class="uil uil-map-marker" aria-hidden="true"></i><?php esc_html_e( 'On the map', 'codeweber' ); ?>
											</button>
										<?php endif; ?>
									</div>

									<div class="row gy-3 fs-15 mb-4">
										<?php if ( $o['address'] ) : ?>
											<div class="col-12">
												<span class="d-block fs-13 text-muted"><?php esc_html_e( 'Address', 'codeweber' ); ?></span>
												<span class="fw-bold"><?php echo esc_html( $o['address'] ); ?></span>
												<?php if ( $o['landmark'] ) : ?>
													<span class="d-block fs-14 text-muted"><?php echo esc_html( $o['landmark'] ); ?></span>
												<?php endif; ?>
											</div>
										<?php endif; ?>
										<?php if ( $o['phones'] ) : ?>
											<div class="col-sm-6">
												<span class="d-block fs-13 text-muted"><?php esc_html_e( 'Phones', 'codeweber' ); ?></span>
												<?php foreach ( $o['phones'] as $phone ) : ?>
													<a href="<?php echo esc_attr( $tel( $phone ) ); ?>" class="d-block fw-bold link-dark"><?php echo esc_html( $phone ); ?></a>
												<?php endforeach; ?>
											</div>
										<?php endif; ?>
										<?php if ( $o['schedule'] ) : ?>
											<div class="col-sm-6">
												<span class="d-block fs-13 text-muted"><?php esc_html_e( 'Working hours', 'codeweber' ); ?></span>
												<?php
												foreach ( $o['schedule'] as $line ) :
													$parts = array_map( 'trim', explode( ':', $line, 2 ) );
													?>
													<span class="d-flex gap-3">
														<span class="text-muted" style="min-width: 3.5rem;"><?php echo esc_html( $parts[0] ); ?></span>
														<span class="fw-bold"><?php echo esc_html( $parts[1] ?? '' ); ?></span>
													</span>
												<?php endforeach; ?>
											</div>
										<?php endif; ?>
									</div>

									<div class="d-flex gap-3 pt-4 border-top">
										<?php if ( $o['route'] ) : ?>
											<a href="<?php echo esc_url( $o['route'] ); ?>" class="btn btn-sm btn-soft-primary btn-icon btn-icon-start has-ripple<?php echo esc_attr( $btn_style ); ?>" target="_blank" rel="noopener">
												<i class="uil uil-navigator"></i><?php esc_html_e( 'Route', 'codeweber' ); ?>
											</a>
										<?php endif; ?>
										<a href="<?php echo esc_url( $o['link'] ); ?>" class="btn btn-sm btn-primary flex-fill has-ripple<?php echo esc_attr( $btn_style ); ?>"><?php esc_html_e( 'More about the office', 'codeweber' ); ?></a>
									</div>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endforeach; ?>
		</div>
	</div>

	<!-- Bottom CTA -->
	<?php if ( $company_phone ) : ?>
		<div class="card mt-4<?php echo $card_radius ? ' ' . esc_attr( $card_radius ) : ''; ?>">
			<div class="card-body p-6 d-flex flex-wrap align-items-center justify-content-between gap-4">
				<div>
					<h3 class="h4 mb-1"><?php esc_html_e( 'No office nearby?', 'codeweber' ); ?></h3>
					<p class="text-muted mb-0"><?php esc_html_e( 'Call us — a consultant will suggest the nearest office and the documents you need.', 'codeweber' ); ?></p>
				</div>
				<a href="<?php echo esc_attr( $tel( $company_phone ) ); ?>" class="btn btn-primary btn-lg has-ripple<?php echo esc_attr( $btn_style ); ?>"><?php echo esc_html( $company_phone ); ?></a>
			</div>
		</div>
	<?php endif; ?>
</div>

<script>
( function () {
	var root = document.querySelector( '[data-cw-offices]' );
	if ( ! root ) return;

	var FORMS = <?php echo wp_json_encode( $office_forms ); ?>;
	var lang  = document.documentElement.lang || 'en';
	var plural = function ( n ) {
		var rule = new Intl.PluralRules( lang ).select( n );
		var form = rule === 'one' ? FORMS[0] : ( rule === 'few' ? FORMS[1] : FORMS[2] );
		return form.replace( '%d', n );
	};

	var chips  = root.querySelectorAll( '[data-cw-chip]' );
	var groups = root.querySelectorAll( '[data-cw-group]' );
	var search = root.querySelector( '[data-cw-search]' );
	var shown  = root.querySelector( '[data-cw-shown]' );
	var reset  = root.querySelector( '[data-cw-reset]' );
	var empty  = root.querySelector( '[data-cw-empty]' );
	var mapBox = root.querySelector( '[data-cw-map] .codeweber-yandex-map-wrapper' );
	var state  = { city: <?php echo wp_json_encode( $active_city ); ?>, q: '' };

	// ?city=<slug> mirrors the selected city so each city has its own link.
	var slugOf = function ( city ) {
		for ( var i = 0; i < chips.length; i++ ) {
			if ( chips[ i ].getAttribute( 'data-cw-chip' ) === city ) return chips[ i ].getAttribute( 'data-cw-slug' );
		}
		return '';
	};
	var cityOf = function ( slug ) {
		for ( var i = 0; i < chips.length; i++ ) {
			if ( slug && chips[ i ].getAttribute( 'data-cw-slug' ) === slug ) return chips[ i ].getAttribute( 'data-cw-chip' );
		}
		return '';
	};
	var pushCity = function () {
		var url = new URL( window.location.href );
		var slug = slugOf( state.city );
		if ( slug ) url.searchParams.set( 'city', slug ); else url.searchParams.delete( 'city' );
		if ( url.href !== window.location.href ) history.pushState( { cwCity: state.city }, '', url );
	};

	// The map is created asynchronously once ymaps3 has loaded.
	var withMap = function ( cb ) {
		if ( ! mapBox ) return;
		var waited = 0;
		( function wait() {
			var inst = mapBox._cwgbYandexMapInstance;
			if ( inst ) { cb( inst ); return; }
			waited += 100;
			if ( waited <= 10000 ) setTimeout( wait, 100 );
		} )();
	};

	var apply = function () {
		var q = state.q.trim().toLowerCase();
		var count = 0;
		groups.forEach( function ( g ) {
			var city = g.getAttribute( 'data-cw-group' );
			var visible = ( ! state.city || city === state.city ) && ( ! q || city.toLowerCase().indexOf( q ) !== -1 );
			g.classList.toggle( 'd-none', ! visible );
			if ( visible ) count += g.querySelectorAll( '[data-cw-office]' ).length;
		} );
		chips.forEach( function ( c ) {
			var active = c.getAttribute( 'data-cw-chip' ) === state.city && ! ( q && ! state.city );
			c.setAttribute( 'aria-pressed', active ? 'true' : 'false' );
			c.classList.toggle( 'btn-primary', active );
			c.classList.toggle( 'btn-soft-primary', ! active );
		} );
		shown.textContent = plural( count );
		empty.classList.toggle( 'd-none', count > 0 );
		reset.classList.toggle( 'd-none', ! state.city && ! q );
		withMap( function ( inst ) {
			if ( typeof inst.filterByCity === 'function' ) inst.filterByCity( state.city );
		} );
	};

	chips.forEach( function ( c ) {
		c.addEventListener( 'click', function () {
			state.city = c.getAttribute( 'data-cw-chip' );
			state.q = '';
			search.value = '';
			apply();
			pushCity();
		} );
	} );
	window.addEventListener( 'popstate', function () {
		state.city = cityOf( new URL( window.location.href ).searchParams.get( 'city' ) || '' );
		state.q = '';
		search.value = '';
		apply();
	} );
	// The list arrives pre-filtered from PHP; only the map still needs it.
	if ( state.city ) {
		withMap( function ( inst ) {
			if ( typeof inst.filterByCity === 'function' ) inst.filterByCity( state.city );
		} );
	}
	search.addEventListener( 'input', function () {
		state.q = search.value;
		state.city = '';
		apply();
	} );
	reset.addEventListener( 'click', function () {
		state.city = '';
		state.q = '';
		search.value = '';
		apply();
		pushCity();
	} );

	root.querySelectorAll( '[data-cw-show-on-map]' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			var id = btn.getAttribute( 'data-cw-show-on-map' );
			state.city = btn.getAttribute( 'data-cw-city' );
			state.q = '';
			search.value = '';
			apply();
			pushCity();
			if ( mapBox && mapBox.getBoundingClientRect().top < 0 ) mapBox.scrollIntoView( { behavior: 'smooth', block: 'center' } );
			withMap( function ( inst ) {
				// filterByCity() re-centres the map shortly after; open the balloon then.
				setTimeout( function () {
					var entry = inst.markerEls && inst.markerEls[ id ];
					if ( entry ) inst.onMarkerClick( entry.data, entry.el );
				}, 150 );
			} );
		} );
	} );

	// Live open/closed badges, computed by the map script's shared status logic.
	var tz = <?php echo wp_json_encode( wp_timezone_string() ); ?>;
	var colors = { open: 'bg-pale-green text-green', soon: 'bg-pale-orange text-orange', 'break': 'bg-pale-orange text-orange', closed: 'bg-pale-red text-red' };
	var refreshStatus = function () {
		if ( typeof window.codeweberOfficeStatus !== 'function' ) return;
		root.querySelectorAll( '[data-cw-office]' ).forEach( function ( card ) {
			var badge = card.querySelector( '[data-cw-status]' );
			var hours;
			try { hours = JSON.parse( card.getAttribute( 'data-cw-hours' ) || '{}' ); } catch ( e ) { hours = {}; }
			var status = window.codeweberOfficeStatus( { officeHours: hours, timezone: tz } );
			if ( ! status ) return;
			badge.className = 'badge rounded-pill align-self-start ' + ( colors[ status.state ] || colors.closed );
			badge.textContent = status.text;
		} );
	};
	var waitStatus = 0;
	( function waitForStatus() {
		if ( typeof window.codeweberOfficeStatus === 'function' ) {
			refreshStatus();
			setInterval( refreshStatus, 60000 );
		} else if ( ( waitStatus += 100 ) <= 10000 ) {
			setTimeout( waitForStatus, 100 );
		}
	} )();
} )();
</script>

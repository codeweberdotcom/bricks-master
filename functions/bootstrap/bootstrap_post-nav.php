<?php

/**
 * Навигация «предыдущая / следующая запись» для single.
 * Вывод в стиле single.php: ссылки с классами hover more-left / hover more.
 */
function codeweber_posts_nav()
{
	$previous_post = get_adjacent_post(false, '', true);
	$next_post    = get_adjacent_post(false, '', false);

	if (!$previous_post && !$next_post) {
		return;
	}

	echo '<nav class="nav mt-8 justify-content-between">';

	if ($previous_post) {
		printf(
			'<a href="%s" class="hover more-left me-4 mb-5">%s</a>',
			esc_url(get_permalink($previous_post->ID)),
			esc_html__('Previous', 'codeweber')
		);
	}

	if ($next_post) {
		printf(
			'<a href="%s" class="hover more ms-auto mb-5">%s</a>',
			esc_url(get_permalink($next_post->ID)),
			esc_html__('Next', 'codeweber')
		);
	}

	echo '</nav>';
}

/**
 * Модальное окно с картой всех проектов (только CPT projects с координатами).
 * Вызывается один раз на странице через static-флаг.
 */
function codeweber_projects_map_modal() {
	static $rendered = false;
	if ( $rendered ) {
		return;
	}
	$rendered = true;

	$show_map = codeweber_projects_settings_get( 'show_map', '1' );
	if ( $show_map !== '1' ) {
		return;
	}

	// Click handler is always output so href="#" doesn't fire when no projects have coordinates yet.
	?>
	<script>
	document.addEventListener('click', function(e) {
		var trigger = e.target.closest('[data-project-map]');
		if (!trigger) return;
		e.preventDefault();
		var el = document.getElementById('projects-map-offcanvas');
		if (el && window.bootstrap) {
			bootstrap.Offcanvas.getOrCreateInstance(el).show();
		}
	});
	</script>
	<?php

	if ( ! class_exists( 'Codeweber_Yandex_Maps' ) ) {
		return;
	}

	// Запрашиваем все опубликованные проекты с заполненными координатами
	$projects = get_posts( [
		'post_type'      => 'projects',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_query'     => [
			'relation' => 'AND',
			[
				'key'     => 'main_information_latitude',
				'value'   => '',
				'compare' => '!=',
			],
			[
				'key'     => 'main_information_longitude',
				'value'   => '',
				'compare' => '!=',
			],
		],
	] );

	if ( empty( $projects ) ) {
		return;
	}

	// Формируем маркеры для v3
	$markers = [];
	foreach ( $projects as $pid ) {
		$lat     = get_post_meta( $pid, 'main_information_latitude', true );
		$lng     = get_post_meta( $pid, 'main_information_longitude', true );
		$addr    = get_post_meta( $pid, 'main_information_address', true );
		$city    = get_post_meta( $pid, 'main_information_city', true );
		$desc    = get_post_meta( $pid, 'main_information_short_description', true );
		$img_id  = (int) get_post_meta( $pid, 'main_information_image', true );
		$img_url = $img_id
			? wp_get_attachment_image_url( $img_id, 'thumbnail' )
			: get_the_post_thumbnail_url( $pid, 'thumbnail' );

		$markers[] = [
			'id'          => $pid,
			'title'       => get_the_title( $pid ),
			'link'        => get_permalink( $pid ),
			'address'     => $addr,
			'city'        => $city,
			'description' => $desc,
			'image'       => $img_url ? $img_url : '',
			'latitude'    => floatval( $lat ),
			'longitude'   => floatval( $lng ),
		];
	}

	$yandex_maps = Codeweber_Yandex_Maps::get_instance();

	ob_start();
	echo $yandex_maps->render_map(
		[
			'api_version'      => 3,
			'map_id'           => 'projects-all-map',
			'zoom'             => 10,
			'height'           => 600,
			'border_radius'    => 0,
			'auto_fit_bounds'  => true,
			'enable_drag'      => true,
			'enable_scroll_zoom' => true,
			'show_sidebar'     => true,
			'sidebar_position' => 'left',
			'sidebar_title'    => __( 'Projects', 'codeweber' ),
			'sidebar_fields'   => [
				'showCity'         => true,
				'showAddress'      => false,
				'showPhone'        => false,
				'showWorkingHours' => false,
				'showDescription'  => false,
			],
			'show_filters'       => true,
			'filter_by_city'     => true,
			'balloon_fields'     => [
				'showCity'         => false,
				'showAddress'      => true,
				'showPhone'        => false,
				'showWorkingHours' => false,
				'showLink'         => true,
				'showDescription'  => false,
			],
			'balloon_max_width'  => 460,
			'color_scheme'       => 'light',
			'color_scheme_custom' => '',
		],
		$markers
	);
	$map_html = ob_get_clean();
	?>
	<style>
	#projects-map-offcanvas {
		--bs-offcanvas-width: 85vw;
	}
	#projects-map-offcanvas .offcanvas-body {
		padding: 0;
		overflow: hidden;
	}
	#projects-map-offcanvas .codeweber-yandex-map-wrapper {
		height: 100%;
	}
	#projects-map-offcanvas #projects-all-map {
		height: 100% !important;
	}
	</style>
	<script>
	document.addEventListener('shown.bs.offcanvas', function(e) {
		if (e.target.id !== 'projects-map-offcanvas') return;
		var wrapper = e.target.querySelector('.codeweber-yandex-map-wrapper');
		if (!wrapper) return;
		var offcanvasEl = e.target;
		// Defer past lazy-init microtask (map initialises async via ymaps3.ready.then)
		setTimeout(function() {
			var inst = wrapper._cwgbYandexMapInstance;
			if (!inst) return;
			if (typeof inst.invalidateSize === 'function') inst.invalidateSize();
			setTimeout(function() {
				var currentId = offcanvasEl.dataset.currentProject;
				if (currentId && inst.markerEls && inst.markerEls[currentId]) {
					var entry = inst.markerEls[currentId];
					inst.onMarkerClick(entry.data, entry.el);
					if (typeof inst.highlightSidebarItem === 'function') inst.highlightSidebarItem(currentId);
				} else if (typeof inst.fitBounds === 'function') {
					inst.fitBounds();
				}
			}, 300);
		}, 0);
	});
	</script>

	<div class="offcanvas offcanvas-end" id="projects-map-offcanvas" tabindex="-1" aria-labelledby="projects-map-offcanvas-label" data-current-project="<?php echo esc_attr( is_singular( 'projects' ) ? get_the_ID() : '' ); ?>">
		<div class="offcanvas-body p-0">
			<?php echo $map_html; ?>
		</div>
	</div>
	<?php
}

/**
 * Навигация для single CPT Projects.
 * Share всегда присутствует.
 * Redux projects_nav_type управляет только стилем вперёд/назад: text (ссылки) или buttons (кнопки с иконками).
 */
function codeweber_projects_nav() {
	$prev_post = get_adjacent_post( false, '', true );
	$next_post = get_adjacent_post( false, '', false );

	$nav_type  = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::get( 'projects_nav_type', 'text' ) : 'text';
	$btn_style = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button' ) : ' rounded-pill';
	?>
	<section class="wrapper">
		<div class="container py-10">
			<div class="row gx-md-6 gy-3 gy-md-0">
				<div class="col-md-8 align-self-center text-center text-md-start navigation">
					<?php if ( $nav_type === 'buttons' ) : ?>
						<?php if ( $prev_post ) : ?>
						<a href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>" class="btn btn-sm btn-soft-primary<?php echo esc_attr( $btn_style ); ?> btn-icon btn-icon-start has-ripple mb-0 me-1">
							<i class="uil uil-arrow-left"></i> <?php esc_html_e( 'Prev', 'codeweber' ); ?>
						</a>
						<?php endif; ?>
						<?php if ( $next_post ) : ?>
						<a href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>" class="btn btn-sm btn-soft-primary<?php echo esc_attr( $btn_style ); ?> btn-icon btn-icon-end has-ripple mb-0">
							<?php esc_html_e( 'Next', 'codeweber' ); ?> <i class="uil uil-arrow-right"></i>
						</a>
						<?php endif; ?>
					<?php else : ?>
						<?php if ( $prev_post ) : ?>
						<a href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>" class="hover more-left me-4 mb-5">
							<?php esc_html_e( 'Previous', 'codeweber' ); ?>
						</a>
						<?php endif; ?>
						<?php if ( $next_post ) : ?>
						<a href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>" class="hover more ms-auto mb-5">
							<?php esc_html_e( 'Next', 'codeweber' ); ?>
						</a>
						<?php endif; ?>
					<?php endif; ?>
				</div>
				<!--/column -->
				<aside class="col-md-4 sidebar text-center text-md-end">
					<?php codeweber_share_page(); ?>
				</aside>
				<!-- /column .sidebar -->
			</div>
			<!--/.row -->
		</div>
		<!-- /.container -->
	</section>
	<?php
}

/**
 * Блок «Товары проекта» для single CPT Projects.
 * Читает main_information_products (массив product ID), рендерит карточки.
 */
function codeweber_projects_related_products() {
	if ( ! function_exists( 'wc_get_product' ) ) {
		return;
	}

	$product_ids = get_post_meta( get_the_ID(), 'main_information_products', true );
	if ( empty( $product_ids ) || ! is_array( $product_ids ) ) {
		return;
	}

	$ids = array_filter( array_map( 'intval', $product_ids ) );
	if ( empty( $ids ) ) {
		return;
	}

	$products = wc_get_products( [
		'include' => $ids,
		'status'  => 'publish',
		'limit'   => -1,
		'orderby' => 'include',
	] );

	if ( empty( $products ) ) {
		return;
	}

	$grid_gap    = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'grid-gap' ) : 'gy-6 gx-md-6';
	$card_tpl    = get_theme_file_path( 'templates/woocommerce/cards/shop-list-sm.php' );
	$GLOBALS['cw_per_row'] = 1;

	$products_title = function_exists( 'codeweber_projects_settings_get' )
		? codeweber_projects_settings_get( 'products_title', '' )
		: '';
	$products_bg    = function_exists( 'codeweber_projects_settings_get' )
		? codeweber_projects_settings_get( 'products_bg', '' )
		: '';

	$section_class = 'wrapper' . ( $products_bg ? ' ' . $products_bg : '' );
	$heading       = $products_title ?: __( 'Project products', 'codeweber' );

	?>
	<section class="<?php echo esc_attr( $section_class ); ?>">
		<div class="container py-10 py-md-12">
			<h2 class="display-6 mb-8"><?php echo esc_html( $heading ); ?></h2>
			<div class="row <?php echo esc_attr( $grid_gap ); ?>">
				<?php foreach ( $products as $product ) :
					global $product;
					$cw_col = 'col-12';
					if ( file_exists( $card_tpl ) ) {
						include $card_tpl;
					}
				endforeach;
				wp_reset_postdata();
				?>
			</div>
		</div>
	</section>
	<?php
}

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

	// Формируем маркеры
	$markers = [];
	foreach ( $projects as $pid ) {
		$lat    = get_post_meta( $pid, 'main_information_latitude', true );
		$lng    = get_post_meta( $pid, 'main_information_longitude', true );
		$addr   = get_post_meta( $pid, 'main_information_address', true );
		$img_id = (int) get_post_meta( $pid, 'main_information_image', true );
		$img_url = $img_id ? wp_get_attachment_image_url( $img_id, 'thumbnail' ) : '';

		$balloon = '';
		if ( $addr ) {
			$balloon .= '<div style="margin-bottom:6px;">' . esc_html( $addr ) . '</div>';
		}
		$balloon .= '<div><a href="' . esc_url( get_permalink( $pid ) ) . '">' . esc_html__( 'Подробнее', 'codeweber' ) . '</a></div>';

		$markers[] = [
			'id'                   => $pid,
			'title'                => get_the_title( $pid ),
			'link'                 => get_permalink( $pid ),
			'address'              => $addr,
			'image'                => $img_url,
			'latitude'             => floatval( $lat ),
			'longitude'            => floatval( $lng ),
			'balloonContentHeader' => '<strong style="color:#333;font-size:15px;">' . esc_html( get_the_title( $pid ) ) . '</strong>',
			'balloonContent'       => $balloon,
			'hintContent'          => get_the_title( $pid ),
		];
	}

	$yandex_maps = Codeweber_Yandex_Maps::get_instance();
	$card_radius = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : 'rounded';

	ob_start();
	echo $yandex_maps->render_map(
		[
			'map_id'                   => 'projects-all-map',
			'zoom'                     => 10,
			'height'                   => 600,
			'width'                    => '100%',
			'border_radius'            => 0,
			'search_control'           => false,
			'show_sidebar'             => true,
			'sidebar_position'         => 'left',
			'sidebar_title'            => __( 'Проекты', 'codeweber' ),
			'sidebar_fields'           => [
				'showAddress'      => true,
				'showCity'         => false,
				'showPhone'        => false,
				'showWorkingHours' => false,
				'showDescription'  => false,
			],
			'clusterer'                => false,
			'auto_fit_bounds'          => true,
			'marker_auto_open_balloon' => false,
		],
		$markers
	);
	$map_html = ob_get_clean();
	?>
	<style>
	#projects-map-modal .modal-body {
		display: flex;
		flex-direction: column;
		overflow: hidden;
		padding: 0;
	}
	#projects-map-modal .codeweber-yandex-map-wrapper {
		flex: 1 1 auto;
		min-height: 0;
		position: relative;
	}
	#projects-map-modal .codeweber-yandex-map {
		height: 100% !important;
	}
	</style>
	<script>
	document.addEventListener('click', function(e) {
		var trigger = e.target.closest('[data-project-map]');
		if (!trigger) return;
		e.preventDefault();
		var modalEl = document.getElementById('projects-map-modal');
		if (modalEl && window.bootstrap) {
			bootstrap.Modal.getOrCreateInstance(modalEl).show();
		}
	});
	document.addEventListener('shown.bs.modal', function(e) {
		if (e.target.id !== 'projects-map-modal') return;
		var wrapper = e.target.querySelector('.codeweber-yandex-map-wrapper');
		if (!wrapper) return;
		var inst = wrapper._cwgbYandexMapInstance;
		if (!inst) return;
		if (typeof inst.invalidateSize === 'function') inst.invalidateSize();
		if (typeof inst.fitBounds === 'function') inst.fitBounds();
		if (inst.map) {
			inst.map.options.set('minZoom', 8);
			inst.map.options.set('maxZoom', 17);
		}
	});
	</script>

	<div class="modal fade" id="projects-map-modal" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-fullscreen">
			<div class="modal-content">
				<div class="modal-body">
					<button type="button" class="btn-close position-absolute top-0 end-0 m-3 z-3" data-bs-dismiss="modal" aria-label="<?php esc_attr_e( 'Close', 'codeweber' ); ?>"></button>
					<?php echo $map_html; ?>
				</div>
			</div>
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

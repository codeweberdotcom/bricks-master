<?php
/**
 * Template: Projects Archive — IT / Web (Overlay Cards)
 *
 * Isotope grid with browser-mockup cards and bottom-overlay title.
 * Screenshot scrolls on card hover; category + title always visible at bottom.
 *
 * @package Codeweber
 */

defined( 'ABSPATH' ) || exit;

$card_radius = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : 'rounded';
$grid_gap    = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'grid-gap' ) : 'gx-md-8 gy-8 gy-md-10';

$filter_terms = get_terms( [
	'taxonomy'   => 'projects_category',
	'hide_empty' => true,
	'orderby'    => 'name',
	'order'      => 'ASC',
] );

$show_map_btn  = class_exists( 'Codeweber_Yandex_Maps' )
	&& function_exists( 'codeweber_projects_settings_get' )
	&& codeweber_projects_settings_get( 'show_map', '1' ) === '1';
$map_btn_style = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button' ) : ' rounded-pill';
$has_filters   = ! empty( $filter_terms ) && ! is_wp_error( $filter_terms );

$projects_query = new WP_Query( [
	'post_type'      => 'projects',
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	'orderby'        => 'menu_order date',
	'order'          => 'ASC',
] );
?>

<style>
/* ── Browser bar ── */
.cw-it2-browser-bar {
	display: flex;
	align-items: center;
	gap: 5px;
	height: 32px;
	padding: 0 12px;
	background: #e9ecef;
	flex-shrink: 0;
}
.cw-it2-dot {
	width: 10px;
	height: 10px;
	border-radius: 50%;
	flex-shrink: 0;
}
.cw-it2-dot--red    { background: #ff5f57; }
.cw-it2-dot--yellow { background: #ffbd2e; }
.cw-it2-dot--green  { background: #28c840; }
.cw-it2-url {
	flex: 1;
	min-width: 0;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
	background: #fff;
	border-radius: 3px;
	padding: 2px 8px;
	font-size: 11px;
	color: #6c757d;
	line-height: 1.6;
	margin-left: 6px;
}
/* ── Screenshot scroll on hover ── */
.cw-it2-screen {
	overflow: hidden;
	height: 220px;
	position: relative;
}
.cw-it2-screenshot {
	display: block;
	width: 100%;
	height: auto;
	transition: transform 4s linear;
	transform: translateY(0);
}
.cw-it2-screenshot-placeholder {
	width: 100%;
	height: 220px;
	background: #f1f3f5;
}
</style>

<section class="wrapper">
	<div class="container py-14 py-md-16">
		<div class="grid grid-view projects-masonry">

			<?php if ( $has_filters || $show_map_btn ) : ?>
			<div class="isotope-filter filter mb-10">
				<?php if ( $show_map_btn ) : ?>
				<div class="mb-4 d-none d-md-flex justify-content-end">
					<a href="#" data-project-map class="btn btn-sm btn-soft-primary<?php echo esc_attr( $map_btn_style ); ?> btn-icon btn-icon-start has-ripple mb-0">
						<i class="uil uil-map-marker"></i> <?php esc_html_e( 'Map of objects', 'codeweber' ); ?>
					</a>
				</div>
				<?php endif; ?>
				<?php if ( $has_filters ) : ?>
				<ul>
					<li><a class="filter-item active" data-filter="*"><?php esc_html_e( 'All', 'codeweber' ); ?></a></li>
					<?php foreach ( $filter_terms as $term ) : ?>
					<li><a class="filter-item" data-filter=".<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_html( $term->name ); ?></a></li>
					<?php endforeach; ?>
				</ul>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<?php if ( $projects_query->have_posts() ) : ?>
			<div class="row <?php echo esc_attr( $grid_gap ); ?> isotope">
				<?php while ( $projects_query->have_posts() ) : $projects_query->the_post();
					$post_id      = get_the_ID();
					$alt_title    = get_post_meta( $post_id, '_alt_title', true );
					$website_url  = get_post_meta( $post_id, 'project_website_url', true );
					$cats         = get_the_terms( $post_id, 'projects_category' );
					$thumbnail_id = get_post_thumbnail_id( $post_id );

					$item_classes = 'project item col-md-6 col-xl-4';
					if ( $cats && ! is_wp_error( $cats ) ) {
						foreach ( $cats as $cat ) {
							$item_classes .= ' ' . sanitize_html_class( $cat->slug );
						}
					}

					$cat_name      = ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : '';
					$display_title = $alt_title ?: get_the_title();
					$url_display   = $website_url ? preg_replace( '#^https?://#', '', rtrim( $website_url, '/' ) ) : '';
				?>
				<div class="<?php echo esc_attr( $item_classes ); ?>">
					<div class="overflow-hidden <?php echo esc_attr( $card_radius ); ?>">

						<!-- Browser bar -->
						<a href="<?php the_permalink(); ?>" class="d-block text-decoration-none">
							<div class="cw-it2-browser-bar">
								<span class="cw-it2-dot cw-it2-dot--red"></span>
								<span class="cw-it2-dot cw-it2-dot--yellow"></span>
								<span class="cw-it2-dot cw-it2-dot--green"></span>
								<?php if ( $url_display ) : ?>
									<span class="cw-it2-url"><?php echo esc_html( $url_display ); ?></span>
								<?php endif; ?>
							</div>
						</a>

						<!-- Screenshot + bottom overlay -->
						<figure class="bottom-overlay position-relative mb-0">
							<a href="<?php the_permalink(); ?>" class="d-block">
								<div class="cw-it2-screen">
									<?php if ( $thumbnail_id ) : ?>
										<?php echo wp_get_attachment_image( $thumbnail_id, 'cw_wide_xl', false, [
											'class' => 'cw-it2-screenshot',
											'alt'   => esc_attr( $display_title ),
										] ); ?>
									<?php else : ?>
										<div class="cw-it2-screenshot-placeholder"></div>
									<?php endif; ?>
								</div>
							</a>
							<figcaption class="position-absolute bottom-0 start-0 end-0 p-4 text-white">
								<?php if ( $cat_name ) : ?>
								<div class="post-category text-line mb-1"><?php echo esc_html( $cat_name ); ?></div>
								<?php endif; ?>
								<h3 class="post-title h5 mb-0">
									<a href="<?php the_permalink(); ?>" class="text-white">
										<?php echo wp_kses_post( $display_title ); ?>
									</a>
								</h3>
							</figcaption>
						</figure>

					</div>
				</div>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>

			<?php else : ?>
			<p><?php esc_html_e( 'No projects found.', 'codeweber' ); ?></p>
			<?php endif; ?>

		</div>
	</div>
</section>

<?php codeweber_projects_map_modal(); ?>
<?php codeweber_projects_map_float_button(); ?>

<script>
(function () {
	function initScreenScroll(root) {
		(root || document).querySelectorAll('.cw-it2-screen').forEach(function (wrap) {
			if (wrap.dataset.cwScrollInit) return;
			wrap.dataset.cwScrollInit = '1';

			var img  = wrap.querySelector('.cw-it2-screenshot');
			if (!img) return;
			var card = wrap.closest('.overflow-hidden');
			if (!card) return;

			function getScrollDist() {
				var imgH = img.naturalHeight * (img.offsetWidth / img.naturalWidth);
				return Math.max(0, imgH - wrap.offsetHeight);
			}
			card.addEventListener('mouseenter', function () {
				var dist = getScrollDist();
				if (dist > 0) img.style.transform = 'translateY(-' + dist + 'px)';
			});
			card.addEventListener('mouseleave', function () {
				img.style.transform = 'translateY(0)';
			});
		});
	}
	initScreenScroll();
})();
</script>

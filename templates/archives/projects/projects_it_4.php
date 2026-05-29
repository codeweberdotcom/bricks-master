<?php
/**
 * Template: Projects Archive — IT / Web (Centered icon)
 *
 * AJAX category filter + 3-col grid with browser mockup cards.
 * Quick View: large frosted icon appears centered on screenshot on hover.
 *
 * @package Codeweber
 */

defined( 'ABSPATH' ) || exit;

$card_radius = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : 'rounded';
$grid_gap    = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'grid-gap' ) : 'gx-md-8 gy-10 gy-md-13';
$btn_style   = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button' ) : ' rounded-pill';

$filter_terms = get_terms( [
	'taxonomy'   => 'projects_category',
	'hide_empty' => true,
	'orderby'    => 'name',
	'order'      => 'ASC',
] );
$has_filters = ! empty( $filter_terms ) && ! is_wp_error( $filter_terms );

$show_map_btn = class_exists( 'Codeweber_Yandex_Maps' )
	&& function_exists( 'codeweber_projects_settings_get' )
	&& codeweber_projects_settings_get( 'show_map', '1' ) === '1';
$map_btn_style = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button' ) : ' rounded-pill';
?>

<style>
/* ── Card: browser bar ── */
.cw-browser-bar {
	display: flex;
	align-items: center;
	gap: 5px;
	height: 32px;
	padding: 0 12px;
	background: #e9ecef;
}
.cw-browser-dot {
	width: 10px;
	height: 10px;
	border-radius: 50%;
	flex-shrink: 0;
}
.cw-browser-dot--red    { background: #ff5f57; }
.cw-browser-dot--yellow { background: #ffbd2e; }
.cw-browser-dot--green  { background: #28c840; }
.cw-browser-url {
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
/* ── Card: screenshot scroll on hover ── */
.cw-it-screen {
	overflow: hidden;
	height: 220px;
	position: relative;
}
.cw-it-screenshot {
	display: block;
	width: 100%;
	height: auto;
	transition: transform 10s linear;
	transform: translateY(0);
}
.cw-it-screenshot-placeholder {
	width: 100%;
	height: 220px;
	background: #f1f3f5;
}
/* ── Card: centered frosted quick view icon ── */
.cw-it-hovericon {
	position: absolute;
	top: 50%;
	left: 50%;
	transform: translate(-50%, -50%) scale(.75);
	width: 58px;
	height: 58px;
	border-radius: 50%;
	border: 0;
	background: rgba(255, 255, 255, .18);
	backdrop-filter: blur(6px);
	-webkit-backdrop-filter: blur(6px);
	color: #fff;
	font-size: 22px;
	display: flex;
	align-items: center;
	justify-content: center;
	opacity: 0;
	transition: opacity .25s ease, transform .25s ease;
	z-index: 3;
	cursor: pointer;
	padding: 0;
	text-decoration: none;
}
.card:hover .cw-it-hovericon {
	opacity: 1;
	transform: translate(-50%, -50%) scale(1);
}
</style>

<section id="content-wrapper" class="wrapper">
	<div class="container py-14 py-md-16">

		<?php if ( $has_filters || $show_map_btn ) : ?>
		<div class="isotope-filter filter projects-category-filters mb-10">
			<?php if ( $show_map_btn ) : ?>
			<div class="mb-4 d-none d-md-flex justify-content-end">
				<a href="#" data-project-map class="btn btn-sm btn-soft-primary<?php echo esc_attr( $map_btn_style ); ?> btn-icon btn-icon-start has-ripple mb-0">
					<i class="uil uil-map-marker"></i> <?php esc_html_e( 'Map of objects', 'codeweber' ); ?>
				</a>
			</div>
			<?php endif; ?>
			<?php if ( $has_filters ) : ?>
			<ul>
				<li><a class="filter-item active" data-cat-id="0"><?php esc_html_e( 'All', 'codeweber' ); ?></a></li>
				<?php foreach ( $filter_terms as $term ) : ?>
				<li>
					<a class="filter-item" data-cat-id="<?php echo esc_attr( $term->term_id ); ?>">
						<?php echo esc_html( $term->name ); ?>
					</a>
				</li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<div id="projects-grid-results">
			<?php if ( have_posts() ) : ?>
			<div class="row <?php echo esc_attr( $grid_gap ); ?>">
				<?php while ( have_posts() ) : the_post();
					$post_id      = get_the_ID();
					$alt_title    = get_post_meta( $post_id, '_alt_title', true );
					$title        = $alt_title ?: get_the_title();
					$cms          = get_post_meta( $post_id, 'main_information_cms', true );
					$client       = get_post_meta( $post_id, 'main_information_client', true );
					$website_url  = get_post_meta( $post_id, 'project_website_url', true );
					$website_open = get_post_meta( $post_id, 'project_website_open', true ) ?: 'new-tab';
					$website_cta  = get_post_meta( $post_id, 'project_website_cta', true ) ?: __( 'View website', 'codeweber' );
					$thumbnail_id = (int) get_post_meta( $post_id, 'project_it_preview_1', true );
					$cats         = get_the_terms( $post_id, 'projects_category' );
					$cat_name     = ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : '';

					$link_target = $website_open === 'same-tab' ? '_self' : '_blank';
					$link_rel    = $website_open !== 'same-tab' ? 'noopener noreferrer' : '';
					$url_display = $website_url ? preg_replace( '#^https?://#', '', rtrim( $website_url, '/' ) ) : '';
				?>
				<div class="col-md-6 col-xl-4">
					<div class="card h-100 overflow-hidden <?php echo esc_attr( $card_radius ); ?>">

						<!-- Browser bar + screenshot -->
						<div class="position-relative">
							<a href="<?php the_permalink(); ?>" class="d-block text-decoration-none">
								<div class="cw-browser-bar">
									<span class="cw-browser-dot cw-browser-dot--red"></span>
									<span class="cw-browser-dot cw-browser-dot--yellow"></span>
									<span class="cw-browser-dot cw-browser-dot--green"></span>
									<?php if ( $url_display ) : ?>
										<span class="cw-browser-url"><?php echo esc_html( $url_display ); ?></span>
									<?php endif; ?>
								</div>
								<div class="cw-it-screen">
									<?php if ( $thumbnail_id ) : ?>
										<?php echo wp_get_attachment_image( $thumbnail_id, 'cw_wide_xl', false, [
											'class' => 'cw-it-screenshot',
											'alt'   => esc_attr( $title ),
										] ); ?>
									<?php else : ?>
										<div class="cw-it-screenshot-placeholder"></div>
									<?php endif; ?>
								</div>
							</a>
							<?php if ( $website_url ) : ?>
							<button type="button"
								class="cw-it-hovericon"
								data-bs-toggle="modal"
								data-bs-target="#cw-preview-modal"
								data-website-url="<?php echo esc_url( $website_url ); ?>"
								data-website-title="<?php echo esc_attr( $title ); ?>"
								aria-label="<?php esc_attr_e( 'Quick view', 'codeweber' ); ?>">
								<i class="uil uil-eye"></i>
							</button>
							<?php endif; ?>
						</div>

						<!-- Card body -->
						<div class="card-body p-4">
							<div class="post-header">
								<?php if ( $cat_name ) : ?>
								<div class="post-category text-line mb-2"><?php echo esc_html( $cat_name ); ?></div>
								<?php endif; ?>
								<h2 class="post-title h5 mb-2">
									<a href="<?php the_permalink(); ?>" class="link-dark text-decoration-none">
										<?php echo wp_kses_post( $title ); ?>
									</a>
								</h2>
							</div>

							<?php if ( $client || $cms ) : ?>
							<p class="fs-15 text-muted mb-3">
								<?php if ( $client ) : ?>
									<span><?php echo esc_html( $client ); ?></span>
								<?php endif; ?>
								<?php if ( $client && $cms ) : ?>
									<span class="mx-1 opacity-50">·</span>
								<?php endif; ?>
								<?php if ( $cms ) : ?>
									<span><?php echo esc_html( $cms ); ?></span>
								<?php endif; ?>
							</p>
							<?php endif; ?>

						</div>

					</div>
				</div>
				<?php endwhile; ?>
			</div>

			<?php codeweber_posts_pagination( [ 'nav_class' => 'd-flex justify-content-center mt-10' ] ); ?>

			<?php else : ?>
			<p class="text-muted"><?php esc_html_e( 'No projects found.', 'codeweber' ); ?></p>
			<?php endif; ?>
		</div><!-- #projects-grid-results -->

	</div>
</section>

<?php codeweber_projects_map_modal(); ?>
<?php codeweber_projects_map_float_button(); ?>

<?php get_template_part( 'templates/components/cw-preview-modal' ); ?>

<script>
(function () {
	var catBtns     = document.querySelectorAll('.projects-category-filters .filter-item');
	var resultsWrap = document.getElementById('projects-grid-results');

	// ── Screenshot scroll on card hover ──────────────────────────────
	function initScreenScroll(root) {
		(root || document).querySelectorAll('.cw-it-screen').forEach(function (wrap) {
			if (wrap.dataset.cwScrollInit) return;
			wrap.dataset.cwScrollInit = '1';

			var img  = wrap.querySelector('.cw-it-screenshot');
			if (!img) return;
			var card = wrap.closest('.card');
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

	// ── Category filter ───────────────────────────────────────────────
	catBtns.forEach(function (btn) {
		btn.addEventListener('click', function (e) {
			e.preventDefault();
			var catId = btn.getAttribute('data-cat-id');

			catBtns.forEach(function (b) { b.classList.remove('active'); });
			btn.classList.add('active');

			if (!resultsWrap || typeof fetch_vars === 'undefined') return;

			resultsWrap.style.opacity       = '0.5';
			resultsWrap.style.pointerEvents = 'none';

			var filters = {};
			if (catId && catId !== '0') filters.projects_category = catId;

			var body = new FormData();
			body.append('action',     'fetch_action');
			body.append('nonce',      fetch_vars.nonce);
			body.append('actionType', 'filterPosts');
			body.append('params',     JSON.stringify({ post_type: 'projects', template: 'projects_it_4', filters: filters }));

			fetch(fetch_vars.ajaxurl, { method: 'POST', body: body })
				.then(function (r) { return r.json(); })
				.then(function (data) {
					if (data.status === 'success' && resultsWrap) {
						resultsWrap.innerHTML = data.data.html;
						initScreenScroll(resultsWrap);
					}
				})
				.catch(function (err) { console.error('Projects filter error:', err); })
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

<?php
/**
 * Template: Projects Archive — IT / Web (Browser Cards)
 *
 * 3-column grid. Each card shows a fake browser address bar, a scrollable
 * screenshot with an overlay CTA on hover, and meta info below.
 *
 * @package Codeweber
 */

defined( 'ABSPATH' ) || exit;

$btn_style = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button' ) : ' rounded-pill';

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
/* ── Browser card wrapper ── */
.cw-it8-card {
	border-radius: 12px;
	overflow: hidden;
	background: #fff;
}
/* ── Fake browser address bar ── */
.cw-it8-bar {
	display: flex;
	align-items: center;
	gap: 6px;
	padding: 8px 12px;
	background: #f1f3f5;
	border-bottom: 1px solid #e9ecef;
}
.cw-it8-dot {
	width: 10px;
	height: 10px;
	border-radius: 50%;
	flex-shrink: 0;
}
.cw-it8-dot:nth-child(1) { background: #ff5f57; }
.cw-it8-dot:nth-child(2) { background: #febc2e; }
.cw-it8-dot:nth-child(3) { background: #28c840; }
.cw-it8-url {
	font-size: 11px;
	color: #868e96;
	background: #fff;
	border-radius: 4px;
	padding: 2px 8px;
	flex: 1;
	text-align: center;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}
/* ── Screenshot area ── */
.cw-it8-shot {
	overflow: hidden;
	max-height: 280px;
}
.cw-it8-img {
	display: block;
	width: 100%;
	height: auto;
	transition: transform 10s linear;
	transform: translateY(0);
}
/* ── Meta below card ── */
.cw-it8-meta {
	padding-top: 1.25rem;
}
.cw-it8-desc {
	font-size: .9rem;
	margin-bottom: .75rem;
}
.cw-it8-year {
	font-size: .8rem;
	font-weight: 500;
	color: #adb5bd;
	flex-shrink: 0;
	margin-left: 1rem;
	align-self: flex-end;
	padding-bottom: .2rem;
}
</style>

<section class="wrapper">
	<div class="container py-14 py-md-16">

		<?php if ( $has_filters || $show_map_btn ) : ?>
		<div class="isotope-filter filter projects-category-filters mb-12">
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
			<div class="row gy-10 gx-md-8">
				<?php while ( have_posts() ) : the_post();
					$post_id      = get_the_ID();
					$alt_title    = get_post_meta( $post_id, '_alt_title', true );
					$title        = $alt_title ?: get_the_title();
					$website_url  = get_post_meta( $post_id, 'project_website_url', true );
					$scroll_id    = (int) get_post_meta( $post_id, 'project_it_preview_1', true );
					$img_id       = $scroll_id ?: get_post_thumbnail_id( $post_id );
					$cats         = get_the_terms( $post_id, 'projects_category' );
					$cat_name     = ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : '';
					$url_display  = $website_url ? preg_replace( '#^https?://#', '', rtrim( $website_url, '/' ) ) : get_bloginfo( 'name' );
					$year         = get_the_date( 'Y' );
					$excerpt      = get_the_excerpt();
				?>
				<div class="col-md-6 col-xl-4 reveal">
					<div class="cw-it8-work">

						<!-- Browser card -->
						<div class="cw-it8-card lift shadow-lg">

							<!-- Fake browser address bar -->
							<div class="cw-it8-bar">
								<span class="cw-it8-dot"></span>
								<span class="cw-it8-dot"></span>
								<span class="cw-it8-dot"></span>
								<span class="cw-it8-url"><?php echo esc_html( $url_display ); ?></span>
							</div>

							<!-- Screenshot + hover overlay -->
							<figure class="overlay overlay-1 mb-0">
								<div class="cw-it8-shot">
									<?php if ( $img_id ) : ?>
									<?php echo wp_get_attachment_image( $img_id, 'cw_wide_xl', false, [
										'class' => 'cw-it8-img',
										'alt'   => esc_attr( $title ),
									] ); ?>
									<?php else : ?>
									<div style="height:260px;background:#e9ecef;"></div>
									<?php endif; ?>
								</div>
								<figcaption>
									<span class="cap-bg"></span>
									<a href="<?php the_permalink(); ?>"
									   class="btn btn-sm btn-white<?php echo esc_attr( $btn_style ); ?> btn-open">
										<?php esc_html_e( 'View project', 'codeweber' ); ?>
										<i class="uil uil-arrow-up-right"></i>
									</a>
								</figcaption>
							</figure>

						</div><!-- .cw-it8-card -->

						<!-- Meta below card -->
						<div class="cw-it8-meta d-flex justify-content-between align-items-start">
							<div>
								<?php if ( $cat_name ) : ?>
								<div class="post-category"><?php echo esc_html( $cat_name ); ?></div>
								<?php endif; ?>
								<h2 class="post-title h4 mb-2 mt-1">
									<a href="<?php the_permalink(); ?>" class="link-dark text-decoration-none">
										<?php echo wp_kses_post( $title ); ?>
									</a>
								</h2>
								<?php if ( $excerpt ) : ?>
								<p class="cw-it8-desc text-muted"><?php echo esc_html( $excerpt ); ?></p>
								<?php endif; ?>
								<a href="<?php the_permalink(); ?>"
								   class="btn btn-sm btn-soft-primary<?php echo esc_attr( $btn_style ); ?> btn-icon btn-icon-end has-ripple">
									<?php esc_html_e( 'View project', 'codeweber' ); ?>
									<i class="uil uil-arrow-right"></i>
								</a>
							</div>
							<?php if ( $year ) : ?>
							<span class="cw-it8-year"><?php echo esc_html( $year ); ?></span>
							<?php endif; ?>
						</div>

					</div><!-- .cw-it8-work -->
				</div>
				<?php endwhile; ?>
			</div><!-- .row -->

			<?php codeweber_posts_pagination( [ 'nav_class' => 'd-flex justify-content-center mt-14' ] ); ?>

			<?php else : ?>
			<p class="text-muted"><?php esc_html_e( 'No projects found.', 'codeweber' ); ?></p>
			<?php endif; ?>
		</div><!-- #projects-grid-results -->

	</div>
</section>

<?php codeweber_projects_map_modal(); ?>
<?php codeweber_projects_map_float_button(); ?>

<script>
(function () {
	var catBtns     = document.querySelectorAll('.projects-category-filters .filter-item');
	var resultsWrap = document.getElementById('projects-grid-results');

	// ── Screenshot scroll on hover ────────────────────────────────────────
	function initScreenScroll(root) {
		(root || document).querySelectorAll('.cw-it8-shot').forEach(function (wrap) {
			if (wrap.dataset.cwScrollInit) return;
			wrap.dataset.cwScrollInit = '1';

			var img = wrap.querySelector('.cw-it8-img');
			if (!img) return;

			function getScrollDist() {
				var imgH = img.naturalHeight * (img.offsetWidth / img.naturalWidth);
				return Math.max(0, imgH - wrap.offsetHeight);
			}

			wrap.addEventListener('mouseenter', function () {
				var dist = getScrollDist();
				if (dist > 0) {
					img.style.transition = 'transform 10s linear';
					img.style.transform  = 'translateY(-' + dist + 'px)';
				}
			});
			wrap.addEventListener('mouseleave', function () {
				img.style.transition = 'transform 0.5s linear';
				img.style.transform  = 'translateY(0)';
			});
		});
	}
	initScreenScroll();

	// ── Category AJAX filter ──────────────────────────────────────────────
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
			body.append('params',     JSON.stringify({ post_type: 'projects', template: 'projects_it_8', filters: filters }));

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

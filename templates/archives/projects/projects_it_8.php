<?php
/**
 * Template: Projects Archive — IT / Web (Browser Mockup Rows)
 *
 * Dark-background full-width rows. Each row: browser mockup (col-lg-7)
 * + project meta (col-lg-4). Odd rows image-left, even rows image-right.
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
/* ── Section dark background ── */
.cw-it8-section { background: #111; }
.cw-it8-section .isotope-filter.filter a { color: rgba(255,255,255,.55); }
.cw-it8-section .isotope-filter.filter a.active,
.cw-it8-section .isotope-filter.filter a:hover { color: #fff; }

/* ── Row divider ── */
.cw-it8-row + .cw-it8-row { padding-top: 5rem; }
@media (min-width: 768px) { .cw-it8-row + .cw-it8-row { padding-top: 7rem; } }

/* ── Browser mockup ── */
.cw-it8-browser {
	background: #1e1e20;
	border-radius: 12px;
	overflow: hidden;
	box-shadow: 0 0 0 1px rgba(255,255,255,.08), 0 32px 80px rgba(0,0,0,.6);
}
.cw-it8-bar {
	display: flex;
	align-items: center;
	gap: 10px;
	padding: 10px 16px;
	background: #2a2a2e;
}
.cw-it8-dots { display: flex; gap: 6px; flex-shrink: 0; }
.cw-it8-dot { width: 11px; height: 11px; border-radius: 50%; }
.cw-it8-dot:nth-child(1) { background: #ff5f57; }
.cw-it8-dot:nth-child(2) { background: #febc2e; }
.cw-it8-dot:nth-child(3) { background: #28c840; }
.cw-it8-url-bar {
	flex: 1;
	background: #3a3a3e;
	border-radius: 6px;
	padding: 4px 12px;
	font-size: 12px;
	color: #999;
	text-align: center;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	max-width: 320px;
	margin: 0 auto;
}
/* ── Screenshot area ── */
.cw-it8-screen {
	overflow: hidden;
	max-height: 440px;
	background: #fff;
}
.cw-it8-img {
	display: block;
	width: 100%;
	height: auto;
	transition: transform 10s linear;
	transform: translateY(0);
}

/* ── Text column ── */
.cw-it8-cat {
	font-size: .7rem;
	font-weight: 700;
	letter-spacing: .12em;
	text-transform: uppercase;
	color: rgba(255,255,255,.45);
	margin-bottom: .75rem;
}
.cw-it8-title {
	font-size: clamp(1.75rem, 3vw, 2.75rem);
	font-weight: 700;
	line-height: 1.15;
	color: #fff;
	margin-bottom: 1rem;
}
.cw-it8-title a { color: inherit; text-decoration: none; }
.cw-it8-title a:hover { opacity: .8; }
.cw-it8-desc {
	font-size: .95rem;
	color: rgba(255,255,255,.5);
	line-height: 1.65;
	margin-bottom: 2rem;
}
.cw-it8-year {
	display: inline-block;
	margin-top: 1.25rem;
	font-size: .8rem;
	font-weight: 500;
	color: rgba(255,255,255,.4);
	border: 1px solid rgba(255,255,255,.15);
	border-radius: 999px;
	padding: 3px 14px;
}
</style>

<section class="wrapper cw-it8-section">
	<div class="container py-14 py-md-16">

		<?php if ( $has_filters || $show_map_btn ) : ?>
		<div class="isotope-filter filter projects-category-filters mb-14">
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
			<?php if ( have_posts() ) :
				$index = 0;
				while ( have_posts() ) : the_post();
					$post_id     = get_the_ID();
					$alt_title   = get_post_meta( $post_id, '_alt_title', true );
					$title       = $alt_title ?: get_the_title();
					$website_url = get_post_meta( $post_id, 'project_website_url', true );
					$scroll_id   = (int) get_post_meta( $post_id, 'project_it_preview_1', true );
					$img_id      = $scroll_id ?: get_post_thumbnail_id( $post_id );
					$cats        = get_the_terms( $post_id, 'projects_category' );
					$cat_name    = ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : '';
					$url_display = $website_url ? preg_replace( '#^https?://#', '', rtrim( $website_url, '/' ) ) : '';
					$year        = get_the_date( 'Y' );
					$excerpt     = get_the_excerpt();
					$is_even     = ( $index % 2 === 1 );
					$index++;
			?>
			<div class="cw-it8-row row gx-md-10 gx-xl-14 align-items-center">

				<!-- Browser mockup -->
				<div class="col-lg-7<?php echo $is_even ? ' order-lg-2' : ''; ?>">
					<div class="cw-it8-browser">
						<div class="cw-it8-bar">
							<div class="cw-it8-dots">
								<span class="cw-it8-dot"></span>
								<span class="cw-it8-dot"></span>
								<span class="cw-it8-dot"></span>
							</div>
							<?php if ( $url_display ) : ?>
							<span class="cw-it8-url-bar"><?php echo esc_html( $url_display ); ?></span>
							<?php endif; ?>
						</div>
						<a href="<?php the_permalink(); ?>" class="d-block text-decoration-none">
							<div class="cw-it8-screen">
								<?php if ( $img_id ) : ?>
								<?php echo wp_get_attachment_image( $img_id, 'cw_wide_xl', false, [
									'class' => 'cw-it8-img',
									'alt'   => esc_attr( $title ),
								] ); ?>
								<?php else : ?>
								<div style="height:360px;background:#e9ecef;"></div>
								<?php endif; ?>
							</div>
						</a>
					</div>
				</div>

				<!-- Project meta -->
				<div class="col-lg-4<?php echo $is_even ? ' me-auto' : ' ms-auto'; ?>">
					<?php if ( $cat_name ) : ?>
					<div class="cw-it8-cat"><?php echo esc_html( $cat_name ); ?></div>
					<?php endif; ?>
					<h2 class="cw-it8-title">
						<a href="<?php the_permalink(); ?>"><?php echo wp_kses_post( $title ); ?></a>
					</h2>
					<?php if ( $excerpt ) : ?>
					<p class="cw-it8-desc"><?php echo esc_html( $excerpt ); ?></p>
					<?php endif; ?>
					<a href="<?php the_permalink(); ?>"
					   class="btn btn-outline-light<?php echo esc_attr( $btn_style ); ?> btn-icon btn-icon-end has-ripple">
						<?php esc_html_e( 'View project', 'codeweber' ); ?>
						<i class="uil uil-arrow-right"></i>
					</a>
					<?php if ( $year ) : ?>
					<div><span class="cw-it8-year"><?php echo esc_html( $year ); ?></span></div>
					<?php endif; ?>
				</div>

			</div>
			<?php
				endwhile;
			?>

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
		(root || document).querySelectorAll('.cw-it8-screen').forEach(function (wrap) {
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

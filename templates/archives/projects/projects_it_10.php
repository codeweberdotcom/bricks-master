<?php
/**
 * Template: Projects Archive — IT / Web (Soft-card + browser bar rows)
 *
 * Same as projects_it_9 but with a browser address bar above the screenshot
 * and an absolute quick view button over the image (appears on card hover).
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

$palettes = [
	[ 'card' => 'bg-soft-grape',   'btn' => 'btn-grape',   'soft' => 'btn-soft-grape',   'bullet' => 'bullet-grape' ],
	[ 'card' => 'bg-soft-primary', 'btn' => 'btn-primary', 'soft' => 'btn-soft-primary', 'bullet' => 'bullet-primary' ],
	[ 'card' => 'bg-soft-yellow',  'btn' => 'btn-yellow',  'soft' => 'btn-soft-yellow',  'bullet' => 'bullet-yellow' ],
	[ 'card' => 'bg-soft-leaf',    'btn' => 'btn-leaf',    'soft' => 'btn-soft-leaf',    'bullet' => 'bullet-leaf' ],
];
?>
<style>
/* ── Browser bar ── */
.cw-browser-bar { height: 32px; }
.cw-browser-dot { width: 10px; height: 10px; }
.cw-browser-dot--red    { background: #ff5f57; }
.cw-browser-dot--yellow { background: #ffbd2e; }
.cw-browser-dot--green  { background: #28c840; }
.cw-browser-url { min-width: 0; font-size: 11px; line-height: 1.6; }
/* ── Screenshot scroll ── */
.cw-it10-screen { max-height: 380px; cursor: pointer; }
.cw-it10-screen img { transform: translateY(0); display: block; }
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
			<?php if ( have_posts() ) :
				$index = 0;
				while ( have_posts() ) : the_post();
					$post_id           = get_the_ID();
					$alt_title         = get_post_meta( $post_id, '_alt_title', true );
					$title             = $alt_title ?: get_the_title();
					$scroll_id         = (int) get_post_meta( $post_id, 'project_it_preview_1', true );
					$img_id            = $scroll_id ?: get_post_thumbnail_id( $post_id );
					$cats              = get_the_terms( $post_id, 'projects_category' );
					$cat_name          = ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : '';
					$cms               = get_post_meta( $post_id, 'main_information_cms', true );
					$client            = get_post_meta( $post_id, 'main_information_client', true );
					$technologies      = get_post_meta( $post_id, 'main_information_technologies', true );
					$short_description = get_post_meta( $post_id, 'main_information_short_description', true );
					$website_url       = get_post_meta( $post_id, 'project_website_url', true );
					$website_open      = get_post_meta( $post_id, 'project_website_open', true ) ?: 'new-tab';
					$year              = get_the_date( 'Y' );
					$is_even           = ( $index % 2 === 1 );
					$palette           = $palettes[ $index % count( $palettes ) ];
					$index++;

					$url_display = $website_url
						? preg_replace( '#^https?://#', '', rtrim( $website_url, '/' ) )
						: '';

					$tags = array_filter( [ $cat_name, $cms, $technologies, $year ] );

					$cat_spans = [];
					if ( $cats && ! is_wp_error( $cats ) ) {
						foreach ( $cats as $cat ) {
							$cat_spans[] = '<span>' . esc_html( $cat->name ) . '</span>';
						}
					}
			?>
			<div class="row gy-10 align-items-center mb-15 mb-md-17">

				<!-- Screenshot card -->
				<div class="col-lg-7<?php echo $is_even ? ' order-lg-2' : ''; ?>">
					<div class="card <?php echo esc_attr( $palette['card'] ); ?>">
						<div class="card-body px-5 px-md-9 py-0 overflow-hidden">
							<div class="mt-5 mt-md-9 position-relative">
							<div class="cw-browser-bar d-flex align-items-center bg-navy gap-1 px-3 py-0 rounded-top">
								<span class="cw-browser-dot cw-browser-dot--red rounded-circle flex-shrink-0"></span>
								<span class="cw-browser-dot cw-browser-dot--yellow rounded-circle flex-shrink-0"></span>
								<span class="cw-browser-dot cw-browser-dot--green rounded-circle flex-shrink-0"></span>
								<?php if ( $url_display ) : ?>
								<span class="cw-browser-url flex-grow-1 text-truncate bg-white rounded-1 px-2 text-muted ms-2"><?php echo esc_html( $url_display ); ?></span>
								<?php endif; ?>
							</div>
							<div class="cw-it10-screen shadow-lg overflow-hidden">
								<?php if ( $img_id ) : ?>
								<?php echo wp_get_attachment_image( $img_id, 'cw_wide_xl', false, [
									'class' => 'w-100 h-100',
									'alt'   => esc_attr( $title ),
								] ); ?>
								<?php endif; ?>
							</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Project info -->
				<div class="col-lg-4<?php echo $is_even ? ' me-auto' : ' ms-auto'; ?>">
					<h2 class="h1 post-title ls-sm mb-2">
						<a href="<?php the_permalink(); ?>" class="link-dark text-decoration-none">
							<?php echo wp_kses_post( $title ); ?>
						</a>
					</h2>
					<?php if ( ! empty( $cat_spans ) ) : ?>
					<div class="post-category text-muted mb-4">
						<?php echo implode( ', ', $cat_spans ); ?>
					</div>
					<?php endif; ?>
					<?php if ( $short_description ) : ?>
					<p class="mb-6"><?php echo esc_html( $short_description ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $tags ) ) : ?>
					<ul class="icon-list <?php echo esc_attr( $palette['bullet'] ); ?> row ms-0 gy-2 mb-5">
						<?php foreach ( $tags as $tag ) : ?>
						<li class="col-md-6">
							<span><i class="uil uil-check"></i></span>
							<span><?php echo esc_html( $tag ); ?></span>
						</li>
						<?php endforeach; ?>
					</ul>
					<?php endif; ?>
					<div class="d-flex flex-column flex-md-row justify-content-center justify-content-lg-start gap-2">
						<a href="<?php the_permalink(); ?>"
						   class="btn <?php echo esc_attr( $palette['btn'] ); ?><?php echo esc_attr( $btn_style ); ?> has-ripple">
							<?php esc_html_e( 'View project', 'codeweber' ); ?>
						</a>
						<?php if ( $website_url ) : ?>
						<button type="button"
							class="btn <?php echo esc_attr( $palette['soft'] ); ?><?php echo esc_attr( $btn_style ); ?> btn-icon btn-icon-start has-ripple"
							data-bs-toggle="modal"
							data-bs-target="#cw-preview-modal"
							data-website-url="<?php echo esc_url( $website_url ); ?>"
							data-website-title="<?php echo esc_attr( wp_strip_all_tags( $title ) ); ?>"
							aria-label="<?php esc_attr_e( 'Quick view', 'codeweber' ); ?>">
							<i class="uil uil-eye"></i>
							<?php esc_html_e( 'Quick view', 'codeweber' ); ?>
						</button>
						<?php endif; ?>
					</div>
				</div>

			</div>
			<?php endwhile; ?>

			<?php codeweber_posts_pagination( [ 'nav_class' => 'd-flex justify-content-center mt-14' ] ); ?>

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

	var SCROLL_SPEED = 150; // px per second

	function getCurrentY(img) {
		var m = window.getComputedStyle(img).transform;
		if (!m || m === 'none') return 0;
		var v = m.match(/matrix\([^,]+,[^,]+,[^,]+,[^,]+,[^,]+,\s*([-\d.]+)\)/);
		return v ? parseFloat(v[1]) : 0;
	}

	function scrollTo(img, targetY) {
		var fromY = getCurrentY(img);
		var dist  = Math.abs(targetY - fromY);
		if (dist < 1) return;
		img.style.transition = 'transform ' + (dist / SCROLL_SPEED).toFixed(2) + 's linear';
		img.style.transform  = 'translateY(' + targetY + 'px)';
	}

	function initScreenScroll(root) {
		(root || document).querySelectorAll('.cw-it10-screen').forEach(function (wrap) {
			if (wrap.dataset.cwScrollInit) return;
			wrap.dataset.cwScrollInit = '1';

			var img    = wrap.querySelector('img');
			if (!img) return;

			var paused = false;
			var target = 0;

			function getScrollDist() {
				var imgH = img.naturalHeight * (img.offsetWidth / img.naturalWidth);
				return Math.max(0, imgH - wrap.offsetHeight);
			}

			wrap.addEventListener('mouseenter', function () {
				if (paused) return;
				var dist = getScrollDist();
				if (dist <= 0) return;
				target = -Math.round(dist * 0.9);
				scrollTo(img, target);
			});
			wrap.addEventListener('mouseleave', function () {
				if (paused) return;
				target = 0;
				scrollTo(img, 0);
			});
			wrap.addEventListener('click', function () {
				paused = !paused;
				if (paused) {
					var y = getCurrentY(img);
					img.style.transition = 'none';
					img.style.transform  = 'translateY(' + y + 'px)';
				} else {
					scrollTo(img, target);
				}
			});
		});
	}
	initScreenScroll();

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
			body.append('params',     JSON.stringify({ post_type: 'projects', template: 'projects_it_10', filters: filters }));

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

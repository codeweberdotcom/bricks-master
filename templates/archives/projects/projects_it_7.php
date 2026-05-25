<?php
/**
 * Template: Projects Archive — IT / Web (Staggered rows)
 *
 * Each project: colored card, two staggered screenshot columns, text on the right.
 * Screenshot scrolls on card hover. Based on portfolio snippet-11.
 *
 * @package Codeweber
 */

defined( 'ABSPATH' ) || exit;

$card_radius = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : 'rounded';
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

$palettes = [
	[ 'card' => 'bg-soft-primary', 'bullet' => 'bullet-primary', 'btn' => 'btn-primary' ],
	[ 'card' => 'bg-soft-leaf',    'bullet' => 'bullet-leaf',    'btn' => 'btn-leaf'    ],
	[ 'card' => 'bg-soft-yellow',  'bullet' => 'bullet-yellow',  'btn' => 'btn-yellow'  ],
	[ 'card' => 'bg-soft-orange',  'bullet' => 'bullet-orange',  'btn' => 'btn-orange'  ],
];
?>

<style>
/* ── Screenshot scroll ── */
.cw-it7-figure {
	overflow: hidden;
	position: relative;
	height: 280px;
}
.cw-it7-img {
	display: block;
	width: 100%;
	height: auto;
	transition: transform 5s linear;
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
			<div class="demos-wrapper">
				<?php
				$index = 0;
				while ( have_posts() ) : the_post();
					$post_id      = get_the_ID();
					$alt_title    = get_post_meta( $post_id, '_alt_title', true );
					$title        = $alt_title ?: get_the_title();
					$cms          = get_post_meta( $post_id, 'main_information_cms', true );
					$client       = get_post_meta( $post_id, 'main_information_client', true );
					$website_url  = get_post_meta( $post_id, 'project_website_url', true );
					$website_open = get_post_meta( $post_id, 'project_website_open', true ) ?: 'new-tab';
					$website_cta  = get_post_meta( $post_id, 'project_website_cta', true ) ?: __( 'View website', 'codeweber' );
					$thumbnail_id = get_post_thumbnail_id( $post_id );
					$cats         = get_the_terms( $post_id, 'projects_category' );
					$cat_name     = ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : '';

					$link_target = $website_open === 'same-tab' ? '_self' : '_blank';
					$link_rel    = $website_open !== 'same-tab' ? 'noopener noreferrer' : '';

					$palette = $palettes[ $index % count( $palettes ) ];
					$index++;

					$tags = array_filter( [ $cat_name, $cms, $client ] );
				?>
				<div class="project mb-10">
					<div class="card <?php echo esc_attr( $palette['card'] ); ?> <?php echo esc_attr( $card_radius ); ?>">
						<div class="card-body ps-xl-12 py-0 overflow-hidden">
							<div class="row gx-md-8 gx-xl-12 d-flex align-items-center">

								<!-- Screenshots -->
								<div class="col-lg-7">
									<div class="row gx-7">

										<div class="col-6">
											<figure class="cw-it7-figure cw-it7-figure--top mt-9">
												<a href="<?php the_permalink(); ?>">
													<?php if ( $thumbnail_id ) : ?>
													<?php echo wp_get_attachment_image( $thumbnail_id, 'cw_wide_xl', false, [
														'class' => 'cw-it7-img shadow-lg rounded-top',
														'alt'   => esc_attr( $title ),
													] ); ?>
													<?php else : ?>
													<div class="shadow-lg rounded-top" style="height:240px;background:#dee2e6;"></div>
													<?php endif; ?>
												</a>
											</figure>
										</div>

										<div class="col-6">
											<figure class="cw-it7-figure cw-it7-figure--bottom">
												<a href="<?php the_permalink(); ?>">
													<?php if ( $thumbnail_id ) : ?>
													<?php echo wp_get_attachment_image( $thumbnail_id, 'cw_wide_xl', false, [
														'class' => 'cw-it7-img cw-it7-img--offset shadow-lg rounded-bottom',
														'alt'   => esc_attr( $title ),
													] ); ?>
													<?php else : ?>
													<div class="shadow-lg rounded-bottom" style="height:240px;background:#dee2e6;"></div>
													<?php endif; ?>
												</a>
											</figure>
										</div>

									</div>
								</div>

								<!-- Project info -->
								<div class="col-lg-5 d-none d-lg-block">
									<h2 class="post-title fs-30 mb-4">
										<a href="<?php the_permalink(); ?>" class="link-dark text-decoration-none">
											<?php echo wp_kses_post( $title ); ?>
										</a>
									</h2>
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
									<a href="<?php the_permalink(); ?>"
									   class="btn btn-sm <?php echo esc_attr( $palette['btn'] ); ?> rounded-pill mt-1">
										<?php esc_html_e( 'View project', 'codeweber' ); ?>
									</a>
									<?php if ( $website_url ) : ?>
									<a href="<?php echo esc_url( $website_url ); ?>"
									   target="<?php echo esc_attr( $link_target ); ?>"
									   <?php if ( $link_rel ) : ?>rel="<?php echo esc_attr( $link_rel ); ?>"<?php endif; ?>
									   class="btn btn-sm btn-outline-primary rounded-pill mt-1 ms-2 btn-icon btn-icon-start has-ripple">
										<i class="uil uil-external-link-alt"></i>
										<?php echo esc_html( $website_cta ); ?>
									</a>
									<?php endif; ?>
								</div>

							</div>
						</div>
					</div>
				</div>
				<?php endwhile; ?>
			</div>

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

	// ── Screenshot scroll on project hover ───────────────────────────
	function initScreenScroll(root) {
		(root || document).querySelectorAll('.demos-wrapper .project').forEach(function (project) {
			if (project.dataset.cwScrollInit) return;
			project.dataset.cwScrollInit = '1';

			var figures = project.querySelectorAll('.cw-it7-figure');

			figures.forEach(function (fig) {
				var img = fig.querySelector('.cw-it7-img');
				if (!img) return;

				var isOffset = img.classList.contains('cw-it7-img--offset');

				function getScrollDist() {
					var imgH = img.naturalHeight * (img.offsetWidth / img.naturalWidth);
					return Math.max(0, imgH - fig.offsetHeight);
				}

				// Pre-scroll the right column to mid-page on load
				function applyInitialOffset() {
					if (!isOffset) return;
					var dist = getScrollDist();
					if (dist > 0) img.style.transform = 'translateY(-' + Math.round(dist * 0.4) + 'px)';
				}
				if (img.complete && img.naturalWidth) {
					applyInitialOffset();
				} else {
					img.addEventListener('load', applyInitialOffset, { once: true });
				}

				project.addEventListener('mouseenter', function () {
					var dist = getScrollDist();
					if (dist <= 0) return;
					// Left: scroll down; Right: scroll back toward top
					var target = isOffset ? Math.round(dist * 0.05) : Math.round(dist * 0.85);
					img.style.transform = 'translateY(-' + target + 'px)';
				});
				project.addEventListener('mouseleave', function () {
					var dist = getScrollDist();
					img.style.transform = isOffset && dist > 0
						? 'translateY(-' + Math.round(dist * 0.4) + 'px)'
						: 'translateY(0)';
				});
			});
		});
	}
	initScreenScroll();

	// ── Category AJAX filter ──────────────────────────────────────────
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
			body.append('params',     JSON.stringify({ post_type: 'projects', template: 'projects_it_7', filters: filters }));

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

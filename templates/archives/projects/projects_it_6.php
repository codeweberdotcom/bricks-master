<?php
/**
 * Template: Projects Archive — IT / Web (Featured rows)
 *
 * Each project occupies a full row: screenshot card (col-lg-7) + text (col-lg-4).
 * Odd rows image-left, even rows image-right. Screenshot scrolls on hover.
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

$card_colors = [ 'bg-soft-primary', 'bg-soft-leaf', 'bg-soft-yellow', 'bg-soft-orange' ];
?>

<style>
/* ── Screenshot scroll on hover ── */
.cw-it6-screen {
	overflow: hidden;
	height: 340px;
	position: relative;
}
.cw-it6-screenshot {
	display: block;
	width: 100%;
	height: auto;
	transition: transform 10s linear;
	transform: translateY(0);
}
.cw-it6-screenshot-placeholder {
	width: 100%;
	height: 340px;
	background: #e9ecef;
}
/* ── Row divider ── */
.cw-it6-row + .cw-it6-row {
	padding-top: 5rem;
}
@media (min-width: 768px) {
	.cw-it6-row + .cw-it6-row {
		padding-top: 6rem;
	}
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
			<?php if ( have_posts() ) :
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
					$thumbnail_id = (int) get_post_meta( $post_id, 'project_it_preview_1', true );
					$cats         = get_the_terms( $post_id, 'projects_category' );
					$cat_name     = ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : '';

					$link_target = $website_open === 'same-tab' ? '_self' : '_blank';
					$link_rel    = $website_open !== 'same-tab' ? 'noopener noreferrer' : '';

					$is_even    = ( $index % 2 === 1 );
					$card_color = $card_colors[ $index % count( $card_colors ) ];
					$index++;
			?>
			<div class="cw-it6-row row gy-10 align-items-center">

				<!-- Screenshot card -->
				<div class="col-lg-7<?php echo $is_even ? ' order-lg-2' : ''; ?>">
					<div class="card <?php echo esc_attr( $card_color ); ?> <?php echo esc_attr( $card_radius ); ?>">
						<div class="card-body p-6 p-md-8 overflow-hidden">
							<a href="<?php the_permalink(); ?>" class="d-block text-decoration-none">
								<div class="cw-it6-screen shadow-lg <?php echo esc_attr( $card_radius ); ?>">
									<?php if ( $thumbnail_id ) : ?>
										<?php echo wp_get_attachment_image( $thumbnail_id, 'cw_wide_xl', false, [
											'class' => 'cw-it6-screenshot',
											'alt'   => esc_attr( $title ),
										] ); ?>
									<?php else : ?>
										<div class="cw-it6-screenshot-placeholder"></div>
									<?php endif; ?>
								</div>
							</a>
						</div>
					</div>
				</div>

				<!-- Project info -->
				<div class="col-lg-4<?php echo $is_even ? ' me-auto' : ' ms-auto'; ?>">
					<?php if ( $cat_name ) : ?>
					<div class="post-category text-line mb-3"><?php echo esc_html( $cat_name ); ?></div>
					<?php endif; ?>
					<h2 class="post-title display-6 ls-sm mb-3">
						<a href="<?php the_permalink(); ?>" class="link-dark text-decoration-none">
							<?php echo wp_kses_post( $title ); ?>
						</a>
					</h2>
					<?php if ( $client || $cms ) : ?>
					<p class="text-muted mb-4">
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
					<a href="<?php the_permalink(); ?>"
					   class="btn btn-primary<?php echo esc_attr( $btn_style ); ?> has-ripple">
						<?php esc_html_e( 'View project', 'codeweber' ); ?>
					</a>
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

	// ── Screenshot scroll on hover ────────────────────────────────────
	function initScreenScroll(root) {
		(root || document).querySelectorAll('.cw-it6-screen').forEach(function (wrap) {
			if (wrap.dataset.cwScrollInit) return;
			wrap.dataset.cwScrollInit = '1';

			var img = wrap.querySelector('.cw-it6-screenshot');
			if (!img) return;

			function getScrollDist() {
				var imgH = img.naturalHeight * (img.offsetWidth / img.naturalWidth);
				return Math.max(0, imgH - wrap.offsetHeight);
			}
			wrap.addEventListener('mouseenter', function () {
				var dist = getScrollDist();
				if (dist > 0) img.style.transform = 'translateY(-' + dist + 'px)';
			});
			wrap.addEventListener('mouseleave', function () {
				img.style.transform = 'translateY(0)';
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
			body.append('params',     JSON.stringify({ post_type: 'projects', template: 'projects_it_6', filters: filters }));

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

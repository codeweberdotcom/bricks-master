<?php
/**
 * Template: Projects Archive — IT / Web (Website Portfolio)
 *
 * AJAX category filter + grid with browser mockup cards.
 * Shows website URL, CMS/tech, CTA button.
 *
 * @package Codeweber
 */

defined( 'ABSPATH' ) || exit;

$card_radius  = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : 'rounded';
$grid_gap     = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'grid-gap' ) : 'gx-md-8 gy-10 gy-md-13';
$btn_style    = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button' ) : ' rounded-pill';

$filter_terms = get_terms( [
	'taxonomy'   => 'projects_category',
	'hide_empty' => true,
	'orderby'    => 'name',
	'order'      => 'ASC',
] );
$has_filters = ! empty( $filter_terms ) && ! is_wp_error( $filter_terms );
?>

<section id="content-wrapper" class="wrapper">
	<div class="container py-14 py-md-16">

		<?php if ( $has_filters ) : ?>
		<div class="isotope-filter filter projects-category-filters mb-10">
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
					$thumbnail_id = get_post_thumbnail_id( $post_id );
					$cats         = get_the_terms( $post_id, 'projects_category' );
					$cat_name     = ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : '';

					$link_target = $website_open === 'same-tab' ? '_self' : '_blank';
					$link_rel    = $website_open !== 'same-tab' ? ' rel="noopener noreferrer"' : '';
				?>
				<div class="col-md-6 col-xl-4">
					<div class="cw-it-card card h-100 border-0 shadow-sm <?php echo esc_attr( $card_radius ); ?>">

						<!-- Browser mockup -->
						<a href="<?php the_permalink(); ?>" class="cw-it-card__browser d-block text-decoration-none">
							<div class="cw-it-card__bar d-flex align-items-center gap-1 px-3" style="height:32px;background:#e9ecef;border-radius:inherit;border-bottom-left-radius:0;border-bottom-right-radius:0;">
								<span style="width:10px;height:10px;border-radius:50%;background:#ff5f57;flex-shrink:0;"></span>
								<span style="width:10px;height:10px;border-radius:50%;background:#ffbd2e;flex-shrink:0;"></span>
								<span style="width:10px;height:10px;border-radius:50%;background:#28c840;flex-shrink:0;"></span>
								<?php if ( $website_url ) : ?>
								<span class="ms-2 flex-grow-1 text-truncate" style="background:#fff;border-radius:3px;padding:2px 8px;font-size:11px;color:#6c757d;line-height:1.6;">
									<?php echo esc_html( preg_replace( '#^https?://#', '', rtrim( $website_url, '/' ) ) ); ?>
								</span>
								<?php endif; ?>
							</div>
							<div class="overflow-hidden" style="border-bottom-left-radius:inherit;border-bottom-right-radius:inherit;">
								<?php if ( $thumbnail_id ) : ?>
									<?php echo wp_get_attachment_image( $thumbnail_id, 'cw_wide_xl', false, [
										'class' => 'w-100 d-block cw-it-card__img',
										'alt'   => esc_attr( $title ),
										'style' => 'aspect-ratio:16/9;object-fit:cover;',
									] ); ?>
								<?php else : ?>
									<div style="aspect-ratio:16/9;background:#f1f3f5;"></div>
								<?php endif; ?>
							</div>
						</a>

						<!-- Card body -->
						<div class="card-body p-4">
							<?php if ( $cat_name ) : ?>
							<div class="post-category text-line mb-2 fs-sm"><?php echo esc_html( $cat_name ); ?></div>
							<?php endif; ?>

							<h2 class="h5 mb-2">
								<a href="<?php the_permalink(); ?>" class="link-dark stretched-link text-decoration-none">
									<?php echo wp_kses_post( $title ); ?>
								</a>
							</h2>

							<?php if ( $client || $cms ) : ?>
							<p class="text-muted fs-sm mb-3" style="font-size:.8125rem;">
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

							<?php if ( $website_url ) : ?>
							<a href="<?php echo esc_url( $website_url ); ?>"
							   target="<?php echo esc_attr( $link_target ); ?>"
							   <?php echo $link_rel; // Already escaped ?>
							   class="btn btn-sm btn-outline-primary<?php echo esc_attr( $btn_style ); ?> position-relative"
							   style="z-index:2;">
								<?php echo esc_html( $website_cta ); ?> <i class="uil uil-external-link-alt ms-1"></i>
							</a>
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

<script>
(function () {
	var catBtns     = document.querySelectorAll('.projects-category-filters .filter-item');
	var resultsWrap = document.getElementById('projects-grid-results');

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
			if (catId && catId !== '0') {
				filters.projects_category = catId;
			}

			var body = new FormData();
			body.append('action',     'fetch_action');
			body.append('nonce',      fetch_vars.nonce);
			body.append('actionType', 'filterPosts');
			body.append('params',     JSON.stringify({ post_type: 'projects', template: 'projects_it_1', filters: filters }));

			fetch(fetch_vars.ajaxurl, { method: 'POST', body: body })
				.then(function (r) { return r.json(); })
				.then(function (data) {
					if (data.status === 'success' && resultsWrap) {
						resultsWrap.innerHTML = data.data.html;
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

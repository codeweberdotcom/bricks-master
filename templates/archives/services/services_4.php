<?php
/**
 * Template: Services Archive — Style 4 (Overlay-5, 4 columns + category filter)
 *
 * @package Codeweber
 */

defined( 'ABSPATH' ) || exit;

$card_radius  = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : 'rounded';
$grid_gap     = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'grid-gap' ) : 'g-6';
$placeholder  = get_template_directory_uri() . '/dist/assets/img/image-placeholder.jpg';

$filter_terms = get_terms( [
	'taxonomy'   => 'service_category',
	'hide_empty' => true,
	'orderby'    => 'name',
	'order'      => 'ASC',
] );
$has_filters = ! empty( $filter_terms ) && ! is_wp_error( $filter_terms );

$all_services = new WP_Query( [
	'post_type'      => 'services',
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	'orderby'        => 'menu_order date',
	'order'          => 'ASC',
] );
?>

<section class="wrapper">
	<div class="container py-14 py-md-16">

		<?php if ( $has_filters ) : ?>
		<div class="isotope-filter filter services-category-filters mb-10">
			<ul>
				<li><a class="filter-item active" data-cat-id="0"><?php esc_html_e( 'All', 'codeweber' ); ?></a></li>
				<?php foreach ( $filter_terms as $term ) : ?>
				<li><a class="filter-item" data-cat-id="<?php echo esc_attr( $term->term_id ); ?>"><?php echo esc_html( $term->name ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php endif; ?>

		<div id="services-grid-results">
			<?php if ( $all_services->have_posts() ) : ?>
			<div class="row <?php echo esc_attr( $grid_gap ); ?>">
				<?php while ( $all_services->have_posts() ) : $all_services->the_post();
					$post_id    = get_the_ID();
					$thumb_id   = get_post_thumbnail_id( $post_id );
					$short_desc = get_post_meta( $post_id, '_service_short_description', true );
				?>
				<div class="col-12 col-md-3">
					<figure class="overlay overlay-5 <?php echo esc_attr( $card_radius ); ?> card-interactive mb-0">
						<a href="<?php the_permalink(); ?>">
							<div class="bottom-overlay post-meta fs-16 position-absolute zindex-1 d-flex flex-column h-100 w-100 p-5">
								<div class="mt-auto">
									<h3 class="h5 text-white mb-0"><?php the_title(); ?></h3>
								</div>
							</div>
							<?php if ( $thumb_id ) : ?>
								<?php echo wp_get_attachment_image( $thumb_id, 'cw_square_md', false, [
									'class' => 'w-100 ' . esc_attr( $card_radius ),
									'alt'   => esc_attr( get_the_title() ),
								] ); ?>
							<?php else : ?>
								<img src="<?php echo esc_url( $placeholder ); ?>" alt="" class="w-100 <?php echo esc_attr( $card_radius ); ?>">
							<?php endif; ?>
						</a>

						<figcaption class="p-5">
							<div class="post-body h-100 d-flex flex-column from-left justify-content-end">
								<?php if ( $short_desc ) : ?>
									<p class="mb-3"><?php echo esc_html( $short_desc ); ?></p>
								<?php endif; ?>
								<span class="hover more me-4"><?php esc_html_e( 'More details', 'codeweber' ); ?></span>
							</div>
						</figcaption>

						<div class="hover_card_button_hide position-absolute top-0 end-0 p-5 zindex-10">
							<i class="fs-25 uil uil-arrow-right lh-1"></i>
						</div>
					</figure>
				</div>
				<!--/column -->
				<?php endwhile; wp_reset_postdata(); ?>
			</div>
			<!--/.row -->

			<?php else : ?>
			<p class="text-muted"><?php esc_html_e( 'No services found.', 'codeweber' ); ?></p>
			<?php endif; ?>
		</div><!-- #services-grid-results -->

	</div>
	<!--/.container -->
</section>

<script>
(function () {
	var catBtns     = document.querySelectorAll('.services-category-filters .filter-item');
	var resultsWrap = document.getElementById('services-grid-results');

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
				filters.service_category = catId;
			}

			var body = new FormData();
			body.append('action',     'fetch_action');
			body.append('nonce',      fetch_vars.nonce);
			body.append('actionType', 'filterPosts');
			body.append('params',     JSON.stringify({ post_type: 'services', template: 'services_4', filters: filters }));

			fetch(fetch_vars.ajaxurl, { method: 'POST', body: body })
				.then(function (r) { return r.json(); })
				.then(function (data) {
					if (data.status === 'success' && resultsWrap) {
						resultsWrap.innerHTML = data.data.html;
						if (typeof window.theme !== 'undefined') {
							if (typeof window.theme.imageHoverOverlay === 'function') window.theme.imageHoverOverlay();
						}
					}
				})
				.catch(function (e) { console.error('Services filter error:', e); })
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

<?php
/**
 * Template: Projects Archive — IT / Web (Overlay Cards)
 *
 * Isotope grid with bottom-overlay cards — category + title always visible at bottom.
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
					$post_id   = get_the_ID();
					$alt_title = get_post_meta( $post_id, '_alt_title', true );
					$cats      = get_the_terms( $post_id, 'projects_category' );

					$item_classes = 'project item col-md-6 col-xl-4';
					if ( $cats && ! is_wp_error( $cats ) ) {
						foreach ( $cats as $cat ) {
							$item_classes .= ' ' . sanitize_html_class( $cat->slug );
						}
					}

					$cat_name      = ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : '';
					$thumbnail_id  = get_post_thumbnail_id( $post_id );
					$display_title = $alt_title ?: get_the_title();
				?>
				<div class="<?php echo esc_attr( $item_classes ); ?>">
					<figure class="bottom-overlay hover-scale <?php echo esc_attr( $card_radius ); ?> position-relative mb-0">
						<a href="<?php the_permalink(); ?>">
							<?php if ( $thumbnail_id ) : ?>
								<?php echo wp_get_attachment_image( $thumbnail_id, 'cw_square_xl', false, [
									'class' => 'w-100',
									'alt'   => esc_attr( $display_title ),
								] ); ?>
							<?php else : ?>
								<div class="w-100" style="aspect-ratio:1/1;background:#e9ecef;"></div>
							<?php endif; ?>
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

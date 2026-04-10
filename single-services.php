<?php
/**
 * Single Services
 *
 * Gutenberg-совместимый шаблон без контейнера.
 * Блоки управляют шириной самостоятельно (align-wide / align-full).
 *
 * @package Codeweber
 */

get_header();

while ( have_posts() ) :
	the_post();
	get_pageheader();
	?>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'cw-service-single' ); ?>>

		<?php the_content(); ?>

		<?php
		wp_link_pages( [
			'before'      => '<nav class="nav"><span class="nav-link">' . esc_html__( 'Part:', 'codeweber' ) . '</span>',
			'after'       => '</nav>',
			'link_before' => '<span class="nav-link">',
			'link_after'  => '</span>',
		] );
		?>

	</article>

	<?php
endwhile;

get_footer();

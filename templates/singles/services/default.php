<?php
/**
 * Template: Single Services — Default
 *
 * Gutenberg-совместимый шаблон без контейнера.
 * Блоки управляют шириной самостоятельно (align-wide / align-full).
 *
 * @package Codeweber
 */

defined( 'ABSPATH' ) || exit;
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

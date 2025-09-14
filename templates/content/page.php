<?php get_header(); ?>
<?php while (have_posts()) {
	the_post();
	get_pageheader();
	global $opt_name;
	$pageheader_name = Redux::get_option($opt_name, 'global_page_header_model');
?>

	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<?php if ($pageheader_name === '1' && !is_front_page()) { ?>
			<div class="container py-14">
				<div class="row align-items-center mb-10 position-relative zindex-1">
					<div class="col-md-8 col-lg-9 col-xl-8 col-xxl-7">
						<?php echo universal_title('h1','theme'); ?>
					</div>
				</div>
			</div>
		<?php } ?>
		<div>
			<?php
			the_content();

			wp_link_pages(
				array(
					'before'        => '<nav class="nav"><span class="nav-link">' . esc_html__('Part:', 'bricks') . '</span>',
					'after'         => '</nav>',
					'link_before'   => '<span class="nav-link">',
					'link_after'    => '</span>',
				)
			);
			?>
		</div>
	</article> <!-- #post-<?php the_ID(); ?> -->

<?php } ?>
<?php
get_footer();

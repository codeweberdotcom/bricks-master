<?php get_header(); ?>
<?php $global_page_header_model = Redux::get_option($opt_name, 'global-page-header-model'); ?>
<?php
while (have_posts()) :
	the_post();
	get_pageheader();
?>
	<?php $post_type = get_post_type();
	$post_type_lc = strtolower($post_type);
	$sidebar_position = Redux::get_option($opt_name, 'sidebar-position-archive-' . ucwords($post_type));
	$pageheader_name = Redux::get_option($opt_name, 'global-page-header-model');
	?>

	<section id="content-wrapper" class="wrapper bg-light">


			<div class="container">
				<div class="row gx-lg-8 gx-xl-12">

					<?php get_sidebar('left'); ?>
					<!-- #sidebar-left -->

					<div id="article-wrapper" class="col-8 py-12">

						<?php if ($pageheader_name === '1') { ?>
								<h1 class="display-4 mb-10"><?php echo universal_title(); ?></h1>
						<?php } ?>
						<!-- #title -->


						<?php get_template_part('templates/content/single', ''); ?>
						<nav class="nav">
							<?php
							previous_post_link('<span class="nav-link me-auto">&laquo; %link</span>');
							next_post_link('<span class="nav-link ms-auto">%link &raquo;</span>');
							?>
						</nav>
					</div> <!-- #article-wrapper -->

					<?php get_sidebar('right'); ?>
					<!-- #sidebar-right -->
				</div>


		</div>

	<?php endwhile ?>

	</main> <!-- #content-wrapper -->

	<?php
	get_footer();

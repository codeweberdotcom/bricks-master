<?php get_header();

while (have_posts()) :
	the_post();
	get_pageheader();

	$post_type = get_post_type();
	$post_type_lc = strtolower($post_type);
	$sidebar_position = get_sidebar_position($opt_name);

	// Определяем класс контента
	$content_class = ($sidebar_position === 'none') ? 'col-12' : 'col-md-8';

	$pageheader_name = Redux::get_option($opt_name, 'global_page_header_model');

?>

	<section id="content-wrapper" class="wrapper bg-light">
		<div class="container">
			<div class="row gx-lg-8 gx-xl-12">
				<?php get_sidebar('left'); ?>
				<!-- #sidebar-left -->

				<div id="article-wrapper" class="<?php echo $content_class; ?> py-12">
					<?php if ($pageheader_name === '1') { ?>
						<h1 class="display-4 mb-10"><?php echo universal_title(); ?></h1>
					<?php } ?>
					<!-- #title -->

					<?php
					$templatesingle = Redux::get_option($opt_name, 'single_template_select_' . $post_type);
					$template_file = "templates/singles/{$post_type_lc}/{$templatesingle}.php";
					if (!empty($templateloop) && locate_template($template_file)) {
						get_template_part("templates/content/single/{$post_type_lc}/{$templatesingle}");
					} else {
						if (locate_template("templates/content/single-{$post_type_lc}.php")) {
							get_template_part("templates/content/single", $post_type_lc);
						} else {
							get_template_part("templates/content/single", '');
						}
					}
					?>

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

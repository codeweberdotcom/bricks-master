<?php get_header(); ?>
<?php
while (have_posts()) {
	the_post();
	get_pageheader();
?>
	<main id="content-wrapper">

		<div class="container">

			<?php
			// Get this page slug
			$slug = $post->post_name;

			// Check if template file exists, set the template to be used
			$template_name = (is_file(get_theme_file_path('templates/content/page-' . $slug . '.php'))) ? $slug : '';

			get_template_part('templates/content/page', $template_name);

			if (comments_open() || get_comments_number()) {
				comments_template();
			}

			?>

		</div>

	<?php } ?>

	</main> <!-- #content-wrapper -->

	<?php
	get_footer();

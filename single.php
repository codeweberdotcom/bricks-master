<?php get_header();

while (have_posts()) :
	the_post();
	get_pageheader();

	if (is_post_type_archive()) {
		$post_type = get_queried_object()->name ?? '';
	} elseif (is_tax() || is_category() || is_tag()) {
		$taxonomy = get_queried_object()->taxonomy ?? '';
		$taxonomy_obj = get_taxonomy($taxonomy);
		$post_type = $taxonomy_obj->object_type[0] ?? 'post';
	} else {
		global $wp_query;
		$post_type = $wp_query->get('post_type') ?? 'post';

		// Если массив, берем первый элемент
		if (is_array($post_type)) {
			$post_type = $post_type[0];
		}
	}

	$post_type_lc = strtolower($post_type);
	$sidebar_position = get_sidebar_position($opt_name);

	// Определяем класс контента
	$content_class = ($sidebar_position === 'none') ? 'col-12' : 'col-md-8';
	$pageheader_name = Redux::get_option($opt_name, 'global_page_header_model');

	// Проверяем, не отключен ли заголовок для этого типа записи
	$single_pageheader_id = Redux::get_option($opt_name, 'single_page_header_select_' . $post_type);
	$show_universal_title = ($single_pageheader_id !== 'disabled');
?>

	<section class="wrapper bg-light">
		<div class="container">
			<?php do_action('before_single_content', $post_type); ?>
			<div class="row gx-lg-8 gx-xl-12">
				<?php get_sidebar('left'); ?>
				<!-- #sidebar-left -->

				<div id="article-wrapper" class="<?php echo $content_class; ?> py-12">
					<?php if ($pageheader_name === '1' && $show_universal_title) { ?>
						<h1 class="display-4 mb-10"><?php echo universal_title(); ?></h1>
					<?php } ?>
					<!-- #title -->

					<?php
					$templatesingle = Redux::get_option($opt_name, 'single_template_select_' . $post_type);
					$template_file = "templates/singles/{$post_type_lc}/{$templatesingle}.php";

					// Проверяем, не отключен ли вообще вывод контента
					if ($single_pageheader_id !== 'disabled') {
						if (!empty($templatesingle) && locate_template($template_file)) {
							get_template_part("templates/content/single/{$post_type_lc}/{$templatesingle}");
						} else {
							if (locate_template("templates/content/single-{$post_type_lc}.php")) {
								get_template_part("templates/content/single", $post_type_lc);
							} else {
								get_template_part("templates/content/single", '');
							}
						}
					}
					?>
					<?php if ($single_pageheader_id !== 'disabled') : ?>
						<nav class="nav">
							<?php
							previous_post_link('<span class="nav-link me-auto">&laquo; %link</span>');
							next_post_link('<span class="nav-link ms-auto">%link &raquo;</span>');
							?>
						</nav>
					<?php endif; ?>
				</div> <!-- #article-wrapper -->

				<?php get_sidebar('right'); ?>
				<!-- #sidebar-right -->
				<?php do_action('after_single_content', $post_type); ?>
			</div>
		</div>
	</section> <!-- #content-wrapper -->

<?php
endwhile;
get_footer();

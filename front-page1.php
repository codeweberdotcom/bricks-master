<?php get_header(); ?>
<?php get_pageheader(); ?>

<main id="content-wrapper">

	<section id="section-hero" class="has-img-background">

		<?php

		get_template_part('templates/sections/home', 'hero');

		?>

	</section> <!-- #section-hero -->

	<section id="section-services">

		<?php

		$args = array(
			'post_type' => 'post',
			'posts_per_page'    => 3,
		);

		get_template_part('templates/sections/home', 'services', $args);

		?>

	</section> <!-- #section-services -->

	<section id="section-slider">

		<?php

		$args = array(
			'post_type' => 'post',
			'posts_per_page'    => 3,
		);

		get_template_part('templates/sections/home', 'slider', $args);

		?>

	</section> <!-- #section-slider -->

	<section id="section-news">

		<?php

		$args = array(
			'post_type' => 'post',
			'posts_per_page'    => 3,
		);

		get_template_part('templates/sections/home', 'news', $args);

		?>

	</section> <!-- #section-news -->

	<section id="section-cta">

		<?php

		$args = array(
			'post_type' => 'post',
			'posts_per_page'    => 1,
		);

		get_template_part('templates/sections/home', 'cta', $args);

		?>

	</section> <!-- #section-cta -->

</main> <!-- #content-wrapper -->

<?php
get_footer();

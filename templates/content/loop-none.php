<section class="wrapper bg-light">
	<div class="container py-14 py-md-16">

		<p>
			<?php if (is_search()) {

				esc_html_e('Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'codeweber');
			} else {

				esc_html_e('It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'codeweber');
			}; ?>
		</p>

		<?php get_search_form(); ?>

	</div>
</section>
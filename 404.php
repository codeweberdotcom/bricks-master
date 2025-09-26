<?php get_header(); ?>

<section class="wrapper bg-light">
	<div class="container pt-12 pt-md-14 pb-14 pb-md-16">
		<div class="row">
			<div class="col-lg-9 col-xl-8 mx-auto">
				<figure class="mb-10">
					<img class="img-fluid" src="<?php echo esc_url(get_template_directory_uri() . '/dist/assets/img/illustrations/404.png'); ?>" srcset="<?php echo esc_url(get_template_directory_uri() . '/dist/assets/img/illustrations/404@2x.png'); ?> 2x" alt="<?php echo esc_attr__('404 Error', 'codeweber'); ?>">
				</figure>
			</div>
			<!-- /column -->
			<div class="col-lg-8 col-xl-7 col-xxl-6 mx-auto text-center">
				<h1 class="mb-3"><?php echo esc_html__('Oops! Page Not Found.', 'codeweber'); ?></h1>
				<p class="lead mb-7 px-md-12 px-lg-5 px-xl-7">
					<?php echo esc_html__('The page you are looking for is not available or has been moved. Try a different page or go to homepage with the button below.', 'codeweber'); ?>
				</p>
				<a href="<?php echo esc_url(home_url()); ?>" class="btn btn-primary <?php GetThemeButton(); ?>'">
					<?php echo esc_html__('Go to Homepage', 'codeweber'); ?>
				</a>
			</div>
			<!-- /column -->
		</div>
		<!-- /.row -->
	</div>
	<!-- /.container -->
</section>

<?php
get_footer();

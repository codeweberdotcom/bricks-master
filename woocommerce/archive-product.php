<?php
/**
 * WooCommerce Archive Product — Shop Page
 * Style: shop2 (sidebar left, isotope grid)
 *
 * Переопределяет woocommerce/archive-product.php из плагина WooCommerce.
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_pageheader();
?>

<section class="wrapper bg-light">
	<div class="container pb-14 pb-md-16 pt-12">

		<?php if ( woocommerce_product_loop() ) : ?>

		<div class="row gy-10">

			<!-- Колонка с товарами -->
			<div class="col-lg-9 order-lg-2">

				<!-- Сортировка + результаты -->
				<div class="row align-items-center mb-10 position-relative zindex-1">
					<div class="col-md-7 col-xl-8 pe-xl-20">
						<?php woocommerce_result_count(); ?>
					</div>
					<!--/column -->
					<div class="col-md-5 col-xl-4 ms-md-auto text-md-end mt-5 mt-md-0">
						<div class="form-select-wrapper">
							<?php woocommerce_catalog_ordering(); ?>
						</div>
						<!--/.form-select-wrapper -->
					</div>
					<!--/column -->
				</div>
				<!--/.row -->

				<!-- Сетка товаров -->
				<div class="grid grid-view projects-masonry shop mb-13">
					<div class="row gx-md-8 gy-10 gy-md-13 isotope">
						<?php while ( have_posts() ) : the_post(); ?>
							<?php wc_get_template_part( 'content', 'product' ); ?>
						<?php endwhile; ?>
					</div>
					<!-- /.row -->
				</div>
				<!-- /.grid -->

				<?php woocommerce_pagination(); ?>

			</div>
			<!-- /column -->

			<!-- Сайдбар -->
			<aside class="col-lg-3 sidebar">
				<?php if ( is_active_sidebar( 'sidebar-woo' ) ) : ?>
					<?php dynamic_sidebar( 'sidebar-woo' ); ?>
				<?php endif; ?>
			</aside>
			<!-- /aside.sidebar -->

		</div>
		<!-- /.row -->

		<?php else : ?>

		<div class="row">
			<div class="col-12 py-14">
				<?php do_action( 'woocommerce_no_products_found' ); ?>
			</div>
		</div>

		<?php endif; ?>

	</div>
	<!-- /.container -->
</section>
<!-- /section -->

<?php get_footer(); ?>

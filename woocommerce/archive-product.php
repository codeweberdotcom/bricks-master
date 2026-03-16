<?php
/**
 * WooCommerce Archive Product — Shop Page
 * Style: shop2 (sidebar left, isotope grid)
 *
 * Переопределяет woocommerce/archive-product.php из плагина WooCommerce.
 */

defined( 'ABSPATH' ) || exit;

// Количество колонок (per_row): 2, 3 или 4. По умолчанию — 3.
$allowed_per_row = [ 2, 3, 4 ];
$per_row         = isset( $_GET['per_row'] ) ? (int) $_GET['per_row'] : 3; // phpcs:ignore WordPress.Security.NonceVerification
$per_row         = in_array( $per_row, $allowed_per_row, true ) ? $per_row : 3;

$row_cols_map = [
	2 => 'row-cols-1 row-cols-sm-2',
	3 => 'row-cols-1 row-cols-sm-2 row-cols-lg-3',
	4 => 'row-cols-2 row-cols-sm-2 row-cols-lg-4',
];
$row_cols_class = $row_cols_map[ $per_row ];

// Базовый URL для кнопок-переключателей (без per_row, сохраняем остальные params)
$base_query_args = $_GET; // phpcs:ignore WordPress.Security.NonceVerification
unset( $base_query_args['per_row'] );
$base_url = add_query_arg( $base_query_args, get_pagenum_link( 1 ) );

get_header();
get_pageheader();
?>

<section class="wrapper bg-light">
	<div class="container pb-14 pb-md-16 pt-12">

		<?php if ( woocommerce_product_loop() ) : ?>

		<div class="row gy-10">

			<!-- Колонка с товарами -->
			<div class="col-lg-9 order-lg-2">

				<!-- Сортировка + результаты + переключатель колонок -->
				<div class="row align-items-center mb-10 position-relative zindex-1">
					<div class="col-md-7 col-xl-8 pe-xl-20">
						<?php woocommerce_result_count(); ?>
					</div>
					<!--/column -->
					<div class="col-md-5 col-xl-4 ms-md-auto text-md-end mt-5 mt-md-0">
						<div class="d-flex align-items-center justify-content-md-end gap-3">

							<!-- Переключатель колонок -->
							<div class="shop-per-row d-none d-sm-flex gap-1">
								<?php foreach ( $allowed_per_row as $cols ) : ?>
									<a href="<?php echo esc_url( add_query_arg( 'per_row', $cols, $base_url ) ); ?>"
									   class="shop-per-row-btn<?php echo $per_row === $cols ? ' active' : ''; ?>"
									   title="<?php echo esc_attr( sprintf( _n( '%d column', '%d columns', $cols, 'codeweber' ), $cols ) ); ?>">
										<?php for ( $i = 0; $i < $cols; $i++ ) : ?><span></span><?php endfor; ?>
									</a>
								<?php endforeach; ?>
							</div>

							<div class="form-select-wrapper flex-grow-1">
								<?php woocommerce_catalog_ordering(); ?>
							</div>
							<!--/.form-select-wrapper -->
						</div>
					</div>
					<!--/column -->
				</div>
				<!--/.row -->

				<!-- Сетка товаров -->
				<div class="grid grid-view projects-masonry shop mb-13">
					<div class="row gx-md-8 gy-10 gy-md-13 isotope <?php echo esc_attr( $row_cols_class ); ?>">
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

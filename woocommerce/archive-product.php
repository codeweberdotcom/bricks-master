<?php
/**
 * WooCommerce Archive Product — Shop Page
 * Style: shop2 (sidebar left, isotope grid)
 *
 * Переопределяет woocommerce/archive-product.php из плагина WooCommerce.
 * Поддерживает PJAX: при заголовке X-PJAX возвращает только контент колонки
 * товаров (#shop-pjax-container) без header/footer.
 */

defined( 'ABSPATH' ) || exit;

// PJAX-запрос: заголовок X-PJAX: true
$is_pjax = ! empty( $_SERVER['HTTP_X_PJAX'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

// Количество колонок (per_row): 2, 3 или 4. По умолчанию — 3.
$allowed_per_row = [ 2, 3, 4 ];
$per_row         = isset( $_GET['per_row'] ) ? (int) $_GET['per_row'] : 3; // phpcs:ignore WordPress.Security.NonceVerification
$per_row         = in_array( $per_row, $allowed_per_row, true ) ? $per_row : 3;

$row_cols_map = [
	2 => 'row-cols-1 row-cols-sm-2',
	3 => 'row-cols-1 row-cols-sm-2 row-cols-lg-3',
	4 => 'row-cols-2 row-cols-sm-2 row-cols-lg-4',
];

// Иконки Unicons для кнопок переключателя колонок
$per_row_icons = [
	2 => 'uil-columns',
	3 => 'uil-grid',
	4 => 'uil-apps',
];
$row_cols_class = $row_cols_map[ $per_row ];

// Количество товаров на странице (per_page): 12, 24, 48. По умолчанию — 12.
$allowed_per_page = [ 12, 24, 48 ];
$per_page         = isset( $_GET['per_page'] ) ? (int) $_GET['per_page'] : 12; // phpcs:ignore WordPress.Security.NonceVerification
$per_page         = in_array( $per_page, $allowed_per_page, true ) ? $per_page : 12;

// Базовый URL для кнопок-переключателей (без per_row и per_page, сохраняем остальные params)
$base_query_args = $_GET; // phpcs:ignore WordPress.Security.NonceVerification
unset( $base_query_args['per_row'], $base_query_args['per_page'] );
$base_url = add_query_arg( $base_query_args, get_pagenum_link( 1 ) );

// Redux: включена ли кнопка Load More
global $opt_name;
$load_more_enabled = false;
if ( class_exists( 'Redux' ) && ! empty( $opt_name ) ) {
	$load_more_enabled = (bool) Redux::get_option( $opt_name, 'woo_shop_load_more', false );
}

// Текущий orderby из URL
$orderby = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'menu_order'; // phpcs:ignore WordPress.Security.NonceVerification

// Данные контекста для Load More API
$queried_object_id   = 0;
$queried_object_type = '';
if ( is_product_category() || is_product_tag() ) {
	$queried_object_id   = get_queried_object_id();
	$queried_object_type = is_product_category() ? 'product_cat' : 'product_tag';
}

if ( ! $is_pjax ) {
	get_header();
	get_pageheader();
}
?>

<?php if ( ! $is_pjax ) : ?>
<section class="wrapper bg-light">
	<div class="container pb-14 pb-md-16 pt-12">

		<?php if ( woocommerce_product_loop() ) : ?>

		<div class="row gy-10">
<?php endif; ?>
<?php endif; ?>

<?php if ( woocommerce_product_loop() ) : ?>

			<!-- Колонка с товарами (PJAX-контейнер) -->
			<div id="shop-pjax-container" <?php echo $is_pjax ? '' : 'class="col-lg-9 order-lg-2"'; ?>>

				<!-- Результаты + переключатели + сортировка -->
				<div class="row align-items-center mb-10 position-relative zindex-1">
					<div class="col-md-4">
						<?php woocommerce_result_count(); ?>
					</div>
					<!--/column -->
					<div class="col-md-8 ms-md-auto mt-5 mt-md-0">
						<div class="d-flex align-items-center justify-content-md-end gap-3">

							<!-- Переключатель количества товаров на странице -->
							<div class="shop-per-page d-none d-sm-flex gap-1 align-items-center">
								<?php foreach ( $allowed_per_page as $count ) : ?>
									<a href="<?php echo esc_url( add_query_arg( [ 'per_page' => $count, 'per_row' => $per_row ], $base_url ) ); ?>"
									   class="shop-per-page-btn pjax-link<?php echo $per_page === $count ? ' active' : ''; ?>">
										<?php echo esc_html( $count ); ?>
									</a>
								<?php endforeach; ?>
							</div>

							<!-- Переключатель колонок -->
							<div class="shop-per-row d-none d-sm-flex gap-1">
								<?php foreach ( $allowed_per_row as $cols ) : ?>
									<a href="<?php echo esc_url( add_query_arg( [ 'per_row' => $cols, 'per_page' => $per_page ], $base_url ) ); ?>"
									   class="shop-per-row-btn pjax-link<?php echo $per_row === $cols ? ' active' : ''; ?>"
									   title="<?php echo esc_attr( sprintf( _n( '%d column', '%d columns', $cols, 'codeweber' ), $cols ) ); ?>">
										<i class="uil <?php echo esc_attr( $per_row_icons[ $cols ] ); ?>"></i>
									</a>
								<?php endforeach; ?>
							</div>

							<div class="form-select-wrapper">
								<?php woocommerce_catalog_ordering(); ?>
							</div>
							<!--/.form-select-wrapper -->
						</div>
					</div>
					<!--/column -->
				</div>
				<!--/.row -->

				<?php if ( $load_more_enabled ) :
					global $wp_query;
					$total_products  = (int) $wp_query->found_posts;
					$has_more        = $total_products > $per_page;
					$load_more_attrs = wp_json_encode( [
						'orderby'             => $orderby,
						'queried_object_id'   => $queried_object_id,
						'queried_object_type' => $queried_object_type,
					] );
				?>
				<!-- Load More: обёртка контейнера -->
				<div class="cwgb-load-more-container"
				     data-block-id="wc-shop-<?php echo esc_attr( get_queried_object_id() ?: 'all' ); ?>"
				     data-block-type="wc-shop"
				     data-block-attributes="<?php echo esc_attr( $load_more_attrs ); ?>"
				     data-current-offset="<?php echo esc_attr( $per_page ); ?>"
				     data-load-count="<?php echo esc_attr( $per_page ); ?>">

					<!-- Сетка товаров -->
					<div class="grid grid-view projects-masonry shop mb-13">
						<div class="row gx-md-8 gy-10 gy-md-13 isotope cwgb-load-more-items <?php echo esc_attr( $row_cols_class ); ?>">
							<?php while ( have_posts() ) : the_post(); ?>
								<?php wc_get_template_part( 'content', 'product' ); ?>
							<?php endwhile; ?>
						</div>
						<!-- /.row -->
					</div>
					<!-- /.grid -->

					<?php if ( $has_more ) : ?>
					<div class="text-center mt-10">
						<button type="button" class="btn btn-primary rounded-pill cwgb-load-more-btn"
						        data-loading-text="<?php esc_attr_e( 'Loading...', 'codeweber' ); ?>">
							<?php esc_html_e( 'Show More', 'codeweber' ); ?>
						</button>
					</div>
					<?php endif; ?>

				</div>
				<!-- /.cwgb-load-more-container -->

				<?php else : ?>

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

				<?php endif; ?>

			</div>
			<!-- /#shop-pjax-container -->

<?php endif; // woocommerce_product_loop ?>

<?php if ( ! $is_pjax ) : ?>

		<?php if ( ! woocommerce_product_loop() ) : ?>
		<div class="row">
			<div class="col-12 py-14">
				<?php do_action( 'woocommerce_no_products_found' ); ?>
			</div>
		</div>
		<?php else : ?>

			<!-- Сайдбар -->
			<aside class="col-lg-3 sidebar">
				<?php if ( is_active_sidebar( 'sidebar-woo' ) ) : ?>
					<?php dynamic_sidebar( 'sidebar-woo' ); ?>
				<?php endif; ?>
			</aside>
			<!-- /aside.sidebar -->

		</div>
		<!-- /.row -->

		<?php endif; ?>

	</div>
	<!-- /.container -->
</section>
<!-- /section -->

<?php get_footer(); ?>
<?php endif; ?>

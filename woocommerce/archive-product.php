<?php
/**
 * WooCommerce Archive Product — Shop Page
 * Style: shop2 (sidebar left, isotope grid)
 *
 * Переопределяет woocommerce/archive-product.php из плагина WooCommerce.
 * Поддерживает PJAX: при заголовке X-PJAX возвращает содержимое #shop-pjax-wrapper
 * (включая page header, сетку товаров и сайдбар) без <html>/<head>/<body>.
 */

defined( 'ABSPATH' ) || exit;

// PJAX-запрос: заголовок X-PJAX: true
$is_pjax = ! empty( $_SERVER['HTTP_X_PJAX'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

// ── Количество колонок (per_row) ──────────────────────────────────────────────
$per_row_cols_map = [
	2 => 'row-cols-1 row-cols-sm-2',
	3 => 'row-cols-1 row-cols-sm-2 row-cols-lg-3',
	4 => 'row-cols-2 row-cols-sm-2 row-cols-lg-4',
];

// Иконки колонок — inline SVG с fill="currentColor" (цвет через CSS)
$per_row_icons_map = [
	2 => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 8 8" fill="currentColor" aria-hidden="true"><path d="M0 0v3h3V0H0zm5 0v3h3V0H5zM0 5v3h3V5H0zm5 0v3h3V5H5z"/></svg>',
	3 => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 8 8" fill="currentColor" aria-hidden="true"><path d="M0 0v2h2V0H0zm3 0v2h2V0H3zm3 0v2h2V0H6zM0 3v2h2V3H0zm3 0v2h2V3H3zm3 0v2h2V3H6zM0 6v2h2V6H0zm3 0v2h2V6H3zm3 0v2h2V6H6z"/></svg>',
	4 => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 8 8" fill="currentColor" aria-hidden="true"><path d="M0 0V1.14286H1.14286V0H0ZM2.28571 0V1.14286H3.42857V0H2.28571ZM4.57143 0V1.14286H5.71429V0H4.57143ZM6.85714 0V1.14286H8V0H6.85714ZM0 2.28571V3.42857H1.14286V2.28571H0ZM2.28571 2.28571V3.42857H3.42857V2.28571H2.28571ZM4.57143 2.28571V3.42857H5.71429V2.28571H4.57143ZM6.85714 2.28571V3.42857H8V2.28571H6.85714ZM0 4.57143V5.71429H1.14286V4.57143H0ZM2.28571 4.57143V5.71429H3.42857V4.57143H2.28571ZM4.57143 4.57143V5.71429H5.71429V4.57143H4.57143ZM6.85714 4.57143V5.71429H8V4.57143H6.85714ZM0 6.85714V8H1.14286V6.85714H0ZM2.28571 6.85714V8H3.42857V6.85714H2.28571ZM4.57143 6.85714V8H5.71429V6.85714H4.57143ZM6.85714 6.85714V8H8V6.85714H6.85714Z"/></svg>',
];

// ── Дефолтный класс колонок из Redux (per-breakpoint) ────────────────────────
$default_row_cols_class = 'row-cols-1 row-cols-sm-2 row-cols-lg-3';
global $opt_name;
if ( class_exists( 'Redux' ) && ! empty( $opt_name ) ) {
	$c_xs = max( 1, min( 4, (int) Redux::get_option( $opt_name, 'woo_cols_xs', 1 ) ) );
	$c_sm = max( 1, min( 4, (int) Redux::get_option( $opt_name, 'woo_cols_sm', 2 ) ) );
	$c_md = max( 1, min( 4, (int) Redux::get_option( $opt_name, 'woo_cols_md', 2 ) ) );
	$c_lg = max( 1, min( 4, (int) Redux::get_option( $opt_name, 'woo_cols_lg', 3 ) ) );
	$c_xl = max( 1, min( 4, (int) Redux::get_option( $opt_name, 'woo_cols_xl', 4 ) ) );
	$default_row_cols_class = "row-cols-{$c_xs} row-cols-sm-{$c_sm} row-cols-md-{$c_md} row-cols-lg-{$c_lg} row-cols-xl-{$c_xl}";
}

// Допустимые значения — из Redux или дефолт [2,3,4]
$allowed_per_row = [ 2, 3, 4 ];
if ( class_exists( 'Redux' ) && ! empty( $opt_name ) ) {
	$checked = Redux::get_option( $opt_name, 'woo_per_row_values', [] );
	if ( is_array( $checked ) && ! empty( $checked ) ) {
		$filtered = array_values( array_filter( [ 2, 3, 4 ], function ( $v ) use ( $checked ) {
			return ! empty( $checked[ (string) $v ] );
		} ) );
		if ( ! empty( $filtered ) ) {
			$allowed_per_row = $filtered;
		}
	}
}

// phpcs:ignore WordPress.Security.NonceVerification
$per_row = isset( $_GET['per_row'] ) ? (int) $_GET['per_row'] : 0;
$per_row = in_array( $per_row, $allowed_per_row, true ) ? $per_row : 0;

$row_cols_map   = array_intersect_key( $per_row_cols_map, array_flip( $allowed_per_row ) );
$per_row_icons  = array_intersect_key( $per_row_icons_map, array_flip( $allowed_per_row ) );
$row_cols_class = $per_row > 0 ? ( $row_cols_map[ $per_row ] ?? $default_row_cols_class ) : $default_row_cols_class;

// ── Количество товаров на странице (per_page) ─────────────────────────────────
$allowed_per_page = [ 12, 24, 48 ];
if ( class_exists( 'Redux' ) && ! empty( $opt_name ) ) {
	$raw = Redux::get_option( $opt_name, 'woo_per_page_values', '12,24,48' );
	if ( ! empty( $raw ) ) {
		$parsed = array_values( array_filter( array_map( 'intval', explode( ',', $raw ) ) ) );
		if ( ! empty( $parsed ) ) {
			$allowed_per_page = $parsed;
		}
	}
}
$per_page_default = $allowed_per_page[0];
$per_page         = isset( $_GET['per_page'] ) ? (int) $_GET['per_page'] : $per_page_default; // phpcs:ignore WordPress.Security.NonceVerification
$per_page         = in_array( $per_page, $allowed_per_page, true ) ? $per_page : $per_page_default;

// Базовый URL для кнопок-переключателей (без per_row и per_page, сохраняем остальные params)
$base_query_args = $_GET; // phpcs:ignore WordPress.Security.NonceVerification
unset( $base_query_args['per_row'], $base_query_args['per_page'] );
$base_url = add_query_arg( $base_query_args, get_pagenum_link( 1 ) );

// ── Redux: настройки видимости элементов ──────────────────────────────────────
$shop_nav_mode = 'pagination';
$show_per_page = true;
$show_per_row  = true;
$show_ordering = true;
if ( class_exists( 'Redux' ) && ! empty( $opt_name ) ) {
	$redux_val     = Redux::get_option( $opt_name, 'woo_shop_load_more', 'pagination' );
	$shop_nav_mode = in_array( $redux_val, [ 'pagination', 'load_more', 'both' ], true ) ? $redux_val : 'pagination';
	$show_per_page = (bool) Redux::get_option( $opt_name, 'woo_show_per_page', true );
	$show_per_row  = (bool) Redux::get_option( $opt_name, 'woo_show_per_row', true );
	$show_ordering = (bool) Redux::get_option( $opt_name, 'woo_show_ordering', true );
}

$show_load_more     = in_array( $shop_nav_mode, [ 'load_more', 'both' ], true );
$show_pagination    = in_array( $shop_nav_mode, [ 'pagination', 'both' ], true );
$show_archive_title = false;
if ( class_exists( 'Redux' ) && ! empty( $opt_name ) ) {
	$show_archive_title = (bool) Redux::get_option( $opt_name, 'woo_show_archive_title', false );
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

// ── Рендер ────────────────────────────────────────────────────────────────────
if ( ! $is_pjax ) {
	get_header();
}
?>

<div id="shop-pjax-wrapper" data-page-title="<?php echo esc_attr( wp_get_document_title() ); ?>">

	<?php get_pageheader(); ?>

	<?php if ( woocommerce_product_loop() ) : ?>

	<section class="wrapper bg-light">
		<div class="container pb-14 pb-md-16 pt-12">
			<div class="row gy-10">

				<!-- Колонка с товарами -->
				<div class="col-lg-9 order-lg-2">

					<!-- Результаты + переключатели + сортировка -->
					<div class="row align-items-center mb-10 position-relative zindex-1">

						<?php if ( $show_archive_title ) : ?>
						<div class="col-md-7 col-xl-6 pe-xl-10">
							<h1 class="display-6 mb-1"><?php echo esc_html( is_product_tag() || is_product_category() ? single_term_title( '', false ) : woocommerce_page_title( false ) ); ?></h1>
							<?php woocommerce_result_count(); ?>
						</div>
						<?php else : ?>
						<div class="col-md-4">
							<?php woocommerce_result_count(); ?>
						</div>
						<?php endif; ?>
						<!--/column -->

						<div class="<?php echo $show_archive_title ? 'col-md-5 col-xl-6' : 'col-md-8'; ?> ms-md-auto mt-5 mt-md-0">
							<div class="d-flex align-items-center justify-content-md-end gap-3">

								<!-- Переключатель количества товаров на странице -->
								<?php if ( $show_per_page ) : ?>
								<div class="shop-per-page d-none d-sm-flex gap-1 align-items-center">
									<?php foreach ( $allowed_per_page as $count ) : ?>
										<a href="<?php echo esc_url( add_query_arg( [ 'per_page' => $count, 'per_row' => $per_row ], $base_url ) ); ?>"
										   class="shop-per-page-btn pjax-link<?php echo $per_page === $count ? ' active' : ''; ?>">
											<?php echo esc_html( $count ); ?>
										</a>
									<?php endforeach; ?>
								</div>
								<?php endif; ?>

								<!-- Переключатель колонок -->
								<?php if ( $show_per_row ) : ?>
								<div class="shop-per-row d-none d-sm-flex gap-1">
									<?php foreach ( $allowed_per_row as $cols ) : ?>
										<a href="<?php echo esc_url( add_query_arg( [ 'per_row' => $cols, 'per_page' => $per_page ], $base_url ) ); ?>"
										   class="shop-per-row-btn pjax-link<?php echo $per_row === $cols ? ' active' : ''; ?>"
										   title="<?php echo esc_attr( sprintf( _n( '%d column', '%d columns', $cols, 'codeweber' ), $cols ) ); ?>">
											<?php echo $per_row_icons[ $cols ]; // phpcs:ignore WordPress.Security.EscapeOutput -- hardcoded SVG ?>
										</a>
									<?php endforeach; ?>
								</div>
								<?php endif; ?>

								<!-- Сортировка -->
								<?php if ( $show_ordering ) : ?>
								<div class="form-select-wrapper">
									<?php
									$_cw_ordering_filter = null;
									if ( class_exists( 'Redux' ) && ! empty( $opt_name ) ) {
										$_cw_checked = Redux::get_option( $opt_name, 'woo_ordering_options', [] );
										if ( is_array( $_cw_checked ) && ! empty( $_cw_checked ) ) {
											$_cw_ordering_filter = function ( $options ) use ( $_cw_checked ) {
												foreach ( array_keys( $options ) as $key ) {
													if ( empty( $_cw_checked[ $key ] ) ) {
														unset( $options[ $key ] );
													}
												}
												return $options ?: [ 'menu_order' => __( 'Default sorting', 'woocommerce' ) ];
											};
											add_filter( 'woocommerce_catalog_orderby', $_cw_ordering_filter, 999 );
										}
									}
									woocommerce_catalog_ordering();
									if ( $_cw_ordering_filter ) {
										remove_filter( 'woocommerce_catalog_orderby', $_cw_ordering_filter, 999 );
									}
									?>
								</div>
								<!--/.form-select-wrapper -->
								<?php endif; ?>

							</div>
						</div>
						<!--/column -->
					</div>
					<!--/.row -->

					<?php if ( $show_load_more ) :
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
							<div class="row <?php echo esc_attr( Codeweber_Options::style( 'grid-gap' ) ); ?> isotope cwgb-load-more-items <?php echo esc_attr( $row_cols_class ); ?>">
								<?php while ( have_posts() ) : the_post(); ?>
									<?php wc_get_template_part( 'content', 'product' ); ?>
								<?php endwhile; ?>
							</div>
							<!-- /.row -->
						</div>
						<!-- /.grid -->

						<?php if ( $has_more ) : ?>
						<div class="text-center mt-10">
							<?php $btn_radius = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button' ) : ' rounded-pill'; ?>
							<button type="button" class="btn btn-primary<?php echo esc_attr( $btn_radius ); ?> cwgb-load-more-btn"
							        data-loading-text="<?php esc_attr_e( 'Loading...', 'codeweber' ); ?>">
								<?php esc_html_e( 'Show More', 'codeweber' ); ?>
							</button>
						</div>
						<?php endif; ?>

					</div>
					<!-- /.cwgb-load-more-container -->

					<?php if ( $show_pagination ) : ?>
					<?php woocommerce_pagination(); ?>
					<?php endif; ?>

					<?php else : ?>

					<!-- Сетка товаров -->
					<div class="grid grid-view projects-masonry shop mb-13">
						<div class="row <?php echo esc_attr( Codeweber_Options::style( 'grid-gap' ) ); ?> isotope <?php echo esc_attr( $row_cols_class ); ?>">
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
				<!-- /.col (товары) -->

				<!-- Сайдбар -->
				<aside class="col-lg-3 sidebar">
					<?php if ( is_active_sidebar( 'sidebar-woo' ) ) : ?>
						<?php dynamic_sidebar( 'sidebar-woo' ); ?>
					<?php endif; ?>
				</aside>
				<!-- /aside.sidebar -->

			</div>
			<!-- /.row -->
		</div>
		<!-- /.container -->
	</section>
	<!-- /section -->

	<?php else : ?>

	<section class="wrapper bg-light">
		<div class="container pb-14 pb-md-16 pt-12">
			<div class="row gy-10">

				<!-- Колонка с сообщением «нет товаров» -->
				<div class="col-lg-9 order-lg-2">

					<!-- Результаты + переключатели + сортировка -->
					<div class="row align-items-center mb-10 position-relative zindex-1">

						<?php if ( $show_archive_title ) : ?>
						<div class="col-md-7 col-xl-6 pe-xl-10">
							<h1 class="display-6 mb-1"><?php echo esc_html( is_product_tag() || is_product_category() ? single_term_title( '', false ) : woocommerce_page_title( false ) ); ?></h1>
							<?php woocommerce_result_count(); ?>
						</div>
						<?php else : ?>
						<div class="col-md-4">
							<?php woocommerce_result_count(); ?>
						</div>
						<?php endif; ?>

						<div class="<?php echo $show_archive_title ? 'col-md-5 col-xl-6' : 'col-md-8'; ?> ms-md-auto mt-5 mt-md-0">
							<div class="d-flex align-items-center justify-content-md-end gap-3">

								<?php if ( $show_per_page ) : ?>
								<div class="shop-per-page d-none d-sm-flex gap-1 align-items-center">
									<?php foreach ( $allowed_per_page as $count ) : ?>
										<a href="<?php echo esc_url( add_query_arg( [ 'per_page' => $count, 'per_row' => $per_row ], $base_url ) ); ?>"
										   class="shop-per-page-btn pjax-link<?php echo $per_page === $count ? ' active' : ''; ?>">
											<?php echo esc_html( $count ); ?>
										</a>
									<?php endforeach; ?>
								</div>
								<?php endif; ?>

								<?php if ( $show_per_row ) : ?>
								<div class="shop-per-row d-none d-sm-flex gap-1">
									<?php foreach ( $allowed_per_row as $cols ) : ?>
										<a href="<?php echo esc_url( add_query_arg( [ 'per_row' => $cols, 'per_page' => $per_page ], $base_url ) ); ?>"
										   class="shop-per-row-btn pjax-link<?php echo $per_row === $cols ? ' active' : ''; ?>"
										   title="<?php echo esc_attr( sprintf( _n( '%d column', '%d columns', $cols, 'codeweber' ), $cols ) ); ?>">
											<?php echo $per_row_icons[ $cols ]; // phpcs:ignore WordPress.Security.EscapeOutput -- hardcoded SVG ?>
										</a>
									<?php endforeach; ?>
								</div>
								<?php endif; ?>

								<?php if ( $show_ordering ) : ?>
								<div class="form-select-wrapper">
									<?php
									$_cw_ordering_filter = null;
									if ( class_exists( 'Redux' ) && ! empty( $opt_name ) ) {
										$_cw_checked = Redux::get_option( $opt_name, 'woo_ordering_options', [] );
										if ( is_array( $_cw_checked ) && ! empty( $_cw_checked ) ) {
											$_cw_ordering_filter = function ( $options ) use ( $_cw_checked ) {
												foreach ( array_keys( $options ) as $key ) {
													if ( empty( $_cw_checked[ $key ] ) ) {
														unset( $options[ $key ] );
													}
												}
												return $options ?: [ 'menu_order' => __( 'Default sorting', 'woocommerce' ) ];
											};
											add_filter( 'woocommerce_catalog_orderby', $_cw_ordering_filter, 999 );
										}
									}
									woocommerce_catalog_ordering();
									if ( $_cw_ordering_filter ) {
										remove_filter( 'woocommerce_catalog_orderby', $_cw_ordering_filter, 999 );
									}
									?>
								</div>
								<?php endif; ?>

							</div>
						</div>
					</div>
					<!--/.row controls -->

					<div class="py-6">
						<?php do_action( 'woocommerce_no_products_found' ); ?>
					</div>

				</div>
				<!-- /.col -->

				<!-- Сайдбар -->
				<aside class="col-lg-3 sidebar">
					<?php if ( is_active_sidebar( 'sidebar-woo' ) ) : ?>
						<?php dynamic_sidebar( 'sidebar-woo' ); ?>
					<?php endif; ?>
				</aside>
				<!-- /aside.sidebar -->

			</div>
			<!-- /.row -->
		</div>
		<!-- /.container -->
	</section>

	<?php endif; // woocommerce_product_loop ?>

</div>
<!-- /#shop-pjax-wrapper -->

<?php if ( ! $is_pjax ) {
	get_footer();
}

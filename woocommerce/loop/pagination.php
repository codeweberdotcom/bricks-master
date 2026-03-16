<?php
/**
 * WooCommerce Loop — Pagination
 *
 * Переопределяет стандартную WC-пагинацию.
 * Использует Bootstrap 5 markup (page-item / page-link) в стиле темы.
 * Сохраняет параметры ?per_row и ?per_page в ссылках.
 *
 * @package Codeweber
 */

defined( 'ABSPATH' ) || exit;

global $wp_query;

$total = (int) $wp_query->max_num_pages;
$paged = max( 1, (int) get_query_var( 'paged' ) );

if ( $total <= 1 ) {
	return;
}

// Собираем дополнительные параметры URL, которые нужно сохранять
$extra_args = array();
// phpcs:disable WordPress.Security.NonceVerification
if ( isset( $_GET['per_row'] ) ) {
	$extra_args['per_row'] = (int) $_GET['per_row'];
}
if ( isset( $_GET['per_page'] ) ) {
	$extra_args['per_page'] = (int) $_GET['per_page'];
}
// phpcs:enable

/**
 * Собрать URL страницы с сохранёнными параметрами.
 *
 * @param int $page
 * @return string
 */
$page_url = function ( $page ) use ( $extra_args ) {
	$link = get_pagenum_link( $page );
	return $extra_args ? add_query_arg( $extra_args, $link ) : $link;
};

$range = 4; // сколько номеров показывать вокруг текущей страницы
$ceil  = (int) ceil( $range / 2 );

if ( $total > $range ) {
	if ( $paged <= $range ) {
		$min = 1;
		$max = $range + 1;
	} elseif ( $paged >= ( $total - $ceil ) ) {
		$min = $total - $range;
		$max = $total;
	} else {
		$min = $paged - $ceil;
		$max = $paged + $ceil;
	}
} else {
	$min = 1;
	$max = $total;
}
?>

<nav class="d-flex justify-content-center mt-6" aria-label="<?php esc_attr_e( 'Products pagination', 'codeweber' ); ?>">
	<ul class="pagination">

		<?php if ( $paged > 1 ) : ?>
		<li class="page-item">
			<a class="page-link pjax-link" href="<?php echo esc_url( $page_url( $paged - 1 ) ); ?>" aria-label="<?php esc_attr_e( 'Previous', 'codeweber' ); ?>">
				<span aria-hidden="true"><i class="uil uil-arrow-left"></i></span>
			</a>
		</li>
		<?php endif; ?>

		<?php if ( ! empty( $min ) && ! empty( $max ) ) : ?>
			<?php for ( $i = $min; $i <= $max; $i++ ) : ?>
				<?php if ( $paged === $i ) : ?>
				<li class="page-item active" aria-current="page">
					<span class="page-link"><?php echo str_pad( $i, 2, '0', STR_PAD_LEFT ); ?></span>
				</li>
				<?php else : ?>
				<li class="page-item">
					<a class="page-link pjax-link" href="<?php echo esc_url( $page_url( $i ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Page %d', 'codeweber' ), $i ) ); ?>">
						<?php echo str_pad( $i, 2, '0', STR_PAD_LEFT ); ?>
					</a>
				</li>
				<?php endif; ?>
			<?php endfor; ?>
		<?php endif; ?>

		<?php if ( $paged < $total ) : ?>
		<li class="page-item">
			<a class="page-link pjax-link" href="<?php echo esc_url( $page_url( $paged + 1 ) ); ?>" aria-label="<?php esc_attr_e( 'Next', 'codeweber' ); ?>">
				<span aria-hidden="true"><i class="uil uil-arrow-right"></i></span>
			</a>
		</li>
		<?php endif; ?>

	</ul>
</nav>

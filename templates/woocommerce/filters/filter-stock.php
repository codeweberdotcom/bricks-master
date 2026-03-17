<?php
/**
 * Stock status filter.
 *
 * Uses ?filter_stock_status=instock|outofstock|onbackorder.
 * Applied to WP_Query via the hook in woocommerce-filters.php.
 *
 * Expected variables:
 *   $options  array — from cw_get_stock_filter_options()
 *
 * @package CodeWeber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $options ) ) {
	return;
}
?>

<ul class="cw-filter-checkbox-list list-unstyled mb-0">
	<?php foreach ( $options as $opt ) : ?>
		<li class="cw-filter-checkbox-list__item">
			<a href="<?php echo esc_url( $opt['url'] ); ?>"
				class="cw-filter-checkbox-link pjax-link d-flex align-items-center gap-2<?php echo $opt['is_active'] ? ' checked' : ''; ?>"
				aria-pressed="<?php echo $opt['is_active'] ? 'true' : 'false'; ?>">
				<span class="cw-checkbox" aria-hidden="true">
					<?php if ( $opt['is_active'] ) : ?>
						<svg width="10" height="8" viewBox="0 0 10 8" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M1 3.5L3.8 6.5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					<?php endif; ?>
				</span>
				<span><?php echo esc_html( $opt['label'] ); ?></span>
			</a>
		</li>
	<?php endforeach; ?>
</ul>

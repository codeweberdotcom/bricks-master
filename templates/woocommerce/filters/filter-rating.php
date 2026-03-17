<?php
/**
 * Rating filter — 5 to 1 stars with filled/empty SVG stars.
 *
 * Multiple ratings can be active at once (OR logic via comma-separated values).
 * WC_Query handles rating_filter parameter natively.
 *
 * Expected variables:
 *   $options  array — from cw_get_rating_filter_options()
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

<ul class="cw-filter-list list-unstyled mb-0">
	<?php foreach ( $options as $opt ) : ?>
		<li class="cw-filter-list__item<?php echo $opt['is_active'] ? ' active' : ''; ?>">
			<a href="<?php echo esc_url( $opt['url'] ); ?>"
				class="cw-filter-list__link cw-filter-rating-link pjax-link d-flex align-items-center gap-2">
				<span class="cw-stars" aria-label="<?php echo esc_attr( sprintf( _n( '%d звезда', '%d звёзд', (int) $opt['value'], 'codeweber' ), (int) $opt['value'] ) ); ?>">
					<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
						<?php if ( $i <= (int) $opt['value'] ) : ?>
							<svg class="cw-star cw-star--filled" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
								<path d="M7 1L8.76 4.58L12.73 5.16L9.87 7.94L10.53 11.9L7 9.97L3.47 11.9L4.13 7.94L1.27 5.16L5.24 4.58L7 1Z" fill="currentColor"/>
							</svg>
						<?php else : ?>
							<svg class="cw-star cw-star--empty" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
								<path d="M7 1L8.76 4.58L12.73 5.16L9.87 7.94L10.53 11.9L7 9.97L3.47 11.9L4.13 7.94L1.27 5.16L5.24 4.58L7 1Z" stroke="currentColor" stroke-width="1" fill="none"/>
							</svg>
						<?php endif; ?>
					<?php endfor; ?>
				</span>
				<span class="cw-rating-label">
					<?php
					printf(
						/* translators: %d: rating value */
						esc_html__( 'от %d', 'codeweber' ),
						(int) $opt['value']
					);
					?>
				</span>
			</a>
		</li>
	<?php endforeach; ?>
</ul>

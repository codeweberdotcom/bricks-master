<?php
/**
 * Attribute filter — supports 'list', 'checkbox', and 'button' display modes.
 *
 * Expected variables provided by the caller (widget or filter-panel.php):
 *   $terms_data   array  — from cw_get_attribute_filter_terms()
 *   $display_mode string — 'checkbox' | 'list' | 'button'
 *   $show_count   bool
 *
 * OR/AND logic:
 *   Multiple values for the same attribute use OR (comma-separated in URL).
 *   Different attributes use AND (separate query params).
 *   All pjax-link hrefs are built by cw_get_filter_url() which toggles values.
 *
 * @package CodeWeber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $terms_data ) ) {
	return;
}

$display_mode = $display_mode ?? 'checkbox';
$show_count   = $show_count ?? true;
?>

<?php if ( 'button' === $display_mode ) : ?>

	<div class="cw-filter-button-group d-flex flex-wrap gap-1">
		<?php foreach ( $terms_data as $item ) :
			$term      = $item['term'];
			$is_active = $item['is_active'];
			$count     = $item['count'];
			?>
			<a href="<?php echo esc_url( $item['url'] ); ?>"
				class="btn btn-sm pjax-link <?php echo $is_active ? 'btn-secondary' : 'btn-outline-secondary'; ?>"
				title="<?php echo $show_count ? esc_attr( sprintf( _n( '%d товар', '%d товаров', $count, 'codeweber' ), $count ) ) : ''; ?>">
				<?php echo esc_html( $term->name ); ?>
				<?php if ( $show_count ) : ?>
					<span class="cw-filter-count">(<?php echo esc_html( $count ); ?>)</span>
				<?php endif; ?>
			</a>
		<?php endforeach; ?>
	</div>

<?php elseif ( 'list' === $display_mode ) : ?>

	<ul class="cw-filter-list list-unstyled mb-0">
		<?php foreach ( $terms_data as $item ) :
			$term      = $item['term'];
			$is_active = $item['is_active'];
			$count     = $item['count'];
			?>
			<li class="cw-filter-list__item<?php echo $is_active ? ' active' : ''; ?>">
				<a href="<?php echo esc_url( $item['url'] ); ?>" class="cw-filter-list__link pjax-link">
					<?php echo esc_html( $term->name ); ?>
					<?php if ( $show_count ) : ?>
						<span class="cw-filter-count">(<?php echo esc_html( $count ); ?>)</span>
					<?php endif; ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>

<?php else : // 'checkbox' — default ?>

	<ul class="cw-filter-checkbox-list list-unstyled mb-0">
		<?php foreach ( $terms_data as $item ) :
			$term      = $item['term'];
			$is_active = $item['is_active'];
			$count     = $item['count'];
			?>
			<li class="cw-filter-checkbox-list__item">
				<a href="<?php echo esc_url( $item['url'] ); ?>"
					class="cw-filter-checkbox-link pjax-link d-flex align-items-center gap-2<?php echo $is_active ? ' checked' : ''; ?>"
					aria-pressed="<?php echo $is_active ? 'true' : 'false'; ?>">
					<span class="cw-checkbox" aria-hidden="true">
						<?php if ( $is_active ) : ?>
							<svg width="10" height="8" viewBox="0 0 10 8" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M1 3.5L3.8 6.5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						<?php endif; ?>
					</span>
					<span class="cw-filter-term-name"><?php echo esc_html( $term->name ); ?></span>
					<?php if ( $show_count ) : ?>
						<span class="cw-filter-count ms-auto">(<?php echo esc_html( $count ); ?>)</span>
					<?php endif; ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>

<?php endif; ?>

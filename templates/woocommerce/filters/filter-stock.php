<?php
/**
 * Stock status filter — Bootstrap form-check style.
 *
 * Expected variables:
 *   $options  array — from cw_get_stock_filter_options()
 *
 * Items with is_empty=true are rendered as disabled (no link, muted, opacity-50).
 *
 * @package CodeWeber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $options ) ) {
	return;
}

$checkbox_size_class = $checkbox_size_class ?? '';
$checkbox_item_class = $checkbox_item_class ?? '';
$checkbox_columns    = $checkbox_columns ?? 1;
?>

<ul class="list-unstyled ps-0 mb-0<?php echo 2 === $checkbox_columns ? ' cc-2' : ''; ?>">
<?php foreach ( $options as $opt ) :
	$uid      = 'cw-stock-' . sanitize_html_class( $opt['value'] );
	$is_empty = $opt['is_empty'] ?? false;
	?>
	<li>
		<div class="form-check mb-1 cw-filter-check<?php echo esc_attr( $checkbox_size_class ); ?><?php echo $checkbox_item_class ? ' ' . esc_attr( $checkbox_item_class ) : ''; ?><?php echo $is_empty ? ' opacity-50' : ''; ?>">
			<input class="form-check-input"
				type="checkbox"
				id="<?php echo esc_attr( $uid ); ?>"
				<?php checked( $opt['is_active'] ); ?>
				<?php disabled( $is_empty ); ?>
				tabindex="-1"
				aria-hidden="true">
			<?php if ( $is_empty ) : ?>
				<span class="form-check-label text-muted pe-none">
					<?php echo esc_html( $opt['label'] ); ?>
				</span>
			<?php else : ?>
				<a href="<?php echo esc_url( $opt['url'] ); ?>"
					class="form-check-label pjax-link"
					aria-pressed="<?php echo $opt['is_active'] ? 'true' : 'false'; ?>">
					<?php echo esc_html( $opt['label'] ); ?>
				</a>
			<?php endif; ?>
		</div>
	</li>
<?php endforeach; ?>
</ul>

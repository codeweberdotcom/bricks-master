<?php
/**
 * Stock status filter — Bootstrap form-check style (shop2.html).
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

<?php foreach ( $options as $opt ) :
	$uid = 'cw-stock-' . sanitize_html_class( $opt['value'] );
	?>
	<div class="form-check mb-1 cw-filter-check">
		<input class="form-check-input"
			type="checkbox"
			id="<?php echo esc_attr( $uid ); ?>"
			<?php checked( $opt['is_active'] ); ?>
			tabindex="-1"
			aria-hidden="true">
		<a href="<?php echo esc_url( $opt['url'] ); ?>"
			class="form-check-label pjax-link"
			aria-pressed="<?php echo $opt['is_active'] ? 'true' : 'false'; ?>">
			<?php echo esc_html( $opt['label'] ); ?>
		</a>
	</div>
<?php endforeach; ?>

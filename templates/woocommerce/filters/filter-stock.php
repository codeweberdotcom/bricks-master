<?php
/**
 * Stock status filter — shop2.html style (form-check mb-1).
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

<?php foreach ( $options as $opt ) : ?>
	<div class="mb-1">
		<a href="<?php echo esc_url( $opt['url'] ); ?>"
			class="cw-check-link pjax-link<?php echo $opt['is_active'] ? ' active' : ''; ?>"
			aria-pressed="<?php echo $opt['is_active'] ? 'true' : 'false'; ?>">
			<span class="cw-check-box" aria-hidden="true"></span>
			<span class="cw-check-label"><?php echo esc_html( $opt['label'] ); ?></span>
		</a>
	</div>
<?php endforeach; ?>

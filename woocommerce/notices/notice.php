<?php
/**
 * Show info/notice messages (theme override)
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! $notices ) {
	return;
}

$card_radius = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style('card-radius') : '';
?>

<?php foreach ( $notices as $notice ) : ?>
	<div class="alert alert-info alert-icon alert-dismissible fade show <?php echo esc_attr( $card_radius ); ?>"<?php echo wc_get_notice_data_attr( $notice ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> role="alert">
		<i class="uil uil-exclamation-circle"></i>
		<?php echo wc_kses_notice( $notice['notice'] ); ?>
		<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
	</div>
<?php endforeach; ?>

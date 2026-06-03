<?php
/**
 * Stock Photos — dedicated admin page (Media → Free Photos).
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cw_providers = cw_stock_photos_providers();
?>
<div class="wrap cw-stock-page">
	<h1><?php esc_html_e( 'Free Photos', 'codeweber' ); ?></h1>
	<p class="description">
		<?php esc_html_e( 'Search free photos from Unsplash, Pexels and Pixabay and import them straight into the Media Library.', 'codeweber' ); ?>
	</p>

	<?php if ( empty( $cw_providers ) ) : ?>
		<div class="notice notice-warning inline">
			<p>
				<?php
				printf(
					/* translators: %s: settings page URL */
					wp_kses_post( __( 'No providers configured. Add at least one API key in <a href="%s">Theme Options → API</a>.', 'codeweber' ) ),
					esc_url( admin_url( 'admin.php?page=redux_demo' ) )
				);
				?>
			</p>
		</div>
	<?php else : ?>
		<div id="cw-stock-app"></div>
	<?php endif; ?>
</div>

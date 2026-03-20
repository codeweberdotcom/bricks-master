<?php
/**
 * Wishlist helper functions.
 *
 * @package CodeWeber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'cw_get_wishlist_url' ) ) {
	/**
	 * Get wishlist page URL.
	 *
	 * @return string
	 */
	function cw_get_wishlist_url() {
		if ( ! class_exists( 'Redux' ) ) {
			return home_url( '/wishlist/' );
		}

		global $opt_name;
		$page_id = Redux::get_option( $opt_name, 'wishlist_page', 0 );

		if ( $page_id ) {
			return get_permalink( (int) $page_id );
		}

		return home_url( '/wishlist/' );
	}
}

if ( ! function_exists( 'cw_get_wishlist_count' ) ) {
	/**
	 * Get wishlist product count for the current user/guest.
	 *
	 * @return int
	 */
	function cw_get_wishlist_count() {
		if ( isset( $_COOKIE['cw_wishlist_count'] ) ) {
			return (int) $_COOKIE['cw_wishlist_count']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		}
		return 0;
	}
}

if ( ! function_exists( 'cw_render_wishlist_icon' ) ) {
	/**
	 * Render wishlist header icon widget (count badge).
	 * Вызывается напрямую из шаблонов шапки.
	 *
	 * @param array $args {
	 *     Optional args.
	 *     @type bool $show_count Show product count badge. Default true.
	 *     @type bool $show_label Show text label. Default false.
	 * }
	 */
	function cw_render_wishlist_icon( $args = array() ) {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		$defaults = array(
			'show_count' => true,
			'show_label' => false,
		);
		$args = wp_parse_args( $args, $defaults );

		$count = cw_get_wishlist_count();
		$url   = cw_get_wishlist_url();

		?>
		<a
			href="<?php echo esc_url( $url ); ?>"
			class="cw-wishlist-widget d-flex align-items-center text-decoration-none"
			title="<?php esc_attr_e( 'Избранное', 'codeweber' ); ?>"
			aria-label="<?php esc_attr_e( 'Избранное', 'codeweber' ); ?>"
		>
			<span class="cw-wishlist-widget__icon position-relative">
				<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
					<path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143q.09.083.176.171a3 3 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15"/>
				</svg>
				<?php if ( $args['show_count'] ) : ?>
					<span class="cw-wishlist-widget__count badge bg-primary rounded-pill position-absolute" style="top:-6px;right:-8px;font-size:.65rem;min-width:18px;">
						<?php echo esc_html( $count ); ?>
					</span>
				<?php endif; ?>
			</span>
			<?php if ( $args['show_label'] ) : ?>
				<span class="cw-wishlist-widget__label ms-1">
					<?php esc_html_e( 'Избранное', 'codeweber' ); ?>
				</span>
			<?php endif; ?>
		</a>
		<?php
	}
}

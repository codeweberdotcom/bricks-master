<?php
/**
 * Compare helper functions.
 *
 * @package CodeWeber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'cw_get_compare_url' ) ) {
	/**
	 * Get compare page URL.
	 *
	 * @return string
	 */
	function cw_get_compare_url() {
		if ( ! class_exists( 'Redux' ) ) {
			return home_url( '/compare/' );
		}

		global $opt_name;
		$page_id = Redux::get_option( $opt_name, 'compare_page', 0 );

		if ( $page_id ) {
			return get_permalink( (int) $page_id );
		}

		return home_url( '/compare/' );
	}
}

if ( ! function_exists( 'cw_get_compare_ids' ) ) {
	/**
	 * Get current compare product/variation IDs from cookie.
	 *
	 * @return int[]
	 */
	function cw_get_compare_ids() {
		if ( class_exists( 'CW_Compare_Storage' ) ) {
			return CW_Compare_Storage::get_ids();
		}
		return array();
	}
}

if ( ! function_exists( 'cw_compare_has' ) ) {
	/**
	 * Check if a product/variation ID is in compare list.
	 *
	 * @param int $id Product or variation ID.
	 * @return bool
	 */
	function cw_compare_has( $id ) {
		if ( class_exists( 'CW_Compare_Storage' ) ) {
			return CW_Compare_Storage::has( $id );
		}
		return false;
	}
}

if ( ! function_exists( 'cw_get_compare_limit' ) ) {
	/**
	 * Get compare limit from Redux settings.
	 *
	 * @return int
	 */
	function cw_get_compare_limit() {
		if ( ! class_exists( 'Redux' ) ) {
			return 4;
		}
		global $opt_name;
		return (int) Redux::get_option( $opt_name, 'compare_limit', 4 );
	}
}

if ( ! function_exists( 'cw_is_compare_page' ) ) {
	/**
	 * Check if current page is the compare page.
	 *
	 * @return bool
	 */
	function cw_is_compare_page() {
		if ( ! class_exists( 'Redux' ) ) {
			return false;
		}
		global $opt_name;
		$page_id = (int) Redux::get_option( $opt_name, 'compare_page', 0 );
		return $page_id && is_page( $page_id );
	}
}

/**
 * AJAX: Create a Compare page with [cw_compare] shortcode.
 */
add_action( 'wp_ajax_cw_create_compare_page', 'cw_ajax_create_compare_page' );

function cw_ajax_create_compare_page() {
	check_ajax_referer( 'cw_create_compare_page', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( esc_html__( 'Insufficient permissions.', 'codeweber' ) );
	}

	$content = '<!-- wp:codeweber-blocks/section -->
<section class="wp-block-codeweber-blocks-section wrapper none" role="region" aria-label="Content section"><div class="container py-14 py-md-16"><!-- wp:shortcode -->
[cw_compare]
<!-- /wp:shortcode --></div></section>
<!-- /wp:codeweber-blocks/section -->';

	$page_id = wp_insert_post(
		array(
			'post_title'   => esc_html__( 'Compare Products', 'codeweber' ),
			'post_content' => $content,
			'post_status'  => 'publish',
			'post_type'    => 'page',
		)
	);

	if ( is_wp_error( $page_id ) ) {
		wp_send_json_error( $page_id->get_error_message() );
	}

	wp_send_json_success(
		array(
			'page_id'    => $page_id,
			'page_title' => get_the_title( $page_id ),
		)
	);
}

/**
 * Inject "Create Compare Page" button on Redux settings page.
 */
add_action(
	'admin_footer',
	function () {
		if ( ! isset( $_GET['page'] ) || 'redux_demo' !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}
		$nonce = wp_create_nonce( 'cw_create_compare_page' );
		?>
		<script>
		(function ($) {
			$(function () {
				var $wrap = $('#redux_demo-compare_page');
				if ( ! $wrap.length ) return;

				var $btn    = $('<button type="button" class="button button-secondary" style="margin-left:8px"><?php echo esc_js( __( 'Create Compare Page', 'codeweber' ) ); ?></button>');
				var $status = $('<span style="margin-left:8px;vertical-align:middle"></span>');

				var $anchor = $wrap.find('.select2-container').first();
				if ( ! $anchor.length ) {
					$anchor = $wrap.find('select').first();
				}
				$anchor.after($status).after($btn);

				$btn.on('click', function () {
					$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Creating…', 'codeweber' ) ); ?>');
					$status.text('').css('color', '');

					$.post(ajaxurl, {
						action : 'cw_create_compare_page',
						nonce  : '<?php echo esc_js( $nonce ); ?>'
					}, function (response) {
						if ( response.success ) {
							var $select = $('#compare_page-select');
							var opt     = new Option(response.data.page_title, response.data.page_id, true, true);
							$select.append(opt).val(response.data.page_id).trigger('change');
							$btn.text('<?php echo esc_js( __( 'Done', 'codeweber' ) ); ?>');
							$status.text('✓ <?php echo esc_js( __( 'Page created', 'codeweber' ) ); ?>').css('color', '#46b450');
						} else {
							$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Create Compare Page', 'codeweber' ) ); ?>');
							$status.text('⚠ ' + response.data).css('color', '#dc3232');
						}
					});
				});
			});
		}(jQuery));
		</script>
		<?php
	}
);

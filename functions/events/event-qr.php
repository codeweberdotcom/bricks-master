<?php
/**
 * Event QR Code — admin metabox
 *
 * Generates an SVG QR code for the event URL.
 * No DB save — just generate and download.
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'codeweber_event_qr',
		__( 'QR Code', 'codeweber' ),
		'codeweber_event_qr_metabox_cb',
		'events',
		'side',
		'default'
	);
} );

function codeweber_event_qr_metabox_cb( $post ) {
	$url   = get_permalink( $post->ID );
	$nonce = wp_create_nonce( 'cw_event_qr_' . $post->ID );
	?>
	<p style="word-break:break-all;font-size:11px;color:#666;margin-bottom:8px;">
		<?php echo $url ? esc_html( $url ) : esc_html__( 'Save the post first to get the URL.', 'codeweber' ); ?>
	</p>

	<?php if ( $url ) : ?>
		<button type="button" class="button button-primary" id="cw-qr-gen-btn"
			data-post-id="<?php echo esc_attr( $post->ID ); ?>"
			data-nonce="<?php echo esc_attr( $nonce ); ?>">
			<?php esc_html_e( 'Generate QR', 'codeweber' ); ?>
		</button>

		<div id="cw-qr-result" style="display:none;margin-top:12px;text-align:center;">
			<img id="cw-qr-preview"
				style="max-width:200px;width:100%;display:block;margin:0 auto 10px;image-rendering:pixelated;"
				alt="QR Code" />
			<a id="cw-qr-download" class="button button-secondary" download="event-qr.svg" style="display:inline-block;">
				<?php esc_html_e( 'Download SVG', 'codeweber' ); ?>
			</a>
		</div>

		<p id="cw-qr-error" style="display:none;color:#d63638;font-size:12px;margin-top:8px;"></p>

		<script>
		(function ($) {
			$('#cw-qr-gen-btn').on('click', function () {
				var btn = $(this);
				btn.prop('disabled', true).text('<?php echo esc_js( __( 'Generating…', 'codeweber' ) ); ?>');
				$('#cw-qr-error').hide();
				$('#cw-qr-result').hide();

				$.post(ajaxurl, {
					action:  'cw_event_generate_qr',
					post_id: btn.data('post-id'),
					nonce:   btn.data('nonce')
				}, function (res) {
					if (res.success) {
						var src = 'data:image/svg+xml;base64,' + res.data.svg_b64;
						$('#cw-qr-preview').attr('src', src);
						$('#cw-qr-download').attr('href', src);
						$('#cw-qr-result').show();
					} else {
						$('#cw-qr-error').text(res.data.message).show();
					}
					btn.prop('disabled', false).text('<?php echo esc_js( __( 'Generate QR', 'codeweber' ) ); ?>');
				}).fail(function () {
					$('#cw-qr-error').text('<?php echo esc_js( __( 'Request failed.', 'codeweber' ) ); ?>').show();
					btn.prop('disabled', false).text('<?php echo esc_js( __( 'Generate QR', 'codeweber' ) ); ?>');
				});
			});
		}(jQuery));
		</script>
	<?php endif;
}

add_action( 'wp_ajax_cw_event_generate_qr', 'codeweber_event_qr_ajax' );

function codeweber_event_qr_ajax() {
	$post_id = intval( $_POST['post_id'] ?? 0 );

	if (
		! $post_id
		|| ! isset( $_POST['nonce'] )
		|| ! wp_verify_nonce( $_POST['nonce'], 'cw_event_qr_' . $post_id )
		|| ! current_user_can( 'edit_post', $post_id )
	) {
		wp_send_json_error( [ 'message' => __( 'Security error.', 'codeweber' ) ] );
	}

	$url = get_permalink( $post_id );
	if ( ! $url ) {
		wp_send_json_error( [ 'message' => __( 'Cannot get event URL.', 'codeweber' ) ] );
	}

	$lib = get_template_directory() . '/functions/lib/phpqrcode/phpqrcode.php';
	if ( ! file_exists( $lib ) ) {
		wp_send_json_error( [ 'message' => __( 'QR library not found.', 'codeweber' ) ] );
	}
	require_once $lib;

	if ( ! class_exists( 'QRencode' ) ) {
		wp_send_json_error( [ 'message' => __( 'QRencode class not available.', 'codeweber' ) ] );
	}

	// Build QR matrix (frame rows are strings; dark module = '1')
	$enc   = QRencode::factory( QR_ECLEVEL_H, 10, 4 );
	$frame = $enc->encode( $url );

	$rows   = count( $frame );
	$cols   = strlen( $frame[0] );
	$margin = 4;
	$total  = $cols + 2 * $margin; // QR is always square

	// Build SVG <rect> elements for dark modules only
	$rects = '';
	for ( $y = 0; $y < $rows; $y++ ) {
		for ( $x = 0; $x < $cols; $x++ ) {
			if ( $frame[ $y ][ $x ] === '1' ) {
				$rects .= '<rect x="' . ( $x + $margin ) . '" y="' . ( $y + $margin ) . '" width="1" height="1"/>';
			}
		}
	}

	$svg = '<?xml version="1.0" encoding="UTF-8"?>'
		. '<svg xmlns="http://www.w3.org/2000/svg"'
		. ' viewBox="0 0 ' . $total . ' ' . $total . '"'
		. ' shape-rendering="crispEdges">'
		. '<rect width="' . $total . '" height="' . $total . '" fill="#fff"/>'
		. '<g fill="#000">' . $rects . '</g>'
		. '</svg>';

	wp_send_json_success( [ 'svg_b64' => base64_encode( $svg ) ] );
}

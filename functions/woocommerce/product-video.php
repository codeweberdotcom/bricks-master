<?php
/**
 * WooCommerce Product Video — метабокс для добавления видео к товару.
 *
 * Meta keys:
 *   _cw_product_video_url       — URL видео (YouTube / Vimeo / VK / Rutube / MP4)
 *   _cw_product_video_poster_id — ID вложения-постера (необязательно)
 *
 * Видео-слайд добавляется последним в галерею single-product.php.
 */

defined( 'ABSPATH' ) || exit;

// ── Регистрация метабокса ─────────────────────────────────────────────────────

add_action( 'add_meta_boxes', 'cw_product_video_add_metabox' );

function cw_product_video_add_metabox(): void {
	add_meta_box(
		'cw-product-video',
		__( 'Product Video', 'codeweber' ),
		'cw_product_video_render_metabox',
		'product',
		'normal',
		'default'
	);
}

// ── Рендер метабокса ─────────────────────────────────────────────────────────

function cw_product_video_render_metabox( WP_Post $post ): void {
	wp_nonce_field( 'cw_product_video_save', 'cw_product_video_nonce' );

	$video_url = get_post_meta( $post->ID, '_cw_product_video_url', true );
	$poster_id = (int) get_post_meta( $post->ID, '_cw_product_video_poster_id', true );

	?>
	<p>
		<label for="cw_product_video_url"><strong><?php esc_html_e( 'Video URL', 'codeweber' ); ?></strong></label><br>
		<input
			type="url"
			id="cw_product_video_url"
			name="cw_product_video_url"
			value="<?php echo esc_attr( $video_url ); ?>"
			class="widefat"
			placeholder="https://www.youtube.com/watch?v=... or https://vimeo.com/... or https://vkvideo.ru/... or https://rutube.ru/... or /path/to/video.mp4"
		>
		<span class="description"><?php esc_html_e( 'Supported: YouTube, Vimeo, VK Video, Rutube, MP4.', 'codeweber' ); ?></span>
	</p>

	<p>
		<label><strong><?php esc_html_e( 'Video Poster (optional)', 'codeweber' ); ?></strong></label><br>
		<?php if ( $poster_id && wp_attachment_is_image( $poster_id ) ) : ?>
			<img src="<?php echo esc_url( wp_get_attachment_thumb_url( $poster_id ) ); ?>" style="max-width:200px;display:block;margin-bottom:8px;">
		<?php endif; ?>
		<input type="hidden" id="cw_product_video_poster_id" name="cw_product_video_poster_id" value="<?php echo esc_attr( $poster_id ?: '' ); ?>">
		<button type="button" class="button" id="cw_product_video_poster_btn"><?php esc_html_e( 'Select Image', 'codeweber' ); ?></button>
		<?php if ( $poster_id ) : ?>
			<button type="button" class="button" id="cw_product_video_poster_remove"><?php esc_html_e( 'Remove', 'codeweber' ); ?></button>
		<?php endif; ?>
	</p>

	<script>
	(function($) {
		var frame;
		$('#cw_product_video_poster_btn').on('click', function(e) {
			e.preventDefault();
			if (frame) { frame.open(); return; }
			frame = wp.media({ title: '<?php echo esc_js( __( 'Select Poster Image', 'codeweber' ) ); ?>', button: { text: '<?php echo esc_js( __( 'Use this image', 'codeweber' ) ); ?>' }, multiple: false });
			frame.on('select', function() {
				var att = frame.state().get('selection').first().toJSON();
				$('#cw_product_video_poster_id').val(att.id);
				location.reload();
			});
			frame.open();
		});
		$('#cw_product_video_poster_remove').on('click', function() {
			$('#cw_product_video_poster_id').val('');
			$(this).closest('p').find('img').remove();
			$(this).remove();
		});
	})(jQuery);
	</script>
	<?php
}

// ── Сохранение ────────────────────────────────────────────────────────────────

add_action( 'save_post_product', 'cw_product_video_save_metabox' );

function cw_product_video_save_metabox( int $post_id ): void {
	if ( ! isset( $_POST['cw_product_video_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_key( $_POST['cw_product_video_nonce'] ), 'cw_product_video_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$video_url = isset( $_POST['cw_product_video_url'] ) ? esc_url_raw( wp_strip_all_tags( $_POST['cw_product_video_url'] ) ) : '';
	$poster_id = isset( $_POST['cw_product_video_poster_id'] ) ? absint( $_POST['cw_product_video_poster_id'] ) : 0;

	if ( $video_url ) {
		update_post_meta( $post_id, '_cw_product_video_url', $video_url );
	} else {
		delete_post_meta( $post_id, '_cw_product_video_url' );
	}

	if ( $poster_id ) {
		update_post_meta( $post_id, '_cw_product_video_poster_id', $poster_id );
	} else {
		delete_post_meta( $post_id, '_cw_product_video_poster_id' );
	}
}

// ── Хелпер: разбор URL видео ──────────────────────────────────────────────────

/**
 * Возвращает массив с типом и данными для рендера видео-слайда.
 *
 * @param string $url
 * @return array{type: string, glightbox_href: string, glightbox_attrs: string, embed_id: string}|null
 */
function cw_product_video_parse( string $url ): ?array {
	if ( ! $url ) {
		return null;
	}

	// YouTube
	if ( preg_match( '/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m ) ) {
		return [
			'type'           => 'youtube',
			'glightbox_href' => $url,
			'glightbox_attrs' => 'data-glightbox',
			'embed_id'       => '',
		];
	}

	// Vimeo
	if ( preg_match( '/vimeo\.com\/(\d+)/', $url, $m ) ) {
		return [
			'type'           => 'vimeo',
			'glightbox_href' => $url,
			'glightbox_attrs' => 'data-glightbox',
			'embed_id'       => '',
		];
	}

	// VK Video — embed URL: https://vkvideo.ru/video_ext.php?oid=-OID&id=ID
	if ( preg_match( '/vkvideo\.ru\/video(-?\d+)_(\d+)/', $url, $m ) ) {
		$embed = 'https://vkvideo.ru/video_ext.php?oid=' . $m[1] . '&id=' . $m[2] . '&hd=2';
		$uid   = 'cw-vv-' . $m[1] . '-' . $m[2];
		return [
			'type'           => 'vk',
			'glightbox_href' => '#' . $uid,
			'glightbox_attrs' => 'data-glightbox="type: iframe; width: 90vw; height: 90vh;"',
			'embed_id'       => $uid,
			'embed_url'      => $embed,
		];
	}

	// Rutube — embed URL: https://rutube.ru/play/embed/HASH
	if ( preg_match( '/rutube\.ru\/video\/([a-f0-9]+)/', $url, $m ) ) {
		$embed = 'https://rutube.ru/play/embed/' . $m[1];
		$uid   = 'cw-rt-' . $m[1];
		return [
			'type'           => 'rutube',
			'glightbox_href' => '#' . $uid,
			'glightbox_attrs' => 'data-glightbox="type: iframe; width: 90vw; height: 90vh;"',
			'embed_id'       => $uid,
			'embed_url'      => $embed,
		];
	}

	// HTML5 / MP4
	if ( preg_match( '/\.(mp4|webm|ogg)(\?.*)?$/i', $url ) ) {
		return [
			'type'           => 'video',
			'glightbox_href' => $url,
			'glightbox_attrs' => 'data-glightbox',
			'embed_id'       => '',
		];
	}

	return null;
}

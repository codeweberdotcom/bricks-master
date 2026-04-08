<?php
/**
 * WooCommerce Product Video — метабокс для добавления видео к товару.
 *
 * Meta keys:
 *   _cw_product_video_type      — тип видео: youtube / vimeo / vk / rutube / mp4
 *   _cw_product_video_url       — URL видео (исходный, от пользователя)
 *   _cw_product_video_poster_id — ID вложения-постера (необязательно)
 *
 * Видео-превью добавляется в thumbs-слайдер single-product.php.
 * В основном слайдере видео-слайда нет — только фото.
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

	$video_type = get_post_meta( $post->ID, '_cw_product_video_type', true ) ?: 'youtube';
	$video_url  = get_post_meta( $post->ID, '_cw_product_video_url', true );
	$poster_id  = (int) get_post_meta( $post->ID, '_cw_product_video_poster_id', true );

	$types = [
		'youtube' => [
			'label'       => 'YouTube',
			'placeholder' => 'https://www.youtube.com/watch?v=VIDEO_ID',
		],
		'vimeo'   => [
			'label'       => 'Vimeo',
			'placeholder' => 'https://vimeo.com/VIDEO_ID',
		],
		'vk'      => [
			'label'       => 'VK Video',
			'placeholder' => 'https://vkvideo.ru/video-123456_789012',
		],
		'rutube'  => [
			'label'       => 'Rutube',
			'placeholder' => 'https://rutube.ru/video/HASH32CHARS/',
		],
		'mp4'     => [
			'label'       => 'MP4 / WebM',
			'placeholder' => '/wp-content/uploads/video.mp4',
		],
	];
	?>

	<p style="margin-bottom:8px;"><strong><?php esc_html_e( 'Video Type', 'codeweber' ); ?></strong></p>
	<p style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:12px;">
		<?php foreach ( $types as $val => $info ) : ?>
		<label style="display:inline-flex;align-items:center;gap:4px;cursor:pointer;">
			<input type="radio"
			       name="cw_product_video_type"
			       value="<?php echo esc_attr( $val ); ?>"
			       <?php checked( $video_type, $val ); ?>
			       data-placeholder="<?php echo esc_attr( $info['placeholder'] ); ?>">
			<?php echo esc_html( $info['label'] ); ?>
		</label>
		<?php endforeach; ?>
	</p>

	<p>
		<label for="cw_product_video_url"><strong><?php esc_html_e( 'Video URL', 'codeweber' ); ?></strong></label><br>
		<input
			type="url"
			id="cw_product_video_url"
			name="cw_product_video_url"
			value="<?php echo esc_attr( $video_url ); ?>"
			class="widefat"
			placeholder="<?php echo esc_attr( $types[ $video_type ]['placeholder'] ); ?>"
		>
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
		// Обновляем placeholder URL-поля при смене типа
		$('input[name="cw_product_video_type"]').on('change', function() {
			$('#cw_product_video_url').attr('placeholder', $(this).data('placeholder'));
		});

		// Медиа-пикер постера
		var frame;
		$('#cw_product_video_poster_btn').on('click', function(e) {
			e.preventDefault();
			if (frame) { frame.open(); return; }
			frame = wp.media({
				title: '<?php echo esc_js( __( 'Select Poster Image', 'codeweber' ) ); ?>',
				button: { text: '<?php echo esc_js( __( 'Use this image', 'codeweber' ) ); ?>' },
				multiple: false
			});
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

	$allowed_types = [ 'youtube', 'vimeo', 'vk', 'rutube', 'mp4' ];
	$video_type    = isset( $_POST['cw_product_video_type'] ) && in_array( $_POST['cw_product_video_type'], $allowed_types, true )
		? $_POST['cw_product_video_type']
		: 'youtube';

	$video_url = isset( $_POST['cw_product_video_url'] ) ? esc_url_raw( wp_strip_all_tags( $_POST['cw_product_video_url'] ) ) : '';
	$poster_id = isset( $_POST['cw_product_video_poster_id'] ) ? absint( $_POST['cw_product_video_poster_id'] ) : 0;

	update_post_meta( $post_id, '_cw_product_video_type', $video_type );

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
 * Возвращает данные для рендера видео-превью в галерее товара.
 *
 * @param string $url  Исходный URL (от пользователя).
 * @param string $type Тип: youtube|vimeo|vk|rutube|mp4. Если пуст — автодетект.
 * @return array{type:string, glightbox_href:string, glightbox_attrs:string, embed_id:string, embed_url:string}|null
 */
function cw_product_video_parse( string $url, string $type = '' ): ?array {
	if ( ! $url ) {
		return null;
	}

	// Автодетект типа если не задан явно
	if ( ! $type ) {
		if ( preg_match( '/(?:youtube\.com|youtu\.be)/', $url ) ) {
			$type = 'youtube';
		} elseif ( strpos( $url, 'vimeo.com' ) !== false ) {
			$type = 'vimeo';
		} elseif ( preg_match( '/vkvideo\.ru|vk\.com\/video/', $url ) ) {
			$type = 'vk';
		} elseif ( strpos( $url, 'rutube.ru' ) !== false ) {
			$type = 'rutube';
		} else {
			$type = 'mp4';
		}
	}

	switch ( $type ) {

		case 'youtube':
			if ( preg_match( '/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m ) ) {
				return [
					'type'            => 'youtube',
					'glightbox_href'  => 'https://www.youtube.com/watch?v=' . $m[1],
					'glightbox_attrs' => 'data-glightbox',
					'embed_id'        => '',
					'embed_url'       => '',
				];
			}
			break;

		case 'vimeo':
			if ( preg_match( '/vimeo\.com\/(\d+)/', $url, $m ) ) {
				return [
					'type'            => 'vimeo',
					'glightbox_href'  => 'https://vimeo.com/' . $m[1],
					'glightbox_attrs' => 'data-glightbox',
					'embed_id'        => '',
					'embed_url'       => '',
				];
			}
			break;

		case 'vk':
			// Принимает: https://vkvideo.ru/video-OID_ID или https://vkvideo.ru/video_ext.php?oid=OID&id=ID
			if ( preg_match( '/(?:vkvideo\.ru|vk\.com)\/video(-?\d+)_(\d+)/', $url, $m ) ) {
				$oid   = $m[1];
				$id    = $m[2];
				$embed = 'https://vkvideo.ru/video_ext.php?oid=' . $oid . '&id=' . $id
				         . '&hd=2&autoplay=1&allowFullscreen=true&fullscreen=true';
				$uid   = 'cw-vv-' . ltrim( $oid, '-' ) . '-' . $id;
				return [
					'type'            => 'vk',
					'glightbox_href'  => '#' . $uid,
					'glightbox_attrs' => 'data-glightbox="type: iframe; width: 90vw; height: 90vh;"',
					'embed_id'        => $uid,
					'embed_url'       => $embed,
				];
			}
			break;

		case 'rutube':
			// Принимает: https://rutube.ru/video/HASH/ или https://rutube.ru/play/embed/HASH
			if ( preg_match( '/rutube\.ru\/(?:video|play\/embed)\/([a-f0-9]{32})/', $url, $m ) ) {
				$vid_id = $m[1];
				$embed  = 'https://rutube.ru/play/embed/' . $vid_id . '?autoplay=1';
				$uid    = 'cw-rt-' . $vid_id;
				return [
					'type'            => 'rutube',
					'glightbox_href'  => '#' . $uid,
					'glightbox_attrs' => 'data-glightbox="type: iframe; width: 90vw; height: 90vh;"',
					'embed_id'        => $uid,
					'embed_url'       => $embed,
				];
			}
			// Просто 32-символьный хэш
			if ( preg_match( '/^[a-f0-9]{32}$/', trim( $url ) ) ) {
				$vid_id = trim( $url );
				$embed  = 'https://rutube.ru/play/embed/' . $vid_id . '?autoplay=1';
				$uid    = 'cw-rt-' . $vid_id;
				return [
					'type'            => 'rutube',
					'glightbox_href'  => '#' . $uid,
					'glightbox_attrs' => 'data-glightbox="type: iframe; width: 90vw; height: 90vh;"',
					'embed_id'        => $uid,
					'embed_url'       => $embed,
				];
			}
			break;

		case 'mp4':
		case 'video':
			return [
				'type'            => 'video',
				'glightbox_href'  => $url,
				'glightbox_attrs' => 'data-glightbox',
				'embed_id'        => '',
				'embed_url'       => '',
			];
	}

	return null;
}

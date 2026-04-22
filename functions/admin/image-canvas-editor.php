<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CW_Image_Canvas_Editor {

	public function __construct() {
		add_action( 'add_meta_boxes',        [ $this, 'register_meta_boxes' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'wp_ajax_cw_img_editor_save', [ $this, 'ajax_save' ] );
	}

	public function register_meta_boxes() {
		if ( class_exists( 'WooCommerce' ) ) {
			add_meta_box(
				'cw-img-canvas-editor',
				__( 'Image Canvas Editor', 'codeweber' ),
				[ $this, 'render_product_metabox' ],
				'product',
				'side',
				'low'
			);
		}

		add_meta_box(
			'cw-img-canvas-editor-attachment',
			__( 'Image Canvas Editor', 'codeweber' ),
			[ $this, 'render_attachment_metabox' ],
			'attachment',
			'side',
			'low'
		);
	}

	public function enqueue_assets( $hook ) {
		$screen = get_current_screen();
		if ( ! $screen ) return;

		$on_product    = in_array( $hook, [ 'post.php', 'post-new.php' ], true ) && $screen->post_type === 'product';
		$on_attachment = in_array( $hook, [ 'post.php', 'post-new.php' ], true ) && $screen->post_type === 'attachment';

		if ( ! $on_product && ! $on_attachment ) return;

		$theme_uri = get_template_directory_uri();
		$theme_dir = get_template_directory();

		wp_enqueue_style(
			'cw-img-editor',
			$theme_uri . '/functions/admin/image-canvas-editor.css',
			[],
			filemtime( $theme_dir . '/functions/admin/image-canvas-editor.css' )
		);

		wp_enqueue_script(
			'cw-img-editor',
			$theme_uri . '/functions/admin/image-canvas-editor.js',
			[],
			filemtime( $theme_dir . '/functions/admin/image-canvas-editor.js' ),
			true
		);

		wp_localize_script( 'cw-img-editor', 'cwImgEditorData', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'cw_img_editor' ),
			'i18n'    => [
				'saving'     => __( 'Saving...', 'codeweber' ),
				'saved'      => __( 'Saved!', 'codeweber' ),
				'error'      => __( 'Error saving image', 'codeweber' ),
				'makeSquare' => __( 'Make square', 'codeweber' ),
				'noImage'    => __( 'Failed to load image', 'codeweber' ),
			],
		] );
	}

	public function render_product_metabox( $post ) {
		$featured_id     = (int) get_post_thumbnail_id( $post->ID );
		$gallery_raw     = get_post_meta( $post->ID, '_product_image_gallery', true );
		$gallery_ids     = array_filter( array_map( 'absint', explode( ',', $gallery_raw ) ) );

		$all_ids = array_unique( array_filter( array_merge( [ $featured_id ], $gallery_ids ) ) );

		if ( empty( $all_ids ) ) {
			echo '<p style="color:#666;font-style:italic;">' . esc_html__( 'No product images found.', 'codeweber' ) . '</p>';
			return;
		}

		echo '<div class="cwice-thumbs">';
		foreach ( $all_ids as $id ) {
			$this->render_thumb( $id );
		}
		echo '</div>';
	}

	public function render_attachment_metabox( $post ) {
		if ( ! wp_attachment_is_image( $post->ID ) ) {
			echo '<p style="color:#666;font-style:italic;">' . esc_html__( 'Not an image.', 'codeweber' ) . '</p>';
			return;
		}
		$this->render_thumb( $post->ID );
	}

	private function render_thumb( $attachment_id ) {
		$thumb    = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );
		$full     = wp_get_attachment_image_src( $attachment_id, 'full' );
		$filename = basename( (string) get_attached_file( $attachment_id ) );

		if ( ! $thumb || ! $full ) return;
		?>
		<div class="cwice-thumb-item">
			<img src="<?php echo esc_url( $thumb[0] ); ?>" width="60" height="60" alt="">
			<span class="cwice-filename" title="<?php echo esc_attr( $filename ); ?>"><?php echo esc_html( $filename ); ?></span>
			<button type="button" class="button cwice-open-btn"
				data-id="<?php echo esc_attr( $attachment_id ); ?>"
				data-url="<?php echo esc_url( $full[0] ); ?>"
				data-w="<?php echo esc_attr( $full[1] ); ?>"
				data-h="<?php echo esc_attr( $full[2] ); ?>">
				<?php esc_html_e( 'Edit', 'codeweber' ); ?>
			</button>
		</div>
		<?php
	}

	public function ajax_save() {
		check_ajax_referer( 'cw_img_editor', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( [ 'message' => 'Insufficient permissions' ] );
		}

		$attachment_id = absint( $_POST['attachment_id'] ?? 0 );
		$raw_data      = $_POST['image_data'] ?? '';  // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$mime_type     = sanitize_text_field( $_POST['mime_type'] ?? 'image/jpeg' );

		if ( ! $attachment_id || ! $raw_data ) {
			wp_send_json_error( [ 'message' => 'Missing parameters' ] );
		}

		// Strip data URI prefix
		$image_data = preg_replace( '/^data:image\/\w+;base64,/', '', $raw_data );
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		$image_data = base64_decode( $image_data );

		if ( ! $image_data ) {
			wp_send_json_error( [ 'message' => 'Invalid image data' ] );
		}

		$file_path = get_attached_file( $attachment_id );

		// If S3 module is active and file is missing locally — restore first
		if ( class_exists( 'Codeweber\S3Storage\Services\RestoreService' ) && ! file_exists( $file_path ) ) {
			$result = \Codeweber\S3Storage\Services\RestoreService::restore_attachment( $attachment_id );
			if ( is_wp_error( $result ) ) {
				wp_send_json_error( [ 'message' => 'S3 restore failed: ' . $result->get_error_message() ] );
			}
			$file_path = get_attached_file( $attachment_id );
		}

		if ( ! $file_path ) {
			wp_send_json_error( [ 'message' => 'Attachment file path not found' ] );
		}

		// Overwrite the original file
		if ( file_put_contents( $file_path, $image_data ) === false ) {
			wp_send_json_error( [ 'message' => 'Failed to write file' ] );
		}

		// Regenerate all thumbnail sizes
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$metadata = wp_generate_attachment_metadata( $attachment_id, $file_path );

		if ( is_wp_error( $metadata ) ) {
			wp_send_json_error( [ 'message' => 'Failed to regenerate thumbnails' ] );
		}

		// Save metadata — also triggers S3 deferred offload via Uploader hook
		wp_update_attachment_metadata( $attachment_id, $metadata );

		wp_send_json_success( [
			'url'     => wp_get_attachment_url( $attachment_id ),
			'message' => 'Saved and thumbnails regenerated',
		] );
	}
}

new CW_Image_Canvas_Editor();

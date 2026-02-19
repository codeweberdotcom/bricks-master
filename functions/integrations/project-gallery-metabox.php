<?php
/**
 * Project Gallery Metabox: FilePond + SortableJS
 *
 * Replaces Redux "slides" repeater with mass upload (FilePond) and sortable grid (SortableJS).
 * Meta key: _project_gallery (array of attachment IDs in order).
 *
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
	exit;
}

const CODEWEBER_PROJECT_GALLERY_META = '_project_gallery';

/**
 * Register metabox and hooks.
 */
function codeweber_project_gallery_metabox_register(): void {
	add_action('add_meta_boxes', 'codeweber_project_gallery_add_metabox');
	add_action('save_post_projects', 'codeweber_project_gallery_save_meta', 10, 2);
	add_action('admin_enqueue_scripts', 'codeweber_project_gallery_admin_scripts', 10, 1);
	add_action('wp_ajax_codeweber_project_gallery_upload', 'codeweber_project_gallery_ajax_upload');
}

/**
 * Add metabox for post type projects.
 */
function codeweber_project_gallery_add_metabox(): void {
	add_meta_box(
		'codeweber_project_gallery',
		__('Project Gallery', 'codeweber'),
		'codeweber_project_gallery_render_metabox',
		'projects',
		'normal',
		'high'
	);
}

/**
 * Render metabox: sortable grid of existing images + FilePond for new uploads.
 */
function codeweber_project_gallery_render_metabox(\WP_Post $post): void {
	wp_nonce_field('codeweber_project_gallery_save', 'codeweber_project_gallery_nonce');
	$ids = codeweber_get_project_gallery_ids($post->ID);
	$value = implode(',', array_map('intval', $ids));
	?>
	<input type="hidden" name="project_gallery_ids" id="project_gallery_ids" value="<?php echo esc_attr($value); ?>">
	<div class="codeweber-project-gallery-wrap">
		<p class="description"><?php esc_html_e('Add images via the upload area below. Drag items in the grid to reorder.', 'codeweber'); ?></p>
		<?php if (!$post->ID) : ?>
		<p class="description" style="color:#856404; background:#fff3cd; padding:8px 12px; border-radius:4px;"><?php esc_html_e('Save the project as draft first, then you can add gallery images.', 'codeweber'); ?></p>
		<?php endif; ?>
		<div id="project-gallery-sortable-grid" class="project-gallery-sortable-grid">
			<?php
			foreach ($ids as $aid) {
				$src = wp_get_attachment_image_url($aid, 'thumbnail');
				if (!$src) {
					continue;
				}
				?>
				<div class="project-gallery-item" data-id="<?php echo esc_attr($aid); ?>">
					<img src="<?php echo esc_url($src); ?>" alt="">
					<button type="button" class="project-gallery-remove" aria-label="<?php esc_attr_e('Remove', 'codeweber'); ?>">&times;</button>
				</div>
				<?php
			}
			?>
		</div>
		<div class="project-gallery-filepond-wrap" <?php echo $post->ID ? '' : ' style="display:none;"'; ?>>
			<input type="file"
				class="filepond project-gallery-filepond"
				id="project_gallery_filepond"
				name="file"
				accept="image/*"
				multiple
				data-filepond="true">
		</div>
	</div>
	<?php
}

/**
 * Save gallery meta on post save.
 */
function codeweber_project_gallery_save_meta(int $post_id, \WP_Post $post): void {
	if (!isset($_POST['codeweber_project_gallery_nonce']) ||
		!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['codeweber_project_gallery_nonce'])), 'codeweber_project_gallery_save')) {
		return;
	}
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	if (!current_user_can('edit_post', $post_id)) {
		return;
	}
	$raw = isset($_POST['project_gallery_ids']) ? sanitize_text_field(wp_unslash($_POST['project_gallery_ids'])) : '';
	$ids = array_filter(array_map('intval', explode(',', $raw)));
	update_post_meta($post_id, CODEWEBER_PROJECT_GALLERY_META, $ids);
}

/**
 * Enqueue FilePond (from plugin), SortableJS, and our admin script only on project edit screen.
 */
function codeweber_project_gallery_admin_scripts(string $hook): void {
	$screen = get_current_screen();
	if (!$screen || $screen->post_type !== 'projects' || ($hook !== 'post.php' && $hook !== 'post-new.php')) {
		return;
	}
	// FilePond assets from plugin
	if (class_exists('\Codeweber\Blocks\Plugin')) {
		\Codeweber\Blocks\Plugin::enqueue_filepond_assets_admin();
	}
	// SortableJS
	wp_enqueue_script(
		'sortablejs',
		'https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js',
		[],
		'1.15.2',
		true
	);
	// Our admin init (FilePond + Sortable)
	$script_path = get_template_directory() . '/functions/integrations/project-gallery-metabox-assets.js';
	$script_url  = get_template_directory_uri() . '/functions/integrations/project-gallery-metabox-assets.js';
	wp_enqueue_script(
		'codeweber-project-gallery-admin',
		$script_url,
		['filepond', 'sortablejs'],
		file_exists($script_path) ? (string) filemtime($script_path) : '1.0',
		true
	);
	wp_localize_script('codeweber-project-gallery-admin', 'codeweberProjectGallery', [
		'uploadUrl' => admin_url('admin-ajax.php?action=codeweber_project_gallery_upload'),
		'nonce'     => wp_create_nonce('codeweber_project_gallery_upload'),
		'postId'    => get_the_ID() ?: 0,
		'i18n'      => [
			'remove'            => __('Remove', 'codeweber'),
			'add'               => __('Add images', 'codeweber'),
			'labelIdle'         => __('Drag & Drop your files or <span class="filepond--label-action">browse</span>', 'codeweber'),
			'description'      => __('Add images via the upload area below. Drag items in the grid to reorder.', 'codeweber'),
			'saveDraftFirst'    => __('Save the project as draft first, then you can add gallery images.', 'codeweber'),
			'uploadFailed'      => __('Upload failed', 'codeweber'),
			'securityError'     => __('Security check failed. Reload the page and try again.', 'codeweber'),
			'invalidRequest'    => __('Invalid request.', 'codeweber'),
			'noFileReceived'    => __('No file received.', 'codeweber'),
			'uploadComplete'    => __('Upload complete', 'codeweber'),
			'tapToCancel'       => __('Tap to cancel', 'codeweber'),
			'tapToUndo'         => __('Tap to undo', 'codeweber'),
		],
	]);
	wp_enqueue_style(
		'codeweber-project-gallery-admin',
		get_template_directory_uri() . '/functions/integrations/project-gallery-metabox.css',
		['filepond'],
		file_exists(get_template_directory() . '/functions/integrations/project-gallery-metabox.css') ? (string) filemtime(get_template_directory() . '/functions/integrations/project-gallery-metabox.css') : '1.0'
	);
}

/**
 * AJAX: upload file and attach to project. Returns JSON with file_id for FilePond.
 */
function codeweber_project_gallery_ajax_upload(): void {
	// Verify nonce manually so we always return JSON (check_ajax_referer would wp_die with -1)
	$nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
	if (!wp_verify_nonce($nonce, 'codeweber_project_gallery_upload')) {
		wp_send_json_error(['message' => __('Security check failed. Reload the page and try again.', 'codeweber')], 403);
	}
	$post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
	if (!$post_id || get_post_type($post_id) !== 'projects' || !current_user_can('edit_post', $post_id)) {
		wp_send_json_error(['message' => __('Invalid request.', 'codeweber')], 403);
	}
	// FilePond may send as 'file', 'filepond', or the input name 'project_gallery_files' (array or single)
	$file_key = null;
	$candidates = ['file', 'filepond', 'project_gallery_files'];
	foreach ($candidates as $key) {
		if (empty($_FILES[$key])) {
			continue;
		}
		$f = $_FILES[$key];
		// Array format: name="project_gallery_files[]" => ['name' => [0=>'x.jpg'], 'tmp_name' => [0=>'...'], ...]
		if (isset($f['name']) && is_array($f['name'])) {
			$idx = key($f['name']);
			if ($idx !== null && isset($f['tmp_name'][$idx]) && is_uploaded_file($f['tmp_name'][$idx])) {
				$_FILES['_project_gallery_single'] = [
					'name'     => $f['name'][$idx],
					'type'     => $f['type'][$idx] ?? '',
					'tmp_name' => $f['tmp_name'][$idx],
					'error'    => $f['error'][$idx] ?? 0,
					'size'     => $f['size'][$idx] ?? 0,
				];
				$file_key = '_project_gallery_single';
				break;
			}
		}
		// Single file
		if ($file_key === null && !empty($f['name']) && isset($f['tmp_name']) && is_uploaded_file($f['tmp_name'])) {
			$file_key = $key;
			break;
		}
	}
	if ($file_key === null) {
		wp_send_json_error(['message' => __('No file received.', 'codeweber')], 400);
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	$aid = media_handle_upload($file_key, $post_id, [], ['test_form' => false]);
	if (is_wp_error($aid)) {
		wp_send_json_error(['message' => $aid->get_error_message()], 400);
	}
	$thumb_url = wp_get_attachment_image_url($aid, 'thumbnail');
	wp_send_json_success([
		'file_id'       => (string) $aid,
		'file'          => ['id' => (string) $aid],
		'thumbnail_url' => $thumb_url ?: '',
	]);
}

/**
 * Get project gallery attachment IDs in order.
 * Uses _project_gallery; falls back to Redux opt-slides for backward compatibility.
 *
 * @param int $post_id Project post ID.
 * @return int[] Attachment IDs in display order.
 */
function codeweber_get_project_gallery_ids(int $post_id): array {
	$ids = get_post_meta($post_id, CODEWEBER_PROJECT_GALLERY_META, true);
	if (is_array($ids) && !empty($ids)) {
		return array_values(array_map('intval', $ids));
	}
	// Legacy: Redux opt-slides
	$opt_name = 'redux_demo';
	if (function_exists('redux_post_meta')) {
		$slides = redux_post_meta($opt_name, $post_id, 'opt-slides', []);
		if (is_array($slides)) {
			$legacy = [];
			foreach ($slides as $slide) {
				if (!empty($slide['attachment_id'])) {
					$legacy[] = (int) $slide['attachment_id'];
				}
			}
			return $legacy;
		}
	}
	return [];
}

add_action('init', 'codeweber_project_gallery_metabox_register', 20);

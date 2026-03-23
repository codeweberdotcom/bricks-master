<?php
/**
 * CPT: Events (Мероприятия)
 *
 * Регистрирует:
 *  - Post type: events
 *  - Taxonomy: event_category (hierarchical)
 *  - Taxonomy: event_format (flat)
 *  - Meta boxes: Даты, Место, Регистрация, Медиа (видео)
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Post Type
// ---------------------------------------------------------------------------

function cptui_register_my_cpts_events() {
	$labels = [
		'name'               => esc_html__( 'Events', 'codeweber' ),
		'singular_name'      => esc_html__( 'Event', 'codeweber' ),
		'menu_name'          => esc_html__( 'Events', 'codeweber' ),
		'all_items'          => esc_html__( 'All Events', 'codeweber' ),
		'add_new'            => esc_html__( 'Add Event', 'codeweber' ),
		'add_new_item'       => esc_html__( 'Add New Event', 'codeweber' ),
		'edit_item'          => esc_html__( 'Edit Event', 'codeweber' ),
		'new_item'           => esc_html__( 'New Event', 'codeweber' ),
		'view_item'          => esc_html__( 'View Event', 'codeweber' ),
		'view_items'         => esc_html__( 'View Events', 'codeweber' ),
		'search_items'       => esc_html__( 'Search Events', 'codeweber' ),
		'not_found'          => esc_html__( 'No events found', 'codeweber' ),
		'not_found_in_trash' => esc_html__( 'No events found in trash', 'codeweber' ),
		'items_list'         => esc_html__( 'Events list', 'codeweber' ),
		'name_admin_bar'     => esc_html__( 'Event', 'codeweber' ),
		'item_published'     => esc_html__( 'Event published', 'codeweber' ),
		'item_updated'       => esc_html__( 'Event updated', 'codeweber' ),
	];

	$args = [
		'label'                 => esc_html__( 'Events', 'codeweber' ),
		'labels'                => $labels,
		'description'           => '',
		'public'                => true,
		'publicly_queryable'    => true,
		'show_ui'               => true,
		'show_in_rest'          => true,
		'rest_base'             => '',
		'rest_controller_class' => 'WP_REST_Posts_Controller',
		'rest_namespace'        => 'wp/v2',
		'has_archive'           => 'events',
		'show_in_menu'          => true,
		'show_in_nav_menus'     => true,
		'delete_with_user'      => false,
		'exclude_from_search'   => false,
		'capability_type'       => 'post',
		'map_meta_cap'          => true,
		'hierarchical'          => false,
		'can_export'            => true,
		'rewrite'               => [ 'slug' => 'events', 'with_front' => true ],
		'query_var'             => true,
		'supports'              => [ 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'author' ],
		'taxonomies'            => [ 'event_category', 'event_format' ],
		'show_in_graphql'       => false,
		'menu_icon'             => 'dashicons-calendar-alt',
	];

	register_post_type( 'events', $args );
}
add_action( 'init', 'cptui_register_my_cpts_events' );

// ---------------------------------------------------------------------------
// Taxonomy: Event Category (hierarchical)
// ---------------------------------------------------------------------------

function cptui_register_my_taxes_event_category() {
	$labels = [
		'name'              => esc_html__( 'Event Categories', 'codeweber' ),
		'singular_name'     => esc_html__( 'Event Category', 'codeweber' ),
		'menu_name'         => esc_html__( 'Categories', 'codeweber' ),
		'all_items'         => esc_html__( 'All Categories', 'codeweber' ),
		'edit_item'         => esc_html__( 'Edit Category', 'codeweber' ),
		'view_item'         => esc_html__( 'View Category', 'codeweber' ),
		'update_item'       => esc_html__( 'Update Category', 'codeweber' ),
		'add_new_item'      => esc_html__( 'Add New Category', 'codeweber' ),
		'new_item_name'     => esc_html__( 'New Category Name', 'codeweber' ),
		'search_items'      => esc_html__( 'Search Categories', 'codeweber' ),
		'not_found'         => esc_html__( 'No categories found', 'codeweber' ),
		'no_terms'          => esc_html__( 'No categories', 'codeweber' ),
	];

	$args = [
		'label'             => esc_html__( 'Event Categories', 'codeweber' ),
		'labels'            => $labels,
		'public'            => true,
		'hierarchical'      => true,
		'show_ui'           => true,
		'show_in_menu'      => true,
		'show_in_nav_menus' => true,
		'query_var'         => true,
		'rewrite'           => [ 'slug' => 'event-category', 'with_front' => true ],
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'show_tagcloud'     => false,
		'show_in_quick_edit'=> true,
		'sort'              => true,
		'show_in_graphql'   => false,
	];

	register_taxonomy( 'event_category', [ 'events' ], $args );
}
add_action( 'init', 'cptui_register_my_taxes_event_category' );

// ---------------------------------------------------------------------------
// Taxonomy: Event Format (flat)
// ---------------------------------------------------------------------------

function cptui_register_my_taxes_event_format() {
	$labels = [
		'name'          => esc_html__( 'Event Formats', 'codeweber' ),
		'singular_name' => esc_html__( 'Event Format', 'codeweber' ),
		'menu_name'     => esc_html__( 'Formats', 'codeweber' ),
		'all_items'     => esc_html__( 'All Formats', 'codeweber' ),
		'add_new_item'  => esc_html__( 'Add New Format', 'codeweber' ),
		'new_item_name' => esc_html__( 'New Format Name', 'codeweber' ),
		'search_items'  => esc_html__( 'Search Formats', 'codeweber' ),
		'not_found'     => esc_html__( 'No formats found', 'codeweber' ),
	];

	$args = [
		'label'             => esc_html__( 'Event Formats', 'codeweber' ),
		'labels'            => $labels,
		'public'            => true,
		'hierarchical'      => false,
		'show_ui'           => true,
		'show_in_menu'      => true,
		'show_in_nav_menus' => true,
		'query_var'         => true,
		'rewrite'           => [ 'slug' => 'event-format', 'with_front' => true ],
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'show_tagcloud'     => false,
		'show_in_quick_edit'=> true,
		'show_in_graphql'   => false,
	];

	register_taxonomy( 'event_format', [ 'events' ], $args );
}
add_action( 'init', 'cptui_register_my_taxes_event_format' );

// ---------------------------------------------------------------------------
// Meta Boxes
// ---------------------------------------------------------------------------

function codeweber_events_add_meta_boxes() {
	add_meta_box(
		'codeweber_event_dates',
		__( 'Event Dates', 'codeweber' ),
		'codeweber_events_render_dates_metabox',
		'events',
		'normal',
		'high'
	);
	add_meta_box(
		'codeweber_event_details',
		__( 'Event Details', 'codeweber' ),
		'codeweber_events_render_details_metabox',
		'events',
		'normal',
		'high'
	);
	add_meta_box(
		'codeweber_event_registration',
		__( 'Registration Settings', 'codeweber' ),
		'codeweber_events_render_registration_metabox',
		'events',
		'normal',
		'default'
	);
	add_meta_box(
		'codeweber_event_video',
		__( 'Event Video', 'codeweber' ),
		'codeweber_events_render_video_metabox',
		'events',
		'normal',
		'default'
	);
	add_meta_box(
		'codeweber_event_report',
		__( 'Event Report Text', 'codeweber' ),
		'codeweber_events_render_report_metabox',
		'events',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'codeweber_events_add_meta_boxes' );

// ---------------------------------------------------------------------------
// Metabox: Dates
// ---------------------------------------------------------------------------

function codeweber_events_render_dates_metabox( \WP_Post $post ): void {
	wp_nonce_field( 'codeweber_event_dates_save', 'codeweber_event_dates_nonce' );
	$date_start          = get_post_meta( $post->ID, '_event_date_start', true );
	$date_end            = get_post_meta( $post->ID, '_event_date_end', true );
	$registration_open   = get_post_meta( $post->ID, '_event_registration_open', true );
	$registration_close  = get_post_meta( $post->ID, '_event_registration_close', true );
	?>
	<table class="form-table">
		<tr>
			<th><label for="_event_date_start"><?php esc_html_e( 'Start Date & Time', 'codeweber' ); ?></label></th>
			<td><input type="datetime-local" id="_event_date_start" name="_event_date_start"
				value="<?php echo esc_attr( $date_start ); ?>" class="regular-text"></td>
		</tr>
		<tr>
			<th><label for="_event_date_end"><?php esc_html_e( 'End Date & Time', 'codeweber' ); ?></label></th>
			<td><input type="datetime-local" id="_event_date_end" name="_event_date_end"
				value="<?php echo esc_attr( $date_end ); ?>" class="regular-text"></td>
		</tr>
		<tr>
			<th><label for="_event_registration_open"><?php esc_html_e( 'Registration Opens', 'codeweber' ); ?></label></th>
			<td>
				<input type="datetime-local" id="_event_registration_open" name="_event_registration_open"
					value="<?php echo esc_attr( $registration_open ); ?>" class="regular-text">
				<p class="description"><?php esc_html_e( 'When registration becomes available. Leave empty to open immediately.', 'codeweber' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="_event_registration_close"><?php esc_html_e( 'Registration Closes', 'codeweber' ); ?></label></th>
			<td>
				<input type="datetime-local" id="_event_registration_close" name="_event_registration_close"
					value="<?php echo esc_attr( $registration_close ); ?>" class="regular-text">
				<p class="description"><?php esc_html_e( 'When registration stops. Leave empty to close when event starts.', 'codeweber' ); ?></p>
			</td>
		</tr>
	</table>
	<?php
}

// ---------------------------------------------------------------------------
// Metabox: Details
// ---------------------------------------------------------------------------

function codeweber_events_render_details_metabox( \WP_Post $post ): void {
	$location  = get_post_meta( $post->ID, '_event_location', true );
	$address   = get_post_meta( $post->ID, '_event_address', true );
	$organizer = get_post_meta( $post->ID, '_event_organizer', true );
	$price     = get_post_meta( $post->ID, '_event_price', true );
	?>
	<table class="form-table">
		<tr>
			<th><label for="_event_location"><?php esc_html_e( 'Location Name', 'codeweber' ); ?></label></th>
			<td><input type="text" id="_event_location" name="_event_location"
				value="<?php echo esc_attr( $location ); ?>" class="regular-text"
				placeholder="<?php esc_attr_e( 'e.g. Conference Hall A', 'codeweber' ); ?>"></td>
		</tr>
		<tr>
			<th><label for="_event_address"><?php esc_html_e( 'Address', 'codeweber' ); ?></label></th>
			<td><input type="text" id="_event_address" name="_event_address"
				value="<?php echo esc_attr( $address ); ?>" class="regular-text"
				placeholder="<?php esc_attr_e( 'Full address', 'codeweber' ); ?>"></td>
		</tr>
		<tr>
			<th><label for="_event_organizer"><?php esc_html_e( 'Organizer', 'codeweber' ); ?></label></th>
			<td><input type="text" id="_event_organizer" name="_event_organizer"
				value="<?php echo esc_attr( $organizer ); ?>" class="regular-text"></td>
		</tr>
		<tr>
			<th><label for="_event_price"><?php esc_html_e( 'Price', 'codeweber' ); ?></label></th>
			<td>
				<input type="text" id="_event_price" name="_event_price"
					value="<?php echo esc_attr( $price ); ?>" class="regular-text"
					placeholder="<?php esc_attr_e( 'e.g. 1500 ₽ or Free', 'codeweber' ); ?>">
				<p class="description"><?php esc_html_e( 'Enter amount or "Free". Shown on archive and single pages.', 'codeweber' ); ?></p>
			</td>
		</tr>
	</table>
	<?php
}

// ---------------------------------------------------------------------------
// Metabox: Registration
// ---------------------------------------------------------------------------

function codeweber_events_render_registration_metabox( \WP_Post $post ): void {
	$enabled          = get_post_meta( $post->ID, '_event_registration_enabled', true );
	$max_participants = get_post_meta( $post->ID, '_event_max_participants', true );
	$fake_registered  = get_post_meta( $post->ID, '_event_fake_registered', true );
	$reg_url          = get_post_meta( $post->ID, '_event_registration_url', true );
	?>
	<table class="form-table">
		<tr>
			<th><?php esc_html_e( 'Enable Registration Form', 'codeweber' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="_event_registration_enabled" value="1"
						<?php checked( $enabled, '1' ); ?>>
					<?php esc_html_e( 'Show built-in registration form on event page', 'codeweber' ); ?>
				</label>
			</td>
		</tr>
		<tr>
			<th><label for="_event_max_participants"><?php esc_html_e( 'Max Participants', 'codeweber' ); ?></label></th>
			<td>
				<input type="number" id="_event_max_participants" name="_event_max_participants"
					value="<?php echo esc_attr( $max_participants ); ?>" class="small-text" min="0">
				<p class="description"><?php esc_html_e( 'Set to 0 for unlimited seats.', 'codeweber' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="_event_fake_registered"><?php esc_html_e( 'Fake Registered Count', 'codeweber' ); ?></label></th>
			<td>
				<input type="number" id="_event_fake_registered" name="_event_fake_registered"
					value="<?php echo esc_attr( $fake_registered ); ?>" class="small-text" min="0">
				<p class="description"><?php esc_html_e( 'Added to the real registration count for display purposes (useful for demos and imported events).', 'codeweber' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="_event_registration_url"><?php esc_html_e( 'External Registration URL', 'codeweber' ); ?></label></th>
			<td>
				<input type="url" id="_event_registration_url" name="_event_registration_url"
					value="<?php echo esc_attr( $reg_url ); ?>" class="regular-text"
					placeholder="https://">
				<p class="description"><?php esc_html_e( 'If set, overrides built-in form with a link button.', 'codeweber' ); ?></p>
			</td>
		</tr>
	</table>
	<?php
}

// ---------------------------------------------------------------------------
// Metabox: Video
// ---------------------------------------------------------------------------

function codeweber_events_render_video_metabox( \WP_Post $post ): void {
	$video_type = get_post_meta( $post->ID, '_event_video_type', true ) ?: 'url';
	$video_url  = get_post_meta( $post->ID, '_event_video_url', true );
	$video_file = get_post_meta( $post->ID, '_event_video_file', true );
	$file_url   = $video_file ? wp_get_attachment_url( (int) $video_file ) : '';
	?>
	<table class="form-table">
		<tr>
			<th><?php esc_html_e( 'Video Type', 'codeweber' ); ?></th>
			<td>
				<label style="margin-right:16px;">
					<input type="radio" name="_event_video_type" value="url"
						<?php checked( $video_type, 'url' ); ?>>
					<?php esc_html_e( 'URL (YouTube / Vimeo / Rutube / VK)', 'codeweber' ); ?>
				</label>
				<label>
					<input type="radio" name="_event_video_type" value="upload"
						<?php checked( $video_type, 'upload' ); ?>>
					<?php esc_html_e( 'Upload video file', 'codeweber' ); ?>
				</label>
			</td>
		</tr>
		<tr id="event-video-url-row" style="<?php echo $video_type === 'upload' ? 'display:none;' : ''; ?>">
			<th><label for="_event_video_url"><?php esc_html_e( 'Video URL', 'codeweber' ); ?></label></th>
			<td>
				<input type="url" id="_event_video_url" name="_event_video_url"
					value="<?php echo esc_attr( $video_url ); ?>" class="regular-text"
					placeholder="https://www.youtube.com/watch?v=...">
				<p class="description"><?php esc_html_e( 'Paste a YouTube, Vimeo, Rutube or VK Video URL.', 'codeweber' ); ?></p>
			</td>
		</tr>
		<tr id="event-video-upload-row" style="<?php echo $video_type === 'url' ? 'display:none;' : ''; ?>">
			<th><?php esc_html_e( 'Video File', 'codeweber' ); ?></th>
			<td>
				<input type="hidden" id="_event_video_file" name="_event_video_file"
					value="<?php echo esc_attr( $video_file ); ?>">
				<div id="event-video-preview" style="margin-bottom:8px;">
					<?php if ( $file_url ) : ?>
						<video src="<?php echo esc_url( $file_url ); ?>" style="max-width:300px;max-height:120px;" controls></video>
					<?php endif; ?>
				</div>
				<button type="button" class="button" id="event-video-upload-btn">
					<?php esc_html_e( $video_file ? 'Replace Video' : 'Upload Video', 'codeweber' ); ?>
				</button>
				<?php if ( $video_file ) : ?>
					<button type="button" class="button button-link-delete" id="event-video-remove-btn" style="margin-left:8px;">
						<?php esc_html_e( 'Remove', 'codeweber' ); ?>
					</button>
				<?php endif; ?>
			</td>
		</tr>
	</table>
	<script>
	(function() {
		var radios = document.querySelectorAll('input[name="_event_video_type"]');
		var urlRow = document.getElementById('event-video-url-row');
		var uploadRow = document.getElementById('event-video-upload-row');
		radios.forEach(function(r) {
			r.addEventListener('change', function() {
				if (this.value === 'url') {
					urlRow.style.display = '';
					uploadRow.style.display = 'none';
				} else {
					urlRow.style.display = 'none';
					uploadRow.style.display = '';
				}
			});
		});

		var uploadBtn = document.getElementById('event-video-upload-btn');
		if (uploadBtn) {
			uploadBtn.addEventListener('click', function() {
				var frame = wp.media({
					title: '<?php echo esc_js( __( 'Select Video', 'codeweber' ) ); ?>',
					button: { text: '<?php echo esc_js( __( 'Use this video', 'codeweber' ) ); ?>' },
					library: { type: 'video' },
					multiple: false
				});
				frame.on('select', function() {
					var att = frame.state().get('selection').first().toJSON();
					document.getElementById('_event_video_file').value = att.id;
					var preview = document.getElementById('event-video-preview');
					preview.innerHTML = '<video src="' + att.url + '" style="max-width:300px;max-height:120px;" controls></video>';
					uploadBtn.textContent = '<?php echo esc_js( __( 'Replace Video', 'codeweber' ) ); ?>';
				});
				frame.open();
			});
		}

		var removeBtn = document.getElementById('event-video-remove-btn');
		if (removeBtn) {
			removeBtn.addEventListener('click', function() {
				document.getElementById('_event_video_file').value = '';
				document.getElementById('event-video-preview').innerHTML = '';
				uploadBtn.textContent = '<?php echo esc_js( __( 'Upload Video', 'codeweber' ) ); ?>';
				removeBtn.style.display = 'none';
			});
		}
	})();
	</script>
	<?php
}

// ---------------------------------------------------------------------------
// Metabox: Report Text
// ---------------------------------------------------------------------------

function codeweber_events_render_report_metabox( \WP_Post $post ): void {
	$report_text = get_post_meta( $post->ID, '_event_report_text', true );
	?>
	<p class="description" style="margin-bottom:8px;">
		<?php esc_html_e( 'Shown instead of the main content when the event has ended. Use for post-event reports, summaries, or photo descriptions.', 'codeweber' ); ?>
	</p>
	<?php
	wp_editor(
		$report_text,
		'event_report_text',
		[
			'textarea_name' => '_event_report_text',
			'textarea_rows' => 8,
			'media_buttons' => true,
			'teeny'         => false,
		]
	);
}

// ---------------------------------------------------------------------------
// Save Meta
// ---------------------------------------------------------------------------

function codeweber_events_save_meta( int $post_id, \WP_Post $post ): void {
	if ( ! isset( $_POST['codeweber_event_dates_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['codeweber_event_dates_nonce'] ) ), 'codeweber_event_dates_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$datetime_fields = [
		'_event_date_start',
		'_event_date_end',
		'_event_registration_open',
		'_event_registration_close',
	];
	foreach ( $datetime_fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, $field, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}

	$text_fields = [
		'_event_location',
		'_event_address',
		'_event_organizer',
		'_event_price',
		'_event_video_url',
		'_event_video_type',
	];
	foreach ( $text_fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, $field, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}

	$url_fields = [ '_event_registration_url' ];
	foreach ( $url_fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, $field, esc_url_raw( wp_unslash( $_POST[ $field ] ) ) );
		}
	}

	// Report text — allow post-level HTML (paragraphs, links, images)
	if ( isset( $_POST['_event_report_text'] ) ) {
		update_post_meta( $post_id, '_event_report_text', wp_kses_post( wp_unslash( $_POST['_event_report_text'] ) ) );
	}

	$int_fields = [ '_event_max_participants', '_event_fake_registered', '_event_video_file' ];
	foreach ( $int_fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, $field, absint( $_POST[ $field ] ) );
		}
	}

	update_post_meta( $post_id, '_event_registration_enabled',
		isset( $_POST['_event_registration_enabled'] ) ? '1' : '0' );
}
add_action( 'save_post_events', 'codeweber_events_save_meta', 10, 2 );

// ---------------------------------------------------------------------------
// Helper: registration status
// ---------------------------------------------------------------------------

/**
 * Определяет текущий статус регистрации на мероприятие.
 *
 * @param int $event_id
 * @return array{status: string, label: string, show_form: bool, seats_left: int|null}
 */
function codeweber_events_get_registration_status( int $event_id ): array {
	$now               = current_time( 'timestamp' );
	$date_end          = get_post_meta( $event_id, '_event_date_end', true );
	$reg_open          = get_post_meta( $event_id, '_event_registration_open', true );
	$reg_close         = get_post_meta( $event_id, '_event_registration_close', true );
	$enabled           = get_post_meta( $event_id, '_event_registration_enabled', true );
	$max               = (int) get_post_meta( $event_id, '_event_max_participants', true );
	$external_url      = get_post_meta( $event_id, '_event_registration_url', true );

	// Если форма регистрации отключена
	if ( ! $enabled || $enabled === '0' ) {
		return [ 'status' => 'disabled', 'label' => '', 'show_form' => false, 'seats_left' => null ];
	}

	// Внешняя ссылка — всегда показываем кнопку
	if ( $external_url ) {
		return [ 'status' => 'external', 'label' => __( 'Register', 'codeweber' ), 'show_form' => false, 'seats_left' => null ];
	}

	// Мероприятие завершено
	if ( $date_end && strtotime( $date_end ) < $now ) {
		return [ 'status' => 'event_ended', 'label' => __( 'Event completed', 'codeweber' ), 'show_form' => false, 'seats_left' => null ];
	}

	// Приём заявок ещё не открылся
	if ( $reg_open && strtotime( $reg_open ) > $now ) {
		return [
			'status'    => 'not_open_yet',
			'label'     => sprintf(
				__( 'Registration opens %s', 'codeweber' ),
				date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $reg_open ) )
			),
			'show_form' => false,
			'seats_left'=> null,
		];
	}

	// Приём заявок закрыт
	if ( $reg_close && strtotime( $reg_close ) < $now ) {
		return [ 'status' => 'registration_closed', 'label' => __( 'Registration closed', 'codeweber' ), 'show_form' => false, 'seats_left' => null ];
	}

	// Считаем занятые места
	$seats_left = null;
	if ( $max > 0 ) {
		$registered = codeweber_events_get_registration_count( $event_id );
		$seats_left = max( 0, $max - $registered );
		if ( $seats_left === 0 ) {
			return [ 'status' => 'no_seats', 'label' => __( 'No seats available', 'codeweber' ), 'show_form' => false, 'seats_left' => 0 ];
		}
	}

	$settings    = get_option( 'codeweber_events_settings', [] );
	$btn_label   = ! empty( $settings['btn_register_text'] ) ? $settings['btn_register_text'] : __( 'Register', 'codeweber' );

	return [ 'status' => 'open', 'label' => $btn_label, 'show_form' => true, 'seats_left' => $seats_left ];
}

/**
 * Возвращает количество подтверждённых + новых заявок на мероприятие.
 *
 * @param int $event_id
 * @return int
 */
function codeweber_events_get_registration_count( int $event_id ): int {
	$query = new WP_Query( [
		'post_type'      => 'event_registrations',
		'post_status'    => [ 'reg_pending', 'reg_confirmed', 'reg_awaiting' ],
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_query'     => [
			[
				'key'   => '_reg_event_id',
				'value' => $event_id,
				'type'  => 'NUMERIC',
			],
		],
	] );
	$real = (int) $query->found_posts;
	$fake = (int) get_post_meta( $event_id, '_event_fake_registered', true );
	return $real + $fake;
}

// ---------------------------------------------------------------------------
// Helper: video GLightbox markup
// ---------------------------------------------------------------------------

/**
 * Разбирает URL видео и возвращает данные для GLightbox.
 *
 * @param int $event_id
 * @return array{href: string, type: string, inline_html: string}|null
 */
function codeweber_events_get_video_glightbox( int $event_id ): ?array {
	$video_type = get_post_meta( $event_id, '_event_video_type', true ) ?: 'url';

	if ( $video_type === 'upload' ) {
		$file_id = (int) get_post_meta( $event_id, '_event_video_file', true );
		if ( ! $file_id ) {
			return null;
		}
		$url = wp_get_attachment_url( $file_id );
		if ( ! $url ) {
			return null;
		}
		return [ 'href' => $url, 'type' => 'video', 'inline_html' => '' ];
	}

	$url = get_post_meta( $event_id, '_event_video_url', true );
	if ( ! $url ) {
		return null;
	}

	// YouTube
	if ( preg_match( '/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([A-Za-z0-9_\-]{11})/', $url, $m ) ) {
		return [ 'href' => $url, 'type' => 'youtube', 'inline_html' => '' ];
	}

	// Vimeo
	if ( preg_match( '/vimeo\.com\/(\d+)/', $url, $m ) ) {
		return [ 'href' => $url, 'type' => 'vimeo', 'inline_html' => '' ];
	}

	// Rutube
	if ( preg_match( '/rutube\.ru\/video\/([a-zA-Z0-9]+)/', $url, $m ) ) {
		$embed_url  = 'https://rutube.ru/play/embed/' . $m[1];
		$inline_id  = 'event-video-rutube-' . $event_id;
		$inline_html = '<div id="' . esc_attr( $inline_id ) . '" style="display:none;">'
			. '<iframe src="' . esc_url( $embed_url ) . '" width="720" height="405" frameborder="0" allowfullscreen></iframe>'
			. '</div>';
		return [ 'href' => '#' . $inline_id, 'type' => 'inline', 'inline_html' => $inline_html ];
	}

	// VK Video
	if ( preg_match( '/vk\.com\/video(-?\d+_\d+)/', $url, $m ) ) {
		$embed_url  = 'https://vk.com/video_ext.php?oid=' . explode( '_', ltrim( $m[1], '-' ) )[0] . '&id=' . explode( '_', $m[1] )[1];
		$inline_id  = 'event-video-vk-' . $event_id;
		$inline_html = '<div id="' . esc_attr( $inline_id ) . '" style="display:none;">'
			. '<iframe src="' . esc_url( $embed_url ) . '" width="720" height="405" frameborder="0" allowfullscreen></iframe>'
			. '</div>';
		return [ 'href' => '#' . $inline_id, 'type' => 'inline', 'inline_html' => $inline_html ];
	}

	// Generic URL (html5 video or other)
	return [ 'href' => $url, 'type' => 'video', 'inline_html' => '' ];
}

// ---------------------------------------------------------------------------
// Admin: load wp.media for video metabox
// ---------------------------------------------------------------------------

add_action( 'admin_enqueue_scripts', function (): void {
	$screen = get_current_screen();
	if ( $screen && $screen->post_type === 'events' ) {
		wp_enqueue_media();
	}
} );

// ---------------------------------------------------------------------------
// Frontend assets (FullCalendar on archive, registration form on single)
// ---------------------------------------------------------------------------

function codeweber_enqueue_events_assets(): void {
	if ( ! is_post_type_archive( 'events' ) && ! is_tax( [ 'event_category', 'event_format' ] ) && ! is_singular( 'events' ) ) {
		return;
	}

	// FullCalendar v6 CDN — только на архиве / таксономии
	if ( is_post_type_archive( 'events' ) || is_tax( [ 'event_category', 'event_format' ] ) ) {
		wp_enqueue_style(
			'fullcalendar',
			'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css',
			[],
			'6.1.11'
		);
		wp_enqueue_script(
			'fullcalendar',
			'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js',
			[],
			'6.1.11',
			true
		);
		wp_enqueue_script(
			'fullcalendar-ru',
			'https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.11/locales/ru.global.min.js',
			[ 'fullcalendar' ],
			'6.1.11',
			true
		);
	}

	// Registration form JS — только на single
	if ( is_singular( 'events' ) ) {
		$src_path  = get_template_directory() . '/src/assets/js/event-registration-form.js';
		$dist_path = codeweber_get_dist_file_path( 'dist/assets/js/event-registration-form.js' );
		if ( $dist_path ) {
			$script_url = codeweber_get_dist_file_url( 'dist/assets/js/event-registration-form.js' );
		} else {
			$script_url = get_template_directory_uri() . '/src/assets/js/event-registration-form.js';
		}
		wp_enqueue_script(
			'codeweber-event-registration-form',
			$script_url,
			[ 'jquery' ],
			file_exists( $src_path ) ? (string) filemtime( $src_path ) : '1.0',
			true
		);
		wp_localize_script( 'codeweber-event-registration-form', 'codeweberEventReg', [
			'restUrl' => rest_url( 'codeweber/v1/events/register' ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
		] );
	}
}
add_action( 'wp_enqueue_scripts', 'codeweber_enqueue_events_assets', 20 );

// ---------------------------------------------------------------------------
// Supporting modules (loaded here so toggling CPT disables everything)
// ---------------------------------------------------------------------------

require_once get_template_directory() . '/functions/events/event-registrations.php';
require_once get_template_directory() . '/functions/events/event-registration-api.php';
require_once get_template_directory() . '/functions/admin/events-settings.php';
require_once get_template_directory() . '/functions/integrations/event-gallery-metabox.php';

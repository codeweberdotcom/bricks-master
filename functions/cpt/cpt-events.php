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
	add_meta_box(
		'codeweber_event_map',
		__( 'Event Map', 'codeweber' ),
		'codeweber_events_render_map_metabox',
		'events',
		'normal',
		'default'
	);
	add_meta_box(
		'codeweber_event_elements',
		__( 'Enable / Disable Elements', 'codeweber' ),
		'codeweber_events_render_elements_metabox',
		'events',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'codeweber_events_add_meta_boxes' );

// Disable Gutenberg for events — traditional metaboxes + wp_editor() require classic editor.
add_filter( 'use_block_editor_for_post_type', function ( bool $enabled, string $post_type ): bool {
	return $post_type === 'events' ? false : $enabled;
}, 10, 2 );

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
	$modal_value      = get_post_meta( $post->ID, '_event_modal_value', true );
	$max_participants = get_post_meta( $post->ID, '_event_max_participants', true );
	$fake_registered  = get_post_meta( $post->ID, '_event_fake_registered', true );
	$reg_url          = get_post_meta( $post->ID, '_event_registration_url', true );

	$email_required = get_post_meta( $post->ID, '_event_reg_email_required', true );
	$phone_required = get_post_meta( $post->ID, '_event_reg_phone_required', true );
	$show_comment   = get_post_meta( $post->ID, '_event_reg_show_comment', true );
	$show_seats     = get_post_meta( $post->ID, '_event_reg_show_seats', true );
	if ( $email_required === '' ) { $email_required = '1'; }
	if ( $show_comment === '' )   { $show_comment = '1'; }
	?>
	<table class="form-table">
		<tr>
			<th><?php esc_html_e( 'Registration Type', 'codeweber' ); ?></th>
			<td>
				<fieldset>
					<label style="display:block;margin-bottom:6px;">
						<input type="radio" name="_event_registration_enabled" value="0" <?php checked( $enabled === '' || $enabled === '0', true ); ?>>
						<?php esc_html_e( 'Disabled', 'codeweber' ); ?>
					</label>
					<label style="display:block;margin-bottom:6px;">
						<input type="radio" name="_event_registration_enabled" value="1" <?php checked( $enabled, '1' ); ?>>
						<?php esc_html_e( 'Built-in form on page', 'codeweber' ); ?>
					</label>
					<label style="display:block;margin-bottom:6px;">
						<input type="radio" name="_event_registration_enabled" value="modal" <?php checked( $enabled, 'modal' ); ?>>
						<?php esc_html_e( 'Button opens modal window', 'codeweber' ); ?>
					</label>
				</fieldset>
				<p id="event-modal-value-row" class="description" style="margin-top:8px;<?php echo $enabled === 'modal' ? '' : 'display:none;'; ?>">
					<?php esc_html_e( 'The built-in registration form will open in a modal window.', 'codeweber' ); ?>
				</p>
				<script>
				(function() {
					document.querySelectorAll('input[name="_event_registration_enabled"]').forEach(function(r) {
						r.addEventListener('change', function() {
							document.getElementById('event-modal-value-row').style.display =
								this.value === 'modal' ? '' : 'none';
						});
					});
				}());
				</script>
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
		<tr>
			<th><label for="_event_reg_form_title"><?php esc_html_e( 'Form Heading', 'codeweber' ); ?></label></th>
			<td>
				<?php
				$_reg_form_title         = get_post_meta( $post->ID, '_event_reg_form_title', true );
				$_reg_form_title_default = codeweber_events_settings_get( 'reg_form_title', 'Register' );
				$_reg_title_options = [
					'Register'              => __( 'Register', 'codeweber' ),
					'Submit an Application' => __( 'Submit an Application', 'codeweber' ),
					'Book Now'              => __( 'Book Now', 'codeweber' ),
					'Reserve a Spot'        => __( 'Reserve a Spot', 'codeweber' ),
					'Get Access'            => __( 'Get Access', 'codeweber' ),
					'Sign Up'               => __( 'Sign Up', 'codeweber' ),
					'Buy a Ticket'          => __( 'Buy a Ticket', 'codeweber' ),
					'Enroll'                => __( 'Enroll', 'codeweber' ),
					'Join the Event'        => __( 'Join the Event', 'codeweber' ),
				];
				?>
				<select id="_event_reg_form_title" name="_event_reg_form_title">
					<option value=""><?php esc_html_e( '— No heading —', 'codeweber' ); ?></option>
					<?php foreach ( $_reg_title_options as $val => $label ) : ?>
					<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $_reg_form_title ?: $_reg_form_title_default, $val ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="_event_reg_button_label"><?php esc_html_e( 'Button Label', 'codeweber' ); ?></label></th>
			<td>
				<?php
				$_reg_btn_label         = get_post_meta( $post->ID, '_event_reg_button_label', true );
				$_reg_btn_label_default = codeweber_events_settings_get( 'btn_register_text', 'Register' );
				$_reg_btn_options = [
					'Register'            => __( 'Register', 'codeweber' ),
					'Submit Application'  => __( 'Submit Application', 'codeweber' ),
					'Book Now'            => __( 'Book Now', 'codeweber' ),
					'Reserve a Spot'      => __( 'Reserve a Spot', 'codeweber' ),
					'Get Access'          => __( 'Get Access', 'codeweber' ),
					'Sign Up'             => __( 'Sign Up', 'codeweber' ),
					'Buy a Ticket'        => __( 'Buy a Ticket', 'codeweber' ),
					'Enroll Now'          => __( 'Enroll Now', 'codeweber' ),
					'Join Now'            => __( 'Join Now', 'codeweber' ),
					'Send Request'        => __( 'Send Request', 'codeweber' ),
				];
				?>
				<select id="_event_reg_button_label" name="_event_reg_button_label">
					<option value=""><?php esc_html_e( '— Default —', 'codeweber' ); ?></option>
					<?php foreach ( $_reg_btn_options as $val => $label ) : ?>
					<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $_reg_btn_label ?: $_reg_btn_label_default, $val ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e( 'If not set, the default label based on registration status is used.', 'codeweber' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Form Fields', 'codeweber' ); ?></th>
			<td>
				<p style="margin:0 0 8px;color:#666;font-style:italic;"><?php esc_html_e( 'Name — always required', 'codeweber' ); ?></p>
				<label style="display:block;margin-bottom:6px;">
					<input type="checkbox" name="_event_reg_email_required" value="1" <?php checked( $email_required, '1' ); ?>>
					<?php esc_html_e( 'Email — required', 'codeweber' ); ?>
				</label>
				<label style="display:block;margin-bottom:6px;">
					<input type="checkbox" name="_event_reg_phone_required" value="1" <?php checked( $phone_required, '1' ); ?>>
					<?php esc_html_e( 'Phone — required', 'codeweber' ); ?>
				</label>
				<p class="description" style="margin-bottom:12px;"><?php esc_html_e( 'At least one of Email or Phone must be required. If both are unchecked, Email is enforced automatically.', 'codeweber' ); ?></p>
				<label style="display:block;margin-bottom:6px;">
					<input type="checkbox" name="_event_reg_show_comment" value="1" <?php checked( $show_comment, '1' ); ?>>
					<?php esc_html_e( 'Show Comment field', 'codeweber' ); ?>
				</label>
				<label style="display:block;margin-bottom:4px;">
					<input type="checkbox" name="_event_reg_show_seats" value="1" <?php checked( $show_seats, '1' ); ?>>
					<?php esc_html_e( 'Show Seats field (number of seats to reserve)', 'codeweber' ); ?>
				</label>
				<p class="description"><?php esc_html_e( 'If hidden, 1 seat is recorded automatically.', 'codeweber' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Consent Documents', 'codeweber' ); ?></th>
			<td>
				<p class="description" style="margin-bottom:12px;"><?php esc_html_e( 'Add consent checkboxes to the form. Placeholders: {document_title_url}, {document_title}, {document_url}.', 'codeweber' ); ?></p>
				<?php
				$_reg_consents = get_post_meta( $post->ID, '_event_reg_consents', true );
				if ( ! is_array( $_reg_consents ) ) { $_reg_consents = []; }
				$_all_docs = function_exists( 'codeweber_forms_get_all_documents' ) ? codeweber_forms_get_all_documents() : [];
				?>
				<div id="event-reg-consents-list">
				<?php foreach ( $_reg_consents as $_ci => $_consent ) : ?>
					<div class="event-reg-consent-row" style="border:1px solid #ddd;padding:12px;margin-bottom:10px;background:#fff;border-radius:4px;">
						<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
							<strong><?php esc_html_e( 'Consent', 'codeweber' ); ?> #<?php echo ( $_ci + 1 ); ?></strong>
							<button type="button" class="button event-reg-remove-consent"><?php esc_html_e( 'Remove', 'codeweber' ); ?></button>
						</div>
						<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:10px;">
							<div>
								<label><strong><?php esc_html_e( 'Label Text', 'codeweber' ); ?>:</strong><br>
									<input type="text" name="event_reg_consents[<?php echo $_ci; ?>][label]"
										value="<?php echo esc_attr( $_consent['label'] ?? '' ); ?>"
										class="large-text"
										placeholder="<?php esc_attr_e( 'I agree to the {document_title_url}', 'codeweber' ); ?>">
								</label>
							</div>
							<div>
								<label><strong><?php esc_html_e( 'Document', 'codeweber' ); ?>:</strong><br>
									<select name="event_reg_consents[<?php echo $_ci; ?>][document_id]" style="width:100%;">
										<option value=""><?php esc_html_e( '— Select —', 'codeweber' ); ?></option>
										<?php foreach ( $_all_docs as $_doc ) : ?>
										<option value="<?php echo esc_attr( $_doc['id'] ); ?>" <?php selected( $_consent['document_id'] ?? '', $_doc['id'] ); ?>>
											<?php echo esc_html( $_doc['title'] ); ?> (<?php echo esc_html( $_doc['type'] ); ?>)
										</option>
										<?php endforeach; ?>
									</select>
								</label>
							</div>
						</div>
						<label>
							<input type="checkbox" name="event_reg_consents[<?php echo $_ci; ?>][required]" value="1" <?php checked( ! empty( $_consent['required'] ) ); ?>>
							<?php esc_html_e( 'Required', 'codeweber' ); ?>
						</label>
					</div>
				<?php endforeach; ?>
				</div>
				<button type="button" id="event-reg-add-consent" class="button button-secondary" style="margin-top:4px;">
					<?php esc_html_e( '+ Add Consent', 'codeweber' ); ?>
				</button>
				<script>
				(function() {
					var list = document.getElementById('event-reg-consents-list');
					var addBtn = document.getElementById('event-reg-add-consent');
					var allDocs = <?php echo wp_json_encode( array_values( $_all_docs ) ); ?>;
					var idx = list.querySelectorAll('.event-reg-consent-row').length;

					function makeRow(i) {
						var opts = '<option value=""><?php echo esc_js( __( '— Select —', 'codeweber' ) ); ?></option>';
						allDocs.forEach(function(d) {
							opts += '<option value="' + d.id + '">' + d.title + ' (' + d.type + ')</option>';
						});
						return '<div class="event-reg-consent-row" style="border:1px solid #ddd;padding:12px;margin-bottom:10px;background:#fff;border-radius:4px;">'
							+ '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">'
							+ '<strong><?php echo esc_js( __( 'Consent', 'codeweber' ) ); ?> #' + (i + 1) + '</strong>'
							+ '<button type="button" class="button event-reg-remove-consent"><?php echo esc_js( __( 'Remove', 'codeweber' ) ); ?></button>'
							+ '</div>'
							+ '<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:10px;">'
							+ '<div><label><strong><?php echo esc_js( __( 'Label Text', 'codeweber' ) ); ?>:</strong><br>'
							+ '<input type="text" name="event_reg_consents[' + i + '][label]" class="large-text" placeholder="<?php echo esc_js( __( 'I agree to the {document_title_url}', 'codeweber' ) ); ?>">'
							+ '</label></div>'
							+ '<div><label><strong><?php echo esc_js( __( 'Document', 'codeweber' ) ); ?>:</strong><br>'
							+ '<select name="event_reg_consents[' + i + '][document_id]" style="width:100%;">' + opts + '</select>'
							+ '</label></div>'
							+ '</div>'
							+ '<label><input type="checkbox" name="event_reg_consents[' + i + '][required]" value="1"> <?php echo esc_js( __( 'Required', 'codeweber' ) ); ?></label>'
							+ '</div>';
					}

					function attachRemove(btn) {
						btn.addEventListener('click', function() { btn.closest('.event-reg-consent-row').remove(); });
					}

					var consentLabelNonce = '<?php echo esc_js( wp_create_nonce( 'codeweber_forms_default_label' ) ); ?>';

					function bindDocSelect(row) {
						var sel = row.querySelector('select[name*="[document_id]"]');
						var inp = row.querySelector('input[name*="[label]"]');
						if (!sel || !inp) return;
						sel.addEventListener('change', function() {
							if (!sel.value) return;
							inp.disabled = true;
							var body = new URLSearchParams({ action: 'codeweber_forms_get_default_label', document_id: sel.value, nonce: consentLabelNonce });
							fetch(ajaxurl, { method: 'POST', body: body })
								.then(function(r) { return r.json(); })
								.then(function(data) { if (data.success && data.data && data.data.label) { inp.value = data.data.label; } })
								.finally(function() { inp.disabled = false; });
						});
					}

					list.querySelectorAll('.event-reg-consent-row').forEach(function(row) {
						attachRemove(row.querySelector('.event-reg-remove-consent'));
						bindDocSelect(row);
					});

					addBtn.addEventListener('click', function() {
						list.insertAdjacentHTML('beforeend', makeRow(idx++));
						var newRow = list.querySelector('.event-reg-consent-row:last-child');
						attachRemove(newRow.querySelector('.event-reg-remove-consent'));
						bindDocSelect(newRow);
					});
				}());
				</script>
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
// Metabox: Map
// ---------------------------------------------------------------------------

function codeweber_events_render_map_metabox( WP_Post $post ): void {
	global $opt_name;
	$yandex_api_key = class_exists( 'Redux' ) ? Redux::get_option( $opt_name, 'yandexapi' ) : '';
	$show_map       = get_post_meta( $post->ID, '_event_show_map', true );
	$latitude       = get_post_meta( $post->ID, '_event_latitude', true );
	$longitude      = get_post_meta( $post->ID, '_event_longitude', true );
	$zoom           = get_post_meta( $post->ID, '_event_zoom', true );
	$address        = get_post_meta( $post->ID, '_event_yandex_address', true );

	if ( empty( $latitude ) ) $latitude = '55.7558';
	if ( empty( $longitude ) ) $longitude = '37.6173';
	if ( empty( $zoom ) ) $zoom = '15';
	?>
	<div style="margin-bottom: 15px;">
		<?php if ( $show_map !== '1' ) : ?>
		<p style="font-size: 12px; color: #856404; background: #fff3cd; padding: 8px 10px; border-radius: 4px; margin-bottom: 10px;">
			<?php esc_html_e( 'Map is hidden on frontend. Enable &ldquo;Show map on frontend&rdquo; to display it to visitors.', 'codeweber' ); ?>
		</p>
		<?php endif; ?>
		<p style="font-size: 13px; color: #666; margin-bottom: 10px;">
			<?php esc_html_e( 'Click on the map to set the location or use the search field.', 'codeweber' ); ?>
		</p>
		<label style="display: block; margin-bottom: 5px; font-weight: bold;">
			<?php esc_html_e( 'Map', 'codeweber' ); ?>
		</label>
		<?php if ( ! empty( $yandex_api_key ) ) : ?>
		<div style="position:relative;margin-bottom:12px;">
			<input type="text" id="event-map-search" placeholder="<?php esc_attr_e( 'Search address...', 'codeweber' ); ?>"
				style="width:100%;padding:8px;border:1px solid #8c8f94;border-radius:4px;box-sizing:border-box;">
		</div>
		<?php endif; ?>
		<div id="event-yandex-map" style="width: 100%; height: 400px; margin-bottom: 15px;"></div>

		<?php if ( ! empty( $yandex_api_key ) ) : ?>
		<script src="https://api-maps.yandex.ru/v3/?apikey=<?php echo esc_attr( $yandex_api_key ); ?>&lang=ru_RU"></script>
		<script>
		(function() {
			var apiKey = '<?php echo esc_js( $yandex_api_key ); ?>';
			var geocodeUrl = 'https://geocode-maps.yandex.ru/1.x/?apikey=' + encodeURIComponent(apiKey) + '&format=json&lang=ru_RU';
			ymaps3.ready.then(function() {
				var YMap = ymaps3.YMap, YMapDefaultSchemeLayer = ymaps3.YMapDefaultSchemeLayer,
				    YMapDefaultFeaturesLayer = ymaps3.YMapDefaultFeaturesLayer,
				    YMapMarker = ymaps3.YMapMarker, YMapListener = ymaps3.YMapListener;

				var latField     = document.getElementById('_event_latitude');
				var lngField     = document.getElementById('_event_longitude');
				var zoomField    = document.getElementById('_event_zoom');
				var addressField = document.getElementById('_event_yandex_address');
				var searchInput  = document.getElementById('event-map-search');

				var lat  = parseFloat(latField  && latField.value  ? latField.value  : '55.7558') || 55.7558;
				var lng  = parseFloat(lngField  && lngField.value  ? lngField.value  : '37.6173') || 37.6173;
				var zoom = parseInt(zoomField && zoomField.value ? zoomField.value : '15') || 15;

				var map = new YMap(document.getElementById('event-yandex-map'), {
					location: { center: [lng, lat], zoom: zoom }
				});
				map.addChild(new YMapDefaultSchemeLayer());
				map.addChild(new YMapDefaultFeaturesLayer());

				var el = document.createElement('div');
				el.style.cssText = 'cursor:grab;width:28px;height:28px;transform:translate(-50%,-100%)';
				el.innerHTML = '<svg viewBox="0 0 24 24" fill="#d63638" xmlns="http://www.w3.org/2000/svg"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>';

				var marker = new YMapMarker({
					coordinates: [lng, lat],
					draggable: true,
					onDragEnd: function(coords) { syncFields(coords[1], coords[0]); }
				}, el);
				map.addChild(marker);

				map.addChild(new YMapListener({
					onClick: function(obj, event) {
						var coords = event && event.coordinates ? event.coordinates : null;
						if (!coords) return;
						marker.update({ coordinates: coords });
						syncFields(coords[1], coords[0]);
					}
				}));

				map.addChild(new YMapListener({
					onActionEnd: function() {
						if (zoomField) { zoomField.value = Math.round(map.zoom); }
					}
				}));

				function syncFields(latVal, lngVal) {
					if (latField)  { latField.value  = latVal.toFixed(6); }
					if (lngField)  { lngField.value  = lngVal.toFixed(6); }
					if (zoomField) { zoomField.value = Math.round(map.zoom); }
					if (addressField) {
						fetch('https://geocode-maps.yandex.ru/1.x/?apikey=' + encodeURIComponent(apiKey) + '&format=json&geocode=' + lngVal + ',' + latVal + '&results=1&lang=ru_RU')
							.then(function(r) { return r.json(); })
							.then(function(d) {
								var fm = d.response && d.response.GeoObjectCollection && d.response.GeoObjectCollection.featureMember;
								if (fm && fm.length) { addressField.value = fm[0].GeoObject.metaDataProperty.GeocoderMetaData.text; }
							});
					}
				}

				function geocodeAndMove(query) {
					if (!query) return;
					fetch(geocodeUrl + '&geocode=' + encodeURIComponent(query) + '&results=1')
						.then(function(r) { return r.json(); })
						.then(function(d) {
							var fm = d.response && d.response.GeoObjectCollection && d.response.GeoObjectCollection.featureMember;
							if (!fm || !fm.length) return;
							var pos = fm[0].GeoObject.Point.pos.split(' ');
							var fLng = parseFloat(pos[0]), fLat = parseFloat(pos[1]);
							if (isNaN(fLat) || isNaN(fLng)) return;
							marker.update({ coordinates: [fLng, fLat] });
							map.update({ location: { center: [fLng, fLat], zoom: 15 } });
							syncFields(fLat, fLng);
						}).catch(function() {});
				}

				function initSuggest(input) {
					var wrap = input.parentNode;
					var drop = document.createElement('div');
					drop.style.cssText = 'display:none;position:absolute;z-index:99999;left:0;right:0;top:100%;background:#fff;border:1px solid #c3c4c7;border-top:none;border-radius:0 0 4px 4px;box-shadow:0 4px 8px rgba(0,0,0,.12);max-height:220px;overflow-y:auto;font-size:13px;';
					wrap.appendChild(drop);
					var timer, active = -1;
					function hide() { drop.style.display = 'none'; active = -1; }
					function hl(i) { active = i; Array.from(drop.children).forEach(function(c,j){c.style.background=j===i?'#f0f7ff':'';}); }
					function pick(t, s) { input.value = t + (s ? ', '+s : ''); hide(); geocodeAndMove(input.value); }
					function esc(s) { return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
					input.addEventListener('input', function() {
						clearTimeout(timer);
						var q = input.value.trim();
						if (q.length < 2) { hide(); return; }
						timer = setTimeout(function() {
							ymaps3.suggest({ text: q, lang: 'ru_RU', results: 5 })
								.then(function(items) {
									drop.innerHTML = '';
									items = (items || []).filter(function(r) { return r.title && r.title.text; });
									if (!items.length) { hide(); return; }
									items.forEach(function(r, i) {
										var t = r.title.text, s = r.subtitle && r.subtitle.text ? r.subtitle.text : '';
										var div = document.createElement('div');
										div.style.cssText = 'padding:7px 12px;cursor:pointer;border-bottom:1px solid #f0f0f1;line-height:1.3;';
										div.innerHTML = '<span style="font-weight:600">'+esc(t)+'</span>'+(s?'<br><span style="color:#777;font-size:12px">'+esc(s)+'</span>':'');
										div.addEventListener('mousedown', function(e) { e.preventDefault(); pick(t, s); });
										div.addEventListener('mouseover', function() { hl(i); });
										drop.appendChild(div);
									});
									drop.style.display = 'block';
								}).catch(function() {});
						}, 250);
					});
					input.addEventListener('keydown', function(e) {
						if (e.key === 'ArrowDown') { e.preventDefault(); hl(Math.min(active+1, drop.children.length-1)); }
						else if (e.key === 'ArrowUp') { e.preventDefault(); hl(Math.max(active-1, 0)); }
						else if (e.key === 'Enter') {
							e.preventDefault();
							if (active >= 0 && drop.children[active]) drop.children[active].dispatchEvent(new MouseEvent('mousedown',{bubbles:true}));
							else geocodeAndMove(input.value.trim());
							hide();
						} else if (e.key === 'Escape') { hide(); }
					});
					input.addEventListener('blur', function() { setTimeout(hide, 200); });
				}

				if (searchInput) initSuggest(searchInput);
			});
		})();
		</script>
		<?php else : ?>
		<p style="color: #d32f2f; background: #ffebee; padding: 10px; border-radius: 4px;">
			<?php esc_html_e( 'Yandex API key is not set. Please configure it in Theme Options > API > Yandex API Key.', 'codeweber' ); ?>
		</p>
		<?php endif; ?>
	</div>

	<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
		<div>
			<label for="_event_latitude" style="display: block; margin-bottom: 5px; font-weight: bold;">
				<?php esc_html_e( 'Latitude', 'codeweber' ); ?>
			</label>
			<input type="number" step="any" id="_event_latitude" name="_event_latitude"
				value="<?php echo esc_attr( $latitude ); ?>"
				style="width: 100%; padding: 8px;" placeholder="55.7558">
		</div>
		<div>
			<label for="_event_longitude" style="display: block; margin-bottom: 5px; font-weight: bold;">
				<?php esc_html_e( 'Longitude', 'codeweber' ); ?>
			</label>
			<input type="number" step="any" id="_event_longitude" name="_event_longitude"
				value="<?php echo esc_attr( $longitude ); ?>"
				style="width: 100%; padding: 8px;" placeholder="37.6173">
		</div>
		<div>
			<label for="_event_zoom" style="display: block; margin-bottom: 5px; font-weight: bold;">
				<?php esc_html_e( 'Zoom', 'codeweber' ); ?>
			</label>
			<input type="number" step="1" min="1" max="19" id="_event_zoom" name="_event_zoom"
				value="<?php echo esc_attr( $zoom ); ?>"
				style="width: 100%; padding: 8px;" placeholder="15">
		</div>
		<div>
			<label for="_event_yandex_address" style="display: block; margin-bottom: 5px; font-weight: bold;">
				<?php esc_html_e( 'Address (from map)', 'codeweber' ); ?>
			</label>
			<input type="text" id="_event_yandex_address" name="_event_yandex_address"
				value="<?php echo esc_attr( $address ); ?>"
				style="width: 100%; padding: 8px;" readonly>
			<p style="font-size: 12px; color: #666; margin-top: 5px;">
				<?php esc_html_e( 'Address is automatically detected when you click on the map', 'codeweber' ); ?>
			</p>
		</div>
	</div>
	<?php
}

// ---------------------------------------------------------------------------
// Metabox: Enable / Disable Elements
// ---------------------------------------------------------------------------

function codeweber_events_render_elements_metabox( \WP_Post $post ): void {
	$show_map              = get_post_meta( $post->ID, '_event_show_map', true );
	$sidebar_hide_author   = get_post_meta( $post->ID, '_event_sidebar_hide_author', true );
	$sidebar_disable_image = get_post_meta( $post->ID, '_event_sidebar_disable_image', true );
	?>
	<div style="display:flex;flex-direction:column;gap:12px;">
		<div style="padding:12px;background:#f5f5f5;border-radius:4px;">
			<label for="event_show_map" style="display:flex;align-items:center;cursor:pointer;">
				<input type="checkbox" id="event_show_map" name="event_show_map" value="1"
					<?php checked( $show_map, '1' ); ?> style="margin-right:10px;">
				<strong><?php esc_html_e( 'Show map on frontend', 'codeweber' ); ?></strong>
			</label>
			<p style="font-size:12px;color:#666;margin:5px 0 0 24px;">
				<?php esc_html_e( 'If disabled, the map is not displayed on the frontend.', 'codeweber' ); ?>
			</p>
		</div>
		<div style="padding:12px;background:#f5f5f5;border-radius:4px;">
			<label for="event_sidebar_hide_author" style="display:flex;align-items:center;cursor:pointer;">
				<input type="checkbox" id="event_sidebar_hide_author" name="event_sidebar_hide_author" value="1"
					<?php checked( $sidebar_hide_author, '1' ); ?> style="margin-right:10px;">
				<strong><?php esc_html_e( 'Hide author in sidebar', 'codeweber' ); ?></strong>
			</label>
			<p style="font-size:12px;color:#666;margin:5px 0 0 24px;">
				<?php esc_html_e( 'When enabled, the author block is hidden in the event sidebar on the frontend.', 'codeweber' ); ?>
			</p>
		</div>
		<div style="padding:12px;background:#f5f5f5;border-radius:4px;">
			<label for="event_sidebar_disable_image" style="display:flex;align-items:center;cursor:pointer;">
				<input type="checkbox" id="event_sidebar_disable_image" name="event_sidebar_disable_image" value="1"
					<?php checked( $sidebar_disable_image, '1' ); ?> style="margin-right:10px;">
				<strong><?php esc_html_e( 'Disable image in sidebar', 'codeweber' ); ?></strong>
			</label>
			<p style="font-size:12px;color:#666;margin:5px 0 0 24px;">
				<?php esc_html_e( 'When enabled, the event thumbnail in the sidebar is hidden on the frontend.', 'codeweber' ); ?>
			</p>
		</div>
		<div style="padding:12px;background:#f5f5f5;border-radius:4px;">
			<label for="event_hide_seats_counter" style="display:flex;align-items:center;cursor:pointer;">
				<input type="checkbox" id="event_hide_seats_counter" name="event_hide_seats_counter" value="1"
					<?php checked( get_post_meta( $post->ID, '_event_hide_seats_counter', true ), '1' ); ?> style="margin-right:10px;">
				<strong><?php esc_html_e( 'Hide seats counter', 'codeweber' ); ?></strong>
			</label>
			<p style="font-size:12px;color:#666;margin:5px 0 0 24px;">
				<?php esc_html_e( 'When enabled, the seats counter and progress bar are hidden in the sidebar.', 'codeweber' ); ?>
			</p>
		</div>
		<div style="padding:12px;background:#f5f5f5;border-radius:4px;">
			<label for="event_hide_add_to_calendar" style="display:flex;align-items:center;cursor:pointer;">
				<input type="checkbox" id="event_hide_add_to_calendar" name="event_hide_add_to_calendar" value="1"
					<?php checked( get_post_meta( $post->ID, '_event_hide_add_to_calendar', true ), '1' ); ?> style="margin-right:10px;">
				<strong><?php esc_html_e( 'Hide "Add to Calendar" button', 'codeweber' ); ?></strong>
			</label>
			<p style="font-size:12px;color:#666;margin:5px 0 0 24px;">
				<?php esc_html_e( 'When enabled, the "Add to Calendar" dropdown button is hidden in the event sidebar.', 'codeweber' ); ?>
			</p>
		</div>
	</div>
	<?php
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
		'_event_latitude',
		'_event_longitude',
		'_event_yandex_address',
		'_event_reg_form_title',
		'_event_reg_button_label',
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

	$int_fields = [ '_event_max_participants', '_event_fake_registered', '_event_video_file', '_event_zoom' ];
	foreach ( $int_fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, $field, absint( $_POST[ $field ] ) );
		}
	}

	$_reg_type = sanitize_key( $_POST['_event_registration_enabled'] ?? '0' );
	if ( ! in_array( $_reg_type, [ '0', '1', 'modal' ], true ) ) { $_reg_type = '0'; }
	update_post_meta( $post_id, '_event_registration_enabled', $_reg_type );
	update_post_meta( $post_id, '_event_modal_value', sanitize_text_field( wp_unslash( $_POST['_event_modal_value'] ?? '' ) ) );

	// Form field controls
	$email_req = isset( $_POST['_event_reg_email_required'] ) ? '1' : '0';
	$phone_req = isset( $_POST['_event_reg_phone_required'] ) ? '1' : '0';
	if ( $email_req === '0' && $phone_req === '0' ) { $email_req = '1'; }
	update_post_meta( $post_id, '_event_reg_email_required', $email_req );
	update_post_meta( $post_id, '_event_reg_phone_required', $phone_req );
	update_post_meta( $post_id, '_event_reg_show_comment',   isset( $_POST['_event_reg_show_comment'] ) ? '1' : '0' );
	update_post_meta( $post_id, '_event_reg_show_seats',     isset( $_POST['_event_reg_show_seats'] )   ? '1' : '0' );

	// Consent documents
	$_consents_to_save = [];
	if ( isset( $_POST['event_reg_consents'] ) && is_array( $_POST['event_reg_consents'] ) ) {
		foreach ( $_POST['event_reg_consents'] as $_cd ) {
			if ( empty( $_cd['label'] ) || empty( $_cd['document_id'] ) ) {
				continue;
			}
			$_consents_to_save[] = [
				'label'       => wp_kses_post( wp_unslash( $_cd['label'] ) ),
				'document_id' => absint( $_cd['document_id'] ),
				'required'    => ! empty( $_cd['required'] ),
			];
		}
	}
	update_post_meta( $post_id, '_event_reg_consents', $_consents_to_save );

	update_post_meta( $post_id, '_event_show_map',
		isset( $_POST['event_show_map'] ) ? '1' : '' );
	update_post_meta( $post_id, '_event_sidebar_hide_author',
		isset( $_POST['event_sidebar_hide_author'] ) ? '1' : '' );
	update_post_meta( $post_id, '_event_sidebar_disable_image',
		isset( $_POST['event_sidebar_disable_image'] ) ? '1' : '' );
	update_post_meta( $post_id, '_event_hide_seats_counter',
		isset( $_POST['event_hide_seats_counter'] ) ? '1' : '' );
	update_post_meta( $post_id, '_event_hide_add_to_calendar',
		isset( $_POST['event_hide_add_to_calendar'] ) ? '1' : '' );
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

	// Кнопка открывает модальное окно
	if ( $enabled === 'modal' ) {
		$modal_value = get_post_meta( $event_id, '_event_modal_value', true );
		return [ 'status' => 'modal', 'label' => '', 'show_form' => false, 'seats_left' => null, 'modal_value' => $modal_value ];
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
		return [ 'href' => $url, 'glightbox' => 'type: video', 'inline_html' => '' ];
	}

	$url = get_post_meta( $event_id, '_event_video_url', true );
	if ( ! $url ) {
		return null;
	}

	// YouTube — auto-detected by GLightbox from URL
	if ( preg_match( '/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([A-Za-z0-9_\-]{11})/', $url ) ) {
		return [ 'href' => $url, 'glightbox' => '', 'inline_html' => '' ];
	}

	// Vimeo — auto-detected by GLightbox from URL
	if ( preg_match( '/vimeo\.com\/(\d+)/', $url ) ) {
		return [ 'href' => $url, 'glightbox' => '', 'inline_html' => '' ];
	}

	// Rutube — hidden iframe div pattern (same as blocks plugin button)
	$rutube_id = '';
	if ( preg_match( '#rutube\.ru/play/embed/([a-zA-Z0-9]+)#', $url, $m ) ) {
		$rutube_id = $m[1];
	} elseif ( preg_match( '#rutube\.ru/video/([a-zA-Z0-9]+)#', $url, $m ) ) {
		$rutube_id = $m[1];
	}
	if ( $rutube_id ) {
		$embed_url   = 'https://rutube.ru/play/embed/' . $rutube_id . '?autoplay=1';
		$inline_id   = 'event-video-rutube-' . $event_id;
		$inline_html = '<div id="' . esc_attr( $inline_id ) . '" style="display:none;">'
			. '<iframe src="' . esc_url( $embed_url ) . '" allow="clipboard-write; autoplay;" allowfullscreen'
			. ' style="border:none;width:720px;height:405px;"></iframe>'
			. '</div>';
		return [ 'href' => '#' . $inline_id, 'glightbox' => 'width: auto;', 'inline_html' => $inline_html ];
	}

	// VK Video — hidden iframe div pattern (same as blocks plugin button)
	$vk_embed_url = '';
	if ( preg_match( '#vkvideo\.ru/video_ext\.php#', $url ) ) {
		// Already an embed URL — use as-is
		$vk_embed_url = $url;
	} elseif ( preg_match( '#vk\.com/video(-?\d+)_(\d+)#', $url, $m ) ) {
		$vk_embed_url = 'https://vkvideo.ru/video_ext.php?oid=' . $m[1] . '&id=' . $m[2];
	}
	if ( $vk_embed_url ) {
		$inline_id   = 'event-video-vk-' . $event_id;
		$inline_html = '<div id="' . esc_attr( $inline_id ) . '" style="display:none;">'
			. '<iframe src="' . esc_url( $vk_embed_url ) . '"'
			. ' allow="autoplay; encrypted-media; fullscreen; picture-in-picture; screen-wake-lock;" allowfullscreen'
			. ' style="border:none;width:720px;height:405px;"></iframe>'
			. '</div>';
		return [ 'href' => '#' . $inline_id, 'glightbox' => 'width: auto;', 'inline_html' => $inline_html ];
	}

	// Generic URL (html5 video or other)
	return [ 'href' => $url, 'glightbox' => 'type: video', 'inline_html' => '' ];
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

	// FullCalendar v6 CDN — только на архиве / таксономии.
	// CSS не нужен — глобальный бандл v6 инжектирует стили через JS.
	// Локаль встроена в бандл, используется через опцию locale: 'ru' в JS.
	if ( is_post_type_archive( 'events' ) || is_tax( [ 'event_category', 'event_format' ] ) ) {
		wp_enqueue_script(
			'fullcalendar',
			'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js',
			[],
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
require_once get_template_directory() . '/functions/events/event-ics.php';
require_once get_template_directory() . '/functions/admin/events-settings.php';
require_once get_template_directory() . '/functions/integrations/event-gallery-metabox.php';

<?php
/**
 * FAQ Settings Page
 *
 * Страница настроек «FAQ → Настройки».
 * Опция: codeweber_faq_settings
 * Согласия синхронизируются в builtin_form_consents['faq']
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Menu
// ---------------------------------------------------------------------------

function codeweber_faq_settings_register_page(): void {
	add_submenu_page(
		'edit.php?post_type=faq',
		__( 'FAQ Settings', 'codeweber' ),
		__( 'Settings', 'codeweber' ),
		'manage_options',
		'codeweber-faq-settings',
		'codeweber_faq_settings_render_page'
	);
}
add_action( 'admin_menu', 'codeweber_faq_settings_register_page' );

// ---------------------------------------------------------------------------
// Settings registration
// ---------------------------------------------------------------------------

function codeweber_faq_settings_register(): void {
	register_setting(
		'codeweber_faq_settings_group',
		'codeweber_faq_settings',
		[
			'sanitize_callback' => 'codeweber_faq_settings_sanitize',
			'default'           => [],
		]
	);

	// Section: Form fields visibility
	add_settings_section(
		'codeweber_faq_form_fields',
		__( 'Ask a Question Form — Fields', 'codeweber' ),
		static function () {
			echo '<p class="description">'
				. esc_html__( 'Toggle which fields are shown in the FAQ question form.', 'codeweber' )
				. '</p>';
		},
		'codeweber-faq-settings'
	);

	add_settings_field(
		'form_mode',
		__( 'Form display mode', 'codeweber' ),
		'codeweber_faq_field_form_mode',
		'codeweber-faq-settings',
		'codeweber_faq_form_fields'
	);

	add_settings_field(
		'show_name',
		__( 'Name field', 'codeweber' ),
		'codeweber_faq_field_show_name',
		'codeweber-faq-settings',
		'codeweber_faq_form_fields'
	);

	add_settings_field(
		'show_email',
		__( 'Email field', 'codeweber' ),
		'codeweber_faq_field_show_email',
		'codeweber-faq-settings',
		'codeweber_faq_form_fields'
	);

	add_settings_field(
		'show_phone',
		__( 'Phone field', 'codeweber' ),
		'codeweber_faq_field_show_phone',
		'codeweber-faq-settings',
		'codeweber_faq_form_fields'
	);

	// Section: Consents
	add_settings_section(
		'codeweber_faq_form_consents',
		__( 'Ask a Question Form — Consents', 'codeweber' ),
		static function () {
			echo '<p class="description">'
				. esc_html__( 'Consent checkboxes shown in the FAQ question form.', 'codeweber' )
				. '</p>';
		},
		'codeweber-faq-settings'
	);

	add_settings_field(
		'form_consents',
		__( 'Documents / Consents', 'codeweber' ),
		'codeweber_faq_field_form_consents',
		'codeweber-faq-settings',
		'codeweber_faq_form_consents'
	);

	// Section: Notifications
	add_settings_section(
		'codeweber_faq_notifications',
		__( 'Notifications', 'codeweber' ),
		null,
		'codeweber-faq-settings'
	);

	add_settings_field(
		'notify_email',
		__( 'Notification email', 'codeweber' ),
		'codeweber_faq_field_notify_email',
		'codeweber-faq-settings',
		'codeweber_faq_notifications'
	);

	add_settings_field(
		'success_message',
		__( 'Success message', 'codeweber' ),
		'codeweber_faq_field_success_message',
		'codeweber-faq-settings',
		'codeweber_faq_notifications'
	);
}
add_action( 'admin_init', 'codeweber_faq_settings_register' );

// ---------------------------------------------------------------------------
// Helper
// ---------------------------------------------------------------------------

function codeweber_faq_settings_get( string $key, $default = '' ) {
	$options = get_option( 'codeweber_faq_settings', [] );
	return $options[ $key ] ?? $default;
}

// ---------------------------------------------------------------------------
// Field renderers
// ---------------------------------------------------------------------------

function codeweber_faq_field_form_mode(): void {
	$val = codeweber_faq_settings_get( 'form_mode', 'inline' );
	echo '<select name="codeweber_faq_settings[form_mode]">';
	echo '<option value="disabled" ' . selected( $val, 'disabled', false ) . '>' . esc_html__( 'Disabled', 'codeweber' ) . '</option>';
	echo '<option value="inline" ' . selected( $val, 'inline', false ) . '>' . esc_html__( 'Inline (in sidebar)', 'codeweber' ) . '</option>';
	echo '<option value="modal" ' . selected( $val, 'modal', false ) . '>' . esc_html__( 'Modal window (button in sidebar)', 'codeweber' ) . '</option>';
	echo '</select>';
	echo '<p class="description">' . esc_html__( 'Disabled hides the form completely. Inline displays the form in the sidebar. Modal shows a button that opens the form in a popup.', 'codeweber' ) . '</p>';
}

function codeweber_faq_field_show_name(): void {
	$val = codeweber_faq_settings_get( 'show_name', '1' );
	echo '<label><input type="checkbox" name="codeweber_faq_settings[show_name]" value="1" ' . checked( $val, '1', false ) . '> ';
	esc_html_e( 'Show name field', 'codeweber' );
	echo '</label>';
}

function codeweber_faq_field_show_email(): void {
	$val = codeweber_faq_settings_get( 'show_email', '1' );
	echo '<label><input type="checkbox" name="codeweber_faq_settings[show_email]" value="1" ' . checked( $val, '1', false ) . '> ';
	esc_html_e( 'Show email field', 'codeweber' );
	echo '</label>';
}

function codeweber_faq_field_show_phone(): void {
	$val = codeweber_faq_settings_get( 'show_phone', '1' );
	echo '<label><input type="checkbox" name="codeweber_faq_settings[show_phone]" value="1" ' . checked( $val, '1', false ) . '> ';
	esc_html_e( 'Show phone field', 'codeweber' );
	echo '</label>';
}

function codeweber_faq_field_notify_email(): void {
	$val = codeweber_faq_settings_get( 'notify_email', '' );
	echo '<input type="email" name="codeweber_faq_settings[notify_email]" value="' . esc_attr( $val ) . '" class="regular-text" placeholder="' . esc_attr( get_option( 'admin_email' ) ) . '">';
	echo '<p class="description">' . esc_html__( 'Leave empty to use admin email.', 'codeweber' ) . '</p>';
}

function codeweber_faq_field_success_message(): void {
	$val = codeweber_faq_settings_get( 'success_message', __( 'Thank you for your question! We will answer it shortly.', 'codeweber' ) );
	echo '<input type="text" name="codeweber_faq_settings[success_message]" value="' . esc_attr( $val ) . '" class="large-text">';
}

function codeweber_faq_field_form_consents(): void {
	$consents = codeweber_faq_settings_get( 'form_consents', [] );
	if ( ! is_array( $consents ) ) {
		$consents = [];
	}
	$all_docs = function_exists( 'codeweber_forms_get_all_documents' ) ? codeweber_forms_get_all_documents() : [];
	?>
	<div id="faq-form-consents-list">
		<?php foreach ( $consents as $ci => $consent ) : ?>
		<div class="faq-form-consent-row" style="border:1px solid #ddd;padding:12px;margin-bottom:10px;background:#fff;border-radius:4px;">
			<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
				<strong><?php esc_html_e( 'Consent', 'codeweber' ); ?> #<?php echo absint( $ci + 1 ); ?></strong>
				<button type="button" class="button faq-form-remove-consent"><?php esc_html_e( 'Remove', 'codeweber' ); ?></button>
			</div>
			<div style="margin-bottom:8px;">
				<label><strong><?php esc_html_e( 'Label', 'codeweber' ); ?>:</strong><br>
					<input type="text"
						name="codeweber_faq_settings[form_consents][<?php echo absint( $ci ); ?>][label]"
						value="<?php echo esc_attr( $consent['label'] ?? '' ); ?>"
						class="large-text"
						placeholder="<?php esc_attr_e( 'I agree to the {document_title_url}', 'codeweber' ); ?>">
				</label>
			</div>
			<div style="margin-bottom:8px;">
				<label><strong><?php esc_html_e( 'Document', 'codeweber' ); ?>:</strong><br>
					<select name="codeweber_faq_settings[form_consents][<?php echo absint( $ci ); ?>][document_id]" style="width:100%;">
						<option value=""><?php esc_html_e( '— Select —', 'codeweber' ); ?></option>
						<?php foreach ( $all_docs as $doc ) : ?>
						<option value="<?php echo esc_attr( $doc['id'] ); ?>" <?php selected( $consent['document_id'] ?? '', $doc['id'] ); ?>>
							<?php echo esc_html( $doc['title'] ); ?> (<?php echo esc_html( $doc['type'] ); ?>)
						</option>
						<?php endforeach; ?>
					</select>
				</label>
			</div>
			<label>
				<input type="checkbox"
					name="codeweber_faq_settings[form_consents][<?php echo absint( $ci ); ?>][required]"
					value="1" <?php checked( ! empty( $consent['required'] ) ); ?>>
				<?php esc_html_e( 'Required', 'codeweber' ); ?>
			</label>
		</div>
		<?php endforeach; ?>
	</div>

	<button type="button" id="faq-form-add-consent" class="button button-secondary" style="margin-top:4px;">
		<?php esc_html_e( '+ Add Consent', 'codeweber' ); ?>
	</button>

	<script>
	(function() {
		var list   = document.getElementById('faq-form-consents-list');
		var addBtn = document.getElementById('faq-form-add-consent');
		var allDocs = <?php echo wp_json_encode( array_values( $all_docs ) ); ?>;
		var idx = list.querySelectorAll('.faq-form-consent-row').length;

		function buildOptions() {
			var opts = '<option value=""><?php echo esc_js( __( '— Select —', 'codeweber' ) ); ?></option>';
			allDocs.forEach(function(doc) {
				opts += '<option value="' + doc.id + '">' + doc.title + ' (' + doc.type + ')</option>';
			});
			return opts;
		}

		function makeRow(i) {
			return '<div class="faq-form-consent-row" style="border:1px solid #ddd;padding:12px;margin-bottom:10px;background:#fff;border-radius:4px;">'
				+ '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">'
				+ '<strong><?php echo esc_js( __( 'Consent', 'codeweber' ) ); ?> #' + (i + 1) + '</strong>'
				+ '<button type="button" class="button faq-form-remove-consent"><?php echo esc_js( __( 'Remove', 'codeweber' ) ); ?></button>'
				+ '</div>'
				+ '<div style="margin-bottom:8px;">'
				+ '<label><strong><?php echo esc_js( __( 'Label', 'codeweber' ) ); ?>:</strong><br>'
				+ '<input type="text" name="codeweber_faq_settings[form_consents][' + i + '][label]" class="large-text" placeholder="<?php echo esc_js( __( 'I agree to the {document_title_url}', 'codeweber' ) ); ?>">'
				+ '</label></div>'
				+ '<div style="margin-bottom:8px;">'
				+ '<label><strong><?php echo esc_js( __( 'Document', 'codeweber' ) ); ?>:</strong><br>'
				+ '<select name="codeweber_faq_settings[form_consents][' + i + '][document_id]" style="width:100%;">' + buildOptions() + '</select>'
				+ '</label></div>'
				+ '<label><input type="checkbox" name="codeweber_faq_settings[form_consents][' + i + '][required]" value="1"> <?php echo esc_js( __( 'Required', 'codeweber' ) ); ?></label>'
				+ '</div>';
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

		function attachRemove(btn) {
			btn.addEventListener('click', function() { btn.closest('.faq-form-consent-row').remove(); });
		}

		list.querySelectorAll('.faq-form-consent-row').forEach(function(row) {
			attachRemove(row.querySelector('.faq-form-remove-consent'));
			bindDocSelect(row);
		});

		addBtn.addEventListener('click', function() {
			list.insertAdjacentHTML('beforeend', makeRow(idx++));
			var newRow = list.querySelector('.faq-form-consent-row:last-child');
			attachRemove(newRow.querySelector('.faq-form-remove-consent'));
			bindDocSelect(newRow);
		});
	})();
	</script>

	<p class="description" style="margin-top:8px;">
		<?php esc_html_e( 'Use {document_title_url} in the label to auto-insert a link to the document.', 'codeweber' ); ?>
	</p>
	<?php
}

// ---------------------------------------------------------------------------
// Sanitize
// ---------------------------------------------------------------------------

function codeweber_faq_settings_sanitize( array $input ): array {
	$clean = [];

	$clean['form_mode']       = in_array( $input['form_mode'] ?? '', [ 'disabled', 'inline', 'modal' ], true ) ? $input['form_mode'] : 'inline';
	$clean['show_name']       = isset( $input['show_name'] ) ? '1' : '0';
	$clean['show_email']      = isset( $input['show_email'] ) ? '1' : '0';
	$clean['show_phone']      = isset( $input['show_phone'] ) ? '1' : '0';
	$clean['notify_email']    = sanitize_email( $input['notify_email'] ?? '' );
	$clean['success_message'] = sanitize_text_field( $input['success_message'] ?? '' );

	// Consents
	$consents = [];
	if ( ! empty( $input['form_consents'] ) && is_array( $input['form_consents'] ) ) {
		foreach ( $input['form_consents'] as $c ) {
			$doc_id = absint( $c['document_id'] ?? 0 );
			$label  = sanitize_text_field( $c['label'] ?? '' );
			if ( $doc_id && $label ) {
				$consents[] = [
					'document_id' => $doc_id,
					'label'       => $label,
					'required'    => ! empty( $c['required'] ) ? '1' : '0',
				];
			}
		}
	}
	$clean['form_consents'] = $consents;

	// Sync to builtin_form_consents['faq']
	$all_builtin        = get_option( 'builtin_form_consents', [] );
	$all_builtin['faq'] = $consents;
	update_option( 'builtin_form_consents', $all_builtin );

	return $clean;
}

// ---------------------------------------------------------------------------
// Page render
// ---------------------------------------------------------------------------

function codeweber_faq_settings_render_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'FAQ Settings', 'codeweber' ); ?></h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'codeweber_faq_settings_group' );
			do_settings_sections( 'codeweber-faq-settings' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

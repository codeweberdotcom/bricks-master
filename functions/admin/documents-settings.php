<?php
/**
 * Documents Settings Page
 *
 * Settings page under Documents CPT: Documents → Settings.
 * Option key: codeweber_documents_settings
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Menu
// ---------------------------------------------------------------------------

function codeweber_documents_settings_register_page(): void {
	add_submenu_page(
		'edit.php?post_type=documents',
		__( 'Document Settings', 'codeweber' ),
		__( 'Settings', 'codeweber' ),
		'manage_options',
		'codeweber-documents-settings',
		'codeweber_documents_settings_render_page'
	);
}
add_action( 'admin_menu', 'codeweber_documents_settings_register_page' );

// ---------------------------------------------------------------------------
// Settings registration
// ---------------------------------------------------------------------------

function codeweber_documents_settings_register(): void {
	register_setting(
		'codeweber_documents_settings_group',
		'codeweber_documents_settings',
		[
			'sanitize_callback' => 'codeweber_documents_settings_sanitize',
			'default'           => [],
		]
	);

	// Section: Consents
	add_settings_section(
		'codeweber_documents_consents',
		__( 'Send by Email — Consents', 'codeweber' ),
		static function () {
			echo '<p class="description">'
				. esc_html__( 'Consent checkboxes shown in the document email form.', 'codeweber' )
				. '</p>';
		},
		'codeweber-documents-settings'
	);

	add_settings_field(
		'consents',
		__( 'Consent Documents', 'codeweber' ),
		'codeweber_documents_field_consents',
		'codeweber-documents-settings',
		'codeweber_documents_consents'
	);

	// Section: Rate Limits
	add_settings_section(
		'codeweber_documents_rate_limits',
		__( 'Send by Email — Rate Limits', 'codeweber' ),
		static function () {
			echo '<p class="description">'
				. esc_html__( 'Limit how often documents can be sent by email. Set 0 to disable a limit.', 'codeweber' )
				. '</p>';
		},
		'codeweber-documents-settings'
	);

	add_settings_field(
		'rl_period',
		__( 'Time window (minutes)', 'codeweber' ),
		'codeweber_documents_field_rl_period',
		'codeweber-documents-settings',
		'codeweber_documents_rate_limits'
	);

	add_settings_field(
		'rl_per_email',
		__( 'Max sends per email address', 'codeweber' ),
		'codeweber_documents_field_rl_per_email',
		'codeweber-documents-settings',
		'codeweber_documents_rate_limits'
	);


	// Section: Messages
	add_settings_section(
		'codeweber_documents_messages',
		__( 'Messages', 'codeweber' ),
		null,
		'codeweber-documents-settings'
	);

	add_settings_field(
		'success_message',
		__( 'Success message', 'codeweber' ),
		'codeweber_documents_field_success_message',
		'codeweber-documents-settings',
		'codeweber_documents_messages'
	);
}
add_action( 'admin_init', 'codeweber_documents_settings_register' );

// ---------------------------------------------------------------------------
// Helper
// ---------------------------------------------------------------------------

function codeweber_documents_settings_get( string $key, $default = '' ) {
	$options = get_option( 'codeweber_documents_settings', [] );
	return $options[ $key ] ?? $default;
}

// ---------------------------------------------------------------------------
// Field renderers
// ---------------------------------------------------------------------------

function codeweber_documents_field_consents(): void {
	$consents = codeweber_documents_settings_get( 'consents', [] );
	if ( ! is_array( $consents ) ) {
		$consents = [];
	}
	$all_docs = function_exists( 'codeweber_forms_get_all_documents' ) ? codeweber_forms_get_all_documents() : [];
	?>
	<div id="doc-email-consents-list">
		<?php foreach ( $consents as $ci => $consent ) : ?>
		<div class="doc-email-consent-row" style="border:1px solid #ddd;padding:12px;margin-bottom:10px;background:#fff;border-radius:4px;">
			<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
				<strong><?php esc_html_e( 'Consent', 'codeweber' ); ?> #<?php echo absint( $ci + 1 ); ?></strong>
				<button type="button" class="button doc-email-remove-consent"><?php esc_html_e( 'Remove', 'codeweber' ); ?></button>
			</div>
			<div style="margin-bottom:8px;">
				<label><strong><?php esc_html_e( 'Label', 'codeweber' ); ?>:</strong><br>
					<input type="text"
						name="codeweber_documents_settings[consents][<?php echo absint( $ci ); ?>][label]"
						value="<?php echo esc_attr( $consent['label'] ?? '' ); ?>"
						class="large-text"
						placeholder="<?php esc_attr_e( 'I agree to the {document_title_url}', 'codeweber' ); ?>">
				</label>
			</div>
			<div style="margin-bottom:8px;">
				<label><strong><?php esc_html_e( 'Document', 'codeweber' ); ?>:</strong><br>
					<select name="codeweber_documents_settings[consents][<?php echo absint( $ci ); ?>][document_id]" style="width:100%;max-width:400px;">
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
					name="codeweber_documents_settings[consents][<?php echo absint( $ci ); ?>][required]"
					value="1" <?php checked( ! empty( $consent['required'] ) ); ?>>
				<?php esc_html_e( 'Required', 'codeweber' ); ?>
			</label>
		</div>
		<?php endforeach; ?>
	</div>

	<button type="button" id="doc-email-add-consent" class="button button-secondary" style="margin-top:4px;">
		<?php esc_html_e( '+ Add Consent', 'codeweber' ); ?>
	</button>

	<script>
	(function() {
		var list   = document.getElementById('doc-email-consents-list');
		var addBtn = document.getElementById('doc-email-add-consent');
		var allDocs = <?php echo wp_json_encode( array_values( $all_docs ) ); ?>;
		var idx = list.querySelectorAll('.doc-email-consent-row').length;
		var consentLabelNonce = '<?php echo esc_js( wp_create_nonce( 'codeweber_forms_default_label' ) ); ?>';

		function buildOptions() {
			var opts = '<option value=""><?php echo esc_js( __( '— Select —', 'codeweber' ) ); ?></option>';
			allDocs.forEach(function(doc) {
				opts += '<option value="' + doc.id + '">' + doc.title + ' (' + doc.type + ')</option>';
			});
			return opts;
		}

		function makeRow(i) {
			return '<div class="doc-email-consent-row" style="border:1px solid #ddd;padding:12px;margin-bottom:10px;background:#fff;border-radius:4px;">'
				+ '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">'
				+ '<strong><?php echo esc_js( __( 'Consent', 'codeweber' ) ); ?> #' + (i + 1) + '</strong>'
				+ '<button type="button" class="button doc-email-remove-consent"><?php echo esc_js( __( 'Remove', 'codeweber' ) ); ?></button>'
				+ '</div>'
				+ '<div style="margin-bottom:8px;">'
				+ '<label><strong><?php echo esc_js( __( 'Label', 'codeweber' ) ); ?>:</strong><br>'
				+ '<input type="text" name="codeweber_documents_settings[consents][' + i + '][label]" class="large-text" placeholder="<?php echo esc_js( __( 'I agree to the {document_title_url}', 'codeweber' ) ); ?>">'
				+ '</label></div>'
				+ '<div style="margin-bottom:8px;">'
				+ '<label><strong><?php echo esc_js( __( 'Document', 'codeweber' ) ); ?>:</strong><br>'
				+ '<select name="codeweber_documents_settings[consents][' + i + '][document_id]" style="width:100%;max-width:400px;">' + buildOptions() + '</select>'
				+ '</label></div>'
				+ '<label><input type="checkbox" name="codeweber_documents_settings[consents][' + i + '][required]" value="1"> <?php echo esc_js( __( 'Required', 'codeweber' ) ); ?></label>'
				+ '</div>';
		}

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
			btn.addEventListener('click', function() { btn.closest('.doc-email-consent-row').remove(); });
		}

		list.querySelectorAll('.doc-email-consent-row').forEach(function(row) {
			attachRemove(row.querySelector('.doc-email-remove-consent'));
			bindDocSelect(row);
		});

		addBtn.addEventListener('click', function() {
			list.insertAdjacentHTML('beforeend', makeRow(idx++));
			var newRow = list.querySelector('.doc-email-consent-row:last-child');
			attachRemove(newRow.querySelector('.doc-email-remove-consent'));
			bindDocSelect(newRow);
		});
	})();
	</script>

	<p class="description" style="margin-top:8px;">
		<?php esc_html_e( 'Use {document_title_url} in the label to auto-insert a link to the document.', 'codeweber' ); ?>
	</p>
	<?php
}

function codeweber_documents_field_rl_period(): void {
	$val = (int) codeweber_documents_settings_get( 'rl_period', 10 );
	echo '<input type="number" name="codeweber_documents_settings[rl_period]" value="' . esc_attr( $val ) . '" min="1" max="1440" class="small-text"> ';
	esc_html_e( 'minutes', 'codeweber' );
	echo '<p class="description">' . esc_html__( 'Time window applied to all rate limit checks below.', 'codeweber' ) . '</p>';
}

function codeweber_documents_field_rl_per_email(): void {
	$val = (int) codeweber_documents_settings_get( 'rl_per_email', 0 );
	echo '<input type="number" name="codeweber_documents_settings[rl_per_email]" value="' . esc_attr( $val ) . '" min="0" class="small-text">';
	echo '<p class="description">' . esc_html__( 'Max documents sent to one email address per time window. 0 = unlimited.', 'codeweber' ) . '</p>';
}


function codeweber_documents_field_success_message(): void {
	$val = codeweber_documents_settings_get( 'success_message', '' );
	echo '<input type="text" name="codeweber_documents_settings[success_message]" value="' . esc_attr( $val ) . '" class="large-text" placeholder="' . esc_attr__( 'Document sent successfully to your email.', 'codeweber' ) . '">';
	echo '<p class="description">' . esc_html__( 'Leave empty to use the default message.', 'codeweber' ) . '</p>';
}

// ---------------------------------------------------------------------------
// Sanitize
// ---------------------------------------------------------------------------

function codeweber_documents_settings_sanitize( array $input ): array {
	$clean = [];

	$clean['rl_period']       = max( 1, absint( $input['rl_period'] ?? 10 ) );
	$clean['rl_per_email']    = absint( $input['rl_per_email'] ?? 0 );
	$clean['success_message'] = sanitize_text_field( $input['success_message'] ?? '' );

	$consents = [];
	if ( ! empty( $input['consents'] ) && is_array( $input['consents'] ) ) {
		foreach ( $input['consents'] as $c ) {
			$doc_id = absint( $c['document_id'] ?? 0 );
			$label  = sanitize_text_field( $c['label'] ?? '' );
			if ( ! $doc_id || ! $label ) {
				continue;
			}
			$consents[] = [
				'document_id' => $doc_id,
				'label'       => $label,
				'required'    => ! empty( $c['required'] ),
			];
		}
	}
	$clean['consents'] = $consents;

	return $clean;
}

// ---------------------------------------------------------------------------
// Page render
// ---------------------------------------------------------------------------

function codeweber_documents_settings_render_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Document Settings', 'codeweber' ); ?></h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'codeweber_documents_settings_group' );
			do_settings_sections( 'codeweber-documents-settings' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

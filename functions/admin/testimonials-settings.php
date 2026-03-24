<?php
/**
 * Testimonials Settings Page
 *
 * Страница настроек «Отзывы → Настройки».
 * Опция: codeweber_testimonials_settings
 * Согласия синхронизируются в builtin_form_consents['testimonial']
 * (именно оттуда читает testimonial-form-api.php).
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Menu
// ---------------------------------------------------------------------------

function codeweber_testimonials_settings_register_page(): void {
	add_submenu_page(
		'edit.php?post_type=testimonials',
		__( 'Testimonials Settings', 'codeweber' ),
		__( 'Settings', 'codeweber' ),
		'manage_options',
		'codeweber-testimonials-settings',
		'codeweber_testimonials_settings_render_page'
	);
}
add_action( 'admin_menu', 'codeweber_testimonials_settings_register_page' );

// ---------------------------------------------------------------------------
// Settings registration
// ---------------------------------------------------------------------------

function codeweber_testimonials_settings_register(): void {
	register_setting(
		'codeweber_testimonials_settings_group',
		'codeweber_testimonials_settings',
		[
			'sanitize_callback' => 'codeweber_testimonials_settings_sanitize',
			'default'           => [],
		]
	);

	// Section: Testimonial form consents
	add_settings_section(
		'codeweber_testimonials_form',
		__( 'Default Testimonial Form', 'codeweber' ),
		static function () {
			echo '<p class="description">'
				. esc_html__( 'Consent checkboxes shown in the built-in testimonial form (leave a review modal).', 'codeweber' )
				. '</p>';
		},
		'codeweber-testimonials-settings'
	);

	add_settings_field(
		'form_consents',
		__( 'Documents / Consents', 'codeweber' ),
		'codeweber_testimonials_field_form_consents',
		'codeweber-testimonials-settings',
		'codeweber_testimonials_form'
	);
}
add_action( 'admin_init', 'codeweber_testimonials_settings_register' );

// ---------------------------------------------------------------------------
// Helper
// ---------------------------------------------------------------------------

function codeweber_testimonials_settings_get( string $key, $default = '' ) {
	$options = get_option( 'codeweber_testimonials_settings', [] );
	return $options[ $key ] ?? $default;
}

// ---------------------------------------------------------------------------
// Field renderers
// ---------------------------------------------------------------------------

function codeweber_testimonials_field_form_consents(): void {
	$consents  = codeweber_testimonials_settings_get( 'form_consents', [] );
	if ( ! is_array( $consents ) ) {
		$consents = [];
	}
	$all_docs = function_exists( 'codeweber_forms_get_all_documents' ) ? codeweber_forms_get_all_documents() : [];
	?>
	<div id="testimonials-form-consents-list">
		<?php foreach ( $consents as $ci => $consent ) : ?>
		<div class="testimonials-form-consent-row" style="border:1px solid #ddd;padding:12px;margin-bottom:10px;background:#fff;border-radius:4px;">
			<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
				<strong><?php esc_html_e( 'Consent', 'codeweber' ); ?> #<?php echo absint( $ci + 1 ); ?></strong>
				<button type="button" class="button testimonials-form-remove-consent"><?php esc_html_e( 'Remove', 'codeweber' ); ?></button>
			</div>
			<div style="margin-bottom:8px;">
				<label><strong><?php esc_html_e( 'Label', 'codeweber' ); ?>:</strong><br>
					<input type="text"
						name="codeweber_testimonials_settings[form_consents][<?php echo absint( $ci ); ?>][label]"
						value="<?php echo esc_attr( $consent['label'] ?? '' ); ?>"
						class="large-text"
						placeholder="<?php esc_attr_e( 'I agree to the {document_title_url}', 'codeweber' ); ?>">
				</label>
			</div>
			<div style="margin-bottom:8px;">
				<label><strong><?php esc_html_e( 'Document', 'codeweber' ); ?>:</strong><br>
					<select name="codeweber_testimonials_settings[form_consents][<?php echo absint( $ci ); ?>][document_id]" style="width:100%;">
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
					name="codeweber_testimonials_settings[form_consents][<?php echo absint( $ci ); ?>][required]"
					value="1" <?php checked( ! empty( $consent['required'] ) ); ?>>
				<?php esc_html_e( 'Required', 'codeweber' ); ?>
			</label>
		</div>
		<?php endforeach; ?>
	</div>

	<button type="button" id="testimonials-form-add-consent" class="button button-secondary" style="margin-top:4px;">
		<?php esc_html_e( '+ Add Consent', 'codeweber' ); ?>
	</button>

	<script>
	(function() {
		var list   = document.getElementById('testimonials-form-consents-list');
		var addBtn = document.getElementById('testimonials-form-add-consent');
		var allDocs = <?php echo wp_json_encode( array_values( $all_docs ) ); ?>;
		var idx = list.querySelectorAll('.testimonials-form-consent-row').length;

		function buildOptions() {
			var opts = '<option value=""><?php echo esc_js( __( '— Select —', 'codeweber' ) ); ?></option>';
			allDocs.forEach(function(doc) {
				opts += '<option value="' + doc.id + '">' + doc.title + ' (' + doc.type + ')</option>';
			});
			return opts;
		}

		function makeRow(i) {
			return '<div class="testimonials-form-consent-row" style="border:1px solid #ddd;padding:12px;margin-bottom:10px;background:#fff;border-radius:4px;">'
				+ '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">'
				+ '<strong><?php echo esc_js( __( 'Consent', 'codeweber' ) ); ?> #' + (i + 1) + '</strong>'
				+ '<button type="button" class="button testimonials-form-remove-consent"><?php echo esc_js( __( 'Remove', 'codeweber' ) ); ?></button>'
				+ '</div>'
				+ '<div style="margin-bottom:8px;">'
				+ '<label><strong><?php echo esc_js( __( 'Label', 'codeweber' ) ); ?>:</strong><br>'
				+ '<input type="text" name="codeweber_testimonials_settings[form_consents][' + i + '][label]" class="large-text" placeholder="<?php echo esc_js( __( 'I agree to the {document_title_url}', 'codeweber' ) ); ?>">'
				+ '</label></div>'
				+ '<div style="margin-bottom:8px;">'
				+ '<label><strong><?php echo esc_js( __( 'Document', 'codeweber' ) ); ?>:</strong><br>'
				+ '<select name="codeweber_testimonials_settings[form_consents][' + i + '][document_id]" style="width:100%;">' + buildOptions() + '</select>'
				+ '</label></div>'
				+ '<label><input type="checkbox" name="codeweber_testimonials_settings[form_consents][' + i + '][required]" value="1"> <?php echo esc_js( __( 'Required', 'codeweber' ) ); ?></label>'
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
			btn.addEventListener('click', function() { btn.closest('.testimonials-form-consent-row').remove(); });
		}

		list.querySelectorAll('.testimonials-form-consent-row').forEach(function(row) {
			attachRemove(row.querySelector('.testimonials-form-remove-consent'));
			bindDocSelect(row);
		});

		addBtn.addEventListener('click', function() {
			list.insertAdjacentHTML('beforeend', makeRow(idx++));
			var newRow = list.querySelector('.testimonials-form-consent-row:last-child');
			attachRemove(newRow.querySelector('.testimonials-form-remove-consent'));
			bindDocSelect(newRow);
		});
	})();
	</script>

	<p class="description" style="margin-top:8px;">
		<?php esc_html_e( 'Use {document_title_url} in the label to auto-insert a link to the document. Example: I agree to the {document_title_url}.', 'codeweber' ); ?>
	</p>
	<?php
}

// ---------------------------------------------------------------------------
// Sanitize
// ---------------------------------------------------------------------------

function codeweber_testimonials_settings_sanitize( array $input ): array {
	$clean = [];

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

	// Sync to builtin_form_consents['testimonial'] — именно оттуда читает testimonial-form-api.php
	$all_builtin                  = get_option( 'builtin_form_consents', [] );
	$all_builtin['testimonial']   = $consents;
	update_option( 'builtin_form_consents', $all_builtin );

	return $clean;
}

// ---------------------------------------------------------------------------
// Page render
// ---------------------------------------------------------------------------

function codeweber_testimonials_settings_render_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Testimonials Settings', 'codeweber' ); ?></h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'codeweber_testimonials_settings_group' );
			do_settings_sections( 'codeweber-testimonials-settings' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

<?php
/**
 * Event registration form partial.
 *
 * Expected variables (passed by caller):
 *   $event_id       (int)
 *   $reg_form_title (string)
 *   $reg_button_label (string)
 *   $form_radius    (string)
 *   $button_style   (string)
 *   $phone_mask     (string)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$nonce = wp_create_nonce( 'codeweber_event_register' );
?>
<div class="event-registration-wrap">
	<h3 class="mb-4"><?php echo esc_html( $reg_form_title ); ?></h3>
	<form class="event-registration-form needs-validation"
		data-event-id="<?php echo esc_attr( $event_id ); ?>"
		novalidate>

		<input type="hidden" name="event_id" value="<?php echo esc_attr( $event_id ); ?>">
		<input type="hidden" name="event_reg_nonce" value="<?php echo esc_attr( $nonce ); ?>">
		<input type="text" name="event_reg_honeypot" class="d-none" tabindex="-1" autocomplete="off">

		<div class="mb-3">
			<input type="text" name="reg_name" class="form-control<?php echo esc_attr( $form_radius ); ?>"
				placeholder="<?php esc_attr_e( 'Your name *', 'codeweber' ); ?>"
				required>
			<div class="invalid-feedback"><?php esc_html_e( 'Please enter your name.', 'codeweber' ); ?></div>
		</div>

		<div class="mb-3">
			<input type="email" name="reg_email" class="form-control<?php echo esc_attr( $form_radius ); ?>"
				placeholder="<?php esc_attr_e( 'Email *', 'codeweber' ); ?>"
				required>
			<div class="invalid-feedback"><?php esc_html_e( 'Please enter a valid email.', 'codeweber' ); ?></div>
		</div>

		<div class="mb-3">
			<input type="tel" name="reg_phone" class="form-control<?php echo esc_attr( $form_radius ); ?>"
				placeholder="<?php esc_attr_e( 'Phone', 'codeweber' ); ?>"
				<?php if ( ! empty( $phone_mask ) ) : ?>data-mask="<?php echo esc_attr( $phone_mask ); ?>"<?php endif; ?>>
		</div>

		<div class="mb-4">
			<textarea name="reg_message" class="form-control<?php echo esc_attr( $form_radius ); ?>" rows="3"
				placeholder="<?php esc_attr_e( 'Comment (optional)', 'codeweber' ); ?>"></textarea>
		</div>

		<div class="event-reg-form-messages mb-3"></div>

		<button type="submit"
			class="btn btn-primary has-ripple w-100<?php echo esc_attr( $button_style ); ?>"
			data-loading-text="<?php esc_attr_e( 'Sending...', 'codeweber' ); ?>">
			<?php echo esc_html( $reg_button_label ); ?>
		</button>
	</form>
</div>

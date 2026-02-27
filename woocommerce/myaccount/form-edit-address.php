<?php
/**
 * Edit address form
 *
 * Theme override: Bootstrap styling — form-floating + form-control for inputs,
 * form-select-wrapper + form-select for country/state selects (like edit-account).
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

$page_title = ( 'billing' === $load_address ) ? esc_html__( 'Billing address', 'woocommerce' ) : esc_html__( 'Shipping address', 'woocommerce' );

do_action( 'woocommerce_before_edit_account_address_form' ); ?>

<?php if ( ! $load_address ) : ?>
	<?php wc_get_template( 'myaccount/my-address.php' ); ?>
<?php else :
	$form_radius = function_exists( 'getThemeFormRadius' ) ? getThemeFormRadius() : ' rounded';
	?>

	<form method="post" class="woocommerce-EditAddressForm contact-form<?php echo esc_attr( $form_radius ); ?>" novalidate>

		<h3 class="mb-6"><?php echo apply_filters( 'woocommerce_my_account_edit_address_title', $page_title, $load_address ); ?></h3><?php // @codingStandardsIgnoreLine ?>

		<div class="woocommerce-address-fields">
			<?php do_action( "woocommerce_before_edit_address_form_{$load_address}" ); ?>

			<div class="woocommerce-address-fields__field-wrapper row">
				<?php
				foreach ( $address as $key => $field ) {
					$field = wp_parse_args( (array) $field, array(
						'type'        => 'text',
						'label'       => '',
						'placeholder' => '',
						'required'    => false,
						'class'       => array(),
						'id'          => $key,
						'value'       => '',
					) );
					$value = wc_get_post_data_by_key( $key, $field['value'] );
					$half_width_keys = array( 'billing_phone', 'billing_email', 'billing_city', 'billing_postcode', 'shipping_phone', 'shipping_email', 'shipping_city', 'shipping_postcode' );
					$wrap_class = ( in_array( 'form-row-wide', (array) $field['class'], true ) && ! in_array( $key, $half_width_keys, true ) ) ? 'col-12' : 'col-md-6';
					$custom_attrs = array();
					if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {
						foreach ( $field['custom_attributes'] as $attr => $val ) {
							$custom_attrs[] = esc_attr( $attr ) . '="' . esc_attr( $val ) . '"';
						}
					}
					// Чтобы при смене страны WooCommerce (country-select.js) подставлял наши классы в новое поле state.
					if ( 'state' === $field['type'] ) {
						$country_key_attr = $load_address . '_country';
						$country_val_attr = isset( $address[ $country_key_attr ]['value'] ) ? wc_get_post_data_by_key( $country_key_attr, $address[ $country_key_attr ]['value'] ) : WC()->countries->get_base_country();
						$states_for_attr  = WC()->countries->get_states( $country_val_attr );
						$custom_attrs[]   = is_array( $states_for_attr ) && ! empty( $states_for_attr )
							? 'data-input-classes="form-select"'
							: 'data-input-classes="form-control state_select"';
					}
					$custom_attrs_str = implode( ' ', $custom_attrs );
					$required_mark = ! empty( $field['required'] ) ? ' <span class="required" aria-hidden="true">*</span>' : '';
					if ( 'state' === $field['type'] ) {
						$country_key_s = $load_address . '_country';
						$country_val_s = isset( $address[ $country_key_s ]['value'] ) ? wc_get_post_data_by_key( $country_key_s, $address[ $country_key_s ]['value'] ) : WC()->countries->get_base_country();
						$states_arr_s  = WC()->countries->get_states( $country_val_s );
						$wrapper_hidden = is_array( $states_arr_s ) && empty( $states_arr_s );
					} else {
						$wrapper_hidden = false;
					}
					?>
					<div class="<?php echo esc_attr( $wrap_class ); ?> mb-4" id="<?php echo esc_attr( $field['id'] ); ?>_field"<?php echo $wrapper_hidden ? ' style="display: none;"' : ''; ?>>
						<?php if ( 'country' === $field['type'] ) : ?>
							<?php
							$countries = ( 'shipping_country' === $key ) ? WC()->countries->get_shipping_countries() : WC()->countries->get_allowed_countries();
							if ( 1 === count( $countries ) ) :
								$c_key = key( $countries );
								$c_val = current( $countries );
							?>
								<div class="form-select-wrapper">
									<strong><?php echo esc_html( $c_val ); ?></strong>
									<input type="hidden" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" value="<?php echo esc_attr( $c_key ); ?>" class="country_to_state" readonly="readonly" <?php echo $custom_attrs_str; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> />
								</div>
							<?php else : ?>
								<label for="<?php echo esc_attr( $field['id'] ); ?>" class="form-label"><?php echo wp_kses_post( $field['label'] ); ?><?php echo $required_mark; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></label>
								<div class="form-select-wrapper">
									<select name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" class="form-select<?php echo esc_attr( $form_radius ); ?> country_to_state country_select" data-placeholder="<?php echo esc_attr( $field['placeholder'] ? esc_attr( $field['placeholder'] ) : esc_attr__( 'Select a country / region…', 'woocommerce' ) ); ?>" <?php echo $custom_attrs_str; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
										<option value=""><?php echo esc_html( $field['placeholder'] ? $field['placeholder'] : __( 'Select a country / region…', 'woocommerce' ) ); ?></option>
										<?php foreach ( $countries as $ckey => $cvalue ) : ?>
											<option value="<?php echo esc_attr( $ckey ); ?>" <?php selected( $value, $ckey ); ?>><?php echo esc_html( $cvalue ); ?></option>
										<?php endforeach; ?>
									</select>
								</div>
							<?php endif; ?>
						<?php elseif ( 'state' === $field['type'] ) : ?>
							<?php $states = $states_arr_s; ?>
							<?php if ( is_array( $states ) && empty( $states ) ) : ?>
								<input type="hidden" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" value="" class="state_select" <?php echo $custom_attrs_str; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> />
							<?php elseif ( is_array( $states ) && ! empty( $states ) ) : ?>
								<div class="form-floating">
									<select name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" class="form-select state_select" aria-label="<?php echo esc_attr( $field['label'] ); ?>" data-placeholder="<?php echo esc_attr( $field['placeholder'] ? $field['placeholder'] : esc_attr__( 'Select an option…', 'woocommerce' ) ); ?>" <?php echo $custom_attrs_str; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
										<option value=""><?php echo esc_html( $field['placeholder'] ? $field['placeholder'] : __( 'Select an option…', 'woocommerce' ) ); ?></option>
										<?php foreach ( $states as $skey => $svalue ) : ?>
											<option value="<?php echo esc_attr( $skey ); ?>" <?php selected( $value, $skey ); ?>><?php echo esc_html( $svalue ); ?></option>
										<?php endforeach; ?>
									</select>
									<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo wp_kses_post( $field['label'] ); ?><?php echo $required_mark; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></label>
								</div>
							<?php else : ?>
								<div class="form-floating">
									<input type="text" class="form-control<?php echo esc_attr( $form_radius ); ?> state_select" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" <?php echo $custom_attrs_str; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> />
									<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo wp_kses_post( $field['label'] ); ?><?php echo $required_mark; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></label>
								</div>
							<?php endif; ?>
						<?php elseif ( 'textarea' === $field['type'] ) : ?>
							<div class="form-floating">
								<textarea name="<?php echo esc_attr( $key ); ?>" class="form-control<?php echo esc_attr( $form_radius ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" rows="2" <?php echo $custom_attrs_str; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_textarea( $value ); ?></textarea>
								<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo wp_kses_post( $field['label'] ); ?><?php echo $required_mark; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></label>
							</div>
						<?php else : ?>
							<div class="form-floating">
								<input type="<?php echo esc_attr( $field['type'] ); ?>" class="form-control<?php echo esc_attr( $form_radius ); ?>" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" <?php echo $custom_attrs_str; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> />
								<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo wp_kses_post( $field['label'] ); ?><?php echo $required_mark; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></label>
							</div>
						<?php endif; ?>
					</div>
				<?php } ?>
			</div>

			<?php do_action( "woocommerce_after_edit_address_form_{$load_address}" ); ?>

			<div class="mb-3">
				<button type="submit" class="btn btn-sm btn-primary<?php echo function_exists( 'getThemeButton' ) ? ' ' . esc_attr( trim( getThemeButton() ) ) : ''; ?><?php echo wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ) : ''; ?>" name="save_address" value="<?php esc_attr_e( 'Save address', 'woocommerce' ); ?>"><?php esc_html_e( 'Save address', 'woocommerce' ); ?></button>
				<?php wp_nonce_field( 'woocommerce-edit_address', 'woocommerce-edit-address-nonce' ); ?>
				<input type="hidden" name="action" value="edit_address" />
			</div>
		</div>

	</form>

<?php endif; ?>

<?php do_action( 'woocommerce_after_edit_account_address_form' ); ?>

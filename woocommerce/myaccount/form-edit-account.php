<?php

/**
 * Edit account form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-edit-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.7.0
 */

defined('ABSPATH') || exit;

/**
 * Hook - woocommerce_before_edit_account_form.
 *
 * @since 2.6.0
 */
do_action('woocommerce_before_edit_account_form');
?>


<form class="woocommerce-EditAccountForm edit-account contact-form" action="" method="post" <?php do_action('woocommerce_edit_account_form_tag'); ?>>

	<?php do_action('woocommerce_edit_account_form_start'); ?>

	<div class="row">
		<h2 class="display-6 mb-6"><?php esc_html_e('Personal data', 'codeweber'); ?></h2>
		<div class="col-md-6">
			<div class="form-floating mb-4">
				<input type="text" class="form-control" name="account_first_name" id="account_first_name" placeholder="<?php esc_attr_e('First name', 'woocommerce'); ?>" autocomplete="given-name" value="<?php echo esc_attr($user->first_name); ?>" aria-required="true">
				<label for="account_first_name"><?php esc_html_e('First name', 'woocommerce'); ?> *</label>
			</div>
		</div>

		<div class="col-md-6">
			<div class="form-floating mb-4">
				<input type="text" class="form-control" name="account_last_name" id="account_last_name" placeholder="<?php esc_attr_e('Last name', 'woocommerce'); ?>" autocomplete="family-name" value="<?php echo esc_attr($user->last_name); ?>" aria-required="true">
				<label for="account_last_name"><?php esc_html_e('Last name', 'woocommerce'); ?> *</label>
			</div>
		</div>

		<div class="col-md-6">
			<div class="form-floating mb-4">
				<input type="text" class="form-control" name="account_display_name" id="account_display_name" placeholder="<?php esc_attr_e('Display name', 'woocommerce'); ?>" value="<?php echo esc_attr($user->display_name); ?>" aria-required="true">
				<label for="account_display_name"><?php esc_html_e('Display name', 'woocommerce'); ?> *</label>
			</div>
		</div>

		<div class="col-md-6">
			<div class="form-floating mb-4">
				<input type="email" class="form-control" name="account_email" id="account_email" placeholder="<?php esc_attr_e('Email address', 'woocommerce'); ?>" autocomplete="email" value="<?php echo esc_attr($user->user_email); ?>" aria-required="true">
				<label for="account_email"><?php esc_html_e('Email address', 'woocommerce'); ?> *</label>
			</div>
		</div>

	</div>



	<?php
	/**
	 * Hook where additional fields should be rendered.
	 *
	 * @since 8.7.0
	 */
	do_action('woocommerce_edit_account_form_fields');
	?>

	<fieldset class="mt-6">
		<h2 class="display-6 mb-6"><?php esc_html_e('Password change', 'woocommerce'); ?></h2>

		<div class="form-floating mb-4 password-field">
			<input
				type="password"
				class="form-control"
				name="password_current"
				id="password_current"
				autocomplete="off"
				placeholder="<?php esc_attr_e('Current password (leave blank to leave unchanged)', 'woocommerce'); ?>" />
			<span class="password-toggle"><i class="uil uil-eye"></i></span>
			<label for="password_current"><?php esc_html_e('Current password (leave blank to leave unchanged)', 'woocommerce'); ?></label>
		</div>

		<div class="form-floating mb-4 password-field">
			<input
				type="password"
				class="form-control"
				name="password_1"
				id="password_1"
				autocomplete="off"
				placeholder="<?php esc_attr_e('New password (leave blank to leave unchanged)', 'woocommerce'); ?>" />
			<span class="password-toggle"><i class="uil uil-eye"></i></span>
			<label for="password_1"><?php esc_html_e('New password (leave blank to leave unchanged)', 'woocommerce'); ?></label>
		</div>

		<div class="form-floating mb-4 password-field">
			<input
				type="password"
				class="form-control"
				name="password_2"
				id="password_2"
				autocomplete="off"
				placeholder="<?php esc_attr_e('Confirm new password', 'woocommerce'); ?>" />
			<span class="password-toggle"><i class="uil uil-eye"></i></span>
			<label for="password_2"><?php esc_html_e('Confirm new password', 'woocommerce'); ?></label>
		</div>
	</fieldset>

	<div class="clear"></div>

	<?php
	/**
	 * My Account edit account form.
	 *
	 * @since 2.6.0
	 */
	do_action('woocommerce_edit_account_form');
	?>

	<div class="mb-3">
		<?php wp_nonce_field('save_account_details', 'save-account-details-nonce'); ?>
		<button
			type="submit"
			class="btn btn-primary <?php getThemeButton(); ?> <?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? wc_wp_theme_get_element_class_name('button') : ''); ?>"
			name="save_account_details"
			value="<?php esc_attr_e('Save changes', 'woocommerce'); ?>">
			<?php esc_html_e('Save changes', 'woocommerce'); ?>
		</button>
		<input type="hidden" name="action" value="save_account_details" />
	</div>


	<?php do_action('woocommerce_edit_account_form_end'); ?>
</form>

<?php do_action('woocommerce_after_edit_account_form'); ?>
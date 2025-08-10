<?php

/**
 * Lost password form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-lost-password.php.
 *
 * @package WooCommerce\Templates
 * @version 9.2.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_lost_password_form');
?>

<form method="post" class="woocommerce-ResetPassword lost_reset_password">

	<p><?php echo apply_filters(
			'woocommerce_lost_password_message',
			esc_html__('Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.', 'woocommerce')
		); ?></p>

	<div class="form-floating mb-4">
		<input
			type="text"
			class="form-control"
			name="user_login"
			id="user_login"
			placeholder="<?php esc_attr_e('Username or email', 'woocommerce'); ?>"
			autocomplete="username"
			required
			aria-required="true" />
		<label for="user_login"><?php esc_html_e('Username or email', 'woocommerce'); ?></label>
	</div>

	<?php do_action('woocommerce_lostpassword_form'); ?>

	<input type="hidden" name="wc_reset_password" value="true" />

	<p class="form-row">
		<button type="submit" class="btn btn-primary <?php getThemeButton(); ?>">
			<?php esc_html_e('Reset password', 'woocommerce'); ?>
		</button>
	</p>

	<?php wp_nonce_field('lost_password', 'woocommerce-lost-password-nonce'); ?>

</form>

<?php do_action('woocommerce_after_lost_password_form'); ?>
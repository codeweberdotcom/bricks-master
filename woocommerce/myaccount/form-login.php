<?php

/**
 * Login Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-login.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.9.0
 */


if (! defined('ABSPATH')) {
	exit;
}

do_action('woocommerce_before_customer_login_form');

// Проверяем, включена ли регистрация
$registration_enabled = get_option('woocommerce_enable_myaccount_registration') === 'yes';

// Проверяем, есть ли в URL параметр register=1
$show_register = $registration_enabled && isset($_GET['register']) && $_GET['register'] == '1';
?>

<div class="row" id="customer_login">

	<!-- LOGIN FORM -->
	<div class="col login-form-wrapper" style="display: <?php echo $show_register ? 'none' : 'block'; ?>;">
		<h2 class="mb-3 text-start"><?php esc_html_e('Login', 'woocommerce'); ?></h2>
		<p class="lead mb-6 text-start"><?php echo esc_html__('Fill your email and password to sign in.', 'codeweber'); ?></p>

		<form class="woocommerce-form woocommerce-form-login text-center needs-validation" method="post" novalidate>
			<?php do_action('woocommerce_login_form_start'); ?>

			<div class="form-floating mb-4">
				<input id="username" type="text" class="form-control" name="username" autocomplete="username" placeholder="<?php esc_attr_e('Username or email address', 'woocommerce'); ?>" value="<?php echo (! empty($_POST['username']) && is_string($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>" required aria-required="true" />
				<label for="username"><?php esc_html_e('Username or email address', 'woocommerce'); ?></label>
			</div>

			<div class="form-floating mb-4 password-field">
				<input id="password" type="password" class="form-control" name="password" autocomplete="current-password" placeholder="<?php esc_attr_e('Password', 'woocommerce'); ?>" required aria-required="true" />
				<span class="password-toggle"><i class="uil uil-eye"></i></span>
				<label for="password"><?php esc_html_e('Password', 'woocommerce'); ?></label>
			</div>

			<?php do_action('woocommerce_login_form'); ?>

			<div class="form-check mb-3 text-start small-chekbox fs-12">
				<input class="form-check-input" name="rememberme" type="checkbox" id="rememberme" value="forever" />
				<label class="form-check-label" for="rememberme"><?php esc_html_e('Remember me', 'woocommerce'); ?></label>
			</div>

			<p class="form-row">
				<?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>
				<button type="submit" class="woocommerce-button btn btn-primary <?php getThemeButton(); ?> woocommerce-form-login__submit<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>" name="login" value="<?php esc_attr_e('Log in', 'woocommerce'); ?>"><?php esc_html_e('Log in', 'woocommerce'); ?></button>
			</p>

			<p class="woocommerce-LostPassword lost_password mb-1">
				<a href="<?php echo esc_url(wp_lostpassword_url()); ?>" class="hover"><?php esc_html_e('Lost your password?', 'woocommerce'); ?></a>
			</p>

			<?php if ($registration_enabled) : ?>
				<p class="mb-0">
					<?php esc_html_e("Don't have an account?", 'codeweber'); ?>
					<a href="?register=1" id="show-register-form" class="hover"><?php esc_html_e('Sign up', 'codeweber'); ?></a>
				</p>
			<?php endif; ?>

			<?php do_action('woocommerce_login_form_end'); ?>
		</form>
	</div>

	<!-- REGISTER FORM -->
	<?php if ($registration_enabled) : ?>
		<div class="col register-form-wrapper" style="display: <?php echo $show_register ? 'block' : 'none'; ?>;">
			<h2 class="mb-3 text-start"><?php esc_html_e('Register', 'woocommerce'); ?></h2>
			<p class="lead mb-6 text-start"><?php echo esc_html__('Registration takes less than a minute.', 'codeweber'); ?></p>

			<form method="post" class="woocommerce-form woocommerce-form-register needs-validation" <?php do_action('woocommerce_register_form_tag'); ?> novalidate>
				<?php do_action('woocommerce_register_form_start'); ?>

				<?php if ('no' === get_option('woocommerce_registration_generate_username')) : ?>
					<div class="form-floating mb-4">
						<input type="text" class="form-control" name="username" id="reg_username" placeholder="<?php esc_attr_e('Username', 'woocommerce'); ?>" value="<?php echo (!empty($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>" required aria-required="true" autocomplete="username" />
						<label for="reg_username"><?php esc_html_e('Username', 'woocommerce'); ?> *</label>
					</div>
				<?php endif; ?>

				<div class="form-floating mb-4">
					<input type="email" class="form-control" name="email" id="reg_email" placeholder="<?php esc_attr_e('Email address', 'woocommerce'); ?>" value="<?php echo (!empty($_POST['email'])) ? esc_attr(wp_unslash($_POST['email'])) : ''; ?>" required aria-required="true" autocomplete="email" />
					<label for="reg_email"><?php esc_html_e('Email address', 'woocommerce'); ?> *</label>
				</div>

				<?php if ('no' === get_option('woocommerce_registration_generate_password')) : ?>
					<div class="form-floating mb-4 password-field">
						<input type="password" class="form-control" name="password" id="reg_password" placeholder="<?php esc_attr_e('Password', 'woocommerce'); ?>" required aria-required="true" autocomplete="new-password" />
						<span class="password-toggle"><i class="uil uil-eye"></i></span>
						<label for="reg_password"><?php esc_html_e('Password', 'woocommerce'); ?> *</label>
					</div>
				<?php else : ?>
					<p><?php esc_html_e('A link to set a new password will be sent to your email address.', 'woocommerce'); ?></p>
				<?php endif; ?>

				<?php do_action('woocommerce_register_form'); ?>

				<p class="woocommerce-form-row form-row">
					<?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>
					<button type="submit" class="woocommerce-Button woocommerce-button btn btn-primary <?php getThemeButton(); ?> <?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?> woocommerce-form-register__submit" name="register" value="<?php esc_attr_e('Register', 'woocommerce'); ?>">
						<?php esc_html_e('Register', 'woocommerce'); ?>
					</button>
				</p>

				<p class="mb-0 text-center">
					<?php esc_html_e('Already have an account?', 'codeweber'); ?>
					<a href="<?php echo esc_url(remove_query_arg('register')); ?>" id="show-login-form" class="hover"><?php esc_html_e('Log in', 'woocommerce'); ?></a>
				</p>

				<?php do_action('woocommerce_register_form_end'); ?>
			</form>
		</div>
	<?php endif; ?>

</div>

<?php do_action('woocommerce_after_customer_login_form'); ?>

<script>
	document.addEventListener("DOMContentLoaded", function() {
		const showRegister = document.getElementById("show-register-form");
		const showLogin = document.getElementById("show-login-form");
		const loginForm = document.querySelector(".login-form-wrapper");
		const registerForm = document.querySelector(".register-form-wrapper");

		if (showRegister && loginForm && registerForm) {
			showRegister.addEventListener("click", function(e) {
				e.preventDefault();
				loginForm.style.display = "none";
				registerForm.style.display = "block";
				history.replaceState(null, '', '?register=1');
			});
		}
		if (showLogin && loginForm && registerForm) {
			showLogin.addEventListener("click", function(e) {
				e.preventDefault();
				loginForm.style.display = "block";
				registerForm.style.display = "none";
				history.replaceState(null, '', window.location.pathname);
			});
		}
	});
</script>
<?php

/**
 * Edit address form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-edit-address.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.3.0
 */

defined('ABSPATH') || exit;

$page_title = ('billing' === $load_address) ? esc_html__('Billing address', 'woocommerce') : esc_html__('Shipping address', 'woocommerce');

do_action('woocommerce_before_edit_account_address_form'); ?>

<?php if (! $load_address) : ?>
	<?php wc_get_template('myaccount/my-address.php'); ?>
<?php else : ?>

	<form method="post" novalidate>

		<h2 class="display-6 mb-6"><?php echo apply_filters('woocommerce_my_account_edit_address_title', $page_title, $load_address); ?></h2><?php // @codingStandardsIgnoreLine 
																																															?>

		<div class="woocommerce-address-fields">
			<?php do_action("woocommerce_before_edit_address_form_{$load_address}"); ?>

			<div class="woocommerce-address-fields__field-wrapper row">
				<?php foreach ($address as $key => $field) :
					$value = wc_get_post_data_by_key($key, $field['value']);
					$label = $field['label'];
					$required = !empty($field['required']) ? 'required' : '';
					$type = $field['type'] ?? 'text';
				?>
					<div class="col-md-6">
						<div class="form-floating mb-4">
							<input
								type="<?php echo esc_attr($type); ?>"
								class="form-control"
								name="<?php echo esc_attr($key); ?>"
								id="<?php echo esc_attr($key); ?>"
								value="<?php echo esc_attr($value); ?>"
								placeholder="<?php echo esc_attr($label); ?>"
								<?php echo $required; ?>>
							<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label>
						</div>
					</div>
				<?php endforeach; ?>

			</div>

			<?php do_action("woocommerce_after_edit_address_form_{$load_address}"); ?>

			<p>
				<button type="submit" class="btn btn-primary <?php getThemeButton(); ?> <?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>" name="save_address" value="<?php esc_attr_e('Save address', 'woocommerce'); ?>"><?php esc_html_e('Save address', 'woocommerce'); ?></button>
				<?php wp_nonce_field('woocommerce-edit_address', 'woocommerce-edit-address-nonce'); ?>
				<input type="hidden" name="action" value="edit_address" />
			</p>
		</div>

	</form>

<?php endif; ?>

<?php do_action('woocommerce_after_edit_account_address_form'); ?>
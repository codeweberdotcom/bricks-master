<?php

/**
 * My Account navigation
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/navigation.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.3.0
 */

if (! defined('ABSPATH')) {
	exit;
}

do_action('woocommerce_before_account_navigation');
?>

<nav id="sidebar-nav" class="navbar-vertical menu-collapse-nav navbar-vertical-light" aria-label="<?php esc_html_e('Account pages', 'woocommerce'); ?>">
	<ul class="navbar-nav list-unstyled text-reset menu-collapse-1">
		<?php foreach (wc_get_account_menu_items() as $endpoint => $label) : ?>
			<?php
			$wc_classes = wc_get_account_menu_item_classes($endpoint);
			$is_current = wc_is_current_account_menu_item($endpoint);
			$li_classes = array_filter(array_merge(
				array('nav-item', 'parent-collapse-item', 'parent-item'),
				explode(' ', $wc_classes),
				$is_current ? array('current-menu-item') : array()
			));
			$link_classes = array_filter(array_merge(
				array('nav-link'),
				$is_current ? array('current-menu-item') : array()
			));
			?>
			<li class="<?php echo esc_attr(implode(' ', $li_classes)); ?>">
				<a href="<?php echo esc_url(wc_get_account_endpoint_url($endpoint)); ?>" class="<?php echo esc_attr(implode(' ', $link_classes)); ?>" <?php echo $is_current ? ' aria-current="page"' : ''; ?>>
					<?php echo esc_html($label); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>

<?php do_action('woocommerce_after_account_navigation'); ?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
	<?php global $opt_name; ?>
</head>

<body>

	<div class="content-wrapper">
		<?php global $opt_name;
		$global_header_model = Redux::get_option($opt_name, 'global-header-model');
		if ($global_header_model === '1' || $global_header_model === '2') {
			get_template_part('templates/header/header', 'classic');
		} elseif ($global_header_model === '3') {
			get_template_part('templates/header/header', 'center-logo');
		} elseif ($global_header_model === '4' || $global_header_model === '5') {
			get_template_part('templates/header/header', 'fancy');
		} elseif ($global_header_model === '6') {
			get_template_part('templates/header/header', 'fancy-center-logo');
		} elseif ($global_header_model === '7') {
			get_template_part('templates/header/header', 'extended');
		} elseif ($global_header_model === '8') {
			get_template_part('templates/header/header', 'extended-center-logo');
		}
		?>
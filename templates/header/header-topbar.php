<?php
global $opt_name;

$phone1 = Redux::get_option($opt_name, 'phone_01');
$email = Redux::get_option($opt_name, 'e-mail');

$country      = Redux::get_option($opt_name, 'fact-country') ?? '';
$region       = Redux::get_option($opt_name, 'fact-region') ?? '';
$city         = Redux::get_option($opt_name, 'fact-city') ?? '';
$street       = ', ' . Redux::get_option($opt_name, 'fact-street') ?? '';
$house_number = ', ' . Redux::get_option($opt_name, 'fact-house') ?? '';
$office       = Redux::get_option($opt_name, 'fact-office') ?? '';
$postal_code  = Redux::get_option($opt_name, 'fact-postal') ?? '';
$full_address = trim("{$city}, {$street}, {$house_number}", ' ,');
?>

<div class="bg-primary text-white fw-bold fs-15">
	<div class="container d-flex flex-row justify-content-between">
		<div class="d-flex flex-row align-items-center">
			<div class="icon text-white  mt-1 me-2"> <i class="uil uil-location-pin-alt"></i></div>
			<address class="mb-0 d-flex"><?= $city; ?> <span class="d-none d-md-block"><?= $street; ?></span> <span class="d-none d-md-block"><?= $house_number; ?></span></address>
		</div>
		<div class="d-none d-md-flex flex-row align-items-center me-6 ms-auto">
			<div class="icon text-white mt-1 me-2"> <i class="uil uil-message"></i></div>
			<p class="mb-0"><a href="mailto:<?= $email; ?>" class="link-white hover"><?= $email; ?></a></p>
		</div>
		<div class="d-flex flex-row align-items-center">
			<div class="icon text-white  mt-1 me-2"> <i class="uil uil-phone-volume"></i></div>
		</div>
	</div>
	<!-- /.container -->
</div>
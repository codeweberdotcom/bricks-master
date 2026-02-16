<?php
/**
 * Offcanvas Search Template
 *
 * Выводится после </nav>. Можно переопределить в дочерней теме.
 *
 * @package Codeweber
 */
if (!defined('ABSPATH')) {
	exit;
}
?>
<div class="offcanvas offcanvas-top bg-light" id="offcanvas-search" data-bs-scroll="true">
	<div class="container d-flex flex-row py-6">
		<form class="search-form w-100">
			<input id="search-form" type="text" class="form-control" placeholder="<?= esc_html__('Type keyword', 'codeweber'); ?>">
		</form>
		<!-- /.search-form -->
		<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
	</div>
	<!-- /.container -->
</div>
<!-- /.offcanvas -->

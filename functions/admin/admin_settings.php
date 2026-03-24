<?php
require_once get_template_directory() . '/functions/admin/admin_menu.php'; // --- Admin menu settings ---
require_once get_template_directory() . '/functions/admin/api-test.php'; // --- API Test buttons ---

require_once get_template_directory() . '/functions/admin/admin_privacy.php'; // --- Admin menu settings Privacy Menu---

require_once get_template_directory() . '/functions/admin/admin_user_profil.php'; // --- Admin menu settings User Profil---

require_once get_template_directory() . '/functions/admin/admin_media.php'; // --- Admin menu settings Media Library ---


require_once get_template_directory() . '/functions/admin/admin-gutenberg.php'; // --- Admin menu settings Media Library ---

/**
 * Фикс deprecated: strip_tags(null) в admin-header.php на новых версиях PHP.
 * Гарантируем, что глобальный $title всегда строка к моменту вызова strip_tags().
 */
add_action( 'current_screen', function () {
	global $title;
	if ( $title === null ) {
		$title = '';
	}
} );


<?php

/**
 * https://developer.wordpress.org/themes/basics/theme-functions/
 */

// ── CPT ───────────────────────────────────────────────────────────────────────
require_once get_template_directory() . '/functions/cpt/cpt-header.php';
require_once get_template_directory() . '/functions/cpt/cpt-footer.php';
require_once get_template_directory() . '/functions/cpt/cpt-page-header.php';
require_once get_template_directory() . '/functions/cpt/cpt-modals.php';
require_once get_template_directory() . '/functions/cpt/cpt-html_blocks.php';
require_once get_template_directory() . '/functions/cpt/cpt-clients.php';
require_once get_template_directory() . '/functions/cpt/cpt-notifications.php';

// ── Ядро темы ─────────────────────────────────────────────────────────────────
require_once get_template_directory() . '/functions/setup.php';
require_once get_template_directory() . '/functions/roles.php';
require_once get_template_directory() . '/functions/gulp.php';

require_once get_template_directory() . '/plugins/tgm/class-tgm-plugin-activation.php';
require_once get_template_directory() . '/plugins/tgm/plugins_autoinstall.php';

require_once get_template_directory() . '/functions/class-codeweber-options.php';
require_once get_template_directory() . '/functions/enqueues.php';
require_once get_template_directory() . '/functions/images.php';
require_once get_template_directory() . '/functions/navmenus.php';
require_once get_template_directory() . '/functions/sidebars.php';
require_once get_template_directory() . '/functions/documentation.php';

// ── Nav walkers ───────────────────────────────────────────────────────────────
require_once get_template_directory() . '/functions/lib/class-wp-bootstrap-navwalker.php';
require_once get_template_directory() . '/functions/lib/class-codeweber-vertical-dropdown-walker.php';
require_once get_template_directory() . '/functions/lib/cw-notify/class-cw-notify.php';
require_once get_template_directory() . '/functions/lib/class-codeweber-menu-collapse-walker.php';

// ── Прочие утилиты ────────────────────────────────────────────────────────────
require_once get_template_directory() . '/functions/codeweber-nav.php';
require_once get_template_directory() . '/functions/global.php';
require_once get_template_directory() . '/functions/breadcrumbs.php';
require_once get_template_directory() . '/functions/cleanup.php';
require_once get_template_directory() . '/functions/custom.php';
require_once get_template_directory() . '/functions/user.php';
require_once get_template_directory() . '/functions/cyr-to-lat.php';
require_once get_template_directory() . '/functions/lib/comments-helper.php';
require_once get_template_directory() . '/functions/comments-reply.php';
require_once get_template_directory() . '/functions/post-card-templates.php';
require_once get_template_directory() . '/functions/post-cards-registry.php';

// ── Админка ───────────────────────────────────────────────────────────────────
require_once get_template_directory() . '/functions/admin/admin_settings.php';
require_once get_template_directory() . '/functions/admin/media-regenerate.php';
require_once get_template_directory() . '/functions/admin/media-cpt-filter.php';
require_once get_template_directory() . '/functions/admin/image-tag-taxonomy.php';
require_once get_template_directory() . '/functions/admin/image-canvas-editor.php';
require_once get_template_directory() . '/functions/admin/term-thumbnail.php';

// ── Fetch / AJAX ──────────────────────────────────────────────────────────────
require_once get_template_directory() . '/functions/fetch/fetch-handler.php';

// ── SEO ──────────────────────────────────────────────────────────────────────
require_once get_template_directory() . '/functions/seo/seo-detect.php';
require_once get_template_directory() . '/functions/seo/seo-meta-tags.php';
require_once get_template_directory() . '/functions/seo/seo-schema.php';
require_once get_template_directory() . '/functions/seo/schema/schema-article.php';
require_once get_template_directory() . '/functions/seo/schema/schema-event.php';
require_once get_template_directory() . '/functions/seo/schema/schema-staff.php';
require_once get_template_directory() . '/functions/seo/schema/schema-vacancy.php';
require_once get_template_directory() . '/functions/seo/schema/schema-office.php';
require_once get_template_directory() . '/functions/seo/schema/schema-service.php';
require_once get_template_directory() . '/functions/seo/schema/schema-testimonial.php';
require_once get_template_directory() . '/functions/seo/schema/schema-faq.php';
require_once get_template_directory() . '/functions/seo/schema/schema-project.php';
require_once get_template_directory() . '/functions/seo/schema/schema-document.php';

// ── Интеграции ────────────────────────────────────────────────────────────────
require_once get_template_directory() . '/functions/integrations/dadata/init.php';
require_once get_template_directory() . '/functions/integrations/personal-data-v2/init.php';
require_once get_template_directory() . '/functions/integrations/newsletter-subscription/newsletter-init.php';
require_once get_template_directory() . '/functions/integrations/image-licenses/image-licenses.php';
require_once get_template_directory() . '/functions/integrations/ajax-search-module/init.php';
require_once get_template_directory() . '/functions/integrations/codeweber-forms/codeweber-forms-init.php';
require_once get_template_directory() . '/functions/integrations/yandex-maps/yandex-maps-init.php';
require_once get_template_directory() . '/functions/integrations/modal/init.php';
require_once get_template_directory() . '/functions/integrations/s3-storage/s3-storage.php';
require_once get_template_directory() . '/functions/integrations/telegram/telegram-init.php';

if ( class_exists( 'WPCF7' ) ) {
	require_once get_template_directory() . '/functions/integrations/cf7/cf7.php';
}

if ( class_exists( 'WooCommerce' ) ) {
	require_once get_template_directory() . '/functions/woocommerce/init.php';
}

// ── Body Background (per-page / per-CPT фон через Redux + метабокс) ──────────
require_once get_template_directory() . '/functions/body-bg.php';

// ── Уведомления (после Redux, priority 40) ────────────────────────────────────
add_action( 'after_setup_theme', function () {
	new CW_Notify();
}, 40 );

// ── Testimonials ──────────────────────────────────────────────────────────────
require_once get_template_directory() . '/functions/testimonials/testimonial-form-api.php';

// ── Demo-данные (только WP_DEBUG) ─────────────────────────────────────────────
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
	require_once get_template_directory() . '/functions/demo/demo-clients.php';
	require_once get_template_directory() . '/functions/demo/demo-faq.php';
	require_once get_template_directory() . '/functions/demo/demo-testimonials.php';
	require_once get_template_directory() . '/functions/demo/demo-staff.php';
	require_once get_template_directory() . '/functions/demo/demo-vacancies.php';
	require_once get_template_directory() . '/functions/demo/demo-forms.php';
	require_once get_template_directory() . '/functions/demo/demo-cf7-forms.php';
	require_once get_template_directory() . '/functions/demo/demo-offices.php';
	require_once get_template_directory() . '/functions/demo/demo-footer.php';
	require_once get_template_directory() . '/functions/demo/demo-header.php';
	require_once get_template_directory() . '/functions/demo/demo-products.php';
	require_once get_template_directory() . '/functions/demo/demo-events.php';
	require_once get_template_directory() . '/functions/demo/demo-ajax.php';
}

<?php




/**
 *  https://developer.wordpress.org/themes/basics/theme-functions/
 */

// Подключение файлов CPT
require_once get_template_directory() . '/functions/cpt/cpt-header.php';
require_once get_template_directory() . '/functions/cpt/cpt-footer.php';
require_once get_template_directory() . '/functions/cpt/cpt-page-header.php';
require_once get_template_directory() . '/functions/cpt/cpt-modals.php';
require_once get_template_directory() . '/functions/cpt/cpt-html_blocks.php';

require_once get_template_directory() . '/functions/setup.php';
require_once get_template_directory() . '/functions/gulp.php';

require_once get_template_directory() . '/plugins/tgm/class-tgm-plugin-activation.php';
require_once get_template_directory() . '/plugins/tgm/plugins_autoinstall.php';

require_once get_template_directory() . '/functions/enqueues.php';
require_once get_template_directory() . '/functions/images.php';
require_once get_template_directory() . '/functions/navmenus.php';
require_once get_template_directory() . '/functions/sidebars.php';
require_once get_template_directory() . '/functions/lib/class-wp-bootstrap-navwalker.php';
require_once get_template_directory() . '/functions/global.php';
require_once get_template_directory() . '/functions/breadcrumbs.php';
require_once get_template_directory() . '/functions/cleanup.php';
require_once get_template_directory() . '/functions/custom.php';

if (class_exists('WPCF7')) {
require_once get_template_directory() . '/functions/integrations/cf7.php';
};

require_once get_template_directory() . '/functions/admin/admin_settings.php';
require_once get_template_directory() . '/functions/fetch/fetch-handler.php';

if (class_exists('WooCommerce')) {
	require_once get_template_directory() . '/functions/woocommerce.php';
}

require_once get_template_directory() . '/functions/cyr-to-lat.php';

require_once get_template_directory() . '/functions/lib/comments-helper.php'; // --- Comments Helper ---
require_once get_template_directory() . '/functions/comments-reply.php'; // --- Comments Reply Functions ---



/**
 * Инициализация Redux Framework
 */
function codeweber_initialize_redux()
{
	if (!class_exists('Redux')) {
		require_once get_template_directory() . '/redux-framework/redux-core/framework.php';
	}

	global $opt_name;
	$opt_name = 'redux_demo';

	require_once get_template_directory() . '/redux-framework/sample/theme-config.php';
	require_once get_template_directory() . '/functions/cpt/redux_cpt.php';
	require_once get_template_directory() . '/functions/sidebars-redux.php';
}
add_action('after_setup_theme', 'codeweber_initialize_redux', 20);



add_shortcode('list_sidebars', function () {
	global $wp_registered_sidebars;
	if (empty($wp_registered_sidebars)) {
		return 'Сайдбаров нет';
	}

	$output = '<ul>';
	foreach ($wp_registered_sidebars as $sidebar) {
		$output .= '<li>' . esc_html($sidebar['id']) . '</li>';
	}
	$output .= '</ul>';

	return $output;
});


/**
 * Шорткод для вывода списка файлов CPT
 * Использование: [cpt_files_list] или [cpt_files_list format="ul"]
 * 
 * @param array $atts Атрибуты шорткода
 * @return string HTML-код списка файлов
 */
function cpt_files_list_shortcode($atts)
{
	// Получаем список файлов
	$files = get_cpt_files_list();

	// Обрабатываем атрибуты
	$atts = shortcode_atts(array(
		'format' => 'ul', // Возможные значения: ul, ol, comma
	), $atts);

	// Если файлов нет
	if (empty($files)) {
		return '<p>No CPT files found.</p>';
	}

	// Формируем вывод в зависимости от формата
	switch ($atts['format']) {
		case 'ol':
			$output = '<ol><li>' . implode('</li><li>', $files) . '</li></ol>';
			break;

		case 'comma':
			$output = implode(', ', $files);
			break;

		case 'ul':
		default:
			$output = '<ul><li>' . implode('</li><li>', $files) . '</li></ul>';
	}

	return $output;
}
add_shortcode('cpt_files_list', 'cpt_files_list_shortcode');
<?php

/**
 * https://developer.wordpress.org/themes/basics/including-css-javascript/
 */

/**
 * Получить путь к файлу из dist, проверяя сначала дочернюю тему, затем родительскую
 * 
 * @param string $file_path Относительный путь к файлу от корня темы (например: '/dist/assets/css/style.css')
 * @return string|false URL файла или false, если файл не найден
 */
if (!function_exists('brk_get_dist_file_url')) {
	function brk_get_dist_file_url($file_path) {
		// Убираем начальный слэш, если есть
		$file_path = ltrim($file_path, '/');
		
		// Проверяем сначала в дочерней теме (если она активна)
		if (is_child_theme()) {
			$child_file = get_stylesheet_directory() . '/' . $file_path;
			if (file_exists($child_file)) {
				return get_stylesheet_directory_uri() . '/' . $file_path;
			}
		}
		
		// Если не найдено в дочерней теме или дочерняя тема не активна, проверяем родительскую
		$parent_file = get_template_directory() . '/' . $file_path;
		if (file_exists($parent_file)) {
			return get_template_directory_uri() . '/' . $file_path;
		}
		
		// Файл не найден
		return false;
	}
}

/**
 * Получить путь к файлу из dist, проверяя сначала дочернюю тему, затем родительскую
 * 
 * @param string $file_path Относительный путь к файлу от корня темы (например: '/dist/assets/css/style.css')
 * @return string|false Полный путь к файлу или false, если файл не найден
 */
if (!function_exists('brk_get_dist_file_path')) {
	function brk_get_dist_file_path($file_path) {
		// Убираем начальный слэш, если есть
		$file_path = ltrim($file_path, '/');
		
		// Проверяем сначала в дочерней теме (если она активна)
		if (is_child_theme()) {
			$child_file = get_stylesheet_directory() . '/' . $file_path;
			if (file_exists($child_file)) {
				return $child_file;
			}
		}
		
		// Если не найдено в дочерней теме или дочерняя тема не активна, проверяем родительскую
		$parent_file = get_template_directory() . '/' . $file_path;
		if (file_exists($parent_file)) {
			return $parent_file;
		}
		
		// Файл не найден
		return false;
	}
}

if (!function_exists('brk_styles_scripts')) {
	function brk_styles_scripts()
	{
		$theme_version = wp_get_theme()->get('Version');

		// --- CSS ---
		//wp_enqueue_style('google-fonts', brk_get_dist_file_url('dist/assets/css/fonts/urbanist.css'), false, $theme_version, 'all');
		$plugin_styles_url = brk_get_dist_file_url('dist/assets/css/plugins.css');
		if ($plugin_styles_url) {
			wp_enqueue_style('plugin-styles', $plugin_styles_url, false, $theme_version, 'all');
		}
		
		$theme_styles_url = brk_get_dist_file_url('dist/assets/css/style.css');
		if ($theme_styles_url) {
			wp_enqueue_style('theme-styles', $theme_styles_url, false, $theme_version, 'all');
		}

		if (class_exists('Redux')) {
		global $opt_name;
		   $theme_color = Redux::get_option($opt_name, 'opt-select-color-theme');
		}else{
			$theme_color = 'default';
		}

		// --- Подключаем основной style.css ---
		wp_enqueue_style('root-styles', get_template_directory_uri() . '/style.css', false, $theme_version, 'all');

		// --- Если выбрана тема не "default" — подключаем соответствующий файл из /dist/assets/assets/css/colors/ ---
		if ($theme_color && $theme_color !== 'default') {
			$color_styles_url = brk_get_dist_file_url('dist/assets/css/colors/' . $theme_color . '.css');
			if ($color_styles_url) {
				wp_enqueue_style(
					'theme-color-style',
					$color_styles_url,
					false,
					$theme_version,
					'all'
				);
			}
		}

		// --- JS ---

		//* add comment reply script */
		if (is_singular() and comments_open() and (get_option('thread_comments') == 1)) wp_enqueue_script('comment-reply');

		/*dist add codeweber theme scripts */
		$plugins_scripts_url = brk_get_dist_file_url('dist/assets/js/plugins.js');
		if ($plugins_scripts_url) {
			wp_enqueue_script('plugins-scripts', $plugins_scripts_url, false, $theme_version, true);
		}
		
		$theme_scripts_url = brk_get_dist_file_url('dist/assets/js/theme.js');
		if ($theme_scripts_url) {
			wp_enqueue_script('theme-scripts', $theme_scripts_url, array('plugins-scripts'), $theme_version, true);
			
			wp_localize_script('theme-scripts', 'theme_scripts_ajax', array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('theme-scripts_nonce'),
				'translations' => array(
					'message_sent' => __('Message successfully sent.', 'codeweber'),
				)
			));
		}
	}
}
add_action('wp_enqueue_scripts', 'brk_styles_scripts');



// --- Unicons ACF admin styles and Blocks Gutenberg ---
if (! function_exists('brk_styles_scripts_gutenberg')) {
	function brk_styles_scripts_gutenberg()
	{
		$theme_version = wp_get_theme()->get('Version');

		// --- CSS ---
		$plugin_styles_url = brk_get_dist_file_url('dist/assets/css/plugins.css');
		if ($plugin_styles_url) {
			wp_enqueue_style('plugin-styles1', $plugin_styles_url, array(), $theme_version, 'all');
		}
		
		$theme_styles_url = brk_get_dist_file_url('dist/assets/css/style.css');
		if ($theme_styles_url) {
			wp_enqueue_style('theme-styles1', $theme_styles_url, array(), $theme_version, 'all');
		}

		// --- JS ---
		$plugins_scripts_url = brk_get_dist_file_url('dist/assets/js/plugins.js');
		if ($plugins_scripts_url) {
			wp_enqueue_script('plugins-scripts2', $plugins_scripts_url, array(), $theme_version, true);
		}
		
		$theme_scripts_url = brk_get_dist_file_url('dist/assets/js/theme.js');
		if ($theme_scripts_url) {
			wp_enqueue_script('theme-scripts2', $theme_scripts_url, array(), $theme_version, true);
		}
	}
}
add_action('enqueue_block_editor_assets', 'brk_styles_scripts_gutenberg');


function enqueue_my_custom_script()
{
	// Load on all pages (needed for ajax-download and other REST API features)
	$restapi_url = brk_get_dist_file_url('dist/assets/js/restapi.js');
	if (!$restapi_url) {
		return; // File doesn't exist
	}
	
	$restapi_path = brk_get_dist_file_path('dist/assets/js/restapi.js');
	$version = $restapi_path ? filemtime($restapi_path) : null;
	
	// Проверяем, загружен ли plugins-scripts (для Bootstrap)
	$plugins_scripts_url = brk_get_dist_file_url('dist/assets/js/plugins.js');
	$dependencies = array();
	if ($plugins_scripts_url) {
		$dependencies[] = 'plugins-scripts';
	}
	
	wp_enqueue_script(
		'my-custom-script',
		$restapi_url,
		$dependencies,
		$version,
		true // Перемещаем в footer, чтобы Bootstrap точно был загружен
	);

		$current_user_id = get_current_user_id();
		wp_localize_script('my-custom-script', 'wpApiSettings', array(
			'root' => esc_url_raw(rest_url()),
			'nonce' => wp_create_nonce('wp_rest'),
			'currentUserId' => $current_user_id,
			'isLoggedIn' => $current_user_id > 0
		));
		
		// Локализация для загрузки документов
		wp_localize_script('my-custom-script', 'codeweberDownload', array(
			'loadingText' => __('Loading...', 'codeweber'),
			'errorText' => __('Error downloading file. Please try again.', 'codeweber')
		));
		
		// Локализация для отправки документов на email
		wp_localize_script('my-custom-script', 'codeweberDocumentEmail', array(
			'sendingText' => __('Sending...', 'codeweber'),
			'successText' => __('Document sent successfully!', 'codeweber'),
			'errorText' => __('Error sending email. Please try again.', 'codeweber'),
			'serverError' => __('Server error', 'codeweber')
		));
}
add_action('wp_enqueue_scripts', 'enqueue_my_custom_script', 20);



function theme_enqueue_fetch_assets()
{
	$script_path = get_template_directory() . '/functions/fetch/assets/js/fetch-handler.js';
	$script_url  = get_template_directory_uri() . '/functions/fetch/assets/js/fetch-handler.js';

	// Если файл существует, подключаем его
	if (file_exists($script_path)) {
		wp_enqueue_script(
			'fetch-handler',
			$script_url,
			['wp-util'], // или ['jquery'] если нужно
			filemtime($script_path),
			true // загрузка в футере
		);

		// Передаем переменные JS
		wp_localize_script('fetch-handler', 'fetch_vars', [
			'ajaxurl' => admin_url('admin-ajax.php'),
		]);
	}
}
add_action('wp_enqueue_scripts', 'theme_enqueue_fetch_assets');

/**
 * Enqueue testimonial form script
 * Form can appear on any page (e.g., in modal), so we always load the universal script
 */
function codeweber_enqueue_testimonial_form() {
	// Always load universal form script (it handles both testimonial and codeweber forms)
	// If codeweber-forms-core already loaded it, WordPress will handle it correctly
	$universal_script_path = get_template_directory() . '/functions/integrations/codeweber-forms/assets/js/form-submit-universal.js';
	$universal_script_url = get_template_directory_uri() . '/functions/integrations/codeweber-forms/assets/js/form-submit-universal.js';
	
	if (file_exists($universal_script_path)) {
		wp_enqueue_script(
			'codeweber',
			$universal_script_url,
			['jquery'],
			filemtime($universal_script_path),
			true
		);
		
		// Add codeweberForms localization (will merge if already exists)
		wp_localize_script('codeweber', 'codeweberForms', [
			'restUrl' => rest_url('codeweber-forms/v1/'),
			'restNonce' => wp_create_nonce('wp_rest'),
		]);
	}
	
	// Add testimonial-specific localization
	wp_localize_script('codeweber', 'codeweberTestimonialForm', [
		'restUrl' => rest_url('codeweber/v1/submit-testimonial'),
		'nonce' => wp_create_nonce('wp_rest'),
	]);
	
	// Add wpApiSettings for REST API access (needed for success message template)
	wp_localize_script('codeweber', 'wpApiSettings', [
		'root' => esc_url_raw(rest_url()),
		'nonce' => wp_create_nonce('wp_rest'),
	]);
}
add_action('wp_enqueue_scripts', 'codeweber_enqueue_testimonial_form', 20); // Priority 20 to run after codeweber-forms-core

/**
 * Enqueue AJAX download script for documents
 * Загружается на всех страницах, так как шаблон documents может использоваться в блоках Gutenberg
 */
function codeweber_enqueue_ajax_download() {
	// Load only from dist
	$dist_path = brk_get_dist_file_path('dist/assets/js/ajax-download.js');
	$dist_url = brk_get_dist_file_url('dist/assets/js/ajax-download.js');
	
	if (!$dist_path || !$dist_url) {
		return; // File doesn't exist in dist
	}
	
	wp_enqueue_script(
		'ajax-download',
		$dist_url,
		['my-custom-script'], // Зависимость от restapi.js для wpApiSettings
		filemtime($dist_path),
		true // Load in footer
	);
	
	// wpApiSettings уже подключен в enqueue_my_custom_script() для всех страниц
	// Поэтому дополнительная локализация не требуется
}
add_action('wp_enqueue_scripts', 'codeweber_enqueue_ajax_download', 25);

/**
 * Enqueue universal AJAX filter script
 * Загружается на всех страницах для универсального использования
 */
function codeweber_enqueue_ajax_filter() {
	// Prefer dist version, fallback to src
	$dist_path = brk_get_dist_file_path('dist/assets/js/ajax-filter.js');
	$dist_url = brk_get_dist_file_url('dist/assets/js/ajax-filter.js');
	
	if ($dist_path && $dist_url) {
		$script_path = $dist_path;
		$script_url = $dist_url;
	} else {
		// Fallback to src
		$src_path = get_template_directory() . '/src/assets/js/ajax-filter.js';
		$src_url = get_template_directory_uri() . '/src/assets/js/ajax-filter.js';
		
		if (file_exists($src_path)) {
			$script_path = $src_path;
			$script_url = $src_url;
		} else {
			return; // File doesn't exist
		}
	}
	
	wp_enqueue_script(
		'ajax-filter',
		$script_url,
		[], // Dependencies
		filemtime($script_path),
		true // Load in footer
	);
	
	// Localize script
	wp_localize_script('ajax-filter', 'codeweberFilter', [
		'ajaxUrl' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('codeweber_filter_nonce'),
		'translations' => [
			'error' => __('Error', 'codeweber'),
			'loading' => __('Loading...', 'codeweber'),
		]
	]);
}
add_action('wp_enqueue_scripts', 'codeweber_enqueue_ajax_filter', 20);
<?php

/**
 * https://developer.wordpress.org/themes/basics/including-css-javascript/
 */

if (!function_exists('brk_styles_scripts')) {
	function brk_styles_scripts()
	{
		$theme_version = wp_get_theme()->get('Version');

		// --- CSS ---
		//wp_enqueue_style('google-fonts', get_template_directory_uri() . '/dist/assets/css/fonts/urbanist.css', false, $theme_version, 'all');
		wp_enqueue_style('plugin-styles', get_template_directory_uri() . '/dist/assets/css/plugins.css', false, $theme_version, 'all');
		wp_enqueue_style('theme-styles', get_template_directory_uri() . '/dist/assets/css/style.css', false, $theme_version, 'all');

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
			wp_enqueue_style(
				'theme-color-style',
				get_template_directory_uri() . '/dist/assets/css/colors/' . $theme_color . '.css',
				false,
				$theme_version,
				'all'
			);
		}

		// --- JS ---

		//* add comment reply script */
		if (is_singular() and comments_open() and (get_option('thread_comments') == 1)) wp_enqueue_script('comment-reply');

		/*dist add codeweber theme scripts */
		wp_enqueue_script('plugins-scripts', get_template_directory_uri() . '/dist/assets/js/plugins.js', false, $theme_version, true);
		wp_enqueue_script('theme-scripts', get_template_directory_uri() . '/dist/assets/js/theme.js', false, $theme_version, true);

		wp_localize_script('theme-scripts', 'theme_scripts_ajax', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('theme-scripts_nonce'),
			'translations' => array(
				'message_sent' => __('Message successfully sent.', 'codeweber'),
			)
		));
	}
}
add_action('wp_enqueue_scripts', 'brk_styles_scripts');



// --- Unicons ACF admin styles and Blocks Gutenberg ---
if (! function_exists('brk_styles_scripts_gutenberg')) {
	function brk_styles_scripts_gutenberg()
	{
		$theme_version = wp_get_theme()->get('Version');

		// --- CSS ---
		wp_enqueue_style('plugin-styles1', get_template_directory_uri() . '/dist/assets/css/plugins.css', array(), $theme_version, 'all');
		wp_enqueue_style('theme-styles1', get_template_directory_uri() . '/dist/assets/css/style.css', array(), $theme_version, 'all');

		// --- JS ---
		wp_enqueue_script('plugins-scripts2', get_template_directory_uri() . '/dist/assets/js/plugins.js', array(), $theme_version, true);
		wp_enqueue_script('theme-scripts2', get_template_directory_uri() . '/dist/assets/js/theme.js', array(), $theme_version, true);
	}
}
add_action('enqueue_block_editor_assets', 'brk_styles_scripts_gutenberg');


function enqueue_my_custom_script()
{
	// Load on pages and post type archives (including testimonials archive)
	if (is_page() || is_post_type_archive() || is_archive()) {
		wp_enqueue_script(
			'my-custom-script',
			get_template_directory_uri() . '/dist/assets/js/restapi.js',
			array(),
			null,
			false // <-- подключаем в head, не в footer
		);

		$current_user_id = get_current_user_id();
		wp_localize_script('my-custom-script', 'wpApiSettings', array(
			'root' => esc_url_raw(rest_url()),
			'nonce' => wp_create_nonce('wp_rest'),
			'currentUserId' => $current_user_id,
			'isLoggedIn' => $current_user_id > 0
		));
	}
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
 */
function codeweber_enqueue_testimonial_form() {
	// Check if we're on testimonials archive page
	if (is_post_type_archive('testimonials') || is_page_template('archive-testimonials.php')) {
		// Prefer dist version, fallback to src
		$dist_path = get_template_directory() . '/dist/assets/js/testimonial-form.js';
		$dist_url = get_template_directory_uri() . '/dist/assets/js/testimonial-form.js';
		$src_path = get_template_directory() . '/src/assets/js/testimonial-form.js';
		$src_url = get_template_directory_uri() . '/src/assets/js/testimonial-form.js';
		
		if (file_exists($dist_path)) {
			$script_path = $dist_path;
			$script_url = $dist_url;
		} elseif (file_exists($src_path)) {
			$script_path = $src_path;
			$script_url = $src_url;
		} else {
			return; // File doesn't exist
		}
		
		wp_enqueue_script(
			'testimonial-form',
			$script_url,
			[], // Dependencies
			filemtime($script_path),
			true // Load in footer
		);
		
		// Localize script
		wp_localize_script('testimonial-form', 'codeweberTestimonialForm', [
			'restUrl' => rest_url('codeweber/v1/submit-testimonial'),
			'nonce' => wp_create_nonce('wp_rest'),
		]);
	}
}
add_action('wp_enqueue_scripts', 'codeweber_enqueue_testimonial_form');
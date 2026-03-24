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
if (!function_exists('codeweber_get_dist_file_url')) {
	function codeweber_get_dist_file_url($file_path) {
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
if (!function_exists('codeweber_get_dist_file_path')) {
	function codeweber_get_dist_file_path($file_path) {
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

/**
 * Возвращает версию ассета: filemtime в режиме отладки, версию темы — в продакшене.
 * Предотвращает лишние stat()-вызовы при каждом запросе на продакшене.
 *
 * @param string|false $file_path Абсолютный путь к файлу (или false).
 * @return string|int|null
 */
if (!function_exists('codeweber_asset_version')) {
	function codeweber_asset_version($file_path) {
		if (defined('WP_DEBUG') && WP_DEBUG && $file_path && file_exists($file_path)) {
			return filemtime($file_path);
		}
		return wp_get_theme()->get('Version');
	}
}

if (!function_exists('codeweber_styles_scripts')) {
	function codeweber_styles_scripts()
	{
		$theme_version = wp_get_theme()->get('Version');

		// --- CSS ---
		//wp_enqueue_style('google-fonts', codeweber_get_dist_file_url('dist/assets/css/fonts/urbanist.css'), false, $theme_version, 'all');
		$plugin_styles_url = codeweber_get_dist_file_url('dist/assets/css/plugins.css');
		if ($plugin_styles_url) {
			wp_enqueue_style('plugin-styles', $plugin_styles_url, false, codeweber_asset_version(codeweber_get_dist_file_path('dist/assets/css/plugins.css')), 'all');
		}

		$theme_styles_url = codeweber_get_dist_file_url('dist/assets/css/style.css');
		if ($theme_styles_url) {
			wp_enqueue_style('codeweber-style', $theme_styles_url, false, codeweber_asset_version(codeweber_get_dist_file_path('dist/assets/css/style.css')), 'all');
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
			$color_styles_url = codeweber_get_dist_file_url('dist/assets/css/colors/' . $theme_color . '.css');
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
		$plugins_scripts_url = codeweber_get_dist_file_url('dist/assets/js/plugins.js');
		if ($plugins_scripts_url) {
			wp_enqueue_script('plugins-scripts', $plugins_scripts_url, false, $theme_version, true);
		}
		
		$theme_scripts_url = codeweber_get_dist_file_url('dist/assets/js/theme.js');
		if ($theme_scripts_url) {
			wp_enqueue_script('theme-scripts', $theme_scripts_url, array('plugins-scripts'), $theme_version, true);
			
			wp_localize_script('theme-scripts', 'theme_scripts_ajax', array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('theme-scripts_nonce'),
				'translations' => array(
					'message_sent' => __('Message successfully sent.', 'codeweber'),
				)
			));

			$phone_mask_value = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::get( 'opt_phone_mask', '' ) : '';
			wp_localize_script( 'theme-scripts', 'codeweberTheme', array(
				'phoneMask' => $phone_mask_value,
			) );
		}

		// Header search dropdown: run after Bootstrap, before theme.js, so our capture listener runs first.
		$plugins_scripts_url = codeweber_get_dist_file_url('dist/assets/js/plugins.js');
		if ($plugins_scripts_url) {
			wp_enqueue_script('codeweber-search-dropdown', false, array('plugins-scripts'), $theme_version, true);
		$search_dropdown_inline = <<<'JS'
(function(){
  function run() {
    var toggle = document.querySelector('.cwgb-search-block .dropdown-toggle');
    var container = toggle ? toggle.closest('.dropdown') : null;
    var menu = container ? container.querySelector('.dropdown-menu') : null;
    if (!toggle || !menu) return;
    toggle.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopImmediatePropagation();
      var show = !menu.classList.contains('show');
      menu.classList.toggle('show', show);
      toggle.setAttribute('aria-expanded', show ? 'true' : 'false');
    }, true);
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', run);
  } else {
    run();
  }
})();
JS;
		wp_add_inline_script('codeweber-search-dropdown', $search_dropdown_inline, 'after');
		}
	}
}
add_action('wp_enqueue_scripts', 'codeweber_styles_scripts');

// --- Unicons ACF admin styles and Blocks Gutenberg ---
if (! function_exists('codeweber_styles_scripts_gutenberg')) {
	function codeweber_styles_scripts_gutenberg()
	{
		$theme_version = wp_get_theme()->get('Version');

		// --- CSS ---
		$plugin_styles_url = codeweber_get_dist_file_url('dist/assets/css/plugins.css');
		if ($plugin_styles_url) {
			wp_enqueue_style('plugin-styles1', $plugin_styles_url, [], $theme_version, 'all');
		}
		
		$theme_styles_url = codeweber_get_dist_file_url('dist/assets/css/style.css');
		if ($theme_styles_url) {
			wp_enqueue_style('theme-styles1', $theme_styles_url, [], $theme_version, 'all');
		}

		// --- JS ---
		$plugins_scripts_url = codeweber_get_dist_file_url('dist/assets/js/plugins.js');
		if ($plugins_scripts_url) {
			wp_enqueue_script('plugins-scripts2', $plugins_scripts_url, [], $theme_version, true);
		}
		
		$theme_scripts_url = codeweber_get_dist_file_url('dist/assets/js/theme.js');
		if ($theme_scripts_url) {
			wp_enqueue_script('theme-scripts2', $theme_scripts_url, [], $theme_version, true);
		}
	}
}
add_action('enqueue_block_editor_assets', 'codeweber_styles_scripts_gutenberg');


function codeweber_enqueue_restapi_script()
{
	// Load on all pages (needed for ajax-download and other REST API features)
	$restapi_url = codeweber_get_dist_file_url('dist/assets/js/restapi.js');
	if (!$restapi_url) {
		return; // File doesn't exist
	}
	
	$restapi_path = codeweber_get_dist_file_path('dist/assets/js/restapi.js');
	$version = codeweber_asset_version($restapi_path);
	
	// Проверяем, загружен ли plugins-scripts (для Bootstrap)
	$plugins_scripts_url = codeweber_get_dist_file_url('dist/assets/js/plugins.js');
	$dependencies = [];
	if ($plugins_scripts_url) {
		$dependencies[] = 'plugins-scripts';
	}
	
	wp_enqueue_script(
		'codeweber-restapi',
		$restapi_url,
		$dependencies,
		$version,
		true // Перемещаем в footer, чтобы Bootstrap точно был загружен
	);

		$current_user_id = get_current_user_id();
		wp_localize_script('codeweber-restapi', 'wpApiSettings', array(
			'root' => esc_url_raw(rest_url()),
			'nonce' => wp_create_nonce('wp_rest'),
			'currentUserId' => $current_user_id,
			'isLoggedIn' => $current_user_id > 0
		));
		
		// Локализация для загрузки документов
		wp_localize_script('codeweber-restapi', 'codeweberDownload', array(
			'loadingText' => __('Loading...', 'codeweber'),
			'errorText' => __('Error downloading file. Please try again.', 'codeweber')
		));
		
		// Локализация для отправки документов на email
		wp_localize_script('codeweber-restapi', 'codeweberDocumentEmail', array(
			'sendingText' => __('Sending...', 'codeweber'),
			'successText' => __('Document sent successfully!', 'codeweber'),
			'errorText' => __('Error sending email. Please try again.', 'codeweber'),
			'serverError' => __('Server error', 'codeweber')
		));
}
add_action('wp_enqueue_scripts', 'codeweber_enqueue_restapi_script', 20);

/**
 * Enqueue notification triggers script
 * Handles all trigger types for notification modals
 */
function codeweber_enqueue_notification_triggers()
{
	// Load on all pages (needed for notification triggers)
	$notification_triggers_url = codeweber_get_dist_file_url('dist/assets/js/notification-triggers.js');
	if (!$notification_triggers_url) {
		return; // File doesn't exist
	}
	
	$notification_triggers_path = codeweber_get_dist_file_path('dist/assets/js/notification-triggers.js');
	$version = codeweber_asset_version($notification_triggers_path);
	
	// Dependencies: plugins-scripts (Bootstrap) and codeweber-restapi (restapi.js)
	$plugins_scripts_url = codeweber_get_dist_file_url('dist/assets/js/plugins.js');
	$dependencies = [];
	if ($plugins_scripts_url) {
		$dependencies[] = 'plugins-scripts';
	}
	if (wp_script_is('codeweber-restapi', 'registered')) {
		$dependencies[] = 'codeweber-restapi';
	}
	
	wp_enqueue_script(
		'notification-triggers',
		$notification_triggers_url,
		$dependencies,
		$version,
		true // Load in footer
	);
}
add_action('wp_enqueue_scripts', 'codeweber_enqueue_notification_triggers', 25);



function theme_enqueue_fetch_assets()
{
	$script_path = get_template_directory() . '/functions/fetch/assets/js/fetch-handler.js';
	$script_url  = get_template_directory_uri() . '/functions/fetch/assets/js/fetch-handler.js';

	// Если файл существует, подключаем его
	if (file_exists($script_path)) {
		wp_enqueue_script(
			'fetch-handler',
			$script_url,
			[],
			codeweber_asset_version($script_path),
			true // загрузка в футере
		);

		// Передаем переменные JS
		wp_localize_script('fetch-handler', 'fetch_vars', [
			'ajaxurl' => admin_url('admin-ajax.php'),
			'nonce'   => wp_create_nonce('fetch_action_nonce'),
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
			[],
			codeweber_asset_version($universal_script_path),
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
	// wpApiSettings уже передаётся в codeweber_enqueue_restapi_script() — дублировать не нужно.
}
add_action('wp_enqueue_scripts', 'codeweber_enqueue_testimonial_form', 20); // Priority 20 to run after codeweber-forms-core

/**
 * Enqueue AJAX download script for documents
 * Загружается на всех страницах, так как шаблон documents может использоваться в блоках Gutenberg
 */
function codeweber_enqueue_ajax_download() {
	// Load only from dist
	$dist_path = codeweber_get_dist_file_path('dist/assets/js/ajax-download.js');
	$dist_url = codeweber_get_dist_file_url('dist/assets/js/ajax-download.js');
	
	if (!$dist_path || !$dist_url) {
		return; // File doesn't exist in dist
	}
	
	wp_enqueue_script(
		'ajax-download',
		$dist_url,
		['codeweber-restapi'], // Зависимость от restapi.js для wpApiSettings
		codeweber_asset_version($dist_path),
		true // Load in footer
	);
	
	// wpApiSettings уже подключен в codeweber_enqueue_restapi_script() для всех страниц
	// Поэтому дополнительная локализация не требуется
}
add_action('wp_enqueue_scripts', 'codeweber_enqueue_ajax_download', 25);

/**
 * Enqueue universal AJAX filter script
 * Загружается на всех страницах для универсального использования
 */
function codeweber_enqueue_ajax_filter() {
	// Prefer dist version, fallback to src
	$dist_path = codeweber_get_dist_file_path('dist/assets/js/ajax-filter.js');
	$dist_url = codeweber_get_dist_file_url('dist/assets/js/ajax-filter.js');
	
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
		codeweber_asset_version($script_path),
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

/**
 * Enqueue share buttons script
 * Подключает скрипт для плавающих кнопок социальных сетей
 */
function codeweber_enqueue_share_buttons() {
	// Prefer dist version, fallback to src
	$dist_path = codeweber_get_dist_file_path('dist/assets/js/share-buttons.js');
	$dist_url = codeweber_get_dist_file_url('dist/assets/js/share-buttons.js');
	
	if ($dist_path && $dist_url) {
		$script_path = $dist_path;
		$script_url = $dist_url;
	} else {
		// Fallback to src
		$src_path = get_template_directory() . '/src/assets/js/share-buttons.js';
		$src_url = get_template_directory_uri() . '/src/assets/js/share-buttons.js';
		
		if (file_exists($src_path)) {
			$script_path = $src_path;
			$script_url = $src_url;
		} else {
			return; // File doesn't exist
		}
	}
	
	wp_enqueue_script(
		'share-buttons',
		$script_url,
		[], // No dependencies
		codeweber_asset_version($script_path),
		true // Load in footer
	);
}
add_action('wp_enqueue_scripts', 'codeweber_enqueue_share_buttons', 20);

/**
 * Enqueue DaData address script on edit-address and checkout when enabled in Redux.
 */
function codeweber_enqueue_dadata_address() {
	$debug = defined( 'WP_DEBUG' ) && WP_DEBUG;

	if ( ! class_exists( 'WooCommerce' ) || ! class_exists( 'Redux' ) ) {
		if ( $debug ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( '[DaData] Enqueue пропущен: WooCommerce=' . ( class_exists( 'WooCommerce' ) ? 'yes' : 'no' ) . ', Redux=' . ( class_exists( 'Redux' ) ? 'yes' : 'no' ) );
		}
		return;
	}
	global $opt_name;
	$enabled = Redux::get_option( $opt_name, 'dadata_enabled' );
	if ( ! $enabled ) {
		if ( $debug ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( '[DaData] Enqueue пропущен: dadata_enabled выключен в Redux' );
		}
		return;
	}
	$scenarios = Redux::get_option( $opt_name, 'dadata_scenarios' );
	if ( ! is_array( $scenarios ) ) {
		$scenarios = array( 'edit_address' => true, 'checkout' => true );
	}
	$on_edit    = ( is_wc_endpoint_url( 'edit-address' ) || ( function_exists( 'is_account_page' ) && is_account_page() ) ) && ! empty( $scenarios['edit_address'] );
	$on_checkout = function_exists( 'is_checkout' ) && is_checkout() && ! empty( $scenarios['checkout'] );
	if ( ! $on_edit && ! $on_checkout ) {
		if ( $debug ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( '[DaData] Enqueue пропущен: не account/edit-address и не checkout. is_edit=' . ( is_wc_endpoint_url( 'edit-address' ) ? '1' : '0' ) . ', is_account=' . ( function_exists( 'is_account_page' ) && is_account_page() ? '1' : '0' ) . ', scenario_edit=' . ( ! empty( $scenarios['edit_address'] ) ? '1' : '0' ) . ', is_checkout=' . ( function_exists( 'is_checkout' ) && is_checkout() ? '1' : '0' ) . ', scenario_checkout=' . ( ! empty( $scenarios['checkout'] ) ? '1' : '0' ) );
		}
		return;
	}

	// Виджет DaData jQuery Suggestions (как в плагине dadata-ru) — из src
	$vendor_suggestions     = get_template_directory() . '/src/assets/js/dadata/jquery.suggestions.min.js';
	$vendor_suggestions_url = get_template_directory_uri() . '/src/assets/js/dadata/jquery.suggestions.min.js';
	$vendor_exists          = file_exists( $vendor_suggestions );
	if ( $vendor_exists ) {
		wp_enqueue_script(
			'dadata-jquery-suggestions',
			$vendor_suggestions_url,
			array( 'jquery' ),
			codeweber_asset_version( $vendor_suggestions ),
			true
		);
	}

	$dist_path = codeweber_get_dist_file_path( 'dist/assets/js/dadata-address.js' );
	$dist_url  = codeweber_get_dist_file_url( 'dist/assets/js/dadata-address.js' );
	if ( $dist_path && $dist_url ) {
		$script_path = $dist_path;
		$script_url  = $dist_url;
	} else {
		$src_path = get_template_directory() . '/src/assets/js/dadata-address.js';
		$src_url  = get_template_directory_uri() . '/src/assets/js/dadata-address.js';
		if ( file_exists( $src_path ) ) {
			$script_path = $src_path;
			$script_url  = $src_url;
		} else {
			return;
		}
	}

	$deps = array( 'jquery' );
	if ( wp_script_is( 'dadata-jquery-suggestions', 'registered' ) ) {
		$deps[] = 'dadata-jquery-suggestions';
	}
	wp_enqueue_script(
		'dadata-address',
		$script_url,
		$deps,
		codeweber_asset_version( $script_path ),
		true
	);

	$token               = Redux::get_option( $opt_name, 'dadata' );
	$checkout_phone_mask = (bool) Redux::get_option( $opt_name, 'dadata_checkout_phone_mask' );

	$localize = array(
		'ajaxUrl'           => admin_url( 'admin-ajax.php' ),
		'nonce'             => wp_create_nonce( 'codeweber_dadata_clean' ),
		'addressPrefix'     => 'billing',
		'isCheckout'        => $on_checkout,
		'checkoutPhoneMask' => $checkout_phone_mask,
		'messages'          => array(
			'enterAddress' => __( 'Введите адрес в поле «Адрес» и нажмите кнопку проверки.', 'codeweber' ),
			'loading'      => __( 'Проверка…', 'codeweber' ),
			'error'        => __( 'Ошибка сети. Попробуйте позже.', 'codeweber' ),
		),
	);
	if ( ! empty( $token ) ) {
		$localize['dadataToken'] = $token;
	}
	if ( $debug ) {
		$localize['debug'] = true;
		$localize['debugEnqueue'] = array(
			'on_edit'           => $on_edit,
			'on_checkout'       => $on_checkout,
			'vendor_script'     => $vendor_exists ? 'enqueued' : 'file_not_found',
			'token_set'         => ! empty( $token ),
			'script_source'     => ( $dist_path && $dist_url ) ? 'dist' : 'src',
			'dadata_address_js' => isset( $script_url ) ? $script_url : '',
		);
		if ( ! $vendor_exists ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( '[DaData] Файл виджета не найден: ' . $vendor_suggestions );
		}
	}
	wp_localize_script( 'dadata-address', 'codeweberDadata', $localize );
}
add_action( 'wp_enqueue_scripts', 'codeweber_enqueue_dadata_address', 25 );

// ── Gutenberg: CSS для класса cwgb-grid-gap-theme ───────────────────────────
add_action(
	'wp_head',
	function () {
		if ( ! class_exists( 'Codeweber_Options' ) ) {
			return;
		}
		$classes = Codeweber_Options::style( 'grid-gap' );
		if ( ! $classes ) {
			return;
		}

		// Карта spacer-значений темы (соответствует Bootstrap-переменным).
		$spacers = [
			0 => '0px', 1 => '5px', 2 => '10px', 3 => '15px', 4 => '20px',
			5 => '25px', 6 => '30px', 7 => '35px', 8 => '40px', 9 => '45px',
			10 => '50px', 11 => '60px', 12 => '70px', 13 => '80px',
		];
		$breakpoints = [
			'sm' => '576px', 'md' => '768px', 'lg' => '992px',
			'xl' => '1200px', 'xxl' => '1400px',
		];

		$base_css  = [];
		$media_css = [];

		foreach ( explode( ' ', $classes ) as $token ) {
			if ( preg_match( '/^g-(\d+)$/', $token, $m ) ) {
				$v = $spacers[ (int) $m[1] ] ?? null;
				if ( $v ) { $base_css['--bs-gutter-x'] = $v; $base_css['--bs-gutter-y'] = $v; }
			} elseif ( preg_match( '/^gx-(\d+)$/', $token, $m ) ) {
				$v = $spacers[ (int) $m[1] ] ?? null;
				if ( $v ) { $base_css['--bs-gutter-x'] = $v; }
			} elseif ( preg_match( '/^gy-(\d+)$/', $token, $m ) ) {
				$v = $spacers[ (int) $m[1] ] ?? null;
				if ( $v ) { $base_css['--bs-gutter-y'] = $v; }
			} elseif ( preg_match( '/^g-(sm|md|lg|xl|xxl)-(\d+)$/', $token, $m ) ) {
				$v = $spacers[ (int) $m[2] ] ?? null;
				if ( $v ) { $media_css[ $m[1] ]['--bs-gutter-x'] = $v; $media_css[ $m[1] ]['--bs-gutter-y'] = $v; }
			} elseif ( preg_match( '/^gx-(sm|md|lg|xl|xxl)-(\d+)$/', $token, $m ) ) {
				$v = $spacers[ (int) $m[2] ] ?? null;
				if ( $v ) { $media_css[ $m[1] ]['--bs-gutter-x'] = $v; }
			} elseif ( preg_match( '/^gy-(sm|md|lg|xl|xxl)-(\d+)$/', $token, $m ) ) {
				$v = $spacers[ (int) $m[2] ] ?? null;
				if ( $v ) { $media_css[ $m[1] ]['--bs-gutter-y'] = $v; }
			}
		}

		if ( empty( $base_css ) && empty( $media_css ) ) {
			return;
		}

		$css = '.cwgb-grid-gap-theme {';
		foreach ( $base_css as $prop => $val ) {
			$css .= ' ' . $prop . ': ' . $val . ';';
		}
		$css .= ' }' . "
";

		foreach ( $media_css as $bp => $props ) {
			$css .= '@media (min-width: ' . esc_attr( $breakpoints[ $bp ] ) . ') { .cwgb-grid-gap-theme {';
			foreach ( $props as $prop => $val ) {
				$css .= ' ' . $prop . ': ' . $val . ';';
			}
			$css .= ' } }' . "
";
		}

		echo '<style id="cwgb-grid-gap-theme">' . "
" . $css . '</style>' . "
";
	},
	99
);
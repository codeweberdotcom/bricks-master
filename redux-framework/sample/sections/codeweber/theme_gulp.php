<?php
// theme_gulp.php

// Функция для сохранения SCSS в файл при сохранении настроек Redux
function save_scss_to_file($options, $changed_values)
{
	global $opt_name;

	// Получаем текущие значения опций
	$redux_options = get_option($opt_name);

	// Проверяем, есть ли поле с SCSS кодом
	if (isset($redux_options['opt-gulp-sass-variation'])) {
		$scss_content = $redux_options['opt-gulp-sass-variation'];

		// Путь к файлу _user-variables.scss
		$file_path = get_template_directory() . '/src/assets/scss/_user-variables.scss';

		// Проверяем возможность записи
		if (file_exists($file_path) && !is_writable($file_path)) {
			error_log('SCSS файл недоступен для записи: ' . $file_path);
			return;
		}

		// Создаем директорию, если она не существует
		$dir_path = dirname($file_path);
		if (!file_exists($dir_path)) {
			wp_mkdir_p($dir_path);
		}

		// Безопасно сохраняем в файл
		if (file_put_contents($file_path, $scss_content) === false) {
			error_log('Ошибка записи в SCSS файл: ' . $file_path);
		}
	}
}

// Регистрируем хуки для сохранения SCSS файла
add_action('redux/options/redux_demo/saved', 'save_scss_to_file', 10, 2);
add_action('redux/options/redux_demo/reset', 'save_scss_to_file', 10, 2);
add_action('redux/options/redux_demo/section/reset', 'save_scss_to_file', 10, 2);

// Подключаем скрипт в админке и передаём данные для AJAX
add_action('admin_enqueue_scripts', function ($hook) {
	wp_enqueue_script(
		'gulp-build-trigger',
		get_template_directory_uri() . '/admin/build-trigger.js',
		['jquery'],
		null,
		true
	);

	wp_localize_script('gulp-build-trigger', 'gulpBuildAjax', [
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce'    => wp_create_nonce('gulp_build_nonce'),
	]);
});

// AJAX обработчики для всех кнопок
add_action('wp_ajax_run_gulp_build', 'run_gulp_build_callback');
add_action('wp_ajax_run_gulp_dev', 'run_gulp_dev_callback');
add_action('wp_ajax_run_gulp_dist', 'run_gulp_dist_callback');
add_action('wp_ajax_run_gulp_css', 'run_gulp_css_callback');
add_action('wp_ajax_run_gulp_js', 'run_gulp_js_callback');

// Общая функция для выполнения Gulp команд
function run_gulp_command($command)
{
	$theme_path = get_template_directory();
	$output = array();
	$return_var = 0;

	// Переходим в директорию темы и выполняем команду
	chdir($theme_path);
	exec($command . ' 2>&1', $output, $return_var);

	return [
		'success' => $return_var === 0,
		'output' => $output
	];
}

// Функция для записи SCSS переменных в файл
function write_scss_variables()
{
	global $opt_name;
	$global_header_model = Redux::get_option($opt_name, 'opt-gulp-sass-variation');

	// Путь к файлу _user-variables.scss
	$scss_file_path = get_template_directory() . '/src/assets/scss/_user-variables.scss';

	// Проверяем, существует ли файл
	if (!file_exists($scss_file_path)) {
		return ['success' => false, 'message' => 'Файл _user-variables.scss не найден'];
	}

	// Перезаписываем файл содержимым из поля Redux или пустой строкой
	$file_content = $global_header_model ?? '';
	$write_result = file_put_contents($scss_file_path, $file_content);

	if ($write_result === false) {
		return ['success' => false, 'message' => 'Не удалось записать в файл'];
	}

	return ['success' => true];
}

// Обработчики для каждой кнопки
function run_gulp_build_callback()
{
	if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce($_POST['_ajax_nonce'], 'gulp_build_nonce')) {
		wp_send_json_error(['message' => 'Nonce verification failed.']);
		return;
	}

	// Сначала записываем SCSS переменные
	$write_result = write_scss_variables();
	if (!$write_result['success']) {
		wp_send_json_error(['message' => $write_result['message']]);
		return;
	}

	// Затем выполняем Gulp команду
	$result = run_gulp_command('gulp build:dist');
	if ($result['success']) {
		wp_send_json_success(['output' => $result['output']]);
	} else {
		wp_send_json_error(['output' => $result['output']]);
	}
}

function run_gulp_dev_callback()
{
	if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce($_POST['_ajax_nonce'], 'gulp_build_nonce')) {
		wp_send_json_error(['message' => 'Nonce verification failed.']);
		return;
	}

	// Сначала записываем SCSS переменные
	$write_result = write_scss_variables();
	if (!$write_result['success']) {
		wp_send_json_error(['message' => $write_result['message']]);
		return;
	}

	$result = run_gulp_command('gulp build:dev');
	if ($result['success']) {
		wp_send_json_success(['output' => $result['output']]);
	} else {
		wp_send_json_error(['output' => $result['output']]);
	}
}

function run_gulp_dist_callback()
{
	if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce($_POST['_ajax_nonce'], 'gulp_build_nonce')) {
		wp_send_json_error(['message' => 'Nonce verification failed.']);
		return;
	}

	// Сначала записываем SCSS переменные
	$write_result = write_scss_variables();
	if (!$write_result['success']) {
		wp_send_json_error(['message' => $write_result['message']]);
		return;
	}

	$result = run_gulp_command('gulp build:dist');
	if ($result['success']) {
		wp_send_json_success(['output' => $result['output']]);
	} else {
		wp_send_json_error(['output' => $result['output']]);
	}
}

function run_gulp_css_callback()
{
	if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce($_POST['_ajax_nonce'], 'gulp_build_nonce')) {
		wp_send_json_error(['message' => 'Nonce verification failed.']);
		return;
	}

	// Сначала записываем SCSS переменные
	$write_result = write_scss_variables();
	if (!$write_result['success']) {
		wp_send_json_error(['message' => $write_result['message']]);
		return;
	}

	$result = run_gulp_command('gulp css:dist && gulp fontcss:dist && gulp colorcss:dist && gulp vendorcss:dist');
	if ($result['success']) {
		wp_send_json_success(['output' => $result['output']]);
	} else {
		wp_send_json_error(['output' => $result['output']]);
	}
}

function run_gulp_js_callback()
{
	if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce($_POST['_ajax_nonce'], 'gulp_build_nonce')) {
		wp_send_json_error(['message' => 'Nonce verification failed.']);
		return;
	}

	// Для JS не нужно записывать SCSS переменные, но оставляем для единообразия
	$write_result = write_scss_variables();
	if (!$write_result['success']) {
		wp_send_json_error(['message' => $write_result['message']]);
		return;
	}

	$result = run_gulp_command('gulp pluginsjs:dist && gulp themejs:dist && gulp restapijs:dist');
	if ($result['success']) {
		wp_send_json_success(['output' => $result['output']]);
	} else {
		wp_send_json_error(['output' => $result['output']]);
	}
}

// Функция для получения пути к файлу шрифта
function redux_get_font_file_path($filename)
{
	$theme_path = get_template_directory() . '/src/assets/scss/fonts/';

	if (substr($filename, -5) !== '.scss') {
		$filename .= '.scss';
	}

	return $theme_path . $filename;
}

// Функция для загрузки содержимого файла
function redux_load_font_file_content($filename)
{
	$file_path = redux_get_font_file_path($filename);

	if (file_exists($file_path)) {
		return file_get_contents($file_path);
	}

	return '';
}

// Функция для сохранения содержимого в файл
function redux_save_font_file_content($filename, $content)
{
	$file_path = redux_get_font_file_path($filename);
	return file_put_contents($file_path, $content);
}

// Функция для сохранения файла при сохранении настроек Redux
function redux_save_font_file_on_save($options, $changed_values)
{
	global $opt_name;

	// Получаем текущие значения опций
	$redux_options = get_option($opt_name);

	// Проверяем, есть ли имя файла и содержимое
	if (!empty($redux_options['fonts_combanation']) && !empty($redux_options['opt-font-variation'])) {
		$filename = $redux_options['fonts_combanation'];
		$content = $redux_options['opt-font-variation'];

		// Сохраняем в файл
		$result = redux_save_font_file_content($filename, $content);
	}
}

// Регистрируем хук для сохранения файлов шрифтов
add_action('redux/options/redux_demo/saved', 'redux_save_font_file_on_save', 10, 2);

$gulp_buttons_html = '
<div class="wrap gulp-buttons-container">
    <div class="gulp-buttons-row">
        <button id="run-gulp-dev" class="button button-primary gulp-button">
            <span class="dashicons dashicons-hammer"></span>
            ' . esc_html__('Build DEV', 'codeweber') . '
        </button>

        <button id="run-gulp-dist" class="button button-secondary gulp-button">
            <span class="dashicons dashicons-performance"></span>
            ' . esc_html__('Build DIST', 'codeweber') . '
        </button>

        <button id="run-gulp-css" class="button button-secondary gulp-button">
            <span class="dashicons dashicons-art"></span>
            ' . esc_html__('Only CSS', 'codeweber') . '
        </button>

        <button id="run-gulp-js" class="button button-secondary gulp-button">
            <span class="dashicons dashicons-editor-code"></span>
            ' . esc_html__('Only JS', 'codeweber') . '
        </button>

        <button id="clear-gulp-log" class="button button-link gulp-button">
            ' . esc_html__('Clear LOG', 'codeweber') . '
        </button>
    </div>

    <div id="gulp-build-log" style="margin-top:20px; padding:15px; background:#f1f1f1; display:none; max-height:400px; overflow:auto; font-family: monospace; font-size: 12px; line-height: 1.4; border: 1px solid #ddd; border-radius: 4px;"></div>
</div>

<style>
.gulp-buttons-container {
    margin: 20px 0;
}

.gulp-buttons-row {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 15px;
    align-items: center;
}

.gulp-button {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 140px;
    justify-content: center;
    height: 36px;
}

.gulp-button .dashicons {
    margin: 0;
    font-size: 16px;
    width: 16px;
    height: 16px;
}

.button-primary.gulp-button {
    background: #2271b1;
    border-color: #2271b1;
    color: white;
}

.button-secondary.gulp-button {
    background: #f6f7f7;
    border-color: #dcdcde;
    color: #3c434a;
}

.button-secondary.gulp-button:hover {
    background: #fff;
    border-color: #2271b1;
    color: #2271b1;
}

.button-link.gulp-button {
    color: #a7aaad;
    border: 1px solid #dcdcde;
    background: transparent;
}

.button-link.gulp-button:hover {
    color: #2271b1;
    border-color: #2271b1;
    background: transparent;
}

#gulp-build-log {
    font-family: "Courier New", Monaco, Menlo, "Ubuntu Mono", consolas, monospace;
    font-size: 12px;
    line-height: 1.4;
    border: 1px solid #ddd;
    border-radius: 4px;
    white-space: pre-wrap;
}

.success-log {
    border-left: 4px solid #46b450;
    background: #f7fcf7 !important;
}

.error-log {
    border-left: 4px solid #dc3232;
    background: #fcf0f1 !important;
}

.processing-log {
    border-left: 4px solid #00a0d2;
    background: #f0f6fc !important;
}

.spinner {
    float: none;
    margin: 0 8px 0 0;
}

.gulp-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.gulp-buttons-row button {
    display: flex!important;
}
</style>';

return array(
	array(
		'id'       => 'fonts_combanation',
		'type'     => 'text',
		'title'    => esc_html__('Font File Name', 'codeweber'),
		'subtitle' => esc_html__('Enter the font file name (with or without .scss extension)', 'codeweber'),
		'default'  => '',
		'readonly' => true
	),

	array(
		'id'       => 'opt-gulp-sass-variation',
		'type'     => 'ace_editor',
		'title'    => esc_html__('Custom SCSS / Code', 'codeweber'),
		'subtitle' => esc_html__('In this window, you can add SCSS code that will be written to the GULP _user-variables.scss file during the build process.', 'codeweber'),
		'mode'     => 'scss',
		'theme'    => 'monokai',
		'default'  => '',
		'args'     => array(
			'minLines' => 15,
			'maxLines' => 30,
		),
	),

	array(
		'id'       => 'opt-font-variation',
		'type'     => 'ace_editor',
		'title'    => esc_html__('Font individual / Code', 'codeweber'),
		'subtitle' => esc_html__("After adding a font, this field will display a code for managing the font's individual properties.", "codeweber"),
		'mode'     => 'scss',
		'theme'    => 'monokai',
		'default'  => '',
		'args'     => array(
			'minLines' => 15,
			'maxLines' => 30,
		),
	),

	array(
		'id'       => 'opt-gulp-js-variation',
		'type'     => 'ace_editor',
		'title'    => esc_html__('Gulp JS Variables / Code', 'codeweber'),
		'mode'     => 'js',
		'theme'    => 'monokai',
		'default'  => '',
		'args'     => array(
			'minLines' => 15,
			'maxLines' => 30,
		),
	),

	array(
		'id' => 'opt-raw_info_4333',
		'type' => 'raw',
		'title' => esc_html__('Gulp Run', 'codeweber'),
		'desc' => esc_html__('Before starting the build Gulp, save the Redux settings.', 'codeweber'),
		'content' => $gulp_buttons_html,
	),
);

<?php
// theme_gulp.php

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
add_action('redux/options/redux_demo/saved', 'redux_save_font_file_on_save', 10, 2);
function redux_save_font_file_on_save($options, $changed_values)
{
	$opt_name = 'redux_demo'; // Замените на ваше имя опции Redux

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

$sample_html = '<div class="wrap">
    <button id="run-gulp-build" class="button button-primary">Собрать CSS и JS</button>
    <pre id="gulp-build-log" style="margin-top:10px; padding:10px; background:#f1f1f1; display:none;"></pre>
</div>';

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
		'content' => $sample_html,
	),
);

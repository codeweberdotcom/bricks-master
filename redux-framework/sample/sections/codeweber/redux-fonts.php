<?php
// redux-fonts.php

// AJAX обработчики
add_action('wp_ajax_redux_upload_custom_font', 'redux_handle_custom_font_upload');
add_action('wp_ajax_redux_apply_custom_font', 'redux_handle_apply_custom_font');
add_action('wp_ajax_redux_delete_custom_font', 'redux_handle_custom_font_delete');

function redux_handle_custom_font_upload()
{
	check_ajax_referer('redux_custom_fonts_nonce', 'nonce');

	if (!current_user_can('manage_options')) {
		wp_die(__('Unauthorized', 'codeweber'));
	}

	$font_name = sanitize_text_field($_POST['font_name']);
	$font_files = $_FILES['font_files'];

	if (empty($font_name) || empty($font_files)) {
		wp_send_json_error(__('Font name and files are required', 'codeweber'));
	}

	$fonts_dir = get_template_directory() . '/src/assets/fonts/';
	$font_dir = $fonts_dir . $font_name . '/';

	// Создаем директории если не существуют
	if (!file_exists($fonts_dir)) {
		wp_mkdir_p($fonts_dir);
	}
	if (!file_exists($font_dir)) {
		wp_mkdir_p($font_dir);
	}

	// Обрабатываем загруженные файлы
	$css_content = '';
	$has_woff_files = false;

	foreach ($font_files['name'] as $index => $name) {
		if ($font_files['error'][$index] === UPLOAD_ERR_OK) {
			$file_tmp = $font_files['tmp_name'][$index];
			$file_name = sanitize_file_name($name);
			$file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

			// Разрешаем только woff, woff2 и css
			if (!in_array($file_ext, ['woff', 'woff2', 'css'])) {
				continue;
			}

			$destination = $font_dir . $file_name;

			if (move_uploaded_file($file_tmp, $destination)) {
				// Если это CSS файл, читаем его содержимое
				if ($file_ext === 'css') {
					$css_content = file_get_contents($destination);
				} else {
					$has_woff_files = true;
				}
			}
		}
	}

	// Если нет CSS файла, но есть woff файлы - генерируем CSS
	if (empty($css_content) && $has_woff_files) {
		$css_content = redux_generate_font_css($font_name, $font_dir);
		file_put_contents($font_dir . $font_name . '.css', $css_content);

		// Формируем сообщение о созданных файлах
		$message = __('Font uploaded successfully!', 'codeweber');
		$message .= '<br><strong>' . __('Created files:', 'codeweber') . '</strong>';
		$message .= '<br>• ' . $font_dir . $font_name . '.css';

		wp_send_json_success($message);
	}

	wp_send_json_success(__('Font uploaded successfully!', 'codeweber'));
}

function redux_handle_apply_custom_font()
{
	check_ajax_referer('redux_custom_fonts_nonce', 'nonce');

	if (!current_user_can('manage_options')) {
		wp_die(__('Unauthorized', 'codeweber'));
	}

	$selected_fonts = isset($_POST['selected_fonts']) ? $_POST['selected_fonts'] : array();
	$primary_font = isset($_POST['primary_font']) ? sanitize_text_field($_POST['primary_font']) : '';
	global $opt_name;

	if (empty($selected_fonts)) {
		wp_send_json_error(__('No fonts selected', 'codeweber'));
	}

	try {
		// Создаем SCSS файл с настройками шрифтов
		$result = redux_create_font_scss($selected_fonts, $primary_font);
		global $opt_name;
		// Проверяем, вернулась ли ошибка
		if (is_wp_error($result)) {
			if ($result->get_error_code() === 'file_exists') {
				wp_send_json_error($result->get_error_message());
			} else {
				wp_send_json_error(__('Error creating SCSS file: ', 'codeweber') . $result->get_error_message());
			}
		}

		$scss_filename = $result;

		// Получаем полное содержимое SCSS файла
		$scss_dir = get_template_directory() . '/src/assets/scss/fonts/';
		$scss_file = $scss_dir . $scss_filename;

		if (file_exists($scss_file)) {
			$font_variation_value = file_get_contents($scss_file);

			// Сохраняем полный SCSS код в поле opt-font-variation
			Redux::set_option($opt_name, 'opt-font-variation', $font_variation_value);

			// Также обновляем импорт в основном поле (если нужно)
			$current_value = Redux::get_option($opt_name, 'opt-gulp-sass-variation');
			$pattern = '~// FONTS START.*?// FONTS END~s';
			$cleaned_value = preg_replace($pattern, '', $current_value);
			$cleaned_value = preg_replace('/\n{3,}/', "\n\n", $cleaned_value);
			$cleaned_value = ltrim($cleaned_value);

			$font_import_name = pathinfo($scss_filename, PATHINFO_FILENAME);
			$updated_value = redux_update_font_import_in_redux($cleaned_value, $font_import_name);

			Redux::set_option($opt_name, 'opt-gulp-sass-variation', $updated_value);

			// ВАЖНО: Обновляем поле fonts_combanation с именем созданного файла
			Redux::set_option($opt_name, 'fonts_combanation', $scss_filename);
		}

		// Сообщение с предложением перезагрузить страницу
		$success_message = __('Fonts applied successfully! SCSS file: ', 'codeweber') . $scss_filename;
		$success_message .= '<br><strong>' . __('Please reload the page to see the changes.', 'codeweber') . '</strong>';

		wp_send_json_success($success_message);
	} catch (Exception $e) {
		error_log('Error in redux_handle_apply_custom_font: ' . $e->getMessage());
		wp_send_json_error(__('Error saving fonts: ', 'codeweber') . $e->getMessage());
	}
}

// Функция для обновления импорта шрифтов в поле Redux
function redux_update_font_import_in_redux($current_value, $font_import_name)
{
	// Паттерн для поиска существующего импорта
	$pattern = '~//START IMPORT FONTS.*?//END IMPORT FONTS~s';

	// Новый контент импорта
	$new_import = "//START IMPORT FONTS\n@import \"fonts/{$font_import_name}\";\n//END IMPORT FONTS";

	if (preg_match($pattern, $current_value)) {
		// Заменяем существующий импорт
		$new_content = preg_replace($pattern, $new_import, $current_value);
	} else {
		// Добавляем новый импорт в конец значения
		$new_content = trim($current_value) . "\n\n" . $new_import . "\n";
	}

	// Очищаем лишние переносы строк
	$new_content = preg_replace('/\n{3,}/', "\n\n", $new_content);
	$new_content = trim($new_content);

	return $new_content;
}

function redux_handle_custom_font_delete()
{
	check_ajax_referer('redux_custom_fonts_nonce', 'nonce');

	if (!current_user_can('manage_options')) {
		wp_die(__('Unauthorized', 'codeweber'));
	}

	$font_name = sanitize_text_field($_POST['font_name']);

	// Защищенные шрифты которые нельзя удалять
	$protected_fonts = array('space', 'thicccboi', 'urbanist');

	if (in_array($font_name, $protected_fonts)) {
		wp_send_json_error(__('This font is protected and cannot be deleted.', 'codeweber'));
	}

	$font_dir = get_template_directory() . '/src/assets/fonts/' . $font_name . '/';

	if (!file_exists($font_dir)) {
		wp_send_json_error(__('Font directory not found', 'codeweber'));
	}

	// Удаляем всю папку со шрифтом
	$files = glob($font_dir . '*');
	foreach ($files as $file) {
		if (is_file($file)) {
			unlink($file);
		}
	}
	rmdir($font_dir);

	// Удаляем импорт из поля Redux
	redux_remove_font_import_from_redux();

	wp_send_json_success(__('Font deleted successfully!', 'codeweber'));
}

// Функция для удаления импорта шрифтов из поля Redux
function redux_remove_font_import_from_redux()
{
	global $opt_name;

	try {
		// Получаем текущее значение
		$current_value = Redux::get_option($opt_name, 'opt-gulp-sass-variation');

		// Паттерн для поиска существующего импорта
		$pattern = '~//START IMPORT FONTS.*?//END IMPORT FONTS~s';

		// Удаляем секцию импорта
		$new_content = preg_replace($pattern, '', $current_value);

		// Очищаем лишние переносы строк
		$new_content = preg_replace('/\n{3,}/', "\n\n", $new_content);
		$new_content = trim($new_content);

		// Сохраняем изменения
		Redux::set_option($opt_name, 'opt-gulp-sass-variation', $new_content);

		return true;
	} catch (Exception $e) {
		error_log('Error removing font import from Redux: ' . $e->getMessage());
		return false;
	}
}


// Функция для получения текущего импортированного шрифта из поля Redux
function redux_get_current_font_import_from_redux()
{
	global $opt_name;

	try {
		$current_value = Redux::get_option($opt_name, 'opt-gulp-sass-variation');

		// Паттерн для поиска импорта
		$pattern = '~//START IMPORT FONTS\s+@import "fonts/([^"]+)";\s+//END IMPORT FONTS~s';

		if (preg_match($pattern, $current_value, $matches)) {
			return $matches[1]; // Возвращаем имя файла без расширения
		}

		return null;
	} catch (Exception $e) {
		error_log('Error getting font import from Redux: ' . $e->getMessage());
		return null;
	}
}

function redux_generate_font_css($font_name, $font_dir)
{
	$files = scandir($font_dir);
	$css = '';
	$font_variants = array();

	foreach ($files as $file) {
		if ($file === '.' || $file === '..') continue;

		$file_path = $font_dir . $file;
		$file_ext = pathinfo($file, PATHINFO_EXTENSION);

		if (in_array($file_ext, ['woff', 'woff2'])) {
			$file_name = pathinfo($file, PATHINFO_FILENAME);
			$weight = redux_extract_font_weight($file_name);
			$style = redux_extract_font_style($file_name);

			$key = $weight . '-' . $style;

			if (!isset($font_variants[$key])) {
				$font_variants[$key] = array(
					'weight' => $weight,
					'style' => $style,
					'formats' => array()
				);
			}

			$font_variants[$key]['formats'][$file_ext] = $file;
		}
	}

	foreach ($font_variants as $variant) {
		$src = array();
		if (isset($variant['formats']['woff2'])) {
			$src[] = "url('{$variant['formats']['woff2']}') format('woff2')";
		}
		if (isset($variant['formats']['woff'])) {
			$src[] = "url('{$variant['formats']['woff']}') format('woff')";
		}

		if (!empty($src)) {
			$css .= "@font-face {
  font-family: '{$font_name}';
  src: " . implode(",\n    ", $src) . ";
  font-weight: {$variant['weight']};
  font-style: {$variant['style']};
  font-display: swap;
}\n";
		}
	}

	return $css;
}

function redux_create_font_scss($selected_fonts, $primary_font)
{
	$scss_dir = get_template_directory() . '/src/assets/scss/fonts/';

	if (!file_exists($scss_dir)) {
		wp_mkdir_p($scss_dir);
	}

	// Формируем имя файла
	if (count($selected_fonts) === 1) {
		$scss_filename = $primary_font . '.scss';
	} else {
		// Сортируем шрифты: primary первый, остальные после
		$other_fonts = array_diff($selected_fonts, [$primary_font]);
		$scss_filename = $primary_font . '_' . implode('_', $other_fonts) . '.scss';
	}

	$scss_file = $scss_dir . $scss_filename;
	global $opt_name;

	// Проверяем, существует ли файл
	if (file_exists($scss_file)) {
		// Файл существует - просто возвращаем имя файла без перезаписи
		Redux::set_option($opt_name, 'fonts_combanation', $scss_filename);
		return $scss_filename;
	}

	// Файл не существует - создаем новый
	$scss_content = "// Font Import\n\n";

	// ... [остальная часть генерации SCSS контента без изменений] ...
	// Массив для хранения настоящих имен шрифтов
	$real_font_names = array();

	// Добавляем все font-face правила для каждого выбранного шрифта
	// ВАЖНО: Сначала добавляем primary шрифт, потом остальные
	$ordered_fonts = array_merge([$primary_font], array_diff($selected_fonts, [$primary_font]));

	foreach ($ordered_fonts as $font_name) {
		$font_dir = get_template_directory() . '/src/assets/fonts/' . $font_name . '/';
		$font_css_path = $font_dir . $font_name . '.css';

		if (file_exists($font_css_path)) {
			// Если есть CSS файл - читаем его и изменяем пути
			$css_content = file_get_contents($font_css_path);

			// Извлекаем настоящее имя шрифта из CSS
			if (preg_match('/font-family: [\'"]([^\'"]+)[\'"]/', $css_content, $matches)) {
				$real_font_names[$font_name] = $matches[1];
			}

			$modified_css = preg_replace_callback(
				'/url\(\'([^\']+)\'\)/',
				function ($matches) use ($font_name) {
					$old_path = $matches[1];
					return "url('../fonts/{$font_name}/" . basename($old_path) . "')";
				},
				$css_content
			);

			$scss_content .= $modified_css . "\n";
		} else {
			// Если нет CSS файла - генерируем font-face из woff/woff2 файлов
			$font_files = scandir($font_dir);
			$font_variants = array();
			$real_font_name = $font_name; // По умолчанию используем имя папки

			foreach ($font_files as $file) {
				if ($file === '.' || $file === '..') continue;

				$file_path = $font_dir . $file;
				$file_ext = pathinfo($file, PATHINFO_EXTENSION);

				if (in_array($file_ext, ['woff', 'woff2'])) {
					// Пытаемся извлечь настоящее имя шрифта из WOFF файла
					if ($real_font_name === $font_name) {
						$extracted_name = redux_extract_font_name_from_woff($file_path);
						if ($extracted_name) {
							$real_font_name = $extracted_name;
						}
					}

					$file_name = pathinfo($file, PATHINFO_FILENAME);
					$weight = redux_extract_font_weight($file_name);
					$style = redux_extract_font_style($file_name);

					$key = $weight . '-' . $style;

					if (!isset($font_variants[$key])) {
						$font_variants[$key] = array(
							'weight' => $weight,
							'style' => $style,
							'formats' => array()
						);
					}

					$font_variants[$key]['formats'][$file_ext] = $file;
				}
			}

			// Сохраняем настоящее имя шрифта
			$real_font_names[$font_name] = $real_font_name;

			foreach ($font_variants as $variant) {
				$src = array();
				if (isset($variant['formats']['woff2'])) {
					$src[] = "url('../fonts/{$font_name}/{$variant['formats']['woff2']}') format('woff2')";
				}
				if (isset($variant['formats']['woff'])) {
					$src[] = "url('../fonts/{$font_name}/{$variant['formats']['woff']}') format('woff')";
				}

				if (!empty($src)) {
					// Используем настоящее имя шрифта для font-family
					$scss_content .= "@font-face {
  font-family: '{$real_font_name}';
  src: " . implode(",\n    ", $src) . ";
  font-weight: {$variant['weight']};
  font-style: {$variant['style']};
  font-display: swap;
}\n";
				}
			}
		}
	}

	$scss_content .= "\n// Bootstrap Configuration\n";
	$scss_content .= "@import \"../../../../node_modules/bootstrap/scss/mixins\";\n\n";

	// Font Variables
	$scss_content .= "// Font Variables\n";

	// Используем настоящие имена шрифтов из WOFF файлов
	$primary_real_name = isset($real_font_names[$primary_font]) ? $real_font_names[$primary_font] : $primary_font;
	$scss_content .= "\$font-family-base: \"{$primary_real_name}\", sans-serif;\n";

	// Определяем secondary шрифт (первый не-primary шрифт из выбранных)
	$secondary_fonts = array_diff($selected_fonts, [$primary_font]);

	if (!empty($secondary_fonts)) {
		$secondary_font = array_values($secondary_fonts)[0];
		$secondary_real_name = isset($real_font_names[$secondary_font]) ? $real_font_names[$secondary_font] : $secondary_font;
		$scss_content .= "\$font-family-secondary: \"{$secondary_real_name}\", sans-serif;\n";
	} else {
		// Для одного шрифта используем тот же шрифт как secondary
		$scss_content .= "\$font-family-secondary: \"{$primary_real_name}\", sans-serif;\n";
	}

	// Добавляем переменные для дополнительных шрифтов (начиная с 3-го)
	$font_counter = 3;
	foreach ($secondary_fonts as $index => $font_name) {
		if ($index > 0) { // Пропускаем первый secondary (уже добавлен)
			$font_real_name = isset($real_font_names[$font_name]) ? $real_font_names[$font_name] : $font_name;
			$scss_content .= "\$font-family-{$font_counter}: \"{$font_real_name}\", sans-serif;\n";
			$font_counter++;
		}
	}

	$scss_content .= "\$font-size-base: 0.85rem;\n\n";

	// Font Specific Settings
	$scss_content .= "// Font Specific Settings\n";
	$scss_content .= "* {\n";
	$scss_content .= "  word-spacing: normal !important;\n";
	$scss_content .= "}\n";
	$scss_content .= "body {\n";
	$scss_content .= "  font-family: \$font-family-base;\n";
	$scss_content .= "  font-size: \$font-size-base;\n";
	$scss_content .= "}\n";
	$scss_content .= ".nav-link,\n";
	$scss_content .= ".dropdown-item,\n";
	$scss_content .= ".btn {\n";
	$scss_content .= "  letter-spacing: normal;\n";
	$scss_content .= "}\n";
	$scss_content .= ".btn,\n";
	$scss_content .= ".navbar .btn-sm,\n";
	$scss_content .= ".nav-link,\n";
	$scss_content .= ".nav-link p,\n";
	$scss_content .= ".lg-sub-html p {\n";
	$scss_content .= "  @include font-size(\$font-size-base);\n";
	$scss_content .= "}\n";
	$scss_content .= ".dropdown-menu {\n";
	$scss_content .= "  @include font-size(\$font-size-base - 0.05);\n";
	$scss_content .= "}\n";
	$scss_content .= ".share-dropdown .dropdown-menu .dropdown-item,\n";
	$scss_content .= ".btn-sm,\n";
	$scss_content .= ".btn-group-sm>.btn,\n";
	$scss_content .= ".post-meta {\n";
	$scss_content .= "  @include font-size(\$font-size-base - 0.1);\n";
	$scss_content .= "}\n";
	$scss_content .= ".meta,\n";
	$scss_content .= ".post-category,\n";
	$scss_content .= ".filter,\n";
	$scss_content .= ".filter ul li a {\n";
	$scss_content .= "  @include font-size(\$font-size-base - 0.15);\n";
	$scss_content .= "}\n";
	$scss_content .= ".post-header .post-meta {\n";
	$scss_content .= "  @include font-size(\$font-size-base);\n";
	$scss_content .= "}\n";
	$scss_content .= ".nav-tabs .nav-link,\n";
	$scss_content .= ".accordion-wrapper .card-header button,\n";
	$scss_content .= ".collapse-link {\n";
	$scss_content .= "  @include font-size(\$font-size-base + 0.05);\n";
	$scss_content .= "}\n";
	$scss_content .= "blockquote {\n";
	$scss_content .= "  @include font-size(\$font-size-base + 0.05);\n";
	$scss_content .= "}\n";
	$scss_content .= ".blockquote-footer {\n";
	$scss_content .= "  @include font-size(\$font-size-base - 0.2);\n";
	$scss_content .= "}\n";
	$scss_content .= ".blockquote-details p {\n";
	$scss_content .= "  @include font-size(\$font-size-base);\n";
	$scss_content .= "}\n";
	$scss_content .= ".counter-wrapper {\n";
	$scss_content .= "  p {\n";
	$scss_content .= "    @include font-size(\$font-size-base);\n";
	$scss_content .= "  }\n";
	$scss_content .= "  .counter {\n";
	$scss_content .= "    @include font-size(2.05rem);\n";
	$scss_content .= "    &.counter-lg {\n";
	$scss_content .= "      @include font-size(2.25rem)\n";
	$scss_content .= "    }\n";
	$scss_content .= "  }\n";
	$scss_content .= "}\n";
	$scss_content .= ".icon-list.bullet-bg i {\n";
	$scss_content .= "  top: 0.25rem;\n";
	$scss_content .= "}\n";
	$scss_content .= ".accordion-wrapper .card-header button:before {\n";
	$scss_content .= "  margin-top: -0.2rem;\n";
	$scss_content .= "}\n";
	$scss_content .= ".form-floating > label {\n";
	$scss_content .= "  padding-top: 0.65rem;\n";
	$scss_content .= "}\n";
	$scss_content .= ".h1, h1 {\n";
	$scss_content .= "  @include font-size(1.5rem);\n";
	$scss_content .= "}\n";
	$scss_content .= ".h2, h2 {\n";
	$scss_content .= "  @include font-size(1.35rem);\n";
	$scss_content .= "}\n";
	$scss_content .= ".h3, h3 {\n";
	$scss_content .= "  @include font-size(1.15rem);\n";
	$scss_content .= "}\n";
	$scss_content .= ".h4, h4 {\n";
	$scss_content .= "  @include font-size(1rem);\n";
	$scss_content .= "}\n";
	$scss_content .= ".h5, h5 {\n";
	$scss_content .= "  @include font-size(0.95rem);\n";
	$scss_content .= "}\n";
	$scss_content .= ".h6, h6 {\n";
	$scss_content .= "  @include font-size(0.9rem);\n";
	$scss_content .= "}\n";
	$scss_content .= ".fs-sm {\n";
	$scss_content .= "  @include font-size(0.75rem !important);\n";
	$scss_content .= "}\n";
	$scss_content .= ".fs-lg {\n";
	$scss_content .= "  @include font-size(1.05rem !important);\n";
	$scss_content .= "}\n";
	$scss_content .= ".lead {\n";
	$scss_content .= "  @include font-size(0.95rem);\n";
	$scss_content .= "  line-height: 1.6;\n";
	$scss_content .= "}\n";
	$scss_content .= ".lead.fs-lg {\n";
	$scss_content .= "  @include font-size(1.1rem !important);\n";
	$scss_content .= "  line-height: 1.55;\n";
	$scss_content .= "}\n";

	// Display classes с font-family для secondary шрифта
	$scss_content .= ".display-1 {\n";
	$scss_content .= "  @include font-size(2.5rem);\n";
	$scss_content .= "  font-family: \$font-family-secondary;\n";
	$scss_content .= "  line-height: 1.15;\n";
	$scss_content .= "}\n";
	$scss_content .= ".display-2 {\n";
	$scss_content .= "  @include font-size(2.3rem);\n";
	$scss_content .= "  font-family: \$font-family-secondary;\n";
	$scss_content .= "  line-height: 1.2;\n";
	$scss_content .= "}\n";
	$scss_content .= ".display-3 {\n";
	$scss_content .= "  @include font-size(2.1rem);\n";
	$scss_content .= "  font-family: \$font-family-secondary;\n";
	$scss_content .= "  line-height: 1.2;\n";
	$scss_content .= "}\n";
	$scss_content .= ".display-4 {\n";
	$scss_content .= "  @include font-size(1.9rem);\n";
	$scss_content .= "  font-family: \$font-family-secondary;\n";
	$scss_content .= "  line-height: 1.25;\n";
	$scss_content .= "}\n";
	$scss_content .= ".display-5 {\n";
	$scss_content .= "  @include font-size(1.7rem);\n";
	$scss_content .= "  font-family: \$font-family-secondary;\n";
	$scss_content .= "  line-height: 1.25;\n";
	$scss_content .= "}\n";
	$scss_content .= ".display-6 {\n";
	$scss_content .= "  @include font-size(1.5rem);\n";
	$scss_content .= "  font-family: \$font-family-secondary;\n";
	$scss_content .= "  line-height: 1.3;\n";
	$scss_content .= "}\n";

	// Сохраняем файл
	if (file_put_contents($scss_file, $scss_content)) {
		Redux::set_option($opt_name, 'fonts_combanation', $scss_filename);
		return $scss_filename;
	} else {
		return new WP_Error('file_write_error', __('Error writing SCSS file: ', 'codeweber') . $scss_filename);
	}
}


// Функция для извлечения имени шрифта из WOFF файла
function redux_extract_font_name_from_woff($file_path)
{
	if (!file_exists($file_path)) {
		return false;
	}

	$handle = fopen($file_path, 'rb');
	if (!$handle) {
		return false;
	}

	// Читаем первые 1024 байта файла
	$data = fread($handle, 1024);
	fclose($handle);

	// Ищем название шрифта в WOFF файле
	if (preg_match('/name.*?font.*?name.*?[\'"]?([A-Za-z0-9\s]+)[\'"]?/i', $data, $matches)) {
		return trim($matches[1]);
	}

	// Альтернативный метод поиска имени
	if (preg_match('/FontName.*?[\'"]?([A-Za-z0-9\s]+)[\'"]?/i', $data, $matches)) {
		return trim($matches[1]);
	}

	return false;
}

function redux_extract_font_style($filename)
{
	$filename = strtolower($filename);

	if (strpos($filename, 'italic') !== false || strpos($filename, 'oblique') !== false) {
		return 'italic';
	}

	return 'normal';
}

function redux_extract_font_weight($filename)
{
	$weights = array(
		'thin' => 100,
		'extralight' => 200,
		'ultralight' => 200,
		'light' => 300,
		'regular' => 400,
		'normal' => 400,
		'medium' => 500,
		'semibold' => 600,
		'demibold' => 600,
		'bold' => 700,
		'extrabold' => 800,
		'ultrabold' => 800,
		'black' => 900,
		'heavy' => 900
	);

	$filename = strtolower($filename);

	// Удаляем italic из имени для поиска веса
	$clean_name = str_replace(array('italic', 'oblique'), '', $filename);

	foreach ($weights as $name => $weight) {
		if (strpos($clean_name, $name) !== false) {
			return $weight;
		}
	}

	return 400; // default weight
}

function redux_get_uploaded_fonts()
{
	$fonts = array();
	$fonts_dir = get_template_directory() . '/src/assets/fonts/';

	if (!file_exists($fonts_dir)) {
		return $fonts;
	}

	$folders = scandir($fonts_dir);

	// Шрифты которые нужно полностью игнорировать
	$ignore_fonts = array('unicons');

	// Шрифты для которых скрываем кнопку удаления
	$protected_fonts = array('space', 'thicccboi', 'urbanist');

	foreach ($folders as $folder) {
		if ($folder === '.' || $folder === '..') continue;

		// Пропускаем игнорируемые шрифты
		if (in_array($folder, $ignore_fonts)) {
			continue;
		}

		$font_path = $fonts_dir . $folder;
		if (is_dir($font_path)) {
			$css_file = $font_path . '/' . $folder . '.css';
			$files = scandir($font_path);

			$font_files = array();
			foreach ($files as $file) {
				if ($file !== '.' && $file !== '..' && $file !== $folder . '.css') {
					$font_files[] = $file;
				}
			}

			$fonts[$folder] = array(
				'path' => $font_path,
				'css' => file_exists($css_file),
				'files' => $font_files,
				'protected' => in_array($folder, $protected_fonts)
			);
		}
	}

	return $fonts;
}

// Генерируем HTML контент
$fonts = redux_get_uploaded_fonts();
ob_start();
?>
<div class="redux-custom-fonts-container">
	<!-- Форма загрузки -->
	<div class="font-upload-form">
		<h4><?php esc_html_e('Upload New Font', 'codeweber'); ?></h4>
		<p><?php esc_html_e('You need to download the font in two formats woff and woff2 to display in old browsers', 'codeweber'); ?></p>
		<input type="text" id="font-name" placeholder="<?php esc_attr_e('Font Family Name', 'codeweber'); ?>" class="regular-text">
		<input type="file" id="font-files" multiple accept=".woff,.woff2,.css" class="font-file-input">
		<button type="button" id="upload-font" class="button button-primary">
			<?php esc_html_e('Upload Font', 'codeweber'); ?>
		</button>
		<div id="upload-progress"></div>
	</div>

	<!-- Список загруженных шрифтов -->
	<div class="uploaded-fonts-list">
		<h4><?php esc_html_e('Uploaded Fonts', 'codeweber'); ?></h4>
		<?php if (empty($fonts)): ?>
			<p><?php esc_html_e('No fonts uploaded yet.', 'codeweber'); ?></p>
		<?php else: ?>
			<form id="fonts-selection-form">
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th class="padding-5"><?php esc_html_e('Font Name', 'codeweber'); ?></th>
							<th class="padding-5"><?php esc_html_e('Files', 'codeweber'); ?></th>
							<th class="padding-5"><?php esc_html_e('CSS', 'codeweber'); ?></th>
							<th class="padding-5"><?php esc_html_e('Select Font', 'codeweber'); ?></th>
							<th class="padding-5"><?php esc_html_e('Primary Font', 'codeweber'); ?></th>
							<th class="padding-5"><?php esc_html_e('Actions', 'codeweber'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($fonts as $font_name => $font_data): ?>
							<tr>
								<td class="padding-5"><strong><?php echo esc_html($font_name); ?></strong></td>
								<td>
									<?php
									$file_count = count($font_data['files']);
									echo esc_html(sprintf(_n('%d file', '%d files', $file_count, 'codeweber'), $file_count));
									?>
								</td>
								<td>
									<?php echo $font_data['css'] ?
										'<span class="dashicons dashicons-yes" style="color:green; font-size:20px;" title="CSS file exists"></span>' :
										'<span class="dashicons dashicons-info" style="color:orange; font-size:20px;" title="CSS file not found"></span>'; ?>
								</td>
								<td class="padding-5">
									<input type="checkbox" name="selected_fonts[]" value="<?php echo esc_attr($font_name); ?>"
										class="font-checkbox" data-font-order>
								</td>
								<td class="padding-5">
									<input type="radio" name="primary_font" value="<?php echo esc_attr($font_name); ?>" class="primary-font-radio">
								</td>
								<td class="protected-font">
									<?php if (isset($font_data['protected']) && !$font_data['protected']): ?>
										<button type="button" class="button button-secondary delete-font"
											data-font="<?php echo esc_attr($font_name); ?>">
											<?php esc_html_e('Delete', 'codeweber'); ?>
										</button>
									<?php else: ?>
										<span class="protected-font-wrappper">
											<span class="dashicons dashicons-lock" style="color:#ccc; font-size:20px;"
												title="<?php esc_attr_e('Protected font - cannot be deleted', 'codeweber'); ?>"></span>
											<small style="display:block; color:#666; font-size:11px;">
												<?php esc_html_e('Protected', 'codeweber'); ?>
											</small>
										</span>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<!-- Кнопка Apply под таблицей -->
				<div style="margin-top: 20px;">
					<button type="button" id="apply-selected-fonts" class="button button-primary">
						<?php esc_html_e('Apply Selected Fonts', 'codeweber'); ?>
					</button>
					<div id="apply-progress" style="margin-top: 10px;"></div>
				</div>
			</form>
		<?php endif; ?>
	</div>
</div>

<style>
	.redux-custom-fonts-container {
		margin: 20px 0;
	}

	.padding-5 {
		padding: 5px !important;
	}

	.font-upload-form {
		background: #f9f9f9;
		padding: 20px;
		border: 1px solid #ddd;
		margin-bottom: 30px;
		border-radius: 4px;
	}

	.protected-font-wrappper {
		display: flex;
		padding-top: 5px;
		padding-bottom: 5px;
		align-items: center;
	}

	.font-upload-form h4 {
		margin-top: 0;
	}

	.font-file-input {
		display: block;
		margin: 10px 0;
	}

	.uploaded-fonts-list {
		margin-top: 30px;
	}

	#upload-progress,
	#apply-progress {
		margin-top: 10px;
		min-height: 30px;
	}

	/* Стиль для защищенных шрифтов */
	.font-protected {
		background-color: #f9f9f9;
	}

	/* Выравнивание чекбоксов и радиокнопок */
	.font-checkbox,
	.primary-font-radio {
		margin: 0 auto;
		display: block;
	}

	/* Стили для кнопок */
	.button-success {
		background-color: #46b450 !important;
		border-color: #46b450 !important;
		color: white !important;
	}

	.button-success:hover {
		background-color: #3a9e43 !important;
		border-color: #3a9e43 !important;
	}

	button#apply-selected-fonts {
		display: flex;
		align-items: center;
	}

	/* Спиннер внутри кнопки */
	#apply-selected-fonts .spinner {
		float: none;
		margin: 0 5px 0 0;
		visibility: visible;
	}

	button#apply-selected-fonts
</style>

<script>
	jQuery(document).ready(function($) {
		// Обработчик для кнопки Apply под таблицей
		$('#apply-selected-fonts').on('click', function(e) {
			e.preventDefault();

			var button = $(this);
			var selectedFonts = [];
			var primaryFont = $('input[name="primary_font"]:checked').val();

			// Собираем выбранные шрифты
			$('input[name="selected_fonts[]"]:checked').each(function() {
				selectedFonts.push($(this).val());
			});

			if (selectedFonts.length === 0) {
				alert('<?php esc_html_e('Please select at least one font', 'codeweber'); ?>');
				return;
			}

			if (!primaryFont) {
				alert('<?php esc_html_e('Please select a primary font', 'codeweber'); ?>');
				return;
			}

			// Проверяем, что основной шрифт выбран среди подключенных
			if ($.inArray(primaryFont, selectedFonts) === -1) {
				alert('<?php esc_html_e('Primary font must be selected in the first column', 'codeweber'); ?>');
				return;
			}

			// Показываем спиннер
			button.html('<span class="spinner is-active" style="float:none;margin:0"></span> <?php esc_html_e('Applying...', 'codeweber'); ?>');
			button.prop('disabled', true);

			$.ajax({
				url: '<?php echo admin_url('admin-ajax.php'); ?>',
				type: 'POST',
				data: {
					action: 'redux_apply_custom_font',
					nonce: '<?php echo wp_create_nonce('redux_custom_fonts_nonce'); ?>',
					selected_fonts: selectedFonts,
					primary_font: primaryFont
				},
				success: function(response) {
					if (response.success) {
						button.html('<?php esc_html_e('Applied!', 'codeweber'); ?>');
						button.removeClass('button-primary').addClass('button-success');
						$('#apply-progress').html('<div class="notice notice-success"><p>' + response.data + '</p></div>');

						// Восстанавливаем через 2 секунды
						setTimeout(function() {
							button.html('<?php esc_html_e('Apply Selected Fonts', 'codeweber'); ?>');
							button.removeClass('button-success').addClass('button-primary');
							button.prop('disabled', false);
							$('#apply-progress').empty();
						}, 2000);

					} else {
						button.html('<?php esc_html_e('Apply Selected Fonts', 'codeweber'); ?>');
						button.prop('disabled', false);
						$('#apply-progress').html('<div class="notice notice-error"><p>' + response.data + '</p></div>');
					}
				},
				error: function() {
					button.html('<?php esc_html_e('Apply Selected Fonts', 'codeweber'); ?>');
					button.prop('disabled', false);
					$('#apply-progress').html('<div class="notice notice-error"><p><?php esc_html_e('Error applying fonts.', 'codeweber'); ?></p></div>');
				}
			});
		});

		// Загрузка шрифта
		$('#upload-font').on('click', function(e) {
			e.preventDefault();

			var fontName = $('#font-name').val().trim();
			var fontFiles = $('#font-files')[0].files;

			if (!fontName) {
				alert('<?php esc_html_e('Please enter a font family name', 'codeweber'); ?>');
				return;
			}

			if (fontFiles.length === 0) {
				alert('<?php esc_html_e('Please select font files to upload', 'codeweber'); ?>');
				return;
			}

			var formData = new FormData();
			formData.append('action', 'redux_upload_custom_font');
			formData.append('nonce', '<?php echo wp_create_nonce('redux_custom_fonts_nonce'); ?>');
			formData.append('font_name', fontName);

			for (var i = 0; i < fontFiles.length; i++) {
				formData.append('font_files[]', fontFiles[i]);
			}

			$('#upload-progress').html('<div class="spinner is-active"></div> <?php esc_html_e('Uploading...', 'codeweber'); ?>');

			$.ajax({
				url: '<?php echo admin_url('admin-ajax.php'); ?>',
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function(response) {
					if (response.success) {
						$('#upload-progress').html('<div class="notice notice-success"><p>' + response.data + '</p></div>');
						setTimeout(function() {
							location.reload();
						}, 1000);
					} else {
						$('#upload-progress').html('<div class="notice notice-error"><p>' + response.data + '</p></div>');
					}
				},
				error: function() {
					$('#upload-progress').html('<div class="notice notice-error"><p><?php esc_html_e('Error uploading font.', 'codeweber'); ?></p></div>');
				}
			});
		});

		// Удаление шрифта
		$(document).on('click', '.delete-font', function() {
			if (!confirm('<?php esc_html_e('Are you sure you want to delete this font?', 'codeweber'); ?>')) return;

			var button = $(this);
			var fontName = button.data('font');

			$.ajax({
				url: '<?php echo admin_url('admin-ajax.php'); ?>',
				type: 'POST',
				data: {
					action: 'redux_delete_custom_font',
					nonce: '<?php echo wp_create_nonce('redux_custom_fonts_nonce'); ?>',
					font_name: fontName
				},
				success: function(response) {
					if (response.success) {
						button.closest('tr').fadeOut(function() {
							$(this).remove();

							// Если строк не осталось, показываем сообщение
							if ($('.uploaded-fonts-list tbody tr').length === 0) {
								$('.uploaded-fonts-list').html('<h4><?php esc_html_e('Uploaded Fonts', 'codeweber'); ?></h4><p><?php esc_html_e('No fonts uploaded yet.', 'codeweber'); ?></p>');
							}
						});
					} else {
						alert(response.data);

						// Если это защищенный шрифт, скрываем кнопку удаления
						if (response.data.includes('protected') || response.data.includes('Protected')) {
							button.replaceWith('<span class="dashicons dashicons-lock" style="color:#ccc; font-size:20px;" title="<?php esc_attr_e('Protected font - cannot be deleted', 'codeweber'); ?>"></span><small style="display:block; color:#666; font-size:11px;"><?php esc_html_e('Protected', 'codeweber'); ?></small>');
						}
					}
				},
				error: function() {
					alert('<?php esc_html_e('Error deleting font.', 'codeweber'); ?>');
				}
			});
		});
	});
</script>
<?php
$font_content = ob_get_clean();

// Возвращаем массив с настройками поля
return array(
	array(
		'id'       => 'font_css_file',
		'type'     => 'select',
		'title'    => esc_html__('Font CSS File', 'codeweber'),
		'subtitle' => esc_html__('Select a CSS file from /dist/assets/fonts/', 'codeweber'),
		'options'  => codeweber_get_font_css_files(),
		'default'  => '',
	),

	array(
		'id'       => 'custom_fonts_upload',
		'type'     => 'raw',
		'title'    => esc_html__('Custom Fonts Upload', 'codeweber'),
		'subtitle' => esc_html__('Upload and manage custom fonts', 'codeweber'),
		'content'  => $font_content,
	),
	array(
		'id'       => 'fonts_combanation',
		'type'     => 'select',
		'title'    => esc_html__('Current Fonts', 'codeweber'),
		'options'  => redux_get_fonts_scss(),
		'default'  => '',
	),

);


/**
 * Get all CSS files from child + parent theme fonts folder
 *
 * @param string $subdir Relative path inside theme (default: dist/assets/fonts)
 * @return array Array of [ relative_path => label ]
 */
function codeweber_get_font_css_files( $subdir = 'dist/assets/fonts' ) {
    $font_options = [];

    // Проверяем сначала child theme, потом parent theme
    $dirs = [ get_stylesheet_directory(), get_template_directory() ];

    foreach ( $dirs as $base_dir ) {
        $dir = $base_dir . '/' . ltrim( $subdir, '/' );

        if ( is_dir( $dir ) ) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator( $dir, RecursiveDirectoryIterator::SKIP_DOTS )
            );

            foreach ( $iterator as $file ) {
                if ( $file->isFile() && strtolower( $file->getExtension() ) === 'css' ) {
                    // Относительный путь с префиксом темы, чтобы не было конфликтов
                    $theme_prefix  = basename( $base_dir );
                    $relative_path = $theme_prefix . '/' . str_replace( $base_dir . '/', '', $file->getPathname() );

                    // Метка: ThemeName → FolderName
                    $parent_dir = basename( dirname( $file->getPathname() ) );
                    $label      = ucfirst( $theme_prefix ) . ' → ' . ( $parent_dir ?: basename( $file ) );

                    $font_options[ $relative_path ] = $label;
                }
            }
        }
    }

    return $font_options;
}



function redux_get_fonts_scss()
{
	$theme_path = get_template_directory();
	$fonts_path = $theme_path . '/src/assets/scss/fonts/';

	$options = array(
		'' => esc_html__('Select Font Combination', 'codeweber')
	);

	// Проверяем существует ли папка
	if (!file_exists($fonts_path)) {
		return $options;
	}

	// Получаем SCSS файлы
	$files = glob($fonts_path . '*.scss');

	foreach ($files as $file) {
		if (is_file($file)) {
			$filename = basename($file); // Получаем имя файла с расширением
			$pretty_name = $filename;
			$options[$filename] = $pretty_name;
		}
	}

	return $options;
}

// Альтернативный вариант - если нужно использовать в Redux поле
function redux_fonts_combinations_field($field)
{
	$options = array(
		'' => esc_html__('Select Font Combination', 'codeweber')
	);

	$theme_path = get_template_directory();
	$fonts_path = $theme_path . '/src/assets/scss/fonts/';

	if (file_exists($fonts_path)) {
		$files = glob($fonts_path . '*.scss');

		foreach ($files as $file) {
			if (is_file($file)) {
				$filename = basename($file); // Получаем имя файла с расширением
				$pretty_name = $filename;
				$options[$filename] = $pretty_name;
			}
		}
	}

	return $options;
}



